<template>
    <div 
        class="menu-tree"
        @dragover.prevent="onDragOver"
        @drop.prevent="onDrop"
    >
        <div 
            v-for="(item, index) in items" 
            :key="item.id" 
            class="menu-item"
            :class="{ 'dragging': draggedItem?.id === item.id, 'drag-over': dragOverIndex === index }"
            :draggable="true"
            @dragstart="onDragStart($event, item, index)"
            @dragend="onDragEnd"
            @dragover.prevent="onItemDragOver($event, index)"
            @dragenter.prevent="onItemDragEnter(index)"
            @dragleave="onItemDragLeave"
        >
            <div class="flex items-center justify-between p-4 border-b border-border hover:bg-muted/5 cursor-move">
                <div class="flex items-center gap-3 flex-1">
                    <svg 
                        class="w-5 h-5 text-muted-foreground" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                    </svg>
                    <div class="flex items-center gap-2 flex-1">
                        <span class="text-sm font-medium text-foreground">{{ item.title }}</span>
                        <span class="text-xs px-2 py-1 rounded bg-muted text-muted-foreground">
                            {{ item.type }}
                        </span>
                        <span v-if="item.url || item.slug" class="text-xs text-muted-foreground">
                            {{ item.url || (item.slug ? '/' + item.slug : '') }}
                        </span>
                        <span v-if="!item.is_active" class="text-xs px-2 py-1 rounded bg-red-500/10 text-red-500">
                            Неактивен
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click.stop="$emit('edit', item)"
                        class="px-3 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors"
                    >
                        Редактировать
                    </button>
                    <button
                        @click.stop="$emit('delete', item)"
                        class="px-3 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded transition-colors"
                    >
                        Удалить
                    </button>
                </div>
            </div>
            <div v-if="item.children && item.children.length > 0" class="ml-6 border-l border-border">
                <MenuTree
                    :items="item.children"
                    @edit="$emit('edit', $event)"
                    @delete="$emit('delete', $event)"
                    @refresh="$emit('refresh')"
                    @order-updated="$emit('order-updated', $event)"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue'

export default {
    name: 'MenuTree',
    props: {
        items: {
            type: Array,
            required: true,
        },
    },
    emits: ['edit', 'delete', 'refresh', 'order-updated'],
    setup(props, { emit }) {
        const draggedItem = ref(null)
        const draggedIndex = ref(null)
        const dragOverIndex = ref(null)

        const onDragStart = (event, item, index) => {
            draggedItem.value = item
            draggedIndex.value = index
            event.dataTransfer.effectAllowed = 'move'
            event.dataTransfer.setData('text/html', event.target)
            event.target.style.opacity = '0.5'
        }

        const onDragEnd = (event) => {
            event.target.style.opacity = '1'
            draggedItem.value = null
            draggedIndex.value = null
            dragOverIndex.value = null
        }

        const onDragOver = (event) => {
            event.preventDefault()
            event.dataTransfer.dropEffect = 'move'
        }

        const onItemDragOver = (event, index) => {
            event.preventDefault()
            if (draggedIndex.value !== null && draggedIndex.value !== index) {
                dragOverIndex.value = index
            }
        }

        const onItemDragEnter = (index) => {
            if (draggedIndex.value !== null && draggedIndex.value !== index) {
                dragOverIndex.value = index
            }
        }

        const onItemDragLeave = () => {
            // Не очищаем dragOverIndex здесь, чтобы сохранить визуальный индикатор
        }

        const onDrop = (event) => {
            event.preventDefault()
            
            if (!draggedItem.value || draggedIndex.value === null) return

            const dropIndex = dragOverIndex.value !== null ? dragOverIndex.value : draggedIndex.value

            if (draggedIndex.value === dropIndex) {
                dragOverIndex.value = null
                return
            }

            // Создаем новый массив с измененным порядком
            const newItems = [...props.items]
            const [removed] = newItems.splice(draggedIndex.value, 1)
            newItems.splice(dropIndex, 0, removed)

            // Обновляем порядок (order) для всех элементов
            const updatedItems = newItems.map((item, index) => ({
                id: item.id,
                order: index,
                parent_id: item.parent_id || null,
            }))

            // Отправляем событие с обновленным порядком и новым массивом элементов
            emit('order-updated', {
                items: updatedItems,
                newOrder: newItems.map(item => item.id),
                newItems: newItems.map((item, index) => ({
                    ...item,
                    order: index,
                })),
            })

            dragOverIndex.value = null
        }

        return {
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
        }
    },
}
</script>

<style scoped>
.menu-item {
    position: relative;
    transition: background-color 0.2s;
}

.menu-item.dragging {
    opacity: 0.5;
}

.menu-item.drag-over {
    background-color: rgba(59, 130, 246, 0.1);
    border-top: 2px solid rgba(59, 130, 246, 0.5);
}

.menu-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.cursor-move {
    cursor: move;
}
</style>

