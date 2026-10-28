<?php
/**
 * Промокоды онлайн-записи: CPT, проверка, счётчик.
 *
 * @package Clinic_Prime
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT промокода.
 */
function booking_register_promo_post_type() {
	register_post_type(
		'booking_promo',
		array(
			'labels'              => array(
				'name'               => 'Промокоды',
				'singular_name'      => 'Промокод',
				'menu_name'          => 'Промокоды',
				'add_new'            => 'Добавить промокод',
				'add_new_item'       => 'Новый промокод',
				'edit_item'          => 'Редактировать промокод',
				'new_item'           => 'Новый промокод',
				'view_item'          => 'Просмотреть промокод',
				'search_items'       => 'Искать промокоды',
				'not_found'          => 'Промокоды не найдены',
				'not_found_in_trash' => 'В корзине промокодов нет',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 26,
			'menu_icon'           => 'dashicons-tickets-alt',
			'supports'            => array( 'title' ),
			'show_in_rest'        => false,
			'exclude_from_search' => true,
		)
	);
}
add_action( 'init', 'booking_register_promo_post_type' );

/**
 * Метабокс настроек промокода.
 */
function booking_promo_metaboxes() {
	add_meta_box(
		'booking-promo-settings',
		'Условия промокода',
		'booking_promo_render_metabox',
		'booking_promo',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'booking_promo_metaboxes' );

/**
 * Разметка метабокса.
 *
 * @param WP_Post $post Пост промокода.
 * @return void
 */
function booking_promo_render_metabox( $post ) {
	wp_nonce_field( 'booking_promo_save', 'booking_promo_nonce' );

	$code      = booking_promo_meta( $post->ID, 'code' );
	if ( '' === $code ) {
		$code = $post->post_title;
	}
	$type      = booking_promo_meta( $post->ID, 'type' );
	$value     = booking_promo_meta( $post->ID, 'value' );
	$date_from = booking_promo_meta( $post->ID, 'date_from' );
	$date_to   = booking_promo_meta( $post->ID, 'date_to' );
	$limit     = (int) booking_promo_meta( $post->ID, 'limit' );
	$used      = (int) booking_promo_meta( $post->ID, 'used' );

	if ( 'amount' !== $type ) {
		$type = 'percent';
	}
	?>
	<style>
		.booking-promo-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 20px 24px;
			max-width: 760px;
		}
		.booking-promo-field {
			margin: 0;
			min-width: 0;
		}
		.booking-promo-field label {
			display: block;
			font-weight: 600;
			margin: 0 0 6px;
		}
		.booking-promo-field input[type="text"],
		.booking-promo-field input[type="number"],
		.booking-promo-field input[type="date"],
		.booking-promo-field select {
			display: block;
			width: 100%;
			max-width: 100%;
			box-sizing: border-box;
		}
		.booking-promo-field .description {
			display: block;
			margin: 6px 0 0;
			line-height: 1.45;
		}
		.booking-promo-span { grid-column: 1 / -1; }
		.booking-promo-used { margin: 0; font-size: 14px; }
		@media (max-width: 782px) {
			.booking-promo-grid { grid-template-columns: 1fr; }
		}
	</style>
	<div class="booking-promo-grid">
		<div class="booking-promo-field">
			<label for="booking_promo_code">Код</label>
			<input type="text" id="booking_promo_code" name="booking_promo_code" value="<?php echo esc_attr( $code ); ?>" />
			<span class="description">Его вводят в виджете. Регистр не важен.</span>
		</div>
		<div class="booking-promo-field">
			<label for="booking_promo_type">Тип скидки</label>
			<select id="booking_promo_type" name="booking_promo_type">
				<option value="percent" <?php selected( $type, 'percent' ); ?>>Проценты</option>
				<option value="amount" <?php selected( $type, 'amount' ); ?>>Рубли</option>
			</select>
		</div>
		<div class="booking-promo-field">
			<label for="booking_promo_value">Размер скидки</label>
			<input type="number" id="booking_promo_value" name="booking_promo_value" min="1" step="1" value="<?php echo esc_attr( $value ? (string) $value : '' ); ?>" />
			<span class="description">Для процентов — число до 100. Для рублей — сумма скидки.</span>
		</div>
		<div class="booking-promo-field">
			<label for="booking_promo_limit">Лимит активаций</label>
			<input type="number" id="booking_promo_limit" name="booking_promo_limit" min="0" step="1" value="<?php echo esc_attr( (string) $limit ); ?>" />
			<span class="description">0 — без ограничения.</span>
		</div>
		<div class="booking-promo-field">
			<label for="booking_promo_date_from">Действует с</label>
			<input type="date" id="booking_promo_date_from" name="booking_promo_date_from" value="<?php echo esc_attr( $date_from ); ?>" />
		</div>
		<div class="booking-promo-field">
			<label for="booking_promo_date_to">Действует до</label>
			<input type="date" id="booking_promo_date_to" name="booking_promo_date_to" value="<?php echo esc_attr( $date_to ); ?>" />
		</div>
		<div class="booking-promo-field booking-promo-span">
			<label>Использовано</label>
			<p class="booking-promo-used"><?php echo esc_html( (string) $used ); ?></p>
			<span class="description">Счётчик растёт после успешной записи. Чтобы выключить промокод — сохраните его как черновик.</span>
		</div>
	</div>
	<?php
}

/**
 * Сохранение метабокса.
 *
 * @param int $post_id ID поста.
 * @return void
 */
function booking_promo_save_metabox( $post_id ) {
	if ( ! isset( $_POST['booking_promo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['booking_promo_nonce'] ) ), 'booking_promo_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$code = isset( $_POST['booking_promo_code'] ) ? booking_normalize_promo_code( wp_unslash( $_POST['booking_promo_code'] ) ) : '';
	if ( '' === $code ) {
		$code = booking_normalize_promo_code( get_the_title( $post_id ) );
	}

	$other = booking_find_promo_id( $code, true );
	if ( $other && (int) $other !== (int) $post_id ) {
		$code = $code . '-' . $post_id;
	}

	$type = isset( $_POST['booking_promo_type'] ) ? sanitize_key( wp_unslash( $_POST['booking_promo_type'] ) ) : 'percent';
	if ( 'amount' !== $type ) {
		$type = 'percent';
	}

	$value = isset( $_POST['booking_promo_value'] ) ? absint( wp_unslash( $_POST['booking_promo_value'] ) ) : 0;
	if ( 'percent' === $type && $value > 100 ) {
		$value = 100;
	}

	$limit     = isset( $_POST['booking_promo_limit'] ) ? absint( wp_unslash( $_POST['booking_promo_limit'] ) ) : 0;
	$date_from = isset( $_POST['booking_promo_date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_promo_date_from'] ) ) : '';
	$date_to   = isset( $_POST['booking_promo_date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_promo_date_to'] ) ) : '';

	if ( $date_from && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_from ) ) {
		$date_from = '';
	}
	if ( $date_to && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_to ) ) {
		$date_to = '';
	}

	update_post_meta( $post_id, '_booking_promo_code', $code );
	update_post_meta( $post_id, '_booking_promo_type', $type );
	update_post_meta( $post_id, '_booking_promo_value', $value );
	update_post_meta( $post_id, '_booking_promo_limit', $limit );
	update_post_meta( $post_id, '_booking_promo_date_from', $date_from );
	update_post_meta( $post_id, '_booking_promo_date_to', $date_to );
	if ( '' === get_post_meta( $post_id, '_booking_promo_used', true ) ) {
		update_post_meta( $post_id, '_booking_promo_used', 0 );
	}
}
add_action( 'save_post_booking_promo', 'booking_promo_save_metabox' );

/**
 * Колонки списка.
 *
 * @param array $columns Колонки.
 * @return array
 */
function booking_promo_columns( $columns ) {
	$out = array();
	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'title' === $key ) {
			$out['promo_code']  = 'Код';
			$out['promo_off']   = 'Скидка';
			$out['promo_dates'] = 'Срок';
			$out['promo_used']  = 'Использовано';
		}
	}
	return $out;
}
add_filter( 'manage_booking_promo_posts_columns', 'booking_promo_columns' );

/**
 * Значения колонок.
 *
 * @param string $column  Колонка.
 * @param int    $post_id ID.
 * @return void
 */
function booking_promo_column_value( $column, $post_id ) {
	if ( 'promo_code' === $column ) {
		echo esc_html( booking_promo_meta( $post_id, 'code' ) );
		return;
	}
	if ( 'promo_off' === $column ) {
		$type  = booking_promo_meta( $post_id, 'type' );
		$value = (int) booking_promo_meta( $post_id, 'value' );
		echo 'amount' === $type
			? esc_html( number_format( $value, 0, '.', ' ' ) . ' ₽' )
			: esc_html( $value . ' %' );
		return;
	}
	if ( 'promo_dates' === $column ) {
		$from = booking_promo_meta( $post_id, 'date_from' );
		$to   = booking_promo_meta( $post_id, 'date_to' );
		if ( '' === $from && '' === $to ) {
			echo 'без срока';
			return;
		}
		$parts = array();
		if ( $from ) {
			$parts[] = 'с ' . mysql2date( 'd.m.Y', $from );
		}
		if ( $to ) {
			$parts[] = 'до ' . mysql2date( 'd.m.Y', $to );
		}
		echo esc_html( implode( ' ', $parts ) );
		return;
	}
	if ( 'promo_used' === $column ) {
		$used  = (int) booking_promo_meta( $post_id, 'used' );
		$limit = (int) booking_promo_meta( $post_id, 'limit' );
		echo $limit > 0
			? esc_html( $used . ' / ' . $limit )
			: esc_html( (string) $used );
	}
}
add_action( 'manage_booking_promo_posts_custom_column', 'booking_promo_column_value', 10, 2 );

/**
 * Нормализовать код.
 *
 * @param string $code Сырой код.
 * @return string
 */
function booking_normalize_promo_code( $code ) {
	$code = trim( (string) $code );
	$code = preg_replace( '/\s+/u', '', $code );
	if ( function_exists( 'mb_strtoupper' ) ) {
		return mb_strtoupper( $code, 'UTF-8' );
	}

	return strtoupper( $code );
}

/**
 * Мета промокода.
 *
 * @param int    $post_id ID.
 * @param string $key     Короткий ключ.
 * @return string
 */
function booking_promo_meta( $post_id, $key ) {
	$map = array(
		'code'      => '_booking_promo_code',
		'type'      => '_booking_promo_type',
		'value'     => '_booking_promo_value',
		'date_from' => '_booking_promo_date_from',
		'date_to'   => '_booking_promo_date_to',
		'limit'     => '_booking_promo_limit',
		'used'      => '_booking_promo_used',
	);
	if ( ! isset( $map[ $key ] ) ) {
		return '';
	}

	return (string) get_post_meta( (int) $post_id, $map[ $key ], true );
}

/**
 * Найти ID промокода по коду.
 *
 * @param string $code       Код.
 * @param bool   $any_status Искать и черновики.
 * @return int
 */
function booking_find_promo_id( $code, $any_status = false ) {
	$code = booking_normalize_promo_code( $code );
	if ( '' === $code ) {
		return 0;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'booking_promo',
			'post_status'            => $any_status ? array( 'publish', 'draft', 'pending', 'private' ) : array( 'publish' ),
			'posts_per_page'         => 1,
			'meta_key'               => '_booking_promo_code',
			'meta_value'             => $code,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		)
	);

	return ! empty( $query->posts[0] ) ? (int) $query->posts[0] : 0;
}

/**
 * Проверить промокод и посчитать скидку от базовой цены.
 *
 * @param string $code  Код.
 * @param int    $base  Цена до промокода.
 * @return array|WP_Error
 */
function booking_validate_promo( $code, $base ) {
	$code = booking_normalize_promo_code( $code );
	if ( '' === $code ) {
		return new WP_Error( 'promo_empty', 'Введите промокод' );
	}

	$post_id = booking_find_promo_id( $code, true );
	if ( $post_id <= 0 ) {
		return new WP_Error( 'promo_missing', 'Промокод не найден' );
	}

	if ( 'publish' !== get_post_status( $post_id ) ) {
		return new WP_Error( 'promo_off', 'Промокод недействителен' );
	}

	$value = (int) booking_promo_meta( $post_id, 'value' );
	if ( $value <= 0 ) {
		return new WP_Error( 'promo_off', 'Промокод недействителен' );
	}

	$today = current_datetime()->format( 'Y-m-d' );
	$from  = booking_promo_meta( $post_id, 'date_from' );
	$to    = booking_promo_meta( $post_id, 'date_to' );
	if ( $from && $today < $from ) {
		return new WP_Error( 'promo_early', 'Промокод ещё не действует' );
	}
	if ( $to && $today > $to ) {
		return new WP_Error( 'promo_expired', 'Срок действия промокода истёк' );
	}

	$limit = (int) booking_promo_meta( $post_id, 'limit' );
	$used  = (int) booking_promo_meta( $post_id, 'used' );
	if ( $limit > 0 && $used >= $limit ) {
		return new WP_Error( 'promo_limit', 'Промокод больше недоступен' );
	}

	$type = booking_promo_meta( $post_id, 'type' );
	if ( 'amount' !== $type ) {
		$type = 'percent';
	}

	$base = max( 0, (int) $base );
	$off  = 'percent' === $type
		? (int) round( $base * min( 100, $value ) / 100 )
		: min( $value, $base );

	$result = array(
		'id'    => $post_id,
		'code'  => booking_promo_meta( $post_id, 'code' ) ?: $code,
		'type'  => $type,
		'value' => $value,
		'off'   => $off,
	);

	if ( 'percent' === $type ) {
		$result['percent'] = min( 100, $value );
	} else {
		$result['amount'] = $value;
	}

	return $result;
}

/**
 * Увеличить счётчик после успешной записи.
 *
 * @param int $post_id ID промокода.
 * @return void
 */
function booking_promo_consume( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return;
	}

	$used = (int) get_post_meta( $post_id, '_booking_promo_used', true );
	update_post_meta( $post_id, '_booking_promo_used', $used + 1 );
}

/**
 * Базовая цена консультации на форме /appointment/.
 *
 * @param int    $doctor_id ID врача.
 * @param string $form_type self|many.
 * @return int
 */
function center_online_promo_base_price( $doctor_id, $form_type ) {
	$doctor_id = (int) $doctor_id;
	if ( $doctor_id <= 0 || 'doctors' !== get_post_type( $doctor_id ) ) {
		return 0;
	}

	$field = ( 'many' === $form_type ) ? 'cost_1_many' : 'cost_1';
	$raw   = function_exists( 'get_field' ) ? get_field( $field, $doctor_id ) : '';
	$raw   = str_replace( array( ' ', ',' ), array( '', '.' ), (string) $raw );

	return is_numeric( $raw ) ? (int) round( (float) $raw ) : 0;
}

/**
 * Ajax: проверить промокод онлайн-записи.
 *
 * @return void
 */
function center_online_ajax_promo() {
	check_ajax_referer( 'clinic_nonce', 'nonce' );

	$code      = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
	$doctor_id = isset( $_POST['doctor_id'] ) ? absint( wp_unslash( $_POST['doctor_id'] ) ) : 0;
	$form_type = isset( $_POST['form_type'] ) ? sanitize_key( wp_unslash( $_POST['form_type'] ) ) : 'self';
	if ( 'many' !== $form_type ) {
		$form_type = 'self';
	}

	$base = center_online_promo_base_price( $doctor_id, $form_type );
	$rule = booking_validate_promo( $code, $base );
	if ( is_wp_error( $rule ) ) {
		wp_send_json_error(
			array(
				'message' => $rule->get_error_message(),
			),
			400
		);
	}

	wp_send_json_success(
		array(
			'code'    => $rule['code'],
			'off'     => $rule['off'],
			'percent' => isset( $rule['percent'] ) ? $rule['percent'] : 0,
			'amount'  => isset( $rule['amount'] ) ? $rule['amount'] : 0,
		)
	);
}
add_action( 'wp_ajax_center_online_promo', 'center_online_ajax_promo' );
add_action( 'wp_ajax_nopriv_center_online_promo', 'center_online_ajax_promo' );
