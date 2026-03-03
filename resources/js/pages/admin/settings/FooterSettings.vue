<template>
    <div class="footer-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки футера</h1>
            <p class="text-muted-foreground mt-1">
                Редактирование настроек футера сайта
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
            <!-- Basic Information -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Основная информация</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Заголовок
                    </label>
                    <input
                        type="text"
                        v-model="settings.title"
                        placeholder="Контакты"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Копирайт
                    </label>
                    <input
                        type="text"
                        v-model="settings.copyright"
                        placeholder="MNKA 2025. Все права защищены"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Ссылка на политику конфиденциальности
                    </label>
                    <input
                        type="text"
                        v-model="settings.privacy_policy_link"
                        placeholder="/police"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    />
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Контактная информация</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Метка отдела
                        </label>
                        <input
                            type="text"
                            v-model="settings.department_label"
                            placeholder="Отдел"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Телефон отдела
                        </label>
                        <input
                            type="text"
                            v-model="settings.department_phone"
                            placeholder="+7 (495) 123-45-67"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Метка объектов
                        </label>
                        <input
                            type="text"
                            v-model="settings.objects_label"
                            placeholder="Объекты"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Телефон объектов
                        </label>
                        <input
                            type="text"
                            v-model="settings.objects_phone"
                            placeholder="+7 (495) 123-45-68"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Метка вопросов
                        </label>
                        <input
                            type="text"
                            v-model="settings.issues_label"
                            placeholder="Вопросы"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Email для вопросов
                        </label>
                        <input
                            type="email"
                            v-model="settings.issues_email"
                            placeholder="info@example.com"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Social Networks -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Социальные сети</h2>
                
                <div class="space-y-6">
                    <!-- VK -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                VK
                            </label>
                            <input
                                type="url"
                                v-model="settings.social_networks.vk"
                                placeholder="https://vk.com/example"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">
                                Иконка для VK (SVG код)
                            </label>
                            <textarea
                                v-model="settings.vk_icon_svg"
                                placeholder="Вставьте SVG код иконки"
                                rows="4"
                                class="w-full px-3 py-2 border border-border rounded bg-background text-xs font-mono"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">
                                Или выберите изображение из медиа
                            </label>
                            <div class="space-y-2">
                                <div v-if="settings.vk_icon" class="flex items-center gap-3">
                                    <img
                                        :src="settings.vk_icon.url"
                                        alt="VK icon"
                                        class="w-[60px] h-[60px] object-contain rounded border border-border bg-background"
                                    />
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="selectIcon('vk')"
                                            class="px-3 py-1.5 text-xs bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20"
                                        >
                                            Изменить
                                        </button>
                                        <button
                                            type="button"
                                            @click="clearIcon('vk')"
                                            class="px-3 py-1.5 text-xs bg-destructive/10 text-destructive border border-destructive/40 rounded hover:bg-destructive/20"
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                                <div v-else>
                                    <button
                                        type="button"
                                        @click="selectIcon('vk')"
                                        class="w-full h-10 border-2 border-dashed border-border rounded flex items-center justify-center hover:bg-muted/10 text-xs text-muted-foreground"
                                    >
                                        Выбрать иконку VK из медиа
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instagram -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Instagram
                            </label>
                            <input
                                type="url"
                                v-model="settings.social_networks.instagram"
                                placeholder="https://instagram.com/example"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">
                                Иконка для Instagram (SVG код)
                            </label>
                            <textarea
                                v-model="settings.instagram_icon_svg"
                                placeholder="Вставьте SVG код иконки"
                                rows="4"
                                class="w-full px-3 py-2 border border-border rounded bg-background text-xs font-mono"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">
                                Или выберите изображение из медиа
                            </label>
                            <div class="space-y-2">
                                <div v-if="settings.instagram_icon" class="flex items-center gap-3">
                                    <img
                                        :src="settings.instagram_icon.url"
                                        alt="Instagram icon"
                                        class="w-[60px] h-[60px] object-contain rounded border border-border bg-background"
                                    />
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="selectIcon('instagram')"
                                            class="px-3 py-1.5 text-xs bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20"
                                        >
                                            Изменить
                                        </button>
                                        <button
                                            type="button"
                                            @click="clearIcon('instagram')"
                                            class="px-3 py-1.5 text-xs bg-destructive/10 text-destructive border border-destructive/40 rounded hover:bg-destructive/20"
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                                <div v-else>
                                    <button
                                        type="button"
                                        @click="selectIcon('instagram')"
                                        class="w-full h-10 border-2 border-dashed border-border rounded flex items-center justify-center hover:bg-muted/10 text-xs text-muted-foreground"
                                    >
                                        Выбрать иконку Instagram из медиа
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Telegram -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Telegram
                            </label>
                            <input
                                type="url"
                                v-model="settings.social_networks.telegram"
                                placeholder="https://t.me/example"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">
                                Иконка для Telegram (SVG код)
                            </label>
                            <textarea
                                v-model="settings.telegram_icon_svg"
                                placeholder="Вставьте SVG код иконки"
                                rows="4"
                                class="w-full px-3 py-2 border border-border rounded bg-background text-xs font-mono"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">
                                Или выберите изображение из медиа
                            </label>
                            <div class="space-y-2">
                                <div v-if="settings.telegram_icon" class="flex items-center gap-3">
                                    <img
                                        :src="settings.telegram_icon.url"
                                        alt="Telegram icon"
                                        class="w-[60px] h-[60px] object-contain rounded border border-border bg-background"
                                    />
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="selectIcon('telegram')"
                                            class="px-3 py-1.5 text-xs bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20"
                                        >
                                            Изменить
                                        </button>
                                        <button
                                            type="button"
                                            @click="clearIcon('telegram')"
                                            class="px-3 py-1.5 text-xs bg-destructive/10 text-destructive border border-destructive/40 rounded hover:bg-destructive/20"
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                                <div v-else>
                                    <button
                                        type="button"
                                        @click="selectIcon('telegram')"
                                        class="w-full h-10 border-2 border-dashed border-border rounded flex items-center justify-center hover:bg-muted/10 text-xs text-muted-foreground"
                                    >
                                        Выбрать иконку Telegram из медиа
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent text-accent-foreground rounded hover:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
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
    name: 'FooterSettings',
    components: {
        Media,
    },
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const settings = ref(null);

        const showMediaModal = ref(false);
        const selectedSocialKey = ref(null);

        const ensureDefaults = (data) => {
            if (!data.social_networks || typeof data.social_networks !== 'object') {
                data.social_networks = {
                    vk: null,
                    instagram: null,
                    telegram: null,
                };
            }

            if (typeof data.vk_icon_id === 'undefined') {
                data.vk_icon_id = null;
            }
            if (typeof data.instagram_icon_id === 'undefined') {
                data.instagram_icon_id = null;
            }
            if (typeof data.telegram_icon_id === 'undefined') {
                data.telegram_icon_id = null;
            }

            if (typeof data.vk_icon === 'undefined') {
                data.vk_icon = null;
            }
            if (typeof data.instagram_icon === 'undefined') {
                data.instagram_icon = null;
            }
            if (typeof data.telegram_icon === 'undefined') {
                data.telegram_icon = null;
            }

            if (typeof data.vk_icon_svg === 'undefined') {
                data.vk_icon_svg = null;
            }
            if (typeof data.instagram_icon_svg === 'undefined') {
                data.instagram_icon_svg = null;
            }
            if (typeof data.telegram_icon_svg === 'undefined') {
                data.telegram_icon_svg = null;
            }

            return data;
        };

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                const response = await axios.get('/api/v1/footer-settings');
                const data = ensureDefaults(response.data.data || {});
                settings.value = data;
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching footer settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            if (!settings.value) return;

            saving.value = true;
            error.value = null;
            
            try {
                const payload = {
                    ...settings.value,
                };

                await axios.put('/api/v1/footer-settings', payload);
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки футера успешно сохранены.',
                    timer: 2000,
                    showConfirmButton: false,
                });
            } catch (err) {
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

        const selectIcon = (socialKey) => {
            selectedSocialKey.value = socialKey;
            showMediaModal.value = true;
        };

        const handleIconSelected = (file) => {
            if (!file || file.type !== 'photo' || !selectedSocialKey.value || !settings.value) {
                return;
            }

            const key = selectedSocialKey.value;
            const idField = `${key}_icon_id`;
            const iconField = `${key}_icon`;

            settings.value[idField] = file.id;
            settings.value[iconField] = file;

            selectedSocialKey.value = null;
            showMediaModal.value = false;
        };

        const clearIcon = (socialKey) => {
            if (!settings.value) return;
            const idField = `${socialKey}_icon_id`;
            const iconField = `${socialKey}_icon`;

            settings.value[idField] = null;
            settings.value[iconField] = null;
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
            showMediaModal,
            selectedSocialKey,
            selectIcon,
            handleIconSelected,
            clearIcon,
        };
    },
};
</script>

