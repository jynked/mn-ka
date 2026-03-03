<template>
    <div class="options-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Опции</h1>
                <p class="text-muted-foreground mt-1">Управление опциями для услуг</p>
            </div>
            <div class="flex items-center gap-3">
                <router-link
                    :to="{ name: 'admin.decisions.options.create' }"
                    class="h-11 px-6 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-2xl shadow-lg shadow-accent/10 inline-flex items-center justify-center gap-2"
                >
                    <span>+</span>
                    <span>Создать опцию</span>
                </router-link>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка опций...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Options List -->
        <div v-if="!loading && options.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
            <div class="divide-y divide-border">
                <div
                    v-for="option in options"
                    :key="option.id"
                    class="p-4 hover:bg-muted/10 transition-colors flex items-center justify-between"
                >
                    <div class="flex-1">
                        <h3 class="font-medium text-foreground">{{ option.name }}</h3>
                        <p class="text-sm text-muted-foreground">
                            Порядок: {{ option.order }} | 
                            {{ option.is_active ? 'Активна' : 'Неактивна' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="{ name: 'admin.decisions.options.edit', params: { id: option.id } }"
                            class="px-3 py-1.5 text-sm bg-muted hover:bg-muted/80 rounded-lg transition-colors inline-block"
                        >
                            Редактировать
                        </router-link>
                        <button
                            @click="deleteOption(option)"
                            class="px-3 py-1.5 text-sm bg-destructive/10 text-destructive hover:bg-destructive/20 rounded-lg transition-colors"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && options.length === 0" class="text-center py-12 bg-card rounded-lg border border-border">
            <p class="text-muted-foreground">Опции не найдены</p>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiDelete } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'DecisionOptions',
    setup() {
        const loading = ref(false);
        const error = ref(null);
        const options = ref([]);

        const fetchOptions = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/options');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки опций');
                }
                const data = await response.json();
                options.value = data.data || [];
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки опций';
                console.error('Error fetching options:', err);
            } finally {
                loading.value = false;
            }
        };

        const deleteOption = async (option) => {
            const result = await Swal.fire({
                title: 'Удалить опцию?',
                text: `Вы уверены, что хотите удалить опцию "${option.name}"?`,
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
                const response = await apiDelete(`/options/${option.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка удаления опции');
                }

                await Swal.fire({
                    title: 'Опция удалена',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                await fetchOptions();
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка удаления опции',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            }
        };

        onMounted(() => {
            fetchOptions();
        });

        return {
            loading,
            error,
            options,
            fetchOptions,
            deleteOption,
        };
    },
};
</script>


