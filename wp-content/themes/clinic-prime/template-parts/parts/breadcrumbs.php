<?php
/**
 * Шаблон для вывода breadcrumbs
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Получаем breadcrumbs для текущей страницы
$breadcrumbs = clinic_get_breadcrumbs();

// Если breadcrumbs пустые, не выводим ничего
if (empty($breadcrumbs)) {
    return;
}
$additional_class = !empty($additional_class) ? $additional_class : '';
clinic_breadcrumbs($breadcrumbs, $additional_class);
