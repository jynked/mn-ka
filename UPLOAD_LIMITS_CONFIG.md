# Настройка лимитов загрузки файлов

## Проблема 413 (Content Too Large)

Если при загрузке ZIP файлов вы получаете ошибку **413 Content Too Large**, нужно увеличить лимиты в нескольких местах.

## 1. Настройки PHP (php.ini)

Найдите файл `php.ini` и установите следующие значения:

```ini
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
```

### Где находится php.ini:
- Windows: `C:\OSPanel\modules\php\PHP-8.x\php.ini`
- Linux: `/etc/php/8.x/apache2/php.ini` или `/etc/php/8.x/fpm/php.ini`

### Как проверить текущие настройки:
```php
<?php
phpinfo();
// Или через консоль:
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

## 2. Настройки веб-сервера

### Nginx

В файле конфигурации nginx (обычно `/etc/nginx/nginx.conf` или в конфиге сайта):

```nginx
server {
    client_max_body_size 100M;
    # ... остальные настройки
}
```

После изменения выполните:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### Apache

В файле `.htaccess` в корне проекта или в конфиге виртуального хоста:

```apache
php_value upload_max_filesize 100M
php_value post_max_size 100M
php_value memory_limit 256M
php_value max_execution_time 300
```

Если `.htaccess` не работает, добавьте в конфиг виртуального хоста:

```apache
<Directory "/path/to/project">
    php_admin_value upload_max_filesize "100M"
    php_admin_value post_max_size "100M"
</Directory>
```

## 3. Настройки Laravel

В контроллерах уже установлен лимит **100MB** (102400 KB):

```php
'file' => 'required|file|mimes:zip,csv,txt|max:102400'
```

## 4. OSPanel (для Windows)

Если используете OSPanel:

1. Откройте настройки OSPanel
2. Перейдите в **Модули** → **PHP**
3. Найдите `php.ini` для вашей версии PHP
4. Измените:
   ```
   upload_max_filesize = 100M
   post_max_size = 100M
   ```
5. Перезапустите OSPanel

## 5. Проверка после изменений

После изменения настроек:

1. **Перезапустите веб-сервер** (Apache/Nginx)
2. **Перезапустите PHP-FPM** (если используется):
   ```bash
   sudo systemctl restart php8.x-fpm
   ```
3. **Перезагрузите PHP** в OSPanel (кнопка "Перезапустить")

## 6. Проверка через PHP

Создайте файл `test_upload_limits.php` в корне проекта:

```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
```

Откройте в браузере: `http://lagom-figma.loc/test_upload_limits.php`

## 7. Рекомендуемые значения

Для импорта ZIP с изображениями:
- **upload_max_filesize**: 100M - 200M
- **post_max_size**: 100M - 200M (должен быть >= upload_max_filesize)
- **memory_limit**: 256M - 512M
- **max_execution_time**: 300 (5 минут)
- **client_max_body_size** (nginx): 100M - 200M

## 8. Если проблема сохраняется

1. Проверьте логи веб-сервера:
   - Nginx: `/var/log/nginx/error.log`
   - Apache: `/var/log/apache2/error.log`
   - OSPanel: `C:\OSPanel\logs\`

2. Проверьте логи Laravel:
   ```
   storage/logs/laravel.log
   ```

3. Убедитесь, что изменены настройки для правильной версии PHP (которую использует веб-сервер)

4. Если используется PHP-FPM, проверьте настройки в `/etc/php/8.x/fpm/php.ini`

## Примечание

Важно: **post_max_size** должен быть больше или равен **upload_max_filesize**, иначе файлы не будут загружаться даже если они меньше upload_max_filesize.

