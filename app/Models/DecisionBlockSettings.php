<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecisionBlockSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'columns',
        'is_active',
        'additional_settings',
    ];

    protected $casts = [
        'columns' => 'integer',
        'is_active' => 'boolean',
        'additional_settings' => 'array',
    ];

    /**
     * Получить настройки блока (singleton)
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'title' => 'Выберите решение под ваш участок',
            'columns' => 3,
            'is_active' => true,
        ]);
    }
}
