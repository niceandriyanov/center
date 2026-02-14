<?php
/**
 * Страница настроек ACF
 *
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Создание страницы настроек с табами
 */
function clinic_acf_options_page() {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page(array(
            'page_title'  => 'Настройки клиники',
            'menu_title'  => 'Настройки клиники',
            'menu_slug'   => 'clinic-settings',
            'capability'  => 'edit_posts',
            'redirect'    => false,
            'icon_url'    => '/wp-content/uploads/2025/08/favicon.svg',
            'position'    => 2,
        ));
        acf_add_options_sub_page(array(
            'page_title'  => 'FAQ',
            'menu_title'  => 'FAQ',
            'parent_slug'   => 'clinic-settings',
            'capability'  => 'edit_posts',
            'redirect'    => false,
            'post_id'     => 'faq',
        ));
    }
}
add_action('acf/init', 'clinic_acf_options_page');
