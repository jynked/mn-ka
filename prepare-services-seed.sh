#!/bin/bash
# Скрипт для подготовки файлов Services CSV для загрузки на сервер
# Использование: ./prepare-services-seed.sh

SOURCE_PATH="/path/to/111_extracted"
TARGET_PATH="storage/app/services-seed"

echo "📦 Подготовка файлов для Services CSV Seeder..."

# Проверяем наличие исходных файлов
if [ ! -f "$SOURCE_PATH/services.csv" ]; then
    echo "❌ Файл services.csv не найден в $SOURCE_PATH"
    exit 1
fi

if [ ! -d "$SOURCE_PATH/images" ]; then
    echo "❌ Папка images не найдена в $SOURCE_PATH"
    exit 1
fi

# Создаем целевую папку
mkdir -p "$TARGET_PATH"
echo "✓ Создана папка: $TARGET_PATH"

# Копируем CSV файл
cp "$SOURCE_PATH/services.csv" "$TARGET_PATH/services.csv"
echo "✓ Скопирован services.csv"

# Копируем папку images
if [ -d "$TARGET_PATH/images" ]; then
    rm -rf "$TARGET_PATH/images"
fi
cp -r "$SOURCE_PATH/images" "$TARGET_PATH/images"
echo "✓ Скопирована папка images"

# Создаем архив для загрузки на сервер
ARCHIVE_PATH="storage/app/services-seed.zip"
if [ -f "$ARCHIVE_PATH" ]; then
    rm -f "$ARCHIVE_PATH"
fi

cd "$TARGET_PATH"
zip -r "../services-seed.zip" .
cd - > /dev/null
echo "✓ Создан архив: $ARCHIVE_PATH"

echo ""
echo "✅ Подготовка завершена!"
echo ""
echo "📋 Следующие шаги:"
echo "1. Загрузите архив $ARCHIVE_PATH на сервер"
echo "2. Распакуйте его в /home/d/dsc23ytp/stroy/public_html/storage/app/"
echo "3. Выполните: php artisan db:seed --class=ServicesFromCsvSeeder"

