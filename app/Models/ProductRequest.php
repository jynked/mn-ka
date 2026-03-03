<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'phone',
        'email',
        'comment',
        'services',
        'status',
        'assigned_to',
        'created_by',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'services' => 'array',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Статусы заявок
    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    /**
     * Получить все доступные статусы
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'Новая',
            self::STATUS_IN_PROGRESS => 'В обработке',
            self::STATUS_COMPLETED => 'Завершена',
            self::STATUS_CANCELLED => 'Отменена',
            self::STATUS_REJECTED => 'Отклонена',
        ];
    }

    /**
     * Получить название статуса
     */
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Продукт, на который подана заявка
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Кому назначена заявка
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Кто создал заявку
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * История обработки заявки
     */
    public function history(): HasMany
    {
        return $this->hasMany(RequestHistory::class, 'request_id');
    }

    /**
     * Scope для фильтрации по статусу
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope для новых заявок
     */
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope для назначенных заявок
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Добавить запись в историю
     */
    public function addHistory(string $action, ?User $user = null, ?string $comment = null, array $changes = []): RequestHistory
    {
        return $this->history()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'old_status' => $this->getOriginal('status') ?? $this->status,
            'new_status' => $this->status,
            'comment' => $comment,
            'changes' => $changes,
        ]);
    }
}
