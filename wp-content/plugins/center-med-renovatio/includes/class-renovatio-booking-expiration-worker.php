<?php
/**
 * Авто-отмена неоплаченных визитов по TTL.
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Booking_Expiration_Worker
 */
class Renovatio_Booking_Expiration_Worker {

	/**
	 * Cron-хук воркера.
	 */
	const CRON_HOOK = 'center_med_renovatio_expire_unpaid_bookings';

	/**
	 * Ключ интервала cron.
	 */
	const CRON_INTERVAL = 'center_med_renovatio_every_3_minutes';

	/**
	 * Регистрация хуков воркера.
	 *
	 * @return void
	 */
	public static function register() {
		add_filter( 'cron_schedules', [ __CLASS__, 'add_cron_schedule' ] );
		add_action( 'init', [ __CLASS__, 'schedule_event' ] );
		add_action( self::CRON_HOOK, [ __CLASS__, 'process_expired_bookings' ] );
	}

	/**
	 * Добавить интервал в 3 минуты.
	 *
	 * @param array $schedules Список интервалов.
	 * @return array
	 */
	public static function add_cron_schedule( $schedules ) {
		if ( ! isset( $schedules[ self::CRON_INTERVAL ] ) ) {
			$schedules[ self::CRON_INTERVAL ] = [
				'interval' => 3 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 3 Minutes (Center Med Renovatio)', 'center-med-renovatio' ),
			];
		}

		return $schedules;
	}

	/**
	 * Планирование cron-события.
	 *
	 * @return void
	 */
	public static function schedule_event() {
		if ( wp_next_scheduled( self::CRON_HOOK ) ) {
			return;
		}

		wp_schedule_event( time() + MINUTE_IN_SECONDS, self::CRON_INTERVAL, self::CRON_HOOK );
	}

	/**
	 * Снять расписание cron при деактивации.
	 *
	 * @return void
	 */
	public static function clear_schedule() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		while ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
			$timestamp = wp_next_scheduled( self::CRON_HOOK );
		}
	}

	/**
	 * Отмена просроченных неоплаченных визитов.
	 *
	 * @return void
	 */
	public static function process_expired_bookings() {
		global $wpdb;

		$ttl_minutes = absint( center_med_renovatio_get_setting( 'reservation_ttl_minutes', 15 ) );
		if ( $ttl_minutes <= 0 ) {
			$ttl_minutes = 15;
		}

		$tables     = Renovatio_Db_Schema::get_table_names();
		$now_mysql  = current_time( 'mysql' );
		$sql        = $wpdb->prepare(
			"SELECT id, public_id, status, appointment_id
			FROM {$tables['bookings']}
			WHERE status = %s
				AND appointment_id IS NOT NULL
				AND appointment_id > 0
				AND (
					(
						reservation_expires_at IS NOT NULL
						AND reservation_expires_at <> '0000-00-00 00:00:00'
						AND reservation_expires_at <= %s
					)
					OR (
						(
							reservation_expires_at IS NULL
							OR reservation_expires_at = '0000-00-00 00:00:00'
						)
						AND created_at <= DATE_SUB(%s, INTERVAL %d MINUTE)
					)
				)
			ORDER BY id ASC
			LIMIT 50",
			'created',
			$now_mysql,
			$now_mysql,
			$ttl_minutes
		);

		$bookings   = $wpdb->get_results( $sql, ARRAY_A );

		if ( empty( $bookings ) ) {
			return;
		}

		$cancel_reason = sanitize_text_field( (string) center_med_renovatio_get_setting( 'cancel_reason_unpaid', 'Оплата не прошла' ) );
		if ( '' === $cancel_reason ) {
			$cancel_reason = 'Оплата не прошла';
		}

		foreach ( $bookings as $booking ) {
			$booking_public_id = isset( $booking['public_id'] ) ? sanitize_text_field( (string) $booking['public_id'] ) : '';
			if ( '' === $booking_public_id ) {
				continue;
			}

			$cancel_result = center_med_renovatio_cancel_booking_unpaid(
				$booking_public_id,
				$cancel_reason,
				'cron_unpaid',
				[
					'ttl_minutes' => $ttl_minutes,
				]
			);
			if ( is_wp_error( $cancel_result ) ) {
				continue;
			}
		}
	}
}

