<template>
    <div class="page-form-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">
                    {{ isEdit ? 'Редактировать страницу' : 'Создать страницу' }}
                </h1>
                <p class="text-muted-foreground mt-1">Управление страницей сайта</p>
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
        <div v-if="!loading" class="bg-card rounded-lg border border-border p-6">
            <PageFormContent
                :page="page"
                :saving="saving"
                :error="formError"
                @submit="savePage"
                @cancel="goBack"
            />
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import PageFormContent from './PageFormContent.vue';

export default {
    name: 'PageEdit',
    components: {
        PageFormContent,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const loading = ref(false);
        const saving = ref(false);
        const error = ref(null);
        const formError = ref(null);
        const page = ref(null);

        const isEdit = computed(() => {
            return !!route.params.id;
        });

        const fetchPage = async () => {
            if (!isEdit.value) {
                page.value = null;
                return;
            }

            loading.value = true;
            error.value = null;

            try {
                const response = await axios.get(`/api/v1/pages/${route.params.id}`);
                if (response.data && response.data.data) {
                    page.value = response.data.data;
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки страницы';
                console.error('Error fetching page:', err);
            } finally {
                loading.value = false;
            }
        };

        const savePage = async (formData) => {
            saving.value = true;
            formError.value = null;

            try {
                const url = isEdit.value 
                    ? `/api/v1/pages/${route.params.id}`
                    : '/api/v1/pages';
                
                const method = isEdit.value ? 'put' : 'post';
                
                const response = await axios[method](url, formData);

                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: isEdit.value ? 'Страница успешно обновлена' : 'Страница успешно создана',
                    timer: 2000,
                    showConfirmButton: false,
                });

                router.push({ name: 'admin.pages' });
            } catch (err) {
                if (err.response?.status === 422) {
                    formError.value = err.response.data.message || 'Ошибка валидации';
                } else {
                    formError.value = err.response?.data?.message || 'Ошибка сохранения страницы';
                }
            } finally {
                saving.value = false;
            }
        };

        const goBack = () => {
            router.push({ name: 'admin.pages' });
        };

        onMounted(() => {
            fetchPage();
        });

        // Отслеживаем изменения параметра id в маршруте
        watch(
            () => route.params.id,
            (newId, oldId) => {
                if (newId !== oldId) {
                    fetchPage();
                }
            }
        );

        return {
            loading,
            saving,
            error,
            formError,
            page,
            isEdit,
            savePage,
            goBack,
        };
    },
};
</script>

