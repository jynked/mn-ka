# Перенос проекта через Git: beget.ru → reg.ru

## Преимущества метода Git

✅ Автоматическая синхронизация кода  
✅ История изменений  
✅ Возможность отката  
✅ Исключение ручных ошибок при копировании  
✅ Легкое обновление в будущем  

---

## Шаг 1: Настройка Git на beget.ru

### 1.1. Проверьте, настроен ли Git

```bash
# Подключитесь к beget по SSH
ssh user@beget.ru

# Проверьте Git
cd ~/public_html
git status

# Если Git не настроен, инициализируйте:
git init
git remote add origin https://github.com/ваш-username/ваш-репозиторий.git
# или
git remote add origin git@github.com:ваш-username/ваш-репозиторий.git
```

### 1.2. Настройте .gitignore (если еще нет)

```bash
# Проверьте наличие .gitignore
cat .gitignore

# Если нет, создайте базовый .gitignore для Laravel
cat > .gitignore << 'EOF'
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.env.production
.phpunit.result.cache
Homestead.json
Homestead.yaml
auth.json
npm-debug.log
yarn-error.log
/.idea
/.vscode
*.log
EOF
```

### 1.3. Добавьте все файлы в Git

```bash
cd ~/public_html

# Добавьте все файлы (кроме тех, что в .gitignore)
git add .

# Проверьте, что будет закоммичено
git status

# Создайте коммит
git commit -m "Backup: полный бэкап проекта перед переносом на reg.ru $(date +%Y-%m-%d)"
```

### 1.4. Отправьте в Git репозиторий

```bash
# Если используете GitHub/GitLab/Bitbucket
git push origin main
# или
git push origin master

# Если репозиторий новый, используйте:
git push -u origin main
```

---

## Шаг 2: Клонирование на reg.ru

### 2.1. Клонируйте проект с Git

```bash
# Подключитесь к reg.ru по SSH
ssh user@reg.ru

# Перейдите в нужную директорию
cd ~/

# Клонируйте проект
git clone https://github.com/ваш-username/ваш-репозиторий.git mn-ka.ru
# или с SSH:
git clone git@github.com:ваш-username/ваш-репозиторий.git mn-ka.ru

# Перейдите в директорию проекта
cd mn-ka.ru
```

### 2.2. Альтернатива: Если проект уже существует на reg.ru

```bash
cd ~/mn-ka.ru

# Проверьте, есть ли уже Git
git status

# Если Git не настроен или нужно обновить:
git init
git remote add origin https://github.com/ваш-username/ваш-репозиторий.git

# Получите последние изменения
git fetch origin
git checkout main  # или master

# Если есть локальные изменения, можете принудительно обновить:
git fetch origin
git reset --hard origin/main
```

---

## Шаг 3: Настройка проекта на reg.ru

### 3.1. Установите зависимости

```bash
cd ~/mn-ka.ru

# Установите Composer зависимости
composer install --no-dev --optimize-autoloader

# Если используете npm
npm install
npm run build
```

### 3.2. Настройте .env

```bash
# Скопируйте .env.example
cp .env.example .env

# Отредактируйте .env
nano .env

# Измените настройки для reg.ru:
# DB_DATABASE=имя_базы_reg
# DB_USERNAME=пользователь_reg
# DB_PASSWORD=пароль_reg
# APP_URL=https://mn-ka.ru
```

### 3.3. Выполните установку проекта

```bash
# Сгенерируйте ключ приложения
php artisan key:generate

# Выполните установку (если есть SQL файл)
php artisan project:install --sql-file=dsc23ytp_lag_crm.sql --force

# Или пошагово:
php artisan migrate --force
php artisan db:import-sql dsc23ytp_lag_crm.sql --skip-fk  # если нужно
php artisan user:create  # создать администратора

# Настройте права
chmod -R 775 storage bootstrap/cache
chmod -R 755 .
```

---

## Шаг 4: Перенос SQL файла отдельно

SQL файл обычно не хранится в Git (слишком большой). Перенесите его отдельно:

### Вариант A: Через SCP

```bash
# С локальной машины или beget
scp user@beget.ru:~/public_html/dsc23ytp_lag_crm.sql user@reg.ru:~/

# Затем на reg.ru:
cd ~/mn-ka.ru
php artisan db:import-sql ~/dsc23ytp_lag_crm.sql --skip-fk
```

### Вариант B: Через панель управления

1. Скачайте `dsc23ytp_lag_crm.sql` с beget через File Manager
2. Загрузите на reg.ru через File Manager
3. Импортируйте через `php artisan db:import-sql`

### Вариант C: Хранить в Git LFS (для больших файлов)

```bash
# Установите Git LFS (если нужно)
git lfs install
git lfs track "*.sql"
git add .gitattributes
git add dsc23ytp_lag_crm.sql
git commit -m "Add SQL backup via LFS"
git push origin main
```

---

## Шаг 5: Настройка автосинхронизации (опционально)

### На reg.ru: Автоматический pull при обновлении

```bash
# Создайте скрипт для обновления
cat > ~/update-project.sh << 'EOF'
#!/bin/bash
cd ~/mn-ka.ru
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
EOF

chmod +x ~/update-project.sh

# Выполните обновление вручную когда нужно:
~/update-project.sh
```

---

## Быстрый скрипт для beget (все команды сразу)

```bash
#!/bin/bash
# git-backup-beget.sh - выполните на beget

cd ~/public_html

# Проверьте Git
if [ ! -d ".git" ]; then
    echo "Инициализация Git..."
    git init
fi

# Настройте remote (если еще не настроен)
# git remote add origin https://github.com/ваш-username/репозиторий.git

# Добавьте все изменения
git add .

# Создайте коммит
git commit -m "Backup: $(date +%Y-%m-%d_%H:%M:%S)"

# Отправьте в репозиторий
git push origin main

echo "✅ Проект отправлен в Git репозиторий"
```

---

## Быстрый скрипт для reg.ru

```bash
#!/bin/bash
# git-setup-reg.sh - выполните на reg.ru

cd ~/mn-ka.ru

# Если проект еще не клонирован:
# git clone https://github.com/ваш-username/репозиторий.git ~/mn-ka.ru

# Обновите проект
git pull origin main

# Установите зависимости
composer install --no-dev --optimize-autoloader

# Настройте .env (если еще не настроен)
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
    echo "⚠️  Настройте .env файл: nano .env"
fi

# Выполните миграции
php artisan migrate --force

# Настройте права
chmod -R 775 storage bootstrap/cache

echo "✅ Проект обновлен из Git"
```

---

## Проверка после переноса

```bash
# На reg.ru
cd ~/mn-ka.ru

# Проверьте статус Git
git status

# Проверьте подключение к БД
php artisan tinker --execute="echo 'DB: ' . DB::connection()->getDatabaseName();"

# Проверьте миграции
php artisan migrate:status

# Проверьте сайт
curl -I https://mn-ka.ru
```

---

## Обновление проекта в будущем

### На beget (после изменений):

```bash
cd ~/public_html
git add .
git commit -m "Описание изменений"
git push origin main
```

### На reg.ru (получить изменения):

```bash
cd ~/mn-ka.ru
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:cache
```

---

## Важные замечания

1. **Не коммитьте .env файл** - он должен быть в .gitignore
2. **SQL файлы** - обычно не хранят в Git (слишком большие), переносите отдельно
3. **vendor и node_modules** - не коммитятся (в .gitignore)
4. **storage/logs** - логи обычно не коммитятся

---

## Проблемы и решения

### Проблема: Git не установлен на beget

**Решение:** Попросите поддержку beget установить Git, или используйте панель управления для загрузки файлов.

### Проблема: Нет доступа к Git репозиторию

**Решение:** 
- Создайте репозиторий на GitHub/GitLab
- Используйте HTTPS с токеном доступа
- Или настройте SSH ключи

### Проблема: Конфликты при pull на reg.ru

**Решение:**
```bash
git stash
git pull origin main
git stash pop
# Решите конфликты вручную
```

---

**Этот метод - самый надежный и удобный для переноса и дальнейшего обслуживания проекта!** 🚀

