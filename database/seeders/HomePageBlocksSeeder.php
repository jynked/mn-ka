<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomePageBlock;

class HomePageBlocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Начало создания блоков главной страницы...');

        $blocks = [
            [
                'block_key' => 'hero_banner',
                'block_name' => 'Главный баннер',
                'component_name' => 'HeroBanner',
                'order' => 0,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'block_key' => 'decisions',
                'block_name' => 'Решения',
                'component_name' => 'Decisions',
                'order' => 1,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'block_key' => 'quiz',
                'block_name' => 'Квиз',
                'component_name' => 'Quiz',
                'order' => 2,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'block_key' => 'how_work',
                'block_name' => 'Как это работает',
                'component_name' => 'HowWork',
                'order' => 3,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'block_key' => 'faq',
                'block_name' => 'FAQ',
                'component_name' => 'Faq',
                'order' => 4,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'block_key' => 'why_choose_us',
                'block_name' => 'Почему выбирают нас',
                'component_name' => 'WhyChooseUs',
                'order' => 5,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'block_key' => 'cases_block',
                'block_name' => 'Кейсы и объекты',
                'component_name' => 'CasesBlock',
                'order' => 6,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'block_key' => 'feedback_form',
                'block_name' => 'Форма обратной связи',
                'component_name' => 'FeedbackForm',
                'order' => 7,
                'is_active' => true,
                'settings' => [
                    'title' => 'Остались вопросы?',
                    'description' => 'Напишите нам, и мы с удовольствием ответим на все ваши вопросы',
                ],
            ],
        ];

        foreach ($blocks as $blockData) {
            HomePageBlock::updateOrCreate(
                ['block_key' => $blockData['block_key']],
                $blockData
            );
        }

        $this->command->info('✅ Блоки главной страницы успешно созданы/обновлены');
        $this->command->info('✅ Всего блоков: ' . count($blocks));
    }
}
