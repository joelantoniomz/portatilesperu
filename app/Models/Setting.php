<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Obtiene el número de WhatsApp oficial configurado en el sistema (limpio de caracteres no numéricos).
     */
    public static function getWhatsappNumber(): string
    {
        $raw = static::getValue('whatsapp_number', '51987654321');
        $cleaned = preg_replace('/[^0-9]/', '', (string) $raw);

        return ! empty($cleaned) ? $cleaned : '51987654321';
    }

    /**
     * Construye una URL de wa.me con el número oficial y un mensaje preformateado.
     */
    public static function getWhatsappUrl(?string $message = null): string
    {
        $number = static::getWhatsappNumber();
        if (empty($message)) {
            return "https://wa.me/{$number}";
        }

        return "https://wa.me/{$number}?text=".urlencode($message);
    }
}
