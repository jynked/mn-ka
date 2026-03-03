<template>
    <div class="w-full px-3 sm:px-4 md:px-5 py-20 md:py-24">
        <div class="w-full max-w-[1200px] mx-auto">
            <!-- Заголовок -->
            <div class="flex justify-center mb-6 sm:mb-8">
                <h2 class="text-2xl md:text-3xl font-semibold text-gray-900">
                    {{ displayTitle }}
                </h2>
            </div>
            <!-- Подзаголовок -->
            <div v-if="subtitle" class="flex justify-center mb-6 sm:mb-8">
                <p class="text-base sm:text-lg text-gray-600 text-center max-w-3xl">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Загрузка -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <div class="text-gray-500">Загрузка...</div>
            </div>

            <!-- Ошибка -->
            <div v-else-if="error" class="flex justify-center items-center py-12">
                <div class="text-red-500">{{ error }}</div>
            </div>

            <!-- Контент -->
            <template v-else>
                <!-- Сетка карточек (только первые 9) -->
                <div v-if="displayedItems.length > 0" class="relative">
                    <TransitionGroup
                        name="stagger"
                        tag="div"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6"
                    >
                        <DecisionCard
                            v-for="(item, index) in displayedItems"
                            :key="`${item.id}-${item.category}`"
                            :decision="item"
                            :slug="item.category === 'products' ? 'products' : 'services'"
                        />
                    </TransitionGroup>
                </div>
                <div v-else class="flex justify-center items-center py-12">
                    <div class="text-gray-500">Продукты и услуги не найдены</div>
                </div>

                <!-- Кнопка "Услуги" -->
                <div v-if="displayedItems.length > 0" class="flex justify-center mt-8 sm:mt-10">
                    <router-link
                        to="/services"
                        class="px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base inline-flex items-center justify-center"
                    >
                        Услуги
                    </router-link>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, TransitionGroup } from 'vue';
import { useStore } from 'vuex';
import DecisionCard from './DecisionCard.vue';

export default {
    name: 'Decisions',
    components: {
        DecisionCard,
        TransitionGroup,
    },
    props: {
        title: {
            type: String,
            default: null,
        },
        subtitle: {
            type: String,
            default: null,
        },
    },
    setup(props) {
        const store = useStore();
        const titleFromSettings = ref('Выберите решение под ваш участок');
        const loading = ref(true);
        const error = ref(null);

        // Computed для отображения с fallback
        const displayTitle = computed(() => {
            return props?.title || titleFromSettings.value;
        });

        const subtitle = computed(() => {
            return props?.subtitle || null;
        });

        // Загрузка настроек блока
        const loadSettings = async () => {
            try {
                const response = await fetch('/api/public/decision-block/settings');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки настроек');
                }
                const result = await response.json();
                if (result.data && result.data.title) {
                    titleFromSettings.value = result.data.title;
                }
            } catch (err) {
                console.error('Ошибка загрузки настроек:', err);
                // Используем значение по умолчанию при ошибке
            }
        };

        // Все элементы (продукты и услуги)
        const allItems = ref([]);

        // Ограничиваем отображение до первых 9 карточек
        const displayedItems = computed(() => {
            return allItems.value.slice(0, 9);
        });

        // Загрузка всех продуктов и услуг из store
        const loadChapters = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                // Загружаем все активные продукты из store
                const allProducts = await store.dispatch('fetchPublicProducts', { minimal: false });
                const productsWithCategory = (allProducts || []).map(product => ({
                    ...product,
                    category: 'products',
                }));
                
                // Загружаем все активные услуги из store (минимальный набор для карточек)
                const allServices = await store.dispatch('fetchPublicServices', { minimal: true });
                const servicesWithCategory = (allServices || []).map(service => ({
                    ...service,
                    category: 'services',
                }));
                
                // Объединяем все продукты и услуги в один массив
                allItems.value = [...productsWithCategory, ...servicesWithCategory];
            } catch (err) {
                console.error('Ошибка загрузки данных:', err);
                error.value = 'Не удалось загрузить данные. Попробуйте обновить страницу.';
            } finally {
                loading.value = false;
            }
        };

        // Загрузка данных при монтировании
        onMounted(async () => {
            await Promise.all([
                loadSettings(),
                loadChapters(),
            ]);
        });

        return {
            displayTitle,
            subtitle,
            allItems,
            displayedItems,
            loading,
            error,
        };
    },
};
</script>

<style scoped>
/* Анимация для карточек с задержкой (stagger effect) */
.stagger-move {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.stagger-enter-active {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.stagger-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stagger-enter-from {
    opacity: 0;
    transform: translateY(20px) scale(0.96);
}

.stagger-leave-to {
    opacity: 0;
    transform: scale(0.96);
}

/* Добавляем задержку для каждой карточки */
.stagger-enter-active:nth-child(1) { transition-delay: 0.03s; }
.stagger-enter-active:nth-child(2) { transition-delay: 0.06s; }
.stagger-enter-active:nth-child(3) { transition-delay: 0.09s; }
.stagger-enter-active:nth-child(4) { transition-delay: 0.12s; }
.stagger-enter-active:nth-child(5) { transition-delay: 0.15s; }
.stagger-enter-active:nth-child(6) { transition-delay: 0.18s; }
.stagger-enter-active:nth-child(7) { transition-delay: 0.21s; }
.stagger-enter-active:nth-child(8) { transition-delay: 0.24s; }
.stagger-enter-active:nth-child(9) { transition-delay: 0.27s; }
.stagger-enter-active:nth-child(10) { transition-delay: 0.3s; }
.stagger-enter-active:nth-child(n+11) { transition-delay: 0.33s; }
</style>

