function initScript() {

    mobileMenu();
    sliders();
    filters();
    fixedBut();
    modalImage()
    phoneMask();
    onlineModal();
    psiForm();
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

    function psiForm() {
        class MultiStepForm {
            constructor() {
                // Проверяем, существует ли контейнер формы на странице
                this.formContainer = document.querySelector('.psi-form-container');

                // Если формы нет на странице - не инициализируем
                if (!this.formContainer) {
                    console.log('Форма не найдена на странице, инициализация пропущена');
                    return;
                }
                this.currentStep = 1;
                this.totalSteps = 4;
                this.formData = {
                    step1: {},
                    step2: {},
                    step3: {
                        questions: {}
                    },
                    step4: {}
                };

                this.init();
            }

            init() {
                this.bindElements();
                this.bindEvents();
                this.initCustomSelect();
                this.updateUI();
            }

            bindElements() {
                this.steps = document.querySelectorAll('.psi-step');
                this.formSteps = document.querySelectorAll('.psi-form-step');
                this.prevBtn = document.getElementById('prevBtn');
                this.nextBtn = document.getElementById('nextBtn');
                this.formFooter = document.querySelector('.psi-form-footer');

                // Шаг 1 элементы
                this.basicEducation = document.getElementById('basicEducation');
                this.cbtEducation = document.getElementById('cbtEducation');
                this.otherEducation = document.getElementById('otherEducation');

                // Шаг 2 элементы
                this.psychologistExperience = document.getElementById('psychologistExperience');
                this.futureWork = document.getElementById('futureWork');
                this.supervisionRadioNo = document.querySelector('input[name="supervision"][value="no"]');
                this.supervisionRadioYes = document.querySelector('input[name="supervision"][value="yes"]');
                this.supervisionDetails = document.getElementById('supervisionDetails');
                this.supervisionDetailsContainer = document.querySelector('.psi-supervision-details');

                // Шаг 3 элементы
                this.questionGroups = document.querySelectorAll('.psi-question-group');

                // Шаг 4 элементы
                this.fullName = document.getElementById('fullName');
                this.age = document.getElementById('age');
                this.contact = document.getElementById('contact');
                this.telegram = document.getElementById('telegram');
                this.email = document.getElementById('email');
                this.recommendationRadioNo = document.querySelector('input[name="recommendation"][value="no"]');
                this.recommendationRadioYes = document.querySelector('input[name="recommendation"][value="yes"]');
                this.specialistNameInput = document.getElementById('specialistName');
                this.selectTrigger = document.getElementById('selectTrigger');
                this.selectOptions = document.getElementById('selectOptions');
                this.selectOptionsList = document.querySelectorAll('.psi-select-option');
                this.recommendationDetailsContainer = document.querySelector('.psi-recommendation-details');
                this.agreePrivacy = document.getElementById('agreePrivacy');
                this.agreeTerms = document.getElementById('agreeTerms');

                // Модальное окно
                this.successModal = document.getElementById('successModal');
                this.closeSuccessModal = document.getElementById('closeSuccessModal');

                this.formContainer = document.querySelector('.psi-form-container');

                // Флаг для отслеживания, была ли навигация внутри формы
                this.isFormNavigation = false;
            }

            initCustomSelect() {
                // Инициализация кастомного селекта
                if (this.selectTrigger) {
                    // Обработчик клика на триггер
                    this.selectTrigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleSelectOptions();
                    });

                    // Обработчики для опций
                    this.selectOptionsList.forEach(option => {
                        option.addEventListener('click', () => {
                            this.selectOption(option);
                        });
                    });

                    // Закрытие селекта при клике вне его
                    document.addEventListener('click', () => {
                        this.closeSelectOptions();
                    });

                    // Предотвращаем закрытие при клике внутри селекта
                    this.selectOptions.addEventListener('click', (e) => {
                        e.stopPropagation();
                    });
                }
            }

            toggleSelectOptions() {
                if (this.selectOptions) {
                    this.selectOptions.classList.toggle('active');
                    this.selectTrigger.classList.toggle('active');
                }
            }

            closeSelectOptions() {
                if (this.selectOptions) {
                    this.selectOptions.classList.remove('active');
                    this.selectTrigger.classList.remove('active');
                }
            }

            selectOption(option) {
                const value = option.getAttribute('data-value');
                const text = option.textContent;

                // Обновляем placeholder
                const placeholder = this.selectTrigger.querySelector('.psi-select-placeholder');
                if (value) {
                    placeholder.textContent = text;
                    this.selectTrigger.classList.add('has-value');
                } else {
                    placeholder.textContent = 'Выберите специалиста';
                    this.selectTrigger.classList.remove('has-value');
                }

                // Обновляем скрытое поле
                if (this.specialistNameInput) {
                    this.specialistNameInput.value = value;
                }

                // Помечаем выбранную опцию
                this.selectOptionsList.forEach(opt => {
                    opt.classList.remove('selected');
                });
                option.classList.add('selected');

                // Закрываем опции
                this.closeSelectOptions();

                // Сохраняем данные
                this.saveStepData();
            }

            bindEvents() {
                // Кнопки навигации - добавляем флаг навигации
                this.prevBtn.addEventListener('click', () => {
                    this.isFormNavigation = true;
                    this.prevStep();
                });

                this.nextBtn.addEventListener('click', () => {
                    this.isFormNavigation = true;
                    this.nextStep();
                });

                // Шаг 1 события
                this.setupTextareaEvents(this.basicEducation);
                this.setupTextareaEvents(this.cbtEducation);
                this.setupTextareaEvents(this.otherEducation);

                // Шаг 2 события
                this.setupTextareaEvents(this.psychologistExperience);
                this.setupTextareaEvents(this.futureWork);

                if (this.supervisionDetails) {
                    this.supervisionDetails.addEventListener('input', () => this.saveStepData());
                }

                // Радио кнопки супервизии
                if (this.supervisionRadioNo) {
                    this.supervisionRadioNo.addEventListener('change', () => {
                        this.toggleSupervisionDetails();
                        this.saveStepData();
                    });
                }

                if (this.supervisionRadioYes) {
                    this.supervisionRadioYes.addEventListener('change', () => {
                        this.toggleSupervisionDetails();
                        this.saveStepData();
                    });
                }

                // Шаг 3 события - чекбоксы
                this.setupCheckboxEvents();

                // Шаг 4 события
                this.setupInputEvents(this.fullName);
                this.setupInputEvents(this.age);
                this.setupInputEvents(this.contact);
                this.setupInputEvents(this.telegram);
                this.setupInputEvents(this.email);

                // Радио кнопки рекомендации
                if (this.recommendationRadioNo) {
                    this.recommendationRadioNo.addEventListener('change', () => {
                        this.toggleRecommendationDetails();
                        this.saveStepData();
                    });
                }

                if (this.recommendationRadioYes) {
                    this.recommendationRadioYes.addEventListener('change', () => {
                        this.toggleRecommendationDetails();
                        this.saveStepData();
                    });
                }

                // Чекбоксы согласия
                if (this.agreePrivacy) {
                    this.agreePrivacy.addEventListener('change', () => {
                        this.saveStepData();
                        this.clearAgreementError(this.agreePrivacy);
                    });
                }

                if (this.agreeTerms) {
                    this.agreeTerms.addEventListener('change', () => {
                        this.saveStepData();
                        this.clearAgreementError(this.agreeTerms);
                    });
                }

                // Модальное окно
                if (this.closeSuccessModal) {
                    this.closeSuccessModal.addEventListener('click', () => this.closeSuccessModalHandler());
                }

                if (this.successModal) {
                    this.successModal.addEventListener('click', (e) => {
                        if (e.target === this.successModal) {
                            this.closeSuccessModalHandler();
                        }
                    });
                }

                // Слушатель для сброса флага навигации после скролла
                window.addEventListener('scroll', () => {
                    // Сбрасываем флаг после завершения скролла
                    setTimeout(() => {
                        this.isFormNavigation = false;
                    }, 100);
                });
            }

            setupTextareaEvents(textarea) {
                if (textarea) {
                    textarea.addEventListener('input', () => {
                        this.saveStepData();
                        this.clearFieldError(textarea);
                    });
                    textarea.addEventListener('focus', () => this.clearFieldError(textarea));
                }
            }

            setupInputEvents(input) {
                if (input) {
                    input.addEventListener('input', () => {
                        this.saveStepData();
                        this.clearFieldError(input);
                    });
                    input.addEventListener('focus', () => this.clearFieldError(input));
                }
            }

            setupCheckboxEvents() {
                this.questionGroups.forEach(group => {
                    const checkboxes = group.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', () => {
                            this.saveStepData();
                            this.clearQuestionError(group);
                        });
                    });
                });
            }

            toggleSupervisionDetails() {
                if (this.supervisionDetailsContainer && this.supervisionRadioYes) {
                    if (this.supervisionRadioYes.checked) {
                        this.supervisionDetailsContainer.style.display = 'block';
                    } else {
                        this.supervisionDetailsContainer.style.display = 'none';
                        if (this.supervisionDetails) {
                            this.supervisionDetails.value = '';
                        }
                    }
                }
            }

            toggleRecommendationDetails() {
                if (this.recommendationDetailsContainer && this.recommendationRadioYes) {
                    if (this.recommendationRadioYes.checked) {
                        this.recommendationDetailsContainer.style.display = 'block';
                    } else {
                        this.recommendationDetailsContainer.style.display = 'none';
                        if (this.specialistNameInput) {
                            this.specialistNameInput.value = '';
                            this.resetCustomSelect();
                        }
                    }
                }
            }

            resetCustomSelect() {
                if (this.selectTrigger) {
                    const placeholder = this.selectTrigger.querySelector('.psi-select-placeholder');
                    placeholder.textContent = 'Выберите специалиста';
                    this.selectTrigger.classList.remove('has-value');

                    this.selectOptionsList.forEach(opt => {
                        opt.classList.remove('selected');
                    });

                    // Помечаем первую опцию как выбранную
                    if (this.selectOptionsList.length > 0) {
                        this.selectOptionsList[0].classList.add('selected');
                    }
                }
            }

            clearFieldError(field) {
                if (field && field.parentElement) {
                    const formGroup = field.parentElement;
                    if (formGroup.classList.contains('psi-field-with-error')) {
                        formGroup.classList.remove('psi-field-with-error');
                    }
                }
            }

            clearQuestionError(questionGroup) {
                if (questionGroup.classList.contains('error')) {
                    questionGroup.classList.remove('error');
                }
            }

            clearAgreementError(checkbox) {
                const errorElement = checkbox.parentElement.nextElementSibling;
                if (errorElement && errorElement.classList.contains('psi-agreement-error')) {
                    errorElement.style.display = 'none';
                }
            }

            showAgreementError(checkbox) {
                const errorElement = checkbox.parentElement.nextElementSibling;
                if (errorElement && errorElement.classList.contains('psi-agreement-error')) {
                    errorElement.style.display = 'block';
                }
            }

            saveStepData() {
                // Сохраняем данные шага 1
                this.formData.step1 = {
                    basicEducation: this.basicEducation ? this.basicEducation.value.trim() : '',
                    cbtEducation: this.cbtEducation ? this.cbtEducation.value.trim() : '',
                    otherEducation: this.otherEducation ? this.otherEducation.value.trim() : ''
                };

                // Сохраняем данные шага 2
                if (this.psychologistExperience) {
                    this.formData.step2 = {
                        psychologistExperience: this.psychologistExperience.value.trim(),
                        futureWork: this.futureWork.value.trim(),
                        supervision: this.supervisionRadioYes ? (this.supervisionRadioYes.checked ? 'yes' : 'no') : 'no',
                        supervisionDetails: this.supervisionDetails ? this.supervisionDetails.value.trim() : ''
                    };
                }

                // Сохраняем данные шага 3
                this.formData.step3.questions = {};

                this.questionGroups.forEach(group => {
                    const questionNumber = group.getAttribute('data-question');
                    const checkboxes = group.querySelectorAll('input[type="checkbox"]:checked');
                    const selectedValues = Array.from(checkboxes).map(cb => cb.value);

                    this.formData.step3.questions[`question${questionNumber}`] = selectedValues;
                });

                // Сохраняем данные шага 4
                if (this.fullName) {
                    this.formData.step4 = {
                        fullName: this.fullName.value.trim(),
                        age: this.age ? this.age.value.trim() : '',
                        contact: this.contact ? this.contact.value.trim() : '',
                        telegram: this.telegram ? this.telegram.value.trim() : '',
                        email: this.email ? this.email.value.trim() : '',
                        recommendation: this.recommendationRadioYes ? (this.recommendationRadioYes.checked ? 'yes' : 'no') : 'no',
                        specialistName: this.specialistNameInput ? this.specialistNameInput.value : '',
                        agreePrivacy: this.agreePrivacy ? this.agreePrivacy.checked : false,
                        agreeTerms: this.agreeTerms ? this.agreeTerms.checked : false
                    };
                }
            }

            validateStep1() {
                let isValid = true;

                const step1Groups = document.querySelectorAll('#step1 .psi-form-group');
                step1Groups.forEach(group => {
                    group.classList.remove('psi-field-with-error');
                });

                if (!this.formData.step1.basicEducation) {
                    this.basicEducation.parentElement.classList.add('psi-field-with-error');
                    isValid = false;
                }

                if (!this.formData.step1.cbtEducation) {
                    this.cbtEducation.parentElement.classList.add('psi-field-with-error');
                    isValid = false;
                }

                return isValid;
            }

            validateStep2() {
                let isValid = true;

                const step2Groups = document.querySelectorAll('#step2 .psi-form-group');
                step2Groups.forEach(group => {
                    group.classList.remove('psi-field-with-error');
                });

                if (!this.formData.step2.psychologistExperience) {
                    this.psychologistExperience.parentElement.classList.add('psi-field-with-error');
                    isValid = false;
                }

                if (!this.formData.step2.futureWork) {
                    this.futureWork.parentElement.classList.add('psi-field-with-error');
                    isValid = false;
                }

                return isValid;
            }

            validateStep3() {
                let allQuestionsValid = true;

                this.questionGroups.forEach(group => {
                    group.classList.remove('error');
                });

                this.questionGroups.forEach(group => {
                    const questionNumber = group.getAttribute('data-question');
                    const selectedValues = this.formData.step3.questions[`question${questionNumber}`] || [];

                    if (selectedValues.length === 0) {
                        group.classList.add('error');
                        allQuestionsValid = false;
                    }
                });

                return allQuestionsValid;
            }

            validateStep4() {
                let isValid = true;

                // Сброс ошибок полей ввода
                const step4Inputs = document.querySelectorAll('#step4 .psi-input');
                step4Inputs.forEach(input => {
                    input.parentElement.classList.remove('psi-field-with-error');
                });

                // Сброс ошибок согласия
                if (this.agreePrivacy) {
                    this.clearAgreementError(this.agreePrivacy);
                }
                if (this.agreeTerms) {
                    this.clearAgreementError(this.agreeTerms);
                }

                // Валидация имени
                if (!this.formData.step4.fullName) {
                    this.fullName.parentElement.classList.add('psi-field-with-error');
                    isValid = false;
                }

                // Валидация возраста
                const age = parseInt(this.formData.step4.age);
                if (!this.formData.step4.age || isNaN(age) || age < 18 || age > 100) {
                    this.age.parentElement.classList.add('psi-field-with-error');
                    isValid = false;
                }

                // Валидация контакта
                if (!this.formData.step4.contact) {
                    this.contact.parentElement.classList.add('psi-field-with-error');
                    isValid = false;
                }

                // Валидация email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!this.formData.step4.email || !emailRegex.test(this.formData.step4.email)) {
                    this.email.parentElement.classList.add('psi-field-with-error');
                    isValid = false;
                }

                // Валидация чекбоксов согласия
                if (!this.formData.step4.agreePrivacy) {
                    this.showAgreementError(this.agreePrivacy);
                    isValid = false;
                }

                if (!this.formData.step4.agreeTerms) {
                    this.showAgreementError(this.agreeTerms);
                    isValid = false;
                }

                return isValid;
            }

            validateCurrentStep() {
                switch(this.currentStep) {
                    case 1:
                        return this.validateStep1();
                    case 2:
                        return this.validateStep2();
                    case 3:
                        return this.validateStep3();
                    case 4:
                        return this.validateStep4();
                    default:
                        return true;
                }
            }

            nextStep() {
                this.saveStepData();

                if (!this.validateCurrentStep()) {
                    // При ошибке валидации скроллим только если это навигация внутри формы
                    if (this.isFormNavigation) {
                        this.safeScrollToTop();
                    }
                    return;
                }

                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                    this.updateUI();
                    // Скроллим только если это навигация внутри формы
                    if (this.isFormNavigation) {
                        this.safeScrollToTop();
                    }
                } else {
                    this.submitForm();
                }
            }

            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                    this.updateUI();
                    // Скроллим только если это навигация внутри формы
                    if (this.isFormNavigation) {
                        this.safeScrollToTop();
                    }
                }
            }

            safeScrollToTop() {
                // Безопасный скролл только если контейнер существует
                if (this.formContainer && this.isFormNavigation) {
                    try {
                        this.formContainer.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    } catch (e) {
                        // В случае ошибки используем обычный скролл
                        window.scrollTo({
                            top: this.formContainer.offsetTop - 20,
                            behavior: 'smooth'
                        });
                    }
                }
            }

            scrollToTop() {
                // Устаревший метод, используйте safeScrollToTop
                this.safeScrollToTop();
            }

            updateUI() {
                // Обновляем индикатор шагов
                this.steps.forEach((step, index) => {
                    const stepNumber = index + 1;
                    step.classList.remove('active', 'completed');

                    if (stepNumber === this.currentStep) {
                        step.classList.add('active');
                    } else if (stepNumber < this.currentStep) {
                        step.classList.add('completed');
                    }
                });

                // Показываем текущий шаг формы
                this.formSteps.forEach((step, index) => {
                    step.classList.remove('active');
                    if (index + 1 === this.currentStep) {
                        step.classList.add('active');
                    }
                });

                // Обновляем кнопки и показываем согласия на последнем шаге
                this.prevBtn.classList.toggle('psi-btn-hidden', this.currentStep === 1);

                if (this.currentStep === this.totalSteps) {
                    this.nextBtn.textContent = 'Отправить заявку';
                    if (this.formFooter) {
                        this.formFooter.classList.add('show-agreements');
                    }
                } else {
                    this.nextBtn.textContent = 'Продолжить →';
                    if (this.formFooter) {
                        this.formFooter.classList.remove('show-agreements');
                    }
                }

                // Загружаем сохраненные данные для текущего шага
                this.loadStepData();
            }

            loadStepData() {
                // Загружаем данные шага 1
                if (this.currentStep === 1 && this.formData.step1) {
                    if (this.basicEducation) this.basicEducation.value = this.formData.step1.basicEducation || '';
                    if (this.cbtEducation) this.cbtEducation.value = this.formData.step1.cbtEducation || '';
                    if (this.otherEducation) this.otherEducation.value = this.formData.step1.otherEducation || '';
                }

                // Загружаем данные шага 2
                if (this.currentStep === 2 && this.formData.step2) {
                    if (this.psychologistExperience) this.psychologistExperience.value = this.formData.step2.psychologistExperience || '';
                    if (this.futureWork) this.futureWork.value = this.formData.step2.futureWork || '';

                    if (this.formData.step2.supervision === 'yes' && this.supervisionRadioYes) {
                        this.supervisionRadioYes.checked = true;
                    } else if (this.supervisionRadioNo) {
                        this.supervisionRadioNo.checked = true;
                    }

                    if (this.supervisionDetails) {
                        this.supervisionDetails.value = this.formData.step2.supervisionDetails || '';
                    }

                    this.toggleSupervisionDetails();
                }

                // Загружаем данные шага 3
                if (this.currentStep === 3 && this.formData.step3 && this.formData.step3.questions) {
                    this.questionGroups.forEach(group => {
                        const questionNumber = group.getAttribute('data-question');
                        const selectedValues = this.formData.step3.questions[`question${questionNumber}`] || [];

                        const checkboxes = group.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = selectedValues.includes(checkbox.value);
                        });
                    });
                }

                // Загружаем данные шага 4
                if (this.currentStep === 4 && this.formData.step4) {
                    if (this.fullName) this.fullName.value = this.formData.step4.fullName || '';
                    if (this.age) this.age.value = this.formData.step4.age || '';
                    if (this.contact) this.contact.value = this.formData.step4.contact || '';
                    if (this.telegram) this.telegram.value = this.formData.step4.telegram || '';
                    if (this.email) this.email.value = this.formData.step4.email || '';

                    if (this.formData.step4.recommendation === 'yes' && this.recommendationRadioYes) {
                        this.recommendationRadioYes.checked = true;
                    } else if (this.recommendationRadioNo) {
                        this.recommendationRadioNo.checked = true;
                    }

                    // Восстанавливаем кастомный селект
                    if (this.specialistNameInput && this.formData.step4.specialistName) {
                        this.specialistNameInput.value = this.formData.step4.specialistName;

                        // Находим соответствующую опцию
                        const selectedOption = Array.from(this.selectOptionsList).find(
                            option => option.getAttribute('data-value') === this.formData.step4.specialistName
                        );

                        if (selectedOption) {
                            this.selectOption(selectedOption);
                        }
                    }

                    if (this.agreePrivacy) this.agreePrivacy.checked = this.formData.step4.agreePrivacy || false;
                    if (this.agreeTerms) this.agreeTerms.checked = this.formData.step4.agreeTerms || false;

                    this.toggleRecommendationDetails();
                }
            }

            submitForm() {
                this.saveStepData();

                const payload = {
                    step1: this.formData.step1,
                    step2: this.formData.step2,
                    step3: {
                        questions: this.buildQuestionPayload()
                    },
                    step4: this.formData.step4
                };

                const requestData = new FormData();
                requestData.append('action', 'clinic_submit_psi_form');
                requestData.append('nonce', (window.clinic_ajax && clinic_ajax.nonce) ? clinic_ajax.nonce : '');
                requestData.append('payload', JSON.stringify(payload));

                const originalBtnText = this.nextBtn ? this.nextBtn.textContent : '';
                if (this.nextBtn) {
                    this.nextBtn.disabled = true;
                    this.nextBtn.textContent = 'Отправляем...';
                }

                fetch((window.clinic_ajax && clinic_ajax.ajax_url) ? clinic_ajax.ajax_url : '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: requestData
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result && result.success) {
                            this.showSuccessModal();
                            return;
                        }

                        const message = result && result.data && result.data.message
                            ? result.data.message
                            : 'Не удалось отправить форму. Попробуйте позже.';
                        console.error('Ошибка отправки формы:', result);
                        alert(message);
                    })
                    .catch(error => {
                        console.error('Ошибка сети при отправке формы:', error);
                        alert('Не удалось отправить форму. Проверьте соединение и попробуйте позже.');
                    })
                    .finally(() => {
                        if (this.nextBtn) {
                            this.nextBtn.disabled = false;
                            this.nextBtn.textContent = originalBtnText;
                        }
                    });
            }

            buildQuestionPayload() {
                if (!this.questionGroups || !this.questionGroups.length) {
                    return [];
                }

                return Array.from(this.questionGroups).map((group, index) => {
                    const questionNumber = group.getAttribute('data-question') || (index + 1);
                    const titleElement = group.querySelector('h3');
                    let questionText = titleElement ? titleElement.textContent.trim() : `Вопрос ${questionNumber}`;
                    questionText = questionText.replace(/^\s*\d+\.\s*/g, '').trim();

                    const selectedAnswers = this.formData.step3.questions[`question${questionNumber}`] || [];

                    return {
                        number: questionNumber,
                        question: questionText,
                        answers: selectedAnswers
                    };
                });
            }

            showSuccessModal() {
                if (this.successModal) {
                    this.successModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            }

            closeSuccessModalHandler() {
                if (this.successModal) {
                    this.successModal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        }

        // Инициализация формы при загрузке страницы
        // Не делаем автоскролл при загрузке
        document.addEventListener('DOMContentLoaded', () => {
            const form = new MultiStepForm();

            // Если нужно проскроллить к форме при загрузке (например, при открытии по ссылке с якорем),
            // это нужно делать отдельно, а не в конструкторе формы
            if (window.location.hash === '#form') {
                setTimeout(() => {
                    const formContainer = document.querySelector('.psi-form-container');
                    if (formContainer) {
                        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
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





