<template>
    <div class="lazy-image-container" :class="containerClass">
        <!-- Placeholder/Skeleton пока изображение не загружено -->
        <div
            v-if="!loaded && !error"
            class="absolute inset-0 bg-muted animate-pulse rounded"
            :class="placeholderClass"
        ></div>
        
        <!-- Изображение -->
        <img
            v-if="shouldLoad"
            ref="imageRef"
            :src="src"
            :alt="alt"
            :class="imageClass"
            @load="handleLoad"
            @error="handleError"
            loading="lazy"
            :decoding="decoding"
        />
        
        <!-- Ошибка загрузки -->
        <div
            v-if="error"
            class="absolute inset-0 flex items-center justify-center bg-muted/50 rounded"
            :class="errorClass"
        >
            <span class="text-muted-foreground text-sm">Ошибка загрузки</span>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';

export default {
    name: 'LazyImage',
    props: {
        src: {
            type: String,
            required: true,
        },
        alt: {
            type: String,
            default: '',
        },
        imageClass: {
            type: String,
            default: 'w-full h-full object-cover',
        },
        containerClass: {
            type: String,
            default: 'relative w-full',
        },
        placeholderClass: {
            type: String,
            default: '',
        },
        errorClass: {
            type: String,
            default: '',
        },
        decoding: {
            type: String,
            default: 'async',
        },
        // Загружать сразу или использовать Intersection Observer
        eager: {
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        const loaded = ref(false);
        const error = ref(false);
        const shouldLoad = ref(props.eager);
        const observer = ref(null);
        const imageRef = ref(null);

        const handleLoad = () => {
            loaded.value = true;
            error.value = false;
        };

        const handleError = () => {
            error.value = true;
            loaded.value = false;
        };

        // Используем Intersection Observer для lazy loading
        onMounted(() => {
            if (props.eager) {
                shouldLoad.value = true;
                return;
            }

            // Проверяем поддержку Intersection Observer
            if ('IntersectionObserver' in window) {
                observer.value = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                shouldLoad.value = true;
                                observer.value.disconnect();
                            }
                        });
                    },
                    {
                        rootMargin: '50px', // Начинаем загрузку за 50px до появления в viewport
                    }
                );

                // Наблюдаем за контейнером
                if (imageRef.value?.parentElement) {
                    observer.value.observe(imageRef.value.parentElement);
                }
            } else {
                // Fallback для старых браузеров - загружаем сразу
                shouldLoad.value = true;
            }
        });

        onUnmounted(() => {
            if (observer.value) {
                observer.value.disconnect();
            }
        });

        return {
            loaded,
            error,
            shouldLoad,
            imageRef,
            handleLoad,
            handleError,
        };
    },
};
</script>

