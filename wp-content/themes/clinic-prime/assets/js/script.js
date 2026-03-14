function initScript() {

    mobileMenu();
    sliders();
    filters();
    fixedBut();
    modalImage()
    phoneMask();
    onlineModal();
    spoiler();

    // Ждем загрузки DOM
    document.addEventListener('DOMContentLoaded', function() {
        initVidjetToggle();
    });

    function mobileMenu() {
        var burger = document.querySelector(".dropdownBut");

        if (burger) {
            burger.addEventListener('click', function () {
                this.classList.toggle('open')
                document.querySelector('.dropdown').classList.toggle('open')
                document.querySelector('body').classList.toggle('fixed')
                document.querySelector('html').classList.toggle('fixed')
            })
        }
        
        // Обработчик для кнопки записи в мобильном меню
        var mobileAppointmentBtn = document.querySelector("#btn_appointment_mobile");
        if (mobileAppointmentBtn) {
            mobileAppointmentBtn.addEventListener('click', function() {
                // Закрываем мобильное меню
                var burger = document.querySelector(".dropdownBut");
                var dropdown = document.querySelector('.dropdown');
                
                if (burger && dropdown) {
                    burger.classList.remove('open');
                    dropdown.classList.remove('open');
                    document.querySelector('body').classList.remove('fixed');
                    document.querySelector('html').classList.remove('fixed');
                }
            });
        }
    }


    function sliders() {
        const specSlider = document.querySelector('.specSlider');

        if(specSlider) {
            const spec_Slider = new Swiper(specSlider, {
                slidesPerView: 1,
                spaceBetween: 32,
                navigation: {
                    nextEl: '.specSliderNext',
                    prevEl: '.specSliderPrev',
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                    },
                    580: {
                        slidesPerView: 2,
                    },
                    819: {
                        slidesPerView: 3,
                    },
                    1024: {
                        slidesPerView: 3,
                    },
                },

            })
        }

        const reviewSlider = document.querySelector('.reviewSlider');

        if(reviewSlider) {
            const review_Slider = new Swiper(reviewSlider, {
                slidesPerView: 1,
                spaceBetween: 10,
                loop: true,
                navigation: {
                    nextEl: '.specSliderNext',
                    prevEl: '.specSliderPrev',
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },

            })
        }

        //

        const specSliderInner = document.querySelector('.specSliderInner');

        if(specSliderInner) {
            const spec_Slider2 = new Swiper(specSliderInner, {
                slidesPerView: 1,
                spaceBetween: 24,
                navigation: {
                    nextEl: '.specInnerSlideNext',
                    prevEl: '.specInnerSlidePrev',
                },
                pagination: {
                    el: '.swiper-pagination2',
                    clickable: true
                },
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                    },
                    580: {
                        slidesPerView: 2,
                    },
                    819: {
                        slidesPerView: 2,
                    },
                    1069: {
                        slidesPerView: 3,
                    },
                },

            })
        }

        const photoSlider = document.querySelector('.photoSlider');

        if(photoSlider) {
            const spec_Slider4 = new Swiper(photoSlider, {
                slidesPerView: 'auto',
                spaceBetween: 8,
                navigation: {
                    nextEl: '.specInnerSlideNext2',
                    prevEl: '.specInnerSlidePrev2',
                },
                pagination: {
                    el: '.swiper-pagination3',
                    clickable: true
                },

            })
        }

        const aboutSlider = document.querySelector('.aboutSlider');

        if(aboutSlider) {
            const spec_Slider3 = new Swiper(aboutSlider, {
                effect: 'coverflow',
                centeredSlides: true,
                spaceBetween: 32,
                slidesPerView: 'auto',
                coverflowEffect: {
                    rotate: 0, // убрать вращение
                    stretch: 0, // убрать растяжение
                    depth: 100, // глубина эффекта
                    modifier: 2, // интенсивность эффекта
                    slideShadows: false, // убрать тени
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },
                navigation: {
                    nextEl: '.specSliderNext',
                    prevEl: '.specSliderPrev',
                },
                loop: true, // бесконечный цикл
                speed: 600, // скорость анимации
                breakpoints: {
                    320: {
                        spaceBetween: 24,
                    },
                    580: {
                        spaceBetween: 32,
                    }
                },
            })

        }
    }

    function fixedBut() {
        document.addEventListener('DOMContentLoaded', function() {
            const butFixed = document.querySelector('.butFixed');
            const innerSection = document.querySelector('.innerSection');

            if(butFixed && innerSection) {
                // Определяем отступ в зависимости от устройства
                const rootMargin = window.innerWidth < 819 ? '-100px 0px 0px 0px' : '-450px 0px 0px 0px';

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (!entry.isIntersecting) {
                            butFixed.classList.add('hidden');
                        } else {
                            butFixed.classList.remove('hidden');
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: rootMargin
                });

                observer.observe(innerSection);
            }
        });
    }

    function filters() {
        var filterBtns = document.querySelectorAll('.page_filters__filter--main--btn');
        var filterItems = document.querySelectorAll('.page_filters__filter--main--items--block--item');
        var filterResetLink = document.querySelector('.filterResetLink');

        // Функция для добавления поиска в каждый фильтр
        function initSearchInFilters() {
            var searchInputs = document.querySelectorAll('.filter-search-input');
            
            searchInputs.forEach(function(searchInput) {
                // Удаляем старые обработчики, чтобы не навешивать повторно
                searchInput.removeEventListener('input', handleSearchInput);
                searchInput.addEventListener('input', handleSearchInput);
            });
        }

        // Обработчик поиска
        function handleSearchInput(e) {
            var searchText = e.target.value.toLowerCase().trim();
            var filterBlock = e.target.closest('.page_filters__filter--main--items');
            var itemsBlock = filterBlock.querySelector('.page_filters__filter--main--items--block');
            var items = itemsBlock.querySelectorAll('.page_filters__filter--main--items--block--item');
            
            // Пропускаем первый элемент "Все проблемы" (индекс 0)
            for (var i = 1; i < items.length; i++) {
                var item = items[i];
                var label = item.querySelector('label');
                var itemText = label.textContent.toLowerCase();
                
                if (searchText === '' || itemText.includes(searchText)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            }
        }

        // Обновляем обработчики кликов по кнопкам фильтров
        Array.from(filterBtns).forEach(function(section) {
            section.addEventListener('click', function(el) {
                var diff = Array.from(filterBtns).filter(element => element !== section);
                diff.forEach(function(otherEl) {
                    otherEl.parentNode.classList.remove('opened');
                });
                
                section.parentNode.classList.toggle('opened');
                
                // Если фильтр открылся, очищаем поиск и фокусируемся на поле ввода
                if (section.parentNode.classList.contains('opened')) {
                    var searchInput = section.parentNode.querySelector('.filter-search-input');
                    if (searchInput) {
                        searchInput.value = ''; // Очищаем поле
                        searchInput.focus(); // Ставим фокус
                        
                        // Показываем все элементы
                        var items = section.parentNode.querySelectorAll('.page_filters__filter--main--items--block--item');
                        items.forEach(function(item) {
                            item.classList.remove('hidden');
                        });
                    }
                }
            });
        });

        // Обновляем обработчики кликов по элементам фильтра
        document.addEventListener('click', function(e) {
            var filterItem = e.target.closest('.page_filters__filter--main--items--block--item');
            
            if (filterItem) {
                var filterBlock = filterItem.closest('.page_filters__filter--main');
                var filterBtnSpan = filterBlock.querySelector('.page_filters__filter--main--btn span');
                var label = filterItem.querySelector('label');
                
                if (label && filterBtnSpan) {
                    filterBtnSpan.innerHTML = label.innerHTML;
                    filterBlock.classList.remove('opened');
                    
                    // Очищаем поиск при выборе значения
                    var searchInput = filterBlock.querySelector('.filter-search-input');
                    if (searchInput) {
                        searchInput.value = '';
                        
                        // Показываем все элементы
                        var items = filterBlock.querySelectorAll('.page_filters__filter--main--items--block--item');
                        items.forEach(function(item) {
                            item.classList.remove('hidden');
                        });
                    }
                }
            }
        });

        // Закрытие при клике вне фильтра
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.page_filters__filter--main')) {
                filterBtns.forEach(function(btn) {
                    btn.parentNode.classList.remove('opened');
                });
            }
        });

        // Инициализация поиска при загрузке
        initSearchInFilters();

        // AJAX поиск и фильтрация
        var searchInput = document.querySelector('.specItemsFilters input[name="search"]');
        var radioButtons = document.querySelectorAll('.specItemsFilters input[type="radio"]');
        var searchTimeout;
        

        // Функция для отправки AJAX запроса
        function sendAjaxRequest() {
            var params = new URLSearchParams();
            var hasFilters = false;
            
            // Добавляем action для WordPress AJAX
            params.append('action', 'clinic_filter_doctors');
            
            // Добавляем поисковый запрос (даже если он пустой)
            if(searchInput) {
                if(searchInput.value.length >= 3) {
                    params.append('search', searchInput.value);
                    hasFilters = true;
                } else if(searchInput.value.length === 0) {
                    // Если поле очищено, все равно отправляем запрос для сброса
                    hasFilters = true;
                }
            }
            
            // Добавляем выбранные фильтры
            radioButtons.forEach(function(radio) {
                if(radio.checked) {
                    params.append(radio.name, radio.value);
                    hasFilters = true;
                }
            });
            
            // Обновляем URL без перезагрузки страницы
            updateURL(params);
            
            // Если нет фильтров, не отправляем AJAX запрос
            if(!hasFilters) {
                return;
            }
            
            // Показываем индикатор загрузки
            var specItemsWrap = document.querySelector('.specItems_wrap');
            if(specItemsWrap) {
                specItemsWrap.style.opacity = '0.5';
                specItemsWrap.style.pointerEvents = 'none';
            }
            
            // Отправляем AJAX запрос
            fetch(clinic_ajax.ajax_url + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Проверяем успешность ответа
                if (data.success) {
                    // Обновляем контент specItems_wrap
                    if (data.data.specItems_wrap && specItemsWrap) {
                        specItemsWrap.innerHTML = data.data.specItems_wrap;
                    }

                    var currentInnerPageMiddle = document.querySelector('.bottomTextResults');
                    if( data.data.found == false ) {
                        currentInnerPageMiddle.style.display = 'none';
                    } else {
                        currentInnerPageMiddle.style.display = 'block';
                    }
                } else {
                    console.error('Ошибка в ответе сервера:', data);
                }
                
                // Убираем индикатор загрузки
                if(specItemsWrap) {
                    specItemsWrap.style.opacity = '1';
                    specItemsWrap.style.pointerEvents = 'auto';
                }
            })
            .catch(error => {
                console.error('Ошибка AJAX запроса:', error);
                // Убираем индикатор загрузки в случае ошибки
                if(specItemsWrap) {
                    specItemsWrap.style.opacity = '1';
                    specItemsWrap.style.pointerEvents = 'auto';
                }
            });
        }

        // Функция для обновления URL без перезагрузки страницы
        function updateURL(params) {
            var url = new URL(window.location);
            
            // Очищаем существующие параметры поиска
            url.searchParams.delete('search');
            url.searchParams.delete('problems');
            url.searchParams.delete('specs');
            
            // Добавляем новые параметры (кроме action), но только если они не пустые и не равны 0
            for (var [key, value] of params) {
                if (key !== 'action' && value && value !== '0' && value.trim() !== '') {
                    url.searchParams.set(key, value);
                }
            }
            
            // Обновляем URL в браузере без перезагрузки
            window.history.pushState({}, '', url);
        }

        // Функция для загрузки параметров из URL при загрузке страницы
        function loadParamsFromURL() {
            var urlParams = new URLSearchParams(window.location.search);
            var hasParams = false;
            
            // Загружаем поисковый запрос
            var searchValue = urlParams.get('search');
            if(searchValue && searchInput) {
                searchInput.value = searchValue;
                hasParams = true;
            }
            
            // Загружаем выбранные фильтры
            var problemsValue = urlParams.get('problems');
            var specsValue = urlParams.get('specs');
            
            if(problemsValue || specsValue) {
                radioButtons.forEach(function(radio) {
                    if((radio.name === 'problems' && radio.value === problemsValue) ||
                       (radio.name === 'specs' && radio.value === specsValue)) {
                        radio.checked = true;
                        hasParams = true;
                        
                        // Обновляем текст кнопки фильтра
                        var filterBtn = radio.closest('.page_filters__filter--main');
                        if(filterBtn) {
                            var btnSpan = filterBtn.querySelector('.page_filters__filter--main--btn span');
                            var label = radio.querySelector('label');
                            if(btnSpan && label) {
                                btnSpan.innerHTML = label.innerHTML;
                            }
                        }
                    }
                });
            }
            
            // Если есть параметры, НЕ отправляем AJAX запрос, так как данные уже загружены на сервере
            // Просто восстанавливаем состояние формы
            if(hasParams) {
                // Данные уже загружены на сервере, просто восстанавливаем состояние формы
                console.log('Параметры найдены в URL, данные уже загружены на сервере');
            } else {
                // Убеждаемся что innerPageMiddle видим при загрузке без параметров
                var currentInnerPageMiddle = document.querySelector('.innerPageMiddle');
                if(currentInnerPageMiddle) {
                    currentInnerPageMiddle.style.display = 'block';
                }
            }
        }

        // Обработчик для поля поиска
        if(searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                
                if(this.value.length >= 3) {
                    searchTimeout = setTimeout(function() {
                        sendAjaxRequest();
                    }, 500); // Задержка 500мс для избежания частых запросов
                } else if(this.value.length === 0) {
                    // Если поле очищено, отправляем запрос для сброса
                    sendAjaxRequest();
                }
            });
        }

        // Обработчик для радио кнопок
        radioButtons.forEach(function(radio) {
            radio.addEventListener('change', function() {
                sendAjaxRequest();
            });
        });

        // Сброс фильтров
        if(filterResetLink) {
            filterResetLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Сбрасываем все радио кнопки
                radioButtons.forEach(function(radio) {
                    radio.checked = false;
                });
                
                // Сбрасываем поле поиска
                if(searchInput) {
                    searchInput.value = '';
                }
                
                // Сбрасываем текст кнопок фильтров на исходный
                var filterBtnSpans = document.querySelectorAll('.page_filters__filter--main--btn span');
                filterBtnSpans.forEach(function(span, index) {
                    if(index === 0) {
                        span.innerHTML = 'Что вас беспокоит?';
                    } else if(index === 1) {
                        span.innerHTML = 'Специальность';
                    }
                });
                
                // Закрываем все открытые фильтры
                filterBtns.forEach(function(btn) {
                    btn.parentNode.classList.remove('opened');
                });
                
                // Показываем innerPageMiddle обратно
                var currentInnerPageMiddle = document.querySelector('.innerPageMiddle');
                if(currentInnerPageMiddle) {
                    currentInnerPageMiddle.style.display = 'block';
                }
                
                // Очищаем URL
                var url = new URL(window.location);
                url.searchParams.delete('search');
                url.searchParams.delete('problems');
                url.searchParams.delete('specs');
                window.history.pushState({}, '', url);
                
                // Отправляем AJAX запрос для получения всех врачей
                var params = new URLSearchParams();
                params.append('action', 'clinic_filter_doctors');
                
                // Показываем индикатор загрузки
                var specItemsWrap = document.querySelector('.specItems_wrap');
                if(specItemsWrap) {
                    specItemsWrap.style.opacity = '0.5';
                    specItemsWrap.style.pointerEvents = 'none';
                }
                
                // Отправляем AJAX запрос
                fetch(clinic_ajax.ajax_url + '?' + params.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Проверяем успешность ответа
                    if (data.success) {
                        // Обновляем контент specItems_wrap
                        if (data.data.specItems_wrap && specItemsWrap) {
                            specItemsWrap.innerHTML = data.data.specItems_wrap;
                        }

                        var currentInnerPageMiddle = document.querySelector('.bottomTextResults');
                        if( data.data.found == false ) {
                            currentInnerPageMiddle.style.display = 'none';
                        } else {
                            currentInnerPageMiddle.style.display = 'block';
                        }
                    } else {
                        console.error('Ошибка в ответе сервера при сбросе:', data);
                    }
                    
                    // Убираем индикатор загрузки
                    if(specItemsWrap) {
                        specItemsWrap.style.opacity = '1';
                        specItemsWrap.style.pointerEvents = 'auto';
                    }
                })
                .catch(error => {
                    console.error('Ошибка AJAX запроса:', error);
                    // Убираем индикатор загрузки в случае ошибки
                    if(specItemsWrap) {
                        specItemsWrap.style.opacity = '1';
                        specItemsWrap.style.pointerEvents = 'auto';
                    }
                });
            });
        }

        // Загружаем параметры из URL при инициализации
        loadParamsFromURL();
    }

    function spoiler() {
        const faqItems = document.querySelectorAll('.faqItem');

        if (faqItems) {
            faqItems.forEach(item => {
                const title = item.querySelector('.faqItemTitle');

                title.addEventListener('click', function() {
                    // Переключаем текущий спойлер
                    item.classList.toggle('active');
                });
            });

        }
    }

    function initVidjetToggle() {
        const vidjetOpen = document.querySelector('.fixed-vidjet-open');
        const vidjetClose = document.querySelector('.fixed-vidjet-close');
        const vidjetMini = document.querySelector('.fixed-vidjet-mini');

        if (!vidjetOpen || !vidjetClose || !vidjetMini) return;

        vidjetClose.addEventListener('click', function() {
            vidjetOpen.classList.remove('active');
            vidjetMini.classList.add('active');
        });

        vidjetMini.addEventListener('click', function() {
            vidjetMini.classList.remove('active');
            vidjetOpen.classList.add('active');
        });

        // Автоматическое разворачивание через 10 секунд
        setTimeout(function() {
            // Проверяем, не открыт ли уже виджет и не закрыт ли он навсегда (если есть такая логика)
            if (!vidjetOpen.classList.contains('active') && vidjetMini.classList.contains('active')) {
                console.log('Auto opening vidjet after 10 seconds');
                vidjetMini.classList.remove('active');
                vidjetOpen.classList.add('active');
            }
        }, 10000); // 10000 миллисекунд = 10 секунд
    }
    

    function phoneMask() {
        
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.querySelector('input[type="tel"]');
            if(phoneInput != null) {
                const im = new Inputmask('+7 (999) 999-99-99', {
                    placeholder: '_',
                    showMaskOnHover: false,
                    clearIncomplete: true
                });
                im.mask(phoneInput);    
            }
        });
    }

    function modalImage() {
        const modal = document.getElementById('modalImage');
        const closeBtn = document.querySelector('.modalWrapClose');
        
        if (!modal) return;
        
        // Проверяем куку
        const cookieName = 'modalImageClosed';
        if (document.cookie.includes(`${cookieName}=true`)) {
            return; // Кука есть - модалка не показывается
        }
        
        // Показ модалки с задержкой 5 секунд
        setTimeout(() => {
            modal.classList.add('active');
        }, 5000);
        
        // Функция для установки куки на 24 часа
        function setCookie() {
            const date = new Date();
            date.setTime(date.getTime() + (24 * 60 * 60 * 1000)); // 24 часа
            document.cookie = `${cookieName}=true; expires=${date.toUTCString()}; path=/`;
        }
        
        // Закрытие модалки и установка куки
        function closeModal() {
            modal.classList.remove('active');
            setCookie();
        }
        
        // Закрытие по клику на крестик
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        
        // Закрытие по клику вне модалки (по backdrop)
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        
        // Закрытие по клику на пустое место в modalImageWrap (если нужно)
        modal.addEventListener('click', function(event) {
            if (event.target.closest('.modalImage')) {
                return; // Не закрываем при клике на изображение
            }
            if (event.target.closest('.modalImageWrap')) {
                closeModal();
            }
        });
    }

    function onlineModal() {
        document.querySelectorAll('.specItemOnlineLabel').forEach(label => {
            const modal = label.previousElementSibling;
            
            if (modal && modal.classList.contains('specItemOnlineModal')) {
                // Показать при наведении на label
                label.addEventListener('mouseenter', () => {
                    modal.style.visibility = 'visible';
                    modal.style.opacity = '1';
                });
                
                // Скрыть когда ушли с обоих элементов
                [label, modal].forEach(element => {
                    element.addEventListener('mouseleave', () => {
                        // Небольшая задержка для плавности
                        setTimeout(() => {
                            if (!label.matches(':hover') && !modal.matches(':hover')) {
                                modal.style.visibility = 'hidden';
                                modal.style.opacity = '0';
                            }
                        }, 100);
                    });
                });
            }
        });
    }




}

initScript();





