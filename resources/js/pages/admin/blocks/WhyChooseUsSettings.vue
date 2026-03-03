<template>
    <div class="why-choose-us-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки блока "Почему выбирают нас"</h1>
            <p class="text-muted-foreground mt-1">Управление блоком "Почему выбирают нас" на главной странице</p>
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
                            placeholder="Почему выбирают нас"
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

            <!-- Items -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Карточки (максимум 6)</h2>
                    <button
                        @click="addItem"
                        :disabled="form.items && form.items.length >= 6"
                        class="px-4 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                    >
                        + Добавить карточку
                    </button>
                </div>

                <div v-if="!form.items || form.items.length === 0" class="text-center py-8 text-muted-foreground">
                    <p>Нет карточек. Добавьте первую карточку.</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(item, index) in form.items"
                        :key="index"
                        class="border border-border rounded-lg p-4 space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-foreground">Карточка {{ index + 1 }}</h3>
                            <button
                                @click="removeItem(index)"
                                class="px-3 py-1 text-sm text-destructive hover:bg-destructive/10 rounded transition-colors"
                            >
                                Удалить
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Текст карточки
                                </label>
                                <textarea
                                    v-model="item.text"
                                    rows="3"
                                    placeholder="500+ участков в базе"
                                    class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent resize-none"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Иконка
                                </label>
                                <div class="space-y-3">
                                    <div v-if="item.icon" class="relative">
                                        <img
                                            :src="item.icon.url || `/${item.icon.disk}/${item.icon.name}`"
                                            alt="Icon preview"
                                            class="w-20 h-20 object-contain rounded-lg border border-border"
                                        />
                                        <div class="mt-2 flex gap-2">
                                            <button
                                                type="button"
                                                @click="selectIcon(index)"
                                                class="px-4 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20 text-sm"
                                            >
                                                Изменить иконку
                                            </button>
                                            <button
                                                type="button"
                                                @click="item.icon_id = null; item.icon = null"
                                                class="px-4 py-2 bg-destructive/10 text-destructive border border-destructive/40 rounded hover:bg-destructive/20 text-sm"
                                            >
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <button
                                            type="button"
                                            @click="selectIcon(index)"
                                            class="w-full h-20 border-2 border-dashed border-border rounded flex items-center justify-center hover:bg-muted/10 transition-colors"
                                        >
                                            <span class="text-muted-foreground text-sm">Выберите иконку</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки перемещения -->
                        <div class="flex gap-2">
                            <button
                                v-if="index > 0"
                                @click="moveItem(index, 'up')"
                                class="px-3 py-1 text-sm border border-border hover:bg-accent/10 rounded transition-colors"
                                title="Переместить вверх"
                            >
                                ↑ Вверх
                            </button>
                            <button
                                v-if="index < form.items.length - 1"
                                @click="moveItem(index, 'down')"
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
            v-if="showMediaModal && selectedItemIndex !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto p-4"
            @click.self="showMediaModal = false"
        >
            <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-7xl max-h-[95vh] overflow-hidden flex flex-col my-auto">
                <div class="p-4 border-b border-border flex items-center justify-between flex-shrink-0">
                    <h3 class="text-lg font-semibold">Выберите иконку</h3>
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
                        @file-selected="handleIconSelected"
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
    name: 'WhyChooseUsSettings',
    components: {
        Media,
    },
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const showMediaModal = ref(false);
        const selectedItemIndex = ref(null);
        const form = ref({
            title: '',
            is_active: true,
            items: [],
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/v1/why-choose-us-block-settings');
                if (response.data && response.data.data) {
                    form.value = {
                        title: response.data.data.title || '',
                        is_active: response.data.data.is_active !== false,
                        items: response.data.data.items || [],
                    };
                    // Загружаем иконки для каждой карточки
                    if (form.value.items && form.value.items.length > 0) {
                        for (const item of form.value.items) {
                            if (item.icon_id) {
                                try {
                                    const iconResponse = await axios.get(`/api/v1/media/${item.icon_id}`);
                                    if (iconResponse.data && iconResponse.data.data) {
                                        item.icon = iconResponse.data.data;
                                    }
                                } catch (err) {
                                    console.error('Error fetching icon:', err);
                                }
                            }
                        }
                    }
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const selectIcon = (index) => {
            selectedItemIndex.value = index;
            showMediaModal.value = true;
        };

        const handleIconSelected = (file) => {
            if (file && file.type === 'photo' && selectedItemIndex.value !== null) {
                const item = form.value.items[selectedItemIndex.value];
                if (item) {
                    item.icon_id = file.id;
                    item.icon = file;
                }
                showMediaModal.value = false;
                selectedItemIndex.value = null;
            }
        };

        const addItem = () => {
            if (!form.value.items) {
                form.value.items = [];
            }
            if (form.value.items.length < 6) {
                form.value.items.push({
                    text: '',
                    icon_id: null,
                    icon: null,
                });
            }
        };

        const removeItem = (index) => {
            if (form.value.items && form.value.items.length > index) {
                form.value.items.splice(index, 1);
            }
        };

        const moveItem = (index, direction) => {
            if (!form.value.items || form.value.items.length <= 1) return;
            
            const newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex >= 0 && newIndex < form.value.items.length) {
                const item = form.value.items[index];
                form.value.items[index] = form.value.items[newIndex];
                form.value.items[newIndex] = item;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            try {
                // Подготавливаем данные для отправки
                const dataToSend = {
                    title: form.value.title,
                    is_active: form.value.is_active,
                    items: form.value.items.map(item => ({
                        text: item.text,
                        icon_id: item.icon_id,
                    })),
                };
                
                const response = await axios.put('/api/v1/why-choose-us-block-settings', dataToSend);
                
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
            selectedItemIndex,
            selectIcon,
            handleIconSelected,
            addItem,
            removeItem,
            moveItem,
            saveSettings,
        };
    },
};
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>



