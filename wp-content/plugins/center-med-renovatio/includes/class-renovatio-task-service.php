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
		$work_main       = isset( $payload['work_main'] ) ? sanitize_textarea_field( (string) $payload['work_main'] ) : '';
		$many_work_main  = isset( $payload['many_work_main'] ) ? sanitize_textarea_field( (string) $payload['many_work_main'] ) : '';
		$experience_psi  = isset( $payload['experience_psi'] ) ? sanitize_text_field( (string) $payload['experience_psi'] ) : '';
		$self_harm       = isset( $payload['self_harm'] ) ? sanitize_text_field( (string) $payload['self_harm'] ) : '';
		$self_harm_intensity = isset( $payload['self_harm_intensity'] ) ? absint( $payload['self_harm_intensity'] ) : 0;
		$visit_psi       = isset( $payload['visit_psi'] ) ? sanitize_text_field( (string) $payload['visit_psi'] ) : '';
		$visit_psi_specialist_id = isset( $payload['visit_psi_specialist_id'] ) ? absint( $payload['visit_psi_specialist_id'] ) : 0;
		$visit_psi_specialist_name = '';

		if ( $visit_psi_specialist_id > 0 ) {
			$visit_psi_specialist_name = get_the_title( $visit_psi_specialist_id );
			$visit_psi_specialist_name = is_string( $visit_psi_specialist_name ) ? sanitize_text_field( $visit_psi_specialist_name ) : '';
		}

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

		if ( 'many' === $form_type ) {
			$desc_lines[] = sprintf( 'Запрос пары: %s', $many_work_main !== '' ? $many_work_main : '-' );
		} else {
			$experience_labels = [
				'meds'   => 'Да, сейчас принимаю препараты',
				'noMeds' => 'Да, без медикаментозного лечения',
				'past'   => 'Да, в прошлом',
				'none'   => 'Нет',
				'other'  => 'Другое',
			];
			$self_harm_labels = [
				'yes' => 'Да',
				'no'  => 'Нет',
			];
			$visit_psi_labels = [
				'yesKnow'   => 'Да, и помню специалиста',
				'yesDonKnow'=> 'Да, но не помню специалиста',
				'no'        => 'Нет',
			];

			$desc_lines[] = sprintf( 'Запрос клиента: %s', $work_main !== '' ? $work_main : '-' );
			$desc_lines[] = sprintf(
				'Опыт обращения к психиатру: %s',
				isset( $experience_labels[ $experience_psi ] ) ? $experience_labels[ $experience_psi ] : '-'
			);
			$desc_lines[] = sprintf(
				'Мысли о самоповреждении/нежелании жить: %s',
				isset( $self_harm_labels[ $self_harm ] ) ? $self_harm_labels[ $self_harm ] : '-'
			);
			$desc_lines[] = sprintf(
				'Выраженность таких мыслей: %s',
				( 'yes' === $self_harm && $self_harm_intensity > 0 ) ? (string) $self_harm_intensity : '-'
			);
			$desc_lines[] = sprintf(
				'Близкие посещают психологов Центра: %s',
				isset( $visit_psi_labels[ $visit_psi ] ) ? $visit_psi_labels[ $visit_psi ] : '-'
			);
			$desc_lines[] = sprintf(
				'Специалист, которого посещают близкие: %s',
				$visit_psi_specialist_name !== '' ? $visit_psi_specialist_name : '-'
			);
		}

		$desc_lines[] = 'Специалист: ' . $specialist_name;

		$request_params = [
			'title'  => $title,
			'desc'   => implode( '<br />', $desc_lines ),
			'type'   => 2,
			'source' => 'website',
		];

        
		//Администраторы
        $request_params['user_id'] = [50472,50473];
        
		//Доп пользователь
        $request_params['user_id'][] = 43951;

		if ( $doctor_id > 0 ) {
            $request_params['responsible_id'] = $doctor_id;
		}

		if ( $clinic_id > 0 ) {
			$request_params['clinic_id'] = $clinic_id;
		}

        $request_params['user_id'] = implode(',',$request_params['user_id']);

		$return = $this->api_client->request( 'createTask', $request_params );
		error_log( print_r( $return, true ) );
		error_log( print_r( $request_params, true ) );
		return $return;
	}
}


