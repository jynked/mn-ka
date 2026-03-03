<template>
    <div class="smtp-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки SMTP</h1>
            <p class="text-muted-foreground mt-1">
                Настройка параметров SMTP сервера для отправки почты
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
            <!-- SMTP Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Параметры SMTP</h2>
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="settings.is_active"
                            id="is_active"
                            class="w-4 h-4 rounded border-border"
                        />
                        <label for="is_active" class="text-sm font-medium text-foreground cursor-pointer">
                            Использовать эти настройки
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Mailer
                        </label>
                        <select
                            v-model="settings.mailer"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        >
                            <option value="smtp">SMTP</option>
                            <option value="sendmail">Sendmail</option>
                            <option value="mailgun">Mailgun</option>
                            <option value="ses">Amazon SES</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            SMTP Host
                        </label>
                        <input
                            type="text"
                            v-model="settings.host"
                            placeholder="smtp.beget.com"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            SMTP Port
                        </label>
                        <input
                            type="number"
                            v-model.number="settings.port"
                            placeholder="465"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Шифрование
                        </label>
                        <select
                            v-model="settings.encryption"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        >
                            <option value="ssl">SSL</option>
                            <option value="tls">TLS</option>
                            <option value="">Нет</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Username (Email)
                        </label>
                        <input
                            type="email"
                            v-model="settings.username"
                            placeholder="info@proffi-center.ru"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Password
                        </label>
                        <input
                            type="password"
                            v-model="password"
                            placeholder="Введите пароль (оставьте пустым чтобы не менять)"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                        <p class="text-xs text-muted-foreground mt-1">
                            Оставьте пустым, чтобы не изменять текущий пароль
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            From Email
                        </label>
                        <input
                            type="email"
                            v-model="settings.from_email"
                            placeholder="info@proffi-center.ru"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            From Name
                        </label>
                        <input
                            type="text"
                            v-model="settings.from_name"
                            placeholder="mn-ka.ru"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Test SMTP Section -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Тестирование SMTP</h2>
                <p class="text-sm text-muted-foreground">
                    Отправьте тестовое письмо для проверки настроек SMTP
                </p>
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Email для теста
                        </label>
                        <input
                            type="email"
                            v-model="testEmail"
                            placeholder="test@example.com"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                    <button
                        @click="testSmtp"
                        :disabled="testing || !testEmail"
                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium whitespace-nowrap"
                    >
                        {{ testing ? 'Отправка...' : 'Отправить тест' }}
                    </button>
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
    name: 'SmtpSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const testing = ref(false);
        const error = ref(null);
        const settings = ref(null);
        const password = ref('');
        const testEmail = ref('');

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                const response = await axios.get('/api/v1/smtp-settings');
                settings.value = response.data.data;
                password.value = ''; // Не показываем пароль
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching SMTP settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            
            try {
                const payload = {
                    ...settings.value,
                };

                // Добавляем пароль только если он был изменен
                if (password.value) {
                    payload.password = password.value;
                }

                await axios.put('/api/v1/smtp-settings', payload);
                
                // Очищаем поле пароля после сохранения
                password.value = '';
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки SMTP успешно сохранены.',
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

        const testSmtp = async () => {
            if (!testEmail.value) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Внимание',
                    text: 'Введите email адрес для теста',
                });
                return;
            }

            testing.value = true;
            error.value = null;

            try {
                await axios.post('/api/v1/smtp-settings/test', {
                    email: testEmail.value,
                });

                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    html: `Тестовое письмо отправлено на <strong>${testEmail.value}</strong><br><br>Проверьте почтовый ящик.`,
                    timer: 4000,
                });
            } catch (err) {
                const errorMessage = err.response?.data?.message || 'Ошибка при отправке тестового письма';
                const errorDetails = err.response?.data?.error?.message || '';
                
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка отправки',
                    html: `<p>${errorMessage}</p>${errorDetails ? `<p class="text-xs mt-2">${errorDetails}</p>` : ''}`,
                });
            } finally {
                testing.value = false;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            testing,
            error,
            settings,
            password,
            testEmail,
            saveSettings,
            testSmtp,
        };
    },
};
</script>
