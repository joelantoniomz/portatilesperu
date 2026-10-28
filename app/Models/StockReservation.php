<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo StockReservation — Reservas temporales de stock ligadas a pedidos.
 *
 * Una reserva en estado 'pending' descuenta qty_available pero NO qty_physical.
 * Una reserva en 'dispatched' ha disparado una salida real (exit movement).
 * Las reservas canceladas/expiradas se conservan para auditoría.
 *
 * @property string $status
 * @property int $quantity
 */
class StockReservation extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'user_id',
        'quantity',
        'status',
        'order_reference',
        'notes',
        'expires_at',
        'confirmed_at',
        'dispatched_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reservation_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'dispatched' => 'Despachada',
            'cancelled' => 'Cancelada',
            'expired' => 'Expirada',
            default => ucfirst($status),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }
}
