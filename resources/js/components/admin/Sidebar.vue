<template>
    <!-- Overlay для мобильной версии -->
    <div
        v-if="isMobileSidebarOpen"
        class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity duration-300"
        @click="closeMobileSidebar"
    ></div>
    
    <aside
        class="relative flex flex-col bg-sidebar-background text-sidebar-foreground transition-all duration-300 border-r border-sidebar-border fixed lg:static inset-y-0 left-0 z-50 lg:z-auto"
        :class="[
            isCollapsed ? 'lg:w-16' : 'lg:w-72',
            isMobileSidebarOpen ? 'flex w-72' : 'hidden lg:flex'
        ]"
    >
        <div class="flex h-16 items-center border-b border-sidebar-border justify-between px-6">
            <h1 v-show="!isCollapsed || isMobileSidebarOpen" class="text-xl font-bold text-sidebar-foreground">CMS Admin</h1>
            <div class="flex items-center gap-2">
                <!-- Кнопка закрытия для мобильной версии -->
                <button
                    v-if="isMobileSidebarOpen"
                    @click="closeMobileSidebar"
                    class="lg:hidden rounded-xl p-2 hover:bg-sidebar-accent transition-all"
                    type="button"
                    title="Закрыть меню"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <!-- Кнопка сворачивания для десктопа -->
                <button
                    @click="toggleCollapse"
                    class="hidden lg:block rounded-xl p-2 hover:bg-sidebar-accent transition-all"
                    :title="isCollapsed ? 'Развернуть меню' : 'Свернуть меню'"
                    type="button"
                >
                <svg
                    class="h-5 w-5 transition-transform duration-300"
                    :class="isCollapsed ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                </button>
            </div>
        </div>
        <nav class="flex-1 overflow-y-auto space-y-1 nav-scroll p-4">
            <div v-if="!menu || menu.length === 0" class="text-center text-muted-foreground py-8">
                Загрузка меню...
            </div>
            <template v-else v-for="item in menu" :key="item.route || item.title">
                <router-link
                    v-if="!item.children"
                    :to="{ name: item.route }"
                    class="nav-menu-item flex items-center rounded-xl text-sm font-medium transition-all text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground px-4 py-3 gap-3"
                    :class="isCollapsed ? 'justify-center' : ''"
                    active-class="router-link-active"
                    :title="isCollapsed ? item.title : ''"
                >
                    <span class="h-5 w-5 shrink-0">
                        <component :is="getIconComponent(item.icon)" />
                    </span>
                    <span v-if="!isCollapsed">{{ item.title }}</span>
                </router-link>
                <div v-else class="rounded-xl overflow-hidden transition-all" :class="isExpanded(item) ? 'bg-sidebar-accent/30' : ''">
                    <button
                        @click="toggleExpanded(item)"
                        class="w-full flex items-center rounded-xl text-sm font-medium transition-all text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground px-4 py-3 gap-3"
                        :class="isCollapsed ? 'justify-center' : 'justify-between'"
                        :title="isCollapsed ? item.title : ''"
                    >
                        <div class="flex items-center gap-3">
                            <span class="h-5 w-5 shrink-0">
                                <component :is="getIconComponent(item.icon)" />
                            </span>
                            <span v-if="!isCollapsed">{{ item.title }}</span>
                        </div>
                        <svg
                            v-if="!isCollapsed"
                            class="h-4 w-4 shrink-0 transition-transform duration-200"
                            :class="isExpanded(item) ? 'rotate-180' : ''"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div
                        v-if="!isCollapsed"
                        class="overflow-hidden transition-all duration-300 ease-in-out"
                        :class="isExpanded(item) ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'"
                    >
                        <div class="pl-4 pr-2 py-2 space-y-1">
                            <router-link
                                v-for="child in item.children"
                                :key="child.route || child.title"
                                :to="getChildRoute(child)"
                                class="resource-submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                            >
                                <span class="h-4 w-4 shrink-0">
                                    <component :is="getIconComponent(child.icon)" />
                                </span>
                                <span>{{ child.title }}</span>
                            </router-link>
                        </div>
                    </div>
                </div>
            </template>
        </nav>
        <div class="border-t border-sidebar-border space-y-3 p-4">
            <div class="flex items-center gap-3 px-2" :class="isCollapsed ? 'justify-center' : ''">
                <div class="h-10 w-10 rounded-full bg-accent/20 border border-accent/30 flex items-center justify-center text-sm font-bold text-accent shrink-0">
                    {{ userInitials }}
                </div>
                <div v-if="!isCollapsed" class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-sidebar-foreground">{{ user?.name || 'Пользователь' }}</p>
                    <p class="text-xs text-muted-foreground truncate">{{ user?.email || '' }}</p>
                </div>
            </div>
            <button
                @click="handleLogout"
                class="w-full flex justify-start gap-2 px-4 py-2 text-muted-foreground hover:text-foreground rounded-md hover:bg-accent/10"
                :class="isCollapsed ? 'justify-center' : ''"
                :title="isCollapsed ? 'Выйти' : ''"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span v-if="!isCollapsed">Выйти</span>
            </button>
        </div>
    </aside>
</template>

<script>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

// Icon components
const HomeIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>' };
const DatabaseIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>' };
const ShoppingCartIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>' };
const FolderIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>' };
const CreditCardIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>' };
const ImageIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>' };
const UsersIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>' };
const ShieldIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>' };
const SettingsIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>' };
const BookIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>' };
const BellIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>' };
const MenuIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>' };
const GridIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>' };
const LayersIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>' };
const PackageIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>' };
const BriefcaseIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' };
const HelpCircleIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' };
const ListIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>' };
const InfoIcon = { template: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' };

export default {
    name: 'Sidebar',
    setup() {
        const store = useStore();
        const router = useRouter();
        const expandedItems = ref([]);
        const isCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
        const isMobileSidebarOpen = ref(false);

        const menu = computed(() => store.getters.menu);
        const user = computed(() => store.getters.user);
        
        const toggleCollapse = () => {
            isCollapsed.value = !isCollapsed.value;
            localStorage.setItem('sidebarCollapsed', isCollapsed.value.toString());
            // Закрываем все раскрытые подменю при сворачивании
            if (isCollapsed.value) {
                expandedItems.value = [];
            }
        };
        
        const handleToggleMobileSidebar = () => {
            isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
        };

        const handleRouteChange = () => {
            if (window.innerWidth < 1024) {
                isMobileSidebarOpen.value = false;
            }
        };

        const closeMobileSidebar = () => {
            isMobileSidebarOpen.value = false;
        };

        // Загружаем меню при монтировании компонента
        onMounted(() => {
            if (store.getters.isAuthenticated) {
                // Всегда загружаем меню при монтировании, чтобы получить актуальное
                store.dispatch('fetchMenu');
            }
            window.addEventListener('toggle-mobile-sidebar', handleToggleMobileSidebar);
            router.afterEach(handleRouteChange);
        });

        onUnmounted(() => {
            window.removeEventListener('toggle-mobile-sidebar', handleToggleMobileSidebar);
        });
        
        // Отслеживаем изменения меню для отладки
        watch(() => menu.value, (newMenu) => {
            console.log('Menu updated in Sidebar:', newMenu);
        }, { immediate: true });
        const userInitials = computed(() => {
            if (!user.value?.name) return 'U';
            const names = user.value.name.split(' ');
            return names.map(n => n[0]).join('').toUpperCase().substring(0, 2);
        });

        const toggleExpanded = (item) => {
            const index = expandedItems.value.indexOf(item.title);
            if (index > -1) {
                expandedItems.value.splice(index, 1);
            } else {
                expandedItems.value.push(item.title);
            }
        };

        const isExpanded = (item) => {
            return expandedItems.value.includes(item.title);
        };

        const getIconComponent = (iconName) => {
            const icons = {
                home: HomeIcon,
                database: DatabaseIcon,
                'shopping-cart': ShoppingCartIcon,
                folder: FolderIcon,
                'credit-card': CreditCardIcon,
                image: ImageIcon,
                users: UsersIcon,
                shield: ShieldIcon,
                settings: SettingsIcon,
                book: BookIcon,
                bell: BellIcon,
                menu: MenuIcon,
                grid: GridIcon,
                layers: LayersIcon,
                package: PackageIcon,
                briefcase: BriefcaseIcon,
                'help-circle': HelpCircleIcon,
                list: ListIcon,
                info: InfoIcon,
            };
            return icons[iconName] || HomeIcon;
        };

        const handleLogout = async () => {
            await store.dispatch('logout');
            router.push('/login');
        };

        const getChildRoute = (child) => {
            if (child.route_params) {
                return {
                    name: child.route,
                    params: child.route_params,
                };
            }
            return { name: child.route };
        };

        return {
            menu,
            user,
            userInitials,
            expandedItems,
            isCollapsed,
            isMobileSidebarOpen,
            toggleCollapse,
            toggleExpanded,
            isExpanded,
            getIconComponent,
            handleLogout,
            closeMobileSidebar,
            getChildRoute,
        };
    },
};
</script>

