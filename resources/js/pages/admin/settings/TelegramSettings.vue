<template>
    <div class="telegram-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки Telegram бота</h1>
            <p class="text-muted-foreground mt-1">
                Настройка интеграции с Telegram для отправки уведомлений и ошибок.
                После сохранения токена webhook регистрируется автоматически.
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
            <!-- Bot Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-foreground">Информация о боте</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Токен бота *
                            </label>
                            <input
                                type="password"
                                v-model="settings.bot_token"
                                placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            />
                            <p class="text-xs text-muted-foreground mt-1">
                                Получите токен у @BotFather в Telegram. После сохранения webhook будет зарегистрирован автоматически.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Имя бота
                            </label>
                            <input
                                type="text"
                                v-model="settings.bot_name"
                                placeholder="Admin Bot"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            />
                        </div>
                    </div>

                    <!-- Bot Info Display -->
                    <div v-if="botInfo" class="p-4 bg-muted/50 rounded-lg">
                        <h3 class="font-semibold text-foreground mb-2">Информация о боте:</h3>
                        <p class="text-sm text-foreground">
                            <strong>Имя:</strong> {{ botInfo.first_name }} {{ botInfo.last_name || '' }}<br>
                            <strong>Username:</strong> @{{ botInfo.username }}<br>
                            <strong>ID:</strong> {{ botInfo.id }}
                        </p>
                    </div>

                    <!-- Webhook Info -->
                    <div v-if="webhookInfo" class="p-4 bg-muted/50 rounded-lg">
                        <h3 class="font-semibold text-foreground mb-2">Информация о webhook:</h3>
                        <p class="text-sm text-foreground">
                            <strong>URL:</strong> {{ webhookInfo.url || 'Не установлен' }}<br>
                            <strong>Ожидает обновлений:</strong> {{ webhookInfo.pending_update_count || 0 }}<br>
                            <strong>Последняя ошибка:</strong> {{ webhookInfo.last_error_message || 'Нет' }}
                        </p>
                    </div>
                </div>

                <!-- General Settings -->
                <div class="space-y-4 border-t border-border pt-6">
                    <h2 class="text-xl font-semibold text-foreground">Общие настройки</h2>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="settings.is_enabled"
                                class="w-4 h-4 rounded border-border"
                            />
                            <span class="text-sm font-medium text-foreground">Включить бота</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="settings.send_notifications"
                                :disabled="!settings.is_enabled"
                                class="w-4 h-4 rounded border-border"
                            />
                            <span class="text-sm font-medium text-foreground">Отправлять уведомления</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="settings.send_errors"
                                :disabled="!settings.is_enabled"
                                class="w-4 h-4 rounded border-border"
                            />
                            <span class="text-sm font-medium text-foreground">Отправлять критические ошибки</span>
                        </label>
                    </div>
                </div>

                <!-- Message Settings -->
                <div class="space-y-4 border-t border-border pt-6">
                    <h2 class="text-xl font-semibold text-foreground">Настройки сообщений</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Режим парсинга
                            </label>
                            <select
                                v-model="settings.parse_mode"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            >
                                <option value="HTML">HTML</option>
                                <option value="Markdown">Markdown</option>
                                <option value="MarkdownV2">MarkdownV2</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="settings.disable_notification"
                                class="w-4 h-4 rounded border-border"
                            />
                            <span class="text-sm font-medium text-foreground">Отправлять без звука</span>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="settings.disable_web_page_preview"
                                class="w-4 h-4 rounded border-border"
                            />
                            <span class="text-sm font-medium text-foreground">Отключить превью ссылок</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 border-t border-border pt-6">
                    <button
                        @click="saveSettings"
                        :disabled="saving"
                        class="px-6 py-2 bg-accent text-accent-foreground rounded hover:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                    >
                        {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                    </button>
                </div>
            </div>

            <!-- Admin Requests -->
            <div class="bg-card rounded-lg border border-border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-foreground">Заявки на администрирование</h2>
                    <button
                        @click="fetchAdminRequests"
                        class="px-4 py-2 text-sm border border-border rounded hover:bg-accent/10"
                    >
                        Обновить
                    </button>
                </div>

                <div v-if="loadingRequests" class="text-center py-4 text-muted-foreground">
                    Загрузка заявок...
                </div>

                <div v-else-if="adminRequests.length === 0" class="text-center py-4 text-muted-foreground">
                    Нет заявок на рассмотрении
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="request in adminRequests"
                        :key="request.id"
                        class="p-4 border border-border rounded-lg"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="font-semibold text-foreground">
                                        {{ request.telegram_first_name }} {{ request.telegram_last_name || '' }}
                                    </h3>
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded',
                                            request.status === 'pending' ? 'bg-yellow-500/20 text-yellow-600' :
                                            request.status === 'approved' ? 'bg-green-500/20 text-green-600' :
                                            'bg-red-500/20 text-red-600'
                                        ]"
                                    >
                                        {{ request.status === 'pending' ? 'На рассмотрении' :
                                           request.status === 'approved' ? 'Одобрена' : 'Отклонена' }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    <strong>Username:</strong> @{{ request.telegram_username || 'не указан' }}<br>
                                    <strong>Telegram ID:</strong> {{ request.telegram_user_id }}<br>
                                    <strong>Chat ID:</strong> {{ request.chat_id }}<br>
                                    <strong>Дата:</strong> {{ formatDate(request.created_at) }}
                                </p>
                                <p v-if="request.rejection_reason" class="text-sm text-red-600 mt-2">
                                    <strong>Причина отклонения:</strong> {{ request.rejection_reason }}
                                </p>
                            </div>
                            <div v-if="request.status === 'pending'" class="flex gap-2">
                                <button
                                    @click="approveRequest(request.id)"
                                    :disabled="processingRequest"
                                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50 text-sm"
                                >
                                    Одобрить
                                </button>
                                <button
                                    @click="showRejectModal(request)"
                                    :disabled="processingRequest"
                                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50 text-sm"
                                >
                                    Отклонить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Telegram Admins -->
            <div class="bg-card rounded-lg border border-border p-6">
                <h2 class="text-xl font-semibold text-foreground mb-4">Администраторы с Telegram</h2>

                <div v-if="loadingAdmins" class="text-center py-4 text-muted-foreground">
                    Загрузка...
                </div>

                <div v-else-if="telegramAdmins.length === 0" class="text-center py-4 text-muted-foreground">
                    Нет администраторов с подключенным Telegram
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="admin in telegramAdmins"
                        :key="admin.id"
                        class="p-3 border border-border rounded-lg"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-foreground">{{ admin.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    Email: {{ admin.email }}<br>
                                    Telegram Chat ID: {{ admin.telegram_chat_id }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div
            v-if="showRejectReasonModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
            @click.self="showRejectReasonModal = false"
        >
            <div class="bg-card rounded-lg border border-border shadow-2xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">Отклонить заявку</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Причина отклонения (необязательно)
                        </label>
                        <textarea
                            v-model="rejectReason"
                            rows="3"
                            class="w-full px-3 py-2 border border-border rounded bg-background text-sm"
                            placeholder="Укажите причину отклонения заявки..."
                        ></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button
                            @click="showRejectReasonModal = false"
                            class="px-4 py-2 border border-border rounded hover:bg-accent/10 text-sm"
                        >
                            Отмена
                        </button>
                        <button
                            @click="rejectRequest"
                            :disabled="processingRequest"
                            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50 text-sm"
                        >
                            Отклонить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'TelegramSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const loadingRequests = ref(false);
        const loadingAdmins = ref(false);
        const processingRequest = ref(false);
        const error = ref(null);
        const settings = ref(null);
        const botInfo = ref(null);
        const webhookInfo = ref(null);
        const adminRequests = ref([]);
        const telegramAdmins = ref([]);
        const showRejectReasonModal = ref(false);
        const rejectReason = ref('');
        const currentRejectRequestId = ref(null);

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                const response = await axios.get('/api/v1/telegram-settings');
                settings.value = response.data.data.settings;
                botInfo.value = response.data.data.bot_info;
                
                // Загружаем информацию о webhook
                if (settings.value.bot_token) {
                    try {
                        const webhookResponse = await axios.get('/api/v1/telegram-settings/webhook-info');
                        if (webhookResponse.data.success) {
                            webhookInfo.value = webhookResponse.data.data;
                        }
                    } catch (e) {
                        // Игнорируем ошибки получения webhook info
                    }
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching Telegram settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const fetchAdminRequests = async () => {
            loadingRequests.value = true;
            try {
                const response = await axios.get('/api/v1/telegram-admin-requests', {
                    params: { status: 'pending' }
                });
                // Laravel paginate возвращает данные напрямую в response.data
                adminRequests.value = response.data.data || [];
            } catch (err) {
                console.error('Error fetching admin requests:', err);
            } finally {
                loadingRequests.value = false;
            }
        };

        const fetchTelegramAdmins = async () => {
            loadingAdmins.value = true;
            try {
                const response = await axios.get('/api/v1/users');
                // UserController возвращает пагинированный ответ, данные в response.data.data
                const users = response.data.data || [];
                telegramAdmins.value = users.filter(user => 
                    user.telegram_chat_id && user.roles?.some(role => ['admin', 'manager'].includes(role.slug))
                );
            } catch (err) {
                console.error('Error fetching telegram admins:', err);
                telegramAdmins.value = [];
            } finally {
                loadingAdmins.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            
            try {
                const response = await axios.put('/api/v1/telegram-settings', settings.value);
                
                if (response.data.data.bot_info) {
                    botInfo.value = response.data.data.bot_info;
                }
                
                // Обновляем информацию о webhook
                if (settings.value.bot_token) {
                    try {
                        const webhookResponse = await axios.get('/api/v1/telegram-settings/webhook-info');
                        if (webhookResponse.data.success) {
                            webhookInfo.value = webhookResponse.data.data;
                        }
                    } catch (e) {
                        // Игнорируем
                    }
                }
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки успешно сохранены. Webhook зарегистрирован автоматически.',
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

        const approveRequest = async (requestId) => {
            processingRequest.value = true;
            try {
                const response = await axios.post(`/api/v1/telegram-admin-requests/${requestId}/approve`);
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Заявка одобрена. Пользователь получил права администратора.',
                    timer: 2000,
                    showConfirmButton: false,
                });
                
                await fetchAdminRequests();
                await fetchTelegramAdmins();
            } catch (err) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: err.response?.data?.message || 'Не удалось одобрить заявку',
                });
            } finally {
                processingRequest.value = false;
            }
        };

        const showRejectModal = (request) => {
            currentRejectRequestId.value = request.id;
            rejectReason.value = '';
            showRejectReasonModal.value = true;
        };

        const rejectRequest = async () => {
            if (!currentRejectRequestId.value) return;
            
            processingRequest.value = true;
            try {
                const response = await axios.post(`/api/v1/telegram-admin-requests/${currentRejectRequestId.value}/reject`, {
                    reason: rejectReason.value
                });
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Заявка отклонена.',
                    timer: 2000,
                    showConfirmButton: false,
                });
                
                showRejectReasonModal.value = false;
                currentRejectRequestId.value = null;
                await fetchAdminRequests();
            } catch (err) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: err.response?.data?.message || 'Не удалось отклонить заявку',
                });
            } finally {
                processingRequest.value = false;
            }
        };

        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString('ru-RU');
        };

        onMounted(() => {
            fetchSettings();
            fetchAdminRequests();
            fetchTelegramAdmins();
        });

        return {
            loading,
            saving,
            loadingRequests,
            loadingAdmins,
            processingRequest,
            error,
            settings,
            botInfo,
            webhookInfo,
            adminRequests,
            telegramAdmins,
            showRejectReasonModal,
            rejectReason,
            saveSettings,
            fetchAdminRequests,
            approveRequest,
            showRejectModal,
            rejectRequest,
            formatDate,
        };
    },
};
</script>
