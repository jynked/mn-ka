<template>
    <div class="contact-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки контактов</h1>
            <p class="text-muted-foreground mt-1">
                Редактирование контактной информации, которая отображается на странице контактов.
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
            <!-- Contact Information -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-foreground">Контактная информация</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Телефон
                            </label>
                            <input
                                type="text"
                                v-model="settings.phone"
                                placeholder="+7 (495) 123-45-67"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Email
                            </label>
                            <input
                                type="email"
                                v-model="settings.email"
                                placeholder="info@example.com"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Адрес
                        </label>
                        <textarea
                            v-model="settings.address"
                            rows="2"
                            placeholder="г. Москва, ул. Примерная, д. 1"
                            class="w-full px-3 py-2 border border-border rounded bg-background text-sm"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Время работы
                        </label>
                        <input
                            type="text"
                            v-model="settings.working_hours"
                            placeholder="Пн-Пт: 9:00 - 18:00"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Social Networks -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Социальные сети</h2>
                    <button
                        @click="addSocial"
                        class="px-4 py-2 text-sm bg-accent text-accent-foreground rounded hover:bg-accent/90"
                    >
                        + Добавить
                    </button>
                </div>

                <div v-if="settings.socials && settings.socials.length === 0" class="text-center py-4 text-muted-foreground">
                    Нет добавленных социальных сетей
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(social, index) in settings.socials"
                        :key="index"
                        class="p-4 border border-border rounded-lg space-y-3"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Иконка (vk, telegram, instagram)
                                </label>
                                <input
                                    type="text"
                                    v-model="social.icon"
                                    placeholder="vk"
                                    class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Название
                                </label>
                                <input
                                    type="text"
                                    v-model="social.title"
                                    placeholder="ВКонтакте"
                                    class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Ссылка
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        type="url"
                                        v-model="social.link"
                                        placeholder="https://vk.com/..."
                                        class="flex-1 h-10 px-3 border border-border rounded bg-background text-sm"
                                    />
                                    <button
                                        @click="removeSocial(index)"
                                        class="px-4 py-2 text-sm bg-red-500 text-white rounded hover:bg-red-600"
                                    >
                                        Удалить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent text-accent-foreground rounded hover:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                >
                    {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'ContactSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const settings = ref(null);

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                const response = await axios.get('/api/v1/contact-settings');
                settings.value = response.data.data;
                
                // Инициализируем socials как массив, если его нет
                if (!settings.value.socials || !Array.isArray(settings.value.socials)) {
                    settings.value.socials = [];
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching contact settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const addSocial = () => {
            if (!settings.value.socials) {
                settings.value.socials = [];
            }
            settings.value.socials.push({
                icon: '',
                title: '',
                link: '',
            });
        };

        const removeSocial = (index) => {
            if (settings.value.socials && settings.value.socials.length > index) {
                settings.value.socials.splice(index, 1);
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            
            try {
                const response = await axios.put('/api/v1/contact-settings', settings.value);
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки контактов успешно сохранены.',
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

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            error,
            settings,
            addSocial,
            removeSocial,
            saveSettings,
        };
    },
};
</script>





