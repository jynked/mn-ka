<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>МНКА - Профессиональные услуги по работе с земельными участками</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @php
        $seoSettings = \App\Models\SeoSettings::getSettings();
        $customJsCode = $seoSettings->custom_js_code ?? '';
    @endphp
    
    @if(!empty($customJsCode))
        {!! $customJsCode !!}
    @endif
    
    @verbatim
    <script>
        // Применяем тему до загрузки страницы, чтобы избежать мигания
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            const html = document.documentElement;
            if (theme === 'dark') {
                html.classList.add('dark');
                html.setAttribute('data-theme', 'dark');
                html.style.colorScheme = 'dark';
            } else {
                html.style.colorScheme = 'light';
            }
        })();
        
        // Устанавливаем дефолтный title и метатеги сразу, чтобы избежать отображения "Laravel"
        // Vue компонент SEOHead обновит их при монтировании
        (function() {
            const defaultTitle = 'МНКА - Профессиональные услуги по работе с земельными участками';
            const defaultDescription = 'Профессиональные услуги по подбору и оформлению земельных участков';
            
            // Обновляем title сразу
            document.title = defaultTitle;
            
            // Обновляем meta description
            let metaDesc = document.querySelector('meta[name="description"]');
            if (!metaDesc) {
                metaDesc = document.createElement('meta');
                metaDesc.setAttribute('name', 'description');
                document.head.appendChild(metaDesc);
            }
            metaDesc.setAttribute('content', defaultDescription);
            
            // Обновляем Open Graph
            let ogTitle = document.querySelector('meta[property="og:title"]');
            if (!ogTitle) {
                ogTitle = document.createElement('meta');
                ogTitle.setAttribute('property', 'og:title');
                document.head.appendChild(ogTitle);
            }
            ogTitle.setAttribute('content', defaultTitle);
            
            let ogDesc = document.querySelector('meta[property="og:description"]');
            if (!ogDesc) {
                ogDesc = document.createElement('meta');
                ogDesc.setAttribute('property', 'og:description');
                document.head.appendChild(ogDesc);
            }
            ogDesc.setAttribute('content', defaultDescription);
            
            // Обновляем Twitter Cards
            let twitterTitle = document.querySelector('meta[name="twitter:title"]');
            if (!twitterTitle) {
                twitterTitle = document.createElement('meta');
                twitterTitle.setAttribute('name', 'twitter:title');
                document.head.appendChild(twitterTitle);
            }
            twitterTitle.setAttribute('content', defaultTitle);
            
            let twitterDesc = document.querySelector('meta[name="twitter:description"]');
            if (!twitterDesc) {
                twitterDesc = document.createElement('meta');
                twitterDesc.setAttribute('name', 'twitter:description');
                document.head.appendChild(twitterDesc);
            }
            twitterDesc.setAttribute('content', defaultDescription);
            
            // Обновляем Schema.org JSON-LD
            let schemaScript = document.querySelector('script[type="application/ld+json"]');
            if (schemaScript) {
                try {
                    const schemas = JSON.parse(schemaScript.textContent);
                    if (Array.isArray(schemas)) {
                        schemas.forEach(function(schema) {
                            var schemaType = schema['@type'];
                            if (schemaType === 'WebSite' || schemaType === 'Organization') {
                                if (schema.name === 'Laravel' || !schema.name) {
                                    schema.name = defaultTitle.split(' - ')[0];
                                }
                                if (schemaType === 'WebSite' && (!schema.description || schema.description.includes('Laravel'))) {
                                    schema.description = defaultDescription;
                                }
                            }
                        });
                        schemaScript.textContent = JSON.stringify(schemas);
                    }
                } catch (e) {
                    // Если не удалось распарсить, создаем новый
                    var contextValue = 'https://schema.org';
                    var newSchemas = [
                        {
                            '@context': contextValue,
                            '@type': 'WebSite',
                            'name': defaultTitle.split(' - ')[0],
                            'description': defaultDescription,
                            'url': window.location.origin,
                        },
                        {
                            '@context': contextValue,
                            '@type': 'Organization',
                            'name': defaultTitle.split(' - ')[0],
                            'url': window.location.origin,
                        }
                    ];
                    schemaScript.textContent = JSON.stringify(newSchemas);
                }
            } else {
                // Создаем новый Schema.org script
                schemaScript = document.createElement('script');
                schemaScript.type = 'application/ld+json';
                var contextValue = 'https://schema.org';
                var newSchemas = [
                    {
                        '@context': contextValue,
                        '@type': 'WebSite',
                        'name': defaultTitle.split(' - ')[0],
                        'description': defaultDescription,
                        'url': window.location.origin,
                    },
                    {
                        '@context': contextValue,
                        '@type': 'Organization',
                        'name': defaultTitle.split(' - ')[0],
                        'url': window.location.origin,
                    }
                ];
                schemaScript.textContent = JSON.stringify(newSchemas);
                document.head.appendChild(schemaScript);
            }
        })();
    </script>
    @endverbatim
</head>
<body class="min-h-screen bg-background text-foreground">
    <!-- Preloader -->
    <div id="preloader" class="preloader">
        <div class="preloader-content">
            <div class="preloader-logo">
                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="30" cy="30" r="26" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="163.363" stroke-dashoffset="163.363" class="preloader-circle">
                        <animate attributeName="stroke-dashoffset" dur="1.5s" values="163.363;0;163.363" repeatCount="indefinite"/>
                    </circle>
                    <text x="30" y="38" font-family="Montserrat, sans-serif" font-size="24" font-weight="600" text-anchor="middle" fill="currentColor">M</text>
                </svg>
            </div>
            <div class="preloader-text">Загрузка...</div>
        </div>
    </div>
    
    <div id="app"></div>
    
    <style>
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }
        
        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        
        .preloader-content {
            text-align: center;
        }
        
        .preloader-logo {
            margin-bottom: 20px;
            color: #688E67;
            display: inline-block;
        }
        
        .preloader-logo svg {
            display: block;
            margin: 0 auto;
        }
        
        .preloader-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            font-weight: 500;
            color: #688E67;
            margin-top: 20px;
            letter-spacing: 0.5px;
        }
        
        /* Dark theme support */
        .dark .preloader {
            background: #0f172a;
        }
        
        .dark .preloader-logo {
            color: #688E67;
        }
        
        .dark .preloader-text {
            color: #e2e8f0;
        }
        
        /* Animation for text */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .preloader-text {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        /* Spinner animation - убираем, так как используем stroke-dashoffset анимацию */
        .preloader-logo svg {
            filter: drop-shadow(0 2px 4px rgba(104, 142, 103, 0.2));
        }
    </style>
    
    <script>
        // Глобальная функция для скрытия прелоадера (будет переопределена в app.js)
        window.hidePreloader = function() {
            const preloader = document.getElementById('preloader');
            if (preloader && !preloader.classList.contains('hidden')) {
                preloader.classList.add('hidden');
                setTimeout(function() {
                    if (preloader.parentNode) {
                        preloader.remove();
                    }
                }, 500);
            }
        };
        
        // Защита от зависания прелоадера (принудительно скрываем через 10 секунд)
        setTimeout(function() {
            const preloader = document.getElementById('preloader');
            if (preloader && !preloader.classList.contains('hidden')) {
                console.warn('Preloader: emergency hide after 10s');
                window.hidePreloader();
            }
        }, 10000);
    </script>
</body>
</html>
