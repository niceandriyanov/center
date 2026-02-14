<?php
/**
 * Функции для вывода элементов шапки сайта
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Вывод верхней панели шапки
 * 
 * @param array $args Аргументы для настройки вывода
 * @return void
 */

/**
 * Вывод верхней панели шапки
 * 
 * @param array $args Аргументы для настройки вывода
 * @return void
 */
function clinic_header_top_bar($args = array()) {
    // Значения по умолчанию
        $defaults = array(
        'show_links'        => true,
        'menu_location'     => 'top',
        'menu_class'        => '',
        'container'         => false,
        'fallback_cb'       => false
    );
    
    $args = wp_parse_args($args, $defaults);
    
    if ($args['show_links']) {
        // Пытаемся вывести меню
        if (has_nav_menu($args['menu_location'])) {
            wp_nav_menu(array(
                'theme_location'    => $args['menu_location'],
                'menu_class'        => $args['menu_class'],
                'container'         => $args['container'],
                'items_wrap'        => '%3$s',
                'walker'            => new Clinic_Walker_Top_Menu(),
                'fallback_cb'       => $args['fallback_cb'],
                'depth'             => 1,
            ));
        } 
    }
}

/**
 * Вывод меню с кастомным Walker
 * 
 * @param string $menu_location Локация меню
 * @param array $args Дополнительные аргументы
 * @return void
 */
function clinic_custom_menu($menu_location = 'top', $args = array()) {
    $defaults = array(
        'menu_class' => '',
        'container_class' => '',
        'fallback_cb' => 'clinic_default_top_bar'
    );
    
    $args = wp_parse_args($args, $defaults);
    
    if (has_nav_menu($menu_location)) {
        wp_nav_menu(array(
            'theme_location' => $menu_location,
            'menu_class' => $args['menu_class'],
            'container_class' => $args['container_class'],
            'walker' => new Clinic_Walker_Top_Menu(),
            'fallback_cb' => $args['fallback_cb']
        ));
    } else {
        if (is_callable($args['fallback_cb'])) {
            call_user_func($args['fallback_cb']);
        }
    }
}

/**
 * Вывод контактной информации в шапке
 * 
 * @param array $args Аргументы для настройки вывода
 * @return void
 */
function clinic_header_contacts($args = array()) {
    // Значения по умолчанию
    $defaults = array(
        'address'           => '',
        'phone'             => '',
        'btn_appointment'   => true,
        'page_contacts'     => '',
    );
    
    $args = wp_parse_args($args, $defaults);
    if ($args['address'] || $args['phone']) {
        echo '<div class="headerTopRight">';
        
        // Адрес
        if ($args['address']) {
            if (!empty($args['page_contacts'])) {
                echo '<a href="' . esc_url($args['page_contacts']) . '" class="headerAddress withLine">' . esc_html($args['address']) . '</a>';
            } else {
                echo '<span class="headerAddress withLine">' . esc_html($args['address']) . '</span>';
            }
        }
        
        // Телефон
        if ($args['phone']) {
            // Очищаем номер от лишних символов для tel: ссылки
            $clean_phone = preg_replace('/[^0-9+]/', '', $args['phone']);
            $phone_url = 'tel:' . $clean_phone;
            echo '<a href="' . esc_attr($phone_url) . '" class="headerTel withLine">' . esc_html($args['phone']) . '</a>';
        }

        echo '<a href="#" class="linkLk butModalRnovaLk">
                <img src="/wp-content/themes/clinic-prime/assets/img/ico/lk1.svg">
            </a>';
        
        echo '</div>';
    }
}

/**
 * Вывод логотипа
 * 
 * @param array $args Аргументы для настройки вывода
 * @return void
 */
function clinic_header_logo($args = array()) {
        
    echo '<a href="' . home_url('/') . '" class="logo headerLogo" rel="home">';
    echo '<img src="' . esc_url($args['url']) . '" alt="' . esc_attr($args['alt']) . '">';
    echo '</a>';
}

/**
 * Вывод навигационного меню
 * 
 * @param array $args Аргументы для настройки вывода
 * @return void
 */
function clinic_header_navigation() {
    // Значения по умолчанию
    $args = array(
        'theme_location'    => 'primary',
        'menu_class'        => 'list listFlex',
        'container_class'   => 'topMenu hiddenm',
        'container'         => 'nav',
        'fallback_cb'       => false,
        'walker'            => new Clinic_Walker_Main_Menu()
    );
    if (has_nav_menu($args['theme_location'])) {
        wp_nav_menu($args);
    }
}
