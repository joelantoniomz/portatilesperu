<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSession extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_sessions';

    protected $fillable = [
        'session_id',
        'status',
        'phone_number',
        'qr_code',
        'battery_level',
        'is_connected',
        'connection_data',
        'last_active_at',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'battery_level' => 'integer',
        'connection_data' => 'array',
        'last_active_at' => 'datetime',
    ];

    /**
     * Obtiene o inicializa la sesión por su session_id.
     */
    public static function getSession(string $sessionId = 'default'): self
    {
        return static::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'status' => 'STOPPED',
                'is_connected' => false,
            ]
        );
    }
}

