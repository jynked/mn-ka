<?php

namespace Database\Seeders;

use App\Models\CasesBlockSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CasesBlockSettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CasesBlockSettings::firstOrCreate(
            [],
            [
                'title' => 'Кейсы и объекты',
                'is_active' => true,
                'case_ids' => [],
                'additional_settings' => [],
            ]
        );
    }
}
