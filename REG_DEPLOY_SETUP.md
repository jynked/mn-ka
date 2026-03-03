# Настройка автоматического деплоя на REG хостинге

## Текущая конфигурация

Система деплоя уже настроена в проекте:
- API endpoint: `POST /api/deploy` (защищен токеном)
- Middleware: `VerifyDeployToken` (проверка `DEPLOY_TOKEN`)
- Artisan команда: `php artisan deploy` (локальная машина)

## Шаг 1: Проверка маршрута на сервере

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# Проверьте, что маршрут зарегистрирован
php artisan route:list | grep deploy
```

Должен вернуть:
```
POST      api/deploy ......................... deploy › Api\DeployController@deploy
```

## Шаг 2: Настройка DEPLOY_TOKEN на сервере

### 2.1. Генерация токена (если его нет)

```bash
# Сгенерируйте секретный токен
php -r "echo bin2hex(random_bytes(32));"
# или
openssl rand -hex 32
```

Скопируйте полученный токен.

### 2.2. Установка токена на сервере

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# Откройте .env
nano .env

# Добавьте (или обновите) строку:
DEPLOY_TOKEN=ваш-сгенерированный-токен-здесь

# Также добавьте PHP_PATH (если нужно):
PHP_PATH=php
# или явный путь (если есть):
# PHP_PATH=/usr/bin/php8.3
```

### 2.3. Установка того же токена на локальной машине

**Важно:** Токен должен быть ОДИНАКОВЫМ на локальной машине и на сервере!

На локальной машине в `.env`:
```env
DEPLOY_TOKEN=ваш-сгенерированный-токен-здесь
SERVER_URL=https://www.mn-ka.ru
```

### 2.4. Очистка кеша на сервере

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# Очистите кеш конфигурации
php artisan config:clear
php artisan cache:clear
```

## Шаг 3: Проверка доступности API

### 3.1. Проверка через curl (на сервере)

```bash
# Замените YOUR_TOKEN на ваш токен
curl -X POST https://www.mn-ka.ru/api/deploy \
  -H "Content-Type: application/json" \
  -H "X-Deploy-Token: YOUR_TOKEN"

# Или передайте через параметр:
curl -X POST "https://www.mn-ka.ru/api/deploy?token=YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Ожидаемый результат:**
- Если токен правильный: JSON с результатами деплоя
- Если токен неверный: `{"success":false,"message":"Неверный токен деплоя"}` (401)
- Если токен не настроен: `{"success":false,"message":"DEPLOY_TOKEN не настроен на сервере"}` (500)

## Шаг 4: Проверка Git на сервере

Убедитесь, что на сервере настроен Git:

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# Проверьте статус Git
git status

# Проверьте remote
git remote -v

# Должно быть:
# origin  https://github.com/letoceiling-coder/mn-ka.git (fetch)
# origin  https://github.com/letoceiling-coder/mn-ka.git (push)
```

## Шаг 5: Проверка Composer на сервере

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# Проверьте, что composer доступен
which composer
# или
ls -la composer.phar

# Если composer.phar нет в проекте, установите его:
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

## Шаг 6: Первый тестовый деплой

### 6.1. С локальной машины

```bash
# На локальной машине (в директории проекта)
php artisan deploy

# Или с опцией для теста без сборки:
php artisan deploy --skip-build
```

Команда автоматически:
1. Выполнит `npm run build` (если не `--skip-build`)
2. Добавит изменения в git
3. Создаст коммит
4. Отправит в репозиторий (git push)
5. Отправит POST запрос на сервер `/api/deploy`

### 6.2. Проверка логов на сервере

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# Просмотрите логи деплоя
tail -f storage/logs/laravel.log | grep -i deploy
```

## Шаг 7: Проверка переменных окружения

Убедитесь, что в `.env` на сервере настроены все необходимые переменные:

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# Проверьте основные переменные
grep -E "DEPLOY_TOKEN|PHP_PATH|APP_URL|DB_" .env
```

Пример `.env` на сервере:
```env
APP_URL=https://www.mn-ka.ru
DEPLOY_TOKEN=ваш-секретный-токен-минимум-32-символа
PHP_PATH=php
# или явный путь:
# PHP_PATH=/usr/bin/php8.3

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u3384357_lag_crm
DB_USERNAME=u3384357_lag_crm
DB_PASSWORD=ваш-пароль
```

## Решение проблем

### Проблема: "DEPLOY_TOKEN не настроен на сервере"

**Решение:**
1. Проверьте, что `DEPLOY_TOKEN` добавлен в `.env` на сервере
2. Выполните `php artisan config:clear`
3. Попробуйте снова

### Проблема: "Неверный токен деплоя" (401)

**Решение:**
1. Убедитесь, что токен ОДИНАКОВЫЙ на локальной машине и на сервере
2. Проверьте, нет ли лишних пробелов в токене
3. Очистите кеш: `php artisan config:clear`

### Проблема: "Git pull failed"

**Решение:**
1. Проверьте права доступа к директории проекта
2. Убедитесь, что Git репозиторий настроен правильно
3. Проверьте, что remote URL правильный: `git remote -v`

### Проблема: "Composer not found"

**Решение:**
1. Установите composer.phar в проект (см. Шаг 5)
2. Или укажите явный путь в `COMPOSER_PATH` в `.env`

## Пример использования

### Обычный деплой
```bash
php artisan deploy
```

### Деплой с кастомным сообщением
```bash
php artisan deploy --message="Добавлен новый функционал"
```

### Деплой без сборки фронтенда
```bash
php artisan deploy --skip-build
```

### Деплой с выполнением seeders
```bash
php artisan deploy --with-seed
```

## Проверка после настройки

- [ ] `DEPLOY_TOKEN` настроен на локальной машине и на сервере (одинаковый)
- [ ] `SERVER_URL` указан на локальной машине (`https://www.mn-ka.ru`)
- [ ] `PHP_PATH` указан на сервере (или автоопределяется)
- [ ] Git репозиторий настроен на сервере
- [ ] Composer доступен на сервере
- [ ] API endpoint `/api/deploy` доступен и отвечает
- [ ] Команда `php artisan deploy` работает на локальной машине
- [ ] Первый тестовый деплой выполнен успешно

---

**Последнее обновление:** 2026-01-18

