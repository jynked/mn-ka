<template>
    <section v-if="settings && settings.is_active" class="w-full px-3 sm:px-4 md:px-5 py-20 md:py-24 bg-background">
        <div class="w-full max-w-[1200px] mx-auto">
            <!-- Заголовок -->
            <div v-if="displayTitle" class="flex justify-center mb-6 md:mb-8">
                <h2 class="text-2xl md:text-3xl font-semibold text-foreground text-center">
                    {{ displayTitle }}
                </h2>
            </div>

            <!-- Сетка карточек (Bootstrap-like grid с разными размерами) -->
            <div v-if="displayItems && displayItems.length > 0" class="flex flex-wrap -mx-3">
                <div
                    v-for="(item, index) in displayItems"
                    :key="index"
                    :class="getCardColumnClass(item, index)"
                    class="px-3 mb-3"
                >
                    <div 
                        class="relative rounded-2xl h-[140px] overflow-hidden"
                        :class="getCardBgClass(item, index)"
                    >
                        <!-- Изображение (абсолютное позиционирование справа внизу) -->
                        <div 
                            v-if="item.icon && (item.icon.url || (item.icon.disk && item.icon.name))" 
                            class="absolute bottom-[5px] right-[5px]"
                        >
                            <img
                                :src="item.icon.url || `/${item.icon.disk}/${item.icon.name}`"
                                :alt="item.text"
                                class="h-auto max-h-[120px] object-contain"
                                loading="lazy"
                            />
                        </div>
                        
                        <!-- Текст (абсолютное позиционирование слева вверху) -->
                        <div 
                            v-if="item.text || item.title" 
                            class="card-title absolute left-5 top-5 text-base sm:text-lg font-medium leading-[22px]"
                            :class="getCardTextClass(item, index)"
                            v-html="item.text || item.title"
                        >
                        </div>
                        <!-- Короткий текст, если есть -->
                        <div 
                            v-if="item.short_text && !item.text && !item.title" 
                            class="card-title absolute left-5 top-5 text-base sm:text-lg font-medium leading-[22px]"
                            :class="getCardTextClass(item, index)"
                        >
                            {{ item.short_text }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { ref, computed, onMounted } from 'vue';

export default {
    name: 'WhyChooseUs',
    props: {
        title: {
            type: String,
            default: null,
        },
        items: {
            type: Array,
            default: null,
        },
    },
    setup(props) {
        const settings = ref(null);
        const loading = ref(true);

        // Computed для отображения с fallback
        const displayTitle = computed(() => {
            return props?.title || settings.value?.title || null;
        });

        const displayItems = computed(() => {
            return props?.items || settings.value?.items || [];
        });

        const fetchSettings = async () => {
            try {
                const response = await fetch('/api/public/why-choose-us-block/settings');
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        settings.value = data.data;
                    }
                }
            } catch (error) {
                console.error('Error fetching WhyChooseUs block settings:', error);
            } finally {
                loading.value = false;
            }
        };

        // Получить класс колонки для карточки
        const getCardColumnClass = (item, index) => {
            // По умолчанию используем размеры из старой версии (на случай, если данные не заполнены)
            const defaultSizes = [3, 6, 3, 6, 6, 12]; // Размеры из старой версии
            
            // Если в item есть col, используем его, иначе берем из массива по умолчанию
            const col = item.col || defaultSizes[index] || 3;
            
            // Преобразуем Bootstrap col в Tailwind классы
            // col-3 = w-full md:w-1/4 (3/12)
            // col-6 = w-full md:w-1/2 (6/12)
            // col-12 = w-full (12/12)
            const colMap = {
                3: 'w-full md:w-1/4',
                6: 'w-full md:w-1/2',
                12: 'w-full',
            };
            
            return colMap[col] || 'w-full md:w-1/4';
        };

        // Получить класс фона для карточки
        const getCardBgClass = (item, index) => {
            // По умолчанию используем цвета из старой версии (на случай, если данные не заполнены)
            const defaultBgs = ['card-blue', 'card-blue', 'card-blue', 'card-blue', 'card-blue', 'card-green'];
            
            // Если в item есть bg, используем его, иначе берем из массива по умолчанию
            const bg = item.bg || defaultBgs[index] || 'card-blue';
            
            return bg === 'card-green' 
                ? 'bg-[#688E67]' 
                : 'bg-[#F4F6FC]';
        };

        // Получить класс текста для карточки
        const getCardTextClass = (item, index) => {
            // По умолчанию используем цвета из старой версии (на случай, если данные не заполнены)
            const defaultBgs = ['card-blue', 'card-blue', 'card-blue', 'card-blue', 'card-blue', 'card-green'];
            const bg = item.bg || defaultBgs[index] || 'card-blue';
            
            return bg === 'card-green' 
                ? 'text-white' 
                : 'text-black';
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            settings,
            loading,
            displayTitle,
            displayItems,
            getCardColumnClass,
            getCardBgClass,
            getCardTextClass,
        };
    },
};
</script>

<style scoped>
/* Адаптивные стили для мобильных устройств */
@media only screen and (max-width: 424px) {
    /* Уменьшаем позицию и размер текста на мобильных */
    .card-title {
        max-width: 50%;
        left: 15px !important;
        top: 15px !important;
        font-size: 0.75rem;
        line-height: 1.375rem;
    }
    
    /* Уменьшаем скругление карточек на мобильных */
    .rounded-2xl {
        border-radius: 8px;
    }
}
</style>
