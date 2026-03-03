# Установка проекта на reg.ru - Пошаговая инструкция

## ✅ Выполнено

1. ✅ Проект клонирован из GitHub
2. ✅ .env файл настроен с правильными данными БД

## Следующие шаги

### Шаг 1: Установите зависимости Composer

```bash
cd ~/mn-ka.ru

# Установите зависимости
composer install --no-dev --optimize-autoloader
```

### Шаг 2: Проверьте APP_KEY

```bash
# Проверьте, что APP_KEY в .env сгенерирован
# Если нужно, сгенерируйте заново:
php artisan key:generate
```

### Шаг 3: Перенесите SQL файл

SQL файл `dsc23ytp_lag_crm.sql` нужно перенести отдельно:

**Вариант A: С beget через SCP**
```bash
# С beget или локальной машины
scp user@beget.ru:~/stroy/public_html/dsc23ytp_lag_crm.sql user@reg.ru:~/
```

**Вариант B: Загрузите через панель управления reg.ru**
- File Manager → загрузите файл `dsc23ytp_lag_crm.sql`

**Вариант C: С локальной машины (C:\OSPanel\domains\mnka)**
```bash
# Через SCP с Windows
scp C:\OSPanel\domains\mnka\dsc23ytp_lag_crm.sql user@reg.ru:~/
```

### Шаг 4: Выполните установку проекта

```bash
cd ~/mn-ka.ru

# Вариант A: Полная установка одной командой (если SQL файл уже на сервере)
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql --force

# Вариант B: Пошаговая установка
# 1. Миграции
php artisan migrate --force

# 2. Импорт SQL
php artisan db:import-sql dsc23ytp_lag_crm.sql --skip-fk

# 3. Создание администратора
php artisan user:create

# 4. Очистка кеша
php artisan config:clear
php artisan cache:clear
```

### Шаг 5: Настройте права доступа

```bash
cd ~/mn-ka.ru

# Права на директории
find . -type d -exec chmod 755 {} \;

# Права на файлы
find . -type f -exec chmod 644 {} \;

# Специальные права
chmod 755 artisan
chmod 755 public/index.php

# Права на writable директории
chmod -R 775 storage bootstrap/cache

# Права на .htaccess
chmod 644 public/.htaccess
chmod 644 .htaccess 2>/dev/null || true
```

### Шаг 6: Проверка

```bash
# Проверьте статус миграций
php artisan migrate:status

# Проверьте подключение к БД
php artisan tinker --execute="echo 'DB: ' . DB::connection()->getDatabaseName();"

# Проверьте сайт
curl -I https://mn-ka.ru
```

---

## Быстрая команда (все сразу)

```bash
cd ~/mn-ka.ru

# 1. Зависимости
composer install --no-dev --optimize-autoloader

# 2. Установка (если SQL файл уже на месте)
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql --force

# 3. Права
chmod -R 775 storage bootstrap/cache
chmod 755 artisan public/index.php

# 4. Проверка
php artisan migrate:status
```

---

## Важно

⚠️ **SQL файл нужно перенести отдельно** - он не хранится в Git (слишком большой).

После переноса SQL файла выполните установку командой `project:install`.

