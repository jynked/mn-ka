<div class="w-full px-3 sm:px-4 md:px-5">
    <header class="rounded-lg h-[60px] flex items-center px-4 sm:px-5 gap-3 sm:gap-5 bg-[#D2E8BE] mt-3 sm:mt-3 w-full max-w-[1200px] mx-auto shadow-sm">
    <!-- Logo -->
    <div class="flex items-center mr-3 sm:mr-5">
        <a href="/" class="font-semibold text-base sm:text-lg text-black no-underline">
            mnka
        </a>
    </div>
    
    <!-- Burger Menu Button -->
    <button type="button" id="burger-btn" class="flex items-center gap-2 sm:gap-[10px] ml-auto md:ml-5 cursor-pointer bg-transparent border-0 p-2 sm:p-0 -mr-2 sm:mr-0">
        <svg width="25" height="17" viewBox="0 0 25 17" fill="none" xmlns="http://www.w3.org/2000/svg">
            <line x1="1.05" y1="1.95" x2="23.95" y2="1.95" stroke="black" stroke-width="2.1" stroke-linecap="round"/>
            <line x1="1.05" y1="8.95" x2="23.95" y2="8.95" stroke="black" stroke-width="2.1" stroke-linecap="round"/>
            <line x1="1.05" y1="15.9501" x2="23.95" y2="15.9501" stroke="black" stroke-width="2.1" stroke-linecap="round"/>
        </svg>
        <span class="font-medium text-base leading-5 text-black hidden md:block">Меню</span>
    </button>

    <!-- Middle Section (Desktop) -->
    <div class="hidden md:flex bg-[#C9E1B5] rounded-lg flex-1 items-center px-[15px] gap-5">
        <a href="/services" class="font-semibold text-xs leading-[15px] text-black bg-white rounded-lg inline-flex items-center justify-center outline-none no-underline shadow-[0_4px_6px_rgba(65,132,144,0.1),0_1px_3px_rgba(0,0,0,0.08)] cursor-pointer select-none h-[45px] transition-all duration-200 border-0 px-[15px] whitespace-nowrap mr-5 hover:scale-105">
            Все услуги
        </a>
        <ul class="flex items-center gap-5 list-none m-0 p-0 flex-wrap" id="header-menu">
            <!-- Menu items will be populated by JS -->
        </ul>
    </div>

    <!-- Search (Desktop) -->
    <div class="hidden md:block ml-auto">
        <div class="relative flex items-center h-[38px] bg-white rounded-lg min-w-[250px] px-[15px] pr-10">
            <input 
                type="text" 
                id="search-input" 
                placeholder="Поиск по сайту" 
                class="outline-none border-0 w-full p-0 bg-transparent text-sm text-black placeholder:text-[#999]"
            >
            <svg 
                id="search-icon" 
                class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer" 
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
                    fill="black"
                />
            </svg>
        </div>
    </div>
</header>
</div>

<!-- Burger Menu -->
<div class="fixed top-0 left-0 w-full h-full overflow-y-auto bg-[#6C7B6D] z-[5] hidden opacity-0 translate-y-full transition-all duration-300 ease-out" id="burger-menu">
    <div class="w-full max-w-[1200px] mx-auto px-4 sm:px-[15px]">
        <div class="flex justify-between items-center mt-8 sm:mt-12 gap-3 sm:gap-5 burger-menu-content opacity-0 translate-y-4 transition-all duration-300 ease-out delay-100">
            <div class="w-full md:w-1/4 pr-0">
                <div class="relative">
                    <input 
                        type="text" 
                        id="burger-search-input" 
                        placeholder="Поиск по сайту" 
                        class="w-full py-2.5 sm:py-[10px] pr-10 pl-3 sm:pl-[10px] border-0 rounded-lg outline-none text-sm sm:text-base bg-gray-200 text-gray-700 placeholder:text-gray-500 focus:bg-gray-100 focus:ring-2 focus:ring-white/30 transition-all duration-200 shadow-inner"
                    >
                    <svg 
                        id="burger-search-icon" 
                        class="absolute right-3 sm:right-[10px] top-1/2 -translate-y-1/2 flex items-center cursor-pointer pointer-events-none z-10" 
                        width="18" 
                        height="18"
                        viewBox="0 0 18 18" 
                        fill="none" 
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path 
                            fill-rule="evenodd" 
                            clip-rule="evenodd"
                            d="M11.015 12.3306C9.66079 13.4127 7.94372 13.9351 6.21641 13.7906C4.48911 13.6461 2.88269 12.8457 1.72708 11.5537C0.571478 10.2617 -0.0455851 8.57623 0.00262643 6.84347C0.050838 5.1107 0.760665 3.46216 1.98632 2.23643C3.21198 1.01071 4.86042 0.300841 6.59309 0.252627C8.32576 0.204412 10.0111 0.821511 11.303 1.97718C12.5949 3.13285 13.3953 4.73937 13.5398 6.46677C13.6843 8.19418 13.1619 9.91134 12.0799 11.2656L17.2602 16.4451C17.3342 16.5141 17.3936 16.5973 17.4348 16.6897C17.476 16.7822 17.4981 16.882 17.4999 16.9831C17.5017 17.0843 17.4831 17.1848 17.4452 17.2787C17.4073 17.3725 17.3509 17.4577 17.2793 17.5293C17.2077 17.6008 17.1225 17.6573 17.0287 17.6952C16.9349 17.7331 16.8344 17.7517 16.7332 17.7499C16.632 17.7481 16.5322 17.726 16.4398 17.6848C16.3474 17.6436 16.2642 17.5842 16.1952 17.5102L11.015 12.3306ZM3.05173 10.7612C2.31432 10.0237 1.81208 9.0841 1.60849 8.0612C1.4049 7.0383 1.50909 5.978 1.90789 5.01431C2.30669 4.05061 2.98221 3.22677 3.84907 2.6469C4.71593 2.06704 5.73522 1.75717 6.77812 1.75647C7.82103 1.75577 8.84073 2.06426 9.70837 2.64296C10.576 3.22166 11.2526 4.04459 11.6527 5.00775C12.0528 5.97091 12.1585 7.03107 11.9562 8.05424C11.754 9.07741 11.253 10.0177 10.5166 10.7562L10.5116 10.7612L10.5066 10.7652C9.51705 11.7525 8.17612 12.3067 6.77833 12.3059C5.38053 12.3052 4.0402 11.7496 3.05173 10.7612Z"
                            fill="#909090"
                        />
                    </svg>
                </div>
            </div>
            <button 
                type="button"
                id="burger-close" 
                class="cursor-pointer bg-transparent border-0 p-2 sm:p-0 w-8 h-8 sm:w-10 sm:h-10 md:w-[40px] md:h-[40px] flex-shrink-0"
            >
                <svg 
                    width="40" 
                    height="40" 
                    viewBox="0 0 40 40"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M10.4 32L8 29.6L17.6 20L8 10.4L10.4 8L20 17.6L29.6 8L32 10.4L22.4 20L32 29.6L29.6 32L20 22.4L10.4 32Z"
                        fill="white"
                    />
                </svg>
            </button>
        </div>
        <div class="mt-6 sm:mt-12 burger-menu-content opacity-0 translate-y-4 transition-all duration-300 ease-out delay-200">
            <div class="font-medium text-xl sm:text-2xl md:text-[28px] leading-6 sm:leading-[34px] text-white mb-3 sm:mb-2">Все услуги</div>
            <div id="all-services-container" class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                <!-- Services will be populated by JS -->
            </div>
        </div>
        <div class="mt-6 sm:mt-12 pb-6 sm:pb-0 burger-menu-content opacity-0 translate-y-4 transition-all duration-300 ease-out delay-300" id="products-container">
            <!-- Products will be populated by JS -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Header data
    const headerData = {
        search: '',
        headerMenu: [
            { slug: '/products', name: 'Продуктовые направления' },
            { slug: '/cases', name: 'Кейсы' },
            { slug: '/about', name: 'О нас' },
            { slug: '/contact', name: 'Контакты' }
        ],
        allService: [
            { slug: '/service1', name: 'Услуга 1' },
            { slug: '/service2', name: 'Услуга 2' },
            { slug: '/service3', name: 'Услуга 3' }
        ],
        products: [
            {
                slug: '',
                name: 'Склады',
                items: [
                    { slug: '/warehouse/light', name: 'Light Industrial до 5 га' },
                    { slug: '/warehouse/large', name: 'Склады от 5 га' },
                    { slug: '/warehouse/mkd', name: 'МКД / Офисы' },
                    { slug: '/warehouse/izhs', name: 'ИЖС' }
                ]
            },
            {
                slug: '',
                name: 'Производство',
                items: [
                    { slug: '/production/cat1', name: 'Производство 1-й категории' },
                    { slug: '/production/cat2', name: 'Производство 2-й категории' },
                    { slug: '/production/cat3', name: 'Производство 3-й категории' }
                ]
            },
            {
                slug: '',
                name: 'Придорожный сервис',
                items: [
                    { slug: '/road/carwash', name: 'Участок под автомойку' },
                    { slug: '/road/gas', name: 'Участок под АЗС' },
                    { slug: '/road/charging', name: 'Участок под зарядочную станцию' },
                    { slug: '/road/food', name: 'Участок под общепит' },
                    { slug: '/road/service', name: 'Автосервис' },
                    { slug: '/road/shop', name: 'Магазин' }
                ]
            },
            {
                slug: '',
                name: 'Коммерция',
                items: [
                    { slug: '/commerce/business', name: 'Бизнес центр' },
                    { slug: '/commerce/mfc', name: 'МФК' },
                    { slug: '/commerce/mall', name: 'Торговый центр' }
                ]
            },
            {
                slug: '',
                name: 'Ритейл',
                items: [
                    { slug: '/retail/building', name: 'Торговое здание' },
                    { slug: '/retail/free', name: 'Помещение свободного назначения' },
                    { slug: '/retail/x5', name: 'Договор аренды земельного участка с договором X5-Retail' }
                ]
            },
            {
                slug: '',
                name: 'Рекреация',
                items: [
                    { slug: '/recreation/glamping', name: 'Участок под глэмпинг' },
                    { slug: '/recreation/hotel', name: 'Участок под гостиничный комплекс' },
                    { slug: '/recreation/pier', name: 'Причалы' },
                    { slug: '/recreation/coast', name: 'Аренда участка прибрежной зоны' },
                    { slug: '/recreation/water', name: 'Аренда водоемов' },
                    { slug: '/recreation/forest', name: 'Лесные участки' },
                    { slug: '/recreation/island', name: 'Острова' }
                ]
            }
        ]
    };

    // Initialize header menu
    function initHeaderMenu() {
        const menuContainer = document.getElementById('header-menu');
        if (menuContainer && headerData.headerMenu.length > 0) {
            menuContainer.innerHTML = headerData.headerMenu.map(nav =>
                `<li class="font-medium text-xs leading-[15px] list-none m-0 p-0">
                    <a href="${nav.slug}" class="text-black pb-[3px] border-b border-transparent transition-all duration-300 no-underline whitespace-nowrap hover:border-black">
                        ${nav.name}
                    </a>
                </li>`
            ).join('');
        }
    }

    // Initialize burger menu services
    function initBurgerMenu() {
        const servicesContainer = document.getElementById('all-services-container');
        if (servicesContainer && headerData.allService.length > 0) {
            servicesContainer.innerHTML = headerData.allService.map(service =>
                `<div class="w-full">
                    <a href="${service.slug}" class="font-normal text-sm md:text-[14px] leading-5 md:leading-[17px] text-[#F4F6FC] no-underline block mb-3 sm:mb-2.5 py-1">
                        ${service.name}
                    </a>
                </div>`
            ).join('');
        }

        const productsContainer = document.getElementById('products-container');
        if (productsContainer && headerData.products.length > 0) {
            const isMobile = window.innerWidth <= 767;
            productsContainer.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">${headerData.products.map(product =>
                `<div class="mt-3 ${isMobile ? 'border-b border-white/20 pb-4 sm:pb-3' : ''}">
                    <div class="font-medium text-base sm:text-lg md:text-2xl md:text-[28px] leading-5 sm:leading-6 md:leading-[34px] text-white mb-3 sm:mb-4 ${isMobile ? 'flex items-center gap-2 sm:gap-[10px] cursor-pointer nav-slide-btn py-1' : ''}">
                        ${product.name}
                        ${isMobile ? `
                            <div class="event transition-transform duration-600">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M7.29374 5.29331C7.48124 5.10604 7.73541 5.00085 8.00041 5.00085C8.26541 5.00085 8.51957 5.10604 8.70708 5.29331L12.4791 9.06397C12.5719 9.15686 12.6456 9.26713 12.6958 9.38848C12.746 9.50982 12.7719 9.63988 12.7718 9.77121C12.7718 9.90254 12.7459 10.0326 12.6956 10.1539C12.6453 10.2752 12.5716 10.3855 12.4787 10.4783C12.3859 10.5712 12.2756 10.6448 12.1542 10.695C12.0329 10.7453 11.9028 10.7711 11.7715 10.7711C11.6402 10.771 11.5101 10.7451 11.3888 10.6948C11.2675 10.6446 11.1573 10.5709 11.0644 10.478L8.00041 7.41464L4.93641 10.4786C4.84421 10.5742 4.7339 10.6504 4.61192 10.7029C4.48994 10.7554 4.35873 10.783 4.22595 10.7842C4.09318 10.7854 3.96149 10.7602 3.83857 10.71C3.71565 10.6598 3.60396 10.5856 3.51002 10.4917C3.41609 10.3979 3.34178 10.2862 3.29144 10.1634C3.2411 10.0405 3.21574 9.90883 3.21683 9.77605C3.21792 9.64327 3.24545 9.51204 3.2978 9.39001C3.35015 9.26798 3.42628 9.1576 3.52174 9.06531L7.29374 5.29331Z"
                                          fill="#F4F6FC"/>
                                </svg>
                            </div>
                        ` : ''}
                    </div>
                    <div class="slide-child-container ${isMobile ? 'hidden' : ''}">
                        ${product.items.map(child =>
                            `<div class="mt-2 p-0">
                                <a href="${child.slug}" class="font-normal text-sm md:text-[14px] leading-5 md:leading-[17px] text-[#F4F6FC] no-underline block mb-2 sm:mb-2.5 py-1">
                                    ${child.name}
                                </a>
                            </div>`
                        ).join('')}
                    </div>
                </div>`
            ).join('')}</div>`;
        }
    }

    // Burger menu toggle
    function toggleBurger() {
        const burgerMenu = document.getElementById('burger-menu');
        if (burgerMenu) {
            if (burgerMenu.classList.contains('hidden')) {
                // Открываем меню
                burgerMenu.classList.remove('hidden');
                burgerMenu.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Запускаем анимацию появления
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        burgerMenu.classList.remove('opacity-0', 'translate-y-full');
                        burgerMenu.classList.add('opacity-100', 'translate-y-0');
                        
                        // Анимация для внутреннего контента
                        const contentElements = burgerMenu.querySelectorAll('.burger-menu-content');
                        contentElements.forEach((el, index) => {
                            setTimeout(() => {
                                el.classList.remove('opacity-0', 'translate-y-4');
                                el.classList.add('opacity-100', 'translate-y-0');
                            }, 100 + (index * 50));
                        });
                    });
                });
            } else {
                // Скрываем внутренний контент
                const contentElements = burgerMenu.querySelectorAll('.burger-menu-content');
                contentElements.forEach((el) => {
                    el.classList.remove('opacity-100', 'translate-y-0');
                    el.classList.add('opacity-0', 'translate-y-4');
                });
                
                // Закрываем меню с анимацией
                setTimeout(() => {
                    burgerMenu.classList.remove('opacity-100', 'translate-y-0');
                    burgerMenu.classList.add('opacity-0', 'translate-y-full');
                    
                    setTimeout(() => {
                        burgerMenu.classList.add('hidden');
                        burgerMenu.style.display = 'none';
                        document.body.style.overflow = '';
                    }, 300);
                }, 50);
            }
        }
    }

    // Nav slide toggle
    function navSlide(e) {
        const slideParent = e.target.closest('.nav-slide-btn')?.closest('[class*="mt-3"]');
        if (slideParent) {
            const event = slideParent.querySelector('.event');
            const slideChildren = slideParent.querySelector('.slide-child-container');

            if (event) {
                event.classList.toggle('rotate-180');
            }

            if (slideChildren) {
                slideChildren.classList.toggle('hidden');
            }
        }
    }

    // Search function
    function getSearch() {
        const searchInput = document.getElementById('search-input');
        const burgerSearchInput = document.getElementById('burger-search-input');
        const searchValue = searchInput ? searchInput.value : (burgerSearchInput ? burgerSearchInput.value : '');
        console.log('Search:', searchValue);
        // Add your search logic here
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initHeaderMenu();
        initBurgerMenu();

        // Burger button
        const burgerBtn = document.getElementById('burger-btn');
        if (burgerBtn) {
            burgerBtn.addEventListener('click', toggleBurger);
        }

        // Burger close button
        const burgerClose = document.getElementById('burger-close');
        if (burgerClose) {
            burgerClose.addEventListener('click', toggleBurger);
        }

        // Search icons
        const searchIcon = document.getElementById('search-icon');
        if (searchIcon) {
            searchIcon.addEventListener('click', getSearch);
        }

        const burgerSearchIcon = document.getElementById('burger-search-icon');
        if (burgerSearchIcon) {
            burgerSearchIcon.addEventListener('click', getSearch);
        }

        // Nav slide buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.nav-slide-btn')) {
                navSlide(e);
            }
        });

        // Close burger menu when clicking on links
        const burgerMenu = document.getElementById('burger-menu');
        if (burgerMenu) {
            burgerMenu.addEventListener('click', function(e) {
                if (e.target.tagName === 'A') {
                    toggleBurger();
                }
            });
        }

        // Search input enter key
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    getSearch();
                }
            });
        }

        const burgerSearchInput = document.getElementById('burger-search-input');
        if (burgerSearchInput) {
            burgerSearchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    getSearch();
                }
            });
        }
    });
</script>
@endpush

