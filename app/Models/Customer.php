<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'city',
        'address',
        'document_type',
        'document_number',
        'company_name',
        'source',
        'status',
        'internal_notes',
        'first_purchase_at',
        'last_purchase_at',
        'orders_count',
        'total_spent',
    ];

    protected $casts = [
        'first_purchase_at' => 'datetime',
        'last_purchase_at' => 'datetime',
        'total_spent' => 'decimal:2',
        'orders_count' => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class)->latest();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class)->latest();
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Total gastado formateado en Soles.
     */
    public function getFormattedTotalSpentAttribute(): string
    {
        return 'S/ '.number_format($this->total_spent, 2);
    }

    /**
     * URL de WhatsApp directo al cliente.
     */
    public function getWhatsappUrlAttribute(): string
    {
        if (! $this->phone) {
            return '#';
        }
        $clean = preg_replace('/[^0-9]/', '', $this->phone);
        if (! str_starts_with($clean, '51') && strlen($clean) === 9) {
            $clean = '51'.$clean;
        }

        return 'https://wa.me/'.$clean;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('document_number', 'like', "%{$term}%")
                    ->orWhere('company_name', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['city'])) {
            $query->where('city', 'like', '%'.$filters['city'].'%');
        }

        return $query;
    }

    // ── Métodos de Negocio ────────────────────────────────────────────────────

    /**
     * Recalcula métricas del cliente desde sus pedidos confirmados/completados.
     */
    public function recalculateMetrics(): void
    {
        $orders = $this->orders()
            ->whereIn('status', ['confirmed', 'in_preparation', 'dispatched', 'completed'])
            ->get();

        $this->update([
            'orders_count' => $orders->count(),
            'total_spent' => $orders->sum('total'),
            'first_purchase_at' => $orders->min('created_at'),
            'last_purchase_at' => $orders->max('created_at'),
        ]);
    }

    /**
     * Encuentra o crea un cliente a partir de los datos de un pedido.
     */
    public static function findOrCreateFromOrder(array $data): static
    {
        // Buscar primero por teléfono (identificador más confiable en Perú)
        $phone = trim($data['customer_phone'] ?? '');
        $email = ! empty($data['customer_email']) ? trim($data['customer_email']) : null;

        $customer = null;

        if ($phone) {
            $customer = static::where('phone', $phone)->first();
        }

        if (! $customer && $email) {
            $customer = static::where('email', $email)->first();
        }

        if (! $customer) {
            $customer = static::create([
                'name' => trim($data['customer_name'] ?? 'Cliente'),
                'phone' => $phone ?: null,
                'email' => $email,
                'city' => trim($data['customer_city'] ?? ''),
                'address' => ! empty($data['customer_address']) ? trim($data['customer_address']) : null,
                'document_type' => $data['document_type'] ?? 'dni',
                'document_number' => ! empty($data['document_number']) ? trim($data['document_number']) : null,
                'source' => 'web',
                'status' => 'active',
            ]);
        } else {
            // Actualizar datos si hay nuevos
            $customer->fill([
                'name' => trim($data['customer_name']) ?: $customer->name,
                'city' => trim($data['customer_city'] ?? '') ?: $customer->city,
                'address' => ! empty($data['customer_address']) ? trim($data['customer_address']) : $customer->address,
            ])->save();
        }

        return $customer;
    }
}
