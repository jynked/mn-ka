# Описание возможностей проекта Lagom Figma

## Общая информация

**Технологический стек:**
- Backend: Laravel 12.0 (PHP 8.2+)
- Frontend: Vue.js 3.5, Vue Router, Vuex, Tailwind CSS 4.0
- Сборка: Vite 7.0
- Аутентификация: Laravel Sanctum
- База данных: MySQL/MariaDB/PostgreSQL
- Дополнительные пакеты: Telegraph (Telegram боты), SweetAlert2, Vue Advanced Cropper, FSLightbox

**Архитектура:**
- SPA (Single Page Application) с Vue Router
- RESTful API на Laravel
- Разделение на публичную и административную части
- Система ролей и прав доступа

---

## 1. Система аутентификации и авторизации

### 1.1 Аутентификация
- Регистрация пользователей (`POST /api/auth/register`)
- Вход в систему (`POST /api/auth/login`)
- Выход из системы (`POST /api/auth/logout`)
- Получение текущего пользователя (`GET /api/auth/user`)
- Восстановление пароля:
  - Запрос на восстановление (`POST /api/auth/forgot-password`)
  - Сброс пароля (`POST /api/auth/reset-password`)

### 1.2 Система ролей
- Модель: `App\Models\Role`
- Модель: `App\Models\User` (связь many-to-many с ролями)
- Роли по умолчанию: `admin`, `manager`
- Middleware `admin` для защиты административных маршрутов
- Управление ролями через API (`/api/v1/roles`)
- Управление пользователями через API (`/api/v1/users`)

### 1.3 Уведомления
- Модель: `App\Models\Notification`
- API endpoints:
  - `GET /api/notifications` - список уведомлений
  - `GET /api/notifications/all` - все уведомления
  - `GET /api/notifications/unread-count` - количество непрочитанных
  - `GET /api/notifications/{id}` - просмотр уведомления
  - `POST /api/notifications/{id}/read` - отметить как прочитанное
  - `DELETE /api/notifications/{id}` - удалить уведомление
- Компонент: `NotificationDropdown.vue` в админ-панели

---

## 2. Управление медиа-файлами

### 2.1 Медиа библиотека (пакет letoceiling-coder/media)
- Модели: `App\Models\Media`, `App\Models\Folder`
- Иерархическая структура папок
- Загрузка файлов (изображения, видео, документы)
- Корзина с возможностью восстановления
- Фильтрация и поиск файлов
- Пагинация и сортировка
- Drag & Drop для папок
- Автоматическая генерация превью для изображений
- Редактор изображений с функцией обрезки (crop)
- Мультипользовательская поддержка (scope по user_id)
- Мягкое удаление (soft delete)

### 2.2 API endpoints (v1)
- Folders:
  - `GET /api/v1/folders` - список папок
  - `GET /api/v1/folders/tree/all` - дерево всех папок
  - `POST /api/v1/folders` - создать папку
  - `PUT /api/v1/folders/{id}` - обновить папку
  - `DELETE /api/v1/folders/{id}` - удалить папку
  - `POST /api/v1/folders/{id}/restore` - восстановить папку
  - `POST /api/v1/folders/update-positions` - обновить порядок
- Media:
  - `GET /api/v1/media` - список файлов
  - `POST /api/v1/media` - загрузить файл
  - `PUT /api/v1/media/{id}` - обновить файл
  - `DELETE /api/v1/media/{id}` - удалить файл
  - `POST /api/v1/media/{id}/restore` - восстановить файл
  - `DELETE /api/v1/media/trash/empty` - очистить корзину

---

## 3. Управление меню

### 3.1 Модель и структура
- Модель: `App\Models\Menu`
- Типы меню: `header`, `footer`, `burger`
- Иерархическая структура (parent_id)
- Порядок сортировки (order)
- Активность (is_active)

### 3.2 API endpoints
- Административные:
  - `GET /api/menus` - список меню
  - `POST /api/menus` - создать пункт меню
  - `PUT /api/menus/{id}` - обновить пункт меню
  - `DELETE /api/menus/{id}` - удалить пункт меню
  - `POST /api/menus/update-order` - обновить порядок
- Публичные:
  - `GET /api/public/menus/{type}` - получить меню по типу (header|footer|burger)

---

## 4. Управление баннерами

### 4.1 Модель
- Модель: `App\Models\Banner`
- Поля: `name`, `slug`, `image_id`, `link`, `order`, `is_active`

### 4.2 API endpoints
- Административные:
  - `GET /api/banners` - список баннеров
  - `POST /api/banners` - создать баннер
  - `PUT /api/banners/{id}` - обновить баннер
  - `DELETE /api/banners/{id}` - удалить баннер
- Публичные:
  - `GET /api/public/banners/{slug}` - получить баннер по slug

---

## 5. Блок "Решения" (Decision Block)

### 5.1 Структура данных
- **Разделы (Chapters)**: `App\Models\Chapter`
  - Поля: `name`, `order`, `is_active`
  - Связи: hasMany Products, hasMany Services
- **Продукты (Products)**: `App\Models\Product`
  - Поля: `name`, `slug`, `description` (JSON), `seo_title`, `seo_description`, `seo_keywords`, `image_id`, `icon_id`, `chapter_id`, `order`, `is_active`
  - Связи: belongsTo Chapter, belongsToMany Services, belongsTo Media (image, icon)
- **Услуги (Services)**: `App\Models\Service`
  - Поля: `name`, `slug`, `description` (JSON), `seo_title`, `seo_description`, `seo_keywords`, `image_id`, `icon_id`, `chapter_id`, `order`, `is_active`
  - Связи: belongsTo Chapter, belongsToMany Products, belongsToMany Options, belongsToMany OptionTrees, belongsToMany Instances, belongsTo Media (image, icon)
- **Опции (Options)**: `App\Models\Option`
  - Простые опции для услуг
  - Связь: belongsToMany Services
- **Деревья опций (OptionTrees)**: `App\Models\OptionTree`
  - Иерархические опции для услуг
  - Связь: belongsToMany Services
- **Экземпляры (Instances)**: `App\Models\Instance`
  - Экземпляры услуг
  - Связь: belongsToMany Services
- **Настройки блока**: `App\Models\DecisionBlockSettings`
  - Настройки отображения блока решений

### 5.2 API endpoints
- Административные:
  - Chapters: `GET/POST/PUT/DELETE /api/chapters`, `POST /api/chapters/update-order`
  - Products: `GET/POST/PUT/DELETE /api/products`, `GET /api/products/export`, `POST /api/products/import`
  - Services: `GET/POST/PUT/DELETE /api/services`, `GET /api/services/export`, `POST /api/services/import`
  - Options: `GET/POST/PUT/DELETE /api/options`
  - OptionTrees: `GET/POST/PUT/DELETE /api/option-trees`
  - Instances: `GET/POST/PUT/DELETE /api/instances`
  - Settings: `GET/PUT /api/decision-block-settings`
- Публичные:
  - `GET /api/public/decision-block/chapters` - получить разделы с продуктами и услугами
  - `GET /api/public/decision-block/settings` - получить настройки блока

### 5.3 Функциональность
- Импорт/экспорт продуктов и услуг (Excel/JSON)
- Кеширование продуктов (автоматическая очистка при изменении)
- SEO оптимизация для продуктов и услуг
- Многоязычные описания (JSON поле description)
- Связи между продуктами и услугами
- Сложные опции для услуг (простые и древовидные)

---

## 6. Блок "Квиз" (Quiz Block)

### 6.1 Структура данных
- **Квизы**: `App\Models\Quiz`
  - Поля: `title`, `description`, `is_active`, `order`
  - Связи: hasMany QuizQuestions
- **Вопросы квиза**: `App\Models\QuizQuestion`
  - Поля: `quiz_id`, `question`, `type` (text, select, radio, checkbox, image), `options` (JSON), `order`, `is_active`
- **Отправки квиза**: `App\Models\QuizSubmission`
  - Хранение ответов пользователей
- **Настройки блока**: `App\Models\QuizBlockSettings`
  - Настройки отображения квиза

### 6.2 API endpoints
- Административные:
  - `GET/POST/PUT/DELETE /api/quizzes`
  - `GET/PUT /api/quiz-block-settings`
- Публичные:
  - `GET /api/public/quiz-block/settings` - получить настройки
  - `GET /api/public/quiz-block/quiz/{id}` - получить квиз для прохождения
  - `POST /api/public/quiz/submit` - отправить ответы квиза

### 6.3 Функциональность
- Различные типы вопросов (текст, выбор, множественный выбор, изображения)
- Сбор изображений в квизе
- Отправка результатов квиза
- Уведомления администраторам о новых отправках

---

## 7. Блок "Как это работает" (How Work Block)

### 7.1 Структура
- Модель: `App\Models\HowWorkBlockSettings`
- Настройки отображения блока с шагами работы

### 7.2 API endpoints
- Административные: `GET/PUT /api/how-work-block-settings`
- Публичные: `GET /api/public/how-work-block/settings`

---

## 8. Блок "FAQ" (Frequently Asked Questions)

### 8.1 Структура
- Модель: `App\Models\FaqBlockSettings`
- Настройки блока с вопросами и ответами

### 8.2 API endpoints
- Административные: `GET/PUT /api/faq-block-settings`
- Публичные: `GET /api/public/faq-block/settings`

---

## 9. Блок "Почему выбирают нас" (Why Choose Us Block)

### 9.1 Структура
- Модель: `App\Models\WhyChooseUsBlockSettings`
- Настройки блока с преимуществами

### 9.2 API endpoints
- Административные: `GET/PUT /api/why-choose-us-block-settings`
- Публичные: `GET /api/public/why-choose-us-block/settings`

---

## 10. Блок "Кейсы" (Cases Block)

### 10.1 Структура данных
- **Кейсы**: `App\Models\ProjectCase`
  - Поля: `title`, `slug`, `description`, `seo_title`, `seo_description`, `seo_keywords`, `image_id`, `order`, `is_active`
  - Связи: belongsToMany Products, belongsToMany Services, hasMany CaseImages
- **Изображения кейсов**: `App\Models\CaseImage`
  - Связь: belongsTo ProjectCase
- **Настройки блока**: `App\Models\CasesBlockSettings`
- **Настройки карточки кейса**: `App\Models\CaseCardSettings`

### 10.2 API endpoints
- Административные:
  - `GET/POST/PUT/DELETE /api/cases`
  - `GET/PUT /api/cases-block-settings`
  - `GET/PUT /api/case-card-settings`
- Публичные:
  - `GET /api/public/cases` - список кейсов
  - `GET /api/public/cases/{id}` - детали кейса
  - `GET /api/public/cases-block/settings` - настройки блока
  - `GET /api/public/case-card-settings` - настройки карточки

### 10.3 Функциональность
- Галерея изображений для кейсов
- Фильтрация кейсов по продуктам и услугам
- SEO оптимизация
- Связанные кейсы

---

## 11. Управление страницами

### 11.1 Структура
- Модель: `App\Models\Page`
- Поля: `title`, `slug`, `content` (JSON), `seo_title`, `seo_description`, `seo_keywords`, `is_active`
- Уникальность slug

### 11.2 API endpoints
- Административные:
  - `GET/POST/PUT/DELETE /api/pages`
  - `POST /api/pages/check-slug` - проверка уникальности slug
- Публичные:
  - `GET /api/public/pages/{slug}` - получить страницу по slug

### 11.3 Функциональность
- Визуальный редактор контента
- SEO оптимизация
- Проверка уникальности slug перед созданием

---

## 12. Блоки главной страницы

### 12.1 Структура
- Модель: `App\Models\HomePageBlock`
- Поля: `block_key`, `block_name`, `component_name`, `order`, `is_active`, `settings` (JSON)
- Блоки: HeroBanner, Decisions, Quiz, HowWork, Faq, WhyChooseUs, CasesBlock, FeedbackForm

### 12.2 API endpoints
- Административные:
  - `GET /api/home-page-blocks` - список блоков
  - `POST /api/home-page-blocks/update-order` - обновить порядок
  - `PUT /api/home-page-blocks/{id}` - обновить блок
- Публичные:
  - `GET /api/public/home-page-blocks` - получить активные блоки в порядке отображения

### 12.3 Функциональность
- Динамическое управление порядком блоков
- Включение/отключение блоков
- Настройки для каждого блока (JSON)

---

## 13. SEO оптимизация

### 13.1 Структура
- Модель: `App\Models\SeoSettings`
- Глобальные SEO настройки:
  - Название сайта, описание, ключевые слова
  - Данные организации (телефон, email, адрес)
  - Open Graph настройки
  - Настройки robots.txt
  - Schema.org разметка

### 13.2 API endpoints
- Административные: `GET/PUT /api/seo-settings`
- Публичные: `GET /api/public/seo-settings`

### 13.3 Функциональность
- Автоматическая генерация robots.txt (`/robots.txt`)
- Автоматическая генерация sitemap.xml (`/sitemap.xml`)
- SEO мета-теги для всех страниц
- Open Graph разметка
- Schema.org структурированные данные
- SEO поля для продуктов, услуг, кейсов, страниц

---

## 14. Заявки на продукты и услуги

### 14.1 Структура
- Модель: `App\Models\ProductRequest`
  - Поля: `name`, `phone`, `email`, `product_id`, `service_id`, `message`, `status`
- Модель: `App\Models\RequestHistory`
  - История изменений заявок

### 14.2 API endpoints
- Административные:
  - `GET/POST/PUT/DELETE /api/product-requests`
  - `GET /api/product-requests/stats` - статистика заявок
- Публичные:
  - `POST /api/public/leave-products` - оставить заявку на продукт
  - `POST /api/public/leave-services` - оставить заявку на услугу

### 14.3 Функциональность
- Отправка заявок с публичных страниц продуктов/услуг
- Уведомления администраторам
- История изменений статусов
- Статистика заявок

---

## 15. Форма обратной связи

### 15.1 Структура
- Модель: `App\Models\FeedbackRequest`
- Поля: `name`, `phone`, `email`, `message`, `status`

### 15.2 API endpoints
- Публичные: `POST /api/public/feedback` - отправить обратную связь

### 15.3 Функциональность
- Универсальная форма обратной связи
- Валидация данных
- Уведомления администраторам

---

## 16. Настройки модальных окон

### 16.1 Структура
- Модель: `App\Models\ModalSettings`
- Типы модальных окон с настройками

### 16.2 API endpoints
- Административные: `GET/POST/PUT/DELETE /api/modal-settings`
- Публичные: `GET /api/public/modal-settings/{type}` - получить настройки модального окна

---

## 17. Настройки контактов

### 17.1 Структура
- Модель: `App\Models\ContactSettings`
- Настройки страницы контактов

### 17.2 API endpoints
- Административные: `GET/PUT /api/contact-settings`
- Публичные: `GET /api/public/contact-settings`

---

## 18. Настройки страницы "О нас"

### 18.1 Структура
- Модель: `App\Models\AboutSettings`
- Настройки страницы "О нас"

### 18.2 API endpoints
- Административные: `GET/PUT /api/about-settings`
- Публичные: `GET /api/public/about-settings`

---

## 19. Настройки футера

### 19.1 Структура
- Модель: `App\Models\FooterSettings`
- Настройки футера сайта

### 19.2 API endpoints
- Административные: `GET/PUT /api/footer-settings`
- Публичные: `GET /api/public/footer/settings`

---

## 20. Интеграция с Telegram

### 20.1 Структура
- Модель: `App\Models\TelegramSettings`
  - Настройки бота: `bot_token`, `webhook_url`, `enabled`
- Модель: `App\Models\TelegramAdminRequest`
  - Заявки на администрирование через Telegram
- Пакет: `defstudio/telegraph`

### 20.2 API endpoints
- Административные:
  - `GET/PUT /api/telegram-settings` - настройки бота
  - `POST /api/telegram-settings/test` - тест подключения
  - `GET /api/telegram-settings/webhook-info` - информация о webhook
  - `GET /api/telegram-admin-requests` - список заявок
  - `POST /api/telegram-admin-requests/{id}/approve` - одобрить заявку
  - `POST /api/telegram-admin-requests/{id}/reject` - отклонить заявку
- Публичные:
  - `POST /api/telegram/webhook` - webhook от Telegram

### 20.3 Функциональность
- Команда `/admin` в Telegram боте для подачи заявки на администрирование
- Уведомления администраторам о новых заявках
- Одобрение/отклонение заявок через админ-панель
- Автоматическое связывание Telegram пользователя с аккаунтом в системе

---

## 21. Система деплоя

### 21.1 Команды Artisan
- `php artisan deploy` - автоматический деплой:
  - Сборка фронтенда (npm run build)
  - Git pull
  - Composer install
  - Миграции
  - Очистка кешей
  - Оптимизация приложения
- `php artisan log:clear` - очистка логов
- `php artisan user:create` - создание пользователя
- `php artisan migrate:legacy-data` - миграция данных из старого проекта

### 21.2 API endpoints для деплоя
- `POST /api/deploy` - выполнить деплой на сервере (защищен токеном)
- `POST /api/sync-sql-file` - синхронизация БД и файлов (защищен токеном)
- `GET /api/sync-check-requirements` - проверка требований для синхронизации

### 21.3 Функциональность деплоя
- Автоматическое обновление кода через git pull
- Установка зависимостей через composer
- Выполнение миграций
- Очистка временных файлов разработки
- Очистка и оптимизация кешей
- Синхронизация БД из SQL дампа
- Синхронизация медиа-файлов из ZIP архива
- Исключение системных таблиц при синхронизации БД

---

## 22. Административная панель

### 22.1 Структура меню
- Dashboard (главная)
- Решения (Chapters, Products, Services, Options, OptionTrees, Instances, Settings)
- Квизы (Quizzes, Settings)
- Кейсы (Cases, Settings)
- Страницы (Pages)
- Медиа (Media Library)
- Меню (Menus)
- Баннеры (Banners)
- Блоки главной страницы (Home Page Blocks)
- Настройки:
  - SEO настройки
  - Telegram настройки
  - Настройки контактов
  - Настройки "О нас"
  - Настройки футера
  - Настройки модальных окон
  - Настройки блоков (HowWork, FAQ, WhyChooseUs, Cases, CaseCard)
- Заявки (Product Requests)
- Уведомления (Notifications)
- Пользователи (Users)
- Роли (Roles)

### 22.2 Компоненты админ-панели
- `AdminLayout.vue` - основной layout
- `Sidebar.vue` - боковое меню
- `Header.vue` - шапка с уведомлениями
- `NotificationDropdown.vue` - выпадающий список уведомлений
- Формы для всех сущностей
- Модальные окна для создания/редактирования
- Drag & Drop для сортировки

---

## 23. Публичная часть

### 23.1 Пользовательские страницы

#### Главная страница (`/`)
- **Компонент:** `Home.vue`
- **Описание:** Динамическая главная страница с настраиваемыми блоками
- **Функциональность:**
  - Загрузка блоков из API (`/api/public/home-page-blocks`)
  - Динамическое отображение блоков в порядке, заданном в админ-панели
  - Поддерживаемые блоки: HeroBanner, Decisions, Quiz, HowWork, Faq, WhyChooseUs, CasesBlock, FeedbackForm
  - SEO оптимизация с использованием глобальных настроек
  - Schema.org разметка (WebSite, Organization)
  - Автоматическая установка дефолтных SEO значений при отсутствии настроек
- **Маршрут:** `/`

#### Страница каталога продуктов (`/products`)
- **Компонент:** `ProductsPage.vue`
- **Описание:** Каталог всех активных продуктов и услуг
- **Функциональность:**
  - Отображение продуктов и услуг в отдельных секциях
  - Сетка карточек (2 колонки на мобильных, 4 на десктопе)
  - Хлебные крошки
  - Форма обратной связи внизу страницы
  - SEO оптимизация с BreadcrumbList schema
- **API:** `GET /api/public/products?active=1`, `GET /api/public/services?active=1`
- **Маршрут:** `/products`

#### Страница продукта (`/products/:slug`)
- **Компонент:** `ProductPage.vue`
- **Описание:** Детальная страница продукта с формой заявки
- **Функциональность:**
  - Отображение информации о продукте (название, описание, изображение)
  - Многоэтапная форма заявки:
    - Этап 1: Выбор услуг (OptionsStage) - выбор связанных услуг продукта
    - Этап 2: Заполнение формы (FormsStage) - имя, телефон, комментарий
    - Этап 3: Успешная отправка (SuccessStage)
  - Счетчик выбранных услуг с правильным склонением
  - Плавный скролл к форме при переходе между этапами
  - Модальное окно с информацией о продукте (из настроек модальных окон)
  - Список других продуктов и услуг внизу страницы
  - Кеширование продукта для быстрой загрузки
  - Хлебные крошки
  - SEO оптимизация с Product schema и BreadcrumbList
  - Отправка заявки через API (`POST /api/public/leave-products`)
- **API:** `GET /api/public/products/{slug}`, `POST /api/public/leave-products`
- **Маршрут:** `/products/:slug`

#### Страница каталога услуг (`/services`)
- **Компонент:** `ServicesPage.vue`
- **Описание:** Каталог всех активных услуг и продуктов
- **Функциональность:**
  - Отображение услуг и продуктов в отдельных секциях (порядок обратный ProductsPage)
  - Сетка карточек (2 колонки на мобильных, 4 на десктопе)
  - Хлебные крошки
  - Форма обратной связи внизу страницы
  - SEO оптимизация с BreadcrumbList schema
- **API:** `GET /api/public/services?active=1`, `GET /api/public/products?active=1`
- **Маршрут:** `/services`

#### Страница услуги (`/services/:slug`)
- **Компонент:** `ServicePage.vue`
- **Описание:** Детальная страница услуги с формой заявки
- **Функциональность:**
  - Отображение информации об услуге (название, описание, изображение)
  - Многоэтапная форма заявки:
    - Этап 1: Выбор параметров (ServiceOptionsStage) - выбор категории заявителя, дерева опций, экземпляра
    - Этап 2: Заполнение формы (ServiceFormsStage) - имя, телефон, комментарий
    - Этап 3: Успешная отправка (ServiceSuccessStage)
  - Валидация выбора всех обязательных параметров
  - Плавный скролл к форме при переходе между этапами
  - Модальное окно с информацией об услуге (из настроек модальных окон)
  - Список других услуг внизу страницы
  - Хлебные крошки
  - SEO оптимизация с Service schema и BreadcrumbList
  - Отправка заявки через API (`POST /api/public/leave-services`)
- **API:** `GET /api/public/services/{slug}`, `POST /api/public/leave-services`
- **Маршрут:** `/services/:slug`

#### Страница каталога кейсов (`/cases`)
- **Компонент:** `CasesPage.vue`
- **Описание:** Каталог всех кейсов с фильтрацией и пагинацией
- **Функциональность:**
  - Отображение кейсов в сетке (1 колонка на мобильных, 2 на десктопе)
  - Модальное окно фильтров (CaseFiltersModal) - фильтрация по продуктам и услугам
  - Пагинация (6 кейсов на страницу)
  - Состояния загрузки и ошибок
  - SEO оптимизация с CollectionPage schema
- **API:** `GET /api/public/cases`
- **Маршрут:** `/cases`

#### Страница кейса (`/cases/:slug`)
- **Компонент:** `CasePage.vue`
- **Описание:** Детальная страница кейса с галереей изображений
- **Функциональность:**
  - Отображение информации о кейсе (название, описание, HTML контент)
  - Hero-секция с первыми 2 изображениями (сетка 1x2 или 2x1)
  - HTML контент с форматированием (prose стили)
  - Галерея изображений с каруселью (CaseImageCarousel)
  - Компонент похожих кейсов (SimilarCases) - кейсы из того же раздела
  - Функция поделиться (Web Share API или копирование в буфер)
  - Дата публикации
  - Хлебные крошки
  - SEO оптимизация с Article schema и BreadcrumbList
  - Обработка ошибок загрузки изображений
- **API:** `GET /api/public/cases/{slug}`
- **Маршрут:** `/cases/:slug`

#### Страница "О нас" (`/about`)
- **Компонент:** `AboutPage.vue`
- **Описание:** Информационная страница о компании
- **Функциональность:**
  - Баннер с изображением (из настроек)
  - Описание компании (HTML контент)
  - Статистика компании (иконки и текст)
  - Виды услуг (сетка карточек услуг)
  - Продуктовые решения (сетка карточек продуктов)
  - Секция "Кому мы помогаем" (карточки клиентов)
  - Компонент "Как мы работаем" (HowWork)
  - Компонент FAQ (Faq)
  - Секция "Наша команда" (фото и должности)
  - Секция "Почему выбирают нас" (преимущества)
  - Форма обратной связи
  - Хлебные крошки
  - SEO оптимизация с AboutPage schema
- **API:** `GET /api/public/about-settings`, `GET /api/public/services?active=1`, `GET /api/public/products?active=1`
- **Маршрут:** `/about`

#### Страница контактов (`/contacts`)
- **Компонент:** `ContactPage.vue`
- **Описание:** Страница с контактной информацией и формой обратной связи
- **Функциональность:**
  - Блок контактов (телефон, email, адрес, время работы)
  - Кликабельные ссылки (tel:, mailto:)
  - Социальные сети с иконками (VK, Instagram, Telegram)
  - Форма обратной связи (FeedbackForm)
  - Хлебные крошки
  - SEO оптимизация с ContactPage schema
- **API:** `GET /api/public/contact-settings`
- **Маршрут:** `/contacts`

#### Динамические страницы (`/:slug`)
- **Компонент:** `Page.vue`
- **Описание:** Универсальная страница для пользовательского контента
- **Функциональность:**
  - Загрузка страницы по slug из API
  - Отображение заголовка и HTML контента
  - Проверка зарезервированных путей (admin, login, register и т.д.)
  - Автоматическое перенаправление на 404 для несуществующих страниц
  - SEO оптимизация с WebPage schema
  - Установка SEO мета-тегов (title, description, keywords)
  - Стилизация контента (prose стили для HTML)
- **API:** `GET /api/public/pages/{slug}`
- **Маршрут:** `/:slug` (catch-all для всех не зарезервированных путей)

#### Страницы ошибок
- **404 Not Found:** `NotFound404.vue` - `/404`
- **403 Forbidden:** `Forbidden403.vue` - `/403`
- **500 Server Error:** `ServerError500.vue` - `/500`

### 23.2 Компоненты
- `PublicLayout.vue` - основной layout
- `PublicHeader.vue` - шапка сайта
- `Footer.vue` - футер
- `BurgerMenu.vue` - мобильное меню
- `HeroBanner.vue` - главный баннер
- `Decisions.vue` - блок решений
- `Quiz.vue` - блок квиза
- `HowWork.vue` - блок "Как это работает"
- `Faq.vue` - блок FAQ
- `WhyChooseUs.vue` - блок "Почему выбирают нас"
- `CasesBlock.vue` - блок кейсов
- `FeedbackForm.vue` - форма обратной связи
- `ProductCard.vue`, `ServiceCard.vue`, `CaseCard.vue` - карточки
- `SearchModal.vue` - модальное окно поиска
- `SEOHead.vue` - компонент для SEO мета-тегов

### 23.3 Функциональность
- Адаптивный дизайн
- Ленивая загрузка изображений (`LazyImage.vue`)
- Кеширование данных продуктов
- SEO оптимизация всех страниц
- Формы заявок на продукты/услуги
- Форма обратной связи

---

## 24. Система кеширования

### 24.1 Кеширование данных
- Кеширование продуктов (автоматическая очистка при изменении)
- Кеширование настроек блоков
- Кеширование меню
- Кеширование SEO настроек

### 24.2 Очистка кеша
- Автоматическая очистка при изменении моделей
- Команды: `cache:clear`, `config:clear`, `route:clear`, `view:clear`, `optimize:clear`
- Очистка через админ-панель

---

## 25. Импорт/Экспорт данных

### 25.1 Экспорт
- Экспорт продуктов (`ProductsExport.php`)
- Экспорт услуг (`ServicesExport.php`)
- Формат: Excel/JSON

### 25.2 Импорт
- Импорт продуктов (`ProductsImport.php`)
- Импорт услуг (`ServicesImport.php`)
- Поддержка изображений при импорте
- Валидация данных

---

## 26. Система сидеров (Seeders)

### 26.1 Основные сидеры
- `RoleSeeder` - роли пользователей
- `MenuSeeder` - меню по умолчанию
- `AppCategorySeeder` - категории заявителя
- `QuizImagesSeeder` - изображения для квиза
- `QuizSeeder` - квизы
- `HowWorkBlockSettingsSeeder` - настройки блока "Как это работает"
- `CasesSeeder` - кейсы
- `FaqBlockSettingsSeeder` - настройки FAQ
- `WhyChooseUsBlockSettingsSeeder` - настройки "Почему выбирают нас"
- `CasesBlockSettingsSeeder` - настройки блока кейсов
- `HomePageBlocksSeeder` - блоки главной страницы
- `CopyMediaFilesSeeder` - копирование медиа-файлов
- `ProductsServicesOptionsCasesSeeder` - продукты, услуги, опции, кейсы
- `ImportProductsServicesSeeder` - импорт из JSON
- `AboutSettingsSeeder` - настройки "О нас"
- `ContactSettingsSeeder` - настройки контактов
- `FooterSettingsSeeder` - настройки футера
- `RegisterAllMediaFilesSeeder` - регистрация медиа-файлов
- `UpdateMediaFolderSeeder` - обновление папок медиа

---

## 27. Middleware

### 27.1 Аутентификация
- `auth:sanctum` - проверка токена аутентификации
- `admin` - проверка прав администратора

### 27.2 Деплой
- `deploy.token` - проверка токена для деплоя

---

## 28. Фильтры и валидация

### 28.1 Request классы
- Валидация данных при создании/обновлении сущностей
- Кастомные правила валидации

### 28.2 Фильтры
- Фильтрация данных в API
- Поиск и сортировка

---

## 29. Сервисы

### 29.1 Сервисные классы
- `AdminMenu` - генерация меню админ-панели
- `TelegramService` - работа с Telegram API
- `NotificationTool` - работа с уведомлениями
- `EmailHelper` - отправка email

---

## 30. Композаблы (Composables)

### 30.1 Vue композаблы
- `useAuthToken.js` - работа с токеном аутентификации
- `useDebounce.js` - debounce для поиска
- `usePreloader.js` - прелоадер страниц
- `useProductCache.js` - кеширование продуктов

---

## 31. Утилиты

### 31.1 API утилиты
- `api.js` - базовые функции для работы с API
- Обработка ошибок
- Интерцепторы запросов

---

## 32. Обработка ошибок

### 32.1 Страницы ошибок
- `NotFound404.vue` - страница 404
- `Forbidden403.vue` - страница 403
- `ServerError500.vue` - страница 500

### 32.2 Обработка ошибок API
- Централизованная обработка ошибок
- Логирование ошибок

---

## 33. Безопасность

### 33.1 Защита API
- Токены аутентификации (Sanctum)
- Middleware для проверки прав
- Валидация входных данных
- Защита от CSRF

### 33.2 Защита файлов
- Проверка типов файлов
- Ограничение размера файлов
- Безопасное хранение файлов

---

## 34. Производительность

### 34.1 Оптимизация
- Кеширование запросов
- Ленивая загрузка изображений
- Оптимизация запросов к БД (eager loading)
- Минификация и сжатие ресурсов

### 34.2 Мониторинг
- Логирование важных событий
- Отслеживание производительности

---

## 35. Документация

### 35.1 Документы проекта
- `ARTISAN_COMMANDS.md` - справочник команд
- `ADMIN_DOCUMENTATION_UPDATE.md` - документация админ-панели
- `FEEDBACK_FORM_USAGE.md` - использование формы обратной связи
- `IMPORT_EXPORT_WITH_IMAGES_OPTIONS.md` - импорт/экспорт с изображениями
- `MIGRATION_README.md` - миграция данных
- `SEO_SETUP_INSTRUCTIONS.md` - настройка SEO
- `ZIP_IMPORT_GUIDE.md` - руководство по импорту ZIP
- И другие документы по настройке и развертыванию

---

## 36. Особенности реализации

### 36.1 Архитектурные решения
- SPA архитектура с Vue Router
- RESTful API
- Разделение на публичную и административную части
- Модульная структура компонентов
- Использование композаблов для переиспользования логики

### 36.2 Паттерны проектирования
- Repository pattern (частично)
- Service layer для бизнес-логики
- Resource classes для форматирования API ответов
- Middleware для обработки запросов

### 36.3 Технические детали
- Мягкое удаление (soft delete) для важных сущностей
- Автоматическая очистка кеша при изменении данных
- Валидация slug для уникальности
- Поддержка JSON полей для многоязычности
- Иерархические структуры (меню, папки, опции)

---

## Использование документа для формирования промптов

Этот документ служит справочником для:
1. **Понимания структуры проекта** - какие модели, контроллеры, компоненты существуют
2. **Добавления нового функционала** - понимание где и как добавлять новые возможности
3. **Изменения существующего кода** - знание текущей реализации для безопасных изменений
4. **Исправления ошибок** - понимание связей между компонентами
5. **Рефакторинга** - знание архитектуры для улучшения кода

### Примеры промптов:

**Добавление новой функции:**
"Добавить возможность отправки email уведомлений при создании заявки на продукт. Используй существующий EmailHelper и модель ProductRequest."

**Изменение существующей функции:**
"Изменить блок 'Решения' чтобы добавить фильтрацию продуктов по цене. Используй существующую модель Product и контроллер ChapterController."

**Исправление ошибки:**
"Исправить ошибку при импорте продуктов - не загружаются изображения. Проверь ProductsImport и связь с Media моделью."

---

**Последнее обновление:** 2025-01-29

