<template>
    <footer class="bg-[#6C7B6D] mt-auto">
        <div class="w-full px-3 sm:px-4 md:px-5 pt-8 pb-6 md:pt-12 md:pb-8">
            <div class="w-full max-w-[1200px] mx-auto">
                <!-- Основной контент: 4 колонки (2 на мобилке) -->
                <div v-if="settings" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                    <!-- Колонка A: О компании -->
                    <div class="footer-column">
                        <h3 class="footer-column-title">О компании</h3>
                        <nav class="footer-nav">
                            <router-link to="/about" class="footer-link">О MNKA</router-link>
                            <router-link to="/about#team" class="footer-link">Команда</router-link>
                            <router-link to="/contacts" class="footer-link">Контакты</router-link>
                            <router-link to="/about#requisites" class="footer-link">Реквизиты</router-link>
                        </nav>
                    </div>

                    <!-- Колонка B: Клиентам -->
                    <div class="footer-column">
                        <h3 class="footer-column-title">Клиентам</h3>
                        <nav class="footer-nav">
                            <router-link to="/products" class="footer-link">Решения</router-link>
                            <router-link to="/services" class="footer-link">Услуги</router-link>
                            <router-link to="/cases" class="footer-link">Кейсы</router-link>
                            <router-link to="/#faq" class="footer-link">Вопросы и ответы</router-link>
                        </nav>
                    </div>

                    <!-- Колонка C: Социальные сети -->
                    <div class="footer-column">
                        <h3 class="footer-column-title">Социальные сети</h3>
                        <div class="flex gap-4 items-center mt-4">
                            <!-- VK -->
                            <a 
                                v-if="settings.social_networks?.vk && (settings.vk_icon_svg || settings.vk_icon?.url)"
                                :href="settings.social_networks.vk"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="hover:opacity-80 transition-opacity flex items-center justify-center social-icon-link"
                                aria-label="VK"
                            >
                                <img
                                    v-if="settings.vk_icon?.url"
                                    :src="settings.vk_icon.url"
                                    alt="VK"
                                    class="social-icon-img"
                                />
                                <div v-else-if="settings.vk_icon_svg" v-html="settings.vk_icon_svg" class="social-icon-svg"></div>
                            </a>

                            <!-- Instagram -->
                            <a 
                                v-if="settings.social_networks?.instagram && (settings.instagram_icon_svg || settings.instagram_icon?.url)"
                                :href="settings.social_networks.instagram"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="hover:opacity-80 transition-opacity flex items-center justify-center social-icon-link"
                                aria-label="Instagram"
                            >
                                <img
                                    v-if="settings.instagram_icon?.url"
                                    :src="settings.instagram_icon.url"
                                    alt="Instagram"
                                    class="social-icon-img"
                                />
                                <div v-else-if="settings.instagram_icon_svg" v-html="settings.instagram_icon_svg" class="social-icon-svg"></div>
                            </a>

                            <!-- Telegram -->
                            <a 
                                v-if="settings.social_networks?.telegram && (settings.telegram_icon_svg || settings.telegram_icon?.url)"
                                :href="settings.social_networks.telegram"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="hover:opacity-80 transition-opacity flex items-center justify-center social-icon-link"
                                aria-label="Telegram"
                            >
                                <img
                                    v-if="settings.telegram_icon?.url"
                                    :src="settings.telegram_icon.url"
                                    alt="Telegram"
                                    class="social-icon-img"
                                />
                                <div v-else-if="settings.telegram_icon_svg" v-html="settings.telegram_icon_svg" class="social-icon-svg"></div>
                            </a>
                        </div>
                    </div>

                    <!-- Колонка D: Документы и политика -->
                    <div class="footer-column">
                        <h3 class="footer-column-title">Документы</h3>
                        <nav class="footer-nav">
                            <router-link
                                v-if="settings.privacy_policy_link"
                                :to="settings.privacy_policy_link"
                                class="footer-link"
                            >
                                Политика конфиденциальности
                            </router-link>
                            <router-link to="/personal-data" class="footer-link">Обработка персональных данных</router-link>
                            <router-link to="/terms" class="footer-link">Пользовательское соглашение</router-link>
                        </nav>
                    </div>
                </div>

                <!-- Разделитель -->
                <div class="border-t border-white/20 mt-8 md:mt-10"></div>

                <!-- Нижняя строка: Копирайт и дополнительные ссылки -->
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- Копирайт -->
                    <div class="text-white text-sm sm:text-base text-center sm:text-left">
                        {{ settings?.copyright || '© MNKA 2026. Все права защищены.' }}
                    </div>

                    <!-- Дополнительные ссылки -->
                    <div class="flex flex-wrap items-center justify-center sm:justify-end gap-4 text-sm">
                        <a href="#" class="text-white hover:opacity-80 transition-opacity">Сообщить о нарушении</a>
                        <a href="#" class="text-white hover:opacity-80 transition-opacity">Правила применения</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
    name: 'Footer',
    setup() {
        const settings = ref(null);
        const loading = ref(true);

        const fetchSettings = async () => {
            try {
                const response = await fetch('/api/public/footer/settings');
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        settings.value = data.data;
                    }
                }
            } catch (error) {
                console.error('Error fetching footer settings:', error);
            } finally {
                loading.value = false;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            settings,
            loading,
        };
    },
};
</script>

<style scoped>
/* Колонки футера */
.footer-column {
    display: flex;
    flex-direction: column;
}

.footer-column-title {
    color: #FFFFFF;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.5;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.footer-nav {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.footer-link {
    color: #FFFFFF;
    text-decoration: none;
    font-size: 0.9375rem;
    line-height: 1.5;
    transition: opacity 0.2s ease;
    display: block;
}

.footer-link:hover {
    opacity: 0.8;
}

/* Стили для иконок соцсетей */
.social-icon-link {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
}

.social-icon-svg {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.social-icon-svg svg {
    width: 100%;
    height: 100%;
    max-width: 40px;
    max-height: 40px;
    object-fit: contain;
}

.social-icon-img {
    max-width: 40px;
    max-height: 40px;
    width: auto;
    height: auto;
    object-fit: contain;
}

/* Адаптивность */
@media (max-width: 640px) {
    .footer-column {
        margin-bottom: 1.5rem;
    }
    
    .footer-column:last-child {
        margin-bottom: 0;
    }
}

@media (min-width: 1024px) {
    .footer-column-title {
        font-size: 1.0625rem;
    }
    
    .footer-link {
        font-size: 1rem;
    }
}

</style>
