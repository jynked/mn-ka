# Исправление проблем с .env на сервере

## Проблемы

1. `DEPLOY_TOKEN` не читается из `.env` (нужно очистить кеш)
2. `SERVER_URL` указан неправильно: `https://post-ads.ru/api/deploy` (должно быть без `/api/deploy`)

## Решение

### Шаг 1: Исправьте .env на сервере

На сервере откройте `.env` и исправьте:

```bash
cd /home/d/dsc23ytp/stroy/public_html
nano .env
```

Измените:
```env
# БЫЛО (неправильно):
SERVER_URL=https://post-ads.ru/api/deploy

# ДОЛЖНО БЫТЬ (правильно):
SERVER_URL=https://post-ads.ru

# И убедитесь, что токен настроен:
DEPLOY_TOKEN=123123123
```

Сохраните файл (Ctrl+O, Enter, Ctrl+X в nano).

### Шаг 2: Очистите кеш конфигурации

**ВАЖНО:** После изменения `.env` всегда нужно очищать кеш!

```bash
cd /home/d/dsc23ytp/stroy/public_html
PHP82="/usr/local/bin/php8.2"

# Очистите кеш конфигурации
$PHP82 artisan config:clear

# Пересоберите кеш
$PHP82 artisan config:cache

# Проверьте, что переменные читаются
$PHP82 artisan tinker
```

В tinker выполните:
```php
env('DEPLOY_TOKEN')
env('SERVER_URL')
```

Должны вернуться правильные значения. Выход: `exit`

### Шаг 3: Проверьте работу

```bash
# Проверьте через curl
curl -X POST https://post-ads.ru/api/deploy \
  -H "Content-Type: application/json" \
  -H "X-Deploy-Token: 123123123"
```

---

## Проверка .env на локальной машине

Также проверьте `.env` на локальной машине:

```bash
# На локальной машине
grep -E "SERVER_URL|DEPLOY_TOKEN" .env
```

Убедитесь, что:
- `SERVER_URL=https://post-ads.ru` (без `/api/deploy`)
- `DEPLOY_TOKEN=123123123` (должен совпадать с сервером)

---

## Важно

⚠️ **После ЛЮБОГО изменения `.env` всегда выполняйте:**
```bash
php artisan config:clear
php artisan config:cache
```

Иначе изменения не применятся!

---

## После исправления

После исправления `SERVER_URL` и очистки кеша попробуйте деплой снова:

```bash
php artisan deploy --insecure
```

