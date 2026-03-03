<template>
    <div class="product-requests-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Заявки</h1>
                <p class="text-muted-foreground mt-1">Управление заявками на продукты</p>
            </div>
            <div v-if="stats" class="flex items-center gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-foreground">{{ stats.new }}</div>
                    <div class="text-xs text-muted-foreground">Новые</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-foreground">{{ stats.in_progress }}</div>
                    <div class="text-xs text-muted-foreground">В обработке</div>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-card rounded-lg border border-border p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Поиск -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground">🔍</span>
                        <input
                            type="text"
                            v-model="filters.search"
                            @input="handleSearch"
                            placeholder="Поиск по имени, телефону, email, комментарию..."
                            class="w-full h-10 pl-9 pr-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                </div>

                <!-- Фильтр по статусу -->
                <div>
                    <select
                        v-model="filters.status"
                        @change="handleFilterChange"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    >
                        <option :value="null">Все статусы</option>
                        <option value="new">Новая</option>
                        <option value="in_progress">В обработке</option>
                        <option value="completed">Завершена</option>
                        <option value="cancelled">Отменена</option>
                        <option value="rejected">Отклонена</option>
                    </select>
                </div>

                <!-- Фильтр по назначенному -->
                <div>
                    <select
                        v-model="filters.assigned_to"
                        @change="handleFilterChange"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    >
                        <option :value="null">Все исполнители</option>
                        <option :value="authUser?.id">Мои заявки</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
                <p class="text-muted-foreground">Загрузка заявок...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Requests List -->
        <div v-if="!loading && !error">
            <div v-if="requests.length === 0" class="text-center py-12 text-muted-foreground">
                <p>Заявки не найдены</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="request in requests"
                    :key="request.id"
                    @click="viewRequest(request)"
                    class="bg-card border border-border rounded-lg p-4 hover:shadow-md transition-all cursor-pointer"
                    :class="{
                        'border-accent/50 bg-accent/5': request.status === 'new',
                        'border-blue-500/50 bg-blue-500/5': request.status === 'in_progress',
                        'border-green-500/50 bg-green-500/5': request.status === 'completed',
                    }"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-semibold text-foreground">#{{ request.id }}</h3>
                                <span
                                    class="px-2 py-1 text-xs rounded font-medium"
                                    :class="getStatusClass(request.status)"
                                >
                                    {{ getStatusLabel(request.status) }}
                                </span>
                                <span class="text-sm text-muted-foreground">
                                    {{ formatDate(request.created_at) }}
                                </span>
                            </div>
                            <div class="space-y-1 text-sm">
                                <p class="text-foreground">
                                    <span class="font-medium">Клиент:</span> {{ request.name }}
                                </p>
                                <p v-if="request.phone" class="text-muted-foreground">
                                    <span class="font-medium">Телефон:</span> {{ request.phone }}
                                </p>
                                <p v-if="request.email" class="text-muted-foreground">
                                    <span class="font-medium">Email:</span> {{ request.email }}
                                </p>
                                <p v-if="request.product" class="text-muted-foreground">
                                    <span class="font-medium">Продукт:</span> {{ request.product.name }}
                                </p>
                                <p v-if="request.assigned_user" class="text-muted-foreground">
                                    <span class="font-medium">Исполнитель:</span> {{ request.assigned_user.name }}
                                </p>
                            </div>
                            <p v-if="request.comment" class="text-sm text-muted-foreground mt-2 line-clamp-2">
                                {{ request.comment }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span
                                v-if="request.status === 'new'"
                                class="h-2 w-2 bg-accent rounded-full"
                                title="Новая заявка"
                            ></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Показано {{ pagination.from }} - {{ pagination.to }} из {{ pagination.total }}
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="handlePageChange(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-2 rounded-md border border-border bg-background hover:bg-accent/10 disabled:opacity-50 disabled:cursor-not-allowed text-sm transition-colors"
                    >
                        ← Назад
                    </button>
                    <div class="flex items-center gap-1">
                        <button
                            v-for="pageNum in getPageNumbers"
                            :key="pageNum"
                            @click="handlePageChange(pageNum)"
                            :class="[
                                'px-3 py-2 rounded-md text-sm transition-colors',
                                pageNum === pagination.current_page
                                    ? 'bg-accent text-accent-foreground border-accent font-semibold'
                                    : 'border-border bg-background hover:bg-accent/10'
                            ]"
                        >
                            {{ pageNum }}
                        </button>
                    </div>
                    <button
                        @click="handlePageChange(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-2 rounded-md border border-border bg-background hover:bg-accent/10 disabled:opacity-50 disabled:cursor-not-allowed text-sm transition-colors"
                    >
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

        <!-- Request Detail Modal -->
        <div
            v-if="selectedRequest"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto"
            @click.self="closeRequestModal"
        >
            <div class="bg-card rounded-lg border border-border shadow-2xl max-w-4xl w-full my-8 max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-border">
                    <div>
                        <h2 class="text-2xl font-semibold text-foreground">Заявка #{{ selectedRequest.id }}</h2>
                        <div class="flex items-center gap-2 mt-2">
                            <span
                                class="px-2 py-1 text-xs rounded font-medium"
                                :class="getStatusClass(selectedRequest.status)"
                            >
                                {{ getStatusLabel(selectedRequest.status) }}
                            </span>
                            <span class="text-xs text-muted-foreground">
                                Создана: {{ formatDate(selectedRequest.created_at) }}
                            </span>
                        </div>
                    </div>
                    <button
                        @click="closeRequestModal"
                        class="p-2 hover:bg-muted/10 rounded-lg transition-colors"
                        title="Закрыть"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6 overflow-y-auto flex-1">
                    <!-- Request Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Клиент</label>
                            <p class="text-foreground mt-1">{{ selectedRequest.name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Телефон</label>
                            <p class="text-foreground mt-1">{{ selectedRequest.phone || 'Не указан' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Email</label>
                            <p class="text-foreground mt-1">{{ selectedRequest.email || 'Не указан' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Продукт</label>
                            <p class="text-foreground mt-1">{{ selectedRequest.product?.name || 'Не указан (общая заявка)' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Исполнитель</label>
                            <select
                                v-model="editForm.assigned_to"
                                @change="updateRequest"
                                class="w-full mt-1 px-3 py-2 border border-border rounded bg-background text-sm"
                            >
                                <option :value="null">Не назначен</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Comment -->
                    <div v-if="selectedRequest.comment">
                        <label class="text-sm font-medium text-muted-foreground">Комментарий клиента</label>
                        <p class="text-foreground mt-1 whitespace-pre-wrap">{{ selectedRequest.comment }}</p>
                    </div>

                    <!-- Services -->
                    <div v-if="selectedRequest.services && selectedRequest.services.length > 0">
                        <label class="text-sm font-medium text-muted-foreground mb-2 block">Выбранные услуги</label>
                        <div class="space-y-1">
                            <div
                                v-for="service in selectedRequest.services"
                                :key="service.id || service"
                                class="px-3 py-2 bg-muted/50 rounded text-sm"
                            >
                                {{ typeof service === 'object' ? service.name : service }}
                            </div>
                        </div>
                    </div>

                    <!-- Status and Notes -->
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground mb-2 block">Статус</label>
                            <select
                                v-model="editForm.status"
                                @change="updateRequest"
                                class="w-full px-3 py-2 border border-border rounded bg-background text-sm"
                            >
                                <option value="new">Новая</option>
                                <option value="in_progress">В обработке</option>
                                <option value="completed">Завершена</option>
                                <option value="cancelled">Отменена</option>
                                <option value="rejected">Отклонена</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-muted-foreground mb-2 block">Заметки</label>
                            <textarea
                                v-model="editForm.notes"
                                @blur="updateRequest"
                                rows="4"
                                placeholder="Введите заметки по заявке..."
                                class="w-full px-3 py-2 border border-border rounded bg-background text-sm resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <!-- History -->
                    <div v-if="selectedRequest.history && selectedRequest.history.length > 0">
                        <label class="text-sm font-medium text-muted-foreground mb-3 block">История обработки</label>
                        <div class="space-y-2">
                            <div
                                v-for="historyItem in selectedRequest.history"
                                :key="historyItem.id"
                                class="p-3 bg-muted/30 rounded border-l-2 border-border"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-foreground">{{ historyItem.action_name || historyItem.action }}</span>
                                    <span class="text-xs text-muted-foreground">{{ formatDate(historyItem.created_at) }}</span>
                                </div>
                                <div v-if="historyItem.user" class="text-xs text-muted-foreground mb-1">
                                    Пользователь: {{ historyItem.user.name }}
                                </div>
                                <div v-if="historyItem.comment" class="text-sm text-foreground">
                                    {{ historyItem.comment }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-6 border-t border-border flex justify-end gap-3">
                    <button
                        @click="closeRequestModal"
                        class="px-4 py-2 border border-border rounded hover:bg-accent/10 text-sm"
                    >
                        Закрыть
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { apiGet, apiPut } from '../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'ProductRequests',
    setup() {
        const loading = ref(false);
        const error = ref(null);
        const requests = ref([]);
        const pagination = ref(null);
        const selectedRequest = ref(null);
        const users = ref([]);
        const stats = ref(null);
        const authUser = ref(null);

        const filters = ref({
            search: '',
            status: null,
            assigned_to: null,
        });

        const editForm = ref({
            status: null,
            assigned_to: null,
            notes: null,
        });

        const fetchRequests = async (page = 1) => {
            loading.value = true;
            error.value = null;
            try {
                const params = new URLSearchParams({
                    page: page.toString(),
                    per_page: '20',
                });

                if (filters.value.search) {
                    params.append('search', filters.value.search);
                }
                if (filters.value.status) {
                    params.append('status', filters.value.status);
                }
                if (filters.value.assigned_to) {
                    params.append('assigned_to', filters.value.assigned_to);
                }

                const response = await apiGet(`/product-requests?${params.toString()}`);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки заявок');
                }

                const data = await response.json();
                requests.value = data.data || [];
                pagination.value = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    from: data.from,
                    to: data.to,
                    total: data.total,
                };
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки заявок';
                console.error('Error fetching requests:', err);
            } finally {
                loading.value = false;
            }
        };

        const fetchStats = async () => {
            try {
                const response = await apiGet('/product-requests/stats');
                if (response.ok) {
                    const data = await response.json();
                    stats.value = data.data;
                }
            } catch (err) {
                console.error('Error fetching stats:', err);
            }
        };

        const fetchUsers = async () => {
            try {
                const response = await apiGet('/users');
                if (response.ok) {
                    const data = await response.json();
                    users.value = data.data || [];
                }
            } catch (err) {
                console.error('Error fetching users:', err);
            }
        };

        const fetchAuthUser = async () => {
            try {
                // Роут /auth/user находится вне префикса v1, поэтому используем прямой путь
                const token = localStorage.getItem('token');
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                };
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }
                const response = await fetch('/api/auth/user', {
                    method: 'GET',
                    headers: headers,
                });
                if (response.ok) {
                    const data = await response.json();
                    // Контроллер возвращает { user: {...} }, как в app.js
                    authUser.value = data.user || data;
                }
            } catch (err) {
                console.error('Error fetching auth user:', err);
            }
        };

        const viewRequest = async (request) => {
            try {
                const response = await apiGet(`/product-requests/${request.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки заявки');
                }
                const data = await response.json();
                selectedRequest.value = data.data;
                editForm.value = {
                    status: data.data.status,
                    assigned_to: data.data.assigned_to,
                    notes: data.data.notes || '',
                };
            } catch (err) {
                Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Не удалось загрузить заявку',
                    icon: 'error',
                });
            }
        };

        const updateRequest = async () => {
            if (!selectedRequest.value) return;

            try {
                const response = await apiPut(`/product-requests/${selectedRequest.value.id}`, editForm.value);
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Ошибка обновления заявки');
                }

                const data = await response.json();
                selectedRequest.value = data.data;
                
                // Обновляем список
                await fetchRequests(pagination.value?.current_page || 1);
                await fetchStats();

                Swal.fire({
                    title: 'Успешно',
                    text: 'Заявка обновлена',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            } catch (err) {
                Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Не удалось обновить заявку',
                    icon: 'error',
                });
            }
        };

        const closeRequestModal = () => {
            selectedRequest.value = null;
            editForm.value = {
                status: null,
                assigned_to: null,
                notes: null,
            };
        };

        const handleSearch = () => {
            // Debounce поиска
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                fetchRequests(1);
            }, 500);
        };

        const handleFilterChange = () => {
            fetchRequests(1);
        };

        const handlePageChange = (page) => {
            fetchRequests(page);
        };

        const getStatusClass = (status) => {
            const classes = {
                new: 'bg-accent/20 text-accent border-accent/50',
                in_progress: 'bg-blue-500/20 text-blue-600 border-blue-500/50',
                completed: 'bg-green-500/20 text-green-600 border-green-500/50',
                cancelled: 'bg-gray-500/20 text-gray-600 border-gray-500/50',
                rejected: 'bg-red-500/20 text-red-600 border-red-500/50',
            };
            return classes[status] || classes.new;
        };

        const getStatusLabel = (status) => {
            const labels = {
                new: 'Новая',
                in_progress: 'В обработке',
                completed: 'Завершена',
                cancelled: 'Отменена',
                rejected: 'Отклонена',
            };
            return labels[status] || status;
        };

        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString('ru-RU', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        };

        const getPageNumbers = computed(() => {
            if (!pagination.value) return [];
            const pages = [];
            const current = pagination.value.current_page;
            const last = pagination.value.last_page;
            
            for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
                pages.push(i);
            }
            return pages;
        });

        onMounted(async () => {
            await Promise.all([
                fetchRequests(),
                fetchStats(),
                fetchUsers(),
                fetchAuthUser(),
            ]);
        });

        return {
            loading,
            error,
            requests,
            pagination,
            selectedRequest,
            users,
            stats,
            authUser,
            filters,
            editForm,
            viewRequest,
            updateRequest,
            closeRequestModal,
            handleSearch,
            handleFilterChange,
            handlePageChange,
            getStatusClass,
            getStatusLabel,
            formatDate,
            getPageNumbers,
        };
    },
};
</script>

