<?php
/**
 * Функции темы Clinic Prime
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @author Clinic Team
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Константы темы
 */
define('THEME_VERSION', '1.0.100');
define('THEME_DIR', get_template_directory());
define('THEME_URI', get_template_directory_uri());

/**
 * Подключение всех модулей темы
 */
require_once THEME_DIR . '/inc/init.php';
