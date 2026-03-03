<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
            <label class="text-sm font-medium mb-1 block">Название *</label>
            <input
                v-model="localForm.name"
                type="text"
                required
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Введите название случая"
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
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { apiGet } from '../../../utils/api';

export default {
    name: 'CaseForm',
    props: {
        initialData: {
            type: Object,
            default: () => ({
                name: '',
                slug: '',
                chapter_id: null,
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
        const localForm = ref({
            name: props.initialData.name || '',
            slug: props.initialData.slug || '',
            chapter_id: props.initialData.chapter_id || null,
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

        // Синхронизируем с изменениями props
        watch(() => props.initialData, (newData) => {
            localForm.value = {
                name: newData.name || '',
                slug: newData.slug || '',
                chapter_id: newData.chapter_id || null,
                order: newData.order ?? 0,
                is_active: newData.is_active !== false,
            };
        }, { deep: true });

        const handleSubmit = () => {
            emit('submit', {
                name: localForm.value.name,
                slug: localForm.value.slug || null,
                chapter_id: localForm.value.chapter_id || null,
                order: localForm.value.order,
                is_active: localForm.value.is_active,
            });
        };

        const handleCancel = () => {
            emit('cancel');
        };

        onMounted(() => {
            fetchChapters();
        });

        return {
            chapters,
            localForm,
            handleSubmit,
            handleCancel,
        };
    },
};
</script>





