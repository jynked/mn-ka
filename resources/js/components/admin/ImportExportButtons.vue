<template>
    <div class="flex items-center gap-2 relative">
        <!-- Кнопка экспорта -->
        <div class="export-button-container relative">
            <button
                @click="toggleExportMenu"
                class="h-11 px-4 flex items-center gap-2 rounded-md bg-accent/10 hover:bg-accent/20 transition-colors text-sm font-medium"
                type="button"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="hidden sm:inline">Экспорт</span>
            </button>

            <!-- Меню экспорта -->
            <div
                v-if="showExportMenu"
                class="absolute top-full right-0 mt-2 w-56 bg-card border border-border rounded-lg shadow-lg z-50"
            >
            <button
                v-for="option in exportOptions"
                :key="option.value"
                @click="handleExport(option.value)"
                class="w-full px-4 py-3 text-left text-sm hover:bg-accent/10 transition-colors first:rounded-t-lg last:rounded-b-lg"
            >
                {{ option.label }}
            </button>
            </div>
        </div>

        <!-- Кнопка импорта -->
        <div class="import-button-container relative">
            <button
                @click="toggleImportMenu"
                class="h-11 px-4 flex items-center gap-2 rounded-md bg-accent/10 hover:bg-accent/20 transition-colors text-sm font-medium"
                type="button"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                <span class="hidden sm:inline">Импорт</span>
            </button>

            <!-- Меню импорта -->
            <div
                v-if="showImportMenu"
                class="absolute top-full right-0 mt-2 w-56 bg-card border border-border rounded-lg shadow-lg z-50"
            >
            <button
                v-for="option in importOptions"
                :key="option.value"
                @click="handleImportSelect(option.value)"
                class="w-full px-4 py-3 text-left text-sm hover:bg-accent/10 transition-colors first:rounded-t-lg last:rounded-b-lg"
            >
                {{ option.label }}
            </button>
            </div>
        </div>

        <!-- Скрытый input для файлов -->
        <input
            ref="fileInput"
            type="file"
            accept=".zip,.csv"
            @change="handleFileChange"
            class="hidden"
        />

        <!-- Модальное окно с результатами импорта (через Teleport в body) -->
        <Teleport to="body">
            <div
                v-if="showImportResults"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999] p-4"
                @click.self="showImportResults = false"
            >
                <div class="bg-card rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-border">
                        <h3 class="text-lg font-semibold">Результаты импорта</h3>
                    </div>
                    <div class="p-6 overflow-y-auto flex-1">
                        <div class="mb-4">
                            <p class="text-sm mb-2">
                                <span class="font-medium">Успешно импортировано:</span>
                                <span class="text-green-600 dark:text-green-400 ml-2">{{ importResults.success_count || 0 }}</span>
                            </p>
                            <p class="text-sm">
                                <span class="font-medium">Пропущено:</span>
                                <span class="text-orange-600 dark:text-orange-400 ml-2">{{ importResults.skip_count || 0 }}</span>
                            </p>
                        </div>

                        <!-- Ошибки -->
                        <div v-if="importResults.errors && importResults.errors.length > 0" class="mt-4">
                            <h4 class="font-medium text-sm mb-2">Ошибки импорта:</h4>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <div
                                    v-for="(error, index) in importResults.errors"
                                    :key="index"
                                    class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-sm"
                                >
                                    <p class="font-medium text-red-800 dark:text-red-400">
                                        <span v-if="error.section">[{{ error.section }}]</span>
                                        Строка {{ error.row || 'неизвестна' }}:
                                    </p>
                                    <ul v-if="error.errors && error.errors.length > 0" class="list-disc list-inside mt-1 text-red-700 dark:text-red-300">
                                        <li v-for="(err, i) in error.errors" :key="i">{{ err }}</li>
                                    </ul>
                                    <p v-else-if="typeof error === 'string'" class="mt-1 text-red-700 dark:text-red-300">
                                        {{ error }}
                                    </p>
                                    <p v-else class="mt-1 text-red-700 dark:text-red-300">
                                        Неизвестная ошибка
                                    </p>
                                    
                                    <!-- Дополнительная информация о заголовках -->
                                    <div v-if="error.data && (error.data.found_headers || error.data.expected_headers)" class="mt-3 pt-3 border-t border-red-300 dark:border-red-700">
                                        <p class="text-xs font-semibold text-red-900 dark:text-red-300 mb-1">Детали:</p>
                                        <div v-if="error.data.found_headers" class="text-xs text-red-800 dark:text-red-400 mb-1">
                                            <span class="font-medium">Найдено:</span> {{ Array.isArray(error.data.found_headers) ? error.data.found_headers.join(', ') : error.data.found_headers }}
                                        </div>
                                        <div v-if="error.data.expected_headers" class="text-xs text-red-800 dark:text-red-400">
                                            <span class="font-medium">Ожидается:</span> {{ Array.isArray(error.data.expected_headers) ? error.data.expected_headers.join(', ') : error.data.expected_headers }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 border-t border-border flex justify-end">
                        <button
                            @click="showImportResults = false"
                            class="px-4 py-2 bg-accent text-accent-foreground rounded-md hover:bg-accent/90 transition-colors"
                        >
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Loader (через Teleport в body) -->
        <Teleport to="body">
            <div
                v-if="loading"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999]"
            >
                <div class="bg-card rounded-lg p-6 flex items-center gap-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-accent"></div>
                    <span class="text-sm font-medium">{{ loadingMessage }}</span>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export default {
    name: 'ImportExportButtons',
    setup() {
        const showExportMenu = ref(false);
        const showImportMenu = ref(false);
        const showImportResults = ref(false);
        const loading = ref(false);
        const loadingMessage = ref('');
        const importResults = ref({});
        const fileInput = ref(null);
        const selectedImportType = ref(null);

        const exportOptions = [
            { label: 'Экспорт всех разделов', value: 'all' },
            { label: 'Экспорт разделов', value: 'chapters' },
            { label: 'Экспорт продуктов', value: 'products' },
            { label: 'Экспорт услуг', value: 'services' },
            { label: 'Экспорт случаев', value: 'cases' },
        ];

        const importOptions = [
            { label: 'Импорт всех разделов', value: 'all' },
            { label: 'Импорт разделов', value: 'chapters' },
            { label: 'Импорт продуктов', value: 'products' },
            { label: 'Импорт услуг', value: 'services' },
            { label: 'Импорт случаев', value: 'cases' },
        ];

        const toggleExportMenu = () => {
            showExportMenu.value = !showExportMenu.value;
            showImportMenu.value = false;
        };

        const toggleImportMenu = () => {
            showImportMenu.value = !showImportMenu.value;
            showExportMenu.value = false;
        };

        // Обработка клика вне меню для их закрытия
        const handleClickOutside = (event) => {
            const target = event.target;
            const exportButton = target.closest('.export-button-container');
            const importButton = target.closest('.import-button-container');
            
            if (!exportButton && !importButton) {
                showExportMenu.value = false;
                showImportMenu.value = false;
            }
        };

        onMounted(() => {
            document.addEventListener('click', handleClickOutside);
        });

        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside);
        });

        const handleExport = async (type) => {
            showExportMenu.value = false;
            loading.value = true;
            loadingMessage.value = 'Подготовка экспорта...';

            try {
                const endpoints = {
                    all: '/api/v1/decisions/export',
                    chapters: '/api/v1/chapters/export',
                    products: '/api/v1/products/export',
                    services: '/api/v1/services/export',
                    cases: '/api/v1/cases/export',
                };

                const response = await axios.get(endpoints[type], {
                    responseType: 'blob',
                });

                // Создаем ссылку для скачивания
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                
                // Получаем имя файла из заголовка
                const contentDisposition = response.headers['content-disposition'];
                let filename = `${type}_export_${new Date().toISOString().split('T')[0]}.zip`;
                if (contentDisposition) {
                    const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                    if (filenameMatch) {
                        filename = filenameMatch[1];
                    }
                }
                
                link.setAttribute('download', filename);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Export error:', error);
                alert('Ошибка при экспорте: ' + (error.response?.data?.message || error.message));
            } finally {
                loading.value = false;
            }
        };

        const handleImportSelect = (type) => {
            selectedImportType.value = type;
            showImportMenu.value = false;
            fileInput.value.click();
        };

        const handleFileChange = async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            loading.value = true;
            loadingMessage.value = 'Импортирование данных...';

            try {
                const formData = new FormData();
                formData.append('file', file);

                const endpoints = {
                    all: '/api/v1/decisions/import',
                    chapters: '/api/v1/chapters/import',
                    products: '/api/v1/products/import',
                    services: '/api/v1/services/import',
                    cases: '/api/v1/cases/import',
                };

                const response = await axios.post(endpoints[selectedImportType.value], formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                // Нормализуем данные ответа
                importResults.value = {
                    success: response.data.success !== undefined ? response.data.success : true,
                    message: response.data.message || 'Импорт завершен',
                    success_count: response.data.success_count || 0,
                    skip_count: response.data.skip_count || 0,
                    errors: Array.isArray(response.data.errors) ? response.data.errors : [],
                };
                
                showImportResults.value = true;

                // Очищаем input
                event.target.value = '';
            } catch (error) {
                console.error('Import error:', error);
                
                // Проверяем, есть ли детальная информация об ошибке
                if (error.response?.data) {
                    const errorData = error.response.data;
                    importResults.value = {
                        success: errorData.success !== undefined ? errorData.success : false,
                        message: errorData.message || 'Ошибка при импорте',
                        success_count: errorData.success_count || 0,
                        skip_count: errorData.skip_count || 0,
                        errors: Array.isArray(errorData.errors) ? errorData.errors : 
                                (errorData.errors ? [errorData.errors] : []),
                    };
                    showImportResults.value = true;
                } else {
                    importResults.value = {
                        success: false,
                        message: 'Ошибка при импорте',
                        success_count: 0,
                        skip_count: 0,
                        errors: [{ row: 'неизвестна', errors: [error.message || 'Неизвестная ошибка'] }],
                    };
                    showImportResults.value = true;
                }
                
                // Очищаем input
                event.target.value = '';
            } finally {
                loading.value = false;
            }
        };

        return {
            showExportMenu,
            showImportMenu,
            showImportResults,
            loading,
            loadingMessage,
            importResults,
            fileInput,
            exportOptions,
            importOptions,
            toggleExportMenu,
            toggleImportMenu,
            handleExport,
            handleImportSelect,
            handleFileChange,
        };
    },
};
</script>

