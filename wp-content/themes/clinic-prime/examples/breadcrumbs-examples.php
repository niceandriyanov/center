<?php
/**
 * Примеры использования модуля breadcrumbs
 * 
 * Этот файл содержит примеры различных способов использования
 * модуля breadcrumbs в теме Clinic Prime
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

// Пример 1: Простое использование
// Выводит breadcrumbs для текущей страницы
clinic_breadcrumbs();

// Пример 2: С дополнительными CSS классами
// Добавляет кастомный класс к breadcrumbs
clinic_breadcrumbs(null, 'custom-breadcrumbs large');

// Пример 3: Использование шаблона
// Использует стандартный шаблон с контейнером
get_template_part('template-parts/parts/breadcrumbs');

// Пример 4: С кастомными параметрами
// Передает дополнительные параметры в шаблон
get_template_part('template-parts/parts/breadcrumbs', null, array(
    'class' => 'breadcrumbs-dark',
    'container_class' => 'container large'
));

// Пример 5: Получение массива breadcrumbs
// Получает массив для дальнейшей обработки
$breadcrumbs = clinic_get_breadcrumbs();

// Пример 6: Кастомные breadcrumbs
// Создает собственный массив breadcrumbs
$custom_breadcrumbs = array(
    array(
        'title' => 'Главная',
        'url' => home_url('/'),
        'is_current' => false
    ),
    array(
        'title' => 'Услуги',
        'url' => get_permalink(123), // ID страницы с услугами
        'is_current' => false
    ),
    array(
        'title' => 'Консультация',
        'url' => '',
        'is_current' => true
    )
);

clinic_breadcrumbs($custom_breadcrumbs);

// Пример 7: Breadcrumbs для конкретной записи
// Получает breadcrumbs для записи по ID
$post_breadcrumbs = clinic_get_breadcrumbs_for_post(456); // ID записи
clinic_breadcrumbs($post_breadcrumbs);

// Пример 8: Условный вывод breadcrumbs
// Выводит breadcrumbs только на определенных страницах
if (!is_front_page() && !is_home()) {
    clinic_breadcrumbs();
}

// Пример 9: Breadcrumbs с фильтрацией
// Фильтрует breadcrumbs перед выводом
$breadcrumbs = clinic_get_breadcrumbs();
$filtered_breadcrumbs = array_filter($breadcrumbs, function($item) {
    return !empty($item['title']);
});
clinic_breadcrumbs($filtered_breadcrumbs);

// Пример 10: Breadcrumbs в цикле
// Выводит breadcrumbs для каждой записи в цикле
if (have_posts()) {
    while (have_posts()) {
        the_post();
        
        // Получаем breadcrumbs для текущей записи
        $post_breadcrumbs = clinic_get_breadcrumbs_for_post(get_the_ID());
        
        // Выводим breadcrumbs
        clinic_breadcrumbs($post_breadcrumbs, 'post-breadcrumbs');
        
        // Остальной контент записи
        the_title('<h1>', '</h1>');
        the_content();
    }
}
?>
