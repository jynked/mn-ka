<template>
    <div class="case-card-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки карточек кейсов</h1>
            <p class="text-muted-foreground mt-1">
                Редактирование настроек отображения карточек кейсов
            </p>
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
        <div v-if="!loading && settings" class="space-y-6">
            <!-- Page Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Настройки страницы</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Заголовок страницы
                    </label>
                    <input
                        type="text"
                        v-model="settings.page_title"
                        placeholder="Наши кейсы"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Описание страницы
                    </label>
                    <textarea
                        v-model="settings.page_description"
                        placeholder="Описание страницы кейсов (опционально)"
                        rows="3"
                        class="w-full px-3 py-2 border border-border rounded bg-background text-sm resize-none"
                    ></textarea>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Настройки отображения</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Количество карточек на странице
                    </label>
                    <input
                        type="number"
                        v-model.number="settings.items_per_page"
                        min="1"
                        max="100"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    />
                    <p class="text-xs text-muted-foreground mt-1">
                        От 1 до 100 карточек на странице
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Соотношение сторон карточки
                    </label>
                    <select
                        v-model="settings.card_aspect_ratio"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    >
                        <option value="16/10">16:10 (по умолчанию)</option>
                        <option value="4/3">4:3</option>
                        <option value="3/2">3:2</option>
                        <option value="1/1">1:1 (квадрат)</option>
                        <option value="21/9">21:9</option>
                    </select>
                </div>
            </div>

            <!-- UI Elements Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Элементы интерфейса</h2>
                
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Показывать фильтры
                        </label>
                        <p class="text-xs text-muted-foreground">
                            Отображать кнопку фильтров на странице кейсов
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="settings.show_filters"
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Показывать хлебные крошки
                        </label>
                        <p class="text-xs text-muted-foreground">
                            Отображать навигационные хлебные крошки на странице кейсов
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="settings.show_breadcrumbs"
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end gap-3">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <svg
                        v-if="saving"
                        class="animate-spin h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    <span>{{ saving ? 'Сохранение...' : 'Сохранить настройки' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

export default {
    name: 'CaseCardSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const settings = ref({
            page_title: 'Наши кейсы',
            page_description: null,
            items_per_page: 6,
            show_filters: true,
            show_breadcrumbs: true,
            card_aspect_ratio: '16/10',
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;

            try {
                const response = await axios.get('/api/v1/case-card-settings');
                settings.value = response.data.data || settings.value;
            } catch (err) {
                console.error('Error fetching case card settings:', err);
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;

            try {
                const response = await axios.put('/api/v1/case-card-settings', settings.value);
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки карточек кейсов успешно сохранены.',
                    timer: 2000,
                    showConfirmButton: false,
                });
            } catch (err) {
                console.error('Error saving case card settings:', err);
                error.value = err.response?.data?.message || 'Ошибка сохранения настроек';
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: error.value,
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
            settings,
            saveSettings,
        };
    },
};
</script>

<style scoped>
.case-card-settings-page {
    max-width: 1200px;
    margin: 0 auto;
}
</style>

