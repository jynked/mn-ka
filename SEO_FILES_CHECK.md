# Проверка robots.txt и sitemap.xml

## Способы проверки

### 1. Проверка через браузер

#### robots.txt
Откройте в браузере:
```
http://lagom-figma.loc/robots.txt
```
или
```
http://127.0.0.1:8000/robots.txt
```

#### sitemap.xml
Откройте в браузере:
```
http://lagom-figma.loc/sitemap.xml
```
или
```
http://127.0.0.1:8000/sitemap.xml
```

### 2. Проверка через командную строку (cURL)

#### robots.txt
```bash
curl http://lagom-figma.loc/robots.txt
```

#### sitemap.xml
```bash
curl http://lagom-figma.loc/sitemap.xml
```

### 3. Проверка через PHP Artisan Tinker

Запустите в терминале:
```bash
php artisan tinker
```

Затем выполните:
```php
// Проверка robots.txt
$response = app(\App\Http\Controllers\RobotsController::class)->index();
echo $response->getContent();

// Проверка sitemap.xml
$response = app(\App\Http\Controllers\SitemapController::class)->index();
echo $response->getContent();
```

### 4. Проверка через тестовый запрос

Создайте временный файл для проверки:

```php
// В routes/web.php временно добавьте:
Route::get('/test-seo', function() {
    $robots = app(\App\Http\Controllers\RobotsController::class)->index();
    $sitemap = app(\App\Http\Controllers\SitemapController::class)->index();
    
    return response()->json([
        'robots_txt' => $robots->getContent(),
        'sitemap_xml' => $sitemap->getContent(),
    ]);
});
```

Затем откройте: `http://lagom-figma.loc/test-seo`

### 5. Проверка валидности sitemap.xml

#### Онлайн-валидаторы:
- https://www.xml-sitemaps.com/validate-xml-sitemap.html
- https://validator.w3.org/
- https://www.xmlvalidation.com/

#### Через командную строку (если установлен xmllint):
```bash
curl http://lagom-figma.loc/sitemap.xml | xmllint --format -
```

### 6. Проверка через Google Search Console

1. Зайдите в [Google Search Console](https://search.google.com/search-console)
2. Выберите ваш сайт
3. Перейдите в раздел "Sitemaps"
4. Добавьте URL: `https://ваш-домен.ru/sitemap.xml`
5. Проверьте статус индексации

### 7. Проверка через Яндекс.Вебмастер

1. Зайдите в [Яндекс.Вебмастер](https://webmaster.yandex.ru/)
2. Выберите ваш сайт
3. Перейдите в раздел "Индексирование" → "Файлы Sitemap"
4. Добавьте URL: `https://ваш-домен.ru/sitemap.xml`
5. Проверьте статус

## Ожидаемое содержимое

### robots.txt (пример)
```
User-agent: *
Disallow: /admin/
Disallow: /api/
Allow: /

# Sitemap
Sitemap: http://lagom-figma.loc/sitemap.xml
```

### sitemap.xml (структура)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>http://lagom-figma.loc/</loc>
        <lastmod>2025-12-03</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>http://lagom-figma.loc/products</loc>
        <lastmod>2025-12-03</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <!-- ... остальные URL ... -->
</urlset>
```

## Настройка robots.txt через админ-панель

1. Зайдите в админ-панель: `/admin/seo-settings`
2. Найдите поле "Robots.txt"
3. Введите кастомный текст или оставьте пустым для автоматической генерации
4. Сохраните настройки

## Настройка индексации

В админ-панели `/admin/seo-settings`:
- **Разрешить индексацию** - включите, чтобы разрешить поисковым системам индексировать сайт
- **Запретить индексацию** - выключите, чтобы запретить индексацию (для тестовых/разработческих сайтов)

## Проверка через консоль браузера

Откройте консоль разработчика (F12) и выполните:

```javascript
// Проверка robots.txt
fetch('/robots.txt')
    .then(response => response.text())
    .then(data => console.log('robots.txt:', data));

// Проверка sitemap.xml
fetch('/sitemap.xml')
    .then(response => response.text())
    .then(data => console.log('sitemap.xml:', data));
```

## Устранение проблем

### Если robots.txt не отображается:
1. Проверьте, что роут зарегистрирован в `routes/web.php`
2. Проверьте, что контроллер `RobotsController` существует
3. Очистите кеш: `php artisan route:clear && php artisan cache:clear`

### Если sitemap.xml не отображается:
1. Проверьте, что роут зарегистрирован в `routes/web.php`
2. Проверьте, что контроллер `SitemapController` существует
3. Проверьте, что модели (Product, Service, CaseModel, Page) существуют
4. Очистите кеш: `php artisan route:clear && php artisan cache:clear`

### Если sitemap.xml пустой:
1. Проверьте, что в базе данных есть активные записи (products, services, cases, pages)
2. Проверьте, что у записей установлено `is_active = true`
3. Проверьте логи Laravel на наличие ошибок

## Автоматическая проверка через тесты

Создайте тест для проверки:

```bash
php artisan make:test SeoFilesTest
```

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoFilesTest extends TestCase
{
    public function test_robots_txt_is_accessible()
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain');
    }

    public function test_sitemap_xml_is_accessible()
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_robots_txt_contains_sitemap()
    {
        $response = $this->get('/robots.txt');
        $response->assertSee('Sitemap:');
    }
}
```

Запустите тесты:
```bash
php artisan test --filter SeoFilesTest
```

