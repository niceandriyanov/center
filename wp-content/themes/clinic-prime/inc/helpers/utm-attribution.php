<?php
/**
 * Сохранение UTM-меток в куки при первом заходе с метками в URL.
 *
 * @package Clinic_Prime
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var int Срок жизни куки UTM (10 суток). */
const CLINIC_PRIME_UTM_COOKIE_TTL = 10 * DAY_IN_SECONDS;

/**
 * Записать utm_source / utm_medium / utm_campaign в HttpOnly-куки.
 *
 * @return void
 */
function clinic_prime_capture_utm_cookies() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}

	$params = [ 'utm_source', 'utm_medium', 'utm_campaign' ];
	$has    = false;
	foreach ( $params as $p ) {
		if ( isset( $_GET[ $p ] ) && (string) wp_unslash( $_GET[ $p ] ) !== '' ) {
			$has = true;
			break;
		}
	}
	if ( ! $has ) {
		return;
	}

	$path   = ( defined( 'COOKIEPATH' ) && COOKIEPATH ) ? COOKIEPATH : '/';
	$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
	$secure = is_ssl();

	$opts = [
		'expires'  => time() + CLINIC_PRIME_UTM_COOKIE_TTL,
		'path'     => $path,
		'domain'   => $domain,
		'secure'   => $secure,
		'httponly' => true,
		'samesite' => 'Lax',
	];

	foreach ( $params as $p ) {
		if ( ! isset( $_GET[ $p ] ) ) {
			continue;
		}
		$val = sanitize_text_field( wp_unslash( (string) $_GET[ $p ] ) );
		if ( '' === $val ) {
			continue;
		}
		$name = 'cmr_' . $p;
		setcookie( $name, $val, $opts );
		// Доступно в том же запросе (в т.ч. для последующих хуков).
		$_COOKIE[ $name ] = $val;
	}
}

add_action( 'init', 'clinic_prime_capture_utm_cookies', 1 );
