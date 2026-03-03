<template>
    <!-- Компонент не рендерит ничего визуально, только управляет метатегами -->
</template>

<script>
import { onMounted, watchEffect, onBeforeUnmount } from 'vue';

export default {
    name: 'SEOHead',
    props: {
        title: {
            type: String,
            default: '',
        },
        description: {
            type: String,
            default: '',
        },
        keywords: {
            type: String,
            default: '',
        },
        ogImage: {
            type: String,
            default: '',
        },
        ogType: {
            type: String,
            default: 'website',
        },
        canonical: {
            type: String,
            default: '',
        },
        noindex: {
            type: Boolean,
            default: false,
        },
        schema: {
            type: [Object, Array],
            default: null,
        },
    },
    setup(props) {
        const createdElements = [];
        let lastTitle = '';
        let lastDescription = '';

        const setMetaTag = (attributes) => {
            const selector = attributes.name 
                ? `meta[name="${attributes.name}"]`
                : `meta[property="${attributes.property}"]`;
            
            let element = document.querySelector(selector);
            
            if (!element) {
                element = document.createElement('meta');
                Object.keys(attributes).forEach(key => {
                    element.setAttribute(key, attributes[key]);
                });
                document.head.appendChild(element);
                createdElements.push(element);
            } else {
                Object.keys(attributes).forEach(key => {
                    element.setAttribute(key, attributes[key]);
                });
            }
        };

        const setLinkTag = (attributes) => {
            const selector = `link[rel="${attributes.rel}"]`;
            let element = document.querySelector(selector);
            
            if (!element) {
                element = document.createElement('link');
                Object.keys(attributes).forEach(key => {
                    element.setAttribute(key, attributes[key]);
                });
                document.head.appendChild(element);
                createdElements.push(element);
            } else {
                Object.keys(attributes).forEach(key => {
                    element.setAttribute(key, attributes[key]);
                });
            }
        };

        const setSchemaScript = (schema) => {
            // Удаляем предыдущий schema script если есть
            const existingScript = document.querySelector('script[type="application/ld+json"]');
            if (existingScript) {
                existingScript.remove();
            }

            const script = document.createElement('script');
            script.type = 'application/ld+json';
            script.text = JSON.stringify(schema);
            document.head.appendChild(script);
            createdElements.push(script);
        };

        const updateMeta = () => {
            // Title - обновляем всегда, даже если пустой
            const titleToSet = props.title || 'Lagom - Профессиональные услуги по работе с земельными участками';
            
            // Description - обновляем всегда
            const descriptionToSet = props.description || 'Профессиональные услуги по подбору и оформлению земельных участков';
            
            // Обновляем title - всегда, даже если значение не изменилось
            // Это важно для SPA, так как другие страницы могли изменить title
            document.title = titleToSet;
            
            // Сохраняем текущие значения для логирования
            lastTitle = titleToSet;
            lastDescription = descriptionToSet;
            
            setMetaTag({ name: 'description', content: descriptionToSet });

            // Keywords
            if (props.keywords) {
                setMetaTag({ name: 'keywords', content: props.keywords });
            }

            // Robots
            if (props.noindex) {
                setMetaTag({ name: 'robots', content: 'noindex, nofollow' });
            } else {
                setMetaTag({ name: 'robots', content: 'index, follow' });
            }

            // Open Graph
            setMetaTag({ property: 'og:title', content: titleToSet });
            setMetaTag({ property: 'og:description', content: descriptionToSet });
            setMetaTag({ property: 'og:type', content: props.ogType });
            setMetaTag({ property: 'og:url', content: props.canonical || window.location.href });

            if (props.ogImage) {
                const imageUrl = props.ogImage.startsWith('http') 
                    ? props.ogImage 
                    : window.location.origin + props.ogImage;
                setMetaTag({ property: 'og:image', content: imageUrl });
            }

            // Twitter Cards
            setMetaTag({ name: 'twitter:card', content: 'summary_large_image' });
            setMetaTag({ name: 'twitter:title', content: titleToSet });
            setMetaTag({ name: 'twitter:description', content: descriptionToSet });

            if (props.ogImage) {
                const imageUrl = props.ogImage.startsWith('http') 
                    ? props.ogImage 
                    : window.location.origin + props.ogImage;
                setMetaTag({ name: 'twitter:image', content: imageUrl });
            }

            // Canonical
            if (props.canonical) {
                setLinkTag({ rel: 'canonical', href: props.canonical });
            }

            // Schema.org JSON-LD
            if (props.schema) {
                setSchemaScript(props.schema);
            }
        };

        // Обновляем метатеги сразу при создании компонента (до монтирования)
        // Это важно для предотвращения мерцания title
        updateMeta();
        
        // Используем watchEffect для отслеживания всех изменений props
        // watchEffect автоматически вызывается при изменении любого используемого значения
        watchEffect(() => {
            // Обновляем метатеги при любом изменении props
            const title = props.title;
            const description = props.description;
            const keywords = props.keywords;
            const canonical = props.canonical;
            const ogImage = props.ogImage;
            const schema = props.schema;
            
            // Вызываем updateMeta при любом изменении
            updateMeta();
        });

        onMounted(() => {
            // Обновляем после монтирования для надежности
            updateMeta();
        });

        onBeforeUnmount(() => {
            // Очищаем созданные элементы при размонтировании
            createdElements.forEach(el => {
                if (el && el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            });
        });

        return {};
    },
};
</script>

