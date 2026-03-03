# Установка Composer на сервере

## Вариант 1: Установка composer.phar в проект (РЕКОМЕНДУЕТСЯ)

Этот вариант самый надежный, так как composer будет всегда доступен в проекте.

### Шаги установки:

1. **Подключитесь к серверу по SSH:**
```bash
ssh dsc23ytp@post-ads.ru
```

2. **Перейдите в директорию проекта:**
```bash
cd /home/d/dsc23ytp/stroy/public_html
```

3. **Скачайте composer.phar:**
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=. --filename=composer.phar
php -r "unlink('composer-setup.php');"
```

4. **Сделайте composer.phar исполняемым:**
```bash
chmod +x composer.phar
```

5. **Проверьте установку:**
```bash
php composer.phar --version
```

6. **Добавьте в .env файл проекта:**
```bash
COMPOSER_PATH=/home/d/dsc23ytp/stroy/public_html/composer.phar
```

## Вариант 2: Установка composer глобально в систему

### Шаги установки:

1. **Подключитесь к серверу по SSH:**
```bash
ssh dsc23ytp@post-ads.ru
```

2. **Скачайте и установите composer:**
```bash
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=~/.local/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

3. **Сделайте composer исполняемым:**
```bash
chmod +x ~/.local/bin/composer
```

4. **Проверьте установку:**
```bash
~/.local/bin/composer --version
```

5. **Добавьте в PATH (опционально):**
Добавьте в `~/.bashrc` или `~/.bash_profile`:
```bash
export PATH="$HOME/.local/bin:$PATH"
```

## Вариант 3: Установка через пакетный менеджер (если есть root доступ)

```bash
# Для Ubuntu/Debian
sudo apt update
sudo apt install composer

# Для CentOS/RHEL
sudo yum install composer
```

## Проверка после установки

После установки любого из вариантов, проверьте:

1. **Локально обновите .env файл** (если использовали вариант 1):
   - Добавьте `COMPOSER_PATH=/home/d/dsc23ytp/stroy/public_html/composer.phar`

2. **Или обновите код** - код уже обновлен для автоматического поиска composer.phar в проекте

3. **Запустите деплой снова:**
```bash
php artisan deploy --insecure
```

## Автоматическая установка composer.phar

Код обновлен для автоматической проверки и установки composer.phar в проект, если его нет. При следующем деплое система попытается установить composer.phar автоматически.





