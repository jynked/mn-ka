# Исправление проблемы: Composer использует неправильную версию PHP

## Проблема

Composer пытается использовать PHP 5.6, а нужен PHP 8.2. Ошибка:
```
Parse error: syntax error, unexpected ':', expecting '{'
```

## Решение

### Вариант 1: Использовать PHP 8.2 напрямую для composer

```bash
cd /home/d/dsc23ytp/stroy/public_html
PHP82="/usr/local/bin/php8.2"

# Используйте PHP 8.2 для запуска composer
$PHP82 /home/d/dsc23ytp/.local/bin/composer dump-autoload
```

### Вариант 2: Обновить composer чтобы использовать PHP 8.2

```bash
cd /home/d/dsc23ytp/stroy/public_html
PHP82="/usr/local/bin/php8.2"

# Убедитесь, что composer использует правильную версию PHP
$PHP82 /home/d/dsc23ytp/.local/bin/composer dump-autoload --optimize
```

### Вариант 3: Создать алиас для composer с PHP 8.2

```bash
# Добавьте в ~/.bashrc
echo 'alias composer="/usr/local/bin/php8.2 /home/d/dsc23ytp/.local/bin/composer"' >> ~/.bashrc
source ~/.bashrc

# Теперь можно использовать просто:
composer dump-autoload
```

---

## Полная команда для выполнения

```bash
cd /home/d/dsc23ytp/stroy/public_html
PHP82="/usr/local/bin/php8.2"
COMPOSER="/home/d/dsc23ytp/.local/bin/composer"

# Обновите автозагрузку с правильной версией PHP
$PHP82 $COMPOSER dump-autoload --optimize

# Очистите кеши
$PHP82 artisan optimize:clear
$PHP82 artisan config:clear
$PHP82 artisan route:clear

# Пересоберите кеши
$PHP82 artisan config:cache
$PHP82 artisan route:cache
```

---

## Проверка после исправления

После выполнения команд проверьте:

```bash
# Проверьте, что маршрут зарегистрирован
/usr/local/bin/php8.2 artisan route:list | grep deploy

# Проверьте, что автозагрузка работает
/usr/local/bin/php8.2 artisan --version
```

