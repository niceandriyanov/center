<?php
/**
 * Шорткоды
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Шорткод формы записи на прием
 */
function clinic_appointment_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Записаться на прием',
        'button_text' => 'Отправить заявку'
    ), $atts);
    
    ob_start();
    ?>
    <div class="clinic-appointment-form">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        <form id="appointment-form" method="post">
            <div class="form-group">
                <input type="text" name="name" placeholder="Ваше имя" required>
            </div>
            <div class="form-group">
                <input type="tel" name="phone" placeholder="Телефон" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email">
            </div>
            <div class="form-group">
                <select name="service" required>
                    <option value="">Выберите услугу</option>
                    <option value="consultation">Консультация</option>
                    <option value="treatment">Лечение</option>
                    <option value="diagnosis">Диагностика</option>
                </select>
            </div>
            <div class="form-group">
                <input type="date" name="date" required>
            </div>
            <div class="form-group">
                <textarea name="message" placeholder="Дополнительная информация"></textarea>
            </div>
            <button type="submit" class="btn"><?php echo esc_html($atts['button_text']); ?></button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('clinic_appointment_form', 'clinic_appointment_form_shortcode');
