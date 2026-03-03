<template>
    <div class="settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки</h1>
            <p class="text-muted-foreground mt-1">Настройки системы</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Telegram Settings Card -->
            <router-link
                to="/admin/settings/telegram"
                class="bg-card rounded-lg border border-border p-6 hover:border-accent transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.173 1.861-.921 6.664-.921 6.664s-.518 2.952-2.069 3.456c-1.556.501-3.896-.979-4.883-1.515-.987-.535-7.311-2.847-7.311-2.847S1.499 13.826 2.726 13.55c1.227-.276 5.334-.706 5.334-.706s1.406.133 1.891-.267c.485-.4.299-1.269.299-1.269s.454-3.023.73-4.436c.278-1.413 1.87-1.304 1.87-1.304s4.526.354 7.532.798c.974.148 1.14.667 1.14.667z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Telegram бот</h2>
                        <p class="text-sm text-muted-foreground">Настройка уведомлений</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Настройка интеграции с Telegram для отправки уведомлений и ошибок
                </p>
            </router-link>

            <!-- Contact Settings Card -->
            <router-link
                to="/admin/settings/contacts"
                class="bg-card rounded-lg border border-border p-6 hover:border-accent transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Контакты</h2>
                        <p class="text-sm text-muted-foreground">Настройка контактной информации</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Редактирование контактных данных для страницы контактов
                </p>
            </router-link>

            <!-- About Settings Card -->
            <router-link
                to="/admin/settings/about"
                class="bg-card rounded-lg border border-border p-6 hover:border-accent transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">О нас</h2>
                        <p class="text-sm text-muted-foreground">Настройка страницы О компании</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Редактирование контента для страницы "О нас"
                </p>
            </router-link>

            <!-- Footer Settings Card -->
            <router-link
                to="/admin/settings/footer"
                class="bg-card rounded-lg border border-border p-6 hover:border-accent transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-gray-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Футер</h2>
                        <p class="text-sm text-muted-foreground">Настройка футера сайта</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Редактирование настроек футера: контакты, соцсети, меню
                </p>
            </router-link>

            <!-- Email Settings Card -->
            <router-link
                to="/admin/settings/email"
                class="bg-card rounded-lg border border-border p-6 hover:border-accent transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-indigo-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Email</h2>
                        <p class="text-sm text-muted-foreground">Настройка email для форм</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Настройка email адреса для получения форм обратной связи с сайта
                </p>
            </router-link>

            <!-- SMTP Settings Card -->
            <router-link
                to="/admin/settings/smtp"
                class="bg-card rounded-lg border border-border p-6 hover:border-accent transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-cyan-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">SMTP</h2>
                        <p class="text-sm text-muted-foreground">Настройка SMTP сервера</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Настройка параметров SMTP сервера для отправки почты
                </p>
            </router-link>

            <!-- JavaScript Settings Card -->
            <router-link
                to="/admin/settings/javascript"
                class="bg-card rounded-lg border border-border p-6 hover:border-accent transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-yellow-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">JavaScript код</h2>
                        <p class="text-sm text-muted-foreground">Аналитика и отслеживание</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Добавление JavaScript кода для Яндекс.Метрики, Google Analytics и других сервисов
                </p>
            </router-link>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Settings',
};
</script>

