<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HowWorkBlockSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'image_alt',
        'button_text',
        'button_type',
        'button_value',
        'is_active',
        'steps',
        'additional_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'steps' => 'array',
        'additional_settings' => 'array',
    ];

    /**
     * Получить настройки блока (singleton)
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'title' => 'Как мы работаем',
            'subtitle' => null,
            'image' => null,
            'image_alt' => 'Как мы работаем',
            'button_text' => 'Заказать обратный звонок',
            'button_type' => 'url',
            'button_value' => '',
            'is_active' => true,
            'steps' => [
                [
                    'point' => 'disc',
                    'title' => 'Вы оставляете заявку',
                    'description' => 'занимает не более <br>1-ой минуты',
                ],
                [
                    'point' => 'disc',
                    'title' => 'Мы подбираем участок <br>и проверяем документы',
                    'description' => null,
                ],
                [
                    'point' => 'disc',
                    'title' => 'Готовим и согласовываем <br>ИРД, ТУ, ВРИ',
                    'description' => null,
                ],
                [
                    'point' => 'disc',
                    'title' => 'Представляем ваши интересы <br>в госструктурах',
                    'description' => null,
                ],
                [
                    'point' => 'star',
                    'title' => 'Вы получаете участок, готовый <br> к реализации проекта',
                    'description' => 'Берём всё на себя — вы получаете<br> результат без бюрократии',
                ],
            ],
        ]);
    }
}
