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
        class PsiMultiStepForm {
            constructor(container) {
                this.container = container;
                this.form = container.querySelector('#multiStepForm');

                if (!this.form) {
                    return;
                }

                this.currentStep = 1;
                this.totalSteps = 4;
                this.isSubmitting = false;
                this.blockResubmit = false;
                this.formData = {
                    step1: {},
                    step2: {},
                    step3: { questions: {} },
                    step4: {}
                };

                this.bindElements();
                this.bindEvents();
                this.updateUI();
            }

            bindElements() {
                this.steps = this.container.querySelectorAll('.psi-step');
                this.formSteps = this.form.querySelectorAll('.psi-form-step');
                this.prevBtn = this.container.querySelector('#prevBtn');
                this.nextBtn = this.container.querySelector('#nextBtn');
                this.formFooter = this.container.querySelector('.psi-form-footer');

                this.basicEducation = this.form.querySelector('#basicEducation');
                this.cbtEducation = this.form.querySelector('#cbtEducation');
                this.otherEducation = this.form.querySelector('#otherEducation');

                this.psychologistExperience = this.form.querySelector('#psychologistExperience');
                this.futureWork = this.form.querySelector('#futureWork');
                this.supervisionDetails = this.form.querySelector('#supervisionDetails');
                this.supervisionDetailsContainer = this.form.querySelector('.psi-supervision-details');

                this.questionGroups = this.form.querySelectorAll('.psi-question-group');

                this.fullName = this.form.querySelector('#fullName');
                this.age = this.form.querySelector('#age');
                this.contact = this.form.querySelector('#contact');
                this.telegram = this.form.querySelector('#telegram');
                this.email = this.form.querySelector('#email');
                this.specialistNameInput = this.form.querySelector('#specialistName');
                this.recommendationDetailsContainer = this.form.querySelector('.psi-recommendation-details');

                this.selectTrigger = this.form.querySelector('#selectTrigger');
                this.selectOptions = this.form.querySelector('#selectOptions');
                this.selectOptionsList = this.form.querySelectorAll('.psi-select-option');

                this.agreePrivacy = this.form.querySelector('#agreePrivacy');
                this.agreeTerms = this.form.querySelector('#agreeTerms');

                this.successModal = this.form.querySelector('#successModal');
                this.closeSuccessModal = this.form.querySelector('#closeSuccessModal');

                this.submitError = document.createElement('div');
                this.submitError.className = 'psi-submit-error';
                this.submitError.style.display = 'none';
                this.submitError.style.color = '#C0392B';
                this.submitError.style.marginTop = '10px';
                this.submitError.style.fontSize = '14px';
                if (this.formFooter) {
                    this.formFooter.appendChild(this.submitError);
                }
            }

            bindEvents() {
                if (this.prevBtn) {
                    this.prevBtn.addEventListener('click', () => this.prevStep());
                }

                if (this.nextBtn) {
                    this.nextBtn.addEventListener('click', () => this.nextStep());
                }

                this.form.querySelectorAll('textarea, input').forEach((field) => {
                    field.addEventListener('input', () => {
                        if (this.blockResubmit) {
                            this.blockResubmit = false;
                        }
                        this.saveStepData();
                        this.clearFieldError(field);
                        this.hideSubmitError();
                    });
                    field.addEventListener('change', () => {
                        if (this.blockResubmit) {
                            this.blockResubmit = false;
                        }
                        this.saveStepData();
                        this.hideSubmitError();
                    });
                });

                this.form.querySelectorAll('input[name="supervision"]').forEach((radio) => {
                    radio.addEventListener('change', () => this.toggleSupervisionDetails());
                });

                this.form.querySelectorAll('input[name="recommendation"]').forEach((radio) => {
                    radio.addEventListener('change', () => this.toggleRecommendationDetails());
                });

                this.questionGroups.forEach((group) => {
                    group.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                        checkbox.addEventListener('change', () => {
                            group.classList.remove('error');
                        });
                    });
                });

                if (this.selectTrigger && this.selectOptions) {
                    this.selectTrigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.selectOptions.classList.toggle('active');
                        this.selectTrigger.classList.toggle('active');
                    });

                    this.selectOptionsList.forEach((option) => {
                        option.addEventListener('click', () => this.selectOption(option));
                    });

                    document.addEventListener('click', () => {
                        this.selectOptions.classList.remove('active');
                        this.selectTrigger.classList.remove('active');
                    });
                }

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
            }

            selectOption(option) {
                const value = option.getAttribute('data-value') || '';
                const text = option.textContent.trim();
                const placeholder = this.selectTrigger.querySelector('.psi-select-placeholder');

                this.selectOptionsList.forEach((opt) => opt.classList.remove('selected'));
                option.classList.add('selected');

                if (this.specialistNameInput) {
                    this.specialistNameInput.value = value;
                }

                if (value) {
                    placeholder.textContent = text;
                    this.selectTrigger.classList.add('has-value');
                } else {
                    placeholder.textContent = 'Укажите психолога';
                    this.selectTrigger.classList.remove('has-value');
                }

                this.selectOptions.classList.remove('active');
                this.selectTrigger.classList.remove('active');
                this.saveStepData();
            }

            toggleSupervisionDetails() {
                const selected = this.form.querySelector('input[name="supervision"]:checked');
                const isYes = selected && selected.value === 'yes';

                if (!this.supervisionDetailsContainer) {
                    return;
                }

                this.supervisionDetailsContainer.style.display = isYes ? 'block' : 'none';
                if (!isYes && this.supervisionDetails) {
                    this.supervisionDetails.value = '';
                }
            }

            toggleRecommendationDetails() {
                const selected = this.form.querySelector('input[name="recommendation"]:checked');
                const isYes = selected && selected.value === 'yes';

                if (!this.recommendationDetailsContainer) {
                    return;
                }

                this.recommendationDetailsContainer.style.display = isYes ? 'block' : 'none';
                if (!isYes && this.specialistNameInput) {
                    this.specialistNameInput.value = '';
                }
            }

            clearFieldError(field) {
                const group = field.closest('.psi-form-group, .psi-question-group');
                if (group) {
                    group.classList.remove('psi-field-with-error');
                }
            }

            showAgreementError(checkbox) {
                if (!checkbox) {
                    return;
                }
                const error = checkbox.closest('.psi-checkbox-label')?.parentElement?.querySelector('.psi-agreement-error');
                if (error) {
                    error.style.display = 'block';
                }
            }

            hideAgreementError(checkbox) {
                if (!checkbox) {
                    return;
                }
                const error = checkbox.closest('.psi-checkbox-label')?.parentElement?.querySelector('.psi-agreement-error');
                if (error) {
                    error.style.display = 'none';
                }
            }

            showSubmitError(message) {
                if (!this.submitError) {
                    return;
                }
                this.submitError.textContent = message;
                this.submitError.style.display = 'block';
            }

            hideSubmitError() {
                if (!this.submitError) {
                    return;
                }
                this.submitError.style.display = 'none';
                this.submitError.textContent = '';
            }

            saveStepData() {
                this.formData.step1 = {
                    basicEducation: this.basicEducation ? this.basicEducation.value.trim() : '',
                    cbtEducation: this.cbtEducation ? this.cbtEducation.value.trim() : '',
                    otherEducation: this.otherEducation ? this.otherEducation.value.trim() : ''
                };

                const supervisionChecked = this.form.querySelector('input[name="supervision"]:checked');
                this.formData.step2 = {
                    psychologistExperience: this.psychologistExperience ? this.psychologistExperience.value.trim() : '',
                    futureWork: this.futureWork ? this.futureWork.value.trim() : '',
                    supervision: supervisionChecked ? supervisionChecked.value : 'no',
                    supervisionDetails: this.supervisionDetails ? this.supervisionDetails.value.trim() : ''
                };

                this.formData.step3.questions = {};
                this.questionGroups.forEach((group) => {
                    const questionNumber = group.getAttribute('data-question');
                    const checked = group.querySelectorAll('input[type="checkbox"]:checked');
                    this.formData.step3.questions['question' + questionNumber] = Array.from(checked).map((cb) => cb.value);
                });

                const recommendationChecked = this.form.querySelector('input[name="recommendation"]:checked');
                this.formData.step4 = {
                    fullName: this.fullName ? this.fullName.value.trim() : '',
                    age: this.age ? this.age.value.trim() : '',
                    contact: this.contact ? this.contact.value.trim() : '',
                    telegram: this.telegram ? this.telegram.value.trim() : '',
                    email: this.email ? this.email.value.trim() : '',
                    recommendation: recommendationChecked ? recommendationChecked.value : 'no',
                    specialistName: this.specialistNameInput ? this.specialistNameInput.value : '',
                    agreePrivacy: this.agreePrivacy ? this.agreePrivacy.checked : false,
                    agreeTerms: this.agreeTerms ? this.agreeTerms.checked : false
                };
            }

            validateStep1() {
                let valid = true;
                if (!this.formData.step1.basicEducation && this.basicEducation) {
                    this.basicEducation.closest('.psi-form-group')?.classList.add('psi-field-with-error');
                    valid = false;
                }
                if (!this.formData.step1.cbtEducation && this.cbtEducation) {
                    this.cbtEducation.closest('.psi-form-group')?.classList.add('psi-field-with-error');
                    valid = false;
                }
                return valid;
            }

            validateStep2() {
                let valid = true;
                if (!this.formData.step2.psychologistExperience && this.psychologistExperience) {
                    this.psychologistExperience.closest('.psi-form-group')?.classList.add('psi-field-with-error');
                    valid = false;
                }
                if (!this.formData.step2.futureWork && this.futureWork) {
                    this.futureWork.closest('.psi-form-group')?.classList.add('psi-field-with-error');
                    valid = false;
                }
                return valid;
            }

            validateStep3() {
                let valid = true;
                this.questionGroups.forEach((group) => {
                    const checked = group.querySelectorAll('input[type="checkbox"]:checked').length;
                    if (!checked) {
                        group.classList.add('error');
                        valid = false;
                    } else {
                        group.classList.remove('error');
                    }
                });
                return valid;
            }

            validateStep4() {
                let valid = true;

                const age = parseInt(this.formData.step4.age, 10);
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!this.formData.step4.fullName && this.fullName) {
                    this.fullName.closest('.psi-form-group')?.classList.add('psi-field-with-error');
                    valid = false;
                }
                if (!this.formData.step4.age || Number.isNaN(age) || age < 18 || age > 100) {
                    this.age.closest('.psi-form-group')?.classList.add('psi-field-with-error');
                    valid = false;
                }
                if (!this.formData.step4.contact && this.contact) {
                    this.contact.closest('.psi-form-group')?.classList.add('psi-field-with-error');
                    valid = false;
                }
                if (!this.formData.step4.email || !emailRegex.test(this.formData.step4.email)) {
                    this.email.closest('.psi-form-group')?.classList.add('psi-field-with-error');
                    valid = false;
                }

                this.hideAgreementError(this.agreePrivacy);
                this.hideAgreementError(this.agreeTerms);

                if (!this.formData.step4.agreePrivacy) {
                    this.showAgreementError(this.agreePrivacy);
                    valid = false;
                }
                if (!this.formData.step4.agreeTerms) {
                    this.showAgreementError(this.agreeTerms);
                    valid = false;
                }

                return valid;
            }

            validateCurrentStep() {
                if (this.currentStep === 1) return this.validateStep1();
                if (this.currentStep === 2) return this.validateStep2();
                if (this.currentStep === 3) return this.validateStep3();
                if (this.currentStep === 4) return this.validateStep4();
                return true;
            }

            nextStep() {
                if (this.isSubmitting) {
                    return;
                }

                if (this.blockResubmit) {
                    this.showSubmitError('Заявка уже отправлена. Заполните форму заново, чтобы отправить еще одну.');
                    return;
                }

                this.saveStepData();
                this.hideSubmitError();

                if (!this.validateCurrentStep()) {
                    this.scrollToTop();
                    return;
                }

                if (this.currentStep < this.totalSteps) {
                    this.currentStep += 1;
                    this.updateUI();
                    this.scrollToTop();
                    return;
                }

                this.submitForm();
            }

            prevStep() {
                if (this.isSubmitting) {
                    return;
                }
                if (this.currentStep > 1) {
                    this.currentStep -= 1;
                    this.updateUI();
                    this.scrollToTop();
                }
            }

            scrollToTop() {
                this.container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            updateUI() {
                this.steps.forEach((step, index) => {
                    const stepNumber = index + 1;
                    step.classList.remove('active', 'completed');
                    if (stepNumber === this.currentStep) {
                        step.classList.add('active');
                    } else if (stepNumber < this.currentStep) {
                        step.classList.add('completed');
                    }
                });

                this.formSteps.forEach((step, index) => {
                    step.classList.toggle('active', index + 1 === this.currentStep);
                });

                if (this.prevBtn) {
                    this.prevBtn.classList.toggle('psi-btn-hidden', this.currentStep === 1);
                }

                if (this.nextBtn) {
                    this.nextBtn.textContent = this.currentStep === this.totalSteps ? 'Отправить заявку' : 'Продолжить →';
                }

                if (this.formFooter) {
                    this.formFooter.classList.toggle('show-agreements', this.currentStep === this.totalSteps);
                }

                this.toggleSupervisionDetails();
                this.toggleRecommendationDetails();
            }

            setSubmitting(isSubmitting) {
                this.isSubmitting = isSubmitting;
                if (this.nextBtn) {
                    this.nextBtn.disabled = isSubmitting;
                    this.nextBtn.textContent = isSubmitting
                        ? 'Отправка...'
                        : (this.currentStep === this.totalSteps ? 'Отправить заявку' : 'Продолжить →');
                }
                if (this.prevBtn) {
                    this.prevBtn.disabled = isSubmitting;
                }
            }

            resetAfterSuccess() {
                this.form.reset();
                this.currentStep = 1;
                this.blockResubmit = true;

                this.form.querySelectorAll('.psi-field-with-error').forEach((group) => {
                    group.classList.remove('psi-field-with-error');
                });
                this.questionGroups.forEach((group) => group.classList.remove('error'));
                this.hideAgreementError(this.agreePrivacy);
                this.hideAgreementError(this.agreeTerms);
                this.hideSubmitError();

                if (this.selectTrigger) {
                    const placeholder = this.selectTrigger.querySelector('.psi-select-placeholder');
                    if (placeholder) {
                        placeholder.textContent = 'Укажите психолога';
                    }
                    this.selectTrigger.classList.remove('has-value', 'active');
                }
                if (this.selectOptions) {
                    this.selectOptions.classList.remove('active');
                }
                this.selectOptionsList.forEach((opt) => opt.classList.remove('selected'));
                if (this.selectOptionsList.length > 0) {
                    this.selectOptionsList[0].classList.add('selected');
                }

                this.saveStepData();
                this.updateUI();
            }

            buildPayload() {
                const questions = Array.from(this.questionGroups).map((group) => {
                    const title = group.querySelector('h3') ? group.querySelector('h3').textContent.trim() : 'Вопрос';
                    const answers = Array.from(group.querySelectorAll('input[type="checkbox"]:checked')).map((cb) => cb.value);
                    return { question: title, answers: answers };
                });

                return {
                    step1: this.formData.step1,
                    step2: this.formData.step2,
                    step3: { questions: questions },
                    step4: {
                        fullName: this.formData.step4.fullName,
                        age: this.formData.step4.age,
                        contact: this.formData.step4.contact,
                        telegram: this.formData.step4.telegram,
                        email: this.formData.step4.email,
                        recommendation: this.formData.step4.recommendation,
                        specialistName: this.formData.step4.specialistName
                    }
                };
            }

            async submitForm() {
                if (this.isSubmitting) {
                    return;
                }

                if (this.blockResubmit) {
                    this.showSubmitError('Заявка уже отправлена. Заполните форму заново, чтобы отправить еще одну.');
                    return;
                }

                this.setSubmitting(true);
                this.hideSubmitError();

                try {
                    const payload = this.buildPayload();
                    const body = new URLSearchParams();
                    body.append('action', 'clinic_submit_psi_form');
                    body.append('nonce', (window.clinic_ajax && window.clinic_ajax.nonce) ? window.clinic_ajax.nonce : '');
                    body.append('payload', JSON.stringify(payload));

                    const response = await fetch((window.clinic_ajax && window.clinic_ajax.ajax_url) ? window.clinic_ajax.ajax_url : '/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: body.toString()
                    });

                    const result = await response.json();

                    if (!response.ok || !result || !result.success) {
                        const serverMessage = result && result.data && result.data.message ? result.data.message : 'Не удалось отправить заявку. Попробуйте позже.';
                        throw new Error(serverMessage);
                    }

                    this.resetAfterSuccess();
                    this.showSuccessModal();
                } catch (error) {
                    this.showSubmitError(error.message || 'Ошибка отправки формы. Попробуйте позже.');
                } finally {
                    this.setSubmitting(false);
                }
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

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.psi-form-container');
            if (!container) {
                return;
            }
            new PsiMultiStepForm(container);
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





