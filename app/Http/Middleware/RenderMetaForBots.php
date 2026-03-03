<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SeoSettings;
use App\Models\Product;
use App\Models\Service;
use App\Models\ProjectCase;
use App\Models\Page;
use Symfony\Component\HttpFoundation\Response;

class RenderMetaForBots
{
    /**
     * Список User-Agent'ов поисковых ботов и социальных сетей
     */
    protected $botAgents = [
        'googlebot',
        'bingbot',
        'slurp',
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'facebookexternalhit',
        'twitterbot',
        'linkedinbot',
        'whatsapp',
        'telegram',
        'slackbot',
        'discordbot',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, является ли запрос от бота
        if (!$this->isBot($request)) {
            return $next($request);
        }

        // Получаем путь запроса
        $path = $request->path();
        
        // Получаем мета-теги для текущей страницы
        $meta = $this->getMetaForPath($path);
        
        if ($meta) {
            // Сохраняем мета-теги в request для использования в шаблоне
            $request->attributes->set('seo_meta', $meta);
        }

        $response = $next($request);

        // Если это HTML-ответ и у нас есть мета-теги, вставляем их
        if ($meta && $response->headers->get('Content-Type') === 'text/html; charset=UTF-8') {
            $content = $response->getContent();
            
            // Заменяем title
            $content = preg_replace(
                '/<title>.*?<\/title>/',
                '<title>' . htmlspecialchars($meta['title']) . '</title>',
                $content,
                1
            );
            
            // Обновляем description
            $content = preg_replace(
                '/<meta\s+name="description"\s+content=".*?"\s*\/?>/',
                '<meta name="description" content="' . htmlspecialchars($meta['description']) . '">',
                $content,
                1
            );
            
            // Обновляем og:title
            $content = preg_replace(
                '/<meta\s+property="og:title"\s+content=".*?"\s*\/?>/',
                '<meta property="og:title" content="' . htmlspecialchars($meta['title']) . '">',
                $content,
                1
            );
            
            // Обновляем og:description
            $content = preg_replace(
                '/<meta\s+property="og:description"\s+content=".*?"\s*\/?>/',
                '<meta property="og:description" content="' . htmlspecialchars($meta['description']) . '">',
                $content,
                1
            );
            
            $response->setContent($content);
        }

        return $response;
    }

    /**
     * Проверяет, является ли запрос от бота
     */
    protected function isBot(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');
        
        foreach ($this->botAgents as $bot) {
            if (str_contains($userAgent, $bot)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Получает мета-теги для заданного пути
     */
    protected function getMetaForPath(string $path): ?array
    {
        // Главная страница
        if ($path === '/' || $path === '') {
            return $this->getHomePageMeta();
        }

        // Страницы продуктов
        if (preg_match('#^products/([^/]+)$#', $path, $matches)) {
            return $this->getProductMeta($matches[1]);
        }

        // Список продуктов
        if ($path === 'products') {
            return [
                'title' => 'Продукты и услуги - МНКА | Каталог земельных участков',
                'description' => 'Полный каталог продуктов и услуг по подбору, оформлению и работе с земельными участками. Профессиональные решения для складов, производства, придорожного сервиса и недвижимости.',
            ];
        }

        // Страницы услуг
        if (preg_match('#^services/([^/]+)$#', $path, $matches)) {
            return $this->getServiceMeta($matches[1]);
        }

        // Список услуг
        if ($path === 'services') {
            return [
                'title' => 'Услуги по работе с земельными участками - МНКА',
                'description' => 'Полный спектр услуг по работе с земельными участками: кадастровые работы, юридическое сопровождение, консультации и оформление документов.',
            ];
        }

        // Страницы кейсов
        if (preg_match('#^cases/([^/]+)$#', $path, $matches)) {
            return $this->getCaseMeta($matches[1]);
        }

        // Список кейсов
        if ($path === 'cases') {
            return [
                'title' => 'Кейсы и объекты - МНКА | Примеры реализованных проектов',
                'description' => 'Портфолио успешно реализованных проектов по подбору и оформлению земельных участков. Реальные кейсы складов, производств, придорожного сервиса и других объектов.',
            ];
        }

        // Страница "О компании"
        if ($path === 'about') {
            return [
                'title' => 'О компании МНКА - Профессиональные услуги по работе с земельными участками',
                'description' => 'МНКА - ведущая компания по подбору и оформлению земельных участков. Узнайте о нашей команде, опыте работы и подходе к решению задач клиентов. Более 10 лет на рынке недвижимости.',
            ];
        }

        // Страница контактов
        if ($path === 'contacts') {
            return [
                'title' => 'Контакты - МНКА | Свяжитесь с нами',
                'description' => 'Свяжитесь с нами для получения консультации или заказа услуг. Полная контактная информация, адрес и форма обратной связи.',
            ];
        }

        // Динамические страницы из админки
        $page = Page::where('slug', $path)->first();
        if ($page) {
            return [
                'title' => $page->seo_title ?: $page->title,
                'description' => $page->seo_description ?: strip_tags($page->html),
            ];
        }

        return null;
    }

    /**
     * Мета-теги для главной страницы
     */
    protected function getHomePageMeta(): array
    {
        try {
            $settings = SeoSettings::first();
            
            if ($settings) {
                return [
                    'title' => $settings->site_name ?: 'МНКА - Профессиональные услуги по работе с земельными участками',
                    'description' => $settings->site_description ?: 'Профессиональные услуги по подбору и оформлению земельных участков. Кадастровые работы, консультации, оформление документов.',
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching SEO settings for bot', ['error' => $e->getMessage()]);
        }

        return [
            'title' => 'МНКА - Профессиональные услуги по работе с земельными участками',
            'description' => 'Профессиональные услуги по подбору и оформлению земельных участков. Кадастровые работы, консультации, оформление документов.',
        ];
    }

    /**
     * Мета-теги для страницы продукта
     */
    protected function getProductMeta(string $slug): ?array
    {
        try {
            $product = Product::where('slug', $slug)->first();
            
            if ($product) {
                $title = $product->seo_title ?: ($product->name . ' - МНКА | Продукты и услуги');
                $description = $product->seo_description ?: strip_tags($product->description);
                
                return [
                    'title' => $title,
                    'description' => mb_substr($description, 0, 160),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching product meta for bot', ['slug' => $slug, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Мета-теги для страницы услуги
     */
    protected function getServiceMeta(string $slug): ?array
    {
        try {
            $service = Service::where('slug', $slug)->first();
            
            if ($service) {
                $title = $service->seo_title ?: ($service->name . ' - МНКА | Профессиональные услуги');
                $description = $service->seo_description ?: strip_tags($service->description);
                
                return [
                    'title' => $title,
                    'description' => mb_substr($description, 0, 160),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching service meta for bot', ['slug' => $slug, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Мета-теги для страницы кейса
     */
    protected function getCaseMeta(string $slug): ?array
    {
        try {
            $case = ProjectCase::where('slug', $slug)->first();
            
            if ($case) {
                $title = $case->seo_title ?: ($case->name . ' - МНКА | Пример реализованного проекта');
                $description = $case->seo_description ?: strip_tags($case->description);
                
                return [
                    'title' => $title,
                    'description' => mb_substr($description, 0, 160),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching case meta for bot', ['slug' => $slug, 'error' => $e->getMessage()]);
        }

        return null;
    }
}

