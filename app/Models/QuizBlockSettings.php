<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizBlockSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'is_active',
    ];

    protected $casts = [
        'quiz_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Связь с квизом
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Получить настройки блока квиза (singleton)
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'quiz_id' => null,
            'is_active' => true,
        ]);
    }
}
