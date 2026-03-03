# Исправление проблемы с Composer при деплое

## Что было сделано

1. **Обновлен метод `getComposerPath()`** в `DeployController.php`:
   - Добавлена проверка `composer.phar` в корне проекта (приоритетный вариант)
   - Добавлена автоматическая установка `composer.phar` если его нет
   - Сохранена обратная совместимость со старыми путями

2. **Добавлен метод `installComposerPhar()`**:
   - Автоматически скачивает и устанавливает `composer.phar` в проект
   - Работает при первом деплое, если composer не найден

## Как проверить

### Вариант 1: Автоматическая установка (рекомендуется)

Просто запустите деплой снова - система автоматически установит `composer.phar` в проект:

```bash
php artisan deploy --insecure
```

При первом запуске система:
1. Проверит наличие `composer.phar` в проекте
2. Если нет - автоматически скачает и установит его
3. Использует установленный `composer.phar` для дальнейших операций

### Вариант 2: Ручная установка на сервере

Если автоматическая установка не сработает, выполните на сервере:

```bash
# Подключитесь к серверу
ssh dsc23ytp@post-ads.ru

# Перейдите в проект
cd /home/d/dsc23ytp/stroy/public_html

# Установите composer.phar
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=. --filename=composer.phar
php -r "unlink('composer-setup.php');"
chmod +x composer.phar

# Проверьте
php composer.phar --version
```

После этого запустите деплой снова локально.

## Порядок проверки путей к composer

Система проверяет пути в следующем порядке:

1. ✅ `COMPOSER_PATH` из `.env` (если указан)
2. ✅ `composer.phar` в корне проекта (автоматическая установка если нет)
3. ✅ `/home/d/dsc23ytp/.local/bin/composer`
4. ✅ `/usr/local/bin/composer`
5. ✅ `/usr/bin/composer`
6. ✅ `composer` из PATH (через `which composer`)

## Логи

При деплое проверьте логи на сервере:
```bash
tail -f storage/logs/laravel.log
```

Вы должны увидеть сообщения:
- `Попытка автоматической установки composer.phar...` (если composer не найден)
- `composer.phar успешно установлен в проект` (при успешной установке)

## Если проблемы остались

1. Проверьте права доступа на директорию проекта на сервере
2. Проверьте доступность интернета с сервера (для скачивания composer installer)
3. Установите composer вручную по инструкции в `COMPOSER_INSTALL.md`





