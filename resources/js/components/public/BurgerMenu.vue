<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-full"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-300 ease-out"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-full"
    >
        <div 
            v-if="isOpen"
            class="fixed top-0 left-0 w-full h-full overflow-y-auto bg-[#6C7B6D] z-[9999] opacity-100"
            style="background-color: #6C7B6D;"
            @click.self="$emit('close')"
        >
            <div class="w-full max-w-[1200px] mx-auto px-4 sm:px-[15px]">
                <div class="flex justify-between items-center mt-8 sm:mt-12 gap-3 sm:gap-5 burger-menu-content">
                    <div class="w-full md:w-1/4 pr-0">
                        <div class="relative">
                            <input 
                                type="text" 
                                v-model="searchQuery"
                                @keypress.enter="handleSearch"
                                placeholder="Поиск по сайту" 
                                class="w-full py-2.5 sm:py-[10px] pr-10 pl-3 sm:pl-[10px] border-0 rounded-lg outline-none text-sm sm:text-base bg-gray-200 text-gray-700 placeholder:text-gray-500 focus:bg-gray-100 focus:ring-2 focus:ring-white/30 transition-all duration-200 shadow-inner"
                            >
                            <button
                                @click="handleSearch"
                                type="button"
                                class="absolute right-3 sm:right-[10px] top-1/2 -translate-y-1/2 flex items-center cursor-pointer z-10"
                            >
                                <svg 
                                    width="18" 
                                    height="18"
                                    viewBox="0 0 18 18" 
                                    fill="none" 
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path 
                                        fill-rule="evenodd" 
                                        clip-rule="evenodd"
                                        d="M11.015 12.3306C9.66079 13.4127 7.94372 13.9351 6.21641 13.7906C4.48911 13.6461 2.88269 12.8457 1.72708 11.5537C0.571478 10.2617 -0.0455851 8.57623 0.00262643 6.84347C0.050838 5.1107 0.760665 3.46216 1.98632 2.23643C3.21198 1.01071 4.86042 0.300841 6.59309 0.252627C8.32576 0.204412 10.0111 0.821511 11.303 1.97718C12.5949 3.13285 13.3953 4.73937 13.5398 6.46677C13.6843 8.19418 13.1619 9.91134 12.0799 11.2656L17.2602 16.4451C17.3342 16.5141 17.3936 16.5973 17.4348 16.6897C17.476 16.7822 17.4981 16.882 17.4999 16.9831C17.5017 17.0843 17.4831 17.1848 17.4452 17.2787C17.4073 17.3725 17.3509 17.4577 17.2793 17.5293C17.2077 17.6008 17.1225 17.6573 17.0287 17.6952C16.9349 17.7331 16.8344 17.7517 16.7332 17.7499C16.632 17.7481 16.5322 17.726 16.4398 17.6848C16.3474 17.6436 16.2642 17.5842 16.1952 17.5102L11.015 12.3306ZM3.05173 10.7612C2.31432 10.0237 1.81208 9.0841 1.60849 8.0612C1.4049 7.0383 1.50909 5.978 1.90789 5.01431C2.30669 4.05061 2.98221 3.22677 3.84907 2.6469C4.71593 2.06704 5.73522 1.75717 6.77812 1.75647C7.82103 1.75577 8.84073 2.06426 9.70837 2.64296C10.576 3.22166 11.2526 4.04459 11.6527 5.00775C12.0528 5.97091 12.1585 7.03107 11.9562 8.05424C11.754 9.07741 11.253 10.0177 10.5166 10.7562L10.5116 10.7612L10.5066 10.7652C9.51705 11.7525 8.17612 12.3067 6.77833 12.3059C5.38053 12.3052 4.0402 11.7496 3.05173 10.7612Z"
                                        fill="#909090"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button 
                        type="button"
                        @click="$emit('close')"
                        class="cursor-pointer bg-transparent border-0 p-2 sm:p-0 w-8 h-8 sm:w-10 sm:h-10 md:w-[40px] md:h-[40px] flex-shrink-0"
                    >
                        <svg 
                            width="40" 
                            height="40" 
                            viewBox="0 0 40 40"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M10.4 32L8 29.6L17.6 20L8 10.4L10.4 8L20 17.6L29.6 8L32 10.4L22.4 20L32 29.6L29.6 32L20 22.4L10.4 32Z"
                                fill="white"
                            />
                        </svg>
                    </button>
                </div>
                
                <div class="mt-6 sm:mt-12 burger-menu-content">
                    <div class="font-medium text-xl sm:text-2xl md:text-[28px] leading-6 sm:leading-[34px] text-white mb-3 sm:mb-2">Все услуги</div>
                    <div v-if="loadingServices" class="text-white/70 text-sm">Загрузка...</div>
                    <div v-else-if="services.length === 0" class="text-white/70 text-sm">Услуги не найдены</div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                        <div v-for="service in services" :key="service.slug" class="w-full">
                            <router-link 
                                :to="service.slug" 
                                @click="$emit('close')"
                                class="font-normal text-sm md:text-[14px] leading-5 md:leading-[17px] text-[#F4F6FC] no-underline block mb-3 sm:mb-2.5 py-1 hover:text-white transition-colors"
                            >
                                {{ service.name }}
                            </router-link>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 sm:mt-12 pb-6 sm:pb-0 burger-menu-content">
                    <div class="font-medium text-xl sm:text-2xl md:text-[28px] leading-6 sm:leading-[34px] text-white mb-3 sm:mb-2">Продуктовые направления</div>
                    <div v-if="loadingProducts" class="text-white/70 text-sm">Загрузка...</div>
                    <div v-else-if="products.length === 0" class="text-white/70 text-sm">Продукты не найдены</div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                        <div v-for="product in products" :key="product.slug" class="w-full">
                            <router-link 
                                :to="product.slug" 
                                @click="$emit('close')"
                                class="font-normal text-sm md:text-[14px] leading-5 md:leading-[17px] text-[#F4F6FC] no-underline block mb-3 sm:mb-2.5 py-1 hover:text-white transition-colors"
                            >
                                {{ product.name }}
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';

export default {
    name: 'BurgerMenu',
    props: {
        isOpen: {
            type: Boolean,
            required: true,
        },
    },
    emits: ['close'],
    setup(props, { emit }) {
        const router = useRouter();
        const store = useStore();
        const searchQuery = ref('');
        const services = ref([]);
        const products = ref([]);
        const isMobile = computed(() => typeof window !== 'undefined' && window.innerWidth <= 767);
        
        // Состояния загрузки из store
        const loadingServices = computed(() => store.getters.isPublicServicesLoading(true));
        const loadingProducts = computed(() => store.getters.isPublicProductsLoading(false));

        // Загрузка услуг из store
        const fetchServices = async () => {
            try {
                const servicesList = await store.dispatch('fetchPublicServices', { minimal: true });
                // Сортируем услуги по полю order (если есть)
                services.value = servicesList
                    .sort((a, b) => {
                        const orderA = a.order ?? 999999;
                        const orderB = b.order ?? 999999;
                        return orderA - orderB;
                    })
                    .map(service => {
                        const serviceSlug = service.slug ? service.slug.replace(/^\/+/, '') : '';
                        return {
                            slug: serviceSlug ? `/services/${serviceSlug}` : '/services',
                            name: service.name || service.title,
                        };
                    });
            } catch (err) {
                console.error('Error fetching services:', err);
            }
        };

        // Загрузка продуктов из store
        const fetchProducts = async () => {
            try {
                const productsList = await store.dispatch('fetchPublicProducts', { minimal: false });
                
                // Преобразуем в плоский список, как услуги
                products.value = productsList
                    .sort((a, b) => {
                        const orderA = a.order ?? 999999;
                        const orderB = b.order ?? 999999;
                        return orderA - orderB;
                    })
                    .map(product => {
                        const productSlug = product.slug ? product.slug.replace(/^\/+/, '') : '';
                        return {
                            slug: productSlug ? `/products/${productSlug}` : '/products',
                            name: product.name || product.title,
                        };
                    });
            } catch (err) {
                console.error('Error fetching products:', err);
            }
        };

        // Загрузка данных при открытии меню
        watch(() => props.isOpen, (isOpen) => {
            if (isOpen) {
                fetchServices();
                fetchProducts();
            }
        });

        // Загрузка при монтировании (если меню уже открыто)
        onMounted(() => {
            if (props.isOpen) {
                fetchServices();
                fetchProducts();
            }
        });

        const handleSearch = () => {
            if (searchQuery.value.trim()) {
                const query = searchQuery.value.trim();
                searchQuery.value = '';
                router.push({
                    name: 'search',
                    query: { q: query }
                });
                // Закрываем меню после перехода на страницу поиска
                emit('close');
            }
        };

        return {
            searchQuery,
            services,
            products,
            loadingServices,
            loadingProducts,
            isMobile,
            handleSearch,
        };
    },
};
</script>

