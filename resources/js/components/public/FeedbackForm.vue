<template>
    <section class="w-full px-3 sm:px-4 md:px-5 py-20 md:py-24 bg-background">
        <div class="w-full max-w-[1200px] mx-auto">
            <div class="feedback-form-container bg-[#F4F6FC] py-8 md:py-12 px-4 md:px-6 rounded-lg">
                <div class="max-w-4xl mx-auto">
                    <!-- Заголовок -->
                    <div v-if="title || subtitle" class="text-center mb-8">
                        <h2 v-if="title" class="text-2xl md:text-3xl font-semibold text-foreground mb-3">
                            {{ title }}
                        </h2>
                        <p v-if="subtitle" class="text-muted-foreground text-base md:text-lg">
                            {{ subtitle }}
                        </p>
                    </div>
                    <!-- Подсказка -->
                    <div v-if="hintText" class="text-center mb-4">
                        <p class="text-sm text-muted-foreground">
                            {{ hintText }}
                        </p>
                    </div>

                    <!-- Форма -->
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Имя -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-foreground mb-2">
                                    Имя <span class="text-red-500">*</span>
                                </label>
                                <input id="name" v-model="form.name" type="text" required placeholder="Ваше имя"
                                    class="w-full h-12 px-4 border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                                    :class="{ 'border-red-500': errors.name }" />
                                <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name }}</p>
                            </div>

                            <!-- Телефон -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-foreground mb-2">
                                    Телефон
                                </label>
                                <input id="phone" v-model="form.phone" type="tel" placeholder="+7 (___) ___-__-__"
                                    class="w-full h-12 px-4 border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                                    :class="{ 'border-red-500': errors.phone }" />
                                <p v-if="errors.phone" class="mt-1 text-sm text-red-500">{{ errors.phone }}</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-foreground mb-2">
                                Email
                            </label>
                            <input id="email" v-model="form.email" type="email" placeholder="your@email.com"
                                class="w-full h-12 px-4 border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                                :class="{ 'border-red-500': errors.email }" />
                            <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email }}</p>
                        </div>

                        <!-- Сообщение -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-foreground mb-2">
                                Сообщение <span class="text-red-500">*</span>
                            </label>
                            <textarea id="message" v-model="form.message" required rows="6"
                                placeholder="Введите ваше сообщение..."
                                class="w-full px-4 py-3 border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent resize-none transition-colors"
                                :class="{ 'border-red-500': errors.message }"></textarea>
                            <p v-if="errors.message" class="mt-1 text-sm text-red-500">{{ errors.message }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ form.message.length }}/5000 символов
                            </p>
                        </div>

                        <!-- Чекбокс согласия на обработку ПДн -->
                        <div class="pt-2">
                            <ConsentCheckbox v-model="consentGiven" :error="consentError"
                                @update:error="consentError = $event" />
                        </div>

                        <!-- Кнопка отправки -->
                        <div class="flex justify-center pt-4">
                            <button type="submit" :disabled="loading || !canSubmit"
                                class="px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium text-base min-w-[200px]">
                                <span v-if="loading">Отправка...</span>
                                <span v-else>Отправить</span>
                            </button>
                        </div>

                        <!-- Сообщение об успехе -->
                        <div v-if="success"
                            class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-center">
                            <p class="font-medium">Спасибо за ваше обращение!</p>
                            <p class="text-sm mt-1">Мы свяжемся с вами в ближайшее время.</p>
                        </div>

                        <!-- Сообщение об ошибке -->
                        <div v-if="error"
                            class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-center">
                            <p class="font-medium">{{ error }}</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { ref, computed, watch } from 'vue';
import Swal from 'sweetalert2';
import ConsentCheckbox from './ConsentCheckbox.vue';

export default {
    name: 'FeedbackForm',
    components: {
        ConsentCheckbox,
    },
    props: {
        title: {
            type: String,
            default: 'Обратная связь',
        },
        subtitle: {
            type: String,
            default: 'Оставьте ваше сообщение, и мы обязательно свяжемся с вами',
        },
        hintText: {
            type: String,
            default: null,
        },
    },
    setup() {
        const form = ref({
            name: '',
            phone: '',
            email: '',
            message: '',
        });

        const errors = ref({});
        const loading = ref(false);
        const success = ref(false);
        const error = ref(null);
        const consentGiven = ref(false);
        const consentError = ref(false);

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
            form.value.phone = formatPhone(newPhone);
        });

        watch(() => form.value.message, (newMessage) => {
            if (newMessage.length > 5000) {
                form.value.message = newMessage.slice(0, 5000);
            }
        });

        const canSubmit = computed(() => {
            return form.value.name.trim() !== '' && form.value.message.trim() !== '' && consentGiven.value;
        });

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
                        message: form.value.message.trim(),
                        consent: consentGiven.value ? 1 : 0,
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

                // Скрываем сообщение об успехе через 5 секунд
                setTimeout(() => {
                    success.value = false;
                }, 5000);
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
            loading,
            success,
            error,
            canSubmit,
            consentGiven,
            consentError,
            submitForm,
        };
    },
};
</script>

<style scoped>
.feedback-form-container {
    min-height: 210px;
}
</style>
