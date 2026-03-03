<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeoSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SeoSettingsController extends Controller
{
    /**
     * Получить настройки SEO
     */
    public function show()
    {
        try {
            $settings = SeoSettings::getSettings();
            
            return response()->json([
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching SEO settings', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения настроек SEO',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Обновить настройки SEO
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'site_keywords' => 'nullable|string|max:500',
            'default_og_image' => 'nullable|string|max:500',
            'og_type' => 'nullable|string|max:50',
            'og_site_name' => 'nullable|string|max:255',
            'twitter_card' => 'nullable|string|max:50',
            'twitter_site' => 'nullable|string|max:255',
            'twitter_creator' => 'nullable|string|max:255',
            'organization_name' => 'nullable|string|max:255',
            'organization_logo' => 'nullable|string|max:500',
            'organization_phone' => 'nullable|string|max:50',
            'organization_email' => 'nullable|email|max:255',
            'organization_address' => 'nullable|string|max:500',
            'allow_indexing' => 'nullable|boolean',
            'robots_txt' => 'nullable|string',
            'additional_schema' => 'nullable|array',
            'custom_js_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $settings = SeoSettings::getSettings();
            $settings->update($request->all());

            return response()->json([
                'message' => 'Настройки SEO успешно обновлены',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating SEO settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ошибка обновления настроек SEO',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }

    /**
     * Получить публичные настройки SEO (для фронтенда)
     */
    public function getPublic()
    {
        try {
            $settings = SeoSettings::getSettings();
            
            // Возвращаем только публичные данные
            return response()->json([
                'data' => [
                    'site_name' => $settings->site_name,
                    'site_description' => $settings->site_description,
                    'default_og_image' => $settings->default_og_image,
                    'og_type' => $settings->og_type,
                    'og_site_name' => $settings->og_site_name,
                    'twitter_card' => $settings->twitter_card,
                    'twitter_site' => $settings->twitter_site,
                    'organization_schema' => $settings->getOrganizationSchema(),
                    'website_schema' => $settings->getWebSiteSchema(),
                    'custom_js_code' => $settings->custom_js_code,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching public SEO settings', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ошибка получения настроек SEO',
                'error' => config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера',
            ], 500);
        }
    }
}
