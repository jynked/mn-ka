# Настройка PHP_PATH на сервере

## Найден PHP 8.2

Путь: `/usr/local/bin/php8.2`
Версия: PHP 8.2.25 ✅

## Шаги настройки

### 1. Добавить PHP_PATH в .env

Выполните на сервере:

```bash
cd /home/d/dsc23ytp/stroy/public_html
nano .env
```

Найдите или добавьте строку:
```env
PHP_PATH=/usr/local/bin/php8.2
```

Сохраните файл (Ctrl+O, Enter, Ctrl+X).

### 2. Проверить composer с PHP 8.2

```bash
/usr/local/bin/php8.2 composer.phar --version
/usr/local/bin/php8.2 composer.phar install --no-dev --optimize-autoloader --no-interaction --dry-run
```

Если команда выполнится без ошибок - всё готово!

### 3. Проверить, что Laravel видит правильную версию

```bash
/usr/local/bin/php8.2 artisan --version
```

## После настройки

Запустите деплой локально:
```bash
php artisan deploy --insecure
```

Система должна:
1. Найти PHP 8.2 по пути из .env
2. Использовать его для composer install
3. Успешно выполнить деплой

## Альтернатива: без изменения .env

Если не хотите менять .env, код автоматически найдет `/usr/local/bin/php8.2` при следующем деплое, так как этот путь добавлен в список проверяемых путей.





