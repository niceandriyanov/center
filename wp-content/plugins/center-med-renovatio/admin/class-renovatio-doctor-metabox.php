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

		$saved_doctor_id = (int) get_post_meta( $post->ID, self::META_KEY_DOCTOR_ID, true );
		$api_key         = (string) center_med_renovatio_get_setting( 'api_key', '' );
		$clinic_id       = (int) center_med_renovatio_get_setting( 'clinic_id', 0 );

		if ( $api_key === '' ) {
			echo '<p>' . esc_html__( 'Сначала укажите API ключ в настройках плагина Renovatio.', 'center-med-renovatio' ) . '</p>';
			self::render_hidden_field( $saved_doctor_id );
			return;
		}

		if ( $clinic_id <= 0 ) {
			echo '<p>' . esc_html__( 'Сначала выберите рабочую клинику в настройках плагина Renovatio.', 'center-med-renovatio' ) . '</p>';
			self::render_hidden_field( $saved_doctor_id );
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
			self::render_hidden_field( $saved_doctor_id );
			return;
		}

		if ( ! is_array( $users ) || empty( $users ) ) {
			echo '<p>' . esc_html__( 'В выбранной клинике не найдено врачей.', 'center-med-renovatio' ) . '</p>';
			self::render_hidden_field( $saved_doctor_id );
			return;
		}

		echo '<label for="cmr-renovatio-doctor-id" style="display:block;margin-bottom:6px;">'
			. esc_html__( 'Врач в МИС', 'center-med-renovatio' )
			. '</label>';
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
	}

	/**
	 * При ошибках API поле для сохранения текущего значения врача.
	 *
	 * @param int $value Значение ID врача.
	 * @return void
	 */
	private static function render_hidden_field( $value ) {
		echo '<input type="hidden" name="cmr_renovatio_doctor_id" value="' . esc_attr( (string) (int) $value ) . '" />';
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
			"jQuery(function($){var \$el=$('.cmr-select2-doctor');if(\$el.length&&typeof \$el.select2==='function'){\$el.select2({width:'100%',placeholder:'— Не выбрано —',allowClear:true});}});"
		);
	}
}
