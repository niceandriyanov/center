<?php
/**
 * Подключение CSS стилей
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Подключение стилей для фронтенда
 */
function clinic_enqueue_styles() {    
    // Подключение основных стилей приложения
    wp_enqueue_style('clinic-app', THEME_URI . '/assets/css/app.css', array(), THEME_VERSION);
    
    // Подключение стилей для Contact Form 7
    wp_enqueue_style('clinic-cf7', THEME_URI . '/assets/css/contact-form7.css', array(), THEME_VERSION);
}
add_action('wp_enqueue_scripts', 'clinic_enqueue_styles');

/**
 * Подключение стилей для админки
 */
function clinic_enqueue_admin_styles() {
    wp_enqueue_style('clinic-admin-style', THEME_URI . '/assets/css/admin.css', array(), THEME_VERSION);
    
    // Подключение стилей для кастомных полей ACF
    wp_enqueue_style('clinic-acf-color-select', THEME_URI . '/inc/acf/assets/color-select.css', array(), THEME_VERSION);
}
add_action('admin_enqueue_scripts', 'clinic_enqueue_admin_styles');
