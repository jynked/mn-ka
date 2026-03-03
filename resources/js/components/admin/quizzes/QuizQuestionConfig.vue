<template>
    <div class="quiz-question-config space-y-4">
        <!-- Конфигурация для images-collect -->
        <div v-if="question.question_type === 'images-collect'" class="space-y-4">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium">Изображения для выбора</label>
                <button
                    @click="addImageSelect"
                    type="button"
                    class="px-3 py-1 text-xs bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20"
                >
                    + Добавить изображение
                </button>
            </div>
            <div v-if="question.question_data?.selects && question.question_data.selects.length > 0" class="space-y-3">
                <div
                    v-for="(select, selectIndex) in question.question_data.selects"
                    :key="selectIndex"
                    class="border border-border rounded-lg p-3 space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Изображение {{ selectIndex + 1 }}</span>
                        <button
                            @click="removeImageSelect(selectIndex)"
                            type="button"
                            class="px-2 py-1 text-xs bg-destructive/10 text-destructive rounded hover:bg-destructive/20"
                        >
                            Удалить
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium mb-1 block">Изображение</label>
                            <div class="flex items-center gap-3">
                                <div v-if="selectedImages[selectIndex]" class="flex-shrink-0">
                                    <img
                                        :src="selectedImages[selectIndex]?.url || select.src"
                                        :alt="selectedImages[selectIndex]?.original_name || 'Изображение'"
                                        class="w-20 h-20 object-cover rounded border border-border"
                                        @error="handleImageError($event, selectIndex)"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="openImageModal(selectIndex)"
                                    class="px-3 py-1.5 text-xs border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors"
                                >
                                    {{ selectedImages[selectIndex] || select.src ? 'Изменить' : 'Выбрать изображение' }}
                                </button>
                                <button
                                    v-if="selectedImages[selectIndex] || select.src"
                                    type="button"
                                    @click="removeImage(selectIndex)"
                                    class="px-3 py-1.5 text-xs border border-destructive text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-medium mb-1 block">Заголовок</label>
                            <input
                                v-model="select.title"
                                type="text"
                                class="w-full h-9 px-2 text-sm border border-border rounded bg-background"
                                placeholder="Название опции"
                                @input="updateQuestionData"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium mb-1 block">ID следующего вопроса</label>
                        <input
                            v-model.number="select.child"
                            type="number"
                            class="w-32 h-9 px-2 text-sm border border-border rounded bg-background"
                            placeholder="Оставьте пустым для следующего"
                            @input="updateQuestionData"
                        />
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-muted-foreground">Добавьте изображения для выбора</p>
        </div>

        <!-- Конфигурация для selects -->
        <div v-else-if="question.question_type === 'selects'" class="space-y-4">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium">Варианты выбора</label>
                <button
                    @click="addSelectOption"
                    type="button"
                    class="px-3 py-1 text-xs bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20"
                >
                    + Добавить вариант
                </button>
            </div>
            <div v-if="question.question_data?.selects && question.question_data.selects.length > 0" class="space-y-2">
                <div
                    v-for="(select, selectIndex) in question.question_data.selects"
                    :key="selectIndex"
                    class="flex items-center gap-2"
                >
                    <input
                        v-model="select.name"
                        type="text"
                        class="flex-1 h-9 px-2 text-sm border border-border rounded bg-background"
                        placeholder="Название варианта"
                        @input="updateQuestionData"
                    />
                    <button
                        @click="removeSelectOption(selectIndex)"
                        type="button"
                        class="px-2 py-1 text-xs bg-destructive/10 text-destructive rounded hover:bg-destructive/20"
                    >
                        Удалить
                    </button>
                </div>
            </div>
            <p v-else class="text-sm text-muted-foreground">Добавьте варианты выбора</p>
        </div>

        <!-- Конфигурация для inputs -->
        <div v-else-if="question.question_type === 'inputs'" class="space-y-3">
            <div>
                <label class="text-sm font-medium mb-1 block">Метка поля</label>
                <input
                    v-model="question.question_data.label"
                    type="text"
                    class="w-full h-9 px-2 text-sm border border-border rounded bg-background"
                    placeholder="Площадь участка"
                    @input="updateQuestionData"
                />
            </div>
            <div>
                <label class="text-sm font-medium mb-1 block">Placeholder</label>
                <input
                    v-model="question.question_data.placeholder"
                    type="text"
                    class="w-full h-9 px-2 text-sm border border-border rounded bg-background"
                    placeholder="Введите площадь вашего участка"
                    @input="updateQuestionData"
                />
            </div>
            <div>
                <label class="text-sm font-medium mb-1 block">ID следующего вопроса</label>
                <input
                    v-model.number="question.question_data.child"
                    type="number"
                    class="w-32 h-9 px-2 text-sm border border-border rounded bg-background"
                    placeholder="Оставьте пустым для следующего"
                    @input="updateQuestionData"
                />
            </div>
        </div>

        <!-- Конфигурация для forms -->
        <div v-else-if="question.question_type === 'forms'" class="space-y-3">
            <div>
                <label class="text-sm font-medium mb-1 block">Текст перед формой</label>
                <textarea
                    v-model="question.question_text"
                    rows="2"
                    class="w-full px-2 py-1 text-sm border border-border rounded bg-background"
                    placeholder="Готово! Мы рассчитаем для вас оптимальный формат проекта..."
                    @input="updateQuestionData"
                ></textarea>
            </div>
            <div>
                <label class="text-sm font-medium mb-1 block">ID следующего вопроса</label>
                <input
                    v-model.number="question.question_data.child"
                    type="number"
                    class="w-32 h-9 px-2 text-sm border border-border rounded bg-background"
                    placeholder="Оставьте пустым для следующего"
                    @input="updateQuestionData"
                />
            </div>
        </div>

        <!-- Конфигурация для thank -->
        <div v-else-if="question.question_type === 'thank'" class="text-sm text-muted-foreground">
            <p>Страница благодарности не требует дополнительных настроек.</p>
        </div>

        <!-- Media Modal for Image Selection -->
        <div v-if="showMediaModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col m-4">
                <div class="flex items-center justify-between p-4 border-b border-border">
                    <h3 class="text-lg font-semibold">Выбрать изображение</h3>
                    <button
                        @click="closeMediaModal"
                        class="text-muted-foreground hover:text-foreground w-8 h-8 flex items-center justify-center rounded hover:bg-muted/10"
                    >
                        ✕
                    </button>
                </div>
                <div class="flex-1 overflow-auto h-full">
                    <Media
                        :selection-mode="true"
                        :count-file="1"
                        @file-selected="handleImageSelected"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import Media from '../../../pages/admin/Media.vue';

export default {
    name: 'QuizQuestionConfig',
    components: {
        Media,
    },
    props: {
        question: {
            type: Object,
            required: true,
        },
        index: {
            type: Number,
            required: true,
        },
    },
    emits: ['update'],
    setup(props, { emit }) {
        const showMediaModal = ref(false);
        const currentImageIndex = ref(null);
        const selectedImages = ref({});

        // Инициализация существующих изображений по URL
        const loadExistingImages = () => {
            if (props.question.question_data?.selects) {
                props.question.question_data.selects.forEach((select, i) => {
                    if (select.src) {
                        // Создаем объект с URL для отображения превью
                        selectedImages.value[i] = {
                            url: select.src.startsWith('/') ? select.src : `/${select.src}`,
                            original_name: select.title || 'Изображение',
                        };
                    }
                });
            }
        };

        const updateQuestionData = () => {
            emit('update', props.question.question_data);
        };

        const openImageModal = (selectIndex) => {
            currentImageIndex.value = selectIndex;
            showMediaModal.value = true;
        };

        const closeMediaModal = () => {
            showMediaModal.value = false;
            currentImageIndex.value = null;
        };

        const handleImageSelected = (file) => {
            if (file) {
                const index = currentImageIndex.value;
                
                if (index !== null && props.question.question_data?.selects) {
                    // Сохраняем выбранный файл
                    selectedImages.value[index] = file;
                    
                    // Обновляем src в данных вопроса
                    props.question.question_data.selects[index].src = file.url;
                    
                    updateQuestionData();
                }
            }
            closeMediaModal();
        };

        const removeImage = (selectIndex) => {
            if (selectedImages.value[selectIndex]) {
                delete selectedImages.value[selectIndex];
            }
            if (props.question.question_data?.selects?.[selectIndex]) {
                props.question.question_data.selects[selectIndex].src = '';
            }
            updateQuestionData();
        };

        const handleImageError = (event, selectIndex) => {
            // Если изображение не загрузилось, скрываем превью
            event.target.style.display = 'none';
        };

        const addImageSelect = () => {
            if (!props.question.question_data) {
                props.question.question_data = {};
            }
            if (!props.question.question_data.selects) {
                props.question.question_data.selects = [];
            }
            const newIndex = props.question.question_data.selects.length;
            props.question.question_data.selects.push({
                id: newIndex + 1,
                name: '',
                src: '',
                title: '',
                child: null,
            });
            updateQuestionData();
        };

        const removeImageSelect = (index) => {
            if (props.question.question_data?.selects) {
                props.question.question_data.selects.splice(index, 1);
                // Удаляем из selectedImages
                delete selectedImages.value[index];
                // Переиндексируем selectedImages
                const newSelectedImages = {};
                Object.keys(selectedImages.value).forEach(key => {
                    const keyNum = parseInt(key);
                    if (keyNum < index) {
                        newSelectedImages[keyNum] = selectedImages.value[keyNum];
                    } else if (keyNum > index) {
                        newSelectedImages[keyNum - 1] = selectedImages.value[keyNum];
                    }
                });
                selectedImages.value = newSelectedImages;
                updateQuestionData();
            }
        };

        const addSelectOption = () => {
            if (!props.question.question_data) {
                props.question.question_data = {};
            }
            if (!props.question.question_data.selects) {
                props.question.question_data.selects = [];
            }
            props.question.question_data.selects.push({
                id: props.question.question_data.selects.length + 1,
                name: '',
            });
            updateQuestionData();
        };

        const removeSelectOption = (index) => {
            if (props.question.question_data?.selects) {
                props.question.question_data.selects.splice(index, 1);
                updateQuestionData();
            }
        };

        // Инициализация question_data если его нет
        if (!props.question.question_data) {
            if (props.question.question_type === 'images-collect' || props.question.question_type === 'selects') {
                props.question.question_data = { selects: [] };
            } else if (props.question.question_type === 'inputs') {
                props.question.question_data = { label: '', placeholder: '', child: null };
            } else if (props.question.question_type === 'forms') {
                props.question.question_data = { form: {}, child: null };
            } else {
                props.question.question_data = {};
            }
        }

        onMounted(() => {
            loadExistingImages();
        });

        return {
            showMediaModal,
            currentImageIndex,
            selectedImages,
            updateQuestionData,
            openImageModal,
            closeMediaModal,
            handleImageSelected,
            removeImage,
            handleImageError,
            addImageSelect,
            removeImageSelect,
            addSelectOption,
            removeSelectOption,
        };
    },
};
</script>
