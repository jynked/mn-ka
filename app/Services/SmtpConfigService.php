<?php

namespace App\Services;

use App\Models\SmtpSettings;
use Illuminate\Support\Facades\Log;

class SmtpConfigService
{
    /**
     * Применить настройки SMTP из базы данных
     */
    public static function applySettings(): void
    {
        try {
            $settings = SmtpSettings::getSettings();
            
            if ($settings->is_active) {
                $settings->applyToConfig();
            }
        } catch (\Exception $e) {
            Log::error('Error applying SMTP settings: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Не прерываем выполнение, используем настройки из .env
        }
    }
}
