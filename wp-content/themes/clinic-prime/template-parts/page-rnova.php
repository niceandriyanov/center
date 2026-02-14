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
$attr = '';
if( !empty($_GET['doctor']) ) {
    $attr = ' data-user_id="' . $_GET['doctor'] . '"';
}
if( !empty($_GET['service']) ) {
    $attr = ' data-service_id="' . $_GET['service'] . '"';
}
if( !empty($_GET['profession']) ) {
    $attr = ' data-profession_id="' . $_GET['profession'] . '"';
}
?>
<div id="rnova-a_form"<?= $attr; ?>></div>
<script>
window.onCreateAppointment = function(data) {
	var ymFn = (window.parent && typeof window.parent.ym === 'function')
		? window.parent.ym
		: (typeof ym === 'function' ? ym : null);

	if (!ymFn) {
		console.warn('Yandex Metrica не загружена ни в родителе, ни в текущем окне');
		return;
	}
	try {
		ymFn(104100346, 'reachGoal', 'appointment_success');
	} catch (error) {
		console.error('Ошибка при отправке метрики:', error);
	}
}
</script>
<script src="https://app.rnova.org/widgets" rel="preload" as="script"></script>