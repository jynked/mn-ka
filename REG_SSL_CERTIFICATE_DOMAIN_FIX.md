# Исправление проблемы с SSL сертификатом

## Проблема
Сертификат выдан для `www.mn-ka.ru`, а сайт открывается на `mn-ka.ru` (без www). Это вызывает несоответствие домена сертификату.

## Решение 1: Редирект на www (если сертификат только для www)

Добавьте в корневой `.htaccess` редирект на www:

```apache
# Redirect non-www to www (before HTTPS redirect if exists)
RewriteCond %{HTTP_HOST} ^mn-ka\.ru$ [NC]
RewriteRule ^(.*)$ https://www.mn-ka.ru/$1 [L,R=301]
```

## Решение 2: Установить сертификат для обоих доменов

В ISPmanager:
1. Откройте "SSL-сертификаты"
2. Установите сертификат, который покрывает оба домена:
   - `mn-ka.ru`
   - `www.mn-ka.ru`
3. Или используйте wildcard сертификат `*.mn-ka.ru`

## Решение 3: Редирект www на без www (если установлен сертификат для обоих)

Если у вас есть сертификат для обоих доменов:
```apache
# Redirect www to non-www
RewriteCond %{HTTP_HOST} ^www\.mn-ka\.ru$ [NC]
RewriteRule ^(.*)$ https://mn-ka.ru/$1 [L,R=301]
```

## Рекомендация

Рекомендуется использовать Решение 1 (редирект на www), так как сертификат уже выдан для `www.mn-ka.ru`.

