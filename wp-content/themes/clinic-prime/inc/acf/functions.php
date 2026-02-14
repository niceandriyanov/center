<?php
/**
 * Функции для работы с ACF данными
 *
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить контакты клиники из ACF
 */
function clinic_get_acf_contacts() {
    return array(
        'phone' => get_field('contact_phone', 'option'),
        'phone_secondary' => get_field('contact_phone_secondary', 'option'),
        'email' => get_field('contact_email', 'option'),
        'address' => get_field('contact_address', 'option'),
        'coordinates' => get_field('contact_coordinates', 'option'),
        'hours' => get_field('contact_hours', 'option'),
        'whatsapp' => get_field('contact_whatsapp', 'option'),
        'telegram' => get_field('contact_telegram', 'option'),
        'feedback_email' => get_field('contact_feedback_email', 'option'),
    );
}

/**
 * Получить настройки Header из ACF
 */
function clinic_get_acf_header() {
    return array(
        'logo' => get_field('header_logo', 'option'),
        'logo_alt' => get_field('header_logo_alt', 'option'),
        'phone' => get_field('header_phone', 'option'),
        'show_phone' => get_field('header_show_phone', 'option'),
        'cta_button' => get_field('header_cta_button', 'option'),
        'sticky' => get_field('header_sticky', 'option'),
        'bg_color' => get_field('header_bg_color', 'option'),
    );
}



/**
 * Получить социальные сети из ACF
 */
function clinic_get_acf_social() {
    return array(
        'vk' => get_field('social_vk', 'option'),
        'telegram' => get_field('social_telegram', 'option'),
        'whatsapp' => get_field('social_whatsapp', 'option'),
        'instagram' => get_field('social_instagram', 'option'),
        'youtube' => get_field('social_youtube', 'option'),
        'facebook' => get_field('social_facebook', 'option'),
        'twitter' => get_field('social_twitter', 'option'),
        'linkedin' => get_field('social_linkedin', 'option'),
        'ok' => get_field('social_ok', 'option'),
        'zen' => get_field('social_zen', 'option'),
        'display_settings' => get_field('social_display_settings', 'option'),
    );
}



/**
 * Форматировать телефон для ссылки
 */
function clinic_format_phone_for_link($phone) {
    return preg_replace('/[^0-9+]/', '', $phone);
}

/**
 * Получить WhatsApp ссылку
 */
function clinic_get_whatsapp_link($phone = null) {
    if ($phone === null) {
        $phone = get_field('contact_whatsapp', 'option');
    }
    
    if (empty($phone)) {
        return '';
    }
    
    $formatted_phone = clinic_format_phone_for_link($phone);
    return "https://wa.me/7{$formatted_phone}";
}

/**
 * Получить Telegram ссылку
 */
function clinic_get_telegram_link($username = null) {
    if ($username === null) {
        $username = get_field('contact_telegram', 'option');
    }
    
    if (empty($username)) {
        return '';
    }
    
    return "https://t.me/{$username}";
}

/**
 * Проверить, нужно ли показывать социальные сети в шапке
 */
function clinic_show_social_in_header() {
    $display_settings = get_field('social_display_settings', 'option');
    return !empty($display_settings['show_in_header']);
}

/**
 * Проверить, нужно ли показывать социальные сети в футере
 */
function clinic_show_social_in_footer() {
    $display_settings = get_field('social_display_settings', 'option');
    return !empty($display_settings['show_in_footer']);
}





