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

	$current_status = sanitize_text_field( (string) ( $booking['status'] ?? '' ) );
	if ( in_array( $current_status, [ 'canceled', 'cancelled' ], true ) ) {
		return new WP_Error( 'booking_canceled', __( 'Бронь уже отменена. Оплата недоступна.', 'center-med-renovatio' ) );
	}

	$status_changed = false;
	if ( $current_status !== 'paid' ) {
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
		$status_changed = true;

		$wpdb->insert(
			$tables['status_log'],
			[
				'booking_id'    => (int) $booking['id'],
				'from_status'   => $current_status,
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

	if ( $status_changed ) {
		/**
		 * Хук после успешной смены статуса на paid.
		 *
		 * @param array $booking Бронь из БД.
		 * @param array $payload Данные платежа.
		 */
		do_action( 'center_med_renovatio_booking_paid', $booking, $payload );
	}

	return true;
}

/**
 * Подтвердить визит в МИС после успешной оплаты.
 *
 * @param string $booking_public_id UUID брони.
 * @param array  $payload Данные события.
 * @return bool|WP_Error
 */
function center_med_renovatio_confirm_booking_appointment( $booking_public_id, array $payload = [] ) {
	global $wpdb;

	$booking_public_id = sanitize_text_field( (string) $booking_public_id );
	if ( '' === $booking_public_id ) {
		return new WP_Error( 'empty_booking_id', __( 'Не указан booking_public_id.', 'center-med-renovatio' ) );
	}

	$booking = center_med_renovatio_get_booking_for_tochka( $booking_public_id );
	if ( ! is_array( $booking ) ) {
		return new WP_Error( 'booking_not_found', __( 'Бронь не найдена.', 'center-med-renovatio' ) );
	}

	$appointment_id = ! empty( $booking['appointment_id'] ) ? absint( $booking['appointment_id'] ) : 0;
	if ( $appointment_id <= 0 ) {
		return new WP_Error( 'missing_appointment_id', __( 'У брони отсутствует appointment_id.', 'center-med-renovatio' ) );
	}

	$tables = Renovatio_Db_Schema::get_table_names();
	$exists = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$tables['status_log']} WHERE booking_id = %d AND source = %s LIMIT 1",
			(int) $booking['id'],
			'appointment_confirm'
		)
	);
	if ( $exists ) {
		return true;
	}

	$confirm_result = center_med_renovatio_api_client()->request(
		'confirmAppointment',
		[
			'appointment_id' => $appointment_id,
			'source'         => 'website',
		]
	);
	if ( is_wp_error( $confirm_result ) ) {
		return $confirm_result;
	}

	$wpdb->insert(
		$tables['status_log'],
		[
			'booking_id'   => (int) $booking['id'],
			'from_status'  => sanitize_text_field( (string) ( $booking['status'] ?? 'paid' ) ),
			'to_status'    => sanitize_text_field( (string) ( $booking['status'] ?? 'paid' ) ),
			'source'       => 'appointment_confirm',
			'message'      => __( 'Визит подтвержден в МИС после оплаты.', 'center-med-renovatio' ),
			'context_json' => wp_json_encode( $payload ),
			'created_at'   => current_time( 'mysql' ),
		],
		[ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
	);

	return true;
}

/**
 * Отменить неоплаченный визит в МИС и локально.
 *
 * @param string $booking_public_id UUID брони.
 * @param string $reason Причина отмены.
 * @param string $source Источник отмены.
 * @param array  $payload Контекст.
 * @return bool|WP_Error
 */
function center_med_renovatio_cancel_booking_unpaid( $booking_public_id, $reason = '', $source = 'payment_failed_hook', array $payload = [] ) {
	global $wpdb;

	$booking_public_id = sanitize_text_field( (string) $booking_public_id );
	$source            = sanitize_text_field( (string) $source );
	$reason            = sanitize_text_field( (string) $reason );
	if ( '' === $reason ) {
		$reason = sanitize_text_field( (string) center_med_renovatio_get_setting( 'cancel_reason_unpaid', 'Оплата не прошла' ) );
	}

	if ( '' === $booking_public_id ) {
		return new WP_Error( 'empty_booking_id', __( 'Не указан booking_public_id.', 'center-med-renovatio' ) );
	}

	$tables  = Renovatio_Db_Schema::get_table_names();
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

	$current_status = sanitize_text_field( (string) ( $booking['status'] ?? '' ) );
	if ( in_array( $current_status, [ 'canceled', 'cancelled' ], true ) ) {
		return true;
	}
	if ( 'paid' === $current_status ) {
		return true;
	}

	$appointment_id = ! empty( $booking['appointment_id'] ) ? absint( $booking['appointment_id'] ) : 0;
	if ( $appointment_id > 0 ) {
		$cancel_result = center_med_renovatio_api_client()->request(
			'cancelAppointment',
			[
				'appointment_id' => $appointment_id,
				'comment'        => $reason,
				'source'         => 'website',
			]
		);
		if ( is_wp_error( $cancel_result ) ) {
			return $cancel_result;
		}
	}

	$updated = $wpdb->update(
		$tables['bookings'],
		[
			'status'        => 'canceled',
			'canceled_at'   => current_time( 'mysql' ),
			'cancel_reason' => $reason,
			'updated_at'    => current_time( 'mysql' ),
		],
		[ 'id' => (int) $booking['id'] ],
		[ '%s', '%s', '%s', '%s' ],
		[ '%d' ]
	);
	if ( false === $updated ) {
		return new WP_Error( 'db_update_failed', __( 'Не удалось обновить статус брони.', 'center-med-renovatio' ) );
	}

	$payload['appointment_id'] = $appointment_id;
	$wpdb->insert(
		$tables['status_log'],
		[
			'booking_id'   => (int) $booking['id'],
			'from_status'  => $current_status,
			'to_status'    => 'canceled',
			'source'       => $source !== '' ? $source : 'payment_failed_hook',
			'message'      => __( 'Визит отменен: оплата не подтверждена.', 'center-med-renovatio' ),
			'context_json' => wp_json_encode( $payload ),
			'created_at'   => current_time( 'mysql' ),
		],
		[ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
	);

	do_action( 'center_med_renovatio_booking_canceled_unpaid', $booking, $appointment_id );

	return true;
}

/**
 * Запланировать фоновый пайплайн пост-оплаты.
 *
 * @param string $booking_public_id UUID брони.
 * @param int    $attempt Номер попытки.
 * @param int    $delay_seconds Задержка в секундах.
 * @return void
 */
function center_med_renovatio_schedule_paid_pipeline( $booking_public_id, $attempt = 1, $delay_seconds = 5 ) {
	$booking_public_id = sanitize_text_field( (string) $booking_public_id );
	$attempt           = max( 1, (int) $attempt );
	$delay_seconds     = max( 1, (int) $delay_seconds );

	if ( '' === $booking_public_id ) {
		return;
	}

	$hook = 'center_med_renovatio_process_paid_pipeline';
	$args = [ $booking_public_id, $attempt ];

	if ( wp_next_scheduled( $hook, $args ) ) {
		return;
	}

	wp_schedule_single_event( time() + $delay_seconds, $hook, $args );
}

/**
 * Записать шаг пайплайна в статус-лог.
 *
 * @param int    $booking_id ID брони.
 * @param string $from_status Статус до действия.
 * @param string $to_status Статус после действия.
 * @param string $source Источник лога.
 * @param string $message Сообщение.
 * @param array  $context Контекст.
 * @return void
 */
function center_med_renovatio_log_pipeline_step( $booking_id, $from_status, $to_status, $source, $message, array $context = [] ) {
	global $wpdb;

	$booking_id = (int) $booking_id;
	if ( $booking_id <= 0 ) {
		return;
	}

	$tables = Renovatio_Db_Schema::get_table_names();
	$wpdb->insert(
		$tables['status_log'],
		[
			'booking_id'   => $booking_id,
			'from_status'  => sanitize_text_field( (string) $from_status ),
			'to_status'    => sanitize_text_field( (string) $to_status ),
			'source'       => sanitize_text_field( (string) $source ),
			'message'      => sanitize_text_field( (string) $message ),
			'context_json' => wp_json_encode( $context ),
			'created_at'   => current_time( 'mysql' ),
		],
		[ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
	);
}

/**
 * Достать номер счета из ответа МИС.
 *
 * @param mixed $response Ответ API.
 * @return string
 */
function center_med_renovatio_extract_invoice_id_from_response( $response ) {
	if ( is_scalar( $response ) ) {
		$value = sanitize_text_field( (string) $response );
		if ( '' !== $value ) {
			return $value;
		}
	}

	if ( ! is_array( $response ) ) {
		return '';
	}

	$candidates = [
		$response['number'] ?? '',
		$response['invoice_id'] ?? '',
		$response['invoiceId'] ?? '',
		$response['id'] ?? '',
		$response['invoice']['number'] ?? '',
		$response['invoice']['id'] ?? '',
		$response[0] ?? '',
	];

	foreach ( $candidates as $candidate ) {
		$value = sanitize_text_field( (string) $candidate );
		if ( '' !== $value ) {
			return $value;
		}
	}

	return '';
}

/**
 * Найти номер счета из ранее успешного шага invoice_create.
 *
 * @param int $booking_id ID брони.
 * @return string
 */
function center_med_renovatio_get_saved_invoice_id( $booking_id ) {
	global $wpdb;

	$booking_id = (int) $booking_id;
	if ( $booking_id <= 0 ) {
		return '';
	}

	$tables = Renovatio_Db_Schema::get_table_names();
	$row    = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT context_json FROM {$tables['status_log']} WHERE booking_id = %d AND source = %s ORDER BY id DESC LIMIT 1",
			$booking_id,
			'invoice_create'
		),
		ARRAY_A
	);

	if ( ! is_array( $row ) || empty( $row['context_json'] ) ) {
		return '';
	}

	$context = json_decode( (string) $row['context_json'], true );
	if ( ! is_array( $context ) ) {
		return '';
	}

	$value = sanitize_text_field( (string) ( $context['invoice_number'] ?? '' ) );
	if ( '' !== $value ) {
		return $value;
	}

	return sanitize_text_field( (string) ( $context['invoice_id'] ?? '' ) );
}

/**
 * Проверить, выполнен ли шаг пайплайна ранее.
 *
 * @param int    $booking_id ID брони.
 * @param string $source Источник шага.
 * @return bool
 */
function center_med_renovatio_has_pipeline_step( $booking_id, $source ) {
	global $wpdb;

	$booking_id = (int) $booking_id;
	$source     = sanitize_text_field( (string) $source );
	if ( $booking_id <= 0 || '' === $source ) {
		return false;
	}

	$tables = Renovatio_Db_Schema::get_table_names();
	$exists = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$tables['status_log']} WHERE booking_id = %d AND source = %s LIMIT 1",
			$booking_id,
			$source
		)
	);

	return ! empty( $exists );
}

/**
 * Запланировать повтор пайплайна после ошибки.
 *
 * @param string $booking_public_id UUID брони.
 * @param int    $attempt Текущая попытка.
 * @param int    $booking_id ID брони.
 * @param string $booking_status Текущий статус брони.
 * @param string $reason Причина повтора.
 * @param array  $context Контекст ошибки.
 * @return void
 */
function center_med_renovatio_schedule_paid_pipeline_retry( $booking_public_id, $attempt, $booking_id, $booking_status, $reason, array $context = [] ) {
	$delays      = [ 1 => 60, 2 => 300, 3 => 900, 4 => 1800, 5 => 3600 ];
	$attempt     = max( 1, (int) $attempt );
	$next_attempt = $attempt + 1;
	$max_attempts = 5;

	if ( $attempt >= $max_attempts ) {
		center_med_renovatio_log_pipeline_step(
			$booking_id,
			$booking_status,
			$booking_status,
			'payment_pipeline_failed',
			'Остановлены попытки post-payment пайплайна.',
			[
				'attempt' => $attempt,
				'reason'  => sanitize_text_field( (string) $reason ),
				'context' => $context,
			]
		);
		return;
	}

	$delay = isset( $delays[ $next_attempt ] ) ? (int) $delays[ $next_attempt ] : 3600;
	center_med_renovatio_schedule_paid_pipeline( $booking_public_id, $next_attempt, $delay );

	center_med_renovatio_log_pipeline_step(
		$booking_id,
		$booking_status,
		$booking_status,
		'payment_pipeline_retry',
		'Запланирован повтор post-payment пайплайна.',
		[
			'attempt'      => $attempt,
			'next_attempt' => $next_attempt,
			'retry_in'     => $delay,
			'reason'       => sanitize_text_field( (string) $reason ),
			'context'      => $context,
		]
	);
}

/**
 * Cron: обработка post-payment пайплайна (confirm -> createInvoice -> payInvoice).
 *
 * @param string $booking_public_id UUID брони.
 * @param int    $attempt Номер попытки.
 * @return void
 */
function center_med_renovatio_process_paid_pipeline_event( $booking_public_id, $attempt = 1 ) {
	$booking_public_id = sanitize_text_field( (string) $booking_public_id );
	$attempt           = max( 1, (int) $attempt );
	if ( '' === $booking_public_id ) {
		return;
	}

	$lock_key = 'cmr_paid_pipeline_lock_' . md5( $booking_public_id );
	if ( get_transient( $lock_key ) ) {
		return;
	}
	set_transient( $lock_key, '1', 3 * MINUTE_IN_SECONDS );

	try {
		$booking = center_med_renovatio_get_booking_for_tochka( $booking_public_id );
		if ( ! is_array( $booking ) ) {
			return;
		}

		$booking_id     = (int) ( $booking['id'] ?? 0 );
		$booking_status = sanitize_text_field( (string) ( $booking['status'] ?? '' ) );
		$appointment_id = ! empty( $booking['appointment_id'] ) ? absint( $booking['appointment_id'] ) : 0;
		if ( $booking_id <= 0 || $appointment_id <= 0 ) {
			return;
		}

		$confirm_result = center_med_renovatio_confirm_booking_appointment(
			$booking_public_id,
			[
				'pipeline' => true,
				'attempt'  => $attempt,
			]
		);
		if ( is_wp_error( $confirm_result ) ) {
			center_med_renovatio_schedule_paid_pipeline_retry(
				$booking_public_id,
				$attempt,
				$booking_id,
				$booking_status,
				'confirm_appointment_failed',
				[ 'error' => $confirm_result->get_error_message() ]
			);
			return;
		}

		$invoice_number = center_med_renovatio_get_saved_invoice_id( $booking_id );
		if ( '' === $invoice_number ) {
			$create_result = center_med_renovatio_api_client()->request(
				'createInvoice',
				[
					'appointment_id' => $appointment_id,
					'source'         => 'website',
				]
			);

			if ( is_wp_error( $create_result ) ) {
				center_med_renovatio_schedule_paid_pipeline_retry(
					$booking_public_id,
					$attempt,
					$booking_id,
					$booking_status,
					'create_invoice_failed',
					[ 'error' => $create_result->get_error_message() ]
				);
				return;
			}

			$invoice_number = center_med_renovatio_extract_invoice_id_from_response( $create_result );
			if ( '' === $invoice_number ) {
				center_med_renovatio_schedule_paid_pipeline_retry(
					$booking_public_id,
					$attempt,
					$booking_id,
					$booking_status,
					'create_invoice_no_id',
					[ 'response' => $create_result ]
				);
				return;
			}

			center_med_renovatio_log_pipeline_step(
				$booking_id,
				$booking_status,
				$booking_status,
				'invoice_create',
				'Счет создан в МИС.',
				[
					'invoice_number' => $invoice_number,
					'response'       => $create_result,
				]
			);
		}

		if ( center_med_renovatio_has_pipeline_step( $booking_id, 'invoice_pay' ) ) {
			return;
		}

		$pay_result = center_med_renovatio_api_client()->request(
			'payInvoice',
			[
				'number'    => $invoice_number,
				'type'      => 2,
				'is_online' => 1,
				'source'    => 'website',
			]
		);
		if ( is_wp_error( $pay_result ) ) {
			center_med_renovatio_schedule_paid_pipeline_retry(
				$booking_public_id,
				$attempt,
				$booking_id,
				$booking_status,
				'pay_invoice_failed',
				[
					'error'          => $pay_result->get_error_message(),
					'invoice_number' => $invoice_number,
				]
			);
			return;
		}

		center_med_renovatio_log_pipeline_step(
			$booking_id,
			$booking_status,
			$booking_status,
			'invoice_pay',
			'Счет оплачен в МИС.',
			[
				'invoice_number' => $invoice_number,
				'response'       => $pay_result,
				'attempt'        => $attempt,
			]
		);
	} finally {
		delete_transient( $lock_key );
	}
}
add_action( 'center_med_renovatio_process_paid_pipeline', 'center_med_renovatio_process_paid_pipeline_event', 10, 2 );

/**
 * Хук: после оплаты запланировать подтверждение и оплату в МИС в фоне.
 *
 * @param array $booking Бронь.
 * @param array $payload Событие оплаты.
 * @return void
 */
function center_med_renovatio_handle_booking_paid_confirm_appointment( $booking, $payload = [] ) {
	if ( ! is_array( $booking ) || empty( $booking['public_id'] ) ) {
		return;
	}

	if ( ! is_array( $payload ) ) {
		$payload = [];
	}

	center_med_renovatio_schedule_paid_pipeline( (string) $booking['public_id'], 1, 5 );
}
add_action( 'center_med_renovatio_booking_paid', 'center_med_renovatio_handle_booking_paid_confirm_appointment', 10, 2 );

/**
 * Хук: отменить бронь при неуспешном статусе оплаты в Точке.
 *
 * @param array  $entity_context Контекст сущности.
 * @param string $status Статус провайдера.
 * @param mixed  $amount Сумма.
 * @param array  $payload Payload.
 * @return void
 */
function center_med_renovatio_handle_tochka_failed_status( $entity_context, $status, $amount, $payload ) {
	if ( ! is_array( $entity_context ) ) {
		return;
	}

	$entity_type = sanitize_text_field( (string) ( $entity_context['entity_type'] ?? '' ) );
	if ( ! in_array( $entity_type, [ 'booking', 'visit' ], true ) ) {
		return;
	}

	$booking_public_id = sanitize_text_field( (string) ( $entity_context['entity_public_id'] ?? '' ) );
	if ( '' === $booking_public_id ) {
		$booking_public_id = sanitize_text_field( (string) ( $entity_context['entity_id'] ?? '' ) );
	}
	if ( '' === $booking_public_id ) {
		return;
	}

	$status_upper = strtoupper( sanitize_text_field( (string) $status ) );
	if ( ! in_array( $status_upper, [ 'DECLINED', 'EXPIRED', 'FAILED', 'CANCELLED', 'CANCELED', 'FAIL' ], true ) ) {
		return;
	}

	if ( ! is_array( $payload ) ) {
		$payload = [];
	}
	if ( $amount !== null && $amount !== '' ) {
		$payload['amount'] = $amount;
	}
	$payload['provider_status'] = $status_upper;

	center_med_renovatio_cancel_booking_unpaid(
		$booking_public_id,
		center_med_renovatio_get_setting( 'cancel_reason_unpaid', 'Оплата не прошла' ),
		'payment_failed_hook',
		$payload
	);
}
add_action( 'tochka_payment_entity_status_changed', 'center_med_renovatio_handle_tochka_failed_status', 10, 4 );

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

/**
 * Получить бронь по ID (int) или public_id (UUID).
 *
 * @param string $identifier Идентификатор брони.
 * @return array|null
 */
function center_med_renovatio_get_booking_for_tochka( $identifier ) {
	global $wpdb;

	$identifier = sanitize_text_field( (string) $identifier );
	if ( $identifier === '' ) {
		return null;
	}

	$tables = Renovatio_Db_Schema::get_table_names();
	$table  = $tables['bookings'];

	if ( ctype_digit( $identifier ) ) {
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", (int) $identifier ),
			ARRAY_A
		);
	}

	return $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE public_id = %s LIMIT 1", $identifier ),
		ARRAY_A
	);
}

/**
 * Извлечь сумму из payload_json брони.
 *
 * @param array $booking Бронь.
 * @return float
 */
function center_med_renovatio_extract_booking_amount( array $booking ) {
	$payload = [];
	if ( ! empty( $booking['payload_json'] ) ) {
		$decoded = json_decode( (string) $booking['payload_json'], true );
		if ( is_array( $decoded ) ) {
			$payload = $decoded;
		}
	}

	$candidates = [ 'amount', 'price', 'sum', 'total' ];
	foreach ( $candidates as $key ) {
		if ( isset( $payload[ $key ] ) && is_numeric( $payload[ $key ] ) ) {
			return (float) $payload[ $key ];
		}
	}

	return 0.0;
}

/**
 * Резолвинг entity-контекста для плагина Tochka.
 *
 * @param array  $context Текущий контекст.
 * @param string $order_id Идентификатор сущности.
 * @param array  $response Ответ API.
 * @return array
 */
function center_med_renovatio_tochka_resolve_entity_context( $context, $order_id, $response ) {
	$booking = center_med_renovatio_get_booking_for_tochka( $order_id );
	if ( ! is_array( $booking ) || empty( $booking['public_id'] ) ) {
		return $context;
	}

	$context['entity_type']      = 'booking';
	$context['entity_id']        = (string) (int) $booking['id'];
	$context['entity_public_id'] = sanitize_text_field( (string) $booking['public_id'] );

	return $context;
}
add_filter( 'tochka_payment_resolve_entity_context', 'center_med_renovatio_tochka_resolve_entity_context', 10, 3 );

/**
 * Передать сущность брони в gateway Tochka.
 *
 * @param array|null $entity Сущность.
 * @param string     $order_id Идентификатор сущности.
 * @return array|null
 */
function center_med_renovatio_tochka_gateway_get_entity( $entity, $order_id ) {
	$booking = center_med_renovatio_get_booking_for_tochka( $order_id );
	if ( ! is_array( $booking ) ) {
		return $entity;
	}

	$status_map = [
		'paid'      => 'paid',
		'canceled'  => 'cancelled',
		'cancelled' => 'cancelled',
		'failed'    => 'failed',
	];

	$booking_status = sanitize_text_field( (string) ( $booking['status'] ?? 'pending' ) );
	$status         = $status_map[ $booking_status ] ?? 'pending';
	$amount         = center_med_renovatio_extract_booking_amount( $booking );
	$options        = [];
	if ( $amount > 0 ) {
		$options[] = [
			'price'    => $amount,
			'quantity' => 1,
		];
	}

	return [
		'status'  => $status,
		'payment' => sanitize_text_field( (string) ( $booking['payment_provider'] ?? '' ) ),
		'pay_at'  => sanitize_text_field( (string) ( $booking['paid_at'] ?? '' ) ),
		'options' => wp_json_encode( $options ),
		'_amount' => $amount,
	];
}
add_filter( 'tochka_payment_gateway_get_entity', 'center_med_renovatio_tochka_gateway_get_entity', 10, 2 );

/**
 * Переопределить расчет суммы для gateway Tochka.
 *
 * @param float|null $amount Сумма.
 * @param array      $entity Сущность.
 * @return float|null
 */
function center_med_renovatio_tochka_gateway_calculate_amount( $amount, $entity ) {
	if ( is_numeric( $amount ) ) {
		return (float) $amount;
	}

	if ( isset( $entity['_amount'] ) && is_numeric( $entity['_amount'] ) ) {
		return (float) $entity['_amount'];
	}

	return $amount;
}
add_filter( 'tochka_payment_gateway_calculate_amount', 'center_med_renovatio_tochka_gateway_calculate_amount', 10, 2 );

/**
 * Склонение слова "год" для вывода стажа.
 *
 * @param int $years Количество лет.
 * @return string
 */
function center_med_renovatio_years_label( $years ) {
	$years = (int) $years;

	if ( $years % 100 >= 11 && $years % 100 <= 14 ) {
		return 'лет';
	}

	$last_digit = $years % 10;
	if ( $last_digit === 1 ) {
		return 'год';
	}
	if ( $last_digit >= 2 && $last_digit <= 4 ) {
		return 'года';
	}

	return 'лет';
}

/**
 * Ajax: подбор врачей для онлайн-формы по отмеченным проблемам.
 *
 * @return void
 */
function center_med_renovatio_ajax_filter_online_doctors() {
	check_ajax_referer( 'clinic_nonce', 'nonce' );

	$types = [
		Renovatio_Doctor_Metabox::META_KEY_STEP_PERSONAL,
		Renovatio_Doctor_Metabox::META_KEY_STEP_PAIR,
	];
	$current_type = $types[0];
	$form_type    = isset( $_POST['form_type'] ) ? sanitize_text_field( wp_unslash( $_POST['form_type'] ) ) : '';

	if ( 'many' === $form_type ) {
		$current_type = $types[1];
	}

	$concerns = isset( $_POST['concerns'] ) ? (array) wp_unslash( $_POST['concerns'] ) : [];
	$concerns = array_values(
		array_filter(
			array_map( 'absint', $concerns ),
			static function( $term_id ) {
				return $term_id > 0;
			}
		)
	);

	$args = [
		'post_type'      => 'doctors',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => [
			'menu_order' => 'ASC',
			'date'       => 'ASC',
		],
	];

	if ( ! empty( $concerns ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'doctor_diseases',
				'field'    => 'term_id',
				'terms'    => $concerns,
				'operator' => 'AND',
			],
		];
		if ( 'many' !== $form_type && ! empty( $concerns ) ) {
			$concern = $concerns[0];
			$type = get_field( 'form_to', 'doctor_diseases_' . $concern );
			if ( isset( $types[ $type ] ) ) {
				$current_type = $types[ $type ];
			}
		}
	}
	else {
		wp_send_json_success(
			[
				'available'   => [],
				'waitingList' => [],
			]
		);
	}
	
	$query     	= new WP_Query( $args );

	$available 	= [];
	$waiting	= [];
	if ( $query->have_posts() ) {
		$api_doctors = new Renovatio_Doctor_Service( center_med_renovatio_api_client() );
		foreach ( $query->posts as $doctor_post ) {
			$doctor_id = (int) $doctor_post->ID;
			$doctor_api_id = (int) get_post_meta( $doctor_id, Renovatio_Doctor_Metabox::META_KEY_DOCTOR_ID, true );
			$doctor_step = (int) get_post_meta( $doctor_id, $current_type, true );
			$shedule = [];
			$nearestSlot = '';
			if( !empty( $doctor_api_id ) ) {
				$shedule = $api_doctors->get_schedule( [
					'user_ids' => [ $doctor_api_id ],
					'time_end' => date( 'd.m.Y H:i', strtotime( '+30 day' ) ),
					'step'     => $doctor_step,
					'show_all' => 1,
				] );
				if( !is_wp_error( $shedule ) && !empty( $shedule[$doctor_api_id] ) ) {
					foreach( $shedule[$doctor_api_id] as $slot ) {
						if( empty( $slot['is_busy'] ) && !empty( $slot['category_id'] ) && $slot['category_id'] == 2910 ) {
							$months = [
								1  => 'янв',
								2  => 'фев',
								3  => 'мар',
								4  => 'апр',
								5  => 'мая',
								6  => 'июн',
								7  => 'июл',
								8  => 'авг',
								9  => 'сен',
								10 => 'окт',
								11 => 'ноя',
								12 => 'дек',
							];

							$slot_day  = isset( $slot['_date'] ) ? sanitize_text_field( (string) $slot['_date'] ) : '';
							$slot_time = isset( $slot['time_start_short'] ) ? sanitize_text_field( (string) $slot['time_start_short'] ) : '';

							if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $slot_day ) && preg_match( '/^\d{2}:\d{2}$/', $slot_time ) ) {
								$slot_parts = explode( '-', $slot_day );
								$day        = isset( $slot_parts[2] ) ? ltrim( $slot_parts[2], '0' ) : '';
								$month_num  = isset( $slot_parts[1] ) ? (int) $slot_parts[1] : 0;
								$month      = isset( $months[ $month_num ] ) ? $months[ $month_num ] : '';
								if ( '' !== $day && '' !== $month ) {
									$nearestSlot = sprintf( '%s %s, %s', $day, $month, $slot_time );
								}
							}
							elseif ( ! empty( $slot['time_start'] ) ) {
								$slot_datetime = DateTimeImmutable::createFromFormat( 'd.m.Y H:i', sanitize_text_field( (string) $slot['time_start'] ), wp_timezone() );
								if ( $slot_datetime instanceof DateTimeImmutable ) {
									$month_num = (int) $slot_datetime->format( 'n' );
									$month     = isset( $months[ $month_num ] ) ? $months[ $month_num ] : '';
									$nearestSlot = sprintf( '%s %s, %s', $slot_datetime->format( 'j' ), $month, $slot_datetime->format( 'H:i' ) );
								}
							}
							break;
						}
					}
				}
			}

			$position = get_field( 'specialist_type', $doctor_id );
			$position_titles = [
				'psychologist' => 'Психолог',
				'clinical' => 'Клинический психолог',
			];

			$position_title = !empty($position_titles[$position]) ? $position_titles[$position] : '';

			if( !empty($_POST['form_type']) && $_POST['form_type'] == 'many' ) {
				$price_raw = get_field( 'cost_1_many', $doctor_id );
			}
			else {
				$price_raw = get_field( 'cost_1', $doctor_id );
			}

			$price_raw = str_replace(' ', '', $price_raw);
			$price = is_numeric( $price_raw ) ? (int) $price_raw : 0;

			$age_work = (int) get_post_meta( $doctor_id, 'age_work', true );
			$experience = 'Не указан';
			if ( $age_work > 0 ) {
				$current_year = (int) gmdate( 'Y' );
				$years = $current_year - $age_work;
				if ( $years > 0 ) {
					$experience = sprintf( '%d %s', $years, center_med_renovatio_years_label( $years ) );
				}
			}

			$avatar = get_field	( 'img', $doctor_id );
			$avatar = !empty($avatar) ? $avatar['url'] : '';
			if( empty( $doctor_api_id ) || empty( $shedule[$doctor_api_id] ) || empty( $nearestSlot ) ) {
				$waiting[] = [
					'id'           => $doctor_id,
					'name'         => get_the_title( $doctor_id ),
					'position'     => $position,
					'positionTitle'=> $position_title,
					'experience'   => $experience,
					'avatar'       => $avatar,
					'price'        => $price,
					'nearestSlot'  => '',
					'description'  => wp_strip_all_tags( get_field( 'specialist_description', $doctor_id ) ),
				];
			}
			else {
				$available[] = [
					'id'           => $doctor_id,
					'name'         => get_the_title( $doctor_id ),
					'position'     => $position,
					'positionTitle'=> $position_title,
					'experience'   => $experience,
					'avatar'       => $avatar,
					'price'        => $price,
					'nearestSlot'  => $nearestSlot,
					'description'  => wp_strip_all_tags( get_field( 'specialist_description', $doctor_id ) ),
				];
			}
			
		}
	}

	wp_send_json_success(
		[
			'available'   => $available,
			'waitingList' => $waiting,
		]
	);
}
add_action( 'wp_ajax_center_med_renovatio_filter_online_doctors', 'center_med_renovatio_ajax_filter_online_doctors' );
add_action( 'wp_ajax_nopriv_center_med_renovatio_filter_online_doctors', 'center_med_renovatio_ajax_filter_online_doctors' );

/**
 * Ajax: доступные дни врача для календаря онлайн-формы по конкретному месяцу.
 *
 * @return void
 */
function center_med_renovatio_ajax_get_online_doctor_available_days() {
	check_ajax_referer( 'clinic_nonce', 'nonce' );

	$doctor_id = isset( $_POST['doctor_id'] ) ? absint( wp_unslash( $_POST['doctor_id'] ) ) : 0;
	$month     = isset( $_POST['month'] ) ? sanitize_text_field( wp_unslash( $_POST['month'] ) ) : '';
	$form_type = isset( $_POST['form_type'] ) ? sanitize_text_field( wp_unslash( $_POST['form_type'] ) ) : 'self';

	if ( $doctor_id <= 0 || empty( $month ) ) {
		wp_send_json_error(
			[
				'message' => 'Не переданы обязательные параметры doctor_id/month.',
			],
			400
		);
	}

	if ( ! preg_match( '/^\d{4}\-\d{2}$/', $month ) ) {
		wp_send_json_error(
			[
				'message' => 'Некорректный формат month. Ожидается YYYY-MM.',
			],
			400
		);
	}

	if ( 'doctors' !== get_post_type( $doctor_id ) ) {
		wp_send_json_error(
			[
				'message' => 'Специалист не найден.',
			],
			404
		);
	}

	$timezone            = wp_timezone();
	$requested_month     = date_create_immutable_from_format( 'Y-m-d H:i:s', $month . '-01 00:00:00', $timezone );
	$current_month_start = new DateTimeImmutable( 'first day of this month 00:00:00', $timezone );
	$today_start         = new DateTimeImmutable( 'today 00:00:00', $timezone );

	if ( false === $requested_month ) {
		wp_send_json_error(
			[
				'message' => 'Не удалось распарсить month.',
			],
			400
		);
	}

	if ( $requested_month < $current_month_start ) {
		wp_send_json_success(
			[
				'doctorId'      => $doctor_id,
				'month'         => $month,
				'availableDays' => [],
				'slotsByDate'   => [],
			]
		);
	}

	$is_current_month = ( $requested_month->format( 'Y-m' ) === $today_start->format( 'Y-m' ) );
	$range_start      = $is_current_month ? $today_start : $requested_month;
	$range_end        = $requested_month->modify( 'last day of this month 23:59:59' );

	$doctor_api_id = (int) get_post_meta( $doctor_id, Renovatio_Doctor_Metabox::META_KEY_DOCTOR_ID, true );
	if ( $doctor_api_id <= 0 ) {
		wp_send_json_success(
			[
				'doctorId'      => $doctor_id,
				'month'         => $month,
				'availableDays' => [],
				'slotsByDate'   => [],
			]
		);
	}

	$step_meta_key = ( 'many' === $form_type )
		? Renovatio_Doctor_Metabox::META_KEY_STEP_PAIR
		: Renovatio_Doctor_Metabox::META_KEY_STEP_PERSONAL;
	$doctor_step = (int) get_post_meta( $doctor_id, $step_meta_key, true );

	$params = [
		'user_ids' 		=> [ $doctor_api_id ],
		'time_start' 	=> $range_start->format( 'd.m.Y H:i' ),
		'time_end' 		=> $range_end->format( 'd.m.Y H:i' ),
		'clinic_id' 	=> center_med_renovatio_get_setting( 'clinic_id', 0 ),
		'show_all' 		=> 1,
	];
	if ( $doctor_step > 0 ) {
		$params['step'] = $doctor_step;
	} else {
		$params['step'] = 60;
	}

	$api_doctors = new Renovatio_Doctor_Service( center_med_renovatio_api_client() );
	$schedule    = $api_doctors->get_schedule( $params );

	if ( is_wp_error( $schedule ) ) {
		wp_send_json_error(
			[
				'message' => $schedule->get_error_message(),
			],
			500
		);
	}

	$slots           = ! empty( $schedule[ $doctor_api_id ] ) ? $schedule[ $doctor_api_id ] : [];
	$days_index      = [];
	$slots_by_day    = [];
	$range_start_day = $range_start->format( 'Y-m-d' );
	$range_end_day   = $range_end->format( 'Y-m-d' );

	foreach ( $slots as $slot ) {
		if ( !empty( $slot['is_busy'] ) ) {
			continue;
		}
		if ( empty( $slot['category_id'] ) || (int) $slot['category_id'] != 2910 ) {
			continue;
		}

		$slot_time_raw = isset( $slot['time_start_short'] ) ? sanitize_text_field( (string) $slot['time_start_short'] ) : '';
		if ( ! preg_match( '/^\d{2}:\d{2}$/', $slot_time_raw ) ) {
			continue;
		}

		$slot_day = $slot['_date'];

		if ( $slot_day < $range_start_day || $slot_day > $range_end_day ) {
			continue;
		}

		$days_index[ $slot_day ] = true;
		if ( empty( $slots_by_day[ $slot_day ] ) ) {
			$slots_by_day[ $slot_day ] = [];
		}

		if ( ! in_array( $slot_time_raw, $slots_by_day[ $slot_day ], true ) ) {
			$slots_by_day[ $slot_day ][] = $slot_time_raw;
		}
	}

	$available_days = array_keys( $days_index );
	sort( $available_days );
	foreach ( $slots_by_day as $day => $day_slots ) {
		sort( $day_slots );
		$slots_by_day[ $day ] = array_values( $day_slots );
	}

	wp_send_json_success(
		[
			'doctorId'      => $doctor_id,
			'month'         => $month,
			'rangeStart'    => $range_start->format( 'Y-m-d' ),
			'rangeEnd'      => $range_end->format( 'Y-m-d' ),
			'availableDays' => $available_days,
			'slotsByDate'   => $slots_by_day,
		]
	);
}
add_action( 'wp_ajax_center_med_renovatio_get_online_doctor_available_days', 'center_med_renovatio_ajax_get_online_doctor_available_days' );
add_action( 'wp_ajax_nopriv_center_med_renovatio_get_online_doctor_available_days', 'center_med_renovatio_ajax_get_online_doctor_available_days' );

/**
 * Ajax: создать заявку на запись (и визит в Renovatio при наличии слота).
 *
 * @return void
 */
function center_med_renovatio_ajax_create_appointment_request() {
	check_ajax_referer( 'clinic_nonce', 'nonce' );

	$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$phone       = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$service     = isset( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '';
	$date_string = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$return_url_raw = isset( $_POST['return_url'] ) ? esc_url_raw( wp_unslash( $_POST['return_url'] ) ) : '';
	$message_raw = isset( $_POST['message'] ) ? trim( (string) wp_unslash( $_POST['message'] ) ) : '';
	$message     = json_decode( $message_raw, true );

	// Иногда message приходит с дополнительным экранированием.
	if ( ! is_array( $message ) && '' !== $message_raw ) {
		$message_unescaped = stripslashes( $message_raw );
		$message           = json_decode( $message_unescaped, true );
	}

	if ( '' === $name || '' === $phone ) {
		wp_send_json_error(
			[
				'message' => __( 'Не заполнены обязательные поля: имя и телефон.', 'center-med-renovatio' ),
			],
			400
		);
	}

	if ( '' !== $email && ! is_email( $email ) ) {
		wp_send_json_error(
			[
				'message' => __( 'Укажите корректный email.', 'center-med-renovatio' ),
			],
			400
		);
	}

	if ( ! is_array( $message ) ) {
		$message = [];
	}

	$form_type          = isset( $message['formType'] ) ? sanitize_text_field( (string) $message['formType'] ) : 'self';
	$is_waiting_list    = ! empty( $message['isWaitingList'] );
	$specialist_post_id = isset( $message['specialistId'] ) ? absint( $message['specialistId'] ) : 0;
	$specialist_name    = isset( $message['specialistName'] ) ? sanitize_text_field( (string) $message['specialistName'] ) : '';
	$specialist_price_raw = isset( $message['specialistPrice'] ) ? sanitize_text_field( (string) $message['specialistPrice'] ) : '';
	$appointment_date   = isset( $message['appointmentDate'] ) ? sanitize_text_field( (string) $message['appointmentDate'] ) : '';
	$appointment_time   = isset( $message['appointmentTime'] ) ? sanitize_text_field( (string) $message['appointmentTime'] ) : '';
	$telegram           = isset( $message['telegram'] ) ? sanitize_text_field( (string) $message['telegram'] ) : '';
	$work_main          = isset( $message['workMain'] ) ? sanitize_textarea_field( (string) $message['workMain'] ) : '';
	$many_work_main     = isset( $message['manyWorkMain'] ) ? sanitize_textarea_field( (string) $message['manyWorkMain'] ) : '';
	$experience_psi     = isset( $message['experiencePsi'] ) ? sanitize_text_field( (string) $message['experiencePsi'] ) : '';
	$self_harm          = isset( $message['selfHarm'] ) ? sanitize_text_field( (string) $message['selfHarm'] ) : '';
	$self_harm_intensity = isset( $message['selfHarmIntensity'] ) ? absint( $message['selfHarmIntensity'] ) : 0;
	$visit_psi          = isset( $message['visitPsi'] ) ? sanitize_text_field( (string) $message['visitPsi'] ) : '';
	$visit_psi_specialist_id = isset( $message['visitPsiSpecialistId'] ) ? absint( $message['visitPsiSpecialistId'] ) : 0;

	$specialist_price_normalized = str_replace( [ ' ', ',' ], [ '', '.' ], $specialist_price_raw );
	$booking_amount = is_numeric( $specialist_price_normalized ) ? (float) $specialist_price_normalized : 0.0;
	if ( $booking_amount <= 0 && $specialist_post_id > 0 ) {
		$doctor_price_raw = (string) get_field( 'cost_1', $specialist_post_id );
		$doctor_price_raw = str_replace( [ ' ', ',' ], [ '', '.' ], $doctor_price_raw );
		if ( is_numeric( $doctor_price_raw ) ) {
			$booking_amount = (float) $doctor_price_raw;
		}
	}

	$booking_public_id = wp_generate_uuid4();
	$appointment_id    = 0;
	$slot_start_mysql  = null;
	$slot_end_mysql    = null;
	$clinic_id         = absint( center_med_renovatio_get_setting( 'clinic_id', 0 ) );
	$doctor_api_id     = 0;
	$api_response      = null;
	$waiting_task_id   = 0;
	$waiting_task_error = '';
	$last_name         = '';
	$return_url_base   = home_url( '/' );

	if ( $specialist_post_id > 0 && 'doctors' === get_post_type( $specialist_post_id ) ) {
		$doctor_api_id = (int) get_post_meta( $specialist_post_id, Renovatio_Doctor_Metabox::META_KEY_DOCTOR_ID, true );
	}

	if ( '' !== $return_url_raw ) {
		$parsed_return = wp_parse_url( $return_url_raw );
		$parsed_home   = wp_parse_url( home_url( '/' ) );
		$return_host   = isset( $parsed_return['host'] ) ? sanitize_text_field( (string) $parsed_return['host'] ) : '';
		$home_host     = isset( $parsed_home['host'] ) ? sanitize_text_field( (string) $parsed_home['host'] ) : '';

		if ( $return_host !== '' && $home_host !== '' && 0 === strcasecmp( $return_host, $home_host ) ) {
			$return_url_base = $return_url_raw;
		}
	}



	if ( ! $is_waiting_list && $specialist_post_id > 0 && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $appointment_date ) && preg_match( '/^\d{2}:\d{2}$/', $appointment_time ) ) {
		if ( 'doctors' !== get_post_type( $specialist_post_id ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Выбранный специалист не найден.', 'center-med-renovatio' ),
				],
				404
			);
		}

		if ( $clinic_id <= 0 ) {
			wp_send_json_error(
				[
					'message' => __( 'Не настроен clinic_id в плагине Renovatio.', 'center-med-renovatio' ),
				],
				500
			);
		}

		if ( $doctor_api_id <= 0 ) {
			wp_send_json_error(
				[
					'message' => __( 'У специалиста не заполнен ID врача в Renovatio.', 'center-med-renovatio' ),
				],
				500
			);
		}

		$service_meta_key  = ( 'many' === $form_type )
			? Renovatio_Doctor_Metabox::META_KEY_SERVICE_PAIR
			: Renovatio_Doctor_Metabox::META_KEY_SERVICE_PERSONAL;
		$doctor_service_id = (int) get_post_meta( $specialist_post_id, $service_meta_key, true );
		if ( $doctor_service_id <= 0 ) {
			wp_send_json_error(
				[
					'message' => __( 'У специалиста не выбрана услуга консультации в настройках МИС.', 'center-med-renovatio' ),
				],
				500
			);
		}

		$timezone   = wp_timezone();
		$start_date = date_create_immutable_from_format( 'Y-m-d H:i', $appointment_date . ' ' . $appointment_time, $timezone );
		if ( false === $start_date ) {
			wp_send_json_error(
				[
					'message' => __( 'Некорректная дата или время записи.', 'center-med-renovatio' ),
				],
				400
			);
		}
		$step_meta_key = ( 'many' === $form_type )
			? Renovatio_Doctor_Metabox::META_KEY_STEP_PAIR
			: Renovatio_Doctor_Metabox::META_KEY_STEP_PERSONAL;
		$doctor_step = (int) get_post_meta( $specialist_post_id, $step_meta_key, true );
		if ( $doctor_step <= 0 ) {
			$doctor_step = 60;
		}
		$end_date = $start_date->modify( '+' . $doctor_step . ' minutes' );

		$slot_start_mysql = $start_date->format( 'Y-m-d H:i:s' );
		$slot_end_mysql   = $end_date->format( 'Y-m-d H:i:s' );

		$name_parts = preg_split( '/\s+/', $name );
		$first_name = ! empty( $name_parts[0] ) ? sanitize_text_field( $name_parts[0] ) : '';
		$last_name  = ! empty( $name_parts[1] ) ? sanitize_text_field( $name_parts[1] ) : '';

		$request_params = [
			'doctor_id'  			=> $doctor_api_id,
			'time_start' 			=> $start_date->format( 'd.m.Y H:i' ),
			'time_end'   			=> $end_date->format( 'd.m.Y H:i' ),
			'clinic_id'  			=> $clinic_id,
			'first_name' 			=> $first_name,
			'last_name'  			=> $last_name,
			'mobile'     			=> $phone,
			'email'      			=> $email,
			'source'     			=> 'website',
			'comment'    			=> $service,
			'check_intersection' 	=> 1,
			'services'              => wp_json_encode(
				[
					[
						'service_id' => $doctor_service_id,
						'count'      => 1,
					],
				]
			),
		];


		$api_response = center_med_renovatio_api_client()->request( 'createAppointment', $request_params );
		if ( is_wp_error( $api_response ) ) {
			wp_send_json_error(
				[
					'message' => $api_response->get_error_message(),
				],
				500
			);
		}

		if ( is_scalar( $api_response ) ) {
			$appointment_id = absint( $api_response );
		} elseif ( is_array( $api_response ) && isset( $api_response['appointment_id'] ) ) {
			$appointment_id = absint( $api_response['appointment_id'] );
		}
	}

	global $wpdb;
	$tables = Renovatio_Db_Schema::get_table_names();

	$stored_payload = [
		'formType'        => $form_type,
		'isWaitingList'   => $is_waiting_list ? 1 : 0,
		'specialistPostId' => $specialist_post_id,
		'specialistName'  => $specialist_name,
		'specialistPrice' => $booking_amount,
		'workMain'        => $work_main,
		'manyWorkMain'    => $many_work_main,
		'experiencePsi'   => $experience_psi,
		'selfHarm'        => $self_harm,
		'selfHarmIntensity' => $self_harm_intensity,
		'visitPsi'        => $visit_psi,
		'visitPsiSpecialistId' => $visit_psi_specialist_id,
		'appointmentDate' => $appointment_date,
		'appointmentTime' => $appointment_time,
		'telegram'        => $telegram,
		'service'         => $service,
		'dateString'      => $date_string,
		'amount'          => $booking_amount,
	];

	$booking_status          = $appointment_id > 0 ? 'created' : 'request';
	$created_at              = current_datetime()->format( 'Y-m-d H:i:s' );
	$updated_at              = $created_at;
	$reservation_expires_at  = '';
	if ( 'created' === $booking_status ) {
		$ttl_minutes = absint( center_med_renovatio_get_setting( 'reservation_ttl_minutes', 15 ) );
		if ( $ttl_minutes <= 0 ) {
			$ttl_minutes = 15;
		}
		$reservation_expires_at = current_datetime()->modify( '+' . $ttl_minutes . ' minutes' )->format( 'Y-m-d H:i:s' );
	}

	$insert_data = [
		'public_id'             => $booking_public_id,
		'status'                => $booking_status,
		'clinic_id'             => $clinic_id,
		'doctor_id'             => $doctor_api_id,
		'slot_start'            => $slot_start_mysql,
		'slot_end'              => $slot_end_mysql,
		'first_name'            => $name,
		'last_name'             => $last_name,
		'phone'                 => $phone,
		'email'                 => $email,
		'telegram'              => $telegram,
		'consent_personal_data' => 1,
		'consent_offer'         => 0,
		'consent_marketing'     => 0,
		'consent_text_version'  => '',
		'payload_json'          => wp_json_encode( $stored_payload ),
		'created_at'            => $created_at,
		'updated_at'            => $updated_at,
	];

	$insert_format = [
		'%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s',
		'%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s',
	];

	if ( $appointment_id > 0 ) {
		$insert_data['appointment_id'] = $appointment_id;
		$insert_format[]               = '%d';
	}

	if ( '' !== $reservation_expires_at ) {
		$insert_data['reservation_expires_at'] = $reservation_expires_at;
		$insert_format[]                       = '%s';
	}

	$inserted = $wpdb->insert( $tables['bookings'], $insert_data, $insert_format );
	if ( false === $inserted ) {
		wp_send_json_error(
			[
				'message' => __( 'Не удалось сохранить заявку в базе данных.', 'center-med-renovatio' ),
			],
			500
		);
	}
	$booking_row_id = (int) $wpdb->insert_id;

	$payment_url    = '';
	$payment_id     = '';
	$payment_status = 'not_created';

	if ( $is_waiting_list && class_exists( 'Renovatio_Task_Service' ) ) {
		$task_service = new Renovatio_Task_Service( center_med_renovatio_api_client() );
		$task_result  = $task_service->create_waiting_list_task(
			[
				'specialist_name'  => $specialist_name,
				'client_name'      => $name,
				'phone'            => $phone,
				'email'            => $email,
				'telegram'         => $telegram,
				'service'          => $service,
				'form_type'        => $form_type,
				'booking_public_id' => $booking_public_id,
				'doctor_id'        => $doctor_api_id,
				'clinic_id'        => $clinic_id,
				'work_main'        => $work_main,
				'many_work_main'   => $many_work_main,
				'experience_psi'   => $experience_psi,
				'self_harm'        => $self_harm,
				'self_harm_intensity' => $self_harm_intensity,
				'visit_psi'        => $visit_psi,
				'visit_psi_specialist_id' => $visit_psi_specialist_id,
			]
		);

		if ( is_wp_error( $task_result ) ) {
			$waiting_task_error = sanitize_text_field( $task_result->get_error_message() );
		} elseif ( is_scalar( $task_result ) ) {
			$waiting_task_id = absint( $task_result );
		} elseif ( is_array( $task_result ) && isset( $task_result['task_id'] ) ) {
			$waiting_task_id = absint( $task_result['task_id'] );
		}
	}

	if ( $appointment_id > 0 && ! $is_waiting_list && class_exists( 'TochkaPayment' ) && $booking_amount > 0 ) {
		$return_url = add_query_arg(
			[
				'clinic_payment_return' => 1,
				'booking'               => $booking_public_id,
			],
			$return_url_base
		);

		$tochka_payment  = new TochkaPayment();
		$payment_result  = $tochka_payment->create_payment(
			$booking_public_id,
			$booking_amount,
			$service !== '' ? $service : ( 'Оплата консультации: ' . $specialist_name ),
			$return_url
		);

		if ( ! is_wp_error( $payment_result ) ) {
			$payment_url    = ! empty( $payment_result['Data']['paymentLink'] ) ? esc_url_raw( (string) $payment_result['Data']['paymentLink'] ) : '';
			$payment_id     = ! empty( $payment_result['Data']['operationId'] ) ? sanitize_text_field( (string) $payment_result['Data']['operationId'] ) : '';
			$payment_status = $payment_url !== '' ? 'created' : 'pending';

			$wpdb->update(
				$tables['bookings'],
				[
					'payment_provider'    => 'tochka',
					'payment_external_id' => $payment_id,
					'updated_at'          => current_time( 'mysql' ),
				],
				[ 'id' => $booking_row_id ],
				[ '%s', '%s', '%s' ],
				[ '%d' ]
			);
		} else {
			$payment_status = 'failed';
		}
	}

	do_action(
		'center_med_renovatio_appointment_request_created',
		[
			'booking_public_id' => $booking_public_id,
			'appointment_id'    => $appointment_id,
			'clinic_id'         => $clinic_id,
			'doctor_id'         => $doctor_api_id,
			'name'              => $name,
			'phone'             => $phone,
			'email'             => $email,
			'service'           => $service,
			'message'           => $stored_payload,
			'api_response'      => $api_response,
			'payment_url'       => $payment_url,
			'payment_id'        => $payment_id,
			'payment_status'    => $payment_status,
			'waiting_task_id'   => $waiting_task_id,
			'waiting_task_error' => $waiting_task_error,
		]
	);

	wp_send_json_success(
		[
			'message'         => __( 'Заявка успешно отправлена.', 'center-med-renovatio' ),
			'bookingPublicId' => $booking_public_id,
			'appointmentId'   => $appointment_id,
			'paymentUrl'      => $payment_url,
			'paymentId'       => $payment_id,
			'paymentStatus'   => $payment_status,
			'waitingTaskId'   => $waiting_task_id,
		]
	);
}
add_action( 'wp_ajax_clinic_create_appointment_request', 'center_med_renovatio_ajax_create_appointment_request' );
add_action( 'wp_ajax_nopriv_clinic_create_appointment_request', 'center_med_renovatio_ajax_create_appointment_request' );

/**
 * Получить запись платежа Точки по public_id брони.
 *
 * @param string $booking_public_id UUID брони.
 * @return array|null
 */
function center_med_renovatio_get_tochka_payment_row( $booking_public_id ) {
	global $wpdb;

	$booking_public_id = sanitize_text_field( (string) $booking_public_id );
	if ( '' === $booking_public_id ) {
		return null;
	}

	$table = $wpdb->prefix . 'tochka_payments';

	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE order_id = %s LIMIT 1",
			$booking_public_id
		),
		ARRAY_A
	);
}

/**
 * Нормализовать статус Точки.
 *
 * @param string $provider_status Статус провайдера.
 * @return string
 */
function center_med_renovatio_normalize_tochka_status( $provider_status ) {
	$status = strtolower( sanitize_text_field( (string) $provider_status ) );

	if ( in_array( $status, [ 'completed', 'approved', 'paid', 'success' ], true ) ) {
		return 'paid';
	}

	if ( in_array( $status, [ 'failed', 'declined', 'cancelled', 'canceled', 'expired' ], true ) ) {
		return 'failed';
	}

	return 'pending';
}

/**
 * Ajax: получить текущее состояние оплаты брони.
 *
 * @return void
 */
function center_med_renovatio_ajax_get_booking_payment_state() {
	check_ajax_referer( 'clinic_nonce', 'nonce' );

	$booking_public_id = isset( $_POST['booking_public_id'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_public_id'] ) ) : '';
	if ( '' === $booking_public_id ) {
		wp_send_json_error(
			[
				'message' => __( 'Не передан booking_public_id.', 'center-med-renovatio' ),
			],
			400
		);
	}

	$booking = center_med_renovatio_get_booking_for_tochka( $booking_public_id );
	if ( ! is_array( $booking ) ) {
		wp_send_json_error(
			[
				'message' => __( 'Бронь не найдена.', 'center-med-renovatio' ),
			],
			404
		);
	}

	$payment_row     = center_med_renovatio_get_tochka_payment_row( $booking_public_id );
	$provider_status = is_array( $payment_row ) ? sanitize_text_field( (string) ( $payment_row['status'] ?? '' ) ) : '';
	$payment_status  = center_med_renovatio_normalize_tochka_status( $provider_status );
	$payment_id      = is_array( $payment_row ) ? sanitize_text_field( (string) ( $payment_row['payment_id'] ?? '' ) ) : '';
	$payment_url     = is_array( $payment_row ) ? esc_url_raw( (string) ( $payment_row['payment_url'] ?? '' ) ) : '';
	$booking_status  = sanitize_text_field( (string) ( $booking['status'] ?? '' ) );
	$is_canceled     = in_array( $booking_status, [ 'canceled', 'cancelled' ], true );

	if ( $is_canceled ) {
		$payment_status = 'failed';
	}

	if ( ! $is_canceled && 'paid' === $payment_status && $booking_status !== 'paid' ) {
		center_med_renovatio_mark_booking_paid(
			$booking_public_id,
			'tochka',
			$payment_id,
			[
				'source' => 'polling',
			]
		);
		$booking = center_med_renovatio_get_booking_for_tochka( $booking_public_id );
		$booking_status = sanitize_text_field( (string) ( $booking['status'] ?? '' ) );
	}

	wp_send_json_success(
		[
			'bookingPublicId' => $booking_public_id,
			'bookingStatus'   => $booking_status,
			'paymentStatus'   => $payment_status,
			'providerStatus'  => $provider_status,
			'paymentId'       => $payment_id,
			'paymentUrl'      => $payment_url,
		]
	);
}
add_action( 'wp_ajax_clinic_get_booking_payment_state', 'center_med_renovatio_ajax_get_booking_payment_state' );
add_action( 'wp_ajax_nopriv_clinic_get_booking_payment_state', 'center_med_renovatio_ajax_get_booking_payment_state' );

/**
 * Ajax: повторно создать ссылку оплаты для существующей брони.
 *
 * @return void
 */
function center_med_renovatio_ajax_retry_booking_payment() {
	check_ajax_referer( 'clinic_nonce', 'nonce' );

	$booking_public_id = isset( $_POST['booking_public_id'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_public_id'] ) ) : '';
	$return_url_raw    = isset( $_POST['return_url'] ) ? esc_url_raw( wp_unslash( $_POST['return_url'] ) ) : '';

	if ( '' === $booking_public_id ) {
		wp_send_json_error(
			[
				'message' => __( 'Не передан booking_public_id.', 'center-med-renovatio' ),
			],
			400
		);
	}

	$booking = center_med_renovatio_get_booking_for_tochka( $booking_public_id );
	if ( ! is_array( $booking ) ) {
		wp_send_json_error(
			[
				'message' => __( 'Бронь не найдена.', 'center-med-renovatio' ),
			],
			404
		);
	}

	if ( (string) ( $booking['status'] ?? '' ) === 'paid' ) {
		wp_send_json_error(
			[
				'message' => __( 'Бронь уже оплачена.', 'center-med-renovatio' ),
			],
			409
		);
	}
	if ( in_array( (string) ( $booking['status'] ?? '' ), [ 'canceled', 'cancelled' ], true ) ) {
		wp_send_json_error(
			[
				'message' => __( 'Бронь отменена. Повторная оплата недоступна.', 'center-med-renovatio' ),
			],
			409
		);
	}

	if ( empty( $booking['appointment_id'] ) ) {
		wp_send_json_error(
			[
				'message' => __( 'Для этой заявки не создан визит в МИС.', 'center-med-renovatio' ),
			],
			409
		);
	}

	if ( ! class_exists( 'TochkaPayment' ) ) {
		wp_send_json_error(
			[
				'message' => __( 'Плагин Точка Банк не активен.', 'center-med-renovatio' ),
			],
			500
		);
	}

	$amount = center_med_renovatio_extract_booking_amount( $booking );
	if ( $amount <= 0 ) {
		wp_send_json_error(
			[
				'message' => __( 'Не удалось определить сумму оплаты по заявке.', 'center-med-renovatio' ),
			],
			500
		);
	}

	$payload = [];
	if ( ! empty( $booking['payload_json'] ) ) {
		$decoded_payload = json_decode( (string) $booking['payload_json'], true );
		if ( is_array( $decoded_payload ) ) {
			$payload = $decoded_payload;
		}
	}
	$specialist_name = isset( $payload['specialistName'] ) ? sanitize_text_field( (string) $payload['specialistName'] ) : '';
	$service_title   = isset( $payload['service'] ) ? sanitize_text_field( (string) $payload['service'] ) : '';

	$return_url_base = home_url( '/' );
	if ( '' !== $return_url_raw ) {
		$parsed_return = wp_parse_url( $return_url_raw );
		$parsed_home   = wp_parse_url( home_url( '/' ) );
		$return_host   = isset( $parsed_return['host'] ) ? sanitize_text_field( (string) $parsed_return['host'] ) : '';
		$home_host     = isset( $parsed_home['host'] ) ? sanitize_text_field( (string) $parsed_home['host'] ) : '';

		if ( $return_host !== '' && $home_host !== '' && 0 === strcasecmp( $return_host, $home_host ) ) {
			$return_url_base = $return_url_raw;
		}
	}

	$return_url = add_query_arg(
		[
			'clinic_payment_return' => 1,
			'booking'               => $booking_public_id,
		],
		$return_url_base
	);

	$description = $service_title !== '' ? $service_title : ( 'Оплата консультации: ' . $specialist_name );
	$tochka      = new TochkaPayment();
	$result      = $tochka->create_payment( $booking_public_id, $amount, $description, $return_url );
	if ( is_wp_error( $result ) ) {
		wp_send_json_error(
			[
				'message' => $result->get_error_message(),
			],
			500
		);
	}

	$payment_url    = ! empty( $result['Data']['paymentLink'] ) ? esc_url_raw( (string) $result['Data']['paymentLink'] ) : '';
	$payment_id     = ! empty( $result['Data']['operationId'] ) ? sanitize_text_field( (string) $result['Data']['operationId'] ) : '';
	$payment_status = $payment_url !== '' ? 'created' : 'pending';

	global $wpdb;
	$tables = Renovatio_Db_Schema::get_table_names();
	$wpdb->update(
		$tables['bookings'],
		[
			'payment_provider'    => 'tochka',
			'payment_external_id' => $payment_id,
			'updated_at'          => current_time( 'mysql' ),
		],
		[ 'id' => (int) $booking['id'] ],
		[ '%s', '%s', '%s' ],
		[ '%d' ]
	);

	wp_send_json_success(
		[
			'bookingPublicId' => $booking_public_id,
			'paymentUrl'      => $payment_url,
			'paymentId'       => $payment_id,
			'paymentStatus'   => $payment_status,
		]
	);
}
add_action( 'wp_ajax_clinic_retry_booking_payment', 'center_med_renovatio_ajax_retry_booking_payment' );
add_action( 'wp_ajax_nopriv_clinic_retry_booking_payment', 'center_med_renovatio_ajax_retry_booking_payment' );