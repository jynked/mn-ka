<template>
    <div class="service-page min-h-screen bg-background">
        <SEOHead
            v-if="service"
            :title="serviceTitle"
            :description="serviceDescription"
            :keywords="serviceKeywords"
            :og-image="serviceImage"
            :canonical="canonicalUrl"
            :schema="serviceSchema"
        />
        
        <div class="w-full px-3 sm:px-4 md:px-5">
            <div class="w-full max-w-[1200px] mx-auto">
                <!-- Skeleton Loader -->
                <ProductSkeleton v-if="loading && !service" />

                <!-- Ошибка -->
                <div v-if="error && !loading" class="py-12">
                    <div class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
                        <p class="text-destructive">{{ error }}</p>
                    </div>
                </div>

                <!-- Контент услуги -->
                <div v-if="service && !loading">
                    <!-- Хлебные крошки (скрыты на мобильных) -->
                    <div class="hidden md:block mt-4">
                        <nav class="flex items-center gap-2 text-sm text-muted-foreground">
                            <router-link to="/" class="hover:text-foreground transition-colors">Главная</router-link>
                            <span>/</span>
                            <router-link to="/services" class="hover:text-foreground transition-colors">Услуги</router-link>
                            <span>/</span>
                            <span class="text-foreground">{{ service.name }}</span>
                        </nav>
                    </div>

                    <!-- Заголовок и изображение -->
                    <div class="mt-6 md:mt-8 grid md:grid-cols-2 gap-6 md:gap-8 items-center">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-semibold text-foreground mb-3 md:text-left text-center">
                                {{ service.name }}
                            </h1>
                            <div 
                                v-if="service.description" 
                                class="text-base md:text-lg text-muted-foreground leading-relaxed md:text-left text-center mb-4"
                                v-html="typeof service.description === 'string' ? service.description : service.description?.ru || ''"
                            ></div>
                            <div class="flex justify-center md:justify-start">
                                <button
                                    @click="showFeedbackModal = true"
                                    class="px-6 md:px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base"
                                >
                                    Получить услугу
                                </button>
                            </div>
                        </div>
                        <div class="relative">
                            <LazyImage
                                v-if="service.image"
                                :src="service.image.url"
                                :alt="service.name"
                                image-class="w-full h-auto rounded-lg shadow-md"
                                container-class="relative w-full"
                                :eager="true"
                            />
                            <!-- SVG иконка поделиться -->
                            <div class="absolute top-4 right-4 cursor-pointer hidden md:block">
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

                    <!-- HTML контент услуги -->
                    <div v-if="service.html_content" class="mt-8 service-html-content" v-html="service.html_content"></div>

                    <!--
                    <div class="mt-8 py-5 relative bg-[#F4F6FC] rounded-lg">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-4 md:px-6">
                            <div class="flex flex-col gap-2">
                                <span class="text-sm md:text-base font-semibold text-foreground">
                                    Для получения услуги <span class="text-[#688E67]">выберите необходимые параметры</span>
                                </span>
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

                        <div id="service-form-container" ref="formContainer" class="mt-8 px-4 md:px-6" :class="windowWidth > 767 ? 'px-5' : ''">
                            <component :is="currentStageComponent" :service="service" @update-options="handleOptionsUpdate" @update-form="handleFormUpdate"></component>
                        </div>

                        <div v-if="stage === 'forms'" class="mt-6 px-4 md:px-6">
                            <ConsentCheckbox 
                                v-model="consentGiven" 
                                :error="consentError"
                                @update:error="consentError = $event"
                            />
                        </div>

                        <div class="mt-6 mb-8 flex justify-center px-4 md:px-6">
                            <button
                                v-if="stage !== 'success'"
                                @click="handleNextStage"
                                :disabled="!canProceed"
                                class="w-full md:w-auto md:px-8 h-12 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                {{ stage === 'options' ? 'Получить услугу' : 'Отправить заявку' }}
                            </button>
                            <router-link
                                v-else
                                to="/services"
                                class="inline-flex items-center justify-center w-full md:w-auto md:px-8 h-12 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium"
                            >
                                <span class="text-center">Смотреть другие услуги</span>
                            </router-link>
                        </div>
                    </div>
                -->

                    <!-- Другие услуги -->
                    <div v-if="servicesList.length > 0" class="mt-12 pb-12">
                        <h2 class="text-2xl md:text-3xl font-semibold text-foreground mb-6">Другие услуги</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                            <DecisionCard
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

        <!-- Модальное окно информации -->
        <div v-if="showInfoModal && modalSettings" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showInfoModal = false">
            <div class="bg-background rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold mb-4">{{ modalSettings.title || 'Информация' }}</h3>
                <p class="text-muted-foreground mb-4" v-html="modalSettings.content || 'Здесь будет информация об услуге.'"></p>
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
import DecisionCard from '../components/public/DecisionCard.vue';
import LazyImage from '../components/public/LazyImage.vue';
import ProductSkeleton from '../components/public/ProductSkeleton.vue';
import ServiceOptionsStage from '../components/public/service/OptionsStage.vue';
import ServiceFormsStage from '../components/public/service/FormsStage.vue';
import ServiceSuccessStage from '../components/public/service/SuccessStage.vue';
import FeedbackModal from '../components/public/FeedbackModal.vue';
import ConsentCheckbox from '../components/public/ConsentCheckbox.vue';

export default {
    name: 'ServicePage',
    components: {
        SEOHead,
        DecisionCard,
        LazyImage,
        ProductSkeleton,
        ServiceOptionsStage,
        ServiceFormsStage,
        ServiceSuccessStage,
        FeedbackModal,
        ConsentCheckbox,
    },
    setup() {
        const route = useRoute();
        const store = useStore();
        
        const loading = ref(false);
        const error = ref(null);
        const service = ref(null);
        const servicesList = ref([]);
        const loadingLists = ref(false);
        const stage = ref('options'); // options, forms, success
        const selectedOptions = ref({
            appCategory: null,
            chapter: null,
            case: null,
        });
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
        const formContainer = ref(null);
        
        const abortController = ref(null);

        const currentStageComponent = computed(() => {
            const components = {
                'options': 'ServiceOptionsStage',
                'forms': 'ServiceFormsStage',
                'success': 'ServiceSuccessStage',
            };
            return components[stage.value] || 'ServiceOptionsStage';
        });

        const canProceed = computed(() => {
            if (stage.value === 'options') {
                return selectedOptions.value.appCategory && selectedOptions.value.chapter && selectedOptions.value.case;
            }
            if (stage.value === 'forms') {
                return formData.value.name.trim() !== '' && formData.value.phone.trim() !== '' && consentGiven.value;
            }
            return false;
        });

        const handleWindowResize = () => {
            windowWidth.value = window.innerWidth;
        };

        const handleOptionsUpdate = (options) => {
            selectedOptions.value = { ...selectedOptions.value, ...options };
        };

        const handleFormUpdate = (form) => {
            formData.value = { ...formData.value, ...form };
        };

        const handleNextStage = async () => {
            if (stage.value === 'options') {
                stage.value = 'forms';
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

                setTimeout(() => {
                    const element = formContainer.value;
                    if (!element) {
                        resolve();
                        return;
                    }

                    const rect = element.getBoundingClientRect();
                    const elementTop = rect.top + window.pageYOffset;
                    const headerOffset = 100;
                    const targetPosition = elementTop - headerOffset;
                    
                    smoothScrollTo(Math.max(0, targetPosition), 600).then(resolve);
                }, 150);
            });
        };

        const smoothScrollTo = (targetPosition, duration) => {
            return new Promise((resolve) => {
                const startPosition = window.pageYOffset;
                const distance = targetPosition - startPosition;
                let startTime = null;

                const animation = (currentTime) => {
                    if (startTime === null) startTime = currentTime;
                    const timeElapsed = currentTime - startTime;
                    const progress = Math.min(timeElapsed / duration, 1);
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
                const response = await fetch('/api/public/leave-services', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        service_id: service.value.id,
                        name: formData.value.name,
                        phone: formData.value.phone,
                        comment: formData.value.comment || '',
                        app_category: selectedOptions.value.appCategory,
                        chapter: selectedOptions.value.chapter,
                        case: selectedOptions.value.case,
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

        const fetchService = async () => {
            loading.value = true;
            error.value = null;
            
            if (abortController.value) {
                abortController.value.abort();
            }
            abortController.value = new AbortController();
            
            try {
                const slug = route.params.slug;
                const url = `/api/public/services/${encodeURIComponent(slug)}`;
                
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
                    throw new Error(`Ошибка: сервер вернул не JSON ответ (статус: ${response.status})`);
                }

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || `Ошибка ${response.status}: Услуга не найдена`);
                }
                
                if (data.data) {
                    service.value = data.data;
                    Promise.all([
                        fetchServices(),
                        fetchModalSettings(),
                    ]);
                } else {
                    throw new Error('Услуга не найдена');
                }
            } catch (err) {
                if (err.name === 'AbortError') {
                    return;
                }
                console.error('Error fetching service:', err);
                if (err instanceof SyntaxError && err.message.includes('JSON')) {
                    error.value = 'Ошибка: сервер вернул некорректный ответ.';
                } else {
                    error.value = err.message || 'Ошибка загрузки услуги';
                }
            } finally {
                loading.value = false;
            }
        };

        const fetchServices = async () => {
            if (loadingLists.value) return;
            loadingLists.value = true;
            try {
                // Загружаем все услуги из store (минимальный набор для карточек)
                const data = await store.dispatch('fetchPublicServices', { minimal: true });
                    // Фильтруем все услуги, исключая текущую
                servicesList.value = (data || []).filter(s => s.id !== service.value?.id);
            } catch (err) {
                console.error('Error fetching services:', err);
            } finally {
                loadingLists.value = false;
            }
        };

        const fetchModalSettings = async () => {
            try {
                const response = await fetch('/api/public/modal-settings/services', {
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

        watch(() => route.params.slug, (newSlug, oldSlug) => {
            if (newSlug !== oldSlug) {
                service.value = null;
                servicesList.value = [];
                stage.value = 'options';
                selectedOptions.value = { appCategory: null, chapter: null, case: null };
                formData.value = { name: '', phone: '', comment: '' };
                fetchService();
            }
        });

        onMounted(() => {
            window.addEventListener('resize', handleWindowResize);
            fetchService();
        });

        onUnmounted(() => {
            window.removeEventListener('resize', handleWindowResize);
            if (abortController.value) {
                abortController.value.abort();
            }
        });

        // SEO computed properties
        const serviceTitle = computed(() => {
            if (!service.value) return 'Услуга - МНКА';
            if (service.value.seo_title) return service.value.seo_title;
            return `${service.value.name} - МНКА | Профессиональные услуги`;
        });

        const serviceDescription = computed(() => {
            if (!service.value) return 'Подробная информация об услуге. Профессиональные услуги по работе с земельными участками.';
            if (service.value.seo_description) return service.value.seo_description;
            const desc = service.value.description;
            const descriptionText = typeof desc === 'string' ? desc : desc?.ru || '';
            // Если описание есть, используем его, иначе создаем базовое описание
            if (descriptionText && descriptionText.length > 50) {
                return descriptionText;
            }
            return `${service.value.name} - профессиональные услуги по подбору, оформлению и кадастровым работам с земельными участками. Полная информация об услуге, условиях и стоимости.`;
        });

        const serviceKeywords = computed(() => {
            if (!service.value) return 'услуга, кадастр, земельные участки';
            if (service.value.seo_keywords) return service.value.seo_keywords;
            return `${service.value.name}, услуга, кадастр, земельные участки, оформление документов, МНКА`;
        });

        const serviceImage = computed(() => {
            if (!service.value?.image?.url) return '';
            return service.value.image.url;
        });

        const canonicalUrl = computed(() => {
            if (!service.value) return '';
            return window.location.origin + '/services/' + (service.value.slug || '');
        });

        const serviceSchema = computed(() => {
            if (!service.value) return null;

            // Схема Service
            const schema = {
                '@context': 'https://schema.org',
                '@type': 'Service',
                'name': service.value.name,
                'description': serviceDescription.value,
                'url': canonicalUrl.value,
                'provider': {
                    '@type': 'Organization',
                    'name': 'МНКА',
                },
            };

            // Добавляем изображение если есть
            if (service.value.image?.url) {
                schema.image = window.location.origin + service.value.image.url;
            }

            // Breadcrumbs schema
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
                        'name': 'Услуги',
                        'item': window.location.origin + '/services',
                    },
                    {
                        '@type': 'ListItem',
                        'position': 3,
                        'name': service.value.name,
                        'item': canonicalUrl.value,
                    },
                ],
            };

            return [schema, breadcrumbSchema];
        });

        return {
            loading,
            error,
            service,
            servicesList,
            stage,
            selectedOptions,
            formData,
            windowWidth,
            showInfoModal,
            showFeedbackModal,
            modalSettings,
            currentStageComponent,
            canProceed,
            handleOptionsUpdate,
            handleFormUpdate,
            handleNextStage,
            consentGiven,
            consentError,
            formContainer,
            scrollToForm,
            loadingLists,
            // SEO properties
            serviceTitle,
            serviceDescription,
            serviceKeywords,
            serviceImage,
            canonicalUrl,
            serviceSchema,
        };
    },
};
</script>

<style scoped>
.service-html-content {
    color: #111827;
}

.service-html-content :deep(h1),
.service-html-content :deep(h2),
.service-html-content :deep(h3),
.service-html-content :deep(h4),
.service-html-content :deep(h5),
.service-html-content :deep(h6) {
    font-weight: 600;
    color: #111827;
    margin-bottom: 1rem;
    margin-top: 1.5rem;
}

.service-html-content :deep(h1) {
    font-size: 1.875rem;
    line-height: 2.25rem;
}

.service-html-content :deep(h2) {
    font-size: 1.5rem;
    line-height: 2rem;
}

.service-html-content :deep(h3) {
    font-size: 1.25rem;
    line-height: 1.75rem;
}

.service-html-content :deep(p) {
    margin-bottom: 1rem;
    font-size: 1rem;
    line-height: 1.75;
}

.service-html-content :deep(ul),
.service-html-content :deep(ol) {
    margin-bottom: 1rem;
    margin-left: 1.5rem;
}

.service-html-content :deep(li) {
    margin-bottom: 0.5rem;
}

.service-html-content :deep(a) {
    color: #688E67;
    text-decoration: none;
}

.service-html-content :deep(a:hover) {
    text-decoration: underline;
}

.service-html-content :deep(img) {
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    margin-top: 1rem;
    margin-bottom: 1rem;
    max-width: 100%;
    height: auto;
}

.service-html-content :deep(blockquote) {
    border-left: 4px solid #688E67;
    padding-left: 1rem;
    font-style: italic;
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.service-html-content :deep(table) {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
}

.service-html-content :deep(th),
.service-html-content :deep(td) {
    border: 1px solid #d1d5db;
    padding: 0.5rem 1rem;
}

.service-html-content :deep(th) {
    background-color: #f3f4f6;
    font-weight: 600;
}
</style>

