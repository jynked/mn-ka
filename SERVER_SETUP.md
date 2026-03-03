# Инструкция по настройке системы деплоя на сервере

## Обзор

После загрузки проекта на сервер необходимо выполнить следующие шаги для корректной работы автоматического деплоя.

---

## Шаг 1: Настройка переменных окружения

### 1.1. Добавление переменных в `.env`

Откройте файл `.env` на сервере и добавьте следующие переменные:

```env
# Deploy Configuration
DEPLOY_TOKEN=your-secure-deploy-token-here-min-32-chars
PHP_PATH=php8.2
```

**Важно:**
- `DEPLOY_TOKEN` - должен совпадать с токеном на локальной машине разработчика
- Используйте сложный токен (минимум 32 символа)
- `PHP_PATH` - путь к исполняемому файлу PHP (опционально, система попытается определить автоматически)

### 1.2. Определение пути к PHP

Если не уверены в пути к PHP, выполните на сервере:

```bash
which php8.2
# или
which php8.3
# или
which php
```

Также можно проверить версию:

```bash
php8.2 --version
```

После определения правильного пути, укажите его в `.env`:
```env
PHP_PATH=php8.2
# или
PHP_PATH=/usr/bin/php8.2
```

---

## Шаг 2: Проверка прав доступа

### 2.1. Права на выполнение команд

Убедитесь, что веб-сервер (обычно `www-data`, `nginx` или `apache`) имеет права на выполнение:

- `git`
- `composer`
- PHP (указанный в `PHP_PATH`)

### 2.2. Права на запись в директорию проекта

```bash
# Проверить текущего пользователя веб-сервера
ps aux | grep -E 'nginx|apache|httpd' | head -1

# Установить права (замените USER на пользователя веб-сервера)
sudo chown -R USER:USER /path/to/your/project
sudo chmod -R 755 /path/to/your/project
sudo chmod -R 775 /path/to/your/project/storage
sudo chmod -R 775 /path/to/your/project/bootstrap/cache
```

### 2.3. Права на выполнение git pull

Для выполнения `git pull` на сервере, пользователь веб-сервера должен иметь доступ к репозиторию.

**Вариант 1: SSH ключи**
```bash
# Создать SSH ключ для пользователя веб-сервера
sudo -u www-data ssh-keygen -t rsa -b 4096

# Добавить публичный ключ в GitHub/GitLab
sudo -u www-data cat ~/.ssh/id_rsa.pub
```

**Вариант 2: HTTPS с токеном (рекомендуется для продакшн)**
```bash
# Настроить git для использования токена
cd /path/to/your/project
sudo -u www-data git config credential.helper store
# При первом pull ввести токен как пароль
```

---

## Шаг 3: Настройка Git репозитория

### 3.1. Проверка подключения к репозиторию

```bash
cd /path/to/your/project
git remote -v
```

Должно показать URL вашего репозитория.

### 3.2. Проверка текущей ветки

```bash
git branch
```

Убедитесь, что вы находитесь на правильной ветке (обычно `main` или `master`).

### 3.3. Настройка автосохранения credentials (опционально)

Для HTTPS репозитория:

```bash
git config credential.helper store
```

---

## Шаг 4: Настройка Composer

### 4.1. Проверка доступности Composer

```bash
composer --version
```

Если Composer не установлен глобально, установите его:
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 4.2. Проверка прав на выполнение

```bash
which composer
sudo chmod +x $(which composer)
```

---

## Шаг 5: Проверка Laravel миграций

### 5.1. Убедитесь, что база данных настроена

Проверьте в `.env` настройки подключения к БД:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5.2. Проверка возможности выполнения миграций

```bash
php8.2 artisan migrate --pretend
```

Если команда выполняется без ошибок, миграции настроены правильно.

---

## Шаг 6: Настройка веб-сервера

### 6.1. Nginx

Убедитесь, что Nginx правильно настроен для обработки PHP:

```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### 6.2. Apache

Убедитесь, что Apache правильно настроен для обработки PHP через `mod_php` или `php-fpm`.

---

## Шаг 7: Тестирование деплоя

### 7.1. Ручной тест API endpoint

```bash
curl -X POST https://your-domain.com/api/deploy \
  -H "X-Deploy-Token: your-deploy-token" \
  -H "Content-Type: application/json" \
  -d '{"commit_hash":"test","timestamp":"2025-01-01 00:00:00"}'
```

**Ожидаемый ответ:**
```json
{
  "success": true,
  "message": "Деплой успешно завершен",
  "data": {
    "php_version": "8.2.15",
    "php_path": "php8.2",
    "git_pull": "success",
    "composer_install": "success",
    "migrations": {
      "status": "success",
      "migrations_run": 0,
      "message": "Новых миграций не обнаружено"
    },
    "cache_cleared": true,
    "optimized": true,
    "commit_hash": "...",
    "deployed_at": "2025-01-01 12:00:00"
  }
}
```

### 7.2. Тест с неправильным токеном (должен вернуть 401)

```bash
curl -X POST https://your-domain.com/api/deploy \
  -H "X-Deploy-Token: wrong-token"
```

**Ожидаемый ответ:**
```json
{
  "success": false,
  "message": "Неверный токен деплоя"
}
```

---

## Шаг 8: Настройка файрвола и безопасности

### 8.1. Ограничение доступа к API endpoint (рекомендуется)

Используйте файрвол для ограничения доступа к `/api/deploy` только с доверенных IP:

**Nginx:**
```nginx
location /api/deploy {
    allow 192.168.1.0/24;  # Ваша локальная сеть
    allow 203.0.113.0/24;  # Ваш офис
    deny all;
    
    # ... остальная конфигурация ...
}
```

**Apache (.htaccess или VirtualHost):**
```apache
<Location /api/deploy>
    Require ip 192.168.1.0/24
    Require ip 203.0.113.0/24
</Location>
```

### 8.2. Rate Limiting (рекомендуется)

Настройте ограничение количества запросов к `/api/deploy` для предотвращения DDoS атак.

**Nginx:**
```nginx
limit_req_zone $binary_remote_addr zone=deploy_limit:10m rate=1r/m;

location /api/deploy {
    limit_req zone=deploy_limit burst=2 nodelay;
    # ... остальная конфигурация ...
}
```

---

## Шаг 9: Логирование

### 9.1. Проверка логов Laravel

Все операции деплоя логируются в `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log
```

### 9.2. Настройка ротации логов

Настройте ротацию логов для предотвращения переполнения диска.

---

## Шаг 10: Мониторинг

### 10.1. Настройка уведомлений (опционально)

Можно настроить отправку email или Telegram уведомлений при деплое.

### 10.2. Проверка дискового пространства

Регулярно проверяйте свободное место на диске:

```bash
df -h
```

Убедитесь, что есть достаточно места для обновлений.

---

## Возможные проблемы и решения

### Проблема 1: "DEPLOY_TOKEN не настроен на сервере"

**Решение:** Добавьте `DEPLOY_TOKEN` в `.env` файл на сервере.

---

### Проблема 2: "Permission denied" при выполнении git pull

**Решение:** 
```bash
# Проверьте пользователя веб-сервера
ps aux | grep -E 'nginx|apache'

# Настройте SSH ключи или credentials для git
sudo -u www-data git config credential.helper store
```

---

### Проблема 3: "PHP не найден" или неправильная версия PHP

**Решение:**
1. Определите путь к PHP: `which php8.2`
2. Добавьте в `.env`: `PHP_PATH=php8.2`
3. Проверьте: `php8.2 --version`

---

### Проблема 4: "Composer not found"

**Решение:**
```bash
# Установите Composer глобально
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

---

### Проблема 5: Ошибки миграций

**Решение:**
```bash
# Проверьте подключение к БД
php8.2 artisan migrate:status

# Выполните миграции вручную для проверки
php8.2 artisan migrate --force
```

---

### Проблема 6: "Cannot write to cache directory"

**Решение:**
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## Проверочный список перед первым деплоем

- [ ] Переменная `DEPLOY_TOKEN` добавлена в `.env` на сервере
- [ ] Переменная `PHP_PATH` указана (или система автоматически определит)
- [ ] Тот же `DEPLOY_TOKEN` указан в `.env` на локальной машине
- [ ] `SERVER_URL` указан в `.env` на локальной машине
- [ ] Git репозиторий настроен на сервере
- [ ] Команды `git`, `composer`, `php` доступны для пользователя веб-сервера
- [ ] Права на запись в директорию проекта установлены
- [ ] База данных настроена и миграции могут выполняться
- [ ] Ручной тест API endpoint `/api/deploy` успешен
- [ ] Логирование работает (`storage/logs/laravel.log`)

---

## Дополнительные рекомендации

1. **Backup перед первым деплоем:** Создайте резервную копию базы данных и файлов
2. **Тестовый деплой:** Выполните тестовый деплой на staging сервере перед продакшн
3. **Мониторинг:** Настройте мониторинг доступности сайта после деплоя
4. **Уведомления:** Настройте уведомления об успешных/неуспешных деплоях

---

## Контакты и поддержка

При возникновении проблем проверьте:
1. Логи Laravel: `storage/logs/laravel.log`
2. Логи веб-сервера (Nginx/Apache)
3. Логи PHP-FPM (если используется)

---

**Последнее обновление:** 2025-01-29

