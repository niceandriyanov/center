<?php
/**
 * API-клиент МИС Renovatio
 *
 * Обёртка для работы с API https://app.rnova.org/api/public/
 * Документация: см. docs/ais.md
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Api_Client
 */
class Renovatio_Api_Client {

	/**
	 * Базовый URL API (без метода и слеша в конце).
	 *
	 * @var string
	 */
	private $base_url = 'https://app.rnova.org/api/public';

	/**
	 * Ключ API (обязательный параметр для каждого запроса).
	 *
	 * @var string
	 */
	private $api_key = '';

	/**
	 * Таймаут запроса в секундах.
	 *
	 * @var int
	 */
	private $timeout = 15;

	/**
	 * Последний сырой ответ (для отладки).
	 *
	 * @var array{ code: int, body: string }|null
	 */
	private $last_response;

	/**
	 * Конструктор.
	 *
	 * @param array $options Массив настроек: api_key (обязательно), base_url (опционально), timeout (опционально).
	 */
	public function __construct( array $options = [] ) {
		if ( ! empty( $options['api_key'] ) ) {
			$this->api_key = (string) $options['api_key'];
		}
		if ( ! empty( $options['base_url'] ) ) {
			$this->base_url = rtrim( (string) $options['base_url'], '/' );
		}
		if ( isset( $options['timeout'] ) && (int) $options['timeout'] > 0 ) {
			$this->timeout = (int) $options['timeout'];
		}
	}

	/**
	 * Выполнить запрос к API.
	 *
	 * @param string $method Имя метода API (например getClinics, getSchedule, createAppointment).
	 * @param array  $params Параметры запроса (api_key добавляется автоматически).
	 * @return array|WP_Error При успехе — массив data из ответа; при ошибке — WP_Error с code и сообщением.
	 */
	public function request( $method, array $params = [] ) {
		$this->last_response = null;

		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'Не указан API ключ МИС Renovatio.', 'center-med-renovatio' ) );
		}

		$params['api_key'] = $this->api_key;
		$url               = $this->base_url . '/' . trim( $method, '/' );

		$response = wp_remote_post(
			$url,
			[
				'timeout' => $this->timeout,
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body'    => $params,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$this->last_response = [ 'code' => $code, 'body' => $body ];

		$decoded = json_decode( $body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error(
				'invalid_json',
				__( 'Некорректный ответ API (не JSON).', 'center-med-renovatio' ),
				[ 'body' => $body ]
			);
		}

		if ( ! is_array( $decoded ) || ! array_key_exists( 'error', $decoded ) ) {
			return new WP_Error(
				'invalid_response',
				__( 'Неожиданная структура ответа API.', 'center-med-renovatio' ),
				[ 'decoded' => $decoded ]
			);
		}

		if ( (int) $decoded['error'] === 1 ) {
			$data = isset( $decoded['data'] ) && is_array( $decoded['data'] ) ? $decoded['data'] : [];
			$code = isset( $data['code'] ) ? $data['code'] : 'api_error';
			$desc = isset( $data['desc'] ) ? $data['desc'] : __( 'Ошибка API МИС Renovatio.', 'center-med-renovatio' );
			return new WP_Error( $code, $desc, $data );
		}

		return isset( $decoded['data'] ) ? $decoded['data'] : [];
	}

	/**
	 * Получить последний сырой ответ (для отладки).
	 *
	 * @return array{ code: int, body: string }|null
	 */
	public function get_last_response() {
		return $this->last_response;
	}

	/**
	 * Проверить, настроен ли клиент (есть api_key).
	 *
	 * @return bool
	 */
	public function is_configured() {
		return $this->api_key !== '';
	}
}
