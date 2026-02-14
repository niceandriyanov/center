<?php
/**
 * Шаблон для верхнего меню
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

// Значения по умолчанию
$defaults = array(
    'logo'              => '',
    'top_bar'           => true,
    'show_contacts'     => false,
    'address'           => array(),
    'phone'             => array(),
    'btn_appointment'   => array(),
);

// Объединяем переданные аргументы с значениями по умолчанию
$args = wp_parse_args($args ?? array(), $defaults);
?>

<div class="headerTop">
    <div class="container">
        <div class="headerTopWrap">
            <?php if ($args['top_bar']): ?>
            <div class="headerTopLeft">
                <?php do_action('header_top_bar'); ?>
            </div>
            <?php endif; ?>            
        </div>
    </div>
</div>