<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo InventoryBalance — Balance de stock por producto × almacén.
 *
 * Invariante: qty_available = qty_physical - qty_reserved (siempre >= 0).
 * Este modelo NO se modifica directamente; se actualiza a través del InventoryService.
 *
 * @property int $qty_physical
 * @property int $qty_reserved
 * @property int $qty_available
 * @property int $qty_minimum
 */
class InventoryBalance extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'qty_physical',
        'qty_reserved',
        'qty_available',
        'qty_minimum',
        'last_movement_at',
    ];

    protected $casts = [
        'qty_physical' => 'integer',
        'qty_reserved' => 'integer',
        'qty_available' => 'integer',
        'qty_minimum' => 'integer',
        'last_movement_at' => 'datetime',
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

    // ─── Accessors / Helpers ──────────────────────────────────────────────────

    /** ¿El stock disponible está por debajo del mínimo? */
    public function isBelowMinimum(): bool
    {
        return $this->qty_available <= $this->qty_minimum;
    }

    /** ¿Hay suficiente stock disponible para la cantidad solicitada? */
    public function hasAvailable(int $qty): bool
    {
        return $this->qty_available >= $qty;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Balances con stock por debajo del mínimo (alertas). */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('qty_available', '<=', 'qty_minimum')
            ->where('qty_minimum', '>', 0);
    }

    /** Balances con stock agotado. */
    public function scopeOutOfStock($query)
    {
        return $query->where('qty_available', 0);
    }
}
