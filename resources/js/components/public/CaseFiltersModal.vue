<template>
    <div 
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        @click.self="$emit('close')"
    >
        <div class="bg-card rounded-lg p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-foreground">Фильтры</h3>
                <button
                    @click="$emit('close')"
                    class="text-muted-foreground hover:text-foreground transition-colors"
                >
                    <svg 
                        width="24" 
                        height="24" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path 
                            d="M18 6L6 18M6 6L18 18" 
                            stroke="currentColor" 
                            stroke-width="2" 
                            stroke-linecap="round" 
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <p class="text-muted-foreground">
                    Функциональность фильтров будет реализована позже
                </p>
            </div>

            <div class="mt-6 flex gap-3">
                <button
                    @click="$emit('close')"
                    class="flex-1 px-4 py-2 border border-border rounded-lg hover:bg-muted/50 transition-colors"
                >
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CaseFiltersModal',
    props: {
        cases: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['close'],
};
</script>



