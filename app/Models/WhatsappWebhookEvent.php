<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappWebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'idempotency_key',
        'message_id',
        'event_type',
        'source',
        'payload',
        'processed_at',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    public static function hasEventBeenProcessed(?string $eventId): bool
    {
        if (! $eventId) {
            return false;
        }

        return static::query()
            ->where('event_id', $eventId)
            ->whereNotNull('processed_at')
            ->exists();
    }
}
