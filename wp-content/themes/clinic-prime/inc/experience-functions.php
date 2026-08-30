<?php
/**
 * Функции для работы со стажем и грейдом специалистов
 *
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получает название грейда специалиста
 *
 * @param int $post_id ID поста специалиста
 * @return string
 */
function get_doctor_grade_name($post_id) {
    $grade = get_field('doctor_grade', $post_id);

    if ($grade instanceof WP_Term) {
        return $grade->name;
    }

    if (is_array($grade) && !empty($grade)) {
        $term = reset($grade);
        if ($term instanceof WP_Term) {
            return $term->name;
        }
    }

    $terms = wp_get_post_terms($post_id, 'doctor_grade', array('number' => 1));

    if (!is_wp_error($terms) && !empty($terms)) {
        return $terms[0]->name;
    }

    return '';
}

/**
 * Получает стаж работы специалиста в годах
 *
 * @param int $post_id ID поста специалиста
 * @return int|false Количество лет стажа или false если не удалось рассчитать
 */
function get_doctor_experience_years($post_id) {
    $age_work = get_field('age_work', $post_id);

    if (!$age_work) {
        return false;
    }

    $current_year = date('Y');
    $experience_years = $current_year - $age_work;

    return $experience_years > 0 ? $experience_years : false;
}

/**
 * Склонение слова «год» для русского языка
 *
 * @param int $years
 * @return string
 */
function clinic_doctor_experience_years_word($years) {
    if ($years == 1) {
        return 'год';
    }

    if ($years >= 11 && $years <= 14) {
        return 'лет';
    }

    if ($years % 10 == 1) {
        return 'год';
    }

    if ($years % 10 >= 2 && $years % 10 <= 4) {
        return 'года';
    }

    return 'лет';
}

/**
 * Получает текст метки на карточке: грейд и/или стаж
 *
 * @param int $post_id ID поста специалиста
 * @return string|false HTML код с текстом метки или false если нечего показать
 */
function get_doctor_experience_text($post_id) {
    $grade_name = get_doctor_grade_name($post_id);
    $experience_years = get_doctor_experience_years($post_id);
    $parts = array();

    if ($grade_name) {
        $parts[] = $grade_name;
    }

    if ($experience_years !== false) {
        $parts[] = sprintf(
            'стаж: %d %s',
            $experience_years,
            clinic_doctor_experience_years_word($experience_years)
        );
    }

    if (empty($parts)) {
        return false;
    }

    return sprintf(
        '<div class="specItemLabel">%s</div>',
        esc_html(implode(', ', $parts))
    );
}

/**
 * Выводит HTML код метки специалиста
 *
 * @param int $post_id ID поста специалиста
 * @return void
 */
function the_doctor_experience($post_id) {
    $experience_text = get_doctor_experience_text($post_id);
    if ($experience_text) {
        echo $experience_text;
    }
}
