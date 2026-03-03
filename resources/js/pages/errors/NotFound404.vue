<template>
    <div class="error-page min-h-screen bg-white flex flex-col items-center justify-center py-12 px-3 sm:px-4 md:px-5">
        <div class="w-full max-w-[1200px] mx-auto text-center">
            <div class="mb-8">
                <h1 class="text-8xl md:text-9xl font-bold text-[#688E67] mb-4">404</h1>
                <h2 class="text-2xl md:text-3xl font-semibold text-black mb-4">
                    Страница не найдена
                </h2>
                <p class="text-base md:text-lg text-gray-600 max-w-md mx-auto mb-8">
                    К сожалению, запрашиваемая страница не существует. Возможно, она была удалена или вы перешли по неверной ссылке.
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a
                    href="/"
                    class="inline-flex items-center justify-center px-6 py-3 bg-[#688E67] text-white rounded-lg font-medium hover:bg-[#5a7a5a] transition-colors"
                >
                    Вернуться на главную
                </a>
                <button
                    @click="goBack"
                    class="inline-flex items-center justify-center px-6 py-3 bg-white text-[#688E67] border-2 border-[#688E67] rounded-lg font-medium hover:bg-[#688E67] hover:text-white transition-colors"
                >
                    Назад
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NotFound404',
    methods: {
        goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        },
    },
};
</script>



