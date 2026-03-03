<template>
    <div class="quiz-form-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">
                    {{ isEdit ? 'Редактировать квиз' : 'Создать квиз' }}
                </h1>
                <p class="text-muted-foreground mt-1">Управление квизом</p>
            </div>
            <button
                @click="goBack"
                class="h-11 px-6 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors"
            >
                Назад
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Form -->
        <div v-if="!loading" class="space-y-6">
            <!-- Основная информация -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Основная информация</h2>
                
                <div>
                    <label class="text-sm font-medium mb-1 block">Название квиза *</label>
                    <input
                        v-model="form.title"
                        type="text"
                        required
                        class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                        placeholder="Введите название квиза"
                    />
                </div>

                <div>
                    <label class="text-sm font-medium mb-1 block">Описание</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                        placeholder="Введите описание квиза"
                    ></textarea>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-4 h-4 rounded border-border"
                        />
                        <label for="is_active" class="text-sm font-medium">
                            Активен
                        </label>
                    </div>

                    <div>
                        <label class="text-sm font-medium mb-1 block">Порядок</label>
                        <input
                            v-model.number="form.order"
                            type="number"
                            min="0"
                            class="w-24 h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                        />
                    </div>
                </div>
            </div>

            <!-- Вопросы -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Вопросы квиза</h2>
                    <button
                        @click="addQuestion"
                        type="button"
                        class="px-4 py-2 text-sm bg-accent/10 text-accent border border-accent/40 hover:bg-accent/20 rounded-lg transition-colors"
                    >
                        + Добавить вопрос
                    </button>
                </div>

                <div v-if="form.questions && form.questions.length > 0" class="space-y-4">
                    <div
                        v-for="(question, index) in form.questions"
                        :key="index"
                        class="border border-border rounded-lg p-4 space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-foreground">Вопрос {{ index + 1 }}</h3>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="moveQuestion(index, 'up')"
                                    :disabled="index === 0"
                                    type="button"
                                    class="px-2 py-1 text-sm border border-border rounded hover:bg-muted/10 disabled:opacity-50"
                                    title="Переместить вверх"
                                >
                                    ↑
                                </button>
                                <button
                                    @click="moveQuestion(index, 'down')"
                                    :disabled="index === form.questions.length - 1"
                                    type="button"
                                    class="px-2 py-1 text-sm border border-border rounded hover:bg-muted/10 disabled:opacity-50"
                                    title="Переместить вниз"
                                >
                                    ↓
                                </button>
                                <button
                                    @click="removeQuestion(index)"
                                    type="button"
                                    class="px-2 py-1 text-sm bg-destructive/10 text-destructive rounded hover:bg-destructive/20"
                                    title="Удалить вопрос"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium mb-1 block">Тип вопроса *</label>
                                <select
                                    v-model="question.question_type"
                                    class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                                    @change="onQuestionTypeChange(question, index)"
                                >
                                    <option value="images-collect">Выбор из изображений</option>
                                    <option value="selects">Выбор из списка</option>
                                    <option value="inputs">Ввод текста</option>
                                    <option value="forms">Форма контактов</option>
                                    <option value="thank">Страница благодарности</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-medium mb-1 block">Текст вопроса</label>
                                <input
                                    v-model="question.question_text"
                                    type="text"
                                    class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                                    placeholder="Введите текст вопроса"
                                />
                            </div>
                        </div>

                        <!-- Компонент настроек вопроса в зависимости от типа -->
                        <QuizQuestionConfig
                            :question="question"
                            :index="index"
                            @update="updateQuestionData(index, $event)"
                        />
                    </div>
                </div>

                <div v-else class="text-center py-8 text-muted-foreground">
                    <p>Вопросы не добавлены. Добавьте первый вопрос.</p>
                </div>
            </div>

            <!-- Кнопки сохранения -->
            <div class="flex justify-end gap-3 pt-4">
                <button
                    @click="goBack"
                    type="button"
                    class="px-6 py-2 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors"
                >
                    Отмена
                </button>
                <button
                    @click="saveQuiz"
                    :disabled="saving"
                    type="button"
                    class="px-6 py-2 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-lg transition-colors disabled:opacity-50"
                >
                    {{ saving ? 'Сохранение...' : 'Сохранить квиз' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { apiGet, apiPost, apiPut } from '../../../utils/api';
import Swal from 'sweetalert2';
import QuizQuestionConfig from '../../../components/admin/quizzes/QuizQuestionConfig.vue';

export default {
    name: 'QuizForm',
    components: {
        QuizQuestionConfig,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const loading = ref(false);
        const saving = ref(false);
        const error = ref(null);
        const form = ref({
            id: null,
            title: '',
            description: '',
            order: 0,
            is_active: true,
            questions: [],
        });

        const isEdit = computed(() => !!route.params.id);

        const fetchQuiz = async () => {
            if (!isEdit.value) {
                loading.value = false;
                return;
            }

            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet(`/quizzes/${route.params.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки квиза');
                }
                const data = await response.json();
                if (data.data) {
                    form.value = {
                        id: data.data.id,
                        title: data.data.title || '',
                        description: data.data.description || '',
                        order: data.data.order ?? 0,
                        is_active: data.data.is_active !== false,
                        questions: (data.data.questions || []).map(q => ({
                            order: q.order || 0,
                            question_type: q.question_type,
                            question_text: q.question_text || '',
                            question_data: q.question_data || {},
                            is_active: q.is_active !== false,
                        })),
                    };
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки квиза';
                console.error('Error fetching quiz:', err);
            } finally {
                loading.value = false;
            }
        };

        const addQuestion = () => {
            form.value.questions.push({
                order: form.value.questions.length,
                question_type: 'images-collect',
                question_text: '',
                question_data: {},
                is_active: true,
            });
        };

        const removeQuestion = (index) => {
            form.value.questions.splice(index, 1);
            // Обновляем порядок вопросов
            form.value.questions.forEach((q, i) => {
                q.order = i;
            });
        };

        const moveQuestion = (index, direction) => {
            const newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex < 0 || newIndex >= form.value.questions.length) {
                return;
            }
            const [moved] = form.value.questions.splice(index, 1);
            form.value.questions.splice(newIndex, 0, moved);
            // Обновляем порядок
            form.value.questions.forEach((q, i) => {
                q.order = i;
            });
        };

        const onQuestionTypeChange = (question, index) => {
            // Инициализируем question_data в зависимости от типа
            if (question.question_type === 'images-collect') {
                question.question_data = { selects: [] };
            } else if (question.question_type === 'selects') {
                question.question_data = { selects: [] };
            } else if (question.question_type === 'inputs') {
                question.question_data = { label: '', placeholder: '', child: null };
            } else if (question.question_type === 'forms') {
                question.question_data = { form: { name: '', email: '', phone: '', check: false }, child: null };
            } else if (question.question_type === 'thank') {
                question.question_data = {};
            }
        };

        const updateQuestionData = (index, data) => {
            if (form.value.questions[index]) {
                form.value.questions[index].question_data = data;
            }
        };

        const saveQuiz = async () => {
            if (!form.value.title) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: 'Пожалуйста, укажите название квиза',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
                return;
            }

            saving.value = true;
            error.value = null;
            try {
                // Подготавливаем данные для отправки
                const payload = {
                    title: form.value.title,
                    description: form.value.description || null,
                    order: form.value.order || 0,
                    is_active: form.value.is_active,
                    questions: form.value.questions.map((q, index) => ({
                        order: q.order !== undefined ? q.order : index,
                        question_type: q.question_type,
                        question_text: q.question_text || null,
                        question_data: q.question_data || {},
                        is_active: q.is_active !== false,
                    })),
                };

                const url = isEdit.value ? `/quizzes/${route.params.id}` : '/quizzes';
                const method = isEdit.value ? apiPut : apiPost;
                
                const response = await method(url, payload);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Ошибка ${isEdit.value ? 'обновления' : 'создания'} квиза`);
                }

                await Swal.fire({
                    title: isEdit.value ? 'Квиз обновлен' : 'Квиз создан',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                router.push({ name: 'admin.quizzes.index' });
            } catch (err) {
                error.value = err.message || 'Ошибка сохранения квиза';
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка сохранения квиза',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                saving.value = false;
            }
        };

        const goBack = () => {
            router.push({ name: 'admin.quizzes.index' });
        };

        onMounted(() => {
            fetchQuiz();
        });

        return {
            loading,
            saving,
            error,
            form,
            isEdit,
            addQuestion,
            removeQuestion,
            moveQuestion,
            onQuestionTypeChange,
            updateQuestionData,
            saveQuiz,
            goBack,
        };
    },
};
</script>


