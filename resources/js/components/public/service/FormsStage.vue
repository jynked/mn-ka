<template>
    <div class="w-full max-w-2xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div>
                <label for="service-name" class="block text-sm font-medium text-foreground mb-2">
                    Как к Вам обращаться *
                </label>
                <input
                    id="service-name"
                    type="text"
                    v-model="form.name"
                    @input="updateForm"
                    placeholder="Ваше имя"
                    class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent"
                    required
                />
            </div>
            <div>
                <label for="service-phone" class="block text-sm font-medium text-foreground mb-2">
                    Ваш номер телефона *
                </label>
                <input
                    id="service-phone"
                    type="tel"
                    v-model="form.phone"
                    @input="updateForm"
                    placeholder="+7 (___) ___-__-__"
                    class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent"
                    required
                />
            </div>
            <div class="md:col-span-2">
                <label for="service-comment" class="block text-sm font-medium text-foreground mb-2">
                    Комментарий (необязательно)
                </label>
                <textarea
                    id="service-comment"
                    v-model="form.comment"
                    @input="updateForm"
                    placeholder="Введите комментарий или пометку"
                    rows="4"
                    class="w-full px-4 py-3 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent resize-none"
                ></textarea>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch } from 'vue';
import { useDebounce } from '../../../composables/useDebounce';

export default {
    name: 'ServiceFormsStage',
    props: {
        service: {
            type: Object,
            required: true,
        },
    },
    emits: ['update-form'],
    setup(props, { emit }) {
        const form = ref({
            name: '',
            phone: '',
            comment: '',
        });

        // Debounced обновление формы для оптимизации
        const { debounced: debouncedUpdate } = useDebounce(() => {
            emit('update-form', { ...form.value });
        }, 300);

        const updateForm = () => {
            debouncedUpdate();
        };

        // Оптимизированная форматизация телефона
        const formatPhone = (value) => {
            let phone = value.replace(/\D/g, '');
            
            if (phone.length > 11) {
                phone = phone.slice(0, 11);
            }
            
            if (phone.length > 0 && !phone.startsWith('7')) {
                if (phone.startsWith('8')) {
                    phone = '7' + phone.slice(1);
                } else {
                    phone = '7' + phone;
                }
            }
            
            if (phone.length <= 1) {
                return phone.length === 0 ? '' : '+7';
            }
            
            let formatted = '+7';
            const digits = phone.slice(1);
            
            if (digits.length > 0) {
                formatted += ' (' + digits.slice(0, 3);
                if (digits.length > 3) {
                    formatted += ') ' + digits.slice(3, 6);
                    if (digits.length > 6) {
                        formatted += '-' + digits.slice(6, 8);
                        if (digits.length > 8) {
                            formatted += '-' + digits.slice(8, 10);
                        }
                    }
                }
            }
            
            return formatted;
        };

        watch(() => form.value.phone, (newPhone) => {
            const formatted = formatPhone(newPhone);
            if (formatted !== newPhone) {
                form.value.phone = formatted;
            }
            updateForm();
        });

        watch(() => form.value.name, () => {
            updateForm();
        });

        watch(() => form.value.comment, () => {
            updateForm();
        });

        return {
            form,
            updateForm,
        };
    },
};
</script>

