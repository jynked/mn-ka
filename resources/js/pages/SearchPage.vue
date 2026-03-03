<template>
    <div class="min-h-screen bg-[#F5F5F5] py-8">
        <div class="w-full max-w-[1200px] mx-auto px-4 sm:px-5">
            <!-- Заголовок -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-semibold text-black mb-2">
                    Результаты поиска
                    <span v-if="searchQuery" class="text-[#6C7B6D]">"{{ searchQuery }}"</span>
                </h1>
                <p v-if="total > 0" class="text-sm text-gray-600">
                    Найдено результатов: {{ total }}
                </p>
                <p v-else-if="!loading && searchQuery" class="text-sm text-gray-600">
                    По вашему запросу ничего не найдено
                </p>
            </div>

            <!-- Поле поиска -->
            <div class="mb-6">
                <div class="relative flex items-center h-[48px] bg-white rounded-lg px-4 shadow-sm" :class="localSearchQuery || searchQuery ? 'pr-20' : 'pr-12'">
                    <input 
                        type="text" 
                        v-model="localSearchQuery"
                        @keypress.enter="performSearch"
                        placeholder="Поиск по сайту" 
                        class="outline-none border-0 w-full p-0 bg-transparent text-sm text-black placeholder:text-[#999]"
                        ref="searchInput"
                    >
                    <!-- Кнопка закрытия (показывается только если есть текст) -->
                    <button
                        v-if="localSearchQuery || searchQuery"
                        @click="clearSearch"
                        type="button"
                        class="absolute right-10 top-1/2 -translate-y-1/2 cursor-pointer hover:opacity-70 transition-opacity"
                        title="Очистить поиск"
                    >
                        <svg 
                            width="20" 
                            height="20"
                            viewBox="0 0 20 20" 
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path 
                                d="M15 5L5 15M5 5L15 15" 
                                stroke="#999" 
                                stroke-width="2" 
                                stroke-linecap="round" 
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                    <!-- Кнопка поиска -->
                    <button
                        @click="performSearch"
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer hover:opacity-70 transition-opacity"
                        title="Найти"
                    >
                        <svg 
                            width="20" 
                            height="20"
                            viewBox="0 0 20 20" 
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path 
                                fill-rule="evenodd" 
                                clip-rule="evenodd"
                                d="M12.5885 13.8064C11.0409 15.0431 9.07854 15.6401 7.10447 15.475C5.13041 15.3098 3.2945 14.3951 1.97381 12.9185C0.653118 11.4419 -0.0520973 9.5157 0.00300163 7.53539C0.0581006 5.55508 0.869331 3.67104 2.27008 2.27021C3.67083 0.869381 5.55476 0.0581039 7.53496 0.00300181C9.51515 -0.0521003 11.4413 0.653156 12.9178 1.97392C14.3942 3.29469 15.309 5.1307 15.4741 7.10488C15.6392 9.07906 15.0422 11.0415 13.8057 12.5893L19.7259 18.5087C19.8105 18.5875 19.8784 18.6826 19.9254 18.7883C19.9725 18.8939 19.9978 19.0079 19.9999 19.1236C20.0019 19.2392 19.9806 19.3541 19.9373 19.4613C19.894 19.5686 19.8295 19.666 19.7478 19.7478C19.666 19.8295 19.5686 19.894 19.4614 19.9373C19.3541 19.9806 19.2393 20.0019 19.1236 19.9999C19.008 19.9978 18.894 19.9725 18.7883 19.9254C18.6827 19.8784 18.5876 19.8105 18.5088 19.7259L12.5885 13.8064ZM3.48769 12.0128C2.64494 11.1699 2.07095 10.0961 1.83828 8.92709C1.6056 7.75806 1.72467 6.54629 2.18045 5.44492C2.63622 4.34355 3.40824 3.40202 4.39894 2.73932C5.38963 2.07661 6.55454 1.72248 7.74643 1.72168C8.93832 1.72088 10.1037 2.07344 11.0953 2.73481C12.0869 3.39618 12.8602 4.33667 13.3174 5.43743C13.7747 6.53818 13.8954 7.74979 13.6643 8.91913C13.4332 10.0885 12.8606 11.1631 12.019 12.0071L12.0133 12.0128L12.0075 12.0174C10.8766 13.1457 9.34413 13.779 7.74666 13.7782C6.14918 13.7773 4.61737 13.1424 3.48769 12.0128Z"
                                fill="black"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Загрузка -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="text-gray-500">Поиск...</div>
            </div>

            <!-- Результаты -->
            <div v-else-if="!loading && total > 0" class="space-y-6">
                <!-- Услуги -->
                <div v-if="results.services && results.services.length > 0">
                    <h2 class="text-lg font-semibold text-black mb-4 flex items-center gap-2">
                        <span class="w-1 h-6 bg-[#6C7B6D] rounded"></span>
                        Услуги ({{ results.services.length }})
                    </h2>
                    <div class="grid gap-4">
                        <router-link
                            v-for="item in results.services"
                            :key="item.id"
                            :to="item.url"
                            class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-transparent hover:border-[#6C7B6D]/20"
                        >
                            <h3 class="font-medium text-base text-black mb-1">{{ item.name }}</h3>
                            <p v-if="item.description" class="text-sm text-gray-600 line-clamp-2">
                                {{ item.description }}
                            </p>
                            <div class="mt-2 text-xs text-[#6C7B6D]">{{ item.type_label }}</div>
                        </router-link>
                    </div>
                </div>

                <!-- Продукты -->
                <div v-if="results.products && results.products.length > 0">
                    <h2 class="text-lg font-semibold text-black mb-4 flex items-center gap-2">
                        <span class="w-1 h-6 bg-[#6C7B6D] rounded"></span>
                        Продукты ({{ results.products.length }})
                    </h2>
                    <div class="grid gap-4">
                        <router-link
                            v-for="item in results.products"
                            :key="item.id"
                            :to="item.url"
                            class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-transparent hover:border-[#6C7B6D]/20"
                        >
                            <h3 class="font-medium text-base text-black mb-1">{{ item.name }}</h3>
                            <p v-if="item.description" class="text-sm text-gray-600 line-clamp-2">
                                {{ item.description }}
                            </p>
                            <div class="mt-2 text-xs text-[#6C7B6D]">{{ item.type_label }}</div>
                        </router-link>
                    </div>
                </div>

                <!-- Кейсы -->
                <div v-if="results.cases && results.cases.length > 0">
                    <h2 class="text-lg font-semibold text-black mb-4 flex items-center gap-2">
                        <span class="w-1 h-6 bg-[#6C7B6D] rounded"></span>
                        Кейсы ({{ results.cases.length }})
                    </h2>
                    <div class="grid gap-4">
                        <router-link
                            v-for="item in results.cases"
                            :key="item.id"
                            :to="item.url"
                            class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-transparent hover:border-[#6C7B6D]/20"
                        >
                            <h3 class="font-medium text-base text-black mb-1">{{ item.name }}</h3>
                            <p v-if="item.description" class="text-sm text-gray-600 line-clamp-2">
                                {{ item.description }}
                            </p>
                            <div class="mt-2 text-xs text-[#6C7B6D]">{{ item.type_label }}</div>
                        </router-link>
                    </div>
                </div>

                <!-- Страницы -->
                <div v-if="results.pages && results.pages.length > 0">
                    <h2 class="text-lg font-semibold text-black mb-4 flex items-center gap-2">
                        <span class="w-1 h-6 bg-[#6C7B6D] rounded"></span>
                        Страницы ({{ results.pages.length }})
                    </h2>
                    <div class="grid gap-4">
                        <router-link
                            v-for="item in results.pages"
                            :key="item.id"
                            :to="item.url"
                            class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-transparent hover:border-[#6C7B6D]/20"
                        >
                            <h3 class="font-medium text-base text-black mb-1">{{ item.name }}</h3>
                            <p v-if="item.description" class="text-sm text-gray-600 line-clamp-2">
                                {{ item.description }}
                            </p>
                            <div class="mt-2 text-xs text-[#6C7B6D]">{{ item.type_label }}</div>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Пустое состояние -->
            <div v-else-if="!loading && searchQuery && total === 0" class="text-center py-12">
                <svg 
                    class="mx-auto mb-4 text-gray-400" 
                    width="64" 
                    height="64" 
                    viewBox="0 0 24 24" 
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path 
                        d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" 
                        stroke="currentColor" 
                        stroke-width="2" 
                        stroke-linecap="round" 
                        stroke-linejoin="round"
                    />
                </svg>
                <p class="text-gray-600 mb-2">Ничего не найдено</p>
                <p class="text-sm text-gray-500">Попробуйте изменить запрос</p>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
    name: 'SearchPage',
    setup() {
        const route = useRoute();
        const router = useRouter();
        const loading = ref(false);
        const results = ref({
            services: [],
            products: [],
            cases: [],
            pages: [],
        });
        const total = ref(0);
        const searchQuery = ref('');
        const localSearchQuery = ref('');
        const searchInput = ref(null);

        const performSearch = async () => {
            const query = localSearchQuery.value.trim();
            if (!query) return;

            router.push({
                name: 'search',
                query: { q: query }
            });
        };

        const clearSearch = () => {
            localSearchQuery.value = '';
            searchQuery.value = '';
            // Очищаем GET параметры из URL
            router.push({
                name: 'search',
                query: {}
            });
            // Очищаем результаты
            results.value = {
                services: [],
                products: [],
                cases: [],
                pages: [],
            };
            total.value = 0;
            // Фокус на поле поиска
            if (searchInput.value) {
                searchInput.value.focus();
            }
        };

        const fetchSearchResults = async (query) => {
            if (!query || query.trim().length < 2) {
                results.value = {
                    services: [],
                    products: [],
                    cases: [],
                    pages: [],
                };
                total.value = 0;
                return;
            }

            loading.value = true;
            try {
                const response = await fetch(`/api/public/search?q=${encodeURIComponent(query)}&limit=20`);
                if (response.ok) {
                    const data = await response.json();
                    results.value = data.data || {
                        services: [],
                        products: [],
                        cases: [],
                        pages: [],
                    };
                    total.value = data.total || 0;
                } else {
                    results.value = {
                        services: [],
                        products: [],
                        cases: [],
                        pages: [],
                    };
                    total.value = 0;
                }
            } catch (error) {
                console.error('Error fetching search results:', error);
                results.value = {
                    services: [],
                    products: [],
                    cases: [],
                    pages: [],
                };
                total.value = 0;
            } finally {
                loading.value = false;
            }
        };

        onMounted(() => {
            const query = route.query.q || '';
            searchQuery.value = query;
            localSearchQuery.value = query;
            if (query) {
                fetchSearchResults(query);
            }
            // Фокус на поле поиска
            if (searchInput.value) {
                searchInput.value.focus();
            }
        });

        watch(() => route.query.q, (newQuery) => {
            searchQuery.value = newQuery || '';
            localSearchQuery.value = newQuery || '';
            if (newQuery) {
                fetchSearchResults(newQuery);
            } else {
                results.value = {
                    services: [],
                    products: [],
                    cases: [],
                    pages: [],
                };
                total.value = 0;
            }
        });

        return {
            loading,
            results,
            total,
            searchQuery,
            localSearchQuery,
            searchInput,
            performSearch,
            clearSearch,
        };
    },
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>



