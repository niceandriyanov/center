// ==================== КОНСТАНТЫ И КОНФИГУРАЦИЯ ====================
const CONFIG = {
    CONSULTATION_TYPES: { SELF: 'self', MANY: 'many' },
    SCREENS: { MAIN: 'main', SELF: 'self', MANY: 'many' },
    STEPS: { STEP_1: 1, STEP_2: 2, STEP_3: 3, STEP_4: 4 },
    VALIDATION: {
        WORK_MAIN_MIN_LENGTH: 10,
        MIN_AGE: 18,
        MAX_AGE: 120,
        SELF_HARM_THRESHOLD: 4,
        PHONE_MIN_DIGITS: 10
    },
    TIMEOUTS: { SUBMIT: 1500, RESET: 5000, ERROR_DISPLAY: 3000 }
};

const PSYCHIATRIST_EXPERIENCE = {
    MEDS: 'meds', NO_MEDS: 'noMeds', PAST: 'past', NONE: 'none', OTHER: 'other'
};

const CSS_CLASSES = {
    ACTIVE: 'active', SELECTED: 'selected', ERROR: 'error',
    DISABLED: 'disabled', WAITING_SELECTED: 'waiting-selected'
};

// ==================== УТИЛИТЫ ====================
const Utils = {
    // DOM helpers
    $(selector, context = document) { return context.querySelector(selector); },
    $$(selector, context = document) { return context.querySelectorAll(selector); },

    show(el) { if (el) el.style.display = 'block'; return el; },
    hide(el) { if (el) el.style.display = 'none'; return el; },
    toggle(el, show) { if (el) el.style.display = show ? 'block' : 'none'; return el; },

    addClass(el, className) { if (el) el.classList.add(className); return el; },
    removeClass(el, className) { if (el) el.classList.remove(className); return el; },
    toggleClass(el, className, force) { if (el) el.classList.toggle(className, force); return el; },
    hasClass(el, className) { return el?.classList.contains(className) || false; },

    // Event helpers
    on(el, event, handler, options) { el?.addEventListener(event, handler, options); return el; },
    off(el, event, handler) { el?.removeEventListener(event, handler); return el; },

    // Validation helpers
    isNotEmpty(value) { return value && value.trim().length > 0; },
    isEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); },
    isPhone(phone) { return phone.replace(/\D/g, '').length >= CONFIG.VALIDATION.PHONE_MIN_DIGITS; },
    isNumberInRange(num, min, max) { return !isNaN(num) && num >= min && num <= max; },

    // Data helpers
    getCheckedRadio(name, context) { return Utils.$(`input[name="${name}"]:checked`, context); },
    getRadioValue(name, context) { return Utils.getCheckedRadio(name, context)?.value; },
    getCheckboxValues(name, context) {
        return Array.from(Utils.$$(`input[name="${name}"]:checked`, context))
            .map(cb => cb.value);
    },

    // Formatting
    formatPhone(value) {
        let digits = value.replace(/\D/g, '');
        if (digits.startsWith('8')) digits = '7' + digits.substring(1);

        let formatted = '';
        if (digits.length > 0) formatted = '+7 (' + digits.substring(1, 4);
        if (digits.length > 8) formatted = formatted.substring(0, 8) + ') ' + digits.substring(8, 11);
        if (digits.length > 13) formatted = formatted.substring(0, 13) + '-' + digits.substring(13, 15);
        if (digits.length > 16) formatted = formatted.substring(0, 16) + '-' + digits.substring(16, 18);

        return formatted;
    },

    // Animation
    scrollToElement(el, options = { behavior: 'smooth', block: 'center' }) {
        el?.scrollIntoView(options);
        return el;
    },

    // Performance
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Storage
    setData(element, key, value) {
        if (element && key) element.dataset[key] = value;
        return element;
    },
    getData(element, key) {
        return element?.dataset[key];
    }
};

// ==================== КЛАСС SpecialistManager ====================
class SpecialistManager {
    constructor(container, onClickCallback) {
        this.container = container;
        this.onClickCallback = onClickCallback;
    }

    render(specialists, selectedId = null, isWaitingList = false) {
        this.clearContainer();

        if (!specialists || specialists.length === 0) {
            return this.renderNoSpecialists();
        }

        specialists.forEach(specialist => {
            const card = this.createCard(specialist, selectedId === specialist.id, isWaitingList);
            this.container.appendChild(card);
        });

        return this.container;
    }

    createCard(specialist, isSelected = false, isWaitingList = false) {
        const card = document.createElement('div');
        const positionClass = specialist.position === 'clinical' ? 'clinical' : 'psychologist';

        card.className = `specialist-card ${isWaitingList ? 'waiting-list' : ''}`;
        card.dataset.id = specialist.id;
        card.dataset.waitingList = isWaitingList;

        if (isSelected) {
            card.classList.add('selected');
            if (isWaitingList) card.classList.add('waiting-selected');
        }

        card.innerHTML = this.getCardHTML(specialist, positionClass, isWaitingList);

        // Добавляем обработчик клика
        if (this.onClickCallback) {
            card.addEventListener('click', (event) => {
                event.stopPropagation();
                this.onClickCallback(specialist, isWaitingList);
            });
        }

        return card;
    }

    getCardHTML(specialist, positionClass, isWaitingList) {
        return `
            <div class="specialist-header">            
                <img src="${specialist.avatar}" alt="${specialist.name}" loading="lazy">            
                <div class="detail-value">Стаж: ${specialist.experience}</div>                   
            </div>
            <div class="specialist-info">
                <div class="position-with-icon">
                    <span class="position-icon ${positionClass}"></span>
                    <span>${specialist.positionTitle}</span>
                </div>  
                <div class="specialist-info-wrap">
                    <h4 class="specialist-name">${specialist.name}</h4>
                    
                    ${specialist.description ? `
                        <div class="specialist-description">
                            ${specialist.description}
                        </div>
                    ` : ''} 
                    
                    ${!isWaitingList && specialist.nearestSlot ? `
                        <div class="specialist-detail">
                            <span class="detail-time">🕐 Ближайшее окно: ${specialist.nearestSlot}</span>
                        </div>
                    ` : isWaitingList ? `
                        <div class="specialist-detail">
                            <span class="waiting-list-label">📋 Записаться в лист ожидания</span>
                        </div>
                    ` : ''}
                    
                    <div class="specialist-price">${specialist.price.toLocaleString()} ₽</div>
                </div>
            </div>
        `;
    }

    clearContainer() {
        this.container.innerHTML = '';
    }

    renderNoSpecialists() {
        this.container.innerHTML = '<div class="no-specialists-message">Нет доступных специалистов</div>';
        return this.container;
    }

    updateSelection(selectedId, isWaitingList) {
        this.container.querySelectorAll('.specialist-card').forEach(card => {
            card.classList.remove('selected', 'waiting-selected');

            if (card.dataset.id == selectedId) {
                card.classList.add('selected');
                if (isWaitingList) card.classList.add('waiting-selected');
            }
        });
    }
}

// ==================== КЛАСС StepManager ====================
class StepManager {
    constructor(config) {
        this.steps = config.steps;
        this.currentStep = config.initialStep || 1;
        this.onStepChange = config.onStepChange;
        this.stepElements = config.stepElements || [];
        this.indicatorElements = config.indicatorElements || [];
    }

    next() {
        if (this.currentStep < this.steps) {
            this.goTo(this.currentStep + 1);
            return true;
        }
        return false;
    }

    prev() {
        if (this.currentStep > 1) {
            this.goTo(this.currentStep - 1);
            return true;
        }
        return false;
    }

    goTo(step) {
        if (step < 1 || step > this.steps) return;

        const oldStep = this.currentStep;
        this.currentStep = step;

        this.updateUI();

        if (this.onStepChange) {
            this.onStepChange(step, oldStep);
        }
    }

    updateUI() {
        // Обновляем видимость шагов
        if (this.stepElements.length >= this.steps) {
            this.stepElements.forEach((element, index) => {
                const stepNumber = index + 1;
                element.style.display = stepNumber === this.currentStep ? 'block' : 'none';
            });
        }

        // Обновляем индикаторы
        this.indicatorElements.forEach((indicator, index) => {
            const stepNumber = index + 1;
            indicator.classList.remove('active', 'completed');

            if (stepNumber < this.currentStep) {
                indicator.classList.add('completed');
            } else if (stepNumber === this.currentStep) {
                indicator.classList.add('active');
            }
        });
    }

    reset() {
        this.currentStep = 1;
        this.updateUI();
    }
}

// ==================== КЛАСС BookingSystem ====================
class BookingSystem {
    constructor() {
        this.state = this.getInitialState();
        this.elements = {};
        this.calendarInstance = null;
        this.specialistManager = null;
        this.stepManager = null;
        this.manyStepManager = null;
        this.init();
    }

    // ==================== ИНИЦИАЛИЗАЦИЯ ====================
    init() {
        this.initElements();
        this.initManagers();
        this.bindEvents();
        this.initPhoneMasks();
        this.updateUI();
    }

    // ==================== МАСКИ ВВОДА ====================
    initPhoneMasks() {
        // Маска для телефона в форме "Для себя"
        if (this.elements.clientPhone) {
            Inputmask({
                mask: '+7 (999) 999-99-99',
                placeholder: '_',
                showMaskOnHover: false,
                clearIncomplete: true,
                onBeforeMask: function(value, opts) {
                    // Преобразуем 8 в начале в +7
                    if (value.startsWith('8')) {
                        return '7' + value.substring(1);
                    }
                    return value;
                }
            }).mask(this.elements.clientPhone);
        }

        // Маска для телефона в форме "Для пары" - ДОБАВЬТЕ ЭТОТ БЛОК
        if (this.elements.manyClient1Phone) {
            Inputmask({
                mask: '+7 (999) 999-99-99',
                placeholder: '_',
                showMaskOnHover: false,
                clearIncomplete: true,
                onBeforeMask: function(value, opts) {
                    // Преобразуем 8 в начале в +7
                    if (value.startsWith('8')) {
                        return '7' + value.substring(1);
                    }
                    return value;
                }
            }).mask(this.elements.manyClient1Phone);
        }
    }

    getInitialState() {
        return {
            screen: CONFIG.SCREENS.MAIN,
            consultationType: null,

            // Форма "Для себя"
            selfForm: {
                currentStep: CONFIG.STEPS.STEP_1,
                data: this.getInitialSelfFormData(),
                specialists: { available: [], waitingList: [] },
                appointment: null
            },

            // Форма "Для пары"
            manyForm: {
                currentStep: 1,
                data: this.getInitialManyFormData(),
                specialists: { available: [], waitingList: [] },
                appointment: null
            },

            // Общие данные
            shared: {
                selectedSpecialist: null,
                calendar: { selectedDate: null, selectedTime: null, formattedDate: null }
            }
        };
    }

    getInitialSelfFormData() {
        return {
            // Шаг 1
            workMain: '',
            psychiatristExperience: PSYCHIATRIST_EXPERIENCE.MEDS,
            experienceDetails: '',
            selfHarm: 'no',
            selfHarmIntensity: 0,

            // Шаг 2
            concerns: { question1: [] },
            visitingPsychologist: 'no',
            specialistName: '',
            recommendationInfo: '',

            // Шаг 3
            selectedSpecialistId: null,
            selectedSpecialistIsWaitingList: false,
            selectedSpecialistName: '',
            selectedSpecialistPrice: null,

            // Шаг 4
            clientName: '',
            clientAge: '',
            clientPhone: '',
            clientEmail: '',
            clientTelegram: '',
            agreementPrivacy: false,
            agreementOffer: false
        };
    }

    getInitialManyFormData() {
        return {
            // Шаг 1
            workMain: '',
            concerns: [],

            // Шаг 2
            selectedSpecialistId: null,
            selectedSpecialistIsWaitingList: false,
            selectedSpecialistName: '',
            selectedSpecialistPrice: null,

            // Шаг 3
            client1Name: '',
            client1Age: '',
            client1Phone: '',
            client1Email: '',
            clientTelegram: '',
            agreementPrivacy: false,
            agreementOffer: false
        };
    }

    initElements() {
        const e = {};
        const d = document;

        // Основные контейнеры
        e.mainScreen = d.getElementById('mainScreen');
        e.selfFormContainer = d.getElementById('selfFormContainer');
        e.manyFormContainer = d.getElementById('manyFormContainer');
        e.choiceButtons = Utils.$$('.online-form-main-choice');

        // Навигация
        e.prevBtn = d.getElementById('prevBtn');
        e.nextBtn = d.getElementById('nextBtn');
        e.footerText = Utils.$('.online-form-footer-text');

        // Шаги формы "Для себя"
        e.selfSteps = Utils.$$('.online-form-self-container .online-step');
        e.selfStepContainers = Utils.$$('#selfFormContainer .online-form-step');

        // Шаги формы "Для пары"
        e.manySteps = Utils.$$('.online-form-many-container .online-step');
        e.manyStepContainers = Utils.$$('#manyFormContainer .online-form-step');

        // Шаг 1 элементы
        this.initStep1Elements(e, d);

        // Шаг 2 элементы
        this.initStep2Elements(e, d);

        // Шаг 3 элементы
        this.initStep3Elements(e, d);

        // Шаг 4 элементы
        this.initStep4Elements(e, d);

        // Форма "Для пары" элементы
        this.initManyFormElements(e, d);

        // Модальные окна
        this.initModalElements(e, d);

        // Календарь
        e.calendarModal = null;
        e.calendarContainer = null;
        e.calendarSpecialistAvatar = null;
        e.calendarSpecialistName = null;
        e.timeSlotsContainer = null;
        e.calendarConfirmBtn = null;

        this.elements = e;
    }

    initStep1Elements(e, d) {
        e.workMain = d.getElementById('workMain');
        e.psychiatristRadios = Utils.$$('input[name="experiencePsi"]');
        e.experienceDetails = d.getElementById('experienceDetails');
        e.experienceDetailsContainer = d.getElementById('experienceDetailsContainer');
        e.selfHarmRadios = Utils.$$('input[name="selfHarm"]');
        e.harmScaleContainer = d.getElementById('harmScaleContainer');
        e.severeStateWarning = d.getElementById('severeStateWarning');
        e.scaleError = d.getElementById('scaleError');
        e.scaleTicks = Utils.$$('.scale-tick span');

        e.experiencePsiGroup = d.getElementById('experiencePsiGroup');
        e.experiencePsiRadioGroup = d.getElementById('experiencePsiRadioGroup');
        e.experiencePsiError = d.getElementById('experiencePsiError');

        e.selfHarmGroup = d.getElementById('selfHarmGroup');
        e.selfHarmRadioGroup = d.getElementById('selfHarmRadioGroup');
        e.selfHarmError = d.getElementById('selfHarmError');
    }

    initStep2Elements(e, d) {
        e.questionGroups = Utils.$$('.online-question-group');
        e.visitingRadios = Utils.$$('input[name="visitPsi"]');
        e.visitingGroup = d.getElementById('visitingGroup');
        e.visitingRadioGroup = d.getElementById('visitingRadioGroup');
        e.visitingGroupError = d.getElementById('visitingGroupError');
        e.recommendationDetails = Utils.$('.online-recommendation-details');
        e.recommendationFree = Utils.$('.online-recommendation-free');
        e.selectTrigger = d.getElementById('selectTrigger');
        e.selectOptions = d.getElementById('selectOptions');
        e.selectPlaceholder = Utils.$('.online-select-placeholder');
        e.specialistNameInput = d.getElementById('specialistName');
        e.recommendationFreeInput = d.getElementById('recomendFreeName');
    }

    initStep3Elements(e, d) {
        e.step3Container = d.getElementById('step3');
        e.step3MainTitle = d.getElementById('step3MainTitle');
        e.step3Description = d.getElementById('step3Description');
        e.availableSpecialists = d.getElementById('availableSpecialists');
        e.availableSpecialistsGrid = d.getElementById('availableSpecialistsGrid');
        e.noSpecialistsMessage = d.getElementById('noSpecialistsMessage');
        e.waitingListSection = d.getElementById('waitingListSection');
        e.waitingListGrid = d.getElementById('waitingListGrid');
    }

    initStep4Elements(e, d) {
        e.step4Container = d.getElementById('step4');
        e.clientName = d.getElementById('clientName');
        e.clientAge = d.getElementById('clientAge');
        e.clientPhone = d.getElementById('clientPhone');
        e.clientEmail = d.getElementById('clientEmail');
        e.clientTelegram = d.getElementById('clientTelegram');
        e.regularAppointmentInfo = d.getElementById('regularAppointmentInfo');
        e.waitingListInfo = d.getElementById('waitingListInfo');
        e.selectedSpecialistSummary = d.getElementById('selectedSpecialistSummary');
        e.agreementPrivacy = d.getElementById('agreementPrivacy');
        e.agreementOffer = d.getElementById('agreementOffer');
        e.privacyError = d.getElementById('privacyError');
        e.offerError = d.getElementById('offerError');
    }

    initManyFormElements(e, d) {
        // Шаг 1
        e.manyWorkMain = d.getElementById('manyWorkMain');
        e.manyConcernsGroup = d.getElementById('manyConcernsGroup');
        e.manyConcernsError = d.getElementById('manyConcernsError');
        e.manyConcernsCheckboxes = Utils.$$('input[name="manyConcerns[]"]');

        // Шаг 2 (используем те же контейнеры, но с префиксами)
        e.manyStep2MainTitle = d.getElementById('manyStep2MainTitle');
        e.manyStep2Description = d.getElementById('manyStep2Description');
        e.manyAvailableSpecialists = d.getElementById('manyAvailableSpecialists');
        e.manyAvailableSpecialistsGrid = d.getElementById('manyAvailableSpecialistsGrid');
        e.manyNoSpecialistsMessage = d.getElementById('manyNoSpecialistsMessage');
        e.manyWaitingListSection = d.getElementById('manyWaitingListSection');
        e.manyWaitingListGrid = d.getElementById('manyWaitingListGrid');

        // Шаг 3
        e.manyClient1Name = d.getElementById('manyClient1Name');
        e.manyClient1Age = d.getElementById('manyClient1Age');
        e.manyClient1Phone = d.getElementById('manyClient1Phone');
        e.manyClient1Email = d.getElementById('manyClient1Email');
        e.manyClientTelegram = d.getElementById('manyClientTelegram');
        e.manyRegularAppointmentInfo = d.getElementById('manyRegularAppointmentInfo');
        e.manyWaitingListInfo = d.getElementById('manyWaitingListInfo');
        e.manySelectedSpecialistSummary = d.getElementById('manySelectedSpecialistSummary');
        e.manyAgreementPrivacy = d.getElementById('manyAgreementPrivacy');
        e.manyAgreementOffer = d.getElementById('manyAgreementOffer');
        e.manyPrivacyError = d.getElementById('manyPrivacyError');
        e.manyOfferError = d.getElementById('manyOfferError');
    }

    initModalElements(e, d) {
        e.bookingSuccessModal = d.getElementById('bookingSuccessModal');
        e.waitingListSuccessModal = d.getElementById('waitingListSuccessModal');
        e.successDateTime = d.getElementById('successDateTime');
        e.successSpecialistName = d.getElementById('successSpecialistName');
        e.waitingSpecialistName = d.getElementById('waitingSpecialistName');
        e.closeBookingModal = d.getElementById('closeBookingModal');
        e.closeWaitingListModal = d.getElementById('closeWaitingListModal');
    }

    initManagers() {
        // Менеджер специалистов для формы "Для себя"
        this.specialistManager = {
            self: {
                available: new SpecialistManager(
                    this.elements.availableSpecialistsGrid,
                    (specialist, isWaitingList) => this.onSelfSpecialistCardClick(specialist, isWaitingList)
                ),
                waitingList: new SpecialistManager(
                    this.elements.waitingListGrid,
                    (specialist, isWaitingList) => this.onSelfSpecialistCardClick(specialist, isWaitingList)
                )
            },
            many: {
                available: new SpecialistManager(
                    this.elements.manyAvailableSpecialistsGrid,
                    (specialist, isWaitingList) => this.onManySpecialistCardClick(specialist, isWaitingList)
                ),
                waitingList: new SpecialistManager(
                    this.elements.manyWaitingListGrid,
                    (specialist, isWaitingList) => this.onManySpecialistCardClick(specialist, isWaitingList)
                )
            }
        };

        // Step Manager для формы "Для себя"
        this.stepManager = new StepManager({
            steps: 4,
            initialStep: 1,
            stepElements: this.elements.selfStepContainers,
            indicatorElements: this.elements.selfSteps,
            onStepChange: (newStep, oldStep) => {
                this.onSelfStepChange(newStep, oldStep);
            }
        });

        // Step Manager для формы "Для пары"
        this.manyStepManager = new StepManager({
            steps: 3,
            initialStep: 1,
            stepElements: this.elements.manyStepContainers,
            indicatorElements: this.elements.manySteps,
            onStepChange: (newStep, oldStep) => {
                this.onManyStepChange(newStep, oldStep);
            }
        });
    }

    // ==================== ОБРАБОТКА СОБЫТИЙ ====================
    bindEvents() {
        const e = this.elements;

        // Основные события
        e.choiceButtons.forEach(btn => {
            Utils.on(btn, 'click', (event) => this.onTypeSelect(event));
        });

        Utils.on(e.nextBtn, 'click', () => this.onNext());
        Utils.on(e.prevBtn, 'click', () => this.onPrev());

        // Модальные окна
        Utils.on(e.closeBookingModal, 'click', () => this.closeResultModal('booking'));
        Utils.on(e.closeWaitingListModal, 'click', () => this.closeResultModal('waiting'));

        Utils.on(e.bookingSuccessModal, 'click', (event) => {
            if (event.target === e.bookingSuccessModal) this.closeResultModal('booking');
        });

        Utils.on(e.waitingListSuccessModal, 'click', (event) => {
            if (event.target === e.waitingListSuccessModal) this.closeResultModal('waiting');
        });

        // Делегирование событий по шагам
        this.bindStepEvents();
        this.bindSelectEvents();
    }

    bindStepEvents() {
        this.bindStep1Events();
        this.bindStep2Events();
        this.bindStep4Events();
        this.bindManyStepEvents();
    }

    bindStep1Events() {
        const e = this.elements;

        // Текстовые поля с дебаунсом
        this.bindDebouncedInput(e.workMain, 'workMain', () => this.validateWorkMain(), 'self');
        this.bindInput(e.experienceDetails, 'experienceDetails', 'self');

        // Радиокнопки
        this.setupRadioValidation('experiencePsi', () => this.validatePsychiatristExperience(), 'self');
        this.setupRadioValidation('selfHarm', () => this.validateSelfHarm(), 'self');

        // Шкала
        if (e.scaleTicks) {
            e.scaleTicks.forEach((tick, index) => {
                Utils.on(tick, 'click', () => this.onScaleSelect(index + 1));
            });
        }
    }

    bindStep2Events() {
        const e = this.elements;

        // Делегирование событий для шага 2
        if (e.selfStepContainers[1]) {
            Utils.on(e.selfStepContainers[1], 'change', (event) => {
                const target = event.target;

                if (target.type === 'checkbox' && target.closest('.online-question-group')) {
                    this.onQuestionCheckbox(target, 'question1');
                }

                if (target.name === 'visitPsi') {
                    this.onVisitingChange();
                    // Добавить валидацию при изменении
                    setTimeout(() => this.validateVisitingPsychologist(), 50);
                }
            });
        }

        this.bindInput(e.recommendationFreeInput, 'recommendationInfo', 'self');
    }

    bindStep4Events() {
        const e = this.elements;

        // Контактные поля
        this.bindDebouncedInput(e.clientName, 'clientName', () => this.validateClientName(), 'self');
        this.bindDebouncedInput(e.clientAge, 'clientAge', () => this.validateClientAge(), 'self');
        this.bindDebouncedInput(e.clientEmail, 'clientEmail', () => this.validateEmail(), 'self');

        // Телефон без дебаунса
        Utils.on(e.clientPhone, 'input', () => {
            this.state.selfForm.data.clientPhone = e.clientPhone.value;
            this.validatePhoneLazy();
            this.updateUI();
        });

        // Telegram (необязательное)
        this.bindDebouncedInput(e.clientTelegram, 'clientTelegram', null, 'self', false);

        // Чекбоксы
        this.bindCheckbox(e.agreementPrivacy, 'agreementPrivacy', e.privacyError, 'self');
        this.bindCheckbox(e.agreementOffer, 'agreementOffer', e.offerError, 'self');
    }

    bindManyStepEvents() {
        const e = this.elements;

        // Шаг 1 формы "Для пары" - чекбоксы "С чем связан ваш запрос?"
        if (e.manyConcernsGroup) {
            Utils.on(e.manyConcernsGroup, 'change', (event) => {
                const target = event.target;
                if (target.name === 'manyConcerns[]') {
                    this.onManyConcernsChange(target);
                }
            });
        }

        // Шаг 1 формы "Для пары" - текстовое поле
        this.bindDebouncedInput(e.manyWorkMain, 'workMain', () => this.validateManyWorkMain(), 'many');

        // Шаг 3 формы "Для пары" - поля только для первого клиента
        this.bindDebouncedInput(e.manyClient1Name, 'client1Name', () => this.validateManyClientName(), 'many');
        this.bindDebouncedInput(e.manyClient1Age, 'client1Age', () => this.validateManyClientAge(), 'many');
        this.bindDebouncedInput(e.manyClient1Email, 'client1Email', () => this.validateManyEmail(), 'many');

        // Телефон с маской (обработка без дебаунса)
        if (e.manyClient1Phone) {
            Utils.on(e.manyClient1Phone, 'input', () => {
                // Сохраняем значение с маской
                this.state.manyForm.data.client1Phone = e.manyClient1Phone.value;
                // Показываем/скрываем ошибку
                this.validateManyPhoneLazy();
                this.updateUI();
            });

            // При потере фокуса - полная валидация
            Utils.on(e.manyClient1Phone, 'blur', () => {
                this.validateManyPhone();
            });
        }

        // Telegram (необязательное поле) - ДОБАВЛЕНО БЕЗ ВАЛИДАЦИИ
        if (e.manyClientTelegram) {
            this.bindDebouncedInput(e.manyClientTelegram, 'clientTelegram', null, 'many', false);
        }

        // Чекбоксы формы "Для пары"
        this.bindCheckbox(e.manyAgreementPrivacy, 'agreementPrivacy', e.manyPrivacyError, 'many');
        this.bindCheckbox(e.manyAgreementOffer, 'agreementOffer', e.manyOfferError, 'many');
    }

    bindDebouncedInput(element, fieldName, validationFn, formType = 'self', updateState = true) {
        if (!element) return;

        Utils.on(element, 'input', Utils.debounce(() => {
            if (updateState) {
                const formData = formType === 'self' ? this.state.selfForm.data : this.state.manyForm.data;
                formData[fieldName] = element.value.trim();
            }
            if (validationFn) validationFn.call(this);
            this.updateUI();
        }, 300));

        if (validationFn) {
            Utils.on(element, 'blur', () => validationFn.call(this));
        }
    }

    bindInput(element, fieldName, formType = 'self') {
        if (!element) return;
        Utils.on(element, 'input', () => {
            const formData = formType === 'self' ? this.state.selfForm.data : this.state.manyForm.data;
            formData[fieldName] = element.value.trim();
        });
    }

    bindCheckbox(element, fieldName, errorElement, formType = 'self') {
        if (!element) return;
        Utils.on(element, 'change', () => {
            const formData = formType === 'self' ? this.state.selfForm.data : this.state.manyForm.data;
            formData[fieldName] = element.checked;
            if (element.checked && errorElement) Utils.hide(errorElement);
            this.updateUI();
        });
    }

    setupRadioValidation(name, validationFn, formType = 'self') {
        const radios = Utils.$$(`input[name="${name}"]`);
        radios.forEach(radio => {
            Utils.on(radio, 'change', () => {
                if (name === 'experiencePsi') this.onPsychiatristChange();
                if (name === 'selfHarm') this.onSelfHarmChange();

                setTimeout(() => validationFn.call(this), 50);
                this.updateUI();
            });
        });
    }

    bindSelectEvents() {
        const e = this.elements;
        if (!e.selectTrigger || !e.selectOptions) return;

        Utils.on(e.selectTrigger, 'click', (event) => this.onSelectToggle(event));
        Utils.on(e.selectOptions, 'click', (event) => this.onSelectOption(event));

        Utils.on(document, 'click', (event) => {
            if (!e.selectTrigger.contains(event.target) && !e.selectOptions.contains(event.target)) {
                this.closeSelect();
            }
        });
    }

    // ==================== ОБРАБОТЧИКИ СОБЫТИЙ ====================
    onTypeSelect(event) {
        const type = event.currentTarget.dataset.choice;

        this.elements.choiceButtons.forEach(btn => {
            Utils.removeClass(btn, CSS_CLASSES.SELECTED);
        });

        Utils.addClass(event.currentTarget, CSS_CLASSES.SELECTED);
        this.state.consultationType = type;
        this.updateUI();
    }

    onNext() {
        const { screen } = this.state;

        if (screen === CONFIG.SCREENS.MAIN) {
            if (!this.state.consultationType) {
                this.showMainError('Пожалуйста, выберите тип консультации');
                return;
            }
            this.showForm();
        } else if (screen === CONFIG.CONSULTATION_TYPES.SELF) {
            if (!this.validateSelfStep(this.stepManager.currentStep)) {
                this.scrollToFirstError();
                return;
            }

            if (this.state.selfForm.data.selfHarmIntensity >= CONFIG.VALIDATION.SELF_HARM_THRESHOLD) {
                Utils.scrollToElement(this.elements.severeStateWarning);
                return;
            }

            if (this.stepManager.currentStep === 4) {
                this.submitSelfForm();
            } else {
                this.stepManager.next();
            }
        } else if (screen === CONFIG.CONSULTATION_TYPES.MANY) {
            if (!this.validateManyStep(this.manyStepManager.currentStep)) {
                this.scrollToFirstErrorMany();
                return;
            }

            if (this.manyStepManager.currentStep === 3) {
                this.submitManyForm();
            } else {
                this.manyStepManager.next();
            }
        }
    }

    onPrev() {
        const { screen } = this.state;

        if (screen === CONFIG.SCREENS.SELF) {
            if (this.stepManager.currentStep === 1) {
                this.showMain();
            } else {
                this.stepManager.prev();
            }
        } else if (screen === CONFIG.CONSULTATION_TYPES.MANY) {
            if (this.manyStepManager.currentStep === 1) {
                this.showMain();
            } else {
                this.manyStepManager.prev();
            }
        }
    }

    onSelfStepChange(newStep, oldStep) {
        if (newStep === 3) {
            this.loadSelfSpecialists();
        } else if (newStep === 4) {
            this.prepareSelfStep4();
        }
        this.scrollToTop();
        this.updateUI();
    }

    onManyStepChange(newStep, oldStep) {
        if (newStep === 2) {
            this.loadManySpecialists();
        } else if (newStep === 3) {
            this.prepareManyStep3();
        }
        this.scrollToTop();
        this.updateUI();
    }

    onPsychiatristChange() {
        const selected = Utils.getCheckedRadio('experiencePsi');
        if (!selected) return;

        this.state.selfForm.data.psychiatristExperience = selected.value;
        const showDetails = selected.value === PSYCHIATRIST_EXPERIENCE.OTHER;

        Utils.toggle(this.elements.experienceDetailsContainer, showDetails);

        if (!showDetails) {
            this.state.selfForm.data.experienceDetails = '';
            if (this.elements.experienceDetails) this.elements.experienceDetails.value = '';
        }

        this.updateUI();
    }

    onSelfHarmChange() {
        const selected = Utils.getCheckedRadio('selfHarm');
        if (!selected) return;

        this.state.selfForm.data.selfHarm = selected.value;
        const showScale = selected.value === 'yes';

        Utils.toggle(this.elements.harmScaleContainer, showScale);

        if (!showScale) {
            this.resetScale();
            if (this.elements.severeStateWarning) {
                this.elements.severeStateWarning.style.display = 'none';
            }
            Utils.hide(this.elements.scaleError);
        } else {
            this.state.selfForm.data.selfHarmIntensity = 0;
        }

        this.updateUI();
    }

    onScaleSelect(value) {
        if (this.state.selfForm.data.selfHarm !== 'yes') return;

        this.state.selfForm.data.selfHarmIntensity = value;
        this.updateScaleTicks(value);

        if (this.elements.severeStateWarning) {
            this.elements.severeStateWarning.style.display =
                value >= CONFIG.VALIDATION.SELF_HARM_THRESHOLD ? 'flex' : 'none';
        }

        Utils.hide(this.elements.scaleError);
        this.updateUI();
    }

    onQuestionCheckbox(checkbox, questionId = 'question1') {
        const group = checkbox.closest('.online-question-group');
        const value = checkbox.value;
        const isChecked = checkbox.checked;

        const concerns = this.state.selfForm.data.concerns[questionId];

        if (isChecked) {
            if (!concerns.includes(value)) concerns.push(value);
        } else {
            const index = concerns.indexOf(value);
            if (index > -1) concerns.splice(index, 1);
        }

        this.validateQuestionGroup(group);
    }

    onManyConcernsChange(checkbox) {
        const value = checkbox.value;
        const isChecked = checkbox.checked;
        const concerns = this.state.manyForm.data.concerns;

        if (isChecked) {
            if (!concerns.includes(value)) concerns.push(value);
        } else {
            const index = concerns.indexOf(value);
            if (index > -1) concerns.splice(index, 1);
        }

        this.validateManyConcerns();
    }

    onVisitingChange() {
        const selected = Utils.getCheckedRadio('visitPsi');
        if (!selected) return;

        this.state.selfForm.data.visitingPsychologist = selected.value;
        const isYesKnow = selected.value === 'yesKnow';
        const isYesDontKnow = selected.value === 'yesDonKnow';

        // Скрываем ошибку при выборе
        if (this.elements.visitingGroupError) {
            Utils.hide(this.elements.visitingGroupError);
        }

        if (this.elements.visitingRadioGroup) {
            Utils.removeClass(this.elements.visitingRadioGroup, CSS_CLASSES.ERROR);
        }

        Utils.toggle(this.elements.recommendationDetails, isYesKnow);
        Utils.toggle(this.elements.recommendationFree, isYesDontKnow);

        if (!isYesKnow) {
            this.state.selfForm.data.specialistName = '';
            if (this.elements.specialistNameInput) this.elements.specialistNameInput.value = '';
            this.updateSelectDisplay('-- Выберите специалиста из списка --');
        }

        if (!isYesDontKnow) {
            this.state.selfForm.data.recommendationInfo = '';
            if (this.elements.recommendationFreeInput) this.elements.recommendationFreeInput.value = '';
        }

        this.updateUI();
    }

    onSelectToggle(event) {
        event.stopPropagation();
        if (!this.elements.selectOptions) return;

        if (this.isSelectOpen()) {
            this.closeSelect();
        } else {
            this.openSelect();
        }
    }

    onSelectOption(event) {
        const option = event.target.closest('.online-select-option');
        if (!option || option.dataset.value === undefined) return;

        const value = option.dataset.value;
        const text = option.textContent;

        this.state.selfForm.data.specialistName = value;

        if (this.elements.specialistNameInput) {
            this.elements.specialistNameInput.value = value;
        }

        this.updateSelectDisplay(text);
        this.markSelectedOption(option);
        this.closeSelect();
        this.updateUI();
    }

    // ==================== ВАЛИДАЦИЯ (ДЛЯ СЕБЯ) ====================
    validateSelfStep(step) {
        const validators = {
            1: () => this.validateSelfStep1(),
            2: () => this.validateSelfStep2(),
            3: () => this.validateSelfStep3(),
            4: () => this.validateSelfStep4()
        };
        return validators[step] ? validators[step]() : true;
    }

    validateSelfStep1() {
        return [
            this.validateWorkMain(),
            this.validatePsychiatristExperience(),
            this.validateExperienceDetails(),
            this.validateSelfHarm(),
        ].every(Boolean);
    }

    validatePsychiatristExperience() {
        const selected = Utils.getCheckedRadio('experiencePsi');
        const isValid = !!selected;

        if (this.elements.experiencePsiError) {
            Utils.toggle(this.elements.experiencePsiError, !isValid);
        }

        if (this.elements.experiencePsiRadioGroup) {
            Utils.toggleClass(this.elements.experiencePsiRadioGroup, CSS_CLASSES.ERROR, !isValid);
        }

        return isValid;
    }

    validateWorkMain() {
        const value = this.state.selfForm.data.workMain;
        const field = this.elements.workMain;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        if (value.length < CONFIG.VALIDATION.WORK_MAIN_MIN_LENGTH) {
            this.showFieldError(field, false, 'Пожалуйста, напишите более развернуто (минимум 10 символов)');
            return false;
        }

        this.showFieldError(field, true);
        return true;
    }

    validateExperienceDetails() {
        if (this.state.selfForm.data.psychiatristExperience !== PSYCHIATRIST_EXPERIENCE.OTHER) return true;
        return Utils.isNotEmpty(this.state.selfForm.data.experienceDetails);
    }

    validateSelfHarm() {
        const selected = Utils.getCheckedRadio('selfHarm');
        const hasSelection = !!selected;

        if (this.elements.selfHarmError) {
            Utils.toggle(this.elements.selfHarmError, !hasSelection);
        }

        if (this.elements.selfHarmRadioGroup) {
            Utils.toggleClass(this.elements.selfHarmRadioGroup, CSS_CLASSES.ERROR, !hasSelection);
        }

        if (!hasSelection) return false;
        if (selected.value === 'no') return true;

        if (selected.value === 'yes') {
            const hasIntensity = this.state.selfForm.data.selfHarmIntensity > 0;
            if (this.elements.scaleError) {
                Utils.toggle(this.elements.scaleError, !hasIntensity);
            }
            return hasIntensity;
        }

        return false;
    }

    validateSelfStep2() {
        return [
            this.validateAllQuestions(),
            this.validateVisitingPsychologist() // Используем обновленный метод
        ].every(Boolean);
    }

    validateAllQuestions() {
        const group = this.elements.questionGroups?.[0];
        if (!group) return true;
        return this.validateQuestionGroup(group);
    }

    validateQuestionGroup(group) {
        const questionNum = Utils.getData(group, 'question');
        const selectedCount = this.state.selfForm.data.concerns[`question${questionNum}`].length;
        const errorElement = group.querySelector('.online-question-error');

        Utils.toggle(errorElement, selectedCount === 0);
        return selectedCount > 0;
    }

    validateVisitingPsychologist() {
        const selected = Utils.getCheckedRadio('visitPsi');
        const isValid = !!selected;

        const e = this.elements;

        if (e.visitingGroupError) {
            Utils.toggle(e.visitingGroupError, !isValid);
        }

        if (e.visitingRadioGroup) {
            Utils.toggleClass(e.visitingRadioGroup, CSS_CLASSES.ERROR, !isValid);
        }

        // Если выбрано "Да, знаю психолога", нужно проверить имя специалиста
        if (isValid && selected.value === 'yesKnow') {
            return Utils.isNotEmpty(this.state.selfForm.data.specialistName);
        }

        // Если выбрано "Да, но не знаю психолога", нужно проверить рекомендацию
        if (isValid && selected.value === 'yesDonKnow') {
            return Utils.isNotEmpty(this.state.selfForm.data.recommendationInfo);
        }

        return isValid;
    }

    validateSelfStep3() {
        const data = this.state.selfForm.data;
        if (!data.selectedSpecialistId) return false;

        if (data.selectedSpecialistIsWaitingList) {
            return true;
        } else {
            const appt = this.state.selfForm.appointment;
            return !!(appt?.date && appt?.time);
        }
    }

    validateSelfStep4() {
        return [
            this.validateClientName(),
            this.validateClientAge(),
            this.validatePhone(),
            this.validateEmail(),
            this.validateAgreements()
        ].every(Boolean);
    }

    validateClientName() {
        const value = this.elements.clientName.value.trim();
        const field = this.elements.clientName;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        let isValid = true;
        let message = '';

        if (value.length < 2) {
            isValid = false;
            message = 'Пожалуйста, введите корректное имя';
        } else if (!value.includes(' ')) {
            isValid = false;
            message = 'Пожалуйста, введите имя и фамилию';
        }

        this.showFieldError(field, isValid, message);
        return isValid;
    }

    validateClientAge() {
        const value = this.elements.clientAge.value.trim();
        const field = this.elements.clientAge;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        const age = parseInt(value);
        let isValid = true;
        let message = '';

        if (!Utils.isNumberInRange(age, CONFIG.VALIDATION.MIN_AGE, CONFIG.VALIDATION.MAX_AGE)) {
            isValid = false;
            message = 'Пожалуйста, укажите возраст от 18 до 120 лет';
        }

        this.showFieldError(field, isValid, message);
        return isValid;
    }

    validatePhone() {
        const value = this.elements.clientPhone.value.trim();
        const field = this.elements.clientPhone;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        const isValid = Utils.isPhone(value);
        this.showFieldError(field, isValid, 'Пожалуйста, укажите корректный номер телефона');
        return isValid;
    }

    validatePhoneLazy() {
        const field = this.elements.clientPhone;
        const error = field?.parentNode.querySelector('.online-error-message');

        if (field && error) {
            Utils.hide(error);
            Utils.removeClass(field, CSS_CLASSES.ERROR);
        }

        return true;
    }

    validateEmail() {
        const value = this.elements.clientEmail.value.trim();
        const field = this.elements.clientEmail;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        const isValid = Utils.isEmail(value);
        this.showFieldError(field, isValid, 'Пожалуйста, укажите корректный email');
        return isValid;
    }

    validateAgreements() {
        const privacyChecked = this.elements.agreementPrivacy.checked;
        const offerChecked = this.elements.agreementOffer.checked;
        const shouldShowErrors = this.stepManager.currentStep === 4;

        Utils.toggle(this.elements.privacyError, !privacyChecked && shouldShowErrors);
        Utils.toggle(this.elements.offerError, !offerChecked && shouldShowErrors);

        return privacyChecked && offerChecked;
    }

    // ==================== ВАЛИДАЦИЯ (ДЛЯ ПАРЫ) ====================
    validateManyStep(step) {
        const validators = {
            1: () => this.validateManyStep1(),
            2: () => this.validateManyStep2(),
            3: () => this.validateManyStep3()
        };
        return validators[step] ? validators[step]() : true;
    }

    validateManyStep1() {
        return [
            this.validateManyWorkMain(),
            this.validateManyConcerns()
        ].every(Boolean);
    }

    validateManyWorkMain() {
        const value = this.state.manyForm.data.workMain;
        const field = this.elements.manyWorkMain;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        if (value.length < CONFIG.VALIDATION.WORK_MAIN_MIN_LENGTH) {
            this.showFieldError(field, false, 'Пожалуйста, напишите более развернуто (минимум 10 символов)');
            return false;
        }

        this.showFieldError(field, true);
        return true;
    }

    validateManyConcerns() {
        const concerns = this.state.manyForm.data.concerns;
        const hasSelection = concerns.length > 0;

        if (this.elements.manyConcernsError) {
            Utils.toggle(this.elements.manyConcernsError, !hasSelection);
        }

        if (this.elements.manyConcernsGroup) {
            Utils.toggleClass(this.elements.manyConcernsGroup, CSS_CLASSES.ERROR, !hasSelection);
        }

        return hasSelection;
    }

    validateManyStep2() {
        const data = this.state.manyForm.data;
        if (!data.selectedSpecialistId) return false;

        if (data.selectedSpecialistIsWaitingList) {
            return true;
        } else {
            const appt = this.state.manyForm.appointment;
            return !!(appt?.date && appt?.time);
        }
    }

    validateManyStep3() {
        return [
            this.validateManyClientName(),
            this.validateManyClientAge(),
            this.validateManyPhone(),
            this.validateManyEmail(),
            this.validateManyAgreements()
        ].every(Boolean);
    }

    validateManyClientName() {
        const value = this.elements.manyClient1Name.value.trim();
        const field = this.elements.manyClient1Name;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        let isValid = true;
        let message = '';

        if (value.length < 2) {
            isValid = false;
            message = 'Пожалуйста, введите корректное имя';
        } else if (!value.includes(' ')) {
            isValid = false;
            message = 'Пожалуйста, введите имя и фамилию';
        }

        this.showFieldError(field, isValid, message);
        return isValid;
    }

    validateManyClientAge() {
        const value = this.elements.manyClient1Age.value.trim();
        const field = this.elements.manyClient1Age;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        const age = parseInt(value);
        let isValid = true;
        let message = '';

        if (!Utils.isNumberInRange(age, CONFIG.VALIDATION.MIN_AGE, CONFIG.VALIDATION.MAX_AGE)) {
            isValid = false;
            message = 'Пожалуйста, укажите возраст от 18 до 120 лет';
        }

        this.showFieldError(field, isValid, message);
        return isValid;
    }

    // ==================== ВАЛИДАЦИЯ (ДЛЯ ПАРЫ) ====================
    validateManyPhone() {
        const value = this.elements.manyClient1Phone.value.trim();
        const field = this.elements.manyClient1Phone;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        // Убираем маску для проверки
        const cleanPhone = value.replace(/\D/g, '');
        const isValid = cleanPhone.length >= CONFIG.VALIDATION.PHONE_MIN_DIGITS;

        this.showFieldError(field, isValid, 'Пожалуйста, укажите корректный номер телефона');
        return isValid;
    }

    validateManyPhoneLazy() {
        const field = this.elements.manyClient1Phone;
        const error = field?.parentNode.querySelector('.online-error-message');

        if (field && error) {
            Utils.hide(error);
            Utils.removeClass(field, CSS_CLASSES.ERROR);
        }

        return true;
    }

    validateManyEmail() {
        const value = this.elements.manyClient1Email.value.trim();
        const field = this.elements.manyClient1Email;

        if (!Utils.isNotEmpty(value)) {
            this.showFieldError(field, false, 'Это поле обязательно для заполнения');
            return false;
        }

        const isValid = Utils.isEmail(value);
        this.showFieldError(field, isValid, 'Пожалуйста, укажите корректный email');
        return isValid;
    }

    validateManyAgreements() {
        const privacyChecked = this.elements.manyAgreementPrivacy.checked;
        const offerChecked = this.elements.manyAgreementOffer.checked;
        const shouldShowErrors = this.manyStepManager.currentStep === 3;

        Utils.toggle(this.elements.manyPrivacyError, !privacyChecked && shouldShowErrors);
        Utils.toggle(this.elements.manyOfferError, !offerChecked && shouldShowErrors);

        return privacyChecked && offerChecked;
    }

    showFieldError(fieldElement, isValid, message = '') {
        if (!fieldElement) return;

        const errorElement = fieldElement.parentNode.querySelector('.online-error-message');
        if (!errorElement) return;

        if (!isValid && message) {
            errorElement.textContent = message;
            Utils.show(errorElement);
            Utils.addClass(fieldElement, CSS_CLASSES.ERROR);
        } else {
            Utils.hide(errorElement);
            Utils.removeClass(fieldElement, CSS_CLASSES.ERROR);
        }
    }

    // ==================== УПРАВЛЕНИЕ ЭКРАНАМИ ====================
    showMain() {
        const e = this.elements;
        this.state.screen = CONFIG.SCREENS.MAIN;

        Utils.show(e.mainScreen);
        Utils.hide(e.selfFormContainer);
        Utils.hide(e.manyFormContainer);

        Utils.hide(e.prevBtn);
        Utils.show(e.footerText);
        this.updateUI();
    }

    showForm() {
        const e = this.elements;
        this.state.screen = this.state.consultationType;

        if (this.state.consultationType === CONFIG.CONSULTATION_TYPES.SELF) {
            Utils.hide(e.mainScreen);
            Utils.show(e.selfFormContainer);
            Utils.hide(e.manyFormContainer);
            this.stepManager.reset();
        } else if (this.state.consultationType === CONFIG.CONSULTATION_TYPES.MANY) {
            Utils.hide(e.mainScreen);
            Utils.hide(e.selfFormContainer);
            Utils.show(e.manyFormContainer);
            this.manyStepManager.reset();
        }

        Utils.show(e.prevBtn);
        Utils.hide(e.footerText);
        this.updateUI();
    }

    // ==================== СПЕЦИАЛИСТЫ ====================
    async loadSelfSpecialists() {
        try {
            const mockData = this.getMockSpecialistsData();
            this.state.selfForm.specialists = mockData;
            this.renderSelfSpecialists();
        } catch (error) {
            console.error('Ошибка загрузки специалистов:', error);
            this.showOnlyWaitingList('self');
        }
    }

    async loadManySpecialists() {
        try {
            const mockData = this.getMockSpecialistsData();
            this.state.manyForm.specialists = mockData;
            this.renderManySpecialists();
        } catch (error) {
            console.error('Ошибка загрузки специалистов:', error);
            this.showOnlyWaitingList('many');
        }
    }

    getMockSpecialistsData() {
        return {
            available: [
                {
                    id: 1,
                    name: "Новикова Анна",
                    position: "psychologist",
                    positionTitle: "Психолог",
                    experience: "8 лет",
                    avatar: "assets/img/onlineForm/img1.jpg",
                    price: 5500, // Цена для пар
                    nearestSlot: "3 дек, 11:00",
                    description: "Специалист по семейным отношениям"
                },
                {
                    id: 2,
                    name: "Новикова Анна2",
                    position: "clinical",
                    positionTitle: "Клинический психолог",
                    experience: "5 лет",
                    avatar: "assets/img/onlineForm/img1.jpg",
                    price: 6000, // Цена для пар
                    nearestSlot: "5 дек, 11:00",
                    description: "Специалист по парным консультациям"
                }
            ],
            waitingList: [
                {
                    id: 101,
                    name: "Смирнова Елена",
                    position: "clinical",
                    positionTitle: "Клинический психолог",
                    experience: "10 лет",
                    avatar: "assets/img/onlineForm/img1.jpg",
                    price: 6000, // Цена для пар
                    description: "Эксперт в области семейной психологии"
                }
            ]
        };
    }

    renderSelfSpecialists() {
        const { available, waitingList } = this.state.selfForm.specialists;
        const { selectedSpecialistId, selectedSpecialistIsWaitingList } = this.state.selfForm.data;

        if (available.length > 0) {
            this.specialistManager.self.available.render(available, selectedSpecialistId, false);
            Utils.show(this.elements.availableSpecialists);
            Utils.hide(this.elements.noSpecialistsMessage);
        } else {
            this.showNoSpecialistsMessage('self');
        }

        if (waitingList.length > 0) {
            this.specialistManager.self.waitingList.render(waitingList, selectedSpecialistId, true);
            Utils.show(this.elements.waitingListSection);
        } else {
            Utils.hide(this.elements.waitingListSection);
        }
    }

    renderManySpecialists() {
        const { available, waitingList } = this.state.manyForm.specialists;
        const { selectedSpecialistId, selectedSpecialistIsWaitingList } = this.state.manyForm.data;

        if (available.length > 0) {
            this.specialistManager.many.available.render(available, selectedSpecialistId, false);
            Utils.show(this.elements.manyAvailableSpecialists);
            Utils.hide(this.elements.manyNoSpecialistsMessage);
        } else {
            this.showNoManySpecialistsMessage();
        }

        if (waitingList.length > 0) {
            this.specialistManager.many.waitingList.render(waitingList, selectedSpecialistId, true);
            Utils.show(this.elements.manyWaitingListSection);
        } else {
            Utils.hide(this.elements.manyWaitingListSection);
        }
    }

    showNoSpecialistsMessage(formType) {
        Utils.hide(this.elements.availableSpecialists);
        Utils.show(this.elements.noSpecialistsMessage);
    }

    showNoManySpecialistsMessage() {
        Utils.hide(this.elements.manyAvailableSpecialists);
        Utils.show(this.elements.manyNoSpecialistsMessage);
    }

    showOnlyWaitingList(formType) {
        if (formType === 'self') {
            this.state.selfForm.specialists.available = [];
            this.renderSelfSpecialists();
        } else {
            this.state.manyForm.specialists.available = [];
            this.renderManySpecialists();
        }
    }

    onSelfSpecialistCardClick(specialist, isWaitingList = false) {
        const selfData = this.state.selfForm.data;
        selfData.selectedSpecialistId = specialist.id;
        selfData.selectedSpecialistIsWaitingList = isWaitingList;
        selfData.selectedSpecialistName = specialist.name;
        selfData.selectedSpecialistPrice = specialist.price;

        this.state.selfForm.appointment = null;

        if (isWaitingList) {
            this.state.selfForm.appointment = {
                specialistId: specialist.id,
                specialistName: specialist.name,
                isWaitingList: true,
                price: specialist.price,
                type: 'waiting_list'
            };
            this.updateSelectedSelfSpecialistUI(specialist.id, isWaitingList);
            this.updateUI();
        } else {
            this.updateSelectedSelfSpecialistUI(specialist.id, false);
            this.showCalendarModal(specialist, false, 'self');
        }
    }

    onManySpecialistCardClick(specialist, isWaitingList = false) {
        const manyData = this.state.manyForm.data;
        manyData.selectedSpecialistId = specialist.id;
        manyData.selectedSpecialistIsWaitingList = isWaitingList;
        manyData.selectedSpecialistName = specialist.name;
        manyData.selectedSpecialistPrice = specialist.price;

        this.state.manyForm.appointment = null;

        if (isWaitingList) {
            this.state.manyForm.appointment = {
                specialistId: specialist.id,
                specialistName: specialist.name,
                isWaitingList: true,
                price: specialist.price,
                type: 'waiting_list'
            };
            this.updateSelectedManySpecialistUI(specialist.id, isWaitingList);
            this.updateUI();
        } else {
            this.updateSelectedManySpecialistUI(specialist.id, false);
            this.showCalendarModal(specialist, false, 'many');
        }
    }

    updateSelectedSelfSpecialistUI(selectedId, isWaitingList) {
        if (isWaitingList) {
            this.specialistManager.self.waitingList.updateSelection(selectedId, true);
            this.specialistManager.self.available.updateSelection(null, false);
        } else {
            this.specialistManager.self.available.updateSelection(selectedId, false);
            this.specialistManager.self.waitingList.updateSelection(null, true);
        }
    }

    updateSelectedManySpecialistUI(selectedId, isWaitingList) {
        if (isWaitingList) {
            this.specialistManager.many.waitingList.updateSelection(selectedId, true);
            this.specialistManager.many.available.updateSelection(null, false);
        } else {
            this.specialistManager.many.available.updateSelection(selectedId, false);
            this.specialistManager.many.waitingList.updateSelection(null, true);
        }
    }

    getSelectedSelfSpecialist() {
        const { selectedSpecialistId, selectedSpecialistIsWaitingList } = this.state.selfForm.data;
        const specialists = selectedSpecialistIsWaitingList ?
            this.state.selfForm.specialists.waitingList :
            this.state.selfForm.specialists.available;

        return specialists.find(s => s.id == selectedSpecialistId);
    }

    getSelectedManySpecialist() {
        const { selectedSpecialistId, selectedSpecialistIsWaitingList } = this.state.manyForm.data;
        const specialists = selectedSpecialistIsWaitingList ?
            this.state.manyForm.specialists.waitingList :
            this.state.manyForm.specialists.available;

        return specialists.find(s => s.id == selectedSpecialistId);
    }

    // ==================== ПОДГОТОВКА ШАГОВ ====================
    prepareSelfStep4() {
        this.hideStep4Errors('self');
        const isWaitingList = this.state.selfForm.data.selectedSpecialistIsWaitingList;

        Utils.toggle(this.elements.regularAppointmentInfo, !isWaitingList);
        Utils.toggle(this.elements.waitingListInfo, isWaitingList);

        this.elements.nextBtn.textContent = isWaitingList ?
            'Отправить заявку' : 'Оплатить консультацию';

        this.renderSelectedSelfSpecialistSummary();
    }

    prepareManyStep3() {
        this.hideStep4Errors('many');
        const isWaitingList = this.state.manyForm.data.selectedSpecialistIsWaitingList;

        Utils.toggle(this.elements.manyRegularAppointmentInfo, !isWaitingList);
        Utils.toggle(this.elements.manyWaitingListInfo, isWaitingList);

        this.elements.nextBtn.textContent = 'Отправить заявку';
        this.renderSelectedManySpecialistSummary();
    }

    hideStep4Errors(formType = 'self') {
        if (formType === 'self') {
            const fields = ['clientName', 'clientAge', 'clientPhone', 'clientEmail'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                const error = field?.parentNode.querySelector('.online-error-message');
                if (field && error) {
                    Utils.hide(error);
                    Utils.removeClass(field, CSS_CLASSES.ERROR);
                }
            });
            Utils.hide(this.elements.privacyError);
            Utils.hide(this.elements.offerError);
        } else {
            const fields = [
                'manyClient1Name', 'manyClient1Age', 'manyClient1Phone', 'manyClient1Email'
            ];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                const error = field?.parentNode.querySelector('.online-error-message');
                if (field && error) {
                    Utils.hide(error);
                    Utils.removeClass(field, CSS_CLASSES.ERROR);
                }
            });
            Utils.hide(this.elements.manyPrivacyError);
            Utils.hide(this.elements.manyOfferError);
        }
    }

    renderSelectedSelfSpecialistSummary() {
        const appointment = this.state.selfForm.appointment;
        const specialist = this.getSelectedSelfSpecialist();
        const isWaitingList = this.state.selfForm.data.selectedSpecialistIsWaitingList;

        if (!specialist || !this.elements.selectedSpecialistSummary) return;

        const positionClass = specialist.position === 'clinical' ? 'clinical' : 'psychologist';

        let appointmentText = '';
        if (isWaitingList) {
            appointmentText = '<div class="selected-specialist-appointment">📋 Записаться в лист ожидания</div>';
        } else if (appointment && appointment.formattedDate && appointment.time) {
            appointmentText = `<div class="selected-specialist-appointment">📅 ${appointment.formattedDate}, ${appointment.time}</div>`;
        }

        this.elements.selectedSpecialistSummary.innerHTML = `
            <h3>Вы выбрали:</h3>
            <div class="selected-specialist-group">
                <div class="selected-specialist-avatar">
                    <img src="${specialist.avatar}" alt="${specialist.name}">
                </div>
                <div class="selected-specialist-details">
                    <h4 class="selected-specialist-name">${specialist.name}</h4>
                    <div class="selected-specialist-position">
                        <span class="position-icon ${positionClass}"></span>
                        <span>${specialist.positionTitle}</span>
                    </div>
                    ${appointmentText}
                    <div class="selected-specialist-price">${specialist.price.toLocaleString()} ₽</div>
                </div>
            </div>
        `;
    }

    renderSelectedManySpecialistSummary() {
        const appointment = this.state.manyForm.appointment;
        const specialist = this.getSelectedManySpecialist();
        const isWaitingList = this.state.manyForm.data.selectedSpecialistIsWaitingList;

        if (!specialist || !this.elements.manySelectedSpecialistSummary) return;

        const positionClass = specialist.position === 'clinical' ? 'clinical' : 'psychologist';

        let appointmentText = '';
        if (isWaitingList) {
            appointmentText = '<div class="selected-specialist-appointment">📋 Записаться в лист ожидания</div>';
        } else if (appointment && appointment.formattedDate && appointment.time) {
            appointmentText = `<div class="selected-specialist-appointment">📅 ${appointment.formattedDate}, ${appointment.time}</div>`;
        }

        this.elements.manySelectedSpecialistSummary.innerHTML = `
            <h3>Вы выбрали:</h3>
            <div class="selected-specialist-group">
                <div class="selected-specialist-avatar">
                    <img src="${specialist.avatar}" alt="${specialist.name}">
                </div>
                <div class="selected-specialist-details">
                    <h4 class="selected-specialist-name">${specialist.name}</h4>
                    <div class="selected-specialist-position">
                        <span class="position-icon ${positionClass}"></span>
                        <span>${specialist.positionTitle}</span>
                    </div>
                    ${appointmentText}
                    <div class="selected-specialist-price">${specialist.price.toLocaleString()} ₽</div>
                </div>
            </div>
        `;
    }

    // ==================== КАЛЕНДАРЬ ====================
    showCalendarModal(specialist, isWaitingList = false, formType = 'self') {
        if (!this.elements.calendarModal) {
            this.createCalendarModal();
        }

        this.state.shared.selectedSpecialist = specialist;
        this.state.shared.calendar = {
            selectedDate: null,
            selectedTime: null,
            formattedDate: null
        };

        this.state.shared.currentFormType = formType;

        this.elements.calendarSpecialistName.textContent = specialist.name;
        this.elements.calendarSpecialistAvatar.innerHTML = specialist.avatar ?
            `<img src="${specialist.avatar}" alt="${specialist.name}">` :
            `<span>${specialist.name.charAt(0)}</span>`;

        const subtitle = this.elements.calendarModal.querySelector('.calendar-modal-subtitle');
        if (subtitle) {
            subtitle.textContent = isWaitingList ?
                'Выберите дату для записи в лист ожидания' :
                'Выберите удобную дату и время';
        }

        this.resetCalendarState();

        if (this.calendarInstance) {
            this.calendarInstance.clear();
        }

        Utils.addClass(this.elements.calendarModal, CSS_CLASSES.ACTIVE);
        document.body.style.overflow = 'hidden';
    }

    resetCalendarState() {
        this.state.shared.calendar = {
            selectedDate: null,
            selectedTime: null,
            formattedDate: null
        };

        if (this.elements.timeSlotsContainer) {
            this.elements.timeSlotsContainer.innerHTML = `
                <div class="time-placeholder">Выберите дату в календаре</div>
            `;
        }

        if (this.elements.calendarConfirmBtn) {
            this.elements.calendarConfirmBtn.disabled = true;
        }
    }

    createCalendarModal() {
        const modal = document.createElement('div');
        modal.className = 'calendar-modal';
        modal.innerHTML = `
            <div class="calendar-modal-content">
                <div class="calendar-modal-header">
                    <div class="calendar-specialist-info">
                        <div class="calendar-specialist-avatar" id="calendarSpecialistAvatar"></div>
                        <div>
                            <h3 id="calendarSpecialistName" class="calendar-specialist-name"></h3>
                            <p class="calendar-modal-subtitle">Выберите удобную дату и время</p>
                        </div>
                    </div>
                    <button class="close-calendar">&times;</button>
                </div>
                
                <div class="calendar-modal-body">
                    <div class="calendar-left-panel">
                        <div id="calendarContainer" class="custom-calendar"></div>
                    </div>
                    
                    <div class="calendar-right-panel">
                        <h4 class="time-selection-title">Выберите время</h4>
                        <div id="timeSlotsContainer" class="time-slots-container">
                            <div class="time-placeholder">Выберите дату в календаре</div>
                        </div>
                    </div>
                </div>
                
                <div class="calendar-modal-footer">
                    <button id="calendarConfirmBtn" class="calendar-confirm-btn" disabled>Подтвердить выбор</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        this.elements.calendarModal = modal;
        this.elements.calendarContainer = Utils.$('#calendarContainer', modal);
        this.elements.calendarSpecialistAvatar = Utils.$('#calendarSpecialistAvatar', modal);
        this.elements.calendarSpecialistName = Utils.$('#calendarSpecialistName', modal);
        this.elements.timeSlotsContainer = Utils.$('#timeSlotsContainer', modal);
        this.elements.calendarConfirmBtn = Utils.$('#calendarConfirmBtn', modal);

        this.bindCalendarEvents();
        this.initCalendar();
    }

    bindCalendarEvents() {
        const closeBtn = Utils.$('.close-calendar', this.elements.calendarModal);
        const confirmBtn = this.elements.calendarConfirmBtn;

        Utils.on(closeBtn, 'click', () => this.closeCalendarModal());
        Utils.on(confirmBtn, 'click', () => this.confirmCalendarSelection());

        Utils.on(this.elements.calendarModal, 'click', (event) => {
            if (event.target === this.elements.calendarModal) {
                this.closeCalendarModal();
            }
        });
    }

    initCalendar() {
        if (!this.elements.calendarContainer) return;

        const minDate = new Date();
        minDate.setHours(0, 0, 0, 0);

        const disabledDates = this.generateRandomDisabledDates();

        this.calendarInstance = flatpickr(this.elements.calendarContainer, {
            inline: true,
            locale: "ru",
            dateFormat: "Y-m-d",
            minDate: minDate,
            monthSelectorType: 'static',
            nextArrow: '<svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.59402 9.2777L4.69913 8.39276L7.80637 5.28551H0.000976562V3.9929H7.80637L4.69913 0.890625L5.59402 0.000709772L10.2325 4.6392L5.59402 9.2777Z"/></svg>',
            prevArrow: '<svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.63876 9.2777L0.000266373 4.6392L4.63876 0.000709772L5.53365 0.885653L2.4264 3.9929H10.2318V5.28551H2.4264L5.53365 8.38778L4.63876 9.2777Z"/></svg>',
            disable: disabledDates,

            onReady: (selectedDates, dateStr, instance) => {
                this.makeMonthYearReadOnly(instance);
                this.addDisabledDateTitles(instance);
            },

            onChange: (selectedDates) => {
                this.onDateSelected(selectedDates[0]);
            }
        });
    }

    generateRandomDisabledDates() {
        const disabledDates = [];
        const today = new Date();

        const numDates = Math.floor(Math.random() * 3) + 5;

        for (let i = 0; i < numDates; i++) {
            const daysToAdd = Math.floor(Math.random() * 60) + 1;
            const randomDate = new Date(today);
            randomDate.setDate(today.getDate() + daysToAdd);

            if (randomDate.getDay() !== 6 && randomDate.getDay() !== 0) {
                disabledDates.push(this.formatDateForFlatpickr(randomDate));
            }
        }

        return disabledDates;
    }

    formatDateForFlatpickr(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    addDisabledDateTitles(calendarInstance) {
        setTimeout(() => {
            const calendarContainer = calendarInstance.calendarContainer;
            const disabledDays = Utils.$$('.flatpickr-day.disabled', calendarContainer);

            disabledDays.forEach(day => {
                day.title = 'Эта дата недоступна для записи';
            });
        }, 100);
    }

    makeMonthYearReadOnly(calendarInstance) {
        const container = calendarInstance.calendarContainer;

        const monthSelect = Utils.$('.flatpickr-monthDropdown-months', container);
        const yearInput = Utils.$('.numInputWrapper input', container);

        if (monthSelect) {
            monthSelect.disabled = true;
            monthSelect.style.cssText = `
                cursor: default;
                background: transparent;
                border: none;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                pointer-events: none;
            `;
            Utils.on(monthSelect, 'mousedown', (e) => e.preventDefault());
        }

        if (yearInput) {
            yearInput.readOnly = true;
            yearInput.style.cssText = `
                cursor: default;
                pointer-events: none;
                user-select: none;
            `;

            const arrows = Utils.$$('.arrowUp, .arrowDown', container);
            arrows.forEach(arrow => arrow.style.display = 'none');
        }

        const monthYearBlock = Utils.$('.flatpickr-current-month', container);
        if (monthYearBlock) {
            monthYearBlock.style.pointerEvents = 'none';
            monthYearBlock.style.cursor = 'default';
        }
    }

    onDateSelected(date) {
        if (!date) return;

        this.state.shared.calendar.selectedDate = date;
        this.state.shared.calendar.formattedDate = this.formatDate(date);

        this.renderTimeSlots(date);

        if (this.state.shared.calendar.selectedTime) {
            this.elements.calendarConfirmBtn.disabled = false;
        }
    }

    formatDate(date) {
        const day = date.getDate();
        const month = date.toLocaleString('ru', { month: 'long' });
        const year = date.getFullYear();
        return `${day} ${month} ${year}`;
    }

    renderTimeSlots(date) {
        if (!this.elements.timeSlotsContainer) return;

        this.elements.timeSlotsContainer.innerHTML = '';

        const availableSlots = this.getAvailableTimeSlots(date);

        if (availableSlots.length === 0) {
            const noSlots = document.createElement('div');
            noSlots.className = 'time-placeholder';
            noSlots.textContent = 'На выбранную дату нет доступных слотов';
            this.elements.timeSlotsContainer.appendChild(noSlots);
            return;
        }

        const slotsGrid = document.createElement('div');
        slotsGrid.className = 'time-slots-container';

        availableSlots.forEach(slot => {
            const timeSlot = document.createElement('div');
            timeSlot.className = 'time-slot';
            timeSlot.textContent = slot.time;
            timeSlot.dataset.time = slot.time;

            if (slot.available) {
                Utils.on(timeSlot, 'click', () => this.onTimeSlotSelect(slot.time));

                if (this.state.shared.calendar.selectedTime === slot.time) {
                    Utils.addClass(timeSlot, CSS_CLASSES.SELECTED);
                }
            } else {
                Utils.addClass(timeSlot, CSS_CLASSES.DISABLED);
            }

            slotsGrid.appendChild(timeSlot);
        });

        this.elements.timeSlotsContainer.appendChild(slotsGrid);
    }

    getAvailableTimeSlots(date) {
        const slots = [
            { time: '09:00', available: true },
            { time: '10:00', available: true },
            { time: '11:00', available: false },
            { time: '12:00', available: true },
            { time: '13:00', available: true },
            { time: '14:00', available: true },
            { time: '15:00', available: false },
            { time: '16:00', available: true },
            { time: '17:00', available: true }
        ];

        const day = date.getDate();
        if (day % 3 === 0) {
            return slots.slice(0, 5);
        }

        return slots;
    }

    onTimeSlotSelect(time) {
        this.state.shared.calendar.selectedTime = time;

        Utils.$$('.time-slot').forEach(slot => {
            Utils.removeClass(slot, CSS_CLASSES.SELECTED);
            if (slot.dataset.time === time) {
                Utils.addClass(slot, CSS_CLASSES.SELECTED);
            }
        });

        if (this.state.shared.calendar.selectedDate) {
            this.elements.calendarConfirmBtn.disabled = false;
        }
    }

    confirmCalendarSelection() {
        if (!this.state.shared.calendar.selectedDate || !this.state.shared.calendar.selectedTime) {
            alert('Пожалуйста, выберите дату и время');
            return;
        }

        const appointment = {
            specialistId: this.state.shared.selectedSpecialist.id,
            specialistName: this.state.shared.selectedSpecialist.name,
            date: this.state.shared.calendar.selectedDate,
            formattedDate: this.state.shared.calendar.formattedDate,
            time: this.state.shared.calendar.selectedTime,
            isWaitingList: false,
            price: this.state.shared.selectedSpecialist.price,
            type: 'regular'
        };

        if (this.state.shared.currentFormType === 'self') {
            this.state.selfForm.appointment = appointment;
        } else {
            this.state.manyForm.appointment = appointment;
        }

        this.closeCalendarModal();

        if (this.state.shared.currentFormType === 'self') {
            this.stepManager.next();
        } else {
            this.manyStepManager.next();
        }
    }

    closeCalendarModal() {
        if (this.elements.calendarModal) {
            Utils.removeClass(this.elements.calendarModal, CSS_CLASSES.ACTIVE);
            document.body.style.overflow = '';
        }
    }

    // ==================== ОТПРАВКА ФОРМ ====================
    async submitSelfForm() {
        const originalText = this.elements.nextBtn.textContent;
        this.elements.nextBtn.disabled = true;
        this.elements.nextBtn.textContent = 'Отправка...';

        try {
            const isWaitingList = this.state.selfForm.data.selectedSpecialistIsWaitingList;

            // ДЕМО-РЕЖИМ
            setTimeout(() => {
                if (isWaitingList) {
                    this.showWaitingListSuccessModal('self');
                } else {
                    this.showBookingSuccessModal('self');
                }
                // ИСПРАВЛЕНО: сбрасываем только форму, но не показываем главный экран
                setTimeout(() => {
                    this.reset(); // сбрасываем состояние
                    this.showMain(); // показываем главный экран
                }, CONFIG.TIMEOUTS.RESET);
            }, CONFIG.TIMEOUTS.SUBMIT);

        } catch (error) {
            console.error('Ошибка отправки:', error);
            alert('Произошла ошибка при отправке. Пожалуйста, попробуйте ещё раз.');
            this.elements.nextBtn.disabled = false;
            this.elements.nextBtn.textContent = originalText;
        }
    }

    async submitManyForm() {
        const originalText = this.elements.nextBtn.textContent;
        this.elements.nextBtn.disabled = true;
        this.elements.nextBtn.textContent = 'Отправка...';

        try {
            const isWaitingList = this.state.manyForm.data.selectedSpecialistIsWaitingList;

            // ДЕМО-РЕЖИМ
            setTimeout(() => {
                if (isWaitingList) {
                    this.showWaitingListSuccessModal('many');
                } else {
                    this.showBookingSuccessModal('many');
                }
                // ИСПРАВЛЕНО: сбрасываем только форму, но не показываем главный экран
                setTimeout(() => {
                    this.reset(); // сбрасываем состояние
                    this.showMain(); // показываем главный экран
                }, CONFIG.TIMEOUTS.RESET);
            }, CONFIG.TIMEOUTS.SUBMIT);

        } catch (error) {
            console.error('Ошибка отправки формы для пары:', error);
            alert('Произошла ошибка при отправке. Пожалуйста, попробуйте ещё раз.');
            this.elements.nextBtn.disabled = false;
            this.elements.nextBtn.textContent = originalText;
        }
    }

    // ==================== МОДАЛЬНЫЕ ОКНА ====================
    showBookingSuccessModal(formType = 'self') {
        const e = this.elements;
        if (!e.bookingSuccessModal) return;

        const appointment = formType === 'self' ? this.state.selfForm.appointment : this.state.manyForm.appointment;
        const specialist = formType === 'self' ? this.getSelectedSelfSpecialist() : this.getSelectedManySpecialist();

        if (e.successDateTime && appointment) {
            e.successDateTime.textContent = `${appointment.formattedDate || ''}, ${appointment.time || ''}`;
        }

        if (e.successSpecialistName && specialist) {
            e.successSpecialistName.textContent = specialist.name;
        }

        Utils.addClass(e.bookingSuccessModal, CSS_CLASSES.ACTIVE);
        document.body.style.overflow = 'hidden';
    }

    showWaitingListSuccessModal(formType = 'self') {
        const e = this.elements;
        if (!e.waitingListSuccessModal) return;

        const specialist = formType === 'self' ? this.getSelectedSelfSpecialist() : this.getSelectedManySpecialist();

        if (e.waitingSpecialistName && specialist) {
            e.waitingSpecialistName.textContent = specialist.name;
        }

        Utils.addClass(e.waitingListSuccessModal, CSS_CLASSES.ACTIVE);
        document.body.style.overflow = 'hidden';
    }

    closeResultModal(type) {
        const e = this.elements;

        if (type === 'booking' && e.bookingSuccessModal) {
            Utils.removeClass(e.bookingSuccessModal, CSS_CLASSES.ACTIVE);
        } else if (type === 'waiting' && e.waitingListSuccessModal) {
            Utils.removeClass(e.waitingListSuccessModal, CSS_CLASSES.ACTIVE);
        }

        document.body.style.overflow = '';
    }

    resetFormAfterDelay() {
        setTimeout(() => {
            this.reset();
            this.showMain();
        }, CONFIG.TIMEOUTS.RESET);
    }

    // ==================== ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ====================
    showMainError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'main-screen-error';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: #ff5252;
            margin-top: 10px;
            padding: 8px 12px;
            background: #ffebee;
            border-radius: 6px;
            border-left: 3px solid #ff5252;
        `;

        const choiceWrap = Utils.$('.online-form-main-choice-wrap');
        if (choiceWrap) {
            choiceWrap.parentNode.insertBefore(errorDiv, choiceWrap.nextSibling);
            setTimeout(() => errorDiv.remove(), CONFIG.TIMEOUTS.ERROR_DISPLAY);
        }
    }

    scrollToFirstError() {
        const e = this.elements;

        if (e.scaleError && Utils.hasClass(e.scaleError, CSS_CLASSES.ACTIVE)) {
            Utils.scrollToElement(e.scaleError);
            return;
        }

        const firstError = Utils.$('.online-error-message[style*="block"]');
        if (firstError) {
            const field = firstError.closest('.online-form-group, .online-question-group');
            Utils.scrollToElement(field);
        }
    }

    scrollToFirstErrorMany() {
        const firstError = Utils.$('#manyFormContainer .online-error-message[style*="block"]');
        if (firstError) {
            const field = firstError.closest('.online-form-group, .online-question-group');
            Utils.scrollToElement(field);
        }
    }

    scrollToTop() {
        if (this.state.screen === CONFIG.SCREENS.SELF && this.elements.selfFormContainer) {
            Utils.scrollToElement(this.elements.selfFormContainer, {
                behavior: 'smooth',
                block: 'start'
            });
        } else if (this.state.screen === CONFIG.CONSULTATION_TYPES.MANY && this.elements.manyFormContainer) {
            Utils.scrollToElement(this.elements.manyFormContainer, {
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    updateSelectDisplay(text) {
        if (!this.elements.selectPlaceholder) return;

        this.elements.selectPlaceholder.textContent = text;
        Utils.removeClass(this.elements.selectPlaceholder, 'online-select-placeholder');
        Utils.addClass(this.elements.selectPlaceholder, 'online-select-selected-text');
    }

    markSelectedOption(selectedOption) {
        if (!this.elements.selectOptions) return;

        Utils.$$('.online-select-option', this.elements.selectOptions).forEach(opt => {
            Utils.removeClass(opt, CSS_CLASSES.SELECTED);
        });

        Utils.addClass(selectedOption, CSS_CLASSES.SELECTED);
    }

    isSelectOpen() {
        const e = this.elements;
        return e.selectOptions?.style.display === 'block' ||
            Utils.hasClass(e.selectOptions, CSS_CLASSES.ACTIVE);
    }

    openSelect() {
        const e = this.elements;
        if (!e.selectOptions || !e.selectTrigger) return;

        Utils.show(e.selectOptions);
        Utils.addClass(e.selectOptions, CSS_CLASSES.ACTIVE);
        Utils.addClass(e.selectTrigger, CSS_CLASSES.ACTIVE);

        const arrow = Utils.$('.online-select-arrow', e.selectTrigger);
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    }

    closeSelect() {
        const e = this.elements;

        if (e.selectOptions) {
            Utils.hide(e.selectOptions);
            Utils.removeClass(e.selectOptions, CSS_CLASSES.ACTIVE);
        }

        if (e.selectTrigger) {
            Utils.removeClass(e.selectTrigger, CSS_CLASSES.ACTIVE);

            const arrow = Utils.$('.online-select-arrow', e.selectTrigger);
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    resetSelect() {
        const e = this.elements;

        if (e.selectOptions) {
            Utils.$$('.online-select-option', e.selectOptions).forEach(opt => {
                Utils.removeClass(opt, CSS_CLASSES.SELECTED);
            });
        }

        this.closeSelect();
        this.updateSelectDisplay('-- Выберите специалиста из списка --');
    }

    updateScaleTicks(value) {
        if (!this.elements.scaleTicks) return;

        this.elements.scaleTicks.forEach((tick, index) => {
            Utils.toggleClass(tick, CSS_CLASSES.ACTIVE, index + 1 === value);
        });
    }

    resetScale() {
        this.state.selfForm.data.selfHarmIntensity = 0;
        if (this.elements.scaleTicks) {
            this.elements.scaleTicks.forEach(tick => {
                Utils.removeClass(tick, CSS_CLASSES.ACTIVE);
            });
        }
    }

    // ==================== UI УПРАВЛЕНИЕ ====================
    updateUI() {
        const e = this.elements;
        const { screen, consultationType } = this.state;
        const selfData = this.state.selfForm.data;
        const manyData = this.state.manyForm.data;

        let disabled = false;
        let text = 'Далее →';

        if (screen === CONFIG.SCREENS.MAIN) {
            disabled = !consultationType;
        } else if (screen === CONFIG.SCREENS.SELF) {
            if (selfData.selfHarmIntensity >= CONFIG.VALIDATION.SELF_HARM_THRESHOLD) {
                disabled = true;
                text = 'Продолжить нельзя';
            } else if (this.stepManager.currentStep === 3) {
                disabled = !selfData.selectedSpecialistId;
                text = selfData.selectedSpecialistId ? 'Далее →' : 'Выберите специалиста';
            } else if (this.stepManager.currentStep === 4) {
                disabled = false;
                text = selfData.selectedSpecialistIsWaitingList ?
                    'Отправить заявку' : 'Оплатить консультацию';
            }
        } else if (screen === CONFIG.CONSULTATION_TYPES.MANY) {
            if (this.manyStepManager.currentStep === 2) {
                disabled = !manyData.selectedSpecialistId;
                text = manyData.selectedSpecialistId ? 'Далее →' : 'Выберите специалиста';
            } else if (this.manyStepManager.currentStep === 3) {
                disabled = false;
                text = manyData.selectedSpecialistIsWaitingList ?
                    'Отправить заявку' : 'Оплатить консультацию';
            }
        }

        if (e.nextBtn) {
            e.nextBtn.disabled = disabled;
            e.nextBtn.textContent = text;
        }

        Utils.toggle(e.prevBtn, screen !== CONFIG.SCREENS.MAIN);
    }

    // ==================== СБРОС И ОЧИСТКА ====================
    reset() {
        this.state = this.getInitialState();
        this.stepManager.reset();
        this.manyStepManager.reset();
        this.resetFormFields();
        this.resetUI();
        this.closeCalendarModal();
        this.updateUI();
    }

    resetFormFields() {
        // Сброс формы "Для себя"
        const selfData = this.state.selfForm.data;
        Object.keys(selfData).forEach(key => {
            if (Array.isArray(selfData[key])) {
                selfData[key] = [];
            } else if (typeof selfData[key] === 'object' && selfData[key] !== null && !(selfData[key] instanceof Date)) {
                if (key === 'concerns') {
                    selfData[key] = { question1: [] };
                } else {
                    selfData[key] = {};
                }
            } else if (typeof selfData[key] === 'boolean') {
                selfData[key] = false;
            } else if (typeof selfData[key] === 'number') {
                selfData[key] = 0;
            } else {
                selfData[key] = '';
            }
        });

        // Сброс формы "Для пары"
        const manyData = this.state.manyForm.data;
        Object.keys(manyData).forEach(key => {
            if (Array.isArray(manyData[key])) {
                manyData[key] = [];
            } else if (typeof manyData[key] === 'object' && manyData[key] !== null && !(manyData[key] instanceof Date)) {
                manyData[key] = {};
            } else if (typeof manyData[key] === 'boolean') {
                manyData[key] = false;
            } else if (typeof manyData[key] === 'number') {
                manyData[key] = 0;
            } else {
                manyData[key] = '';
            }
        });

        // Сброс UI полей
        const fieldsToReset = [
            [this.elements.workMain, ''],
            [this.elements.experienceDetails, ''],
            [this.elements.specialistNameInput, ''],
            [this.elements.recommendationFreeInput, ''],
            [this.elements.clientName, ''],
            [this.elements.clientAge, ''],
            [this.elements.clientPhone, ''],
            [this.elements.clientEmail, ''],
            [this.elements.clientTelegram, ''],
            [this.elements.manyWorkMain, ''],
            [this.elements.manyClient1Name, ''],
            [this.elements.manyClient1Age, ''],
            [this.elements.manyClient1Phone, ''],
            [this.elements.manyClient1Email, ''],
            [this.elements.manyClientTelegram, '']
        ];

        fieldsToReset.forEach(([element, value]) => {
            if (element) {
                element.value = '';
            }
        });

        // Сброс радио-кнопок
        if (this.elements.psychiatristRadios[0]) this.elements.psychiatristRadios[0].checked = true;
        if (this.elements.selfHarmRadios[0]) this.elements.selfHarmRadios[0].checked = true;
        if (this.elements.visitingRadios[0]) this.elements.visitingRadios[0].checked = true;

        // Сброс радио-кнопок visiting
        if (this.elements.visitingGroupError) {
            Utils.hide(this.elements.visitingGroupError);
        }

        if (this.elements.visitingRadioGroup) {
            Utils.removeClass(this.elements.visitingRadioGroup, CSS_CLASSES.ERROR);
        }

        // Сброс чекбоксов
        if (this.elements.agreementPrivacy) this.elements.agreementPrivacy.checked = false;
        if (this.elements.agreementOffer) this.elements.agreementOffer.checked = false;
        if (this.elements.manyAgreementPrivacy) this.elements.manyAgreementPrivacy.checked = false;
        if (this.elements.manyAgreementOffer) this.elements.manyAgreementOffer.checked = false;

        // Сброс чекбоксов вопросов
        const questionGroup = Utils.$('.online-question-group');
        if (questionGroup) {
            const checkboxes = questionGroup.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
        }

        // Сброс чекбоксов формы "Для пары"
        if (this.elements.manyConcernsCheckboxes) {
            this.elements.manyConcernsCheckboxes.forEach(cb => cb.checked = false);
        }
    }

    resetUI() {
        const e = this.elements;

        // Скрыть дополнительные блоки
        const elementsToHide = [
            e.experienceDetailsContainer,
            e.harmScaleContainer,
            e.severeStateWarning,
            e.recommendationDetails,
            e.recommendationFree,
            e.regularAppointmentInfo,
            e.waitingListInfo,
            e.manyRegularAppointmentInfo,
            e.manyWaitingListInfo
        ];

        elementsToHide.forEach(el => Utils.hide(el));

        // Сброс шкалы
        this.resetScale();

        // Очистка динамического контента
        if (e.selectedSpecialistSummary) e.selectedSpecialistSummary.innerHTML = '';
        if (e.manySelectedSpecialistSummary) e.manySelectedSpecialistSummary.innerHTML = '';

        // Сброс селекта
        this.resetSelect();

        // Сброс карточек специалистов
        Utils.$$('.specialist-card').forEach(card => {
            Utils.removeClass(card, CSS_CLASSES.SELECTED, CSS_CLASSES.WAITING_SELECTED);
        });

        // Сброс выбора типа консультации
        this.elements.choiceButtons.forEach(btn => {
            Utils.removeClass(btn, CSS_CLASSES.SELECTED);
        });
    }
}

// ==================== ИНИЦИАЛИЗАЦИЯ ====================
document.addEventListener('DOMContentLoaded', () => {
    window.bookingSystem = new BookingSystem();
});