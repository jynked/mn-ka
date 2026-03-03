<template>
    <div class="relative w-full" v-if="images.length > 0">
        <FsLightbox
            :toggler="lightboxToggler"
            :sources="lightboxSources"
            :slide="currentSlide"
            :type="'image'"
        />
        
        <div class="relative w-full case-carousel">
            <div 
                class="relative w-full aspect-[100/51] bg-gray-100 rounded-xl overflow-hidden cursor-pointer group"
                @click="openLightbox(currentIndex)"
            >
                <LazyImage
                    :src="currentImage.webp || currentImage.url"
                    :alt="`${caseName} - изображение ${currentIndex + 1}`"
                    image-class="w-full h-full object-cover"
                    container-class="relative w-full h-full"
                    :eager="true"
                />
                
                <!-- Стрелки навигации -->
                <button
                    v-if="images.length > 1"
                    @click.stop="previousSlide"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-[#688E67] border-2 border-white text-white rounded-full flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity z-10"
                    :disabled="currentIndex === 0"
                    :class="{ 'opacity-50 cursor-not-allowed': currentIndex === 0 }"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                
                <button
                    v-if="images.length > 1"
                    @click.stop="nextSlide"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-[#688E67] border-2 border-white text-white rounded-full flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity z-10"
                    :disabled="currentIndex === images.length - 1"
                    :class="{ 'opacity-50 cursor-not-allowed': currentIndex === images.length - 1 }"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <!-- Точки пагинации -->
            <div 
                v-if="images.length > 1"
                class="flex justify-center gap-2 mt-4"
            >
                <button
                    v-for="(image, index) in images"
                    :key="index"
                    @click="goToSlide(index)"
                    :class="[
                        'w-2 h-2 rounded-full transition-all',
                        index === currentIndex 
                            ? 'bg-[#688E67] w-8' 
                            : 'bg-gray-300 hover:bg-gray-400'
                    ]"
                ></button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import FsLightbox from 'fslightbox-vue';
import LazyImage from './LazyImage.vue';

export default {
    name: 'CaseImageCarousel',
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
        const currentIndex = ref(0);

        const lightboxSources = computed(() => {
            return props.images.map(img => img.webp || img.url || '');
        });

        const currentImage = computed(() => {
            return props.images[currentIndex.value] || props.images[0] || {};
        });

        const openLightbox = (slideIndex) => {
            if (lightboxSources.value.length > 0) {
                currentSlide.value = slideIndex + 1;
                lightboxToggler.value = !lightboxToggler.value;
            }
        };

        const nextSlide = () => {
            if (currentIndex.value < props.images.length - 1) {
                currentIndex.value++;
            }
        };

        const previousSlide = () => {
            if (currentIndex.value > 0) {
                currentIndex.value--;
            }
        };

        const goToSlide = (index) => {
            currentIndex.value = index;
        };

        return {
            lightboxToggler,
            currentSlide,
            currentIndex,
            lightboxSources,
            currentImage,
            openLightbox,
            nextSlide,
            previousSlide,
            goToSlide,
        };
    },
};
</script>

<style scoped>
.case-carousel {
    position: relative;
}
</style>

