<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqBlockSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'is_active',
        'faq_items',
        'additional_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'faq_items' => 'array',
        'additional_settings' => 'array',
    ];

    /**
     * Получить настройки блока (singleton)
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'title' => 'Частые вопросы',
            'is_active' => true,
            'faq_items' => [
                [
                    'question' => 'Как выбрать участок под мой проект?',
                    'answer' => 'We specialize in branding, UI/UX design, motion graphics, campaign assets, and content production. Whether you need a full brand identity or a quick-turn digital campaign, we\'ve got you covered.',
                ],
                [
                    'question' => 'Могу ли я изменить назначение земли?',
                    'answer' => 'We specialize in branding, UI/UX design, motion graphics, campaign assets, and content production. Whether you need a full brand identity or a quick-turn digital campaign, we\'ve got you covered.',
                ],
                [
                    'question' => 'Какие документы вы оформляете?',
                    'answer' => 'We specialize in branding, UI/UX design, motion graphics, campaign assets, and content production. Whether you need a full brand identity or a quick-turn digital campaign, we\'ve got you covered.',
                ],
                [
                    'question' => 'Сколько стоит услуга и как происходит оплата?',
                    'answer' => 'We specialize in branding, UI/UX design, motion graphics, campaign assets, and content production. Whether you need a full brand identity or a quick-turn digital campaign, we\'ve got you covered.',
                ],
                [
                    'question' => 'Насколько безопасна сделка?',
                    'answer' => 'We specialize in branding, UI/UX design, motion graphics, campaign assets, and content production. Whether you need a full brand identity or a quick-turn digital campaign, we\'ve got you covered.',
                ],
                [
                    'question' => 'Нет моего вопроса',
                    'answer' => 'We specialize in branding, UI/UX design, motion graphics, campaign assets, and content production. Whether you need a full brand identity or a quick-turn digital campaign, we\'ve got you covered.',
                ],
            ],
        ]);
    }
}
