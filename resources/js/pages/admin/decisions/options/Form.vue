<template>
    <div class="option-form-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">
                {{ isEdit ? 'Редактировать опцию' : 'Создать опцию' }}
            </h1>
            <p class="text-muted-foreground mt-1">Управление опцией</p>
        </div>

        <div class="bg-card rounded-lg border border-border p-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Название *
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        placeholder="Введите название опции"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Порядок
                    </label>
                    <input
                        v-model.number="form.order"
                        type="number"
                        min="0"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        placeholder="0"
                    />
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Активна</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                    <router-link
                        :to="{ name: 'admin.decisions.options' }"
                        class="px-4 py-2 border border-border rounded hover:bg-muted/10 transition-colors"
                    >
                        Отмена
                    </router-link>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-6 py-2 bg-accent text-accent-foreground rounded hover:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ saving ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { apiGet, apiPost, apiPut } from '../../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'OptionForm',
    setup() {
        const route = useRoute();
        const router = useRouter();
        const isEdit = ref(!!route.params.id);
        const saving = ref(false);
        const loading = ref(false);
        const form = ref({
            name: '',
            order: 0,
            is_active: true,
        });

        const fetchOption = async () => {
            if (!isEdit.value) {
                return;
            }

            loading.value = true;
            try {
                const response = await apiGet(`/options/${route.params.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки опции');
                }
                const data = await response.json();
                form.value = {
                    name: data.data.name || '',
                    order: data.data.order || 0,
                    is_active: data.data.is_active ?? true,
                };
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка загрузки опции',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
                router.push({ name: 'admin.decisions.options' });
            } finally {
                loading.value = false;
            }
        };

        const handleSubmit = async () => {
            saving.value = true;
            try {
                const url = isEdit.value 
                    ? `/options/${route.params.id}`
                    : '/options';
                const method = isEdit.value ? apiPut : apiPost;
                
                const response = await method(url, form.value);
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Ошибка сохранения опции');
                }

                await Swal.fire({
                    title: 'Успешно!',
                    text: isEdit.value ? 'Опция обновлена' : 'Опция создана',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                router.push({ name: 'admin.decisions.options' });
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка сохранения опции',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                saving.value = false;
            }
        };

        onMounted(() => {
            fetchOption();
        });

        return {
            isEdit,
            saving,
            loading,
            form,
            handleSubmit,
        };
    },
};
</script>


