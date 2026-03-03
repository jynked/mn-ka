<template>
    <div class="chapters-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Разделы</h1>
                <p class="text-muted-foreground mt-1">Управление разделами для блока решений</p>
            </div>
            <router-link
                :to="{ name: 'admin.decisions.chapters.create' }"
                class="h-11 px-6 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-2xl shadow-lg shadow-accent/10 inline-flex items-center justify-center gap-2"
            >
                <span>+</span>
                <span>Создать раздел</span>
            </router-link>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка разделов...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Chapters List -->
        <div v-if="!loading && chapters.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
            <div class="divide-y divide-border">
                <div
                    v-for="chapter in chapters"
                    :key="chapter.id"
                    class="p-4 hover:bg-muted/10 transition-colors flex items-center justify-between"
                >
                    <div class="flex-1">
                        <h3 class="font-medium text-foreground">{{ chapter.name }}</h3>
                        <p class="text-sm text-muted-foreground">
                            Порядок: {{ chapter.order }} | 
                            {{ chapter.is_active ? 'Активен' : 'Неактивен' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="{ name: 'admin.decisions.chapters.edit', params: { id: chapter.id } }"
                            class="px-3 py-1.5 text-sm bg-muted hover:bg-muted/80 rounded-lg transition-colors inline-block"
                        >
                            Редактировать
                        </router-link>
                        <button
                            @click="deleteChapter(chapter)"
                            class="px-3 py-1.5 text-sm bg-destructive/10 text-destructive hover:bg-destructive/20 rounded-lg transition-colors"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && chapters.length === 0" class="text-center py-12 bg-card rounded-lg border border-border">
            <p class="text-muted-foreground">Разделы не найдены</p>
        </div>

    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiDelete } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'Chapters',
    setup() {
        const loading = ref(false);
        const error = ref(null);
        const chapters = ref([]);

        const fetchChapters = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/chapters');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки разделов');
                }
                const data = await response.json();
                chapters.value = data.data || [];
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки разделов';
                console.error('Error fetching chapters:', err);
            } finally {
                loading.value = false;
            }
        };

        const deleteChapter = async (chapter) => {
            const result = await Swal.fire({
                title: 'Удалить раздел?',
                text: `Вы уверены, что хотите удалить раздел "${chapter.name}"?`,
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
                const response = await apiDelete(`/chapters/${chapter.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка удаления раздела');
                }

                await Swal.fire({
                    title: 'Раздел удален',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                await fetchChapters();
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка удаления раздела',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            }
        };

        onMounted(() => {
            fetchChapters();
        });

        return {
            loading,
            error,
            chapters,
            fetchChapters,
            deleteChapter,
        };
    },
};
</script>
