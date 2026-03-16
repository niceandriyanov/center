<?php
/**
 * Уведомления о новых заявках онлайн-формы.
 *
 * @package center-med-renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Appointment_Notifier
 */
class Renovatio_Appointment_Notifier {

	/**
	 * Регистрация хуков.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'center_med_renovatio_appointment_request_created', [ __CLASS__, 'handle_appointment_request_created' ], 10, 1 );
		add_action( 'center_med_renovatio_appointment_request_created', [ __CLASS__, 'send_waiting_list_email_to_user' ], 20, 1 );
		add_action( 'center_med_renovatio_booking_appointment_confirmed', [ __CLASS__, 'send_paid_email_to_user' ], 10, 2 );
	}

	/**
	 * Обработать создание заявки и отправить уведомление администратору.
	 *
	 * @param array $payload Данные заявки.
	 * @return void
	 */
	public static function handle_appointment_request_created( $payload ) {
		if ( ! is_array( $payload ) ) {
			return;
		}

		$admin_email_raw = (string) center_med_renovatio_get_setting( 'notify_admin_email', get_option( 'admin_email' ) );
		$admin_emails    = [];
		$email_parts     = array_map( 'trim', explode( ',', $admin_email_raw ) );

		foreach ( $email_parts as $email_part ) {
			$admin_email = sanitize_email( $email_part );
			if ( '' === $admin_email || ! is_email( $admin_email ) ) {
				continue;
			}

			$admin_emails[] = $admin_email;
		}

		$admin_emails = array_values( array_unique( $admin_emails ) );
		if ( empty( $admin_emails ) ) {
			return;
		}

		$message   = isset( $payload['message'] ) && is_array( $payload['message'] ) ? $payload['message'] : [];
		$form_type = isset( $message['formType'] ) ? sanitize_text_field( (string) $message['formType'] ) : 'self';
		$is_many   = ( 'many' === $form_type );
		$subject   = $is_many
			? __( 'Новая заявка онлайн-формы: Для пары', 'center-med-renovatio' )
			: __( 'Новая заявка онлайн-формы: Для себя', 'center-med-renovatio' );
		$body      = $is_many
			? self::build_many_email_template( $payload, $message )
			: self::build_self_email_template( $payload, $message );

		wp_mail(
			$admin_emails,
			$subject,
			$body,
			[
				'Content-Type: text/html; charset=UTF-8',
			]
		);
	}

	/**
	 * Отправить письмо пользователю о листе ожидания после создания задачи в МИС.
	 *
	 * @param array $payload Данные заявки.
	 * @return void
	 */
	public static function send_waiting_list_email_to_user( $payload ) {
		if ( ! is_array( $payload ) ) {
			return;
		}

		$message         = isset( $payload['message'] ) && is_array( $payload['message'] ) ? $payload['message'] : [];
		$is_waiting_list = ! empty( $message['isWaitingList'] );
		$waiting_task_id = isset( $payload['waiting_task_id'] ) ? absint( $payload['waiting_task_id'] ) : 0;
		$user_email      = isset( $payload['email'] ) ? sanitize_email( (string) $payload['email'] ) : '';

		// Отправляем только для листа ожидания и только после успешного создания задачи в МИС.
		if ( ! $is_waiting_list || $waiting_task_id <= 0 || '' === $user_email || ! is_email( $user_email ) ) {
			return;
		}

		$template_path = trailingslashit( CENTER_MED_RENOVATIO_PLUGIN_DIR ) . 'mails/user-waiting-list.php';
		if ( ! file_exists( $template_path ) ) {
			return;
		}

		$user_name       = isset( $payload['name'] ) ? sanitize_text_field( (string) $payload['name'] ) : '';
		$specialist_name = isset( $message['specialistName'] ) ? sanitize_text_field( (string) $message['specialistName'] ) : '';

		ob_start();
		$email_data = [
			'user_name'       => $user_name,
			'specialist_name' => $specialist_name,
			'home_url'        => home_url( '/' ),
		];
		include $template_path;
		$body = (string) ob_get_clean();

		if ( '' === trim( $body ) ) {
			return;
		}

		wp_mail(
			$user_email,
			__( 'Мы получили вашу заявку в лист ожидания', 'center-med-renovatio' ),
			$body,
			[
				'Content-Type: text/html; charset=UTF-8',
			]
		);
	}

	/**
	 * Отправить письмо пользователю об успешной оплате после подтверждения визита в МИС.
	 *
	 * @param array $booking Запись брони из БД.
	 * @param array $context Контекст подтверждения.
	 * @return void
	 */
	public static function send_paid_email_to_user( $booking, $context = [] ) {
		if ( ! is_array( $booking ) ) {
			return;
		}

		$user_email = isset( $booking['email'] ) ? sanitize_email( (string) $booking['email'] ) : '';
		if ( '' === $user_email || ! is_email( $user_email ) ) {
			return;
		}

		$payload_json = isset( $booking['payload_json'] ) ? (string) $booking['payload_json'] : '';
		$message      = json_decode( $payload_json, true );
		$message      = is_array( $message ) ? $message : [];
		$is_waiting   = ! empty( $message['isWaitingList'] );
		if ( $is_waiting ) {
			return;
		}

		$template_path = trailingslashit( CENTER_MED_RENOVATIO_PLUGIN_DIR ) . 'mails/user-paid.php';
		if ( ! file_exists( $template_path ) ) {
			return;
		}

		$user_name            = isset( $booking['first_name'] ) ? sanitize_text_field( (string) $booking['first_name'] ) : '';
		$specialist_name      = isset( $message['specialistName'] ) ? sanitize_text_field( (string) $message['specialistName'] ) : '';
		$appointment_datetime = isset( $message['dateString'] ) ? sanitize_text_field( (string) $message['dateString'] ) : '';
		$appointment_date     = isset( $message['appointmentDate'] ) ? sanitize_text_field( (string) $message['appointmentDate'] ) : '';
		$appointment_time     = isset( $message['appointmentTime'] ) ? sanitize_text_field( (string) $message['appointmentTime'] ) : '';

		if ( '' === $appointment_datetime && '' !== $appointment_date && '' !== $appointment_time ) {
			$appointment_datetime = $appointment_date . ' ' . $appointment_time;
		}

		ob_start();
		$email_data = [
			'user_name'            => $user_name,
			'specialist_name'      => $specialist_name,
			'appointment_datetime' => $appointment_datetime,
			'home_url'             => home_url( '/' ),
		];
		include $template_path;
		$body = (string) ob_get_clean();

		if ( '' === trim( $body ) ) {
			return;
		}

		wp_mail(
			$user_email,
			__( 'Ваша консультация забронирована и оплачена', 'center-med-renovatio' ),
			$body,
			[
				'Content-Type: text/html; charset=UTF-8',
			]
		);
	}

	/**
	 * Сформировать HTML-шаблон письма для формы "Для себя".
	 *
	 * @param array $payload Общий payload хука.
	 * @param array $message Вложенный блок message.
	 * @return string
	 */
	private static function build_self_email_template( array $payload, array $message ) {
		$title = __( 'Новая заявка: Для себя', 'center-med-renovatio' );
		return self::build_email_layout( $title, self::build_common_rows( $payload, $message, false ) );
	}

	/**
	 * Сформировать HTML-шаблон письма для формы "Для пары".
	 *
	 * @param array $payload Общий payload хука.
	 * @param array $message Вложенный блок message.
	 * @return string
	 */
	private static function build_many_email_template( array $payload, array $message ) {
		$title = __( 'Новая заявка: Для пары', 'center-med-renovatio' );
		return self::build_email_layout( $title, self::build_common_rows( $payload, $message, true ) );
	}

	/**
	 * Собрать строки таблицы с данными заявки.
	 *
	 * @param array $payload Общий payload хука.
	 * @param array $message Вложенный блок message.
	 * @param bool  $is_many Тип формы.
	 * @return string
	 */
	private static function build_common_rows( array $payload, array $message, $is_many ) {
		$name           = isset( $payload['name'] ) ? sanitize_text_field( (string) $payload['name'] ) : '';
		$phone          = isset( $payload['phone'] ) ? sanitize_text_field( (string) $payload['phone'] ) : '';
		$email          = isset( $payload['email'] ) ? sanitize_email( (string) $payload['email'] ) : '';
		$service        = isset( $payload['service'] ) ? sanitize_text_field( (string) $payload['service'] ) : '';
		$booking_id     = isset( $payload['booking_public_id'] ) ? sanitize_text_field( (string) $payload['booking_public_id'] ) : '';
		$appointment_id = isset( $payload['appointment_id'] ) ? absint( $payload['appointment_id'] ) : 0;
		$payment_status = isset( $payload['payment_status'] ) ? sanitize_text_field( (string) $payload['payment_status'] ) : '';
		$payment_url    = isset( $payload['payment_url'] ) ? esc_url_raw( (string) $payload['payment_url'] ) : '';

		$specialist_name  = isset( $message['specialistName'] ) ? sanitize_text_field( (string) $message['specialistName'] ) : '';
		$appointment_date = isset( $message['appointmentDate'] ) ? sanitize_text_field( (string) $message['appointmentDate'] ) : '';
		$appointment_time = isset( $message['appointmentTime'] ) ? sanitize_text_field( (string) $message['appointmentTime'] ) : '';
		$telegram         = isset( $message['telegram'] ) ? sanitize_text_field( (string) $message['telegram'] ) : '';
		$is_waiting_list  = ! empty( $message['isWaitingList'] );
		$concerns_html    = self::build_concerns_html( isset( $message['concerns'] ) ? $message['concerns'] : [] );

		$rows  = '';
		$rows .= self::build_row( __( 'Тип формы', 'center-med-renovatio' ), $is_many ? __( 'Для пары', 'center-med-renovatio' ) : __( 'Для себя', 'center-med-renovatio' ) );
		$rows .= self::build_row( __( 'Статус заявки', 'center-med-renovatio' ), $is_waiting_list ? __( 'Лист ожидания', 'center-med-renovatio' ) : __( 'Обычная запись', 'center-med-renovatio' ) );
		$rows .= self::build_row( $is_many ? __( 'Контактное лицо', 'center-med-renovatio' ) : __( 'Клиент', 'center-med-renovatio' ), $name );
		$rows .= self::build_row( __( 'Телефон', 'center-med-renovatio' ), $phone );
		$rows .= self::build_row( __( 'Email', 'center-med-renovatio' ), $email );
		$rows .= self::build_row( __( 'Telegram', 'center-med-renovatio' ), $telegram );
		$rows .= self::build_row( __( 'Специалист', 'center-med-renovatio' ), $specialist_name );
		$rows .= self::build_row( __( 'Дата', 'center-med-renovatio' ), $appointment_date );
		$rows .= self::build_row( __( 'Время', 'center-med-renovatio' ), $appointment_time );
		$rows .= self::build_row( __( 'Услуга', 'center-med-renovatio' ), $service );
		$rows .= self::build_row( __( 'Темы запроса', 'center-med-renovatio' ), $concerns_html, true );
		$rows .= self::build_row( __( 'Booking ID', 'center-med-renovatio' ), $booking_id );
		$rows .= self::build_row( __( 'Appointment ID', 'center-med-renovatio' ), $appointment_id > 0 ? (string) $appointment_id : '-' );
		$rows .= self::build_row( __( 'Статус оплаты', 'center-med-renovatio' ), $payment_status );
		$rows .= self::build_row( __( 'Ссылка на оплату', 'center-med-renovatio' ), $payment_url );

		return $rows;
	}

	/**
	 * Собрать обертку письма.
	 *
	 * @param string $title Заголовок.
	 * @param string $rows  HTML строк таблицы.
	 * @return string
	 */
	private static function build_email_layout( $title, $rows ) {
		$site_name = get_bloginfo( 'name' );
		$year      = gmdate( 'Y' );

		return '<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>' . esc_html( $title ) . '</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
	<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="padding:24px 12px;">
		<tr>
			<td align="center">
				<table role="presentation" cellpadding="0" cellspacing="0" width="680" style="max-width:680px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
					<tr>
						<td style="background:linear-gradient(135deg,#f6007f 0%,#9b287b 100%);padding:24px;">
							<div style="font-size:22px;line-height:1.3;font-weight:700;color:#ffffff;">' . esc_html( $site_name ) . '</div>
							<div style="margin-top:8px;font-size:16px;line-height:1.4;color:#fde8f4;">' . esc_html( $title ) . '</div>
						</td>
					</tr>
					<tr>
						<td style="padding:20px 24px 10px;">
							<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
								' . $rows . '
							</table>
						</td>
					</tr>
					<tr>
						<td style="padding:16px 24px 24px;color:#6b7280;font-size:13px;">
							' . esc_html__( 'Это автоматическое уведомление сайта.', 'center-med-renovatio' ) . ' &copy; ' . esc_html( $year ) . '
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>';
	}

	/**
	 * Собрать строку таблицы.
	 *
	 * @param string $label Подпись.
	 * @param string $value Значение.
	 * @param bool   $allow_html Разрешить HTML в значении.
	 * @return string
	 */
	private static function build_row( $label, $value, $allow_html = false ) {
		$safe_label = esc_html( (string) $label );
		$value      = is_string( $value ) ? trim( $value ) : '';
		if ( '' === $value ) {
			$value = '-';
		}

		$safe_value = $allow_html ? wp_kses_post( $value ) : esc_html( $value );

		return '<tr>
			<td style="padding:10px 12px;border-bottom:1px solid #eef0f2;background:#f9fafb;font-size:13px;font-weight:700;color:#374151;width:220px;">' . $safe_label . '</td>
			<td style="padding:10px 12px;border-bottom:1px solid #eef0f2;font-size:14px;line-height:1.45;color:#111827;">' . $safe_value . '</td>
		</tr>';
	}

	/**
	 * Собрать отображение тем запроса по term_id.
	 *
	 * @param mixed $concerns Список term_id.
	 * @return string
	 */
	private static function build_concerns_html( $concerns ) {
		if ( ! is_array( $concerns ) || empty( $concerns ) ) {
			return '-';
		}

		$labels = [];
		foreach ( $concerns as $term_id ) {
			$term_id = absint( $term_id );
			if ( $term_id <= 0 ) {
				continue;
			}

			$term = get_term( $term_id, 'doctor_diseases' );
			if ( is_wp_error( $term ) || ! $term || empty( $term->name ) ) {
				continue;
			}

			$labels[] = esc_html( sanitize_text_field( (string) $term->name ) );
		}

		if ( empty( $labels ) ) {
			return '-';
		}

		return implode( '<br>', $labels );
	}
}

