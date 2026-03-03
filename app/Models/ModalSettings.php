<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModalSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'content',
        'is_active',
        'additional_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'additional_settings' => 'array',
    ];

    /**
     * Получить настройки модального окна по типу
     */
    public static function getByType(string $type): ?self
    {
        return static::where('type', $type)->where('is_active', true)->first();
    }

    /**
     * Получить или создать настройки модального окна по типу
     */
    public static function getOrCreateByType(string $type, array $defaults = []): self
    {
        return static::firstOrCreate(
            ['type' => $type],
            array_merge([
                'title' => null,
                'content' => null,
                'is_active' => true,
                'additional_settings' => null,
            ], $defaults)
        );
    }
}
