<?php
/**
 * Plugin Name: Center Med — интеграция с МИС Renovatio
 * Plugin URI: https://center-med.ru
 * Description: Интеграция сайта с API медицинской информационной системы Renovatio.
 * Version: 0.1.0
 * Author: Center Med
 * Author URI: https://center-med.ru
 * Text Domain: center-med-renovatio
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

const CENTER_MED_RENOVATIO_VERSION   = '0.1.0';
const CENTER_MED_RENOVATIO_PLUGIN_FILE = __FILE__;
const CENTER_MED_RENOVATIO_PLUGIN_DIR = __DIR__;
const CENTER_MED_RENOVATIO_OPTION_NAME = 'center_med_renovatio_settings';

// Библиотека работы с API МИС Renovatio.
require_once CENTER_MED_RENOVATIO_PLUGIN_DIR . '/includes/class-renovatio-api-client.php';
require_once CENTER_MED_RENOVATIO_PLUGIN_DIR . '/includes/class-renovatio-db-schema.php';
require_once CENTER_MED_RENOVATIO_PLUGIN_DIR . '/includes/functions.php';

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'center-med-renovatio', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// Страница настроек (только в админке).
if ( is_admin() ) {
	require_once CENTER_MED_RENOVATIO_PLUGIN_DIR . '/admin/class-renovatio-admin-settings.php';
	Renovatio_Admin_Settings::register();
}

register_activation_hook( __FILE__, [ 'Renovatio_Db_Schema', 'install' ] );
