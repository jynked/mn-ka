# Исправление проблемы с правами доступа на REG хостинге

## Проблема
Ошибка указывает на путь `/home/d/dsc23ytp/stroy/public_html/storage/logs`, но вы находитесь в `mn-ka.ru`. 
Это означает, что Laravel использует другой путь, чем текущая директория.

## Решение

### Шаг 1: Проверьте текущую директорию и реальный путь проекта

```bash
# Проверьте, где вы находитесь
pwd

# Проверьте, где реально находится проект
ls -la
cat .env | grep APP_PATH
# или
php artisan tinker --execute="echo base_path();"
```

### Шаг 2: Перейдите в правильную директорию (если нужно)

Если путь в ошибке правильный:

```bash
# Перейдите в директорию, указанную в ошибке
cd /home/d/dsc23ytp/stroy/public_html

# Проверьте структуру
ls -la storage/logs/
```

### Шаг 3: Определите пользователя, под которым работает PHP

На REG хостинге PHP обычно работает под пользователем веб-сервера или под вашим пользователем:

```bash
# Проверьте пользователя PHP-FPM
ps aux | grep php-fpm

# Или проверьте текущего пользователя
whoami

# Проверьте пользователя для директории проекта
ls -la /home/d/dsc23ytp/stroy/public_html/ | head -20
```

### Шаг 4: Установите правильного владельца и права

**Вариант A: Если PHP работает под вашим пользователем**

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Создайте директории (если еще не созданы)
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Установите владельца (замените u3384357 на ваше имя пользователя)
chown -R u3384357:u3384357 storage
chown -R u3384357:u3384357 bootstrap/cache

# Установите права
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

**Вариант B: Если команда chown недоступна (нет sudo)**

На некоторых хостингах REG нет доступа к `chown`. В этом случае попробуйте:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Установите максимальные права (777) - только для диагностики
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# Попробуйте миграции
php artisan migrate
```

**Вариант C: Если директория уже существует, но права неправильные**

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Проверьте текущие права
ls -ld storage/
ls -ld storage/logs/

# Если права 755 или меньше, измените на 775 или 777
chmod 775 storage
chmod -R 775 storage/*
chmod 775 bootstrap/cache
chmod -R 775 bootstrap/cache/*
```

### Шаг 5: Проверьте, существует ли директория по указанному пути

```bash
# Проверьте наличие директории
ls -la /home/d/dsc23ytp/stroy/public_html/storage/logs/

# Если директория не существует, создайте её
mkdir -p /home/d/dsc23ytp/stroy/public_html/storage/logs
mkdir -p /home/d/dsc23ytp/stroy/public_html/storage/framework/cache/data
mkdir -p /home/d/dsc23ytp/stroy/public_html/storage/framework/sessions
mkdir -p /home/d/dsc23ytp/stroy/public_html/storage/framework/views
mkdir -p /home/d/dsc23ytp/stroy/public_html/bootstrap/cache

# Установите права
chmod -R 777 /home/d/dsc23ytp/stroy/public_html/storage
chmod -R 777 /home/d/dsc23ytp/stroy/public_html/bootstrap/cache
```

### Шаг 6: Проверьте конфигурацию Laravel

Возможно, в `.env` указан неправильный путь. Проверьте:

```bash
cd /home/d/dsc23ytp/stroy/public_html
cat .env | grep APP_
cat .env | grep STORAGE
```

## Полное решение для REG хостинга (рекомендуется)

Выполните все команды по порядку:

```bash
# 1. Перейдите в директорию проекта
cd /home/d/dsc23ytp/stroy/public_html

# 2. Проверьте текущее состояние
echo "Текущая директория: $(pwd)"
echo "Пользователь: $(whoami)"
ls -ld storage/ bootstrap/cache/

# 3. Создайте все необходимые директории
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# 4. Установите права 777 (для REG хостинга это часто необходимо)
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# 5. Проверьте, что директории созданы и имеют правильные права
ls -la storage/
ls -la storage/logs/
ls -la bootstrap/cache/

# 6. Попробуйте создать тестовый файл
touch storage/logs/test.txt
echo "test" > storage/logs/test.txt
cat storage/logs/test.txt
rm storage/logs/test.txt

# 7. Запустите миграции
php artisan migrate
```

## Альтернативное решение: Обращение в поддержку REG

Если ничего не помогает, обратитесь в поддержку REG хостинга с запросом:

```
Здравствуйте! 

При выполнении команды "php artisan migrate" в Laravel получаю ошибку:
"There is no existing directory at "/home/d/dsc23ytp/stroy/public_html/storage/logs" 
and it could not be created: Permission denied"

Проблема: PHP не может создать директории в /home/d/dsc23ytp/stroy/public_html/storage/

Прошу настроить права доступа для следующих директорий:
- /home/d/dsc23ytp/stroy/public_html/storage/
- /home/d/dsc23ytp/stroy/public_html/bootstrap/cache/

Необходимые права: 775 или 777 для записи логов и кеша Laravel.

Спасибо!
```

## Проверка после исправления

```bash
# Проверьте логи Laravel
tail -f /home/d/dsc23ytp/stroy/public_html/storage/logs/laravel.log

# Попробуйте выполнить другие команды artisan
php artisan config:clear
php artisan cache:clear
```

