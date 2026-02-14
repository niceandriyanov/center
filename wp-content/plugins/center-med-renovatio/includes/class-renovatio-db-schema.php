<?php
/**
 * Создание таблиц плагина Center Med — Renovatio.
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Db_Schema
 */
class Renovatio_Db_Schema {

	/**
	 * Версия схемы БД плагина.
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Установить/обновить таблицы.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$tables          = self::get_table_names();

		$sql_bookings = "CREATE TABLE {$tables['bookings']} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			public_id CHAR(36) NOT NULL,
			status VARCHAR(32) NOT NULL DEFAULT 'draft',
			clinic_id INT UNSIGNED NOT NULL DEFAULT 0,
			doctor_id INT UNSIGNED NOT NULL DEFAULT 0,
			appointment_id BIGINT UNSIGNED NULL,
			slot_start DATETIME NULL,
			slot_end DATETIME NULL,
			reservation_expires_at DATETIME NULL,
			paid_at DATETIME NULL,
			canceled_at DATETIME NULL,
			cancel_reason VARCHAR(255) NULL,
			first_name VARCHAR(100) NOT NULL DEFAULT '',
			last_name VARCHAR(100) NOT NULL DEFAULT '',
			age SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			phone VARCHAR(32) NOT NULL DEFAULT '',
			email VARCHAR(190) NOT NULL DEFAULT '',
			telegram VARCHAR(100) NULL,
			consent_personal_data TINYINT(1) NOT NULL DEFAULT 0,
			consent_offer TINYINT(1) NOT NULL DEFAULT 0,
			consent_marketing TINYINT(1) NOT NULL DEFAULT 0,
			consent_text_version VARCHAR(32) NOT NULL DEFAULT '',
			payment_provider VARCHAR(32) NULL,
			payment_external_id VARCHAR(128) NULL,
			payload_json LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY public_id (public_id),
			UNIQUE KEY appointment_id (appointment_id),
			KEY status (status),
			KEY clinic_id (clinic_id),
			KEY doctor_id (doctor_id),
			KEY reservation_expires_at (reservation_expires_at),
			KEY phone (phone),
			KEY email (email),
			KEY payment_external_id (payment_external_id)
		) $charset_collate;";

		$sql_status_log = "CREATE TABLE {$tables['status_log']} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			booking_id BIGINT UNSIGNED NOT NULL,
			from_status VARCHAR(32) NULL,
			to_status VARCHAR(32) NOT NULL,
			source VARCHAR(32) NOT NULL DEFAULT 'system',
			message TEXT NULL,
			context_json LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY booking_id (booking_id),
			KEY to_status (to_status),
			KEY created_at (created_at)
		) $charset_collate;";

		$sql_payment_events = "CREATE TABLE {$tables['payment_events']} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			provider VARCHAR(32) NOT NULL,
			external_event_id VARCHAR(191) NOT NULL,
			booking_public_id CHAR(36) NOT NULL,
			event_type VARCHAR(64) NULL,
			event_status VARCHAR(64) NULL,
			raw_payload LONGTEXT NULL,
			processed_at DATETIME NULL,
			result VARCHAR(32) NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY external_event_id (external_event_id),
			KEY provider (provider),
			KEY booking_public_id (booking_public_id),
			KEY created_at (created_at)
		) $charset_collate;";

		dbDelta( $sql_bookings );
		dbDelta( $sql_status_log );
		dbDelta( $sql_payment_events );

		update_option( 'center_med_renovatio_db_version', self::DB_VERSION );
	}

	/**
	 * Получить имена таблиц.
	 *
	 * @return array<string, string>
	 */
	public static function get_table_names() {
		global $wpdb;

		return [
			'bookings'       => $wpdb->prefix . 'cmr_bookings',
			'status_log'     => $wpdb->prefix . 'cmr_booking_status_log',
			'payment_events' => $wpdb->prefix . 'cmr_payment_events',
		];
	}
}
