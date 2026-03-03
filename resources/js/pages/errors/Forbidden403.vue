<template>
    <div class="error-page min-h-screen bg-white flex flex-col">
        <PublicHeader />
        <main class="flex-1 flex items-center justify-center py-12 px-3 sm:px-4 md:px-5">
            <div class="w-full max-w-[1200px] mx-auto text-center">
                <div class="mb-8">
                    <h1 class="text-8xl md:text-9xl font-bold text-[#688E67] mb-4">403</h1>
                    <h2 class="text-2xl md:text-3xl font-semibold text-black mb-4">
                        Доступ запрещен
                    </h2>
                    <p class="text-base md:text-lg text-gray-600 max-w-md mx-auto mb-8">
                        У вас нет доступа к этой странице. Если вы считаете, что это ошибка, обратитесь к администратору.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <router-link
                        to="/"
                        class="inline-flex items-center justify-center px-6 py-3 bg-[#688E67] text-white rounded-lg font-medium hover:bg-[#5a7a5a] transition-colors"
                    >
                        Вернуться на главную
                    </router-link>
                    <router-link
                        v-if="!isAuthenticated"
                        to="/login"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white text-[#688E67] border-2 border-[#688E67] rounded-lg font-medium hover:bg-[#688E67] hover:text-white transition-colors"
                    >
                        Войти в систему
                    </router-link>
                    <button
                        v-else
                        @click="$router.go(-1)"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white text-[#688E67] border-2 border-[#688E67] rounded-lg font-medium hover:bg-[#688E67] hover:text-white transition-colors"
                    >
                        Назад
                    </button>
                </div>
            </div>
        </main>
        <PublicFooter />
    </div>
</template>

<script>
import { computed } from 'vue';
import { useStore } from 'vuex';
import PublicHeader from '../../components/public/PublicHeader.vue';
import PublicFooter from '../../components/public/Footer.vue';

export default {
    name: 'Forbidden403',
    components: {
        PublicHeader,
        PublicFooter,
    },
    setup() {
        const store = useStore();
        const isAuthenticated = computed(() => !!store.state.token);
        
        return {
            isAuthenticated,
        };
    },
};
</script>



