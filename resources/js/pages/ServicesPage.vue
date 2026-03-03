<template>
    <div class="services-page min-h-screen bg-background">
        <SEOHead
            title="Услуги по работе с земельными участками - МНКА"
            description="Профессиональные услуги по подбору, оформлению и кадастровым работам с земельными участками. Консультации, оформление документов, кадастровый учет и регистрация прав."
            keywords="услуги, кадастр, земельные участки, консультации, оформление документов, кадастровый учет, регистрация прав"
            :canonical="canonicalUrl"
            :schema="breadcrumbSchema"
        />
        
        <div class="w-full px-3 sm:px-4 md:px-5">
            <div class="w-full max-w-[1200px] mx-auto">
                <!-- Хлебные крошки -->
                <div class="hidden md:block mt-4">
                    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
                        <router-link to="/" class="hover:text-foreground transition-colors">Главная</router-link>
                        <span>/</span>
                        <span class="text-foreground">Услуги</span>
                    </nav>
                </div>

                <!-- Заголовок -->
                <div class="mt-6 md:mt-8 mb-8">
                    <h1 class="text-2xl md:text-3xl font-semibold text-foreground text-center md:text-left">
                        Наши услуги
                    </h1>
                    <p class="text-base md:text-lg text-muted-foreground mt-2 text-center md:text-left">
                        Выберите услугу, которая подходит именно вам
                    </p>
                </div>

                <!-- Загрузка -->
                <div v-if="loadingServices" class="py-12 flex items-center justify-center">
                    <p class="text-muted-foreground">Загрузка...</p>
                </div>

                <!-- Ошибка -->
                <div v-if="error && !loadingServices" class="py-12">
                    <div class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
                        <p class="text-destructive">{{ error }}</p>
                    </div>
                </div>

                <!-- Контент -->
                <div v-if="!loadingServices">
                    <!-- Услуги -->
                    <div v-if="services.length > 0" class="mb-12">
                        <h2 class="text-xl md:text-2xl font-semibold text-foreground mb-6">Все услуги</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                            <ProductCard
                                v-for="service in services"
                                :key="`service-${service.id}`"
                                :decision="service"
                                slug="services"
                                class="h-full"
                            />
                        </div>
                    </div>

                    <!-- Пустое состояние -->
                    <div v-if="services.length === 0" class="py-12 text-center">
                        <p class="text-muted-foreground">Услуги пока не добавлены</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import ProductCard from '../components/public/ProductCard.vue';
import SEOHead from '../components/SEOHead.vue';

export default {
    name: 'ServicesPage',
    components: {
        ProductCard,
        SEOHead,
    },
    setup() {
        const store = useStore();
        const error = ref(null);
        const services = ref([]);

        const loadingServices = computed(() => store.getters.isPublicServicesLoading(true));

        const fetchServices = async () => {
            error.value = null;
            try {
                const servicesList = await store.dispatch('fetchPublicServices', { minimal: true });
                console.log(servicesList);
                    // Сортируем услуги по полю order (если есть)
                services.value = (servicesList || []).sort((a, b) => {
                        const orderA = a.order ?? 999999;
                        const orderB = b.order ?? 999999;
                        return orderA - orderB;
                    });
            } catch (err) {
                console.error('Error fetching services:', err);
                error.value = err.message || 'Ошибка загрузки услуг';
            }
        };

        onMounted(() => {
            fetchServices();
        });

        const canonicalUrl = computed(() => window.location.origin + '/services');
        const breadcrumbSchema = computed(() => ({
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            'itemListElement': [
                {
                    '@type': 'ListItem',
                    'position': 1,
                    'name': 'Главная',
                    'item': window.location.origin,
                },
                {
                    '@type': 'ListItem',
                    'position': 2,
                    'name': 'Услуги',
                    'item': window.location.origin + '/services',
                },
            ],
        }));

        return {
            loadingServices,
            error,
            services,
            canonicalUrl,
            breadcrumbSchema,
        };
    },
};
</script>





