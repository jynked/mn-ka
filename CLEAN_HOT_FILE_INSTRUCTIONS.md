# Инструкция по удалению файла public/hot

## Проблема
Файл `public/hot` создается Vite dev server и не должен быть в продакшене. Файл может появляться после деплоя или при обращении к сайту.

## Решение

### Вариант 1: Ручное удаление на сервере

Выполните на сервере:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Удалить файл вручную
rm -f public/hot
rm -rf public/hot

# Или через Artisan команду (после обновления кода)
PHP82="/usr/local/bin/php8.2"
$PHP82 artisan clean:hot --force
```

### Вариант 2: Автоматическое удаление через cron

Добавьте в crontab (выполните `crontab -e`):

```bash
# Удалять файл public/hot каждые 5 минут
*/5 * * * * rm -f /home/d/dsc23ytp/stroy/public_html/public/hot
```

### Вариант 3: Убедиться, что код обновлен

Если код на сервере не обновляется:

1. Вручную обновите код:
```bash
cd /home/d/dsc23ytp/stroy/public_html

# Сохраните .htaccess
cp .htaccess .htaccess.backup
mv .htaccess .htaccess.local

# Обновите код
git pull origin main

# Восстановите .htaccess
mv .htaccess.local .htaccess

# Очистите кеш
PHP82="/usr/local/bin/php8.2"
$PHP82 artisan config:clear
$PHP82 artisan cache:clear
$PHP82 artisan optimize:clear

# Переобнаружьте пакеты
$PHP82 artisan package:discover --ansi

# Пересоздайте кеш
$PHP82 artisan config:cache
```

2. Проверьте, что метод `cleanDevelopmentFiles()` существует в `DeployController`:
```bash
grep -n "cleanDevelopmentFiles" app/Http/Controllers/Api/DeployController.php
```

3. Проверьте, что команда `clean:hot` существует:
```bash
$PHP82 artisan list | grep clean:hot
```

## Проверка

После обновления кода и удаления файла, проверьте:

```bash
# Проверить наличие файла
ls -la public/hot

# Если файл не существует - все хорошо
# Если файл существует - удалите его вручную
```

## Примечание

Файл `public/hot` создается Laravel Vite плагином при запуске dev-сервера. В продакшене dev-сервер не должен быть запущен, поэтому файл не должен создаваться. Если файл появляется, это может означать, что:

1. Dev-сервер запущен на сервере (неправильно)
2. Файл создается при обращении к сайту (нужно проверить код)
3. Файл попадает из Git (нужно проверить .gitignore)

