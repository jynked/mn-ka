<template>
    <div class="home-banner-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Баннер на главной</h1>
                <p class="text-muted-foreground mt-1">Настройка главного баннера сайта</p>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка баннера...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Banner Form -->
        <div v-if="!loading" class="bg-card rounded-lg border border-border overflow-hidden">
            <form @submit.prevent="saveBanner" class="p-6 space-y-6">
                <!-- Title -->
                <div>
                    <label class="text-sm font-medium mb-2 block">Название баннера</label>
                    <input
                        v-model="form.title"
                        type="text"
                        required
                        class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                        placeholder="Баннер на главной"
                    />
                </div>

                <!-- Background Image -->
                <div>
                    <label class="text-sm font-medium mb-2 block">Фоновое изображение</label>
                    <div class="space-y-3">
                        <div v-if="form.background_image" class="relative">
                            <img
                                :src="form.background_image.startsWith('data:') || form.background_image.startsWith('/') ? form.background_image : `/${form.background_image}`"
                                alt="Preview"
                                class="w-full h-48 object-cover rounded border border-border"
                            />
                            <div class="mt-2 flex gap-2">
                                <button
                                    type="button"
                                    @click="selectImage"
                                    class="px-4 py-2 bg-accent/10 text-accent border border-accent/40 rounded hover:bg-accent/20"
                                >
                                    Изменить изображение
                                </button>
                                <button
                                    type="button"
                                    @click="form.background_image = null"
                                    class="px-4 py-2 bg-destructive/10 text-destructive border border-destructive/40 rounded hover:bg-destructive/20"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                        <div v-else>
                            <button
                                type="button"
                                @click="selectImage"
                                class="w-full h-32 border-2 border-dashed border-border rounded flex items-center justify-center hover:bg-muted/10 transition-colors"
                            >
                                <span class="text-muted-foreground">Выберите изображение</span>
                            </button>
                        </div>
                    </div>
                    <input
                        ref="imageInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="handleImageSelect"
                    />
                    <p class="text-xs text-muted-foreground mt-1">Рекомендуемый размер: 1920x700px</p>
                </div>

                <!-- Heading 1 -->
                <div>
                    <label class="text-sm font-medium mb-2 block">Заголовок (первая строка)</label>
                    <input
                        v-model="form.heading_1"
                        type="text"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                        placeholder="Подберём и оформим"
                    />
                </div>

                <!-- Heading 2 -->
                <div>
                    <label class="text-sm font-medium mb-2 block">Заголовок (вторая строка)</label>
                    <input
                        v-model="form.heading_2"
                        type="text"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                        placeholder="участок под ваш проект"
                    />
                </div>

                <!-- Description -->
                <div>
                    <label class="text-sm font-medium mb-2 block">Описание</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-border rounded bg-background text-foreground"
                        placeholder="— от ИЖС до складов&#10;и ритейла"
                    ></textarea>
                    <p class="text-xs text-muted-foreground mt-1">Используйте перенос строки для многострочного текста</p>
                </div>

                <!-- Button Settings -->
                <div class="border-t border-border pt-6">
                    <h3 class="text-lg font-semibold mb-4">Настройки кнопки</h3>
                    
                    <!-- Button Text -->
                    <div class="mb-4">
                        <label class="text-sm font-medium mb-2 block">Текст кнопки</label>
                        <input
                            v-model="form.button_text"
                            type="text"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                            placeholder="Подробнее"
                        />
                    </div>

                    <!-- Button Type -->
                    <div class="mb-4">
                        <label class="text-sm font-medium mb-2 block">Тип кнопки</label>
                        <select
                            v-model="form.button_type"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                        >
                            <option value="url">URL (ссылка)</option>
                            <option value="method">Метод (popup)</option>
                        </select>
                    </div>

                    <!-- Button Value (URL or Method) -->
                    <div v-if="form.button_type === 'url'">
                        <label class="text-sm font-medium mb-2 block">URL</label>
                        <input
                            v-model="form.button_value"
                            type="text"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                            placeholder="/page или https://example.com"
                        />
                    </div>

                    <div v-if="form.button_type === 'method'">
                        <label class="text-sm font-medium mb-2 block">ID метода</label>
                        <input
                            v-model="form.button_value"
                            type="text"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                            placeholder="Методы будут добавлены позже"
                            disabled
                        />
                        <p class="text-xs text-muted-foreground mt-1">Выбор методов будет доступен после создания функционала методов</p>
                    </div>
                </div>

                <!-- Height Settings -->
                <div class="border-t border-border pt-6">
                    <h3 class="text-lg font-semibold mb-4">Настройки высоты</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium mb-2 block">
                                Высота для десктопа (px)
                            </label>
                            <input
                                v-model.number="form.height_desktop"
                                type="number"
                                min="100"
                                max="2000"
                                step="10"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                                placeholder="380"
                            />
                            <p class="text-xs text-muted-foreground mt-1">
                                Высота баннера на больших экранах (от 1024px)
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium mb-2 block">
                                Высота для мобильных (px)
                            </label>
                            <input
                                v-model.number="form.height_mobile"
                                type="number"
                                min="100"
                                max="2000"
                                step="10"
                                class="w-full h-10 px-3 border border-border rounded bg-background text-foreground"
                                placeholder="300"
                            />
                            <p class="text-xs text-muted-foreground mt-1">
                                Высота баннера на мобильных устройствах (до 640px)
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-4 bg-muted/50 rounded-lg">
                        <p class="text-sm text-muted-foreground">
                            <strong>Совет:</strong> Высота будет плавно изменяться между мобильной и десктопной версией 
                            в зависимости от ширины экрана. Например, если установить 380px для десктопа и 300px для мобильных, 
                            то на экране шириной 320px высота будет 300px, а на экране 1200px+ - 380px.
                        </p>
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-2">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        id="is_active"
                        class="w-4 h-4"
                    />
                    <label for="is_active" class="text-sm font-medium">Активен</label>
                </div>

                <!-- Save Button -->
                <div class="flex gap-3 pt-4 border-t border-border">
                    <button
                        type="submit"
                        :disabled="saving"
                        class="flex-1 h-10 px-4 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ saving ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                </div>
            </form>
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
import { apiGet, apiPost, apiPut } from '../../../utils/api';
import Swal from 'sweetalert2';
import Media from '../Media.vue';

export default {
    name: 'HomeBanner',
    components: {
        Media,
    },
    setup() {
        const loading = ref(false);
        const saving = ref(false);
        const error = ref(null);
        const showMediaModal = ref(false);
        const imageInput = ref(null);
        
        const form = ref({
            id: null,
            title: 'Баннер на главной',
            slug: 'home-banner',
            background_image: null,
            heading_1: '',
            heading_2: '',
            description: '',
            button_text: '',
            button_type: 'url',
            button_value: '',
            height_desktop: 380,
            height_mobile: 300,
            is_active: true,
        });

        const fetchBanner = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                // Сначала пробуем загрузить существующий баннер
                const response = await apiGet('/banners');
                if (response.ok) {
                    const data = await response.json();
                    const homeBanner = data.data?.find(b => b.slug === 'home-banner');
                    
                    if (homeBanner) {
                        form.value = {
                            id: homeBanner.id,
                            title: homeBanner.title,
                            slug: homeBanner.slug,
                            background_image: homeBanner.background_image || null,
                            heading_1: homeBanner.heading_1 || '',
                            heading_2: homeBanner.heading_2 || '',
                            description: homeBanner.description || '',
                            button_text: homeBanner.button_text || '',
                            button_type: homeBanner.button_type || 'url',
                            button_value: homeBanner.button_value || '',
                            height_desktop: homeBanner.height_desktop || 380,
                            height_mobile: homeBanner.height_mobile || 300,
                            is_active: homeBanner.is_active !== undefined ? homeBanner.is_active : true,
                        };
                    }
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки баннера';
            } finally {
                loading.value = false;
            }
        };

        const selectImage = () => {
            showMediaModal.value = true;
        };

        const uploadNewImage = () => {
            showMediaModal.value = false;
            if (imageInput.value) {
                imageInput.value.click();
            }
        };

        const handleImageSelect = async (e) => {
            const file = e.target.files?.[0];
            if (!file) return;

            // Пока просто показываем превью, загрузку реализуем позже
            const reader = new FileReader();
            reader.onload = (event) => {
                form.value.background_image = event.target.result;
            };
            reader.readAsDataURL(file);
        };

        const handleImageSelected = (file) => {
            console.log('handleImageSelected called with file:', file);
            if (file && file.type === 'photo') {
                // Сохраняем путь к изображению (убираем начальный слеш если есть)
                const imagePath = file.url.replace(/^\//, '');
                console.log('Setting background_image to:', imagePath);
                form.value.background_image = imagePath;
                // Закрываем модальное окно
                showMediaModal.value = false;
            } else {
                console.warn('File is not a photo:', file);
            }
        };

        const saveBanner = async () => {
            saving.value = true;
            error.value = null;
            
            try {
                const url = form.value.id ? `/banners/${form.value.id}` : '/banners';
                const method = form.value.id ? apiPut : apiPost;
                
                const response = await method(url, {
                    title: form.value.title,
                    slug: form.value.slug,
                    background_image: form.value.background_image,
                    heading_1: form.value.heading_1,
                    heading_2: form.value.heading_2,
                    description: form.value.description,
                    button_text: form.value.button_text,
                    button_type: form.value.button_type,
                    button_value: form.value.button_value,
                    height_desktop: form.value.height_desktop,
                    height_mobile: form.value.height_mobile,
                    is_active: form.value.is_active,
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Ошибка сохранения');
                }

                const data = await response.json();
                form.value.id = data.data.id;
                
                await Swal.fire('Успешно!', 'Баннер успешно сохранен.', 'success');
                await fetchBanner();
            } catch (err) {
                error.value = err.message || 'Ошибка сохранения баннера';
                await Swal.fire('Ошибка!', err.message || 'Не удалось сохранить баннер.', 'error');
            } finally {
                saving.value = false;
            }
        };

        onMounted(() => {
            fetchBanner();
        });

        return {
            loading,
            saving,
            error,
            form,
            showMediaModal,
            imageInput,
            selectImage,
            uploadNewImage,
            handleImageSelect,
            handleImageSelected,
            saveBanner,
        };
    },
};
</script>

