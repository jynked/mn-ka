# Финальная проверка и тест маршрута /api/deploy

## ✅ Маршрут зарегистрирован!

Маршрут найден в списке:
```
POST   api/deploy  ... Api\DeployController@deploy
```

## Проблема с composer

Вы видите ошибку:
```
Could not open input file: composer
```

Это нормально, если composer не в PATH. Используйте:

```bash
# Найдите путь к composer
which composer
# или
/usr/local/bin/composer dump-autoload

# Или используйте php composer.phar, если composer установлен локально
```

---

## Тест маршрута

### 1. Проверьте, что маршрут доступен через curl

```bash
# Тест без токена (должен вернуть 401 Unauthorized)
curl -X POST https://post-ads.ru/api/deploy \
  -H "Content-Type: application/json" \
  -v
```

Ожидаемый ответ: `401 Unauthorized` (это значит маршрут работает!)

### 2. Проверьте с токеном (из .env)

Сначала проверьте, что токен настроен:

```bash
cd /home/d/dsc23ytp/stroy/public_html
grep DEPLOY_TOKEN .env
```

Если токен не настроен, добавьте в `.env`:

```env
DEPLOY_TOKEN=ваш-секретный-токен-минимум-32-символа
```

Затем проверьте:

```bash
# Замените YOUR_TOKEN на токен из .env
curl -X POST https://post-ads.ru/api/deploy \
  -H "Content-Type: application/json" \
  -H "X-Deploy-Token: YOUR_TOKEN"
```

---

## Возможные проблемы и решения

### Проблема 1: Маршрут работает, но возвращает 404 при запросе

**Причина:** Веб-сервер (Apache/Nginx) не перезагружен после изменения маршрутов.

**Решение:** Перезапустите веб-сервер или подождите несколько минут (кеш может обновиться автоматически).

### Проблема 2: Ошибка 500 вместо 401/404

**Проверьте логи:**

```bash
tail -f storage/logs/laravel.log
```

И попробуйте снова отправить запрос - в логах будет детальная информация об ошибке.

### Проблема 3: Маршрут не работает через браузер

Убедитесь, что вы используете **POST** метод, а не GET. В браузере по умолчанию используется GET.

---

## Финальная настройка .env

Убедитесь, что в `.env` настроены:

```env
PHP_PATH=/usr/local/bin/php8.2
DEPLOY_TOKEN=ваш-секретный-токен
```

**Важно:** `DEPLOY_TOKEN` должен совпадать с токеном на локальной машине!

---

## Проверка через artisan tinker

Попробуйте проверить маршрут через tinker:

```bash
/usr/local/bin/php8.2 artisan tinker

# В tinker:
Route::has('api.deploy')
# Должно вернуть: true

# Проверьте маршрут:
Route::getRoutes()->match(Request::create('/api/deploy', 'POST'))
```

---

## Если маршрут все еще не работает

### Проверьте конфигурацию веб-сервера

Убедитесь, что все запросы к `/api/*` обрабатываются Laravel (через `public/index.php`).

Для Apache проверьте `.htaccess` в `public/`:

```bash
cat public/.htaccess
```

Для Nginx проверьте конфигурацию сервера - все запросы должны идти через `public/index.php`.

---

## После настройки токена

После того как настроите `DEPLOY_TOKEN` в `.env`, попробуйте деплой снова:

```bash
# С локальной машины
php artisan deploy --insecure
```

Система должна успешно подключиться к серверу!

