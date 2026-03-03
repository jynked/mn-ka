<?php

namespace App\Services;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $bot = null;
    protected $chatId = null;
    protected $token = null;

    /**
     * Инициализация бота
     */
    public function __construct()
    {
        $this->loadBot();
    }

    /**
     * Загрузить настройки бота из базы данных
     */
    protected function loadBot()
    {
        try {
            $telegramSettings = \App\Models\TelegramSettings::getSettings();
            if ($telegramSettings && $telegramSettings->is_enabled) {
                $this->token = $telegramSettings->bot_token;
                $this->chatId = $telegramSettings->chat_id;
                
                // Загружаем или создаем бота в Telegraph
                if ($this->token) {
                    try {
                        $this->bot = TelegraphBot::firstOrCreate(
                            ['token' => $this->token],
                            ['name' => $telegramSettings->bot_name ?? 'Admin Bot']
                        );
                    } catch (\Exception $e) {
                        // Игнорируем ошибки создания бота в Telegraph
                        Log::debug('Telegraph bot creation skipped', ['error' => $e->getMessage()]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Telegram bot initialization failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Отправить сообщение в Telegram
     * 
     * @param string $message
     * @param string|null $chatId
     * @param array $options
     * @param string|null $token Переопределить токен для тестов
     * @return bool
     */
    public function sendMessage(string $message, ?string $chatId = null, array $options = [], ?string $token = null): bool
    {
        $useToken = $token ?? $this->token;
        $targetChatId = $chatId ?? $this->chatId;
        
        if (!$useToken || !$targetChatId) {
            Log::warning('Telegram token or chat ID not configured', [
                'has_token' => !empty($useToken),
                'has_chat_id' => !empty($targetChatId),
            ]);
            return false;
        }

        try {
            // Используем прямой API запрос для большей гибкости
            $url = "https://api.telegram.org/bot{$useToken}/sendMessage";
            
            $payload = [
                'chat_id' => $targetChatId,
                'text' => $message,
            ];

            // Добавляем parse_mode, если указан
            if (isset($options['parse_mode'])) {
                $payload['parse_mode'] = $options['parse_mode'];
            } else {
                $payload['parse_mode'] = 'HTML';
            }

            // Добавляем остальные опции
            $allowedOptions = [
                'disable_notification',
                'reply_to_message_id',
                'disable_web_page_preview',
                'reply_markup',
            ];
            
            foreach ($allowedOptions as $option) {
                if (isset($options[$option])) {
                    $payload[$option] = is_array($options[$option]) 
                        ? json_encode($options[$option]) 
                        : $options[$option];
                }
            }

            $response = Http::timeout(10)->post($url, $payload);

            if ($response->successful()) {
                return true;
            } else {
                Log::error('Telegram API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Отправить уведомление в Telegram
     * 
     * @param string $title
     * @param string $message
     * @param string $type
     * @param array|null $data
     * @param string|null $chatId Переопределить chat_id
     * @return bool
     */
    public function sendNotification(string $title, string $message, string $type = 'info', ?array $data = null, ?string $chatId = null): bool
    {
        $emoji = match($type) {
            'success' => '✅',
            'error' => '❌',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            default => '📢',
        };

        $text = "{$emoji} <b>{$title}</b>\n\n";
        $text .= $message;

        // Добавляем дополнительные данные, если есть
        if ($data) {
            if (isset($data['quiz_id'])) {
                $text .= "\n\n📋 <b>Квиз:</b> " . ($data['quiz_title'] ?? 'Неизвестно');
            }
            if (isset($data['contact'])) {
                $text .= "\n👤 <b>Пользователь:</b> " . ($data['contact']['name'] ?? 'Неизвестно');
                $text .= "\n📧 <b>Email:</b> " . ($data['contact']['email'] ?? 'Неизвестно');
            }
            if (isset($data['type']) && $data['type'] === 'product_request') {
                $text .= "\n\n📦 <b>Продукт:</b> " . ($data['product_name'] ?? 'Неизвестно');
                if (isset($data['request_id'])) {
                    $text .= "\n🆔 <b>ID заявки:</b> #" . $data['request_id'];
                }
            }
        }

        return $this->sendMessage($text, $chatId, ['parse_mode' => 'HTML']);
    }

    /**
     * Отправить критическую ошибку в Telegram
     * 
     * @param \Exception $exception
     * @param array|null $context
     * @param string|null $chatId Переопределить chat_id
     * @return bool
     */
    public function sendError(\Exception $exception, ?array $context = null, ?string $chatId = null): bool
    {
        $useChatId = $chatId ?? $this->chatId;
        
        if (!$this->token || !$useChatId) {
            return false;
        }

        $message = "🚨 <b>Критическая ошибка сервера</b>\n\n";
        $message .= "❌ <b>Ошибка:</b> " . $exception->getMessage() . "\n";
        $message .= "📍 <b>Файл:</b> " . basename($exception->getFile()) . ":" . $exception->getLine() . "\n";

        if ($context) {
            if (isset($context['url'])) {
                $message .= "🔗 <b>URL:</b> " . $context['url'] . "\n";
            }
            if (isset($context['method'])) {
                $message .= "🔧 <b>Метод:</b> " . $context['method'] . "\n";
            }
            if (isset($context['user_id'])) {
                $message .= "👤 <b>Пользователь ID:</b> " . $context['user_id'] . "\n";
            }
        }

        $message .= "\n⏰ <b>Время:</b> " . now()->format('d.m.Y H:i:s');

        return $this->sendMessage($message, $useChatId, ['parse_mode' => 'HTML']);
    }

    /**
     * Проверить, включен ли бот
     */
    public function isEnabled(): bool
    {
        return !empty($this->token) && !empty($this->chatId);
    }

    /**
     * Получить информацию о боте
     * 
     * @param string|null $token Переопределить токен для тестов
     * @return array|null
     */
    public function getBotInfo(?string $token = null): ?array
    {
        $useToken = $token ?? $this->token;
        
        if (!$useToken) {
            return null;
        }

        try {
            $url = "https://api.telegram.org/bot{$useToken}/getMe";
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                return $response->json('result');
            }
        } catch (\Exception $e) {
            Log::error('Failed to get bot info', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Отправить файл (фото, документ и т.д.)
     * 
     * @param string $filePath
     * @param string $type (photo, document, video, audio)
     * @param string|null $caption
     * @return bool
     */
    public function sendFile(string $filePath, string $type = 'document', ?string $caption = null): bool
    {
        if (!$this->isEnabled() || !file_exists($filePath)) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->token}/send" . ucfirst($type);
            
            $response = Http::timeout(30)
                ->attach($type, file_get_contents($filePath), basename($filePath))
                ->post($url, [
                    'chat_id' => $this->chatId,
                    'caption' => $caption,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send file to Telegram', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Отправить локацию
     * 
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public function sendLocation(float $latitude, float $longitude): bool
    {
        return $this->sendMessage('', null, [
            'method' => 'sendLocation',
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /**
     * Отправить контакт
     * 
     * @param string $phoneNumber
     * @param string $firstName
     * @param string|null $lastName
     * @return bool
     */
    public function sendContact(string $phoneNumber, string $firstName, ?string $lastName = null): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->token}/sendContact";
            
            $response = Http::timeout(10)->post($url, [
                'chat_id' => $this->chatId,
                'phone_number' => $phoneNumber,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send contact to Telegram', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Отправить опрос (poll)
     * 
     * @param string $question
     * @param array $options
     * @param bool $isAnonymous
     * @return bool
     */
    public function sendPoll(string $question, array $options, bool $isAnonymous = true): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->token}/sendPoll";
            
            $response = Http::timeout(10)->post($url, [
                'chat_id' => $this->chatId,
                'question' => $question,
                'options' => json_encode($options),
                'is_anonymous' => $isAnonymous,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send poll to Telegram', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Редактировать сообщение
     * 
     * @param int $messageId
     * @param string $newText
     * @return bool
     */
    public function editMessage(int $messageId, string $newText): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->token}/editMessageText";
            
            $response = Http::timeout(10)->post($url, [
                'chat_id' => $this->chatId,
                'message_id' => $messageId,
                'text' => $newText,
                'parse_mode' => 'HTML',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to edit message in Telegram', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Удалить сообщение
     * 
     * @param int $messageId
     * @return bool
     */
    public function deleteMessage(int $messageId): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->token}/deleteMessage";
            
            $response = Http::timeout(10)->post($url, [
                'chat_id' => $this->chatId,
                'message_id' => $messageId,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to delete message in Telegram', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Получить обновления бота (webhook updates)
     * 
     * @param int|null $offset
     * @param int $limit
     * @return array|null
     */
    public function getUpdates(?int $offset = null, int $limit = 100): ?array
    {
        if (!$this->token) {
            return null;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->token}/getUpdates";
            $params = ['limit' => $limit];
            if ($offset) {
                $params['offset'] = $offset;
            }
            
            $response = Http::timeout(10)->get($url, $params);

            if ($response->successful()) {
                return $response->json('result');
            }
        } catch (\Exception $e) {
            Log::error('Failed to get updates from Telegram', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Установить webhook
     * 
     * @param string $url
     * @param array $options
     * @param string|null $token Переопределить токен
     * @return bool
     */
    public function setWebhook(string $url, array $options = [], ?string $token = null): bool
    {
        $useToken = $token ?? $this->token;
        
        if (!$useToken) {
            return false;
        }

        try {
            $apiUrl = "https://api.telegram.org/bot{$useToken}/setWebhook";
            
            $payload = array_merge([
                'url' => $url,
            ], $options);
            
            $response = Http::timeout(10)->post($apiUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['ok']) && $result['ok']) {
                    return true;
                }
            }
            
            Log::warning('Webhook registration failed', [
                'response' => $response->body(),
            ]);
            
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to set webhook', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Удалить webhook
     * 
     * @return bool
     */
    public function deleteWebhook(): bool
    {
        if (!$this->token) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->token}/deleteWebhook";
            $response = Http::timeout(10)->post($url);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to delete webhook', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Получить информацию о webhook
     * 
     * @param string|null $token Переопределить токен
     * @return array|null
     */
    public function getWebhookInfo(?string $token = null): ?array
    {
        $useToken = $token ?? $this->token;
        
        if (!$useToken) {
            return null;
        }

        try {
            $url = "https://api.telegram.org/bot{$useToken}/getWebhookInfo";
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                return $response->json('result');
            }
        } catch (\Exception $e) {
            Log::error('Failed to get webhook info', ['error' => $e->getMessage()]);
        }

        return null;
    }
}

