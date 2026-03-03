<template>
    <div class="documentation-page">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-foreground">Документация</h1>
            <p class="text-muted-foreground mt-2">Полное руководство по использованию админ-панели</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Navigation -->
            <aside class="lg:w-64 flex-shrink-0">
                <div class="bg-card rounded-lg border border-border p-4 sticky top-4">
                    <h2 class="font-semibold text-foreground mb-4">Содержание</h2>
                    <nav class="space-y-1">
                        <a
                            v-for="section in sections"
                            :key="section.id"
                            :href="`#${section.id}`"
                            @click.prevent="scrollToSection(section.id)"
                            :class="[
                                'block px-3 py-2 rounded-md text-sm transition-colors',
                                activeSection === section.id
                                    ? 'bg-accent text-accent-foreground font-medium'
                                    : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground'
                            ]"
                        >
                            {{ section.title }}
                        </a>
                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                <div class="bg-card rounded-lg border border-border p-6 space-y-8">
                    <!-- Introduction -->
                    <section id="introduction" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Введение</h2>
                        <div class="prose prose-sm max-w-none text-foreground">
                            <p>
                                Добро пожаловать в админ-панель системы управления контентом. 
                                Эта документация поможет вам разобраться со всеми возможностями системы 
                                и эффективно управлять контентом вашего сайта.
                            </p>
                            <p>
                                Админ-панель разделена на несколько основных разделов, каждый из которых 
                                отвечает за определенный тип контента или настройки системы.
                            </p>
                        </div>
                    </section>

                    <!-- Dashboard -->
                    <section id="dashboard" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Панель управления</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                <strong>Панель управления</strong> — это главная страница админ-панели, 
                                которая предоставляет общий обзор системы и быстрый доступ к основным функциям.
                            </p>
                            
                            <h3 class="text-xl font-semibold mt-6 mb-3">Как использовать</h3>
                            <ul class="list-disc pl-6 space-y-2">
                                <li>Откройте раздел "Панель управления" из главного меню</li>
                                <li>Просмотрите статистику и основные показатели системы</li>
                                <li>Используйте быстрые ссылки для перехода к нужным разделам</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Resources -->
                    <section id="resources" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Ресурсы</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                Раздел <strong>Ресурсы</strong> содержит управление основными типами контента: 
                                продуктами, категориями и услугами.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Продукты</h3>
                            <p><strong>Как создать продукт:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Ресурсы" → "Продукты"</li>
                                <li>Нажмите кнопку "Создать продукт"</li>
                                <li>Заполните обязательные поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название</strong> — название продукта</li>
                                        <li><strong>Описание</strong> — подробное описание продукта</li>
                                        <li><strong>Цена</strong> — стоимость продукта</li>
                                        <li><strong>Категория</strong> — выберите категорию из списка</li>
                                        <li><strong>Изображение</strong> — загрузите изображение продукта</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <p class="mt-4"><strong>Как редактировать продукт:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Найдите нужный продукт в списке</li>
                                <li>Нажмите на кнопку "Редактировать" (иконка карандаша)</li>
                                <li>Внесите необходимые изменения</li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <p class="mt-4"><strong>Как удалить продукт:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Найдите продукт в списке</li>
                                <li>Нажмите на кнопку "Удалить" (иконка корзины)</li>
                                <li>Подтвердите удаление в диалоговом окне</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Категории</h3>
                            <p><strong>Как создать категорию:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Ресурсы" → "Категории"</li>
                                <li>Нажмите кнопку "Создать категорию"</li>
                                <li>Заполните поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название</strong> — название категории</li>
                                        <li><strong>Описание</strong> — описание категории (необязательно)</li>
                                        <li><strong>Родительская категория</strong> — выберите родительскую категорию, если нужно создать подкатегорию</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Услуги</h3>
                            <p>
                                <strong>Услуги</strong> в разделе "Ресурсы" — это общие услуги сайта, 
                                не связанные с разделом "Решения". Для управления услугами в блоке "Решения" 
                                используйте раздел "Решения" → "Услуги".
                            </p>
                            <p><strong>Как создать услугу:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Ресурсы" → "Услуги"</li>
                                <li>Нажмите кнопку "Создать услугу"</li>
                                <li>Заполните поля аналогично созданию продукта</li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>
                            <p class="mt-4 text-sm text-muted-foreground">
                                <strong>Примечание:</strong> Для управления услугами в блоке "Решения" 
                                (с привязкой к разделам и случаям) используйте раздел "Решения" → "Услуги".
                            </p>
                        </div>
                    </section>

                    <!-- Media -->
                    <section id="media" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Медиа</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                Раздел <strong>Медиа</strong> предназначен для управления всеми медиафайлами: 
                                изображениями, видео, документами и другими файлами.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как загрузить файл</h3>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Медиа"</li>
                                <li>Нажмите кнопку "Загрузить файл" или перетащите файлы в область загрузки</li>
                                <li>Выберите файлы с вашего компьютера</li>
                                <li>Дождитесь завершения загрузки</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как организовать файлы</h3>
                            <p>Вы можете создавать папки для организации файлов:</p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Нажмите кнопку "Создать папку"</li>
                                <li>Введите название папки</li>
                                <li>Перетащите файлы в нужную папку или используйте меню "Переместить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как использовать файл</h3>
                            <p>После загрузки файла вы можете:</p>
                            <ul class="list-disc pl-6 space-y-2">
                                <li>Скопировать URL файла для использования в контенте</li>
                                <li>Просмотреть информацию о файле (размер, тип, дата загрузки)</li>
                                <li>Редактировать изображения с помощью встроенного редактора</li>
                                <li>Удалить файл, если он больше не нужен</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Notifications -->
                    <section id="notifications" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Уведомления</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                Раздел <strong>Уведомления</strong> отображает все системные уведомления, 
                                включая уведомления о прохождении квизов, важных событиях и обновлениях.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как просмотреть уведомления</h3>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Уведомления"</li>
                                <li>Просмотрите список всех уведомлений</li>
                                <li>Используйте фильтры для поиска нужных уведомлений:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li>По статусу (прочитанные/непрочитанные)</li>
                                        <li>По типу</li>
                                        <li>По тексту (поиск)</li>
                                    </ul>
                                </li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Уведомления о квизах</h3>
                            <p>При прохождении квиза пользователем создается уведомление с детальной информацией:</p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Нажмите на уведомление о прохождении квиза</li>
                                <li>Просмотрите детальную информацию:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li>Информация о пользователе (имя, email, телефон)</li>
                                        <li>Все вопросы и ответы квиза</li>
                                        <li>Дата и время прохождения</li>
                                    </ul>
                                </li>
                                <li>Уведомление автоматически отмечается как прочитанное</li>
                            </ol>
                        </div>
                    </section>

                    <!-- Users -->
                    <section id="users" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Пользователи</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p class="text-sm text-muted-foreground italic">
                                Доступно только для администраторов
                            </p>
                            <p>
                                Раздел <strong>Пользователи</strong> позволяет управлять всеми пользователями системы, 
                                их ролями и правами доступа.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как создать пользователя</h3>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Пользователи"</li>
                                <li>Нажмите кнопку "Создать пользователя"</li>
                                <li>Заполните обязательные поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Имя</strong> — полное имя пользователя</li>
                                        <li><strong>Email</strong> — электронная почта (используется для входа)</li>
                                        <li><strong>Пароль</strong> — пароль должен содержать минимум 8 символов</li>
                                        <li><strong>Роли</strong> — выберите роли пользователя (можно несколько)</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как редактировать пользователя</h3>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Найдите пользователя в списке</li>
                                <li>Нажмите кнопку "Редактировать"</li>
                                <li>Измените необходимые данные</li>
                                <li>Для смены пароля введите новый пароль в поле "Пароль"</li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Роли пользователей</h3>
                            <p>В системе существует несколько ролей:</p>
                            <ul class="list-disc pl-6 space-y-2">
                                <li><strong>admin</strong> — полный доступ ко всем функциям</li>
                                <li><strong>manager</strong> — доступ к управлению контентом, но без доступа к настройкам системы</li>
                                <li><strong>user</strong> — базовый доступ, только просмотр</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Roles -->
                    <section id="roles" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Роли</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p class="text-sm text-muted-foreground italic">
                                Доступно только для администраторов
                            </p>
                            <p>
                                Раздел <strong>Роли</strong> позволяет управлять ролями пользователей и их правами доступа.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как создать роль</h3>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Роли"</li>
                                <li>Нажмите кнопку "Создать роль"</li>
                                <li>Заполните поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название</strong> — отображаемое название роли</li>
                                        <li><strong>Slug</strong> — уникальный идентификатор (латинские буквы, цифры, дефисы)</li>
                                        <li><strong>Описание</strong> — описание роли и её назначения</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <p class="mt-4"><strong>Важно:</strong> Slug должен быть уникальным и не может быть изменен после создания.</p>
                        </div>
                    </section>

                    <!-- Settings -->
                    <section id="settings" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Настройки</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p class="text-sm text-muted-foreground italic">
                                Доступно только для администраторов
                            </p>
                            <p>
                                Раздел <strong>Настройки</strong> содержит различные настройки системы.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Telegram настройки</h3>
                            <p>Настройка интеграции с Telegram ботом для получения уведомлений:</p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Настройки" → "Telegram"</li>
                                <li>Заполните поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Токен бота</strong> — получите у @BotFather в Telegram</li>
                                        <li><strong>Имя бота</strong> — название бота (необязательно)</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить настройки" — webhook зарегистрируется автоматически</li>
                                <li>Включите опции:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li>"Отправлять уведомления" — для получения уведомлений о событиях</li>
                                        <li>"Отправлять критические ошибки" — для получения уведомлений об ошибках сервера</li>
                                    </ul>
                                </li>
                            </ol>

                            <h4 class="text-lg font-semibold mt-4 mb-2">Как добавить администратора через Telegram</h4>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>После настройки бота, отправьте команду <code>/admin</code> в боте</li>
                                <li>Заявка на администрирование появится в разделе "Заявки на администрирование"</li>
                                <li>Администратор может одобрить или отклонить заявку</li>
                                <li>После одобрения пользователь получит права администратора и будет получать все уведомления</li>
                            </ol>
                        </div>
                    </section>

                    <!-- Menus -->
                    <section id="menus" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Меню</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p class="text-sm text-muted-foreground italic">
                                Доступно только для администраторов
                            </p>
                            <p>
                                Раздел <strong>Меню</strong> позволяет управлять навигационными меню сайта: 
                                главным меню, меню в футере и бургер-меню.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как создать пункт меню</h3>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Меню"</li>
                                <li>Выберите тип меню (Header, Footer, Burger)</li>
                                <li>Нажмите кнопку "Добавить пункт"</li>
                                <li>Заполните поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название</strong> — текст ссылки</li>
                                        <li><strong>URL</strong> — адрес ссылки</li>
                                        <li><strong>Порядок</strong> — порядок отображения</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как изменить порядок пунктов</h3>
                            <p>Вы можете перетаскивать пункты меню для изменения их порядка:</p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Наведите курсор на пункт меню</li>
                                <li>Зажмите левую кнопку мыши и перетащите пункт в нужное место</li>
                                <li>Отпустите кнопку мыши</li>
                                <li>Изменения сохранятся автоматически</li>
                            </ol>
                        </div>
                    </section>

                    <!-- Banners -->
                    <section id="banners" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Баннеры</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                Раздел <strong>Баннеры</strong> позволяет управлять баннерами на сайте.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Баннер на главной</h3>
                            <p><strong>Как создать/редактировать баннер:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Баннеры" → "Баннер на главной"</li>
                                <li>Заполните поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Заголовок</strong> — основной заголовок баннера</li>
                                        <li><strong>Подзаголовок</strong> — дополнительный текст</li>
                                        <li><strong>Изображение</strong> — загрузите изображение баннера</li>
                                        <li><strong>Ссылка</strong> — URL, на который ведет баннер (необязательно)</li>
                                        <li><strong>Текст кнопки</strong> — текст на кнопке (необязательно)</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>
                        </div>
                    </section>

                    <!-- Decisions -->
                    <section id="decisions" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Решения</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                Раздел <strong>Решения</strong> предназначен для управления блоком "Решения" на сайте, 
                                который позволяет пользователям выбирать между продуктами и услугами. 
                                Структура состоит из <strong>Разделов</strong> (цель обращения), <strong>Случаев</strong> (подходящий случай) 
                                и <strong>Услуг</strong>, связанных с ними.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Разделы</h3>
                            <p>
                                <strong>Разделы</strong> представляют собой категории целей обращения пользователей 
                                (например, "Получение РНС зданий", "Аренда земельного участка").
                            </p>
                            <p><strong>Как создать раздел:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Решения" → "Разделы"</li>
                                <li>Нажмите кнопку "Создать раздел"</li>
                                <li>Заполните поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название</strong> — название раздела (цель обращения)</li>
                                        <li><strong>Описание</strong> — описание раздела</li>
                                        <li><strong>Изображение</strong> — загрузите изображение раздела</li>
                                        <li><strong>Иконка</strong> — загрузите иконку раздела</li>
                                        <li><strong>Порядок</strong> — порядок отображения</li>
                                        <li><strong>Активен</strong> — включите для отображения на сайте</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Случаи</h3>
                            <p>
                                <strong>Случаи</strong> — это конкретные ситуации, подходящие для выбранного раздела 
                                (например, "Индивидуальный жилой дом", "Многоквартирный дом", "Гостиницы").
                            </p>
                            <p><strong>Как создать случай:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Решения" → "Случаи"</li>
                                <li>Нажмите кнопку "Создать случай"</li>
                                <li>Заполните поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название</strong> — название случая</li>
                                        <li><strong>Раздел</strong> — выберите раздел, к которому относится случай (обязательно)</li>
                                        <li><strong>Описание</strong> — описание случая (необязательно)</li>
                                        <li><strong>HTML контент</strong> — дополнительный HTML контент</li>
                                        <li><strong>Изображение</strong> — загрузите изображение случая</li>
                                        <li><strong>Иконка</strong> — загрузите иконку случая</li>
                                        <li><strong>Порядок</strong> — порядок отображения внутри раздела</li>
                                        <li><strong>Активен</strong> — включите для отображения на сайте</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Услуги</h3>
                            <p>
                                <strong>Услуги</strong> в разделе "Решения" связаны с разделами и случаями. 
                                При создании услуги вы можете выбрать раздел и связанные случаи.
                            </p>
                            <p><strong>Как создать услугу:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в "Решения" → "Услуги"</li>
                                <li>Нажмите "Создать услугу"</li>
                                <li>Заполните поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название</strong> — название услуги</li>
                                        <li><strong>Slug</strong> — URL-адрес услуги (генерируется автоматически из названия)</li>
                                        <li><strong>Описание</strong> — описание услуги (можно указать краткое и подробное)</li>
                                        <li><strong>HTML контент</strong> — HTML контент, отображаемый на странице услуги</li>
                                        <li><strong>Раздел</strong> — выберите раздел, к которому относится услуга</li>
                                        <li><strong>Случаи</strong> — выберите связанные случаи (можно несколько)</li>
                                        <li><strong>Изображение</strong> — загрузите изображение услуги</li>
                                        <li><strong>Иконка</strong> — загрузите иконку услуги</li>
                                        <li><strong>Порядок</strong> — порядок отображения (можно изменить перетаскиванием в списке)</li>
                                        <li><strong>Активна</strong> — включите для отображения на сайте</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h4 class="text-lg font-semibold mt-4 mb-2">Изменение порядка услуг</h4>
                            <p>Вы можете изменить порядок отображения услуг с помощью drag-and-drop:</p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в "Решения" → "Услуги"</li>
                                <li>Наведите курсор на услугу, которую хотите переместить</li>
                                <li>Зажмите левую кнопку мыши на иконке перетаскивания (слева от названия)</li>
                                <li>Перетащите услугу в нужное место</li>
                                <li>Отпустите кнопку мыши — порядок сохранится автоматически</li>
                            </ol>

                            <h4 class="text-lg font-semibold mt-4 mb-2">Импорт и экспорт услуг</h4>
                            <p><strong>Экспорт услуг:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в "Решения" → "Услуги"</li>
                                <li>Нажмите кнопку "Экспорт CSV/ZIP"</li>
                                <li>Выберите формат экспорта (CSV или ZIP с изображениями)</li>
                                <li>Файл будет скачан на ваш компьютер</li>
                            </ol>

                            <p class="mt-4"><strong>Импорт услуг:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в "Решения" → "Услуги"</li>
                                <li>Нажмите кнопку "Импорт ZIP/CSV"</li>
                                <li>Ознакомьтесь с документацией по импорту (откроется автоматически)</li>
                                <li>Отметьте "Я ознакомлен с документацией"</li>
                                <li>Выберите файл для импорта (CSV или ZIP)</li>
                                <li>Если в файле указан <strong>ID</strong> услуги, существующая услуга будет обновлена, иначе создастся новая</li>
                                <li>Обязательные поля: <strong>Название</strong></li>
                                <li>После импорта вы увидите отчет с результатами (успешно, пропущено, ошибки)</li>
                            </ol>

                            <div class="bg-muted/50 p-4 rounded-lg mt-4">
                                <p class="font-semibold mb-2">Важно при импорте:</p>
                                <ul class="list-disc pl-6 space-y-1 text-sm">
                                    <li>Поле <strong>Описание</strong> можно указать как обычный текст (будет автоматически преобразовано в JSON)</li>
                                    <li>Если нужно указать краткое и подробное описание, разделите их двойным переносом строки</li>
                                    <li>Изображения и иконки должны быть в папке <code>images/</code> внутри ZIP архива</li>
                                    <li>Пути к изображениям указываются относительно папки <code>images/</code></li>
                                </ul>
                            </div>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Продукты</h3>
                            <p>
                                <strong>Продукты</strong> в разделе "Решения" отображаются на главной странице 
                                вместе с услугами в блоке выбора решений.
                            </p>
                            <p><strong>Как создать продукт:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в "Решения" → "Продукты"</li>
                                <li>Нажмите "Создать продукт"</li>
                                <li>Заполните поля аналогично услугам</li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Настройки блока</h3>
                            <p>В настройках блока "Решения" вы можете:</p>
                            <ul class="list-disc pl-6 space-y-2">
                                <li>Включить/отключить отображение блока на сайте</li>
                                <li>Настроить заголовок и описание блока</li>
                                <li>Настроить внешний вид и поведение блока</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Quizzes -->
                    <section id="quizzes" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Квизы</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                Раздел <strong>Квизы</strong> позволяет создавать и управлять интерактивными квизами 
                                для сбора информации от пользователей.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как создать квиз</h3>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Квизы" → "Список квизов"</li>
                                <li>Нажмите кнопку "Создать квиз"</li>
                                <li>Заполните основную информацию:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название</strong> — название квиза</li>
                                        <li><strong>Описание</strong> — описание квиза</li>
                                        <li><strong>Изображение</strong> — загрузите изображение квиза</li>
                                    </ul>
                                </li>
                                <li>Добавьте вопросы:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li>Нажмите "Добавить вопрос"</li>
                                        <li>Выберите тип вопроса:
                                            <ul class="list-disc pl-6 mt-2">
                                                <li><strong>Текст</strong> — текстовый ответ</li>
                                                <li><strong>Выбор</strong> — выбор из вариантов (radio buttons)</li>
                                                <li><strong>Сбор изображений</strong> — выбор из изображений</li>
                                            </ul>
                                        </li>
                                        <li>Заполните текст вопроса</li>
                                        <li>Для вопросов с выбором добавьте варианты ответов</li>
                                        <li>Установите порядок вопроса</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Типы вопросов</h3>
                            <div class="bg-muted/50 p-4 rounded-lg space-y-3">
                                <div>
                                    <strong>Текст</strong>
                                    <p class="text-sm mt-1">Пользователь вводит текстовый ответ в поле ввода.</p>
                                </div>
                                <div>
                                    <strong>Выбор</strong>
                                    <p class="text-sm mt-1">Пользователь выбирает один вариант из предложенных. Каждый вариант может иметь дочерние варианты, которые отображаются при выборе родительского.</p>
                                </div>
                                <div>
                                    <strong>Сбор изображений</strong>
                                    <p class="text-sm mt-1">Пользователь выбирает один вариант из предложенных изображений. Каждое изображение может иметь дочерние варианты.</p>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как просмотреть результаты квиза</h3>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>При прохождении квиза пользователем создается уведомление</li>
                                <li>Перейдите в раздел "Уведомления"</li>
                                <li>Нажмите на уведомление о прохождении квиза</li>
                                <li>Просмотрите детальную информацию:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li>Все вопросы и ответы</li>
                                        <li>Контактную информацию пользователя</li>
                                        <li>Дату и время прохождения</li>
                                    </ul>
                                </li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Настройки блока квизов</h3>
                            <p>В настройках блока квизов вы можете:</p>
                            <ul class="list-disc pl-6 space-y-2">
                                <li>Включить/отключить отображение блока на сайте</li>
                                <li>Настроить заголовок и описание блока</li>
                                <li>Выбрать квиз, который будет отображаться по умолчанию</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Search -->
                    <section id="search" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Поиск по сайту</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                На сайте реализован полнотекстовый поиск по всем типам контента: услугам, продуктам, 
                                кейсам и страницам. Поиск работает автоматически и не требует дополнительной настройки.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Как работает поиск</h3>
                            <p>Поисковая система индексирует следующие поля:</p>
                            <ul class="list-disc pl-6 space-y-2">
                                <li><strong>Услуги:</strong> название, описание, HTML контент, slug, SEO поля</li>
                                <li><strong>Продукты:</strong> название, описание, HTML контент, slug, SEO поля</li>
                                <li><strong>Кейсы:</strong> название, описание, HTML контент, slug, SEO поля</li>
                                <li><strong>Страницы:</strong> заголовок, содержимое, slug, SEO поля</li>
                            </ul>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Автодополнение</h3>
                            <p>
                                При вводе запроса в поле поиска (минимум 2 символа) автоматически появляются 
                                подсказки с наиболее релевантными результатами. Подсказки обновляются с задержкой 
                                300 мс для оптимизации производительности.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Страница результатов поиска</h3>
                            <p>
                                При выполнении поиска пользователь перенаправляется на страницу результатов, 
                                где результаты сгруппированы по типам контента (Услуги, Продукты, Кейсы, Страницы) 
                                и отображаются с пагинацией (10 результатов на страницу).
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Рекомендации</h3>
                            <ul class="list-disc pl-6 space-y-2">
                                <li>Заполняйте SEO поля для лучшей индексации контента</li>
                                <li>Используйте релевантные ключевые слова в названиях и описаниях</li>
                                <li>Добавляйте HTML контент для услуг и продуктов для более полного индексирования</li>
                            </ul>
                        </div>
                    </section>

                    <!-- SEO -->
                    <section id="seo" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">SEO оптимизация</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <p>
                                Раздел <strong>SEO настройки</strong> позволяет управлять поисковой оптимизацией сайта, 
                                настраивать мета-теги, Open Graph, Twitter Cards и Schema.org микроразметку.
                            </p>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Глобальные SEO настройки</h3>
                            <p><strong>Как настроить SEO:</strong></p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Настройки" → "SEO настройки"</li>
                                <li>Заполните основные настройки:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название сайта</strong> — отображается в поисковой выдаче и браузере</li>
                                        <li><strong>Описание сайта</strong> — краткое описание для поисковиков (рекомендуется 150-160 символов)</li>
                                        <li><strong>Ключевые слова</strong> — основные ключевые слова через запятую</li>
                                        <li><strong>Разрешить индексацию</strong> — включите для разрешения индексации поисковыми системами</li>
                                    </ul>
                                </li>
                                <li>Настройте Open Graph (для социальных сетей):
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Изображение по умолчанию</strong> — изображение для превью в соцсетях (рекомендуется 1200x630px)</li>
                                        <li><strong>Тип контента OG</strong> — website, article или business</li>
                                        <li><strong>Название сайта для OG</strong> — название для Open Graph</li>
                                    </ul>
                                </li>
                                <li>Настройте Twitter Cards:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Тип карточки</strong> — summary или summary_large_image</li>
                                        <li><strong>Twitter аккаунт сайта</strong> — @username вашего Twitter аккаунта</li>
                                        <li><strong>Twitter создателя</strong> — @username создателя контента</li>
                                    </ul>
                                </li>
                                <li>Заполните информацию об организации (для Schema.org):
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>Название организации</strong> — официальное название компании</li>
                                        <li><strong>Лого организации</strong> — URL логотипа</li>
                                        <li><strong>Телефон</strong> — контактный телефон</li>
                                        <li><strong>Email</strong> — контактный email</li>
                                        <li><strong>Адрес организации</strong> — физический адрес</li>
                                    </ul>
                                </li>
                                <li>Настройте robots.txt (опционально):
                                    <ul class="list-disc pl-6 mt-2">
                                        <li>Укажите правила для поисковых роботов</li>
                                        <li>По умолчанию закрыты разделы /admin/ и /api/</li>
                                    </ul>
                                </li>
                                <li>Нажмите "Сохранить настройки"</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">SEO для отдельных страниц</h3>
                            <p>Для продуктов, услуг, кейсов и пользовательских страниц можно настроить индивидуальные SEO параметры:</p>
                            
                            <h4 class="text-lg font-semibold mt-4 mb-2">Продукты и Услуги</h4>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>При создании или редактировании продукта/услуги заполните SEO поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>SEO заголовок</strong> — уникальный заголовок для поисковой выдачи (если не указан, используется название)</li>
                                        <li><strong>SEO описание</strong> — краткое описание для сниппета в поиске (рекомендуется 150-160 символов)</li>
                                        <li><strong>SEO ключевые слова</strong> — релевантные ключевые слова через запятую</li>
                                    </ul>
                                </li>
                                <li>SEO данные автоматически используются на странице продукта/услуги</li>
                                <li>Добавляется Schema.org микроразметка (Product/Service)</li>
                                <li>Генерируются хлебные крошки (Breadcrumbs)</li>
                            </ol>

                            <h4 class="text-lg font-semibold mt-4 mb-2">Кейсы</h4>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>При создании или редактировании кейса заполните SEO поля аналогично продуктам</li>
                                <li>Для кейсов используется Schema.org тип "Article"</li>
                                <li>Автоматически добавляются даты публикации и обновления</li>
                                <li>Изображения кейса включаются в микроразметку</li>
                            </ol>

                            <h4 class="text-lg font-semibold mt-4 mb-2">Пользовательские страницы</h4>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Перейдите в раздел "Страницы" → "Список страниц"</li>
                                <li>Создайте или отредактируйте страницу</li>
                                <li>Заполните SEO поля:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><strong>SEO заголовок</strong> — заголовок для поисковой выдачи</li>
                                        <li><strong>SEO описание</strong> — описание для сниппета</li>
                                        <li><strong>SEO ключевые слова</strong> — ключевые слова</li>
                                    </ul>
                                </li>
                                <li>Укажите уникальный URL (slug) для страницы</li>
                                <li>Сохраните страницу</li>
                            </ol>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Автоматические SEO инструменты</h3>
                            <div class="bg-muted/50 p-4 rounded-lg space-y-3">
                                <div>
                                    <strong>Robots.txt</strong>
                                    <p class="text-sm mt-1">
                                        Автоматически генерируется на основе настроек. Доступен по адресу: 
                                        <code>/robots.txt</code>
                                    </p>
                                </div>
                                <div>
                                    <strong>Sitemap.xml</strong>
                                    <p class="text-sm mt-1">
                                        Автоматически генерируется со всеми страницами сайта. Включает:
                                        главную страницу, статические страницы, продукты, услуги, кейсы и пользовательские страницы.
                                        Доступен по адресу: <code>/sitemap.xml</code>
                                    </p>
                                </div>
                                <div>
                                    <strong>Canonical URL</strong>
                                    <p class="text-sm mt-1">
                                        Автоматически добавляется на всех страницах для предотвращения дублей контента.
                                    </p>
                                </div>
                                <div>
                                    <strong>Schema.org микроразметка</strong>
                                    <p class="text-sm mt-1">
                                        Автоматически добавляется на всех страницах в формате JSON-LD. 
                                        Типы разметки: Organization, WebSite, Product, Service, Article, BreadcrumbList.
                                    </p>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Рекомендации по SEO</h3>
                            <ul class="list-disc pl-6 space-y-2">
                                <li>Используйте уникальные заголовки и описания для каждой страницы</li>
                                <li>Описание должно быть информативным и привлекательным (150-160 символов)</li>
                                <li>Включайте ключевые слова естественным образом в заголовки и описания</li>
                                <li>Загружайте изображения для Open Graph размером 1200x630px</li>
                                <li>Регулярно обновляйте sitemap.xml в Google Search Console и Яндекс.Вебмастер</li>
                                <li>Проверяйте микроразметку с помощью валидаторов:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><a href="https://search.google.com/test/rich-results" target="_blank" class="text-accent hover:underline">Google Rich Results Test</a></li>
                                        <li><a href="https://validator.schema.org/" target="_blank" class="text-accent hover:underline">Schema.org Validator</a></li>
                                    </ul>
                                </li>
                                <li>Проверяйте превью в соцсетях:
                                    <ul class="list-disc pl-6 mt-2">
                                        <li><a href="https://developers.facebook.com/tools/debug/" target="_blank" class="text-accent hover:underline">Facebook Sharing Debugger</a></li>
                                        <li><a href="https://cards-dev.twitter.com/validator" target="_blank" class="text-accent hover:underline">Twitter Card Validator</a></li>
                                    </ul>
                                </li>
                            </ul>

                            <h3 class="text-xl font-semibold mt-6 mb-3">Проверка индексации</h3>
                            <p>После настройки SEO рекомендуется:</p>
                            <ol class="list-decimal pl-6 space-y-2">
                                <li>Добавить сайт в <a href="https://search.google.com/search-console" target="_blank" class="text-accent hover:underline">Google Search Console</a></li>
                                <li>Добавить сайт в <a href="https://webmaster.yandex.ru/" target="_blank" class="text-accent hover:underline">Яндекс.Вебмастер</a></li>
                                <li>Отправить sitemap.xml в обе системы</li>
                                <li>Регулярно проверять статус индексации</li>
                                <li>Отслеживать позиции в поисковой выдаче</li>
                            </ol>
                        </div>
                    </section>

                    <!-- Tips -->
                    <section id="tips" class="scroll-mt-4">
                        <h2 class="text-2xl font-bold text-foreground mb-4">Полезные советы</h2>
                        <div class="prose prose-sm max-w-none text-foreground space-y-4">
                            <ul class="list-disc pl-6 space-y-2">
                                <li>Всегда сохраняйте изменения перед переходом на другую страницу</li>
                                <li>Используйте поиск и фильтры для быстрого нахождения нужных элементов</li>
                                <li>Регулярно проверяйте уведомления для отслеживания активности пользователей</li>
                                <li>Используйте изображения оптимального размера для быстрой загрузки сайта</li>
                                <li>Проверяйте работу квизов после создания или редактирования</li>
                                <li>Настройте Telegram бота для получения уведомлений в реальном времени</li>
                            </ul>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';

export default {
    name: 'Documentation',
    setup() {
        const activeSection = ref('introduction');
        const sections = ref([
            { id: 'introduction', title: 'Введение' },
            { id: 'dashboard', title: 'Панель управления' },
            { id: 'resources', title: 'Ресурсы' },
            { id: 'media', title: 'Медиа' },
            { id: 'notifications', title: 'Уведомления' },
            { id: 'users', title: 'Пользователи' },
            { id: 'roles', title: 'Роли' },
            { id: 'settings', title: 'Настройки' },
            { id: 'menus', title: 'Меню' },
            { id: 'banners', title: 'Баннеры' },
            { id: 'decisions', title: 'Решения' },
            { id: 'quizzes', title: 'Квизы' },
            { id: 'search', title: 'Поиск по сайту' },
            { id: 'seo', title: 'SEO оптимизация' },
            { id: 'tips', title: 'Полезные советы' },
        ]);

        const scrollToSection = (sectionId) => {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                activeSection.value = sectionId;
            }
        };

        const handleScroll = () => {
            const scrollPosition = window.scrollY + 200;
            
            for (let i = sections.value.length - 1; i >= 0; i--) {
                const section = document.getElementById(sections.value[i].id);
                if (section && section.offsetTop <= scrollPosition) {
                    activeSection.value = sections.value[i].id;
                    break;
                }
            }
        };

        onMounted(() => {
            window.addEventListener('scroll', handleScroll);
            // Проверяем начальную позицию
            handleScroll();
        });

        onUnmounted(() => {
            window.removeEventListener('scroll', handleScroll);
        });

        return {
            activeSection,
            sections,
            scrollToSection,
        };
    },
};
</script>

<style scoped>
.documentation-page {
    min-height: 100vh;
}

.prose code {
    background-color: hsl(var(--muted));
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
}

.scroll-mt-4 {
    scroll-margin-top: 1rem;
}
</style>

