<template>
    <div class="about-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки страницы "О нас"</h1>
            <p class="text-muted-foreground mt-1">
                Редактирование контента, который отображается на странице "О компании".
            </p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка настроек...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Settings Form -->
        <div v-if="!loading && settings" class="space-y-6">
            <!-- Баннер и описание -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Баннер и описание</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Изображение баннера
                    </label>
                    <div class="space-y-3">
                        <div v-if="settings.banner_image" class="relative">
                            <img
                                :src="getImageUrl(settings.banner_image)"
                                alt="Banner preview"
                                class="w-full max-w-md h-auto object-cover rounded-lg border border-border"
                            />
                            <div class="mt-2 flex gap-2">
                                <button
                                    type="button"
                                    @click="openMediaModal('banner_image')"
                                    class="px-4 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20 text-sm"
                                >
                                    Изменить изображение
                                </button>
                                <button
                                    type="button"
                                    @click="settings.banner_image = null"
                                    class="px-4 py-2 bg-destructive/10 text-destructive border border-destructive/40 rounded hover:bg-destructive/20 text-sm"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                        <div v-else>
                            <button
                                type="button"
                                @click="openMediaModal('banner_image')"
                                class="w-full h-32 border-2 border-dashed border-border rounded flex items-center justify-center hover:bg-muted/10 transition-colors"
                            >
                                <span class="text-muted-foreground">Выберите изображение баннера</span>
                            </button>
                        </div>
                        <div>
                            <label class="block text-xs text-muted-foreground mb-1">Или введите URL вручную:</label>
                            <input
                                type="text"
                                v-model="settings.banner_image"
                                placeholder="/img/banner.jpg или полный URL"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="settings.banner_overlay"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Наложение (overlay) на баннер</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Описание о компании (HTML)
                    </label>
                    <textarea
                        v-model="settings.description"
                        rows="6"
                        placeholder="Введите описание о компании..."
                        class="w-full px-3 py-2 border border-border rounded bg-background text-sm"
                    ></textarea>
                </div>
            </div>

            <!-- Статистика -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Статистика</h2>
                    <button
                        @click="addStatistic"
                        class="px-4 py-2 text-sm bg-accent text-accent-foreground rounded hover:bg-accent/90"
                    >
                        + Добавить
                    </button>
                </div>

                <div v-if="!settings.statistics || settings.statistics.length === 0" class="text-center py-4 text-muted-foreground">
                    Нет элементов статистики
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(stat, index) in settings.statistics"
                        :key="index"
                        class="p-4 border border-border rounded-lg space-y-3"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Иконка
                                </label>
                                <div class="space-y-2">
                                    <div v-if="stat.icon" class="flex items-center gap-3">
                                        <img
                                            :src="getImageUrl(stat.icon)"
                                            alt="Icon preview"
                                            class="w-12 h-12 object-contain border border-border rounded"
                                        />
                                        <div class="flex-1">
                                            <input
                                                type="text"
                                                v-model="stat.icon"
                                                placeholder="/img/system/1.svg"
                                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            @click="openMediaModal('statistics', index, 'icon')"
                                            class="px-3 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20 text-xs whitespace-nowrap"
                                        >
                                            Выбрать
                                        </button>
                                    </div>
                                    <div v-else class="flex gap-2">
                                        <input
                                            type="text"
                                            v-model="stat.icon"
                                            placeholder="/img/system/1.svg"
                                            class="flex-1 h-10 px-3 border border-border rounded bg-background text-sm"
                                        />
                                        <button
                                            type="button"
                                            @click="openMediaModal('statistics', index, 'icon')"
                                            class="px-3 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20 text-xs whitespace-nowrap"
                                        >
                                            Выбрать
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Текст
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        v-model="stat.text"
                                        placeholder="93% клиентов приходят по рекомендации"
                                        class="flex-1 h-10 px-3 border border-border rounded bg-background text-sm"
                                    />
                                    <button
                                        @click="removeStatistic(index)"
                                        class="px-4 py-2 text-sm bg-red-500 text-white rounded hover:bg-red-600"
                                    >
                                        Удалить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Кому мы помогаем -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Кому мы помогаем</h2>
                    <button
                        @click="addClient"
                        class="px-4 py-2 text-sm bg-accent text-accent-foreground rounded hover:bg-accent/90"
                    >
                        + Добавить
                    </button>
                </div>

                <div v-if="!settings.clients || settings.clients.length === 0" class="text-center py-4 text-muted-foreground">
                    Нет элементов
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(client, index) in settings.clients"
                        :key="index"
                        class="p-4 border border-border rounded-lg space-y-3"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Заголовок *
                                </label>
                                <input
                                    type="text"
                                    v-model="client.title"
                                    placeholder="Девелоперам и застройщикам"
                                    class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Описание
                                </label>
                                <input
                                    type="text"
                                    v-model="client.description"
                                    placeholder="Помогаем с подбором участков..."
                                    class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Иконка
                                </label>
                                <div class="space-y-2">
                                    <div v-if="client.icon" class="flex items-center gap-3">
                                        <img
                                            :src="getImageUrl(client.icon)"
                                            alt="Icon preview"
                                            class="w-12 h-12 object-contain border border-border rounded"
                                        />
                                        <div class="flex-1">
                                            <input
                                                type="text"
                                                v-model="client.icon"
                                                placeholder="/img/system/4.svg"
                                                class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            @click="openMediaModal('clients', index, 'icon')"
                                            class="px-3 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20 text-xs whitespace-nowrap"
                                        >
                                            Выбрать
                                        </button>
                                    </div>
                                    <div v-else class="flex gap-2">
                                        <input
                                            type="text"
                                            v-model="client.icon"
                                            placeholder="/img/system/4.svg"
                                            class="flex-1 h-10 px-3 border border-border rounded bg-background text-sm"
                                        />
                                        <button
                                            type="button"
                                            @click="openMediaModal('clients', index, 'icon')"
                                            class="px-3 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20 text-xs whitespace-nowrap"
                                        >
                                            Выбрать
                                        </button>
                                    </div>
                                    <button
                                        @click="removeClient(index)"
                                        class="w-full px-4 py-2 text-sm bg-red-500 text-white rounded hover:bg-red-600"
                                    >
                                        Удалить элемент
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Команда -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Наша команда</h2>
                    <button
                        @click="addTeamMember"
                        class="px-4 py-2 text-sm bg-accent text-accent-foreground rounded hover:bg-accent/90"
                    >
                        + Добавить
                    </button>
                </div>

                <div v-if="!settings.team || settings.team.length === 0" class="text-center py-4 text-muted-foreground">
                    Нет членов команды
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(member, index) in settings.team"
                        :key="index"
                        class="p-4 border border-border rounded-lg space-y-3"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Имя *
                                </label>
                                <input
                                    type="text"
                                    v-model="member.name"
                                    placeholder="Иван Иванов"
                                    class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Должность
                                </label>
                                <input
                                    type="text"
                                    v-model="member.position"
                                    placeholder="CEO МНКА"
                                    class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Фото
                                </label>
                                <div class="space-y-2">
                                    <div v-if="member.photo" class="relative">
                                        <img
                                            :src="getImageUrl(member.photo)"
                                            alt="Photo preview"
                                            class="w-full max-w-xs h-auto object-cover rounded-lg border border-border"
                                        />
                                        <div class="mt-2 flex gap-2">
                                            <button
                                                type="button"
                                                @click="openMediaModal('team', index, 'photo')"
                                                class="px-4 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20 text-xs"
                                            >
                                                Изменить фото
                                            </button>
                                            <button
                                                type="button"
                                                @click="member.photo = null"
                                                class="px-4 py-2 bg-destructive/10 text-destructive border border-destructive/40 rounded hover:bg-destructive/20 text-xs"
                                            >
                                                Удалить фото
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <div class="flex gap-2">
                                            <input
                                                type="text"
                                                v-model="member.photo"
                                                placeholder="/img/team/1.jpg"
                                                class="flex-1 h-10 px-3 border border-border rounded bg-background text-sm"
                                            />
                                            <button
                                                type="button"
                                                @click="openMediaModal('team', index, 'photo')"
                                                class="px-3 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20 text-xs whitespace-nowrap"
                                            >
                                                Выбрать
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        @click="removeTeamMember(index)"
                                        class="w-full px-4 py-2 text-sm bg-red-500 text-white rounded hover:bg-red-600"
                                    >
                                        Удалить из команды
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Преимущества (не используется на странице About - используется компонент WhyChooseUs) -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6 opacity-60">
                <div class="flex items-center justify-between">
                    <div>
                    <h2 class="text-xl font-semibold text-foreground">Почему выбирают нас</h2>
                        <p class="text-xs text-muted-foreground mt-1">
                            ⚠️ Это поле больше не используется на странице "О нас". Используется компонент "Почему выбирают нас" из настроек блоков.
                        </p>
                    </div>
                    <button
                        @click="addBenefit"
                        class="px-4 py-2 text-sm bg-accent text-accent-foreground rounded hover:bg-accent/90"
                        disabled
                    >
                        + Добавить
                    </button>
                </div>

                <div v-if="!settings.benefits || settings.benefits.length === 0" class="text-center py-4 text-muted-foreground">
                    Нет преимуществ
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(benefit, index) in settings.benefits"
                        :key="index"
                        class="p-4 border border-border rounded-lg space-y-3"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Заголовок *
                                </label>
                                <input
                                    type="text"
                                    v-model="benefit.title"
                                    placeholder="500+ участков в базе"
                                    class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Описание
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        v-model="benefit.description"
                                        placeholder="Большой выбор готовых предложений"
                                        class="flex-1 h-10 px-3 border border-border rounded bg-background text-sm"
                                    />
                                    <button
                                        @click="removeBenefit(index)"
                                        class="px-4 py-2 text-sm bg-red-500 text-white rounded hover:bg-red-600"
                                    >
                                        Удалить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent text-accent-foreground rounded hover:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                >
                    {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                </button>
            </div>
        </div>

        <!-- Media Selector Modal -->
        <div
            v-if="showMediaModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto p-4"
            @click.self="showMediaModal = false"
        >
            <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-7xl max-h-[95vh] overflow-hidden flex flex-col my-auto">
                <div class="p-4 border-b border-border flex items-center justify-between flex-shrink-0">
                    <h3 class="text-lg font-semibold">Выберите изображение</h3>
                    <button
                        @click="showMediaModal = false"
                        class="text-muted-foreground hover:text-foreground w-8 h-8 flex items-center justify-center rounded hover:bg-muted/10"
                    >
                        ✕
                    </button>
                </div>
                <div class="flex-1 overflow-auto h-full">
                    <Media
                        :selection-mode="true"
                        :count-file="1"
                        @file-selected="handleImageSelected"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import Media from '../Media.vue';

export default {
    name: 'AboutSettings',
    components: {
        Media,
    },
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const settings = ref(null);
        const showMediaModal = ref(false);
        const currentMediaTarget = ref(null); // { type: 'banner_image' | 'statistics' | 'clients' | 'team', index?: number, field?: string }

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                const response = await axios.get('/api/v1/about-settings');
                settings.value = response.data.data;
                
                // Инициализируем массивы, если их нет
                if (!settings.value.statistics || !Array.isArray(settings.value.statistics)) {
                    settings.value.statistics = [];
                }
                if (!settings.value.clients || !Array.isArray(settings.value.clients)) {
                    settings.value.clients = [];
                }
                if (!settings.value.team || !Array.isArray(settings.value.team)) {
                    settings.value.team = [];
                }
                if (!settings.value.benefits || !Array.isArray(settings.value.benefits)) {
                    settings.value.benefits = [];
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching about settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const addStatistic = () => {
            if (!settings.value.statistics) {
                settings.value.statistics = [];
            }
            settings.value.statistics.push({
                icon: '',
                text: '',
            });
        };

        const removeStatistic = (index) => {
            if (settings.value.statistics && settings.value.statistics.length > index) {
                settings.value.statistics.splice(index, 1);
            }
        };

        const addClient = () => {
            if (!settings.value.clients) {
                settings.value.clients = [];
            }
            settings.value.clients.push({
                title: '',
                description: '',
                icon: '',
            });
        };

        const removeClient = (index) => {
            if (settings.value.clients && settings.value.clients.length > index) {
                settings.value.clients.splice(index, 1);
            }
        };

        const addTeamMember = () => {
            if (!settings.value.team) {
                settings.value.team = [];
            }
            settings.value.team.push({
                name: '',
                position: '',
                photo: '',
            });
        };

        const removeTeamMember = (index) => {
            if (settings.value.team && settings.value.team.length > index) {
                settings.value.team.splice(index, 1);
            }
        };

        const addBenefit = () => {
            if (!settings.value.benefits) {
                settings.value.benefits = [];
            }
            settings.value.benefits.push({
                title: '',
                description: '',
            });
        };

        const removeBenefit = (index) => {
            if (settings.value.benefits && settings.value.benefits.length > index) {
                settings.value.benefits.splice(index, 1);
            }
        };

        const openMediaModal = (type, index = null, field = null) => {
            currentMediaTarget.value = { type, index, field };
            showMediaModal.value = true;
        };

        const handleImageSelected = (file) => {
            if (!file || file.type !== 'photo') return;

            // Сохраняем путь к изображению (убираем начальный слеш если есть)
            const imagePath = file.url.replace(/^\//, '');

            if (currentMediaTarget.value) {
                const { type, index, field } = currentMediaTarget.value;

                if (type === 'banner_image') {
                    settings.value.banner_image = imagePath;
                } else if (type === 'statistics' && index !== null && field === 'icon') {
                    if (settings.value.statistics && settings.value.statistics[index]) {
                        settings.value.statistics[index].icon = imagePath;
                    }
                } else if (type === 'clients' && index !== null && field === 'icon') {
                    if (settings.value.clients && settings.value.clients[index]) {
                        settings.value.clients[index].icon = imagePath;
                    }
                } else if (type === 'team' && index !== null && field === 'photo') {
                    if (settings.value.team && settings.value.team[index]) {
                        settings.value.team[index].photo = imagePath;
                    }
                }
            }

            showMediaModal.value = false;
            currentMediaTarget.value = null;
        };

        const getImageUrl = (url) => {
            if (!url) return '';
            // Если URL уже полный (data:, http://, https://), возвращаем как есть
            if (url.startsWith('data:') || url.startsWith('http://') || url.startsWith('https://')) {
                return url;
            }
            // Добавляем начальный слеш для относительных путей
            return url.startsWith('/') ? url : `/${url}`;
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            
            try {
                const response = await axios.put('/api/v1/about-settings', settings.value);
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки страницы "О нас" успешно сохранены.',
                    timer: 2000,
                    showConfirmButton: false,
                });
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка сохранения настроек';
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: error.value,
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
            settings,
            showMediaModal,
            addStatistic,
            removeStatistic,
            addClient,
            removeClient,
            addTeamMember,
            removeTeamMember,
            addBenefit,
            removeBenefit,
            openMediaModal,
            handleImageSelected,
            getImageUrl,
            saveSettings,
        };
    },
};
</script>

