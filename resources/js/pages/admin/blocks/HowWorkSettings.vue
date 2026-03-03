<template>
    <div class="how-work-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки блока "Как мы работаем"</h1>
            <p class="text-muted-foreground mt-1">Управление блоком "Как мы работаем" на главной странице</p>
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
        <div v-if="!loading" class="space-y-6">
            <!-- General Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Общие настройки</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Заголовок блока
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Как мы работаем"
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        />
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
            </div>

            <!-- Image Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Изображение</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Изображение
                        </label>
                        <div class="space-y-3">
                            <div v-if="form.image" class="relative">
                                <img
                                    :src="form.image.startsWith('data:') || form.image.startsWith('/') ? form.image : `/${form.image}`"
                                    alt="Preview"
                                    class="w-full max-w-md h-auto object-cover rounded-lg border border-border"
                                />
                                <div class="mt-2 flex gap-2">
                                    <button
                                        type="button"
                                        @click="selectImage"
                                        class="px-4 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20"
                                    >
                                        Изменить изображение
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.image = null; form.image_alt = ''"
                                        class="px-4 py-2 bg-destructive/10 text-destructive border border-destructive/40 rounded hover:bg-destructive/20"
                                    >
                                        Удалить
                                    </button>
                                </div>
                            </div>
                            <div v-else>
                                <button
                                    type="button"
                                    @click="selectImage"
                                    class="w-full h-32 border-2 border-dashed border-border rounded flex items-center justify-center hover:bg-muted/10 transition-colors"
                                >
                                    <span class="text-muted-foreground">Выберите изображение</span>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Рекомендуемый размер: 510x290px (десктоп), 290x165px (мобильный)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Alt текст для изображения
                        </label>
                        <input
                            v-model="form.image_alt"
                            type="text"
                            placeholder="Как мы работаем"
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        />
                    </div>
                </div>
            </div>

            <!-- Button Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Настройки кнопки</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Текст кнопки
                        </label>
                        <input
                            v-model="form.button_text"
                            type="text"
                            placeholder="Заказать обратный звонок"
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Тип кнопки
                        </label>
                        <select
                            v-model="form.button_type"
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        >
                            <option value="url">URL (ссылка)</option>
                            <option value="method">Метод (popup)</option>
                        </select>
                    </div>

                    <div v-if="form.button_type === 'url'">
                        <label class="block text-sm font-medium text-foreground mb-2">
                            URL
                        </label>
                        <input
                            v-model="form.button_value"
                            type="text"
                            placeholder="/page или https://example.com"
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        />
                    </div>

                    <div v-if="form.button_type === 'method'">
                        <label class="block text-sm font-medium text-foreground mb-2">
                            ID метода
                        </label>
                        <input
                            v-model="form.button_value"
                            type="text"
                            placeholder="Методы будут добавлены позже"
                            disabled
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg opacity-50 cursor-not-allowed"
                        />
                        <p class="text-xs text-muted-foreground mt-1">Выбор методов будет доступен после создания функционала методов</p>
                    </div>
                </div>
            </div>

            <!-- Steps -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Шаги работы</h2>
                    <button
                        @click="addStep"
                        class="px-4 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors text-sm font-medium"
                    >
                        + Добавить шаг
                    </button>
                </div>

                <div v-if="form.steps && form.steps.length === 0" class="text-center py-8 text-muted-foreground">
                    <p>Нет шагов. Добавьте первый шаг.</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(step, index) in form.steps"
                        :key="index"
                        class="border border-border rounded-lg p-4 space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-foreground">Шаг {{ index + 1 }}</h3>
                            <button
                                @click="removeStep(index)"
                                class="px-3 py-1 text-sm text-destructive hover:bg-destructive/10 rounded transition-colors"
                            >
                                Удалить
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Тип иконки
                                </label>
                                <select
                                    v-model="step.point"
                                    class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                                >
                                    <option value="disc">Круг</option>
                                    <option value="star">Звезда</option>
                                </select>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Круг для обычных шагов, звезда для финального шага
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Заголовок шага
                            </label>
                            <textarea
                                v-model="step.title"
                                rows="2"
                                placeholder="Вы оставляете заявку"
                                class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent resize-none"
                            ></textarea>
                            <p class="text-xs text-muted-foreground mt-1">
                                Можно использовать HTML теги, например &lt;br&gt; для переноса строки
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Описание шага (необязательно)
                            </label>
                            <textarea
                                v-model="step.description"
                                rows="2"
                                placeholder="занимает не более 1-ой минуты"
                                class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent resize-none"
                            ></textarea>
                            <p class="text-xs text-muted-foreground mt-1">
                                Можно использовать HTML теги, например &lt;br&gt; для переноса строки
                            </p>
                        </div>

                        <!-- Кнопки перемещения -->
                        <div class="flex gap-2">
                            <button
                                v-if="index > 0"
                                @click="moveStep(index, 'up')"
                                class="px-3 py-1 text-sm border border-border hover:bg-accent/10 rounded transition-colors"
                                title="Переместить вверх"
                            >
                                ↑ Вверх
                            </button>
                            <button
                                v-if="index < form.steps.length - 1"
                                @click="moveStep(index, 'down')"
                                class="px-3 py-1 text-sm border border-border hover:bg-accent/10 rounded transition-colors"
                                title="Переместить вниз"
                            >
                                ↓ Вниз
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-border">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                >
                    {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                </button>
            </div>
        </div>

        <!-- Media Selector Modal -->
        <div
            v-if="showMediaModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto p-4"
            @click.self="showMediaModal = false"
        >
            <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-7xl max-h-[95vh] overflow-hidden flex flex-col my-auto">
                <div class="p-4 border-b border-border flex items-center justify-between flex-shrink-0">
                    <h3 class="text-lg font-semibold">Выберите изображение</h3>
                    <button
                        @click="showMediaModal = false"
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
import axios from 'axios';
import Swal from 'sweetalert2';
import Media from '../Media.vue';

export default {
    name: 'HowWorkSettings',
    components: {
        Media,
    },
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const showMediaModal = ref(false);
        const form = ref({
            title: '',
            subtitle: null,
            image: null,
            image_alt: '',
            button_text: '',
            button_type: 'url',
            button_value: '',
            is_active: true,
            steps: [],
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/v1/how-work-block-settings');
                if (response.data && response.data.data) {
                    form.value = {
                        title: response.data.data.title || '',
                        subtitle: response.data.data.subtitle || null,
                        image: response.data.data.image || null,
                        image_alt: response.data.data.image_alt || '',
                        button_text: response.data.data.button_text || '',
                        button_type: response.data.data.button_type || 'url',
                        button_value: response.data.data.button_value || '',
                        is_active: response.data.data.is_active !== false,
                        steps: response.data.data.steps || [],
                    };
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const selectImage = () => {
            showMediaModal.value = true;
        };

        const handleImageSelected = (file) => {
            if (file && file.type === 'photo') {
                const imagePath = file.url.replace(/^\//, '');
                form.value.image = imagePath;
                if (!form.value.image_alt) {
                    form.value.image_alt = file.original_name || 'Как мы работаем';
                }
                showMediaModal.value = false;
            }
        };

        const addStep = () => {
            if (!form.value.steps) {
                form.value.steps = [];
            }
            form.value.steps.push({
                point: 'disc',
                title: '',
                description: null,
            });
        };

        const removeStep = (index) => {
            if (form.value.steps && form.value.steps.length > index) {
                form.value.steps.splice(index, 1);
            }
        };

        const moveStep = (index, direction) => {
            if (!form.value.steps || form.value.steps.length <= 1) return;
            
            const newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex >= 0 && newIndex < form.value.steps.length) {
                const step = form.value.steps[index];
                form.value.steps[index] = form.value.steps[newIndex];
                form.value.steps[newIndex] = step;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            try {
                const response = await axios.put('/api/v1/how-work-block-settings', form.value);
                
                await Swal.fire({
                    title: 'Настройки сохранены',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка сохранения настроек';
                await Swal.fire({
                    title: 'Ошибка',
                    text: error.value,
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                saving.value = false;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            error,
            form,
            showMediaModal,
            selectImage,
            handleImageSelected,
            addStep,
            removeStep,
            moveStep,
            saveSettings,
        };
    },
};
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>
