<template>
    <div class="w-full px-3 sm:px-4 md:px-5 py-8 sm:py-12 md:py-16 lg:py-20">
        <section v-if="banner && banner.is_active"
            class="relative w-full max-w-[1200px] mx-auto rounded-lg overflow-hidden shadow-sm" :style="bannerStyle">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-[#6C7B6D]" :style="{
                backgroundImage: banner.background_image ? `url('${banner.background_image}')` : 'none',
                backgroundSize: 'cover',
                backgroundPosition: 'center'
            }"></div>

            <!-- Content Overlay -->
            <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 h-full flex items-center" :style="bannerStyle">
                <div class="w-full max-w-2xl">
                    <div v-if="displayTitle || displaySubtitle" class="space-y-2 sm:space-y-3 mb-6 sm:mb-8">
                        <h1 v-if="displayTitle"
                            class="text-white text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-semibold leading-tight drop-shadow-lg">
                            {{ displayTitle }}
                        </h1>
                        <p v-if="displaySubtitle"
                            class="text-white text-lg sm:text-xl md:text-2xl lg:text-3xl font-normal leading-relaxed whitespace-pre-line drop-shadow-md">
                            {{ displaySubtitle }}
                        </p>
                    </div>
                    <component :is="displayButtonLink ? 'a' : 'button'" v-if="displayButtonText"
                        :href="displayButtonLink || undefined"
                        :to="displayButtonLink && !displayButtonLink.startsWith('http') ? displayButtonLink : undefined"
                        @click="handleButtonClick"
                        class="inline-block px-8 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors font-medium text-base">
                        {{ displayButtonText }}
                    </component>
                </div>
            </div>
        </section>

        <!-- Feedback Modal -->
        <FeedbackModal :is-open="showFeedbackModal" @close="showFeedbackModal = false"
            @success="handleFeedbackSuccess" />
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import FeedbackModal from './FeedbackModal.vue';

export default {
    name: 'HeroBanner',
    components: {
        FeedbackModal,
    },
    props: {
        slug: {
            type: String,
            default: 'home-banner',
        },
        title: {
            type: String,
            default: null,
        },
        subtitle: {
            type: String,
            default: null,
        },
        buttonText: {
            type: String,
            default: null,
        },
        buttonLink: {
            type: String,
            default: null,
        },
    },
    setup(props) {
        const banner = ref(null);
        const loading = ref(true);
        const showFeedbackModal = ref(false);

        // Computed для отображения с fallback
        const displayTitle = computed(() => {
            return props?.title || banner.value?.heading_1 || banner.value?.heading_2 || null;
        });

        const displaySubtitle = computed(() => {
            return props?.subtitle || banner.value?.description || null;
        });

        const displayButtonText = computed(() => {
            return props?.buttonText || banner.value?.button_text || null;
        });

        const displayButtonLink = computed(() => {
            return props?.buttonLink || (banner.value?.button_type === 'url' ? banner.value?.button_value : null);
        });

        const fetchBanner = async () => {
            try {
                const response = await fetch(`/api/public/banners/${props.slug}`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        banner.value = {
                            ...data.data,
                            background_image: data.data.background_image
                                ? `/${data.data.background_image.replace(/^\//, '')}`
                                : null,
                            height_desktop: data.data.height_desktop || 380,
                            height_mobile: data.data.height_mobile || 300,
                        };
                    }
                }
            } catch (error) {
                console.error('Error fetching banner:', error);
            } finally {
                loading.value = false;
            }
        };

        const handleButtonClick = (event) => {
            if (displayButtonLink.value) return;
            if (!banner.value) return;
            if (banner.value.button_type === 'method' && !banner.value.button_value) {
                showFeedbackModal.value = true;
            } else {
                console.log('Method button clicked:', banner.value.button_value);
            }
        };

        const handleFeedbackSuccess = () => {
            // Обработка успешной отправки формы
            showFeedbackModal.value = false;
        };

        // Вычисляем адаптивную высоту баннера
        const bannerStyle = computed(() => {
            if (!banner.value) return {};

            const heightDesktop = banner.value.height_desktop || 380;
            const heightMobile = banner.value.height_mobile || 300;

            // Используем CSS clamp для плавного перехода между высотами
            // Формула: clamp(min, preferred, max)
            // Интерполяция: на 320px (мобильный) = heightMobile, на 1200px+ (десктоп) = heightDesktop
            // 
            // Вычисляем preferred значение используя viewport width
            // На 320px: height = heightMobile
            // На 1200px: height = heightDesktop
            // Линейная интерполяция: height = heightMobile + (heightDesktop - heightMobile) * ((100vw - 320px) / (1200px - 320px))

            const heightDiff = heightDesktop - heightMobile;
            const widthRange = 1200 - 320; // 880px

            // Используем упрощенную формулу для clamp
            // preferred = heightMobile + heightDiff * ((100vw - 320px) / 880px)
            // Но для лучшей совместимости используем более простой вариант
            const preferredCalc = `(${heightMobile}px + ${heightDiff} * ((100vw - 320px) / ${widthRange}))`;

            return {
                '--banner-height-mobile': `${heightMobile}px`,
                '--banner-height-desktop': `${heightDesktop}px`,
                minHeight: `${heightMobile}px`,
                height: `clamp(${heightMobile}px, ${preferredCalc}, ${heightDesktop}px)`,
            };
        });

        onMounted(() => {
            fetchBanner();
        });

        return {
            banner,
            loading,
            bannerStyle,
            showFeedbackModal,
            displayTitle,
            displaySubtitle,
            displayButtonText,
            displayButtonLink,
            handleButtonClick,
            handleFeedbackSuccess,
        };
    },
};
</script>
