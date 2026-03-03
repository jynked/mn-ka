<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
            <label class="text-sm font-medium mb-1 block">Название *</label>
            <input
                v-model="localForm.name"
                type="text"
                required
                class="w-full h-10 px-3 border border-border rounded bg-background focus:outline-none focus:ring-2 focus:ring-accent"
                placeholder="Введите название раздела"
            />
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
import { ref, watch } from 'vue';

export default {
    name: 'ChapterForm',
    props: {
        initialData: {
            type: Object,
            default: () => ({
                name: '',
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
        const localForm = ref({
            name: props.initialData.name || '',
            order: props.initialData.order ?? 0,
            is_active: props.initialData.is_active !== false,
        });

        // Синхронизируем с изменениями props
        watch(() => props.initialData, (newData) => {
            localForm.value = {
                name: newData.name || '',
                order: newData.order ?? 0,
                is_active: newData.is_active !== false,
            };
        }, { deep: true });

        const handleSubmit = () => {
            emit('submit', {
                name: localForm.value.name,
                order: localForm.value.order,
                is_active: localForm.value.is_active,
            });
        };

        const handleCancel = () => {
            emit('cancel');
        };

        return {
            localForm,
            handleSubmit,
            handleCancel,
        };
    },
};
</script>

