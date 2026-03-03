# Экспорт и импорт данных продуктов и сервисов

Этот набор seeders позволяет экспортировать все данные о продуктах, сервисах и связанных с ними данных из текущей базы данных в JSON файл, а затем импортировать их на сервере.

## Экспорт данных

Для экспорта всех данных о продуктах, сервисах и связанных с ними данных выполните:

```bash
php artisan db:seed --class=ExportProductsServicesSeeder
```

Это создаст JSON файл в директории `database/seeders/exports/` с именем вида:
`products_services_export_YYYY-MM-DD_HHMMSS.json`

### Что экспортируется:

- **Chapters** (Разделы) - все разделы/категории
- **Options** (Опции) - простые опции услуг
- **OptionTrees** (Деревья опций) - древовидные опции с дочерними элементами
- **Instances** (Экземпляры) - экземпляры услуг
- **Services** (Услуги) - все услуги с их изображениями и иконками
- **Products** (Продукты) - все продукты с их изображениями и иконками
- **Banners** (Баннеры) - все баннеры главной страницы
- **Relations** (Связи) - все связи между таблицами:
  - `product_service` - связи продуктов с услугами
  - `option_service` - связи опций с услугами
  - `option_tree_service` - связи деревьев опций с услугами
  - `instance_service` - связи экземпляров с услугами

### Информация о медиа файлах

В экспорт включается информация о медиа файлах (изображениях и иконках), но сами файлы не копируются. Убедитесь, что медиа файлы доступны на сервере в тех же путях, что указаны в экспорте.

## Импорт данных

Для импорта данных из экспортированного JSON файла выполните:

```bash
php artisan db:seed --class=ImportProductsServicesSeeder
```

Seeder автоматически найдет последний экспортированный файл в директории `database/seeders/exports/`.

### Указание конкретного файла

Вы можете указать конкретный файл для импорта через переменную окружения:

```bash
IMPORT_FILE=/path/to/export.json php artisan db:seed --class=ImportProductsServicesSeeder
```

Или в файле `.env`:

```
IMPORT_FILE=database/seeders/exports/products_services_export_2025-12-03_143000.json
```

### Порядок импорта

Данные импортируются в следующем порядке:

1. Chapters (Разделы)
2. Options (Опции)
3. OptionTrees (Деревья опций) - сначала корневые, затем дочерние
4. Instances (Экземпляры)
5. Media (Медиа файлы) - информация о файлах
6. Services (Услуги)
7. Products (Продукты)
8. Banners (Баннеры)
9. Relations (Связи) - все связи между таблицами

### Важно

- При импорте существующие записи обновляются по ID (используется `updateOrCreate`)
- Связи (pivot таблицы) полностью очищаются перед импортом (`truncate`)
- Медиа файлы должны быть доступны на сервере в тех же путях, что указаны в экспорте
- Если медиа файл уже существует в БД, он не будет перезаписан

## Использование на сервере

1. Экспортируйте данные на локальной машине:
   ```bash
   php artisan db:seed --class=ExportProductsServicesSeeder
   ```

2. Скопируйте JSON файл на сервер в директорию `database/seeders/exports/`

3. Убедитесь, что медиа файлы доступны на сервере (скопируйте их в соответствующие директории)

4. Импортируйте данные на сервере:
   ```bash
   php artisan db:seed --class=ImportProductsServicesSeeder
   ```

## Структура экспортированного JSON

```json
{
  "exported_at": "2025-12-03 14:30:00",
  "version": "1.0",
  "chapters": [...],
  "options": [...],
  "option_trees": [...],
  "instances": [...],
  "services": [
    {
      "id": 1,
      "name": "Название услуги",
      "slug": "slug-uslugi",
      "description": {...},
      "image_id": 123,
      "icon_id": 124,
      "image": {
        "id": 123,
        "name": "image.jpg",
        "disk": "upload/services",
        "path": "upload/services/image.jpg"
      },
      ...
    }
  ],
  "products": [...],
  "banners": [
    {
      "id": 1,
      "title": "Главный баннер",
      "slug": "home-banner",
      "background_image": "upload/banners/banner.jpg",
      "heading_1": "Заголовок 1",
      "heading_2": "Заголовок 2",
      "description": "Описание",
      "button_text": "Кнопка",
      "button_type": "url",
      "button_value": "/page",
      "height_desktop": 380,
      "height_mobile": 300,
      "is_active": true,
      "order": 0
    }
  ],
  "relations": {
    "product_service": [...],
    "option_service": [...],
    "option_tree_service": [...],
    "instance_service": [...]
  }
}
```

