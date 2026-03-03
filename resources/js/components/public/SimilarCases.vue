<template>
    <div v-if="similarCases.length > 0" class="w-full px-3 sm:px-4 md:px-5">
        <div class="w-full max-w-[1200px] mx-auto mt-6 md:mt-8">
            <h2 class="text-2xl md:text-3xl font-bold text-black mb-6 md:mb-8">
                Похожие кейсы
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                <CaseCard
                    v-for="caseItem in similarCases"
                    :key="caseItem.id"
                    :case-item="caseItem"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import CaseCard from './CaseCard.vue';

export default {
    name: 'SimilarCases',
    components: {
        CaseCard,
    },
    props: {
        chapterId: {
            type: Number,
            default: null,
        },
        excludeCaseId: {
            type: Number,
            default: null,
        },
        limit: {
            type: Number,
            default: 2,
        },
    },
    setup(props) {
        const similarCases = ref([]);
        const loading = ref(false);

        const fetchSimilarCases = async () => {
            loading.value = true;
            try {
                const params = new URLSearchParams();
                if (props.chapterId) {
                    params.append('chapter_id', props.chapterId);
                }
                if (props.excludeCaseId) {
                    params.append('offerNot', props.excludeCaseId);
                }

                const response = await fetch(`/api/public/cases?${params.toString()}`);
                if (response.ok) {
                    const data = await response.json();
                    const cases = data.data || [];
                    // Берем первые N кейсов, исключая текущий
                    similarCases.value = cases
                        .filter(c => c.id !== props.excludeCaseId)
                        .slice(0, props.limit);
                }
            } catch (error) {
                console.error('Error fetching similar cases:', error);
            } finally {
                loading.value = false;
            }
        };

        onMounted(() => {
            fetchSimilarCases();
        });

        return {
            similarCases,
            loading,
        };
    },
};
</script>

