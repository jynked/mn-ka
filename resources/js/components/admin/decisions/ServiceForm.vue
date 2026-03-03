<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
            <label class="text-sm font-medium mb-1 block">Название *</label>
            <input
                v-model="localForm.name"
                type="text"
                required
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Введите название услуги"
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
                @change="fetchCasesForChapter"
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
        <div v-if="localForm.chapter_id && casesForChapter.length > 0">
            <label class="text-sm font-medium mb-1 block">Случаи в этом разделе</label>
            <div class="border border-border rounded-lg p-3 max-h-48 overflow-y-auto bg-muted/5">
                <div class="space-y-1">
                    <div
                        v-for="caseItem in casesForChapter"
                        :key="caseItem.id"
                        class="text-sm text-muted-foreground flex items-center gap-2"
                    >
                        <span class="w-2 h-2 rounded-full bg-accent"></span>
                        {{ caseItem.name }}
                    </div>
                </div>
            </div>
            <p class="text-xs text-muted-foreground mt-1">
                Всего случаев: {{ casesForChapter.length }}. 
                <router-link 
                    :to="{ name: 'admin.decisions.cases', query: { chapter_id: localForm.chapter_id } }"
                    class="text-accent hover:underline"
                >
                    Управление случаями
                </router-link>
            </p>
        </div>
        <div v-else-if="localForm.chapter_id && !loadingCases" class="text-sm text-muted-foreground">
            В этом разделе пока нет случаев. 
            <router-link 
                :to="{ name: 'admin.decisions.cases.create', query: { chapter_id: localForm.chapter_id } }"
                class="text-accent hover:underline"
            >
                Создать случай
            </router-link>
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
            <label class="text-sm font-medium mb-1 block">HTML контент</label>
            <textarea
                v-model="localForm.html_content"
                rows="10"
                class="w-full px-3 py-2 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none font-mono text-sm"
                placeholder="Введите HTML контент для услуги"
            ></textarea>
            <p class="text-xs text-muted-foreground mt-1">HTML контент, который будет отображаться на странице услуги</p>
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
        <div class="flex items-center gap-2">
            <input
                v-model="localForm.is_active"
                type="checkbox"
                id="is_active"
                class="w-4 h-4 rounded border-border"
            />
            <label for="is_active" class="text-sm font-medium">
                Активна
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
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { apiGet } from '../../../utils/api';
import Media from '../../../pages/admin/Media.vue';

export default {
    name: 'ServiceForm',
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
        const casesForChapter = ref([]);
        const loadingCases = ref(false);
        const showImageMediaModal = ref(false);
        const showIconMediaModal = ref(false);
        const selectedImage = ref(null);
        const selectedIcon = ref(null);
        const localForm = ref({
            name: props.initialData.name || '',
            slug: props.initialData.slug || '',
            chapter_id: props.initialData.chapter_id || null,
            image_id: props.initialData.image_id || null,
            icon_id: props.initialData.icon_id || null,
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

        const fetchCasesForChapter = async () => {
            if (!localForm.value.chapter_id) {
                casesForChapter.value = [];
                return;
            }
            
            loadingCases.value = true;
            try {
                const response = await apiGet(`/cases?chapter_id=${localForm.value.chapter_id}`);
                if (response.ok) {
                    const data = await response.json();
                    casesForChapter.value = data.data || [];
                }
            } catch (err) {
                console.error('Error fetching cases:', err);
                casesForChapter.value = [];
            } finally {
                loadingCases.value = false;
            }
        };



        const openImageModal = () => {
            showImageMediaModal.value = true;
        };

        const openIconModal = () => {
            showIconMediaModal.value = true;
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

        const removeImage = () => {
            selectedImage.value = null;
            localForm.value.image_id = null;
        };

        const removeIcon = () => {
            selectedIcon.value = null;
            localForm.value.icon_id = null;
        };

        // Синхронизируем с изменениями props
        watch(() => props.initialData, (newData) => {
            localForm.value = {
                name: newData.name || '',
                slug: newData.slug || '',
                chapter_id: newData.chapter_id || null,
                image_id: newData.image_id || null,
                icon_id: newData.icon_id || null,
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
            // Загружаем случаи для выбранного раздела
            if (newData.chapter_id) {
                fetchCasesForChapter();
            }
        }, { deep: true });

        // Отслеживаем изменение раздела
        watch(() => localForm.value.chapter_id, () => {
            fetchCasesForChapter();
        });

        const handleSubmit = () => {
            emit('submit', {
                name: localForm.value.name,
                slug: localForm.value.slug || null,
                chapter_id: localForm.value.chapter_id || null,
                image_id: localForm.value.image_id || null,
                icon_id: localForm.value.icon_id || null,
                html_content: localForm.value.html_content || null,
                order: localForm.value.order,
                is_active: localForm.value.is_active,
            });
        };

        const handleCancel = () => {
            emit('cancel');
        };

        onMounted(() => {
            fetchChapters();
            if (props.initialData.image) {
                selectedImage.value = props.initialData.image;
            }
            if (props.initialData.icon) {
                selectedIcon.value = props.initialData.icon;
            }
            // Загружаем случаи для выбранного раздела
            if (props.initialData.chapter_id) {
                fetchCasesForChapter();
            }
        });

        return {
            chapters,
            casesForChapter,
            loadingCases,
            localForm,
            showImageMediaModal,
            showIconMediaModal,
            selectedImage,
            selectedIcon,
            openImageModal,
            openIconModal,
            handleImageSelected,
            handleIconSelected,
            removeImage,
            removeIcon,
            fetchCasesForChapter,
            handleSubmit,
            handleCancel,
        };
    },
};
</script>

