# Команда установки проекта: project:install

## Описание

Единая команда для полной установки проекта на новом сервере. Выполняет по порядку:
1. Миграции базы данных
2. Импорт SQL файла с данными
3. Seeders (опционально)
4. Создание администратора
5. Очистка кеша

## Использование

### Базовое использование

```bash
# Полная установка с файлом dsc23ytp_lag_crm.sql в корне проекта
php artisan project:install

# С указанием пути к SQL файлу
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql

# Или полный путь
php artisan project:install --sql-file=C:\OSPanel\domains\mnka\dsc23ytp_lag_crm.sql

# Без подтверждений (для автоматизации)
php artisan project:install --force
```

### Опции

- `--sql-file=ПУТЬ` - Путь к SQL файлу для импорта (по умолчанию: `dsc23ytp_lag_crm.sql`)
- `--skip-migrate` - Пропустить выполнение миграций
- `--skip-seed` - Пропустить выполнение seeders
- `--skip-import` - Пропустить импорт SQL файла
- `--skip-user` - Пропустить создание пользователя
- `--force` - Пропустить все подтверждения

### Примеры

```bash
# Только миграции и импорт SQL (без seeders и пользователя)
php artisan project:install --skip-seed --skip-user

# Только миграции
php artisan project:install --skip-import --skip-seed --skip-user

# Полная установка без подтверждений
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql --force
```

## Проверка миграций перед установкой

### Шаг 1: Проверьте структуру миграций

```bash
# Проверьте список всех миграций
php artisan migrate:status

# Проверьте конкретную миграцию
php artisan migrate:status | grep products
php artisan migrate:status | grep cases
```

### Шаг 2: Проверьте SQL файл

```bash
# Проверьте наличие проблемных foreign keys
grep -i "FOREIGN KEY" dsc23ytp_lag_crm.sql | head -10

# Проверьте наличие таблиц products и cases
grep -i "CREATE TABLE.*products" dsc23ytp_lag_crm.sql
grep -i "CREATE TABLE.*cases" dsc23ytp_lag_crm.sql

# Проверьте размер файла
ls -lh dsc23ytp_lag_crm.sql
```

### Шаг 3: Проверьте настройки .env

Перед установкой убедитесь, что `.env` настроен правильно:

```bash
# Проверьте настройки базы данных
php artisan tinker --execute="echo 'DB: ' . env('DB_DATABASE');"
php artisan tinker --execute="echo 'DB User: ' . env('DB_USERNAME');"
```

## Пошаговая установка на новом сервере

### 1. Подготовка

```bash
# Убедитесь, что вы в директории проекта
cd /path/to/project

# Проверьте .env файл
nano .env

# Установите зависимости (если еще не установлены)
composer install --no-dev --optimize-autoloader
```

### 2. Проверка SQL файла

```bash
# Убедитесь, что SQL файл доступен
ls -lh dsc23ytp_lag_crm.sql

# Или проверьте в корне проекта
find . -name "dsc23ytp_lag_crm.sql" -type f
```

### 3. Выполнение установки

```bash
# Полная установка
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql

# Или если файл в корне проекта
php artisan project:install
```

### 4. После установки

```bash
# Проверьте права доступа
chmod -R 775 storage bootstrap/cache

# Проверьте статус миграций
php artisan migrate:status

# Проверьте данные в базе
php artisan tinker --execute="echo 'Users: ' . DB::table('users')->count();"
php artisan tinker --execute="echo 'Services: ' . DB::table('services')->count();"
```

## Проверка после установки

### Проверка таблиц

```bash
# Проверьте список таблиц
php artisan tinker --execute="echo implode(PHP_EOL, DB::select('SHOW TABLES'));"

# Или через MySQL
mysql -u user -p database -e "SHOW TABLES;"
```

### Проверка данных

```bash
# Количество записей в основных таблицах
mysql -u user -p database -e "
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL SELECT 'services', COUNT(*) FROM services
UNION ALL SELECT 'products', COUNT(*) FROM products
UNION ALL SELECT 'cases', COUNT(*) FROM cases
UNION ALL SELECT 'media', COUNT(*) FROM media;
"
```

### Проверка пользователя

```bash
# Проверьте созданного пользователя
php artisan tinker --execute="
\$user = App\Models\User::first();
if (\$user) {
    echo 'User: ' . \$user->email . PHP_EOL;
    echo 'Roles: ' . \$user->roles->pluck('name')->implode(', ');
}
"
```

## Возможные проблемы

### Проблема: SQL файл не найден

**Решение:**
```bash
# Укажите полный путь
php artisan project:install --sql-file=/полный/путь/к/dsc23ytp_lag_crm.sql

# Или скопируйте файл в корень проекта
cp /путь/к/dsc23ytp_lag_crm.sql ./
php artisan project:install
```

### Проблема: Ошибки foreign keys при импорте

**Решение:** Команда автоматически использует `--skip-fk` при импорте SQL. Если проблемы остаются, проверьте SQL файл вручную.

### Проблема: Seeders конфликтуют с импортированными данными

**Решение:** Используйте `--skip-seed` если данные уже импортированы из SQL:

```bash
php artisan project:install --skip-seed
```

### Проблема: Пользователь не создался

**Решение:** Создайте вручную:

```bash
php artisan user:create --email=admin@example.com --password=password123
```

## Для переноса на reg.ru

### Шаг 1: На локальной машине (проверка)

```bash
cd C:\OSPanel\domains\mnka

# Проверьте миграции
php artisan migrate:status

# Проверьте SQL файл
ls -lh dsc23ytp_lag_crm.sql

# Проверьте установку локально (на тестовой БД)
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql
```

### Шаг 2: Перенос на reg.ru

```bash
# Загрузите все файлы на reg.ru
# Затем на reg.ru выполните:

cd ~/mn-ka.ru

# Настройте .env
nano .env

# Выполните установку
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql --force

# Настройте права
chmod -R 775 storage bootstrap/cache
```

## Логи установки

Команда выводит подробную информацию о каждом шаге:
- ✅ Успешное выполнение
- ❌ Ошибки
- ⚠️ Предупреждения
- ⏭️ Пропущенные шаги

Все ошибки логируются в `storage/logs/laravel.log`.

