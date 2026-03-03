#!/bin/bash

# Команды для полного обновления проекта на сервере

# 1. Обновление кода из git
git fetch origin
git pull origin main

# 2. Установка/обновление зависимостей Composer
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Очистка всех кешей Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Пересоздание кешей (оптимизация для продакшена)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Запуск миграций базы данных
php artisan migrate --force

# 6. Очистка старого кеша (опционально)
php artisan optimize:clear

# 7. Создание символической ссылки для storage (если нужно)
php artisan storage:link

# 8. Оптимизация приложения (для продакшена)
php artisan optimize





