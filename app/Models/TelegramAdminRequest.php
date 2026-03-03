<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramAdminRequest extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'telegram_username',
        'telegram_first_name',
        'telegram_last_name',
        'chat_id',
        'status',
        'approved_by',
        'user_id',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'telegram_user_id' => 'integer',
        'chat_id' => 'integer',
        'approved_by' => 'integer',
        'user_id' => 'integer',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Пользователь, который одобрил заявку
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Связанный пользователь системы
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить полное имя пользователя
     */
    public function getFullNameAttribute(): string
    {
        $name = trim(($this->telegram_first_name ?? '') . ' ' . ($this->telegram_last_name ?? ''));
        return $name ?: $this->telegram_username ?? 'Неизвестно';
    }
}
