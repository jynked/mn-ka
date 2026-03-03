<template>
    <div class="product-skeleton space-y-6">
        <!-- Breadcrumbs skeleton -->
        <div class="hidden md:block mt-4">
            <div class="flex items-center gap-2">
                <div class="h-4 w-16 bg-muted rounded animate-pulse"></div>
                <div class="h-4 w-1 bg-muted"></div>
                <div class="h-4 w-20 bg-muted rounded animate-pulse"></div>
                <div class="h-4 w-1 bg-muted"></div>
                <div class="h-4 w-32 bg-muted rounded animate-pulse"></div>
            </div>
        </div>

        <!-- Header and image skeleton -->
        <div class="mt-6 md:mt-8 grid md:grid-cols-2 gap-6 md:gap-8">
            <div class="space-y-4">
                <div class="h-8 md:h-10 w-3/4 bg-muted rounded animate-pulse"></div>
                <div class="h-4 w-full bg-muted rounded animate-pulse"></div>
                <div class="h-4 w-5/6 bg-muted rounded animate-pulse"></div>
            </div>
            <div class="relative">
                <div class="w-full aspect-square bg-muted rounded-lg animate-pulse"></div>
            </div>
        </div>

        <!-- Services section skeleton -->
        <div class="mt-8 py-5 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-2">
                    <div class="h-5 w-64 bg-muted rounded animate-pulse"></div>
                    <div class="h-5 w-48 bg-muted rounded animate-pulse"></div>
                </div>
            </div>

            <!-- Services grid skeleton -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                <div
                    v-for="i in 6"
                    :key="i"
                    class="h-16 bg-muted rounded-lg animate-pulse"
                ></div>
            </div>

            <!-- Button skeleton -->
            <div class="flex justify-center mt-6">
                <div class="h-12 w-48 bg-muted rounded-lg animate-pulse"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProductSkeleton',
};
</script>

