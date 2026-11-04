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
    $foreign_languages = sanitize_textarea_field($step1['foreignLanguages'] ?? '');

    $psychologist_experience = sanitize_textarea_field($step2['psychologistExperience'] ?? '');
    $clients_count = sanitize_text_field($step2['clientsCount'] ?? '');
    $consultation_hours = sanitize_text_field($step2['consultationHours'] ?? '');
    $future_work = sanitize_textarea_field($step2['futureWork'] ?? '');
    $avoid_work = sanitize_textarea_field($step2['avoidWork'] ?? '');
    $supervision = sanitize_text_field($step2['supervision'] ?? 'no');
    $supervision_details = sanitize_textarea_field($step2['supervisionDetails'] ?? '');
    $supervisor_name = sanitize_textarea_field($step2['supervisorName'] ?? '');
    $supervision_hours = sanitize_text_field($step2['supervisionHours'] ?? '');

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
    $message .= clinic_psi_mail_row('Иностранные языки', $foreign_languages);
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

/**
 * ==================== Ближайшие окна записи из МИС ====================
 *
 * Используется в двух местах:
 *  - главный блок (template-parts/blocks/main.php) — самое раннее окно среди всех врачей,
 *    приходит через AJAX clinic_main_nearest_slot (JS — в конце assets/js/script.js);
 *  - карточка врача (template-parts/parts/doctor.php) — ближайшее окно конкретного врача
 *    или «Запись в лист ожидания», выводится сразу в PHP; по этим же данным сортируется каталог;
 *  - слайдер «Психологи центра» на главной (template-parts/blocks/spec.php) — 6 врачей
 *    с самыми ранними окнами, см. clinic_nearest_doctor_ids().
 *
 * Обработчик формы (center_med_renovatio_filter_online_doctors в плагине) тут не подходит:
 * без выбранных запросов клиента он возвращает пустой список, а по каждому врачу ходит в МИС отдельно.
 * Поэтому ходим в МИС сами, через API-клиент плагина center-med-renovatio, по тем же правилам, что и форма,
 * одним заходом сразу по всем врачам. Результат общий для всех мест.
 *
 * Чтобы страницы не ждали ответа МИС: расписание считается свежим 5 минут, после этого
 * посетители видят последнее сохранённое, а новое подтягивается в фоне (WP-Cron).
 * Синхронно МИС опрашивается, только если сохранённого расписания нет или оно старше 30 минут.
 */

// Ключи и значения — те же, что использует плагин center-med-renovatio
define( 'CLINIC_NEAREST_META_DOCTOR_ID', '_cmr_renovatio_doctor_id' );   // ID врача в МИС
define( 'CLINIC_NEAREST_META_STEP', '_cmr_doctor_step_personal' );        // шаг слотов для личной консультации
define( 'CLINIC_NEAREST_CATEGORY_ID', 2910 );                             // категория слотов онлайн-приёма
define( 'CLINIC_NEAREST_CACHE_KEY', 'clinic_doctors_nearest_slots' );
define( 'CLINIC_NEAREST_CACHE_TTL', 5 * MINUTE_IN_SECONDS );
define( 'CLINIC_NEAREST_ERROR_TTL', MINUTE_IN_SECONDS );                  // если МИС не ответила — повторим через минуту
define( 'CLINIC_NEAREST_KEEP_SLOTS', 5 );                                 // сколько ближайших окон храним на врача
define( 'CLINIC_NEAREST_LAST_OPTION', 'clinic_doctors_nearest_slots_last' ); // последнее удачное расписание
define( 'CLINIC_NEAREST_MAX_STALE', 30 * MINUTE_IN_SECONDS );             // старше — ждём свежее, а не показываем старое
define( 'CLINIC_NEAREST_SLIDER_LIMIT', 6 );                               // врачей в слайдере на главной

/**
 * Ближайшие свободные окна всех врачей.
 *
 * @return array|WP_Error [ ID записи врача => [ts, ts, ...] ] — до 5 ближайших окон по возрастанию.
 *                        Врачи без окон (или без ID в МИС) в массив не попадают.
 */
function clinic_get_doctors_nearest_slots() {
	static $result = null; // на одной странице считаем один раз
	if ( null !== $result ) {
		return $result;
	}

	// Свежее расписание (до 5 минут)
	$cached = get_transient( CLINIC_NEAREST_CACHE_KEY );
	if ( is_array( $cached ) ) {
		$result = $cached;
		return $result;
	}

	// Свежего нет — отдаём последнее сохранённое, а новое запрашиваем в фоне
	$last = get_option( CLINIC_NEAREST_LAST_OPTION );
	if ( is_array( $last ) && isset( $last['data'], $last['saved'] ) && time() - (int) $last['saved'] < CLINIC_NEAREST_MAX_STALE ) {
		if ( 'error' !== $cached && ! wp_next_scheduled( 'clinic_refresh_doctors_nearest_slots' ) ) {
			wp_schedule_single_event( time(), 'clinic_refresh_doctors_nearest_slots' );
		}
		$result = $last['data'];
		return $result;
	}

	// Сохранённого нет (или оно слишком старое) — ждём ответа МИС
	if ( 'error' === $cached ) {
		$result = new WP_Error( 'mis_unavailable', 'МИС недавно не ответила' );
		return $result;
	}

	$result = clinic_refresh_doctors_nearest_slots();
	return $result;
}

/**
 * Обновление расписания из МИС и сохранение в кэш (вызывается сразу или в фоне через WP-Cron).
 *
 * @return array|WP_Error
 */
function clinic_refresh_doctors_nearest_slots() {
	$result = clinic_load_doctors_nearest_slots();

	if ( is_wp_error( $result ) ) {
		// МИС не ответила — не дёргаем её на каждом открытии страницы, повторим через минуту
		set_transient( CLINIC_NEAREST_CACHE_KEY, 'error', CLINIC_NEAREST_ERROR_TTL );
		return $result;
	}

	set_transient( CLINIC_NEAREST_CACHE_KEY, $result, CLINIC_NEAREST_CACHE_TTL );
	update_option( CLINIC_NEAREST_LAST_OPTION, array( 'saved' => time(), 'data' => $result ), false );

	return $result;
}
add_action( 'clinic_refresh_doctors_nearest_slots', 'clinic_refresh_doctors_nearest_slots' );

/**
 * Запрос расписания в МИС (без кэша).
 *
 * @return array|WP_Error
 */
function clinic_load_doctors_nearest_slots() {
	if ( ! function_exists( 'center_med_renovatio_api_client' ) || ! class_exists( 'Renovatio_Doctor_Service' ) ) {
		return new WP_Error( 'no_plugin', 'Плагин center-med-renovatio не активен' );
	}

	// Все опубликованные врачи, у которых указан ID в МИС
	$doctor_posts = get_posts( array(
		'post_type'      => 'doctors',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => CLINIC_NEAREST_META_DOCTOR_ID,
				'value'   => '',
				'compare' => '!=',
			),
		),
	) );

	// Группируем врачей по шагу слотов: врачей с одинаковым шагом спрашиваем у МИС одним запросом
	$groups   = array(); // шаг => [ ID в МИС, ... ]
	$post_ids = array(); // ID в МИС => [ ID записи врача, ... ]
	foreach ( $doctor_posts as $post_id ) {
		$api_id = (int) get_post_meta( $post_id, CLINIC_NEAREST_META_DOCTOR_ID, true );
		if ( $api_id <= 0 ) {
			continue;
		}
		$step = (int) get_post_meta( $post_id, CLINIC_NEAREST_META_STEP, true );
		$groups[ $step ][]     = $api_id;
		$post_ids[ $api_id ][] = (int) $post_id;
	}

	if ( empty( $groups ) ) {
		return array();
	}

	$service   = new Renovatio_Doctor_Service( center_med_renovatio_api_client() );
	$now       = time();
	$slots     = array(); // ID в МИС => [ ts, ... ]
	$errors    = 0;

	foreach ( $groups as $step => $api_ids ) {
		$schedule = $service->get_schedule( array(
			'user_ids' => array_values( array_unique( $api_ids ) ),
			'time_end' => wp_date( 'd.m.Y H:i', strtotime( '+30 day' ) ),
			'step'     => $step,
			'show_all' => 1,
		) );

		if ( is_wp_error( $schedule ) ) {
			$errors++;
			continue;
		}
		if ( ! is_array( $schedule ) ) {
			continue;
		}

		foreach ( $schedule as $api_id => $doctor_slots ) {
			if ( ! is_array( $doctor_slots ) ) {
				continue;
			}
			foreach ( $doctor_slots as $slot ) {
				if ( ! empty( $slot['is_busy'] ) ) {
					continue;
				}
				if ( empty( $slot['category_id'] ) || (int) $slot['category_id'] !== CLINIC_NEAREST_CATEGORY_ID ) {
					continue;
				}
				$ts = clinic_nearest_slot_timestamp( $slot );
				if ( $ts > $now ) {
					$slots[ (int) $api_id ][] = $ts;
				}
			}
		}
	}

	// МИС не ответила ни на один запрос — это ошибка, а не «окон нет»
	if ( $errors === count( $groups ) ) {
		return new WP_Error( 'mis_unavailable', 'МИС не ответила' );
	}

	$result = array();
	foreach ( $slots as $api_id => $list ) {
		if ( empty( $post_ids[ $api_id ] ) ) {
			continue;
		}
		$list = array_values( array_unique( $list ) );
		sort( $list );
		$list = array_slice( $list, 0, CLINIC_NEAREST_KEEP_SLOTS );
		foreach ( $post_ids[ $api_id ] as $post_id ) {
			$result[ $post_id ] = $list;
		}
	}

	return $result;
}

/**
 * Ближайшее окно конкретного врача.
 *
 * @param int $post_id ID записи врача.
 * @return int|null timestamp окна; 0 — окон нет (лист ожидания); null — МИС недоступна, не знаем.
 */
function clinic_get_doctor_nearest_ts( $post_id ) {
	$all = clinic_get_doctors_nearest_slots();
	if ( is_wp_error( $all ) ) {
		return null;
	}

	$now = time();
	if ( ! empty( $all[ $post_id ] ) ) {
		foreach ( $all[ $post_id ] as $ts ) {
			if ( $ts > $now ) { // окна из кэша, которые уже прошли, пропускаем
				return (int) $ts;
			}
		}
	}

	return 0;
}

/**
 * Дата окна для вывода: «Сегодня, 14:00» / «Завтра, 10:00» / «5 ноября, 12:00».
 *
 * @param int $ts timestamp.
 * @return string
 */
function clinic_format_nearest_date( $ts ) {
	$months = array( 1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря' );

	$day = wp_date( 'Y-m-d', $ts );
	if ( wp_date( 'Y-m-d' ) === $day ) {
		$label = 'Сегодня';
	} elseif ( wp_date( 'Y-m-d', strtotime( '+1 day' ) ) === $day ) {
		$label = 'Завтра';
	} else {
		$label = wp_date( 'j', $ts ) . ' ' . $months[ (int) wp_date( 'n', $ts ) ];
	}

	return $label . ', ' . wp_date( 'H:i', $ts );
}

/**
 * Врачи для слайдера «Психологи центра» на главной (template-parts/blocks/spec.php).
 *
 * Берутся все опубликованные врачи, кроме стажёров (грейд «Стажер»).
 * Сначала — врачи со свободными окнами, по времени ближайшего окна;
 * если таких меньше, чем нужно, список добирается остальными в порядке из админки.
 *
 * @param int $limit Сколько врачей нужно.
 * @return int[] ID записей врачей.
 */
function clinic_nearest_doctor_ids( $limit = CLINIC_NEAREST_SLIDER_LIMIT ) {
	$args = array(
		'post_type'      => 'doctors',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'ASC',
		),
	);

	// Стажёры не участвуют
	$intern = get_term_by( 'slug', 'intern', 'doctor_grade' );
	if ( ! $intern ) {
		$intern = get_term_by( 'name', 'Стажер', 'doctor_grade' );
	}
	if ( $intern && ! is_wp_error( $intern ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'doctor_grade',
				'field'    => 'term_id',
				'terms'    => array( (int) $intern->term_id ),
				'operator' => 'NOT IN',
			),
		);
	}

	$ids = array_map( 'intval', get_posts( $args ) );
	if ( empty( $ids ) ) {
		return array();
	}

	// Порядок: по ближайшему окну; без окон — в конце; при равенстве — как в админке
	$order = array_flip( $ids );
	$ts    = array();
	foreach ( $ids as $id ) {
		$nearest   = clinic_get_doctor_nearest_ts( $id );
		$ts[ $id ] = $nearest > 0 ? $nearest : PHP_INT_MAX;
	}

	usort(
		$ids,
		static function ( $a, $b ) use ( $ts, $order ) {
			if ( $ts[ $a ] !== $ts[ $b ] ) {
				return $ts[ $a ] < $ts[ $b ] ? -1 : 1;
			}
			return $order[ $a ] - $order[ $b ];
		}
	);

	return array_slice( $ids, 0, $limit );
}

/**
 * AJAX для главного блока: самое раннее окно среди всех врачей.
 * Ответ: { success: true, data: { ts: 1762004400, slot: "1 ноя, 17:30" } }
 * Если окон нет: { success: true, data: { ts: 0, slot: "" } }
 */
function clinic_ajax_main_nearest_slot() {
	check_ajax_referer( 'clinic_nonce', 'nonce' );

	$result = clinic_find_nearest_slot();

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( $result );
}
add_action( 'wp_ajax_clinic_main_nearest_slot', 'clinic_ajax_main_nearest_slot' );
add_action( 'wp_ajax_nopriv_clinic_main_nearest_slot', 'clinic_ajax_main_nearest_slot' );

/**
 * Самое раннее свободное окно среди всех врачей.
 *
 * @return array|WP_Error { ts: int, slot: string }
 */
function clinic_find_nearest_slot() {
	$all = clinic_get_doctors_nearest_slots();
	if ( is_wp_error( $all ) ) {
		return $all;
	}

	$best_ts = 0;
	foreach ( array_keys( $all ) as $post_id ) {
		$ts = clinic_get_doctor_nearest_ts( $post_id );
		if ( $ts > 0 && ( 0 === $best_ts || $ts < $best_ts ) ) {
			$best_ts = $ts;
		}
	}

	if ( 0 === $best_ts ) {
		return array( 'ts' => 0, 'slot' => '' );
	}

	$months = array( 1 => 'янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек' );

	return array(
		'ts'   => $best_ts,
		'slot' => wp_date( 'j', $best_ts ) . ' ' . $months[ (int) wp_date( 'n', $best_ts ) ] . ', ' . wp_date( 'H:i', $best_ts ),
	);
}

/**
 * Время начала слота (timestamp) — разбирается так же, как в плагине.
 *
 * @param array $slot Слот из ответа getSchedule.
 * @return int
 */
function clinic_nearest_slot_timestamp( $slot ) {
	$day  = isset( $slot['_date'] ) ? (string) $slot['_date'] : '';
	$time = isset( $slot['time_start_short'] ) ? (string) $slot['time_start_short'] : '';

	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $day ) && preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
		$date = date_create_immutable_from_format( 'Y-m-d H:i', $day . ' ' . $time, wp_timezone() );
		return $date ? $date->getTimestamp() : 0;
	}

	if ( ! empty( $slot['time_start'] ) ) {
		$date = DateTimeImmutable::createFromFormat( 'd.m.Y H:i', (string) $slot['time_start'], wp_timezone() );
		return $date ? $date->getTimestamp() : 0;
	}

	return 0;
}
