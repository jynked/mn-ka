<?php

namespace Database\Seeders;

use App\Models\AppCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Физическое лицо',
            'Юридическое лицо',
        ];

        foreach ($categories as $categoryName) {
            AppCategory::firstOrCreate(
                ['name' => $categoryName]
            );
        }

        $this->command->info('✓ Категории заявителя созданы: ' . implode(', ', $categories));
    }
}
