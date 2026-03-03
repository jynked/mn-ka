<template>
    <div class="w-full max-w-4xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
            <div
                v-for="service in services"
                :key="service.id"
                :class="[
                    'flex items-center justify-between p-3 md:p-4 rounded-lg border transition-all cursor-pointer',
                    service.active
                        ? 'bg-[#688E67] border-[#688E67] text-white'
                        : 'bg-white border-[#688E67] text-foreground hover:bg-muted/5'
                ]"
                @click="toggleService(service)"
            >
                <div class="text-sm md:text-base font-normal flex-1">
                    {{ service.name }}
                </div>
                <div class="ml-4 flex-shrink-0">
                    <input
                        type="checkbox"
                        :checked="service.active"
                        @change="toggleService(service)"
                        @click.stop
                        class="w-6 h-6 rounded border-2 border-white cursor-pointer"
                        :class="service.active ? 'bg-white' : 'bg-transparent'"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch, computed, nextTick } from 'vue';

export default {
    name: 'OptionsStage',
    props: {
        product: {
            type: Object,
            required: true,
        },
    },
    emits: ['update-services'],
    setup(props, { emit }) {
        const services = ref([]);

        // Инициализируем услуги при монтировании
        const initializeServices = () => {
            if (props.product?.services && Array.isArray(props.product.services)) {
                services.value = props.product.services.map(service => ({
                    id: service.id,
                    name: service.name || '',
                    slug: service.slug || '',
                    active: true, // По умолчанию все активны
                }));
                emit('update-services', services.value);
            }
        };

        // Инициализация при монтировании
        initializeServices();

        const toggleService = (service) => {
            service.active = !service.active;
            // Используем nextTick для оптимизации множественных обновлений
            nextTick(() => {
                emit('update-services', services.value);
            });
        };

        // Оптимизированный watch - только проверка изменения services массива
        watch(() => props.product?.services?.length, (newLength, oldLength) => {
            if (newLength !== oldLength || (newLength && newLength !== services.value.length)) {
                initializeServices();
            }
        });

        // Отслеживаем изменение ID продукта (при навигации)
        watch(() => props.product?.id, () => {
            initializeServices();
        });

        return {
            services,
            toggleService,
        };
    },
};
</script>

<style scoped>
input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    position: relative;
}

input[type="checkbox"]:checked::after {
    content: "✓";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #688E67;
    font-weight: bold;
    font-size: 14px;
}
</style>

