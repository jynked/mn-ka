<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-300 ease-out"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div 
            v-if="isOpen"
            class="fixed inset-0 z-50 flex items-start justify-center bg-black/60 backdrop-blur-sm pt-20 px-4"
            @click.self="$emit('close')"
        >
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-300 ease-out"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-4"
            >
                <div 
                    v-if="isOpen"
                    class="bg-white rounded-lg shadow-2xl w-full max-w-2xl p-6"
                    @click.stop
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-black">Поиск по сайту</h3>
                        <button
                            @click="$emit('close')"
                            type="button"
                            class="text-gray-500 hover:text-black transition-colors"
                        >
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                    <div class="relative">
                        <input
                            ref="searchInput"
                            v-model="searchQuery"
                            @keypress.enter="handleSearch"
                            type="text"
                            placeholder="Введите запрос для поиска..."
                            class="w-full h-12 px-4 pr-12 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-[#6C7B6D] focus:border-transparent"
                            autofocus
                        >
                        <button
                            @click="handleSearch"
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-black transition-colors"
                        >
                            <svg 
                                width="20" 
                                height="20"
                                viewBox="0 0 20 20" 
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path 
                                    fill-rule="evenodd" 
                                    clip-rule="evenodd"
                                    d="M12.5885 13.8064C11.0409 15.0431 9.07854 15.6401 7.10447 15.475C5.13041 15.3098 3.2945 14.3951 1.97381 12.9185C0.653118 11.4419 -0.0520973 9.5157 0.00300163 7.53539C0.0581006 5.55508 0.869331 3.67104 2.27008 2.27021C3.67083 0.869381 5.55476 0.0581039 7.53496 0.00300181C9.51515 -0.0521003 11.4413 0.653156 12.9178 1.97392C14.3942 3.29469 15.309 5.1307 15.4741 7.10488C15.6392 9.07906 15.0422 11.0415 13.8057 12.5893L19.7259 18.5087C19.8105 18.5875 19.8784 18.6826 19.9254 18.7883C19.9725 18.8939 19.9978 19.0079 19.9999 19.1236C20.0019 19.2392 19.9806 19.3541 19.9373 19.4613C19.894 19.5686 19.8295 19.666 19.7478 19.7478C19.666 19.8295 19.5686 19.894 19.4614 19.9373C19.3541 19.9806 19.2393 20.0019 19.1236 19.9999C19.008 19.9978 18.894 19.9725 18.7883 19.9254C18.6827 19.8784 18.5876 19.8105 18.5088 19.7259L12.5885 13.8064ZM3.48769 12.0128C2.64494 11.1699 2.07095 10.0961 1.83828 8.92709C1.6056 7.75806 1.72467 6.54629 2.18045 5.44492C2.63622 4.34355 3.40824 3.40202 4.39894 2.73932C5.38963 2.07661 6.55454 1.72248 7.74643 1.72168C8.93832 1.72088 10.1037 2.07344 11.0953 2.73481C12.0869 3.39618 12.8602 4.33667 13.3174 5.43743C13.7747 6.53818 13.8954 7.74979 13.6643 8.91913C13.4332 10.0885 12.8606 11.1631 12.019 12.0071L12.0133 12.0128L12.0075 12.0174C10.8766 13.1457 9.34413 13.779 7.74666 13.7782C6.14918 13.7773 4.61737 13.1424 3.48769 12.0128Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<script>
import { ref, computed, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';

export default {
    name: 'SearchModal',
    props: {
        isOpen: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close', 'search'],
    setup(props, { emit }) {
        const router = useRouter();
        const searchQuery = ref('');
        const searchInput = ref(null);

        const handleSearch = () => {
            if (searchQuery.value.trim()) {
                router.push({
                    name: 'search',
                    query: { q: searchQuery.value.trim() }
                });
                searchQuery.value = '';
                emit('close');
            }
        };

        watch(() => props.isOpen, (newVal) => {
            if (newVal) {
                nextTick(() => {
                    if (searchInput.value) {
                        searchInput.value.focus();
                    }
                });
            } else {
                searchQuery.value = '';
            }
        });

        return {
            isOpen: computed(() => props.isOpen),
            searchQuery,
            searchInput,
            handleSearch,
        };
    },
};
</script>

