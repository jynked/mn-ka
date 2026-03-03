<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        @click.self="closeModal"
    >
        <div class="bg-background rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-border">
                <h2 class="text-2xl font-semibold text-foreground">Обратная связь</h2>
                <button
                    @click="closeModal"
                    class="text-muted-foreground hover:text-foreground transition-colors"
                    type="button"
                >
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Имя -->
                    <div>
                        <label for="modal-name" class="block text-sm font-medium text-foreground mb-2">
                            Имя <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="modal-name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Ваше имя"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': errors.name }"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name }}</p>
                    </div>

                    <!-- Телефон -->
                    <div>
                        <label for="modal-phone" class="block text-sm font-medium text-foreground mb-2">
                            Телефон
                        </label>
                        <input
                            id="modal-phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="+7 (___) ___-__-__"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': errors.phone }"
                        />
                        <p v-if="errors.phone" class="mt-1 text-sm text-red-500">{{ errors.phone }}</p>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="modal-email" class="block text-sm font-medium text-foreground mb-2">
                        Email
                    </label>
                    <input
                        id="modal-email"
                        v-model="form.email"
                        type="email"
                        placeholder="your@email.com"
                        class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email }}</p>
                </div>

                <!-- Сообщение -->
                <div>
                    <label for="modal-message" class="block text-sm font-medium text-foreground mb-2">
                        Сообщение <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="modal-message"
                        v-model="form.message"
                        required
                        rows="6"
                        placeholder="Введите ваше сообщение..."
                        class="w-full px-4 py-3 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent resize-none transition-colors"
                        :class="{ 'border-red-500': errors.message }"
                    ></textarea>
                    <p v-if="errors.message" class="mt-1 text-sm text-red-500">{{ errors.message }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ form.message.length }}/5000 символов
                    </p>
                </div>

                <!-- Чекбокс согласия на обработку ПДн -->
                <div class="pt-2">
                    <ConsentCheckbox 
                        v-model="consentGiven" 
                        :error="consentError"
                        @update:error="consentError = $event"
                    />
                </div>

                <!-- Error message -->
                <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-600">{{ error }}</p>
                </div>

                <!-- Success message -->
                <div v-if="success" class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-600">
                        Спасибо за ваше обращение! Мы свяжемся с вами в ближайшее время.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4 pt-4">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-6 py-3 border border-border rounded-lg hover:bg-muted transition-colors font-medium"
                        :disabled="loading"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        :disabled="loading || !canSubmit"
                        class="px-6 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                    >
                        <span v-if="loading">Отправка...</span>
                        <span v-else>Отправить</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import Swal from 'sweetalert2';
import ConsentCheckbox from './ConsentCheckbox.vue';

export default {
    name: 'FeedbackModal',
    components: {
        ConsentCheckbox,
    },
    props: {
        isOpen: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close', 'success'],
    setup(props, { emit }) {
        const loading = ref(false);
        const error = ref(null);
        const success = ref(false);
        const errors = ref({});
        const consentGiven = ref(false);
        const consentError = ref(false);

        const form = ref({
            name: '',
            phone: '',
            email: '',
            message: '',
        });

        // Форматирование телефона
        const formatPhone = (value) => {
            const cleaned = value.replace(/\D/g, '');
            if (cleaned.startsWith('8')) {
                const match = cleaned.slice(1).match(/^(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})$/);
                if (match) {
                    return '+7 (' + (match[1] ? match[1] : '') + (match[2] ? ') ' + match[2] : '') + (match[3] ? '-' + match[3] : '') + (match[4] ? '-' + match[4] : '');
                }
            } else if (cleaned.startsWith('7')) {
                const match = cleaned.slice(1).match(/^(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})$/);
                if (match) {
                    return '+7 (' + (match[1] ? match[1] : '') + (match[2] ? ') ' + match[2] : '') + (match[3] ? '-' + match[3] : '') + (match[4] ? '-' + match[4] : '');
                }
            }
            return value;
        };

        watch(() => form.value.phone, (newPhone) => {
            const formatted = formatPhone(newPhone);
            if (formatted !== newPhone) {
                form.value.phone = formatted;
            }
        });

        watch(() => form.value.message, (newMessage) => {
            if (newMessage.length > 5000) {
                form.value.message = newMessage.slice(0, 5000);
            }
        });

        watch(() => props.isOpen, (isOpen) => {
            if (!isOpen) {
                // Сброс формы при закрытии
                setTimeout(() => {
                    form.value = {
                        name: '',
                        phone: '',
                        email: '',
                        message: '',
                    };
                    errors.value = {};
                    error.value = null;
                    success.value = false;
                    consentGiven.value = false;
                    consentError.value = false;
                }, 300);
            }
        });

        const canSubmit = computed(() => {
            return form.value.name.trim() !== '' && form.value.message.trim() !== '' && consentGiven.value;
        });

        const closeModal = () => {
            emit('close');
        };

        const submitForm = async () => {
            if (loading.value) return;

            errors.value = {};
            error.value = null;
            consentError.value = false;

            // Проверка согласия на обработку ПДн
            if (!consentGiven.value) {
                consentError.value = true;
                return;
            }

            // Проверка обязательных полей
            if (form.value.name.trim() === '' || form.value.message.trim() === '') {
                if (form.value.name.trim() === '') {
                    errors.value.name = 'Поле обязательно для заполнения';
                }
                if (form.value.message.trim() === '') {
                    errors.value.message = 'Поле обязательно для заполнения';
                }
                return;
            }

            loading.value = true;

            try {
                const response = await fetch('/api/public/feedback', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        name: form.value.name.trim(),
                        phone: form.value.phone.trim() || null,
                        email: form.value.email.trim() || null,
                        message: form.value.message.trim() || null,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        errors.value = data.errors;
                    }
                    throw new Error(data.message || 'Ошибка при отправке формы');
                }

                success.value = true;
                
                // Очищаем форму
                form.value = {
                    name: '',
                    phone: '',
                    email: '',
                    message: '',
                };
                consentGiven.value = false;
                consentError.value = false;

                // Показываем уведомление
                await Swal.fire({
                    icon: 'success',
                    title: 'Спасибо!',
                    text: 'Ваше сообщение успешно отправлено. Мы свяжемся с вами в ближайшее время.',
                    timer: 3000,
                    showConfirmButton: false,
                });

                // Закрываем модальное окно через небольшую задержку
                setTimeout(() => {
                    emit('success');
                    emit('close');
                }, 500);
            } catch (err) {
                error.value = err.message || 'Ошибка при отправке формы';
                console.error('Error submitting feedback:', err);
            } finally {
                loading.value = false;
            }
        };

        return {
            form,
            errors,
            error,
            success,
            loading,
            canSubmit,
            consentGiven,
            consentError,
            closeModal,
            submitForm,
        };
    },
};
</script>

