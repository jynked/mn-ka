<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePageBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_key',
        'block_name',
        'component_name',
        'order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'settings' => 'array',
    ];

    /**
     * Scope для активных блоков
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для сортировки по порядку
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Получить все блоки в правильном порядке
     */
    public static function getOrderedBlocks(): array
    {
        return static::active()
            ->ordered()
            ->get()
            ->map(function ($block) {
                return [
                    'key' => $block->block_key,
                    'component' => $block->component_name,
                    'name' => $block->block_name,
                    'settings' => $block->settings ?? [],
                ];
            })
            ->toArray();
    }
}
