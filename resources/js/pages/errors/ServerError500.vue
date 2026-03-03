<template>
    <div class="error-page min-h-screen bg-white flex flex-col">
        <PublicHeader />
        <main class="flex-1 flex items-center justify-center py-12 px-3 sm:px-4 md:px-5">
            <div class="w-full max-w-[1200px] mx-auto text-center">
                <div class="mb-8">
                    <h1 class="text-8xl md:text-9xl font-bold text-[#688E67] mb-4">500</h1>
                    <h2 class="text-2xl md:text-3xl font-semibold text-black mb-4">
                        Внутренняя ошибка сервера
                    </h2>
                    <p class="text-base md:text-lg text-gray-600 max-w-md mx-auto mb-8">
                        Произошла внутренняя ошибка сервера. Мы уже работаем над ее устранением. Пожалуйста, попробуйте позже.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <router-link
                        to="/"
                        class="inline-flex items-center justify-center px-6 py-3 bg-[#688E67] text-white rounded-lg font-medium hover:bg-[#5a7a5a] transition-colors"
                    >
                        Вернуться на главную
                    </router-link>
                    <button
                        @click="reloadPage"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white text-[#688E67] border-2 border-[#688E67] rounded-lg font-medium hover:bg-[#688E67] hover:text-white transition-colors"
                    >
                        Обновить страницу
                    </button>
                </div>
            </div>
        </main>
        <PublicFooter />
    </div>
</template>

<script>
import PublicHeader from '../../components/public/PublicHeader.vue';
import PublicFooter from '../../components/public/Footer.vue';

export default {
    name: 'ServerError500',
    components: {
        PublicHeader,
        PublicFooter,
    },
    methods: {
        reloadPage() {
            window.location.reload();
        },
    },
};
</script>



