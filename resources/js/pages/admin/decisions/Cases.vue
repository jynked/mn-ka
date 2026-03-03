<template>
    <div class="cases-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Случаи</h1>
                <p class="text-muted-foreground mt-1">Управление случаями для блока решений</p>
            </div>
            <router-link
                :to="{ name: 'admin.decisions.cases.create' }"
                class="h-11 px-6 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-2xl shadow-lg shadow-accent/10 inline-flex items-center justify-center gap-2"
            >
                <span>+</span>
                <span>Создать случай</span>
            </router-link>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка случаев...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Filter -->
        <div v-if="!loading && chapters.length > 0" class="flex items-center gap-4">
            <select
                v-model="selectedChapter"
                @change="fetchCases"
                class="px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option :value="null">Все разделы</option>
                <option
                    v-for="chapter in chapters"
                    :key="chapter.id"
                    :value="chapter.id"
                >
                    {{ chapter.name }}
                </option>
            </select>
        </div>

        <!-- Cases List -->
        <div v-if="!loading && cases.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
            <div class="divide-y divide-border">
                <div
                    v-for="caseItem in cases"
                    :key="caseItem.id"
                    class="p-4 hover:bg-muted/10 transition-colors flex items-center justify-between"
                >
                    <div class="flex-1">
                        <h3 class="font-medium text-foreground">{{ caseItem.name }}</h3>
                        <p class="text-sm text-muted-foreground">
                            Раздел: {{ caseItem.chapter?.name || 'Не указан' }} | 
                            Порядок: {{ caseItem.order }} | 
                            {{ caseItem.is_active ? 'Активен' : 'Неактивен' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="{ name: 'admin.decisions.cases.edit', params: { id: caseItem.id } }"
                            class="px-3 py-1.5 text-sm bg-muted hover:bg-muted/80 rounded-lg transition-colors inline-block"
                        >
                            Редактировать
                        </router-link>
                        <button
                            @click="deleteCase(caseItem)"
                            class="px-3 py-1.5 text-sm bg-destructive/10 text-destructive hover:bg-destructive/20 rounded-lg transition-colors"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && cases.length === 0" class="text-center py-12 bg-card rounded-lg border border-border">
            <p class="text-muted-foreground">Случаи не найдены</p>
        </div>

    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { apiGet, apiDelete } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'DecisionCases',
    setup() {
        const route = useRoute();
        const loading = ref(false);
        const error = ref(null);
        const cases = ref([]);
        const chapters = ref([]);
        const selectedChapter = ref(route.query.chapter_id ? parseInt(route.query.chapter_id) : null);

        const fetchChapters = async () => {
            try {
                const response = await apiGet('/chapters');
                if (response.ok) {
                    const data = await response.json();
                    chapters.value = data.data || [];
                }
            } catch (err) {
                console.error('Error fetching chapters:', err);
            }
        };

        const fetchCases = async () => {
            loading.value = true;
            error.value = null;
            try {
                let url = '/cases';
                if (selectedChapter.value) {
                    url += `?chapter_id=${selectedChapter.value}`;
                }
                const response = await apiGet(url);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки случаев');
                }
                const data = await response.json();
                cases.value = data.data || [];
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки случаев';
                console.error('Error fetching cases:', err);
            } finally {
                loading.value = false;
            }
        };

        const deleteCase = async (caseItem) => {
            const result = await Swal.fire({
                title: 'Удалить случай?',
                text: `Вы уверены, что хотите удалить случай "${caseItem.name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
                confirmButtonColor: '#dc2626',
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await apiDelete(`/cases/${caseItem.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка удаления случая');
                }

                await Swal.fire({
                    title: 'Случай удален',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                await fetchCases();
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка удаления случая',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            }
        };

        onMounted(() => {
            fetchChapters();
            fetchCases();
        });

        return {
            loading,
            error,
            cases,
            chapters,
            selectedChapter,
            fetchCases,
            deleteCase,
        };
    },
};
</script>





