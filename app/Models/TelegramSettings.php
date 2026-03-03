<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramSettings extends Model
{
    protected $fillable = [
        'bot_token',
        'bot_name',
        'chat_id',
        'webhook_url',
        'is_enabled',
        'send_notifications',
        'send_errors',
        'parse_mode',
        'disable_notification',
        'reply_to_message_id',
        'disable_web_page_preview',
        'additional_settings',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'send_notifications' => 'boolean',
        'send_errors' => 'boolean',
        'disable_notification' => 'boolean',
        'disable_web_page_preview' => 'boolean',
        'reply_to_message_id' => 'integer',
        'additional_settings' => 'array',
    ];

    /**
     * Получить или создать настройки
     */
    public static function getSettings(): self
    {
        $settings = static::firstOrCreate([], [
            'is_enabled' => false,
            'send_notifications' => true,
            'send_errors' => true,
            'parse_mode' => 'HTML',
        ]);
        
        // Автоматически включаем бота, если есть токен и chat_id
        if (!$settings->is_enabled && !empty($settings->bot_token) && !empty($settings->chat_id)) {
            $settings->is_enabled = true;
            $settings->save();
        }
        
        return $settings;
    }
}
