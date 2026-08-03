<?php
/**
 * REST API: создание задач МИС для внешних доменов.
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Tasks_Rest_Controller
 */
class Renovatio_Tasks_Rest_Controller {

	/**
	 * Namespace REST.
	 *
	 * @var string
	 */
	const NAMESPACE = 'renovatio/v1';

	/**
	 * Разрешённый Origin для CORS.
	 *
	 * @var string
	 */
	const ALLOWED_ORIGIN = 'https://clinic.handlingbetter.ru';

	/**
	 * Регистрация хуков.
	 */
	public static function register() {
		$instance = new self();
		add_action( 'rest_api_init', [ $instance, 'register_routes' ] );
		add_filter( 'rest_pre_serve_request', [ $instance, 'send_cors_headers' ], 15, 4 );
	}

	/**
	 * Регистрация маршрутов.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/tasks',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_task' ],
					'permission_callback' => [ $this, 'check_permission' ],
					'args'                => $this->get_create_args(),
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'options_probe' ],
					'permission_callback' => '__return_true',
				],
			]
		);
	}

	/**
	 * Аргументы POST /tasks.
	 *
	 * @return array
	 */
	private function get_create_args() {
		return [
			'title' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'desc' => [
				'required' => false,
				'type'     => 'string',
				'default'  => '',
			],
			'type' => [
				'required' => false,
				'type'     => 'integer',
				'default'  => 2,
			],
			'source' => [
				'required'          => false,
				'type'              => 'string',
				'default'           => 'website',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'user_ids' => [
				'required' => false,
				'type'     => 'array',
				'items'    => [
					'type' => 'integer',
				],
				'default'  => [],
			],
			'responsible_id' => [
				'required' => false,
				'type'     => 'integer',
				'default'  => 0,
			],
			'clinic_id' => [
				'required' => false,
				'type'     => 'integer',
				'default'  => 0,
			],
			'priority' => [
				'required' => false,
				'type'     => 'integer',
			],
			'due_date' => [
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'due_time' => [
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'patient_id' => [
				'required' => false,
				'type'     => 'integer',
				'default'  => 0,
			],
		];
	}

	/**
	 * Проверка ключа внешнего API.
	 *
	 * @param WP_REST_Request $request Запрос.
	 * @return true|WP_Error
	 */
	public function check_permission( WP_REST_Request $request ) {
		$expected = (string) center_med_renovatio_get_setting( 'tasks_api_key', '' );
		if ( '' === $expected ) {
			return new WP_Error(
				'tasks_api_disabled',
				__( 'Внешний API задач не настроен (нет ключа в настройках Renovatio МИС).', 'center-med-renovatio' ),
				[ 'status' => 503 ]
			);
		}

		$provided = $request->get_header( 'x-renovatio-tasks-key' );
		if ( ! is_string( $provided ) || '' === $provided ) {
			$provided = $request->get_param( 'api_key' );
		}
		$provided = is_string( $provided ) ? trim( $provided ) : '';

		if ( '' === $provided || ! hash_equals( $expected, $provided ) ) {
			return new WP_Error(
				'tasks_api_forbidden',
				__( 'Неверный ключ доступа.', 'center-med-renovatio' ),
				[ 'status' => 401 ]
			);
		}

		return true;
	}

	/**
	 * Создание задачи из внешнего запроса.
	 *
	 * @param WP_REST_Request $request Запрос.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_task( WP_REST_Request $request ) {
		$service = new Renovatio_Task_Service( center_med_renovatio_api_client() );
		$result  = $service->create_task(
			[
				'title'          => $request->get_param( 'title' ),
				'desc'           => (string) $request->get_param( 'desc' ),
				'type'           => absint( $request->get_param( 'type' ) ),
				'source'         => (string) $request->get_param( 'source' ),
				'user_ids'       => (array) $request->get_param( 'user_ids' ),
				'responsible_id' => absint( $request->get_param( 'responsible_id' ) ),
				'clinic_id'      => absint( $request->get_param( 'clinic_id' ) ),
				'priority'       => $request->get_param( 'priority' ),
				'due_date'       => $request->get_param( 'due_date' ),
				'due_time'       => $request->get_param( 'due_time' ),
				'patient_id'     => absint( $request->get_param( 'patient_id' ) ),
				'context'        => 'external',
			]
		);

		$mis_payload = $service->get_last_request_params();

		if ( is_wp_error( $result ) ) {
			$status = (int) $result->get_error_data( 'status' );
			if ( $status <= 0 ) {
				$status = 502;
			}
			return new WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				[
					'status'      => $status,
					'mis_payload' => $mis_payload,
				]
			);
		}

		$task_id = 0;
		if ( is_numeric( $result ) ) {
			$task_id = (int) $result;
		} elseif ( is_array( $result ) ) {
			if ( isset( $result['task_id'] ) ) {
				$task_id = (int) $result['task_id'];
			} elseif ( isset( $result['id'] ) ) {
				$task_id = (int) $result['id'];
			} elseif ( isset( $result[0] ) && is_numeric( $result[0] ) ) {
				$task_id = (int) $result[0];
			}
		}

		return new WP_REST_Response(
			[
				'success'     => true,
				'task_id'     => $task_id,
				'data'        => $result,
				'mis_payload' => $mis_payload,
			],
			201
		);
	}

	/**
	 * Простой ответ для health/probe.
	 *
	 * @return WP_REST_Response
	 */
	public function options_probe() {
		return new WP_REST_Response(
			[
				'ok'      => true,
				'endpoint' => self::NAMESPACE . '/tasks',
			],
			200
		);
	}

	/**
	 * CORS для clinic.handlingbetter.ru.
	 *
	 * @param bool             $served  Уже отдан.
	 * @param WP_HTTP_Response $result  Ответ.
	 * @param WP_REST_Request  $request Запрос.
	 * @param WP_REST_Server   $server  Сервер.
	 * @return bool
	 */
	public function send_cors_headers( $served, $result, $request, $server ) {
		$route = $request instanceof WP_REST_Request ? (string) $request->get_route() : '';
		if ( 0 !== strpos( $route, '/' . self::NAMESPACE . '/tasks' ) ) {
			return $served;
		}

		$origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? esc_url_raw( wp_unslash( (string) $_SERVER['HTTP_ORIGIN'] ) ) : '';
		if ( self::ALLOWED_ORIGIN === $origin ) {
			header( 'Access-Control-Allow-Origin: ' . self::ALLOWED_ORIGIN );
			header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS' );
			header( 'Access-Control-Allow-Headers: Content-Type, X-Renovatio-Tasks-Key' );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Vary: Origin' );
		}

		return $served;
	}
}
