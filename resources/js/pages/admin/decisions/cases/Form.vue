<template>
    <div class="case-form-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">
                    {{ isEdit ? 'Редактировать случай' : 'Создать случай' }}
                </h1>
                <p class="text-muted-foreground mt-1">Управление случаем для блока решений</p>
            </div>
            <button
                @click="goBack"
                class="h-11 px-6 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors"
            >
                Назад
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Form -->
        <div v-if="!loading" class="bg-card rounded-lg border border-border p-6 space-y-6">
            <CaseForm
                :initial-data="form"
                :saving="saving"
                :error="formError"
                @submit="saveCase"
                @cancel="goBack"
            />
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { apiGet, apiPost, apiPut } from '../../../../utils/api';
import Swal from 'sweetalert2';
import CaseForm from '../../../../components/admin/decisions/CaseForm.vue';

export default {
    name: 'CaseFormPage',
    components: {
        CaseForm,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const loading = ref(false);
        const saving = ref(false);
        const error = ref(null);
        const formError = ref(null);
        const form = ref({
            id: null,
            name: '',
            slug: '',
            chapter_id: route.query.chapter_id ? parseInt(route.query.chapter_id) : null,
            order: 0,
            is_active: true,
        });

        const isEdit = computed(() => !!route.params.id);

        const fetchCase = async () => {
            if (!isEdit.value) {
                loading.value = false;
                return;
            }

            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet(`/cases/${route.params.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки случая');
                }
                const data = await response.json();
                if (data.data) {
                    form.value = {
                        id: data.data.id,
                        name: data.data.name || '',
                        slug: data.data.slug || '',
                        chapter_id: data.data.chapter_id || null,
                        order: data.data.order ?? 0,
                        is_active: data.data.is_active !== false,
                    };
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки случая';
                console.error('Error fetching case:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveCase = async (formData) => {
            saving.value = true;
            formError.value = null;
            try {
                const url = isEdit.value ? `/cases/${route.params.id}` : '/cases';
                const method = isEdit.value ? apiPut : apiPost;
                
                const response = await method(url, formData);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Ошибка ${isEdit.value ? 'обновления' : 'создания'} случая`);
                }

                await Swal.fire({
                    title: isEdit.value ? 'Случай обновлен' : 'Случай создан',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                router.push({ name: 'admin.decisions.cases' });
            } catch (err) {
                formError.value = err.message || 'Ошибка сохранения случая';
            } finally {
                saving.value = false;
            }
        };

        const goBack = () => {
            router.push({ name: 'admin.decisions.cases' });
        };

        onMounted(() => {
            fetchCase();
        });

        return {
            loading,
            saving,
            error,
            formError,
            form,
            isEdit,
            saveCase,
            goBack,
        };
    },
};
</script>





