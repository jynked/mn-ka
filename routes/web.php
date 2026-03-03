<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;

// SEO маршруты
Route::get('/robots.txt', [RobotsController::class, 'index']);
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Единая точка входа для SPA (все маршруты обрабатываются Vue Router на клиенте)
// Исключаем API маршруты, чтобы они обрабатывались routes/api.php
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
