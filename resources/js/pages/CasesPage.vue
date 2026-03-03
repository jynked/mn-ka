<template>
    <div class="cases-page min-h-screen bg-white">
        <SEOHead
            title="Кейсы и объекты - МНКА | Примеры реализованных проектов"
            description="Портфолио успешно реализованных проектов по подбору и оформлению земельных участков. Реальные кейсы складов, производств, придорожного сервиса и других объектов."
            keywords="кейсы, проекты, примеры работ, объекты, портфолио, реализованные проекты, земельные участки"
            :canonical="canonicalUrl"
            :schema="casesSchema"
        />
        
        <div class="w-full px-3 sm:px-4 md:px-5">
            <div class="w-full max-w-[1200px] mx-auto py-8 md:py-12">
                <!-- Заголовок и фильтры -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 md:mb-12">
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-black">
                        Наши кейсы
                    </h1>

                    <!-- Кнопка фильтров -->
                    <button
                        @click="showFilters = true"
                        class="inline-flex items-center gap-2 px-4 py-2 text-black rounded-lg hover:bg-gray-100 transition-colors font-medium text-sm sm:text-base"
                    >
                        <svg 
                            width="20" 
                            height="20" 
                            viewBox="0 0 28 30" 
                            fill="none" 
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5"
                        >
                            <path
                                d="M5.12793 3.49121L12 11.8545V21.0713L12.0781 21.1445L15.5771 24.4775L16 24.8799V11.8369L22.8721 3.49219L23.208 3.08301H4.79199L5.12793 3.49121ZM9.00098 12.8252L8.94434 12.7559L0.5625 2.54102C0.321139 2.24571 0.213888 1.87335 0.260742 1.50586C0.307646 1.13894 0.504723 0.800599 0.814453 0.567383C1.11116 0.360285 1.42861 0.25 1.75195 0.25H26.248C26.5711 0.25 26.8881 0.36058 27.1846 0.567383C27.4947 0.800614 27.6923 1.13869 27.7393 1.50586C27.7861 1.87335 27.6789 2.24571 27.4375 2.54102L19.0557 12.7559L18.999 12.8252V28.1465L19.002 28.1641C19.0611 28.5862 18.9122 29.0337 18.5781 29.3242L18.7422 29.5127L18.7383 29.5088L18.5771 29.3242L18.5693 29.332C18.4312 29.4638 18.2662 29.5687 18.084 29.6406C17.9018 29.7126 17.7057 29.75 17.5078 29.75C17.3101 29.7499 17.1147 29.7125 16.9326 29.6406C16.796 29.5866 16.6688 29.5143 16.5557 29.4258L16.4473 29.332L9.43066 22.6494L9.42871 22.6475L9.31445 22.5273C9.208 22.4022 9.12497 22.2609 9.06934 22.1094C8.99514 21.9071 8.97129 21.6915 8.99902 21.4795L9.00098 21.4629V12.8252Z"
                                fill="currentColor"
                            />
                        </svg>
                        <span>Фильтры</span>
                    </button>
                </div>

                <!-- Загрузка -->
                <div v-if="loading" class="py-12 flex items-center justify-center">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#688E67] mb-4"></div>
                        <p class="text-gray-600">Загрузка кейсов...</p>
                    </div>
                </div>

                <!-- Ошибка -->
                <div v-if="error && !loading" class="py-12">
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-600">{{ error }}</p>
                    </div>
                </div>

                <!-- Список кейсов -->
                <div v-if="!loading && !error">
                    <div v-if="cases.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-12">
                        <CaseCard
                            v-for="caseItem in displayedCases"
                            :key="caseItem.id"
                            :case-item="caseItem"
                        />
                    </div>

                    <!-- Пустое состояние -->
                    <div v-else class="py-12 text-center">
                        <p class="text-gray-600 text-lg">Кейсы пока не добавлены</p>
                    </div>
                </div>

                <!-- Пагинация -->
                <div v-if="!loading && !error && totalPages > 1" class="flex justify-center items-center gap-2 mt-12">
                    <button
                        v-for="pageNum in getPageNumbers()"
                        :key="pageNum"
                        @click="goToPage(pageNum)"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors min-w-[40px]',
                            isPageActive(pageNum)
                                ? 'bg-[#688E67] text-white'
                                : 'bg-white text-black border border-gray-300 hover:bg-gray-100'
                        ]"
                    >
                        {{ pageNum }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно фильтров -->
        <CaseFiltersModal
            v-if="showFilters"
            :cases="cases"
            @close="showFilters = false"
        />
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import SEOHead from '../components/SEOHead.vue';
import CaseCard from '../components/public/CaseCard.vue';
import CaseFiltersModal from '../components/public/CaseFiltersModal.vue';

export default {
    name: 'CasesPage',
    components: {
        SEOHead,
        CaseCard,
        CaseFiltersModal,
    },
    setup() {
        const loading = ref(false);
        const error = ref(null);
        const cases = ref([]);
        const showFilters = ref(false);
        const currentPage = ref(1);
        const itemsPerPage = ref(6);
        const totalPages = ref(1);

        const fetchCases = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                const response = await fetch('/api/public/cases', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Ошибка загрузки кейсов');
                }

                const data = await response.json();
                cases.value = data.data || [];
                
                // Отладка: проверим структуру данных для первого кейса
                if (cases.value.length > 0) {
                    console.log('First case data:', JSON.stringify(cases.value[0], null, 2));
                }
                
                // Вычисляем общее количество страниц
                totalPages.value = Math.ceil(cases.value.length / itemsPerPage.value);
            } catch (err) {
                console.error('Error fetching cases:', err);
                error.value = err.message || 'Ошибка загрузки кейсов';
            } finally {
                loading.value = false;
            }
        };

        // Вычисляем кейсы для текущей страницы
        const displayedCases = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage.value;
            const end = start + itemsPerPage.value;
            return cases.value.slice(start, end);
        });

        // Генерируем номера страниц для пагинации
        const getPageNumbers = () => {
            const pages = [];
            
            if (totalPages.value <= 5) {
                // Если страниц 5 или меньше, показываем все
                for (let i = 1; i <= totalPages.value; i++) {
                    pages.push(i);
                }
            } else {
                // Показываем первые 5 страниц
                for (let i = 1; i <= 5; i++) {
                    pages.push(i);
                }
                // Показываем диапазон для остальных страниц
                pages.push(`6-${totalPages.value}`);
            }
            
            return pages;
        };

        // Проверяем, активна ли страница (для диапазонов проверяем, входит ли текущая страница в диапазон)
        const isPageActive = (pageNum) => {
            if (typeof pageNum === 'number') {
                return pageNum === currentPage.value;
            }
            if (typeof pageNum === 'string' && pageNum.includes('-')) {
                const [start, end] = pageNum.split('-').map(Number);
                return currentPage.value >= start && currentPage.value <= (end || totalPages.value);
            }
            return false;
        };

        const goToPage = (pageNum) => {
            let targetPage;
            
            // Если это диапазон страниц (например, "6-10"), переходим на первую страницу диапазона
            if (typeof pageNum === 'string' && pageNum.includes('-')) {
                const [start] = pageNum.split('-').map(Number);
                targetPage = start;
            } else if (typeof pageNum === 'number') {
                targetPage = pageNum;
            } else {
                return;
            }
            
            if (targetPage >= 1 && targetPage <= totalPages.value) {
                currentPage.value = targetPage;
                // Прокрутка вверх страницы
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };

        onMounted(() => {
            fetchCases();
        });

        // SEO data
        const canonicalUrl = computed(() => {
            return window.location.origin + '/cases';
        });

        const casesSchema = computed(() => {
            return {
                '@context': 'https://schema.org',
                '@type': 'CollectionPage',
                'name': 'Наши кейсы',
                'description': 'Примеры успешно реализованных проектов по подбору и оформлению земельных участков',
                'url': canonicalUrl.value,
            };
        });

        return {
            loading,
            error,
            cases,
            displayedCases,
            showFilters,
            currentPage,
            totalPages,
            getPageNumbers,
            isPageActive,
            goToPage,
            canonicalUrl,
            casesSchema,
        };
    },
};
</script>
