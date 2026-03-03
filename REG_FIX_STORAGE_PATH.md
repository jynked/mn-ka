# Исправление неправильного пути storage на REG хостинге

## Проблема
Laravel пытается создать директории по пути `/home/d/dsc23ytp/stroy/public_html/storage/logs`, 
но проект находится в `mn-ka.ru`. Это означает, что в кеше конфигурации сохранен старый путь.

## Решение

### Шаг 1: Очистите весь кеш Laravel

Выполните в директории `mn-ka.ru`:

```bash
cd ~/mn-ka.ru

# Очистите все кеши
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Или одной командой
php artisan optimize:clear
```

### Шаг 2: Проверьте .env файл

Проверьте, нет ли в `.env` неправильных путей:

```bash
# Проверьте .env на наличие старых путей
cat .env | grep -i path
cat .env | grep APP_
```

### Шаг 3: Проверьте реальный путь проекта

```bash
# Узнайте текущую директорию
pwd

# Проверьте, какой путь использует Laravel
php artisan tinker --execute="echo base_path();"
php artisan tinker --execute="echo storage_path();"
php artisan tinker --execute="echo storage_path('logs');"
```

### Шаг 4: Создайте директории в правильном месте

После очистки кеша создайте директории:

```bash
# Убедитесь, что вы в правильной директории
cd ~/mn-ka.ru
pwd

# Создайте директории
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Установите права
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### Шаг 5: Повторно запустите миграции

```bash
php artisan migrate
```

## Полное решение (все команды сразу)

```bash
cd ~/mn-ka.ru

# 1. Очистка кеша
php artisan optimize:clear

# 2. Проверка пути
echo "Текущая директория: $(pwd)"
php artisan tinker --execute="echo 'Base path: ' . base_path();"

# 3. Создание директорий
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# 4. Установка прав
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# 5. Проверка
ls -la storage/logs/

# 6. Миграции
php artisan migrate
```

## Если проблема сохраняется

Если после очистки кеша Laravel все еще ищет старый путь, проверьте:

1. **Файлы конфигурации:**
```bash
cat config/app.php | grep -i path
cat bootstrap/app.php | grep -i path
```

2. **Проверьте символические ссылки:**
```bash
ls -la | grep "^l"
```

3. **Проверьте переменные окружения в PHP:**
```bash
php -r "echo getcwd();"
```

4. **Проверьте, где находится artisan:**
```bash
ls -la artisan
readlink -f artisan
```

## Альтернативное решение: Принудительная очистка

Если ничего не помогает, удалите все файлы кеша вручную:

```bash
cd ~/mn-ka.ru

# Удалите кешированные конфиги
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*

# Очистите через artisan
php artisan config:clear
php artisan cache:clear

# Создайте директории заново
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# Попробуйте миграции
php artisan migrate
```

