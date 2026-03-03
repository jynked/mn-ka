<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NotificationTool
{
    /**
     * Получить уведомления для пользователя
     *
     * @param User|null $user
     * @param int $limit
     * @param bool $onlyUnread
     * @return Collection
     */
    public function getNotifications(?User $user = null, int $limit = 10, bool $onlyUnread = true): Collection
    {
        if (!$user) {
            return collect([]);
        }

        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($onlyUnread) {
            $query->where('read', false);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Добавить уведомление
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param string|null $type
     * @param array|null $data
     * @param bool $sendToTelegram Отправлять ли в Telegram (по умолчанию true)
     * @return Notification
     */
    public function addNotification(User $user, string $title, string $message, ?string $type = 'info', ?array $data = null, bool $sendToTelegram = true): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'read' => false,
        ]);

        // Отправляем уведомление в Telegram всем администраторам, если включено
        if ($sendToTelegram) {
            try {
                $telegramSettings = \App\Models\TelegramSettings::getSettings();
                
                \Illuminate\Support\Facades\Log::debug('Telegram notification check', [
                    'is_enabled' => $telegramSettings->is_enabled,
                    'send_notifications' => $telegramSettings->send_notifications,
                    'has_bot_token' => !empty($telegramSettings->bot_token),
                    'has_chat_id' => !empty($telegramSettings->chat_id),
                ]);
                
                if ($telegramSettings->is_enabled && $telegramSettings->send_notifications) {
                // Получаем всех администраторов с telegram_chat_id
                $adminUsers = \App\Models\User::whereNotNull('telegram_chat_id')
                    ->whereHas('roles', function ($query) {
                        $query->whereIn('slug', ['admin', 'manager']);
                    })
                    ->get();

                \Illuminate\Support\Facades\Log::debug('Telegram admins found', [
                    'count' => $adminUsers->count(),
                    'admin_ids' => $adminUsers->pluck('id')->toArray(),
                        'chat_ids' => $adminUsers->pluck('telegram_chat_id')->toArray(),
                ]);

                if ($adminUsers->isNotEmpty()) {
                    $telegramService = new \App\Services\TelegramService();
                    foreach ($adminUsers as $adminUser) {
                            try {
                        $sent = $telegramService->sendNotification(
                            $title,
                            $message,
                            $type,
                            $data,
                            (string)$adminUser->telegram_chat_id
                        );
                        
                        \Illuminate\Support\Facades\Log::info('Telegram notification sent', [
                            'admin_id' => $adminUser->id,
                                    'admin_email' => $adminUser->email,
                            'chat_id' => $adminUser->telegram_chat_id,
                            'sent' => $sent,
                            'title' => $title,
                        ]);
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Failed to send Telegram notification to user', [
                                    'admin_id' => $adminUser->id,
                                    'chat_id' => $adminUser->telegram_chat_id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                    }
                } else {
                        // Проверяем, есть ли администраторы без telegram_chat_id
                        $allAdmins = \App\Models\User::whereHas('roles', function ($query) {
                            $query->whereIn('slug', ['admin', 'manager']);
                        })->get();
                        
                        $adminsWithoutChatId = $allAdmins->filter(function ($user) {
                            return empty($user->telegram_chat_id);
                        });
                        
                    \Illuminate\Support\Facades\Log::warning('No Telegram admins found for notification', [
                        'notification_id' => $notification->id,
                            'total_admins' => $allAdmins->count(),
                            'admins_without_chat_id' => $adminsWithoutChatId->count(),
                            'admin_emails_without_chat_id' => $adminsWithoutChatId->pluck('email')->toArray(),
                    ]);
                }
                } else {
                    \Illuminate\Support\Facades\Log::debug('Telegram notifications disabled', [
                        'is_enabled' => $telegramSettings->is_enabled,
                        'send_notifications' => $telegramSettings->send_notifications,
                    ]);
                }
            } catch (\Exception $e) {
                // Логируем ошибку, но не прерываем создание уведомления
                \Illuminate\Support\Facades\Log::error('Failed to send notification to Telegram', [
                    'error' => $e->getMessage(),
                    'notification_id' => $notification->id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Отметить уведомление как прочитанное
     *
     * @param User $user
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead(User $user, int $notificationId): bool
    {
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Получить количество непрочитанных уведомлений
     *
     * @param User|null $user
     * @return int
     */
    public function getUnreadCount(?User $user = null): int
    {
        if (!$user) {
            return 0;
        }

        return Notification::where('user_id', $user->id)
            ->where('read', false)
            ->count();
    }

    /**
     * Получить все уведомления с пагинацией и фильтрацией
     *
     * @param User|null $user
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllNotifications(?User $user = null, array $filters = [], int $perPage = 20)
    {
        if (!$user) {
            return \Illuminate\Pagination\LengthAwarePaginator::make([], 0, $perPage);
        }

        $query = Notification::where('user_id', $user->id);

        // Фильтр по статусу прочитанности
        if (isset($filters['read'])) {
            $query->where('read', $filters['read']);
        }

        // Фильтр по типу
        if (isset($filters['type']) && $filters['type']) {
            $query->where('type', $filters['type']);
        }

        // Поиск по заголовку и сообщению
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Удалить уведомление
     *
     * @param User $user
     * @param int $notificationId
     * @return bool
     */
    public function deleteNotification(User $user, int $notificationId): bool
    {
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->delete();
    }

    /**
     * Получить уведомления в формате JSON для API
     *
     * @param User|null $user
     * @param int $limit
     * @return array
     */
    public function getNotificationsJson(?User $user = null, int $limit = 10): array
    {
        return $this->getNotifications($user, $limit)->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'data' => $notification->data, // Убеждаемся, что data включена
                'read' => $notification->read,
                'read_at' => $notification->read_at ? $notification->read_at->toDateTimeString() : null,
                'created_at' => $notification->created_at->toDateTimeString(),
                'created_at_human' => $notification->created_at->diffForHumans(),
            ];
        })->toArray();
    }
}
