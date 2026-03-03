<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageSettings extends Model
{
    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_button_text',
        'hero_button_link',
        'select_title',
        'select_subtitle',
        'work_title',
        'work_items',
        'work_button_text',
        'work_button_link',
        'faq_title',
        'faq_items',
        'benefits_title',
        'benefits_items',
        'contact_title',
        'contact_subtitle',
        'contact_form_hint_text',
    ];

    protected $casts = [
        'work_items' => 'array',
        'faq_items' => 'array',
        'benefits_items' => 'array',
    ];

    /**
     * Получить единственную запись настроек или создать новую
     */
    public static function getSettings()
    {
        return static::firstOrCreate([], []);
    }
}
