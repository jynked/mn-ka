# Быстрый старт миграции данных

## Шаг 1: Убедитесь, что миграции выполнены

```bash
php artisan migrate
```

## Шаг 2: Запустите команду миграции

### Простой вариант (интерактивный)
```bash
php artisan migrate:legacy-data
```

Команда спросит:
- Legacy database name (например: `lagom_db`)
- Legacy database password

### С параметрами (рекомендуется)
```bash
php artisan migrate:legacy-data \
    --legacy-db-name="lagom_db" \
    --legacy-db-pass="ваш_пароль"
```

Если проект находится не в `C:\OSPanel\domains\lagom`:
```bash
php artisan migrate:legacy-data \
    --legacy-path="путь_к_старому_проекту" \
    --legacy-db-name="lagom_db" \
    --legacy-db-pass="ваш_пароль"
```

## Шаг 3: Дождитесь завершения

Команда покажет прогресс для каждого типа данных:
- ✅ Media files - файлы изображений
- ✅ Chapters - разделы
- ✅ Products - продукты  
- ✅ Services - услуги
- ✅ Relationships - связи между продуктами и услугами

## Готово! 

После завершения все данные будут в новом проекте.

## Полная документация

См. `MIGRATION_README.md` для детальной информации.

