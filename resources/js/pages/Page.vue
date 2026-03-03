<template>
    <div class="page-content">
        <SEOHead
            v-if="page"
            :title="pageTitle"
            :description="pageDescription"
            :keywords="pageKeywords"
            :og-image="ogImage"
            :canonical="canonicalUrl"
            :schema="pageSchema"
        />
        
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-muted-foreground">Загрузка...</div>
        </div>

        <div v-else-if="error" class="w-full max-w-[1200px] mx-auto px-3 sm:px-4 md:px-5 py-8 sm:py-12 md:py-16 lg:py-20">
            <div class="text-center">
                <h1 class="text-3xl font-semibold text-foreground mb-4">Страница не найдена</h1>
                <p class="text-muted-foreground mb-6">{{ error }}</p>
                <router-link
                    to="/"
                    class="inline-block px-6 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors"
                >
                    Вернуться на главную
                </router-link>
            </div>
        </div>

        <article v-else-if="page" class="w-full max-w-[1200px] mx-auto px-3 sm:px-4 md:px-5 py-8 sm:py-12 md:py-16 lg:py-20">
            <header class="mb-8">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-semibold text-foreground mb-4">
                    {{ page.title }}
                </h1>
            </header>

            <div 
                v-if="page.content" 
                class="prose prose-lg max-w-none"
                v-html="page.content"
            ></div>

            <div v-else class="text-muted-foreground">
                <p>Содержимое страницы пока не добавлено.</p>
            </div>
        </article>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import SEOHead from '../components/SEOHead.vue';

export default {
    name: 'Page',
    components: {
        SEOHead,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const page = ref(null);
        const loading = ref(true);
        const error = ref(null);

        const fetchPage = async () => {
            loading.value = true;
            error.value = null;

            try {
                // Проверяем, не является ли это зарезервированным маршрутом
                const currentPath = route.path;
                const reservedPaths = ['/admin', '/login', '/register', '/forgot-password', '/reset-password', '/403', '/404', '/500'];
                const isReserved = reservedPaths.some(path => currentPath.startsWith(path));
                
                if (isReserved) {
                    // Сразу редиректим на 404 без показа промежуточного сообщения
                    router.replace('/404');
                    return;
                }

                const slug = route.params.slug;
                const response = await fetch(`/api/public/pages/${slug}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        page.value = data.data;
                        
                        // Устанавливаем SEO мета-теги
                        if (page.value.seo_title) {
                            document.title = page.value.seo_title;
                        } else {
                            document.title = page.value.title;
                        }
                        
                        // Устанавливаем meta description
                        let metaDescription = document.querySelector('meta[name="description"]');
                        if (!metaDescription) {
                            metaDescription = document.createElement('meta');
                            metaDescription.setAttribute('name', 'description');
                            document.head.appendChild(metaDescription);
                        }
                        if (page.value.seo_description) {
                            metaDescription.setAttribute('content', page.value.seo_description);
                        }
                        
                        // Устанавливаем meta keywords
                        if (page.value.seo_keywords) {
                            let metaKeywords = document.querySelector('meta[name="keywords"]');
                            if (!metaKeywords) {
                                metaKeywords = document.createElement('meta');
                                metaKeywords.setAttribute('name', 'keywords');
                                document.head.appendChild(metaKeywords);
                            }
                            metaKeywords.setAttribute('content', page.value.seo_keywords);
                        }
                    } else {
                        // Страница не найдена - сразу редиректим на 404
                        router.replace('/404');
                        return;
                    }
                } else if (response.status === 404) {
                    // Страница не найдена - сразу редиректим на 404
                    router.replace('/404');
                    return;
                } else {
                    error.value = 'Ошибка загрузки страницы';
                }
            } catch (err) {
                console.error('Error fetching page:', err);
                // При ошибке также редиректим на 404, если это похоже на 404
                if (err.message && err.message.includes('404')) {
                    router.replace('/404');
                    return;
                }
                error.value = 'Ошибка загрузки страницы';
            } finally {
                loading.value = false;
            }
        };

        onMounted(() => {
            fetchPage();
        });

        const canonicalUrl = computed(() => {
            if (!page.value) return '';
            return window.location.origin + '/' + page.value.slug;
        });

        // SEO computed properties
        const pageTitle = computed(() => {
            if (!page.value) return 'Страница - МНКА';
            if (page.value.seo_title) return page.value.seo_title;
            return `${page.value.title} - МНКА`;
        });

        const pageDescription = computed(() => {
            if (!page.value) return 'Информационная страница сайта МНКА.';
            if (page.value.seo_description) return page.value.seo_description;
            // Создаем описание на основе title, если seo_description отсутствует
            return `${page.value.title} - подробная информация на сайте МНКА. Профессиональные услуги по подбору и оформлению земельных участков.`;
        });

        const pageKeywords = computed(() => {
            if (!page.value) return 'страница, информация';
            if (page.value.seo_keywords) return page.value.seo_keywords;
            return `${page.value.title}, информация, услуги, земельные участки, МНКА`;
        });

        const ogImage = computed(() => {
            // Можно добавить логику для получения изображения из контента страницы
            return '';
        });

        const pageSchema = computed(() => {
            if (!page.value) return null;
            
            return {
                '@context': 'https://schema.org',
                '@type': 'WebPage',
                'name': page.value.title,
                'description': pageDescription.value,
                'url': canonicalUrl.value,
            };
        });

        return {
            page,
            loading,
            error,
            canonicalUrl,
            ogImage,
            pageSchema,
            pageTitle,
            pageDescription,
            pageKeywords,
        };
    },
};
</script>

<style scoped>
.prose {
    color: rgb(var(--foreground));
}

.prose h1 {
    font-size: 2.25em;
    font-weight: 700;
    margin-top: 0;
    margin-bottom: 0.5em;
}

.prose h2 {
    font-size: 1.875em;
    font-weight: 600;
    margin-top: 1.5em;
    margin-bottom: 0.75em;
}

.prose h3 {
    font-size: 1.5em;
    font-weight: 600;
    margin-top: 1.25em;
    margin-bottom: 0.5em;
}

.prose p {
    margin-bottom: 1.25em;
    line-height: 1.75;
}

.prose ul, .prose ol {
    margin-left: 1.5em;
    margin-bottom: 1.25em;
}

.prose li {
    margin-bottom: 0.5em;
}

.prose a {
    color: #688E67;
    text-decoration: underline;
}

.prose a:hover {
    color: #5a7a5a;
}

.prose strong {
    font-weight: 600;
}

.prose em {
    font-style: italic;
}

.prose u {
    text-decoration: underline;
}
</style>

