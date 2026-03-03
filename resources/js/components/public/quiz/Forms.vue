<template>
    <div class="mt-6">
        <!-- Текст вопроса -->
        <div class="mb-6 px-2">
            <div
                class="question text-lg sm:text-xl md:text-2xl font-medium text-gray-900"
                v-html="data.question"
            ></div>
        </div>

        <!-- Форма -->
        <div class="max-w-2xl">
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label for="quiz-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Ваше имя
                    </label>
                    <input
                        id="quiz-name"
                        v-model="data.form.name"
                        type="text"
                        required
                        class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm sm:text-base transition-colors focus:border-[#657C6C] focus:ring-2 focus:ring-[#657C6C]/20 focus:outline-none"
                        placeholder="Иван Иванов"
                    />
                </div>
                <div>
                    <label for="quiz-email" class="block text-sm font-medium text-gray-700 mb-2">
                        Ваш email
                    </label>
                    <input
                        id="quiz-email"
                        v-model="data.form.email"
                        type="email"
                        required
                        class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm sm:text-base transition-colors focus:border-[#657C6C] focus:ring-2 focus:ring-[#657C6C]/20 focus:outline-none"
                        placeholder="your@email.com"
                    />
                </div>
                <div>
                    <label for="quiz-phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Ваш телефон
                    </label>
                    <input
                        id="quiz-phone"
                        v-model="data.form.phone"
                        type="tel"
                        required
                        class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm sm:text-base transition-colors focus:border-[#657C6C] focus:ring-2 focus:ring-[#657C6C]/20 focus:outline-none"
                        placeholder="+7 (999) 123-45-67"
                    />
                </div>
                
                <!-- Чекбокс согласия на обработку ПДн -->
                <div class="pt-2">
                    <ConsentCheckbox 
                        v-model="data.form.check" 
                        :error="consentError"
                        @update:error="consentError = $event"
                        input-id="quiz-consent-checkbox"
                    />
                </div>
                
                <button
                    type="submit"
                    :disabled="!isFormValid"
                    class="w-full sm:w-auto px-8 py-3 bg-[#657C6C] hover:bg-[#55695a] disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#657C6C] focus:ring-offset-2"
                >
                    Отправить
                </button>
            </form>
        </div>
    </div>
</template>

<script>
import ConsentCheckbox from '../ConsentCheckbox.vue';

export default {
    name: 'Forms',
    components: {
        ConsentCheckbox,
    },
    props: {
        data: {
            type: Object,
            required: true,
        },
        numberQuestion: {
            type: Number,
            required: true,
        },
    },
    emits: ['next', 'answer'],
    data() {
        return {
            consentError: false,
        };
    },
    computed: {
        isFormValid() {
            return (
                this.data.form.name &&
                this.data.form.name.trim() !== '' &&
                this.data.form.email &&
                this.data.form.email.trim() !== '' &&
                this.data.form.phone &&
                this.data.form.phone.trim() !== '' &&
                this.data.form.check
            );
        },
    },
    methods: {
        async handleSubmit() {
            // Проверка согласия перед отправкой
            if (!this.data.form.check) {
                this.consentError = true;
                return;
            }
            this.consentError = false;

            if (this.isFormValid) {
                // Сохраняем ответ формы
                this.$emit('answer', this.data.form);
                
                // Отправляем данные на сервер через родительский компонент
                this.$emit('submit', {
                    name: this.data.form.name,
                    email: this.data.form.email,
                    phone: this.data.form.phone,
                });
            }
        },
    },
};
</script>

