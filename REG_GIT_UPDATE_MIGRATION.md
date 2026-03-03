# Обновление миграции через Git на REG хостинге

## Способ 1: Через Git (рекомендуется, если Git настроен)

```bash
cd ~/mn-ka.ru

# Проверьте статус git
git status

# Получите последние изменения
git pull origin main
# или
git pull origin master

# Если файл был изменен локально на сервере, сбросьте его
git checkout HEAD -- database/migrations/2025_11_08_171010_add_protected_to_folders_table.php

# Или принудительно получить файл из репозитория
git fetch origin
git checkout origin/main -- database/migrations/2025_11_08_171010_add_protected_to_folders_table.php

# Запустите миграции
php artisan migrate
```

## Способ 2: Если файл еще не в Git или нужно быстро исправить

Создайте временный скрипт для замены файла через cat:

```bash
cd ~/mn-ka.ru

# Сделайте резервную копию
cp database/migrations/2025_11_08_171010_add_protected_to_folders_table.php database/migrations/2025_11_08_171010_add_protected_to_folders_table.php.bak

# Проверьте синтаксис текущего файла
php -l database/migrations/2025_11_08_171010_add_protected_to_folders_table.php
```

## Способ 3: Скачать правильный файл напрямую через curl/wget

Если у вас есть доступ к репозиторию через HTTPS или файл размещен где-то:

```bash
# Временно загрузите правильный файл
# (замените URL на реальный путь к файлу в вашем репозитории)
```

## Способ 4: Исправить синтаксис вручную

Проверьте файл на сервере:

```bash
cd ~/mn-ka.ru

# Проверьте синтаксис PHP
php -l database/migrations/2025_11_08_171010_add_protected_to_folders_table.php

# Посмотрите файл полностью
cat database/migrations/2025_11_08_171010_add_protected_to_folders_table.php

# Или проверьте количество открытых/закрытых скобок
grep -o '{' database/migrations/2025_11_08_171010_add_protected_to_folders_table.php | wc -l
grep -o '}' database/migrations/2025_11_08_171010_add_protected_to_folders_table.php | wc -l
```

