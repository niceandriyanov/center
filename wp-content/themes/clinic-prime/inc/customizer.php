<?php
/**
 * Настройки кастомайзера для темы клиники
 */

// Предотвращение прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Расширенные настройки кастомайзера
 */
function clinic_customizer_settings($wp_customize) {
    
    // Секция основных настроек
    $wp_customize->add_section('clinic_general', array(
        'title'    => __('Основные настройки', 'clinic-stati-prosche'),
        'priority' => 20,
    ));
    
    // Цветовая схема
    $wp_customize->add_setting('clinic_primary_color', array(
        'default'           => '#667eea',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'clinic_primary_color', array(
        'label'   => __('Основной цвет', 'clinic-stati-prosche'),
        'section' => 'clinic_general',
    )));
    
    // Вторичный цвет
    $wp_customize->add_setting('clinic_secondary_color', array(
        'default'           => '#764ba2',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'clinic_secondary_color', array(
        'label'   => __('Вторичный цвет', 'clinic-stati-prosche'),
        'section' => 'clinic_general',
    )));
    
    // Секция контактов
    $wp_customize->add_section('clinic_contacts', array(
        'title'    => __('Контакты клиники', 'clinic-stati-prosche'),
        'priority' => 30,
    ));
    
    // Телефон
    $wp_customize->add_setting('clinic_phone', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('clinic_phone', array(
        'label'   => __('Телефон', 'clinic-stati-prosche'),
        'section' => 'clinic_contacts',
        'type'    => 'text',
    ));
    
    // Email
    $wp_customize->add_setting('clinic_email', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_email',
    ));
    
    $wp_customize->add_control('clinic_email', array(
        'label'   => __('Email', 'clinic-stati-prosche'),
        'section' => 'clinic_contacts',
        'type'    => 'email',
    ));
    
    // Адрес
    $wp_customize->add_setting('clinic_address', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('clinic_address', array(
        'label'   => __('Адрес', 'clinic-stati-prosche'),
        'section' => 'clinic_contacts',
        'type'    => 'textarea',
    ));
    
    // Время работы
    $wp_customize->add_setting('clinic_hours', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('clinic_hours', array(
        'label'   => __('Время работы', 'clinic-stati-prosche'),
        'section' => 'clinic_contacts',
        'type'    => 'textarea',
    ));
    
    // Секция социальных сетей
    $wp_customize->add_section('clinic_social', array(
        'title'    => __('Социальные сети', 'clinic-stati-prosche'),
        'priority' => 40,
    ));
    
    // VKontakte
    $wp_customize->add_setting('clinic_social_vk', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    $wp_customize->add_control('clinic_social_vk', array(
        'label'   => __('VKontakte', 'clinic-stati-prosche'),
        'section' => 'clinic_social',
        'type'    => 'url',
    ));
    
    // Telegram
    $wp_customize->add_setting('clinic_social_telegram', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    $wp_customize->add_control('clinic_social_telegram', array(
        'label'   => __('Telegram', 'clinic-stati-prosche'),
        'section' => 'clinic_social',
        'type'    => 'url',
    ));
    
    // WhatsApp
    $wp_customize->add_setting('clinic_social_whatsapp', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    $wp_customize->add_control('clinic_social_whatsapp', array(
        'label'   => __('WhatsApp', 'clinic-stati-prosche'),
        'section' => 'clinic_social',
        'type'    => 'url',
    ));
    
    // Instagram
    $wp_customize->add_setting('clinic_social_instagram', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    $wp_customize->add_control('clinic_social_instagram', array(
        'label'   => __('Instagram', 'clinic-stati-prosche'),
        'section' => 'clinic_social',
        'type'    => 'url',
    ));
    
    // Секция главной страницы
    $wp_customize->add_section('clinic_homepage', array(
        'title'    => __('Главная страница', 'clinic-stati-prosche'),
        'priority' => 50,
    ));
    
    // Заголовок главной страницы
    $wp_customize->add_setting('clinic_homepage_title', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('clinic_homepage_title', array(
        'label'   => __('Заголовок главной страницы', 'clinic-stati-prosche'),
        'section' => 'clinic_homepage',
        'type'    => 'text',
    ));
    
    // Подзаголовок главной страницы
    $wp_customize->add_setting('clinic_homepage_subtitle', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('clinic_homepage_subtitle', array(
        'label'   => __('Подзаголовок главной страницы', 'clinic-stati-prosche'),
        'section' => 'clinic_homepage',
        'type'    => 'textarea',
    ));
    
    // Кнопка записи на прием
    $wp_customize->add_setting('clinic_appointment_button_text', array(
        'default'           => 'Записаться на прием',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('clinic_appointment_button_text', array(
        'label'   => __('Текст кнопки записи', 'clinic-stati-prosche'),
        'section' => 'clinic_homepage',
        'type'    => 'text',
    ));
    
    // Секция SEO
    $wp_customize->add_section('clinic_seo', array(
        'title'    => __('SEO настройки', 'clinic-stati-prosche'),
        'priority' => 60,
    ));
    
    // Meta description
    $wp_customize->add_setting('clinic_meta_description', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('clinic_meta_description', array(
        'label'   => __('Meta Description', 'clinic-stati-prosche'),
        'section' => 'clinic_seo',
        'type'    => 'textarea',
    ));
    
    // Google Analytics
    $wp_customize->add_setting('clinic_google_analytics', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('clinic_google_analytics', array(
        'label'   => __('Google Analytics код', 'clinic-stati-prosche'),
        'section' => 'clinic_seo',
        'type'    => 'textarea',
        'description' => __('Вставьте код Google Analytics (gtag.js)', 'clinic-stati-prosche'),
    ));
    
    // Секция дополнительных настроек
    $wp_customize->add_section('clinic_advanced', array(
        'title'    => __('Дополнительные настройки', 'clinic-stati-prosche'),
        'priority' => 70,
    ));
    
    // Показывать хлебные крошки
    $wp_customize->add_setting('clinic_show_breadcrumbs', array(
        'default'           => true,
        'sanitize_callback' => 'clinic_sanitize_checkbox',
    ));
    
    $wp_customize->add_control('clinic_show_breadcrumbs', array(
        'label'   => __('Показывать хлебные крошки', 'clinic-stati-prosche'),
        'section' => 'clinic_advanced',
        'type'    => 'checkbox',
    ));
    
    // Показывать статус клиники
    $wp_customize->add_setting('clinic_show_status', array(
        'default'           => true,
        'sanitize_callback' => 'clinic_sanitize_checkbox',
    ));
    
    $wp_customize->add_control('clinic_show_status', array(
        'label'   => __('Показывать статус клиники', 'clinic-stati-prosche'),
        'section' => 'clinic_advanced',
        'type'    => 'checkbox',
    ));
    
    // Показывать социальные сети
    $wp_customize->add_setting('clinic_show_social', array(
        'default'           => true,
        'sanitize_callback' => 'clinic_sanitize_checkbox',
    ));
    
    $wp_customize->add_control('clinic_show_social', array(
        'label'   => __('Показывать социальные сети', 'clinic-stati-prosche'),
        'section' => 'clinic_advanced',
        'type'    => 'checkbox',
    ));
}

add_action('customize_register', 'clinic_customizer_settings');

/**
 * Функция для санитизации checkbox
 */
function clinic_sanitize_checkbox($checked) {
    return ((isset($checked) && true == $checked) ? true : false);
}

/**
 * Функция для санитизации select
 */
function clinic_sanitize_select($input, $setting) {
    $input = sanitize_key($input);
    $choices = $setting->manager->get_control($setting->id)->choices;
    return (array_key_exists($input, $choices) ? $input : $setting->default);
}

/**
 * Функция для санитизации radio
 */
function clinic_sanitize_radio($input, $setting) {
    $input = sanitize_key($input);
    $choices = $setting->manager->get_control($setting->id)->choices;
    return (array_key_exists($input, $choices) ? $input : $setting->default);
}

/**
 * Функция для санитизации number
 */
function clinic_sanitize_number($input, $setting) {
    $number = absint($input);
    return ($number ? $number : $setting->default);
}

/**
 * Функция для санитизации URL
 */
function clinic_sanitize_url($url) {
    return esc_url_raw($url);
}

/**
 * Функция для санитизации email
 */
function clinic_sanitize_email($email) {
    return sanitize_email($email);
}

/**
 * Функция для санитизации текста
 */
function clinic_sanitize_text($text) {
    return sanitize_text_field($text);
}

/**
 * Функция для санитизации textarea
 */
function clinic_sanitize_textarea($text) {
    return sanitize_textarea_field($text);
}

/**
 * Функция для санитизации HTML
 */
function clinic_sanitize_html($html) {
    return wp_kses_post($html);
}

/**
 * Функция для санитизации цвета
 */
function clinic_sanitize_color($color) {
    return sanitize_hex_color($color);
}

/**
 * Функция для санитизации изображения
 */
function clinic_sanitize_image($image, $setting) {
    $mimes = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif'          => 'image/gif',
        'png'          => 'image/png',
        'bmp'          => 'image/bmp',
        'tif|tiff'     => 'image/tiff',
        'ico'          => 'image/x-icon'
    );
    
    $file = wp_check_filetype($image, $mimes);
    return ($file['ext'] ? $image : $setting->default);
}

/**
 * Функция для санитизации файла
 */
function clinic_sanitize_file($file, $setting) {
    $mimes = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif'          => 'image/gif',
        'png'          => 'image/png',
        'bmp'          => 'image/bmp',
        'tif|tiff'     => 'image/tiff',
        'ico'          => 'image/x-icon',
        'pdf'          => 'application/pdf',
        'doc|docx'     => 'application/msword',
        'xls|xlsx'     => 'application/vnd.ms-excel',
        'ppt|pptx'     => 'application/vnd.ms-powerpoint',
        'txt'          => 'text/plain',
        'zip'          => 'application/zip',
        'rar'          => 'application/x-rar-compressed'
    );
    
    $file_check = wp_check_filetype($file, $mimes);
    return ($file_check['ext'] ? $file : $setting->default);
}
