<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramSettings;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TelegramSettingsController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Получить настройки Telegram
     */
    public function show()
    {
        $settings = TelegramSettings::getSettings();
        $botInfo = null;
        
        // Пытаемся получить информацию о боте, если токен указан
        if ($settings->bot_token) {
            try {
                $telegramService = new TelegramService();
                $botInfo = $telegramService->getBotInfo($settings->bot_token);
            } catch (\Exception $e) {
                // Игнорируем ошибки получения информации о боте
            }
        }
        
        return response()->json([
            'data' => [
                'settings' => $settings,
                'bot_info' => $botInfo,
            ],
        ]);
    }

    /**
     * Обновить настройки Telegram
     */
    public function update(Request $request)
    {
        $settings = TelegramSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'bot_token' => 'nullable|string|max:255',
            'bot_name' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url|max:500',
            'is_enabled' => 'nullable|boolean',
            'send_notifications' => 'nullable|boolean',
            'send_errors' => 'nullable|boolean',
            'parse_mode' => 'nullable|in:HTML,Markdown,MarkdownV2',
            'disable_notification' => 'nullable|boolean',
            'reply_to_message_id' => 'nullable|integer',
            'disable_web_page_preview' => 'nullable|boolean',
            'additional_settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldToken = $settings->bot_token;
        $newToken = $request->input('bot_token');

        $settings->update($request->only([
            'bot_token',
            'bot_name',
            'webhook_url',
            'is_enabled',
            'send_notifications',
            'send_errors',
            'parse_mode',
            'disable_notification',
            'reply_to_message_id',
            'disable_web_page_preview',
            'additional_settings',
        ]));

        // Автоматически регистрируем webhook при сохранении токена
        if ($newToken && ($oldToken !== $newToken || !$settings->webhook_url)) {
            try {
                // Формируем URL для webhook
                $webhookUrl = $request->input('webhook_url');
                if (!$webhookUrl) {
                    $webhookUrl = url('/api/telegram/webhook');
                    $settings->webhook_url = $webhookUrl;
                    $settings->save();
                }

                // Регистрируем webhook
                $telegramService = new TelegramService();
                $success = $telegramService->setWebhook($webhookUrl, [], $newToken);

                if ($success) {
                    \Log::info('Webhook successfully registered', ['url' => $webhookUrl]);
                } else {
                    \Log::warning('Failed to register webhook', ['url' => $webhookUrl]);
                }
            } catch (\Exception $e) {
                // Логируем ошибку, но не прерываем сохранение настроек
                \Log::error('Failed to set webhook', ['error' => $e->getMessage()]);
            }
        }

        // Получаем информацию о боте после обновления
        $botInfo = null;
        if ($settings->bot_token) {
            try {
                $telegramService = new TelegramService();
                $botInfo = $telegramService->getBotInfo($settings->bot_token);
            } catch (\Exception $e) {
                // Игнорируем ошибку
            }
        }

        return response()->json([
            'message' => 'Настройки Telegram успешно обновлены',
            'data' => [
                'settings' => $settings->fresh(),
                'bot_info' => $botInfo,
            ],
        ]);
    }

    /**
     * Проверить соединение с ботом
     */
    public function testConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bot_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Получаем первого администратора с telegram_chat_id для теста
            $adminUser = User::whereNotNull('telegram_chat_id')
                ->whereHas('roles', function ($query) {
                    $query->whereIn('slug', ['admin', 'manager']);
                })
                ->first();

            if (!$adminUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет администраторов с подключенным Telegram. Используйте команду /admin в боте для подачи заявки.',
                ], 400);
            }

            $telegramService = new TelegramService();
            $testMessage = '🧪 Тестовое сообщение от ' . config('app.name') . "\n\nВремя: " . now()->format('d.m.Y H:i:s');
            
            $success = $telegramService->sendMessage($testMessage, (string)$adminUser->telegram_chat_id, [
                'parse_mode' => 'HTML',
            ], $request->bot_token);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Тестовое сообщение успешно отправлено',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось отправить тестовое сообщение',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке тестового сообщения: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить информацию о webhook
     */
    public function getWebhookInfo()
    {
        $settings = TelegramSettings::getSettings();
        
        if (!$settings->bot_token) {
            return response()->json([
                'success' => false,
                'message' => 'Токен бота не указан',
            ], 400);
        }

        try {
            $telegramService = new TelegramService();
            $webhookInfo = $telegramService->getWebhookInfo($settings->bot_token);
            
            return response()->json([
                'success' => true,
                'data' => $webhookInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения информации о webhook: ' . $e->getMessage(),
            ], 500);
        }
    }
}
