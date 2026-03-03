<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSettings extends Model
{
    protected $fillable = [
        'phone',
        'email',
        'address',
        'working_hours',
        'socials',
    ];

    protected $casts = [
        'socials' => 'array',
    ];

    /**
     * Получить или создать настройки
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'phone' => null,
            'email' => null,
            'address' => null,
            'working_hours' => null,
            'socials' => [],
        ]);
    }
}





