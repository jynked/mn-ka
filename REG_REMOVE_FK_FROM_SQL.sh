#!/bin/bash
# Скрипт для удаления foreign key constraints из SQL файла

INPUT_FILE="dsc23ytp_lag_crm.sql"
OUTPUT_FILE="dsc23ytp_lag_crm_no_fk.sql"

echo "Удаление FOREIGN KEY constraints из SQL файла..."

# Используем awk для более точного удаления блоков с FOREIGN KEY
awk '
BEGIN { skip=0; in_fk=0 }
/CONSTRAINT.*FOREIGN KEY|FOREIGN KEY/ { 
    in_fk=1
    # Если FK на той же строке что и CREATE TABLE, нужно удалить только часть строки
    if (match($0, /,CONSTRAINT.*FOREIGN KEY/)) {
        # Удаляем часть с CONSTRAINT до конца строки
        sub(/,CONSTRAINT.*$/, "");
        if ($0 !~ /^[[:space:]]*$/) print $0;
        skip=1;
        next;
    }
    if (match($0, /ADD CONSTRAINT.*FOREIGN KEY/)) {
        skip=1;
        next;
    }
}
in_fk && /REFERENCES/ {
    # Нашли REFERENCES, продолжаем пропускать до ON DELETE или конца
    skip=1;
    next;
}
in_fk && /ON DELETE/ {
    # Конец FK constraint
    skip=0;
    in_fk=0;
    next;
}
in_fk && /ON UPDATE/ {
    # Конец FK constraint с ON UPDATE
    skip=0;
    in_fk=0;
    next;
}
!skip { 
    in_fk=0;
    print 
}
' "$INPUT_FILE" > "$OUTPUT_FILE"

# Добавим заголовок и футер
cat > dsc23ytp_lag_crm_import.sql << 'HEADER'
SET FOREIGN_KEY_CHECKS=0;
SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';
HEADER

cat "$OUTPUT_FILE" >> dsc23ytp_lag_crm_import.sql

echo "" >> dsc23ytp_lag_crm_import.sql
echo "SET FOREIGN_KEY_CHECKS=1;" >> dsc23ytp_lag_crm_import.sql

echo "Файл создан: dsc23ytp_lag_crm_import.sql"

