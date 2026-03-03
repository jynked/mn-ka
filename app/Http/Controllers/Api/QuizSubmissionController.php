<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\User;
use App\Mail\QuizCompletionMail;
use App\Services\NotificationTool;
use App\Services\TelegramService;
use App\Helpers\EmailHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class QuizSubmissionController extends Controller
{
    protected $notificationTool;

    public function __construct(NotificationTool $notificationTool)
    {
        $this->notificationTool = $notificationTool;
    }

    /**
     * Обработать прохождение квиза
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quiz_id' => 'required|exists:quizzes,id',
            'answers' => 'required|array',
            'contact' => 'required|array',
            'contact.name' => 'required|string|max:255',
            'contact.email' => 'required|email|max:255',
            'contact.phone' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $quiz = Quiz::with('questions')->findOrFail($request->quiz_id);
            
            // Получаем или создаем пользователя по email
            $user = User::firstOrCreate(
                ['email' => $request->contact['email']],
                [
                    'name' => $request->contact['name'],
                    'password' => bcrypt(str()->random(32)), // Случайный пароль
                ]
            );

            // Обновляем имя если оно изменилось
            if ($user->name !== $request->contact['name']) {
                $user->update(['name' => $request->contact['name']]);
            }

            // Формируем данные для сохранения (с детализацией вопросов и ответов)
            $detailedAnswers = [];
            $questions = $quiz->questions->sortBy('order')->values();
            
            foreach ($request->answers as $index => $answer) {
                if (isset($questions[$index])) {
                    $question = $questions[$index];
                    
                    // Определяем текст ответа в зависимости от типа вопроса
                    $answerText = $answer;
                    $answerValue = $answer;
                    
                    if (is_array($answer)) {
                        // Если ответ - массив (объект с полной информацией)
                        $answerText = $answer['text'] ?? $answer['title'] ?? $answer['name'] ?? (string)($answer['id'] ?? '');
                        $answerValue = $answer['id'] ?? $answerText;
                    } elseif (is_object($answer)) {
                        // Если ответ - объект
                        $answerText = $answer->text ?? $answer->title ?? $answer->name ?? (string)($answer->id ?? '');
                        $answerValue = $answer->id ?? $answerText;
                    }
                    
                    // Для вопросов с вариантами (selects, images-collect) получаем полный путь
                    if (in_array($question->question_type, ['selects', 'images-collect'])) {
                        $questionData = is_array($question->question_data) ? $question->question_data : (json_decode($question->question_data, true) ?? []);
                        
                        if (isset($questionData['selects']) && is_array($questionData['selects'])) {
                            $selectedOption = null;
                            // Ищем выбранный вариант в данных вопроса
                            foreach ($questionData['selects'] as $option) {
                                if (!is_array($option)) {
                                    continue;
                                }
                                $optionId = $option['id'] ?? null;
                                if ($optionId && ($optionId == $answerValue || (is_array($answer) && ($optionId == ($answer['id'] ?? null))))) {
                                    $selectedOption = $option;
                                    break;
                                }
                            }
                            
                            if ($selectedOption && is_array($selectedOption)) {
                                $answerText = $selectedOption['title'] ?? $selectedOption['name'] ?? $answerText;
                            }
                        }
                    }
                    
                    $detailedAnswers[] = [
                        'question_id' => $question->id,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'answer' => [
                            'value' => $answerValue,
                            'text' => $answerText,
                            'full_data' => is_array($answer) ? $answer : (is_object($answer) ? (array)$answer : ['value' => $answer]),
                        ],
                    ];
                } else {
                    // Если вопрос не найден, все равно сохраняем ответ
                    $answerText = is_array($answer) ? ($answer['text'] ?? $answer['title'] ?? $answer['name'] ?? '') : (string)$answer;
                    $detailedAnswers[] = [
                        'question_id' => null,
                        'question_text' => "Вопрос " . ($index + 1),
                        'question_type' => 'unknown',
                        'answer' => [
                            'value' => is_array($answer) ? ($answer['id'] ?? $answerText) : $answer,
                            'text' => $answerText,
                            'full_data' => is_array($answer) ? $answer : ['value' => $answer],
                        ],
                    ];
                }
            }

            $quizData = [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'answers' => $detailedAnswers,
                'contact' => $request->contact,
                'completed_at' => now()->toDateTimeString(),
            ];

            // Создаем заявку в product_requests (для отображения в админке /admin/product-requests)
            $comment = "Прохождение квиза: {$quiz->title}\n\n";
            $comment .= "Ответы:\n";
            foreach ($detailedAnswers as $index => $answerData) {
                $comment .= ($index + 1) . ". {$answerData['question_text']}\n";
                $comment .= "   Ответ: {$answerData['answer']['text']}\n\n";
            }
            
            $productRequest = \App\Models\ProductRequest::create([
                'product_id' => null, // Заявка из квиза, не привязана к продукту
                'name' => $request->contact['name'],
                'phone' => $request->contact['phone'],
                'email' => $request->contact['email'],
                'comment' => $comment,
                'status' => \App\Models\ProductRequest::STATUS_NEW,
            ]);
            
            // Добавляем запись в историю
            $productRequest->addHistory(
                \App\Models\RequestHistory::ACTION_CREATED,
                null,
                'Заявка создана через прохождение квиза'
            );

            // Создаем уведомление для администраторов через NotificationTool
            $adminUsers = User::whereHas('roles', function($q) {
                $q->whereIn('slug', ['admin', 'manager']);
            })->get();

            // Добавляем ссылку на просмотр уведомления в данных
            $quizData['view_url'] = '/admin/product-requests';
            $quizData['request_id'] = $productRequest->id;
            
            $title = 'Новое прохождение квиза';
            $message = "Пользователь {$request->contact['name']} ({$request->contact['email']}) прошел квиз \"{$quiz->title}\". Перейдите в заявки для просмотра деталей.";
            
            // Создаем уведомления в БД для каждого администратора
            foreach ($adminUsers as $admin) {
                $this->notificationTool->addNotification(
                    $admin,
                    $title,
                    $message,
                    'success',
                    $quizData,
                    false // Не отправлять в Telegram (отправим один раз для всех)
                );
            }
            
            // Отправляем уведомление в Telegram один раз для всех администраторов
            try {
                $telegramSettings = \App\Models\TelegramSettings::getSettings();
                if ($telegramSettings->is_enabled && $telegramSettings->send_notifications) {
                    $telegramAdmins = User::whereNotNull('telegram_chat_id')
                        ->whereHas('roles', function ($query) {
                            $query->whereIn('slug', ['admin', 'manager']);
                        })
                        ->get();

                    if ($telegramAdmins->isNotEmpty()) {
                        $telegramService = new TelegramService();
                        foreach ($telegramAdmins as $telegramAdmin) {
                            $telegramService->sendNotification(
                                $title,
                                $message,
                                'success',
                                $quizData,
                                (string)$telegramAdmin->telegram_chat_id
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send quiz notification to Telegram', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Отправляем email с результатами
            $emailSent = false;
            $emailError = null;
            try {
                // Перезагружаем квиз с вопросами для email (важно для правильного отображения)
                $quizForEmail = Quiz::with(['questions' => function($query) {
                    $query->where('is_active', true)->orderBy('order');
                }])->findOrFail($quiz->id);

                // Формируем детальные ответы для email (соответствующие вопросам)
                // Важно: ответы должны быть в том же порядке, что и вопросы
                $emailAnswers = [];
                foreach ($detailedAnswers as $answerData) {
                    // Для email передаем текст ответа или значение
                    if (isset($answerData['answer']['text'])) {
                        $emailAnswers[] = $answerData['answer']['text'];
                    } elseif (isset($answerData['answer']['value'])) {
                        $emailAnswers[] = $answerData['answer']['value'];
                    } elseif (isset($answerData['answer']['full_data'])) {
                        $fullData = $answerData['answer']['full_data'];
                        $emailAnswers[] = is_array($fullData) ? ($fullData['text'] ?? $fullData['title'] ?? $fullData['name'] ?? $fullData) : $fullData;
                    } else {
                        $emailAnswers[] = $answerData['answer'];
                    }
                }
                
                // Логируем для отладки
                Log::debug('Данные для email', [
                    'answers_count' => count($emailAnswers),
                    'questions_count' => $quizForEmail->questions->count(),
                    'quiz_id' => $quizForEmail->id,
                ]);

                // Проверяем валидность email перед отправкой
                if (!EmailHelper::isValidForSending($request->contact['email'])) {
                    Log::warning('Попытка отправить email на невалидный адрес', [
                        'email' => $request->contact['email'],
                        'quiz_id' => $quiz->id,
                    ]);
                    $emailError = 'Невалидный email адрес';
                } else {
                // Применяем SMTP настройки из базы данных
                \App\Services\SmtpConfigService::applySettings();
                
                // Проверяем настройки почты перед отправкой
                $mailConfig = config('mail.default');
                Log::info('Попытка отправки email', [
                    'email' => $request->contact['email'],
                    'mailer' => $mailConfig,
                    'host' => config('mail.mailers.smtp.host'),
                ]);

                Mail::to($request->contact['email'])
                    ->send(new QuizCompletionMail($quizForEmail, $emailAnswers, $request->contact));
                
                $emailSent = true;
                Log::info('✅ Email с результатами квиза успешно отправлен', [
                    'email' => $request->contact['email'],
                    'quiz_id' => $quiz->id,
                ]);
                }
            } catch (\Exception $e) {
                $emailError = $e->getMessage();
                Log::error('❌ Ошибка отправки email с результатами квиза', [
                    'email' => $request->contact['email'],
                    'quiz_id' => $quiz->id,
                    'error' => $emailError,
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Не прерываем выполнение - уведомление уже создано
            }

            // Формируем ответ
            $response = [
                'success' => true,
                'message' => 'Спасибо за прохождение квиза! Результаты отправлены на вашу почту.',
            ];
            
            // Добавляем информацию об отправке email (только для логирования в dev режиме)
            if (config('app.debug')) {
                $response['email_sent'] = $emailSent;
                if ($emailError) {
                    $response['email_error'] = $emailError;
                }
            }
            
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Ошибка обработки прохождения квиза', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка обработки результатов квиза: ' . $e->getMessage(),
            ], 500);
        }
    }
}
