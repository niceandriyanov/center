<?php
/**
 * Функции для работы с breadcrumbs
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить breadcrumbs для текущей страницы
 * 
 * @return array Массив с элементами breadcrumbs
 */
function clinic_get_breadcrumbs() {
    $breadcrumbs = array();
    
    // Главная страница
    $breadcrumbs[] = array(
        'title' => 'Главная',
        'url' => home_url('/'),
        'is_current' => is_front_page()
    );
    
    if (is_front_page()) {
        return $breadcrumbs;
    }
    
    if (is_home()) {
        $breadcrumbs[] = array(
            'title' => 'Блог',
            'url' => '',
            'is_current' => true
        );
        return $breadcrumbs;
    }
    
    if (is_page()) {
        $post = get_post();
        $ancestors = array_reverse(get_post_ancestors($post));
        
        foreach ($ancestors as $ancestor_id) {
            $ancestor = get_post($ancestor_id);
            $breadcrumbs[] = array(
                'title' => get_the_title($ancestor),
                'url' => get_permalink($ancestor),
                'is_current' => false
            );
        }
        
        $breadcrumbs[] = array(
            'title' => get_the_title(),
            'url' => '',
            'is_current' => true
        );
    }
    
    if (is_single()) {
        $post_type = get_post_type();
        
        if ($post_type === 'post') {
            /*$categories = get_the_category();
            if (!empty($categories)) {
                $category = $categories[0];
                $breadcrumbs[] = array(
                    'title' => $category->name,
                    'url' => get_category_link($category),
                    'is_current' => false
                );
            }*/
        } else {
            $post_type_obj = get_post_type_object($post_type);
            if ($post_type_obj) {
                
                if( $post_type === 'doctors' ) {
                    $url = get_field('theme_doctors_page', 'option');
                }
                else {
                    $url = get_post_type_archive_link($post_type);
                }

                $breadcrumbs[] = array(
                    'title' => $post_type_obj->labels->name,
                    'url' => $url,
                    'is_current' => false
                );
            }
        }
        
        $breadcrumbs[] = array(
            'title' => get_the_title(),
            'url' => '',
            'is_current' => true
        );
    }
    
    if (is_category()) {
        $category = get_queried_object();
        $ancestors = array_reverse(get_ancestors($category->term_id, 'category'));
        
        foreach ($ancestors as $ancestor_id) {
            $ancestor = get_term($ancestor_id, 'category');
            $breadcrumbs[] = array(
                'title' => $ancestor->name,
                'url' => get_term_link($ancestor),
                'is_current' => false
            );
        }
        
        $breadcrumbs[] = array(
            'title' => $category->name,
            'url' => '',
            'is_current' => true
        );
    }
    
    if (is_tag()) {
        $tag = get_queried_object();
        $breadcrumbs[] = array(
            'title' => $tag->name,
            'url' => '',
            'is_current' => true
        );
    }
    
    if (is_archive()) {
        if (is_post_type_archive()) {
            $post_type_obj = get_post_type_object(get_post_type());
            if ($post_type_obj) {
                $breadcrumbs[] = array(
                    'title' => $post_type_obj->labels->name,
                    'url' => '',
                    'is_current' => true
                );
            }
        } /*else {
            $breadcrumbs[] = array(
                'title' => 'Архив',
                'url' => '',
                'is_current' => true
            );
        }*/
    }
    
    if (is_search()) {
        $breadcrumbs[] = array(
            'title' => 'Поиск: ' . get_search_query(),
            'url' => '',
            'is_current' => true
        );
    }
    
    if (is_404()) {
        $breadcrumbs[] = array(
            'title' => 'Страница не найдена',
            'url' => '',
            'is_current' => true
        );
    }
    
    return $breadcrumbs;
}

/**
 * Вывести HTML breadcrumbs
 * 
 * @param array $breadcrumbs Массив breadcrumbs
 * @param string $class Дополнительные CSS классы
 */
function clinic_breadcrumbs($breadcrumbs = null, $class = '') {
    if ($breadcrumbs === null) {
        $breadcrumbs = clinic_get_breadcrumbs();
    }
    
    if (empty($breadcrumbs)) {
        return;
    }
    
    $class = trim('breadcrumbs ' . $class);
    
    echo '<div class="' . esc_attr($class) . '">';
    echo '<ul class="list listFlex">';
    
    foreach ($breadcrumbs as $index => $item) {
        echo '<li>';
        
        if ($item['is_current']) {
            echo '<span>' . esc_html($item['title']) . '</span>';
        } else {
            echo '<a href="' . esc_url($item['url']) . '">' . esc_html($item['title']) . '</a>';
        }
        
        echo '</li>';
    }
    
    echo '</ul>';
    echo '</div>';
}

/**
 * Получить breadcrumbs для конкретной страницы
 * 
 * @param int $post_id ID записи
 * @return array Массив breadcrumbs
 */
function clinic_get_breadcrumbs_for_post($post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return array();
    }
    
    $breadcrumbs = array();
    
    // Главная страница
    $breadcrumbs[] = array(
        'title' => 'Главная',
        'url' => home_url('/'),
        'is_current' => false
    );
    
    if ($post->post_type === 'page') {
        $ancestors = array_reverse(get_post_ancestors($post));
        
        foreach ($ancestors as $ancestor_id) {
            $ancestor = get_post($ancestor_id);
            $breadcrumbs[] = array(
                'title' => get_the_title($ancestor),
                'url' => get_permalink($ancestor),
                'is_current' => false
            );
        }
    }
    
    $breadcrumbs[] = array(
        'title' => get_the_title($post),
        'url' => '',
        'is_current' => true
    );
    
    return $breadcrumbs;
}
