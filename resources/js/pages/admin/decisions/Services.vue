<template>
    <div class="services-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Услуги</h1>
                <p class="text-muted-foreground mt-1">Управление услугами для блока решений</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    @click="exportServices('csv')"
                    :disabled="loading || exporting"
                    class="h-11 px-4 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <span v-if="!exporting">📥</span>
                    <span v-else>...</span>
                    <span>{{ exporting ? 'Экспорт...' : 'Экспорт CSV' }}</span>
                </button>
                <button
                    @click="exportServices('zip')"
                    :disabled="loading || exporting"
                    class="h-11 px-4 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <span v-if="!exporting">📦</span>
                    <span v-else>...</span>
                    <span>{{ exporting ? 'Экспорт...' : 'Экспорт ZIP' }}</span>
                </button>
                <button
                    @click="showImportDocumentation = true"
                    :disabled="loading || importing"
                    class="h-11 px-4 border border-border bg-background hover:bg-muted/10 rounded-lg transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <span v-if="!importing">📤</span>
                    <span v-else>...</span>
                    <span>{{ importing ? 'Импорт...' : 'Импорт ZIP/CSV' }}</span>
                </button>
                <router-link
                    :to="{ name: 'admin.decisions.services.create' }"
                    class="h-11 px-6 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-2xl shadow-lg shadow-accent/10 inline-flex items-center justify-center gap-2"
                >
                    <span>+</span>
                    <span>Создать услугу</span>
                </router-link>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка услуг...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Services List -->
        <div v-if="!loading && services.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
            <div 
                class="divide-y divide-border"
                @dragover.prevent="onDragOver"
                @drop.prevent="onDrop"
            >
                <div
                    v-for="(service, index) in services"
                    :key="service.id"
                    :class="[
                        'p-4 hover:bg-muted/10 transition-colors flex items-center justify-between cursor-move',
                        { 'dragging': draggedItem?.id === service.id, 'drag-over': dragOverIndex === index }
                    ]"
                    :draggable="true"
                    @dragstart="onDragStart($event, service, index)"
                    @dragend="onDragEnd"
                    @dragover.prevent="onItemDragOver($event, index)"
                    @dragenter.prevent="onItemDragEnter(index)"
                    @dragleave="onItemDragLeave"
                >
                    <!-- Drag Handle -->
                    <div class="flex-shrink-0 text-muted-foreground cursor-grab active:cursor-grabbing mr-3">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 5H13M7 10H13M7 15H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="4" cy="5" r="1" fill="currentColor"/>
                            <circle cx="4" cy="10" r="1" fill="currentColor"/>
                            <circle cx="4" cy="15" r="1" fill="currentColor"/>
                            <circle cx="16" cy="5" r="1" fill="currentColor"/>
                            <circle cx="16" cy="10" r="1" fill="currentColor"/>
                            <circle cx="16" cy="15" r="1" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-medium text-foreground">{{ service.name }}</h3>
                        <p class="text-sm text-muted-foreground">
                            Slug: {{ service.slug }} | 
                            Порядок: {{ service.order }} | 
                            {{ service.is_active ? 'Активна' : 'Неактивна' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="{ name: 'admin.decisions.services.edit', params: { id: service.id } }"
                            class="px-3 py-1.5 text-sm bg-muted hover:bg-muted/80 rounded-lg transition-colors inline-block"
                            @click.stop
                        >
                            Редактировать
                        </router-link>
                        <button
                            @click.stop="deleteService(service)"
                            class="px-3 py-1.5 text-sm bg-destructive/10 text-destructive hover:bg-destructive/20 rounded-lg transition-colors"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && services.length === 0" class="text-center py-12 bg-card rounded-lg border border-border">
            <p class="text-muted-foreground">Услуги не найдены</p>
        </div>

        <!-- Модальное окно с документацией по импорту -->
        <div v-if="showImportDocumentation" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showImportDocumentation = false">
            <div class="bg-background rounded-lg p-6 max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <h2 class="text-2xl font-semibold mb-4">Документация по импорту услуг</h2>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <h3 class="font-semibold text-lg mb-2">Формат файла</h3>
                        <p class="text-muted-foreground mb-2">Поддерживаются следующие форматы:</p>
                        <ul class="list-disc list-inside text-muted-foreground space-y-1 ml-4">
                            <li><strong>CSV</strong> - файл с разделителем точка с запятой (;)</li>
                            <li><strong>ZIP</strong> - архив, содержащий CSV файл и папку images/ с изображениями</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-2">Структура CSV файла</h3>
                        <p class="text-muted-foreground mb-2">Первая строка должна содержать заголовки колонок:</p>
                        <div class="bg-muted/30 p-3 rounded-lg font-mono text-xs overflow-x-auto">
                            ID;Название;Slug;Описание;HTML контент;Раздел ID;ID изображения;Путь изображения;ID иконки;Путь иконки;Порядок;Активен
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-2">Описание полей</h3>
                        <div class="space-y-2">
                            <div class="border-l-4 border-blue-500 pl-3">
                                <p class="font-semibold text-red-600">ID <span class="text-xs font-normal text-muted-foreground">(обязательное для обновления)</span></p>
                                <p class="text-muted-foreground text-xs">Уникальный идентификатор услуги. Если указан и услуга существует - будет обновлена, иначе создана новая.</p>
                            </div>
                            <div class="border-l-4 border-red-500 pl-3">
                                <p class="font-semibold text-red-600">Название <span class="text-xs font-normal text-red-600">(обязательное)</span></p>
                                <p class="text-muted-foreground text-xs">Название услуги. Максимум 255 символов.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">Slug</p>
                                <p class="text-muted-foreground text-xs">URL-адрес услуги. Если не указан, будет сгенерирован автоматически из названия. Максимум 255 символов.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">Описание</p>
                                <p class="text-muted-foreground text-xs">Текстовое описание услуги. Может быть пустым.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">HTML контент</p>
                                <p class="text-muted-foreground text-xs">HTML-контент для отображения на странице услуги. Может быть пустым.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">Раздел ID</p>
                                <p class="text-muted-foreground text-xs">ID раздела (chapter), к которому относится услуга. Должен существовать в базе данных.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">ID изображения</p>
                                <p class="text-muted-foreground text-xs">ID изображения из медиа-библиотеки. Альтернатива пути к изображению.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">Путь изображения</p>
                                <p class="text-muted-foreground text-xs">Относительный путь к изображению в ZIP архиве (например: images/services/image.jpg). Работает только при импорте ZIP.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">ID иконки</p>
                                <p class="text-muted-foreground text-xs">ID иконки из медиа-библиотеки. Альтернатива пути к иконке.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">Путь иконки</p>
                                <p class="text-muted-foreground text-xs">Относительный путь к иконке в ZIP архиве (например: images/icons/icon.png). Работает только при импорте ZIP.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">Порядок</p>
                                <p class="text-muted-foreground text-xs">Число для сортировки услуг. Если не указано, будет установлено автоматически.</p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-3">
                                <p class="font-semibold">Активен</p>
                                <p class="text-muted-foreground text-xs">1, true или "да" - услуга активна, иначе - неактивна. По умолчанию: активна.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <h4 class="font-semibold text-yellow-800 mb-2">⚠️ Важные замечания:</h4>
                        <ul class="list-disc list-inside text-yellow-700 text-xs space-y-1">
                            <li>Поле <strong>"Название"</strong> является обязательным</li>
                            <li>Если указан <strong>ID</strong> и услуга существует - она будет обновлена</li>
                            <li>Если <strong>ID</strong> не указан или услуга не найдена - будет создана новая услуга</li>
                            <li>При импорте ZIP архива изображения должны находиться в папке <code>images/services/</code> или <code>images/icons/</code></li>
                            <li>Максимальный размер файла: 100 MB</li>
                            <li>Разделитель в CSV файле: точка с запятой (;)</li>
                        </ul>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <h4 class="font-semibold text-blue-800 mb-2">💡 Пример строки CSV:</h4>
                        <div class="bg-white p-2 rounded font-mono text-xs overflow-x-auto">
                            1;Название услуги;slug-uslugi;Описание услуги;;5;10;images/services/image.jpg;11;images/icons/icon.png;0;1
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6 pt-4 border-t border-border">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="importDocumentationRead"
                            class="w-4 h-4"
                        />
                        <span class="text-sm">Я ознакомлен с документацией</span>
                    </label>
                    <div class="flex gap-3">
                        <button
                            @click="showImportDocumentation = false"
                            class="px-4 py-2 border border-border rounded-lg hover:bg-muted/10 transition-colors"
                        >
                            Отмена
                        </button>
                        <label class="px-4 py-2 bg-accent/10 text-accent border border-accent/40 hover:bg-accent/20 rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center gap-2" :class="{ 'opacity-50 cursor-not-allowed': !importDocumentationRead }">
                            <input
                                type="file"
                                accept=".zip,.csv,.txt"
                                @change="handleImportFile"
                                class="hidden"
                                :disabled="!importDocumentationRead || loading || importing"
                            />
                            <span>📤</span>
                            <span>Загрузить файл</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { apiGet, apiDelete } from '../../../utils/api';
import Swal from 'sweetalert2';

export default {
    name: 'DecisionServices',
    setup() {
        const loading = ref(false);
        const exporting = ref(false);
        const importing = ref(false);
        const saving = ref(false);
        const error = ref(null);
        const services = ref([]);
        const showImportDocumentation = ref(false);
        const importDocumentationRead = ref(false);
        const draggedItem = ref(null);
        const draggedIndex = ref(null);
        const dragOverIndex = ref(null);

        const fetchServices = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await apiGet('/services');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки услуг');
                }
                const data = await response.json();
                const servicesList = data.data || [];
                // Сортируем услуги по полю order
                services.value = servicesList.sort((a, b) => {
                    const orderA = a.order ?? 999999;
                    const orderB = b.order ?? 999999;
                    return orderA - orderB;
                });
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки услуг';
                console.error('Error fetching services:', err);
            } finally {
                loading.value = false;
            }
        };

        // Drag and Drop handlers
        const onDragStart = (event, service, index) => {
            draggedItem.value = service;
            draggedIndex.value = index;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/html', event.target);
        };

        const onDragEnd = () => {
            draggedItem.value = null;
            draggedIndex.value = null;
            dragOverIndex.value = null;
        };

        const onDragOver = (event) => {
            event.dataTransfer.dropEffect = 'move';
        };

        const onItemDragOver = (event, index) => {
            if (draggedIndex.value !== null && draggedIndex.value !== index) {
                dragOverIndex.value = index;
            }
        };

        const onItemDragEnter = (index) => {
            if (draggedIndex.value !== null && draggedIndex.value !== index) {
                dragOverIndex.value = index;
            }
        };

        const onItemDragLeave = () => {
            // Не очищаем dragOverIndex здесь, чтобы сохранить визуальный индикатор
        };

        const onDrop = async (event) => {
            if (!draggedItem.value || draggedIndex.value === null) return;

            const dropIndex = dragOverIndex.value !== null ? dragOverIndex.value : draggedIndex.value;

            if (draggedIndex.value === dropIndex) {
                dragOverIndex.value = null;
                return;
            }

            // Перемещаем элемент в массиве
            const newServices = [...services.value];
            const [removed] = newServices.splice(draggedIndex.value, 1);
            newServices.splice(dropIndex, 0, removed);

            // Обновляем порядок
            newServices.forEach((service, index) => {
                service.order = index;
            });

            services.value = newServices;
            dragOverIndex.value = null;

            // Сохраняем новый порядок
            await saveOrder(newServices);
        };

        const saveOrder = async (servicesList) => {
            saving.value = true;
            try {
                const token = localStorage.getItem('token');
                const servicesData = servicesList.map((service, index) => ({
                    id: service.id,
                    order: index,
                }));

                const response = await fetch('/api/v1/services/update-order', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ services: servicesData }),
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Ошибка сохранения порядка');
                }

                await Swal.fire({
                    title: 'Порядок обновлен',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка сохранения порядка услуг',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
                // Восстанавливаем исходный порядок при ошибке
                await fetchServices();
            } finally {
                saving.value = false;
            }
        };

        const exportServices = async (format = 'csv') => {
            exporting.value = true;
            try {
                const token = localStorage.getItem('token');
                const url = `/api/v1/services/export${format === 'zip' ? '?format=zip' : ''}`;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': format === 'zip' ? 'application/zip' : 'text/csv',
                    },
                });

                if (!response.ok) {
                    throw new Error('Ошибка экспорта услуг');
                }

                const blob = await response.blob();
                const url_download = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url_download;
                const extension = format === 'zip' ? 'zip' : 'csv';
                a.download = `services_${new Date().toISOString().split('T')[0]}.${extension}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url_download);
                document.body.removeChild(a);

                await Swal.fire({
                    title: 'Экспорт завершен',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка экспорта услуг',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                exporting.value = false;
            }
        };

        const handleImportFile = async (event) => {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }

            // Закрываем модальное окно документации
            showImportDocumentation.value = false;
            importDocumentationRead.value = false;

            // Проверяем размер файла на клиенте (100MB = 100 * 1024 * 1024 байт)
            const maxSize = 100 * 1024 * 1024; // 100MB
            if (file.size > maxSize) {
                await Swal.fire({
                    title: 'Файл слишком большой',
                    html: `
                        <p>Размер файла: <strong>${(file.size / 1024 / 1024).toFixed(2)} MB</strong></p>
                        <p>Максимальный размер: <strong>100 MB</strong></p>
                        <p class="mt-4 text-sm text-gray-600">Если файл меньше 100MB, проверьте настройки сервера:</p>
                        <ul class="mt-2 text-sm text-left list-disc list-inside text-gray-600">
                            <li>PHP: upload_max_filesize, post_max_size</li>
                            <li>Веб-сервер: client_max_body_size (nginx) или LimitRequestBody (apache)</li>
                        </ul>
                    `,
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
                event.target.value = '';
                return;
            }

            importing.value = true;
            try {
                const formData = new FormData();
                formData.append('file', file);

                const token = localStorage.getItem('token');
                const response = await fetch('/api/v1/services/import', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    },
                    body: formData,
                });

                // Проверяем статус ответа
                if (response.status === 413) {
                    throw new Error('Файл слишком большой. Максимальный размер: 100MB. Проверьте настройки сервера (upload_max_filesize, post_max_size).');
                }

                let result;
                try {
                    result = await response.json();
                } catch (e) {
                    if (response.status === 413) {
                        throw new Error('Файл слишком большой (413 Content Too Large). Увеличьте лимиты загрузки на сервере.');
                    }
                    throw new Error('Ошибка обработки ответа сервера');
                }

                if (!response.ok) {
                    const errorMessage = result?.message || result?.errors?.[0] || 'Ошибка импорта услуг';
                    throw new Error(errorMessage);
                }

                // Формируем детальное сообщение об ошибках
                let errorsHtml = '';
                if (result.errors && result.errors.length > 0) {
                    errorsHtml = `
                        <details class="mt-4 text-left" open>
                            <summary class="cursor-pointer text-sm font-semibold mb-2">Ошибки и предупреждения (${result.errors.length})</summary>
                            <div class="mt-2 max-h-60 overflow-y-auto text-xs space-y-2">
                                ${result.errors.map(e => {
                                    const errorText = Array.isArray(e.errors) ? e.errors.join('<br>') : e.errors;
                                    const dataInfo = e.data ? `<br><small class="text-gray-500">Данные: ${JSON.stringify(e.data).substring(0, 100)}...</small>` : '';
                                    return `<div class="p-2 bg-red-50 border border-red-200 rounded">
                                        <strong>Строка ${e.row}:</strong><br>
                                        <span class="text-red-700">${errorText}</span>
                                        ${dataInfo}
                                    </div>`;
                                }).join('')}
                            </div>
                        </details>
                    `;
                }

                await Swal.fire({
                    title: result.success_count > 0 ? 'Импорт завершен' : 'Импорт завершен с ошибками',
                    html: `
                        <div class="text-left">
                            <p class="mb-2"><strong>Успешно обработано:</strong> ${result.success_count || 0}</p>
                            <p class="mb-4"><strong>Пропущено:</strong> ${result.skip_count || 0}</p>
                            ${errorsHtml}
                        </div>
                    `,
                    icon: result.success_count > 0 ? (result.errors && result.errors.length > 0 ? 'warning' : 'success') : 'error',
                    confirmButtonText: 'ОК',
                    width: '600px'
                });

                // Очищаем input
                event.target.value = '';
                await fetchServices();
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка импорта услуг',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                importing.value = false;
            }
        };

        const deleteService = async (service) => {
            const result = await Swal.fire({
                title: 'Удалить услугу?',
                text: `Вы уверены, что хотите удалить услугу "${service.name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
                confirmButtonColor: '#dc2626',
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                const response = await apiDelete(`/services/${service.id}`);
                if (!response.ok) {
                    throw new Error('Ошибка удаления услуги');
                }

                await Swal.fire({
                    title: 'Услуга удалена',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                await fetchServices();
            } catch (err) {
                await Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Ошибка удаления услуги',
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            }
        };

        onMounted(() => {
            fetchServices();
        });

        return {
            loading,
            exporting,
            importing,
            saving,
            error,
            services,
            showImportDocumentation,
            importDocumentationRead,
            draggedItem,
            draggedIndex,
            dragOverIndex,
            fetchServices,
            deleteService,
            exportServices,
            handleImportFile,
            onDragStart,
            onDragEnd,
            onDragOver,
            onItemDragOver,
            onItemDragEnter,
            onItemDragLeave,
            onDrop,
        };
    },
};
</script>

<style scoped>
.dragging {
    opacity: 0.5;
}

.drag-over {
    background-color: rgba(59, 130, 246, 0.1);
    border-top: 2px solid rgba(59, 130, 246, 0.5);
}
</style>

