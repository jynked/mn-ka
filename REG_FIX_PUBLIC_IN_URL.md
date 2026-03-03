# Удаление /public/ из URL на reg.ru

## Проблема
URL показывает `https://mn-ka.ru/public/index.php` вместо `https://mn-ka.ru/`.

## Решение

### Проверка текущей конфигурации

```bash
# Проверьте .htaccess в корне
cat /var/www/u3384357/data/www/mn-ka.ru/.htaccess

# Проверьте, какой путь используется для редиректа
grep -r "DocumentRoot\|root" /etc/apache2/sites-enabled/* 2>/dev/null | grep mn-ka
```

### Вариант 1: Изменить настройки в ISPmanager (если доступно)

В настройках сайта `mn-ka.ru` в ISPmanager:
1. Найдите раздел "Дополнительные настройки" или "Параметры"
2. Ищите поле типа "Директория" или "Путь к сайту"
3. Установите: `public` или полный путь `/var/www/u3384357/data/www/mn-ka.ru/public`

### Вариант 2: Через .htaccess (если нельзя изменить DocumentRoot)

Измените `.htaccess` в корне, чтобы он работал как внутренний редирект (без изменения URL):

```apache
RewriteEngine on

# Перенаправление HTTP на HTTPS (но не добавляем /public/)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Внутреннее перенаправление в public/ (без изменения URL в браузере)
RewriteCond %{REQUEST_URI} !^/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /public/$1 [L]

# Для корня - перенаправление в public/index.php
RewriteRule ^(/)?$ /public/index.php [L]
```

### Вариант 3: Обратиться в поддержку REG

Если нет возможности изменить DocumentRoot самостоятельно, обратитесь в поддержку REG с запросом изменить DocumentRoot для домена `mn-ka.ru` на директорию `public/`.

