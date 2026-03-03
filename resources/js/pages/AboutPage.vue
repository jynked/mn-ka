<template>
    <div class="about-page min-h-screen bg-background">
        <SEOHead
            title="О компании МНКА - Профессиональные услуги по работе с земельными участками"
            description="МНКА - ведущая компания по подбору и оформлению земельных участков. Узнайте о нашей команде, опыте работы и подходе к решению задач клиентов. Более 10 лет на рынке недвижимости."
            keywords="о компании, команда, услуги, земельные участки, опыт работы, профессионалы, недвижимость"
            :canonical="canonicalUrl"
            :schema="aboutSchema"
        />
        
        <div class="w-full px-3 sm:px-4 md:px-5">
            <div class="w-full max-w-[1200px] mx-auto">
                <!-- Хлебные крошки -->
                <div class="hidden md:block mt-4">
                    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
                        <router-link to="/" class="hover:text-foreground transition-colors">Главная</router-link>
                        <span>/</span>
                        <span class="text-foreground">О компании</span>
                    </nav>
                </div>

                <!-- Заголовок страницы -->
                <h1 class="text-2xl md:text-3xl font-semibold text-[#424448] mt-6 md:mt-8 mb-6 md:mb-8 text-center md:text-left">
                    О компании
                </h1>

                <!-- Баннер -->
                <div class="w-full h-[250px] md:h-[300px] lg:h-[400px] bg-[#BED2AD] rounded-lg md:rounded-xl mb-8 md:mb-10 overflow-hidden relative">
                    <img 
                        v-if="bannerImage" 
                        :src="bannerImage" 
                        alt="О компании"
                        class="w-full h-full object-cover"
                    />
                    <div v-if="bannerOverlay" class="absolute inset-0 bg-black/50 z-10"></div>
                </div>

                <!-- Текст о компании -->
                <div 
                    v-if="description" 
                    class="max-w-[720px] mx-auto mb-12 md:mb-16 text-center"
                    v-html="description"
                ></div>

                <!-- Статистика -->
                <div class="flex flex-col md:flex-row justify-center items-center gap-8 md:gap-12 lg:gap-16 mb-16 md:mb-20 flex-wrap">
                    <div 
                        v-for="(stat, index) in statistics" 
                        :key="index"
                        class="flex items-center gap-5"
                    >
                        <div class="w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-lg overflow-hidden">
                            <img 
                                :src="stat.icon" 
                                :alt="stat.text"
                                class="w-full h-full object-contain"
                            />
                        </div>
                        <p class="font-semibold text-lg md:text-xl lg:text-2xl text-black max-w-[250px]">
                            {{ stat.text }}
                        </p>
                    </div>
                </div>

                <!-- Виды услуг -->
                <div v-if="services.length > 0" class="mb-16 md:mb-20">
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#424448] text-center mb-8 md:mb-10">
                        Виды услуг
                    </h2>
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

                <!-- Продуктовые решения -->
                <div v-if="products.length > 0" class="mb-16 md:mb-20">
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#424448] text-center mb-8 md:mb-10">
                        Продуктовые решения
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                        <ProductCard
                            v-for="product in products"
                            :key="`product-${product.id}`"
                            :decision="product"
                            slug="products"
                            class="h-full"
                        />
                    </div>
                </div>

                <!-- Кому мы помогаем -->
                <div v-if="clients.length > 0" class="mb-16 md:mb-20">
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#424448] text-center mb-8 md:mb-10">
                        Кому мы помогаем
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                        <div
                            v-for="client in clients"
                            :key="client.id"
                            class="bg-[#F4F6FC] rounded-[18px] p-6 flex justify-between items-start gap-5 hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                        >
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg md:text-xl text-[#424448] mb-3 md:mb-4">
                                    {{ client.title }}
                                </h3>
                                <p class="text-base text-[#424448]">
                                    {{ client.description }}
                                </p>
                            </div>
                            <div class="w-[58px] h-[49px] flex-shrink-0">
                                <img 
                                    :src="client.icon" 
                                    :alt="client.title"
                                    class="w-full h-full object-contain"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Как мы работаем - используем существующий компонент -->
                <div class="mb-16 md:mb-20">
                    <HowWork />
                </div>

                <!-- Частые вопросы - используем существующий компонент -->
                <div class="mb-16 md:mb-20">
                    <Faq />
                </div>

                <!-- Наша команда -->
                <div v-if="team.length > 0" class="mb-16 md:mb-20">
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#424448] text-center mb-8 md:mb-10">
                        Наша команда
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                        <div
                            v-for="member in team"
                            :key="member.id"
                            class="text-center"
                        >
                            <div class="w-full aspect-[245/272] rounded-[11px] overflow-hidden mb-4 bg-[#F4F6FC]">
                                <img 
                                    :src="member.photo" 
                                    :alt="member.name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <h3 class="font-semibold text-base md:text-lg text-[#424448] mb-1 md:mb-2">
                                {{ member.name }}
                            </h3>
                            <p class="text-sm md:text-base text-[#424448]">
                                {{ member.position }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Почему выбирают нас - используем компонент с главной страницы -->
                <div class="mb-16 md:mb-20">
                    <WhyChooseUs />
                </div>

                <!-- Форма обратной связи -->
                <div class="bg-[#F4F6FC] rounded-[32px] p-6 md:p-10 lg:p-12 mt-12 md:mt-16 mb-12 md:mb-20">
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#424448] text-center mb-3 md:mb-4">
                        Оставьте заявку
                    </h2>
                    <p class="text-base md:text-lg text-[#424448] text-center mb-8 md:mb-10 max-w-[600px] mx-auto">
                        Мы бесплатно подберем участок и покажем как его оформить под ваш проект
                    </p>
                    <FeedbackForm 
                        title=""
                        description=""
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import SEOHead from '../components/SEOHead.vue';
import ProductCard from '../components/public/ProductCard.vue';
import HowWork from '../components/public/HowWork.vue';
import Faq from '../components/public/Faq.vue';
import FeedbackForm from '../components/public/FeedbackForm.vue';
import WhyChooseUs from '../components/public/WhyChooseUs.vue';

export default {
    name: 'AboutPage',
    components: {
        SEOHead,
        ProductCard,
        HowWork,
        Faq,
        FeedbackForm,
        WhyChooseUs,
    },
    setup() {
        const store = useStore();
        const loading = ref(true);
        const bannerImage = ref(null);
        const bannerOverlay = ref(false);
        const description = ref('');
        const statistics = ref([]);
        const services = ref([]);
        const products = ref([]);
        const clients = ref([]);
        const team = ref([]);

        // Загрузка настроек страницы "О нас"
        const fetchAboutSettings = async () => {
            try {
                const response = await fetch('/api/public/about-settings', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    const settings = data.data || {};

                    // Баннер
                    bannerImage.value = settings.banner_image || null;
                    bannerOverlay.value = settings.banner_overlay || false;

                    // Описание
                    description.value = settings.description || '';

                    // Статистика
                    statistics.value = settings.statistics || [];

                    // Клиенты
                    clients.value = (settings.clients || []).map((client, index) => ({
                        id: index + 1,
                        ...client,
                    }));

                    // Команда
                    team.value = (settings.team || []).map((member, index) => ({
                        id: index + 1,
                        ...member,
                    }));
                }
            } catch (err) {
                console.error('Error fetching about settings:', err);
            }
        };

        // Загрузка услуг из store
        const fetchServices = async () => {
            try {
                const data = await store.dispatch('fetchPublicServices', { minimal: false });
                services.value = (data || []).slice(0, 8); // Ограничиваем до 8 услуг
            } catch (err) {
                console.error('Error fetching services:', err);
            }
        };

        // Загрузка продуктов из store
        const fetchProducts = async () => {
            try {
                const data = await store.dispatch('fetchPublicProducts', { minimal: false });
                products.value = (data || []).slice(0, 8); // Ограничиваем до 8 продуктов
            } catch (err) {
                console.error('Error fetching products:', err);
            }
        };

        onMounted(() => {
            loading.value = true;
            Promise.all([
                fetchAboutSettings(),
                fetchServices(),
                fetchProducts(),
            ]).finally(() => {
                loading.value = false;
            });
        });

        // SEO data
        const canonicalUrl = computed(() => {
            return window.location.origin + '/about';
        });

        const aboutSchema = computed(() => {
            return {
                '@context': 'https://schema.org',
                '@type': 'AboutPage',
                'name': 'О компании',
                'description': 'Узнайте о нашей компании, команде и подходе к работе',
                'url': canonicalUrl.value,
            };
        });

        return {
            loading,
            bannerImage,
            bannerOverlay,
            description,
            statistics,
            services,
            products,
            clients,
            team,
            canonicalUrl,
            aboutSchema,
        };
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap');
</style>

