<template>
    <div class="w-full max-w-4xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <!-- Категория заявителя (app_categories) -->
            <div class="md:col-span-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#688E67] text-white font-semibold text-sm flex-shrink-0">1</span>
                    <span class="text-sm md:text-base font-medium text-foreground">Категория заявителя</span>
                </div>
                <select
                    v-model="selectedAppCategory"
                    @change="onAppCategoryChange"
                    class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                >
                    <option value="">Выберите категорию</option>
                    <option
                        v-for="category in service.app_categories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <!-- Цель вашего обращения (chapters) -->
            <div class="md:col-span-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#688E67] text-white font-semibold text-sm flex-shrink-0">2</span>
                    <span class="text-sm md:text-base font-medium text-foreground">Цель вашего обращения</span>
                </div>
                <select
                    v-model="selectedChapter"
                    @change="onChapterChange"
                    class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                >
                    <option value="">Выберите цель</option>
                    <option
                        v-for="chapter in availableChapters"
                        :key="chapter.id"
                        :value="chapter.id"
                    >
                        {{ chapter.name }}
                    </option>
                </select>
            </div>

            <!-- Подходящий случай (cases из выбранного chapter) -->
            <div v-if="selectedChapter && availableCases.length > 0" class="md:col-span-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#688E67] text-white font-semibold text-sm flex-shrink-0">3</span>
                    <span class="text-sm md:text-base font-medium text-foreground">Подходящий случай</span>
                </div>
                <select
                    v-model="selectedCase"
                    @change="onCaseChange"
                    class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                >
                    <option value="">Выберите случай</option>
                    <option
                        v-for="caseItem in availableCases"
                        :key="caseItem.id"
                        :value="caseItem.id"
                    >
                        {{ caseItem.name }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue';

export default {
    name: 'ServiceOptionsStage',
    props: {
        service: {
            type: Object,
            required: true,
        },
    },
    emits: ['update-options'],
    setup(props, { emit }) {
        const selectedAppCategory = ref(null);
        const selectedChapter = ref(null);
        const selectedCase = ref(null);

        // Получаем доступные разделы из service
        const availableChapters = computed(() => {
            return props.service?.available_chapters || [];
        });

        // Найти доступные cases из выбранного chapter
        const availableCases = computed(() => {
            if (!selectedChapter.value || !availableChapters.value.length) {
                return [];
            }

            const selectedChapterData = availableChapters.value.find(
                chapter => chapter.id === selectedChapter.value
            );

            if (!selectedChapterData || !selectedChapterData.cases || selectedChapterData.cases.length === 0) {
                return [];
            }

            return selectedChapterData.cases;
        });

        const onAppCategoryChange = () => {
            emitOptions();
        };

        const onChapterChange = () => {
            // Сбрасываем case при изменении chapter
            selectedCase.value = null;
            emitOptions();
        };

        const onCaseChange = () => {
            emitOptions();
        };

        const emitOptions = () => {
            emit('update-options', {
                appCategory: selectedAppCategory.value,
                chapter: selectedChapter.value,
                case: selectedCase.value,
            });
        };

        // Сбрасываем при изменении услуги
        watch(() => props.service?.id, () => {
            selectedAppCategory.value = null;
            selectedChapter.value = null;
            selectedCase.value = null;
        });

        return {
            selectedAppCategory,
            selectedChapter,
            selectedCase,
            availableChapters,
            availableCases,
            onAppCategoryChange,
            onChapterChange,
            onCaseChange,
        };
    },
};
</script>

