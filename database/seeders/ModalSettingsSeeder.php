<?php

namespace Database\Seeders;

use App\Models\ModalSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModalSettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Начало создания настроек модальных окон...');

        // Настройки для модального окна продуктов
        $productsModal = ModalSettings::firstOrCreate(
            ['type' => 'products'],
            [
                'title' => 'Информация',
                'content' => 'Мы подсказываем Вам как правильно поступить в той или иной ситуации. Мы подсказываем Вам как правильно поступить в той или иной ситуации. Мы подсказываем Вам как правильно поступить в той или иной ситуации. Мы подсказываем Вам как правильно поступить в той или иной ситуации.',
                'is_active' => true,
                'additional_settings' => null,
            ]
        );

        if ($productsModal->wasRecentlyCreated) {
            $this->command->info("✓ Настройки модального окна 'products' созданы (ID: {$productsModal->id})");
        } else {
            $this->command->warn("Настройки модального окна 'products' уже существуют (ID: {$productsModal->id})");
        }

        $this->command->info('Готово! Настройки модальных окон созданы.');
    }
}
