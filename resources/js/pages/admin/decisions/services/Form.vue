<template>
    <div class="service-form-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">
                    {{ isEdit ? 'Редактировать услугу' : 'Создать услугу' }}
                </h1>
                <p class="text-muted-foreground mt-1">Управление услугой для блока решений</p>
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
            <ServiceForm
                :initial-data="form"
                :saving="saving"
                :error="formError"
                @submit="saveService"
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
import ServiceForm from '../../../../components/admin/decisions/ServiceForm.vue';

export default {
    name: 'ServiceFormPage',
    components: {
        ServiceForm,
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
            chapter_id: null,
            image_id: null,
            icon_id: null,
            html_content: '',
            image: null,
            icon: null,
            order: 0,
            is_active: true,
        });

        const isEdit = computed(() => !!route.params.id);

        const fetchService = async () => {
            if (!isEdit.value) {
                loading.value = false;
                return;
            }

            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet(`/services/${route.params.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки услуги');
                }
                const data = await response.json();
                if (data.data) {
                    form.value = {
                        id: data.data.id,
                        name: data.data.name || '',
                        slug: data.data.slug || '',
                        chapter_id: data.data.chapter_id || null,
                        image_id: data.data.image_id || null,
                        icon_id: data.data.icon_id || null,
                        html_content: data.data.html_content || '',
                        image: data.data.image || null,
                        icon: data.data.icon || null,
                        order: data.data.order ?? 0,
                        is_active: data.data.is_active !== false,
                    };
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки услуги';
                console.error('Error fetching service:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveService = async (formData) => {
            saving.value = true;
            formError.value = null;
            try {
                const url = isEdit.value ? `/services/${route.params.id}` : '/services';
                const method = isEdit.value ? apiPut : apiPost;
                
                const response = await method(url, formData);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Ошибка ${isEdit.value ? 'обновления' : 'создания'} услуги`);
                }

                await Swal.fire({
                    title: isEdit.value ? 'Услуга обновлена' : 'Услуга создана',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                router.push({ name: 'admin.decisions.services' });
            } catch (err) {
                formError.value = err.message || 'Ошибка сохранения услуги';
            } finally {
                saving.value = false;
            }
        };

        const goBack = () => {
            router.push({ name: 'admin.decisions.services' });
        };

        onMounted(() => {
            fetchService();
        });

        return {
            loading,
            saving,
            error,
            formError,
            form,
            isEdit,
            saveService,
            goBack,
        };
    },
};
</script>

