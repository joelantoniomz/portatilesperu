<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'direction',
        'sender_type',
        'type',
        'whatsapp_message_id',
        'status',
        'content',
        'attachment_url',
        'attachment_type',
        'attachment_filename',
        'attachment_size',
        'error_code',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'raw_payload',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'attachment_size' => 'integer',
        'raw_payload' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    public function isMedia(): bool
    {
        return ! empty($this->attachment_url) || in_array($this->type, ['image', 'document', 'audio', 'video'], true);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isDelivered(): bool
    {
        return in_array($this->status, ['delivered', 'read'], true);
    }

    public function isRead(): bool
    {
        return $this->status === 'read';
    }
}
