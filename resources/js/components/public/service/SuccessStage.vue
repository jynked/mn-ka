<template>
    <div class="w-full max-w-md mx-auto text-center">
        <div class="flex justify-center mb-6">
            <svg width="116" height="90" viewBox="0 0 116 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.30078 42.0864L42.8008 79.0864L110.301 5.08636" stroke="#688E67" stroke-width="15.095" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h2 class="text-xl md:text-2xl font-semibold text-foreground">
            Спасибо за Ваше обращение, в ближайшее время мы свяжемся с Вами
        </h2>
    </div>
</template>

<script>
export default {
    name: 'ServiceSuccessStage',
    props: {
        service: {
            type: Object,
            required: true,
        },
    },
};
</script>

