<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizBlockSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем квиз
        $quiz = Quiz::firstOrCreate(
            ['title' => 'Подберите идеальное решение'],
            [
                'description' => 'Ответьте на несколько вопросов, и мы предложим оптимальный вариант',
                'is_active' => true,
                'order' => 0,
            ]
        );

        // Удаляем старые вопросы, если они есть
        $quiz->questions()->delete();

        // Создаем вопросы
        $questions = [
            // Вопрос 1: Выбор из изображений
            [
                'order' => 0,
                'question_type' => 'images-collect',
                'question_text' => 'Что Вас интересует?',
                'question_data' => [
                    'selects' => [
                        [
                            'id' => 1,
                            'name' => '',
                            'src' => '/upload/quiz/interest-1.jpg',
                            'title' => 'Купить/продать',
                            'child' => null, // Переход к следующему вопросу по порядку
                        ],
                        [
                            'id' => 2,
                            'name' => '',
                            'src' => '/upload/quiz/interest-2.jpg',
                            'title' => 'Аренда земли',
                            'child' => null, // Переход к следующему вопросу по порядку
                        ],
                        [
                            'id' => 3,
                            'name' => '',
                            'src' => '/upload/quiz/interest-3.jpg',
                            'title' => 'Юридические аспекты',
                            'child' => null, // Переход к следующему вопросу по порядку
                        ],
                        [
                            'id' => 4,
                            'name' => '',
                            'src' => '/upload/quiz/interest-4.jpg',
                            'title' => 'Консультация',
                            'child' => null, // Переход к следующему вопросу по порядку
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            // Вопрос 2: Второй выбор из изображений (может быть условным)
            [
                'order' => 1,
                'question_type' => 'images-collect',
                'question_text' => 'Что Вас интересует?',
                'question_data' => [
                    'selects' => [
                        [
                            'id' => 1,
                            'name' => '',
                            'src' => '/upload/quiz/interest-1.jpg',
                            'title' => 'Купить/продать',
                            'child' => null, // Переход к следующему вопросу по порядку
                        ],
                        [
                            'id' => 2,
                            'name' => '',
                            'src' => '/upload/quiz/interest-2.jpg',
                            'title' => 'Аренда земли',
                            'child' => null, // Переход к следующему вопросу по порядку
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            // Вопрос 3: Выбор из списка
            [
                'order' => 2,
                'question_type' => 'selects',
                'question_text' => 'Какой тип проекта вы планируете?',
                'question_data' => [
                    'selects' => [
                        [
                            'id' => 1,
                            'name' => 'Склад',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Ритейл',
                        ],
                        [
                            'id' => 3,
                            'name' => 'Рекреация',
                        ],
                    ],
                ],
                'is_active' => true,
            ],
            // Вопрос 4: Ввод текста
            [
                'order' => 3,
                'question_type' => 'inputs',
                'question_text' => 'Площадь участка',
                'question_data' => [
                    'label' => 'Площадь участка',
                    'placeholder' => 'Введите площадь вашего участка',
                    'child' => null, // Переход к следующему вопросу по порядку
                ],
                'is_active' => true,
            ],
            // Вопрос 5: Форма контактов
            [
                'order' => 4,
                'question_type' => 'forms',
                'question_text' => 'Готово! Мы рассчитаем для вас оптимальный формат проекта <br>Наши специалисты свяжутся с вами в течение 15 минут:',
                'question_data' => [
                    'form' => [
                        'name' => '',
                        'email' => '',
                        'phone' => '',
                        'check' => false,
                    ],
                    'child' => null, // Переход к следующему вопросу по порядку
                ],
                'is_active' => true,
            ],
            // Вопрос 6: Страница благодарности
            [
                'order' => 5,
                'question_type' => 'thank',
                'question_text' => null,
                'question_data' => [],
                'is_active' => true,
            ],
        ];

        // Создаем вопросы
        foreach ($questions as $questionData) {
            $quiz->questions()->create($questionData);
        }

        // Подключаем квиз в настройках блока
        $settings = QuizBlockSettings::getSettings();
        $settings->update([
            'quiz_id' => $quiz->id,
            'is_active' => true,
        ]);

        $this->command->info("✅ Квиз '{$quiz->title}' успешно создан с " . count($questions) . " вопросами и подключен к блоку на главной странице!");
    }
}
