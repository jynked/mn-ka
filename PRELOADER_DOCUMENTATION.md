# Документация по прелоадеру страницы

## Описание

Прелоадер отображается при загрузке страницы и автоматически скрывается после формирования контента в области видимости. Это улучшает пользовательский опыт, предотвращая отображение пустой страницы во время загрузки.

## Компоненты

### 1. HTML-разметка (`resources/views/app.blade.php`)

```html
<div id="preloader" class="preloader">
    <div class="preloader-content">
        <div class="preloader-logo">
            <svg>...</svg>
        </div>
        <div class="preloader-text">Загрузка...</div>
    </div>
</div>
```

**Особенности:**
- Фиксированная позиция на весь экран (`z-index: 9999`)
- Анимированный логотип с кругом
- Пульсирующий текст "Загрузка..."
- Поддержка светлой и темной темы
- Плавное исчезновение через CSS transitions

### 2. Логика скрытия (`resources/js/app.js`)

```javascript
window.hidePreloader = () => {
    const preloader = document.getElementById('preloader');
    if (preloader && !preloader.classList.contains('hidden')) {
        preloader.classList.add('hidden');
        setTimeout(() => {
            if (preloader.parentNode) {
                preloader.remove();
            }
        }, 500);
    }
};
```

**Механизм работы:**
1. После монтирования Vue приложения запускается проверка контента
2. Каждые 50ms проверяется наличие отрендеренного контента
3. Проверяются:
   - Наличие элементов в `#app`
   - Высота контента (> 50px)
   - Наличие основных классов страниц (`.home-page`, `.product-page` и т.д.)
4. После обнаружения контента прелоадер скрывается с задержкой 200ms
5. Принудительное скрытие через 5 секунд (защита от зависания)

### 3. Composable для управления (`resources/js/composables/usePreloader.js`)

```javascript
import { usePreloader } from '../composables/usePreloader';

export default {
    setup() {
        const { hidePreloader } = usePreloader();
        
        // Скрываем прелоадер после загрузки данных
        const fetchData = async () => {
            try {
                // ... загрузка данных
            } finally {
                hidePreloader(); // Скрывает прелоадер с задержкой 300ms
            }
        };
    }
}
```

**Методы:**
- `hidePreloader(delay = 300)` - скрывает прелоадер с задержкой
- `showPreloader()` - показывает прелоадер (если был скрыт)
- `isPreloaderVisible()` - проверяет видимость прелоадера

## Использование в компонентах

### Пример 1: Главная страница

```vue
<script>
import { usePreloader } from '../composables/usePreloader';

export default {
    setup() {
        const { hidePreloader } = usePreloader();
        
        const fetchBlocks = async () => {
            try {
                const response = await fetch('/api/public/home-page-blocks');
                // ... обработка данных
            } finally {
                loading.value = false;
                hidePreloader(); // Скрываем после загрузки
            }
        };
        
        onMounted(() => {
            fetchBlocks();
        });
    }
}
</script>
```

### Пример 2: Страница продукта

```vue
<script>
import { usePreloader } from '../composables/usePreloader';

export default {
    setup() {
        const { hidePreloader } = usePreloader();
        
        const fetchProduct = async () => {
            try {
                const response = await fetch(`/api/public/products/${slug}`);
                // ... обработка данных
            } finally {
                loading.value = false;
                hidePreloader();
            }
        };
    }
}
</script>
```

## Защита от зависания

Реализовано несколько уровней защиты:

1. **В `app.blade.php`**: принудительное скрытие через 10 секунд
2. **В `app.js`**: принудительное скрытие через 5 секунд
3. **Проверка контента**: автоматическое скрытие при обнаружении контента

## Стилизация

### Светлая тема
- Фон: `#ffffff`
- Цвет логотипа: `#688E67`
- Цвет текста: `#688E67`

### Темная тема
- Фон: `#0f172a`
- Цвет логотипа: `#688E67`
- Цвет текста: `#e2e8f0`

## Анимации

1. **Круг логотипа**: анимация `stroke-dashoffset` (1.5s)
2. **Текст**: пульсация opacity (1.5s)
3. **Скрытие**: плавное исчезновение opacity (0.5s)

## Интеграция в новые страницы

Для добавления поддержки прелоадера в новую страницу:

1. Импортируйте composable:
```javascript
import { usePreloader } from '../composables/usePreloader';
```

2. Используйте в setup:
```javascript
const { hidePreloader } = usePreloader();
```

3. Вызовите после загрузки данных:
```javascript
finally {
    loading.value = false;
    hidePreloader();
}
```

## Отладка

Для отладки прелоадера проверьте консоль:
- `Preloader: forced hide after 5s timeout` - принудительное скрытие в `app.js`
- `Preloader: emergency hide after 10s` - аварийное скрытие в `app.blade.php`

## Совместимость

- ✅ Vue 3
- ✅ Все современные браузеры
- ✅ Мобильные устройства
- ✅ Светлая и темная тема

