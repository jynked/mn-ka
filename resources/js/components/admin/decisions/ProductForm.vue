<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
            <label class="text-sm font-medium mb-1 block">Название *</label>
            <input
                v-model="localForm.name"
                type="text"
                required
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Введите название продукта"
            />
        </div>
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
        <div>
            <label class="text-sm font-medium mb-1 block">Раздел</label>
            <select
                v-model="localForm.chapter_id"
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
        <div>
            <label class="text-sm font-medium mb-1 block">Изображение</label>
            <div class="flex items-center gap-3">
                <div v-if="selectedImage" class="flex-1">
                    <img
                        :src="selectedImage.url"
                        :alt="selectedImage.original_name"
                        class="w-20 h-20 object-cover rounded border border-border"
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
        <div>
            <label class="text-sm font-medium mb-1 block">Короткое описание</label>
            <textarea
                v-model="localForm.short_description"
                rows="3"
                maxlength="500"
                class="w-full px-3 py-2 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none text-sm"
                placeholder="Короткое описание для карточек и первого экрана (140–220 символов)"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">Отображается на главной странице</p>
        </div>
        <div>
            <label class="text-sm font-medium mb-1 block">Изображение для карточки (превью)</label>
            <div class="flex items-center gap-3">
                <div v-if="selectedCardPreviewImage" class="flex-1">
                    <img
                        :src="selectedCardPreviewImage.url"
                        :alt="selectedCardPreviewImage.original_name"
                        class="w-20 h-20 object-cover rounded border border-border"
                    />
                </div>
                <button
                    type="button"
                    @click="openCardPreviewImageModal"
                    class="px-4 py-2 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors text-sm"
                >
                    {{ selectedCardPreviewImage ? 'Изменить' : 'Выбрать изображение' }}
                </button>
                <button
                    v-if="selectedCardPreviewImage"
                    type="button"
                    @click="removeCardPreviewImage"
                    class="px-4 py-2 border border-destructive text-destructive hover:bg-destructive/10 rounded-lg transition-colors text-sm"
                >
                    Удалить
                </button>
            </div>
            <p class="text-xs text-muted-foreground mt-1">Используется только в карточках листинга. Не влияет на основное изображение.</p>
        </div>
        <div>
            <label class="text-sm font-medium mb-1 block">Заголовок страницы (H1)</label>
            <input
                v-model="localForm.page_title"
                type="text"
                maxlength="255"
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Заголовок страницы продукта"
            />
            <p class="text-xs text-muted-foreground mt-1">Отображается на главной странице</p>
        </div>
        <div>
            <label class="text-sm font-medium mb-1 block">Подзаголовок/лид страницы</label>
            <input
                v-model="localForm.page_subtitle"
                type="text"
                maxlength="500"
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Подзаголовок страницы продукта"
            />
            <p class="text-xs text-muted-foreground mt-1">Отображается на главной странице</p>
        </div>
        <div>
            <label class="text-sm font-medium mb-1 block">Текст кнопки CTA</label>
            <input
                v-model="localForm.cta_text"
                type="text"
                maxlength="255"
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Текст кнопки призыва к действию"
            />
            <p class="text-xs text-muted-foreground mt-1">Отображается на главной странице</p>
        </div>
        <div>
            <label class="text-sm font-medium mb-1 block">Ссылка кнопки CTA</label>
            <input
                v-model="localForm.cta_link"
                type="text"
                maxlength="500"
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="URL ссылки кнопки CTA"
            />
            <p class="text-xs text-muted-foreground mt-1">Отображается на главной странице</p>
        </div>
        <div>
            <label class="text-sm font-medium mb-1 block">HTML контент</label>
            <textarea
                v-model="localForm.html_content"
                rows="10"
                class="w-full px-3 py-2 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none font-mono text-sm"
                placeholder="Введите HTML контент для продукта"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">HTML контент, который будет отображаться на странице продукта</p>
        </div>
        <div>
            <label class="text-sm font-medium mb-1 block">Порядок</label>
            <input
                v-model.number="localForm.order"
                type="number"
                min="0"
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
            />
        </div>
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
        <div v-if="error" class="p-3 bg-destructive/10 border border-destructive/20 rounded text-sm text-destructive">
            {{ error }}
        </div>
        <div class="flex gap-2 pt-4">
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

    <!-- Media Modal for Card Preview Image -->
    <div v-if="showCardPreviewImageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col m-4">
            <div class="flex items-center justify-between p-4 border-b border-border">
                <h3 class="text-lg font-semibold">Выбрать изображение для карточки</h3>
                <button
                    @click="showCardPreviewImageModal = false"
                    class="text-muted-foreground hover:text-foreground w-8 h-8 flex items-center justify-center rounded hover:bg-muted/10"
                >
                    ✕
                </button>
            </div>
            <div class="flex-1 overflow-auto h-full">
                <Media
                    :selection-mode="true"
                    :count-file="1"
                    @file-selected="handleCardPreviewImageSelected"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { apiGet } from '../../../utils/api';
import Media from '../../../pages/admin/Media.vue';

export default {
    name: 'ProductForm',
    components: {
        Media,
    },
    props: {
        initialData: {
            type: Object,
            default: () => ({
                name: '',
                slug: '',
                chapter_id: null,
                image_id: null,
                icon_id: null,
                html_content: '',
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
        const loadingServices = ref(false);
        const selectedServices = ref([]);
        const showImageMediaModal = ref(false);
        const showIconMediaModal = ref(false);
        const showCardPreviewImageModal = ref(false);
        const selectedImage = ref(null);
        const selectedIcon = ref(null);
        const selectedCardPreviewImage = ref(null);
        const localForm = ref({
            name: props.initialData.name || '',
            slug: props.initialData.slug || '',
            chapter_id: props.initialData.chapter_id || null,
            image_id: props.initialData.image_id || null,
            icon_id: props.initialData.icon_id || null,
            card_preview_image_id: props.initialData.card_preview_image_id || null,
            short_description: props.initialData.short_description || '',
            page_title: props.initialData.page_title || '',
            page_subtitle: props.initialData.page_subtitle || '',
            cta_text: props.initialData.cta_text || '',
            cta_link: props.initialData.cta_link || '',
            html_content: props.initialData.html_content || '',
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


        const openImageModal = () => {
            showImageMediaModal.value = true;
        };

        const openIconModal = () => {
            showIconMediaModal.value = true;
        };

        const openCardPreviewImageModal = () => {
            showCardPreviewImageModal.value = true;
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

        const handleCardPreviewImageSelected = (file) => {
            selectedCardPreviewImage.value = file;
            localForm.value.card_preview_image_id = file.id;
            showCardPreviewImageModal.value = false;
        };

        const removeImage = () => {
            selectedImage.value = null;
            localForm.value.image_id = null;
        };

        const removeIcon = () => {
            selectedIcon.value = null;
            localForm.value.icon_id = null;
        };

        const removeCardPreviewImage = () => {
            selectedCardPreviewImage.value = null;
            localForm.value.card_preview_image_id = null;
        };

        // Синхронизируем с изменениями props
        watch(() => props.initialData, (newData) => {
            localForm.value = {
                name: newData.name || '',
                slug: newData.slug || '',
                chapter_id: newData.chapter_id || null,
                image_id: newData.image_id || null,
                icon_id: newData.icon_id || null,
                card_preview_image_id: newData.card_preview_image_id || null,
                short_description: newData.short_description || '',
                page_title: newData.page_title || '',
                page_subtitle: newData.page_subtitle || '',
                cta_text: newData.cta_text || '',
                cta_link: newData.cta_link || '',
                html_content: newData.html_content || '',
                order: newData.order ?? 0,
                is_active: newData.is_active !== false,
            };
            if (newData.image) {
                selectedImage.value = newData.image;
            }
            if (newData.icon) {
                selectedIcon.value = newData.icon;
            }
            if (newData.card_preview_image) {
                selectedCardPreviewImage.value = newData.card_preview_image;
            }
            if (newData.services && Array.isArray(newData.services)) {
                selectedServices.value = newData.services.map(s => s.id || s);
            }
        }, { deep: true });

        const handleSubmit = () => {
            emit('submit', {
                name: localForm.value.name,
                slug: localForm.value.slug || null,
                chapter_id: localForm.value.chapter_id || null,
                image_id: localForm.value.image_id || null,
                icon_id: localForm.value.icon_id || null,
                card_preview_image_id: localForm.value.card_preview_image_id || null,
                short_description: localForm.value.short_description || null,
                page_title: localForm.value.page_title || null,
                page_subtitle: localForm.value.page_subtitle || null,
                cta_text: localForm.value.cta_text || null,
                cta_link: localForm.value.cta_link || null,
                html_content: localForm.value.html_content || null,
                order: localForm.value.order,
                is_active: localForm.value.is_active,
                services: selectedServices.value,
            });
        };

        const handleCancel = () => {
            emit('cancel');
        };

        onMounted(() => {
            fetchChapters();
            fetchServices();
            if (props.initialData.image) {
                selectedImage.value = props.initialData.image;
            }
            if (props.initialData.icon) {
                selectedIcon.value = props.initialData.icon;
            }
            if (props.initialData.card_preview_image) {
                selectedCardPreviewImage.value = props.initialData.card_preview_image;
            }
            if (props.initialData.services && Array.isArray(props.initialData.services)) {
                selectedServices.value = props.initialData.services.map(s => s.id || s);
            }
        });

        return {
            chapters,
            services,
            loadingServices,
            selectedServices,
            localForm,
            showImageMediaModal,
            showIconMediaModal,
            showCardPreviewImageModal,
            selectedImage,
            selectedIcon,
            selectedCardPreviewImage,
            openImageModal,
            openIconModal,
            openCardPreviewImageModal,
            handleImageSelected,
            handleIconSelected,
            handleCardPreviewImageSelected,
            removeImage,
            removeIcon,
            removeCardPreviewImage,
            handleSubmit,
            handleCancel,
        };
    },
};
</script>

