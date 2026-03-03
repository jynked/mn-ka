# Исправление ошибки 403 Forbidden на REG хостинге

## Проблема
После успешных миграций сайт возвращает `403 Forbidden`. Это обычно связано с правами доступа или конфигурацией.

## Решение

### Шаг 1: Проверьте права доступа на основные файлы и директории

Выполните на сервере:

```bash
cd ~/mn-ka.ru

# Проверьте права на корневую директорию
ls -la | head -20

# Установите правильные права на директории (755 - чтение и выполнение для всех)
find . -type d -exec chmod 755 {} \;

# Установите права на файлы (644 - чтение для всех, запись для владельца)
find . -type f -exec chmod 644 {} \;

# Особые права для исполняемых файлов
chmod 755 artisan
chmod 755 public/index.php

# Права на директории для записи (775)
chmod 775 storage
chmod 775 storage/logs
chmod 775 storage/framework
chmod 775 storage/framework/cache
chmod 775 storage/framework/cache/data
chmod 775 storage/framework/sessions
chmod 775 storage/framework/views
chmod 775 bootstrap/cache
```

### Шаг 2: Проверьте существование и права на .htaccess

```bash
# Проверьте .htaccess в public/
ls -la public/.htaccess

# Если файла нет, создайте его
cat public/.htaccess

# Убедитесь, что права на .htaccess правильные
chmod 644 public/.htaccess

# Проверьте .htaccess в корне (если есть)
ls -la .htaccess
chmod 644 .htaccess
```

### Шаг 3: Проверьте, что DocumentRoot указывает на public/

На REG хостинге обычно нужно настроить DocumentRoot в панели управления.

**Вариант A: Если есть доступ к настройкам домена в панели REG:**
- Зайдите в панель управления хостингом
- Найдите настройки домена `mn-ka.ru`
- Проверьте, что DocumentRoot указывает на `public/` директорию
- Должно быть: `/home/.../mn-ka.ru/public` или `/var/www/.../mn-ka.ru/public`

**Вариант B: Если нужно использовать .htaccess в корне:**

Убедитесь, что в корне проекта есть `.htaccess`, который перенаправляет в `public/`:

```bash
cat .htaccess
```

Если файла нет или он неправильный, создайте:

```bash
cat > .htaccess << 'EOF'
RewriteEngine on

RewriteCond %{REQUEST_URI} !^/public/

RewriteCond %{REQUEST_URI} !-f
RewriteCond %{REQUEST_URI} !-d

RewriteRule ^(.*)$ /public/$1
RewriteRule ^(/)?$ /public/index.php [L]
EOF

chmod 644 .htaccess
```

### Шаг 4: Проверьте права на index.php

```bash
# Проверьте существование и права
ls -la public/index.php

# Установите правильные права
chmod 644 public/index.php

# Проверьте содержимое index.php
head -10 public/index.php
```

### Шаг 5: Проверьте права владельца

```bash
# Проверьте текущего пользователя
whoami

# Проверьте владельца файлов
ls -la public/index.php
ls -la public/.htaccess

# Если владелец неправильный, установите (если есть права)
# chown -R u3384357:u3384357 .
```

### Шаг 6: Быстрое решение (все команды сразу)

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
chmod 644 .htaccess 2>/dev/null || echo ".htaccess в корне не найден"

# Проверка
ls -la public/ | head -10
```

### Шаг 7: Проверьте через браузер

После выполнения команд попробуйте открыть:
- `http://mn-ka.ru`
- `https://mn-ka.ru`
- `http://mn-ka.ru/public/index.php`

### Шаг 8: Если проблема сохраняется - проверьте логи

```bash
# Логи Apache (если доступны)
tail -50 /var/log/apache2/error.log
# или
tail -50 /var/log/httpd/error_log

# Логи Laravel
tail -50 storage/logs/laravel.log
```

## Альтернатива: Обращение в поддержку REG

Если ничего не помогает, обратитесь в поддержку REG с запросом:

```
Здравствуйте!

После переноса Laravel проекта на хостинг получаю ошибку 403 Forbidden при обращении к домену mn-ka.ru.

Прошу проверить:
1. Правильность настроек DocumentRoot (должен указывать на директорию public/)
2. Включен ли mod_rewrite для Apache
3. Правильность прав доступа к файлам и директориям

Путь к проекту: [укажите полный путь к mn-ka.ru]
Домен: mn-ka.ru

Спасибо!
```

## Типичные проблемы на REG хостинге

1. **DocumentRoot не настроен на public/** - самая частая причина
2. **mod_rewrite отключен** - нужно включить в панели управления
3. **Слишком строгие права** - на REG обычно нужны 755 для директорий и 644 для файлов
4. **Неправильный .htaccess** - может блокировать доступ

