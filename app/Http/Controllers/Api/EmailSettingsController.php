<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmailSettingsResource;
use App\Models\EmailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailSettingsController extends Controller
{
    /**
     * Получить настройки email
     */
    public function show()
    {
        try {
            $settings = EmailSettings::getSettings();
            
            return response()->json([
                'data' => new EmailSettingsResource($settings),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in EmailSettingsController::show: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'Ошибка при загрузке настроек email',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Обновить настройки email
     */
    public function update(Request $request)
    {
        $settings = EmailSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'recipient_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings->update($request->only([
            'recipient_email',
        ]));

        return response()->json([
            'message' => 'Настройки email успешно обновлены',
            'data' => new EmailSettingsResource($settings->fresh()),
        ]);
    }
}
