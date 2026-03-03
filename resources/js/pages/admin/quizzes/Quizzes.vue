<template>
    <div class="quizzes-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Квизы</h1>
                <p class="text-muted-foreground mt-1">Управление квизами</p>
            </div>
            <router-link
                :to="{ name: 'admin.quizzes.create' }"
                class="h-11 px-6 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-2xl shadow-lg shadow-accent/10 inline-flex items-center justify-center gap-2"
            >
                <span>+</span>
                <span>Создать квиз</span>
            </router-link>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка квизов...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Quizzes List -->
        <div v-if="!loading && quizzes.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
            <div class="divide-y divide-border">
                <div
                    v-for="quiz in quizzes"
                    :key="quiz.id"
                    class="p-4 hover:bg-muted/10 transition-colors flex items-center justify-between"
                >
                    <div class="flex-1">
                        <h3 class="font-medium text-foreground">{{ quiz.title }}</h3>
                        <p class="text-sm text-muted-foreground mt-1">
                            {{ quiz.description || 'Без описания' }}
                        </p>
                        <p class="text-sm text-muted-foreground mt-1">
                            Порядок: {{ quiz.order }} | 
                            Вопросов: {{ quiz.questions?.length || 0 }} | 
                            {{ quiz.is_active ? 'Активен' : 'Неактивен' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="{ name: 'admin.quizzes.edit', params: { id: quiz.id } }"
                            class="px-3 py-1.5 text-sm bg-muted hover:bg-muted/80 rounded-lg transition-colors inline-block"
                        >
                            Редактировать
                        </router-link>
                        <button
                            @click="deleteQuiz(quiz)"
                            class="px-3 py-1.5 text-sm bg-destructive/10 text-destructive hover:bg-destructive/20 rounded-lg transition-colors"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && quizzes.length === 0" class="text-center py-12 bg-card rounded-lg border border-border">
            <p class="text-muted-foreground">Квизы не найдены. Создайте первый квиз!</p>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiDelete } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'Quizzes',
    setup() {
        const loading = ref(false);
        const error = ref(null);
        const quizzes = ref([]);

        const fetchQuizzes = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/quizzes');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки квизов');
                }
                const data = await response.json();
                quizzes.value = data.data || [];
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки квизов';
                console.error('Error fetching quizzes:', err);
            } finally {
                loading.value = false;
            }
        };

        const deleteQuiz = async (quiz) => {
            const result = await Swal.fire({
                title: 'Удалить квиз?',
                text: `Вы уверены, что хотите удалить квиз "${quiz.title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
            });

            if (result.isConfirmed) {
                try {
                    const response = await apiDelete(`/quizzes/${quiz.id}`);
                    if (!response.ok) {
                        throw new Error('Ошибка удаления квиза');
                    }

                    await Swal.fire({
                        title: 'Квиз удален',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });

                    await fetchQuizzes();
                } catch (err) {
                    await Swal.fire({
                        title: 'Ошибка',
                        text: err.message || 'Ошибка удаления квиза',
                        icon: 'error',
                        confirmButtonText: 'ОК'
                    });
                }
            }
        };

        onMounted(() => {
            fetchQuizzes();
        });

        return {
            loading,
            error,
            quizzes,
            fetchQuizzes,
            deleteQuiz,
        };
    },
};
</script>


