/**
 * Composable для кеширования продуктов
 * Использует localStorage для кеширования данных продуктов
 */
import { ref } from 'vue';

const CACHE_PREFIX = 'product_cache_';
const CACHE_EXPIRY = 5 * 60 * 1000; // 5 минут

export function useProductCache() {
    /**
     * Получить данные из кеша
     */
    const getCachedProduct = (slug) => {
        try {
            const cacheKey = `${CACHE_PREFIX}${slug}`;
            const cached = localStorage.getItem(cacheKey);
            if (!cached) return null;

            const { data, timestamp } = JSON.parse(cached);
            const now = Date.now();

            // Проверяем, не истек ли кеш
            if (now - timestamp > CACHE_EXPIRY) {
                localStorage.removeItem(cacheKey);
                return null;
            }

            return data;
        } catch (error) {
            console.error('Error reading from cache:', error);
            return null;
        }
    };

    /**
     * Сохранить данные в кеш
     */
    const setCachedProduct = (slug, data) => {
        try {
            const cacheKey = `${CACHE_PREFIX}${slug}`;
            const cacheData = {
                data,
                timestamp: Date.now(),
            };
            localStorage.setItem(cacheKey, JSON.stringify(cacheData));
        } catch (error) {
            console.error('Error writing to cache:', error);
            // Если localStorage переполнен, очищаем старые записи
            clearOldCache();
        }
    };

    /**
     * Очистить старые записи из кеша
     */
    const clearOldCache = () => {
        try {
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith(CACHE_PREFIX)) {
                    try {
                        const cached = JSON.parse(localStorage.getItem(key));
                        if (Date.now() - cached.timestamp > CACHE_EXPIRY) {
                            localStorage.removeItem(key);
                        }
                    } catch (e) {
                        localStorage.removeItem(key);
                    }
                }
            });
        } catch (error) {
            console.error('Error clearing old cache:', error);
        }
    };

    /**
     * Очистить кеш для конкретного продукта
     */
    const clearProductCache = (slug) => {
        try {
            const cacheKey = `${CACHE_PREFIX}${slug}`;
            localStorage.removeItem(cacheKey);
        } catch (error) {
            console.error('Error clearing product cache:', error);
        }
    };

    return {
        getCachedProduct,
        setCachedProduct,
        clearProductCache,
        clearOldCache,
    };
}

