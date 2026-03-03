<template>
    <div class="home-page-settings space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Главная страница</h1>
            <p class="text-muted-foreground mt-1">Управление порядком блоков на главной странице</p>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-muted-foreground">Загрузка...</div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm text-red-600">{{ error }}</p>
        </div>

        <div v-else class="bg-card rounded-lg border border-border p-6">
            <div class="mb-6">
                <p class="text-sm text-muted-foreground">
                    Перетащите блоки мышкой, чтобы изменить их порядок на главной странице
                </p>
            </div>

            <div
                class="space-y-3"
                @dragover.prevent="onDragOver"
                @drop.prevent="onDrop"
            >
                <div
                    v-for="(block, index) in blocks"
                    :key="block.id"
                    :class="[
                        'flex items-center gap-4 p-4 border border-border rounded-lg bg-background transition-all cursor-move',
                        { 'dragging': draggedItem?.id === block.id, 'drag-over': dragOverIndex === index }
                    ]"
                    :draggable="true"
                    @dragstart="onDragStart($event, block, index)"
                    @dragend="onDragEnd"
                    @dragover.prevent="onItemDragOver($event, index)"
                    @dragenter.prevent="onItemDragEnter(index)"
                    @dragleave="onItemDragLeave"
                >
                    <!-- Drag Handle -->
                    <div class="flex-shrink-0 text-muted-foreground cursor-grab active:cursor-grabbing">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 5H13M7 10H13M7 15H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="4" cy="5" r="1" fill="currentColor"/>
                            <circle cx="4" cy="10" r="1" fill="currentColor"/>
                            <circle cx="4" cy="15" r="1" fill="currentColor"/>
                            <circle cx="16" cy="5" r="1" fill="currentColor"/>
                            <circle cx="16" cy="10" r="1" fill="currentColor"/>
                            <circle cx="16" cy="15" r="1" fill="currentColor"/>
                        </svg>
                    </div>

                    <!-- Block Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="font-medium text-foreground">{{ block.block_name }}</h3>
                            <span class="text-xs text-muted-foreground">({{ block.block_key }})</span>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">Компонент: {{ block.component_name }}</p>
                    </div>

                    <!-- Order Number -->
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-accent/10 text-accent font-semibold">
                        {{ block.order }}
                    </div>

                    <!-- Active Toggle -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            :checked="block.is_active"
                            @change="toggleBlock(block.id, $event.target.checked)"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm text-foreground">{{ block.is_active ? 'Активен' : 'Неактивен' }}</span>
                    </label>
                </div>
            </div>

            <div v-if="saving" class="mt-6 flex items-center justify-center py-4">
                <div class="text-muted-foreground">Сохранение...</div>
            </div>

            <div v-if="saveSuccess" class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm text-green-600">Порядок блоков успешно сохранен!</p>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'HomePageSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const saveSuccess = ref(false);
        const blocks = ref([]);
        const draggedItem = ref(null);
        const draggedIndex = ref(null);
        const dragOverIndex = ref(null);

        const fetchBlocks = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/v1/home-page-blocks');
                if (response.data && response.data.data) {
                    blocks.value = response.data.data;
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки блоков';
                console.error('Error fetching blocks:', err);
            } finally {
                loading.value = false;
            }
        };

        const onDragStart = (event, block, index) => {
            draggedItem.value = block;
            draggedIndex.value = index;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/html', event.target);
        };

        const onDragEnd = () => {
            draggedItem.value = null;
            draggedIndex.value = null;
            dragOverIndex.value = null;
        };

        const onDragOver = (event) => {
            event.dataTransfer.dropEffect = 'move';
        };

        const onItemDragOver = (event, index) => {
            if (draggedIndex.value !== null && draggedIndex.value !== index) {
                dragOverIndex.value = index;
            }
        };

        const onItemDragEnter = (index) => {
            if (draggedIndex.value !== null && draggedIndex.value !== index) {
                dragOverIndex.value = index;
            }
        };

        const onItemDragLeave = () => {
            // Не очищаем dragOverIndex здесь, чтобы сохранить визуальный индикатор
        };

        const onDrop = async (event) => {
            if (!draggedItem.value || draggedIndex.value === null) return;

            const dropIndex = dragOverIndex.value !== null ? dragOverIndex.value : draggedIndex.value;

            if (draggedIndex.value === dropIndex) {
                dragOverIndex.value = null;
                return;
            }

            // Перемещаем элемент в массиве
            const newBlocks = [...blocks.value];
            const [removed] = newBlocks.splice(draggedIndex.value, 1);
            newBlocks.splice(dropIndex, 0, removed);

            // Обновляем порядок
            newBlocks.forEach((block, index) => {
                block.order = index;
            });

            blocks.value = newBlocks;
            dragOverIndex.value = null;

            // Сохраняем новый порядок
            await saveOrder();
        };

        const saveOrder = async () => {
            saving.value = true;
            saveSuccess.value = false;
            try {
                const blocksData = blocks.value.map((block, index) => ({
                    id: block.id,
                    order: index,
                }));

                await axios.post('/api/v1/home-page-blocks/update-order', {
                    blocks: blocksData,
                });

                saveSuccess.value = true;
                setTimeout(() => {
                    saveSuccess.value = false;
                }, 3000);
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка сохранения порядка';
                console.error('Error saving order:', err);
                // Восстанавливаем исходный порядок при ошибке
                await fetchBlocks();
            } finally {
                saving.value = false;
            }
        };

        const toggleBlock = async (blockId, isActive) => {
            try {
                await axios.put(`/api/v1/home-page-blocks/${blockId}`, {
                    is_active: isActive,
                });
                // Обновляем локальное состояние
                const block = blocks.value.find(b => b.id === blockId);
                if (block) {
                    block.is_active = isActive;
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка обновления блока';
                console.error('Error toggling block:', err);
                // Восстанавливаем исходное состояние
                await fetchBlocks();
            }
        };

        onMounted(() => {
            fetchBlocks();
        });

        return {
            loading,
            saving,
            error,
            saveSuccess,
            blocks,
            draggedItem,
            draggedIndex,
            dragOverIndex,
            onDragStart,
            onDragEnd,
            onDragOver,
            onItemDragOver,
            onItemDragEnter,
            onItemDragLeave,
            onDrop,
            toggleBlock,
        };
    },
};
</script>

<style scoped>
.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.drag-over {
    border-color: #688E67;
    background-color: rgba(104, 142, 103, 0.1);
}
</style>

