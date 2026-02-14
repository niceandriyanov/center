<?php
/**
 * Настройки производительности темы
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Настройки отключения ресурсов
 * Измените значения на false, чтобы включить обратно соответствующие ресурсы
 */
define('DISABLE_BLOCK_LIBRARY', true);    // Отключить wp-block-library CSS + inline стили
define('DISABLE_JQUERY', true);           // Отключить jQuery
define('DISABLE_EMOJI', true);            // Отключить emoji скрипты
define('DISABLE_EMBED', true);            // Отключить embed скрипты
define('DISABLE_REST_LINKS', true);       // Отключить REST API ссылки

/**
 * Условное отключение wp-block-library CSS
 */
function clinic_conditional_disable_block_library() {
    if (DISABLE_BLOCK_LIBRARY) {
        wp_dequeue_style('wp-block-library');
        wp_deregister_style('wp-block-library');
        
        // Отключение inline стилей блоков
        wp_dequeue_style('wp-block-library-theme');
        wp_deregister_style('wp-block-library-theme');
        
        // Отключение classic-theme-styles
        wp_dequeue_style('classic-theme-styles');
        wp_deregister_style('classic-theme-styles');
        
        // Отключение global-styles
        wp_dequeue_style('global-styles');
        wp_deregister_style('global-styles');
    }
}
add_action('wp_enqueue_scripts', 'clinic_conditional_disable_block_library', 100);

/**
 * Дополнительное отключение inline стилей WordPress
 */
function clinic_remove_inline_styles() {
    if (DISABLE_BLOCK_LIBRARY) {
        // Удаляем inline стили из head
        remove_action('wp_head', 'wp_enqueue_global_styles');
        remove_action('wp_head', 'wp_enqueue_classic_theme_styles');
        
        // Отключаем поддержку блоков в теме
        add_theme_support('disable-block-styles');
    }
}
add_action('after_setup_theme', 'clinic_remove_inline_styles');



/**
 * Буферизация вывода для удаления inline стилей
 */
function clinic_start_output_buffer() {
    if (DISABLE_BLOCK_LIBRARY && !is_admin()) {
        ob_start('clinic_clean_output');
    }
}
add_action('wp_head', 'clinic_start_output_buffer', 0);

function clinic_clean_output($html) {
    if (DISABLE_BLOCK_LIBRARY) {
        // Удаляем inline стили блоков
        $html = preg_replace('/<style[^>]*id=[\'"]wp-block-library-theme-inline-css[\'"][^>]*>.*?<\/style>/s', '', $html);
        $html = preg_replace('/<style[^>]*id=[\'"]classic-theme-styles-inline-css[\'"][^>]*>.*?<\/style>/s', '', $html);
        $html = preg_replace('/<style[^>]*id=[\'"]global-styles-inline-css[\'"][^>]*>.*?<\/style>/s', '', $html);
    }
    return $html;
}

/**
 * Условное отключение jQuery
 */
function clinic_conditional_disable_jquery() {
    if (DISABLE_JQUERY && !is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', false);
    }
}
add_action('wp_enqueue_scripts', 'clinic_conditional_disable_jquery', 100);

/**
 * Условное отключение jQuery Migrate
 */
function clinic_conditional_disable_jquery_migrate() {
    if (DISABLE_JQUERY && !is_admin()) {
        wp_deregister_script('jquery-migrate');
        wp_register_script('jquery-migrate', false);
    }
}
add_action('wp_enqueue_scripts', 'clinic_conditional_disable_jquery_migrate', 100);

/**
 * Условное отключение emoji скриптов
 */
function clinic_conditional_disable_emoji() {
    if (DISABLE_EMOJI) {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        
        // Отключение emoji в TinyMCE
        add_filter('tiny_mce_plugins', function($plugins) {
            return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
        });
        
        // Удаляем dns-prefetch для emoji
        add_filter('emoji_svg_url', '__return_false');
    }
}
add_action('init', 'clinic_conditional_disable_emoji');

/**
 * Условное отключение embed скриптов
 */
function clinic_conditional_disable_embed() {
    if (DISABLE_EMBED) {
        wp_deregister_script('wp-embed');
    }
}
add_action('wp_footer', 'clinic_conditional_disable_embed');

/**
 * Условное отключение REST API ссылок
 */
function clinic_conditional_remove_rest_api_links() {
    if (DISABLE_REST_LINKS) {
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
    }
}
add_action('after_setup_theme', 'clinic_conditional_remove_rest_api_links');
