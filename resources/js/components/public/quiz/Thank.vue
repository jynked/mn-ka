<template>
    <div class="mt-6">
        <div class="flex flex-col items-center justify-center py-8 sm:py-12 px-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-green-100 flex items-center justify-center mb-6">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-semibold text-gray-900 text-center mb-4">
                Спасибо!
            </h3>
            <p class="text-base sm:text-lg text-gray-600 text-center max-w-md">
                Наши специалисты свяжутся с вами в течение 15 минут
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Thank',
    props: {
        data: {
            type: Object,
            default: () => ({}),
        },
        numberQuestion: {
            type: Number,
            required: true,
        },
    },
};
</script>


