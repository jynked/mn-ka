# SEO для ботов в SPA

## Проблема

В Single Page Application (SPA) на Vue.js все мета-теги обновляются динамически через JavaScript. Однако поисковые боты и социальные сети часто не выполняют JavaScript и видят только статический HTML из `app.blade.php` с дефолтными значениями.

## Решение

Создан middleware `RenderMetaForBots`, который:
1. Определяет, является ли запрос от бота
2. Получает правильные мета-теги для текущей страницы из базы данных
3. Заменяет дефолтные мета-теги в HTML-ответе на актуальные

## Файлы

### 1. Middleware
**Файл:** `app/Http/Middleware/RenderMetaForBots.php`

Определяет User-Agent'ы ботов:
- Google (googlebot)
- Bing (bingbot)
- Yandex (yandexbot)
- Facebook (facebookexternalhit)
- Twitter (twitterbot)
- LinkedIn (linkedinbot)
- WhatsApp
- Telegram
- Slack
- Discord

### 2. Регистрация middleware
**Файл:** `bootstrap/app.php`

Middleware добавлен в группу `web`:
```php
$middleware->web(append: [
    \App\Http\Middleware\RenderMetaForBots::class,
]);
```

## Поддерживаемые страницы

### Главная страница (`/`)
- Использует настройки из `SeoSettings`
- Fallback на дефолтные значения

### Список продуктов (`/products`)
- Статический title и description

### Страница продукта (`/products/{slug}`)
- Использует `seo_title`, `seo_description`, `seo_keywords` из таблицы `products`
- Fallback на `name` и `description`

### Список услуг (`/services`)
- Статический title и description

### Страница услуги (`/services/{slug}`)
- Использует `seo_title`, `seo_description`, `seo_keywords` из таблицы `services`
- Fallback на `name` и `description`

### Список кейсов (`/cases`)
- Статический title и description

### Страница кейса (`/cases/{slug}`)
- Использует `seo_title`, `seo_description`, `seo_keywords` из таблицы `cases`
- Fallback на `name` и `description`

### Страница "О компании" (`/about`)
- Статический title и description

### Страница контактов (`/contacts`)
- Статический title и description

### Динамические страницы (`/{slug}`)
- Использует данные из таблицы `pages`
- Поля: `seo_title`, `seo_description` или `title`, `html`

## Как это работает

1. **Запрос от браузера**:
   - Middleware пропускает запрос
   - Vue.js загружается и обновляет мета-теги динамически

2. **Запрос от бота**:
   - Middleware определяет бота по User-Agent
   - Загружает актуальные данные из БД
   - Заменяет мета-теги в HTML через regex
   - Бот получает HTML с правильными мета-тегами

## Обновляемые теги

- `<title>` - заголовок страницы
- `<meta name="description">` - описание
- `<meta property="og:title">` - Open Graph title
- `<meta property="og:description">` - Open Graph description

## Тестирование

### Curl (эмуляция бота)
```bash
curl -A "Googlebot" http://lagom-figma.loc/products
curl -A "facebookexternalhit" http://lagom-figma.loc/cases
```

### Online инструменты
- [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)
- [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/)

### Проверка в консоли
```bash
# Google Bot
curl -A "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)" http://lagom-figma.loc/

# Facebook
curl -A "facebookexternalhit/1.1" http://lagom-figma.loc/products

# Twitter
curl -A "Twitterbot/1.0" http://lagom-figma.loc/cases
```

## Производительность

- Middleware работает только для ботов
- Обычные пользователи не испытывают задержек
- Запросы к БД кешируются автоматически Laravel

## Расширение

Для добавления поддержки новой страницы:

1. Добавьте условие в метод `getMetaForPath()`:
```php
if ($path === 'new-page') {
    return [
        'title' => 'New Page Title',
        'description' => 'New Page Description',
    ];
}
```

2. Или создайте отдельный метод:
```php
protected function getNewPageMeta(string $slug): ?array
{
    // Логика получения данных
    return ['title' => '...', 'description' => '...'];
}
```

## Логирование

Все ошибки логируются в `storage/logs/laravel.log`:
- Ошибки загрузки данных из БД
- Проблемы с получением мета-тегов

## Важно

- Middleware должен быть в группе `web` (после `StartSession`)
- Не забывайте очищать кеш после изменений: `php artisan cache:clear`
- Боты могут кешировать результаты на несколько дней

