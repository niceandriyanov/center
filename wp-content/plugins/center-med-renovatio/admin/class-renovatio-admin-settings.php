<?php
/**
 * Страница настроек плагина Center Med — Renovatio
 *
 * @package Center_Med_Renovatio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Renovatio_Admin_Settings
 */
class Renovatio_Admin_Settings {

	const OPTION_GROUP = 'center_med_renovatio_settings';
	const OPTION_NAME  = 'center_med_renovatio_settings';

	/**
	 * Регистрация пункта меню и страницы настроек.
	 */
	public static function register() {
		add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Добавить пункт меню.
	 */
	public static function add_menu() {
		add_options_page(
			__( 'Renovatio МИС', 'center-med-renovatio' ),
			__( 'Renovatio МИС', 'center-med-renovatio' ),
			'manage_options',
			'center-med-renovatio',
			[ __CLASS__, 'render_page' ]
		);

		add_management_page(
			__( 'Renovatio: визиты', 'center-med-renovatio' ),
			__( 'Renovatio: визиты', 'center-med-renovatio' ),
			'manage_options',
			'center-med-renovatio-bookings',
			[ __CLASS__, 'render_bookings_page' ]
		);
	}

	/**
	 * Зарегистрировать настройки и поля.
	 */
	public static function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			[
				'type'              => 'array',
				'sanitize_callback' => [ __CLASS__, 'sanitize_settings' ],
			]
		);

		add_settings_section(
			'renovatio_api_section',
			__( 'Подключение к API МИС Renovatio', 'center-med-renovatio' ),
			[ __CLASS__, 'render_section_api' ],
			'center-med-renovatio',
			[ 'before_section' => '<div class="center-med-renovatio-settings">' ]
		);

		add_settings_field(
			'api_key',
			__( 'API ключ', 'center-med-renovatio' ),
			[ __CLASS__, 'render_field_api_key' ],
			'center-med-renovatio',
			'renovatio_api_section',
			[ 'label_for' => 'center_med_renovatio_api_key' ]
		);

		add_settings_field(
			'api_base_url',
			__( 'Базовый URL API', 'center-med-renovatio' ),
			[ __CLASS__, 'render_field_api_base_url' ],
			'center-med-renovatio',
			'renovatio_api_section',
			[ 'label_for' => 'center_med_renovatio_api_base_url' ]
		);

		add_settings_field(
			'api_timeout',
			__( 'Таймаут запроса (сек)', 'center-med-renovatio' ),
			[ __CLASS__, 'render_field_api_timeout' ],
			'center-med-renovatio',
			'renovatio_api_section',
			[ 'label_for' => 'center_med_renovatio_api_timeout' ]
		);

		add_settings_section(
			'renovatio_clinic_section',
			__( 'Рабочая клиника', 'center-med-renovatio' ),
			[ __CLASS__, 'render_section_clinic' ],
			'center-med-renovatio'
		);

		add_settings_field(
			'clinic_id',
			__( 'Клиника для записи', 'center-med-renovatio' ),
			[ __CLASS__, 'render_field_clinic' ],
			'center-med-renovatio',
			'renovatio_clinic_section',
			[ 'label_for' => 'center_med_renovatio_clinic_id' ]
		);

		add_settings_section(
			'renovatio_booking_section',
			__( 'Логика записи и оплаты', 'center-med-renovatio' ),
			[ __CLASS__, 'render_section_booking' ],
			'center-med-renovatio'
		);

		add_settings_field(
			'reservation_ttl_minutes',
			__( 'Время ожидания оплаты', 'center-med-renovatio' ),
			[ __CLASS__, 'render_field_reservation_ttl' ],
			'center-med-renovatio',
			'renovatio_booking_section',
			[ 'label_for' => 'center_med_renovatio_reservation_ttl_minutes' ]
		);

		add_settings_field(
			'notify_admin_email',
			__( 'Email администратора', 'center-med-renovatio' ),
			[ __CLASS__, 'render_field_notify_admin_email' ],
			'center-med-renovatio',
			'renovatio_booking_section',
			[ 'label_for' => 'center_med_renovatio_notify_admin_email' ]
		);

		add_settings_field(
			'notify_patient_email',
			__( 'Уведомления пользователю', 'center-med-renovatio' ),
			[ __CLASS__, 'render_field_notify_patient_email' ],
			'center-med-renovatio',
			'renovatio_booking_section',
			[ 'label_for' => 'center_med_renovatio_notify_patient_email' ]
		);

		add_settings_field(
			'cancel_reason_unpaid',
			__( 'Причина авто-отмены', 'center-med-renovatio' ),
			[ __CLASS__, 'render_field_cancel_reason_unpaid' ],
			'center-med-renovatio',
			'renovatio_booking_section',
			[ 'label_for' => 'center_med_renovatio_cancel_reason_unpaid' ]
		);
	}

	/**
	 * Санитизация настроек.
	 *
	 * @param array $input Введённые значения.
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$out = [
			'api_key'                 => '',
			'api_base_url'            => 'https://app.rnova.org/api/public',
			'api_timeout'             => 15,
			'clinic_id'               => 0,
			'reservation_ttl_minutes' => 15,
			'notify_admin_email'      => get_option( 'admin_email' ),
			'notify_patient_email'    => 1,
			'cancel_reason_unpaid'    => 'Оплата не прошла',
		];

		if ( ! is_array( $input ) ) {
			return $out;
		}

		if ( isset( $input['api_key'] ) && is_string( $input['api_key'] ) ) {
			$out['api_key'] = sanitize_text_field( trim( $input['api_key'] ) );
		}

		if ( isset( $input['api_base_url'] ) && is_string( $input['api_base_url'] ) ) {
			$url = esc_url_raw( trim( $input['api_base_url'] ), [ 'https' ] );
			$out['api_base_url'] = $url ? $url : $out['api_base_url'];
		}

		if ( isset( $input['api_timeout'] ) ) {
			$t = (int) $input['api_timeout'];
			$out['api_timeout'] = $t >= 5 && $t <= 60 ? $t : 15;
		}

		if ( array_key_exists( 'clinic_id', $input ) ) {
			$out['clinic_id'] = absint( $input['clinic_id'] );
		}

		if ( isset( $input['reservation_ttl_minutes'] ) ) {
			$ttl = (int) $input['reservation_ttl_minutes'];
			$out['reservation_ttl_minutes'] = in_array( $ttl, [ 15, 30, 60 ], true ) ? $ttl : 15;
		}

		if ( isset( $input['notify_admin_email'] ) ) {
			$email = sanitize_email( (string) $input['notify_admin_email'] );
			$out['notify_admin_email'] = $email ? $email : get_option( 'admin_email' );
		}

		$out['notify_patient_email'] = ! empty( $input['notify_patient_email'] ) ? 1 : 0;

		if ( isset( $input['cancel_reason_unpaid'] ) ) {
			$reason = sanitize_text_field( (string) $input['cancel_reason_unpaid'] );
			$out['cancel_reason_unpaid'] = $reason !== '' ? $reason : 'Оплата не прошла';
		}

		return $out;
	}

	/**
	 * Описание секции API.
	 */
	public static function render_section_api() {
		echo '<p class="description">';
		esc_html_e( 'Укажите данные для доступа к API МИС Renovatio. API ключ можно получить в настройках МИС.', 'center-med-renovatio' );
		echo '</p>';
	}

	/**
	 * Поле: API ключ.
	 */
	public static function render_field_api_key() {
		$opts = self::get_options();
		$val  = isset( $opts['api_key'] ) ? $opts['api_key'] : '';
		?>
		<input type="text"
			id="center_med_renovatio_api_key"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[api_key]"
			value="<?php echo esc_attr( $val ); ?>"
			class="regular-text"
			autocomplete="off"
		/>
		<p class="description"><?php esc_html_e( 'Обязательный параметр для всех запросов к API.', 'center-med-renovatio' ); ?></p>
		<?php
	}

	/**
	 * Поле: базовый URL API.
	 */
	public static function render_field_api_base_url() {
		$opts = self::get_options();
		$val  = isset( $opts['api_base_url'] ) ? $opts['api_base_url'] : 'https://app.rnova.org/api/public';
		?>
		<input type="url"
			id="center_med_renovatio_api_base_url"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[api_base_url]"
			value="<?php echo esc_attr( $val ); ?>"
			class="large-text code"
		/>
		<p class="description"><?php esc_html_e( 'По умолчанию: https://app.rnova.org/api/public (без слеша в конце). Для версионированного API добавьте версию в путь.', 'center-med-renovatio' ); ?></p>
		<?php
	}

	/**
	 * Поле: таймаут.
	 */
	public static function render_field_api_timeout() {
		$opts = self::get_options();
		$val  = isset( $opts['api_timeout'] ) ? (int) $opts['api_timeout'] : 15;
		?>
		<input type="number"
			id="center_med_renovatio_api_timeout"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[api_timeout]"
			value="<?php echo esc_attr( $val ); ?>"
			min="5"
			max="60"
			step="1"
			class="small-text"
		/>
		<p class="description"><?php esc_html_e( 'От 5 до 60 секунд.', 'center-med-renovatio' ); ?></p>
		<?php
	}

	/**
	 * Описание секции «Рабочая клиника».
	 */
	public static function render_section_clinic() {
		$opts = self::get_options();
		if ( empty( $opts['api_key'] ) ) {
			echo '<p class="description">';
			esc_html_e( 'Сначала укажите и сохраните API ключ выше — затем здесь появится список клиник из МИС.', 'center-med-renovatio' );
			echo '</p>';
			return;
		}
		echo '<p class="description">';
		esc_html_e( 'Выберите клинику, с которой работает сайт (запись на приём, расписание и т.д.). В МИС может быть несколько клиник.', 'center-med-renovatio' );
		echo '</p>';
	}

	/**
	 * Поле: выбор клиники (список из API getClinics).
	 */
	public static function render_field_clinic() {
		$opts     = self::get_options();
		$saved_id = isset( $opts['clinic_id'] ) ? (int) $opts['clinic_id'] : 0;

		if ( empty( $opts['api_key'] ) ) {
			echo '<input type="hidden" name="' . esc_attr( self::OPTION_NAME ) . '[clinic_id]" value="' . esc_attr( $saved_id ) . '" />';
			echo '<p class="description">' . esc_html__( 'Сохраните API ключ и обновите страницу, чтобы загрузить список клиник.', 'center-med-renovatio' ) . '</p>';
			return;
		}

		$client = center_med_renovatio_api_client();
		$list   = $client->request( 'getClinics', [] );

		if ( is_wp_error( $list ) ) {
			echo '<input type="hidden" name="' . esc_attr( self::OPTION_NAME ) . '[clinic_id]" value="' . esc_attr( $saved_id ) . '" />';
			echo '<p class="description notice notice-warning inline">';
			echo esc_html__( 'Не удалось загрузить список клиник. Проверьте подключение и обновите страницу.', 'center-med-renovatio' );
			echo ' ' . esc_html( $list->get_error_message() );
			echo '</p>';
			return;
		}

		if ( ! is_array( $list ) ) {
			$list = [];
		}

		?>
		<select id="center_med_renovatio_clinic_id"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[clinic_id]"
			class="regular-text"
		>
			<option value="0"><?php esc_html_e( '— Выберите клинику —', 'center-med-renovatio' ); ?></option>
			<?php
			foreach ( $list as $clinic ) {
				$id    = isset( $clinic['id'] ) ? (int) $clinic['id'] : 0;
				$title = isset( $clinic['title'] ) ? $clinic['title'] : (string) $id;
				if ( $id <= 0 ) {
					continue;
				}
				?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $saved_id, $id ); ?>><?php echo esc_html( $title ); ?></option>
				<?php
			}
			?>
		</select>
		<p class="description"><?php esc_html_e( 'Сохраните настройки после выбора. Эта клиника будет использоваться для записи на приём.', 'center-med-renovatio' ); ?></p>
		<?php
	}

	/**
	 * Описание секции логики записи/оплаты.
	 */
	public static function render_section_booking() {
		echo '<p class="description">';
		esc_html_e( 'Настройки для жизненного цикла записи: ожидание оплаты, уведомления и причина авто-отмены неоплаченных броней.', 'center-med-renovatio' );
		echo '</p>';
	}

	/**
	 * Поле: TTL ожидания оплаты.
	 */
	public static function render_field_reservation_ttl() {
		$opts = self::get_options();
		$val  = isset( $opts['reservation_ttl_minutes'] ) ? (int) $opts['reservation_ttl_minutes'] : 15;
		?>
		<select
			id="center_med_renovatio_reservation_ttl_minutes"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[reservation_ttl_minutes]"
		>
			<option value="15" <?php selected( $val, 15 ); ?>><?php esc_html_e( '15 минут', 'center-med-renovatio' ); ?></option>
			<option value="30" <?php selected( $val, 30 ); ?>><?php esc_html_e( '30 минут', 'center-med-renovatio' ); ?></option>
			<option value="60" <?php selected( $val, 60 ); ?>><?php esc_html_e( '1 час', 'center-med-renovatio' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Через это время неоплаченная запись может быть автоматически отменена в МИС.', 'center-med-renovatio' ); ?></p>
		<?php
	}

	/**
	 * Поле: email администратора.
	 */
	public static function render_field_notify_admin_email() {
		$opts = self::get_options();
		$val  = isset( $opts['notify_admin_email'] ) ? $opts['notify_admin_email'] : get_option( 'admin_email' );
		?>
		<input
			type="email"
			id="center_med_renovatio_notify_admin_email"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[notify_admin_email]"
			value="<?php echo esc_attr( $val ); ?>"
			class="regular-text"
		/>
		<p class="description"><?php esc_html_e( 'На этот адрес будут отправляться технические уведомления по записям и оплатам.', 'center-med-renovatio' ); ?></p>
		<?php
	}

	/**
	 * Поле: отправлять email пользователю.
	 */
	public static function render_field_notify_patient_email() {
		$opts = self::get_options();
		$val  = ! empty( $opts['notify_patient_email'] );
		?>
		<label for="center_med_renovatio_notify_patient_email">
			<input
				type="checkbox"
				id="center_med_renovatio_notify_patient_email"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[notify_patient_email]"
				value="1"
				<?php checked( $val ); ?>
			/>
			<?php esc_html_e( 'Отправлять email пользователю о результате записи/оплаты', 'center-med-renovatio' ); ?>
		</label>
		<?php
	}

	/**
	 * Поле: причина авто-отмены при неоплате.
	 */
	public static function render_field_cancel_reason_unpaid() {
		$opts = self::get_options();
		$val  = isset( $opts['cancel_reason_unpaid'] ) ? $opts['cancel_reason_unpaid'] : 'Оплата не прошла';
		?>
		<input
			type="text"
			id="center_med_renovatio_cancel_reason_unpaid"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[cancel_reason_unpaid]"
			value="<?php echo esc_attr( $val ); ?>"
			class="regular-text"
			maxlength="255"
		/>
		<p class="description"><?php esc_html_e( 'Комментарий, который будет передаваться при авто-отмене визита из-за неоплаты.', 'center-med-renovatio' ); ?></p>
		<?php
	}

	/**
	 * Вывод страницы настроек.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$connection_status = self::render_connection_status();
		?>
		<div class="wrap center-med-renovatio-settings-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php echo $connection_status; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<form action="options.php" method="post" id="center-med-renovatio-settings-form">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( 'center-med-renovatio' );
				submit_button( __( 'Сохранить настройки', 'center-med-renovatio' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Техническая страница: статус визитов/оплат.
	 */
	public static function render_bookings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$tables = Renovatio_Db_Schema::get_table_names();
		$table  = $tables['bookings'];
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Renovatio: визиты и оплаты', 'center-med-renovatio' ) . '</h1>';

		if ( $exists !== $table ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Таблицы плагина не найдены. Деактивируйте и активируйте плагин, чтобы выполнить установку схемы БД.', 'center-med-renovatio' ) . '</p></div>';
			echo '</div>';
			return;
		}

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows  = $wpdb->get_results( "SELECT status, COUNT(*) AS cnt FROM {$table} GROUP BY status ORDER BY cnt DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		echo '<p>' . esc_html__( 'Всего записей:', 'center-med-renovatio' ) . ' <strong>' . esc_html( (string) $total ) . '</strong></p>';
		echo '<table class="widefat striped" style="max-width:520px"><thead><tr><th>' . esc_html__( 'Статус', 'center-med-renovatio' ) . '</th><th>' . esc_html__( 'Количество', 'center-med-renovatio' ) . '</th></tr></thead><tbody>';
		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				echo '<tr><td>' . esc_html( $row['status'] ) . '</td><td>' . esc_html( (string) $row['cnt'] ) . '</td></tr>';
			}
		} else {
			echo '<tr><td colspan="2">' . esc_html__( 'Пока нет данных.', 'center-med-renovatio' ) . '</td></tr>';
		}
		echo '</tbody></table>';
		echo '<p class="description">' . esc_html__( 'Детализированный журнал появится на следующем этапе разработки.', 'center-med-renovatio' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Блок проверки соединения с API.
	 *
	 * @return string HTML.
	 */
	private static function render_connection_status() {
		$opts = self::get_options();
		if ( empty( $opts['api_key'] ) ) {
			return '<p class="notice notice-info inline"><span class="dashicons dashicons-info"></span> '
				. esc_html__( 'Укажите API ключ и сохраните настройки, чтобы проверить подключение.', 'center-med-renovatio' )
				. '</p>';
		}

		$client = center_med_renovatio_api_client();
		$result = $client->request( 'getClinics', [] );

		if ( is_wp_error( $result ) ) {
			return '<div class="notice notice-error inline"><p><span class="dashicons dashicons-warning"></span> '
				. esc_html__( 'Ошибка подключения к API:', 'center-med-renovatio' ) . ' '
				. esc_html( $result->get_error_message() )
				. ' <code>' . esc_html( $result->get_error_code() ) . '</code></p></div>';
		}

		$count = is_array( $result ) ? count( $result ) : 0;
		return '<div class="notice notice-success inline"><p><span class="dashicons dashicons-yes-alt"></span> '
			. esc_html__( 'Подключение успешно. Получено клиник:', 'center-med-renovatio' ) . ' ' . (int) $count
			. '</p></div>';
	}

	/**
	 * Подключить стили/скрипты только на странице настроек.
	 *
	 * @param string $hook_suffix Текущий экран.
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( $hook_suffix !== 'settings_page_center-med-renovatio' ) {
			return;
		}
		// При необходимости добавить CSS/JS для страницы настроек.
	}

	/**
	 * Получить сохранённые настройки.
	 *
	 * @return array
	 */
	public static function get_options() {
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
		$opts = get_option( self::OPTION_NAME, [] );
		return is_array( $opts ) ? array_merge( $defaults, $opts ) : $defaults;
	}
}
