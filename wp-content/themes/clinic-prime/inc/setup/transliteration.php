<?php
/**
 * Функции транслитерации для автоматического создания ярлыков
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Транслитерация русского текста в латиницу
 */
function clinic_transliterate($string) {
    $converter = array(
        'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
        'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
        'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
        'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
        'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
        'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
        'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
        
        'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
        'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
        'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
        'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
        'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
        'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
        'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
    );
    
    $string = strtr($string, $converter);
    $string = strtolower($string);
    $string = preg_replace('/[^-a-z0-9_]+/', '-', $string);
    $string = trim($string, '-');
    
    return $string;
}

/**
 * Автоматическое создание ярлыка для постов
 */
function clinic_auto_generate_slug($post_id) {
    // Проверяем, что это не автосохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Проверяем права пользователя
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Получаем данные поста
    $post = get_post($post_id);
    
    // Проверяем, что это нужный тип поста
    $post_types = array('post', 'page', 'services', 'doctors', 'testimonials', 'faq');
    if (!in_array($post->post_type, $post_types)) {
        return;
    }
    
    // Проверяем, нужно ли транслитерировать slug
    $current_slug = $post->post_name;
    $title = $post->post_title;
    
    if (!empty($title) && clinic_should_transliterate_slug($current_slug, $title)) {
        $slug = clinic_transliterate($title);
        
        // Проверяем уникальность ярлыка
        $slug = clinic_unique_slug($slug, $post_id, $post->post_type);
        
        // Обновляем пост только если slug изменился
        if ($current_slug !== $slug) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => $slug
            ));
        }
    }
}

/**
 * Автоматическое создание ярлыка для таксономий
 */
function clinic_auto_generate_term_slug($term_id, $tt_id, $taxonomy) {
    // Получаем термин
    $term = get_term($term_id, $taxonomy);
    
    if (!$term || is_wp_error($term)) {
        return;
    }
    
    // Проверяем, нужно ли транслитерировать slug
    if (clinic_should_transliterate_term_slug($term->slug, $term->name)) {
        $slug = clinic_transliterate($term->name);
        
        // Проверяем уникальность ярлыка
        $slug = clinic_unique_term_slug($slug, $term_id, $taxonomy);
        
        // Обновляем термин
        wp_update_term($term_id, $taxonomy, array(
            'slug' => $slug
        ));
    }
}

/**
 * Проверка, нужно ли транслитерировать slug термина
 */
function clinic_should_transliterate_term_slug($slug, $name = '') {
    // Если slug пустой - транслитерируем
    if (empty($slug)) {
        return true;
    }
    
    // Если slug содержит кириллицу - транслитерируем
    if (clinic_contains_cyrillic(urldecode($slug))) {
        return true;
    }
    
    // Если название содержит кириллицу, а slug пустой - транслитерируем
    if (!empty($name) && clinic_contains_cyrillic($name) && empty($slug)) {
        return true;
    }
    
    return false;
}

/**
 * Проверка уникальности ярлыка для постов
 */
function clinic_unique_slug($slug, $post_id, $post_type) {
    $original_slug = $slug;
    $counter = 1;
    
    while (clinic_slug_exists($slug, $post_id, $post_type)) {
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Проверка уникальности ярлыка для терминов
 */
function clinic_unique_term_slug($slug, $term_id, $taxonomy) {
    $original_slug = $slug;
    $counter = 1;
    
    while (clinic_term_slug_exists($slug, $term_id, $taxonomy)) {
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Проверка существования ярлыка для постов
 */
function clinic_slug_exists($slug, $post_id, $post_type) {
    global $wpdb;
    
    $query = $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s AND ID != %d",
        $slug,
        $post_type,
        $post_id
    );
    
    return $wpdb->get_var($query);
}

/**
 * Проверка существования ярлыка для терминов
 */
function clinic_term_slug_exists($slug, $term_id, $taxonomy) {
    global $wpdb;
    
    $query = $wpdb->prepare(
        "SELECT term_id FROM {$wpdb->terms} t 
         INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
         WHERE t.slug = %s AND tt.taxonomy = %s AND t.term_id != %d",
        $slug,
        $taxonomy,
        $term_id
    );
    
    return $wpdb->get_var($query);
}

/**
 * Принудительное обновление ярлыка для существующих постов
 */
function clinic_update_existing_slugs() {
    $post_types = array('post', 'page', 'services', 'doctors', 'testimonials', 'faq');
    
    foreach ($post_types as $post_type) {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'numberposts' => -1,
            'post_status' => 'publish'
        ));
        
        foreach ($posts as $post) {
            if (empty($post->post_name) || clinic_contains_cyrillic($post->post_title)) {
                clinic_auto_generate_slug($post->ID);
            }
        }
    }
}

/**
 * Принудительное обновление ярлыков для существующих терминов
 */
function clinic_update_existing_term_slugs() {
    $taxonomies = array('category', 'post_tag', 'service_category', 'doctor_specialty', 'doctor_diseases', 'faq_category');
    
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ));
        
        foreach ($terms as $term) {
            if (empty($term->slug) || clinic_contains_cyrillic($term->name)) {
                clinic_auto_generate_term_slug($term->term_id, 0, $taxonomy);
            }
        }
    }
}

/**
 * Проверка на наличие кириллицы в тексте
 */
function clinic_contains_cyrillic($text) {
    return preg_match('/[а-яё]/ui', $text);
}

/**
 * Проверка на стандартные WordPress slug'и (черновик, автосохранение и т.д.)
 */
function clinic_is_wordpress_default_slug($slug) {
    $default_slugs = array(
        'chernovik',
        'auto-draft',
        'revision',
        'attachment'
    );
    
    // Проверяем точное совпадение или с числом в конце
    foreach ($default_slugs as $default_slug) {
        if ($slug === $default_slug || preg_match('/^' . preg_quote($default_slug, '/') . '-\d+$/', $slug)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Проверка, нужно ли транслитерировать slug
 */
function clinic_should_transliterate_slug($slug, $title = '') {
    // Если slug пустой - транслитерируем
    if (empty($slug)) {
        return true;
    }
    
    // Если slug содержит кириллицу - транслитерируем
    if (clinic_contains_cyrillic(urldecode($slug))) {
        return true;
    }
    
    // Если slug является стандартным WordPress slug'ом - транслитерируем
    if (clinic_is_wordpress_default_slug($slug)) {
        return true;
    }
    
    // Если заголовок содержит кириллицу, а slug пустой или стандартный - транслитерируем
    if (!empty($title) && clinic_contains_cyrillic($title) && (empty($slug) || clinic_is_wordpress_default_slug($slug))) {
        return true;
    }
    
    return false;
}

/**
 * Фильтр для автоматической транслитерации slug'ов постов
 */
function clinic_sanitize_title($title, $raw_title, $context) {
    // Проверяем, что это создание slug'а
    if ($context === 'save' && !empty($raw_title)) {
        // Проверяем, содержит ли заголовок кириллицу
        if (clinic_contains_cyrillic($raw_title)) {
            return clinic_transliterate($raw_title);
        }
    }
    return $title;
}

/**
 * Фильтр для обработки slug'а при создании поста
 */
function clinic_pre_post_slug($slug, $post_ID, $post_status, $post_type) {
    // Получаем заголовок поста
    $post = get_post($post_ID);
    if ($post && !empty($post->post_title)) {
        $title = $post->post_title;
        
        // Проверяем, нужно ли транслитерировать slug
        if (clinic_should_transliterate_slug($slug, $title)) {
            $new_slug = clinic_transliterate($title);
            $new_slug = clinic_unique_slug($new_slug, $post_ID, $post_type);
            return $new_slug;
        }
    }
    
    return $slug;
}

/**
 * Фильтр для автоматической транслитерации slug'ов терминов
 */
function clinic_sanitize_term_slug($slug, $term = '', $taxonomy = '') {
    // Защита от ошибок - проверяем количество переданных аргументов
    $args_count = func_num_args();
    
    // Если передан только slug (2 аргумента), обрабатываем его напрямую
    if ($args_count == 2) {
        if (!empty($slug) && clinic_contains_cyrillic(urldecode($slug))) {
            return clinic_transliterate($slug);
        }
        return $slug;
    }
    
    // Если переданы все параметры (3 аргумента), используем название термина
    if ($args_count == 3 && !empty($term) && clinic_contains_cyrillic($term)) {
        return clinic_transliterate($term);
    }
    
    return $slug;
}

/**
 * Обработка slug'а при сохранении поста
 */
function clinic_save_post_slug($post_id, $post, $update) {
    // Проверяем, что это не автосохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Проверяем права пользователя
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Проверяем, что это нужный тип поста
    $post_types = array('post', 'page', 'services', 'doctors', 'testimonials', 'faq');
    if (!in_array($post->post_type, $post_types)) {
        return;
    }
    
    // Проверяем, нужно ли транслитерировать slug
    if (clinic_should_transliterate_slug($post->post_name, $post->post_title)) {
        $new_slug = clinic_transliterate($post->post_title);
        $new_slug = clinic_unique_slug($new_slug, $post_id, $post->post_type);
        
        // Обновляем пост только если slug изменился
        if ($post->post_name !== $new_slug) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => $new_slug
            ));
        }
    }
}

// Хуки для автоматического создания ярлыков
add_action('save_post', 'clinic_auto_generate_slug', 10, 1);
add_action('save_post', 'clinic_save_post_slug', 10, 3);
add_action('wp_insert_post', 'clinic_auto_generate_slug', 10, 1);
add_action('created_term', 'clinic_auto_generate_term_slug', 10, 3);
add_action('edited_term', 'clinic_auto_generate_term_slug', 10, 3);

/**
 * AJAX обработчик для транслитерации slug'а
 */
function clinic_ajax_transliterate_slug() {
    check_ajax_referer('clinic_transliterate_nonce', 'nonce');
    
    $text = sanitize_text_field($_POST['text']);
    $transliterated = clinic_transliterate($text);
    
    wp_send_json_success(array('slug' => $transliterated));
}
add_action('wp_ajax_clinic_transliterate_slug', 'clinic_ajax_transliterate_slug');

// Фильтры для автоматической транслитерации
add_filter('sanitize_title', 'clinic_sanitize_title', 10, 3);
add_filter('pre_post_slug', 'clinic_pre_post_slug', 10, 4);
add_filter('pre_term_slug', 'clinic_sanitize_term_slug', 10, 3);

/**
 * Принудительное обновление slug'а для конкретного поста
 */
function clinic_force_update_post_slug($post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return false;
    }
    
    $title = $post->post_title;
    if (!empty($title)) {
        $new_slug = clinic_transliterate($title);
        $new_slug = clinic_unique_slug($new_slug, $post_id, $post->post_type);
        
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_name' => $new_slug
        ));
        
        return !is_wp_error($result);
    }
    
    return false;
}

// Функции для принудительного обновления (можно вызвать вручную)
// clinic_update_existing_slugs();
// clinic_update_existing_term_slugs();
