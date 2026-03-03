<template>
    <div class="cases-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Кейсы</h1>
            <p class="text-muted-foreground mt-1">Управление кейсами (портфолио проектов)</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
                <p class="text-muted-foreground">Загрузка кейсов...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Cases List -->
        <div v-if="!loading && !error" class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Поиск по названию..."
                        class="px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    />
                    <select
                        v-model="selectedChapter"
                        class="px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="">Все разделы</option>
                        <option v-for="chapter in chapters" :key="chapter.id" :value="chapter.id">
                            {{ chapter.name }}
                        </option>
                    </select>
                </div>
                <button
                    @click="openCreateModal"
                    class="px-4 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors text-sm font-medium flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить кейс
                </button>
            </div>

            <div v-if="filteredCases.length === 0" class="text-center py-12 text-muted-foreground">
                <p>Кейсы не найдены</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="caseItem in filteredCases"
                    :key="caseItem.id"
                    class="bg-card border border-border rounded-lg overflow-hidden hover:shadow-md transition-shadow"
                >
                    <div v-if="caseItem.image" class="w-full h-48 overflow-hidden bg-muted">
                        <img
                            :src="caseItem.image.url"
                            :alt="caseItem.name"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-foreground mb-2">{{ caseItem.name }}</h3>
                        <p v-if="caseItem.chapter" class="text-sm text-muted-foreground mb-2">
                            Раздел: {{ caseItem.chapter.name }}
                        </p>
                        <div class="flex items-center justify-between mt-4">
                            <span
                                class="px-2 py-1 text-xs rounded"
                                :class="caseItem.is_active ? 'bg-green-500/20 text-green-600' : 'bg-muted text-muted-foreground'"
                            >
                                {{ caseItem.is_active ? 'Активен' : 'Неактивен' }}
                            </span>
                            <div class="flex gap-2">
                                <button
                                    @click="editCase(caseItem)"
                                    class="p-2 text-accent hover:bg-accent/10 rounded transition-colors"
                                    title="Редактировать"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteCase(caseItem.id)"
                                    class="p-2 text-destructive hover:bg-destructive/10 rounded transition-colors"
                                    title="Удалить"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'Cases',
    setup() {
        const router = useRouter();
        const loading = ref(true);
        const error = ref(null);
        const cases = ref([]);
        const chapters = ref([]);
        const searchQuery = ref('');
        const selectedChapter = ref('');

        const fetchCases = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/v1/cases');
                cases.value = response.data.data || [];
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки кейсов';
                console.error('Error fetching cases:', err);
            } finally {
                loading.value = false;
            }
        };

        const fetchChapters = async () => {
            try {
                const response = await axios.get('/api/v1/chapters');
                chapters.value = response.data.data || [];
            } catch (err) {
                console.error('Error fetching chapters:', err);
            }
        };

        const filteredCases = computed(() => {
            let filtered = cases.value;

            if (searchQuery.value) {
                const query = searchQuery.value.toLowerCase();
                filtered = filtered.filter(c => 
                    c.name.toLowerCase().includes(query)
                );
            }

            if (selectedChapter.value) {
                filtered = filtered.filter(c => 
                    c.chapter_id == selectedChapter.value
                );
            }

            return filtered;
        });

        const openCreateModal = () => {
            router.push({ name: 'admin.cases.create' });
        };

        const editCase = (caseItem) => {
            router.push({ name: 'admin.cases.edit', params: { id: caseItem.id } });
        };

        const deleteCase = async (id) => {
            const result = await Swal.fire({
                title: 'Удалить кейс?',
                text: 'Это действие нельзя отменить',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
                confirmButtonColor: '#ef4444',
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/api/v1/cases/${id}`);
                    await Swal.fire({
                        title: 'Удалено',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    fetchCases();
                } catch (err) {
                    await Swal.fire({
                        title: 'Ошибка',
                        text: err.response?.data?.message || 'Не удалось удалить кейс',
                        icon: 'error',
                    });
                }
            }
        };

        onMounted(() => {
            fetchCases();
            fetchChapters();
        });

        return {
            loading,
            error,
            cases,
            chapters,
            searchQuery,
            selectedChapter,
            filteredCases,
            openCreateModal,
            editCase,
            deleteCase,
        };
    },
};
</script>





