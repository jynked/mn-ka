/**
 * Composable для debounce функций
 */
import { ref, onUnmounted } from 'vue';

export function useDebounce(func, delay = 300) {
    const timeoutId = ref(null);

    const debounced = (...args) => {
        if (timeoutId.value !== null) {
            clearTimeout(timeoutId.value);
        }

        timeoutId.value = setTimeout(() => {
            func(...args);
            timeoutId.value = null;
        }, delay);
    };

    const cancel = () => {
        if (timeoutId.value !== null) {
            clearTimeout(timeoutId.value);
            timeoutId.value = null;
        }
    };

    // Очищаем таймер при размонтировании компонента
    onUnmounted(() => {
        cancel();
    });

    return {
        debounced,
        cancel,
    };
}

