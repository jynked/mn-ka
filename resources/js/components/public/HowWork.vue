<template>
    <section v-if="settings && settings.is_active" class="w-full px-3 sm:px-4 md:px-5 py-20 md:py-24">
        <div class="w-full max-w-[1200px] mx-auto">
            <!-- Заголовок -->
            <div v-if="displayTitle" class="flex justify-center mb-8 sm:mb-12">
                <h2 class="text-2xl md:text-3xl font-semibold text-foreground">
                    {{ displayTitle }}
                </h2>
            </div>

            <!-- Основной контент: две колонки -->
            <div class="flex flex-col md:flex-row gap-6 md:gap-8">
                <!-- Левая колонка: Шаги (на мобильных - внизу) -->
                <div class="w-full md:w-1/2 order-2 md:order-1">
                    <div class="space-y-0">
                        <div
                            v-for="(step, index) in displayItems"
                            :key="index"
                            class="flex gap-4 relative"
                        >
                            <!-- Вертикальная линия и иконка -->
                            <div class="flex flex-col items-center flex-shrink-0">
                                <!-- Иконка (круг или звезда) -->
                                <div class="w-5 h-5 flex-shrink-0">
                                    <!-- Круг -->
                                    <svg
                                        v-if="step.point === 'disc'"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <rect width="20" height="20" rx="10" fill="#6C7B6D"/>
                                    </svg>
                                    <!-- Звезда -->
                                    <svg
                                        v-else-if="step.point === 'star'"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M3.825 20L5.45 12.6053L0 7.63158L7.2 6.97368L10 0L12.8 6.97368L20 7.63158L14.55 12.6053L16.175 20L10 16.0789L3.825 20Z"
                                            fill="#6C7B6D"
                                        />
                                    </svg>
                                </div>
                                
                                <!-- Вертикальная линия (кроме последнего элемента) -->
                                <div
                                    v-if="index < displayItems.length - 1"
                                    class="w-0.5 bg-[#306221] flex-1 min-h-[50px] mt-2"
                                ></div>
                            </div>

                            <!-- Текст шага -->
                            <div class="flex-1 pb-6 md:pb-8">
                                <h3
                                    v-if="step.title"
                                    class="text-base sm:text-lg font-medium text-[#424448] leading-[22px] mb-2"
                                    v-html="step.title"
                                ></h3>
                                <p
                                    v-if="step.description || step.text"
                                    class="text-sm sm:text-base font-normal text-[#424448] leading-[20px]"
                                    v-html="step.description || step.text"
                                ></p>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопка на мобильных (под шагами) -->
                    <a
                        v-if="displayButtonText && displayButtonLink"
                        :href="displayButtonLink"
                        class="mt-6 w-full md:hidden px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base text-center inline-block"
                    >
                        {{ displayButtonText }}
                    </a>
                    <button
                        v-else-if="displayButtonText"
                        @click="handleButtonClick"
                        class="mt-6 w-full md:hidden px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base"
                    >
                        {{ displayButtonText }}
                    </button>
                </div>

                <!-- Правая колонка: Изображение и кнопка (на мобильных - сверху) -->
                <div class="w-full md:w-1/2 order-1 md:order-2">
                    <!-- Изображение -->
                    <div v-if="settings.image && imageUrl" class="overflow-hidden rounded-[18px] mb-6">
                        <img
                            :src="imageUrl"
                            :alt="settings.image_alt || 'Как мы работаем'"
                            class="w-full h-auto object-cover"
                        />
                    </div>

                    <!-- Кнопка на десктопе (под изображением) -->
                    <a
                        v-if="displayButtonText && displayButtonLink"
                        :href="displayButtonLink"
                        class="hidden md:block w-full px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base text-center inline-block"
                    >
                        {{ displayButtonText }}
                    </a>
                    <button
                        v-else-if="displayButtonText"
                        @click="handleButtonClick"
                        class="hidden md:block w-full px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base"
                    >
                        {{ displayButtonText }}
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Feedback Modal -->
        <FeedbackModal :is-open="showFeedbackModal" @close="showFeedbackModal = false" @success="handleFeedbackSuccess" />
    </section>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import FeedbackModal from './FeedbackModal.vue';

export default {
    name: 'HowWork',
    components: {
        FeedbackModal,
    },
    props: {
        title: {
            type: String,
            default: null,
        },
        items: {
            type: Array,
            default: null,
        },
        buttonText: {
            type: String,
            default: null,
        },
        buttonLink: {
            type: String,
            default: null,
        },
    },
    setup(props) {
        const router = useRouter();
        const settings = ref(null);
        const loading = ref(true);
        const showFeedbackModal = ref(false);

        // Computed для отображения с fallback
        const displayTitle = computed(() => {
            return props?.title || settings.value?.title || null;
        });

        const displayItems = computed(() => {
            // Если пришли items из props (HomePageSettings), используем их
            if (props?.items && Array.isArray(props.items)) {
                return props.items;
            }
            // Иначе используем steps из settings
            return settings.value?.steps || [];
        });

        const displayButtonText = computed(() => {
            return props?.buttonText || settings.value?.button_text || null;
        });

        const displayButtonLink = computed(() => {
            return props?.buttonLink || (settings.value?.button_type === 'url' ? settings.value?.button_value : null);
        });

        const fetchSettings = async () => {
            try {
                const response = await fetch('/api/public/how-work-block/settings');
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        settings.value = data.data;
                    }
                }
            } catch (error) {
                console.error('Error fetching HowWork block settings:', error);
            } finally {
                loading.value = false;
            }
        };

        const imageUrl = computed(() => {
            if (!settings.value?.image) return null;
            const img = settings.value.image;
            // Если это путь к файлу
            if (typeof img === 'string') {
                return img.startsWith('/') ? img : `/${img}`;
            }
            return null;
        });

        const handleButtonClick = () => {
            if (!settings.value) {
                console.warn('HowWork: settings not loaded');
                return;
            }
            
            console.log('HowWork button clicked:', {
                button_type: settings.value.button_type,
                button_value: settings.value.button_value,
            });
            
            // Если тип кнопки - URL и значение указано, выполняем переход
            if (settings.value.button_type === 'url' && settings.value.button_value) {
                if (settings.value.button_value.startsWith('http')) {
                    window.open(settings.value.button_value, '_blank');
                } else {
                    router.push(settings.value.button_value);
                }
            } else {
                // Во всех остальных случаях (method, не указан, или пустой) открываем форму обратной связи
                console.log('Opening feedback modal');
                    showFeedbackModal.value = true;
            }
        };

        const handleFeedbackSuccess = () => {
            // Обработка успешной отправки формы
            showFeedbackModal.value = false;
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            settings,
            loading,
            imageUrl,
            showFeedbackModal,
            displayTitle,
            displayItems,
            displayButtonText,
            displayButtonLink,
            handleButtonClick,
            handleFeedbackSuccess,
        };
    },
};
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>
