<?php
/**
 * Drag & Drop сортировка таксономий в админке
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Отображение data-атрибутов в колонке name
 */
function clinic_show_taxonomy_sort_column($value, $column_name, $term_id) {
    if ($column_name === 'name') {
        // Получаем текущий порядок
        $order = get_term_meta($term_id, 'taxonomy_order', true);
        $order = $order ? intval($order) : 0;
        
        // Добавляем data-атрибуты к ячейке имени
        $value = '<div class="term-row" data-term-id="' . $term_id . '" data-order="' . $order . '">' . $value . '</div>';
    }
    
    return $value;
}

/**
 * Добавление скриптов и стилей для сортировки
 */
function clinic_enqueue_taxonomy_sorting_assets($hook) {
    // Проверяем, что мы на странице таксономии
    if (!in_array($hook, array('edit-tags.php', 'term.php'))) {
        return;
    }
    
    wp_enqueue_script(
        'clinic-taxonomy-sorting',
        get_template_directory_uri() . '/assets/js/admin/taxonomy-sorting.js',
        array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-mouse', 'jquery-ui-draggable'),
        '1.0.0',
        true
    );
    
    wp_enqueue_style(
        'clinic-taxonomy-sorting',
        get_template_directory_uri() . '/assets/css/admin/taxonomy-sorting.css',
        array(),
        '1.0.0'
    );
    
    // Получаем таксономию из URL
    $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : '';
    
    // Передаем данные в JavaScript
    $localize_data = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('clinic_taxonomy_sorting_nonce'),
        'taxonomy' => $taxonomy,
        'strings' => array(
            'saving' => __('Сохранение порядка...', 'clinic-prime'),
            'saved' => __('Порядок сохранен!', 'clinic-prime'),
            'error' => __('Ошибка сохранения!', 'clinic-prime')
        )
    );
    
    wp_localize_script('clinic-taxonomy-sorting', 'clinicTaxonomySorting', $localize_data);
    
    // Добавляем хуки для текущей таксономии
    add_filter('manage_' . $taxonomy . '_custom_column', 'clinic_show_taxonomy_sort_column', 10, 3);
    // Также добавляем общий хук для всех таксономий
    add_filter('manage_edit-tags_custom_column', 'clinic_show_taxonomy_sort_column', 10, 3);
    
}




/**
 * AJAX обработчик для сохранения порядка
 */
function clinic_save_taxonomy_order() {
    // Проверяем nonce
    if (!wp_verify_nonce($_POST['nonce'], 'clinic_taxonomy_sorting_nonce')) {
        wp_die('Security check failed');
    }
    
    // Проверяем права пользователя
    if (!current_user_can('manage_categories')) {
        wp_die('Insufficient permissions');
    }
    
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $order = array_map('intval', $_POST['order']);
    
    // Сохраняем порядок для каждого термина
    foreach ($order as $position => $term_id) {
        update_term_meta($term_id, 'taxonomy_order', $position+1);
    }
    
    wp_send_json_success(array('message' => 'Order saved successfully'));
}

/**
 * Установка порядка по умолчанию для новых терминов
 */
function clinic_set_default_taxonomy_order($term_id, $tt_id, $taxonomy) {
    // Получаем максимальный порядок
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'meta_key' => 'taxonomy_order',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'number' => 1
    ));
    
    $max_order = 1;
    if (!empty($terms)) {
        $max_order = get_term_meta($terms[0]->term_id, 'taxonomy_order', true);
        $max_order = $max_order ? intval($max_order) : 1;
    }
    
    // Устанавливаем порядок для нового термина
    update_term_meta($term_id, 'taxonomy_order', $max_order + 1);
}

/**
 * Модификация запроса для сортировки по порядку
 */
add_filter('terms_clauses', function( $clauses, $taxonomies, $args ) {
    // Только в админке
    if ( ! is_admin() ) return $clauses;
    
    global $pagenow;
    if ( $pagenow !== 'edit-tags.php' ) return $clauses;

    // Поддерживаемые таксономии
    $supported_taxonomies = array('category', 'post_tag', 'service_category', 'doctor_specialty', 'doctor_diseases', 'faq_category');
    
    // Получаем текущую таксономию
    $taxonomy = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';
    
    if ( ! $taxonomy || ! in_array($taxonomy, $supported_taxonomies, true) ) {
        return $clauses;
    }

    // Если пользователь выбрал другую сортировку, не вмешиваемся
    if ( isset($_GET['orderby']) && $_GET['orderby'] !== '' ) {
        return $clauses;
    }

    // Не добавляем сортировку для COUNT запросов
    if ( isset($args['count']) && $args['count'] ) {
        return $clauses;
    }
    
    // Не добавляем сортировку если fields = count
    if ( isset($args['fields']) && $args['fields'] === 'count' ) {
        return $clauses;
    }

    global $wpdb;
    $alias = 'tm_ord';
    
    // Проверяем, что join еще не добавлен
    if ( strpos($clauses['join'], $alias) === false ) {
        // Добавляем JOIN для получения meta_value
        $clauses['join'] .= " LEFT JOIN {$wpdb->termmeta} AS {$alias} ON (t.term_id = {$alias}.term_id AND {$alias}.meta_key = 'taxonomy_order')";
    }
    
    // Добавляем сортировку по meta_value (с fallback на name)
    $clauses['orderby'] = " ORDER BY CAST(COALESCE({$alias}.meta_value, '999999') AS UNSIGNED) ";
    


    return $clauses;
}, 10, 3);

/**
 * Принудительное обновление порядка для существующих терминов
 */
function clinic_update_existing_taxonomy_orders() {
    $taxonomies = array('category', 'post_tag', 'service_category', 'doctor_specialty', 'doctor_diseases', 'faq_category');
    
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        $order = 1;
        foreach ($terms as $term) {
            $existing_order = get_term_meta($term->term_id, 'taxonomy_order', true);
            if (!$existing_order) {
                update_term_meta($term->term_id, 'taxonomy_order', $order);
            }
            $order++;
        }
    }
}

// ХУКИ

// Также добавляем для стандартных таксономий
add_filter('manage_edit-tags_custom_column', 'clinic_show_taxonomy_sort_column', 10, 3);

// Подключение скриптов и стилей
add_action('admin_enqueue_scripts', 'clinic_enqueue_taxonomy_sorting_assets');

// AJAX обработчик
add_action('wp_ajax_clinic_save_taxonomy_order', 'clinic_save_taxonomy_order');

// Установка порядка по умолчанию
add_action('created_term', 'clinic_set_default_taxonomy_order', 10, 3);

// Раскомментируйте строку ниже для принудительного обновления порядка
//add_action('admin_init', 'clinic_update_existing_taxonomy_orders');
