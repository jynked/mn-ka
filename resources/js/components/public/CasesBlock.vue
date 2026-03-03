<template>
    <section v-if="settings && settings.is_active && cases.length > 0" class="w-full px-3 sm:px-4 md:px-5 py-20 md:py-24 bg-background">
        <div class="w-full max-w-[1200px] mx-auto">
            <!-- Заголовок -->
            <div v-if="settings.title" class="flex justify-center mb-6 md:mb-8">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-foreground text-center">
                    {{ settings.title }}
                </h2>
            </div>

            <!-- Сетка кейсов -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                <CaseCard
                    v-for="caseItem in cases"
                    :key="caseItem.id"
                    :case-item="caseItem"
                />
            </div>

            <!-- Кнопка "Смотреть все кейсы" -->
            <div class="flex justify-center mt-8 md:mt-12">
                <router-link
                    to="/cases"
                    class="inline-flex items-center justify-center px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base"
                >
                    Смотреть все кейсы
                </router-link>
            </div>
        </div>
    </section>
</template>

<script>
import { ref, onMounted } from 'vue';
import CaseCard from './CaseCard.vue';

export default {
    name: 'CasesBlock',
    components: {
        CaseCard,
    },
    setup() {
        const settings = ref(null);
        const cases = ref([]);
        const loading = ref(true);

        const fetchSettings = async () => {
            try {
                const response = await fetch('/api/public/cases-block/settings');
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        settings.value = data.data;
                        // Загружаем кейсы, если есть ID
                        if (settings.value.case_ids && settings.value.case_ids.length > 0) {
                            await fetchCases(settings.value.case_ids);
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching Cases block settings:', error);
            } finally {
                loading.value = false;
            }
        };

        const fetchCases = async (caseIds) => {
            try {
                // Загружаем кейсы по ID
                const idsParam = caseIds.join(',');
                const response = await fetch(`/api/public/cases?ids=${idsParam}&with=image,images`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        // Сортируем кейсы в порядке, указанном в case_ids
                        const casesMap = new Map(data.data.map(c => [c.id, c]));
                        cases.value = caseIds
                            .map(id => casesMap.get(id))
                            .filter(c => c !== undefined);
                    }
                }
            } catch (error) {
                console.error('Error fetching cases:', error);
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            settings,
            cases,
            loading,
        };
    },
};
</script>

