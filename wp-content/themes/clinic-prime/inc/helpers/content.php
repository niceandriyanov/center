<?php
/**
 * Функции для работы с контентом
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Кастомизация вывода excerpt
 */
function clinic_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'clinic_excerpt_length');

function clinic_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'clinic_excerpt_more');

/**
 * Кастомизация пагинации
 */
function clinic_pagination() {
    global $wp_query;
    
    $big = 999999999;
    
    $paginate_links = paginate_links(array(
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => max(1, get_query_var('paged')),
        'total'     => $wp_query->max_num_pages,
        'prev_text' => '&laquo; Предыдущая',
        'next_text' => 'Следующая &raquo;',
        'type'      => 'array',
    ));
    
    if ($paginate_links) {
        echo '<nav class="pagination">';
        echo '<ul>';
        foreach ($paginate_links as $link) {
            echo '<li>' . $link . '</li>';
        }
        echo '</ul>';
        echo '</nav>';
    }
}

/**
 * Функция для расчета времени чтения
 */
function clinic_calculate_reading_time($post_id) {
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // 200 слов в минуту
    return $reading_time;
}

/**
 * Функция для форматирования телефона
 */
function clinic_format_phone($phone) {
    return preg_replace('/[^0-9+()-]/', '', $phone);
}

/**
 * Функция для проверки рабочего времени
 */
function clinic_is_open() {
    $hours = get_theme_mod('clinic_hours', '');
    if (empty($hours)) {
        return true; // По умолчанию считаем, что клиника открыта
    }
    
    // Здесь можно добавить логику проверки времени работы
    return true;
}

/**
 * Функция для форматирования даты из формата DD.MM.YYYY в формат DD MMM YYYY
 * 
 * @param string $date Дата в формате DD.MM.YYYY
 * @return string Дата в формате DD MMM YYYY (например: 15 авг 2025)
 */
function clinic_format_date($date) {
    if (empty($date)) {
        return '';
    }
    
    // Массив названий месяцев на русском языке
    $months = array(
        '01' => 'янв',
        '02' => 'фев', 
        '03' => 'мар',
        '04' => 'апр',
        '05' => 'май',
        '06' => 'июн',
        '07' => 'июл',
        '08' => 'авг',
        '09' => 'сен',
        '10' => 'окт',
        '11' => 'ноя',
        '12' => 'дек'
    );
    
    // Разбиваем дату на части
    $date_parts = explode('.', $date);
    
    // Проверяем, что дата имеет правильный формат
    if (count($date_parts) !== 3) {
        return $date; // Возвращаем исходную дату, если формат неправильный
    }
    
    $day = $date_parts[0];
    $month = $date_parts[1];
    $year = $date_parts[2];
    
    // Проверяем, что месяц существует в массиве
    if (!isset($months[$month])) {
        return $date; // Возвращаем исходную дату, если месяц неправильный
    }
    
    // Убираем ведущий ноль с дня, если он есть
    $day = ltrim($day, '0');
    if (empty($day)) {
        $day = '0';
    }
    
    return $day . ' ' . $months[$month] . ' ' . $year;
}