<template>
    <div class="product-page min-h-screen bg-background">
        <SEOHead
            v-if="product"
            :title="productTitle"
            :description="productDescription"
            :keywords="productKeywords"
            :og-image="productImage"
            :canonical="canonicalUrl"
            :schema="productSchema"
        />
        
        <div class="w-full px-3 sm:px-4 md:px-5">
            <div class="w-full max-w-[1200px] mx-auto">
            <!-- Skeleton Loader -->
            <ProductSkeleton v-if="loading && !product" />

            <!-- Ошибка -->
            <div v-if="error && !loading" class="py-12">
                <div class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
                    <p class="text-destructive">{{ error }}</p>
                </div>
            </div>

            <!-- Контент продукта -->
            <div v-if="product && !loading">
                <!-- Хлебные крошки (скрыты на мобильных) -->
                <div class="hidden md:block mt-4">
                    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
                        <router-link to="/" class="hover:text-foreground transition-colors">Главная</router-link>
                        <span>/</span>
                        <router-link to="/products" class="hover:text-foreground transition-colors">Продукты</router-link>
                        <span>/</span>
                        <span class="text-foreground">{{ product.name }}</span>
                    </nav>
                </div>

                <!-- Заголовок и изображение -->
                <div class="mt-6 md:mt-8 grid md:grid-cols-2 gap-6 md:gap-8 items-center">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-semibold text-foreground mb-3 md:text-left text-center">
                            {{ product.page_title || product.name }}
                        </h1>
                        <div 
                            v-if="product.page_subtitle || product.description" 
                            class="text-base md:text-lg text-muted-foreground leading-relaxed md:text-left text-center mb-4"
                            v-html="product.page_subtitle || (typeof product.description === 'string' ? product.description : product.description?.ru || '')"
                        ></div>
                        <div class="flex justify-center md:justify-start">
                            <a
                                v-if="product.cta_link && product.cta_text"
                                :href="product.cta_link"
                                class="px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base inline-block"
                            >
                                {{ product.cta_text }}
                            </a>
                            <button
                                v-else
                                @click="showFeedbackModal = true"
                                class="px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base"
                            >
                                Получить услугу
                            </button>
                        </div>
                    </div>
                    <div class="relative">
                        <LazyImage
                            v-if="product.image"
                            :src="product.image.url"
                            :alt="product.name"
                            image-class="w-full h-auto rounded-lg shadow-md"
                            container-class="relative w-full"
                            :eager="true"
                        />
                        <!-- SVG иконка поделиться (опционально) -->
                        <div 
                            @click="handleShare"
                            class="absolute top-4 right-4 cursor-pointer hidden md:block hover:opacity-80 transition-opacity"
                            title="Поделиться"
                        >
                            <svg width="44" height="48" viewBox="0 0 44 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M35.3327 15.2C39.0145 15.2 41.9993 12.2451 41.9993 8.60002C41.9993 4.95493 39.0145 2 35.3327 2C31.6508 2 28.666 4.95493 28.666 8.60002C28.666 12.2451 31.6508 15.2 35.3327 15.2Z"
                                    stroke="#688E67" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                <path
                                    d="M8.66664 30.5998C12.3485 30.5998 15.3333 27.6449 15.3333 23.9998C15.3333 20.3547 12.3485 17.3997 8.66664 17.3997C4.98476 17.3997 2 20.3547 2 23.9998C2 27.6449 4.98476 30.5998 8.66664 30.5998Z"
                                    stroke="#688E67" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                <path
                                    d="M35.3327 46C39.0145 46 41.9993 43.0451 41.9993 39.4C41.9993 35.7549 39.0145 32.8 35.3327 32.8C31.6508 32.8 28.666 35.7549 28.666 39.4C28.666 43.0451 31.6508 46 35.3327 46Z"
                                    stroke="#688E67" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                <g filter="url(#filter0_d_743_2384)">
                                    <path d="M14.2227 27.3001L29.7782 36.1002M29.7782 11.9001L14.2227 20.7001"
                                          stroke="#688E67" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"
                                          shape-rendering="crispEdges"/>
                                </g>
                                <defs>
                                    <filter id="filter0_d_743_2384" x="8.22266" y="9.89972" width="27.5557" height="36.2008"
                                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                        <feColorMatrix in="SourceAlpha" type="matrix"
                                                       values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                                       result="hardAlpha"/>
                                        <feOffset dy="4"/>
                                        <feGaussianBlur stdDeviation="2"/>
                                        <feComposite in2="hardAlpha" operator="out"/>
                                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                                        <feBlend mode="normal" in2="BackgroundImageFix"
                                                 result="effect1_dropShadow_743_2384"/>
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_743_2384"
                                                 result="shape"/>
                                    </filter>
                                </defs>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- HTML контент продукта -->
                <div v-if="product.html_content" class="mt-8 product-html-content" v-html="product.html_content"></div>

                <!-- Информация об услугах
                <div class="mt-8 py-5 relative">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex flex-col gap-2">
                            <span class="text-sm md:text-base text-foreground">Необходимые услуги уже включены:</span>
                            <span class="text-sm md:text-base text-[#688E67]">по умолчанию, но можно редактировать</span>
                        </div>
                        <button
                            v-if="windowWidth > 767"
                            @click="showInfoModal = true"
                            class="flex items-center justify-center w-10 h-10 hover:bg-muted/10 rounded-lg transition-colors"
                            type="button"
                        >
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M0 2.22222V37.7778C0 38.3671 0.234126 38.9324 0.650874 39.3491C1.06762 39.7659 1.63285 40 2.22222 40H37.7778C38.3671 40 38.9324 39.7659 39.3491 39.3491C39.7659 38.9324 40 38.3671 40 37.7778V2.22222C40 1.63285 39.7659 1.06762 39.3491 0.650874C38.9324 0.234126 38.3671 0 37.7778 0H2.22222C1.63285 0 1.06762 0.234126 0.650874 0.650874C0.234126 1.06762 0 1.63285 0 2.22222ZM17.7778 10.8889C17.7778 9.78432 18.6732 8.88889 19.7778 8.88889H20.2222C21.3268 8.88889 22.2222 9.78432 22.2222 10.8889V11.3333C22.2222 12.4379 21.3268 13.3333 20.2222 13.3333H19.7778C18.6732 13.3333 17.7778 12.4379 17.7778 11.3333V10.8889ZM17.7778 19.7778C17.7778 18.6732 18.6732 17.7778 19.7778 17.7778H20.2222C21.3268 17.7778 22.2222 18.6732 22.2222 19.7778V29.1111C22.2222 30.2157 21.3268 31.1111 20.2222 31.1111H19.7778C18.6732 31.1111 17.7778 30.2157 17.7778 29.1111V19.7778Z"
                                    fill="#688E67"/>
                            </svg>
                        </button>
                    </div>

                    <div id="product-form-container" ref="formContainer" class="mt-8" :class="windowWidth > 767 ? 'px-5' : ''">
                        <component :is="currentStageComponent" :product="product" @update-services="handleServicesUpdate" @update-form="handleFormUpdate"></component>
                    </div>

                    <div class="text-center w-full mt-4 flex items-center justify-center gap-3">
                        <span class="text-sm md:text-base text-muted-foreground">
                            Выбрано {{ formattedCount }}
                        </span>
                        <button
                            v-if="windowWidth <= 767"
                            @click="showInfoModal = true"
                            class="flex items-center justify-center"
                            type="button"
                        >
                            <svg width="24" height="24" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M0 2.22222V37.7778C0 38.3671 0.234126 38.9324 0.650874 39.3491C1.06762 39.7659 1.63285 40 2.22222 40H37.7778C38.3671 40 38.9324 39.7659 39.3491 39.3491C39.7659 38.9324 40 38.3671 40 37.7778V2.22222C40 1.63285 39.7659 1.06762 39.3491 0.650874C38.9324 0.234126 38.3671 0 37.7778 0H2.22222C1.63285 0 1.06762 0.234126 0.650874 0.650874C0.234126 1.06762 0 1.63285 0 2.22222ZM17.7778 10.8889C17.7778 9.78432 18.6732 8.88889 19.7778 8.88889H20.2222C21.3268 8.88889 22.2222 9.78432 22.2222 10.8889V11.3333C22.2222 12.4379 21.3268 13.3333 20.2222 13.3333H19.7778C18.6732 13.3333 17.7778 12.4379 17.7778 11.3333V10.8889ZM17.7778 19.7778C17.7778 18.6732 18.6732 17.7778 19.7778 17.7778H20.2222C21.3268 17.7778 22.2222 18.6732 22.2222 19.7778V29.1111C22.2222 30.2157 21.3268 31.1111 20.2222 31.1111H19.7778C18.6732 31.1111 17.7778 30.2157 17.7778 29.1111V19.7778Z"
                                    fill="#688E67"/>
                            </svg>
                        </button>
                    </div>

                    <div v-if="stage === 'forms'" class="mt-6 px-5">
                        <ConsentCheckbox 
                            v-model="consentGiven" 
                            :error="consentError"
                            @update:error="consentError = $event"
                        />
                    </div>

                    <div class="mt-6 mb-8 flex justify-center">
                        <button
                            v-if="stage !== 'success'"
                            @click="handleNextStage"
                            :disabled="!canProceed"
                            class="w-full md:w-auto px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium text-base"
                        >
                            {{ stage === 'options' ? 'Получить услугу' : 'Отправить заявку' }}
                        </button>
                        <router-link
                            v-else
                            to="/products"
                            class="inline-flex items-center justify-center w-full md:w-auto px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base"
                        >
                            <span class="text-center">Смотреть другие услуги</span>
                        </router-link>
                    </div>
                </div>

                -->

                <!-- Другие продукты и услуги (если список) -->
                <div v-if="productsList.length > 0 || servicesList.length > 0" class="mt-12 pb-12">
                    <div v-if="productsList.length > 0">
                        <h2 class="text-2xl md:text-3xl font-semibold text-foreground mb-6">Продуктовые направления</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                            <ProductCard
                                v-for="item in productsList"
                                :key="`product-${item.id}`"
                                :decision="item"
                                slug="products"
                                class="h-full"
                            />
                        </div>
                    </div>

                    <div v-if="servicesList.length > 0" class="mt-12">
                        <h2 class="text-2xl md:text-3xl font-semibold text-foreground mb-6">Все услуги</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                            <ProductCard
                                v-for="item in servicesList"
                                :key="`service-${item.id}`"
                                :decision="item"
                                slug="services"
                                class="h-full"
                            />
                        </div>
                    </div>
                </div>

            </div>
            </div>
        </div>

        <!-- Модальное окно информации -->
        <div v-if="showInfoModal && modalSettings" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showInfoModal = false">
            <div class="bg-background rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold mb-4">{{ modalSettings.title || 'Информация' }}</h3>
                <p class="text-muted-foreground mb-4" v-html="modalSettings.content || 'Здесь будет информация о продукте и услугах.'"></p>
                <button
                    @click="showInfoModal = false"
                    class="w-full h-10 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors"
                >
                    Закрыть
                </button>
            </div>
        </div>

        <!-- Модальное окно обратной связи -->
        <FeedbackModal
            :is-open="showFeedbackModal"
            @close="showFeedbackModal = false"
            @success="showFeedbackModal = false"
        />
    </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useStore } from 'vuex';
import SEOHead from '../components/SEOHead.vue';
import { usePreloader } from '../composables/usePreloader';
import DecisionCard from '../components/public/DecisionCard.vue';
import ProductCard from '../components/public/ProductCard.vue';
import LazyImage from '../components/public/LazyImage.vue';
import ProductSkeleton from '../components/public/ProductSkeleton.vue';
import { useProductCache } from '../composables/useProductCache';
import OptionsStage from '../components/public/product/OptionsStage.vue';
import FormsStage from '../components/public/product/FormsStage.vue';
import SuccessStage from '../components/public/product/SuccessStage.vue';
import FeedbackModal from '../components/public/FeedbackModal.vue';
import ConsentCheckbox from '../components/public/ConsentCheckbox.vue';

export default {
    name: 'ProductPage',
    components: {
        SEOHead,
        DecisionCard,
        ProductCard,
        LazyImage,
        ProductSkeleton,
        OptionsStage,
        FormsStage,
        SuccessStage,
        FeedbackModal,
        ConsentCheckbox,
    },
    setup() {
        const route = useRoute();
        const store = useStore();
        const { getCachedProduct, setCachedProduct, clearProductCache } = useProductCache();
        const { hidePreloader } = usePreloader();
        
        const loading = ref(false);
        const error = ref(null);
        const product = ref(null);
        const productsList = ref([]);
        const servicesList = ref([]);
        const loadingLists = ref(false);
        const stage = ref('options'); // options, forms, success
        const selectedServices = ref([]);
        const formData = ref({
            name: '',
            phone: '',
            comment: '',
        });
        const consentGiven = ref(false);
        const consentError = ref(false);
        const windowWidth = ref(window.innerWidth);
        const showInfoModal = ref(false);
        const showFeedbackModal = ref(false);
        const modalSettings = ref(null);
        const formContainer = ref(null); // Ref для контейнера формы
        
        // AbortController для отмены запросов
        const abortController = ref(null);

        const currentStageComponent = computed(() => {
            const components = {
                'options': 'OptionsStage',
                'forms': 'FormsStage',
                'success': 'SuccessStage',
            };
            return components[stage.value] || 'OptionsStage';
        });

        const servicesCount = computed(() => {
            if (!product.value?.services) return 0;
            return selectedServices.value.filter(s => s.active).length;
        });

        const formattedCount = computed(() => {
            const count = servicesCount.value;
            if (count === 0 || count >= 20) {
                return `${count} услуг`;
            }
            if (count === 1) {
                return `${count} услуга`;
            }
            if (count >= 2 && count <= 4) {
                return `${count} услуги`;
            }
            return `${count} услуг`;
        });

        const canProceed = computed(() => {
            if (stage.value === 'options') {
                return servicesCount.value > 0;
            }
            if (stage.value === 'forms') {
                return formData.value.name.trim() !== '' && formData.value.phone.trim() !== '' && consentGiven.value;
            }
            return false;
        });

        const handleWindowResize = () => {
            windowWidth.value = window.innerWidth;
        };

        const handleServicesUpdate = (services) => {
            selectedServices.value = services;
        };

        const handleFormUpdate = (form) => {
            formData.value = { ...formData.value, ...form };
        };

        const handleNextStage = async () => {
            if (stage.value === 'options') {
                stage.value = 'forms';
                // Плавный скролл к форме после рендера
                await nextTick();
                await scrollToForm();
            } else if (stage.value === 'forms') {
                // Проверка согласия перед отправкой
                if (!consentGiven.value) {
                    consentError.value = true;
                    return;
                }
                consentError.value = false;
                await submitForm();
            }
        };

        const scrollToForm = () => {
            return new Promise((resolve) => {
                if (!formContainer.value) {
                    resolve();
                    return;
                }

                // Дополнительная задержка для полного рендера формы
                setTimeout(() => {
                    const element = formContainer.value;
                    if (!element) {
                        resolve();
                        return;
                    }

                    // Получаем абсолютную позицию элемента
                    const rect = element.getBoundingClientRect();
                    const elementTop = rect.top + window.pageYOffset;
                    
                    // Отступ сверху для видимости формы (с запасом)
                    const headerOffset = 100;
                    
                    // Целевая позиция скролла
                    const targetPosition = elementTop - headerOffset;
                    
                    // Используем плавный скролл с ручным расчетом для лучшего контроля
                    smoothScrollTo(Math.max(0, targetPosition), 600).then(resolve);
                }, 150); // Задержка для гарантии рендера формы
            });
        };

        // Функция для плавного скролла (polyfill для старых браузеров)
        const smoothScrollTo = (targetPosition, duration) => {
            return new Promise((resolve) => {
                const startPosition = window.pageYOffset;
                const distance = targetPosition - startPosition;
                let startTime = null;

                const animation = (currentTime) => {
                    if (startTime === null) startTime = currentTime;
                    const timeElapsed = currentTime - startTime;
                    const progress = Math.min(timeElapsed / duration, 1);
                    
                    // Easing function для плавности
                    const ease = 0.5 - Math.cos(progress * Math.PI) / 2;
                    
                    window.scrollTo(0, startPosition + distance * ease);
                    
                    if (timeElapsed < duration) {
                        requestAnimationFrame(animation);
                    } else {
                        resolve();
                    }
                };

                requestAnimationFrame(animation);
            });
        };

        const submitForm = async () => {
            loading.value = true;
            try {
                const response = await fetch('/api/public/leave-products', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: product.value.id,
                        name: formData.value.name,
                        phone: formData.value.phone,
                        comment: formData.value.comment || '',
                        services: selectedServices.value.filter(s => s.active).map(s => ({
                            id: s.id,
                            active: s.active,
                        })),
                    }),
                });

                if (response.ok) {
                    stage.value = 'success';
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    error.value = errorData.message || 'Ошибка при отправке заявки';
                }
            } catch (err) {
                error.value = 'Ошибка при отправке заявки';
                console.error('Error submitting form:', err);
            } finally {
                loading.value = false;
            }
        };

        const fetchProduct = async () => {
            loading.value = true;
            error.value = null;
            
            // Отменяем предыдущий запрос если он есть
            if (abortController.value) {
                abortController.value.abort();
            }
            abortController.value = new AbortController();
            
            try {
                const slug = route.params.slug;
                
                // Проверяем кеш
                const cached = getCachedProduct(slug);
                if (cached) {
                    product.value = cached;
                    // Инициализируем выбранные услуги
                    if (product.value.services && Array.isArray(product.value.services)) {
                        selectedServices.value = product.value.services.map(service => ({
                            id: service.id || service,
                            name: service.name || '',
                            active: true,
                        }));
                    }
                    loading.value = false;
                    // Загружаем списки в фоне
                    fetchProductsList();
                    fetchServices();
                    fetchModalSettings();
                    return;
                }
                
                const url = `/api/public/products/${encodeURIComponent(slug)}`;
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    signal: abortController.value.signal,
                });
                
                const contentType = response.headers.get('content-type');
                
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(`Ошибка: сервер вернул не JSON ответ (статус: ${response.status})`);
                }

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || `Ошибка ${response.status}: Продукт не найден`);
                }
                
                if (data.data) {
                    product.value = data.data;
                    // Сохраняем в кеш
                    setCachedProduct(slug, data.data);
                    
                    // Инициализируем выбранные услуги
                    if (product.value.services && Array.isArray(product.value.services)) {
                        selectedServices.value = product.value.services.map(service => ({
                            id: service.id || service,
                            name: service.name || '',
                            active: true,
                        }));
                    }
                    
                    // Загружаем списки параллельно
                    Promise.all([
                        fetchProductsList(),
                        fetchServices(),
                        fetchModalSettings(),
                    ]);
                } else {
                    throw new Error('Продукт не найден');
                }
            } catch (err) {
                if (err.name === 'AbortError') {
                    return; // Запрос был отменен
                }
                console.error('Error fetching product:', err);
                if (err instanceof SyntaxError && err.message.includes('JSON')) {
                    error.value = 'Ошибка: сервер вернул некорректный ответ.';
                } else {
                    error.value = err.message || 'Ошибка загрузки продукта';
                }
            } finally {
                loading.value = false;
                // Скрываем прелоадер после загрузки контента
                hidePreloader();
            }
        };

        const fetchProductsList = async () => {
            if (loadingLists.value) return;
            loadingLists.value = true;
            try {
                const data = await store.dispatch('fetchPublicProducts', { minimal: false });
                // Исключаем текущий продукт из списка и ограничиваем до 8
                productsList.value = (data || []).filter(p => p.id !== product.value?.id).slice(0, 8);
            } catch (err) {
                console.error('Error fetching products list:', err);
            } finally {
                loadingLists.value = false;
            }
        };

        const fetchServices = async () => {
            if (loadingLists.value) return;
            try {
                const data = await store.dispatch('fetchPublicServices', { minimal: true });
                // Ограничиваем до 8
                servicesList.value = (data || []).slice(0, 8);
            } catch (err) {
                console.error('Error fetching services:', err);
            }
        };

        const fetchModalSettings = async () => {
            try {
                const response = await fetch('/api/public/modal-settings/products', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.data && data.data.is_active) {
                        modalSettings.value = data.data;
                    }
                }
            } catch (err) {
                console.error('Error fetching modal settings:', err);
            }
        };

        // Отслеживаем изменения slug для перезагрузки продукта
        watch(() => route.params.slug, (newSlug, oldSlug) => {
            if (newSlug !== oldSlug) {
                product.value = null;
                productsList.value = [];
                servicesList.value = [];
                stage.value = 'options';
                selectedServices.value = [];
                formData.value = { name: '', phone: '', comment: '' };
                fetchProduct();
            }
        });

        onMounted(() => {
            window.addEventListener('resize', handleWindowResize);
            fetchProduct();
        });

        onUnmounted(() => {
            window.removeEventListener('resize', handleWindowResize);
            // Отменяем запросы при размонтировании
            if (abortController.value) {
                abortController.value.abort();
            }
        });

        // SEO computed properties
        const productTitle = computed(() => {
            if (!product.value) return 'Продукт - МНКА';
            if (product.value.seo_title) return product.value.seo_title;
            return `${product.value.name} - МНКА | Продукты и услуги`;
        });

        const productDescription = computed(() => {
            if (!product.value) return 'Подробная информация о продукте. Профессиональные услуги по работе с земельными участками.';
            if (product.value.seo_description) return product.value.seo_description;
            const desc = product.value.description;
            const descriptionText = typeof desc === 'string' ? desc : desc?.ru || '';
            // Если описание есть, используем его, иначе создаем базовое описание
            if (descriptionText && descriptionText.length > 50) {
                return descriptionText;
            }
            return `${product.value.name} - профессиональные услуги по подбору и оформлению земельных участков. Полная информация о продукте, условиях и преимуществах.`;
        });

        const productKeywords = computed(() => {
            if (!product.value) return 'продукт, услуги, земельные участки';
            if (product.value.seo_keywords) return product.value.seo_keywords;
            return `${product.value.name}, продукт, услуги, земельные участки, недвижимость, МНКА`;
        });

        const productImage = computed(() => {
            if (!product.value?.image?.url) return '';
            return product.value.image.url;
        });

        const canonicalUrl = computed(() => {
            if (!product.value) return '';
            return window.location.origin + '/products/' + (product.value.slug || '');
        });

        const productSchema = computed(() => {
            if (!product.value) return null;

            // Базовая схема Product
            const schema = {
                '@context': 'https://schema.org',
                '@type': 'Product',
                'name': product.value.name,
                'description': productDescription.value,
                'url': canonicalUrl.value,
            };

            // Добавляем изображение если есть
            if (product.value.image?.url) {
                schema.image = window.location.origin + product.value.image.url;
            }

            // Добавляем услуги как offers
            if (product.value.services && product.value.services.length > 0) {
                schema.offers = {
                    '@type': 'AggregateOffer',
                    'priceCurrency': 'RUB',
                    'availability': 'https://schema.org/InStock',
                    'offerCount': product.value.services.length,
                };
            }

            // Добавляем breadcrumbs
            const breadcrumbSchema = {
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
                    {
                        '@type': 'ListItem',
                        'position': 3,
                        'name': product.value.name,
                        'item': canonicalUrl.value,
                    },
                ],
            };

            return [schema, breadcrumbSchema];
        });

        // Функция для поделиться
        const handleShare = async () => {
            const url = window.location.href;
            const title = product.value?.name || 'Продукт';
            const description = product.value?.description;
            let descriptionText = '';
            
            // Извлекаем текст описания
            if (description) {
                if (typeof description === 'string') {
                    descriptionText = description;
                } else if (typeof description === 'object' && description !== null) {
                    descriptionText = description.ru || description.short || description.full || '';
                }
            }

            if (navigator.share) {
                try {
                    await navigator.share({
                        title,
                        text: descriptionText || '',
                        url,
                    });
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.error('Error sharing:', err);
                    }
                }
            } else {
                // Fallback: копируем в буфер обмена
                try {
                    await navigator.clipboard.writeText(url);
                    alert('Ссылка скопирована в буфер обмена');
                } catch (err) {
                    console.error('Failed to copy:', err);
                }
            }
        };

        return {
            loading,
            error,
            product,
            productsList,
            servicesList,
            stage,
            selectedServices,
            formData,
            windowWidth,
            showInfoModal,
            showFeedbackModal,
            modalSettings,
            currentStageComponent,
            formattedCount,
            handleShare,
            canProceed,
            handleServicesUpdate,
            handleFormUpdate,
            handleNextStage,
            consentGiven,
            consentError,
            formContainer,
            scrollToForm,
            loadingLists,
            // SEO properties
            productTitle,
            productDescription,
            productKeywords,
            productImage,
            canonicalUrl,
            productSchema,
        };
        },
    };
    </script>
