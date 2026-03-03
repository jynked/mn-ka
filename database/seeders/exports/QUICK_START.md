# Быстрый старт: Развертывание данных на сервере

## ⚡ Самый быстрый способ: Использование команды deploy

**Рекомендуемый способ** - использовать команду `php artisan deploy` на локальной машине:

```bash
# На локальной машине (обычный deploy без seeders)
php artisan deploy --insecure

# С выполнением seeders (включая импорт данных)
php artisan deploy --insecure --with-seed
```

**Важно:** По умолчанию seeders НЕ выполняются при deploy. Для выполнения seeders используйте флаг `--with-seed`.

---

## 📋 Ручной способ: Краткая инструкция (5 минут)

### 1. Подключитесь к серверу

```bash
ssh user@your-server.com
cd /path/to/your/project
```

### 2. Проверьте файл экспорта

```bash
ls -la database/seeders/exports/products_services_export_2025-12-03_150726.json
```

### 3. Создайте резервную копию БД (рекомендуется)

```bash
mysqldump -u your_username -p your_database > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 4. Скопируйте медиа файлы (если еще не скопированы)

```bash
# С локальной машины
rsync -avz public/upload/ user@your-server.com:/path/to/your/project/public/upload/
```

### 5. Выполните базовые seeders

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=MenuSeeder
php artisan db:seed --class=AppCategorySeeder
php artisan db:seed --class=CasesBlockSettingsSeeder
php artisan db:seed --class=HowWorkBlockSettingsSeeder
php artisan db:seed --class=FaqBlockSettingsSeeder
php artisan db:seed --class=WhyChooseUsBlockSettingsSeeder
php artisan db:seed --class=AboutSettingsSeeder
php artisan db:seed --class=ContactSettingsSeeder
php artisan db:seed --class=FooterSettingsSeeder
```

### 6. Импортируйте данные

```bash
php artisan db:seed --class=ImportProductsServicesSeeder
```

### 7. Зарегистрируйте медиа файлы

```bash
php artisan db:seed --class=RegisterAllMediaFilesSeeder
php artisan db:seed --class=UpdateMediaFolderSeeder
```

### 8. Очистите кеш

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 9. Проверьте результат

Откройте сайт в браузере и убедитесь, что:
- ✅ Главная страница отображается с баннером
- ✅ Продукты и услуги загружаются
- ✅ Изображения отображаются корректно

## Полная инструкция

Для подробной инструкции с решением проблем см. [DEPLOYMENT.md](./DEPLOYMENT.md)

