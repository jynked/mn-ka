<template>
    <div class="relative w-full">
        <FsLightbox
            v-if="images.length > 0"
            :toggler="lightboxToggler"
            :sources="lightboxSources"
            :slide="currentSlide"
            :type="'image'"
        />
        
        <div class="relative w-full">
            <!-- Все изображения в сетке для просмотра -->
            <div 
                v-if="images.length === 1"
                class="relative w-full aspect-[100/51] bg-muted/30 rounded-xl overflow-hidden cursor-pointer group"
                @click="openLightbox(0)"
            >
                <LazyImage
                    :src="images[0].webp || images[0].url"
                    :alt="caseName"
                    image-class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    container-class="relative w-full h-full"
                    :eager="true"
                />
            </div>

            <!-- Если несколько изображений, показываем сетку -->
            <div v-else class="space-y-3 md:space-y-4">
                <!-- Основное изображение (первое) -->
                <div 
                    class="relative w-full aspect-[100/51] bg-muted/30 rounded-xl overflow-hidden cursor-pointer group"
                    @click="openLightbox(0)"
                >
                    <LazyImage
                        :src="images[0].webp || images[0].url"
                        :alt="caseName"
                        image-class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                        container-class="relative w-full h-full"
                        :eager="true"
                    />
                    
                    <!-- Счетчик изображений -->
                    <div class="absolute bottom-4 right-4 bg-black/70 text-white px-3 py-1 rounded-full text-sm">
                        {{ images.length }} фото
                    </div>
                </div>

                <!-- Миниатюры остальных изображений -->
                <div 
                    v-if="images.length > 1"
                    class="grid grid-cols-2 gap-3 md:gap-4"
                >
                    <div
                        v-for="(image, index) in images.slice(1, images.length > 3 ? 3 : images.length)"
                        :key="index"
                        class="relative aspect-[50/33] bg-muted/30 rounded-lg overflow-hidden cursor-pointer group"
                        @click="openLightbox(index + 1)"
                    >
                        <LazyImage
                            :src="image.webp || image.url"
                            :alt="`${caseName} - изображение ${index + 2}`"
                            image-class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                            container-class="relative w-full h-full"
                            :eager="false"
                        />
                        <!-- Показываем "ещё" на последней миниатюре, если есть больше изображений -->
                        <div 
                            v-if="index === 1 && images.length > 3"
                            class="absolute inset-0 flex items-center justify-center bg-black/60 text-white font-medium text-sm"
                        >
                            +{{ images.length - 3 }} ещё
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import FsLightbox from 'fslightbox-vue';
import LazyImage from './LazyImage.vue';

export default {
    name: 'CaseGallery',
    components: {
        FsLightbox,
        LazyImage,
    },
    props: {
        images: {
            type: Array,
            default: () => [],
        },
        caseName: {
            type: String,
            default: 'Кейс',
        },
    },
    setup(props) {
        const lightboxToggler = ref(false);
        const currentSlide = ref(1);

        const lightboxSources = computed(() => {
            return props.images.map(img => img.webp || img.url || '');
        });

        const openLightbox = (slideIndex) => {
            if (lightboxSources.value.length > 0) {
                currentSlide.value = slideIndex + 1;
                lightboxToggler.value = !lightboxToggler.value;
            }
        };

        return {
            lightboxToggler,
            currentSlide,
            lightboxSources,
            openLightbox,
        };
    },
};
</script>

