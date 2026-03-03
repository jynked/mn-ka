<template>
    <div class="settings-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Настройки блока</h1>
                <p class="text-muted-foreground mt-1">Настройки блока "Решения"</p>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка настроек...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Settings Form -->
        <div v-if="!loading" class="bg-card rounded-lg border border-border p-6 space-y-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Заголовок блока
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        placeholder="Выберите решение под ваш участок"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Количество колонок
                    </label>
                    <select
                        v-model="form.columns"
                        class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option :value="1">1 колонка</option>
                        <option :value="2">2 колонки</option>
                        <option :value="3">3 колонки</option>
                        <option :value="4">4 колонки</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        id="is_active"
                        class="w-4 h-4 rounded border-border"
                    />
                    <label for="is_active" class="text-sm font-medium text-foreground">
                        Блок активен
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-border">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent hover:bg-accent/90 text-white rounded-lg transition-colors disabled:opacity-50"
                >
                    {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiPut } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'DecisionSettings',
    setup() {
        const loading = ref(false);
        const saving = ref(false);
        const error = ref(null);
        const form = ref({
            title: 'Выберите решение под ваш участок',
            columns: 3,
            is_active: true,
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/decision-block-settings');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки настроек');
                }
                const data = await response.json();
                if (data.data) {
                    form.value = {
                        title: data.data.title || 'Выберите решение под ваш участок',
                        columns: data.data.columns || 3,
                        is_active: data.data.is_active !== false,
                    };
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки настроек';
                console.error('Error fetching settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            try {
                const response = await apiPut('/decision-block-settings', form.value);
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Ошибка сохранения настроек');
                }

                await Swal.fire({
                    title: 'Настройки сохранены',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } catch (err) {
                error.value = err.message || 'Ошибка сохранения настроек';
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка сохранения настроек',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                saving.value = false;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            error,
            form,
            fetchSettings,
            saveSettings,
        };
    },
};
</script>

