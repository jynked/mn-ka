# Исправление проблемы с версией PHP

## Проблема

На сервере используется PHP 5.6.40 по умолчанию, а проект требует PHP 8.2.

## Решение

### Вариант 1: Указать PHP_PATH в .env (РЕКОМЕНДУЕТСЯ)

1. **На сервере проверьте, где находится PHP 8.2:**
```bash
which php8.2
# или
ls -la /usr/local/bin/php*
# или
ls -la /usr/bin/php*
```

2. **Проверьте версию:**
```bash
/usr/local/bin/php8.2 --version
# или тот путь, который нашли выше
```

3. **Добавьте в .env файл проекта на сервере:**
```bash
cd /home/d/dsc23ytp/stroy/public_html
nano .env
```

Добавьте строку (используйте правильный путь):
```env
PHP_PATH=/usr/local/bin/php8.2
```

Или если PHP 8.2 находится в другом месте:
```env
PHP_PATH=/usr/bin/php8.2
```

### Вариант 2: Автоматическое определение

Код обновлен для автоматического поиска PHP 8.2 в стандартных местах:
- `/usr/local/bin/php8.2`
- `/usr/local/bin/php8.3`
- `/usr/local/bin/php8.1`
- `/usr/bin/php8.2`
- `/usr/bin/php8.3`
- `/usr/bin/php8.1`

Система автоматически проверит версию и выберет подходящую.

## Проверка на сервере

Выполните на сервере для проверки:

```bash
# Найти все версии PHP
which php8.2 php8.3 php8.1 php

# Проверить версию каждого
/usr/local/bin/php8.2 --version
/usr/local/bin/php8.3 --version
php --version

# Проверить composer с правильной версией PHP
/usr/local/bin/php8.2 composer.phar --version
/usr/local/bin/php8.2 composer.phar install --no-dev --optimize-autoloader --no-interaction --dry-run
```

## После исправления

1. **Если добавили PHP_PATH в .env** - просто запустите деплой снова:
```bash
php artisan deploy --insecure
```

2. **Если используете автоматическое определение** - код сам найдет правильную версию PHP.

## Логи

При деплое проверьте логи на сервере:
```bash
tail -f storage/logs/laravel.log
```

Вы должны увидеть:
```
Используется PHP: /usr/local/bin/php8.2 (версия: 8.2.28)
```

Если видите версию 5.6.40 - значит PHP_PATH не настроен правильно.





