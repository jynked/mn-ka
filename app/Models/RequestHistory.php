<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestHistory extends Model
{
    use HasFactory;

    protected $table = 'request_history';

    protected $fillable = [
        'request_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'comment',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Действия
    const ACTION_CREATED = 'created';
    const ACTION_STATUS_CHANGED = 'status_changed';
    const ACTION_ASSIGNED = 'assigned';
    const ACTION_NOTE_ADDED = 'note_added';
    const ACTION_COMPLETED = 'completed';
    const ACTION_CANCELLED = 'cancelled';
    const ACTION_REJECTED = 'rejected';

    /**
     * Получить названия действий
     */
    public static function getActions(): array
    {
        return [
            self::ACTION_CREATED => 'Создана',
            self::ACTION_STATUS_CHANGED => 'Изменен статус',
            self::ACTION_ASSIGNED => 'Назначена',
            self::ACTION_NOTE_ADDED => 'Добавлена заметка',
            self::ACTION_COMPLETED => 'Завершена',
            self::ACTION_CANCELLED => 'Отменена',
            self::ACTION_REJECTED => 'Отклонена',
        ];
    }

    /**
     * Заявка
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(ProductRequest::class, 'request_id');
    }

    /**
     * Пользователь, выполнивший действие
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить название действия
     */
    public function getActionNameAttribute(): string
    {
        return self::getActions()[$this->action] ?? $this->action;
    }
}
