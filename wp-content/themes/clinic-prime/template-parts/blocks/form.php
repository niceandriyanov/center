<?php
/**
 * Шаблон для блока FAQ
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="psiFormSection" id="psiFormSection">
    <div class="container small">
        <div class="psiFormHeader">
            <h2 class="sectionTitle">Заполнить анкету</h2>
            <div class="psiFormHeaderText">Заполнение анкеты может занять до 20 минут</div>
        </div>

        <div class="psi-form-container">
            <div class="psi-form-header">
                <div class="psi-steps-indicator">
                    <div class="psi-step active" data-step="1">
                        <div class="psi-step-circle">1</div>
                        <div class="psi-step-label">Образование</div>
                    </div>
                    <div class="psi-step" data-step="2">
                        <div class="psi-step-circle">2</div>
                        <div class="psi-step-label">Опыт работы</div>
                    </div>
                    <div class="psi-step" data-step="3">
                        <div class="psi-step-circle">3</div>
                        <div class="psi-step-label">Этический тест</div>
                    </div>
                    <div class="psi-step" data-step="4">
                        <div class="psi-step-circle">4</div>
                        <div class="psi-step-label">Личные данные</div>
                    </div>
                </div>
            </div>

            <form id="multiStepForm">
                <div class="psi-form-content">
                    <!-- Шаг 1 Образование -->
                    <div class="psi-form-step active" id="step1">
                        <h2>Основное образование <span class="required"></span></h2>
                        <div class="psi-form-group">
                            <textarea
                                    id="basicEducation"
                                    name="basicEducation"
                                    placeholder="Укажите ВУЗ, факультет, год окончания, специальность&#10;&#10;Например:&#10;МГУ им. М.В. Ломоносова, факультет психологии, 2018 г., клинический психолог"
                            ></textarea>
                            <div class="psi-error-message">Это поле обязательно для заполнения</div>
                        </div>

                        <h2>Дополнительное образование по КПТ <span class="required"></span></h2>
                        <div class="psi-form-group">
                            <textarea
                                    id="cbtEducation"
                                    name="cbtEducation"
                                    placeholder="Укажите учебное заведение, количество часов обучения&#10;&#10;Например:&#10;Институт когнитивно-поведенческой психотерапии, программа КПТ, 240 часов, 2020 г."
                            ></textarea>
                            <div class="psi-error-message">Это поле обязательно для заполнения</div>
                        </div>

                        <h2>Дополнительное образование в других подходах</h2>
                        <div class="psi-form-group">
                            <textarea
                                    id="otherEducation"
                                    name="otherEducation"
                                    placeholder="Если есть, укажите их. Если нет, оставьте поле пустым"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Шаг 2 Опыт работы  -->
                    <div class="psi-form-step" id="step2">
                        <h2>Опыт работы психологом <span class="required"></span></h2>
                        <div class="psi-form-group">
                        <textarea
                                id="psychologistExperience"
                                name="psychologistExperience"
                                placeholder="Опишите:&#10;- Когда начали практику индивидуальных консультаций&#10;- В каких подходах работали&#10;- Как давно ведёте клиентов в рамках КПТ&#10;- С какими запросами в основном работали"
                        ></textarea>
                            <div class="psi-error-message">Это поле обязательно для заполнения</div>
                        </div>

                        <h2>С какими клиентами/запросами хотите работать в дальнейшем? <span class="required"></span></h2>
                        <div class="psi-form-group">
                        <textarea
                                id="futureWork"
                                name="futureWork"
                                placeholder="Опишите направления работы, которые вас интересуют"
                        ></textarea>
                            <div class="psi-error-message">Это поле обязательно для заполнения</div>
                        </div>

                        <h2>Проходите ли вы индивидуальную супервизию?</h2>
                        <div class="psi-form-group psi-supervision-group">
                            <div class="psi-radio-group">
                                <label class="psi-radio-label">
                                    <input type="radio" name="supervision" value="no" checked>
                                    <span class="psi-radio-custom"></span>
                                    <span class="psi-radio-text">Нет, не прохожу</span>
                                </label>
                                <label class="psi-radio-label">
                                    <input type="radio" name="supervision" value="yes">
                                    <span class="psi-radio-custom"></span>
                                    <span class="psi-radio-text">Да, прохожу супервизию</span>
                                </label>
                            </div>

                            <div class="psi-supervision-details" style="display: none;">
                                <label for="supervisionDetails">С какого года и в каком подходе?</label>
                                <textarea
                                        id="supervisionDetails"
                                        name="supervisionDetails"
                                        placeholder="Например: с 2020 года, КПТ"
                                        rows="2"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Шаг 3 Этический тест -->
                    <div class="psi-form-step" id="step3">
                        <h2>Выберите все подходящие варианты ответа для каждого вопроса <span class="required"></span></h2>

                        <div class="psi-questions-container">
                            <?php if( !empty($args['questions']) ) { ?>
                                <?php foreach( $args['questions'] as $k => $question ) { ?>
                                    <div class="psi-question-group" data-question="<?= $k + 1; ?>">
                                        <h3><?= $k+1; ?>. <?= $question['question']; ?></h3>
                                        <div class="psi-checkbox-group">
                                            <?php foreach( $question['answers'] as $answer ) { ?>
                                                <label class="psi-checkbox-label">
                                                    <input type="checkbox" name="question<?= $k + 1; ?>" value="<?= htmlspecialchars($answer['value']); ?>">
                                                    <span class="psi-checkbox-custom"></span>
                                                    <span class="psi-checkbox-text"><?= $answer['value']; ?></span>
                                                </label>
                                            <?php } ?>
                                        </div>
                                        <div class="psi-question-error">Пожалуйста, выберите хотя бы один вариант ответа</div>
                                    </div>
                                <?php } ?>
                            <?php } ?>

                        </div>
                    </div>

                    <!-- Шаг 4 Личные данные и отправка -->
                    <div class="psi-form-step" id="step4">
                        <h2>Личная информация</h2>

                        <div class="psi-form-group-wrap">
                            <div class="psi-form-group full-width">
                                <label for="fullName" class="required">Ваше имя и фамилия</label>
                                <input
                                        type="text"
                                        id="fullName"
                                        name="fullName"
                                        placeholder="Например: Анна Смирнова"
                                        class="psi-input"
                                >
                                <div class="psi-error-message">Это поле обязательно для заполнения</div>
                            </div>

                            <div class="psi-form-group">
                                <label for="age" class="required">Ваш возраст</label>
                                <input
                                        type="number"
                                        id="age"
                                        name="age"
                                        min="18"
                                        max="100"
                                        placeholder="Например: 28"
                                        class="psi-input"
                                >
                                <div class="psi-error-message">Пожалуйста, введите корректный возраст (от 18 до 100)</div>
                            </div>

                            <div class="psi-form-group">
                                <label for="contact" class="required">Telegram или номер телефона</label>
                                <input
                                        type="text"
                                        id="contact"
                                        name="contact"
                                        placeholder="@username или +7 (___) ___-__-__"
                                        class="psi-input"
                                >
                                <div class="psi-error-message">Это поле обязательно для заполнения</div>
                            </div>

                            <div class="psi-form-group">
                                <label for="telegram">Telegram</label>
                                <input
                                        type="text"
                                        id="telegram"
                                        name="telegram"
                                        placeholder="@username"
                                        class="psi-input"
                                >
                            </div>

                            <div class="psi-form-group">
                                <label for="email" class="required">Email</label>
                                <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        placeholder="example@mail.ru"
                                        class="psi-input"
                                >
                                <div class="psi-error-message">Пожалуйста, введите корректный email</div>
                            </div>
                        </div>


                        <div class="psi-form-group">
                            <h2>Кто-то из наших сотрудников порекомендовал вам оставить заявку?</h2>
                            <div class="psi-radio-group">
                                <label class="psi-radio-label">
                                    <input type="radio" name="recommendation" value="no" checked>
                                    <span class="psi-radio-custom"></span>
                                    <span class="psi-radio-text">Нет</span>
                                </label>
                                <label class="psi-radio-label">
                                    <input type="radio" name="recommendation" value="yes">
                                    <span class="psi-radio-custom"></span>
                                    <span class="psi-radio-text">Да</span>
                                </label>
                            </div>

                            <div class="psi-recommendation-details" style="display: none;">
                                <label for="specialistName">Укажите имя специалиста</label>

                                <!-- Кастомный селект -->
                                <div class="psi-custom-select">
                                    <div class="psi-select-selected" id="selectTrigger">
                                        <span class="psi-select-placeholder">Укажите психолога</span>
                                        <span class="psi-select-arrow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 15.5002C11.744 15.5002 11.488 15.4023 11.293 15.2073L7.29301 11.2072C6.90201 10.8162 6.90201 10.1842 7.29301 9.79325C7.68401 9.40225 8.31601 9.40225 8.70701 9.79325L12.012 13.0982L15.305 9.91825C15.704 9.53525 16.335 9.54625 16.719 9.94325C17.103 10.3403 17.092 10.9742 16.695 11.3572L12.695 15.2193C12.5 15.4073 12.25 15.5002 12 15.5002Z" fill="#7D7C7F"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="psi-select-options" id="selectOptions">
                                        <div class="psi-select-option" data-value="">Выберите специалиста</div>
                                        <?php 
                                        $args = array(
                                            'post_type' => 'doctors',
                                            'posts_per_page' => -1,
                                            'orderby' => array(
                                                'menu_order' => 'ASC',
                                                'date' => 'ASC'
                                            ),
                                            'post_status' => 'publish',
                                        );

                                        $doctors = new WP_Query($args);
                                        if ($doctors->have_posts()) {
                                            while ($doctors->have_posts()) {
                                                $doctors->the_post();
                                                echo '<div class="psi-select-option" data-value="' . get_the_title() . '">' . get_the_title() . '</div>';
                                            }
                                        }
                                        wp_reset_postdata();
                                        ?>
                                    </div>
                                    <input type="hidden" id="specialistName" name="specialistName">
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Окно успешной отправки -->
                    <div class="psi-success-modal" id="successModal">
                        <div class="psi-success-modal-content">
                            <div class="psi-success-icon"><img src="assets/img/psi/ico2.svg" alt=""></div>
                            <h2>Ваша заявка<br>успешно отправлена</h2>
                            <p>Спасибо за ваш интерес к&nbsp;работе в нашем центре.<br>
                                Мы внимательно изучим вашу заявку и&nbsp;свяжемся с&nbsp;вами в&nbsp;ближайшее время.</p>
                            <button class="but psi-btn psi-btn-success" id="closeSuccessModal">Закрыть</button>
                        </div>
                    </div>
                </div>

                <div class="psi-form-footer">
                    <button type="button" class="but psi-btn psi-btn-back psi-btn-hidden" id="prevBtn">← Назад</button>
                    <button type="button" class="but psi-btn psi-btn-next" id="nextBtn">Продолжить →</button>

                    <!-- Область согласий (только для последнего шага) -->
                    <div class="psi-agreements-container">
                        <div class="psi-checkbox-group">
                            <label class="psi-checkbox-label">
                                <input type="checkbox" id="agreePrivacy" name="agreePrivacy">
                                <span class="psi-checkbox-custom"></span>
                                <span class="psi-checkbox-text">Даю согласие на <a href="" target="_blank">обработку персональных данны</a>х в соответствии с <a
                                            href=""target="_blank">политикой</a></span>
                            </label>
                            <div class="psi-agreement-error" style="display: none;">Необходимо дать согласие на обработку персональных данных</div>
                        </div>

                        <div class="psi-checkbox-group">
                            <label class="psi-checkbox-label">
                                <input type="checkbox" id="agreeTerms" name="agreeTerms">
                                <span class="psi-checkbox-custom"></span>
                                <span class="psi-checkbox-text">Соглашаюсь с условиями <a href="" target="_blank">публичной оферты</a></span>
                            </label>
                            <div class="psi-agreement-error" style="display: none;">Необходимо согласиться с условиями оферты</div>
                        </div>
                    </div>

                </div>
            </form>
        </div>

    </div>
</section>