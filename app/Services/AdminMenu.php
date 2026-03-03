<?php

namespace App\Services;

use App\Models\User;
use App\Models\Page;
use Illuminate\Support\Collection;

class AdminMenu
{
    /**
     * Получить меню для пользователя с фильтрацией по ролям
     *
     * @param User|null $user
     * @return Collection
     */
    public function getMenu(?User $user = null): Collection
    {
        $menu = collect([
            [
                'title' => 'Документация',
                'route' => 'admin.documentation',
                'icon' => 'book',
                'roles' => ['admin', 'manager', 'user'],
            ],
            [
                'title' => 'Медиа',
                'route' => 'admin.media',
                'icon' => 'image',
                'roles' => ['admin', 'manager'],
            ],
            [
                'title' => 'Уведомления',
                'route' => 'admin.notifications',
                'icon' => 'bell',
                'roles' => ['admin', 'manager', 'user'],
            ],
            [
                'title' => 'Пользователи',
                'route' => 'admin.users',
                'icon' => 'users',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Роли',
                'route' => 'admin.roles',
                'icon' => 'shield',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Настройки',
                'icon' => 'settings',
                'roles' => ['admin', 'manager'],
                'children' => [
                    [
                        'title' => 'SEO настройки',
                        'route' => 'admin.seo-settings',
                        'icon' => 'search',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Настройки сайта',
                        'route' => 'admin.settings',
                        'icon' => 'settings',
                        'roles' => ['admin'],
                    ],
                ],
            ],
            [
                'title' => 'Меню',
                'route' => 'admin.menus',
                'icon' => 'menu',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Баннеры',
                'icon' => 'image',
                'roles' => ['admin', 'manager'],
                'children' => [
                    [
                        'title' => 'Баннер на главной',
                        'route' => 'admin.banners.home',
                        'icon' => 'home',
                        'roles' => ['admin', 'manager'],
                    ],
                ],
            ],
            [
                'title' => 'Решения',
                'icon' => 'grid',
                'roles' => ['admin', 'manager'],
                'children' => [
                    [
                        'title' => 'Разделы',
                        'route' => 'admin.decisions.chapters',
                        'icon' => 'layers',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Продукты',
                        'route' => 'admin.decisions.products',
                        'icon' => 'package',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Услуги',
                        'route' => 'admin.decisions.services',
                        'icon' => 'briefcase',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Случаи',
                        'route' => 'admin.decisions.cases',
                        'icon' => 'file-text',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Настройки блока',
                        'route' => 'admin.decisions.settings',
                        'icon' => 'settings',
                        'roles' => ['admin', 'manager'],
                    ],
                ],
            ],
            [
                'title' => 'Квизы',
                'icon' => 'help-circle',
                'roles' => ['admin', 'manager'],
                'children' => [
                    [
                        'title' => 'Список квизов',
                        'route' => 'admin.quizzes.index',
                        'icon' => 'list',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Настройки блока',
                        'route' => 'admin.quizzes.settings',
                        'icon' => 'settings',
                        'roles' => ['admin', 'manager'],
                    ],
                ],
            ],
            [
                'title' => 'Страницы',
                'icon' => 'file-text',
                'roles' => ['admin', 'manager'],
                'children' => $this->getPagesMenuItems(),
            ],
            [
                'title' => 'Блоки',
                'icon' => 'grid',
                'roles' => ['admin', 'manager'],
                'children' => [
                    [
                        'title' => 'Как это работает',
                        'route' => 'admin.blocks.how-work',
                        'icon' => 'settings',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'FAQ',
                        'route' => 'admin.blocks.faq',
                        'icon' => 'help-circle',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Почему выбирают нас',
                        'route' => 'admin.blocks.why-choose-us',
                        'icon' => 'star',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Кейсы и объекты',
                        'route' => 'admin.blocks.cases',
                        'icon' => 'briefcase',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Футер',
                        'route' => 'admin.settings.footer',
                        'icon' => 'layout',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'title' => 'Карточки кейсов',
                        'route' => 'admin.settings.case-cards',
                        'icon' => 'briefcase',
                        'roles' => ['admin', 'manager'],
                    ],
                ],
            ],
            [
                'title' => 'Кейсы',
                'route' => 'admin.cases',
                'icon' => 'briefcase',
                'roles' => ['admin', 'manager'],
            ],
            [
                'title' => 'Модальные окна',
                'route' => 'admin.modal-settings',
                'icon' => 'square',
                'roles' => ['admin', 'manager'],
            ],
            [
                'title' => 'Заявки',
                'route' => 'admin.product-requests',
                'icon' => 'file-text',
                'roles' => ['admin', 'manager'],
            ],
        ]);

        if (!$user) {
            return collect([]);
        }

        // Загружаем роли пользователя, если они еще не загружены
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        // Получаем роли пользователя
        $userRoles = $user->roles->pluck('slug')->toArray();
        
        // Если у пользователя нет ролей, возвращаем пустое меню
        if (empty($userRoles)) {
            return collect([]);
        }

        // Фильтруем меню по ролям
        return $menu->map(function ($item) use ($userRoles) {
            // Проверяем доступ к родительскому элементу
            if (!empty($item['roles']) && !$this->hasAccess($userRoles, $item['roles'])) {
                return null;
            }

            // Фильтруем дочерние элементы
            if (isset($item['children'])) {
                $item['children'] = collect($item['children'])->filter(function ($child) use ($userRoles) {
                    return empty($child['roles']) || $this->hasAccess($userRoles, $child['roles']);
                })->values()->toArray();

                // Если нет доступных дочерних элементов, скрываем родительский
                if (empty($item['children'])) {
                    return null;
                }
            }

            return $item;
        })->filter()->values();
    }

    /**
     * Проверить доступ пользователя к элементу меню
     *
     * @param array $userRoles
     * @param array $requiredRoles
     * @return bool
     */
    protected function hasAccess(array $userRoles, array $requiredRoles): bool
    {
        return !empty(array_intersect($userRoles, $requiredRoles));
    }

    /**
     * Получить пункты меню для созданных страниц
     *
     * @return array
     */
    protected function getPagesMenuItems(): array
    {
        $items = [
            [
                'title' => 'Список страниц',
                'route' => 'admin.pages',
                'icon' => 'file-text',
                'roles' => ['admin', 'manager'],
            ],
            [
                'title' => 'Главная страница',
                'route' => 'admin.pages.home',
                'icon' => 'home',
                'roles' => ['admin', 'manager'],
            ],
            [
                'title' => 'Страница "О нас"',
                'route' => 'admin.settings.about',
                'icon' => 'info',
                'roles' => ['admin', 'manager'],
            ],
        ];

        // Получаем активные страницы и добавляем их в меню
        try {
            $pages = Page::active()
                ->ordered()
                ->select('id', 'title', 'slug', 'order')
                ->get();

            foreach ($pages as $page) {
                $items[] = [
                    'title' => $page->title,
                    'route' => 'admin.pages.edit',
                    'route_params' => ['id' => $page->id],
                    'icon' => 'file-text',
                    'roles' => ['admin', 'manager'],
                ];
            }
        } catch (\Exception $e) {
            // Если произошла ошибка при получении страниц, просто возвращаем базовые пункты
            \Illuminate\Support\Facades\Log::warning('Error fetching pages for admin menu: ' . $e->getMessage());
        }

        return $items;
    }

    /**
     * Получить меню в формате JSON для API
     *
     * @param User|null $user
     * @return array
     */
    public function getMenuJson(?User $user = null): array
    {
        return $this->getMenu($user)->toArray();
    }
}
