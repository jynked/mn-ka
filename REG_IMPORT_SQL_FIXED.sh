#!/bin/bash
# Скрипт для исправления и импорта SQL файла на REG хостинге

DB_USER="u3384357_lag_crm"
DB_PASS="g7F1TeRRp5&y"
DB_NAME="u3384357_lag_crm"
SQL_FILE="dsc23ytp_lag_crm.sql"
FIXED_SQL_FILE="dsc23ytp_lag_crm_fixed.sql"

echo "Создание исправленной версии SQL файла..."

# Создайте резервную копию оригинального файла
cp "$SQL_FILE" "${SQL_FILE}.backup"

# Создайте исправленный файл с отключенными проверками в начале
cat > "$FIXED_SQL_FILE" << 'HEADER'
SET FOREIGN_KEY_CHECKS=0;
SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';
HEADER

# Добавьте содержимое оригинального файла
cat "$SQL_FILE" >> "$FIXED_SQL_FILE"

# Добавьте включение проверок в конец
cat >> "$FIXED_SQL_FILE" << 'FOOTER'

SET FOREIGN_KEY_CHECKS=1;
FOOTER

echo "Импорт исправленного SQL файла..."

# Импортируйте исправленный файл
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$FIXED_SQL_FILE"

if [ $? -eq 0 ]; then
    echo "Импорт успешно завершен!"
    echo "Проверка данных..."
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;"
else
    echo "Ошибка при импорте. Проверьте логи выше."
    exit 1
fi

