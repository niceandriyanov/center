<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Разрешает загрузку SVG в WordPress.
 *
 * @param array $mimes Список разрешенных MIME-типов.
 * @return array Обновленный список MIME-типов.
 */
function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

/**
 * Добавляет заголовок XML в SVG перед загрузкой.
 *
 * @param array $file Информация о загружаемом файле.
 * @return array Исправленный файл.
 */
function fix_svg_upload($file) {
    if ($file['type'] === 'image/svg+xml') {
        $svg_content = file_get_contents($file['tmp_name']);

        // Проверяем, есть ли в файле строка <?xml
        if (strpos($svg_content, '<?xml') === false) {
            $svg_content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . $svg_content;
            file_put_contents($file['tmp_name'], $svg_content);
        }
    }
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'fix_svg_upload');

/**
 * Фикс MIME-типа SVG при загрузке.
 *
 * @param mixed $data Тип файла.
 * @param string $file Путь к файлу.
 * @param string $filename Имя файла.
 * @param array $mimes Разрешенные MIME-типы.
 * @return mixed Исправленный MIME-тип.
 */
function fix_svg_mime_type($data, $file, $filename, $mimes) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext === 'svg') {
        $data['ext']  = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'fix_svg_mime_type', 10, 4);
if( !function_exists('add_svg_to_media_library') ) {
    /**
     * Добавляет поддержку превью SVG в медиабиблиотеке WordPress.
     *
     * WordPress по умолчанию не отображает SVG-изображения в медиабиблиотеке.
     * Эта функция исправляет отображение SVG, добавляя CSS-правило,
     * которое корректирует размер превью.
     *
     * @return void
     */
    function add_svg_to_media_library()
    {
        ob_start(); ?>

        <style>
            .attachment .thumbnail img[src$=".svg"] { width: 100% !important; height: auto !important; }
        </style>

        <?php echo ob_get_clean();
    }
    add_action('admin_head', 'add_svg_to_media_library');
}


if( !function_exists('theme_load_option') ) {
    function theme_load_option()
    {
        // Проверяем, что ACF готов к работе
        if (!function_exists('get_fields')) {
            return array();
        }
        
        // Название ключа транзиента
        $transient_key = 'theme_option_fields';

        // Пытаемся получить данные из кэша
        $options = get_transient( $transient_key );
        // Если транзиент отсутствует или устарел
        if ( false === $options ) {
            // Получаем опции ACF
            $options = get_fields( 'options' ); // ACF-функция для страницы опций

            // Сохраняем в транзиент, например, на 10 дней
            set_transient( $transient_key, $options, 10 * DAY_IN_SECONDS );
        }

        // Записываем их в глобальную переменную (по аналогии с вашим кодом)
        return $options;
    }
}

/**
 * Хук для вывода верхней панели шапки
 */
function clinic_header_top_bar_action() {
    // Проверяем, что ACF готов к работе
    if (!function_exists('get_field')) {
        return;
    }
    
    // Получаем настройки из ACF (если есть)
    $top_bar_settings = get_field('header_top_bar', 'option');
    
    // Аргументы для функции
    $args = array(
        'show_links' => !empty($top_bar_settings['show_links']) || true, // По умолчанию показываем
        'menu_location' => 'top',
        'menu_class' => '',
        'container_class' => ''
    );
    
    clinic_header_top_bar($args);
}
add_action('header_top_bar', 'clinic_header_top_bar_action');

/**
 * Удаляет приставки из заголовков архивов (Рубрика:, Метка:, Автор: и т.д.)
 *
 * @param string $title Заголовок архива с приставкой.
 * @return string Заголовок архива без приставки.
 */
function remove_archive_title_prefix($title) {
    // Список приставок для удаления
    $prefixes = array(
        'Рубрика: ',
        'Метка: ',
        'Автор: ',
        'Архив: ',
        'Категория: ',
        'Tag: ',
        'Author: ',
        'Archive: ',
        'Category: ',
        'Date: ',
        'Дата: ',
        'Month: ',
        'Месяц: ',
        'Year: ',
        'Год: '
    );
    
    // Удаляем каждую приставку из заголовка
    foreach ($prefixes as $prefix) {
        if (strpos($title, $prefix) === 0) {
            $title = substr($title, strlen($prefix));
            break; // Прерываем цикл после удаления первой найденной приставки
        }
    }
    
    return $title;
}
add_filter('get_the_archive_title', 'remove_archive_title_prefix');


/**
 * Отключение стандартного поиска WordPress
 */
function disable_wp_search() {
    // Перенаправляем поисковые запросы на главную страницу
    if (is_search() && !is_admin()) {
        wp_redirect(home_url('/'));
        exit();
    }
}
add_action('template_redirect', 'disable_wp_search');

/**
 * Удаляем поисковые запросы из URL
 */
function remove_search_query_vars($vars) {
    unset($vars['s']);
    return $vars;
}
add_filter('query_vars', 'remove_search_query_vars');

/**
 * Отключаем поисковые виджеты и формы
 */
function disable_search_widgets() {
    unregister_widget('WP_Widget_Search');
}
add_action('widgets_init', 'disable_search_widgets');



/**
 * Логирование ошибок wp_mail для диагностики.
 */
function clinic_log_wp_mail_failure($wp_error) {
    if (!is_wp_error($wp_error)) {
        return;
    }

    $error_data = $wp_error->get_error_data();
    $context = array();
    if (is_array($error_data)) {
        $context = array(
            'to' => isset($error_data['to']) ? $error_data['to'] : null,
            'subject' => isset($error_data['subject']) ? $error_data['subject'] : null,
        );
    }

    $message = sprintf(
        'wp_mail failed: %s; context: %s',
        $wp_error->get_error_message(),
        wp_json_encode($context, JSON_UNESCAPED_UNICODE)
    );
    error_log($message);
}
add_action('wp_mail_failed', 'clinic_log_wp_mail_failure');