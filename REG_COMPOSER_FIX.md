# Решение проблемы с Composer на reg.ru

## Проблема
```
-bash: composer: команда не найдена
```

Composer не установлен глобально или не в PATH.

## Решение

### Вариант 1: Использовать composer.phar из проекта (если есть)

```bash
cd ~/mn-ka.ru

# Проверьте, есть ли composer.phar в проекте
ls -la composer.phar

# Если есть, используйте его:
php composer.phar install --no-dev --optimize-autoloader
```

### Вариант 2: Найти путь к composer

```bash
# Поиск composer в системе
which composer
find /usr -name composer 2>/dev/null
find /usr/local -name composer 2>/dev/null

# Проверка альтернативных путей на reg.ru
ls -la /usr/local/bin/composer
ls -la /usr/bin/composer
ls -la ~/composer.phar
```

### Вариант 3: Установить composer локально (если нет)

```bash
cd ~/

# Скачайте composer
curl -sS https://getcomposer.org/installer | php

# Переместите в удобное место
mv composer.phar ~/composer

# Сделайте исполняемым
chmod +x ~/composer

# Используйте
~/composer install --no-dev --optimize-autoloader
```

### Вариант 4: Использовать полный путь (если composer установлен в нестандартном месте)

```bash
# На reg.ru composer может быть в нестандартном месте
# Попробуйте найти:
/usr/local/bin/composer install --no-dev --optimize-autoloader
# или
/usr/bin/composer install --no-dev --optimize-autoloader
```

### Вариант 5: Установить composer глобально (если есть права)

```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

---

## Рекомендуемый порядок проверки

```bash
cd ~/mn-ka.ru

# 1. Проверьте composer.phar в проекте
if [ -f "composer.phar" ]; then
    echo "Используем composer.phar из проекта"
    php composer.phar install --no-dev --optimize-autoloader
elif [ -f "/usr/local/bin/composer" ]; then
    echo "Используем /usr/local/bin/composer"
    /usr/local/bin/composer install --no-dev --optimize-autoloader
elif [ -f "~/composer" ]; then
    echo "Используем ~/composer"
    ~/composer install --no-dev --optimize-autoloader
else
    echo "Composer не найден, нужна установка"
fi
```

