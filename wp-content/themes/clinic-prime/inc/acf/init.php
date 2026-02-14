<?php
/**
 * Инициализация ACF для темы Clinic Prime
 *
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}


// Подключение всех ACF полей
require_once THEME_DIR . '/inc/acf/fields/fields.php';

// Подключение кастомных полей ACF
require_once THEME_DIR . '/inc/acf/fields/color-select-field.php';

// Подключение функций для работы с ACF
require_once THEME_DIR . '/inc/acf/functions.php';
