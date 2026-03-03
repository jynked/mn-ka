<template>
    <div class="modal-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Модальные окна</h1>
            <p class="text-muted-foreground mt-1">Управление информационными сообщениями в модальных окнах</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-16">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
                <p class="text-muted-foreground">Загрузка настроек...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-destructive flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-destructive">{{ error }}</p>
            </div>
        </div>

        <!-- Settings List -->
        <div v-if="!loading" class="space-y-6">
            <!-- Create buttons for missing modals -->
            <div v-if="!hasModalType('products') || !hasModalType('services')" class="bg-card rounded-lg border border-border p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">Создать модальное окно</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button
                        v-if="!hasModalType('products')"
                        @click="createModal('products')"
                        :disabled="creating === 'products'"
                        class="p-4 border-2 border-dashed border-border rounded-lg hover:border-accent transition-colors text-left"
                    >
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <div>
                                <p class="font-medium text-foreground">Модальное окно продуктов</p>
                                <p class="text-sm text-muted-foreground">Создать настройки для продуктов</p>
                            </div>
                        </div>
                    </button>
                    <button
                        v-if="!hasModalType('services')"
                        @click="createModal('services')"
                        :disabled="creating === 'services'"
                        class="p-4 border-2 border-dashed border-border rounded-lg hover:border-accent transition-colors text-left"
                    >
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <div>
                                <p class="font-medium text-foreground">Модальное окно услуг</p>
                                <p class="text-sm text-muted-foreground">Создать настройки для услуг</p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div v-for="modal in modals" :key="modal.id" class="bg-card rounded-lg border border-border p-6 space-y-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-foreground flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            {{ getModalTitle(modal.type) }}
                            <span 
                                class="ml-2 px-2 py-0.5 text-xs font-medium rounded"
                                :class="modal.is_active ? 'bg-green-500/20 text-green-600' : 'bg-muted text-muted-foreground'"
                            >
                                {{ modal.is_active ? 'Активно' : 'Неактивно' }}
                            </span>
                        </h2>
                        <p class="text-sm text-muted-foreground mt-1">{{ getModalDescription(modal.type) }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Тип модального окна
                        </label>
                        <input
                            :value="modal.type"
                            type="text"
                            disabled
                            class="w-full px-4 py-2.5 bg-muted border border-border rounded-lg text-muted-foreground cursor-not-allowed"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Заголовок модального окна
                        </label>
                        <input
                            v-model="modal.title"
                            type="text"
                            placeholder="Информация"
                            class="w-full px-4 py-2.5 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-all"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Текст информационного сообщения
                        </label>
                        <textarea
                            v-model="modal.content"
                            rows="6"
                            placeholder="Введите текст информационного сообщения..."
                            class="w-full px-4 py-2.5 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-all resize-y"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-muted/30 rounded-lg">
                        <input
                            v-model="modal.is_active"
                            type="checkbox"
                            :id="`is_active_${modal.id}`"
                            class="w-5 h-5 rounded border-border text-accent focus:ring-2 focus:ring-accent cursor-pointer"
                        />
                        <label :for="`is_active_${modal.id}`" class="text-sm font-medium text-foreground cursor-pointer flex-1">
                            Модальное окно активно
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <button
                        @click="saveModal(modal)"
                        :disabled="saving === modal.id"
                        class="px-6 py-2.5 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-all text-sm font-medium flex items-center gap-2 shadow-sm hover:shadow disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="saving === modal.id" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ saving === modal.id ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!modals || modals.length === 0" class="text-center py-12 border-2 border-dashed border-border rounded-lg bg-muted/20">
                <svg class="w-12 h-12 text-muted-foreground mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <p class="text-muted-foreground mb-4">Нет настроек модальных окон</p>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiPut, apiPost } from '../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'ModalSettings',
    setup() {
        const loading = ref(false);
        const saving = ref(null);
        const creating = ref(null);
        const error = ref(null);
        const modals = ref([]);

        const getModalTitle = (type) => {
            const titles = {
                'products': 'Модальное окно продуктов',
                'services': 'Модальное окно услуг',
            };
            return titles[type] || type;
        };

        const getModalDescription = (type) => {
            const descriptions = {
                'products': 'Информационное сообщение, которое отображается в модальном окне на странице продукта',
                'services': 'Информационное сообщение, которое отображается в модальном окне на странице услуги',
            };
            return descriptions[type] || 'Информационное сообщение для модального окна';
        };

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/modal-settings');
                if (response.ok) {
                    const data = await response.json();
                    modals.value = data.data || [];
                } else {
                    throw new Error('Ошибка загрузки настроек');
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки настроек модальных окон';
                console.error('Error fetching modal settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const hasModalType = (type) => {
            return modals.value.some(modal => modal.type === type);
        };

        const createModal = async (type) => {
            creating.value = type;
            error.value = null;
            try {
                const defaultTitles = {
                    'products': 'Информация о продукте',
                    'services': 'Информация об услуге',
                };
                const defaultContents = {
                    'products': 'Здесь будет информация о продукте и услугах.',
                    'services': 'Здесь будет информация об услуге и параметрах.',
                };

                const response = await apiPost('/modal-settings', {
                    type: type,
                    title: defaultTitles[type] || 'Информация',
                    content: defaultContents[type] || 'Информационное сообщение',
                    is_active: true,
                });

                if (response.ok) {
                    await Swal.fire({
                        title: 'Модальное окно создано',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    await fetchSettings();
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Ошибка создания модального окна');
                }
            } catch (err) {
                error.value = err.message || 'Ошибка создания модального окна';
                await Swal.fire({
                    title: 'Ошибка',
                    text: error.value,
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                creating.value = null;
            }
        };

        const saveModal = async (modal) => {
            saving.value = modal.id;
            error.value = null;
            try {
                const response = await apiPut(`/modal-settings/${modal.id}`, {
                    type: modal.type,
                    title: modal.title || null,
                    content: modal.content || null,
                    is_active: modal.is_active,
                });

                if (response.ok) {
                    await Swal.fire({
                        title: 'Настройки сохранены',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Ошибка сохранения настроек');
                }
            } catch (err) {
                error.value = err.message || 'Ошибка сохранения настроек модального окна';
                await Swal.fire({
                    title: 'Ошибка',
                    text: error.value,
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                saving.value = null;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            creating,
            error,
            modals,
            getModalTitle,
            getModalDescription,
            hasModalType,
            createModal,
            saveModal,
        };
    },
};
</script>

<style scoped>
/* Custom styles if needed */
</style>

