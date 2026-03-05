<div class="online-form-container">
    <!-- Начальный экран выбора типа -->
    <div class="online-form-main-screen" id="mainScreen">
        <h2>Выберите тип консультации</h2>
        <div class="online-form-main-choice-wrap">
            <div class="online-form-main-choice" data-choice="self">
                <div class="online-form-main-choice-ico"><img src="<?= THEME_URI; ?>/assets/img/onlineForm/ico1.svg" alt=""></div>
                <div class="online-form-main-choice-title">Для себя</div>
            </div>
            <div class="online-form-main-choice" data-choice="many">
                <div class="online-form-main-choice-ico"><img src="<?= THEME_URI; ?>/assets/img/onlineForm/ico2.svg" alt=""></div>
                <div class="online-form-main-choice-title">Для пары</div>
            </div>
        </div>
        <div class="online-form-main-choice-text">
            Пожалуйста, заполните анкету — это поможет подобрать психолога для онлайн-консультации с учётом вашего запроса. Вся информация, которую вы укажете, является конфиденциальной и не передается третьим лицам.
        </div>
    </div>

    <!-- Форма "Для себя" (изначально скрыта) -->
    <div class="online-form-self-container" id="selfFormContainer" style="display: none;">
        <div class="online-form-header">
            <div class="online-steps-indicator">
                <div class="online-step active" data-step="1">
                    <div class="online-step-circle">1</div>
                    <div class="online-step-label">Основная информация</div>
                </div>
                <div class="online-step" data-step="2">
                    <div class="online-step-circle">2</div>
                    <div class="online-step-label">Что вас беспокоит</div>
                </div>
                <div class="online-step" data-step="3">
                    <div class="online-step-circle">3</div>
                    <div class="online-step-label">Выбор специалиста</div>
                </div>
                <div class="online-step" data-step="4">
                    <div class="online-step-circle">4</div>
                    <div class="online-step-label">Контакты</div>
                </div>
            </div>
        </div>
        <form id="multiStepOnlineForm">
            <div class="online-form-content">
                <!-- Шаг 1 Основная информация -->
                <div class="online-form-step active" id="step1">
                    <h2>Опишите, пожалуйста, с чем вы хотели бы поработать <span class="required"></span></h2>
                    <div class="online-form-group">
                        <textarea
                                id="workMain"
                                name="workMain"
                                placeholder="Например: стало сложно справляться со стрессом / много тревоги / отношения / упадок сил..."
                        ></textarea>
                        <div class="online-error-message">Это поле обязательно для заполнения</div>
                    </div>

                    <h2>Был ли у вас опыт обращения к психиатру? <span class="required"></span></h2>
                    <div class="online-form-group" id="experiencePsiGroup">
                        <div class="online-radio-group" id="experiencePsiRadioGroup">
                            <label class="online-radio-label">
                                <input type="radio" name="experiencePsi" value="none">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Нет</span>
                            </label>
                            <label class="online-radio-label">
                                <input type="radio" name="experiencePsi" value="past">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Был опыт обращения к психиатру ранее, сейчас не наблюдаюсь</span>
                            </label>
                            <label class="online-radio-label">
                                <input type="radio" name="experiencePsi" value="meds">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Да, и сейчас принимаю препараты, назначенные врачом-психиатром</span>
                            </label>
                            <label class="online-radio-label">
                                <input type="radio" name="experiencePsi" value="noMeds">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Наблюдаюсь, но без медикаментозного лечения</span>
                            </label>
                            <label class="online-radio-label">
                                <input type="radio" name="experiencePsi" value="other">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Другой ответ</span>
                            </label>
                        </div>

                        <div class="online-error-message" id="experiencePsiError">Необходимо выбрать один из вариантов</div>

                        <div class="online-experience-details" id="experienceDetailsContainer" style="display: none;">
                            <textarea
                                    id="experienceDetails"
                                    name="experienceDetails"
                                    placeholder="Например: стало сложно справляться со стрессом / много тревоги / отношения / упадок сил..."
                                    rows="2"
                            ></textarea>
                        </div>

                    </div>

                    <h2>За последние месяцы у вас возникали мысли о самоповреждении или нежелании жить? <span class="required"></span></h2>
                    <div class="online-form-group" id="selfHarmGroup">
                        <div class="online-radio-group" id="selfHarmRadioGroup">
                            <label class="online-radio-label">
                                <input type="radio" name="selfHarm" value="no">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Нет</span>
                            </label>
                            <label class="online-radio-label">
                                <input type="radio" name="selfHarm" value="yes">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Да</span>
                            </label>
                        </div>

                        <div class="online-error-message" id="selfHarmError">Необходимо выбрать один из вариантов</div>

                        <!-- Блок со шкалой (скрыт по умолчанию) -->
                        <div class="online-harm-scale-container" id="harmScaleContainer" style="display: none;">
                            <h3>Укажите, пожалуйста, насколько сильно выражены эти мысли/намерения</h3>
                            <p class="scale-subtitle">1 — Практически не выражены, 5 — Выражены в максимальной степени</p>

                            <!-- Контейнер для ползунка -->
                            <div class="harm-scale-slider-container">
                                <!-- Просто цветная полоса -->
                                <div class="color-scale-bar"></div>

                                <!-- Цифры под полосой -->
                                <div class="scale-ticks-container">
                                    <div class="scale-tick">
                                        <span>1</span>
                                    </div>
                                    <div class="scale-tick">
                                        <span>2</span>
                                    </div>
                                    <div class="scale-tick">
                                        <span>3</span>
                                    </div>
                                    <div class="scale-tick">
                                        <span>4</span>
                                    </div>
                                    <div class="scale-tick">
                                        <span>5</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Сообщение об ошибке -->
                            <div class="online-error-message" id="scaleError" style="display: none;">
                                Пожалуйста, выберите значение шкалы
                            </div>

                        </div>

                        <!-- Блок "Мы не работаем с тяжёлыми состояниями" (скрыт по умолчанию) -->
                        <div class="online-warning-block" id="severeStateWarning" style="display: none;">
                            <div class="warning-icon"><img src="<?= THEME_URI; ?>/assets/img/onlineForm/ico3.svg" alt=""></div>
                            <div class="warning-content">
                                <h3>Мы не работаем с тяжёлыми состояниями</h3>
                                <p>В вашем случае рекомендуем обратиться в специализированное медицинское учреждение для получения неотложной психиатрической помощи.</p>
                                <a href="tel:112" class="emergency-link">
                                    <img src="<?= THEME_URI; ?>/assets/img/onlineForm/ico4.svg" alt="">
                                    <span>Экстренная помощь: 112</span>
                                </a>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Шаг 2 Что вас беспокоит -->
                <div class="online-form-step" id="step2" style="display: none;">
                    <h2 class="small-margin">Что вас беспокоит <span class="required"></span></h2>
                    <p>Отметьте всё, что подходит. Это помогает быстро подобрать специалиста и формат работы.</p>
                    <div class="online-questions-container">
                        <!-- Вопрос 1 -->
                        <div class="online-question-group" data-question="1">
                            <div class="online-checkbox-group">
                                <?php
                                $questions = get_terms(array(
                                    'taxonomy' => 'doctor_diseases',
                                    'hide_empty' => false,
                                    'meta_key' => 'taxonomy_order',
                                    'orderby' => 'meta_value_num',
                                    'order' => 'ASC',
                                    'meta_query' => array(
                                        array(
                                            'key' => 'form_to',
                                            'value' => 0,
                                            'compare' => '=',
                                            'type'    => 'NUMERIC'
                                        )
                                    )
                                ));
                                foreach($questions as $question) {
                                    echo '<label class="online-checkbox-label">';
                                    echo '<input type="checkbox" name="question1[]" value="'.$question->term_id.'">';
                                    echo '<span class="online-checkbox-custom"></span>';
                                    echo '<span class="online-checkbox-text">'.$question->name.'</span>';
                                    echo '</label>';
                                }
                                ?>
                            </div>
                            <div class="online-question-error">Пожалуйста, выберите хотя бы один вариант ответа</div>
                        </div>

                    </div>

                    <div class="online-form-group last" id="visitingGroup">
                        <h2>Посещает ли кто-то из ваших близких психологов нашего Центра?</h2>
                        <div class="online-radio-group width33" id="visitingRadioGroup">
                            <label class="online-radio-label">
                                <input type="radio" name="visitPsi" value="no">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Нет</span>
                            </label>
                            <label class="online-radio-label">
                                <input type="radio" name="visitPsi" value="yesKnow">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Да, знаю психолога</span>
                            </label>
                            <label class="online-radio-label">
                                <input type="radio" name="visitPsi" value="yesDonKnow">
                                <span class="online-radio-custom"></span>
                                <span class="online-radio-text">Да, но не знаю психолога</span>
                            </label>
                        </div>
                        <div class="online-question-error" id="visitingGroupError">Необходимо выбрать один из вариантов</div>
                    </div>

                    <div class="online-recommendation-details" style="display: none;">
                        <label for="selectTrigger">Укажите имя специалиста</label>

                        <!-- Кастомный селект -->
                        <div class="online-custom-select">
                            <div class="online-select-selected" id="selectTrigger">
                                <span class="online-select-placeholder">-- Выберите специалиста из списка --</span>
                                <span class="online-select-arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 15.5002C11.744 15.5002 11.488 15.4023 11.293 15.2073L7.29301 11.2072C6.90201 10.8162 6.90201 10.1842 7.29301 9.79325C7.68401 9.40225 8.31601 9.40225 8.70701 9.79325L12.012 13.0982L15.305 9.91825C15.704 9.53525 16.335 9.54625 16.719 9.94325C17.103 10.3403 17.092 10.9742 16.695 11.3572L12.695 15.2193C12.5 15.4073 12.25 15.5002 12 15.5002Z" fill="#7D7C7F"/>
                                    </svg>
                                </span>
                            </div>
                            <div class="online-select-options" id="selectOptions">
                                <div class="online-select-option" data-value="">Выберите специалиста</div>
                                <?php
                                $specialists = get_posts(array(
                                    'post_type' => 'doctors',
                                    'posts_per_page' => -1,
                                    'orderby' => 'menu_order',
                                    'order' => 'ASC',
                                    'post_status' => 'publish',
                                ));
                                foreach($specialists as $specialist) {
                                    echo '<div class="online-select-option" data-value="'.$specialist->ID.'">'.$specialist->post_title.'</div>';
                                }
                                ?>
                            </div>
                            <input type="hidden" id="specialistName" name="specialistName">
                        </div>
                    </div>

                    <div class="online-recommendation-free" style="display: none;">
                        <label for="recomendFreeName">Если вы не знаете, у кого из специалистов наблюдается ваш близкий, напишите данные клиента, чтобы мы могли самостоятельно проверить эту информацию</label>
                        <input type="text" class="field" id="recomendFreeName" name="recomendFreeName" placeholder="Например: Иванов Иван, телефон +7 999 123-45-67">
                    </div>

                </div>

                <!-- Шаг 3 Выбор специалиста -->
                <div class="online-form-step" id="step3" style="display: none;">
                    <div class="specialist-selection-container">
                        <h2 id="step3MainTitle" class="small-margin">Выбор специалиста</h2>
                        <p id="step3Description" class="step-description">
                            На основе ваших ответов мы подобрали специалистов, которые лучше всего смогут вам помочь
                        </p>

                        <!-- Блок доступных специалистов -->
                        <div id="availableSpecialists" class="specialists-available-section">
                            <div class="specialists-grid" id="availableSpecialistsGrid">
                                <!-- Карточки будут добавляться через JS -->
                            </div>
                        </div>

                        <!-- Блок "Нет доступных специалистов" -->
                        <div id="noSpecialistsMessage" class="no-specialists-message" style="display: none;">
                                <p>К сожалению, на основании вашей анкеты нам не удалось подобрать специалиста.</p>
                                <p>Вы можете обратиться <a href="https://t.me/handlingbetter" target="_blank">к администратору</a> за помощью с подбором.</p>
                        </div>

                        <!-- Блок специалистов в листе ожидания -->
                        <div id="waitingListSection" class="waiting-list-section">
                            <h3>Специалисты, к которым можно записаться в лист ожидания</h3>
                            <div class="waiting-list-grid" id="waitingListGrid">
                                <!-- Карточки будут добавляться через JS -->
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Шаг 4 Контакты и завершение -->
                <div class="online-form-step" id="step4" style="display: none;">
                    <h2>Контакты</h2>

                    <!-- Блок контактных данных (одинаковый для обоих вариантов) -->
                    <div class="online-form-group last">
                        <div class="contact-fields-container">
                            <!-- Имя и фамилия -->
                            <div class="contact-field-group full-width">
                                <label for="clientName">Имя и фамилия <span class="required"></span></label>
                                <input type="text" id="clientName" name="clientName" class="field" placeholder="Например: Анна Смирнова">
                                <div class="online-error-message">Это поле обязательно для заполнения</div>
                            </div>

                            <!-- Возраст -->
                            <div class="contact-field-group">
                                <label for="clientAge">Ваш возраст <span class="required"></span></label>
                                <input type="number" id="clientAge" name="clientAge" class="field" placeholder="Например: 28" min="18" max="120">
                                <div class="online-error-message">Пожалуйста, укажите возраст от 18 до 120 лет</div>
                            </div>

                            <!-- Телефон -->
                            <div class="contact-field-group">
                                <label for="clientPhone">Телефон <span class="required"></span></label>
                                <input type="tel" id="clientPhone" name="clientPhone" class="field" placeholder="+7 (___) ___-__-__">
                                <div class="online-error-message">Пожалуйста, укажите корректный номер телефона</div>
                            </div>

                            <!-- телеграм -->
                            <div class="contact-field-group">
                                <label for="clientTelegram">Telegram</label>
                                <input type="tel" id="clientTelegram" name="clientTelegram" class="field" placeholder="@username">
                            </div>

                            <!-- Email -->
                            <div class="contact-field-group">
                                <label for="clientEmail">Email <span class="required"></span></label>
                                <input type="email" id="clientEmail" name="clientEmail" class="field" placeholder="example@mail.ru">
                                <div class="online-error-message">Пожалуйста, укажите корректный email</div>
                            </div>
                        </div>
                    </div>

                    <!-- Блок информации в зависимости от типа записи -->
                    <div class="online-info-block" id="regularAppointmentInfo" style="display: none;">
                        <h3>Информация об оплате</h3>
                        <p>
                            <b>Мы работаем по предоплате.</b><br>
                            Чтобы завершить бронирование консультации, оплатите ее, пожалуйста, на следующем шаге.
                        </p>
                        <p>
                            За час до начала консультации специалист свяжется с вами и направит ссылку на присоединение к встрече.<br>
                            Если вам нужна оплата картой зарубежного банка, пожалуйста, свяжитесь с администратором в телеграм
                            <a href="http://t.me/handlingbetter" target="_blank">@handlingbetter</a>
                        </p>
                        <p>
                            <b>Важно:</b><br>
                            Мы не возвращаем оплату, если консультация отменена менее чем за 48 часа до начала.<br>
                            Если вы хотите перенести время — свяжитесь с администратором, и мы вам поможем!
                        </p>
                    </div>

                    <div class="online-info-block" id="waitingListInfo" style="display: none;">
                        <h3>📋 Лист ожидания</h3>
                        <p>
                            <b>У выбранного специалиста сейчас нет свободных окон.</b><br>
                            Мы внесем вас в лист ожидания к этому специалисту и свяжемся с вами, как только появится возможность к записи. Срок ожидания ориентировочно от месяца.
                        </p>
                        <p>Также мы можем помочь подобрать другого специалиста с похожей специализацией, у которого есть доступные окна.</p>
                        <p>
                            <b>Что дальше:</b><br>
                            После отправки заявки наш администратор свяжется с вами в течение рабочего дня, чтобы уточнить детали и предложить варианты записи.
                        </p>
                    </div>

                    <!-- Блок выбранного специалиста -->
                    <div class="selected-specialist-summary">
                        <div class="selected-specialist-card" id="selectedSpecialistSummary">
                            <h3>Вы выбрали:</h3>
                            <!-- Динамически заполняется через JS -->
                        </div>
                    </div>

                    <!-- Чекбоксы согласия -->
                    <div class="online-form-group last">
                        <div class="agreement-checkboxes">
                            <label class="online-checkbox-label">
                                <input type="checkbox" id="agreementPrivacy" name="agreementPrivacy">
                                <span class="online-checkbox-custom"></span>
                                <span class="online-checkbox-text">Даю согласие на обработку <a href="" target="_blank">персональных данных</a> в соответствии с <a href="#" target="_blank">политикой</a></span>
                            </label>
                            <div class="online-error-message" id="privacyError" style="display: none;">Необходимо ваше согласие</div>

                            <label class="online-checkbox-label">
                                <input type="checkbox" id="agreementOffer" name="agreementOffer">
                                <span class="online-checkbox-custom"></span>
                                <span class="online-checkbox-text">Соглашаюсь с условиями <a href="#" target="_blank">публичной оферты</a></span>
                            </label>
                            <div class="online-error-message" id="offerError" style="display: none;">Необходимо ваше согласие</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Форма "Для пары" (изначально скрыта) -->
    <div class="online-form-many-container" id="manyFormContainer" style="display: none;">
        <div class="online-form-header">
            <div class="online-steps-indicator">
                <div class="online-step active" data-step="1">
                    <div class="online-step-circle">1</div>
                    <div class="online-step-label">Основная информация</div>
                </div>
                <div class="online-step" data-step="2">
                    <div class="online-step-circle">2</div>
                    <div class="online-step-label">Выбор специалиста</div>
                </div>
                <div class="online-step" data-step="3">
                    <div class="online-step-circle">3</div>
                    <div class="online-step-label">Контакты</div>
                </div>
            </div>
        </div>

        <form id="multiStepManyForm">
            <div class="online-form-content">
                <!-- Шаг 1 Основная информация для пары -->
                <div class="online-form-step active" id="manyStep1">
                    <h2>Опишите, пожалуйста, с чем вы хотели бы поработать <span class="required"></span></h2>
                    <div class="online-form-group">
            <textarea
                    id="manyWorkMain"
                    name="manyWorkMain"
                    placeholder="Например: стало сложно справляться со стрессом / много тревоги / отношения / упадок сил..."
            ></textarea>
                        <div class="online-error-message">Это поле обязательно для заполнения</div>
                    </div>

                    <h2 class="small-margin">Что вас беспокоит? <span class="required"></span></h2>
                    <p>Отметьте всё, что подходит. Это помогает быстро подобрать специалиста и формат работы.</p>

                    <div class="online-form-group" id="manyConcernsGroup">
                        <div class="online-checkbox-group">
                            <?php
                            $questions = get_terms(array(
                                'taxonomy' => 'doctor_diseases',
                                'hide_empty' => false,
                                'meta_key' => 'taxonomy_order',
                                'orderby' => 'meta_value_num',
                                'order' => 'ASC',
                                'meta_query' => array(
                                    array(
                                        'key' => 'form_to',
                                        'value' => 1,
                                        'compare' => '=',
                                        'type'    => 'NUMERIC'
                                    )
                                )
                            ));
                            ?>
                            <?php foreach ($questions as $question) : ?>
                                <label class="online-checkbox-label">
                                    <input type="checkbox" name="manyConcerns[]" value="<?= $question->term_id; ?>">
                                    <span class="online-checkbox-custom"></span>
                                    <span class="online-checkbox-text"><?= $question->name; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="online-error-message" id="manyConcernsError">Пожалуйста, выберите хотя бы один вариант</div>
                    </div>
                </div>

                <!-- Шаг 2 Выбор специалиста (полностью копируем из формы "Для себя") -->
                <div class="online-form-step" id="manyStep2" style="display: none;">
                    <div class="specialist-selection-container">
                        <h2 id="manyStep2MainTitle" class="small-margin">Выбор специалиста</h2>
                        <p id="manyStep2Description" class="step-description">
                            На основе ваших ответов мы подобрали специалистов, которые лучше всего смогут вам помочь
                        </p>

                        <!-- Блок доступных специалистов -->
                        <div id="manyAvailableSpecialists" class="specialists-available-section">
                            <div class="specialists-grid" id="manyAvailableSpecialistsGrid">
                                <!-- Карточки будут добавляться через JS -->
                            </div>
                        </div>

                        <!-- Блок "Нет доступных специалистов" -->
                        <div id="manyNoSpecialistsMessage" class="no-specialists-message" style="display: none;">
                            <p>К сожалению, на основании вашей анкеты нам не удалось подобрать специалиста.</p>
                            <p>Вы можете обратиться <a href="https://t.me/handlingbetter" target="_blank">к администратору</a> за помощью с подбором.</p>
                        </div>

                        <!-- Блок специалистов в листе ожидания -->
                        <div id="manyWaitingListSection" class="waiting-list-section">
                            <h3>Специалисты, к которым можно записаться в лист ожидания</h3>
                            <div class="waiting-list-grid" id="manyWaitingListGrid">
                                <!-- Карточки будут добавляться через JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Шаг 3 Контакты  -->
                <div class="online-form-step" id="manyStep3" style="display: none;">
                    <h2>Контакты</h2>

                    <div class="online-form-group last">
                        <div class="contact-fields-container">
                            <div class="contact-field-group full-width">
                                <label for="manyClient1Name">Имя и фамилия <span class="required"></span></label>
                                <input type="text" id="manyClient1Name" name="manyClient1Name" class="field" placeholder="Например: Анна Смирнова">
                                <div class="online-error-message">Это поле обязательно для заполнения</div>
                            </div>

                            <div class="contact-field-group">
                                <label for="manyClient1Age">Возраст <span class="required"></span></label>
                                <input type="number" id="manyClient1Age" name="manyClient1Age" class="field" placeholder="Например: 28" min="18" max="120">
                                <div class="online-error-message">Пожалуйста, укажите возраст от 18 до 120 лет</div>
                            </div>

                            <div class="contact-field-group">
                                <label for="manyClient1Phone">Телефон <span class="required"></span></label>
                                <input type="tel" id="manyClient1Phone" name="manyClient1Phone" class="field" placeholder="+7 (___) ___-__-__">
                                <div class="online-error-message">Пожалуйста, укажите корректный номер телефона</div>
                            </div>

                            <!-- телеграм -->
                            <div class="contact-field-group">
                                <label for="manyClientTelegram">Telegram</label>
                                <input type="text" id="manyClientTelegram" name="manyClientTelegram" class="field" placeholder="@username">
                            </div>

                            <div class="contact-field-group">
                                <label for="manyClient1Email">Email <span class="required"></span></label>
                                <input type="email" id="manyClient1Email" name="manyClient1Email" class="field" placeholder="example@mail.ru">
                                <div class="online-error-message">Пожалуйста, укажите корректный email</div>
                            </div>
                        </div>
                    </div>

                    <!-- Блок информации в зависимости от типа записи -->
                    <div class="online-info-block" id="manyRegularAppointmentInfo" style="display: none;">
                        <h3>Информация об оплате</h3>
                        <p>
                            <b>Мы работаем по предоплате.</b><br>
                            Чтобы завершить бронирование консультации, оплатите её на следующем шаге.
                        </p>
                        <p>
                            За час до начала на ваш телефон придёт ссылка для входа в Zoom.<br>
                            Если вам нужна оплата картой зарубежного банка, пожалуйста, свяжитесь с администратором.
                        </p>
                        <p>
                            <b>Важно:</b><br>
                            Мы не возвращаем оплату, если консультация отменена менее чем за 24 часа до начала.<br>
                            Если вы хотите перенести время — свяжитесь с администратором, и мы постараемся вам помочь.
                        </p>
                    </div>

                    <div class="online-info-block" id="manyWaitingListInfo" style="display: none;">
                        <h3>📋 Лист ожидания</h3>
                        <p>
                            <b>У выбранного специалиста сейчас нет свободных окон.</b><br>
                            Мы внесем вас в лист ожидания к этому специалисту и свяжемся с вами, как только появится свободное время.
                        </p>
                        <p>Также мы можем помочь подобрать другого специалиста с похожей специализацией, у которого есть доступные окна.</p>
                        <p>
                            <b>Что дальше:</b><br>
                            После отправки заявки наш администратор свяжется с вами в течение рабочего дня, чтобы уточнить детали и предложить варианты записи.
                        </p>
                    </div>

                    <!-- Блок выбранного специалиста -->
                    <div class="selected-specialist-summary">
                        <div class="selected-specialist-card" id="manySelectedSpecialistSummary">
                            <h3>Вы выбрали:</h3>
                            <!-- Динамически заполняется через JS -->
                        </div>
                    </div>

                    <!-- Чекбоксы согласия -->
                    <div class="online-form-group last">
                        <div class="agreement-checkboxes">
                            <label class="online-checkbox-label">
                                <input type="checkbox" id="manyAgreementPrivacy" name="manyAgreementPrivacy">
                                <span class="online-checkbox-custom"></span>
                                <span class="online-checkbox-text">Даю согласие на обработку <a href="" target="_blank">персональных данных</a> в соответствии с <a href="#" target="_blank">политикой</a> <span class="required"></span></span>
                            </label>
                            <div class="online-error-message" id="manyPrivacyError" style="display: none;">Необходимо ваше согласие</div>

                            <label class="online-checkbox-label">
                                <input type="checkbox" id="manyAgreementOffer" name="manyAgreementOffer">
                                <span class="online-checkbox-custom"></span>
                                <span class="online-checkbox-text">Соглашаюсь с условиями <a href="#" target="_blank">публичной оферты</a> <span class="required"></span></span>
                            </label>
                            <div class="online-error-message" id="manyOfferError" style="display: none;">Необходимо ваше согласие</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="online-form-footer">
        <div class="form-navigation">
            <button type="button" class="but online-btn-prev" id="prevBtn" style="display: none;">
                <span class="mobile-arrow">
                    <img src="<?= THEME_URI; ?>/assets/img/onlineForm/arrow.svg" alt="">
                </span>
                <span class="but-text">← Назад</span>
            </button>
            <button type="button" class="but online-btn-next" id="nextBtn">Далее →</button>
        </div>
        <div class="online-form-footer-text">Нажимая далее, вы соглашаетесь с <a href="" target="_blank">политикой конфиденциальности</a></div>
    </div>
</div>