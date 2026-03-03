# Исправление проблемы с Foreign Keys при импорте SQL

## Проблема
```
ERROR 1822: Failed to add the foreign key constraint. Missing index for constraint 'cases_image_cases_id_foreign' in the referenced table 'cases'
```

Это означает, что при создании внешнего ключа таблица `cases` еще не существует или не имеет нужных индексов.

## Решение 1: Удалить foreign key constraints из SQL файла, импортировать данные, затем создать FK через миграции

### Шаг 1: Создайте SQL файл без foreign keys

```bash
# Создайте версию без foreign keys
sed '/CONSTRAINT.*FOREIGN KEY/d' dsc23ytp_lag_crm.sql | \
sed '/ADD CONSTRAINT.*FOREIGN KEY/d' | \
sed '/FOREIGN KEY.*REFERENCES/d' > dsc23ytp_lag_crm_no_fk.sql

# Или более точная команда - удалить строки с FOREIGN KEY
grep -v "FOREIGN KEY" dsc23ytp_lag_crm.sql | \
grep -v "REFERENCES" > dsc23ytp_lag_crm_no_fk.sql || true

# Добавьте заголовок
cat > dsc23ytp_lag_crm_import.sql << 'HEADER'
SET FOREIGN_KEY_CHECKS=0;
SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';
HEADER

cat dsc23ytp_lag_crm_no_fk.sql >> dsc23ytp_lag_crm_import.sql

echo "" >> dsc23ytp_lag_crm_import.sql
echo "SET FOREIGN_KEY_CHECKS=1;" >> dsc23ytp_lag_crm_import.sql
```

## Решение 2: Использовать mysqldump с опциями для правильного экспорта (если возможно пересоздать дамп)

Но так как у нас уже есть SQL файл, лучше исправить его.

## Решение 3: Импортировать без foreign keys, затем создать их через Laravel миграции

После импорта данных выполните миграции Laravel, которые создадут foreign keys правильно:

```bash
# Импортируйте данные без foreign keys
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < dsc23ytp_lag_crm_import.sql

# Выполните миграции Laravel (они создадут foreign keys)
php artisan migrate --force
```

## Решение 4: Исправить SQL файл вручную - закомментировать проблемные строки

Но это сложно для большого файла.

## Решение 5: Использовать Python/PHP скрипт для обработки SQL файла

Самый надежный способ - написать скрипт, который удалит все FOREIGN KEY constraints из SQL файла.

