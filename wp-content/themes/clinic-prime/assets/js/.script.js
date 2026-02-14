function initScript() {

    mobileMenu();
    sliders();
    filters();
    fixedBut();
    modalForm();
    modalImage()
    phoneMask();
    onlineModal()
    spoiler();

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

        Array.from(filterBtns).forEach(function(section) {
            section.addEventListener('click', function(el) {
                var diff = Array.from(filterBtns).filter(element => element !== section)
                diff.forEach(function(otherEl) {
                    otherEl.parentNode.classList.remove('opened')
                })
                section.parentNode.classList.toggle('opened')
            });
        });

        for (i = 0; i < filterItems.length; i++) {
            filterItems[i].addEventListener('click', function() {
                this.parentNode.parentNode.parentNode.classList.remove('opened')
                this.parentNode.parentNode.parentNode.querySelector('.page_filters__filter--main--btn span').innerHTML = this.querySelector('label').innerHTML
            });
        }

        document.addEventListener('click', function(e) {
            if(!e.target.classList.contains('page_filters__filter--main--btn')) {
                for (i = 0; i < filterBtns.length; i++) {
                    filterBtns[i].parentNode.classList.remove('opened')
                }
            }
        })

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

    function modalForm() {
        document.addEventListener('DOMContentLoaded', function() {

            const modalForm = document.getElementById('modalForm');
            const rnovaForm = document.getElementById('rnovaForm');
            const rnovaFormLk = document.getElementById('rnovaFormLk');
            const modalThanks = document.getElementById('modalThanks');
            const openButton = document.querySelector('.butModal');
            const rnovaButtons = document.querySelectorAll('.butModalRnova');
            const rnovaLkButtons = document.querySelectorAll('.butModalRnovaLk');
            const closeButtons = document.querySelectorAll('.modalWrapClose');
            const submitBut = document.querySelector('.formRightButton .butPrimary');
            const checkbox = document.querySelector('.checkboxInput');
            const checkboxText = document.querySelector('.checkboxText');
            
            // Переменная для выбора метода загрузки (true = iframe, false = ajax)
            let useIframeMethod = true; // По умолчанию используем iframe
            
            // Функция для переключения между методами (доступна глобально для тестирования)
            window.toggleRnovaMethod = function() {
                useIframeMethod = !useIframeMethod;
                console.log('Метод загрузки Rnova изменен на:', useIframeMethod ? 'iframe' : 'ajax');
                return useIframeMethod;
            };
            
            // Функция для установки конкретного метода
            window.setRnovaMethod = function(method) {
                useIframeMethod = method === 'iframe';
                console.log('Метод загрузки Rnova установлен на:', useIframeMethod ? 'iframe' : 'ajax');
                return useIframeMethod;
            };
            
            // Функция для очистки контента Rnova формы
            window.clearRnovaContent = function() {
                if (rnovaForm) {
                    // Сбрасываем флаг загрузки iframe
                    window.rnovaIframeLoaded = false;
                    const loader = rnovaForm.querySelector('#rnova-a_form_loader');
                    if (loader) {
                        loader.innerHTML = '';
                    }
                    // Удаляем iframe если есть
                    const iframe = rnovaForm.querySelector('#rnova-iframe');
                    if (iframe) {
                        iframe.remove();
                    }
                    console.log('Контент Rnova формы очищен');
                }
            };
            
            function openModal(modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Блокируем прокрутку страницы
            }
            
            function closeModal(modal) {
                modal.classList.remove('active');
                document.body.style.overflow = ''; // Восстанавливаем прокрутку
            }
            
            if(openButton != null) {
                openButton.addEventListener('click', function(e) {
                    e.preventDefault(); // Предотвращаем переход по ссылке
                    openModal(modalForm);
                });
            }
            
            [modalForm, rnovaForm, rnovaFormLk, modalThanks].forEach(modal => {
                if(modal != null) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) { // Клик именно на фон, а не на содержимое
                            closeModal(modal);
                        }
                    });
                }
            });
            
            closeButtons.forEach(button => {
                if(button != null) {
                    button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        closeModal(modal);
                    }
                    });
                }
            });
            
            rnovaButtons.forEach(button => {
                if(button != null) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault(); // Предотвращаем переход по ссылке
                        
                        // Собираем все data-атрибуты
                        const dataParams = {};
                        const attributes = this.attributes;
                        for (let i = 0; i < attributes.length; i++) {
                            const attr = attributes[i];
                            if (attr.name.startsWith('data-')) {
                                const paramName = attr.name.replace('data-', '');
                                dataParams[paramName] = attr.value;
                            }
                        }
                                                
                        // Выбираем метод загрузки в зависимости от переменной
                        if (useIframeMethod) {
                            loadRnovaWidgetIframe(dataParams);
                        } else {
                            loadRnovaWidget(dataParams);
                        }
                    });
                }
            });

            rnovaLkButtons.forEach(button => {
                if(button != null) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault(); // Предотвращаем переход по ссылке
                        
                        // Собираем все data-атрибуты
                        const dataParams = {};
                        const attributes = this.attributes;
                        for (let i = 0; i < attributes.length; i++) {
                            const attr = attributes[i];
                            if (attr.name.startsWith('data-')) {
                                const paramName = attr.name.replace('data-', '');
                                dataParams[paramName] = attr.value;
                            }
                        }
                        
                        // Для LK всегда используем iframe
                        loadRnovaWidgetIframeLk(dataParams);
                    });
                }
            });
            if(submitBut != null) {
                submitBut.addEventListener('click', function() { 
                    if (!checkbox.checked) {                     
                        // Добавляем красную обводку
                        checkboxText.classList.add('error');
                        return false;
                    } 
                });
            }


            if(checkbox != null) {
                checkbox.addEventListener('change', function() {
                checkboxText.classList.remove('error');
                });
            }
            
            // Вызываем showThanksModal() после отправки формы
            function showThanksModal() {
                closeModal(modalForm);
                openModal(modalThanks);
                
                setTimeout(() => {
                    closeModal(modalThanks);
                }, 3000);
            }
            
            // Функция загрузки Rnova виджета через AJAX
            function loadRnovaWidget(dataParams = {}) {
                if (!rnovaForm) return;
                
                // Показываем модальное окно
                openModal(rnovaForm);
                
                // Проверяем, есть ли уже загруженный контент
                const existingContent = rnovaForm.querySelector('#rnova-a_form_loader');
                if (existingContent && existingContent.innerHTML.trim() !== '') {
                    return; // Контент уже загружен
                }
                
                // Показываем красивый прелоадер
                const loadingIndicator = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; min-height: 200px;">
                        <div style="width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid #366159; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
                        <div style="color: #366159; font-size: 18px; font-weight: 500; text-align: center;">Загрузка формы записи...</div>
                        <div style="color: #666; font-size: 14px; margin-top: 8px; text-align: center;">Пожалуйста, подождите</div>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                `;
                rnovaForm.querySelector('#rnova-a_form_loader').innerHTML = loadingIndicator;
                
                // Формируем URL с параметрами
                let url = clinic_ajax.rnova_url;
                if (Object.keys(dataParams).length > 0) {
                    const urlParams = new URLSearchParams(dataParams);
                    url += (url.includes('?') ? '&' : '?') + urlParams.toString();
                }
                
                console.log('Загружаем Rnova виджет с параметрами:', dataParams);
                console.log('URL:', url);
                
                // Загружаем контент через fetch
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Ошибка загрузки: ' + response.status);
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Создаем временный контейнер для парсинга
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        
                        // Извлекаем контент без скриптов
                        const content = tempDiv.querySelector('#rnova-a_form');
                        const script = tempDiv.querySelector('script[src*="rnova.org"]');
                        
                        if (content) {
                            // Вставляем только контент
                            rnovaForm.querySelector('#rnova-a_form_loader').innerHTML = '';
                            rnovaForm.querySelector('#rnova-a_form_loader').appendChild(content);
                        }
                        
                        if (script) {
                            // Проверяем, не загружен ли уже скрипт
                            if (!document.querySelector('script[src*="rnova.org"]')) {
                                const newScript = document.createElement('script');
                                newScript.src = script.src;
                                
                                newScript.onload = () => {
                                    // Показываем прелоадер инициализации
                                    const initLoader = `
                                        <div id="rnova-a_form_loader_init" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; min-height: 150px;">
                                            <div style="width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid #366159; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 15px;"></div>
                                            <div style="color: #366159; font-size: 16px; font-weight: 500; text-align: center;">Инициализация формы...</div>
                                        </div>
                                    `;
                                    rnovaForm.querySelector('#rnova-a_form_loader').insertAdjacentHTML('beforeend', initLoader);
                                    
                                    // Дополнительная попытка через 3 секунды
                                    setTimeout(() => {
                                        
                                        // Проверяем, появился ли контент в rnova-a_form
                                        const rnovaContainer = rnovaForm.querySelector('#rnova-a_form');
                                        if (rnovaContainer) {
                                            if (rnovaContainer.innerHTML.trim() === '') {
                                            
                       
                                            
                                            // Попробуем вызвать событие DOMContentLoaded
                                            const event = new Event('DOMContentLoaded');
                                            document.dispatchEvent(event);
                                            
                                            // Попробуем вызвать событие load
                                            const loadEvent = new Event('load');
                                            window.dispatchEvent(loadEvent);

                                            rnovaForm.querySelector('#rnova-a_form_loader_init').remove();
                                            
                                            // Попробуем найти и вызвать любые функции инициализации
                                            if (typeof window.rnovaWidgetsConfig !== 'undefined') {
                                                console.log('Структура rnovaWidgetsConfig:', Object.keys(window.rnovaWidgetsConfig));
                                            }
                                            } else {
                                                console.log('Контейнер уже содержит контент:', rnovaContainer.innerHTML);
                                            }
                                        } else {
                                            console.log('Контейнер rnova-a_form не найден!');
                                        }
                                    }, 1500);
                                    
                                    
                                };
                                
                                newScript.onerror = () => console.error('Ошибка загрузки скрипта Rnova:', newScript.src);
                                
                                document.head.appendChild(newScript);
                            } else {
                                console.log('Скрипт Rnova уже загружен');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка загрузки Rnova виджета:', error);
                        rnovaForm.querySelector('#rnova-a_form_loader').innerHTML = 
                            '<div style="text-align: center; padding: 40px; color: #ff0000;">Ошибка загрузки формы записи. Попробуйте позже.</div>';
                    });
            }

            // Функция загрузки Rnova виджета через iframe
            function loadRnovaWidgetIframe(dataParams = {}) {
                console.log('=== loadRnovaWidgetIframe вызвана ===');
                console.log('dataParams:', dataParams);
                console.log('rnovaForm:', rnovaForm);
                
                if (!rnovaForm) {
                    console.error('rnovaForm не найден!');
                    return;
                }
                
                // Показываем модальное окно
                openModal(rnovaForm);
                console.log('Модальное окно открыто');
                
                // Проверяем, есть ли уже загруженный iframe
                const existingIframe = rnovaForm.querySelector('#rnova-iframe');
                if (existingIframe) {
                    existingIframe.remove();
                }
                
                // Формируем URL с параметрами
                let url = clinic_ajax.rnova_url;
                if (Object.keys(dataParams).length > 0) {
                    const urlParams = new URLSearchParams(dataParams);
                    url += (url.includes('?') ? '&' : '?') + urlParams.toString();
                }
                
                console.log('Загружаем Rnova виджет через iframe с параметрами:', dataParams);
                console.log('URL:', url);
                
                // Создаем iframe
                const iframe = document.createElement('iframe');
                iframe.id = 'rnova-iframe';
                iframe.src = url;
                iframe.style.cssText = `
                    width: 100%;
                    height: 600px;
                    border: none;
                    border-radius: 8px;
                    background: #fff;
                `;
                
                // Показываем прелоадер
                const loadingIndicator = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; min-height: 200px;">
                        <div style="width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid #366159; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
                        <div style="color: #366159; font-size: 18px; font-weight: 500; text-align: center;">Загрузка формы записи...</div>
                        <div style="color: #666; font-size: 14px; margin-top: 8px; text-align: center;">Пожалуйста, подождите</div>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                `;
                
                // Очищаем контейнер и добавляем прелоадер
                const loader = rnovaForm.querySelector('#rnova-a_form_loader');
                loader.innerHTML = loadingIndicator;
                
                // Добавляем iframe в DOM сразу (скрытый под прелоадером)
                loader.appendChild(iframe);
                iframe.style.display = 'none'; // Скрываем iframe до загрузки
                
                console.log('iframe добавлен в DOM:', iframe);
                console.log('iframe src:', iframe.src);
                
                // Обработчик загрузки iframe
                iframe.onload = function() {
                    console.log('iframe загружен успешно');
                    // Показываем iframe и скрываем прелоадер
                    iframe.style.display = 'block';
                    const preloader = loader.querySelector('div');
                    if (preloader) {
                        preloader.remove();
                    }
                    // Уведомляем о успешной загрузке iframe
                    if (typeof window.onRnovaIframeLoad === 'function') {
                        window.onRnovaIframeLoad();
                    }
                };
                
                iframe.onerror = function() {
                    console.error('Ошибка загрузки iframe');
                    iframe.remove(); // Удаляем iframe при ошибке
                    loader.innerHTML = 
                        '<div style="text-align: center; padding: 40px; color: #ff0000;">Ошибка загрузки формы записи. Попробуйте позже.</div>';
                };
                
                // Таймаут для отладки - показываем iframe через 3 секунды независимо от onload
                setTimeout(() => {
                    console.log('Таймаут: проверяем состояние iframe');
                    console.log('iframe в DOM:', document.querySelector('#rnova-iframe'));
                    console.log('iframe display:', iframe.style.display);
                    
                    if (iframe.style.display === 'none') {
                        console.log('Показываем iframe принудительно через таймаут');
                        iframe.style.display = 'block';
                        const preloader = loader.querySelector('div');
                        if (preloader) {
                            preloader.remove();
                        }
                    }
                }, 3000);
                
                // Добавляем обработчик закрытия модального окна для очистки iframe
                const originalCloseModal = closeModal;
                closeModal = function(modal) {
                    if (modal === rnovaForm) {
                        // Сбрасываем флаг загрузки iframe
                        window.rnovaIframeLoaded = false;
                        // Удаляем iframe при закрытии
                        const iframeToRemove = rnovaForm.querySelector('#rnova-iframe');
                        if (iframeToRemove) {
                            iframeToRemove.remove();
                        }
                        // Очищаем контейнер
                        const loaderToClear = rnovaForm.querySelector('#rnova-a_form_loader');
                        if (loaderToClear) {
                            loaderToClear.innerHTML = '';
                        }
                    }
                    originalCloseModal(modal);
                };
            }


            // Функция загрузки Rnova виджета через iframe для LK
            function loadRnovaWidgetIframeLk(dataParams = {}) {
                console.log('=== loadRnovaWidgetIframeLk вызвана ===');
                console.log('dataParams:', dataParams);
                console.log('rnovaFormLk:', rnovaFormLk);
                
                if (!rnovaFormLk) {
                    console.error('rnovaFormLk не найден!');
                    return;
                }
                
                // Показываем модальное окно
                openModal(rnovaFormLk);
                console.log('Модальное окно LK открыто');
                
                // Проверяем, есть ли уже загруженный iframe
                const existingIframe = rnovaFormLk.querySelector('#rnova-iframe-lk');
                if (existingIframe) {
                    console.log('Iframe LK уже существует, просто показываем окно');
                    return; // Iframe уже создан, просто показываем модальное окно
                }
                
                // Формируем URL с параметрами для LK
                let url = clinic_ajax.rnova_lk_url;
                if (Object.keys(dataParams).length > 0) {
                    const urlParams = new URLSearchParams(dataParams);
                    url += '?' + urlParams.toString();
                }
                
                
                // Создаем iframe
                const iframe = document.createElement('iframe');
                iframe.id = 'rnova-iframe-lk';
                iframe.src = url;
                iframe.style.width = '100%';
                iframe.style.height = '80vh';
                iframe.style.border = 'none';
                iframe.style.borderRadius = '8px';
                
                // Прелоадер
                const loadingIndicator = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; min-height: 400px;">
                        <div style="width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid #366159; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
                        <div style="color: #366159; font-size: 18px; font-weight: 500; text-align: center;">Загрузка формы записи...</div>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                `;
                
                // Очищаем контейнер и добавляем прелоадер
                const loader = rnovaFormLk.querySelector('#rnova-a_form_loader_lk');
                loader.innerHTML = loadingIndicator;
                
                // Добавляем iframe в DOM сразу (скрытый под прелоадером)
                loader.appendChild(iframe);
                iframe.style.display = 'none'; // Скрываем iframe до загрузки
                
                // Обработчик загрузки iframe
                iframe.onload = function() {
                    console.log('Iframe LK загружен');
                    window.rnovaIframeLoaded = true;
                    
                    // Скрываем прелоадер и показываем iframe
                    const preloader = loader.querySelector('div');
                    if (preloader) {
                        preloader.style.display = 'none';
                    }
                    iframe.style.display = 'block';
                };
                
                // Таймаут на случай, если iframe не загрузится
                setTimeout(() => {
                    if (!window.rnovaIframeLoaded) {
                        console.warn('Iframe LK не загрузился за 5 секунд');
                        loader.innerHTML = '<div style="text-align: center; padding: 40px; color: #ff0000;">Ошибка загрузки формы записи. Попробуйте позже.</div>';
                    }
                }, 5000);
                
                // Добавляем обработчик закрытия модального окна (iframe НЕ удаляем для повторного использования)
                const originalCloseModal = closeModal;
                closeModal = function(modal) {
                    if (modal === rnovaFormLk) {
                        // Сбрасываем флаг загрузки iframe
                        window.rnovaIframeLoaded = false;
                        // НЕ удаляем iframe - оставляем для повторного использования
                        console.log('Модальное окно LK закрыто, iframe остается в DOM');
                    }
                    originalCloseModal(modal);
                };
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





