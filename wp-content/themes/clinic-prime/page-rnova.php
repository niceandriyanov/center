<?php
/**
 * Template Name: Rnova Widget
 * 
 * Шаблон страницы для Rnova виджета записи на прием
 * Загружается через AJAX в модальное окно
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

// Предотвращаем прямой доступ к файлу
if (!defined('ABSPATH')) {
    exit;
}

// Устанавливаем заголовки для AJAX-запроса
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div id="rnova-a_form"></div>
    <script src="https://app.rnova.org/widgets" rel="preload" as="script"></script>
    <?php wp_footer(); ?>
</body>
</html>