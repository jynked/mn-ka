# Настройка прав доступа для Laravel на REG хостинге

## Проблема
```
There is no existing directory at "/home/d/dsc23ytp/stroy/public_html/storage/logs" 
and it could not be created: Permission denied
```

Это означает, что директории `storage` и `bootstrap/cache` не имеют правильных прав доступа для записи.

## Решение

### Шаг 1: Создание необходимых директорий

Выполните следующие команды на сервере (подключитесь по SSH к `u3384357@server37`):

```bash
# Перейдите в директорию проекта
cd /home/d/dsc23ytp/stroy/public_html

# Проверьте текущие права доступа
ls -la storage/
ls -la bootstrap/cache/ 2>/dev/null || echo "Директория bootstrap/cache не существует"

# Создайте недостающие директории (если их нет)
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p bootstrap/cache
```

### Шаг 2: Установка правильных прав доступа

На REG хостинге обычно используется ваш пользователь (например, `dsc23ytp`). Выполните:

```bash
# Вариант 1: Если вы знаете пользователя веб-сервера
# Сначала определите пользователя:
ps aux | grep -E 'nginx|apache|httpd|php-fpm' | head -1

# Затем установите права (замените USER на имя пользователя из команды выше)
# Обычно на REG хостинге это ваш основной пользователь или www-data

# Вариант 2: Установите права для вашего пользователя (рекомендуется для REG)
# Определите вашего текущего пользователя:
whoami

# Установите права (замените YOUR_USER на результат команды whoami)
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R YOUR_USER:YOUR_USER storage
chown -R YOUR_USER:YOUR_USER bootstrap/cache
```

**Для REG хостинга обычно достаточно:**

```bash
# Установите права 775 для директорий (разрешает чтение и запись владельцу и группе)
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Если команда chown недоступна (на некоторых хостингах без sudo), 
# пропустите её - права 775 обычно достаточно
```

### Шаг 3: Проверка структуры директорий

Убедитесь, что все необходимые директории существуют:

```bash
# Проверьте структуру storage
ls -la storage/
# Должны быть видны: app/, framework/, logs/

ls -la storage/framework/
# Должны быть видны: cache/, sessions/, views/

ls -la storage/framework/cache/
# Должна быть видна: data/

# Проверьте bootstrap/cache
ls -la bootstrap/cache/
```

### Шаг 4: Создание файлов .gitkeep (если нужно)

Если некоторые директории были созданы вручную, создайте `.gitkeep` файлы:

```bash
touch storage/logs/.gitkeep
touch storage/framework/cache/data/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch bootstrap/cache/.gitkeep
```

### Шаг 5: Проверка прав доступа

Проверьте, что права установлены правильно:

```bash
# Проверьте права на storage
ls -ld storage/
# Должно быть: drwxrwxr-x (775)

# Проверьте права на поддиректории
ls -ld storage/logs/
ls -ld storage/framework/
ls -ld bootstrap/cache/
```

### Шаг 6: Тестирование записи

Попробуйте создать тестовый файл:

```bash
# Проверьте возможность записи в storage/logs
touch storage/logs/test.log
echo "test" > storage/logs/test.log
cat storage/logs/test.log
rm storage/logs/test.log

# Если команды выполнились без ошибок - права настроены правильно
```

### Шаг 7: Повторный запуск миграций

После настройки прав попробуйте снова:

```bash
php artisan migrate
```

## Альтернативное решение (если права не помогают)

Если проблема сохраняется, попробуйте установить более широкие права (777) - **только для теста**:

```bash
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

**⚠️ Внимание:** Права 777 менее безопасны. Используйте их только для диагностики. После успешной настройки вернитесь к 775.

## Быстрое решение (все команды сразу)

```bash
cd /home/d/dsc23ytp/stroy/public_html

# Создание директорий
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Установка прав доступа
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Проверка
php artisan migrate
```

## Проверка результата

После выполнения всех команд проверьте:

```bash
# Проверьте, что директория существует и доступна
ls -la storage/logs/

# Попробуйте выполнить миграции
php artisan migrate

# Если всё работает, проверьте логи
tail -n 20 storage/logs/laravel.log
```

## Дополнительная информация

- Права `775` означают: владелец и группа могут читать, писать и выполнять; остальные могут только читать и выполнять
- Если на хостинге используется PHP-FPM, проверьте пользователя в настройках PHP-FPM
- На некоторых хостингах REG требуется обращаться в поддержку для изменения прав на системные директории

## Контакты поддержки REG

Если проблема не решается, обратитесь в поддержку REG хостинга с описанием проблемы и запросом на настройку прав доступа для директорий `storage` и `bootstrap/cache`.

