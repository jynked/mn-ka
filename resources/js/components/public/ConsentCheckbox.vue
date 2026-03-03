<template>
    <div class="space-y-2">
        <div class="flex items-start gap-3">
            <input
                :id="inputId"
                type="checkbox"
                v-model="checked"
                @change="handleChange"
                class="mt-1 w-5 h-5 text-[#688E67] border-gray-300 rounded focus:ring-2 focus:ring-[#688E67] focus:ring-offset-0 cursor-pointer flex-shrink-0"
                :class="{ 'border-red-500': hasError }"
            />
            <label :for="inputId" class="text-sm text-foreground leading-relaxed cursor-pointer select-none flex-1">
                Я согласен на 
                <a href="/approval" target="_blank" class="text-[#688E67] underline hover:text-[#5a7a5a] transition-colors" @click.stop>
                    обработку персональных данных
                </a>
                и принимаю условия 
                <a href="/privacy-policy" target="_blank" class="text-[#688E67] underline hover:text-[#5a7a5a] transition-colors" @click.stop>
                    Политики конфиденциальности
                </a>.
            </label>
        </div>
        
        <div v-if="showAdditionalLink" class="pl-8">
            <a href="/personal-data" target="_blank" class="text-xs text-muted-foreground hover:text-[#688E67] transition-colors underline">
                Подробнее об обработке ПДн
            </a>
        </div>
        
        <p v-if="hasError" class="text-sm text-red-500 pl-8 mt-1">
            Нужно согласие на обработку персональных данных
        </p>
    </div>
</template>

<script>
import { ref, watch } from 'vue';

export default {
    name: 'ConsentCheckbox',
    props: {
        modelValue: {
            type: Boolean,
            default: false,
        },
        error: {
            type: Boolean,
            default: false,
        },
        showAdditionalLink: {
            type: Boolean,
            default: true,
        },
        inputId: {
            type: String,
            default: () => `consent-checkbox-${Math.random().toString(36).substr(2, 9)}`,
        },
    },
    emits: ['update:modelValue', 'update:error'],
    setup(props, { emit }) {
        const checked = ref(props.modelValue);
        const hasError = ref(props.error);

        watch(() => props.modelValue, (newValue) => {
            checked.value = newValue;
        });

        watch(() => props.error, (newValue) => {
            hasError.value = newValue;
        });

        const handleChange = () => {
            emit('update:modelValue', checked.value);
            if (checked.value && hasError.value) {
                hasError.value = false;
                emit('update:error', false);
            }
        };

        return {
            checked,
            hasError,
            handleChange,
        };
    },
};
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>

