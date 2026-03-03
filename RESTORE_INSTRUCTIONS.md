# Инструкция по восстановлению проекта на сервере до 17.12.2025

## Важно!
После выполнения этих команд проект будет откачен к состоянию на 12.12.2025 (последний коммит до 17.12.2025). Все изменения после этой даты будут удалены.

## Шаги восстановления:

### 1. Подключитесь к серверу по SSH

### 2. Перейдите в директорию проекта
```bash
cd /path/to/lagom-figma
```

### 3. Проверьте текущий статус git
```bash
git status
git log --oneline -10
```

### 4. Откатите проект к коммиту от 12.12.2025
```bash
git fetch origin
git reset --hard 8efd677
```

Или если нужен последний коммит до 17.12.2025:
```bash
git reset --hard $(git rev-list -n 1 --before="2025-12-17" HEAD)
```

### 5. Очистите рабочую директорию от незакоммиченных изменений
```bash
git clean -fd
```

### 6. Если нужно удалить коммиты из удалённого репозитория (ОСТОРОЖНО!)
```bash
git push --force origin main
```
⚠️ **ВНИМАНИЕ:** Force push удалит коммиты из удалённого репозитория. Убедитесь, что никто не работает с этими коммитами.

### 7. Установите зависимости (если папка vendor отсутствует или обновилась)
```bash
composer install --no-dev --optimize-autoloader
```

Для разработки:
```bash
composer install
```

### 8. Очистите кеши Laravel
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 9. Опционально: запустите миграции (если база данных изменилась)
```bash
php artisan migrate
```

### 10. Проверьте статус
```bash
git status
php artisan --version
```

## Проверка коммита перед откатом

Чтобы посмотреть коммит, к которому будет выполнен откат:
```bash
git log --oneline --date=short --format="%h %ad %s" --until="2025-12-17" -5
```

## Альтернативный вариант: через checkout

Если нужно только переключиться без удаления коммитов:
```bash
git checkout 8efd677
git checkout -b restore-before-2025-12-17
```

## Восстановление после отката (если понадобилось)

Если нужно вернуть изменения:
```bash
git reflog
git reset --hard HEAD@{N}  # где N - номер нужного состояния
```

## Быстрая команда (все в одном)

```bash
cd /path/to/lagom-figma && \
git fetch origin && \
git reset --hard 8efd677 && \
git clean -fd && \
composer install --no-dev --optimize-autoloader && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan view:clear
```

---

**Дата создания:** 2025-12-19
**Коммит для отката:** 8efd677 (Deploy: 2025-12-12 10:29:36)





