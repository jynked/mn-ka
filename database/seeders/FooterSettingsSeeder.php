<?php

namespace Database\Seeders;

use App\Models\FooterSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FooterSettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Начало создания/обновления настроек футера...');

        $existing = FooterSettings::first();

        // Создаем или обновляем настройки
        $settings = $existing ?: new FooterSettings();
        
        if ($existing) {
            $this->command->info('ℹ️ Найдены существующие настройки. Обновляем данные...');
        }

        // Данные на основе HTML из старого футера
        $settings->title = 'Наши контакты';
        $settings->department_label = 'Отдел продаж';
        $settings->department_phone = '8 (915) 108-57-88';
        $settings->objects_label = 'Подбор объектов';
        $settings->objects_phone = '8 (926) 108-70-89';
        $settings->issues_label = 'По вопросам сотрудничества';
        $settings->issues_email = 'offerus@mnka.ru';
        
        // Социальные сети (будут заполнены позже через админку)
        $settings->social_networks = [
            'vk' => null,
            'instagram' => null,
            'telegram' => null,
        ];
        
        $settings->privacy_policy_link = '/police';
        $settings->copyright = 'MNKA 2025. Все права защищены';

        $settings->save();

        if ($existing) {
            $this->command->info('✅ Настройки футера успешно обновлены!');
        } else {
            $this->command->info('✅ Настройки футера успешно созданы!');
        }
    }
}

