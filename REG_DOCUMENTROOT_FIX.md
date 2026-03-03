# Исправление проблемы DocumentRoot на reg.ru

## Проблема
Редирект работает, но перенаправляет на `https://mn-ka.ru/public/index.php` вместо `https://mn-ka.ru/`.

Это означает, что DocumentRoot настроен неправильно - указывает на корень проекта вместо `public/`.

## Решение

### Вариант 1: Настроить DocumentRoot в панели управления (рекомендуется)

1. Зайдите в панель управления reg.ru (ISPmanager)
2. Перейдите в **Сайты** → выберите `mn-ka.ru`
3. Найдите настройку **"Корневая директория"** или **"DocumentRoot"**
4. Измените на: `/var/www/u3384357/data/www/mn-ka.ru/public`
5. Сохраните изменения
6. Перезагрузите веб-сервер (обычно делается автоматически)

### Вариант 2: Проверить .htaccess в корне проекта

Если DocumentRoot указывает на корень проекта, `.htaccess` в корне должен перенаправлять в `public/`:

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# Проверьте .htaccess в корне
cat .htaccess

# Должен быть примерно такой:
# RewriteEngine on
# RewriteCond %{REQUEST_URI} !^/public/
# RewriteRule ^(.*)$ /public/$1
```

### Вариант 3: Проверьте конфигурацию в ISPmanager

В панели управления проверьте настройки сайта:
- **Директория сайта:** `/var/www/u3384357/data/www/mn-ka.ru`
- **Корневая директория (DocumentRoot):** должна быть `/var/www/u3384357/data/www/mn-ka.ru/public`

---

## Проверка после исправления

```bash
# Проверьте редирект
curl -I http://mn-ka.ru
# Должен вернуть: Location: https://mn-ka.ru/ (без /public/)

curl -I https://mn-ka.ru
# Должен вернуть 200 OK без редиректов
```

---

## Быстрая проверка DocumentRoot

```bash
# Создайте тестовый файл в public/
echo "test" > /var/www/u3384357/data/www/mn-ka.ru/public/test.txt

# Попробуйте открыть в браузере:
# https://mn-ka.ru/test.txt

# Если файл открывается - DocumentRoot правильный
# Если нет - DocumentRoot указывает на корень проекта
```

