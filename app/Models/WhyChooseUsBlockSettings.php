<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhyChooseUsBlockSettings extends Model
{
    use HasFactory;

    protected $table = 'why_choose_us_block_settings';

    protected $fillable = [
        'title',
        'is_active',
        'items',
        'additional_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'items' => 'array',
        'additional_settings' => 'array',
    ];

    /**
     * Получить настройки блока (singleton)
     */
    public static function getSettings(): self
    {
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([
                'title' => 'Почему выбирают нас',
                'is_active' => true,
                'items' => [],
                'additional_settings' => [],
            ]);
        }
        
        return $settings;
    }
}

