# Перенос проекта с beget.ru на reg.ru - План действий

## Вариант 1: Через rsync/scp (самый быстрый и надежный) ⭐ РЕКОМЕНДУЕТСЯ

### Шаг 1: Создайте архив проекта на beget

```bash
# Подключитесь к beget по SSH
ssh user@beget.ru

# Перейдите в директорию проекта
cd ~/public_html

# Создайте архив (исключая ненужные папки)
tar -czf project_backup.tar.gz \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  .

# Или создайте архив через панель beget (File Manager → Архивировать)
```

### Шаг 2: Экспортируйте базу данных

```bash
# На beget
mysqldump -u username -p database_name > database_backup.sql

# Или через phpMyAdmin в панели beget:
# - Выберите базу данных
# - Вкладка "Экспорт"
# - Выберите "Быстрый" или "Обычный"
# - Формат: SQL
# - Сохраните файл
```

### Шаг 3: Перенесите файлы на reg.ru

```bash
# С локальной машины или с beget напрямую:

# Способ A: Через SCP (если есть SSH доступ к обоим серверам)
scp user@beget.ru:~/public_html/project_backup.tar.gz user@reg.ru:~/
scp user@beget.ru:~/database_backup.sql user@reg.ru:~/

# Способ B: Через rsync (синхронизация)
rsync -avz --exclude 'node_modules' --exclude 'vendor' \
  --exclude '.git' --exclude 'storage/logs' \
  user@beget.ru:~/public_html/ user@reg.ru:~/mn-ka.ru/

# Способ C: Через панель управления (File Manager)
# 1. Загрузите архив project_backup.tar.gz через панель reg.ru
# 2. Распакуйте через File Manager или SSH
```

### Шаг 4: Распакуйте и настройте на reg.ru

```bash
# На reg.ru через SSH
ssh user@reg.ru

cd ~/mn-ka.ru

# Распакуйте архив (если переносили архивом)
tar -xzf ~/project_backup.tar.gz

# Или если копировали напрямую, просто обновите зависимости
composer install --no-dev --optimize-autoloader
npm install  # если нужно

# Настройте .env файл (скопируйте с beget и измените)
cp .env.example .env
nano .env
# Измените:
# - DB_DATABASE, DB_USERNAME, DB_PASSWORD (данные reg.ru)
# - APP_URL (новый домен)
# - Другие настройки по необходимости

# Сгенерируйте ключ приложения
php artisan key:generate

# Настройте права
chmod -R 775 storage bootstrap/cache
chmod -R 755 .

# Импортируйте базу данных
mysql -u user -p database_name < ~/database_backup.sql
# Или через команду, которую мы уже использовали:
php artisan db:import-sql ~/database_backup.sql --skip-fk

# Выполните миграции (если нужно)
php artisan migrate --force

# Очистите кеш
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Вариант 2: Через Git (если проект в Git)

### Шаг 1: Запушьте изменения с beget в Git

```bash
# На beget
cd ~/public_html
git add .
git commit -m "Backup before migration"
git push origin main
```

### Шаг 2: Сделайте pull на reg.ru

```bash
# На reg.ru
cd ~/mn-ka.ru
git pull origin main

# Установите зависимости
composer install --no-dev --optimize-autoloader

# Настройте .env и выполните остальные шаги как в Варианте 1
```

---

## Вариант 3: Через панели управления (без SSH)

### Шаг 1: Экспорт с beget через панель

1. **Файлы:**
   - Зайдите в панель beget → File Manager
   - Выберите все файлы проекта
   - Архивируйте в ZIP/TAR.GZ
   - Скачайте архив на локальную машину

2. **База данных:**
   - Зайдите в phpMyAdmin на beget
   - Выберите базу данных
   - Вкладка "Экспорт" → "Быстрый" → "SQL"
   - Скачайте файл

### Шаг 2: Импорт на reg.ru через панель

1. **Файлы:**
   - Зайдите в панель reg.ru → File Manager
   - Загрузите архив проекта
   - Распакуйте архив

2. **База данных:**
   - Зайдите в phpMyAdmin на reg.ru
   - Выберите базу данных
   - Вкладка "Импорт" → выберите SQL файл
   - **Важно:** Отключите проверку foreign keys:
     - Вкладка "SQL" → выполните: `SET FOREIGN_KEY_CHECKS=0;`
     - Затем импортируйте
     - Затем: `SET FOREIGN_KEY_CHECKS=1;`

### Шаг 3: Настройка через SSH (или через панель)

```bash
# Подключитесь по SSH к reg.ru
ssh user@reg.ru
cd ~/mn-ka.ru

# Настройте .env, права, выполните команды как в Варианте 1
```

---

## Вариант 4: Автоматический скрипт миграции

Создайте скрипт на beget для автоматического создания бэкапа:

```bash
#!/bin/bash
# migration_backup.sh на beget

BACKUP_DIR=~/migration_backup_$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Архив файлов
cd ~/public_html
tar -czf $BACKUP_DIR/project.tar.gz \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  .

# Бэкап базы данных
mysqldump -u DB_USER -pDB_PASS DB_NAME > $BACKUP_DIR/database.sql

# Бэкап .env
cp .env $BACKUP_DIR/.env.backup

echo "Бэкап создан в: $BACKUP_DIR"
echo "Скопируйте папку на reg.ru:"
echo "scp -r $BACKUP_DIR user@reg.ru:~/"
```

---

## Чек-лист переноса

### Перед переносом:

- [ ] Создайте резервную копию проекта на beget
- [ ] Создайте резервную копию базы данных
- [ ] Запишите настройки .env с beget
- [ ] Проверьте версии PHP на обоих серверах
- [ ] Проверьте требования (extensions) PHP

### Файлы для переноса:

- [ ] Весь код проекта (кроме node_modules, vendor - установятся заново)
- [ ] Файл .env (скопировать и адаптировать)
- [ ] Медиа файлы (public/upload, storage/app/public)
- [ ] SQL дамп базы данных

### После переноса на reg.ru:

- [ ] Обновить .env с новыми настройками БД
- [ ] Установить зависимости: `composer install`
- [ ] Сгенерировать APP_KEY: `php artisan key:generate`
- [ ] Импортировать базу данных
- [ ] Настроить права: `chmod -R 775 storage bootstrap/cache`
- [ ] Выполнить миграции: `php artisan migrate --force`
- [ ] Очистить кеш: `php artisan config:clear && cache:clear`
- [ ] Проверить работу сайта

### Проверка после переноса:

- [ ] Сайт открывается в браузере
- [ ] База данных подключена (проверить логины, данные)
- [ ] Медиа файлы загружаются
- [ ] Нет ошибок в storage/logs/laravel.log
- [ ] Админ-панель работает (если есть)

---

## Рекомендации

**Самый простой вариант для начала:**
1. Используйте панели управления (beget → скачать, reg.ru → загрузить)
2. Или через rsync/scp, если есть SSH доступ к обоим

**Самый надежный:**
1. Git для кода
2. mysqldump для базы
3. rsync для медиа файлов

**Если проект большой:**
- Исключите `vendor`, `node_modules` из архива
- Установите зависимости заново на reg.ru

---

## Частые проблемы и решения

### Проблема: Ошибки с правами доступа
**Решение:** `chmod -R 775 storage bootstrap/cache`

### Проблема: Ошибки с foreign keys при импорте БД
**Решение:** Используйте `php artisan db:import-sql database.sql --skip-fk`

### Проблема: Неправильный APP_KEY
**Решение:** `php artisan key:generate`

### Проблема: Старые пути в конфигурации
**Решение:** Проверьте .env, очистите кеш: `php artisan config:clear`

