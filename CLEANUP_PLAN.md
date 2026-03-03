# План очистки проекта от неиспользуемых компонентов

## АНАЛИЗ ИСПОЛЬЗУЕМЫХ КОМПОНЕНТОВ

### ✅ ИСПОЛЬЗУЕМЫЕ МОДЕЛИ (оставить):
- User, Role, Menu, Banner
- Chapter, Product, Service, ProjectCase
- Option, OptionTree, Instance
- Quiz, QuizQuestion
- Page, HomePageBlock
- Media, Folder
- Notification
- ProductRequest, RequestHistory, FeedbackRequest
- DecisionBlockSettings, QuizBlockSettings, HowWorkBlockSettings, FaqBlockSettings, WhyChooseUsBlockSettings, CasesBlockSettings, CaseCardSettings
- SeoSettings, ContactSettings, AboutSettings, FooterSettings, ModalSettings
- TelegramSettings, TelegramAdminRequest
- AppCategory (используется в ServiceController, но нет админ-интерфейса - ОСТАВИТЬ, так как используется)

### ✅ ИСПОЛЬЗУЕМЫЕ КОНТРОЛЛЕРЫ (оставить):
Все контроллеры в app/Http/Controllers/Api используются через routes/api.php

### ✅ ИСПОЛЬЗУЕМЫЕ МИГРАЦИИ (оставить):
Все миграции связаны с используемыми моделями. telegraph_bots и telegraph_chats - часть пакета DefStudio\Telegraph (оставить).

---

## ❌ К УДАЛЕНИЮ

### 1. НЕИСПОЛЬЗУЕМЫЕ АДМИН СТРАНИЦЫ (Vue компоненты)

**Файлы для удаления:**
- `resources/js/pages/admin/Subscription.vue` - страница "Страница в разработке", не используется в меню
- `resources/js/pages/admin/Versions.vue` - страница "Страница в разработке", не используется в меню
- `resources/js/pages/admin/Categories.vue` - страница "Страница в разработке", не используется в меню

**Причина удаления:**
- Эти страницы не упомянуты в AdminMenu.php
- Содержат только заглушку "Страница в разработке"
- Routes для них существуют, но функционал не реализован

### 2. НЕИСПОЛЬЗУЕМЫЕ МАРШРУТЫ (routes)

**Файл:** `resources/js/app.js`

**Удалить следующие маршруты из admin children:**
```javascript
{
    path: 'subscription',
    name: 'admin.subscription',
    component: () => import('./pages/admin/Subscription.vue'),
},
{
    path: 'versions',
    name: 'admin.versions',
    component: () => import('./pages/admin/Versions.vue'),
},
{
    path: 'categories',
    name: 'admin.categories',
    component: () => import('./pages/admin/Categories.vue'),
},
```

---

## ⚠️ ВАЖНЫЕ ЗАМЕЧАНИЯ

1. **AppCategory модель** - используется в ServiceController для работы с категориями заявителя, но нет отдельного админ-интерфейса для её управления. Модель и миграция должны остаться.

2. **telegraph_bots и telegraph_chats миграции** - это миграции пакета DefStudio\Telegraph, используются в TelegramService. ОСТАВИТЬ.

3. **Все остальные модели, контроллеры и миграции** используются в приложении и должны остаться.

---

## РЕЗЮМЕ УДАЛЕНИЯ

**Удалить:**
- 3 Vue файла (Subscription.vue, Versions.vue, Categories.vue)
- 3 маршрута из app.js (subscription, versions, categories)

**Всего файлов для удаления:** 3
**Файлов для редактирования:** 1 (app.js)

**НИ ОДНА МОДЕЛЬ, КОНТРОЛЛЕР ИЛИ МИГРАЦИЯ НЕ БУДЕТ УДАЛЕНА**, так как все они используются в приложении.





