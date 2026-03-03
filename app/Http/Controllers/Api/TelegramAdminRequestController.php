<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramAdminRequest;
use App\Models\User;
use App\Models\Role;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramAdminRequestController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Получить список заявок
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        $query = TelegramAdminRequest::with(['approver', 'user'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20);

        // Laravel paginate возвращает данные напрямую, не обернутые в data
        return response()->json($requests);
    }

    /**
     * Одобрить заявку
     */
    public function approve(Request $request, int $id)
    {
        $adminRequest = TelegramAdminRequest::findOrFail($id);

        if ($adminRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Заявка уже обработана',
            ], 400);
        }

        try {
            \DB::beginTransaction();

            // Находим или создаем пользователя
            $user = $this->findOrCreateUser($adminRequest);

            // Назначаем роль администратора
            $adminRole = Role::where('slug', 'admin')->first();
            if ($adminRole && !$user->hasRole('admin')) {
                $user->roles()->syncWithoutDetaching([$adminRole->id]);
            }

            // Сохраняем chat_id в профиле пользователя
            $user->telegram_chat_id = $adminRequest->chat_id;
            $user->save();

            // Обновляем заявку
            $adminRequest->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'user_id' => $user->id,
            ]);

            // Отправляем уведомление в Telegram
            $this->sendApprovalMessage($adminRequest);

            \DB::commit();

            return response()->json([
                'message' => 'Заявка успешно одобрена',
                'data' => $adminRequest->fresh(['approver', 'user']),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error approving admin request', [
                'error' => $e->getMessage(),
                'request_id' => $id,
            ]);

            return response()->json([
                'message' => 'Ошибка при одобрении заявки: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Отклонить заявку
     */
    public function reject(Request $request, int $id)
    {
        $adminRequest = TelegramAdminRequest::findOrFail($id);

        if ($adminRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Заявка уже обработана',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $adminRequest->update([
                'status' => 'rejected',
                'approved_by' => $request->user()->id,
                'rejection_reason' => $validated['reason'] ?? null,
            ]);

            // Отправляем уведомление в Telegram
            $this->sendRejectionMessage($adminRequest, $validated['reason'] ?? null);

            return response()->json([
                'message' => 'Заявка отклонена',
                'data' => $adminRequest->fresh(['approver']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting admin request', [
                'error' => $e->getMessage(),
                'request_id' => $id,
            ]);

            return response()->json([
                'message' => 'Ошибка при отклонении заявки: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Найти или создать пользователя
     */
    protected function findOrCreateUser(TelegramAdminRequest $adminRequest): User
    {
        // Пытаемся найти пользователя по telegram_chat_id
        $user = User::where('telegram_chat_id', $adminRequest->chat_id)->first();

        if ($user) {
            return $user;
        }

        // Пытаемся найти по email, если есть username
        if ($adminRequest->telegram_username) {
            $email = $adminRequest->telegram_username . '@telegram.local';
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $user->telegram_chat_id = $adminRequest->chat_id;
                $user->save();
                return $user;
            }
        }

        // Создаем нового пользователя
        $name = trim(($adminRequest->telegram_first_name ?? '') . ' ' . ($adminRequest->telegram_last_name ?? ''));
        $name = $name ?: $adminRequest->telegram_username ?? 'Telegram User';
        $email = $adminRequest->telegram_username 
            ? $adminRequest->telegram_username . '@telegram.local'
            : 'telegram_' . $adminRequest->telegram_user_id . '@telegram.local';

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => \Hash::make(\Str::random(32)), // Случайный пароль
            'telegram_chat_id' => $adminRequest->chat_id,
        ]);

        return $user;
    }

    /**
     * Отправить сообщение об одобрении
     */
    protected function sendApprovalMessage(TelegramAdminRequest $adminRequest): void
    {
        try {
            $settings = \App\Models\TelegramSettings::getSettings();
            if ($settings->bot_token) {
                $message = "✅ <b>Заявка одобрена!</b>\n\n";
                $message .= "Вы получили права администратора в системе.\n";
                $message .= "Теперь вы будете получать все уведомления от сервера.";
                
                $this->telegramService->sendMessage(
                    $message,
                    (string)$adminRequest->chat_id,
                    ['parse_mode' => 'HTML'],
                    $settings->bot_token
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send approval message', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Отправить сообщение об отклонении
     */
    protected function sendRejectionMessage(TelegramAdminRequest $adminRequest, ?string $reason): void
    {
        try {
            $settings = \App\Models\TelegramSettings::getSettings();
            if ($settings->bot_token) {
                $message = "❌ <b>Заявка отклонена</b>\n\n";
                if ($reason) {
                    $message .= "Причина: {$reason}";
                } else {
                    $message .= "Ваша заявка на администрирование была отклонена.";
                }
                
                $this->telegramService->sendMessage(
                    $message,
                    (string)$adminRequest->chat_id,
                    ['parse_mode' => 'HTML'],
                    $settings->bot_token
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send rejection message', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
