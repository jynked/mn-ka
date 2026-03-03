<template>
    <div class="mt-6">
        <!-- Номер вопроса и текст -->
        <div class="flex items-center gap-3 sm:gap-4 mb-6 px-2">
            <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-[#657C6C] text-white font-semibold text-lg sm:text-xl flex-shrink-0">
                {{ data.id }}
            </div>
            <div class="question text-lg sm:text-xl md:text-2xl font-medium text-gray-900">
                {{ data.question }}
            </div>
        </div>

        <!-- Поле ввода -->
        <div class="max-w-2xl">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ data.label }}
            </label>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <input
                    v-model="answerValue"
                    type="text"
                    :placeholder="data.placeholder"
                    @keyup.enter="handleNext"
                    class="flex-1 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm sm:text-base transition-colors disabled:opacity-50 focus:border-[#657C6C] focus:ring-2 focus:ring-[#657C6C]/20 focus:outline-none"
                />
                <button
                    @click="handleNext"
                    :disabled="!isAnswerValid"
                    class="px-6 sm:px-8 py-3 bg-[#657C6C] hover:bg-[#55695a] disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#657C6C] focus:ring-offset-2"
                >
                    Далее
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
    name: 'Inputs',
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
    setup(props, { emit }) {
        // Создаем локальную реактивную переменную для ответа
        // Инициализируем из props.data.answer или пустой строкой
        const answerValue = ref(props.data?.answer || '');

        // Вычисляем, валиден ли ответ (проверяем, что значение не пустое)
        const isAnswerValid = computed(() => {
            const value = answerValue.value;
            if (value === null || value === undefined) {
                return false;
            }
            const strValue = value.toString().trim();
            return strValue !== '';
        });

        const handleNext = () => {
            const trimmedAnswer = answerValue.value?.toString().trim() || '';
            if (trimmedAnswer) {
                // Сохраняем ответ в data для синхронизации
                if (props.data) {
                    props.data.answer = trimmedAnswer;
                }
                emit('answer', trimmedAnswer);
                if (props.data?.child) {
                    emit('next', props.data.child);
                } else {
                    emit('next');
                }
            }
        };

        return {
            answerValue,
            isAnswerValid,
            handleNext,
        };
    },
};
</script>


