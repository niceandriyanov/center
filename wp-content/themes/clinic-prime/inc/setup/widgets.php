<?php
/**
 * Регистрация областей виджетов
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация областей виджетов
 */
function clinic_widgets_init() {
    register_sidebar(array(
        'name'          => __('Боковая панель', 'clinic-prime'),
        'id'            => 'sidebar-1',
        'description'   => __('Добавьте виджеты сюда.', 'clinic-prime'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
    
    register_sidebar(array(
        'name'          => __('Подвал - Колонка 1', 'clinic-prime'),
        'id'            => 'footer-1',
        'description'   => __('Первая колонка в подвале.', 'clinic-prime'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => __('Подвал - Колонка 2', 'clinic-prime'),
        'id'            => 'footer-2',
        'description'   => __('Вторая колонка в подвале.', 'clinic-prime'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => __('Подвал - Колонка 3', 'clinic-prime'),
        'id'            => 'footer-3',
        'description'   => __('Третья колонка в подвале.', 'clinic-prime'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'clinic_widgets_init');
