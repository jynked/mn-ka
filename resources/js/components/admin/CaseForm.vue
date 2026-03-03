<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Название -->
        <div>
            <label class="text-sm font-medium mb-1 block">Название *</label>
            <input
                v-model="localForm.name"
                type="text"
                required
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Введите название кейса"
            />
        </div>

        <!-- Slug -->
        <div>
            <label class="text-sm font-medium mb-1 block">Slug</label>
            <input
                v-model="localForm.slug"
                type="text"
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Автоматически генерируется из названия"
            />
            <p class="text-xs text-muted-foreground mt-1">Если не указан, будет создан автоматически</p>
        </div>

        <!-- Раздел -->
        <div>
            <label class="text-sm font-medium mb-1 block">Раздел *</label>
            <select
                v-model="localForm.chapter_id"
                required
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option :value="null">Не выбран</option>
                <option
                    v-for="chapter in chapters"
                    :key="chapter.id"
                    :value="chapter.id"
                >
                    {{ chapter.name }}
                </option>
            </select>
        </div>

        <!-- Описание -->
        <div>
            <label class="text-sm font-medium mb-1 block">Описание *</label>
            <textarea
                v-model="localForm.description"
                rows="5"
                required
                class="w-full px-3 py-2 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none"
                placeholder="Введите описание кейса"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">Краткое описание для карточки кейса</p>
        </div>

        <!-- HTML контент -->
        <div>
            <label class="text-sm font-medium mb-1 block">HTML контент</label>
            <textarea
                v-model="localForm.html"
                rows="10"
                class="w-full px-3 py-2 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none font-mono text-sm"
                placeholder="Введите HTML контент для страницы кейса"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">Полный HTML контент, который будет отображаться на странице кейса</p>
        </div>

        <!-- Основное изображение -->
        <div>
            <label class="text-sm font-medium mb-1 block">Основное изображение</label>
            <div class="flex items-center gap-3">
                <div v-if="selectedImage" class="flex-1">
                    <img
                        :src="selectedImage.url"
                        :alt="selectedImage.original_name"
                        class="w-32 h-32 object-cover rounded border border-border"
                    />
                </div>
                <button
                    type="button"
                    @click="openImageModal"
                    class="px-4 py-2 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors text-sm"
                >
                    {{ selectedImage ? 'Изменить' : 'Выбрать изображение' }}
                </button>
                <button
                    v-if="selectedImage"
                    type="button"
                    @click="removeImage"
                    class="px-4 py-2 border border-destructive text-destructive hover:bg-destructive/10 rounded-lg transition-colors text-sm"
                >
                    Удалить
                </button>
            </div>
        </div>

        <!-- Иконка -->
        <div>
            <label class="text-sm font-medium mb-1 block">Иконка</label>
            <div class="flex items-center gap-3">
                <div v-if="selectedIcon" class="flex-1">
                    <img
                        :src="selectedIcon.url"
                        :alt="selectedIcon.original_name"
                        class="w-16 h-16 object-cover rounded border border-border"
                    />
                </div>
                <button
                    type="button"
                    @click="openIconModal"
                    class="px-4 py-2 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors text-sm"
                >
                    {{ selectedIcon ? 'Изменить' : 'Выбрать иконку' }}
                </button>
                <button
                    v-if="selectedIcon"
                    type="button"
                    @click="removeIcon"
                    class="px-4 py-2 border border-destructive text-destructive hover:bg-destructive/10 rounded-lg transition-colors text-sm"
                >
                    Удалить
                </button>
            </div>
        </div>

        <!-- Галерея изображений -->
        <div>
            <label class="text-sm font-medium mb-1 block">Галерея изображений</label>
            <div v-if="galleryImages.length > 0" class="grid grid-cols-4 gap-3 mb-3">
                <div
                    v-for="(img, index) in galleryImages"
                    :key="img.id || index"
                    class="relative group"
                >
                    <img
                        :src="img.url"
                        :alt="img.original_name"
                        class="w-full h-24 object-cover rounded border border-border"
                    />
                    <button
                        type="button"
                        @click="removeGalleryImage(index)"
                        class="absolute top-1 right-1 w-6 h-6 bg-destructive text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-xs"
                    >
                        ×
                    </button>
                </div>
            </div>
            <button
                type="button"
                @click="openGalleryModal"
                class="px-4 py-2 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors text-sm"
            >
                {{ galleryImages.length > 0 ? 'Добавить еще' : 'Добавить изображения в галерею' }}
            </button>
        </div>

        <!-- Услуги -->
        <div>
            <label class="text-sm font-medium mb-1 block">Услуги</label>
            <div class="border border-border rounded-lg p-4 max-h-64 overflow-y-auto">
                <div v-if="loadingServices" class="text-sm text-muted-foreground">
                    Загрузка услуг...
                </div>
                <div v-else-if="services.length === 0" class="text-sm text-muted-foreground">
                    Услуги не найдены
                </div>
                <div v-else class="space-y-2">
                    <label
                        v-for="service in services"
                        :key="service.id"
                        class="flex items-center gap-2 cursor-pointer hover:bg-muted/10 p-2 rounded"
                    >
                        <input
                            type="checkbox"
                            :value="service.id"
                            v-model="selectedServices"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm">{{ service.name }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Продукты -->
        <div>
            <label class="text-sm font-medium mb-1 block">Продукты</label>
            <div class="border border-border rounded-lg p-4 max-h-64 overflow-y-auto">
                <div v-if="loadingProducts" class="text-sm text-muted-foreground">
                    Загрузка продуктов...
                </div>
                <div v-else-if="products.length === 0" class="text-sm text-muted-foreground">
                    Продукты не найдены
                </div>
                <div v-else class="space-y-2">
                    <label
                        v-for="product in products"
                        :key="product.id"
                        class="flex items-center gap-2 cursor-pointer hover:bg-muted/10 p-2 rounded"
                    >
                        <input
                            type="checkbox"
                            :value="product.id"
                            v-model="selectedProducts"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm">{{ product.name }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Порядок -->
        <div>
            <label class="text-sm font-medium mb-1 block">Порядок</label>
            <input
                v-model.number="localForm.order"
                type="number"
                min="0"
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
            />
        </div>

        <!-- Активен -->
        <div class="flex items-center gap-2">
            <input
                v-model="localForm.is_active"
                type="checkbox"
                id="is_active"
                class="w-4 h-4 rounded border-border"
            />
            <label for="is_active" class="text-sm font-medium">
                Активен
            </label>
        </div>

        <!-- Ошибка -->
        <div v-if="error" class="p-3 bg-destructive/10 border border-destructive/20 rounded text-sm text-destructive">
            {{ error }}
        </div>

        <!-- Кнопки -->
        <div class="flex gap-2 pt-4 border-t border-border">
            <button
                type="button"
                @click="handleCancel"
                class="flex-1 h-10 px-4 border border-border bg-background/50 hover:bg-accent/10 rounded-lg transition-colors"
            >
                Отмена
            </button>
            <button
                type="submit"
                :disabled="saving"
                class="flex-1 h-10 px-4 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-lg transition-colors disabled:opacity-50"
            >
                {{ saving ? 'Сохранение...' : 'Сохранить' }}
            </button>
        </div>
    </form>

    <!-- Media Modal for Image -->
    <div v-if="showImageMediaModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col m-4">
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold">Выбрать изображение</h3>
                <button
                    @click="showImageMediaModal = false"
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

    <!-- Media Modal for Icon -->
    <div v-if="showIconMediaModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col m-4">
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold">Выбрать иконку</h3>
                <button
                    @click="showIconMediaModal = false"
                    class="text-muted-foreground hover:text-foreground w-8 h-8 flex items-center justify-center rounded hover:bg-muted/10"
                >
                    ✕
                </button>
            </div>
            <div class="flex-1 overflow-auto h-full">
                <Media
                    :selection-mode="true"
                    :count-file="1"
                    @file-selected="handleIconSelected"
                />
            </div>
        </div>
    </div>

    <!-- Media Modal for Gallery -->
    <div v-if="showGalleryMediaModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col m-4">
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold">Выбрать изображения для галереи</h3>
                <button
                    @click="showGalleryMediaModal = false"
                    class="text-muted-foreground hover:text-foreground w-8 h-8 flex items-center justify-center rounded hover:bg-muted/10"
                >
                    ✕
                </button>
            </div>
            <div class="flex-1 overflow-auto h-full">
                <Media
                    :selection-mode="true"
                    :count-file="999"
                    @file-selected="handleGallerySelected"
                />
            </div>
            <div class="p-4 border-t border-border flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Выбрано изображений: {{ galleryImages.length }}
                </div>
                <button
                    @click="showGalleryMediaModal = false"
                    class="px-4 py-2 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-lg transition-colors text-sm"
                >
                    Готово
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { apiGet } from '../../utils/api';
import Media from '../../pages/admin/Media.vue';

export default {
    name: 'CaseForm',
    components: {
        Media,
    },
    props: {
        initialData: {
            type: Object,
            default: () => ({
                name: '',
                slug: '',
                description: '',
                html: '',
                chapter_id: null,
                image_id: null,
                icon_id: null,
                order: 0,
                is_active: true,
            }),
        },
        saving: {
            type: Boolean,
            default: false,
        },
        error: {
            type: String,
            default: null,
        },
    },
    emits: ['submit', 'cancel'],
    setup(props, { emit }) {
        const chapters = ref([]);
        const services = ref([]);
        const products = ref([]);
        const loadingServices = ref(false);
        const loadingProducts = ref(false);
        const selectedServices = ref([]);
        const selectedProducts = ref([]);
        const showImageMediaModal = ref(false);
        const showIconMediaModal = ref(false);
        const showGalleryMediaModal = ref(false);
        const selectedImage = ref(null);
        const selectedIcon = ref(null);
        const galleryImages = ref([]);
        
        // Обработка description и html - они могут быть массивами или строками
        const normalizeText = (value) => {
            if (!value) return '';
            if (typeof value === 'string') return value;
            if (typeof value === 'object') {
                if (Array.isArray(value)) {
                    return value.join('\n');
                }
                return value.ru || value.en || value.short || value.full || JSON.stringify(value);
            }
            return String(value);
        };

        const localForm = ref({
            name: props.initialData.name || '',
            slug: props.initialData.slug || '',
            description: normalizeText(props.initialData.description),
            html: normalizeText(props.initialData.html),
            chapter_id: props.initialData.chapter_id || null,
            image_id: props.initialData.image_id || null,
            icon_id: props.initialData.icon_id || null,
            order: props.initialData.order ?? 0,
            is_active: props.initialData.is_active !== false,
        });

        const fetchChapters = async () => {
            try {
                const response = await apiGet('/chapters');
                if (response.ok) {
                    const data = await response.json();
                    chapters.value = data.data || [];
                }
            } catch (err) {
                console.error('Error fetching chapters:', err);
            }
        };

        const fetchServices = async () => {
            loadingServices.value = true;
            try {
                const response = await apiGet('/services?active=1');
                if (response.ok) {
                    const data = await response.json();
                    services.value = data.data || [];
                }
            } catch (err) {
                console.error('Error fetching services:', err);
            } finally {
                loadingServices.value = false;
            }
        };

        const fetchProducts = async () => {
            loadingProducts.value = true;
            try {
                const response = await apiGet('/products?active=1');
                if (response.ok) {
                    const data = await response.json();
                    products.value = data.data || [];
                }
            } catch (err) {
                console.error('Error fetching products:', err);
            } finally {
                loadingProducts.value = false;
            }
        };

        const openImageModal = () => {
            showImageMediaModal.value = true;
        };

        const openIconModal = () => {
            showIconMediaModal.value = true;
        };

        const openGalleryModal = () => {
            showGalleryMediaModal.value = true;
        };

        const handleImageSelected = (file) => {
            selectedImage.value = file;
            localForm.value.image_id = file.id;
            showImageMediaModal.value = false;
        };

        const handleIconSelected = (file) => {
            selectedIcon.value = file;
            localForm.value.icon_id = file.id;
            showIconMediaModal.value = false;
        };

        const handleGallerySelected = (file) => {
            // Проверяем, что файл еще не добавлен
            if (!galleryImages.value.find(img => img.id === file.id)) {
                galleryImages.value.push(file);
            }
            // Не закрываем модалку автоматически, чтобы можно было выбрать несколько файлов
            // Пользователь может закрыть модалку вручную
        };

        const removeImage = () => {
            selectedImage.value = null;
            localForm.value.image_id = null;
        };

        const removeIcon = () => {
            selectedIcon.value = null;
            localForm.value.icon_id = null;
        };

        const removeGalleryImage = (index) => {
            galleryImages.value.splice(index, 1);
        };

        // Синхронизируем с изменениями props
        watch(() => props.initialData, (newData) => {
            localForm.value = {
                name: newData.name || '',
                slug: newData.slug || '',
                description: normalizeText(newData.description),
                html: normalizeText(newData.html),
                chapter_id: newData.chapter_id || null,
                image_id: newData.image_id || null,
                icon_id: newData.icon_id || null,
                order: newData.order ?? 0,
                is_active: newData.is_active !== false,
            };
            if (newData.image) {
                selectedImage.value = newData.image;
            }
            if (newData.icon) {
                selectedIcon.value = newData.icon;
            }
            if (newData.images && Array.isArray(newData.images)) {
                galleryImages.value = newData.images;
            }
            if (newData.services && Array.isArray(newData.services)) {
                selectedServices.value = newData.services.map(s => s.id || s);
            }
            if (newData.products && Array.isArray(newData.products)) {
                selectedProducts.value = newData.products.map(p => p.id || p);
            }
        }, { deep: true });

        const handleSubmit = () => {
            // Преобразуем description и html в массивы для отправки (как требует API)
            const formData = {
                name: localForm.value.name,
                slug: localForm.value.slug || null,
                description: localForm.value.description ? { 'ru': localForm.value.description } : { 'ru': '' },
                html: localForm.value.html ? { 'ru': localForm.value.html } : null,
                chapter_id: localForm.value.chapter_id || null,
                image_id: localForm.value.image_id || null,
                icon_id: localForm.value.icon_id || null,
                order: localForm.value.order,
                is_active: localForm.value.is_active,
                services: selectedServices.value,
                products: selectedProducts.value,
                images: galleryImages.value.map(img => ({ id: img.id })),
            };
            emit('submit', formData);
        };

        const handleCancel = () => {
            emit('cancel');
        };

        onMounted(() => {
            fetchChapters();
            fetchServices();
            fetchProducts();
            if (props.initialData.image) {
                selectedImage.value = props.initialData.image;
            }
            if (props.initialData.icon) {
                selectedIcon.value = props.initialData.icon;
            }
            if (props.initialData.images && Array.isArray(props.initialData.images)) {
                galleryImages.value = props.initialData.images;
            }
            if (props.initialData.services && Array.isArray(props.initialData.services)) {
                selectedServices.value = props.initialData.services.map(s => s.id || s);
            }
            if (props.initialData.products && Array.isArray(props.initialData.products)) {
                selectedProducts.value = props.initialData.products.map(p => p.id || p);
            }
        });

        return {
            chapters,
            services,
            products,
            loadingServices,
            loadingProducts,
            selectedServices,
            selectedProducts,
            localForm,
            showImageMediaModal,
            showIconMediaModal,
            showGalleryMediaModal,
            selectedImage,
            selectedIcon,
            galleryImages,
            openImageModal,
            openIconModal,
            openGalleryModal,
            handleImageSelected,
            handleIconSelected,
            handleGallerySelected,
            removeImage,
            removeIcon,
            removeGalleryImage,
            handleSubmit,
            handleCancel,
        };
    },
};
</script>

