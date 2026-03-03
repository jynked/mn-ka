<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProductRequest;
use App\Models\User;
use App\Models\EmailSettings;
use App\Mail\FeedbackMail;
use App\Services\NotificationTool;
use App\Services\TelegramService;
use App\Services\SmtpConfigService;
use App\Helpers\EmailHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    /**
     * Отправить форму обратной связи
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Создаем заявку в product_requests (для отображения в админке /admin/product-requests)
            $productRequest = ProductRequest::create([
                'product_id' => null, // Общая заявка обратной связи, не привязана к продукту
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'comment' => $request->message, // message сохраняем в comment
                'status' => ProductRequest::STATUS_NEW,
            ]);

            // Также создаем запись в feedback_requests для обратной совместимости (если нужно)
            // Но основная заявка теперь в product_requests

            // Получаем всех администраторов и менеджеров для отправки уведомлений
            $adminUsers = User::whereHas('roles', function ($query) {
                $query->whereIn('slug', ['admin', 'manager']);
            })->get();

            // Формируем сообщение для уведомления
            $notificationTitle = "Новая обратная связь от: {$request->name}";
            $notificationMessage = "Имя: {$request->name}\n";
            if ($request->phone) {
                $notificationMessage .= "Телефон: {$request->phone}\n";
            }
            if ($request->email) {
                $notificationMessage .= "Email: {$request->email}\n";
            }
            $notificationMessage .= "\nСообщение:\n{$request->message}";

            // Создаем уведомления для всех администраторов и менеджеров
            $notificationTool = new NotificationTool();
            foreach ($adminUsers as $adminUser) {
                $notificationTool->addNotification(
                    $adminUser,
                    $notificationTitle,
                    $notificationMessage,
                    'info',
                    [
                        'request_id' => $productRequest->id,
                        'type' => 'feedback',
                        'contact' => [
                            'name' => $request->name,
                            'phone' => $request->phone,
                            'email' => $request->email,
                        ],
                    ],
                    true // Отправлять в Telegram
                );
            }

            // Отправляем email на адрес из настроек
            try {
                // Применяем SMTP настройки из базы данных
                SmtpConfigService::applySettings();

                $emailSettings = EmailSettings::getSettings();
                $recipientEmail = $emailSettings->recipient_email;

                if ($recipientEmail && EmailHelper::isValidForSending($recipientEmail)) {
                    Mail::to($recipientEmail)->send(new FeedbackMail($productRequest));
                    Log::info('Feedback email sent', [
                        'sent_to' => $recipientEmail,
                    ]);
                } else {
                    Log::warning('Invalid or missing recipient email in email settings', [
                        'recipient_email' => $recipientEmail,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error sending feedback email: ' . $e->getMessage());
                // Не прерываем выполнение, если email не отправился
            }

            // Отправляем в Telegram бот через NotificationTool (он уже отправляет в Telegram)
            // Дополнительная отправка напрямую в Telegram для всех администраторов
            try {
                $telegramService = new TelegramService();
                $telegramMessage = "📝 <b>Новая обратная связь</b>\n\n";
                $telegramMessage .= "👤 <b>Имя:</b> {$request->name}\n";
                if ($request->phone) {
                    $telegramMessage .= "📞 <b>Телефон:</b> {$request->phone}\n";
                }
                if ($request->email) {
                    $telegramMessage .= "📧 <b>Email:</b> {$request->email}\n";
                }
                $telegramMessage .= "\n💬 <b>Сообщение:</b>\n{$request->message}";
                $telegramMessage .= "\n\n🔗 <a href=\"" . url('/admin/product-requests') . "\">Просмотреть в админке</a>";

                // Отправляем всем администраторам с telegram_chat_id
                $telegramAdmins = User::whereNotNull('telegram_chat_id')
                    ->whereHas('roles', function ($query) {
                        $query->whereIn('slug', ['admin', 'manager']);
                    })
                    ->get();

                foreach ($telegramAdmins as $admin) {
                    $telegramService->sendMessage($telegramMessage, $admin->telegram_chat_id, ['parse_mode' => 'HTML']);
                }
            } catch (\Exception $e) {
                Log::error('Error sending feedback to Telegram: ' . $e->getMessage());
                // Не прерываем выполнение, если Telegram не отправился
            }

            try {
                $bitrixUrl = 'https://mnka.bitrix24.ru/bitrix/services/main/ajax.php?action=crm.site.form.fill';

                $values = [
                    'CONTACT_NAME' => [$request->name],
                ];
                if (!empty($request->phone)) {
                    $values['CONTACT_PHONE'] = [$request->phone];
                }
                if (!empty($request->email)) {
                    $values['CONTACT_EMAIL'] = [$request->email];
                }
                if (!empty($request->message)) {
                    $values['LEAD_COMMENTS'] = [$request->message];
                }

                $bitrixData = [
                    'id' => 15,
                    'sec' => 'k1jace',
                    'lang' => 'ru',
                    'timeZoneOffset' => -180,
                    'values' => json_encode($values, JSON_UNESCAPED_UNICODE),
                    'consents' => $request->consent ? json_encode(['AGREEMENT_1' => 'Y'], JSON_UNESCAPED_UNICODE) : '[]',
                    'entities' => '[]',
                    'properties' => '{}',
                    'recaptcha' => '',
                    'yandexSmartCaptcha' => '',
                    'security_sign' => '',
                ];

                $trace = [
                    'url' => 'https://b24-2bo7tt.bitrix24site.ru/crm_form_eqpkg/',
                    'device' => ['isMobile' => false],
                    'tags' => [
                        'ts' => time(),
                        'list' => [],
                        'gclid' => null,
                    ],
                    'client' => [],
                    'pages' => [
                        'list' => [
                            ['https://b24-2bo7tt.bitrix24site.ru/crm_form_eqpkg/', time(), 'Обратная связь с сайта']
                        ]
                    ],
                    'gid' => uniqid('', true),
                    'previous' => ['list' => []],
                ];
                $bitrixData['trace'] = json_encode($trace, JSON_UNESCAPED_UNICODE);

                $response = Http::timeout(5)
                    ->withOptions(['verify' => false]) // SSL
                    ->asMultipart()
                    ->post($bitrixUrl, $bitrixData);

                if ($response->successful()) {
                    Log::info('Bitrix24: feedback sent successfully', [
                        'request_id' => $productRequest->id,
                        'response'   => $response->body(),
                    ]);
                } else {
                    Log::warning('Bitrix24: feedback sending failed', [
                        'request_id' => $productRequest->id,
                        'status'     => $response->status(),
                        'response'   => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Bitrix24: exception while sending feedback', [
                    'request_id' => $productRequest->id,
                    'error'      => $e->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Спасибо за ваше обращение! Мы свяжемся с вами в ближайшее время.',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting feedback: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Ошибка при отправке формы',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }
}
