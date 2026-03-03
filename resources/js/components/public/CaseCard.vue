<template>
    <article class="flex flex-col h-full bg-white rounded-lg overflow-hidden">
        <!-- Изображение -->
        <div class="relative w-full aspect-[16/10] bg-gray-100 overflow-hidden">
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="caseItem.name"
                class="w-full h-full object-cover"
                @error="handleImageError"
                @load="handleImageLoad"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                <span class="text-gray-400 text-sm">Нет изображения (URL: {{ imageUrl }})</span>
            </div>
        </div>

        <!-- Контент -->
        <div class="flex-1 flex flex-col p-4 md:p-6 gap-3">
            <!-- Заголовок -->
            <h3 class="font-bold text-lg md:text-xl text-black leading-tight">
                {{ caseItem.name || 'Название объекта' }}
            </h3>

            <!-- Описание -->
            <p v-if="caseDescription" class="text-sm md:text-base text-gray-600 leading-relaxed line-clamp-3">
                {{ caseDescription }}
            </p>
            <p v-else class="text-sm md:text-base text-gray-600 leading-relaxed">
                Краткое описание обращения, выполнения услуги и подробности
            </p>

            <!-- Ссылка -->
            <router-link
                :to="getCaseUrl(caseItem)"
                class="inline-flex items-center gap-1 text-sm md:text-base text-black hover:text-gray-700 transition-colors font-medium mt-2"
            >
                <span>читать подробнее</span>
                <svg 
                    width="20" 
                    height="10" 
                    viewBox="0 0 28 13" 
                    fill="none" 
                    xmlns="http://www.w3.org/2000/svg"
                    class="flex-shrink-0"
                >
                    <path
                        d="M26.9528 7.09687C27.2962 6.75347 27.2962 6.19673 26.9528 5.85333L21.3569 0.257436C21.0135 -0.0859557 20.4568 -0.0859557 20.1134 0.257436C19.77 0.600828 19.77 1.15758 20.1134 1.50097L25.0875 6.4751L20.1134 11.4492C19.77 11.7926 19.77 12.3494 20.1134 12.6928C20.4568 13.0362 21.0135 13.0362 21.3569 12.6928L26.9528 7.09687ZM0 6.4751L-7.67312e-08 7.35441L26.3311 7.35441L26.3311 6.4751L26.3311 5.59579L7.67312e-08 5.59579L0 6.4751Z"
                        fill="currentColor"
                    />
                </svg>
            </router-link>
        </div>
    </article>
</template>

<script>
import { computed } from 'vue';

export default {
    name: 'CaseCard',
    props: {
        caseItem: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        // Определяем URL изображения для карточки
        const imageUrl = computed(() => {
            // Используем основное изображение напрямую (как в админ панели)
            if (props.caseItem.image && props.caseItem.image.url) {
                return props.caseItem.image.url;
            }
            
            // Если основного изображения нет, используем первое из галереи
            if (props.caseItem.images && Array.isArray(props.caseItem.images) && props.caseItem.images.length > 0) {
                const firstImage = props.caseItem.images[0];
                if (firstImage && firstImage.url) {
                    return firstImage.url;
                }
            }
            
            return null;
        });

        // Вычисляем описание для отображения
        const caseDescription = computed(() => {
            if (!props.caseItem || !props.caseItem.description) {
                return null;
            }
            
            const desc = props.caseItem.description;
            
            // Если это строка, возвращаем её
            if (typeof desc === 'string') {
                return desc;
            }
            
            // Если это объект, извлекаем нужное поле
            if (typeof desc === 'object') {
                // Приоритет: ru -> short -> full -> detailed
                if (desc.ru) {
                    return desc.ru;
                }
                if (desc.short) {
                    return desc.short;
                }
                if (desc.full) {
                    return desc.full;
                }
                if (desc.detailed) {
                    // Если detailed - это длинный текст, берем только начало для карточки
                    const detailedText = typeof desc.detailed === 'string' 
                        ? desc.detailed 
                        : String(desc.detailed);
                    // Берем первые 150 символов для краткого описания
                    return detailedText.length > 150 
                        ? detailedText.substring(0, 150) + '...' 
                        : detailedText;
                }
            }
            
            return null;
        });

        // Формируем правильный URL для кейса
        const getCaseUrl = (caseItem) => {
            if (!caseItem || !caseItem.slug) {
                return '/cases';
            }
            
            // Убираем префикс `/` если он есть, и добавляем правильный путь
            const slug = caseItem.slug.startsWith('/') ? caseItem.slug.substring(1) : caseItem.slug;
            return `/cases/${slug}`;
        };

        const handleImageError = (event) => {
            console.error('Error loading image:', imageUrl.value, event);
            console.error('Image element:', event.target);
            console.error('Case item:', props.caseItem);
        };

        const handleImageLoad = () => {
            console.log('Image loaded successfully:', imageUrl.value);
        };

        return {
            imageUrl,
            caseDescription,
            getCaseUrl,
            handleImageError,
            handleImageLoad,
        };
    },
};
</script>
