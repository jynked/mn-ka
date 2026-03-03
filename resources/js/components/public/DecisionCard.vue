<template>
    <router-link :to="getProductUrl(decision)" class="block h-full" :aria-label="`Перейти к ${decision.name}`">
        <div class="flex flex-col justify-between min-h-[96px] h-full p-3 bg-[#F4F4F4] rounded-lg transition-all duration-200 hover:shadow-md hover:bg-gray-50">
            <div class="name text-sm sm:text-base font-normal text-gray-900 leading-[1.4] mb-2 line-clamp-2 overflow-hidden">
                {{ decision.name }}
            </div>
            <!-- Короткое описание, если есть -->
            <div v-if="decision.short_description" class="text-xs text-gray-600 line-clamp-1 mb-2">
                {{ decision.short_description }}
            </div>
            <div class="flex items-center justify-end gap-2 mt-auto flex-shrink-0 pt-1">
                <span class="text-xs sm:text-sm font-normal text-[#688E67] whitespace-nowrap">подробнее</span>
                <svg width="16" height="8" viewBox="0 0 16 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="flex-shrink-0">
                    <path d="M15.3536 4.35355C15.5488 4.15829 15.5488 3.84171 15.3536 3.64645L12.1716 0.464465C11.9763 0.269203 11.6597 0.269203 11.4645 0.464465C11.2692 0.659727 11.2692 0.97631 11.4645 1.17157L14.2929 4L11.4645 6.82843C11.2692 7.02369 11.2692 7.34027 11.4645 7.53553C11.6597 7.7308 11.9763 7.7308 12.1716 7.53553L15.3536 4.35355ZM0 4L4.37114e-08 4.5L15 4.5L15 4L15 3.5L-4.37114e-08 3.5L0 4Z" fill="#688E67"/>
                </svg>
            </div>
        </div>
    </router-link>
</template>

<script>
export default {
    name: 'DecisionCard',
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
            // Если slug начинается со слэша, убираем его
            const productSlug = decision.slug ? decision.slug.replace(/^\/+/, '') : '';
            
            if (!productSlug) {
                return `/${this.slug}`;
            }
            
            // Формируем URL: /products/light-industrial-5
            return `/${this.slug}/${productSlug}`;
        },
    },
};
</script>

