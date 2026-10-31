<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_id',
        'user_id',
        'channel',
        'session_id',
        'customer_phone',
        'customer_name',
        'order_reference',
        'status',
        'unread_count',
        'summary',
        'last_message_preview',
        'last_activity_at',
        'last_incoming_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'last_incoming_at' => 'datetime',
        'unread_count' => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('sent_at');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * URL de WhatsApp directo al teléfono de la conversación.
     */
    public function getWhatsappUrlAttribute(): string
    {
        if (! $this->customer_phone) {
            return '#';
        }
        $clean = preg_replace('/[^0-9]/', '', $this->customer_phone);
        if (! str_starts_with($clean, '51') && strlen($clean) === 9) {
            $clean = '51'.$clean;
        }
        $text = urlencode(
            $this->order_reference
                ? "Hola, continuando la conversación sobre tu pedido {$this->order_reference}. ¿En qué podemos ayudarte?"
                : "Hola {$this->customer_name}, ¿En qué podemos ayudarte hoy? 💻"
        );

        return "https://wa.me/{$clean}?text={$text}";
    }

    /**
     * Color del badge según el estado de la conversación.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'amber',
            'in_progress' => 'sky',
            'resolved' => 'emerald',
            'archived' => 'slate',
            default => 'slate',
        };
    }

    /**
     * Etiqueta legible del estado.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Abierta',
            'in_progress' => 'En Atención',
            'resolved' => 'Resuelta',
            'archived' => 'Archivada',
            default => ucfirst($this->status),
        };
    }

    /**
     * Determina si la conversación está dentro de la ventana de atención de 24 horas de WhatsApp.
     */
    public function isWithin24HourWindow(): bool
    {
        if (! $this->last_incoming_at) {
            return false;
        }

        return $this->last_incoming_at->greaterThanOrEqualTo(now()->subHours(24));
    }

    /**
     * Fecha y hora exacta de expiración de la ventana de 24 horas.
     */
    public function getWindowExpiresAtAttribute(): ?Carbon
    {
        return $this->last_incoming_at ? $this->last_incoming_at->copy()->addHours(24) : null;
    }

    /**
     * Teléfono limpio con código de país para WhatsApp.
     */
    public function cleanPhone(): string
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $this->customer_phone);
        if (! str_starts_with($clean, '51') && strlen($clean) === 9) {
            $clean = '51'.$clean;
        }

        return $clean;
    }

    /**
     * Marca la conversación como leída y reinicia el contador de no leídos.
     */
    public function markAsRead(): void
    {
        if ($this->unread_count > 0) {
            $this->update(['unread_count' => 0]);
        }

        $this->messages()
            ->where('direction', 'inbound')
            ->whereNull('read_at')
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
    }
}
