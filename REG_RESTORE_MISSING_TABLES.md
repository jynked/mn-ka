# Восстановление отсутствующих таблиц после импорта

## Проблема
Таблицы `cases` и `products` не были созданы при импорте SQL файла, хотя они есть в SQL дампе.

## Решение 1: Создать таблицы через миграции Laravel

```bash
# Попробуйте выполнить миграции заново (только новые/отсутствующие таблицы)
php artisan migrate:refresh --path=database/migrations/2025_11_29_191653_create_products_table.php
php artisan migrate:refresh --path=database/migrations/2025_11_30_152211_create_cases_table.php
```

## Решение 2: Извлечь и импортировать только эти таблицы из SQL

```bash
# Извлеките CREATE TABLE для products и cases из SQL файла
grep -A 100 "CREATE TABLE \`products\`" dsc23ytp_lag_crm.sql | grep -m 1 -B 100 ";" > products_table.sql

# Или попробуйте выполнить миграции заново
php artisan migrate:fresh --path=database/migrations --seed
```

## Решение 3: Проверить, почему таблицы не создались

Возможно, были ошибки при создании этих таблиц. Проверьте логи или попробуйте создать их вручную.

