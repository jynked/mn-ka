<template>
    <div class="chapter-form-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">
                    {{ isEdit ? 'Редактировать раздел' : 'Создать раздел' }}
                </h1>
                <p class="text-muted-foreground mt-1">Управление разделом для блока решений</p>
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
            <ChapterForm
                :initial-data="form"
                :saving="saving"
                :error="formError"
                @submit="saveChapter"
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
import ChapterForm from '../../../../components/admin/decisions/ChapterForm.vue';

export default {
    name: 'ChapterFormPage',
    components: {
        ChapterForm,
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
            order: 0,
            is_active: true,
        });

        const isEdit = computed(() => !!route.params.id);

        const fetchChapter = async () => {
            if (!isEdit.value) {
                loading.value = false;
                return;
            }

            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet(`/chapters/${route.params.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки раздела');
                }
                const data = await response.json();
                if (data.data) {
                    form.value = {
                        id: data.data.id,
                        name: data.data.name || '',
                        order: data.data.order ?? 0,
                        is_active: data.data.is_active !== false,
                    };
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки раздела';
                console.error('Error fetching chapter:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveChapter = async (formData) => {
            saving.value = true;
            formError.value = null;
            try {
                const url = isEdit.value ? `/chapters/${route.params.id}` : '/chapters';
                const method = isEdit.value ? apiPut : apiPost;
                
                const response = await method(url, formData);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Ошибка ${isEdit.value ? 'обновления' : 'создания'} раздела`);
                }

                await Swal.fire({
                    title: isEdit.value ? 'Раздел обновлен' : 'Раздел создан',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                router.push({ name: 'admin.decisions.chapters' });
            } catch (err) {
                formError.value = err.message || 'Ошибка сохранения раздела';
            } finally {
                saving.value = false;
            }
        };

        const goBack = () => {
            router.push({ name: 'admin.decisions.chapters' });
        };

        onMounted(() => {
            fetchChapter();
        });

        return {
            loading,
            saving,
            error,
            formError,
            form,
            isEdit,
            saveChapter,
            goBack,
        };
    },
};
</script>

