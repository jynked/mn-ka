<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSettings extends Model
{
    use HasFactory;

    protected $table = 'email_settings';

    protected $fillable = [
        'recipient_email',
    ];

    /**
     * Получить настройки email (singleton)
     */
    public static function getSettings(): self
    {
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([
                'recipient_email' => 'info@mn-ka.ru',
            ]);
        }
        
        return $settings;
    }
}
