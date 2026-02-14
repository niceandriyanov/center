<?php
/**
 * Вспомогательные функции плагина Center Med — Renovatio
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Получить настройки плагина.
 *
 * @return array
 */
function center_med_renovatio_get_settings() {
	$defaults = [
		'api_key'                 => '',
		'api_base_url'            => 'https://app.rnova.org/api/public',
		'api_timeout'             => 15,
		'clinic_id'               => 0,
		'reservation_ttl_minutes' => 15,
		'notify_admin_email'      => get_option( 'admin_email' ),
		'notify_patient_email'    => 1,
		'cancel_reason_unpaid'    => 'Оплата не прошла',
	];

	$option_name = defined( 'CENTER_MED_RENOVATIO_OPTION_NAME' )
		? CENTER_MED_RENOVATIO_OPTION_NAME
		: 'center_med_renovatio_settings';
	$opts        = get_option( $option_name, [] );

	return is_array( $opts ) ? array_merge( $defaults, $opts ) : $defaults;
}

/**
 * Получить значение конкретной настройки.
 *
 * @param string $key Ключ настройки.
 * @param mixed  $default Значение по умолчанию.
 * @return mixed
 */
function center_med_renovatio_get_setting( $key, $default = null ) {
	$settings = center_med_renovatio_get_settings();
	return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
}

/**
 * Получить экземпляр API-клиента МИС Renovatio с настройками из опций.
 *
 * @return Renovatio_Api_Client
 */
function center_med_renovatio_api_client() {
	static $client = null;
	if ( $client === null ) {
		$opts    = center_med_renovatio_get_settings();
		$options = [
			'api_key'  => isset( $opts['api_key'] ) ? $opts['api_key'] : '',
			'base_url' => isset( $opts['api_base_url'] ) ? $opts['api_base_url'] : 'https://app.rnova.org/api/public',
			'timeout'  => isset( $opts['api_timeout'] ) ? (int) $opts['api_timeout'] : 15,
		];
		$client = new Renovatio_Api_Client( $options );
	}
	return $client;
}

/**
 * Пометить бронь как оплаченную.
 *
 * Можно вызывать из платежного плагина:
 * center_med_renovatio_mark_booking_paid( $booking_public_id, 'tochka', $payment_id, $payload, $event_id );
 *
 * @param string $booking_public_id UUID брони.
 * @param string $provider Платежный провайдер.
 * @param string $payment_external_id ID платежа во внешней системе.
 * @param array  $payload Сырые данные события.
 * @param string $external_event_id Уникальный ID события (для идемпотентности).
 * @return bool|WP_Error
 */
function center_med_renovatio_mark_booking_paid( $booking_public_id, $provider = 'tochka', $payment_external_id = '', array $payload = [], $external_event_id = '' ) {
	global $wpdb;

	$booking_public_id = sanitize_text_field( (string) $booking_public_id );
	$provider          = sanitize_text_field( (string) $provider );
	$payment_external_id = sanitize_text_field( (string) $payment_external_id );
	$external_event_id = sanitize_text_field( (string) $external_event_id );

	if ( $booking_public_id === '' ) {
		return new WP_Error( 'empty_booking_id', __( 'Не указан booking_public_id.', 'center-med-renovatio' ) );
	}

	$tables = Renovatio_Db_Schema::get_table_names();

	if ( $external_event_id !== '' ) {
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$tables['payment_events']} WHERE external_event_id = %s LIMIT 1",
				$external_event_id
			)
		);
		if ( $exists ) {
			return true;
		}
	}

	$booking = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$tables['bookings']} WHERE public_id = %s LIMIT 1",
			$booking_public_id
		),
		ARRAY_A
	);

	if ( ! $booking ) {
		return new WP_Error( 'booking_not_found', __( 'Бронь не найдена.', 'center-med-renovatio' ) );
	}

	if ( $booking['status'] !== 'paid' ) {
		$updated = $wpdb->update(
			$tables['bookings'],
			[
				'status'              => 'paid',
				'paid_at'             => current_time( 'mysql' ),
				'payment_provider'    => $provider,
				'payment_external_id' => $payment_external_id,
				'updated_at'          => current_time( 'mysql' ),
			],
			[ 'id' => (int) $booking['id'] ],
			[ '%s', '%s', '%s', '%s', '%s' ],
			[ '%d' ]
		);

		if ( $updated === false ) {
			return new WP_Error( 'db_update_failed', __( 'Не удалось обновить статус брони.', 'center-med-renovatio' ) );
		}

		$wpdb->insert(
			$tables['status_log'],
			[
				'booking_id'    => (int) $booking['id'],
				'from_status'   => $booking['status'],
				'to_status'     => 'paid',
				'source'        => 'payment_hook',
				'message'       => __( 'Получено подтверждение оплаты.', 'center-med-renovatio' ),
				'context_json'  => wp_json_encode( $payload ),
				'created_at'    => current_time( 'mysql' ),
			],
			[ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);
	}

	if ( $external_event_id !== '' ) {
		$wpdb->insert(
			$tables['payment_events'],
			[
				'provider'         => $provider,
				'external_event_id'=> $external_event_id,
				'booking_public_id'=> $booking_public_id,
				'event_type'       => 'payment',
				'event_status'     => 'paid',
				'raw_payload'      => wp_json_encode( $payload ),
				'processed_at'     => current_time( 'mysql' ),
				'result'           => 'applied',
				'created_at'       => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);
	}

	/**
	 * Хук после успешной смены статуса на paid.
	 *
	 * @param array $booking Бронь из БД.
	 * @param array $payload Данные платежа.
	 */
	do_action( 'center_med_renovatio_booking_paid', $booking, $payload );

	return true;
}

/**
 * Обработчик входящего action-хука от внешних платежных интеграций.
 *
 * Использование из другого плагина:
 * do_action( 'center_med_renovatio_payment_paid', $booking_public_id, 'tochka', $payment_external_id, $payload, $external_event_id );
 *
 * @param string $booking_public_id UUID брони.
 * @param string $provider Провайдер.
 * @param string $payment_external_id ID платежа.
 * @param array  $payload Сырые данные.
 * @param string $external_event_id ID события.
 * @return void
 */
function center_med_renovatio_handle_payment_paid_action( $booking_public_id, $provider = 'tochka', $payment_external_id = '', $payload = [], $external_event_id = '' ) {
	if ( ! is_array( $payload ) ) {
		$payload = [];
	}

	center_med_renovatio_mark_booking_paid(
		$booking_public_id,
		$provider,
		$payment_external_id,
		$payload,
		$external_event_id
	);
}
add_action( 'center_med_renovatio_payment_paid', 'center_med_renovatio_handle_payment_paid_action', 10, 5 );
