<template>
    <div class="cases-block-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки блока "Кейсы и объекты"</h1>
            <p class="text-muted-foreground mt-1">Управление блоком "Кейсы и объекты" на главной странице</p>
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
        <div v-if="!loading" class="space-y-6">
            <!-- General Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Общие настройки</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Заголовок блока
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Кейсы и объекты"
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-4 h-4 rounded border-border"
                        />
                        <label for="is_active" class="text-sm font-medium text-foreground">
                            Блок активен на главной странице
                        </label>
                    </div>
                </div>
            </div>

            <!-- Cases Selection -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Выбор кейсов (максимум 2)</h2>
                </div>

                <div v-if="availableCases.length === 0" class="text-center py-8 text-muted-foreground">
                    <p>Нет доступных кейсов. Создайте кейсы в разделе "Кейсы".</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(caseId, index) in form.case_ids"
                        :key="index"
                        class="border border-border rounded-lg p-4 space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-foreground">Кейс {{ index + 1 }}</h3>
                            <button
                                @click="removeCase(index)"
                                class="px-3 py-1 text-sm text-destructive hover:bg-destructive/10 rounded transition-colors"
                            >
                                Удалить
                            </button>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Выберите кейс
                            </label>
                            <select
                                v-model="form.case_ids[index]"
                                class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                            >
                                <option :value="null">Выберите кейс</option>
                                <option
                                    v-for="caseItem in getAvailableCasesForIndex(index)"
                                    :key="caseItem.id"
                                    :value="caseItem.id"
                                >
                                    {{ caseItem.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <button
                        v-if="form.case_ids.length < 2"
                        @click="addCase"
                        class="w-full px-4 py-2 bg-accent/10 text-accent border border-accent/40 rounded-lg hover:bg-accent/20 transition-colors text-sm font-medium"
                    >
                        + Добавить кейс
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-border">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                >
                    {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'CasesBlockSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const availableCases = ref([]);
        const form = ref({
            title: '',
            is_active: true,
            case_ids: [],
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/v1/cases-block-settings');
                if (response.data && response.data.data) {
                    form.value = {
                        title: response.data.data.title || '',
                        is_active: response.data.data.is_active !== false,
                        case_ids: response.data.data.case_ids || [],
                    };
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const fetchCases = async () => {
            try {
                const response = await axios.get('/api/v1/cases?active=1');
                if (response.data && response.data.data) {
                    availableCases.value = response.data.data;
                }
            } catch (err) {
                console.error('Error fetching cases:', err);
            }
        };

        const getAvailableCasesForIndex = (index) => {
            // Возвращаем все кейсы, кроме уже выбранных в других позициях
            const selectedIds = form.value.case_ids.filter((id, i) => i !== index && id !== null);
            return availableCases.value.filter(c => !selectedIds.includes(c.id));
        };

        const addCase = () => {
            if (form.value.case_ids.length < 2) {
                form.value.case_ids.push(null);
            }
        };

        const removeCase = (index) => {
            if (form.value.case_ids && form.value.case_ids.length > index) {
                form.value.case_ids.splice(index, 1);
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            try {
                // Фильтруем null значения
                const caseIds = form.value.case_ids.filter(id => id !== null);
                
                const dataToSend = {
                    title: form.value.title,
                    is_active: form.value.is_active,
                    case_ids: caseIds,
                };
                
                const response = await axios.put('/api/v1/cases-block-settings', dataToSend);
                
                await Swal.fire({
                    title: 'Настройки сохранены',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка сохранения настроек';
                await Swal.fire({
                    title: 'Ошибка',
                    text: error.value,
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                saving.value = false;
            }
        };

        onMounted(async () => {
            await Promise.all([
                fetchSettings(),
                fetchCases(),
            ]);
        });

        return {
            loading,
            saving,
            error,
            form,
            availableCases,
            getAvailableCasesForIndex,
            addCase,
            removeCase,
            saveSettings,
        };
    },
};
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>

