<?php
/**
 * Drag & Drop сортировка врачей в админке
 *
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Подключение скриптов и стилей для сортировки врачей
 */
function clinic_enqueue_doctor_sorting_assets($hook) {
    if ($hook !== 'edit.php') {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'doctors') {
        return;
    }

    wp_enqueue_script(
        'clinic-doctor-sorting',
        get_template_directory_uri() . '/assets/js/admin/doctor-sorting.js',
        array('jquery', 'jquery-ui-sortable'),
        '1.0.0',
        true
    );

    wp_enqueue_style(
        'clinic-doctor-sorting',
        get_template_directory_uri() . '/assets/css/admin/doctor-sorting.css',
        array(),
        '1.0.0'
    );

    wp_localize_script('clinic-doctor-sorting', 'clinicDoctorSorting', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('clinic_doctor_sorting_nonce'),
        'strings' => array(
            'saving' => __('Сохранение порядка...', 'clinic-prime'),
            'saved' => __('Порядок сохранен!', 'clinic-prime'),
            'error' => __('Ошибка сохранения!', 'clinic-prime')
        )
    ));
}
add_action('admin_enqueue_scripts', 'clinic_enqueue_doctor_sorting_assets');

/**
 * Добавление колонки для сортировки в списке врачей
 */
function clinic_add_doctor_order_column($columns) {
    $new_columns = array();

    foreach ($columns as $key => $label) {
        if ($key === 'title') {
            $new_columns['doctor_order'] = __('Порядок', 'clinic-prime');
        }
        $new_columns[$key] = $label;
    }

    if (!isset($new_columns['doctor_order'])) {
        $new_columns['doctor_order'] = __('Порядок', 'clinic-prime');
    }

    return $new_columns;
}
add_filter('manage_doctors_posts_columns', 'clinic_add_doctor_order_column');

/**
 * Вывод содержимого колонки сортировки
 */
function clinic_render_doctor_order_column($column, $post_id) {
    if ($column !== 'doctor_order') {
        return;
    }

    echo '<span class="clinic-order-handle dashicons dashicons-menu" aria-hidden="true"></span>';
    echo '<span class="screen-reader-text">' . esc_html__('Перетащите для сортировки', 'clinic-prime') . '</span>';
}
add_action('manage_doctors_posts_custom_column', 'clinic_render_doctor_order_column', 10, 2);

/**
 * AJAX обработчик для сохранения порядка врачей
 */
function clinic_save_doctor_order() {
    if (!wp_verify_nonce($_POST['nonce'], 'clinic_doctor_sorting_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
    }

    $order = isset($_POST['order']) ? array_map('intval', (array) $_POST['order']) : array();
    if (empty($order)) {
        wp_send_json_error(array('message' => 'Empty order'));
    }

    foreach ($order as $position => $post_id) {
        wp_update_post(array(
            'ID' => $post_id,
            'menu_order' => $position + 1
        ));
    }

    wp_send_json_success(array('message' => 'Order saved successfully'));
}
add_action('wp_ajax_clinic_save_doctor_order', 'clinic_save_doctor_order');

/**
 * Сортировка списка врачей в админке по menu_order
 */
function clinic_doctors_admin_order($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    global $pagenow;
    if ($pagenow !== 'edit.php') {
        return;
    }

    $post_type = $query->get('post_type');
    if ($post_type !== 'doctors') {
        return;
    }

    if ($query->get('orderby')) {
        return;
    }

    $query->set('orderby', 'menu_order');
    $query->set('order', 'ASC');
}
add_action('pre_get_posts', 'clinic_doctors_admin_order');

/**
 * Инициализация порядка для существующих врачей (один раз)
 */
function clinic_initialize_doctors_menu_order() {
    if (get_option('clinic_doctors_order_initialized')) {
        return;
    }

    global $wpdb;
    $has_custom_order = (int) $wpdb->get_var(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'doctors' AND menu_order > 0 LIMIT 1"
    );

    if ($has_custom_order) {
        update_option('clinic_doctors_order_initialized', 1);
        return;
    }

    $doctor_ids = get_posts(array(
        'post_type' => 'doctors',
        'post_status' => array('publish', 'draft', 'private', 'pending'),
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'ASC',
        'fields' => 'ids'
    ));

    $order = 1;
    foreach ($doctor_ids as $doctor_id) {
        wp_update_post(array(
            'ID' => $doctor_id,
            'menu_order' => $order
        ));
        $order++;
    }

    update_option('clinic_doctors_order_initialized', 1);
}
add_action('admin_init', 'clinic_initialize_doctors_menu_order');

/**
 * Установка порядка по умолчанию для нового врача
 */
function clinic_set_default_doctor_menu_order($post_id, $post, $update) {
    if ($update || $post->post_type !== 'doctors') {
        return;
    }

    static $updating = false;
    if ($updating) {
        return;
    }

    $max = get_posts(array(
        'post_type' => 'doctors',
        'post_status' => array('publish', 'draft', 'private', 'pending'),
        'posts_per_page' => 1,
        'orderby' => 'menu_order',
        'order' => 'DESC',
        'fields' => 'ids'
    ));

    $max_order = 0;
    if (!empty($max)) {
        $max_order = (int) get_post_field('menu_order', $max[0]);
    }

    $updating = true;
    wp_update_post(array(
        'ID' => $post_id,
        'menu_order' => $max_order + 1
    ));
    $updating = false;
}
add_action('wp_insert_post', 'clinic_set_default_doctor_menu_order', 10, 3);
