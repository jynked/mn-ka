<?php

namespace Database\Seeders;

use App\Models\ContactSettings;
use Illuminate\Database\Seeder;

class ContactSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Начало создания настроек контактов...');

        $existing = ContactSettings::first();

        if ($existing && $this->hasData($existing)) {
            $this->command->warn('Настройки контактов уже существуют и содержат данные.');
            return;
        }

        // Создаем или обновляем настройки
        $settings = $existing ?: new ContactSettings();

        // Начальные данные
        $settings->phone = '+7 (495) 123-45-67';
        $settings->email = 'info@example.com';
        $settings->address = 'г. Москва, ул. Примерная, д. 1';
        $settings->working_hours = 'Пн-Пт: 9:00 - 18:00';
        $settings->socials = [
            [
                'icon' => 'vk',
                'title' => 'ВКонтакте',
                'link' => 'https://vk.com/example',
            ],
            [
                'icon' => 'telegram',
                'title' => 'Telegram',
                'link' => 'https://t.me/example',
            ],
            [
                'icon' => 'instagram',
                'title' => 'Instagram',
                'link' => 'https://instagram.com/example',
            ],
        ];

        $settings->save();

        $this->command->info('✅ Настройки контактов успешно созданы!');
    }

    /**
     * Проверить, есть ли данные в настройках
     */
    protected function hasData(ContactSettings $settings): bool
    {
        return !empty($settings->phone) || 
               !empty($settings->email) || 
               !empty($settings->address) || 
               !empty($settings->working_hours) ||
               (!empty($settings->socials) && is_array($settings->socials) && count($settings->socials) > 0);
    }
}





