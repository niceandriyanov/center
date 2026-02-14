<?php
/**
 * Инициализация всех модулей темы
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

//Подключение модуля транслитерации
require_once THEME_DIR . '/inc/setup/transliteration.php';

// Подключение стилей и скриптов
require_once THEME_DIR . '/inc/enqueue/css.php';
require_once THEME_DIR . '/inc/enqueue/js.php';
require_once THEME_DIR . '/inc/enqueue/performance-settings.php';

// Подключение настройки темы
require_once THEME_DIR . '/inc/setup/theme-setup.php';
require_once THEME_DIR . '/inc/setup/widgets.php';

// Подключение виджетов
require_once THEME_DIR . '/inc/widgets/contact-widget.php';

// Подключение вспомогательных функций
require_once THEME_DIR . '/inc/helpers/content.php';
require_once THEME_DIR . '/inc/helpers/ajax.php';
require_once THEME_DIR . '/inc/helpers/seo.php';
require_once THEME_DIR . '/inc/helpers/shortcodes.php';
require_once THEME_DIR . '/inc/helpers/hooks.php';
require_once THEME_DIR . '/inc/helpers/color-helpers.php';

// Подключение админских функций
require_once THEME_DIR . '/inc/admin/admin-functions.php';
require_once THEME_DIR . '/inc/admin/admin-scripts.php';
require_once THEME_DIR . '/inc/admin/doctor-sorting.php';

// Подключение сортировки таксономий
require_once THEME_DIR . '/inc/admin/taxonomy-sorting.php';

// Подключение кастомных типов записей
require_once THEME_DIR . '/inc/post-types.php';

// Подключение кастомайзера
require_once THEME_DIR . '/inc/customizer.php';

// Подключение функций шаблонов
require_once THEME_DIR . '/inc/template-functions/breadcrumbs.php';
require_once THEME_DIR . '/inc/template-functions/header-functions.php';
require_once THEME_DIR . '/inc/template-functions/walkers.php';
require_once THEME_DIR . '/inc/template-functions.php';

// Подключение функций для работы со стажем специалистов
require_once THEME_DIR . '/inc/experience-functions.php';

require_once THEME_DIR . '/inc/acf/init.php';

require_once THEME_DIR . '/inc/acf/options-page.php';