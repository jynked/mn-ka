# Исправление проблемы 404: Apache не направляет запросы в Laravel

## Проблема

Маршрут зарегистрирован в Laravel, но Apache возвращает 404. Это означает, что запросы не доходят до Laravel.

## Причины

1. **DocumentRoot не указывает на `public/` директорию**
2. **mod_rewrite не включен**
3. **`.htaccess` не работает**

## Решение

### Шаг 1: Проверьте DocumentRoot

Проверьте, что Apache настроен на использование `public/` директории:

```bash
# Проверьте конфигурацию Apache
# Для виртуального хоста должно быть:
# DocumentRoot /home/d/dsc23ytp/stroy/public_html/public
```

### Шаг 2: Проверьте .htaccess

Убедитесь, что файл `.htaccess` существует в `public/`:

```bash
cd /home/d/dsc23ytp/stroy/public_html
ls -la public/.htaccess

# Если файла нет, создайте его из репозитория
cat public/.htaccess
```

### Шаг 3: Проверьте mod_rewrite

Убедитесь, что mod_rewrite включен в Apache. Если нет прав для проверки, попросите администратора.

### Шаг 4: Проверьте через HTTPS

Попробуйте использовать HTTPS вместо HTTP:

```bash
curl -X POST https://post-ads.ru/api/deploy \
  -H "Content-Type: application/json" \
  -v
```

### Шаг 5: Проверьте URL через браузер

Откройте в браузере:
- `https://post-ads.ru/api/deploy` (должен вернуть ошибку, но не 404)
- `https://post-ads.ru` (должна открыться главная страница)

---

## Альтернативное решение: Проверка через прямую ссылку

Попробуйте обратиться напрямую к `index.php`:

```bash
curl -X POST https://post-ads.ru/index.php/api/deploy \
  -H "Content-Type: application/json" \
  -v
```

Если это работает, значит проблема в `.htaccess` или mod_rewrite.

---

## Если используете поддиректорию

Если Laravel установлен в поддиректории (не в корне домена), нужно настроить `.htaccess`:

```apache
RewriteBase /поддиректория/public/
```

---

## Проверка конфигурации на хостинге

Для хостинга Beget (судя по домену `dragon.beget.ru`):

1. **Проверьте в панели управления Beget:**
   - Настройки сайта
   - DocumentRoot должен указывать на `public_html/public`
   
2. **Или создайте симлинк:**
   ```bash
   # НЕ выполняйте без понимания! Это может сломать сайт!
   # Только если уверены, что нужно
   ```

---

## Быстрая проверка

Выполните на сервере:

```bash
cd /home/d/dsc23ytp/stroy/public_html

# 1. Проверьте структуру
ls -la public/

# 2. Проверьте .htaccess
cat public/.htaccess

# 3. Проверьте, существует ли index.php
ls -la public/index.php

# 4. Попробуйте прямой доступ
curl -I https://post-ads.ru/
curl -I https://post-ads.ru/index.php
```

---

## Если ничего не помогает

Проверьте логи Apache:

```bash
tail -f /var/log/apache2/error.log
# или
tail -f /var/log/httpd/error_log
```

И попробуйте снова отправить запрос - в логах будет информация о проблеме.

