<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Warehouse — Almacenes físicos o ubicaciones lógicas.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $is_active
 */
class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'responsible_name',
        'phone',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function inventoryBalances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
