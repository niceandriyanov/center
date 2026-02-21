<?php
/**
 * Подключение JavaScript файлов
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Подключение скриптов для фронтенда
 */
function clinic_enqueue_scripts() {
    wp_enqueue_script('clinic-mask', 'https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js', [], null, true);
    wp_enqueue_script('clinic-swiper', THEME_URI . '/assets/js/lib/swiper-bundle.min.js', [], null, true);
    if(is_page_template('template-parts/online-form.php')) {
        wp_enqueue_script('online-form', THEME_URI . '/assets/js/online-form.js', [], THEME_VERSION, true);
    }
    wp_enqueue_script('clinic-scripts', THEME_URI . '/assets/js/script.js', [], THEME_VERSION, true);
    if(!is_page_template('template-parts/online-form.php')) {
        wp_enqueue_script('clinic-form-validation', THEME_URI . '/assets/js/form-validation.js', [], THEME_VERSION, true);
    }
    
    // Локализация скриптов
    wp_localize_script('clinic-scripts', 'clinic_ajax', array(
        'ajax_url'      => admin_url('admin-ajax.php'),
        'nonce'         => wp_create_nonce('clinic_nonce'),
        'rnova_url'     => get_permalink(294),
        'rnova_lk_url'  => get_permalink(436), // ID страницы для LK виджета
    ));
}
add_action('wp_enqueue_scripts', 'clinic_enqueue_scripts');
