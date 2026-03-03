<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $headerMenus = [
            [
                'title' => 'Продуктовые направления',
                'slug' => '/products',
                'type' => 'header',
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Кейсы',
                'slug' => '/cases',
                'type' => 'header',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'О нас',
                'slug' => '/about',
                'type' => 'header',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Контакты',
                'slug' => '/contact',
                'type' => 'header',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($headerMenus as $menuData) {
            Menu::firstOrCreate(
                [
                    'slug' => $menuData['slug'],
                    'type' => $menuData['type'],
                ],
                $menuData
            );
        }

        $this->command->info('Header меню успешно создано!');

        // Элементы меню футера
        $footerMenus = [
            [
                'title' => 'Коробочное решение',
                'slug' => '/products',
                'type' => 'footer',
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Кузница земли',
                'slug' => '/services',
                'type' => 'footer',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Кейсы',
                'slug' => '/cases',
                'type' => 'footer',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Контакты',
                'slug' => '/contacts',
                'type' => 'footer',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Продуктовые направления',
                'slug' => '/products',
                'type' => 'footer',
                'order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($footerMenus as $menuData) {
            Menu::firstOrCreate(
                [
                    'slug' => $menuData['slug'],
                    'type' => $menuData['type'],
                ],
                $menuData
            );
        }

        $this->command->info('Footer меню успешно создано!');
    }
}
