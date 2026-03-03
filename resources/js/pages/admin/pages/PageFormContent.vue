<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Title -->
        <div>
            <label for="title" class="block text-sm font-medium text-foreground mb-2">
                Название страницы <span class="text-red-500">*</span>
            </label>
            <input
                id="title"
                v-model="localForm.title"
                type="text"
                required
                placeholder="Введите название страницы"
                class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                :class="{ 'border-red-500': errors.title }"
                @input="generateSlug"
            />
            <p v-if="errors.title" class="mt-1 text-sm text-red-500">{{ errors.title }}</p>
        </div>

        <!-- Slug -->
        <div>
            <label for="slug" class="block text-sm font-medium text-foreground mb-2">
                URL страницы <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="text-muted-foreground">/</span>
                <input
                    id="slug"
                    v-model="localForm.slug"
                    type="text"
                    required
                    placeholder="url-stranicy"
                    class="flex-1 h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors font-mono text-sm"
                    :class="{ 'border-red-500': errors.slug || slugConflict }"
                    @blur="checkSlug"
                />
                <button
                    v-if="slugConflict"
                    type="button"
                    @click="useSuggestedSlug"
                    class="px-4 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                >
                    Использовать: {{ suggestedSlug }}
                </button>
            </div>
            <p v-if="errors.slug" class="mt-1 text-sm text-red-500">{{ errors.slug }}</p>
            <p v-else-if="slugConflict" class="mt-1 text-sm text-yellow-600">
                Этот URL уже занят. Предложен альтернативный вариант.
            </p>
            <p v-else-if="slugChecked && !slugConflict" class="mt-1 text-sm text-green-600">
                ✓ URL доступен
            </p>
            <p v-else class="mt-1 text-xs text-muted-foreground">
                URL будет автоматически сгенерирован из названия, если не указан
            </p>
        </div>

        <!-- Content Editor -->
        <div>
            <label class="block text-sm font-medium text-foreground mb-2">
                Содержимое страницы
            </label>
            <div class="border border-border rounded-lg overflow-hidden">
                <!-- Toolbar -->
                <div class="bg-muted/30 border-b border-border p-2 flex items-center gap-2 flex-wrap">
                    <button
                        type="button"
                        @click="formatText('bold')"
                        class="p-2 hover:bg-muted rounded transition-colors"
                        title="Жирный"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        @click="formatText('italic')"
                        class="p-2 hover:bg-muted rounded transition-colors"
                        title="Курсив"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        @click="formatText('underline')"
                        class="p-2 hover:bg-muted rounded transition-colors"
                        title="Подчеркнутый"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h14M5 7h14" />
                        </svg>
                    </button>
                    <div class="w-px h-6 bg-border"></div>
                    <button
                        type="button"
                        @click="insertTag('<h2>', '</h2>')"
                        class="px-3 py-1 text-xs hover:bg-muted rounded transition-colors"
                        title="Заголовок 2"
                    >
                        H2
                    </button>
                    <button
                        type="button"
                        @click="insertTag('<h3>', '</h3>')"
                        class="px-3 py-1 text-xs hover:bg-muted rounded transition-colors"
                        title="Заголовок 3"
                    >
                        H3
                    </button>
                    <button
                        type="button"
                        @click="insertTag('<p>', '</p>')"
                        class="px-3 py-1 text-xs hover:bg-muted rounded transition-colors"
                        title="Параграф"
                    >
                        P
                    </button>
                    <button
                        type="button"
                        @click="insertTag('<ul><li>', '</li></ul>')"
                        class="px-3 py-1 text-xs hover:bg-muted rounded transition-colors"
                        title="Список"
                    >
                        UL
                    </button>
                    <button
                        type="button"
                        @click="insertTag('<ol><li>', '</li></ol>')"
                        class="px-3 py-1 text-xs hover:bg-muted rounded transition-colors"
                        title="Нумерованный список"
                    >
                        OL
                    </button>
                    <div class="w-px h-6 bg-border"></div>
                    <button
                        type="button"
                        @click="insertLink"
                        class="px-3 py-1 text-xs hover:bg-muted rounded transition-colors"
                        title="Ссылка"
                    >
                        A
                    </button>
                    <button
                        type="button"
                        @click="showPreview = !showPreview"
                        class="px-3 py-1 text-xs hover:bg-muted rounded transition-colors"
                        :class="{ 'bg-accent text-accent-foreground': showPreview }"
                    >
                        {{ showPreview ? 'Редактировать' : 'Предпросмотр' }}
                    </button>
                </div>
                <!-- Editor -->
                <div v-if="!showPreview" class="relative">
                    <textarea
                        ref="contentEditor"
                        v-model="localForm.content"
                        rows="15"
                        placeholder="Введите содержимое страницы. Используйте HTML разметку или кнопки форматирования выше."
                        class="w-full px-4 py-3 border-0 focus:outline-none focus:ring-0 resize-none font-mono text-sm"
                    ></textarea>
                </div>
                <!-- Preview -->
                <div v-else class="p-4 min-h-[300px] prose prose-sm max-w-none">
                    <div v-html="previewContent"></div>
                </div>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
                Поддерживается HTML разметка. Используйте кнопки форматирования или вводите HTML вручную.
            </p>
        </div>

        <!-- SEO Section -->
        <div class="border-t border-border pt-6">
            <h3 class="text-lg font-semibold text-foreground mb-4">SEO настройки</h3>
            
            <div class="space-y-4">
                <!-- SEO Title -->
                <div>
                    <label for="seo_title" class="block text-sm font-medium text-foreground mb-2">
                        SEO заголовок (meta title)
                    </label>
                    <input
                        id="seo_title"
                        v-model="localForm.seo_title"
                        type="text"
                        placeholder="Заголовок для поисковых систем"
                        maxlength="255"
                        class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                    />
                    <p class="mt-1 text-xs text-muted-foreground">
                        Рекомендуемая длина: 50-60 символов. Осталось: {{ 255 - (localForm.seo_title?.length || 0) }}
                    </p>
                </div>

                <!-- SEO Description -->
                <div>
                    <label for="seo_description" class="block text-sm font-medium text-foreground mb-2">
                        SEO описание (meta description)
                    </label>
                    <textarea
                        id="seo_description"
                        v-model="localForm.seo_description"
                        rows="3"
                        placeholder="Краткое описание страницы для поисковых систем"
                        maxlength="500"
                        class="w-full px-4 py-3 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent resize-none"
                    ></textarea>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Рекомендуемая длина: 150-160 символов. Осталось: {{ 500 - (localForm.seo_description?.length || 0) }}
                    </p>
                </div>

                <!-- SEO Keywords -->
                <div>
                    <label for="seo_keywords" class="block text-sm font-medium text-foreground mb-2">
                        SEO ключевые слова (meta keywords)
                    </label>
                    <input
                        id="seo_keywords"
                        v-model="localForm.seo_keywords"
                        type="text"
                        placeholder="ключевое слово 1, ключевое слово 2, ключевое слово 3"
                        maxlength="255"
                        class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                    />
                    <p class="mt-1 text-xs text-muted-foreground">
                        Укажите ключевые слова через запятую
                    </p>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="border-t border-border pt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="localForm.is_active"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Активна</span>
                    </label>
                </div>
                <div>
                    <label for="order" class="block text-sm font-medium text-foreground mb-2">
                        Порядок сортировки
                    </label>
                    <input
                        id="order"
                        v-model.number="localForm.order"
                        type="number"
                        min="0"
                        class="w-full h-12 px-4 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] focus:border-transparent transition-colors"
                    />
                </div>
            </div>
        </div>

        <!-- Error message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm text-red-600">{{ error }}</p>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-4 pt-4 border-t border-border">
            <button
                type="button"
                @click="$emit('cancel')"
                class="px-6 py-3 border border-border rounded-lg hover:bg-muted transition-colors font-medium"
                :disabled="saving"
            >
                Отмена
            </button>
            <button
                type="submit"
                :disabled="saving || slugConflict"
                class="px-6 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
            >
                <span v-if="saving">Сохранение...</span>
                <span v-else>{{ page ? 'Сохранить' : 'Создать' }}</span>
            </button>
        </div>
    </form>
</template>

<script>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';

export default {
    name: 'PageFormContent',
    props: {
        page: {
            type: Object,
            default: null,
        },
        saving: {
            type: Boolean,
            default: false,
        },
        error: {
            type: String,
            default: null,
        },
    },
    emits: ['submit', 'cancel'],
    setup(props, { emit }) {
        const errors = ref({});
        const slugConflict = ref(false);
        const slugChecked = ref(false);
        const suggestedSlug = ref('');
        const showPreview = ref(false);
        const contentEditor = ref(null);

        const localForm = ref({
            title: '',
            slug: '',
            content: '',
            seo_title: '',
            seo_description: '',
            seo_keywords: '',
            is_active: true,
            order: 0,
        });

        // Computed для preview контента
        const previewContent = computed(() => {
            if (localForm.value.content && localForm.value.content.trim()) {
                return localForm.value.content;
            }
            return '<p class="text-muted-foreground">Нет содержимого</p>';
        });

        // Инициализация формы из props
        watch(() => props.page, (newPage) => {
            if (newPage) {
                localForm.value = {
                    title: newPage.title || '',
                    slug: newPage.slug || '',
                    content: newPage.content || '',
                    seo_title: newPage.seo_title || '',
                    seo_description: newPage.seo_description || '',
                    seo_keywords: newPage.seo_keywords || '',
                    is_active: newPage.is_active !== undefined ? newPage.is_active : true,
                    order: newPage.order || 0,
                };
            }
        }, { immediate: true });

        const generateSlug = () => {
            if (!localForm.value.slug || localForm.value.slug === props.page?.slug) {
                const title = localForm.value.title || '';
                localForm.value.slug = title
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                slugChecked.value = false;
                slugConflict.value = false;
            }
        };

        const checkSlug = async () => {
            if (!localForm.value.slug) return;
            
            slugChecked.value = false;
            slugConflict.value = false;

            try {
                const response = await axios.post('/api/v1/pages/check-slug', {
                    slug: localForm.value.slug,
                    exclude_id: props.page?.id,
                });

                if (response.data.available) {
                    slugConflict.value = false;
                    slugChecked.value = true;
                } else {
                    slugConflict.value = true;
                    suggestedSlug.value = response.data.suggested_slug || localForm.value.slug + '-1';
                }
            } catch (err) {
                console.error('Error checking slug:', err);
            }
        };

        const useSuggestedSlug = () => {
            localForm.value.slug = suggestedSlug.value;
            slugConflict.value = false;
            slugChecked.value = true;
        };

        const formatText = (command) => {
            if (!contentEditor.value) return;
            
            const textarea = contentEditor.value;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = localForm.value.content.substring(start, end);
            
            let replacement = '';
            if (command === 'bold') {
                replacement = `<strong>${selectedText || 'текст'}</strong>`;
            } else if (command === 'italic') {
                replacement = `<em>${selectedText || 'текст'}</em>`;
            } else if (command === 'underline') {
                replacement = `<u>${selectedText || 'текст'}</u>`;
            }
            
            localForm.value.content = 
                localForm.value.content.substring(0, start) +
                replacement +
                localForm.value.content.substring(end);
            
            nextTick(() => {
                textarea.focus();
                textarea.setSelectionRange(start + replacement.length, start + replacement.length);
            });
        };

        const insertTag = (openTag, closeTag) => {
            if (!contentEditor.value) return;
            
            const textarea = contentEditor.value;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = localForm.value.content.substring(start, end) || 'текст';
            
            const replacement = openTag + selectedText + closeTag;
            
            localForm.value.content = 
                localForm.value.content.substring(0, start) +
                replacement +
                localForm.value.content.substring(end);
            
            nextTick(() => {
                textarea.focus();
                const newPos = start + replacement.length;
                textarea.setSelectionRange(newPos, newPos);
            });
        };

        const insertLink = () => {
            insertTag('<a href="">', '</a>');
        };

        const handleSubmit = () => {
            if (slugConflict.value) {
                errors.value.slug = 'Исправьте конфликт URL перед сохранением';
                return;
            }

            emit('submit', localForm.value);
        };

        return {
            localForm,
            errors,
            slugConflict,
            slugChecked,
            suggestedSlug,
            showPreview,
            contentEditor,
            previewContent,
            generateSlug,
            checkSlug,
            useSuggestedSlug,
            formatText,
            insertTag,
            insertLink,
            handleSubmit,
        };
    },
};
</script>

<style scoped>
.prose {
    color: rgb(var(--foreground));
}

.prose h2 {
    font-size: 1.5em;
    font-weight: 600;
    margin-top: 1em;
    margin-bottom: 0.5em;
}

.prose h3 {
    font-size: 1.25em;
    font-weight: 600;
    margin-top: 1em;
    margin-bottom: 0.5em;
}

.prose p {
    margin-bottom: 1em;
}

.prose ul, .prose ol {
    margin-left: 1.5em;
    margin-bottom: 1em;
}

.prose li {
    margin-bottom: 0.5em;
}
</style>

