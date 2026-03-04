<?php
/**
 * Metabox связи врача сайта с врачом МИС Renovatio.
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Doctor_Metabox
 */
class Renovatio_Doctor_Metabox {

	/**
	 * Post type
	 */
	const POST_TYPE = 'doctors';

	/**
	 * Meta key для ID врача в МИС.
	 */
	const META_KEY_DOCTOR_ID = '_cmr_renovatio_doctor_id';

	/**
	 * Meta keys услуг врача в МИС.
	 */
	const META_KEY_SERVICE_PERSONAL = '_cmr_doctor_service_personal';
	const META_KEY_SERVICE_PAIR     = '_cmr_doctor_service_pair';
	const META_KEY_STEP_PERSONAL    = '_cmr_doctor_step_personal';
	const META_KEY_STEP_PAIR        = '_cmr_doctor_step_pair';
	const EXCLUDED_SERVICE_IDS      = [ 3014011, 3014010 ];

	/**
	 * Nonce action.
	 */
	const NONCE_ACTION = 'cmr_renovatio_doctor_metabox_save';

	/**
	 * Nonce field name.
	 */
	const NONCE_NAME = 'cmr_renovatio_doctor_metabox_nonce';

	/**
	 * Регистрация хуков.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_metabox' ] );
		add_action( 'save_post_' . self::POST_TYPE, [ __CLASS__, 'save_metabox' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
		add_action( 'wp_ajax_cmr_renovatio_get_doctor_services', [ __CLASS__, 'ajax_get_doctor_services' ] );
	}

	/**
	 * Добавить metabox к post_type=doctors.
	 *
	 * @return void
	 */
	public static function add_metabox() {
		add_meta_box(
			'cmr-renovatio-doctor-link',
			__( 'Связь с врачом МИС Renovatio', 'center-med-renovatio' ),
			[ __CLASS__, 'render_metabox' ],
			self::POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Html metabox.
	 *
	 * @param WP_Post $post Пост врача сайта.
	 * @return void
	 */
	public static function render_metabox( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		echo '<div id="cmr-renovatio-doctor-metabox-inner" style="position:relative;">';

		$saved_doctor_id = (int) get_post_meta( $post->ID, self::META_KEY_DOCTOR_ID, true );
		$saved_service_personal = (int) get_post_meta( $post->ID, self::META_KEY_SERVICE_PERSONAL, true );
		$saved_service_pair     = (int) get_post_meta( $post->ID, self::META_KEY_SERVICE_PAIR, true );
		$saved_step_personal    = (int) get_post_meta( $post->ID, self::META_KEY_STEP_PERSONAL, true );
		$saved_step_pair        = (int) get_post_meta( $post->ID, self::META_KEY_STEP_PAIR, true );

		$api_key         = (string) center_med_renovatio_get_setting( 'api_key', '' );
		$clinic_id       = (int) center_med_renovatio_get_setting( 'clinic_id', 0 );

		if ( $api_key === '' ) {
			echo '<p>' . esc_html__( 'Сначала укажите API ключ в настройках плагина Renovatio.', 'center-med-renovatio' ) . '</p>';
			self::render_hidden_field( $saved_doctor_id, 'cmr_renovatio_doctor_id' );
			self::render_hidden_field( $saved_service_personal, 'cmr_doctor_service_personal' );
			self::render_hidden_field( $saved_service_pair, 'cmr_doctor_service_pair' );
			self::render_hidden_field( $saved_step_personal, 'cmr_doctor_step_personal' );
			self::render_hidden_field( $saved_step_pair, 'cmr_doctor_step_pair' );
			echo '</div>';
			return;
		}

		if ( $clinic_id <= 0 ) {
			echo '<p>' . esc_html__( 'Сначала выберите рабочую клинику в настройках плагина Renovatio.', 'center-med-renovatio' ) . '</p>';
			self::render_hidden_field( $saved_doctor_id, 'cmr_renovatio_doctor_id' );
			self::render_hidden_field( $saved_service_personal, 'cmr_doctor_service_personal' );
			self::render_hidden_field( $saved_service_pair, 'cmr_doctor_service_pair' );
			self::render_hidden_field( $saved_step_personal, 'cmr_doctor_step_personal' );
			self::render_hidden_field( $saved_step_pair, 'cmr_doctor_step_pair' );
			echo '</div>';
			return;
		}

		$params = [
			'clinic_id' => $clinic_id,
			'show_all' 	=> true,
		];

		$client = center_med_renovatio_api_client();
		$users  = $client->request( 'getUsers', $params );

		if ( is_wp_error( $users ) ) {
			echo '<p style="color:#b32d2e;">' . esc_html__( 'Ошибка загрузки врачей из API:', 'center-med-renovatio' ) . ' ' . esc_html( $users->get_error_message() ) . '</p>';
			self::render_hidden_field( $saved_doctor_id, 'cmr_renovatio_doctor_id' );
			self::render_hidden_field( $saved_service_personal, 'cmr_doctor_service_personal' );
			self::render_hidden_field( $saved_service_pair, 'cmr_doctor_service_pair' );
			self::render_hidden_field( $saved_step_personal, 'cmr_doctor_step_personal' );
			self::render_hidden_field( $saved_step_pair, 'cmr_doctor_step_pair' );
			echo '</div>';
			return;
		}

		if ( ! is_array( $users ) || empty( $users ) ) {
			echo '<p>' . esc_html__( 'В выбранной клинике не найдено врачей.', 'center-med-renovatio' ) . '</p>';
			self::render_hidden_field( $saved_doctor_id, 'cmr_renovatio_doctor_id' );
			self::render_hidden_field( $saved_service_personal, 'cmr_doctor_service_personal' );
			self::render_hidden_field( $saved_service_pair, 'cmr_doctor_service_pair' );
			self::render_hidden_field( $saved_step_personal, 'cmr_doctor_step_personal' );
			self::render_hidden_field( $saved_step_pair, 'cmr_doctor_step_pair' );
			echo '</div>';
			return;
		}

		echo '<label for="cmr-renovatio-doctor-id" style="display:block;margin-bottom:6px;">'
			. esc_html__( 'Врач в МИС', 'center-med-renovatio' )
			. '</label><br>';
		echo '<select id="cmr-renovatio-doctor-id" class="cmr-select2-doctor" name="cmr_renovatio_doctor_id" style="width:100%;">';
		echo '<option value="0">' . esc_html__( '— Не выбрано —', 'center-med-renovatio' ) . '</option>';
		foreach ( $users as $user ) {
			if ( ! is_array( $user ) ) {
				continue;
			}

			$user_id = isset( $user['id'] ) ? (int) $user['id'] : 0;
			if ( $user_id <= 0 ) {
				continue;
			}

			$name       = isset( $user['name'] ) ? (string) $user['name'] : (string) $user_id;
			$profession = isset( $user['profession_titles'] ) ? (string) $user['profession_titles'] : '';
			$label      = $profession !== '' ? sprintf( '%s (%s)', $name, $profession ) : $name;

			echo '<option value="' . esc_attr( (string) $user_id ) . '" ' . selected( $saved_doctor_id, $user_id, false ) . '>'
				. esc_html( $label )
				. '</option>';
		}

		echo '</select>';
		echo '<p style="margin-top:8px;color:#50575e;">'
			. esc_html__( 'Сохраняется ID врача из МИС. Используется для записи и получения расписания.', 'center-med-renovatio' )
			. '</p>';

		echo '<br>';
		echo '<label for="cmr-renovatio-doctor-step-personal" style="display:block;margin-bottom:6px;">'
			. esc_html__( 'Шаг персонального приёма (мин)', 'center-med-renovatio' )
			. '</label>';
		echo '<input type="number" id="cmr-renovatio-doctor-step-personal" class="cmr-input-number" name="cmr_doctor_step_personal" value="' . esc_attr( (string) $saved_step_personal ) . '" min="10" step="5" />';

		echo '<br><br>';
		echo '<label for="cmr-renovatio-doctor-step-pair" style="display:block;margin-bottom:6px;">'
			. esc_html__( 'Шаг парного приёма (мин)', 'center-med-renovatio' )
			. '</label>';
		echo '<input type="number" id="cmr-renovatio-doctor-step-pair" class="cmr-input-number" name="cmr_doctor_step_pair" value="' . esc_attr( (string) $saved_step_pair ) . '" min="10" step="5" />';

		$services = [];
		if ( $saved_doctor_id > 0 ) {
			$services = self::get_doctor_services( $saved_doctor_id, $clinic_id );
		}

		echo '<br>';
		echo '<label for="cmr-renovatio-service-personal" style="display:block;margin-bottom:6px;">'
			. esc_html__( 'Услуга консультации (индивидуальная)', 'center-med-renovatio' )
			. '</label>';
		echo '<select id="cmr-renovatio-service-personal" class="cmr-select2-service" name="cmr_doctor_service_personal" style="width:100%;">';
		echo '<option value="0">' . esc_html__( '— Не выбрано —', 'center-med-renovatio' ) . '</option>';
		self::render_service_options( $services, $saved_service_personal );
		echo '</select>';

		echo '<br><br>';
		echo '<label for="cmr-renovatio-service-pair" style="display:block;margin-bottom:6px;">'
			. esc_html__( 'Услуга консультации (для пары)', 'center-med-renovatio' )
			. '</label>';
		echo '<select id="cmr-renovatio-service-pair" class="cmr-select2-service" name="cmr_doctor_service_pair" style="width:100%;">';
		echo '<option value="0">' . esc_html__( '— Не выбрано —', 'center-med-renovatio' ) . '</option>';
		self::render_service_options( $services, $saved_service_pair );
		echo '</select>';
		echo '<div id="cmr-renovatio-metabox-loader" style="display:none;position:absolute;inset:0;background:rgba(255,255,255,.75);z-index:20;align-items:center;justify-content:center;">'
			. '<span class="spinner is-active" style="float:none;margin:0;"></span>'
			. '</div>';
		echo '</div>';
	}

	/**
	 * Save metabox.
	 *
	 * @param int $post_id ID поста.
	 * @return void
	 */
	public static function save_metabox( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$doctor_id = isset( $_POST['cmr_renovatio_doctor_id'] ) ? absint( wp_unslash( $_POST['cmr_renovatio_doctor_id'] ) ) : 0;
		if ( $doctor_id > 0 ) {
			update_post_meta( $post_id, self::META_KEY_DOCTOR_ID, $doctor_id );
		} else {
			delete_post_meta( $post_id, self::META_KEY_DOCTOR_ID );
		}

		$service_personal = isset( $_POST['cmr_doctor_service_personal'] ) ? absint( wp_unslash( $_POST['cmr_doctor_service_personal'] ) ) : 0;
		if ( $service_personal > 0 ) {
			update_post_meta( $post_id, self::META_KEY_SERVICE_PERSONAL, $service_personal );
		} else {
			delete_post_meta( $post_id, self::META_KEY_SERVICE_PERSONAL );
		}

		$service_pair = isset( $_POST['cmr_doctor_service_pair'] ) ? absint( wp_unslash( $_POST['cmr_doctor_service_pair'] ) ) : 0;
		if ( $service_pair > 0 ) {
			update_post_meta( $post_id, self::META_KEY_SERVICE_PAIR, $service_pair );
		} else {
			delete_post_meta( $post_id, self::META_KEY_SERVICE_PAIR );
		}

		$step_personal = isset( $_POST['cmr_doctor_step_personal'] ) ? absint( wp_unslash( $_POST['cmr_doctor_step_personal'] ) ) : 0;
		if ( $step_personal > 0 ) {
			update_post_meta( $post_id, self::META_KEY_STEP_PERSONAL, $step_personal );
		} else {
			delete_post_meta( $post_id, self::META_KEY_STEP_PERSONAL );
		}

		$step_pair = isset( $_POST['cmr_doctor_step_pair'] ) ? absint( wp_unslash( $_POST['cmr_doctor_step_pair'] ) ) : 0;
		if ( $step_pair > 0 ) {
			update_post_meta( $post_id, self::META_KEY_STEP_PAIR, $step_pair );
		} else {
			delete_post_meta( $post_id, self::META_KEY_STEP_PAIR );
		}
	}

	/**
	 * При ошибках API поле для сохранения текущего значения врача.
	 *
	 * @param int $value Значение ID врача.
	 * @return void
	 */
	private static function render_hidden_field( $value, $name ) {
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) (int) $value ) . '" />';
	}

	/**
	 * Отрисовать option для списка услуг.
	 *
	 * @param array $services Список услуг.
	 * @param int   $saved_id Сохраненный ID.
	 * @return void
	 */
	private static function render_service_options( array $services, $saved_id ) {
		foreach ( $services as $service ) {
			$service_id = isset( $service['service_id'] ) ? (int) $service['service_id'] : 0;
			if ( $service_id <= 0 ) {
				continue;
			}
			if ( in_array( $service_id, self::EXCLUDED_SERVICE_IDS, true ) ) {
				continue;
			}
			$title = isset( $service['title'] ) ? (string) $service['title'] : (string) $service_id;
			$price = isset( $service['price'] ) && is_numeric( $service['price'] ) ? (string) $service['price'] : '';
			$label = '' !== $price ? sprintf( '%s (%s)', $title, $price ) : $title;
			echo '<option value="' . esc_attr( (string) $service_id ) . '" ' . selected( (int) $saved_id, $service_id, false ) . '>'
				. esc_html( $label )
				. '</option>';
		}
	}

	/**
	 * Получить список услуг врача.
	 *
	 * @param int $doctor_id ID врача в МИС.
	 * @param int $clinic_id ID клиники.
	 * @return array
	 */
	private static function get_doctor_services( $doctor_id, $clinic_id ) {
		$doctor_id = absint( $doctor_id );
		$clinic_id = absint( $clinic_id );
		if ( $doctor_id <= 0 || $clinic_id <= 0 ) {
			return [];
		}

		$result = center_med_renovatio_api_client()->request(
			'getServices',
			[
				'user_id'   => $doctor_id,
				'clinic_id' => $clinic_id,
				'show_all'  => 1,
				'limit'     => 500,
			]
		);

		return is_array( $result ) ? $result : [];
	}

	/**
	 * Ajax: список услуг врача для селектов.
	 *
	 * @return void
	 */
	public static function ajax_get_doctor_services() {
		check_ajax_referer( 'cmr_renovatio_doctor_services_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Недостаточно прав.', 'center-med-renovatio' ),
				],
				403
			);
		}

		$doctor_id = isset( $_POST['doctor_id'] ) ? absint( wp_unslash( $_POST['doctor_id'] ) ) : 0;
		$clinic_id = (int) center_med_renovatio_get_setting( 'clinic_id', 0 );
		$services  = self::get_doctor_services( $doctor_id, $clinic_id );

		$out = [];
		foreach ( $services as $service ) {
			$service_id = isset( $service['service_id'] ) ? (int) $service['service_id'] : 0;
			if ( $service_id <= 0 ) {
				continue;
			}
			if ( in_array( $service_id, self::EXCLUDED_SERVICE_IDS, true ) ) {
				continue;
			}
			$title = isset( $service['title'] ) ? (string) $service['title'] : (string) $service_id;
			$price = isset( $service['price'] ) && is_numeric( $service['price'] ) ? (string) $service['price'] : '';
			$label = '' !== $price ? sprintf( '%s (%s)', $title, $price ) : $title;

			$out[] = [
				'id'   => $service_id,
				'text' => $label,
			];
		}

		wp_send_json_success(
			[
				'services' => $out,
			]
		);
	}

	/**
	 * Подключение select2 только на экране doctors.
	 *
	 * @param string $hook_suffix Текущий экран.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== self::POST_TYPE ) {
			return;
		}

		wp_enqueue_style(
			'cmr-select2',
			'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
			[],
			'4.1.0-rc.0'
		);
		wp_enqueue_script(
			'cmr-select2',
			'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
			[ 'jquery' ],
			'4.1.0-rc.0',
			true
		);

		wp_add_inline_script(
			'cmr-select2',
			"jQuery(function($){var ajaxUrl='" . esc_js( admin_url( 'admin-ajax.php' ) ) . "',nonce='" . esc_js( wp_create_nonce( 'cmr_renovatio_doctor_services_nonce' ) ) . "',doctor=\$('.cmr-select2-doctor'),service=\$('.cmr-select2-service'),loader=\$('#cmr-renovatio-metabox-loader');function init(\$el){if(\$el.length&&typeof \$el.select2==='function'){\$el.select2({width:'100%',allowClear:true,placeholder:'— Не выбрано —'});}}function showLoader(){if(loader.length){loader.css('display','flex');}}function hideLoader(){if(loader.length){loader.hide();}}function fill(items){service.each(function(){var \$s=\$(this),keep=\$s.val();\$s.find('option').not(':first').remove();items.forEach(function(it){\$s.append(new Option(it.text,it.id,false,false));});if(keep&&\$s.find('option[value=\"'+keep+'\"]').length){\$s.val(keep);}else{\$s.val('0');}\$s.trigger('change.select2');});}init(doctor);init(service);doctor.on('change',function(){var id=parseInt(\$(this).val()||'0',10);if(!id){fill([]);return;}showLoader();jQuery.post(ajaxUrl,{action:'cmr_renovatio_get_doctor_services',nonce:nonce,doctor_id:id}).done(function(resp){if(resp&&resp.success&&resp.data&&Array.isArray(resp.data.services)){fill(resp.data.services);}else{fill([]);}}).fail(function(){fill([]);}).always(function(){hideLoader();});});});"
		);
	}
}
