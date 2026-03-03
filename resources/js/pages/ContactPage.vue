<template>
    <div class="contact-page min-h-screen bg-background pb-12 md:pb-20">
        <SEOHead title="Контакты - МНКА | Свяжитесь с нами"
            description="Контактная информация компании МНКА. Свяжитесь с нами для получения консультации по подбору и оформлению земельных участков. Адреса, телефоны, email, время работы. Бесплатная консультация."
            keywords="контакты, адрес, телефон, связаться, email, время работы, консультация, МНКА"
            :canonical="canonicalUrl" :schema="contactSchema" />

        <div class="w-full px-3 sm:px-4 md:px-5">
            <div class="w-full max-w-[1200px] mx-auto">
                <!-- Хлебные крошки -->
                <div class="hidden md:block mt-4">
                    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
                        <router-link to="/" class="hover:text-foreground transition-colors">Главная</router-link>
                        <span>/</span>
                        <span class="text-foreground">Контакты</span>
                    </nav>
                </div>

                <!-- Заголовок -->
                <div class="mt-6 md:mt-8 mb-8 md:mb-12">
                    <h1 class="text-2xl md:text-3xl font-semibold text-foreground text-center md:text-left">
                        Контакты
                    </h1>
                </div>

                <!-- Основной контент -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-10">
                    <!-- Блок контактов -->
                    <div class="bg-white rounded-[32px] p-6 md:p-10 shadow-[0_20px_60px_rgba(25,48,49,0.08)]">
                        <div class="mb-8">
                            <h2 class="text-2xl md:text-3xl font-semibold text-[#494c4c] mb-8">
                                Контакты
                            </h2>
                            <div v-if="loading" class="text-center py-8 text-muted-foreground">
                                Загрузка контактов...
                            </div>
                            <div v-else-if="contactItems.length === 0" class="text-center py-8 text-muted-foreground">
                                Контакты пока не настроены
                            </div>
                            <div v-else class="space-y-6">
                                <div v-for="(item, index) in contactItems" :key="index" class="contact-item">
                                    <p class="text-sm text-[#8a8f8c] mb-1">{{ item.label }}</p>
                                    <a v-if="item.link" :href="item.link" target="_blank" rel="noopener"
                                        class="text-xl md:text-2xl font-semibold text-[#608361] hover:underline transition-colors block">
                                        {{ item.value }}
                                    </a>
                                    <p v-else class="text-xl md:text-2xl font-semibold text-[#608361]">
                                        {{ item.value }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Социальные сети
                        <div v-if="socialItems.length > 0" class="mt-10">
                            <div class="flex gap-4 flex-wrap">
                                <a
                                    v-for="(social, index) in socialItems"
                                    :key="index"
                                    :href="social.link"
                                    target="_blank"
                                    rel="noopener"
                                    :title="social.title"
                                    class="w-12 h-12 rounded-xl bg-[#f0f5f1] flex items-center justify-center hover:transform hover:-translate-y-0.5 transition-all duration-200"
                                >
                                    <span class="text-[#608361]" v-html="getSocialIcon(social.icon)"></span>
                                </a>
                            </div>
                        </div>
                        -->

                        <div ref="mapContainer" class="map-container"></div>
                    </div>

                    <!-- Форма обратной связи -->
                    <div class="bg-[#f6f8fc] rounded-[32px] p-6 md:p-10">
                        <h2 class="text-2xl md:text-3xl font-semibold text-[#494c4c] mb-3">
                            Обратный звонок
                        </h2>
                        <p class="text-[#6f7372] mb-8 max-w-[360px]">
                            Закажите обратный звонок, мы свяжемся с вами и ответим на все вопросы
                        </p>
                        <FeedbackForm title="" description="" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import SEOHead from '../components/SEOHead.vue';
import FeedbackForm from '../components/public/FeedbackForm.vue';

export default {
    name: 'ContactPage',
    components: {
        SEOHead,
        FeedbackForm,
    },
    setup() {
        const loading = ref(true);
        const contactItems = ref([]);
        const socialItems = ref([]);
        const mapContainer = ref(null);

        // Загрузка настроек контактов из API
        const fetchContactSettings = async () => {
            loading.value = true;
            try {
                const response = await fetch('/api/public/contact-settings', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    const settings = data.data || {};

                    // Формируем массив контактных элементов
                    const items = [];
                    if (settings.phone) {
                        items.push({
                            label: 'Телефон',
                            value: settings.phone,
                            link: `tel:${settings.phone.replace(/\s/g, '')}`,
                        });
                    }
                    if (settings.email) {
                        items.push({
                            label: 'Email',
                            value: settings.email,
                            link: `mailto:${settings.email}`,
                        });
                    }
                    if (settings.address) {
                        items.push({
                            label: 'Адрес',
                            value: settings.address,
                            link: null,
                        });
                    }
                    if (settings.working_hours) {
                        items.push({
                            label: 'Время работы',
                            value: settings.working_hours,
                            link: null,
                        });
                    }
                    contactItems.value = items;

                    // Социальные сети
                    socialItems.value = settings.socials || [];
                }
            } catch (err) {
                console.error('Error fetching contact settings:', err);
            } finally {
                loading.value = false;
            }
        };

        // Иконки социальных сетей
        const socialIcons = {
            vk: `<svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="16" fill="#e5efe7"/>
                    <path d="M8.5 11.5h2.74l2 3.7 2-3.7h2.6l-1.9 3.3 2.9 4.7h-2.8l-1.4-2.2-1.4 2.2H12l2.5-4.6-2.2-3.8H9.65l1.5 3.8-2.65 4.6H6l2.5-4.2L6 11.5h2.5z" fill="#608361"/>
                </svg>`,
            instagram: `<svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="16" fill="#e5efe7"/>
                    <path d="M21 10h-10a1 1 0 0 0-1 1v10c0 .55.45 1 1 1h10c.55 0 1-.45 1-1V11a1 1 0 0 0-1-1zm-5 9a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm3.5-6.6a.9.9 0 1 1 0-1.8.9.9 0 0 1 0 1.8z" fill="#608361"/>
                </svg>`,
            telegram: `<svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="16" fill="#e5efe7"/>
                    <path d="M23.5 9.5L8.5 15.2c-.9.36-.9 1.09-.16 1.34l3.62 1.13 1.38 4.44c.17.5.31.7.64.7.33 0 .48-.12.67-.32l2.01-1.96 3.44 2.54c.63.35 1.09.17 1.25-.59l2.26-10.63c.2-.95-.36-1.38-1.11-1.07z" fill="#608361"/>
                </svg>`,
        };

        const getSocialIcon = (name = '') => {
            const key = name.toLowerCase();
            return socialIcons[key] ?? `<span>${name}</span>`;
        };

        const loadMapScript = () => {
            return new Promise((resolve, reject) => {
                const scriptId = 'yandex-map-script';
                if (document.getElementById(scriptId)) {
                    document.getElementById(scriptId).remove();
                }

                const script = document.createElement('script');
                script.id = scriptId;
                script.type = 'text/javascript';
                script.charset = 'utf-8';
                script.async = true;
                script.src = 'https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Aecdddca1a98423f7c117575647055d8ac50b0e921084d056bda0dda783e0065c&width=100%25&height=500&lang=ru_RU&scroll=true';

                script.onload = resolve;
                script.onerror = reject;

                if (mapContainer.value) {
                    mapContainer.value.appendChild(script);
                } else {
                    reject(new Error('Контейнер карты не найден'));
                }
            });
        };

        // Загружаем настройки при монтировании компонента
        onMounted(async () => {
            fetchContactSettings();
            try {
                await loadMapScript();
                console.log('Карта успешно загружена');
            } catch (error) {
                console.error('Ошибка загрузки карты:', error);
                if (mapContainer.value) {
                    mapContainer.value.innerHTML = '<p class="text-center text-muted-foreground">Не удалось загрузить карту</p>';
                }
            }
        });

        onUnmounted(() => {
            if (mapContainer.value) {
                mapContainer.value.innerHTML = '';
            }
            const script = document.getElementById('yandex-map-script');
            if (script) script.remove();
        });

        // SEO data
        const canonicalUrl = computed(() => {
            return window.location.origin + '/contacts';
        });

        const contactSchema = computed(() => {
            return {
                '@context': 'https://schema.org',
                '@type': 'ContactPage',
                'name': 'Контакты',
                'description': 'Свяжитесь с нами для получения консультации',
                'url': canonicalUrl.value,
            };
        });

        return {
            loading,
            contactItems,
            socialItems,
            getSocialIcon,
            canonicalUrl,
            contactSchema,
            mapContainer,
        };
    },
};
</script>

<style scoped>
.contact-item+.contact-item {
    margin-top: 0;
}

.map-container {
  width: 100%;
  height: 500px;
  margin-top: 2rem;
  border-radius: 24px;
  overflow: hidden;
}

@media (max-width: 768px) {
  .map-container {
    height: 400px;
  }
}
</style>
