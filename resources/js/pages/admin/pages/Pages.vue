<template>
    <div class="pages-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Страницы</h1>
            <p class="text-muted-foreground mt-1">Управление страницами сайта</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="flex flex-col items-center gap-4">
                <div class="w-12 h-12 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
                <p class="text-muted-foreground">Загрузка страниц...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Pages List -->
        <div v-if="!loading && !error" class="space-y-4">
            <div class="flex items-center justify-between">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Поиск по названию или URL..."
                    class="px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent max-w-md"
                />
                <router-link
                    :to="{ name: 'admin.pages.create' }"
                    class="px-4 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors text-sm font-medium flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить страницу
                </router-link>
            </div>

            <div v-if="filteredPages.length === 0" class="text-center py-12 text-muted-foreground">
                <p>Страницы не найдены</p>
            </div>

            <div v-else class="bg-card border border-border rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-muted/30">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">Название</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">URL</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">Статус</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-foreground">Порядок</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-foreground">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="page in filteredPages"
                            :key="page.id"
                            class="hover:bg-muted/20 transition-colors"
                        >
                            <td class="px-4 py-3">
                                <div class="font-medium text-foreground">{{ page.title }}</div>
                                <div v-if="page.seo_title" class="text-xs text-muted-foreground mt-1">
                                    SEO: {{ page.seo_title }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="text-xs bg-muted px-2 py-1 rounded">/{{ page.slug }}</code>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-1 text-xs rounded"
                                    :class="page.is_active ? 'bg-green-500/20 text-green-600' : 'bg-muted text-muted-foreground'"
                                >
                                    {{ page.is_active ? 'Активна' : 'Неактивна' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-muted-foreground">{{ page.order }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <router-link
                                        :to="{ name: 'admin.pages.edit', params: { id: page.id } }"
                                        class="p-2 text-accent hover:bg-accent/10 rounded transition-colors inline-block"
                                        title="Редактировать"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </router-link>
                                    <button
                                        @click="deletePage(page.id)"
                                        class="p-2 text-destructive hover:bg-destructive/10 rounded transition-colors"
                                        title="Удалить"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'Pages',
    setup() {
        const loading = ref(true);
        const error = ref(null);
        const pages = ref([]);
        const searchQuery = ref('');

        const filteredPages = computed(() => {
            if (!searchQuery.value) {
                return pages.value;
            }
            const query = searchQuery.value.toLowerCase();
            return pages.value.filter(page => 
                page.title.toLowerCase().includes(query) ||
                page.slug.toLowerCase().includes(query)
            );
        });

        const fetchPages = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/v1/pages');
                if (response.data && response.data.data) {
                    pages.value = response.data.data;
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки страниц';
                console.error('Error fetching pages:', err);
            } finally {
                loading.value = false;
            }
        };


        const deletePage = async (pageId) => {
            const result = await Swal.fire({
                title: 'Удалить страницу?',
                text: 'Это действие нельзя отменить',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/api/v1/pages/${pageId}`);
                    await Swal.fire('Удалено!', 'Страница успешно удалена', 'success');
                    fetchPages();
                } catch (err) {
                    await Swal.fire('Ошибка!', err.response?.data?.message || 'Не удалось удалить страницу', 'error');
                }
            }
        };

        onMounted(() => {
            fetchPages();
        });

        return {
            loading,
            error,
            pages,
            searchQuery,
            filteredPages,
            deletePage,
        };
    },
};
</script>

