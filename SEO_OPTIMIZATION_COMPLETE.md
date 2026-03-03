# SEO Оптимизация - Завершено

## Выполненные работы

### 1. Компонент SEOHead для управления мета-тегами

Создан универсальный компонент `SEOHead.vue` для управления:
- Title
- Meta Description
- Meta Keywords
- Open Graph теги (og:title, og:description, og:image, og:type, og:url)
- Twitter Cards (twitter:card, twitter:title, twitter:description, twitter:image)
- Canonical URL
- Schema.org микроразметка (JSON-LD)
- Robots meta теги

**Расположение:** `resources/js/components/SEOHead.vue`

### 2. Внедрение SEO на публичных страницах

#### Страницы с полной микроразметкой:

**ProductPage.vue**
- SEO мета-теги (title, description, keywords)
- Open Graph разметка
- Schema.org Product
- Breadcrumbs schema
- Canonical URL

**ServicePage.vue**
- SEO мета-теги
- Open Graph разметка
- Schema.org Service
- Breadcrumbs schema
- Canonical URL

**CasePage.vue**
- SEO мета-теги
- Open Graph разметка
- Schema.org Article
- Breadcrumbs schema
- Canonical URL
- Поддержка множественных изображений

#### Страницы-каталоги:

**Home.vue**
- Глобальные SEO настройки
- Schema.org Organization
- Schema.org WebSite
- Динамическая загрузка настроек из API

**AboutPage.vue**
- SEO мета-теги
- Schema.org AboutPage
- Canonical URL

**ContactPage.vue**
- SEO мета-теги
- Schema.org ContactPage
- Canonical URL

**ProductsPage.vue**
- SEO мета-теги
- Breadcrumbs schema
- Canonical URL

**ServicesPage.vue**
- SEO мета-теги
- Breadcrumbs schema
- Canonical URL

**CasesPage.vue**
- SEO мета-теги
- Schema.org CollectionPage
- Canonical URL

**Page.vue** (динамические страницы из админки)
- SEO мета-теги из настроек страницы
- Schema.org WebPage
- Canonical URL

### 3. Backend - Модели и миграции

#### Модель SeoSettings
**Файл:** `app/Models/SeoSettings.php`

Поля:
- `site_name` - Название сайта
- `site_description` - Описание сайта
- `site_keywords` - Ключевые слова
- `default_og_image` - Изображение по умолчанию для OG
- `og_type`, `og_site_name` - Open Graph настройки
- `twitter_card`, `twitter_site`, `twitter_creator` - Twitter Cards
- `organization_name`, `organization_logo`, `organization_phone`, `organization_email`, `organization_address` - Данные организации для Schema.org
- `allow_indexing` - Разрешить индексацию
- `robots_txt` - Содержимое robots.txt
- `additional_schema` - Дополнительная разметка (JSON)

Методы:
- `getSettings()` - Получить настройки (singleton)
- `getOrganizationSchema()` - Schema.org Organization
- `getWebSiteSchema()` - Schema.org WebSite

#### Модель Page
**Файл:** `app/Models/Page.php`

Добавлены SEO поля:
- `seo_title` - SEO заголовок
- `seo_description` - SEO описание
- `seo_keywords` - SEO ключевые слова

#### Модели Product, Service, ProjectCase

Добавлены SEO поля в fillable:
- `seo_title`
- `seo_description`
- `seo_keywords`

**Миграция:** `database/migrations/2025_12_03_173418_add_seo_fields_to_products_services_cases.php`

### 4. Контроллеры SEO

#### SeoSettingsController
**Файл:** `app/Http/Controllers/Api/SeoSettingsController.php`

Методы:
- `show()` - Получить настройки (админ)
- `update()` - Обновить настройки (админ)
- `getPublic()` - Получить публичные настройки (для фронтенда)

#### RobotsController
**Файл:** `app/Http/Controllers/RobotsController.php`

- Динамическая генерация robots.txt
- Использует настройки из SeoSettings
- Автоматически добавляет ссылку на sitemap.xml

#### SitemapController
**Файл:** `app/Http/Controllers/SitemapController.php`

Генерирует sitemap.xml со всеми страницами:
- Главная страница
- Статические страницы (products, services, cases, about, contacts)
- Динамические продукты
- Динамические услуги
- Динамические кейсы
- Пользовательские страницы из админки

### 5. Роуты

**Файл:** `routes/web.php`
```php
Route::get('/robots.txt', [RobotsController::class, 'index']);
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
```

**Файл:** `routes/api.php`
```php
// Админ
Route::get('seo-settings', [SeoSettingsController::class, 'show']);
Route::put('seo-settings', [SeoSettingsController::class, 'update']);

// Публичный API
Route::get('/public/seo-settings', [SeoSettingsController::class, 'getPublic']);
Route::get('/public/pages/{slug}', [PageController::class, 'getBySlug']);
```

### 6. Админ-панель

#### Пункт меню "SEO настройки"
**Файл:** `app/Services/AdminMenu.php`

Добавлен в раздел "Настройки":
- Название: "SEO настройки"
- Роут: `admin.seo-settings`
- Доступ: admin, manager

#### Страница управления SEO
**Файл:** `resources/js/pages/admin/SeoSettings.vue`

Разделы:
1. **Основные настройки**
   - Название сайта
   - Описание сайта
   - Ключевые слова
   - Разрешить индексацию

2. **Open Graph**
   - Изображение по умолчанию
   - Тип контента
   - Название сайта для OG

3. **Twitter Cards**
   - Тип карточки
   - Twitter аккаунт сайта
   - Twitter создателя

4. **Информация об организации (Schema.org)**
   - Название организации
   - Лого
   - Телефон
   - Email
   - Адрес

5. **Robots.txt**
   - Редактирование содержимого robots.txt

#### Пункт меню "Страницы"
**Файл:** `app/Services/AdminMenu.php`

Добавлен раздел "Страницы":
- Список страниц (admin.pages)
- Главная страница (admin.pages.home)

#### Управление страницами
**Файлы:**
- `resources/js/pages/admin/pages/Pages.vue` - Список страниц
- `resources/js/pages/admin/pages/PageEdit.vue` - Редактирование страницы

Возможности:
- Создание/редактирование страниц
- SEO поля для каждой страницы
- Автоматическая генерация slug
- Проверка конфликтов URL
- Активация/деактивация страниц

### 7. Сидеры

#### SeoSettingsSeeder
**Файл:** `database/seeders/SeoSettingsSeeder.php`

Создает начальные SEO настройки:
- Название сайта: "Lagom"
- Описание и ключевые слова
- Настройки Open Graph и Twitter Cards
- Данные организации
- Robots.txt по умолчанию

**Запуск:**
```bash
php artisan db:seed --class=SeoSettingsSeeder
```

### 8. Миграции

1. **create_seo_settings_table.php** - Таблица глобальных SEO настроек
2. **create_pages_table.php** - Таблица пользовательских страниц с SEO полями
3. **add_seo_fields_to_products_services_cases.php** - Добавление SEO полей в существующие таблицы

**Запуск миграций:**
```bash
php artisan migrate
```

## Преимущества реализации

### 1. SEO оптимизация
- ✅ Уникальные meta title, description, keywords для каждой страницы
- ✅ Open Graph для красивых превью в соцсетях
- ✅ Twitter Cards для Twitter
- ✅ Schema.org микроразметка для поисковиков
- ✅ Canonical URL для избежания дублей
- ✅ Breadcrumbs для навигации
- ✅ Динамический robots.txt
- ✅ Автоматический sitemap.xml

### 2. Управление контентом
- ✅ Централизованное управление SEO настройками
- ✅ Индивидуальные SEO настройки для продуктов, услуг, кейсов
- ✅ Создание пользовательских страниц через админку
- ✅ Проверка конфликтов URL

### 3. Производительность
- ✅ Кеширование SEO настроек
- ✅ Оптимизированные запросы к БД
- ✅ Ленивая загрузка компонентов

### 4. Удобство разработки
- ✅ Универсальный компонент SEOHead
- ✅ Computed properties для SEO данных
- ✅ Автоматическая генерация микроразметки
- ✅ Типизация и валидация данных

## Инструкции по использованию

### Для администраторов

1. **Настройка глобальных SEO параметров:**
   - Перейти в Админ панель → Настройки → SEO настройки
   - Заполнить основные данные о сайте
   - Настроить Open Graph и Twitter Cards
   - Указать данные организации
   - При необходимости отредактировать robots.txt

2. **Создание пользовательских страниц:**
   - Перейти в Админ панель → Страницы → Список страниц
   - Нажать "Добавить страницу"
   - Заполнить контент и SEO поля
   - Сохранить

3. **Настройка SEO для продуктов/услуг/кейсов:**
   - При создании/редактировании элемента заполнить поля:
     - SEO заголовок (seo_title)
     - SEO описание (seo_description)
     - SEO ключевые слова (seo_keywords)

### Для разработчиков

1. **Использование SEOHead в новых страницах:**
```vue
<template>
  <div>
    <SEOHead
      :title="pageTitle"
      :description="pageDescription"
      :keywords="pageKeywords"
      :og-image="pageImage"
      :canonical="canonicalUrl"
      :schema="pageSchema"
    />
    <!-- Контент страницы -->
  </div>
</template>

<script>
import SEOHead from '../components/SEOHead.vue';

export default {
  components: { SEOHead },
  setup() {
    const pageTitle = computed(() => 'Заголовок страницы');
    const pageDescription = computed(() => 'Описание страницы');
    // ...
    return { pageTitle, pageDescription, ... };
  }
}
</script>
```

2. **Добавление новых типов Schema.org:**
```javascript
const customSchema = computed(() => {
  return {
    '@context': 'https://schema.org',
    '@type': 'YourType',
    'name': 'Name',
    'description': 'Description',
    // ...
  };
});
```

## Дальнейшие улучшения

### Рекомендуемые доработки:

1. **Расширенная аналитика:**
   - Интеграция Google Analytics
   - Интеграция Яндекс.Метрика
   - Отслеживание конверсий

2. **Дополнительные типы Schema.org:**
   - FAQ schema для страниц с вопросами
   - Review schema для отзывов
   - Event schema для мероприятий

3. **Мультиязычность:**
   - hreflang теги для разных языков
   - Локализованные SEO настройки

4. **Автоматизация:**
   - Автоматическая генерация meta description из контента
   - Анализ SEO качества страниц
   - Предложения по улучшению

5. **Интеграция с внешними сервисами:**
   - Google Search Console
   - Яндекс.Вебмастер
   - Автоматическая отправка sitemap

## Заключение

Все публичные страницы сайта теперь полностью оптимизированы для поисковых систем:
- ✅ Уникальные мета-теги на каждой странице
- ✅ Микроразметка Schema.org
- ✅ Open Graph и Twitter Cards
- ✅ Динамический robots.txt и sitemap.xml
- ✅ Удобное управление через админ-панель
- ✅ Возможность создания пользовательских страниц

Система готова к продакшену и может быть легко расширена в будущем.

