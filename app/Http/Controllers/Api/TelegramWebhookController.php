<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramAdminRequest;
use App\Models\TelegramSettings;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Обработка webhook от Telegram
     */
    public function handle(Request $request)
    {
        try {
            $update = $request->all();
            Log::debug('Telegram webhook received', ['update' => $update]);

            // Проверяем, что это сообщение
            if (!isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $userId = $message['from']['id'];
            $text = $message['text'] ?? '';

            // Обрабатываем команду /admin
            if (preg_match('/^\/admin(@\w+)?$/', $text)) {
                $this->handleAdminCommand($message);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Error processing Telegram webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Обработка команды /admin
     */
    protected function handleAdminCommand(array $message)
    {
        $from = $message['from'];
        $chat = $message['chat'];
        
        $telegramUserId = $from['id'];
        $chatId = $chat['id'];
        $username = $from['username'] ?? null;
        $firstName = $from['first_name'] ?? null;
        $lastName = $from['last_name'] ?? null;

        // Проверяем, есть ли уже заявка от этого пользователя
        $existingRequest = TelegramAdminRequest::where('telegram_user_id', $telegramUserId)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            // Отправляем сообщение, что заявка уже подана
            $this->sendMessage($chatId, "⏳ Ваша заявка на администрирование уже находится на рассмотрении.");
            return;
        }

        // Проверяем, не является ли пользователь уже администратором
        $adminUsers = User::where('telegram_chat_id', $chatId)->get();
        
        foreach ($adminUsers as $user) {
            if ($user->hasAnyRole(['admin', 'manager'])) {
                $this->sendMessage($chatId, "✅ Вы уже являетесь администратором системы.");
                return;
            }
        }

        // Создаем новую заявку
        $request = TelegramAdminRequest::create([
            'telegram_user_id' => $telegramUserId,
            'telegram_username' => $username,
            'telegram_first_name' => $firstName,
            'telegram_last_name' => $lastName,
            'chat_id' => $chatId,
            'status' => 'pending',
        ]);

        // Отправляем подтверждение пользователю
        $this->sendMessage($chatId, "✅ Заявка на администрирование успешно отправлена! Ожидайте подтверждения.");

        // Отправляем уведомление администраторам в системе
        $this->notifyAdminsAboutRequest($request);

        Log::info('New admin request created', [
            'request_id' => $request->id,
            'telegram_user_id' => $telegramUserId,
            'chat_id' => $chatId,
        ]);
    }

    /**
     * Отправить сообщение в Telegram
     */
    protected function sendMessage(int $chatId, string $text): void
    {
        try {
            $settings = TelegramSettings::getSettings();
            if ($settings->bot_token) {
                $this->telegramService->sendMessage($text, (string)$chatId, ['parse_mode' => 'HTML'], $settings->bot_token);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send message to Telegram', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Уведомить администраторов о новой заявке
     */
    protected function notifyAdminsAboutRequest(TelegramAdminRequest $request): void
    {
        try {
            $adminUsers = User::whereHas('roles', function ($query) {
                $query->whereIn('slug', ['admin']);
            })->get();

            foreach ($adminUsers as $admin) {
                $notificationTool = new \App\Services\NotificationTool();
                $notificationTool->addNotification(
                    $admin,
                    'Новая заявка на администрирование Telegram',
                    "Пользователь {$request->full_name} (@{$request->telegram_username}) подал заявку на администрирование через Telegram бота.",
                    'info',
                    [
                        'telegram_request_id' => $request->id,
                        'telegram_user_id' => $request->telegram_user_id,
                        'chat_id' => $request->chat_id,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about request', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
