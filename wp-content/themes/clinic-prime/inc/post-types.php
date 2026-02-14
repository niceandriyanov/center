<?php
/**
 * Кастомные типы постов для клиники
 */

// Предотвращение прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация кастомных типов постов
 */
function clinic_register_post_types() {
    
    // Тип поста "Услуги" (технический раздел)
    register_post_type('services', array(
        'labels' => array(
            'name'               => __('Услуги', 'clinic-stati-prosche'),
            'singular_name'      => __('Услуга', 'clinic-stati-prosche'),
            'menu_name'          => __('Услуги', 'clinic-stati-prosche'),
            'name_admin_bar'     => __('Услуга', 'clinic-stati-prosche'),
            'add_new'            => __('Добавить услугу', 'clinic-stati-prosche'),
            'add_new_item'       => __('Добавить новую услугу', 'clinic-stati-prosche'),
            'new_item'           => __('Новая услуга', 'clinic-stati-prosche'),
            'edit_item'          => __('Редактировать услугу', 'clinic-stati-prosche'),
            'view_item'          => __('Просмотреть услугу', 'clinic-stati-prosche'),
            'all_items'          => __('Все услуги', 'clinic-stati-prosche'),
            'search_items'       => __('Искать услуги', 'clinic-stati-prosche'),
            'parent_item_colon'  => __('Родительские услуги:', 'clinic-stati-prosche'),
            'not_found'          => __('Услуги не найдены', 'clinic-stati-prosche'),
            'not_found_in_trash' => __('В корзине услуг не найдено', 'clinic-stati-prosche'),
        ),
        'public'              => false,           // Не публичный
        'publicly_queryable'  => false,          // Не доступен для запросов
        'show_ui'             => true,           // Показывать в админке
        'show_in_menu'        => true,           // Показывать в меню админки
        'query_var'           => false,          // Не использовать в запросах
        'rewrite'             => false,          // Отключить перезапись URL
        'capability_type'     => 'post',
        'has_archive'         => false,          // Нет архива
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-heart',
        'supports'            => array('title'),
        'show_in_rest'        => false,          // Отключить REST API
        'exclude_from_search' => true,           // Исключить из поиска
    ));
    
    // Тип поста "Врачи"
    register_post_type('doctors', array(
        'labels' => array(
            'name'               => __('Врачи', 'clinic-stati-prosche'),
            'singular_name'      => __('Врач', 'clinic-stati-prosche'),
            'menu_name'          => __('Врачи', 'clinic-stati-prosche'),
            'name_admin_bar'     => __('Врач', 'clinic-stati-prosche'),
            'add_new'            => __('Добавить врача', 'clinic-stati-prosche'),
            'add_new_item'       => __('Добавить нового врача', 'clinic-stati-prosche'),
            'new_item'           => __('Новый врач', 'clinic-stati-prosche'),
            'edit_item'          => __('Редактировать врача', 'clinic-stati-prosche'),
            'view_item'          => __('Просмотреть врача', 'clinic-stati-prosche'),
            'all_items'          => __('Все врачи', 'clinic-stati-prosche'),
            'search_items'       => __('Искать врачей', 'clinic-stati-prosche'),
            'parent_item_colon'  => __('Родительские врачи:', 'clinic-stati-prosche'),
            'not_found'          => __('Врачи не найдены', 'clinic-stati-prosche'),
            'not_found_in_trash' => __('В корзине врачей не найдено', 'clinic-stati-prosche'),
        ),
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'doctors'),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 6,
        'menu_icon'           => 'dashicons-admin-users',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes'),
        'show_in_rest'        => true,
    ));
    
}

add_action('init', 'clinic_register_post_types');

/**
 * Регистрация таксономий
 */
function clinic_register_taxonomies() {
    
    // Таксономия для врачей
    register_taxonomy('doctor_specialty', array('doctors'), array(
        'labels' => array(
            'name'              => __('Специализации', 'clinic-stati-prosche'),
            'singular_name'     => __('Специализация', 'clinic-stati-prosche'),
            'search_items'      => __('Искать специализации', 'clinic-stati-prosche'),
            'all_items'         => __('Все специализации', 'clinic-stati-prosche'),
            'parent_item'       => __('Родительская специализация', 'clinic-stati-prosche'),
            'parent_item_colon' => __('Родительская специализация:', 'clinic-stati-prosche'),
            'edit_item'         => __('Редактировать специализацию', 'clinic-stati-prosche'),
            'update_item'       => __('Обновить специализацию', 'clinic-stati-prosche'),
            'add_new_item'      => __('Добавить новую специализацию', 'clinic-stati-prosche'),
            'new_item_name'     => __('Название новой специализации', 'clinic-stati-prosche'),
            'menu_name'         => __('Специализации', 'clinic-stati-prosche'),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'specialty'),
        'show_in_rest'      => true,
    ));
    
    // Таксономия тегов для болезней врачей
    register_taxonomy('doctor_diseases', array('doctors'), array(
        'labels' => array(
            'name'              => __('Болезни', 'clinic-stati-prosche'),
            'singular_name'     => __('Болезнь', 'clinic-stati-prosche'),
            'search_items'      => __('Искать болезни', 'clinic-stati-prosche'),
            'all_items'         => __('Все болезни', 'clinic-stati-prosche'),
            'edit_item'         => __('Редактировать болезнь', 'clinic-stati-prosche'),
            'update_item'       => __('Обновить болезнь', 'clinic-stati-prosche'),
            'add_new_item'      => __('Добавить новую болезнь', 'clinic-stati-prosche'),
            'new_item_name'     => __('Название новой болезни', 'clinic-stati-prosche'),
            'menu_name'         => __('Болезни', 'clinic-stati-prosche'),
            'popular_items'     => __('Популярные болезни', 'clinic-stati-prosche'),
            'separate_items_with_commas' => __('Разделяйте болезни запятыми', 'clinic-stati-prosche'),
            'add_or_remove_items' => __('Добавить или удалить болезни', 'clinic-stati-prosche'),
            'choose_from_most_used' => __('Выбрать из часто используемых', 'clinic-stati-prosche'),
        ),
        'hierarchical'      => false, // Теги не иерархические
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'diseases'),
        'show_in_rest'      => true,
        'show_tagcloud'     => true, // Показывать облако тегов
    ));
}

add_action('init', 'clinic_register_taxonomies');
