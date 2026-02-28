<?php
/**
 * Сервис создания задач в МИС Renovatio.
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Task_Service
 */
class Renovatio_Task_Service {

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
	 * Создать задачу по заявке из листа ожидания.
	 *
	 * @param array $payload Данные заявки.
	 * @return array|int|WP_Error
	 */
	public function create_waiting_list_task( array $payload ) {
		$specialist_name = isset( $payload['specialist_name'] ) ? sanitize_text_field( (string) $payload['specialist_name'] ) : '';
		$client_name     = isset( $payload['client_name'] ) ? sanitize_text_field( (string) $payload['client_name'] ) : '';
		$phone           = isset( $payload['phone'] ) ? sanitize_text_field( (string) $payload['phone'] ) : '';
		$email           = isset( $payload['email'] ) ? sanitize_email( (string) $payload['email'] ) : '';
		$telegram        = isset( $payload['telegram'] ) ? sanitize_text_field( (string) $payload['telegram'] ) : '';
		$service         = isset( $payload['service'] ) ? sanitize_text_field( (string) $payload['service'] ) : '';
		$form_type       = isset( $payload['form_type'] ) ? sanitize_text_field( (string) $payload['form_type'] ) : 'self';
		$booking_id      = isset( $payload['booking_public_id'] ) ? sanitize_text_field( (string) $payload['booking_public_id'] ) : '';
		$doctor_id       = isset( $payload['doctor_id'] ) ? absint( $payload['doctor_id'] ) : 0;
		$clinic_id       = isset( $payload['clinic_id'] ) ? absint( $payload['clinic_id'] ) : 0;

		$title = $specialist_name !== ''
			? sprintf( 'Лист ожидания: %s', $specialist_name )
			: 'Лист ожидания: новая заявка';

		$form_label = ( 'many' === $form_type ) ? 'Для пары' : 'Для себя';
		$desc_lines = [
			sprintf( 'Форма: %s', $form_label ),
			sprintf( 'Клиент: %s', $client_name !== '' ? $client_name : '-' ),
			sprintf( 'Телефон: %s', $phone !== '' ? $phone : '-' ),
			sprintf( 'Email: %s', $email !== '' ? $email : '-' ),
			sprintf( 'Telegram: %s', $telegram !== '' ? $telegram : '-' ),
			sprintf( 'Услуга: %s', $service !== '' ? $service : '-' ),
			sprintf( 'Booking ID: %s', $booking_id !== '' ? $booking_id : '-' ),
		];

		$request_params = [
			'title'  => $title,
			'desc'   => implode( "\n", $desc_lines ),
			'type'   => 2,
			'source' => 'website',
		];

        
        $request_params['user_id'] = [43951];
		if ( $doctor_id > 0 ) {
            $request_params['user_id'][] = $doctor_id;
            $request_params['responsible_id'] = $doctor_id;
		}

		if ( $clinic_id > 0 ) {
			$request_params['clinic_id'] = $clinic_id;
		}

        $request_params['user_id'] = implode(',',$request_params['user_id']);

		$return = $this->api_client->request( 'createTask', $request_params );

		return $return;
	}
}


