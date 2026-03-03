<template>
    <div class="quiz-settings-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Настройки блока квиза</h1>
                <p class="text-muted-foreground mt-1">Настройки блока квиза на главной странице</p>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка настроек...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Settings Form -->
        <div v-if="!loading" class="bg-card rounded-lg border border-border p-6 space-y-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Выберите квиз для отображения
                    </label>
                    <select
                        v-model="form.quiz_id"
                        class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option :value="null">-- Не выбран --</option>
                        <option
                            v-for="quiz in quizzes"
                            :key="quiz.id"
                            :value="quiz.id"
                        >
                            {{ quiz.title }}
                        </option>
                    </select>
                    <p class="text-xs text-muted-foreground mt-1">
                        Выберите квиз, который будет отображаться на главной странице
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        id="is_active"
                        class="w-4 h-4 rounded border-border"
                    />
                    <label for="is_active" class="text-sm font-medium text-foreground">
                        Блок активен на главной странице
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-border">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-lg transition-colors disabled:opacity-50"
                >
                    {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiPut } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'QuizSettings',
    setup() {
        const loading = ref(false);
        const saving = ref(false);
        const error = ref(null);
        const quizzes = ref([]);
        const form = ref({
            quiz_id: null,
            is_active: true,
        });

        const fetchQuizzes = async () => {
            try {
                const response = await apiGet('/quizzes');
                if (response.ok) {
                    const data = await response.json();
                    quizzes.value = data.data || [];
                }
            } catch (err) {
                console.error('Error fetching quizzes:', err);
            }
        };

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/quiz-block-settings');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки настроек');
                }
                const data = await response.json();
                if (data.data) {
                    form.value = {
                        quiz_id: data.data.quiz_id || null,
                        is_active: data.data.is_active !== false,
                    };
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки настроек';
                console.error('Error fetching settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            try {
                const response = await apiPut('/quiz-block-settings', form.value);
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Ошибка сохранения настроек');
                }

                await Swal.fire({
                    title: 'Настройки сохранены',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } catch (err) {
                error.value = err.message || 'Ошибка сохранения настроек';
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка сохранения настроек',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                saving.value = false;
            }
        };

        onMounted(async () => {
            await Promise.all([
                fetchQuizzes(),
                fetchSettings(),
            ]);
        });

        return {
            loading,
            saving,
            error,
            quizzes,
            form,
            saveSettings,
        };
    },
};
</script>


