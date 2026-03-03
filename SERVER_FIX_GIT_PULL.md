# Решение проблемы git pull на сервере

## Проблема
При выполнении `git pull` возникают конфликты из-за локальных изменений на сервере.

## Решение

Выполните эти команды на сервере **по порядку**:

### Вариант 1: Сохранить локальные изменения (рекомендуется)

```bash
cd /home/d/dsc23ytp/stroy/public_html

# 1. Сохранить локальные изменения в stash
git stash push -m "Local changes before deploy $(date +%Y-%m-%d)"

# 2. Удалить неотслеживаемые файлы, которые конфликтуют
git clean -fd

# 3. Выполнить pull
git pull origin main

# 4. Если нужно восстановить локальные изменения:
# git stash pop
```

### Вариант 2: Полный сброс к состоянию репозитория (если локальные изменения не важны)

```bash
cd /home/d/dsc23ytp/stroy/public_html

# 1. Сбросить все локальные изменения
git reset --hard HEAD

# 2. Удалить все неотслеживаемые файлы
git clean -fd

# 3. Выполнить pull
git pull origin main
```

### Вариант 3: Принять изменения из репозитория (перезаписать локальные)

```bash
cd /home/d/dsc23ytp/stroy/public_html

# 1. Сбросить локальное состояние к состоянию в origin/main
git fetch origin
git reset --hard origin/main

# 2. Удалить неотслеживаемые файлы
git clean -fd
```

---

## ⚠️ Важно

**Перед выполнением команд убедитесь, что:**
1. Ваш файл `.env` **НЕ** в git (он должен быть в `.gitignore`)
2. Все важные локальные изменения сохранены
3. У вас есть доступ к SSH для выполнения команд

---

## После выполнения

После успешного `git pull` выполните:

```bash
# Установите зависимости
composer install --no-dev --optimize-autoloader

# Очистите кеши
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan optimize:clear

# Обновите автозагрузку
composer dump-autoload

# Выполните миграции (если были новые)
php artisan migrate --force

# Оптимизируйте
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Автоматическое решение в будущем

После обновления кода на сервере система деплоя будет автоматически обрабатывать такие ситуации. Просто используйте:

```bash
php artisan deploy --insecure
```

Или отправьте запрос на `/api/deploy` - система сама сохранит локальные изменения в stash и выполнит pull.

