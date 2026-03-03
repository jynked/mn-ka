<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая обратная связь</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1b1b18;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #688E67 0%, #5a7a5a 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .info-block {
            background-color: #f8f9fa;
            border-left: 4px solid #688E67;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .info-value {
            font-size: 16px;
            color: #1b1b18;
            font-weight: 500;
        }
        .message-block {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            margin-top: 25px;
        }
        .message-label {
            font-size: 14px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }
        .message-text {
            font-size: 16px;
            color: #1b1b18;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer-text {
            font-size: 14px;
            color: #6c757d;
            margin: 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #688E67;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        .icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Новая обратная связь</h1>
        </div>
        
        <div class="content">
            <div class="info-block">
                <div class="info-label">👤 Имя</div>
                <div class="info-value">{{ $feedbackRequest->name }}</div>
            </div>
            
            @if($feedbackRequest->phone)
            <div class="info-block">
                <div class="info-label">📞 Телефон</div>
                <div class="info-value">{{ $feedbackRequest->phone }}</div>
            </div>
            @endif
            
            @if($feedbackRequest->email)
            <div class="info-block">
                <div class="info-label">📧 Email</div>
                <div class="info-value">{{ $feedbackRequest->email }}</div>
            </div>
            @endif
            
            <div class="message-block">
                <div class="message-label">💬 Сообщение</div>
                <div class="message-text">{{ $feedbackRequest->comment ?? $feedbackRequest->message ?? '' }}</div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/admin/product-requests') }}" class="button">
                    Просмотреть в админке
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p class="footer-text">
                Это автоматическое уведомление о новой обратной связи с сайта.<br>
                Дата получения: {{ $feedbackRequest->created_at->format('d.m.Y H:i') }}
            </p>
        </div>
    </div>
</body>
</html>

