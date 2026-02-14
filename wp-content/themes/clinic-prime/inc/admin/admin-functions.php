<?php
/**
 * Админские функции
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Функция для добавления кастомных стилей в админку
 */
function clinic_admin_custom_styles() {
    echo '<style>
        .clinic-admin-notice {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>';
}
add_action('admin_head', 'clinic_admin_custom_styles');

/**
 * Функция для добавления уведомлений в админку
 */
function clinic_admin_notices() {
    if (isset($_GET['page']) && $_GET['page'] === 'clinic-settings') {
        echo '<div class="notice notice-info clinic-admin-notice">';
        echo '<p><strong>Добро пожаловать в настройки Clinic Prime!</strong></p>';
        echo '<p>Здесь вы можете настроить все параметры вашей клиники.</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'clinic_admin_notices');

/**
 * Функция для добавления кастомных колонок в админке
 */
function clinic_add_admin_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['clinic_meta'] = 'Дополнительно';
        }
    }
    return $new_columns;
}
add_filter('manage_posts_columns', 'clinic_add_admin_columns');

function clinic_admin_column_content($column, $post_id) {
    if ($column === 'clinic_meta') {
        $views = get_post_meta($post_id, '_post_views', true);
        echo 'Просмотров: ' . ($views ? $views : '0');
    }
}
add_action('manage_posts_custom_column', 'clinic_admin_column_content', 10, 2);

/**
 * Функция для подсчета просмотров постов
 */
function clinic_set_post_views() {
    if (is_single()) {
        $post_id = get_the_ID();
        $views = get_post_meta($post_id, '_post_views', true);
        $views = $views ? $views + 1 : 1;
        update_post_meta($post_id, '_post_views', $views);
    }
}
add_action('wp_head', 'clinic_set_post_views');

/**
 * Функция для получения популярных постов
 */
function clinic_get_popular_posts($limit = 5) {
    return get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'meta_key' => '_post_views',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'post_status' => 'publish'
    ));
}

/**
 * Добавление админского меню для управления slug'ами
 */
function clinic_add_admin_menu() {
    add_management_page(
        'Управление URL', 
        'Управление URL', 
        'manage_options', 
        'clinic-url-management', 
        'clinic_url_management_page'
    );
}
add_action('admin_menu', 'clinic_add_admin_menu');

/**
 * Страница управления URL
 */
function clinic_url_management_page() {
    if (isset($_POST['update_slugs']) && wp_verify_nonce($_POST['_wpnonce'], 'clinic_update_slugs')) {
        $updated_posts = 0;
        $updated_terms = 0;
        
        // Обновляем slug'и постов
        if (function_exists('clinic_update_existing_slugs')) {
            clinic_update_existing_slugs();
            $updated_posts = wp_count_posts()->publish + wp_count_posts('page')->publish;
        }
        
        // Обновляем slug'и терминов
        if (function_exists('clinic_update_existing_term_slugs')) {
            clinic_update_existing_term_slugs();
            $updated_terms = wp_count_terms();
        }
        
        echo '<div class="notice notice-success"><p>Обновлено постов: ' . $updated_posts . ', терминов: ' . $updated_terms . '</p></div>';
    }
    
    // Тестируем транслитерацию
    $test_title = 'О клинике';
    $test_slug = function_exists('clinic_transliterate') ? clinic_transliterate($test_title) : 'функция не найдена';
    $test_chernovik = function_exists('clinic_should_transliterate_slug') ? (clinic_should_transliterate_slug('chernovik-1', $test_title) ? 'ДА' : 'НЕТ') : 'функция не найдена';
    $test_manual = function_exists('clinic_should_transliterate_slug') ? (clinic_should_transliterate_slug('about-clinic', $test_title) ? 'ДА' : 'НЕТ') : 'функция не найдена';
    ?>
    <div class="wrap">
        <h1>Управление URL</h1>
        <p>Эта страница позволяет обновить все существующие URL, содержащие русские символы, на транслитерированные версии.</p>
        
        <div class="card">
            <h2>Тест транслитерации</h2>
            <p><strong>Заголовок:</strong> "<?php echo $test_title; ?>"</p>
            <p><strong>Результат транслитерации:</strong> "<?php echo $test_slug; ?>"</p>
            <p><strong>Транслитерировать "chernovik-1":</strong> <?php echo $test_chernovik; ?></p>
            <p><strong>Транслитерировать "about-clinic":</strong> <?php echo $test_manual; ?></p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('clinic_update_slugs'); ?>
            <p>
                <input type="submit" name="update_slugs" class="button button-primary" value="Обновить все URL" 
                       onclick="return confirm('Вы уверены, что хотите обновить все URL? Это действие нельзя отменить.');">
            </p>
        </form>
        
        <div class="card">
            <h2>Информация</h2>
            <p>Функция автоматически:</p>
            <ul>
                <li>Найдет все посты и страницы с русскими символами в URL</li>
                <li>Транслитерирует их в латиницу</li>
                <li>Проверит уникальность новых URL</li>
                <li>Обновит все ссылки в базе данных</li>
            </ul>
        </div>
    </div>
    <?php
}