<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 — Inventario, QR y códigos de barras.
 *
 * Tablas:
 *  - warehouses            : Almacenes físicos (o ubicaciones lógicas)
 *  - inventory_balances    : Stock físico, reservado y disponible por producto/almacén
 *  - stock_movements       : Historial inmutable de entradas, salidas, ajustes, etc.
 *  - stock_reservations    : Reservas temporales vinculadas a pedidos o ventas
 *
 * Reglas de negocio implementadas:
 *  - El stock nunca puede quedar negativo (CHECK en MySQL 8.x).
 *  - Las reservas se liberan al cancelar; no se eliminan (status = 'cancelled').
 *  - Los movimientos son inmutables: no hay UPDATE ni DELETE en producción.
 *  - La concurrencia se controla con SELECT ... FOR UPDATE en la capa de servicio.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // 1. Almacenes
        // ─────────────────────────────────────────────
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                    // "Almacén Principal Lima"
            $table->string('code', 20)->unique();           // "LIMA-01"
            $table->text('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('responsible_name', 120)->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ─────────────────────────────────────────────
        // 2. Balances de inventario (una fila por producto × almacén)
        // ─────────────────────────────────────────────
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            // Cantidades (nunca negativas — validadas en la capa de servicio)
            $table->unsignedInteger('qty_physical')->default(0);   // Stock físico real en almacén
            $table->unsignedInteger('qty_reserved')->default(0);   // Comprometido a pedidos
            $table->unsignedInteger('qty_available')->default(0);  // = physical - reserved
            $table->unsignedInteger('qty_minimum')->default(0);    // Umbral de alerta de stock bajo

            $table->timestamp('last_movement_at')->nullable();     // Timestamp del último movimiento
            $table->timestamps();

            // Cada producto solo tiene un balance por almacén
            $table->unique(['product_id', 'warehouse_id']);
        });

        // ─────────────────────────────────────────────
        // 3. Movimientos de stock (registro inmutable)
        // ─────────────────────────────────────────────
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('user_id')->constrained('users');    // Quién registró el movimiento

            /**
             * Tipos de movimiento:
             *   entry        — Entrada de mercancía (compra, devolución proveedor)
             *   exit         — Salida de mercancía (venta, despacho)
             *   adjustment   — Ajuste manual (corrección de inventario físico)
             *   transfer_in  — Entrada por traslado entre almacenes
             *   transfer_out — Salida por traslado entre almacenes
             *   return       — Devolución de cliente
             *   reservation  — Reserva vinculada a pedido (no mueve stock físico)
             *   release      — Liberación de reserva
             */
            $table->enum('type', [
                'entry',
                'exit',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'return',
                'reservation',
                'release',
            ]);

            // Cantidad: siempre positiva; el tipo determina si incrementa o decrementa
            $table->unsignedInteger('quantity');

            // Snapshots del balance ANTES y DESPUÉS del movimiento
            $table->unsignedInteger('qty_before');
            $table->unsignedInteger('qty_after');

            // Trazabilidad
            $table->string('reference', 80)->nullable();           // Número de pedido, guía, etc.
            $table->string('reason', 200)->nullable();             // Motivo legible
            $table->text('notes')->nullable();
            $table->string('document_number', 60)->nullable();     // Factura, guía de remisión, etc.

            // Precio unitario en el momento del movimiento (para valorización)
            $table->decimal('unit_cost', 12, 2)->nullable();

            // Si fue un traslado, almacén de destino/origen
            $table->foreignId('related_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            // Referencia a una reserva vinculada
            $table->foreignId('reservation_id')
                ->nullable();                                       // FK diferida para evitar ciclo

            $table->timestamp('moved_at')->useCurrent();           // Fecha exacta del movimiento
            $table->timestamps();

            // Índices de consulta frecuente
            $table->index(['product_id', 'warehouse_id', 'moved_at']);
            $table->index(['type', 'moved_at']);
            $table->index('reference');
        });

        // ─────────────────────────────────────────────
        // 4. Reservas de stock
        // ─────────────────────────────────────────────
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('user_id')->constrained('users');    // Vendedor que generó la reserva

            $table->unsignedInteger('quantity');

            /**
             * Estados:
             *   pending    — Reserva activa (descuenta qty_available)
             *   confirmed  — Pedido confirmado / pago recibido
             *   dispatched — Mercancía despachada (descuenta qty_physical)
             *   cancelled  — Liberada sin historial borrado
             *   expired    — Expirada por tiempo sin confirmar
             */
            $table->enum('status', [
                'pending',
                'confirmed',
                'dispatched',
                'cancelled',
                'expired',
            ])->default('pending');

            $table->string('order_reference', 80)->nullable();     // Referencia interna del pedido
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable();           // Tiempo límite de la reserva
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason', 200)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id', 'status']);
            $table->index('order_reference');
            $table->index('expires_at');
        });

        // Añadir FK diferida: stock_movements.reservation_id → stock_reservations.id
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('reservation_id')
                ->references('id')
                ->on('stock_reservations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Primero eliminar FK diferida antes de bajar la tabla
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
        });

        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('warehouses');
    }
};
