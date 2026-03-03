<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSettings extends Model
{
    protected $fillable = [
        'banner_image',
        'banner_overlay',
        'description',
        'statistics',
        'clients',
        'team',
        'benefits',
    ];

    protected $casts = [
        'banner_overlay' => 'boolean',
        'statistics' => 'array',
        'clients' => 'array',
        'team' => 'array',
        'benefits' => 'array',
    ];

    /**
     * Получить или создать настройки
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'banner_image' => null,
            'banner_overlay' => false,
            'description' => null,
            'statistics' => [],
            'clients' => [],
            'team' => [],
            'benefits' => [],
        ]);
    }
}





