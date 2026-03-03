# Исправление прав доступа к папке upload

## Проблема
`GET https://mn-ka.ru/upload/quiz/placeholder.jpg 403 (Forbidden)`

## Решение

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# 1. Проверьте существование папки upload
ls -la public/ | grep upload

# 2. Если папки нет, создайте её
mkdir -p public/upload/quiz
mkdir -p public/upload

# 3. Установите правильные права доступа
chmod -R 755 public/upload

# 4. Проверьте права
ls -la public/upload/

# 5. Если файлы должны быть в storage/app/public, создайте симлинк
php artisan storage:link

# 6. Проверьте, что файлы доступны
ls -la public/storage/ 2>/dev/null || echo "Symlink not created"
```

## Альтернатива: проверка структуры

Если файлы находятся в `storage/app/public/upload`, нужно создать симлинк:
```bash
php artisan storage:link
```

Это создаст `public/storage` → `storage/app/public`

