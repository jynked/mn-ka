<template>
    <router-link :to="getProductUrl(decision)" class="block h-full" :aria-label="`Перейти к ${decision.name}`">
        <div class="relative w-full h-[100%] bg-[#F4F6FC] rounded-[16px] overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-[1.02] flex flex-col p-4">
            <!-- Верхний ряд: заголовок -->
            <div class="flex-1 flex items-start mb-2">
                <h1 class="font-montserrat font-medium text-lg leading-[22px] text-black">
                    {{ decision.name }}
                </h1>
            </div>
            
            <!-- Короткое описание, если есть -->
            <div v-if="decision.short_description" class="text-xs text-gray-600 line-clamp-1 mb-2">
                {{ decision.short_description }}
            </div>
            
            <!-- Нижний ряд: изображение или иконка справа -->
            <div class="flex justify-end">
                <!-- Используем card_preview_image, если есть, иначе icon -->
                <div v-if="(decision.card_preview_image && decision.card_preview_image.url) || (decision.icon && decision.icon.url)" class="flex-shrink-0 w-[80px] h-[60px] flex items-center justify-center">
                    <img 
                        :src="(decision.card_preview_image && decision.card_preview_image.url) ? decision.card_preview_image.url : decision.icon.url" 
                        :alt="decision.name"
                        class="max-w-full max-h-full object-contain"
                        loading="lazy"
                    />
                </div>
            </div>
        </div>
    </router-link>
</template>

<script>
export default {
    name: 'ProductCard',
    props: {
        decision: {
            type: Object,
            required: true,
        },
        slug: {
            type: String,
            default: 'products',
        },
    },
    methods: {
        getProductUrl(decision) {
            const productSlug = decision.slug ? decision.slug.replace(/^\/+/, '') : '';
            
            if (!productSlug) {
                return `/${this.slug}`;
            }
            
            return `/${this.slug}/${productSlug}`;
        },
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap');

.font-montserrat {
    font-family: 'Montserrat', sans-serif;
}
</style>

