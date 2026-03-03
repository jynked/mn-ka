# Справочник Artisan команд

Этот документ содержит описание всех доступных Artisan команд в проекте, включая стандартные команды Laravel и кастомные команды проекта.

---

## Содержание

- [Кастомные команды проекта](#кастомные-команды-проекта)
  - [deploy](#deploy)
  - [log:clear](#logclear)
  - [user:create](#usercreate)
  - [migrate:legacy-data](#migratelegacy-data)
- [Стандартные команды Laravel](#стандартные-команды-laravel)
  - [Миграции](#миграции)
  - [Кеш](#кеш)
  - [Конфигурация](#конфигурация)
  - [База данных](#база-данных)
  - [Тестирование](#тестирование)

---

## Кастомные команды проекта

### deploy

**Описание:** Деплой проекта: сборка, коммит в git, отправка на сервер

**Использование:**
```bash
php artisan deploy [опции]
```

**Опции:**
- `--message=MESSAGE` - Кастомное сообщение для коммита
- `--skip-build` - Пропустить npm run build
- `--dry-run` - Показать что будет сделано без выполнения
- `--insecure` - Отключить проверку SSL сертификата (для разработки)

**Примеры:**
```bash
# Обычный деплой
php artisan deploy

# Деплой с кастомным сообщением
php artisan deploy --message="Добавлен новый функционал"

# Деплой без сборки фронтенда
php artisan deploy --skip-build

# Просмотр что будет сделано (без выполнения)
php artisan deploy --dry-run

# Деплой с отключенной проверкой SSL (для разработки)
php artisan deploy --insecure
```

**Что делает:**
1. Выполняет `npm run build` (если не указан `--skip-build`)
2. Проверяет изменения в git
3. Добавляет изменения в git
4. Создает коммит
5. Отправляет изменения в репозиторий
6. Отправляет POST запрос на сервер для обновления

**Требования:**
- Настроенные переменные в `.env`: `SERVER_URL`, `DEPLOY_TOKEN`
- Настроенный git репозиторий

**Подробнее:** См. `DEPLOY_NEXT_STEPS.md`

---

### log:clear

**Описание:** Очистить файлы логов Laravel

**Использование:**
```bash
php artisan log:clear [опции]
```

**Опции:**
- `--all` - Очистить все файлы логов
- `--file=FILE` - Очистить конкретный файл лога (например: laravel.log)
- `--empty` - Очистить только пустые файлы логов
- `--days=DAYS` - Удалить логи старше указанного количества дней
- `--confirm` - Пропустить подтверждение

**Примеры:**
```bash
# Очистить основной файл лога (laravel.log)
php artisan log:clear

# Очистить все логи
php artisan log:clear --all

# Очистить конкретный файл
php artisan log:clear --file=laravel.log

# Удалить пустые файлы
php artisan log:clear --empty

# Удалить логи старше 7 дней
php artisan log:clear --days=7

# Очистить все логи без подтверждения
php artisan log:clear --all --confirm
```

**Примечание:**
- По умолчанию очищается `laravel.log`
- Очистка файла не удаляет его, а очищает содержимое
- Опции `--empty` и `--days` физически удаляют файлы

**Подробнее:** См. `LOG_CLEAR_COMMAND.md`

---

### user:create

**Описание:** Создать нового пользователя

**Использование:**
```bash
php artisan user:create [опции]
```

**Опции:**
- `--email=EMAIL` - Email пользователя
- `--password=PASSWORD` - Пароль пользователя
- `--name=NAME` - Имя пользователя
- `--roles=ROLES` - Роли пользователя (slug через запятую, multiple values)

**Примеры:**
```bash
# Создать пользователя с настройками по умолчанию
php artisan user:create

# Создать пользователя с указанными параметрами
php artisan user:create --email="user@example.com" --password="password123" --name="Иван Иванов"

# Создать пользователя с ролями
php artisan user:create --email="admin@example.com" --roles=admin,manager
```

**Значения по умолчанию** (если не указаны):
- Email: `dsc-23@yandex.ru`
- Password: `123123123`
- Name: `Джон Уик`
- Roles: Все доступные роли (администратор)

**Примечание:**
- Если пользователь с указанным email уже существует, будет предложено обновить его
- Если роли не указаны, присваиваются все доступные роли

---

### migrate:legacy-data

**Описание:** Миграция данных из старого проекта (chapters, products, services, media files)

**Использование:**
```bash
php artisan migrate:legacy-data [опции]
```

**Опции:**
- `--legacy-path=PATH` - Путь к директории старого проекта
- `--legacy-db-host=HOST` - Хост базы данных старого проекта (по умолчанию: 127.0.0.1)
- `--legacy-db-port=PORT` - Порт базы данных (по умолчанию: 3306)
- `--legacy-db-name=NAME` - Имя базы данных старого проекта
- `--legacy-db-user=USER` - Пользователь базы данных (по умолчанию: root)
- `--legacy-db-pass=PASS` - Пароль базы данных

**Примеры:**
```bash
# Миграция с указанием пути к старому проекту
php artisan migrate:legacy-data --legacy-path="C:\OSPanel\domains\lagom"

# Миграция с полными параметрами БД
php artisan migrate:legacy-data \
  --legacy-path="C:\OSPanel\domains\lagom" \
  --legacy-db-host="127.0.0.1" \
  --legacy-db-port="3306" \
  --legacy-db-name="lagom_old" \
  --legacy-db-user="root" \
  --legacy-db-pass="password"
```

**Что мигрирует:**
1. Медиа файлы (изображения)
2. Разделы (Chapters)
3. Продукты (Products)
4. Услуги (Services)
5. Связи между продуктами и услугами

**Примечание:**
- По умолчанию путь к старому проекту: `C:\OSPanel\domains\lagom`
- Команда создает маппинг ID для корректной миграции связей
- Предотвращает дублирование данных
- Файлы копируются в медиа библиотеку

**Подробнее:** См. `MIGRATION_README.md`

---

## Стандартные команды Laravel

### Миграции

#### migrate

**Описание:** Выполнить миграции базы данных

```bash
php artisan migrate
```

**Полезные опции:**
- `--force` - Выполнить миграции в production без подтверждения
- `--pretend` - Показать SQL запросы без выполнения
- `--seed` - Выполнить сидеры после миграции

#### migrate:rollback

**Описание:** Откатить последнюю миграцию

```bash
php artisan migrate:rollback
```

#### migrate:refresh

**Описание:** Откатить все миграции и выполнить заново

```bash
php artisan migrate:refresh
```

#### migrate:reset

**Описание:** Откатить все миграции

```bash
php artisan migrate:reset
```

#### migrate:status

**Описание:** Показать статус миграций

```bash
php artisan migrate:status
```

---

### Кеш

#### cache:clear

**Описание:** Очистить кеш приложения

```bash
php artisan cache:clear
```

#### config:cache

**Описание:** Создать кеш конфигурации для ускорения загрузки

```bash
php artisan config:cache
```

#### config:clear

**Описание:** Очистить кеш конфигурации

```bash
php artisan config:clear
```

#### route:cache

**Описание:** Создать кеш маршрутов

```bash
php artisan route:cache
```

#### route:clear

**Описание:** Очистить кеш маршрутов

```bash
php artisan route:clear
```

#### view:cache

**Описание:** Скомпилировать все Blade шаблоны

```bash
php artisan view:cache
```

#### view:clear

**Описание:** Очистить скомпилированные представления

```bash
php artisan view:clear
```

#### optimize:clear

**Описание:** Очистить все кеши (config, route, view, cache)

```bash
php artisan optimize:clear
```

---

### Конфигурация

#### config:show

**Описание:** Показать значения конфигурации

```bash
php artisan config:show app
php artisan config:show database.connections.mysql
```

---

### База данных

#### db:seed

**Описание:** Выполнить сидеры базы данных

```bash
php artisan db:seed
php artisan db:seed --class=MenuSeeder
```

#### db:wipe

**Описание:** Удалить все таблицы, представления и типы

```bash
php artisan db:wipe
```

**⚠️ Внимание:** Эта команда удаляет все данные!

---

### Тестирование

#### test

**Описание:** Запустить тесты приложения

```bash
php artisan test
php artisan test --filter=ExampleTest
```

---

### Разработка

#### serve

**Описание:** Запустить встроенный PHP сервер разработки

```bash
php artisan serve
php artisan serve --host=0.0.0.0 --port=8000
```

#### tinker

**Описание:** Интерактивная консоль Laravel

```bash
php artisan tinker
```

**Примеры использования:**
```php
// В tinker
User::count()
User::first()
$user = User::find(1)
$user->name = "New Name"
$user->save()
```

---

### Очереди

#### queue:work

**Описание:** Обработать задачи из очереди

```bash
php artisan queue:work
```

**Полезные опции:**
- `--queue=NAME` - Указать очередь
- `--tries=3` - Количество попыток
- `--timeout=60` - Таймаут в секундах

#### queue:listen

**Описание:** Слушать очередь (рестарт после каждого задания)

```bash
php artisan queue:listen
```

#### queue:failed

**Описание:** Показать неудачные задачи

```bash
php artisan queue:failed
```

#### queue:retry

**Описание:** Повторить неудачную задачу

```bash
php artisan queue:retry all
php artisan queue:retry 5
```

---

### Обслуживание

#### down

**Описание:** Перевести приложение в режим обслуживания

```bash
php artisan down
php artisan down --message="Обновление сервера" --retry=60
```

#### up

**Описание:** Вывести приложение из режима обслуживания

```bash
php artisan up
```

---

### Логи

#### pail

**Описание:** Просмотр логов в реальном времени

```bash
php artisan pail
php artisan pail --filter=error
```

---

## Полезные комбинации команд

### Очистка всех кешей

```bash
php artisan optimize:clear
```

### Обновление конфигурации после изменений

```bash
php artisan config:clear
php artisan config:cache
```

### Полный сброс и пересоздание базы данных

```bash
php artisan migrate:fresh --seed
```

### Обновление после изменений в коде

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Получение справки

Для получения справки по любой команде используйте:

```bash
php artisan help [команда]
# или
php artisan [команда] --help
```

Пример:
```bash
php artisan help deploy
php artisan deploy --help
```

---

## Полезные опции для всех команд

- `-h, --help` - Показать справку
- `-q, --quiet` - Только ошибки (минимум вывода)
- `-v, --verbose` - Подробный вывод (1, 2 или 3 уровня)
- `-n, --no-interaction` - Не задавать вопросы (для автоматизации)
- `--env=ENV` - Указать окружение

---

## Автоматизация

### Примеры cron задач

```bash
# Очистка кеша каждый день в 3:00
0 3 * * * cd /path/to/project && php artisan optimize:clear

# Очистка старых логов каждую неделю
0 2 * * 0 cd /path/to/project && php artisan log:clear --days=30 --confirm

# Обработка очередей
* * * * * cd /path/to/project && php artisan schedule:run
```

---

## Дополнительная документация

- **Деплой:** `DEPLOY_NEXT_STEPS.md`, `SERVER_SETUP.md`
- **Очистка логов:** `LOG_CLEAR_COMMAND.md`
- **Миграция данных:** `MIGRATION_README.md`

---

**Последнее обновление:** 2025-01-29

