<template>
    <div class="email-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки Email</h1>
            <p class="text-muted-foreground mt-1">
                Настройка email адреса для получения форм с сайта
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
            <!-- Email Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Email для получения форм</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Email адрес получателя
                    </label>
                    <input
                        type="email"
                        v-model="settings.recipient_email"
                        placeholder="info@mn-ka.ru"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    />
                    <p class="text-xs text-muted-foreground mt-2">
                        На этот email будут отправляться все формы обратной связи, заявки на продукты и другие формы с сайта
                    </p>
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
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'EmailSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const settings = ref(null);

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                const response = await axios.get('/api/v1/email-settings');
                settings.value = response.data.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching email settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            
            try {
                await axios.put('/api/v1/email-settings', settings.value);
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки email успешно сохранены.',
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
            saveSettings,
        };
    },
};
</script>
