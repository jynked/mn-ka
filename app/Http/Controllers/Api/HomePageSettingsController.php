<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomePageSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomePageSettingsController extends Controller
{
    /**
     * Получить настройки главной страницы (единственная запись)
     */
    public function show()
    {
        $settings = HomePageSettings::getSettings();
        
        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Обновить настройки главной страницы
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'hero_button_text' => 'nullable|string|max:255',
            'hero_button_link' => 'nullable|string|max:500',
            'select_title' => 'nullable|string|max:255',
            'select_subtitle' => 'nullable|string|max:1000',
            'work_title' => 'nullable|string|max:255',
            'work_items' => 'nullable|array',
            'work_items.*.title' => 'required_with:work_items|string|max:255',
            'work_items.*.text' => 'required_with:work_items|string|max:1000',
            'work_button_text' => 'nullable|string|max:255',
            'work_button_link' => 'nullable|string|max:500',
            'faq_title' => 'nullable|string|max:255',
            'faq_items' => 'nullable|array',
            'faq_items.*.question' => 'required_with:faq_items|string|max:500',
            'faq_items.*.answer' => 'required_with:faq_items|string|max:2000',
            'benefits_title' => 'nullable|string|max:255',
            'benefits_items' => 'nullable|array',
            'benefits_items.*.title' => 'required_with:benefits_items|string|max:255',
            'benefits_items.*.short_text' => 'required_with:benefits_items|string|max:500',
            'benefits_items.*.icon' => 'nullable|string|max:255',
            'contact_title' => 'nullable|string|max:255',
            'contact_subtitle' => 'nullable|string|max:1000',
            'contact_form_hint_text' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = HomePageSettings::getSettings();
        $settings->update($request->only([
            'hero_title',
            'hero_subtitle',
            'hero_button_text',
            'hero_button_link',
            'select_title',
            'select_subtitle',
            'work_title',
            'work_items',
            'work_button_text',
            'work_button_link',
            'faq_title',
            'faq_items',
            'benefits_title',
            'benefits_items',
            'contact_title',
            'contact_subtitle',
            'contact_form_hint_text',
        ]));

        return response()->json([
            'message' => 'Настройки главной страницы успешно обновлены',
            'data' => $settings,
        ]);
    }
}
