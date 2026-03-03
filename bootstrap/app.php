<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Добавляем middleware для рендеринга мета-тегов для ботов
        $middleware->web(append: [
            \App\Http\Middleware\RenderMetaForBots::class,
        ]);
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'deploy.token' => \App\Http\Middleware\VerifyDeployToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Отправка критических ошибок в Telegram
        $exceptions->reportable(function (\Throwable $e): void {
            // Проверяем, нужно ли отправлять ошибки
            try {
                $telegramSettings = \App\Models\TelegramSettings::getSettings();
                if ($telegramSettings->is_enabled && $telegramSettings->send_errors) {
                    // Отправляем только критические ошибки (500 и выше)
                    $shouldSend = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException 
                        ? $e->getStatusCode() >= 500 
                        : true;
                    
                    if ($shouldSend) {
                        $telegramService = new \App\Services\TelegramService();
                        
                        // Собираем контекст ошибки
                        $context = [];
                        if (request()) {
                            $context['url'] = request()->fullUrl();
                            $context['method'] = request()->method();
                            if (auth()->check()) {
                                $context['user_id'] = auth()->id();
                            }
                        }
                        
                        // Отправляем ошибку всем администраторам с telegram_chat_id
                        $adminUsers = \App\Models\User::whereNotNull('telegram_chat_id')
                            ->whereHas('roles', function ($query) {
                                $query->whereIn('slug', ['admin', 'manager']);
                            })
                            ->get();

                        if ($adminUsers->isNotEmpty()) {
                            foreach ($adminUsers as $adminUser) {
                                $telegramService->sendError($e, $context, (string)$adminUser->telegram_chat_id);
                            }
                        }
                    }
                }
            } catch (\Throwable $telegramException) {
                // Игнорируем ошибки отправки в Telegram, чтобы не создавать бесконечный цикл
            }
        });
    })->create();
