<template>
    <div class="option-trees-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Деревья опций</h1>
                <p class="text-muted-foreground mt-1">Управление древовидными опциями для услуг</p>
            </div>
            <div class="flex items-center gap-3">
                <router-link
                    :to="{ name: 'admin.decisions.option-trees.create' }"
                    class="h-11 px-6 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-2xl shadow-lg shadow-accent/10 inline-flex items-center justify-center gap-2"
                >
                    <span>+</span>
                    <span>Создать дерево опций</span>
                </router-link>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка деревьев опций...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Option Trees List -->
        <div v-if="!loading && optionTrees.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
            <div class="divide-y divide-border">
                <div
                    v-for="tree in optionTrees"
                    :key="tree.id"
                    class="p-4 hover:bg-muted/10 transition-colors flex items-center justify-between"
                >
                    <div class="flex-1">
                        <h3 class="font-medium text-foreground">{{ tree.name }}</h3>
                        <p class="text-sm text-muted-foreground">
                            Родитель: {{ tree.parent || 'Корневой' }} | 
                            Сортировка: {{ tree.sort }} | 
                            {{ tree.is_active ? 'Активно' : 'Неактивно' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="{ name: 'admin.decisions.option-trees.edit', params: { id: tree.id } }"
                            class="px-3 py-1.5 text-sm bg-muted hover:bg-muted/80 rounded-lg transition-colors inline-block"
                        >
                            Редактировать
                        </router-link>
                        <button
                            @click="deleteOptionTree(tree)"
                            class="px-3 py-1.5 text-sm bg-destructive/10 text-destructive hover:bg-destructive/20 rounded-lg transition-colors"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && optionTrees.length === 0" class="text-center py-12 bg-card rounded-lg border border-border">
            <p class="text-muted-foreground">Деревья опций не найдены</p>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiDelete } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'DecisionOptionTrees',
    setup() {
        const loading = ref(false);
        const error = ref(null);
        const optionTrees = ref([]);

        const fetchOptionTrees = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/option-trees');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки деревьев опций');
                }
                const data = await response.json();
                optionTrees.value = data.data || [];
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки деревьев опций';
                console.error('Error fetching option trees:', err);
            } finally {
                loading.value = false;
            }
        };

        const deleteOptionTree = async (tree) => {
            const result = await Swal.fire({
                title: 'Удалить дерево опций?',
                text: `Вы уверены, что хотите удалить дерево опций "${tree.name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
                confirmButtonColor: '#dc2626',
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await apiDelete(`/option-trees/${tree.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка удаления дерева опций');
                }

                await Swal.fire({
                    title: 'Дерево опций удалено',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                await fetchOptionTrees();
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка удаления дерева опций',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            }
        };

        onMounted(() => {
            fetchOptionTrees();
        });

        return {
            loading,
            error,
            optionTrees,
            fetchOptionTrees,
            deleteOptionTree,
        };
    },
};
</script>


