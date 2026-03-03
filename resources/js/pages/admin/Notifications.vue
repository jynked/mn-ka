<template>
    <div class="notifications-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Уведомления</h1>
                <p class="text-muted-foreground mt-1">Просмотр всех уведомлений</p>
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
                            placeholder="Поиск по заголовку и сообщению..."
                            class="w-full h-10 pl-9 pr-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                </div>

                <!-- Фильтр по статусу -->
                <div>
                    <select
                        v-model="filters.read"
                        @change="handleFilterChange"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    >
                        <option :value="null">Все уведомления</option>
                        <option :value="false">Непрочитанные</option>
                        <option :value="true">Прочитанные</option>
                    </select>
                </div>

                <!-- Фильтр по типу -->
                <div>
                    <select
                        v-model="filters.type"
                        @change="handleFilterChange"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    >
                        <option value="">Все типы</option>
                        <option value="info">Информация</option>
                        <option value="success">Успех</option>
                        <option value="warning">Предупреждение</option>
                        <option value="error">Ошибка</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка уведомлений...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Notifications List -->
        <div v-if="!loading && notifications.length > 0" class="space-y-3">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                :class="[
                    'bg-card rounded-lg border p-4 transition-colors',
                    !notification.read ? 'border-accent bg-accent/5' : 'border-border'
                ]"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="font-semibold text-foreground">{{ notification.title }}</h3>
                            <span
                                v-if="!notification.read"
                                class="h-2 w-2 bg-accent rounded-full flex-shrink-0"
                                title="Непрочитанное"
                            ></span>
                            <span
                                :class="[
                                    'px-2 py-1 text-xs rounded',
                                    getTypeClass(notification.type)
                                ]"
                            >
                                {{ getTypeLabel(notification.type) }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground mb-2">{{ notification.message }}</p>
                        <div class="flex items-center gap-4 text-xs text-muted-foreground">
                            <span>{{ formatDate(notification.created_at) }}</span>
                            <span v-if="notification.read_at">
                                Прочитано: {{ formatDate(notification.read_at) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button
                            @click="viewNotification(notification)"
                            class="px-3 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors"
                            title="Просмотреть детали"
                        >
                            Просмотр
                        </button>
                        <button
                            v-if="!notification.read"
                            @click="markAsRead(notification.id)"
                            class="px-3 py-1 text-xs bg-green-500 hover:bg-green-600 text-white rounded transition-colors"
                            title="Отметить как прочитанное"
                        >
                            Прочитано
                        </button>
                        <button
                            @click="deleteNotification(notification.id)"
                            class="px-3 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded transition-colors"
                            title="Удалить"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && notifications.length === 0" class="bg-card rounded-lg border border-border p-12 text-center">
            <p class="text-muted-foreground">Уведомления не найдены</p>
        </div>

        <!-- Пагинация -->
        <div v-if="pagination && pagination.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between pt-6 border-t border-border gap-4">
            <div class="text-sm text-muted-foreground">
                <span>
                    Показано {{ pagination.from }}-{{ pagination.to }} из {{ pagination.total }}
                </span>
            </div>
            <div class="flex gap-2 items-center">
                <button
                    @click="handlePageChange(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-3 py-2 rounded-md border border-border bg-background hover:bg-accent/10 disabled:opacity-50 disabled:cursor-not-allowed text-sm transition-colors"
                >
                    ← Назад
                </button>
                
                <!-- Номера страниц -->
                <div class="flex gap-1">
                    <button
                        v-for="pageNum in getPageNumbers(pagination.current_page, pagination.last_page)"
                        :key="pageNum"
                        @click="handlePageChange(pageNum)"
                        :class="[
                            'px-3 py-2 rounded-md border text-sm transition-colors min-w-[40px]',
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

    <!-- Модальное окно для просмотра деталей уведомления -->
    <div
        v-if="selectedNotification"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        @click.self="closeNotificationModal"
    >
        <div class="bg-card rounded-lg border border-border shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Заголовок модального окна -->
            <div class="flex items-center justify-between p-6 border-b border-border">
                <div>
                    <h2 class="text-2xl font-semibold text-foreground">{{ selectedNotification.title }}</h2>
                    <div class="flex items-center gap-2 mt-2">
                        <span
                            :class="[
                                'px-2 py-1 text-xs rounded',
                                getTypeClass(selectedNotification.type)
                            ]"
                        >
                            {{ getTypeLabel(selectedNotification.type) }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{ formatDate(selectedNotification.created_at) }}
                        </span>
                    </div>
                </div>
                <button
                    @click="closeNotificationModal"
                    class="p-2 hover:bg-muted/10 rounded-lg transition-colors"
                    title="Закрыть"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Содержимое модального окна -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="space-y-6">
                    <!-- Основное сообщение -->
                    <div>
                        <h3 class="text-sm font-medium text-muted-foreground mb-2">Сообщение</h3>
                        <p class="text-foreground whitespace-pre-wrap">{{ selectedNotification.message }}</p>
                    </div>

                    <!-- Данные уведомления (если есть) -->
                    <div v-if="selectedNotification.data" class="space-y-4">
                        <!-- Данные о квизе -->
                        <div v-if="selectedNotification.data.quiz_id" class="border-t border-border pt-4">
                            <h3 class="text-sm font-medium text-muted-foreground mb-4">Детали квиза</h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-muted-foreground">Квиз:</span>
                                    <span class="ml-2 text-foreground">{{ selectedNotification.data.quiz_title }}</span>
                                </div>
                                
                                <div v-if="selectedNotification.data.contact" class="mt-4">
                                    <h4 class="text-sm font-semibold text-foreground mb-2">Контактная информация:</h4>
                                    <div class="bg-muted/50 rounded-lg p-4 space-y-2">
                                        <div>
                                            <span class="text-sm text-muted-foreground">Имя:</span>
                                            <span class="ml-2 text-foreground">{{ selectedNotification.data.contact.name }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm text-muted-foreground">Email:</span>
                                            <span class="ml-2 text-foreground">{{ selectedNotification.data.contact.email }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm text-muted-foreground">Телефон:</span>
                                            <span class="ml-2 text-foreground">{{ selectedNotification.data.contact.phone }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="selectedNotification.data.completed_at" class="mt-4">
                                    <span class="text-sm text-muted-foreground">Завершено:</span>
                                    <span class="ml-2 text-foreground">{{ formatDate(selectedNotification.data.completed_at) }}</span>
                                </div>

                                <div v-if="selectedNotification.data.answers && selectedNotification.data.answers.length > 0" class="mt-4">
                                    <p class="text-sm text-muted-foreground mb-3">
                                        Ответов на вопросы: {{ selectedNotification.data.answers.length }}
                                    </p>
                                </div>

                                <div class="mt-6">
                                    <router-link
                                        :to="`/admin/notifications/quiz/${selectedNotification.data.quiz_id}?notification_id=${selectedNotification.id}`"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Просмотреть подробности прохождения квиза
                                    </router-link>
                                </div>
                            </div>
                        </div>

                        <!-- Другие данные (JSON) -->
                        <div v-else class="border-t border-border pt-4">
                            <h3 class="text-sm font-medium text-muted-foreground mb-2">Дополнительные данные</h3>
                            <pre class="bg-muted/50 rounded-lg p-4 text-xs overflow-auto">{{ JSON.stringify(selectedNotification.data, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Футер модального окна -->
            <div class="flex items-center justify-between p-6 border-t border-border bg-muted/30">
                <div class="text-sm text-muted-foreground">
                    <span v-if="selectedNotification.read_at">
                        Прочитано: {{ formatDate(selectedNotification.read_at) }}
                    </span>
                    <span v-else>
                        Не прочитано
                    </span>
                </div>
                <div class="flex gap-2">
                    <button
                        v-if="!selectedNotification.read"
                        @click="markAsReadFromModal(selectedNotification.id)"
                        class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors"
                    >
                        Отметить как прочитанное
                    </button>
                    <button
                        @click="closeNotificationModal"
                        class="px-4 py-2 text-sm border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors"
                    >
                        Закрыть
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { apiGet, apiPost, apiDelete } from '../../utils/api'
import Swal from 'sweetalert2'

export default {
    name: 'Notifications',
    setup() {
        const router = useRouter()
        const loading = ref(false)
        const error = ref(null)
        const notifications = ref([])
        const pagination = ref(null)
        const filters = ref({
            search: '',
            read: null,
            type: ''
        })
        const currentPage = ref(1)
        const perPage = ref(20)
        const searchTimeout = ref(null)
        const selectedNotification = ref(null)

        const fetchNotifications = async (page = 1) => {
            loading.value = true
            error.value = null
            try {
                const params = new URLSearchParams()
                params.append('page', page)
                params.append('per_page', perPage.value)
                
                if (filters.value.search) {
                    params.append('search', filters.value.search)
                }
                if (filters.value.read !== null) {
                    params.append('read', filters.value.read ? '1' : '0')
                }
                if (filters.value.type) {
                    params.append('type', filters.value.type)
                }

                // Используем прямой путь к API, так как notifications не в v1
                const token = localStorage.getItem('token')
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`
                }
                
                const response = await fetch(`/api/notifications/all?${params.toString()}`, {
                    method: 'GET',
                    headers,
                })
                
                if (!response.ok) {
                    throw new Error('Ошибка загрузки уведомлений')
                }

                const data = await response.json()
                
                notifications.value = data.data || []
                pagination.value = {
                    current_page: data.current_page || 1,
                    last_page: data.last_page || 1,
                    per_page: data.per_page || perPage.value,
                    total: data.total || 0,
                    from: data.from || 0,
                    to: data.to || 0
                }
                currentPage.value = data.current_page || 1
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки уведомлений'
            } finally {
                loading.value = false
            }
        }

        const handleSearch = () => {
            if (searchTimeout.value) {
                clearTimeout(searchTimeout.value)
            }
            searchTimeout.value = setTimeout(() => {
                currentPage.value = 1
                fetchNotifications(1)
            }, 500)
        }

        const handleFilterChange = () => {
            currentPage.value = 1
            fetchNotifications(1)
        }

        const handlePageChange = (page) => {
            fetchNotifications(page)
        }

        const markAsRead = async (id) => {
            try {
                const token = localStorage.getItem('token')
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`
                }
                
                const response = await fetch(`/api/notifications/${id}/read`, {
                    method: 'POST',
                    headers,
                })
                
                if (response.ok) {
                    await fetchNotifications(currentPage.value)
                }
            } catch (err) {
                console.error('Error marking notification as read:', err)
            }
        }

        const deleteNotification = async (id) => {
            const result = await Swal.fire({
                title: 'Удалить уведомление?',
                text: 'Вы уверены, что хотите удалить это уведомление?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
            })

            if (!result.isConfirmed) return

            try {
                const token = localStorage.getItem('token')
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`
                }
                
                const response = await fetch(`/api/notifications/${id}`, {
                    method: 'DELETE',
                    headers,
                })
                
                if (!response.ok) {
                    throw new Error('Ошибка удаления уведомления')
                }

                await Swal.fire({
                    title: 'Уведомление удалено',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                })

                await fetchNotifications(currentPage.value)
            } catch (err) {
                Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка удаления уведомления',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                })
            }
        }

        const formatDate = (dateString) => {
            if (!dateString) return ''
            const date = new Date(dateString)
            return date.toLocaleString('ru-RU', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        }

        const getTypeLabel = (type) => {
            const labels = {
                'info': 'Информация',
                'success': 'Успех',
                'warning': 'Предупреждение',
                'error': 'Ошибка'
            }
            return labels[type] || type
        }

        const getTypeClass = (type) => {
            const classes = {
                'info': 'bg-blue-500/10 text-blue-500',
                'success': 'bg-green-500/10 text-green-500',
                'warning': 'bg-yellow-500/10 text-yellow-500',
                'error': 'bg-red-500/10 text-red-500'
            }
            return classes[type] || 'bg-muted text-muted-foreground'
        }

        // Просмотр детальной информации об уведомлении
        const viewNotification = async (notification) => {
            // Всегда загружаем полные данные уведомления для проверки типа
            try {
                const token = localStorage.getItem('token')
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`
                }
                
                const response = await fetch(`/api/notifications/${notification.id}`, {
                    method: 'GET',
                    headers,
                })
                
                if (!response.ok) {
                    throw new Error('Ошибка загрузки уведомления')
                }

                const result = await response.json()
                const fullNotification = result.data

                // Если это уведомление о квизе, сразу переходим на страницу с деталями
                if (fullNotification.data && fullNotification.data.quiz_id) {
                    // Отмечаем как прочитанное перед переходом
                    if (!fullNotification.read) {
                        await markAsRead(fullNotification.id)
                    }
                    // Переходим на страницу с деталями квиза
                    router.push(`/admin/notifications/quiz/${fullNotification.data.quiz_id}?notification_id=${fullNotification.id}`)
                    return
                }

                // Для остальных уведомлений открываем модальное окно
                selectedNotification.value = fullNotification
                
                // Отмечаем как прочитанное при открытии
                if (!fullNotification.read) {
                    await markAsRead(fullNotification.id)
                }
            } catch (err) {
                console.error('Error loading notification:', err)
                // Если не удалось загрузить детали, показываем то что есть в модальном окне
                selectedNotification.value = notification
            }
        }


        const closeNotificationModal = () => {
            selectedNotification.value = null
        }

        const markAsReadFromModal = async (id) => {
            await markAsRead(id)
            if (selectedNotification.value) {
                selectedNotification.value.read = true
                selectedNotification.value.read_at = new Date().toISOString()
            }
        }

        // Форматирование ответа для отображения
        const formatAnswer = (answer) => {
            if (answer === null || answer === undefined) {
                return 'Нет ответа'
            }

            // Если ответ - объект с полной информацией
            if (typeof answer === 'object') {
                // Новый формат с answer.value и answer.text
                if (answer.text) {
                    return answer.text
                }
                if (answer.value) {
                    return answer.value
                }
                // Старый формат - ищем текст в объекте
                if (answer.name) return answer.name
                if (answer.title) return answer.title
                if (Array.isArray(answer)) {
                    return answer.map(a => typeof a === 'object' ? (a.name || a.title || JSON.stringify(a)) : a).join(', ')
                }
                return JSON.stringify(answer, null, 2)
            }
            
            return String(answer)
        }

        const getPageNumbers = (currentPage, lastPage) => {
            const pages = []
            const maxVisible = 5
            
            if (lastPage <= maxVisible) {
                for (let i = 1; i <= lastPage; i++) {
                    pages.push(i)
                }
            } else {
                if (currentPage <= 3) {
                    for (let i = 1; i <= 5; i++) {
                        pages.push(i)
                    }
                } else if (currentPage >= lastPage - 2) {
                    for (let i = lastPage - 4; i <= lastPage; i++) {
                        pages.push(i)
                    }
                } else {
                    for (let i = currentPage - 2; i <= currentPage + 2; i++) {
                        pages.push(i)
                    }
                }
            }
            
            return pages
        }

        onMounted(() => {
            fetchNotifications(1)
        })

        return {
            loading,
            error,
            notifications,
            pagination,
            filters,
            handleSearch,
            handleFilterChange,
            handlePageChange,
            markAsRead,
            deleteNotification,
            formatDate,
            getTypeLabel,
            getTypeClass,
            getPageNumbers,
            selectedNotification,
            viewNotification,
            closeNotificationModal,
            markAsReadFromModal,
            formatAnswer
        }
    }
}
</script>

