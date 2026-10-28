<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo StockMovement — Registro inmutable de movimientos de inventario.
 *
 * Regla: Los registros NUNCA se actualizan ni eliminan en producción.
 * Para corregir un error se crea un movimiento de ajuste compensatorio.
 *
 * @property string $type
 * @property int $quantity
 * @property int $qty_before
 * @property int $qty_after
 */
class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'user_id',
        'type',
        'quantity',
        'qty_before',
        'qty_after',
        'reference',
        'reason',
        'notes',
        'document_number',
        'unit_cost',
        'related_warehouse_id',
        'reservation_id',
        'moved_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'qty_before' => 'integer',
        'qty_after' => 'integer',
        'unit_cost' => 'decimal:2',
        'moved_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'related_warehouse_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(StockReservation::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** Etiquetas legibles para el tipo de movimiento. */
    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'entry' => 'Entrada',
            'exit' => 'Salida',
            'adjustment' => 'Ajuste',
            'transfer_in' => 'Traslado entrada',
            'transfer_out' => 'Traslado salida',
            'return' => 'Devolución',
            'reservation' => 'Reserva',
            'release' => 'Liberación de reserva',
            default => ucfirst($type),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabel($this->type);
    }

    /** Alias de presentación para las vistas de Kardex. */
    public function getQtyAttribute(): int
    {
        return $this->quantity;
    }

    /** ¿El movimiento incrementa el stock físico? */
    public function isIncoming(): bool
    {
        return in_array($this->type, ['entry', 'transfer_in', 'return']);
    }

    /** ¿El movimiento decrementa el stock físico? */
    public function isOutgoing(): bool
    {
        return in_array($this->type, ['exit', 'transfer_out']);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
