# Проверка SSL сертификата

## Диагностика

```bash
cd /var/www/u3384357/data/www/mn-ka.ru

# 1. Проверьте сертификат через curl
openssl s_client -connect mn-ka.ru:443 -servername mn-ka.ru </dev/null 2>&1 | grep -i "verify\|certificate\|expire"

# 2. Проверьте цепочку сертификатов
openssl s_client -connect mn-ka.ru:443 -servername mn-ka.ru -showcerts </dev/null 2>&1 | grep -A 5 "Certificate chain"

# 3. Проверьте срок действия сертификата
echo | openssl s_client -connect mn-ka.ru:443 -servername mn-ka.ru 2>/dev/null | openssl x509 -noout -dates

# 4. Проверьте, что сертификат соответствует домену
echo | openssl s_client -connect mn-ka.ru:443 -servername mn-ka.ru 2>/dev/null | openssl x509 -noout -subject
```

## Возможные проблемы

1. **Смешанный контент** - на HTTPS странице загружаются HTTP ресурсы (CSS, JS, изображения)
   - Проверьте в консоли браузера на наличие предупреждений о смешанном контенте
   - Все ресурсы должны загружаться по HTTPS

2. **Неправильный домен в сертификате** - сертификат выдан для другого домена

3. **Цепочка сертификатов неполная** - отсутствуют промежуточные сертификаты

4. **Срок действия истек**

## Решение

Если проблема в смешанном контенте, убедитесь что в `.env` указан правильный `APP_URL`:
```env
APP_URL=https://mn-ka.ru
```

И очистите кеш:
```bash
php artisan config:clear
php artisan cache:clear
```

