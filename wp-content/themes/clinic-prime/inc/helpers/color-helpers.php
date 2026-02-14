<?php
/**
 * Функции-хелперы для работы с цветами
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получает HTML для отображения цвета по значению
 * 
 * @param string $color_value Значение цвета из поля
 * @return string HTML код для отображения цвета
 */
function get_color_display($color_value) {
    $color_map = array(
        '1' => '<span style="width: 24px;height:24px;background:#000;display:inline-block;border-radius:4px;border:1px solid #ddd;"></span>',
        '2' => '<span style="width: 24px;height:24px;background:#ff0000;display:inline-block;border-radius:4px;border:1px solid #ddd;"></span>',
        '3' => '<span style="width: 24px;height:24px;background:#00ff00;display:inline-block;border-radius:4px;border:1px solid #ddd;"></span>',
        '4' => '<span style="width: 24px;height:24px;background:#0000ff;display:inline-block;border-radius:4px;border:1px solid #ddd;"></span>',
        '5' => '<span style="width: 24px;height:24px;background:#ffff00;display:inline-block;border-radius:4px;border:1px solid #ddd;"></span>',
        '6' => '<span style="width: 24px;height:24px;background:#ff00ff;display:inline-block;border-radius:4px;border:1px solid #ddd;"></span>',
        '7' => '<span style="width: 24px;height:24px;background:#00ffff;display:inline-block;border-radius:4px;border:1px solid #ddd;"></span>',
        '8' => '<span style="width: 24px;height:24px;background:#808080;display:inline-block;border-radius:4px;border:1px solid #ddd;"></span>',
    );
    
    return isset($color_map[$color_value]) ? $color_map[$color_value] : $color_value;
}

/**
 * Выводит HTML для отображения цвета
 * 
 * @param string $color_value Значение цвета из поля
 */
function the_color_display($color_value) {
    echo get_color_display($color_value);
}

/**
 * Получает цвет по значению поля ACF
 * 
 * @param string $field_name Имя поля ACF
 * @param int|false $post_id ID поста
 * @return string HTML код для отображения цвета
 */
function get_field_color_display($field_name, $post_id = false) {
    $color_value = get_field($field_name, $post_id);
    return get_color_display($color_value);
}

/**
 * Выводит цвет по значению поля ACF
 * 
 * @param string $field_name Имя поля ACF
 * @param int|false $post_id ID поста
 */
function the_field_color_display($field_name, $post_id = false) {
    echo get_field_color_display($field_name, $post_id);
}
