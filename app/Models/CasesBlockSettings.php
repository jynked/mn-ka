<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasesBlockSettings extends Model
{
    use HasFactory;

    protected $table = 'cases_block_settings';

    protected $fillable = [
        'title',
        'is_active',
        'case_ids',
        'additional_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'case_ids' => 'array',
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
                'title' => 'Кейсы и объекты',
                'is_active' => true,
                'case_ids' => [],
                'additional_settings' => [],
            ]);
        }
        
        return $settings;
    }
}

