<?php
/**
 * Кастомные Walkers для меню
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Кастомный Walker для меню верхней панели
 * Выводит ссылки в формате: <a href="" target="_blank" class="withLine">Текст</a>
 * Без обертки ul/li
 */
class Clinic_Walker_Top_Menu extends Walker_Nav_Menu {
    
    /**
     * Начало списка - ничего не выводим
     */
    function start_lvl(&$output, $depth = 0, $args = null) {
        // Не выводим ul
    }
    
    /**
     * Конец списка - ничего не выводим
     */
    function end_lvl(&$output, $depth = 0, $args = null) {
        // Не выводим /ul
    }
    
    /**
     * Начало элемента - ничего не выводим
     */
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        // Не выводим li
    }
    
    /**
     * Конец элемента - ничего не выводим
     */
    function end_el(&$output, $item, $depth = 0, $args = null) {
        // Не выводим /li
    }
    
    /**
     * Переопределяем метод для вывода только ссылок
     */
    function walk($elements, $max_depth, ...$args) {
        $output = '';
        
        foreach ($elements as $item) {
            // Получаем target из custom field или meta
            $target = get_post_meta($item->ID, '_menu_item_target', true);
            $target_attr = !empty($target) ? ' target="' . esc_attr($target) . '"' : '';
            
            // Формируем ссылку по шаблону
            $output .= '<a href="' . esc_url($item->url) . '"' . $target_attr . ' class="withLine">' . esc_html($item->title) . '</a>';
        }
        
        return $output;
    }
}

/**
 * Кастомный Walker для меню верхней панели
 * Выводит ссылки в формате: <a href="" target="_blank" class="withLine">Текст</a>
 * Без обертки ul/li
 */
class Clinic_Walker_Footer_Menu extends Walker_Nav_Menu {
    
    /**
     * Начало списка - ничего не выводим
     */
    function start_lvl(&$output, $depth = 0, $args = null) {
        // Не выводим ul
    }
    
    /**
     * Конец списка - ничего не выводим
     */
    function end_lvl(&$output, $depth = 0, $args = null) {
        // Не выводим /ul
    }
    
    /**
     * Начало элемента - ничего не выводим
     */
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        // Не выводим li
    }
    
    /**
     * Конец элемента - ничего не выводим
     */
    function end_el(&$output, $item, $depth = 0, $args = null) {
        // Не выводим /li
    }
    
    /**
     * Переопределяем метод для вывода только ссылок
     */
    function walk($elements, $max_depth, ...$args) {
        $output = '';
        
        foreach ($elements as $item) {
            // Получаем target из custom field или meta
            $target = get_post_meta($item->ID, '_menu_item_target', true);
            $target_attr = !empty($target) ? ' target="' . esc_attr($target) . '"' : '';
            
            // Формируем ссылку по шаблону
            $output .= '<a href="' . esc_url($item->url) . '"' . $target_attr . '>' . esc_html($item->title) . '</a>';
        }
        
        return $output;
    }
}

/**
 * Кастомный Walker для основного меню
 * Выводит стандартное меню с поддержкой вложений
 */
class Clinic_Walker_Main_Menu extends Walker_Nav_Menu {
    
    /**
     * Начало элемента
     */
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        
        // Добавляем класс для активного элемента
        if (in_array('current-menu-item', $classes)) {
            $classes[] = 'active';
        }
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
        
        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';
        
        $output .= '<li' . $id . $class_names . '>';
        
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        
        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;
        
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
    
    /**
     * Конец элемента
     */
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
    
    /**
     * Начало списка
     */
    function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $submenu = ($depth > 0) ? ' sub-menu' : '';
        $output .= "\n$indent<ul class=\"dropdown-menu$submenu\">\n";
    }
    
    /**
     * Конец списка
     */
    function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
}

/**
 * Кастомный Walker для мобильного меню
 * Выводит меню с аккордеоном для мобильных устройств
 */
class Clinic_Walker_Mobile_Menu extends Walker_Nav_Menu {
    
    /**
     * Начало элемента
     */
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        
        // Добавляем класс для активного элемента
        if (in_array('current-menu-item', $classes)) {
            $classes[] = 'active';
        }
        
        // Добавляем класс для элементов с подменю
        if (in_array('menu-item-has-children', $classes)) {
            $classes[] = 'has-submenu';
        }
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
        
        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';
        
        $output .= '<li' . $id . $class_names . '>';
        
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        
        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a>';
        
        // Добавляем кнопку для подменю
        if (in_array('menu-item-has-children', $classes)) {
            $item_output .= '<button class="submenu-toggle" aria-label="Открыть подменю"><span></span></button>';
        }
        
        $item_output .= $args->after;
        
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
    
    /**
     * Конец элемента
     */
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
    
    /**
     * Начало списка
     */
    function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $submenu = ($depth > 0) ? ' sub-submenu' : ' submenu';
        $output .= "\n$indent<ul class=\"mobile$submenu\">\n";
    }
    
    /**
     * Конец списка
     */
    function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
}
