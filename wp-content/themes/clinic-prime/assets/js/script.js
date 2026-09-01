function initScript() {

    mobileMenu();
    sliders();
    filters();
    fixedBut();
    modalImage()
    phoneMask();
    onlineModal();
    psiForm();
    gradeFilters();
    mobileFilters();
    filterScroll();
    scrollToActiveItem();
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
        if (!document.querySelector('.specItemsFilters')) {
            return;
        }

        var filterBtns = document.querySelectorAll('.page_filters__filter--main--btn');

        function initSearchInFilters() {
            var searchInputs = document.querySelectorAll('.filter-search-input');

            searchInputs.forEach(function(searchInput) {
                searchInput.removeEventListener('input', handleSearchInput);
                searchInput.addEventListener('input', handleSearchInput);
            });
        }

        function handleSearchInput(e) {
            var searchText = e.target.value.toLowerCase().trim();
            var filterBlock = e.target.closest('.page_filters__filter--main--items');
            var itemsBlock = filterBlock.querySelector('.page_filters__filter--main--items--block');
            var items = itemsBlock.querySelectorAll('.page_filters__filter--main--items--block--item');

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

        Array.from(filterBtns).forEach(function(section) {
            section.addEventListener('click', function() {
                var diff = Array.from(filterBtns).filter(element => element !== section);
                diff.forEach(function(otherEl) {
                    otherEl.parentNode.classList.remove('opened');
                });

                section.parentNode.classList.toggle('opened');

                if (section.parentNode.classList.contains('opened')) {
                    var searchInput = section.parentNode.querySelector('.filter-search-input');
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();

                        var items = section.parentNode.querySelectorAll('.page_filters__filter--main--items--block--item');
                        items.forEach(function(item) {
                            item.classList.remove('hidden');
                        });
                    }
                }
            });
        });

        document.addEventListener('click', function(e) {
            var filterItem = e.target.closest('.page_filters__filter--main--items--block--item');

            if (filterItem) {
                var filterBlock = filterItem.closest('.page_filters__filter--main');
                var filterBtnSpan = filterBlock.querySelector('.page_filters__filter--main--btn span');
                var label = filterItem.querySelector('label');

                if (label && filterBtnSpan) {
                    filterBtnSpan.innerHTML = label.innerHTML;
                    filterBlock.classList.remove('opened');

                    var searchInput = filterBlock.querySelector('.filter-search-input');
                    if (searchInput) {
                        searchInput.value = '';

                        var items = filterBlock.querySelectorAll('.page_filters__filter--main--items--block--item');
                        items.forEach(function(item) {
                            item.classList.remove('hidden');
                        });
                    }
                }
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.page_filters__filter--main')) {
                filterBtns.forEach(function(btn) {
                    btn.parentNode.classList.remove('opened');
                });
            }
        });

        initSearchInFilters();

        var catalogFilters = document.getElementById('specItemsFilters');
        var specialtyId = catalogFilters ? catalogFilters.getAttribute('data-specialty-id') : '';
        var searchInput = document.querySelector('.specItemsFilters input[name="search"]');
        var problemRadios = document.querySelectorAll('.specItemsFilters input[name="problems"]');
        var searchTimeout;

        function getActiveGradeId() {
            var activeGrade = document.querySelector('.specItemGrade.active');

            if (!activeGrade) {
                return '0';
            }

            return activeGrade.getAttribute('data-grade') || '0';
        }

        function buildFilterParams() {
            var params = new URLSearchParams();

            params.append('action', 'clinic_filter_doctors');

            if (specialtyId) {
                params.append('specs', specialtyId);
            }

            var gradeId = getActiveGradeId();

            if (gradeId && gradeId !== '0') {
                params.append('grade', gradeId);
            }

            if (searchInput) {
                if (searchInput.value.length >= 3) {
                    params.append('search', searchInput.value);
                }
            }

            problemRadios.forEach(function(radio) {
                if (radio.checked && radio.value !== '0') {
                    params.append(radio.name, radio.value);
                }
            });

            return params;
        }

        function shouldSendAjax(params) {
            if (specialtyId) {
                return true;
            }

            return params.has('search') || params.has('problems') || params.has('grade');
        }

        function updateDoctorsCount(count) {
            document.querySelectorAll('.specDoctorsCount').forEach(function(el) {
                el.textContent = count;
            });
        }

        function resetProblemToAll() {
            problemRadios.forEach(function(radio) {
                radio.checked = radio.value === '0';
            });

            var filterBtnSpan = document.querySelector('.page_filters__filter--main--btn span');

            if (filterBtnSpan) {
                filterBtnSpan.innerHTML = 'Что вас беспокоит?';
            }
        }

        function resetGradeToAll() {
            document.querySelectorAll('.specItemGrade').forEach(function(gradeEl) {
                gradeEl.classList.toggle('active', gradeEl.getAttribute('data-grade') === '0');
            });

            if (typeof window.clinicDoctorsGradeDescriptionUpdate === 'function') {
                var allGradesBtn = document.querySelector('.specItemGrade[data-grade="0"]');

                if (allGradesBtn) {
                    window.clinicDoctorsGradeDescriptionUpdate(allGradesBtn);
                }
            }
        }

        function termIdInList(termId, list) {
            if (!Array.isArray(list)) {
                return false;
            }

            return list.indexOf(parseInt(termId, 10)) !== -1;
        }

        function updateFilterAvailability(availability) {
            if (!availability) {
                return false;
            }

            document.querySelectorAll('.page_filters__filter--main--items--block--item[data-term-id]').forEach(function(item) {
                var id = parseInt(item.getAttribute('data-term-id'), 10);
                item.classList.toggle('filter-unavailable', !termIdInList(id, availability.problems));
            });

            var gradesList = Array.isArray(availability.grades) ? availability.grades : [];
            var gradesWrap = document.querySelector('.specItemGrades');

            if (gradesWrap) {
                gradesWrap.style.display = gradesList.length ? '' : 'none';
            }

            document.querySelectorAll('.specItemGrade[data-grade]').forEach(function(el) {
                var grade = el.getAttribute('data-grade');

                if (grade === '0') {
                    return;
                }

                el.classList.toggle('filter-unavailable', !termIdInList(grade, gradesList));
            });

            var needsRefetch = false;
            var activeGradeId = getActiveGradeId();

            if (activeGradeId !== '0' && (gradesList.length === 0 || !termIdInList(activeGradeId, gradesList))) {
                resetGradeToAll();
                needsRefetch = true;
            }

            var checkedProblem = document.querySelector('.specItemsFilters input[name="problems"]:checked');

            if (checkedProblem && checkedProblem.value !== '0' && !termIdInList(checkedProblem.value, availability.problems)) {
                resetProblemToAll();
                needsRefetch = true;
            }

            return needsRefetch;
        }

        function handleAjaxResponse(data, specItemsWrap, isRefetch) {
            if (!data.success) {
                console.error('Ошибка в ответе сервера:', data);
                return false;
            }

            if (data.data.specItems_wrap && specItemsWrap) {
                specItemsWrap.innerHTML = data.data.specItems_wrap;
            }

            var bottomTextResults = document.querySelector('.bottomTextResults');

            if (bottomTextResults) {
                bottomTextResults.style.display = data.data.found ? 'block' : 'none';
            }

            if (typeof data.data.found_count !== 'undefined') {
                updateDoctorsCount(data.data.found_count);
            }

            if (data.data.filter_availability) {
                var needsRefetch = updateFilterAvailability(data.data.filter_availability);

                if (needsRefetch && !isRefetch) {
                    return true;
                }
            }

            return false;
        }

        function sendAjaxRequest(forceRequest, isRefetch) {
            var params = buildFilterParams();

            updateURL(params);

            if (!forceRequest && !shouldSendAjax(params)) {
                return;
            }

            var specItemsWrap = document.querySelector('.specItems_wrap');

            if (specItemsWrap) {
                specItemsWrap.style.opacity = '0.5';
                specItemsWrap.style.pointerEvents = 'none';
            }

            fetch(clinic_ajax.ajax_url + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                var needsRefetch = handleAjaxResponse(data, specItemsWrap, isRefetch);

                if (needsRefetch) {
                    sendAjaxRequest(true, true);
                }
            })
            .catch(function(error) {
                console.error('Ошибка AJAX запроса:', error);
            })
            .finally(function() {
                if (specItemsWrap) {
                    specItemsWrap.style.opacity = '1';
                    specItemsWrap.style.pointerEvents = 'auto';
                }
            });
        }

        window.clinicDoctorsFilterRefresh = sendAjaxRequest;

        function updateURL(params) {
            var url = new URL(window.location);

            url.searchParams.delete('search');
            url.searchParams.delete('problems');
            url.searchParams.delete('grade');

            params.forEach(function(value, key) {
                if (key !== 'action' && key !== 'specs' && value && value !== '0' && String(value).trim() !== '') {
                    url.searchParams.set(key, value);
                }
            });

            window.history.pushState({}, '', url);
        }

        function loadParamsFromURL() {
            var urlParams = new URLSearchParams(window.location.search);
            var searchValue = urlParams.get('search');

            if (searchValue && searchInput) {
                searchInput.value = searchValue;
            }

            var problemsValue = urlParams.get('problems');

            if (problemsValue) {
                problemRadios.forEach(function(radio) {
                    if (radio.value === problemsValue) {
                        radio.checked = true;

                        var filterBtn = radio.closest('.page_filters__filter--main');

                        if (filterBtn) {
                            var btnSpan = filterBtn.querySelector('.page_filters__filter--main--btn span');
                            var label = document.querySelector('label[for="' + radio.id + '"]');

                            if (btnSpan && label) {
                                btnSpan.innerHTML = label.innerHTML;
                            }
                        }
                    }
                });
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);

                if (this.value.length >= 3 || this.value.length === 0) {
                    searchTimeout = setTimeout(function() {
                        sendAjaxRequest(true);
                    }, 500);
                }
            });
        }

        problemRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                sendAjaxRequest(true);
            });
        });

        function resetFilters(e) {
            if (e) {
                e.preventDefault();
            }

            resetProblemToAll();

            if (searchInput) {
                searchInput.value = '';
            }

            resetGradeToAll();

            var filterBtnSpans = document.querySelectorAll('.page_filters__filter--main--btn span');
            filterBtnSpans.forEach(function(span) {
                span.innerHTML = 'Что вас беспокоит?';
            });

            filterBtns.forEach(function(btn) {
                btn.parentNode.classList.remove('opened');
            });

            var bottomTextResults = document.querySelector('.bottomTextResults');

            if (bottomTextResults) {
                bottomTextResults.style.display = 'block';
            }

            var url = new URL(window.location);
            url.searchParams.delete('search');
            url.searchParams.delete('problems');
            url.searchParams.delete('grade');
            window.history.pushState({}, '', url);

            sendAjaxRequest(true);
        }

        document.querySelectorAll('.filterResetLink').forEach(function(link) {
            link.addEventListener('click', resetFilters);
        });

        loadParamsFromURL();
    }

    function gradeFilters() {
        var gradeFiltersEls = document.querySelectorAll('.specItemGrade');
        var descriptionBlock = document.querySelector('.specItemGradeDescription');

        if (!gradeFiltersEls.length || !descriptionBlock) {
            return;
        }

        function updateDescription(gradeElement) {
            var grade = gradeElement.getAttribute('data-grade');

            if (grade === '0' || grade === 'all') {
                descriptionBlock.style.display = 'none';
                descriptionBlock.textContent = '';
            } else {
                var description = gradeElement.getAttribute('data-description') || '';
                descriptionBlock.textContent = description;
                descriptionBlock.style.display = description ? 'block' : 'none';
            }
        }

        window.clinicDoctorsGradeDescriptionUpdate = updateDescription;

        function setActiveGrade(activeElement) {
            gradeFiltersEls.forEach(function(filter) {
                filter.classList.remove('active');
            });
            activeElement.classList.add('active');
            updateDescription(activeElement);
        }

        gradeFiltersEls.forEach(function(filter) {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                setActiveGrade(this);

                if (typeof window.clinicDoctorsFilterRefresh === 'function') {
                    window.clinicDoctorsFilterRefresh(true);
                }
            });
        });

        var activeGrade = document.querySelector('.specItemGrade.active');
        var urlParams = new URLSearchParams(window.location.search);
        var gradeValue = urlParams.get('grade');

        if (gradeValue) {
            document.querySelectorAll('.specItemGrade').forEach(function(gradeEl) {
                gradeEl.classList.toggle('active', gradeEl.getAttribute('data-grade') === gradeValue);
            });
            activeGrade = document.querySelector('.specItemGrade.active');
        }

        if (activeGrade) {
            updateDescription(activeGrade);
        }
    }

    function mobileFilters() {
        var filterButton = document.querySelector('.specItemsFiltersSwitchBut');
        var filterWrap = document.querySelector('.specItemsFiltersSwitchWrap');
        var overlay = document.querySelector('.specItemsFiltersOverlay');

        if (!filterButton || !filterWrap) {
            return;
        }

        function closeFilters() {
            filterWrap.classList.remove('open');
            if (overlay) {
                overlay.classList.remove('active');
            }
            document.body.style.overflow = '';
        }

        function openFilters() {
            filterWrap.classList.add('open');
            if (overlay) {
                overlay.classList.add('active');
            }
            document.body.style.overflow = 'hidden';
        }

        filterButton.addEventListener('click', function(e) {
            e.stopPropagation();
            openFilters();
        });

        if (overlay) {
            overlay.addEventListener('click', closeFilters);
        }

        document.querySelectorAll('.butShow').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (filterWrap.classList.contains('open')) {
                    closeFilters();
                }
            });
        });

        document.querySelectorAll('.modalWrapClose').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (filterWrap.classList.contains('open')) {
                    closeFilters();
                }
            });
        });

        document.addEventListener('click', function(e) {
            var isClickInsideWrap = filterWrap.contains(e.target);
            var isClickOnButton = filterButton.contains(e.target);
            var isClickOnButShow = e.target.closest('.butShow');

            if (!isClickInsideWrap && !isClickOnButton && !isClickOnButShow && filterWrap.classList.contains('open')) {
                closeFilters();
            }
        });
    }

    function filterScroll() {
        var filterScrollButton = document.querySelector('.filterScroll');
        var targetBlock = document.querySelector('#specItemsFilters');
        var headerHeight = 80;

        if (filterScrollButton && targetBlock) {
            filterScrollButton.addEventListener('click', function(e) {
                e.preventDefault();

                var targetPosition = targetBlock.getBoundingClientRect().top + window.scrollY;
                var offsetPosition = targetPosition - headerHeight;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            });
        }

        if (filterScrollButton) {
            window.addEventListener('scroll', function() {
                if (window.scrollY >= 600) {
                    filterScrollButton.classList.add('show');
                } else {
                    filterScrollButton.classList.remove('show');
                }
            });

            if (window.scrollY >= 600) {
                filterScrollButton.classList.add('show');
            }
        }
    }

    function scrollToActiveItem() {
        var container = document.querySelector('.specItemsFiltersTop');

        if (!container) {
            return;
        }

        var activeItem = container.querySelector('.specItemsFiltersTopItem.active');

        if (activeItem) {
            var containerLeft = container.getBoundingClientRect().left;
            var activeLeft = activeItem.getBoundingClientRect().left;
            var scrollOffset = activeLeft - containerLeft - (container.clientWidth / 2) + (activeItem.clientWidth / 2);

            container.scrollTo({
                left: container.scrollLeft + scrollOffset,
                behavior: 'smooth'
            });
        }
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





