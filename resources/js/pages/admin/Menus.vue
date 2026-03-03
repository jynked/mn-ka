<template>
    <div class="menus-page space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-foreground">Меню</h1>
                <p class="text-muted-foreground mt-1">Управление меню сайта (Header, Footer)</p>
            </div>
            <button
                @click="showCreateModal = true"
                class="h-11 px-6 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-2xl shadow-lg shadow-accent/10 inline-flex items-center justify-center gap-2"
            >
                <span>+</span>
                <span>Создать пункт меню</span>
            </button>
        </div>

        <!-- Type Filter -->
        <div class="flex gap-2">
            <button
                v-for="type in menuTypes"
                :key="type.value"
                @click="selectedType = type.value; fetchMenus()"
                :class="[
                    'px-4 py-2 rounded-lg transition-colors',
                    selectedType === type.value
                        ? 'bg-accent/20 text-accent border border-accent/40'
                        : 'bg-card border border-border text-foreground hover:bg-muted/10'
                ]"
            >
                {{ type.label }}
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка меню...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Menus List -->
        <div v-if="!loading && menus.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
            <MenuTree
                :items="menus"
                @edit="editMenu"
                @delete="deleteMenu"
                @refresh="fetchMenus"
                @order-updated="handleOrderUpdate"
            />
        </div>

        <!-- Empty State -->
        <div v-if="!loading && menus.length === 0" class="bg-card rounded-lg border border-border p-12 text-center">
            <p class="text-muted-foreground">Меню не найдены. Создайте первый пункт меню.</p>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto p-4">
            <div class="bg-background border border-border rounded-lg shadow-2xl w-full max-w-md my-auto">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        {{ showEditModal ? 'Редактировать пункт меню' : 'Создать пункт меню' }}
                    </h3>
                    <form @submit.prevent="saveMenu" class="space-y-4">
                        <div>
                            <label class="text-sm font-medium mb-1 block">Название</label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                class="w-full h-10 px-3 border border-border rounded bg-background"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-1 block">URL или Slug</label>
                            <input
                                v-model="form.url"
                                type="text"
                                placeholder="/page или https://example.com"
                                class="w-full h-10 px-3 border border-border rounded bg-background"
                            />
                            <p class="text-xs text-muted-foreground mt-1">Оставьте пустым, если используете slug</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-1 block">Slug</label>
                            <input
                                v-model="form.slug"
                                type="text"
                                placeholder="page-slug"
                                class="w-full h-10 px-3 border border-border rounded bg-background"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-1 block">Тип меню</label>
                            <select
                                v-model="form.type"
                                required
                                class="w-full h-10 px-3 border border-border rounded bg-background"
                            >
                                <option value="header">Header</option>
                                <option value="footer">Footer</option>
                                <option value="burger">Burger Menu</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-1 block">Родительский пункт</label>
                            <select
                                v-model="form.parent_id"
                                class="w-full h-10 px-3 border border-border rounded bg-background"
                            >
                                <option :value="null">Нет (корневой элемент)</option>
                                <option
                                    v-for="menu in availableParents"
                                    :key="menu.id"
                                    :value="menu.id"
                                >
                                    {{ menu.title }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium mb-1 block">Порядок</label>
                            <input
                                v-model.number="form.order"
                                type="number"
                                min="0"
                                class="w-full h-10 px-3 border border-border rounded bg-background"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                id="is_active"
                                class="w-4 h-4"
                            />
                            <label for="is_active" class="text-sm font-medium cursor-pointer">Активен</label>
                        </div>
                        <div class="flex gap-2 pt-4">
                            <button
                                type="button"
                                @click="closeModal"
                                class="flex-1 h-10 px-4 border border-border bg-background/50 hover:bg-accent/10 rounded-lg transition-colors"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                :disabled="saving"
                                class="flex-1 h-10 px-4 bg-accent/10 backdrop-blur-xl text-accent border border-accent/40 hover:bg-accent/20 rounded-lg transition-colors disabled:opacity-50"
                            >
                                {{ saving ? 'Сохранение...' : 'Сохранить' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { apiGet, apiPost, apiPut, apiDelete } from '../../utils/api'
import Swal from 'sweetalert2'
import MenuTree from '../../components/admin/MenuTree.vue'

export default {
    name: 'Menus',
    components: {
        MenuTree,
    },
    setup() {
        const loading = ref(false)
        const saving = ref(false)
        const error = ref(null)
        const menus = ref([])
        const allMenus = ref([])
        const selectedType = ref('header')
        const showCreateModal = ref(false)
        const showEditModal = ref(false)
        const form = ref({
            id: null,
            title: '',
            slug: '',
            url: '',
            type: 'header',
            parent_id: null,
            order: 0,
            is_active: true,
        })

        const menuTypes = [
            { value: 'header', label: 'Header Меню' },
            { value: 'footer', label: 'Footer Меню' },
        ]

        const availableParents = computed(() => {
            return allMenus.value.filter(
                m => m.type === form.value.type && m.id !== form.value.id && (!m.parent_id || m.parent_id === null)
            )
        })

        const fetchMenus = async () => {
            loading.value = true
            error.value = null
            try {
                const response = await apiGet('/menus', { type: selectedType.value })
                if (!response.ok) {
                    throw new Error('Ошибка загрузки меню')
                }
                const data = await response.json()
                menus.value = data.data || []
                
                // Загружаем все меню для выбора родителя
                const allResponse = await apiGet('/menus')
                if (allResponse.ok) {
                    const allData = await allResponse.json()
                    allMenus.value = flattenMenus(allData.data || [])
                }
            } catch (err) {
                error.value = err.message || 'Ошибка загрузки меню'
            } finally {
                loading.value = false
            }
        }

        const flattenMenus = (items) => {
            let result = []
            items.forEach(item => {
                result.push(item)
                if (item.children && item.children.length > 0) {
                    result = result.concat(flattenMenus(item.children))
                }
            })
            return result
        }

        const editMenu = (menu) => {
            form.value = {
                id: menu.id,
                title: menu.title,
                slug: menu.slug || '',
                url: menu.url || '',
                type: menu.type,
                parent_id: menu.parent_id,
                order: menu.order,
                is_active: menu.is_active,
            }
            showEditModal.value = true
        }

        const deleteMenu = async (menu) => {
            const result = await Swal.fire({
                title: 'Удалить пункт меню?',
                text: `Вы уверены, что хотите удалить "${menu.title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена',
            })

            if (!result.isConfirmed) return

            try {
                const response = await apiDelete(`/menus/${menu.id}`)
                if (!response.ok) {
                    const data = await response.json()
                    throw new Error(data.message || 'Ошибка удаления')
                }

                await Swal.fire('Удалено!', 'Пункт меню успешно удален.', 'success')
                await fetchMenus()
            } catch (err) {
                await Swal.fire('Ошибка!', err.message || 'Не удалось удалить пункт меню.', 'error')
            }
        }

        const saveMenu = async () => {
            saving.value = true
            error.value = null
            try {
                const url = form.value.id ? `/menus/${form.value.id}` : '/menus'
                const method = form.value.id ? apiPut : apiPost
                
                const response = await method(url, {
                    title: form.value.title,
                    slug: form.value.slug || null,
                    url: form.value.url || null,
                    type: form.value.type,
                    parent_id: form.value.parent_id || null,
                    order: form.value.order || 0,
                    is_active: form.value.is_active,
                })

                if (!response.ok) {
                    const data = await response.json()
                    throw new Error(data.message || 'Ошибка сохранения')
                }

                await Swal.fire('Успешно!', 'Пункт меню успешно сохранен.', 'success')
                closeModal()
                await fetchMenus()
            } catch (err) {
                error.value = err.message || 'Ошибка сохранения'
                await Swal.fire('Ошибка!', error.value, 'error')
            } finally {
                saving.value = false
            }
        }

        const handleOrderUpdate = (event) => {
            // Сохраняем оригинальное состояние для отката при ошибке
            const originalMenus = JSON.parse(JSON.stringify(menus.value))
            
            // Оптимистично обновляем локальный порядок элементов
            // Переупорядочиваем массив меню согласно новому порядку
            if (event.newOrder && Array.isArray(event.newOrder)) {
                const itemMap = new Map()
                menus.value.forEach(item => {
                    itemMap.set(item.id, { ...item })
                })
                
                // Переупорядочиваем только корневые элементы
                menus.value = event.newOrder.map(id => {
                    const item = itemMap.get(id)
                    if (item) {
                        return { ...item }
                    }
                    return null
                }).filter(Boolean)
                
                // Обновляем order для каждого элемента
                menus.value.forEach((item, index) => {
                    item.order = index
                })
            }
            
            // Подготовка данных для отправки на сервер
            const itemsToUpdate = event.items || []

            // Отправляем запрос на сервер в фоновом режиме без блокировки UI
            apiPost('/menus/update-order', {
                items: itemsToUpdate,
            }).then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Ошибка обновления порядка')
                    })
                }
            }).catch(err => {
                console.error('Ошибка обновления порядка на сервере:', err)
                // Откатываем изменения при ошибке
                menus.value = originalMenus
                // Показываем уведомление об ошибке (тихо, без блокировки)
                Swal.fire({
                    title: 'Ошибка',
                    text: err.message || 'Не удалось сохранить порядок меню. Изменения отменены.',
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                })
            })
        }

        const closeModal = () => {
            showCreateModal.value = false
            showEditModal.value = false
            form.value = {
                id: null,
                title: '',
                slug: '',
                url: '',
                type: selectedType.value,
                parent_id: null,
                order: 0,
                is_active: true,
            }
        }

        onMounted(async () => {
            await fetchMenus()
        })

        return {
            loading,
            saving,
            error,
            menus,
            selectedType,
            menuTypes,
            showCreateModal,
            showEditModal,
            form,
            availableParents,
            fetchMenus,
            editMenu,
            deleteMenu,
            saveMenu,
            closeModal,
            handleOrderUpdate,
        }
    },
}
</script>

