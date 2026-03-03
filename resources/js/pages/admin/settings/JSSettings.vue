<template>
    <div class="js-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки JavaScript кода</h1>
            <p class="text-muted-foreground mt-1">Добавление пользовательского JavaScript кода для всех публичных страниц</p>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="text-muted-foreground">Загрузка...</div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm text-red-600">{{ error }}</p>
        </div>

        <form v-else @submit.prevent="saveSettings" class="space-y-6">
            <!-- JavaScript код -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Пользовательский JavaScript код</h2>
                
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        JavaScript код для вставки на все публичные страницы
                    </label>
                    <p class="text-xs text-muted-foreground mb-3">
                        Вставьте код для Яндекс.Метрики, Google Analytics или других сервисов аналитики. 
                        Код будет добавлен в секцию &lt;head&gt; всех публичных страниц.
                    </p>
                    <textarea
                        v-model="form.custom_js_code"
                        rows="15"
                        placeholder='<!-- Пример для Яндекс.Метрики -->
<script type="text/javascript">
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(YOUR_COUNTER_ID, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/YOUR_COUNTER_ID" style="position:absolute; left:-9999px;" alt="" /></div></noscript>'
                        class="w-full px-4 py-3 border border-border rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-[#688E67] resize-none font-mono text-sm"
                    ></textarea>
                    <p class="mt-2 text-xs text-muted-foreground">
                        Код будет вставлен как есть, без дополнительной обработки. Убедитесь, что код корректный и не содержит синтаксических ошибок.
                    </p>
                </div>
            </div>

            <!-- Информация -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-900 mb-1">Важно</h3>
                        <ul class="text-xs text-blue-800 space-y-1">
                            <li>• Код будет добавлен на все публичные страницы сайта</li>
                            <li>• Код вставляется в секцию &lt;head&gt; каждой страницы</li>
                            <li>• Для Яндекс.Метрики и Google Analytics обычно нужно вставить код отслеживания</li>
                            <li>• После сохранения изменения применяются ко всем новым загрузкам страниц</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-4">
                <button
                    type="button"
                    @click="clearCode"
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                >
                    Очистить
                </button>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-6 py-3 bg-[#688E67] text-white rounded-lg hover:bg-[#5a7a5a] transition-colors disabled:opacity-50 font-medium"
                >
                    <span v-if="saving">Сохранение...</span>
                    <span v-else>Сохранить настройки</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'JSSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);

        const form = ref({
            custom_js_code: '',
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;

            try {
                const response = await axios.get('/api/v1/seo-settings');
                if (response.data && response.data.data) {
                    form.value.custom_js_code = response.data.data.custom_js_code || '';
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching JS settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;

            try {
                await axios.put('/api/v1/seo-settings', {
                    custom_js_code: form.value.custom_js_code,
                });
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки JavaScript кода успешно сохранены',
                    timer: 2000,
                    showConfirmButton: false,
                });
            } catch (err) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка!',
                    text: err.response?.data?.message || 'Не удалось сохранить настройки',
                });
            } finally {
                saving.value = false;
            }
        };

        const clearCode = () => {
            Swal.fire({
                title: 'Очистить код?',
                text: 'Вы уверены, что хотите удалить весь JavaScript код?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Да, очистить',
                cancelButtonText: 'Отмена',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.value.custom_js_code = '';
                }
            });
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            error,
            form,
            saveSettings,
            clearCode,
        };
    },
};
</script>
