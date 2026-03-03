<?php
/**
 * Скрипт для быстрой проверки robots.txt и sitemap.xml
 * 
 * Использование:
 * php check-seo-files.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Получаем базовый URL
$baseUrl = env('APP_URL', 'http://lagom-figma.loc');

echo "🔍 Проверка SEO файлов\n";
echo "=====================\n\n";

// Проверка robots.txt
echo "1. Проверка robots.txt:\n";
echo "   URL: {$baseUrl}/robots.txt\n";
try {
    $robotsController = new \App\Http\Controllers\RobotsController();
    $robotsResponse = $robotsController->index();
    $robotsContent = $robotsResponse->getContent();
    
    echo "   ✅ Файл доступен\n";
    echo "   Содержимое:\n";
    echo "   " . str_repeat("-", 50) . "\n";
    $lines = explode("\n", $robotsContent);
    foreach ($lines as $line) {
        echo "   " . $line . "\n";
    }
    echo "   " . str_repeat("-", 50) . "\n";
    
    // Проверка наличия Sitemap
    if (strpos($robotsContent, 'Sitemap:') !== false) {
        echo "   ✅ Ссылка на sitemap.xml найдена\n";
    } else {
        echo "   ⚠️  Ссылка на sitemap.xml не найдена\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n";

// Проверка sitemap.xml
echo "2. Проверка sitemap.xml:\n";
echo "   URL: {$baseUrl}/sitemap.xml\n";
try {
    $sitemapController = new \App\Http\Controllers\SitemapController();
    $sitemapResponse = $sitemapController->index();
    $sitemapContent = $sitemapResponse->getContent();
    
    echo "   ✅ Файл доступен\n";
    
    // Парсим XML для подсчета URL
    $xml = simplexml_load_string($sitemapContent);
    if ($xml) {
        $urlCount = count($xml->url);
        echo "   ✅ XML валиден\n";
        echo "   📊 Количество URL: {$urlCount}\n";
        
        // Показываем первые 5 URL
        echo "   Первые URL:\n";
        $count = 0;
        foreach ($xml->url as $url) {
            if ($count >= 5) break;
            echo "     - " . (string)$url->loc . "\n";
            $count++;
        }
        if ($urlCount > 5) {
            echo "     ... и еще " . ($urlCount - 5) . " URL\n";
        }
    } else {
        echo "   ⚠️  XML не валиден\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n";
echo "✅ Проверка завершена\n";
echo "\n";
echo "💡 Для проверки в браузере откройте:\n";
echo "   - {$baseUrl}/robots.txt\n";
echo "   - {$baseUrl}/sitemap.xml\n";

