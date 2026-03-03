# Обновление проекта на beget.ru из нового Git репозитория

## Проблема

Remote `origin` указывает на старый репозиторий `lagom-crm`, нужно обновить на `mn-ka`.

## Решение

### Шаг 1: Измените URL remote

```bash
# Проверьте текущий remote
git remote -v

# Измените URL на новый репозиторий
git remote set-url origin https://github.com/letoceiling-coder/mn-ka.git

# Проверьте, что изменилось
git remote -v
```

### Шаг 2: Получите код из нового репозитория

```bash
# Получите изменения из нового репозитория
git fetch origin

# Принудительно обновите код (все локальные изменения будут потеряны)
git reset --hard origin/main

# Или если хотите сохранить локальные изменения, используйте merge:
# git merge origin/main --allow-unrelated-histories
```

### Шаг 3: Проверка

```bash
# Проверьте статус
git status

# Проверьте текущий коммит
git log --oneline -5
```

---

## Полная последовательность команд

```bash
# На beget.ru
cd ~/stroy/public_html

# 1. Измените remote
git remote set-url origin https://github.com/letoceiling-coder/mn-ka.git

# 2. Получите код из нового репозитория
git fetch origin

# 3. Принудительно обновите (⚠️ локальные изменения будут потеряны)
git reset --hard origin/main

# 4. Проверьте результат
git status
git log --oneline -3
```

---

## Если нужно сохранить локальные изменения

Если на beget есть локальные изменения, которые нужно сохранить:

```bash
# Создайте бэкап текущих изменений
git stash

# Или создайте новую ветку с текущим состоянием
git branch backup-before-update

# Затем обновите
git fetch origin
git reset --hard origin/main
```

---

## Альтернатива: Клонирование в новую директорию

Если не уверены, можно клонировать в новую директорию для проверки:

```bash
# Клонируйте в временную директорию
cd ~/
git clone https://github.com/letoceiling-coder/mn-ka.git mn-ka-check

# Проверьте содержимое
cd mn-ka-check
ls -la

# Если все хорошо, можно заменить старую директорию
```

