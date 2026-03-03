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

        <!-- Сетка изображений -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
            <div
                v-for="select in data.selects"
                :key="select.id"
                class="relative group cursor-pointer"
                @click="handleSelect(select)"
            >
                <div class="relative aspect-[245/140] sm:aspect-[245/140] rounded-2xl overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <img
                        :src="select.src || select.webp || '/upload/quiz/placeholder.jpg'"
                        :alt="select.name || select.title"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        @error="handleImageError"
                    />
                    <!-- Градиентный оверлей -->
                    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/20 to-transparent pointer-events-none"></div>
                    <!-- Текст поверх изображения -->
                    <div class="absolute top-3 left-3 right-3 z-10">
                        <h3 class="text-white font-medium text-base sm:text-lg md:text-xl drop-shadow-lg">
                            {{ select.title }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ImagesCollect',
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
                text: select.title || select.name || '',
                src: select.src || select.webp || '',
            };
            this.$emit('answer', answerData);
            if (select.child) {
                setTimeout(() => {
                    this.$emit('next', select.child);
                }, 300);
            } else {
                // Если child не указан, переходим к следующему вопросу
                setTimeout(() => {
                    this.$emit('next');
                }, 300);
            }
        },
        handleImageError(event) {
            // Если изображение не загрузилось, используем placeholder
            event.target.src = '/upload/quiz/placeholder.jpg';
        },
    },
};
</script>

