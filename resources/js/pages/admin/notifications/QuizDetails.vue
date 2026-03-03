<template>
    <div class="quiz-details-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Детали прохождения квиза</h1>
                <p class="text-muted-foreground mt-1">Подробная информация о прохождении квиза пользователем</p>
            </div>
            <button
                @click="$router.push('/admin/notifications')"
                class="px-4 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Вернуться к уведомлениям
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка данных...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Quiz Details -->
        <div v-if="!loading && quizData" class="space-y-6">
            <!-- Quiz Header -->
            <div class="bg-card rounded-lg border border-border p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-foreground mb-2">{{ quizData.quiz_title }}</h2>
                        <p v-if="quizData.completed_at" class="text-sm text-muted-foreground">
                            Завершено: {{ formatDate(quizData.completed_at) }}
                        </p>
                    </div>
                    <span class="px-3 py-1 text-sm bg-green-500/10 text-green-500 rounded-full">
                        Успешно завершен
                    </span>
                </div>
            </div>

            <!-- Contact Information -->
            <div v-if="quizData.contact" class="bg-card rounded-lg border border-border p-6">
                <h3 class="text-xl font-semibold text-foreground mb-4">Контактная информация</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground mb-1">Имя</p>
                        <p class="text-foreground font-medium">{{ quizData.contact.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground mb-1">Email</p>
                        <p class="text-foreground font-medium">{{ quizData.contact.email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground mb-1">Телефон</p>
                        <p class="text-foreground font-medium">{{ quizData.contact.phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Answers -->
            <div v-if="quizData.answers && quizData.answers.length > 0" class="bg-card rounded-lg border border-border p-6">
                <h3 class="text-xl font-semibold text-foreground mb-6">Ответы пользователя</h3>
                <div class="space-y-4">
                    <div
                        v-for="(answerItem, index) in quizData.answers"
                        :key="index"
                        class="border border-border rounded-lg p-5 hover:bg-muted/30 transition-colors"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-accent text-accent-foreground flex items-center justify-center font-semibold">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-lg font-medium text-foreground mb-2">
                                    {{ answerItem.question_text || `Вопрос ${index + 1}` }}
                                </h4>
                                <div class="bg-muted/50 rounded-lg p-4 mt-3">
                                    <p class="text-sm text-muted-foreground mb-1">Ответ:</p>
                                    <p class="text-foreground font-medium text-base">
                                        {{ formatAnswer(answerItem.answer) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

export default {
    name: 'QuizDetails',
    setup() {
        const route = useRoute()
        const loading = ref(false)
        const error = ref(null)
        const quizData = ref(null)

        const fetchQuizDetails = async () => {
            loading.value = true
            error.value = null
            try {
                const notificationId = route.query.notification_id
                if (!notificationId) {
                    throw new Error('ID уведомления не указан')
                }

                const token = localStorage.getItem('token')
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
                if (token) {
                    headers['Authorization'] = `Bearer ${token}`
                }
                
                const response = await fetch(`/api/notifications/${notificationId}`, {
                    method: 'GET',
                    headers,
                })
                
                if (!response.ok) {
                    throw new Error('Ошибка загрузки данных уведомления')
                }

                const result = await response.json()
                const notification = result.data

                if (!notification.data || !notification.data.quiz_id) {
                    throw new Error('Данные о квизе не найдены')
                }

                quizData.value = notification.data
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки данных'
            } finally {
                loading.value = false
            }
        }

        const formatDate = (dateString) => {
            if (!dateString) return ''
            const date = new Date(dateString)
            return date.toLocaleString('ru-RU', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        }

        // Форматирование ответа для отображения
        const formatAnswer = (answer) => {
            if (answer === null || answer === undefined) {
                return 'Нет ответа'
            }

            // Если ответ - объект с полной информацией
            if (typeof answer === 'object') {
                // Новый формат с answer.value и answer.text
                if (answer.text) {
                    return answer.text
                }
                if (answer.value) {
                    return answer.value
                }
                // Старый формат - ищем текст в объекте
                if (answer.name) return answer.name
                if (answer.title) return answer.title
                if (Array.isArray(answer)) {
                    return answer.map(a => typeof a === 'object' ? (a.name || a.title || JSON.stringify(a)) : a).join(', ')
                }
                return JSON.stringify(answer, null, 2)
            }
            
            return String(answer)
        }

        onMounted(() => {
            fetchQuizDetails()
        })

        return {
            loading,
            error,
            quizData,
            formatDate,
            formatAnswer
        }
    }
}
</script>

