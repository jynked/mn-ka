<template>
    <div class="home-page">
        <SEOHead
            :title="seoTitle"
            :description="seoDescription"
            :keywords="seoKeywords"
            :og-image="seoSettings.default_og_image"
            :og-type="seoSettings.og_type || 'website'"
            :canonical="canonicalUrl"
            :schema="combinedSchema"
        />
        
        <component
            v-for="(block, index) in orderedBlocks"
            :key="block.key"
            :is="block.component"
            v-bind="block.props"
        />
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import SEOHead from '../components/SEOHead.vue';
import { usePreloader } from '../composables/usePreloader';
import HeroBanner from '../components/public/HeroBanner.vue';
import HowWork from '../components/public/HowWork.vue';
import Decisions from '../components/public/Decisions.vue';
// import Quiz from '../components/public/Quiz.vue';
import Faq from '../components/public/Faq.vue';
import WhyChooseUs from '../components/public/WhyChooseUs.vue';
import CasesBlock from '../components/public/CasesBlock.vue';
import FeedbackForm from '../components/public/FeedbackForm.vue';

// Маппинг компонентов
const componentMap = {
    'HeroBanner': HeroBanner,
    'HowWork': HowWork,
    'Decisions': Decisions,
    // 'Quiz': Quiz,
    'Faq': Faq,
    'WhyChooseUs': WhyChooseUs,
    'CasesBlock': CasesBlock,
    'FeedbackForm': FeedbackForm,
};

export default {
    name: 'Home',
    components: {
        SEOHead,
    },
    setup() {
        const { hidePreloader } = usePreloader();
        const blocks = ref([]);
        const loading = ref(true);
        const homePageSettings = ref(null);
        // Инициализируем seoSettings с дефолтными значениями сразу
        // Это предотвращает мерцание title/description при загрузке
        const defaultSeoSettings = {
            site_name: 'МНКА - Профессиональные услуги по работе с земельными участками',
            site_description: 'Профессиональные услуги по подбору и оформлению земельных участков. Кадастровые работы, консультации, оформление документов.',
            site_keywords: 'земельные участки, кадастр, оформление документов, недвижимость, МНКА',
            default_og_image: '',
            og_type: 'website',
        };
        
        const seoSettings = ref(defaultSeoSettings);

        const orderedBlocks = computed(() => {
            return blocks.value
                .map(block => {
                    const component = componentMap[block.component];
                    if (!component) {
                        console.warn(`Component ${block.component} not found`);
                        return null;
                    }
                    // Для FeedbackForm передаем title и description как props
                    const blockProps = { ...(block.settings || {}) };
                    
                    // Добавляем настройки из HomePageSettings для соответствующих блоков
                    if (homePageSettings.value) {
                        if (block.component === 'HeroBanner') {
                            if (homePageSettings.value.hero_title) blockProps.title = homePageSettings.value.hero_title;
                            if (homePageSettings.value.hero_subtitle) blockProps.subtitle = homePageSettings.value.hero_subtitle;
                            if (homePageSettings.value.hero_button_text) blockProps.buttonText = homePageSettings.value.hero_button_text;
                            if (homePageSettings.value.hero_button_link) blockProps.buttonLink = homePageSettings.value.hero_button_link;
                        } else if (block.component === 'Decisions') {
                            if (homePageSettings.value.select_title) blockProps.title = homePageSettings.value.select_title;
                            if (homePageSettings.value.select_subtitle) blockProps.subtitle = homePageSettings.value.select_subtitle;
                        } else if (block.component === 'HowWork') {
                            if (homePageSettings.value.work_title) blockProps.title = homePageSettings.value.work_title;
                            if (homePageSettings.value.work_items) blockProps.items = homePageSettings.value.work_items;
                            if (homePageSettings.value.work_button_text) blockProps.buttonText = homePageSettings.value.work_button_text;
                            if (homePageSettings.value.work_button_link) blockProps.buttonLink = homePageSettings.value.work_button_link;
                        } else if (block.component === 'Faq') {
                            if (homePageSettings.value.faq_title) blockProps.title = homePageSettings.value.faq_title;
                            if (homePageSettings.value.faq_items) blockProps.items = homePageSettings.value.faq_items;
                        } else if (block.component === 'WhyChooseUs') {
                            if (homePageSettings.value.benefits_title) blockProps.title = homePageSettings.value.benefits_title;
                            if (homePageSettings.value.benefits_items) blockProps.items = homePageSettings.value.benefits_items;
                        } else if (block.component === 'FeedbackForm') {
                            if (homePageSettings.value.contact_title) blockProps.title = homePageSettings.value.contact_title;
                            if (homePageSettings.value.contact_subtitle) blockProps.subtitle = homePageSettings.value.contact_subtitle;
                            if (homePageSettings.value.contact_form_hint_text) blockProps.hintText = homePageSettings.value.contact_form_hint_text;
                        }
                    }
                    
                    return {
                        key: block.key,
                        component,
                        props: blockProps,
                    };
                })
                .filter(block => block !== null);
        });

        const fetchBlocks = async () => {
            try {
                const response = await fetch('/api/public/home-page-blocks');
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        blocks.value = data.data;
                    }
                }
            } catch (error) {
                console.error('Error fetching home page blocks:', error);
                // Fallback к дефолтному порядку при ошибке
                blocks.value = [
                    { key: 'hero_banner', component: 'HeroBanner', settings: {} },
                    { key: 'decisions', component: 'Decisions', settings: {} },
                    { key: 'quiz', component: 'Quiz', settings: {} },
                    { key: 'how_work', component: 'HowWork', settings: {} },
                    { key: 'faq', component: 'Faq', settings: {} },
                    { key: 'why_choose_us', component: 'WhyChooseUs', settings: {} },
                    { key: 'cases_block', component: 'CasesBlock', settings: {} },
                    { key: 'feedback_form', component: 'FeedbackForm', settings: { title: 'Остались вопросы?', description: 'Напишите нам, и мы с удовольствием ответим на все ваши вопросы' } },
                ];
            } finally {
                loading.value = false;
                // Скрываем прелоадер после загрузки контента
                hidePreloader();
            }
        };

        const canonicalUrl = computed(() => {
            return window.location.origin + '/';
        });

        const combinedSchema = computed(() => {
            const schemas = [];
            
            // Website Schema - создаем дефолтную, если нет из настроек
            const websiteSchema = seoSettings.value.website_schema || {
                '@context': 'https://schema.org',
                '@type': 'WebSite',
                'name': seoSettings.value.site_name || 'МНКА',
                'description': seoSettings.value.site_description || 'Профессиональные услуги по подбору и оформлению земельных участков',
                'url': window.location.origin,
            };
            schemas.push(websiteSchema);
            
            // Organization Schema - создаем дефолтную, если нет из настроек
            const organizationSchema = seoSettings.value.organization_schema || {
                '@context': 'https://schema.org',
                '@type': 'Organization',
                'name': seoSettings.value.site_name || 'МНКА',
                'url': window.location.origin,
            };
            schemas.push(organizationSchema);

            return schemas;
        });

        const fetchSeoSettings = async () => {
            try {
                const response = await fetch('/api/public/seo-settings');
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.data) {
                        // Проверяем, что site_name не пустой и не равен 'Laravel' (дефолтное значение Laravel)
                        const isValidSiteName = data.data.site_name && 
                                                data.data.site_name.trim() !== '' && 
                                                data.data.site_name !== 'Laravel' &&
                                                data.data.site_name.toLowerCase() !== 'laravel';
                        
                        // Проверяем, что site_description не пустой
                        const isValidDescription = data.data.site_description && 
                                                   data.data.site_description.trim() !== '';
                        
                        // Объединяем загруженные данные с дефолтными, но используем дефолтные значения
                        // если API вернул пустые или некорректные данные
                        const newSeoSettings = {
                            ...defaultSeoSettings,
                            ...data.data,
                            // Используем данные из API только если они валидны, иначе используем дефолтные
                            site_name: isValidSiteName ? data.data.site_name : defaultSeoSettings.site_name,
                            site_description: isValidDescription ? data.data.site_description : defaultSeoSettings.site_description,
                            site_keywords: (data.data.site_keywords && data.data.site_keywords.trim() !== '') 
                                ? data.data.site_keywords 
                                : defaultSeoSettings.site_keywords,
                        };
                        
                        seoSettings.value = newSeoSettings;
                    }
                }
            } catch (error) {
                console.error('Error fetching SEO settings:', error);
                // Значения по умолчанию уже установлены при инициализации
                // Не сбрасываем их, чтобы избежать мерцания title/description
            }
        };

        const fetchHomePageSettings = async () => {
            try {
                const response = await fetch('/api/public/home-page-settings');
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        homePageSettings.value = data.data;
                    }
                }
            } catch (error) {
                console.error('Error fetching home page settings:', error);
            }
        };

        onMounted(() => {
            fetchSeoSettings();
            fetchHomePageSettings();
            fetchBlocks();
        });

        // Computed свойства для SEO с защитой от некорректных значений
        const seoTitle = computed(() => {
            const title = seoSettings.value.site_name;
            // Проверяем, что title валидный
            if (!title || title.trim() === '' || title === 'Laravel' || title.toLowerCase() === 'laravel') {
                return defaultSeoSettings.site_name;
            }
            return title;
        });
        
        const seoDescription = computed(() => {
            const desc = seoSettings.value.site_description;
            // Проверяем, что description валидный
            if (!desc || desc.trim() === '') {
                return defaultSeoSettings.site_description;
            }
            return desc;
        });
        
        const seoKeywords = computed(() => {
            const keywords = seoSettings.value.site_keywords;
            // Проверяем, что keywords валидные
            if (!keywords || keywords.trim() === '') {
                return defaultSeoSettings.site_keywords;
            }
            return keywords;
        });

        return {
            blocks,
            loading,
            orderedBlocks,
            seoSettings,
            seoTitle,
            seoDescription,
            seoKeywords,
            canonicalUrl,
            combinedSchema,
        };
    },
};
</script>

