<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminMenuController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\OptionTreeController;
use App\Http\Controllers\Api\InstanceController;
use App\Http\Controllers\Api\DecisionBlockSettingsController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizBlockSettingsController;
use App\Http\Controllers\Api\QuizSubmissionController;
use App\Http\Controllers\Api\HowWorkBlockSettingsController;
use App\Http\Controllers\Api\FaqBlockSettingsController;
use App\Http\Controllers\Api\WhyChooseUsBlockSettingsController;
use App\Http\Controllers\Api\CasesBlockSettingsController;
use App\Http\Controllers\Api\HomePageBlocksController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\SeoSettingsController;
use App\Http\Controllers\Api\ModalSettingsController;
use App\Http\Controllers\Api\ProductRequestController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\DeployController;
use App\Http\Controllers\Api\TelegramSettingsController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\Api\TelegramAdminRequestController;
use App\Http\Controllers\Api\ContactSettingsController;
use App\Http\Controllers\Api\AboutSettingsController;
use App\Http\Controllers\Api\FooterSettingsController;
use App\Http\Controllers\Api\EmailSettingsController;
use App\Http\Controllers\Api\SmtpSettingsController;
use App\Http\Controllers\Api\CaseCardSettingsController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\DecisionController;
use App\Http\Controllers\Api\HomePageSettingsController;
use App\Http\Controllers\Api\v1\FolderController;
use App\Http\Controllers\Api\v1\MediaController;
use Illuminate\Support\Facades\Route;

// Публичные роуты
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Защищённые роуты
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Меню
    Route::get('/admin/menu', [AdminMenuController::class, 'index']);
    
    // Уведомления
    // Специфичные маршруты должны быть выше маршрутов с параметрами
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/all', [NotificationController::class, 'all']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Media API (v1)
    Route::prefix('v1')->group(function () {
        // Folders
        Route::get('folders/tree/all', [FolderController::class, 'tree'])->name('folders.tree');
        Route::post('folders/update-positions', [FolderController::class, 'updatePositions'])->name('folders.update-positions');
        Route::post('folders/{id}/restore', [FolderController::class, 'restore'])->name('folders.restore');
        Route::apiResource('folders', FolderController::class);
        
        // Media
        Route::post('media/{id}/restore', [MediaController::class, 'restore'])->name('media.restore');
        Route::delete('media/trash/empty', [MediaController::class, 'emptyTrash'])->name('media.trash.empty');
        Route::apiResource('media', MediaController::class);
        
        // Admin only routes (Roles and Users management)
        Route::middleware('admin')->group(function () {
            Route::apiResource('roles', RoleController::class);
            Route::apiResource('users', UserController::class);
            Route::post('menus/update-order', [MenuController::class, 'updateOrder']);
            Route::apiResource('menus', MenuController::class);
            Route::apiResource('banners', BannerController::class);
            
            // Decision Block Management
            Route::post('chapters/update-order', [ChapterController::class, 'updateOrder']);
            Route::get('chapters/export', [ChapterController::class, 'export']);
            Route::post('chapters/import', [ChapterController::class, 'import']);
            Route::apiResource('chapters', ChapterController::class);
            Route::get('products/export', [ProductController::class, 'export']);
            Route::post('products/import', [ProductController::class, 'import']);
            Route::apiResource('products', ProductController::class);
            Route::get('services/export', [ServiceController::class, 'export']);
            Route::post('services/import', [ServiceController::class, 'import']);
            Route::post('services/update-order', [ServiceController::class, 'updateOrder']);
            Route::apiResource('services', ServiceController::class);
            Route::get('cases/export', [CaseController::class, 'export']);
            Route::post('cases/import', [CaseController::class, 'import']);
            Route::apiResource('cases', CaseController::class);
            
            // Full Decisions Export/Import
            Route::get('decisions/export', [DecisionController::class, 'exportAll']);
            Route::post('decisions/import', [DecisionController::class, 'importAll']);
            Route::apiResource('options', OptionController::class);
            Route::apiResource('option-trees', OptionTreeController::class);
            Route::apiResource('instances', InstanceController::class);
            Route::get('decision-block-settings', [DecisionBlockSettingsController::class, 'show']);
            Route::put('decision-block-settings', [DecisionBlockSettingsController::class, 'update']);
            Route::apiResource('quizzes', QuizController::class);
            Route::get('quiz-block-settings', [QuizBlockSettingsController::class, 'show']);
            Route::put('quiz-block-settings', [QuizBlockSettingsController::class, 'update']);
            Route::get('how-work-block-settings', [HowWorkBlockSettingsController::class, 'show']);
            Route::put('how-work-block-settings', [HowWorkBlockSettingsController::class, 'update']);
            Route::get('faq-block-settings', [FaqBlockSettingsController::class, 'show']);
            Route::put('faq-block-settings', [FaqBlockSettingsController::class, 'update']);
            Route::get('why-choose-us-block-settings', [WhyChooseUsBlockSettingsController::class, 'show']);
            Route::put('why-choose-us-block-settings', [WhyChooseUsBlockSettingsController::class, 'update']);
            Route::get('cases-block-settings', [CasesBlockSettingsController::class, 'show']);
            Route::put('cases-block-settings', [CasesBlockSettingsController::class, 'update']);
            Route::get('telegram-settings', [TelegramSettingsController::class, 'show']);
            Route::put('telegram-settings', [TelegramSettingsController::class, 'update']);
            Route::post('telegram-settings/test', [TelegramSettingsController::class, 'testConnection']);
            Route::get('telegram-settings/webhook-info', [TelegramSettingsController::class, 'getWebhookInfo']);
            Route::get('contact-settings', [ContactSettingsController::class, 'show']);
            Route::put('contact-settings', [ContactSettingsController::class, 'update']);
            Route::get('about-settings', [AboutSettingsController::class, 'show']);
            Route::put('about-settings', [AboutSettingsController::class, 'update']);
            Route::get('footer-settings', [FooterSettingsController::class, 'show']);
            Route::put('footer-settings', [FooterSettingsController::class, 'update']);
            Route::get('email-settings', [EmailSettingsController::class, 'show']);
            Route::put('email-settings', [EmailSettingsController::class, 'update']);
            Route::get('smtp-settings', [SmtpSettingsController::class, 'show']);
            Route::put('smtp-settings', [SmtpSettingsController::class, 'update']);
            Route::post('smtp-settings/test', [SmtpSettingsController::class, 'test']);
            Route::get('case-card-settings', [CaseCardSettingsController::class, 'show']);
            Route::put('case-card-settings', [CaseCardSettingsController::class, 'update']);
            Route::get('home-page-settings', [HomePageSettingsController::class, 'show']);
            Route::put('home-page-settings', [HomePageSettingsController::class, 'update']);
            Route::get('telegram-admin-requests', [TelegramAdminRequestController::class, 'index']);
            Route::post('telegram-admin-requests/{id}/approve', [TelegramAdminRequestController::class, 'approve']);
            Route::post('telegram-admin-requests/{id}/reject', [TelegramAdminRequestController::class, 'reject']);
            Route::apiResource('modal-settings', ModalSettingsController::class);
            Route::get('product-requests/stats', [ProductRequestController::class, 'stats']);
            Route::apiResource('product-requests', ProductRequestController::class);
            Route::get('home-page-blocks', [HomePageBlocksController::class, 'index']);
            Route::post('home-page-blocks/update-order', [HomePageBlocksController::class, 'updateOrder']);
            Route::put('home-page-blocks/{id}', [HomePageBlocksController::class, 'update']);
            Route::apiResource('pages', PageController::class);
            Route::post('pages/check-slug', [PageController::class, 'checkSlug']);
            
            // SEO Settings
            Route::get('seo-settings', [SeoSettingsController::class, 'show']);
            Route::put('seo-settings', [SeoSettingsController::class, 'update']);
        });
    });
});

// Публичные маршруты для меню (без авторизации)
Route::get('/public/menus/{type}', [MenuController::class, 'getPublicMenu'])->where('type', 'header|footer|burger');

// Публичные маршруты для баннеров (без авторизации)
Route::get('/public/banners/{slug}', [BannerController::class, 'getBySlug']);

// Публичные маршруты для Decision Block (без авторизации)
Route::get('/public/decision-block/chapters', [ChapterController::class, 'index']);
Route::get('/public/decision-block/settings', [DecisionBlockSettingsController::class, 'show']);

// Публичные маршруты для Quiz Block (без авторизации)
Route::get('/public/quiz-block/settings', [QuizBlockSettingsController::class, 'show']);
Route::get('/public/quiz-block/quiz/{id}', [QuizController::class, 'showPublic']);
Route::post('/public/quiz/submit', [QuizSubmissionController::class, 'submit']);

// Публичные маршруты для HowWork Block (без авторизации)
Route::get('/public/how-work-block/settings', [HowWorkBlockSettingsController::class, 'show']);

// Публичные маршруты для FAQ Block (без авторизации)
Route::get('/public/faq-block/settings', [FaqBlockSettingsController::class, 'show']);

// Публичные маршруты для WhyChooseUs Block (без авторизации)
Route::get('/public/why-choose-us-block/settings', [WhyChooseUsBlockSettingsController::class, 'show']);

// Публичные маршруты для Home Page Blocks (без авторизации)
Route::get('/public/home-page-blocks', [HomePageBlocksController::class, 'getPublic']);

// Публичные маршруты для настроек главной страницы (без авторизации)
Route::get('/public/home-page-settings', [HomePageSettingsController::class, 'show']);

// Публичные маршруты для Pages (без авторизации)
Route::get('/public/pages/{slug}', [PageController::class, 'getBySlug']);

// Публичные маршруты для SEO (без авторизации)
Route::get('/public/seo-settings', [SeoSettingsController::class, 'getPublic']);

// Публичные маршруты для Cases Block (без авторизации)
Route::get('/public/cases-block/settings', [CasesBlockSettingsController::class, 'show']);

// Публичные маршруты для Case Card Settings (без авторизации)
Route::get('/public/case-card-settings', [CaseCardSettingsController::class, 'show']);

// Публичные маршруты для Cases (без авторизации)
Route::get('/public/cases', [CaseController::class, 'index']);
Route::get('/public/cases/{id}', [CaseController::class, 'show']);

// Публичные маршруты для Products (без авторизации)
Route::get('/public/products', [ProductController::class, 'index']);
Route::get('/public/products/{slug}', [ProductController::class, 'showBySlug']);
Route::post('/public/leave-products', [ProductController::class, 'submitRequest']);
Route::post('/public/leave-services', [ServiceController::class, 'submitRequest']);

// Публичные маршруты для Services (без авторизации)
Route::get('/public/services', [ServiceController::class, 'index']);
Route::get('/public/services/{slug}', [ServiceController::class, 'showBySlug']);

// Публичные маршруты для Modal Settings (без авторизации)
Route::get('/public/modal-settings/{type}', [ModalSettingsController::class, 'show']);

// Публичные маршруты для обратной связи (без авторизации)
Route::post('/public/feedback', [FeedbackController::class, 'submit']);

// Публичные маршруты для поиска (без авторизации)
Route::get('/public/search', [SearchController::class, 'search']);
Route::get('/public/search/autocomplete', [SearchController::class, 'autocomplete']);

// Публичные маршруты для настроек контактов (без авторизации)
Route::get('/public/contact-settings', [ContactSettingsController::class, 'show']);

// Публичные маршруты для настроек страницы "О нас" (без авторизации)
Route::get('/public/about-settings', [AboutSettingsController::class, 'show']);

// Публичные маршруты для настроек футера (без авторизации)
Route::get('/public/footer/settings', [FooterSettingsController::class, 'show']);

// Публичный webhook для Telegram (без авторизации)
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

// Маршрут для деплоя (защищен токеном)
Route::post('/deploy', [DeployController::class, 'deploy'])
    ->middleware('deploy.token');

// Маршрут для синхронизации БД и файлов (защищен токеном)
Route::post('/sync-sql-file', [DeployController::class, 'syncSqlFile'])
    ->middleware('deploy.token');

// Маршрут для проверки требований синхронизации (защищен токеном)
Route::get('/sync-check-requirements', [DeployController::class, 'checkSyncRequirements'])
    ->middleware('deploy.token');


