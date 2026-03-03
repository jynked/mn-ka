# Решение проблемы версии PHP на сервере

## Проблема

Проект требует PHP 8.2 или выше, но на сервере используется PHP 5.6.40.

```
Root composer.json requires php ^8.2 but your php version (5.6.40) does not satisfy that requirement.
```

## Решение

### Шаг 1: Проверьте доступные версии PHP на сервере

Выполните на сервере:

```bash
# Проверьте все доступные версии PHP
which php8.2
which php8.3
which php8.1
which php

# Или
php8.2 --version
php8.3 --version
```

### Шаг 2: Используйте правильную версию PHP

#### Вариант A: Если PHP 8.2 доступен как `php8.2`

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Используйте php8.2 для composer
php8.2 $(which composer) install --no-dev --optimize-autoloader

# Или если composer доступен как php8.2 composer:
php8.2 composer install --no-dev --optimize-autoloader

# Проверьте версию
php8.2 --version
```

#### Вариант B: Если PHP 8.2 доступен через другой путь

```bash
# Найдите путь к PHP 8.2
find /usr -name "php8.2" 2>/dev/null
find /opt -name "php8.2" 2>/dev/null

# Используйте полный путь
/usr/bin/php8.2 composer install --no-dev --optimize-autoloader
```

#### Вариант C: Создайте алиас для PHP

```bash
# Создайте алиас в ~/.bashrc
echo 'alias php="php8.2"' >> ~/.bashrc
source ~/.bashrc

# Теперь `php` будет указывать на php8.2
php --version
php composer install --no-dev --optimize-autoloader
```

### Шаг 3: Настройте PHP_PATH в .env

Откройте `.env` на сервере и добавьте/обновите:

```env
PHP_PATH=php8.2
# или
PHP_PATH=/usr/bin/php8.2
# или полный путь к PHP 8.2
```

### Шаг 4: Обновите автозагрузку с правильной версией PHP

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Используйте правильную версию PHP для всех команд
php8.2 $(which composer) install --no-dev --optimize-autoloader
php8.2 artisan optimize:clear
php8.2 artisan config:clear
php8.2 artisan route:clear
php8.2 artisan cache:clear

# Обновите автозагрузку
php8.2 $(which composer) dump-autoload

# Выполните миграции
php8.2 artisan migrate --force

# Оптимизируйте
php8.2 artisan config:cache
php8.2 artisan route:cache
php8.2 artisan view:cache
```

---

## Если PHP 8.2 не установлен на сервере

### Для хостинга с панелью управления (cPanel, ISPmanager и т.д.)

1. Войдите в панель управления хостингом
2. Найдите раздел "PHP версии" или "Выбор версии PHP"
3. Выберите PHP 8.2 или выше для вашего домена/директории
4. Сохраните изменения

### Для VPS/сервера (установка через пакетный менеджер)

#### Ubuntu/Debian:

```bash
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd php8.2-fpm
```

#### CentOS/RHEL:

```bash
sudo yum install -y php82 php82-cli php82-common php82-mysqlnd php82-xml php82-curl php82-mbstring php82-zip php82-gd
```

---

## Проверка после настройки

После установки правильной версии PHP проверьте:

```bash
# Проверьте версию PHP
php8.2 --version
# Должно показать: PHP 8.2.x или выше

# Проверьте установленные расширения
php8.2 -m | grep -E "pdo|mysql|curl|mbstring|zip|gd"

# Проверьте composer
php8.2 composer --version

# Попробуйте установить зависимости
php8.2 composer install --no-dev --optimize-autoloader
```

---

## Настройка веб-сервера

Если вы используете Apache или Nginx, убедитесь, что веб-сервер настроен на использование PHP 8.2:

### Apache

В `.htaccess` или конфигурации виртуального хоста:

```apache
<FilesMatch \.php$>
    SetHandler "proxy:unix:/var/run/php/php8.2-fpm.sock|fcgi://localhost"
</FilesMatch>
```

### Nginx

В конфигурации сервера:

```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    # или
    fastcgi_pass 127.0.0.1:9000;
    # ...
}
```

---

## Быстрая диагностика

Выполните эти команды для диагностики:

```bash
# 1. Какая версия PHP используется по умолчанию?
php --version

# 2. Какие версии PHP установлены?
ls -la /usr/bin/php*

# 3. Где находится composer?
which composer

# 4. Какая версия PHP использует composer?
composer --version

# 5. Проверьте версию через веб-сервер (создайте test.php)
echo "<?php phpinfo(); ?>" > public/test.php
# Откройте в браузере: https://your-domain.com/test.php
# Удалите после проверки: rm public/test.php
```

---

## После решения проблемы

После настройки правильной версии PHP выполните:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Обновите .env
# Добавьте: PHP_PATH=php8.2

# Установите зависимости
php8.2 composer install --no-dev --optimize-autoloader

# Очистите кеши
php8.2 artisan optimize:clear

# Выполните миграции
php8.2 artisan migrate --force

# Оптимизируйте
php8.2 artisan config:cache
php8.2 artisan route:cache
php8.2 artisan view:cache
```

---

**Важно:** Убедитесь, что веб-сервер также использует PHP 8.2, а не только CLI!

