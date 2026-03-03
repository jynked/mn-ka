import './bootstrap';
import { createApp } from 'vue';
import { createStore } from 'vuex';
import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';

// Store
const store = createStore({
    state: {
        user: null,
        token: localStorage.getItem('token') || null,
        menu: [],
        notifications: [],
        theme: localStorage.getItem('theme') || 'light',
        // Кеш для публичных данных
        publicCache: {
            products: {
                data: null,
                timestamp: null,
                loading: false,
            },
            services: {
                data: null,
                timestamp: null,
                loading: false,
            },
            productsMinimal: {
                data: null,
                timestamp: null,
                loading: false,
            },
            servicesMinimal: {
                data: null,
                timestamp: null,
                loading: false,
            },
        },
    },
    mutations: {
        SET_USER(state, user) {
            state.user = user;
        },
        SET_TOKEN(state, token) {
            state.token = token;
            if (token) {
                localStorage.setItem('token', token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            } else {
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
            }
        },
        SET_MENU(state, menu) {
            state.menu = menu;
        },
        SET_NOTIFICATIONS(state, notifications) {
            state.notifications = notifications;
        },
        LOGOUT(state) {
            state.user = null;
            state.token = null;
            state.menu = [];
            state.notifications = [];
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
        },
        SET_THEME(state, theme) {
            state.theme = theme;
            localStorage.setItem('theme', theme);
            // Применяем тему к документу
            const html = document.documentElement;
            const body = document.body;
            if (theme === 'dark') {
                html.classList.add('dark');
                html.setAttribute('data-theme', 'dark');
                if (body) body.classList.add('dark');
                html.style.colorScheme = 'dark';
            } else {
                html.classList.remove('dark');
                html.setAttribute('data-theme', 'light');
                if (body) body.classList.remove('dark');
                html.style.colorScheme = 'light';
            }
        },
        // Мутации для публичного кеша
        SET_PUBLIC_PRODUCTS(state, { data, minimal = false }) {
            const key = minimal ? 'productsMinimal' : 'products';
            state.publicCache[key].data = data;
            state.publicCache[key].timestamp = Date.now();
            state.publicCache[key].loading = false;
        },
        SET_PUBLIC_SERVICES(state, { data, minimal = false }) {
            const key = minimal ? 'servicesMinimal' : 'services';
            state.publicCache[key].data = data;
            state.publicCache[key].timestamp = Date.now();
            state.publicCache[key].loading = false;
        },
        SET_PUBLIC_LOADING(state, { type, minimal = false, loading }) {
            const key = minimal ? `${type}Minimal` : type;
            if (state.publicCache[key]) {
                state.publicCache[key].loading = loading;
            }
        },
        CLEAR_PUBLIC_CACHE(state) {
            state.publicCache = {
                products: { data: null, timestamp: null, loading: false },
                services: { data: null, timestamp: null, loading: false },
                productsMinimal: { data: null, timestamp: null, loading: false },
                servicesMinimal: { data: null, timestamp: null, loading: false },
            };
        },
    },
    actions: {
        async login({ commit, dispatch }, credentials) {
            try {
                const response = await axios.post('/api/auth/login', credentials);
                commit('SET_TOKEN', response.data.token);
                commit('SET_USER', response.data.user);
                // Загружаем меню после успешной авторизации
                await dispatch('fetchMenu');
                await dispatch('fetchNotifications');
                return { success: true };
            } catch (error) {
                return { success: false, error: error.response?.data?.message || 'Ошибка авторизации' };
            }
        },
        async register({ commit, dispatch }, userData) {
            try {
                const response = await axios.post('/api/auth/register', userData);
                commit('SET_TOKEN', response.data.token);
                commit('SET_USER', response.data.user);
                // Загружаем меню после успешной регистрации
                await dispatch('fetchMenu');
                await dispatch('fetchNotifications');
                return { success: true };
            } catch (error) {
                return { success: false, error: error.response?.data?.message || 'Ошибка регистрации' };
            }
        },
        async logout({ commit }) {
            try {
                await axios.post('/api/auth/logout');
            } catch (error) {
                console.error('Logout error:', error);
            }
            commit('LOGOUT');
        },
        async fetchUser({ commit, state }) {
            if (!state.token) return;
            try {
                const response = await axios.get('/api/auth/user');
                commit('SET_USER', response.data.user);
            } catch (error) {
                commit('LOGOUT');
            }
        },
        async fetchMenu({ commit, state }) {
            if (!state.token) return;
            try {
                const response = await axios.get('/api/admin/menu');
                console.log('Menu loaded:', response.data.menu);
                commit('SET_MENU', response.data.menu);
            } catch (error) {
                console.error('Menu fetch error:', error);
            }
        },
        async fetchNotifications({ commit, state }) {
            if (!state.token) return;
            try {
                const response = await axios.get('/api/notifications');
                commit('SET_NOTIFICATIONS', response.data.notifications);
            } catch (error) {
                console.error('Notifications fetch error:', error);
            }
        },
        toggleTheme({ commit, state }) {
            const newTheme = state.theme === 'dark' ? 'light' : 'dark';
            commit('SET_THEME', newTheme);
        },
        // Actions для загрузки публичных данных с кешированием
        async fetchPublicProducts({ commit, state }, { minimal = false, force = false } = {}) {
            const key = minimal ? 'productsMinimal' : 'products';
            const cache = state.publicCache[key];
            const CACHE_TTL = 5 * 60 * 1000; // 5 минут
            
            // Проверяем кеш
            if (!force && cache.data && cache.timestamp) {
                const age = Date.now() - cache.timestamp;
                if (age < CACHE_TTL) {
                    return cache.data; // Возвращаем кешированные данные
                }
            }
            
            // Если уже идет загрузка, возвращаем промис существующего запроса
            if (cache.loading) {
                // Ждем завершения текущей загрузки
                return new Promise((resolve) => {
                    const checkInterval = setInterval(() => {
                        if (!cache.loading && cache.data) {
                            clearInterval(checkInterval);
                            resolve(cache.data);
                        }
                    }, 100);
                });
            }
            
            commit('SET_PUBLIC_LOADING', { type: 'products', minimal, loading: true });
            
            try {
                const url = minimal 
                    ? '/api/public/products?active=1&minimal=1'
                    : '/api/public/products?active=1';
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });
                
                if (response.ok) {
                    const result = await response.json();
                    const data = result.data || [];
                    commit('SET_PUBLIC_PRODUCTS', { data, minimal });
                    return data;
                } else {
                    throw new Error('Failed to fetch products');
                }
            } catch (error) {
                console.error('Error fetching products:', error);
                commit('SET_PUBLIC_LOADING', { type: 'products', minimal, loading: false });
                // Возвращаем кешированные данные, если есть, даже если они устарели
                if (cache.data) {
                    return cache.data;
                }
                throw error;
            }
        },
        async fetchPublicServices({ commit, state }, { minimal = false, force = false } = {}) {
            const key = minimal ? 'servicesMinimal' : 'services';
            const cache = state.publicCache[key];
            const CACHE_TTL = 5 * 60 * 1000; // 5 минут
            
            // Проверяем кеш
            if (!force && cache.data && cache.timestamp) {
                const age = Date.now() - cache.timestamp;
                if (age < CACHE_TTL) {
                    return cache.data; // Возвращаем кешированные данные
                }
            }
            
            // Если уже идет загрузка, возвращаем промис существующего запроса
            if (cache.loading) {
                // Ждем завершения текущей загрузки
                return new Promise((resolve) => {
                    const checkInterval = setInterval(() => {
                        if (!cache.loading && cache.data) {
                            clearInterval(checkInterval);
                            resolve(cache.data);
                        }
                    }, 100);
                });
            }
            
            commit('SET_PUBLIC_LOADING', { type: 'services', minimal, loading: true });
            
            try {
                const url = minimal 
                    ? '/api/public/services?active=1&minimal=1'
                    : '/api/public/services?active=1';
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });
                
                if (response.ok) {
                    const result = await response.json();
                    const data = result.data || [];
                    commit('SET_PUBLIC_SERVICES', { data, minimal });
                    return data;
                } else {
                    throw new Error('Failed to fetch services');
                }
            } catch (error) {
                console.error('Error fetching services:', error);
                commit('SET_PUBLIC_LOADING', { type: 'services', minimal, loading: false });
                // Возвращаем кешированные данные, если есть, даже если они устарели
                if (cache.data) {
                    return cache.data;
                }
                throw error;
            }
        },
    },
    getters: {
        isAuthenticated: (state) => !!state.token,
        user: (state) => state.user,
        menu: (state) => state.menu,
        notifications: (state) => state.notifications,
        theme: (state) => state.theme,
        isDarkMode: (state) => state.theme === 'dark',
        unreadNotificationsCount: (state) => {
            return state.notifications.filter(n => !n.read).length;
        },
        hasRole: (state) => (roleSlug) => {
            if (!state.user || !state.user.roles) return false;
            return state.user.roles.some(role => role.slug === roleSlug);
        },
        hasAnyRole: (state) => (roleSlugs) => {
            if (!state.user || !state.user.roles) return false;
            return state.user.roles.some(role => roleSlugs.includes(role.slug));
        },
        isAdmin: (state) => {
            if (!state.user || !state.user.roles) return false;
            return state.user.roles.some(role => role.slug === 'admin');
        },
        // Геттеры для публичных данных
        publicProducts: (state) => (minimal = false) => {
            const key = minimal ? 'productsMinimal' : 'products';
            return state.publicCache[key].data || [];
        },
        publicServices: (state) => (minimal = false) => {
            const key = minimal ? 'servicesMinimal' : 'services';
            return state.publicCache[key].data || [];
        },
        isPublicProductsLoading: (state) => (minimal = false) => {
            const key = minimal ? 'productsMinimal' : 'products';
            return state.publicCache[key].loading || false;
        },
        isPublicServicesLoading: (state) => (minimal = false) => {
            const key = minimal ? 'servicesMinimal' : 'services';
            return state.publicCache[key].loading || false;
        },
    },
});

// Router
const routes = [
    // Публичные маршруты
    {
        path: '/',
        component: () => import('./layouts/PublicLayout.vue'),
        meta: { requiresAuth: false },
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('./pages/Home.vue'),
            },
            {
                path: 'products',
                name: 'products',
                component: () => import('./pages/ProductsPage.vue'),
            },
            {
                path: 'products/:slug',
                name: 'product',
                component: () => import('./pages/ProductPage.vue'),
            },
            {
                path: 'services',
                name: 'services',
                component: () => import('./pages/ServicesPage.vue'),
            },
            {
                path: 'services/:slug',
                name: 'service',
                component: () => import('./pages/ServicePage.vue'),
            },
            {
                path: 'contacts',
                name: 'contacts',
                component: () => import('./pages/ContactPage.vue'),
            },
            {
                path: 'about',
                name: 'about',
                component: () => import('./pages/AboutPage.vue'),
            },
            {
                path: 'cases',
                name: 'cases',
                component: () => import('./pages/CasesPage.vue'),
            },
            {
                path: 'cases/:slug',
                name: 'case',
                component: () => import('./pages/CasePage.vue'),
            },
            {
                path: 'search',
                name: 'search',
                component: () => import('./pages/SearchPage.vue'),
            },
            {
                path: ':slug',
                name: 'page',
                component: () => import('./pages/Page.vue'),
                beforeEnter: async (to, from, next) => {
                    // Исключаем зарезервированные пути
                    const reservedPaths = ['admin', 'login', 'register', 'forgot-password', 'reset-password', '403', '404', '500'];
                    if (reservedPaths.includes(to.params.slug)) {
                        next('/404');
                        return;
                    }
                    
                    // Проверяем существование страницы через API
                    try {
                        const response = await fetch(`/api/public/pages/${to.params.slug}`);
                        if (!response.ok || response.status === 404) {
                            // Страница не найдена - сразу редиректим на 404
                            next('/404');
                            return;
                        }
                        // Страница существует - продолжаем
                        next();
                    } catch (err) {
                        // При ошибке также редиректим на 404
                        console.error('Error checking page existence:', err);
                        next('/404');
                    }
                },
            },
        ],
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('./pages/auth/Login.vue'),
        meta: { requiresAuth: false },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('./pages/auth/Register.vue'),
        meta: { requiresAuth: false },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('./pages/auth/ForgotPassword.vue'),
        meta: { requiresAuth: false },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('./pages/auth/ResetPassword.vue'),
        meta: { requiresAuth: false },
    },
    {
        path: '/admin',
        component: () => import('./layouts/AdminLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: 'documentation',
                name: 'admin.documentation',
                component: () => import('./pages/admin/Documentation.vue'),
            },
            {
                path: '',
                name: 'admin.dashboard',
                component: () => import('./pages/admin/Dashboard.vue'),
            },
            {
                path: 'products',
                name: 'admin.products',
                component: () => import('./pages/admin/Products.vue'),
            },
            {
                path: 'categories',
                name: 'admin.categories',
                component: () => import('./pages/admin/Categories.vue'),
            },
            {
                path: 'services',
                name: 'admin.services',
                component: () => import('./pages/admin/Services.vue'),
            },
            {
                path: 'cases',
                name: 'admin.cases',
                component: () => import('./pages/admin/Cases.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'cases/create',
                name: 'admin.cases.create',
                component: () => import('./pages/admin/cases/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'cases/:id/edit',
                name: 'admin.cases.edit',
                component: () => import('./pages/admin/cases/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'media',
                name: 'admin.media',
                component: () => import('./pages/admin/Media.vue'),
            },
            {
                path: 'media/:id/edit',
                name: 'admin.media.edit',
                component: () => import('./pages/admin/EditImage.vue'),
            },
            {
                path: 'users',
                name: 'admin.users',
                component: () => import('./pages/admin/Users.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'roles',
                name: 'admin.roles',
                component: () => import('./pages/admin/Roles.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'subscription',
                name: 'admin.subscription',
                component: () => import('./pages/admin/Subscription.vue'),
            },
            {
                path: 'versions',
                name: 'admin.versions',
                component: () => import('./pages/admin/Versions.vue'),
            },
            {
                path: 'settings',
                name: 'admin.settings',
                component: () => import('./pages/admin/Settings.vue'),
            },
            {
                path: 'settings/telegram',
                name: 'admin.settings.telegram',
                component: () => import('./pages/admin/settings/TelegramSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'settings/contacts',
                name: 'admin.settings.contacts',
                component: () => import('./pages/admin/settings/ContactSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'settings/about',
                name: 'admin.settings.about',
                component: () => import('./pages/admin/settings/AboutSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'settings/footer',
                name: 'admin.settings.footer',
                component: () => import('./pages/admin/settings/FooterSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'settings/email',
                name: 'admin.settings.email',
                component: () => import('./pages/admin/settings/EmailSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'settings/smtp',
                name: 'admin.settings.smtp',
                component: () => import('./pages/admin/settings/SmtpSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'settings/case-cards',
                name: 'admin.settings.case-cards',
                component: () => import('./pages/admin/settings/CaseCardSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'settings/javascript',
                name: 'admin.settings.javascript',
                component: () => import('./pages/admin/settings/JSSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'menus',
                name: 'admin.menus',
                component: () => import('./pages/admin/Menus.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin'] },
            },
            {
                path: 'banners/home',
                name: 'admin.banners.home',
                component: () => import('./pages/admin/banners/HomeBanner.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'notifications',
                name: 'admin.notifications',
                component: () => import('./pages/admin/Notifications.vue'),
            },
            {
                path: 'notifications/quiz/:quizId',
                name: 'admin.notifications.quiz',
                component: () => import('./pages/admin/notifications/QuizDetails.vue'),
            },
            // Decision Block routes
            {
                path: 'decisions/chapters',
                name: 'admin.decisions.chapters',
                component: () => import('./pages/admin/decisions/Chapters.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/chapters/create',
                name: 'admin.decisions.chapters.create',
                component: () => import('./pages/admin/decisions/chapters/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/chapters/:id/edit',
                name: 'admin.decisions.chapters.edit',
                component: () => import('./pages/admin/decisions/chapters/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/products',
                name: 'admin.decisions.products',
                component: () => import('./pages/admin/decisions/Products.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/products/create',
                name: 'admin.decisions.products.create',
                component: () => import('./pages/admin/decisions/products/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/products/:id/edit',
                name: 'admin.decisions.products.edit',
                component: () => import('./pages/admin/decisions/products/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/services',
                name: 'admin.decisions.services',
                component: () => import('./pages/admin/decisions/Services.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/services/create',
                name: 'admin.decisions.services.create',
                component: () => import('./pages/admin/decisions/services/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/services/:id/edit',
                name: 'admin.decisions.services.edit',
                component: () => import('./pages/admin/decisions/services/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/cases',
                name: 'admin.decisions.cases',
                component: () => import('./pages/admin/decisions/Cases.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/cases/create',
                name: 'admin.decisions.cases.create',
                component: () => import('./pages/admin/decisions/cases/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/cases/:id/edit',
                name: 'admin.decisions.cases.edit',
                component: () => import('./pages/admin/decisions/cases/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/options',
                name: 'admin.decisions.options',
                component: () => import('./pages/admin/decisions/Options.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/options/create',
                name: 'admin.decisions.options.create',
                component: () => import('./pages/admin/decisions/options/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/options/:id/edit',
                name: 'admin.decisions.options.edit',
                component: () => import('./pages/admin/decisions/options/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/option-trees',
                name: 'admin.decisions.option-trees',
                component: () => import('./pages/admin/decisions/OptionTrees.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/option-trees/create',
                name: 'admin.decisions.option-trees.create',
                component: () => import('./pages/admin/decisions/option-trees/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/option-trees/:id/edit',
                name: 'admin.decisions.option-trees.edit',
                component: () => import('./pages/admin/decisions/option-trees/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/instances',
                name: 'admin.decisions.instances',
                component: () => import('./pages/admin/decisions/Instances.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/instances/create',
                name: 'admin.decisions.instances.create',
                component: () => import('./pages/admin/decisions/instances/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/instances/:id/edit',
                name: 'admin.decisions.instances.edit',
                component: () => import('./pages/admin/decisions/instances/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'decisions/settings',
                name: 'admin.decisions.settings',
                component: () => import('./pages/admin/decisions/Settings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            // Quiz routes
            {
                path: 'quizzes',
                name: 'admin.quizzes.index',
                component: () => import('./pages/admin/quizzes/Quizzes.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'quizzes/create',
                name: 'admin.quizzes.create',
                component: () => import('./pages/admin/quizzes/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'quizzes/:id/edit',
                name: 'admin.quizzes.edit',
                component: () => import('./pages/admin/quizzes/Form.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'quizzes/settings',
                name: 'admin.quizzes.settings',
                component: () => import('./pages/admin/quizzes/Settings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            // Blocks routes
            {
                path: 'blocks/how-work',
                name: 'admin.blocks.how-work',
                component: () => import('./pages/admin/blocks/HowWorkSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'blocks/faq',
                name: 'admin.blocks.faq',
                component: () => import('./pages/admin/blocks/FaqSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'blocks/why-choose-us',
                name: 'admin.blocks.why-choose-us',
                component: () => import('./pages/admin/blocks/WhyChooseUsSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'blocks/cases',
                name: 'admin.blocks.cases',
                component: () => import('./pages/admin/blocks/CasesBlockSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'modal-settings',
                name: 'admin.modal-settings',
                component: () => import('./pages/admin/ModalSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'product-requests',
                name: 'admin.product-requests',
                component: () => import('./pages/admin/ProductRequests.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'pages',
                name: 'admin.pages',
                component: () => import('./pages/admin/pages/Pages.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'pages/create',
                name: 'admin.pages.create',
                component: () => import('./pages/admin/pages/PageEdit.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'pages/:id/edit',
                name: 'admin.pages.edit',
                component: () => import('./pages/admin/pages/PageEdit.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'pages/home',
                name: 'admin.pages.home',
                component: () => import('./pages/admin/pages/HomePage.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
            {
                path: 'seo-settings',
                name: 'admin.seo-settings',
                component: () => import('./pages/admin/SeoSettings.vue'),
                meta: { requiresAuth: true, requiresRole: ['admin', 'manager'] },
            },
        ],
    },
    // Страницы ошибок
    {
        path: '/403',
        name: 'error.403',
        component: () => import('./pages/errors/Forbidden403.vue'),
        meta: { requiresAuth: false },
    },
    {
        path: '/404',
        name: 'error.404',
        component: () => import('./pages/errors/NotFound404.vue'),
        meta: { requiresAuth: false },
    },
    {
        path: '/500',
        name: 'error.500',
        component: () => import('./pages/errors/ServerError500.vue'),
        meta: { requiresAuth: false },
    },
    // Catch-all 404 - должен быть последним
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('./pages/errors/NotFound404.vue'),
        meta: { requiresAuth: false },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        // Если есть сохраненная позиция (например, при использовании кнопки "назад")
        if (savedPosition) {
            return savedPosition;
        }
        // Если есть хеш в URL, скроллим к элементу
        if (to.hash) {
            return {
                el: to.hash,
                behavior: 'smooth',
            };
        }
        // Иначе скроллим в начало страницы
        return {
            top: 0,
            left: 0,
            behavior: 'smooth',
        };
    },
});

// Navigation guard
router.beforeEach(async (to, from, next) => {
    try {
        const isAuthenticated = store.getters.isAuthenticated;
        
        if (to.meta.requiresAuth && !isAuthenticated) {
            next('/login');
            return;
        }
        
        if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
            next('/admin');
            return;
        }
        
        if (to.meta.requiresRole) {
            // Проверка ролей
            const requiredRoles = Array.isArray(to.meta.requiresRole) 
                ? to.meta.requiresRole 
                : [to.meta.requiresRole];
            
            // Если пользователь аутентифицирован, но данные еще не загружены, ждем загрузки
            if (isAuthenticated && !store.state.user) {
                try {
                    await store.dispatch('fetchUser');
                } catch (error) {
                    console.error('Error fetching user:', error);
                    // Если не удалось загрузить пользователя, перенаправляем на логин
                    next('/login');
                    return;
                }
            }
            
            // Проверяем наличие пользователя и ролей
            const user = store.state.user;
            if (!user) {
                // Если пользователь не загружен, но есть токен - разрешаем доступ
                // Проверка будет на уровне компонента или API
                console.warn('User not loaded, allowing route access for component-level check');
                next();
                return;
            }
            
            // Проверяем наличие ролей
            if (!user.roles || !Array.isArray(user.roles) || user.roles.length === 0) {
                // Если у пользователя нет ролей, перенаправляем на 403
                console.warn('User has no roles, redirecting to 403');
                next('/403');
                return;
            }
            
            // Проверяем роли через getter
            if (store.getters.hasAnyRole && typeof store.getters.hasAnyRole === 'function') {
                const hasRole = store.getters.hasAnyRole(requiredRoles);
                if (!hasRole) {
                    // Пользователь не имеет нужной роли - перенаправляем на 403
                    console.warn('User does not have required roles:', requiredRoles, 'User roles:', user.roles.map(r => r.slug));
                    next('/403');
                    return;
                }
            } else {
                // Fallback: проверяем роли напрямую
                const userRoleSlugs = user.roles.map(role => role.slug);
                const hasRequiredRole = requiredRoles.some(role => userRoleSlugs.includes(role));
                if (!hasRequiredRole) {
                    console.warn('User does not have required roles (fallback check):', requiredRoles, 'User roles:', userRoleSlugs);
                    next('/403');
                    return;
                }
            }
        }
        
        next();
    } catch (error) {
        console.error('Navigation guard error:', error);
        // Если ошибка при проверке маршрута, пробуем продолжить
        next();
    }
});

// Обработка ошибок при загрузке компонентов маршрута
let errorRedirectCount = 0;
const MAX_ERROR_REDIRECTS = 2;
let lastErrorPath = null;
let lastErrorTime = 0;
const ERROR_RETRY_DELAY = 2000; // 2 секунды между попытками

router.onError((error, to) => {
    console.error('Router component loading error:', error, 'Route:', to);
    
    // Проверяем, не прошло ли достаточно времени с последней ошибки
    const now = Date.now();
    if (now - lastErrorTime < ERROR_RETRY_DELAY) {
        console.warn('Error retry too soon, ignoring');
        return;
    }
    lastErrorTime = now;
    
    // Если это ошибка загрузки модуля (MIME type или failed to fetch), перезагружаем страницу
    const errorMessage = error?.message || error?.toString() || '';
    const isModuleError = errorMessage.includes('MIME type') || 
                         errorMessage.includes('Failed to fetch dynamically imported module') ||
                         errorMessage.includes('Failed to load module script');
    
    if (isModuleError) {
        console.warn('Module loading error detected, reloading page to clear cache...');
        // Перезагружаем страницу с очисткой кеша
        setTimeout(() => {
            window.location.reload();
        }, 500);
        return;
    }
    
    // Если это та же ошибка, что и раньше, не перенаправляем снова
    if (to && to.path === lastErrorPath) {
        console.error('Same error path detected. Preventing redirect loop.');
        errorRedirectCount = 0;
        lastErrorPath = null;
        return;
    }
    
    // Предотвращаем бесконечный цикл
    if (errorRedirectCount >= MAX_ERROR_REDIRECTS) {
        console.error('Max error redirects reached. Stopping redirect loop.');
        errorRedirectCount = 0;
        lastErrorPath = null;
        // Перезагружаем страницу как последнюю попытку
        setTimeout(() => {
            window.location.reload();
        }, 1000);
        return;
    }
    
    // Если ошибка при загрузке самого компонента 404, не перенаправляем
    if (to && (to.path === '/404' || to.name === 'error.404' || to.name === 'not-found')) {
        console.error('Error loading 404 component. Cannot redirect to 404.');
        errorRedirectCount = 0;
        lastErrorPath = null;
        return;
    }
    
    // Если ошибка при загрузке главной страницы или публичных маршрутов, не перенаправляем на 404
    if (to && (to.path === '/' || to.name === 'home' || to.path === '')) {
        console.error('Error loading home page. Cannot redirect to 404.');
        errorRedirectCount = 0;
        lastErrorPath = null;
        return;
    }
    
    // Если ошибка при загрузке админского маршрута, пробуем загрузить снова
    if (to && to.path && to.path.startsWith('/admin')) {
        // Для админских маршрутов не прерываем навигацию
        return;
    }
    
    // Если ошибка при загрузке публичных маршрутов (products, services, cases и т.д.), не перенаправляем
    const publicRoutes = ['/products', '/services', '/cases', '/about', '/contacts'];
    if (to && to.path && publicRoutes.some(route => to.path.startsWith(route))) {
        console.error('Error loading public route. Not redirecting to 404.');
        errorRedirectCount = 0;
        lastErrorPath = null;
        return;
    }
    
    // Для других маршрутов перенаправляем на 404 только если это не критическая ошибка
    lastErrorPath = to?.path || null;
    errorRedirectCount++;
    
    // Используем setTimeout для предотвращения немедленного повторного вызова
    setTimeout(() => {
        // Проверяем, что мы не в цикле
        if (errorRedirectCount > MAX_ERROR_REDIRECTS) {
            console.error('Too many redirects. Stopping.');
            errorRedirectCount = 0;
            lastErrorPath = null;
            return;
        }
        
        router.push('/404').catch((err) => {
            console.error('Failed to redirect to 404:', err);
            errorRedirectCount = 0;
            lastErrorPath = null;
        });
    }, 100);
});

// Сбрасываем счетчик при успешной навигации
router.afterEach(() => {
    errorRedirectCount = 0;
    lastErrorPath = null;
});

// Initialize app
import App from './App.vue';
const app = createApp(App);

// Set up axios defaults
if (store.state.token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${store.state.token}`;
}

// Инициализация темы при загрузке приложения
// Применяем тему сразу, до монтирования приложения
const savedTheme = localStorage.getItem('theme') || 'light';
const html = document.documentElement;
if (savedTheme === 'dark') {
    html.classList.add('dark');
    html.setAttribute('data-theme', 'dark');
    html.style.colorScheme = 'dark';
} else {
    html.classList.remove('dark');
    html.setAttribute('data-theme', 'light');
    html.style.colorScheme = 'light';
}
// Устанавливаем начальное состояние в store
store.state.theme = savedTheme;

// Initialize user and menu on app start
store.dispatch('fetchUser').then((user) => {
    if (user) {
        store.dispatch('fetchMenu');
        store.dispatch('fetchNotifications');
    }
});

app.use(store);
app.use(router);

// Глобальная функция для скрытия прелоадера
window.hidePreloader = () => {
    const preloader = document.getElementById('preloader');
    if (preloader && !preloader.classList.contains('hidden')) {
        preloader.classList.add('hidden');
        // Удаляем прелоадер из DOM после анимации
        setTimeout(() => {
            if (preloader.parentNode) {
                preloader.remove();
            }
        }, 500);
    }
};

// Mount app
// Монтируем приложение в контейнер #app (единая точка входа)
const appContainer = document.getElementById('app');
if (appContainer) {
    app.mount('#app');
    
    // Отслеживаем загрузку контента через router
    let contentCheckInterval;
    let hasContent = false;
    
    // Функция проверки наличия контента в видимой области
    const checkContentLoaded = () => {
        // Проверяем наличие контента в #app
        const appContent = document.querySelector('#app > *');
        if (!appContent) return false;
        
        // Проверяем, что контент имеет размеры (отрендерился)
        const contentHeight = appContent.offsetHeight;
        if (contentHeight < 50) return false;
        
        // Проверяем наличие основных элементов на странице
        const hasVisibleContent = 
            document.querySelector('.home-page, .product-page, .service-page, .case-page, .products-page, .services-page, .cases-page, .about-page, .contact-page, .admin-layout, .page-content') ||
            document.querySelector('[class*="-page"], [class*="layout"]');
        
        return !!hasVisibleContent;
    };
    
    // Запускаем проверку после монтирования Vue
    setTimeout(() => {
        contentCheckInterval = setInterval(() => {
            if (checkContentLoaded()) {
                hasContent = true;
                clearInterval(contentCheckInterval);
                
                // Даем небольшую задержку для рендеринга изображений и стилей
                setTimeout(() => {
                    window.hidePreloader();
                }, 200);
            }
        }, 50);
        
        // Принудительно скрываем прелоадер через 5 секунд (защита от зависания)
        setTimeout(() => {
            if (!hasContent) {
                clearInterval(contentCheckInterval);
                console.warn('Preloader: forced hide after 5s timeout');
                window.hidePreloader();
            }
        }, 5000);
    }, 100);
}
