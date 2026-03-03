# Проверка DocumentRoot на reg.ru

## Проблема
Редирект ведет на `https://mn-ka.ru/public/index.php` вместо `https://mn-ka.ru/`.

## Решение

### Вариант 1: Проверить DocumentRoot через SSH

```bash
# Проверьте конфигурацию Apache/Nginx
grep -r "DocumentRoot\|root" /etc/apache2/sites-enabled/* 2>/dev/null | grep mn-ka
grep -r "root" /etc/nginx/sites-enabled/* 2>/dev/null | grep mn-ka

# Или проверьте через ISPmanager API/конфиги
```

### Вариант 2: В панели ISPmanager

1. Зайдите в настройки сайта `mn-ka.ru`
2. Найдите раздел "Дополнительные настройки" или "Расширенные"
3. Ищите поле "DocumentRoot" или "Корневая директория"
4. Установите: `public` или `/var/www/u3384357/data/www/mn-ka.ru/public`

### Вариант 3: Если нет возможности изменить DocumentRoot

Используйте симлинк или проверьте настройки виртуального хоста.

---

## Альтернатива: Оставить как есть

Если DocumentRoot должен быть в корне, это нормально. URL с `/public/` будет работать, но можно скрыть это через .htaccess или настройки сервера.

