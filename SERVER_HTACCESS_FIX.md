# Исправление конфликта с .htaccess

## Проблема

При `git pull` возникает конфликт с файлом `.htaccess` в корне проекта.

## Решение

На сервере выполните:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Удалите .htaccess из корня (если он там есть)
rm .htaccess

# Теперь выполните git pull
git pull origin main

# Очистите кеш
PHP82="/usr/local/bin/php8.2"
$PHP82 artisan config:clear
$PHP82 artisan config:cache
```

## Важно

`.htaccess` должен быть только в директории `public/`, а не в корне проекта!

Правильная структура:
- ✅ `public/.htaccess` - правильное место
- ❌ `.htaccess` в корне - удалить

---

## После удаления и обновления

После выполнения команд выше код будет обновлен, и можно будет попробовать деплой снова.

