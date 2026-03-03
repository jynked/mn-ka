# Настройка Git репозитория для переноса на reg.ru

## Текущий статус

✅ Git инициализирован  
✅ Есть remote origin  
✅ .gitignore настроен правильно  

## Новый репозиторий

**GitHub:** https://github.com/letoceiling-coder/mn-ka.git

---

## Шаг 1: Измените remote на новый репозиторий

```bash
# Проверьте текущий remote
git remote -v

# Измените URL на новый репозиторий
git remote set-url origin https://github.com/letoceiling-coder/mn-ka.git

# Или добавьте новый remote (если хотите сохранить старый)
git remote add mn-ka https://github.com/letoceiling-coder/mn-ka.git
git remote set-url origin https://github.com/letoceiling-coder/mn-ka.git

# Проверьте
git remote -v
```

## Шаг 2: Добавьте все изменения

```bash
# Добавьте все изменения (модифицированные и новые файлы)
git add .

# Проверьте, что будет закоммичено
git status
```

## Шаг 3: Создайте коммит

```bash
# Создайте коммит с описанием
git commit -m "Backup: подготовка к переносу на reg.ru

- Исправлена миграция add_protected_to_folders_table
- Добавлена команда project:install для полной установки
- Добавлена команда db:import-sql для импорта SQL
- Добавлена команда project:check для проверки проекта
- Обновлена документация по переносу на reg.ru"
```

## Шаг 4: Отправьте на GitHub

```bash
# Отправьте на новый репозиторий
git push -u origin main

# Если возникнет ошибка о разных histories, используйте:
git push -u origin main --force
```

---

## Быстрая команда (все сразу)

```bash
cd C:\OSPanel\domains\lagom-figma

# Измените remote
git remote set-url origin https://github.com/letoceiling-coder/mn-ka.git

# Добавьте все изменения
git add .

# Закоммитьте
git commit -m "Backup: подготовка к переносу на reg.ru - $(Get-Date -Format 'yyyy-MM-dd')"

# Отправьте на GitHub
git push -u origin main
```

---

## Проверка после отправки

1. Откройте https://github.com/letoceiling-coder/mn-ka в браузере
2. Убедитесь, что все файлы загружены
3. Проверьте, что .env НЕ в репозитории (должен быть в .gitignore)

---

## После отправки на beget и reg.ru

### На beget.ru:

```bash
ssh user@beget.ru
cd ~/public_html

# Если Git еще не настроен
git init
git remote add origin https://github.com/letoceiling-coder/mn-ka.git

# Получите код из GitHub
git pull origin main

# Или если проект уже существует
git fetch origin
git reset --hard origin/main
```

### На reg.ru:

```bash
ssh user@reg.ru

# Клонируйте проект
git clone https://github.com/letoceiling-coder/mn-ka.git mn-ka.ru

cd mn-ka.ru

# Настройте проект
cp .env.example .env
nano .env  # Настройте БД для reg.ru
composer install --no-dev --optimize-autoloader
php artisan key:generate

# Импортируйте SQL (перенесите файл отдельно)
php artisan db:import-sql dsc23ytp_lag_crm.sql --skip-fk

# Установите проект
php artisan project:install --skip-import --force

# Права
chmod -R 775 storage bootstrap/cache
```

---

## Важные файлы НЕ в Git

Благодаря .gitignore эти файлы НЕ будут закоммичены:

- `.env` - создайте из .env.example на сервере
- `vendor/` - установится через composer install
- `node_modules/` - установится через npm install
- `storage/logs/*.log` - логи создаются автоматически
- `dsc23ytp_lag_crm.sql` - перенесите отдельно (большой файл)

---

**Готово! После выполнения команд ваш проект будет на GitHub и готов к клонированию на beget и reg.ru** 🚀

