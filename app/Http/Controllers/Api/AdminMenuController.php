<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminMenu;
use Illuminate\Http\Request;

class AdminMenuController extends Controller
{
    protected $adminMenu;

    public function __construct(AdminMenu $adminMenu)
    {
        $this->adminMenu = $adminMenu;
    }

    /**
     * Получить меню для текущего пользователя
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Если пользователь не аутентифицирован, возвращаем пустое меню
        if (!$user) {
            return response()->json([
                'menu' => [],
            ]);
        }
        
        // Загружаем роли пользователя, если они еще не загружены
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }
        
        $menu = $this->adminMenu->getMenuJson($user);

        return response()->json([
            'menu' => $menu,
        ]);
    }
}
