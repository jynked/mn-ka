<template>
    <div class="seo-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки SEO</h1>
            <p class="text-muted-foreground mt-1">Глобальные настройки SEO, Open Graph и Schema.org</p>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-muted-foreground">Загрузка...</div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm text-red-600">{{ error }}</p>
        </div>

        <form v-else @submit.prevent="saveSettings" class="space-y-6">
            <!-- Основные настройки -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Основные настройки</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Название сайта
                    </label>
                    <input
                        v-model="form.site_name"
                        type="text"
                        placeholder="МНКА"
                        maxlength="255"
                        class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Описание сайта
                    </label>
                    <textarea
                        v-model="form.site_description"
                        rows="3"
                        placeholder="Профессиональные услуги по подбору и оформлению земельных участков"
                        maxlength="1000"
                        class="w-full px-4 py-3 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] resize-none"
                    ></textarea>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Осталось символов: {{ 1000 - (form.site_description?.length || 0) }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Ключевые слова (через запятую)
                    </label>
                    <input
                        v-model="form.site_keywords"
                        type="text"
                        placeholder="земельные участки, кадастр, недвижимость"
                        maxlength="500"
                        class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                    />
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.allow_indexing"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Разрешить индексацию поисковыми системами</span>
                    </label>
                </div>
            </div>

            <!-- Open Graph -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Open Graph (для социальных сетей)</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Изображение по умолчанию (URL)
                    </label>
                    <input
                        v-model="form.default_og_image"
                        type="text"
                        placeholder="/img/og-image.jpg"
                        class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                    />
                    <p class="mt-1 text-xs text-muted-foreground">
                        Рекомендуемый размер: 1200x630 пикселей
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Тип контента OG
                        </label>
                        <select
                            v-model="form.og_type"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        >
                            <option value="website">Website</option>
                            <option value="article">Article</option>
                            <option value="business.business">Business</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Название сайта для OG
                        </label>
                        <input
                            v-model="form.og_site_name"
                            type="text"
                            placeholder="МНКА"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        />
                    </div>
                </div>
            </div>

            <!-- Twitter Cards -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Twitter Cards</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Тип карточки
                        </label>
                        <select
                            v-model="form.twitter_card"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        >
                            <option value="summary">Summary</option>
                            <option value="summary_large_image">Summary Large Image</option>
                            <option value="app">App</option>
                            <option value="player">Player</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Twitter аккаунт сайта
                        </label>
                        <input
                            v-model="form.twitter_site"
                            type="text"
                            placeholder="@yoursite"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Twitter создателя
                        </label>
                        <input
                            v-model="form.twitter_creator"
                            type="text"
                            placeholder="@creator"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        />
                    </div>
                </div>
            </div>

            <!-- Организация (Schema.org) -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Информация об организации (Schema.org)</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Название организации
                        </label>
                        <input
                            v-model="form.organization_name"
                            type="text"
                            placeholder="ООО Лагом"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Лого организации (URL)
                        </label>
                        <input
                            v-model="form.organization_logo"
                            type="text"
                            placeholder="/img/logo.png"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Телефон
                        </label>
                        <input
                            v-model="form.organization_phone"
                            type="text"
                            placeholder="+7 (999) 123-45-67"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Email
                        </label>
                        <input
                            v-model="form.organization_email"
                            type="email"
                            placeholder="info@example.com"
                            class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67]"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Адрес организации
                    </label>
                    <textarea
                        v-model="form.organization_address"
                        rows="2"
                        placeholder="г. Москва, ул. Примерная, д. 1"
                        class="w-full px-4 py-3 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] resize-none"
                    ></textarea>
                </div>
            </div>

            <!-- Robots.txt -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Robots.txt</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Содержимое robots.txt
                    </label>
                    <textarea
                        v-model="form.robots_txt"
                        rows="10"
                        placeholder="User-agent: *&#10;Disallow: /admin/&#10;Allow: /"
                        class="w-full px-4 py-3 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] resize-none font-mono text-sm"
                    ></textarea>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Настройте правила для поисковых роботов
                    </p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-4">
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors disabled:opacity-50 font-medium"
                >
                    <span v-if="saving">Сохранение...</span>
                    <span v-else>Сохранить настройки</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'SeoSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);

        const form = ref({
            site_name: '',
            site_description: '',
            site_keywords: '',
            default_og_image: '',
            og_type: 'website',
            og_site_name: '',
            twitter_card: 'summary_large_image',
            twitter_site: '',
            twitter_creator: '',
            organization_name: '',
            organization_logo: '',
            organization_phone: '',
            organization_email: '',
            organization_address: '',
            allow_indexing: true,
            robots_txt: '',
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;

            try {
                const response = await axios.get('/api/v1/seo-settings');
                if (response.data && response.data.data) {
                    Object.assign(form.value, response.data.data);
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching SEO settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;

            try {
                await axios.put('/api/v1/seo-settings', form.value);
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки SEO успешно сохранены',
                    timer: 2000,
                    showConfirmButton: false,
                });
            } catch (err) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка!',
                    text: err.response?.data?.message || 'Не удалось сохранить настройки',
                });
            } finally {
                saving.value = false;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            error,
            form,
            saveSettings,
        };
    },
};
</script>

