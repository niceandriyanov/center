<?php
/**
 * AJAX функции для темы
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX функция для записи на прием
 */
function clinic_ajax_appointment() {
    check_ajax_referer('clinic_nonce', 'nonce');
    
    $name = sanitize_text_field($_POST['name']);
    $phone = sanitize_text_field($_POST['phone']);
    $email = sanitize_email($_POST['email']);
    $service = sanitize_text_field($_POST['service']);
    $date = sanitize_text_field($_POST['date']);
    $message = sanitize_textarea_field($_POST['message']);
    
    // Здесь можно добавить логику отправки заявки
    $success = true;
    
    if ($success) {
        wp_send_json_success('Заявка отправлена успешно!');
    } else {
        wp_send_json_error('Ошибка при отправке заявки.');
    }
}
add_action('wp_ajax_clinic_appointment', 'clinic_ajax_appointment');
add_action('wp_ajax_nopriv_clinic_appointment', 'clinic_ajax_appointment');

/**
 * AJAX функция для фильтрации специалистов
 */
function clinic_ajax_filter_doctors() {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        return;
    }

    $filters = array(
        'search'         => isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '',
        'problems'       => isset($_GET['problems']) ? sanitize_text_field(wp_unslash($_GET['problems'])) : '',
        'specs'          => isset($_GET['specs']) ? sanitize_text_field(wp_unslash($_GET['specs'])) : '',
        'grade'          => isset($_GET['grade']) ? absint($_GET['grade']) : 0,
        'specialty_slug' => isset($_GET['specialty_slug']) ? sanitize_text_field(wp_unslash($_GET['specialty_slug'])) : '',
    );

    $args = function_exists('clinic_build_doctors_query_args')
        ? clinic_build_doctors_query_args($filters)
        : array(
            'post_type'      => 'doctors',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );

    $doctors = new WP_Query($args);

    $response_data = array(
        'success' => true,
        'data'    => array(
            'specItems_wrap' => '',
            'found'          => false,
            'found_count'    => 0,
        ),
    );

    if ($doctors->have_posts()) {
        ob_start();

        while ($doctors->have_posts()) {
            $doctors->the_post();
            get_template_part('template-parts/parts/doctor', null, array('id' => get_the_ID()));
        }

        $response_data['data']['specItems_wrap'] = ob_get_clean();
        $response_data['data']['found'] = true;
        $response_data['data']['found_count'] = (int) $doctors->found_posts;
    } else {
        ob_start();
        ?>
        <div class="no-results">
            <p>По вашему запросу ничего не найдено</p>
        </div>
        <?php
        $response_data['data']['specItems_wrap'] = ob_get_clean();
        $response_data['data']['found'] = false;
        $response_data['data']['found_count'] = 0;
    }

    wp_reset_postdata();

    if (function_exists('clinic_get_doctors_catalog_filter_availability')) {
        $response_data['data']['filter_availability'] = clinic_get_doctors_catalog_filter_availability($filters);
    }

    wp_send_json($response_data);
}
add_action('wp_ajax_clinic_filter_doctors', 'clinic_ajax_filter_doctors');
add_action('wp_ajax_nopriv_clinic_filter_doctors', 'clinic_ajax_filter_doctors');

if (!function_exists('clinic_psi_mail_row')) {
    /**
     * Форматирование строки письма для анкеты
     *
     * @param string $label
     * @param string $value
     * @return string
     */
    function clinic_psi_mail_row($label, $value) {
        $value = trim((string) $value);
        $safe_value = $value !== '' ? nl2br(esc_html($value)) : '—';

        return sprintf(
            '<tr><td style="padding:8px 12px;border:1px solid #E6E6E6;width:35%%;vertical-align:top;"><strong>%s</strong></td><td style="padding:8px 12px;border:1px solid #E6E6E6;">%s</td></tr>',
            esc_html($label),
            $safe_value
        );
    }
}

/**
 * AJAX функция для отправки анкеты психолога
 */
function clinic_ajax_submit_psi_form() {
    check_ajax_referer('clinic_nonce', 'nonce');

    $payload_raw = isset($_POST['payload']) ? wp_unslash($_POST['payload']) : '';
    $payload = json_decode($payload_raw, true);

    if (!is_array($payload)) {
        wp_send_json_error(array('message' => 'Некорректные данные формы.'));
    }

    $step1 = isset($payload['step1']) && is_array($payload['step1']) ? $payload['step1'] : array();
    $step2 = isset($payload['step2']) && is_array($payload['step2']) ? $payload['step2'] : array();
    $step3 = isset($payload['step3']) && is_array($payload['step3']) ? $payload['step3'] : array();
    $step4 = isset($payload['step4']) && is_array($payload['step4']) ? $payload['step4'] : array();

    $basic_education = sanitize_textarea_field($step1['basicEducation'] ?? '');
    $cbt_education = sanitize_textarea_field($step1['cbtEducation'] ?? '');
    $other_education = sanitize_textarea_field($step1['otherEducation'] ?? '');

    $psychologist_experience = sanitize_textarea_field($step2['psychologistExperience'] ?? '');
    $clients_count = sanitize_text_field($step2['clientsCount'] ?? '');
    $consultation_hours = sanitize_text_field($step2['consultationHours'] ?? '');
    $future_work = sanitize_textarea_field($step2['futureWork'] ?? '');
    $avoid_work = sanitize_textarea_field($step2['avoidWork'] ?? '');
    $supervision = sanitize_text_field($step2['supervision'] ?? 'no');
    $supervision_details = sanitize_textarea_field($step2['supervisionDetails'] ?? '');
    $supervisor_name = sanitize_textarea_field($step2['supervisorName'] ?? '');
    $supervision_hours = sanitize_text_field($step2['supervisionHours'] ?? '');
    $foreign_languages = sanitize_textarea_field($step2['foreignLanguages'] ?? '');

    $full_name = sanitize_text_field($step4['fullName'] ?? '');
    $age = isset($step4['age']) ? (int) $step4['age'] : 0;
    $contact = sanitize_text_field($step4['contact'] ?? '');
    $telegram = sanitize_text_field($step4['telegram'] ?? '');
    $email = sanitize_email($step4['email'] ?? '');
    $recommendation = sanitize_text_field($step4['recommendation'] ?? 'no');
    $specialist_name = sanitize_text_field($step4['specialistName'] ?? '');

    $errors = array();
    if ($basic_education === '') {
        $errors[] = 'Не заполнено основное образование.';
    }
    if ($cbt_education === '') {
        $errors[] = 'Не заполнено дополнительное образование по КПТ.';
    }
    if ($psychologist_experience === '') {
        $errors[] = 'Не заполнен опыт работы психологом.';
    }
    if ($future_work === '') {
        $errors[] = 'Не заполнены планы по работе с клиентами.';
    }
    if ($full_name === '') {
        $errors[] = 'Не заполнено имя и фамилия.';
    }
    if ($age < 18 || $age > 100) {
        $errors[] = 'Возраст должен быть от 18 до 100.';
    }
    if ($contact === '') {
        $errors[] = 'Не заполнены контакты.';
    }
    if ($email === '' || !is_email($email)) {
        $errors[] = 'Некорректный email.';
    }

    if (!empty($errors)) {
        wp_send_json_error(array(
            'message' => 'Проверьте корректность заполнения формы.',
            'errors' => $errors,
        ));
    }

    $questions = array();
    if (!empty($step3['questions']) && is_array($step3['questions'])) {
        foreach ($step3['questions'] as $question) {
            if (!is_array($question)) {
                continue;
            }
            $question_text = sanitize_text_field($question['question'] ?? '');
            $answers = isset($question['answers']) && is_array($question['answers'])
                ? array_map('sanitize_text_field', $question['answers'])
                : array();
            $questions[] = array(
                'question' => $question_text,
                'answers' => $answers,
            );
        }
    }

    $subject = sprintf('Новая анкета психолога: %s', $full_name);

    $message = '<div style="font-family:Arial, sans-serif;font-size:14px;line-height:1.5;color:#1F1F1F;">';
    $message .= '<h2 style="margin:0 0 12px 0;">Новая анкета психолога</h2>';
    $message .= '<h3 style="margin:24px 0 8px 0;">Образование</h3>';
    $message .= '<table style="border-collapse:collapse;width:100%;margin-bottom:16px;">';
    $message .= clinic_psi_mail_row('Основное образование', $basic_education);
    $message .= clinic_psi_mail_row('Доп. образование по КПТ', $cbt_education);
    $message .= clinic_psi_mail_row('Доп. образование в других подходах', $other_education);
    $message .= '</table>';

    $message .= '<h3 style="margin:24px 0 8px 0;">Опыт работы</h3>';
    $message .= '<table style="border-collapse:collapse;width:100%;margin-bottom:16px;">';
    $message .= clinic_psi_mail_row('Опыт работы психологом', $psychologist_experience);
    $message .= clinic_psi_mail_row('Примерное количество клиентов', $clients_count);
    $message .= clinic_psi_mail_row('Примерное количество часов консультаций', $consultation_hours);
    $message .= clinic_psi_mail_row('С какими клиентами/запросами хотите работать', $future_work);
    $message .= clinic_psi_mail_row('С какими клиентами/запросами не работаете', $avoid_work);
    $message .= clinic_psi_mail_row('Индивидуальная супервизия', $supervision === 'yes' ? 'Да' : 'Нет');
    if ($supervision === 'yes' || $supervision_details !== '') {
        $message .= clinic_psi_mail_row('С какого года и в каком подходе', $supervision_details);
    }
    if ($supervision === 'yes' || $supervisor_name !== '' || $supervision_hours !== '') {
        $message .= clinic_psi_mail_row('ФИО супервизора, подход', $supervisor_name);
        $message .= clinic_psi_mail_row('Количество часов супервизии', $supervision_hours);
    }
    $message .= clinic_psi_mail_row('Иностранные языки', $foreign_languages);
    $message .= '</table>';

    $message .= '<h3 style="margin:24px 0 8px 0;">Этический тест</h3>';
    if (!empty($questions)) {
        foreach ($questions as $index => $question) {
            $answers_text = !empty($question['answers']) ? implode(' | ', $question['answers']) : '—';
            $message .= '<div style="margin-bottom:12px;padding:12px;border:1px solid #E6E6E6;border-radius:6px;">';
            $message .= '<div style="font-weight:600;margin-bottom:6px;">' . esc_html(($index + 1) . '. ' . ($question['question'] ?: 'Вопрос')) . '</div>';
            $message .= '<div>Ответ(ы): ' . esc_html($answers_text) . '</div>';
            $message .= '</div>';
        }
    } else {
        $message .= '<p style="margin:0 0 16px 0;">Ответы не указаны.</p>';
    }

    $message .= '<h3 style="margin:24px 0 8px 0;">Личные данные</h3>';
    $message .= '<table style="border-collapse:collapse;width:100%;margin-bottom:16px;">';
    $message .= clinic_psi_mail_row('Имя и фамилия', $full_name);
    $message .= clinic_psi_mail_row('Возраст', $age ? (string) $age : '');
    $message .= clinic_psi_mail_row('Telegram или телефон', $contact);
    $message .= clinic_psi_mail_row('Telegram', $telegram);
    $message .= clinic_psi_mail_row('Email', $email);
    $message .= clinic_psi_mail_row('Рекомендация сотрудника', $recommendation === 'yes' ? 'Да' : 'Нет');
    if ($recommendation === 'yes' && $specialist_name !== '') {
        $message .= clinic_psi_mail_row('Кто рекомендовал', $specialist_name);
    }
    $message .= '</table>';
    $message .= '</div>';

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $recipients_raw = get_field('psi_form_emails', 'option');
    $recipients_list = preg_split('/[\s,;]+/', (string) $recipients_raw, -1, PREG_SPLIT_NO_EMPTY);
    $recipients_list = array_values(array_unique(array_filter($recipients_list, 'is_email')));
    if (empty($recipients_list)) {
        $recipients_list = array(get_option('admin_email'));
    }
    $extra_email = 'center@handlingbetter.ru';
    if (!in_array($extra_email, array_map('strtolower', $recipients_list), true)) {
        $recipients_list[] = $extra_email;
    }
    $sent = wp_mail($recipients_list, $subject, $message, $headers);
    
    if ($sent) {
        wp_send_json_success('Заявка отправлена успешно!');
    }

    wp_send_json_error(array('message' => 'Ошибка при отправке письма. Попробуйте позже.'));
}
add_action('wp_ajax_clinic_submit_psi_form', 'clinic_ajax_submit_psi_form');
add_action('wp_ajax_nopriv_clinic_submit_psi_form', 'clinic_ajax_submit_psi_form');