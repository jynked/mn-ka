#!/bin/bash

# Скрипт для настройки прав доступа Laravel на REG хостинге
# Использование: bash setup-permissions.sh

echo "========================================="
echo "Настройка прав доступа для Laravel"
echo "========================================="
echo ""

# Получаем путь к корню проекта
PROJECT_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "Директория проекта: $PROJECT_ROOT"
cd "$PROJECT_ROOT"

# Проверяем, что мы в правильной директории
if [ ! -f "artisan" ]; then
    echo "ОШИБКА: Файл artisan не найден. Убедитесь, что вы находитесь в корне Laravel проекта."
    exit 1
fi

echo ""
echo "Шаг 1: Создание необходимых директорий..."
echo "----------------------------------------"

# Создаем все необходимые директории
directories=(
    "storage/logs"
    "storage/framework/cache/data"
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/app/public"
    "bootstrap/cache"
)

for dir in "${directories[@]}"; do
    if [ ! -d "$dir" ]; then
        echo "Создание директории: $dir"
        mkdir -p "$dir"
        
        if [ $? -eq 0 ]; then
            echo "  ✓ Создана"
        else
            echo "  ✗ Ошибка при создании"
        fi
    else
        echo "  ✓ Уже существует: $dir"
    fi
done

echo ""
echo "Шаг 2: Создание файлов .gitkeep (если нужно)..."
echo "----------------------------------------"

# Создаем .gitkeep файлы
for dir in "${directories[@]}"; do
    gitkeep_file="$dir/.gitkeep"
    if [ ! -f "$gitkeep_file" ]; then
        touch "$gitkeep_file"
        echo "  ✓ Создан: $gitkeep_file"
    fi
done

echo ""
echo "Шаг 3: Установка прав доступа..."
echo "----------------------------------------"

# Устанавливаем права 775 для storage и bootstrap/cache
echo "Установка прав 775 для storage..."
chmod -R 775 storage
if [ $? -eq 0 ]; then
    echo "  ✓ Права установлены для storage"
else
    echo "  ✗ Ошибка при установке прав для storage"
    echo "  Попробуйте выполнить команду вручную: chmod -R 775 storage"
fi

echo "Установка прав 775 для bootstrap/cache..."
chmod -R 775 bootstrap/cache
if [ $? -eq 0 ]; then
    echo "  ✓ Права установлены для bootstrap/cache"
else
    echo "  ✗ Ошибка при установке прав для bootstrap/cache"
    echo "  Попробуйте выполнить команду вручную: chmod -R 775 bootstrap/cache"
fi

echo ""
echo "Шаг 4: Проверка прав доступа..."
echo "----------------------------------------"

# Проверяем права на основные директории
check_permissions() {
    local path=$1
    if [ -d "$path" ]; then
        perms=$(stat -c "%a" "$path" 2>/dev/null || stat -f "%OLp" "$path" 2>/dev/null || echo "неизвестно")
        echo "  $path: $perms"
    else
        echo "  $path: не существует"
    fi
}

echo "Права доступа:"
check_permissions "storage"
check_permissions "storage/logs"
check_permissions "storage/framework"
check_permissions "bootstrap/cache"

echo ""
echo "Шаг 5: Тест записи..."
echo "----------------------------------------"

# Пробуем создать тестовый файл
test_file="storage/logs/permission-test-$(date +%s).txt"
echo "test" > "$test_file" 2>/dev/null

if [ $? -eq 0 ] && [ -f "$test_file" ]; then
    echo "  ✓ Запись в storage/logs работает"
    rm "$test_file"
else
    echo "  ✗ Ошибка записи в storage/logs"
    echo "  Возможно, нужны дополнительные настройки или права 777"
fi

echo ""
echo "========================================="
echo "Настройка завершена!"
echo "========================================="
echo ""
echo "Следующие шаги:"
echo "1. Проверьте вывод выше на наличие ошибок"
echo "2. Попробуйте выполнить: php artisan migrate"
echo ""
echo "Если проблема сохраняется:"
echo "- Попробуйте установить права 777: chmod -R 777 storage bootstrap/cache"
echo "- Проверьте пользователя веб-сервера: ps aux | grep -E 'nginx|apache|php-fpm'"
echo "- Обратитесь в поддержку хостинга"
echo ""

