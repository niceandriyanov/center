<?php
/**
 * Подключение админских скриптов и стилей
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Подключение админских скриптов
 */
function clinic_admin_scripts($hook) {
    // Подключаем только на страницах редактирования постов и страниц
    if (in_array($hook, array('post.php', 'post-new.php', 'page.php', 'page-new.php'))) {
        // Классический редактор
        wp_enqueue_script(
            'clinic-slug-transliteration',
            THEME_URI . '/inc/admin/slug-transliteration.js',
            array('jquery'),
            THEME_VERSION,
            true
        );
        
        // Gutenberg редактор
        /*wp_enqueue_script(
            'clinic-gutenberg-transliteration',
            THEME_URI . '/inc/admin/gutenberg-transliteration.js',
            array('wp-data', 'wp-edit-post'),
            THEME_VERSION,
            true
        );*/
        
        // Передаем AJAX данные в JavaScript
        wp_localize_script('clinic-slug-transliteration', 'clinic_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('clinic_transliterate_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'clinic_admin_scripts');
