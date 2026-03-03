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

        <!-- Список вариантов -->
        <div class="space-y-3 sm:space-y-4 max-w-2xl">
            <button
                v-for="select in data.selects"
                :key="select.id"
                @click="handleSelect(select)"
                class="w-full px-6 py-4 text-left bg-white border-2 border-gray-200 rounded-lg hover:border-[#657C6C] hover:bg-gray-50 transition-all duration-200 text-base sm:text-lg font-normal text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#657C6C] focus:ring-offset-2"
            >
                {{ select.name }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Selects',
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
    methods: {
        handleSelect(select) {
            // Отправляем полную информацию о выбранном варианте
            const answerData = {
                id: select.id || select.name,
                name: select.name || '',
                title: select.title || select.name || '',
                text: select.name || select.title || '',
            };
            this.$emit('answer', answerData);
            // Переходим к следующему вопросу (по умолчанию к следующему по порядку)
            setTimeout(() => {
                this.$emit('next');
            }, 300);
        },
    },
};
</script>

