<?php
/**
 * Основная настройка темы
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Настройка темы
 */
function clinic_theme_setup() {
    // Поддержка переводов
    load_theme_textdomain('clinic-prime', THEME_DIR . '/languages');
    
    // Поддержка автоматических ссылок на RSS
    add_theme_support('automatic-feed-links');
    
    // Поддержка заголовка сайта
    add_theme_support('title-tag');
    
    // Поддержка миниатюр постов
    add_theme_support('post-thumbnails');
    
    // Поддержка HTML5 разметки
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    
    // Поддержка широкого и полного контента
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    #add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    
    // Регистрация меню
    register_nav_menus(array(
        'top'       => __('Верхнее меню', 'clinic-prime'),
        'primary'   => __('Главное меню', 'clinic-prime'),
        'footer'    => __('Нижнее меню', 'clinic-prime'),
    ));
    
    // Добавление поддержки WooCommerce
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'clinic_theme_setup');


add_image_size('doctor-article', 438, 246, true);
add_image_size('doctor', 580, 720, true);
add_image_size('avatar', 120, 120, true);
add_image_size('about_slider', 485, 646, true);
add_image_size('about_gallery', 684, 386, true);


add_action('phpmailer_init', function($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp.yandex.ru';
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 465;
    $phpmailer->Username   = 'clinic@handlingbetter.ru';
    $phpmailer->Password   = 'ifjxiyavwdvoaanu'; // 16 символов без пробелов
    $phpmailer->SMTPSecure = 'ssl';
    
    // Принудительно устанавливаем отправителя, чтобы CF7 не подставил другой email
    $phpmailer->From       = 'clinic@handlingbetter.ru';
    $phpmailer->FromName   = get_bloginfo('name');
    $phpmailer->Sender     = $phpmailer->From;

    // Диагностика: логируем фактический From перед отправкой
    $debug_context = array(
        'from' => $phpmailer->From,
        'from_name' => $phpmailer->FromName,
        'sender' => $phpmailer->Sender,
        'host' => $phpmailer->Host,
        'username' => $phpmailer->Username,
        'request_uri' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
    );
    error_log('phpmailer_init: ' . wp_json_encode($debug_context, JSON_UNESCAPED_UNICODE));

    // Настройка для некоторых серверов, где возникают проблемы с проверкой сертификатов
    $phpmailer->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
});