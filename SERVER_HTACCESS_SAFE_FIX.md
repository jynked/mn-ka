# Безопасное решение конфликта с .htaccess (без удаления файла)

## Проблема

Файл `.htaccess` в корне проекта нужен для работы сайта, но конфликтует с git pull.

## Решение (без удаления файла)

### Вариант 1: Сохранить локальный .htaccess и обновить код

```bash
cd /home/d/dsc23ytp/stroy/public_html

# 1. Сохраните текущий .htaccess в резервную копию
cp .htaccess .htaccess.backup

# 2. Временно переименуйте его
mv .htaccess .htaccess.local

# 3. Выполните git pull
git pull origin main

# 4. Восстановите ваш .htaccess
mv .htaccess.local .htaccess

# 5. Очистите кеш
PHP82="/usr/local/bin/php8.2"
$PHP82 artisan config:clear
$PHP82 artisan config:cache
```

### Вариант 2: Использовать git stash для сохранения локальных изменений

```bash
cd /home/d/dsc23ytp/stroy/public_html

# 1. Сохраните локальные изменения (включая .htaccess)
git stash push -m "Save local .htaccess before pull"

# 2. Выполните git pull
git pull origin main

# 3. Восстановите ваш .htaccess
git stash pop

# 4. Очистите кеш
PHP82="/usr/local/bin/php8.2"
$PHP82 artisan config:clear
$PHP82 artisan config:cache
```

### Вариант 3: Добавить .htaccess в .gitignore (если он должен быть уникальным для сервера)

Если `.htaccess` в корне уникален для каждого сервера, добавьте его в `.gitignore`:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Добавьте .htaccess в .gitignore (только в корне!)
echo ".htaccess" >> .gitignore

# Теперь git pull будет игнорировать этот файл
git pull origin main

# Очистите кеш
PHP82="/usr/local/bin/php8.2"
$PHP82 artisan config:clear
$PHP82 artisan config:cache
```

**⚠️ Внимание:** Не добавляйте `public/.htaccess` в `.gitignore` - он должен быть в репозитории!

---

## Рекомендуемое решение

**Используйте Вариант 1** - он самый безопасный и не влияет на работу сайта:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Сохраните и временно переименуйте
cp .htaccess .htaccess.backup
mv .htaccess .htaccess.local

# Обновите код
git pull origin main

# Восстановите
mv .htaccess.local .htaccess

# Очистите кеш
PHP82="/usr/local/bin/php8.2"
$PHP82 artisan config:clear
$PHP82 artisan config:cache
```

---

## После обновления

После выполнения команд код будет обновлен, и ваш `.htaccess` останется на месте.

Попробуйте деплой:
```bash
php artisan deploy --insecure
```

