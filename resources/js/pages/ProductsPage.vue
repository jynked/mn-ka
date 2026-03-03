<template>
    <div class="products-page min-h-screen bg-background">
        <SEOHead
            title="Продукты и услуги - МНКА | Каталог земельных участков"
            description="Полный каталог продуктов и услуг по подбору, оформлению и работе с земельными участками. Профессиональные решения для складов, производства, придорожного сервиса и недвижимости."
            keywords="продукты, услуги, земельные участки, недвижимость, кадастр, оформление земли, подбор участка"
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
                        <span class="text-foreground">Продукты</span>
                    </nav>
                </div>

                <!-- Заголовок -->
                <div class="mt-6 md:mt-8 mb-8">
                    <h1 class="text-2xl md:text-3xl font-semibold text-foreground text-center md:text-left">
                        Продуктовые направления
                    </h1>
                    <p class="text-base md:text-lg text-muted-foreground mt-2 text-center md:text-left">
                        Выберите продукт, который подходит именно вам
                    </p>
                </div>

                <!-- Загрузка -->
                <div v-if="loadingProducts" class="py-12 flex items-center justify-center">
                    <p class="text-muted-foreground">Загрузка...</p>
                </div>

                <!-- Ошибка -->
                <div v-if="error && !loadingProducts" class="py-12">
                    <div class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
                        <p class="text-destructive">{{ error }}</p>
                    </div>
                </div>

                <!-- Контент -->
                <div v-if="!loadingProducts">
                    <!-- Продукты -->
                    <div v-if="products.length > 0" class="mb-12">
                        <h2 class="text-xl md:text-2xl font-semibold text-foreground mb-6">Продуктовые направления</h2>
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

                    <!-- Пустое состояние -->
                    <div v-if="products.length === 0" class="py-12 text-center">
                        <p class="text-muted-foreground">Продукты пока не добавлены</p>
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
    name: 'ProductsPage',
    components: {
        ProductCard,
        SEOHead,
    },
    setup() {
        const store = useStore();
        const error = ref(null);
        const products = ref([]);

        const loadingProducts = computed(() => store.getters.isPublicProductsLoading(false));

        const fetchProducts = async () => {
            error.value = null;
            try {
                const data = await store.dispatch('fetchPublicProducts', { minimal: false });
                products.value = data || [];
            } catch (err) {
                console.error('Error fetching products:', err);
                error.value = err.message || 'Ошибка загрузки продуктов';
            }
        };

        onMounted(() => {
            fetchProducts();
        });

        const canonicalUrl = computed(() => window.location.origin + '/products');
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
                    'name': 'Продукты',
                    'item': window.location.origin + '/products',
                },
            ],
        }));

        return {
            loadingProducts,
            error,
            products,
            canonicalUrl,
            breadcrumbSchema,
        };
    },
};
</script>

