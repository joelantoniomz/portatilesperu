<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_city',
        'customer_address',
        'document_type',
        'document_number',
        'invoice_type',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'discount',
        'shipping_cost',
        'total',
        'currency',
        'notes',
        'whatsapp_message',
        'whatsapp_sent_at',
        'user_id',
        'warehouse_id',
        'customer_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'whatsapp_sent_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Genera un número de pedido único con formato PED-YYYY-XXXXX.
     */
    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = static::whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $nextNumber = 1;
        if ($lastOrder && preg_match('/PED-\d{4}-(\d+)/', $lastOrder->order_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return sprintf('PED-%s-%05d', $year, $nextNumber);
    }

    /**
     * Total formateado en Soles o Dólares.
     */
    public function getFormattedTotalAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : 'S/';

        return "{$symbol} ".number_format($this->total, 2);
    }

    /**
     * URL codificada para abrir WhatsApp Web / App.
     */
    public function getWhatsappUrlAttribute(): string
    {
        $phone = config('app.whatsapp_number', '51987654321');
        $text = urlencode($this->whatsapp_message ?? "Hola Portátiles Perú, consulto por mi pedido {$this->order_number}");

        return "https://wa.me/{$phone}?text={$text}";
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'confirmed', 'in_preparation', 'dispatched']);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%")
                    ->orWhere('customer_email', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
