<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тестовое письмо SMTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; border-radius: 8px; padding: 30px; border: 1px solid #e9ecef;">
        <h1 style="color: #28a745; margin-top: 0;">✅ Тестовое письмо SMTP</h1>
        
        <p>Это тестовое письмо было отправлено для проверки настроек SMTP сервера.</p>
        
        <div style="background-color: #fff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;"><strong>Время отправки:</strong> {{ now()->format('d.m.Y H:i:s') }}</p>
            <p style="margin: 5px 0 0 0;"><strong>Сервер:</strong> {{ config('mail.mailers.smtp.host') ?? 'Не указан' }}</p>
            <p style="margin: 5px 0 0 0;"><strong>Порт:</strong> {{ config('mail.mailers.smtp.port') ?? 'Не указан' }}</p>
            <p style="margin: 5px 0 0 0;"><strong>Шифрование:</strong> {{ config('mail.mailers.smtp.encryption') ?? 'Не указано' }}</p>
        </div>
        
        <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
            Если вы получили это письмо, значит настройки SMTP работают корректно! 🎉
        </p>
    </div>
</body>
</html>
