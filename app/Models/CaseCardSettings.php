<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseCardSettings extends Model
{
    use HasFactory;

    protected $table = 'case_card_settings';

    protected $fillable = [
        'page_title',
        'page_description',
        'items_per_page',
        'show_filters',
        'show_breadcrumbs',
        'card_aspect_ratio',
    ];

    protected $casts = [
        'items_per_page' => 'integer',
        'show_filters' => 'boolean',
        'show_breadcrumbs' => 'boolean',
    ];

    /**
     * Получить настройки карточек кейсов (singleton)
     */
    public static function getSettings(): self
    {
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([
                'page_title' => 'Наши кейсы',
                'page_description' => null,
                'items_per_page' => 6,
                'show_filters' => true,
                'show_breadcrumbs' => true,
                'card_aspect_ratio' => '16/10',
            ]);
        }
        
        return $settings;
    }
}



