<template>
    <div class="product-form-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">
                    {{ isEdit ? 'Редактировать продукт' : 'Создать продукт' }}
                </h1>
                <p class="text-muted-foreground mt-1">Управление продуктом для блока решений</p>
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
            <ProductForm
                :initial-data="form"
                :saving="saving"
                :error="formError"
                @submit="saveProduct"
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
import ProductForm from '../../../../components/admin/decisions/ProductForm.vue';

export default {
    name: 'ProductFormPage',
    components: {
        ProductForm,
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
            card_preview_image_id: null,
            short_description: '',
            page_title: '',
            page_subtitle: '',
            cta_text: '',
            cta_link: '',
            html_content: '',
            image: null,
            icon: null,
            card_preview_image: null,
            order: 0,
            is_active: true,
        });

        const isEdit = computed(() => !!route.params.id);

        const fetchProduct = async () => {
            if (!isEdit.value) {
                loading.value = false;
                return;
            }

            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet(`/products/${route.params.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка загрузки продукта');
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
                        card_preview_image_id: data.data.card_preview_image_id || null,
                        short_description: data.data.short_description || '',
                        page_title: data.data.page_title || '',
                        page_subtitle: data.data.page_subtitle || '',
                        cta_text: data.data.cta_text || '',
                        cta_link: data.data.cta_link || '',
                        html_content: data.data.html_content || '',
                        image: data.data.image || null,
                        icon: data.data.icon || null,
                        card_preview_image: data.data.card_preview_image || null,
                        order: data.data.order ?? 0,
                        is_active: data.data.is_active !== false,
                        services: data.data.services || [],
                    };
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки продукта';
                console.error('Error fetching product:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveProduct = async (formData) => {
            saving.value = true;
            formError.value = null;
            try {
                const url = isEdit.value ? `/products/${route.params.id}` : '/products';
                const method = isEdit.value ? apiPut : apiPost;
                
                const response = await method(url, formData);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Ошибка ${isEdit.value ? 'обновления' : 'создания'} продукта`);
                }

                await Swal.fire({
                    title: isEdit.value ? 'Продукт обновлен' : 'Продукт создан',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                router.push({ name: 'admin.decisions.products' });
            } catch (err) {
                formError.value = err.message || 'Ошибка сохранения продукта';
            } finally {
                saving.value = false;
            }
        };

        const goBack = () => {
            router.push({ name: 'admin.decisions.products' });
        };

        onMounted(() => {
            fetchProduct();
        });

        return {
            loading,
            saving,
            error,
            formError,
            form,
            isEdit,
            saveProduct,
            goBack,
        };
    },
};
</script>

