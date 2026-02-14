<?php
/**
 * Функции для работы со стажем специалистов
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
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
 * Получает текст стажа с правильными окончаниями
 * 
 * @param int $post_id ID поста специалиста
 * @return string|false HTML код с текстом стажа или false если стаж не удалось рассчитать
 */
function get_doctor_experience_text($post_id) {
    $experience_years = get_doctor_experience_years($post_id);
    
    if ($experience_years === false) {
        return false;
    }
    
    // Правильные окончания для русского языка
    if ($experience_years == 1) {
        $years_text = 'год';
    } elseif ($experience_years >= 11 && $experience_years <= 14) {
        $years_text = 'лет';
    } elseif ($experience_years % 10 == 1) {
        $years_text = 'год';
    } elseif ($experience_years % 10 >= 2 && $experience_years % 10 <= 4) {
        $years_text = 'года';
    } else {
        $years_text = 'лет';
    }
    
    return sprintf('<div class="specItemLabel">Стаж: %d %s</div>', $experience_years, $years_text);
}

/**
 * Выводит HTML код стажа специалиста
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
