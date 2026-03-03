<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FooterSettingsResource;
use App\Models\FooterSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FooterSettingsController extends Controller
{
    /**
     * Получить настройки футера
     */
    public function show()
    {
        try {
            $settings = FooterSettings::getSettings()->load([
                'vkIcon',
                'instagramIcon',
                'telegramIcon',
            ]);
            
            return response()->json([
                'data' => new FooterSettingsResource($settings),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in FooterSettingsController::show: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'Ошибка при загрузке настроек футера',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Обновить настройки футера
     */
    public function update(Request $request)
    {
        $settings = FooterSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'department_label' => 'nullable|string|max:255',
            'department_phone' => 'nullable|string|max:255',
            'objects_label' => 'nullable|string|max:255',
            'objects_phone' => 'nullable|string|max:255',
            'issues_label' => 'nullable|string|max:255',
            'issues_email' => 'nullable|email|max:255',
            'social_networks' => 'nullable|array',
            'social_networks.vk' => 'nullable|url|max:500',
            'social_networks.instagram' => 'nullable|url|max:500',
            'social_networks.telegram' => 'nullable|url|max:500',
            'vk_icon_id' => 'nullable|exists:media,id',
            'vk_icon_svg' => 'nullable|string',
            'instagram_icon_id' => 'nullable|exists:media,id',
            'instagram_icon_svg' => 'nullable|string',
            'telegram_icon_id' => 'nullable|exists:media,id',
            'telegram_icon_svg' => 'nullable|string',
            'privacy_policy_link' => 'nullable|string|max:500',
            'copyright' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings->update($request->only([
            'title',
            'department_label',
            'department_phone',
            'objects_label',
            'objects_phone',
            'issues_label',
            'issues_email',
            'social_networks',
            'vk_icon_id',
            'vk_icon_svg',
            'instagram_icon_id',
            'instagram_icon_svg',
            'telegram_icon_id',
            'telegram_icon_svg',
            'privacy_policy_link',
            'copyright',
        ]));

        return response()->json([
            'message' => 'Настройки футера успешно обновлены',
            'data' => $settings->fresh(),
        ]);
    }
}

