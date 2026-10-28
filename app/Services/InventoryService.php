<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\InventoryBalance;
use App\Models\StockMovement;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * InventoryService — Toda la lógica de inventario pasa por aquí.
 *
 * Principios:
 *  - Toda modificación de stock ocurre dentro de una transacción DB.
 *  - Antes de modificar un balance se usa lockForUpdate() (SELECT ... FOR UPDATE)
 *    para evitar sobreventa en solicitudes concurrentes.
 *  - Las cantidades nunca quedan negativas; se lanza una excepción si no hay suficiente.
 *  - Cada operación genera un StockMovement inmutable para auditoría.
 *  - Escanear un código QR/barras NO mueve stock; solo lo localiza.
 *
 * Firma de AuditLog::log():
 *   log(string $action, ?string $entityType = null, ?int $entityId = null,
 *       ?array $oldValues = null, ?array $newValues = null, ?int $userId = null)
 */
class InventoryService
{
    // ──────────────────────────────────────────────────────────────────────────
    // Obtener o crear balance
    // ──────────────────────────────────────────────────────────────────────────

    public function getOrCreateBalance(int $productId, int $warehouseId): InventoryBalance
    {
        return InventoryBalance::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['qty_physical' => 0, 'qty_reserved' => 0, 'qty_available' => 0, 'qty_minimum' => 0]
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Entradas de stock
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Registra una entrada de mercancía.
     *
     * @param  string  $type  entry | transfer_in | return
     *
     * @throws \Throwable
     */
    public function registerEntry(
        int $productId,
        int $warehouseId,
        int $userId,
        int $quantity,
        string $type = 'entry',
        ?string $reference = null,
        ?string $reason = null,
        ?string $documentNumber = null,
        ?float $unitCost = null,
        ?int $relatedWarehouseId = null,
    ): StockMovement {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        return DB::transaction(function () use (
            $productId, $warehouseId, $userId, $quantity, $type,
            $reference, $reason, $documentNumber, $unitCost, $relatedWarehouseId
        ) {
            $balance = InventoryBalance::lockForUpdate()->firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['qty_physical' => 0, 'qty_reserved' => 0, 'qty_available' => 0, 'qty_minimum' => 0]
            );

            $qtyBefore = $balance->qty_physical;
            $balance->qty_physical += $quantity;
            $balance->qty_available = $balance->qty_physical - $balance->qty_reserved;
            $balance->last_movement_at = now();
            $balance->save();

            $movement = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $quantity,
                'qty_before' => $qtyBefore,
                'qty_after' => $balance->qty_physical,
                'reference' => $reference,
                'reason' => $reason,
                'document_number' => $documentNumber,
                'unit_cost' => $unitCost,
                'related_warehouse_id' => $relatedWarehouseId,
                'moved_at' => now(),
            ]);

            AuditLog::log(
                'inventory.entry',
                'StockMovement',
                $movement->id,
                null,
                ['qty_added' => $quantity, 'warehouse_id' => $warehouseId, 'reference' => $reference],
                $userId
            );

            return $movement;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Salidas de stock
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Registra una salida directa (sin reserva previa).
     *
     * @param  string  $type  exit | transfer_out
     *
     * @throws ValidationException|\Throwable
     */
    public function registerExit(
        int $productId,
        int $warehouseId,
        int $userId,
        int $quantity,
        string $type = 'exit',
        ?string $reference = null,
        ?string $reason = null,
        ?string $documentNumber = null,
        ?float $unitCost = null,
        ?int $relatedWarehouseId = null,
    ): StockMovement {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }

        return DB::transaction(function () use (
            $productId, $warehouseId, $userId, $quantity, $type,
            $reference, $reason, $documentNumber, $unitCost, $relatedWarehouseId
        ) {
            $balance = InventoryBalance::lockForUpdate()
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if (! $balance || $balance->qty_physical < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        'Stock insuficiente. Disponible: '.($balance?->qty_physical ?? 0).", solicitado: {$quantity}.",
                    ],
                ]);
            }

            $qtyBefore = $balance->qty_physical;
            $balance->qty_physical -= $quantity;
            $balance->qty_available = max(0, $balance->qty_physical - $balance->qty_reserved);
            $balance->last_movement_at = now();
            $balance->save();

            $movement = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $quantity,
                'qty_before' => $qtyBefore,
                'qty_after' => $balance->qty_physical,
                'reference' => $reference,
                'reason' => $reason,
                'document_number' => $documentNumber,
                'unit_cost' => $unitCost,
                'related_warehouse_id' => $relatedWarehouseId,
                'moved_at' => now(),
            ]);

            AuditLog::log(
                'inventory.exit',
                'StockMovement',
                $movement->id,
                ['qty_physical' => $qtyBefore],
                ['qty_physical' => $balance->qty_physical, 'reference' => $reference],
                $userId
            );

            return $movement;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Ajuste de inventario físico
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Ajusta el stock a una cantidad exacta (toma física).
     *
     * @throws \Throwable
     */
    public function registerAdjustment(
        int $productId,
        int $warehouseId,
        int $userId,
        int $newPhysicalQty,
        ?string $reason = null,
        ?string $documentNumber = null,
    ): StockMovement {
        if ($newPhysicalQty < 0) {
            throw new \InvalidArgumentException('El stock ajustado no puede ser negativo.');
        }

        return DB::transaction(function () use (
            $productId, $warehouseId, $userId, $newPhysicalQty, $reason, $documentNumber
        ) {
            $balance = InventoryBalance::lockForUpdate()->firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['qty_physical' => 0, 'qty_reserved' => 0, 'qty_available' => 0, 'qty_minimum' => 0]
            );

            $qtyBefore = $balance->qty_physical;
            $difference = abs($newPhysicalQty - $qtyBefore);

            $balance->qty_physical = $newPhysicalQty;
            $balance->qty_available = max(0, $newPhysicalQty - $balance->qty_reserved);
            $balance->last_movement_at = now();
            $balance->save();

            $movement = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'type' => 'adjustment',
                'quantity' => $difference,
                'qty_before' => $qtyBefore,
                'qty_after' => $newPhysicalQty,
                'reason' => $reason ?? 'Ajuste de inventario físico',
                'document_number' => $documentNumber,
                'moved_at' => now(),
            ]);

            AuditLog::log(
                'inventory.adjustment',
                'StockMovement',
                $movement->id,
                ['qty_physical' => $qtyBefore],
                ['qty_physical' => $newPhysicalQty, 'reason' => $reason],
                $userId
            );

            return $movement;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Reservas de stock
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Crea una reserva de stock.
     *
     * REGLA DE NEGOCIO: El escaneo QR/barras NO reserva stock.
     * Solo la capa de pedidos llama explícitamente a este método.
     *
     * @throws ValidationException|\Throwable
     */
    public function createReservation(
        int $productId,
        int $warehouseId,
        int $userId,
        int $quantity,
        ?string $orderReference = null,
        ?string $notes = null,
        ?\DateTimeInterface $expiresAt = null,
    ): StockReservation {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La cantidad a reservar debe ser mayor a cero.');
        }

        return DB::transaction(function () use (
            $productId, $warehouseId, $userId, $quantity, $orderReference, $notes, $expiresAt
        ) {
            $balance = InventoryBalance::lockForUpdate()
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if (! $balance || $balance->qty_available < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        'Stock disponible insuficiente. Disponible: '.($balance?->qty_available ?? 0).", solicitado: {$quantity}.",
                    ],
                ]);
            }

            $balance->qty_reserved += $quantity;
            $balance->qty_available -= $quantity;
            $balance->last_movement_at = now();
            $balance->save();

            $reservation = StockReservation::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'status' => 'pending',
                'order_reference' => $orderReference,
                'notes' => $notes,
                'expires_at' => $expiresAt,
            ]);

            // Movimiento informativo (no mueve stock físico)
            StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'type' => 'reservation',
                'quantity' => $quantity,
                'qty_before' => $balance->qty_physical,
                'qty_after' => $balance->qty_physical,
                'reference' => $orderReference,
                'reason' => "Reserva #{$reservation->id} creada",
                'reservation_id' => $reservation->id,
                'moved_at' => now(),
            ]);

            AuditLog::log(
                'inventory.reservation.created',
                'StockReservation',
                $reservation->id,
                null,
                ['quantity' => $quantity, 'order_reference' => $orderReference],
                $userId
            );

            return $reservation;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cancelar reserva
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Cancela una reserva activa y libera el stock disponible.
     * El historial se conserva (no se borra nada).
     *
     * @throws ValidationException|\Throwable
     */
    public function cancelReservation(
        int $reservationId,
        int $cancelledBy,
        string $reason,
    ): StockReservation {
        return DB::transaction(function () use ($reservationId, $cancelledBy, $reason) {
            $reservation = StockReservation::lockForUpdate()->findOrFail($reservationId);

            if (! $reservation->isActive()) {
                throw ValidationException::withMessages([
                    'reservation' => [
                        "La reserva #{$reservationId} no está activa (estado: {$reservation->status}).",
                    ],
                ]);
            }

            $balance = InventoryBalance::lockForUpdate()
                ->where('product_id', $reservation->product_id)
                ->where('warehouse_id', $reservation->warehouse_id)
                ->firstOrFail();

            $balance->qty_reserved = max(0, $balance->qty_reserved - $reservation->quantity);
            $balance->qty_available = $balance->qty_physical - $balance->qty_reserved;
            $balance->last_movement_at = now();
            $balance->save();

            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledBy,
                'cancellation_reason' => $reason,
            ]);

            StockMovement::create([
                'product_id' => $reservation->product_id,
                'warehouse_id' => $reservation->warehouse_id,
                'user_id' => $cancelledBy,
                'type' => 'release',
                'quantity' => $reservation->quantity,
                'qty_before' => $balance->qty_physical,
                'qty_after' => $balance->qty_physical,
                'reference' => $reservation->order_reference,
                'reason' => "Liberación de reserva #{$reservation->id}: {$reason}",
                'reservation_id' => $reservation->id,
                'moved_at' => now(),
            ]);

            AuditLog::log(
                'inventory.reservation.cancelled',
                'StockReservation',
                $reservation->id,
                ['status' => 'pending'],
                ['status' => 'cancelled', 'reason' => $reason],
                $cancelledBy
            );

            return $reservation;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Expirar reservas vencidas (para comando artisan)
    // ──────────────────────────────────────────────────────────────────────────

    public function expireOverdueReservations(): int
    {
        $expired = StockReservation::expired()->get();
        $count = 0;

        foreach ($expired as $reservation) {
            try {
                DB::transaction(function () use ($reservation) {
                    $balance = InventoryBalance::lockForUpdate()
                        ->where('product_id', $reservation->product_id)
                        ->where('warehouse_id', $reservation->warehouse_id)
                        ->first();

                    if ($balance) {
                        $balance->qty_reserved = max(0, $balance->qty_reserved - $reservation->quantity);
                        $balance->qty_available = $balance->qty_physical - $balance->qty_reserved;
                        $balance->save();
                    }

                    $reservation->update([
                        'status' => 'expired',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Reserva expirada automáticamente',
                    ]);

                    StockMovement::create([
                        'product_id' => $reservation->product_id,
                        'warehouse_id' => $reservation->warehouse_id,
                        'user_id' => $reservation->user_id,
                        'type' => 'release',
                        'quantity' => $reservation->quantity,
                        'qty_before' => $balance?->qty_physical ?? 0,
                        'qty_after' => $balance?->qty_physical ?? 0,
                        'reason' => "Liberación automática por expiración de reserva #{$reservation->id}",
                        'reservation_id' => $reservation->id,
                        'moved_at' => now(),
                    ]);
                });
                $count++;
            } catch (\Throwable) {
                // Continuar con la siguiente reserva
            }
        }

        return $count;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Traslado entre almacenes
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Traslada unidades de un almacén a otro.
     * Genera dos movimientos: transfer_out (origen) y transfer_in (destino).
     *
     * @throws ValidationException|\Throwable
     */
    public function transferBetweenWarehouses(
        int $productId,
        int $fromWarehouseId,
        int $toWarehouseId,
        int $userId,
        int $quantity,
        ?string $reference = null,
        ?string $reason = null,
    ): array {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new \InvalidArgumentException('El almacén de origen y destino no pueden ser el mismo.');
        }

        return DB::transaction(function () use (
            $productId, $fromWarehouseId, $toWarehouseId, $userId, $quantity, $reference, $reason
        ) {
            // Bloquear en orden consistente para evitar deadlocks
            $ids = [$fromWarehouseId, $toWarehouseId];
            sort($ids);

            $balances = InventoryBalance::lockForUpdate()
                ->where('product_id', $productId)
                ->whereIn('warehouse_id', $ids)
                ->get()
                ->keyBy('warehouse_id');

            $origin = $balances[$fromWarehouseId] ?? null;

            if (! $origin || $origin->qty_physical < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        'Stock insuficiente en el almacén de origen. Disponible: '.($origin?->qty_physical ?? 0),
                    ],
                ]);
            }

            // Actualizar origen
            $originBefore = $origin->qty_physical;
            $origin->qty_physical -= $quantity;
            $origin->qty_available = max(0, $origin->qty_physical - $origin->qty_reserved);
            $origin->last_movement_at = now();
            $origin->save();

            // Actualizar o crear destino
            $destination = $balances[$toWarehouseId] ?? InventoryBalance::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $toWarehouseId],
                ['qty_physical' => 0, 'qty_reserved' => 0, 'qty_available' => 0, 'qty_minimum' => 0]
            );
            $destBefore = $destination->qty_physical;
            $destination->qty_physical += $quantity;
            $destination->qty_available = $destination->qty_physical - $destination->qty_reserved;
            $destination->last_movement_at = now();
            $destination->save();

            $out = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'user_id' => $userId,
                'type' => 'transfer_out',
                'quantity' => $quantity,
                'qty_before' => $originBefore,
                'qty_after' => $origin->qty_physical,
                'reference' => $reference,
                'reason' => $reason ?? 'Traslado de almacén',
                'related_warehouse_id' => $toWarehouseId,
                'moved_at' => now(),
            ]);

            $in = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'user_id' => $userId,
                'type' => 'transfer_in',
                'quantity' => $quantity,
                'qty_before' => $destBefore,
                'qty_after' => $destination->qty_physical,
                'reference' => $reference,
                'reason' => $reason ?? 'Traslado de almacén',
                'related_warehouse_id' => $fromWarehouseId,
                'moved_at' => now(),
            ]);

            AuditLog::log(
                'inventory.transfer',
                'StockMovement',
                $out->id,
                ['warehouse_from' => $fromWarehouseId, 'qty' => $originBefore],
                ['warehouse_to' => $toWarehouseId, 'quantity' => $quantity],
                $userId
            );

            return ['out' => $out, 'in' => $in];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Consultas (solo lectura)
    // ──────────────────────────────────────────────────────────────────────────

    /** Stock global consolidado de un producto en todos los almacenes. */
    public function getGlobalStock(int $productId): array
    {
        $balances = InventoryBalance::where('product_id', $productId)
            ->with('warehouse:id,name,code')
            ->get();

        $byWarehouse = $balances->map(fn ($b) => [
            'warehouse_id' => $b->warehouse_id,
            'warehouse_name' => $b->warehouse?->name ?? 'Almacén',
            'warehouse_code' => $b->warehouse?->code ?? '',
            'qty_physical' => (int) $b->qty_physical,
            'qty_reserved' => (int) $b->qty_reserved,
            'qty_available' => (int) $b->qty_available,
        ])->toArray();

        return [
            'total_physical' => (int) $balances->sum('qty_physical'),
            'total_reserved' => (int) $balances->sum('qty_reserved'),
            'total_available' => (int) $balances->sum('qty_available'),
            'warehouses' => $balances->toArray(),
            'by_warehouse' => $byWarehouse,
        ];
    }
}
