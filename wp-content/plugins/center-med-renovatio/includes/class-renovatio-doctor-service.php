<?php
/**
 * Сервис работы с врачами в МИС Renovatio.
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Doctor_Service
 */
class Renovatio_Doctor_Service {

	/**
	 * API-клиент.
	 *
	 * @var Renovatio_Api_Client
	 */
	private $api_client;

	/**
	 * Конструктор.
	 *
	 * @param Renovatio_Api_Client $api_client Экземпляр API-клиента.
	 */
	public function __construct( Renovatio_Api_Client $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Получить расписание врачей (метод API getSchedule).
	 *
	 * Поддерживает одиночный ID врача или список ID врачей.
	 * Параметр user_id на выходе всегда передается строкой "1,2,3".
	 *
	 * @param array $params Параметры запроса API.
	 * @return array|WP_Error
	 */
	public function get_schedule( array $params = [] ) {
		$request_params = $this->normalize_schedule_params( $params );

		if ( empty( $request_params['user_id'] ) ) {
			return new WP_Error(
				'missing_user_id',
				__( 'Для получения расписания необходимо указать user_id.', 'center-med-renovatio' )
			);
		}

		return $this->api_client->request( 'getSchedule', $request_params );
	}

	/**
	 * Нормализация и базовая санитизация параметров getSchedule.
	 *
	 * @param array $params Параметры.
	 * @return array
	 */
	private function normalize_schedule_params( array $params ) {
		$normalized = [];

		if ( isset( $params['clinic_id'] ) ) {
			$clinic_id = absint( $params['clinic_id'] );
			if ( $clinic_id > 0 ) {
				$normalized['clinic_id'] = $clinic_id;
			}
		}
		else {
			$normalized['clinic_id'] = center_med_renovatio_get_setting( 'clinic_id', 0 );
		}

		$user_ids = $this->normalize_user_ids( $params );
		if ( ! empty( $user_ids ) ) {
			$normalized['user_id'] = implode( ',', $user_ids );
		}

		if ( isset( $params['service_id'] ) ) {
			$service_id = absint( $params['service_id'] );
			if ( $service_id > 0 ) {
				$normalized['service_id'] = $service_id;
			}
		}

		if ( isset( $params['time_start'] ) && is_string( $params['time_start'] ) ) {
			$normalized['time_start'] = sanitize_text_field( $params['time_start'] );
		}

		if ( isset( $params['time_end'] ) && is_string( $params['time_end'] ) ) {
			$normalized['time_end'] = sanitize_text_field( $params['time_end'] );
		}

		if ( isset( $params['step'] ) ) {
			$step = absint( $params['step'] );
			if ( in_array( $step, [ 10, 15, 20, 30, 60 ], true ) ) {
				$normalized['step'] = $step;
			}
		}

		if ( isset( $params['mode'] ) && is_string( $params['mode'] ) ) {
			$mode = sanitize_text_field( $params['mode'] );
			if ( in_array( $mode, [ 'slots', 'visits' ], true ) ) {
				$normalized['mode'] = $mode;
			}
		}

		foreach ( [ 'use_doctor_avg_time', 'all_clinics', 'show_busy', 'show_past', 'show_all' ] as $flag ) {
			if ( isset( $params[ $flag ] ) ) {
				$normalized[ $flag ] = (int) (bool) $params[ $flag ];
			}
		}

		if ( isset( $params['source'] ) && is_string( $params['source'] ) ) {
			$normalized['source'] = sanitize_text_field( $params['source'] );
		}

		return $normalized;
	}

	/**
	 * Нормализация user_id: int|string|array -> int[].
	 *
	 * @param array $params Параметры.
	 * @return int[]
	 */
	private function normalize_user_ids( array $params ) {
		if ( isset( $params['user_ids'] ) && is_array( $params['user_ids'] ) ) {
			$raw = $params['user_ids'];
		} elseif ( isset( $params['user_id'] ) ) {
			$raw = $params['user_id'];
		} else {
			return [];
		}

		if ( is_string( $raw ) ) {
			$raw = explode( ',', $raw );
		}

		if ( is_numeric( $raw ) ) {
			$raw = [ $raw ];
		}

		if ( ! is_array( $raw ) ) {
			return [];
		}

		$ids = array_values(
			array_unique(
				array_filter(
					array_map( 'absint', $raw ),
					static function( $id ) {
						return $id > 0;
					}
				)
			)
		);

		return $ids;
	}
}

