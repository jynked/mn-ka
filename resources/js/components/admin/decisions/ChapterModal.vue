<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
        @click.self="$emit('close')"
    >
        <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4">
                {{ isEdit ? 'Редактировать раздел' : 'Создать раздел' }}
            </h3>
            <ChapterForm
                :initial-data="initialData"
                :saving="saving"
                :error="error"
                @submit="$emit('submit', $event)"
                @cancel="$emit('close')"
            />
        </div>
    </div>
</template>

<script>
import ChapterForm from './ChapterForm.vue';

export default {
    name: 'ChapterModal',
    components: {
        ChapterForm,
    },
    props: {
        isOpen: {
            type: Boolean,
            default: false,
        },
        isEdit: {
            type: Boolean,
            default: false,
        },
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
    emits: ['close', 'submit'],
};
</script>

