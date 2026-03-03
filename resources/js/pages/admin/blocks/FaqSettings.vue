<template>
    <div class="faq-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки блока FAQ</h1>
            <p class="text-muted-foreground mt-1">Управление блоком "Часто задаваемые вопросы" на главной странице</p>
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

        <!-- Settings Form -->
        <div v-if="!loading" class="space-y-6">
            <!-- General Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6 shadow-sm">
                <h2 class="text-xl font-semibold text-foreground flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Общие настройки
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Заголовок блока
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Часто задаваемые вопросы"
                            class="w-full px-4 py-2.5 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-all"
                            :class="errors.title ? 'border-destructive' : ''"
                        />
                        <p v-if="errors.title" class="mt-1 text-xs text-destructive">{{ errors.title }}</p>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-muted/30 rounded-lg">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-5 h-5 rounded border-border text-accent focus:ring-2 focus:ring-accent cursor-pointer"
                        />
                        <label for="is_active" class="text-sm font-medium text-foreground cursor-pointer flex-1">
                            Блок активен на главной странице
                        </label>
                        <span 
                            class="px-2 py-1 text-xs font-medium rounded"
                            :class="form.is_active ? 'bg-green-500/20 text-green-600' : 'bg-muted text-muted-foreground'"
                        >
                            {{ form.is_active ? 'Активен' : 'Неактивен' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- FAQ Items -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <h2 class="text-xl font-semibold text-foreground flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Вопросы и ответы
                        <span v-if="form.faq_items && form.faq_items.length > 0" class="ml-2 px-2 py-0.5 text-xs font-medium bg-accent/20 text-accent rounded-full">
                            {{ form.faq_items.length }}
                        </span>
                    </h2>
                    <button
                        @click="addFaqItem"
                        class="px-4 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-all text-sm font-medium flex items-center gap-2 shadow-sm hover:shadow"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить вопрос
                    </button>
                </div>

                <div v-if="form.faq_items && form.faq_items.length === 0" class="text-center py-12 border-2 border-dashed border-border rounded-lg">
                    <svg class="w-12 h-12 text-muted-foreground mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-muted-foreground mb-2">Нет вопросов</p>
                    <p class="text-sm text-muted-foreground">Добавьте первый вопрос, нажав кнопку выше</p>
                </div>

                <div v-else class="space-y-4">
                    <TransitionGroup name="faq-item" tag="div">
                        <div
                            v-for="(item, index) in form.faq_items"
                            :key="`faq-item-${index}`"
                            class="border border-border rounded-lg p-5 space-y-4 bg-background/50 hover:border-accent/50 transition-all"
                            :class="getItemErrors(index) ? 'border-destructive/50' : ''"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-accent/10 text-accent font-semibold text-sm">
                                        {{ index + 1 }}
                                    </div>
                                    <h3 class="font-semibold text-foreground">Вопрос {{ index + 1 }}</h3>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="confirmRemove(index)"
                                        class="p-2 text-destructive hover:bg-destructive/10 rounded transition-colors"
                                        title="Удалить вопрос"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Вопрос <span class="text-destructive">*</span>
                                </label>
                                <input
                                    v-model="item.question"
                                    type="text"
                                    placeholder="Введите вопрос"
                                    class="w-full px-4 py-2.5 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-all"
                                    :class="getItemErrors(index).question ? 'border-destructive' : ''"
                                    @blur="validateItem(index)"
                                />
                                <p v-if="getItemErrors(index).question" class="mt-1 text-xs text-destructive">
                                    {{ getItemErrors(index).question }}
                                </p>
                                <p v-else class="mt-1 text-xs text-muted-foreground">
                                    Максимум 255 символов
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Ответ <span class="text-destructive">*</span>
                                </label>
                                <textarea
                                    v-model="item.answer"
                                    rows="4"
                                    placeholder="Введите ответ на вопрос. Поддерживается HTML разметка."
                                    class="w-full px-4 py-2.5 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-all resize-y"
                                    :class="getItemErrors(index).answer ? 'border-destructive' : ''"
                                    @blur="validateItem(index)"
                                ></textarea>
                                <p v-if="getItemErrors(index).answer" class="mt-1 text-xs text-destructive">
                                    {{ getItemErrors(index).answer }}
                                </p>
                                <p v-else class="mt-1 text-xs text-muted-foreground">
                                    Поддерживается HTML. Используйте &lt;br&gt; для переноса строки.
                                </p>
                            </div>

                            <!-- Кнопки перемещения -->
                            <div class="flex items-center gap-2 pt-2 border-t border-border">
                                <button
                                    v-if="index > 0"
                                    @click="moveFaqItem(index, 'up')"
                                    class="px-3 py-1.5 text-sm border border-border hover:bg-accent/10 rounded transition-colors flex items-center gap-1"
                                    title="Переместить вверх"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                    Вверх
                                </button>
                                <button
                                    v-if="index < form.faq_items.length - 1"
                                    @click="moveFaqItem(index, 'down')"
                                    class="px-3 py-1.5 text-sm border border-border hover:bg-accent/10 rounded transition-colors flex items-center gap-1"
                                    title="Переместить вниз"
                                >
                                    Вниз
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-border">
                <button
                    @click="saveSettings"
                    :disabled="saving || !isFormValid"
                    class="px-6 py-2.5 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium flex items-center gap-2 shadow-sm hover:shadow"
                >
                    <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div v-else class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                    {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'FaqSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const errors = ref({});
        const itemErrors = ref({});
        const form = ref({
            title: '',
            is_active: true,
            faq_items: [],
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/v1/faq-block-settings');
                if (response.data && response.data.data) {
                    form.value = {
                        title: response.data.data.title || '',
                        is_active: response.data.data.is_active !== false,
                        faq_items: Array.isArray(response.data.data.faq_items) 
                            ? response.data.data.faq_items 
                            : [],
                    };
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const validateForm = () => {
            errors.value = {};
            
            if (!form.value.title || form.value.title.trim().length === 0) {
                errors.value.title = 'Заголовок обязателен для заполнения';
            } else if (form.value.title.length > 255) {
                errors.value.title = 'Заголовок не должен превышать 255 символов';
            }

            return Object.keys(errors.value).length === 0;
        };

        const validateItem = (index) => {
            if (!form.value.faq_items || !form.value.faq_items[index]) return;
            
            const item = form.value.faq_items[index];
            const itemError = {};
            
            if (!item.question || item.question.trim().length === 0) {
                itemError.question = 'Вопрос обязателен для заполнения';
            } else if (item.question.length > 255) {
                itemError.question = 'Вопрос не должен превышать 255 символов';
            }
            
            if (!item.answer || item.answer.trim().length === 0) {
                itemError.answer = 'Ответ обязателен для заполнения';
            }
            
            if (Object.keys(itemError).length > 0) {
                itemErrors.value[index] = itemError;
            } else {
                delete itemErrors.value[index];
            }
        };

        const validateAllItems = () => {
            itemErrors.value = {};
            if (form.value.faq_items && form.value.faq_items.length > 0) {
                form.value.faq_items.forEach((_, index) => {
                    validateItem(index);
                });
            }
            return Object.keys(itemErrors.value).length === 0;
        };

        const getItemErrors = (index) => {
            return itemErrors.value[index] || {};
        };

        const isFormValid = computed(() => {
            const formValid = validateForm();
            const itemsValid = validateAllItems();
            const hasItems = form.value.faq_items && form.value.faq_items.length > 0;
            return formValid && itemsValid && hasItems;
        });

        const addFaqItem = () => {
            if (!form.value.faq_items) {
                form.value.faq_items = [];
            }
            form.value.faq_items.push({
                question: '',
                answer: '',
            });
        };

        const confirmRemove = async (index) => {
            const result = await Swal.fire({
                title: 'Удалить вопрос?',
                text: 'Это действие нельзя отменить',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
            });

            if (result.isConfirmed) {
                removeFaqItem(index);
            }
        };

        const removeFaqItem = (index) => {
            if (form.value.faq_items && form.value.faq_items.length > index) {
                form.value.faq_items.splice(index, 1);
                // Удаляем ошибки для этого индекса
                delete itemErrors.value[index];
                // Перенумеровываем ошибки
                const newErrors = {};
                Object.keys(itemErrors.value).forEach(key => {
                    const keyNum = parseInt(key);
                    if (keyNum > index) {
                        newErrors[keyNum - 1] = itemErrors.value[key];
                    } else if (keyNum < index) {
                        newErrors[keyNum] = itemErrors.value[key];
                    }
                });
                itemErrors.value = newErrors;
            }
        };

        const moveFaqItem = (index, direction) => {
            if (!form.value.faq_items || form.value.faq_items.length <= 1) return;
            
            const newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex >= 0 && newIndex < form.value.faq_items.length) {
                const item = form.value.faq_items[index];
                form.value.faq_items[index] = form.value.faq_items[newIndex];
                form.value.faq_items[newIndex] = item;
                
                // Обновляем ошибки
                const itemError = itemErrors.value[index];
                const newItemError = itemErrors.value[newIndex];
                if (itemError) {
                    itemErrors.value[newIndex] = itemError;
                } else {
                    delete itemErrors.value[newIndex];
                }
                if (newItemError) {
                    itemErrors.value[index] = newItemError;
                } else {
                    delete itemErrors.value[index];
                }
            }
        };

        const saveSettings = async () => {
            // Валидация перед сохранением
            if (!validateForm()) {
                await Swal.fire({
                    title: 'Ошибка валидации',
                    text: 'Пожалуйста, исправьте ошибки в форме',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
                return;
            }

            if (!validateAllItems()) {
                await Swal.fire({
                    title: 'Ошибка валидации',
                    text: 'Пожалуйста, заполните все поля вопросов и ответов',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
                return;
            }

            if (!form.value.faq_items || form.value.faq_items.length === 0) {
                await Swal.fire({
                    title: 'Нет вопросов',
                    text: 'Добавьте хотя бы один вопрос',
                    icon: 'warning',
                    confirmButtonText: 'ОК'
                });
                return;
            }

            saving.value = true;
            error.value = null;
            try {
                const response = await axios.put('/api/v1/faq-block-settings', form.value);
                
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
                const errorDetails = err.response?.data?.errors;
                if (errorDetails) {
                    // Обработка ошибок валидации от сервера
                    if (errorDetails.title) {
                        errors.value.title = errorDetails.title[0];
                    }
                }
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
            errors,
            form,
            addFaqItem,
            confirmRemove,
            removeFaqItem,
            moveFaqItem,
            saveSettings,
            validateItem,
            getItemErrors,
            isFormValid,
        };
    },
};
</script>

<style scoped>
/* Анимации для списка вопросов */
.faq-item-enter-active,
.faq-item-leave-active {
    transition: all 0.3s ease;
}

.faq-item-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.faq-item-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

.faq-item-move {
    transition: transform 0.3s ease;
}
</style>

