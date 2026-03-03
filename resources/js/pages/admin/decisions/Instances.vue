<template>
    <div class="instances-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Экземпляры</h1>
                <p class="text-muted-foreground mt-1">Управление экземплярами для услуг</p>
            </div>
            <div class="flex items-center gap-3">
                <router-link
                    :to="{ name: 'admin.decisions.instances.create' }"
                    class="h-11 px-6 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-2xl shadow-lg shadow-accent/10 inline-flex items-center justify-center gap-2"
                >
                    <span>+</span>
                    <span>Создать экземпляр</span>
                </router-link>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка экземпляров...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Instances List -->
        <div v-if="!loading && instances.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
            <div class="divide-y divide-border">
                <div
                    v-for="instance in instances"
                    :key="instance.id"
                    class="p-4 hover:bg-muted/10 transition-colors flex items-center justify-between"
                >
                    <div class="flex-1">
                        <h3 class="font-medium text-foreground">{{ instance.name }}</h3>
                        <p class="text-sm text-muted-foreground">
                            Порядок: {{ instance.order }} | 
                            {{ instance.is_active ? 'Активен' : 'Неактивен' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="{ name: 'admin.decisions.instances.edit', params: { id: instance.id } }"
                            class="px-3 py-1.5 text-sm bg-muted hover:bg-muted/80 rounded-lg transition-colors inline-block"
                        >
                            Редактировать
                        </router-link>
                        <button
                            @click="deleteInstance(instance)"
                            class="px-3 py-1.5 text-sm bg-destructive/10 text-destructive hover:bg-destructive/20 rounded-lg transition-colors"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && instances.length === 0" class="text-center py-12 bg-card rounded-lg border border-border">
            <p class="text-muted-foreground">Экземпляры не найдены</p>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiDelete } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'DecisionInstances',
    setup() {
        const loading = ref(false);
        const error = ref(null);
        const instances = ref([]);

        const fetchInstances = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/instances');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки экземпляров');
                }
                const data = await response.json();
                instances.value = data.data || [];
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки экземпляров';
                console.error('Error fetching instances:', err);
            } finally {
                loading.value = false;
            }
        };

        const deleteInstance = async (instance) => {
            const result = await Swal.fire({
                title: 'Удалить экземпляр?',
                text: `Вы уверены, что хотите удалить экземпляр "${instance.name}"?`,
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
                const response = await apiDelete(`/instances/${instance.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка удаления экземпляра');
                }

                await Swal.fire({
                    title: 'Экземпляр удален',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                await fetchInstances();
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка удаления экземпляра',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            }
        };

        onMounted(() => {
            fetchInstances();
        });

        return {
            loading,
            error,
            instances,
            fetchInstances,
            deleteInstance,
        };
    },
};
</script>


