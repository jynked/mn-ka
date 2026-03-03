<template>
    <div class="relative">
        <button
            @click="toggleDropdown"
            class="relative h-11 w-11 flex items-center justify-center rounded-md hover:bg-accent/10 transition-colors"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                ></path>
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>
        <div
            v-if="isOpen"
            class="absolute right-0 top-full mt-2 w-80 bg-card border border-border rounded-lg shadow-lg z-40 max-h-96 overflow-hidden flex flex-col"
        >
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="font-semibold text-foreground">Уведомления</h3>
                <router-link
                    to="/admin/notifications"
                    class="text-sm text-blue-500 hover:text-blue-600 underline"
                    @click="isOpen = false"
                >
                    Все уведомления
                </router-link>
            </div>
            <div class="overflow-y-auto flex-1">
                <div v-if="notifications.length === 0" class="p-4 text-center text-muted-foreground">
                    Нет уведомлений
                </div>
                <div v-else class="divide-y divide-border">
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="handleNotificationClick(notification)"
                        :class="[
                            'p-4 hover:bg-accent/10 cursor-pointer transition-colors',
                            !notification.read ? 'bg-accent/5 border-l-2 border-accent' : ''
                        ]"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-semibold text-sm text-foreground">{{ notification.title }}</h4>
                                    <span
                                        v-if="!notification.read"
                                        class="h-2 w-2 bg-accent rounded-full flex-shrink-0"
                                    ></span>
                                </div>
                                <p class="text-sm text-muted-foreground mb-2">{{ notification.message }}</p>
                                <span class="text-xs text-muted-foreground">
                                    {{ notification.created_at_human || formatDate(notification.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
    name: 'NotificationDropdown',
    setup() {
        const store = useStore();
        const router = useRouter();
        const isOpen = ref(false);
        // Фильтруем только непрочитанные уведомления
        const notifications = computed(() => {
            const allNotifications = store.getters.notifications || [];
            return allNotifications.filter(n => !n.read);
        });
        const unreadCount = computed(() => store.getters.unreadNotificationsCount);

        const toggleDropdown = () => {
            isOpen.value = !isOpen.value;
            if (isOpen.value) {
                store.dispatch('fetchNotifications');
            }
        };

        const handleNotificationClick = async (notification) => {
            // Сначала проверяем данные, которые уже есть в уведомлении
            let notificationData = notification.data;
            let quizId = null;

            if (notificationData && notificationData.quiz_id) {
                quizId = notificationData.quiz_id;
            } else {
                // Если данных нет, загружаем полные данные уведомления
                try {
                    const response = await axios.get(`/api/notifications/${notification.id}`);
                    if (response.data && response.data.data) {
                        const fullNotification = response.data.data;
                        notificationData = fullNotification.data;
                        if (notificationData && notificationData.quiz_id) {
                            quizId = notificationData.quiz_id;
                        }
                    }
                } catch (error) {
                    console.error('Error loading notification details:', error);
                }
            }

            // Если это уведомление о квизе, переходим на страницу деталей
            if (quizId) {
                // Отмечаем как прочитанное перед переходом
                if (!notification.read) {
                    await markAsRead(notification.id);
                }
                // Переходим на страницу с деталями квиза
                router.push(`/admin/notifications/quiz/${quizId}?notification_id=${notification.id}`);
                isOpen.value = false; // Закрываем dropdown
                return;
            }

            // Для остальных уведомлений просто отмечаем как прочитанное
            await markAsRead(notification.id);
        };

        const markAsRead = async (id) => {
            // Не помечаем как прочитанное, если уже прочитано
            const notification = notifications.value.find(n => n.id === id);
            if (notification && notification.read) {
                return;
            }

            try {
                const response = await axios.post(`/api/notifications/${id}/read`);
                if (response.data) {
                    // Обновляем уведомления после пометки как прочитанное
                    await store.dispatch('fetchNotifications');
                    // Обновляем счетчик непрочитанных
                    await fetchUnreadCount();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        };

        const fetchUnreadCount = async () => {
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    return; // Если нет токена, не делаем запрос
                }

                const response = await axios.get('/api/notifications/unread-count');
                
                if (response.data && response.data.count !== undefined) {
                    // Обновляем счетчик в store через обновление уведомлений
                    await store.dispatch('fetchNotifications');
                }
            } catch (error) {
                // Тихо игнорируем ошибки 404/401 (маршрут может быть недоступен или пользователь не авторизован)
                if (error.response?.status !== 404 && error.response?.status !== 401) {
                    console.error('Error fetching unread count:', error);
                }
            }
        };

        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Только что';
            if (minutes < 60) return `${minutes} мин. назад`;
            if (hours < 24) return `${hours} ч. назад`;
            if (days < 7) return `${days} дн. назад`;
            return date.toLocaleDateString('ru-RU');
        };

        const handleClickOutside = (event) => {
            if (!event.target.closest('.relative')) {
                isOpen.value = false;
            }
        };

        let unreadCountInterval = null;

        onMounted(() => {
            document.addEventListener('click', handleClickOutside);
            store.dispatch('fetchNotifications');
            
            // Обновляем счетчик непрочитанных каждые 30 секунд
            unreadCountInterval = setInterval(() => {
                fetchUnreadCount();
            }, 30000);
        });

        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside);
            if (unreadCountInterval) {
                clearInterval(unreadCountInterval);
            }
        });

        return {
            isOpen,
            notifications,
            unreadCount,
            toggleDropdown,
            handleNotificationClick,
            markAsRead,
            formatDate,
        };
    },
};
</script>

