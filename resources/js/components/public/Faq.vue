<template>
    <section 
        v-if="settings && settings.is_active" 
        class="w-full px-3 sm:px-4 md:px-5 py-20 md:py-24 bg-gradient-to-b from-[#F4F6FC] to-white"
        aria-label="Часто задаваемые вопросы"
    >
        <div class="w-full max-w-[1200px] mx-auto">
            <!-- Заголовок -->
            <div v-if="displayTitle" class="text-center mb-10 sm:mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-semibold text-[#424448] mb-3 sm:mb-4">
                    {{ displayTitle }}
                </h2>
                <div class="w-20 h-1 bg-[#306221] mx-auto rounded-full"></div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-16">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-12 h-12 border-4 border-[#306221] border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-[#6C7B6D] text-sm">Загрузка вопросов...</p>
                </div>
            </div>

            <!-- FAQ Items -->
            <div 
                v-else-if="displayItems && displayItems.length > 0" 
                class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 md:gap-6 items-stretch"
                role="list"
            >
                <div
                    v-for="(item, index) in displayItems"
                    :key="`faq-${index}-${item.question}`"
                    class="group h-full"
                    role="listitem"
                >
                    <div
                        class="border border-[#E5E7EB] rounded-xl sm:rounded-2xl shadow-sm transition-all duration-300 h-full flex flex-col overflow-hidden"
                        :class="[
                            openItems[index] 
                                ? 'border-[#306221] shadow-md bg-[#F9FAFB]' 
                                : 'bg-white group-hover:bg-[#F4F6FC]/50 group-hover:shadow-lg group-hover:border-[#657C6C]'
                        ]"
                    >
                        <!-- Question Button -->
                        <button
                            @click="toggleAnswer(index)"
                            @keydown.enter.prevent="toggleAnswer(index)"
                            @keydown.space.prevent="toggleAnswer(index)"
                            :aria-expanded="openItems[index]"
                            :aria-controls="`faq-answer-${index}`"
                            class="w-full flex items-center justify-between gap-4 p-5 sm:p-6 text-left focus:outline-none transition-colors flex-shrink-0"
                            :class="openItems[index] ? 'bg-[#F9FAFB]' : 'bg-transparent'"
                        >
                            <h3 class="flex-1 text-base sm:text-lg md:text-xl font-semibold text-[#424448] leading-relaxed pr-4">
                                {{ item.question }}
                            </h3>
                            <div 
                                class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-[#F4F6FC] group-hover:bg-[#657C6C]/10 transition-all duration-300"
                                :class="openItems[index] ? 'bg-[#306221]/10' : ''"
                            >
                                <svg
                                    class="w-5 h-5 text-[#6C7B6D] transition-transform duration-300 ease-in-out"
                                    :class="openItems[index] ? 'rotate-180 text-[#306221]' : ''"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Answer with smooth animation -->
                        <Transition
                            name="faq-accordion"
                            @enter="onEnter"
                            @after-enter="onAfterEnter"
                            @leave="onLeave"
                            @after-leave="onAfterLeave"
                        >
                            <div
                                v-if="openItems[index]"
                                :id="`faq-answer-${index}`"
                                class="overflow-hidden flex-1 bg-[#F9FAFB]"
                                role="region"
                                :aria-labelledby="`faq-question-${index}`"
                            >
                                <div class="px-5 sm:px-6 pb-5 sm:pb-6 pt-4">
                                    <div 
                                        class="text-sm sm:text-base text-[#424448] leading-relaxed prose prose-sm max-w-none"
                                        v-html="formatAnswer(item.answer)"
                                    ></div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!loading" class="text-center py-16">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#F4F6FC] mb-4">
                    <svg class="w-8 h-8 text-[#6C7B6D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-[#6C7B6D] text-base">Вопросы не настроены. Добавьте вопросы в настройках блока.</p>
            </div>
        </div>
    </section>
</template>

<script>
import { ref, computed, onMounted, nextTick } from 'vue';

export default {
    name: 'Faq',
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
        const openItems = ref({});

        // Computed для отображения с fallback
        const displayTitle = computed(() => {
            return props?.title || settings.value?.title || null;
        });

        const displayItems = computed(() => {
            return props?.items || settings.value?.faq_items || [];
        });

        const fetchSettings = async () => {
            try {
                const response = await fetch('/api/public/faq-block/settings');
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        settings.value = data.data;
                        // Инициализируем состояние открытых элементов
                        const items = props?.items || data.data.faq_items || [];
                        if (Array.isArray(items)) {
                            items.forEach((_, index) => {
                                openItems.value[index] = false;
                            });
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching FAQ block settings:', error);
            } finally {
                loading.value = false;
            }
        };

        const toggleAnswer = (index) => {
            // Опционально: закрывать другие открытые элементы (accordion mode)
            // const wasOpen = openItems.value[index];
            // if (!wasOpen) {
            //     Object.keys(openItems.value).forEach(key => {
            //         openItems.value[key] = false;
            //     });
            // }
            openItems.value[index] = !openItems.value[index];
        };

        // Форматирование ответа (поддержка HTML)
        const formatAnswer = (answer) => {
            if (!answer) return '';
            // Если ответ уже содержит HTML теги, возвращаем как есть
            if (/<[a-z][\s\S]*>/i.test(answer)) {
                return answer;
            }
            // Иначе конвертируем переносы строк в <br>
            return answer.replace(/\n/g, '<br>');
        };

        // Анимации для аккордеона (оптимизированные для плавности)
        const onEnter = (el) => {
            // Устанавливаем начальное состояние сразу
            el.style.height = '0';
            el.style.opacity = '0';
            // Принудительно запускаем рефлоу для синхронизации
            el.offsetHeight;
        };

        const onAfterEnter = (el) => {
            // Используем requestAnimationFrame для более плавной анимации
            requestAnimationFrame(() => {
                el.style.height = `${el.scrollHeight}px`;
                el.style.opacity = '1';
            });
        };

        const onLeave = (el) => {
            // Сохраняем текущую высоту перед анимацией
            el.style.height = `${el.scrollHeight}px`;
            el.style.opacity = '1';
            // Принудительно запускаем рефлоу
            el.offsetHeight;
            // Используем requestAnimationFrame для плавности
            requestAnimationFrame(() => {
                el.style.height = '0';
                el.style.opacity = '0';
            });
        };

        const onAfterLeave = (el) => {
            el.style.height = '';
            el.style.opacity = '';
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            settings,
            loading,
            openItems,
            displayTitle,
            displayItems,
            toggleAnswer,
            formatAnswer,
            onEnter,
            onAfterEnter,
            onLeave,
            onAfterLeave,
        };
    },
};
</script>

<style scoped>
/* Плавные анимации для аккордеона (оптимизированные) */
.faq-accordion-enter-active {
    transition: height 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease-out;
    overflow: hidden;
    will-change: height, opacity;
}

.faq-accordion-leave-active {
    transition: height 0.2s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease-in;
    overflow: hidden;
    will-change: height, opacity;
}

.faq-accordion-enter-from,
.faq-accordion-leave-to {
    height: 0 !important;
    opacity: 0;
}

/* Стили для HTML контента в ответах */
:deep(.prose) {
    color: #424448;
}

:deep(.prose p) {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
}

:deep(.prose p:first-child) {
    margin-top: 0;
}

:deep(.prose p:last-child) {
    margin-bottom: 0;
}

:deep(.prose ul),
:deep(.prose ol) {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
    padding-left: 1.5em;
}

:deep(.prose li) {
    margin-top: 0.25em;
    margin-bottom: 0.25em;
}

:deep(.prose strong) {
    font-weight: 600;
    color: #424448;
}

:deep(.prose a) {
    color: #306221;
    text-decoration: underline;
    transition: opacity 0.2s;
}

:deep(.prose a:hover) {
    opacity: 0.8;
    color: #657C6C;
}

/* Улучшенная доступность для фокуса - убрано зеленое кольцо */
button:focus-visible {
    outline: none;
}

/* Плавная анимация для карточек */
.group:hover .group-hover\:bg-accent\/10 {
    transition: background-color 0.2s ease-in-out;
}
</style>

