/**
 * Composable для управления прелоадером
 * Использование: вызывайте hidePreloader() после загрузки контента
 */

export function usePreloader() {
    /**
     * Скрывает прелоадер страницы
     * @param {number} delay - Задержка перед скрытием (мс)
     */
    const hidePreloader = (delay = 300) => {
        setTimeout(() => {
            if (window.hidePreloader && typeof window.hidePreloader === 'function') {
                window.hidePreloader();
            }
        }, delay);
    };

    /**
     * Показывает прелоадер (если он был скрыт)
     */
    const showPreloader = () => {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.classList.remove('hidden');
        }
    };

    /**
     * Проверяет, виден ли прелоадер
     */
    const isPreloaderVisible = () => {
        const preloader = document.getElementById('preloader');
        return preloader && !preloader.classList.contains('hidden');
    };

    return {
        hidePreloader,
        showPreloader,
        isPreloaderVisible,
    };
}

