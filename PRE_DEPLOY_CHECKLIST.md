# Чек-лист проверки перед переносом на reg.ru

## Быстрая проверка командой

```bash
cd C:\OSPanel\domains\mnka
php artisan project:check
```

## Ручная проверка

### 1. Проверка миграций ✅

```bash
# Проверьте список миграций
php artisan migrate:status

# Проверьте синтаксис миграций
php artisan migrate:pretend

# Убедитесь, что миграция add_protected_to_folders_table исправлена
grep -A 5 "Schema::hasColumn" database/migrations/2025_11_08_171010_add_protected_to_folders_table.php
```

**Ожидаемый результат:**
- Все миграции должны быть в порядке
- Миграция `2025_11_08_171010_add_protected_to_folders_table` должна содержать проверку `Schema::hasColumn`

### 2. Проверка SQL файла ✅

```bash
# Проверьте наличие файла
ls -lh dsc23ytp_lag_crm.sql
# или в Windows
dir dsc23ytp_lag_crm.sql

# Проверьте размер (должен быть несколько МБ)
# Проверьте, что файл содержит CREATE TABLE
findstr /C:"CREATE TABLE" dsc23ytp_lag_crm.sql | find /C "CREATE TABLE"
```

**Ожидаемый результат:**
- Файл должен существовать в корне проекта
- Размер обычно 5-10 МБ или больше
- Должен содержать CREATE TABLE и INSERT INTO команды

### 3. Проверка Artisan команд ✅

```bash
# Проверьте, что команды доступны
php artisan list | findstr "project:install"
php artisan list | findstr "db:import-sql"
php artisan list | findstr "user:create"
```

**Ожидаемый результат:**
- Все три команды должны быть в списке

### 4. Проверка конфигурации ✅

```bash
# Проверьте наличие .env.example
dir .env.example

# Проверьте composer.json
dir composer.json

# Проверьте структуру проекта
dir app\Console\Commands
dir database\migrations
dir database\seeders
```

**Ожидаемый результат:**
- Все необходимые файлы и директории должны существовать

### 5. Проверка зависимостей ✅

```bash
# Проверьте composer.lock
dir composer.lock

# Проверьте package.json (если используется)
dir package.json
```

## Финальная проверка перед отправкой

### Файлы для переноса:

- [x] Весь код проекта (app/, config/, database/, resources/, routes/, public/, и т.д.)
- [x] Файл `.env.example` (для настройки на сервере)
- [x] `composer.json` и `composer.lock`
- [x] SQL файл `dsc23ytp_lag_crm.sql`
- [x] `artisan` файл

### Файлы, которые НЕ нужно переносить:

- [ ] `vendor/` - установится заново через `composer install`
- [ ] `node_modules/` - установится заново через `npm install`
- [ ] `.env` - создастся из `.env.example` на сервере
- [ ] `storage/logs/*.log` - логи не нужны
- [ ] `.git/` - если не используете Git на сервере

### Команды для подготовки:

```bash
# Создайте архив без ненужных папок (опционально)
# В Windows PowerShell:
Compress-Archive -Path app,bootstrap,config,database,public,resources,routes,storage,artisan,composer.json,composer.lock,.env.example,dsc23ytp_lag_crm.sql -DestinationPath project.zip -CompressionLevel Optimal

# Или просто скопируйте все файлы (кроме vendor, node_modules, .git)
```

## На сервере reg.ru после переноса

### 1. Настройка .env

```bash
cd ~/mn-ka.ru
cp .env.example .env
nano .env
# Измените настройки БД на данные reg.ru
```

### 2. Выполнение установки

```bash
# Установите зависимости
composer install --no-dev --optimize-autoloader

# Выполните установку проекта
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql --force

# Настройте права
chmod -R 775 storage bootstrap/cache
chmod -R 755 .
```

### 3. Проверка

```bash
# Проверьте статус миграций
php artisan migrate:status

# Проверьте данные
php artisan tinker --execute="echo 'Tables: ' . count(DB::select('SHOW TABLES'));"
```

## Известные проблемы и решения

### Проблема: SQL файл не найден при установке

**Решение:**
- Убедитесь, что файл `dsc23ytp_lag_crm.sql` находится в корне проекта
- Или укажите полный путь: `--sql-file=/полный/путь/dsc23ytp_lag_crm.sql`

### Проблема: Foreign keys в SQL файле

**Решение:**
- Команда `db:import-sql` автоматически использует `--skip-fk`
- Foreign keys будут созданы миграциями Laravel после импорта

### Проблема: Таблицы products и cases не создаются

**Решение:**
- Таблицы создаются миграциями Laravel
- Если их нет после импорта SQL, они будут созданы миграциями

