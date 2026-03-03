<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты квиза</title>
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
        }
        .header {
            background: linear-gradient(135deg, #657C6C 0%, #55695a 100%);
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
        .greeting {
            font-size: 18px;
            margin-bottom: 30px;
            color: #1b1b18;
        }
        .quiz-title {
            font-size: 24px;
            font-weight: 600;
            color: #657C6C;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #657C6C;
        }
        .answers-section {
            margin-top: 30px;
        }
        .answers-title {
            font-size: 20px;
            font-weight: 600;
            color: #1b1b18;
            margin-bottom: 20px;
        }
        .answer-item {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #657C6C;
        }
        .answer-question {
            font-weight: 600;
            color: #1b1b18;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .answer-value {
            color: #555;
            font-size: 15px;
        }
        .contact-info {
            background-color: #f0f7f0;
            padding: 25px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .contact-title {
            font-size: 18px;
            font-weight: 600;
            color: #657C6C;
            margin-bottom: 15px;
        }
        .contact-item {
            margin-bottom: 10px;
            font-size: 15px;
        }
        .contact-label {
            font-weight: 600;
            color: #1b1b18;
            display: inline-block;
            min-width: 120px;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #657C6C;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: 600;
        }
        .thank-you {
            text-align: center;
            padding: 30px 0;
            font-size: 18px;
            color: #657C6C;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Спасибо за прохождение квиза!</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Здравствуйте, <strong>{{ $contact['name'] }}</strong>!
            </div>
            
            <div class="quiz-title">
                {{ $quiz->title }}
            </div>
            
            @if($quiz->description)
            <p style="color: #666; font-size: 15px; margin-bottom: 30px;">
                {{ $quiz->description }}
            </p>
            @endif
            
            <div class="answers-section">
                <div class="answers-title">Ваши ответы:</div>
                
                @php
                    $questions = $quiz->questions->sortBy('order');
                @endphp
                
                @foreach($answers as $index => $answer)
                    @php
                        $question = $questions->get($index);
                        $questionText = $question ? $question->question_text : "Вопрос " . ($index + 1);
                    @endphp
                    <div class="answer-item">
                        <div class="answer-question">
                            {{ $questionText }}
                        </div>
                        <div class="answer-value">
                            @if(is_array($answer))
                                @if(isset($answer['text']))
                                    {{ $answer['text'] }}
                                @elseif(isset($answer['title']))
                                    {{ $answer['title'] }}
                                @elseif(isset($answer['name']))
                                    {{ $answer['name'] }}
                                @else
                                    {{ is_string($answer) ? $answer : json_encode($answer, JSON_UNESCAPED_UNICODE) }}
                                @endif
                            @elseif(is_object($answer))
                                @if(isset($answer->text))
                                    {{ $answer->text }}
                                @elseif(isset($answer->title))
                                    {{ $answer->title }}
                                @elseif(isset($answer->name))
                                    {{ $answer->name }}
                                @else
                                    {{ json_encode($answer, JSON_UNESCAPED_UNICODE) }}
                                @endif
                            @else
                                {{ $answer }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="contact-info">
                <div class="contact-title">Ваши контактные данные:</div>
                <div class="contact-item">
                    <span class="contact-label">Имя:</span>
                    {{ $contact['name'] }}
                </div>
                <div class="contact-item">
                    <span class="contact-label">Email:</span>
                    {{ $contact['email'] }}
                </div>
                <div class="contact-item">
                    <span class="contact-label">Телефон:</span>
                    {{ $contact['phone'] }}
                </div>
            </div>
            
            <div class="thank-you">
                Наши специалисты свяжутся с вами в ближайшее время!
            </div>
        </div>
        
        <div class="footer">
            <p>Это автоматическое письмо, пожалуйста, не отвечайте на него.</p>
            <p>Если у вас возникли вопросы, свяжитесь с нами по контактам на сайте.</p>
        </div>
    </div>
</body>
</html>

