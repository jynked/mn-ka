# Исправление проблемы 404 для маршрута /api/deploy

## Проблема
Маршрут `/api/deploy` возвращает 404 Not Found.

## Причины и решения

### Причина 1: Старая версия файла routes/api.php

Убедитесь, что на сервере обновлен файл `routes/api.php`. Проверьте на сервере:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Проверьте, есть ли маршрут deploy в файле
grep -n "deploy" routes/api.php
```

Должна быть строка:
```
Route::post('/deploy', [DeployController::class, 'deploy'])
```

Если её нет, выполните:
```bash
git pull origin main
```

---

### Причина 2: Кеш маршрутов не очищен

Очистите кеш маршрутов:

```bash
cd /home/d/dsc23ytp/stroy/public_html
PHP82="/usr/local/bin/php8.2"

# Очистите все кеши
$PHP82 artisan route:clear
$PHP82 artisan config:clear
$PHP82 artisan cache:clear
$PHP82 artisan optimize:clear

# Пересоберите кеш маршрутов
$PHP82 artisan route:cache
$PHP82 artisan config:cache
```

---

### Причина 3: Middleware не зарегистрирован

Проверьте, что middleware `deploy.token` зарегистрирован в `bootstrap/app.php`.

На сервере выполните:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Проверьте, есть ли middleware в bootstrap/app.php
grep -n "deploy.token" bootstrap/app.php
```

Должна быть строка:
```php
'deploy.token' => \App\Http\Middleware\VerifyDeployToken::class,
```

Если её нет, обновите файл через git pull.

---

### Причина 4: Файл VerifyDeployToken.php отсутствует

Проверьте наличие файла middleware:

```bash
ls -la app/Http/Middleware/VerifyDeployToken.php
```

Если файл отсутствует, выполните:
```bash
git pull origin main
```

---

## Полное решение (выполните по порядку)

```bash
cd /home/d/dsc23ytp/stroy/public_html
PHP82="/usr/local/bin/php8.2"

# 1. Убедитесь, что код обновлен
git pull origin main

# 2. Проверьте наличие файлов
ls -la app/Http/Controllers/Api/DeployController.php
ls -la app/Http/Middleware/VerifyDeployToken.php
ls -la routes/api.php

# 3. Проверьте маршрут в routes/api.php
grep -n "deploy" routes/api.php

# 4. Проверьте middleware в bootstrap/app.php
grep -n "deploy.token" bootstrap/app.php

# 5. Очистите все кеши
$PHP82 artisan optimize:clear
$PHP82 artisan config:clear
$PHP82 artisan route:clear
$PHP82 artisan cache:clear

# 6. Обновите автозагрузку
$PHP82 composer dump-autoload

# 7. Пересоберите кеши
$PHP82 artisan config:cache
$PHP82 artisan route:cache

# 8. Проверьте список маршрутов
$PHP82 artisan route:list | grep deploy
```

Ожидаемый вывод:
```
POST   api/deploy ............................ deploy
```

---

## Проверка работы маршрута

После выполнения всех шагов проверьте маршрут:

```bash
# Проверьте список маршрутов
/usr/local/bin/php8.2 artisan route:list | grep deploy

# Попробуйте тестовый запрос (должен вернуть 401 без токена)
curl -X POST https://post-ads.ru/api/deploy \
  -H "Content-Type: application/json"

# Должен вернуть 401 Unauthorized (это значит маршрут работает)
```

---

## Если маршрут все еще не работает

### Проверьте конфигурацию API роутов

Убедитесь, что в `bootstrap/app.php` настроены API роуты:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // Должна быть эта строка
    // ...
)
```

### Проверьте логи

```bash
tail -f storage/logs/laravel.log
```

И попробуйте снова отправить запрос на `/api/deploy` - в логах должна появиться информация об ошибке.

---

## Быстрое решение (если ничего не помогло)

```bash
cd /home/d/dsc23ytp/stroy/public_html
PHP82="/usr/local/bin/php8.2"

# Полный сброс и пересборка
$PHP82 artisan optimize:clear
$PHP82 composer dump-autoload --optimize
$PHP82 artisan config:cache
$PHP82 artisan route:cache
$PHP82 artisan view:cache

# Проверка
$PHP82 artisan route:list | grep deploy
```

