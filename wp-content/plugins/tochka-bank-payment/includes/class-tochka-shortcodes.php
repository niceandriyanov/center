<?php
/**
 * Шорткоды для плагина Точка Банка
 */

if (!defined('ABSPATH')) {
    exit;
}

class TochkaShortcodes {
    
    private $payment_gateway;
    
    public function __construct() {
        $this->payment_gateway = new TochkaPaymentGateway();
        $this->init_hooks();
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        add_shortcode('tochka_pay_button', array($this, 'pay_button_shortcode'));
        add_shortcode('tochka_payment_status', array($this, 'payment_status_shortcode'));
    }
    
    /**
     * Шорткод кнопки оплаты
     * [tochka_pay_button order_id="123" button_text="Оплатить"]
     */
    public function pay_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'order_id' => '',
            'button_text' => 'Оплатить через Точка Банк',
            'show_amount' => 'true'
        ), $atts);
        
        if (empty($atts['order_id'])) {
            return '<p style="color: red;">Ошибка: не указан ID заказа</p>';
        }
        
        $order_id = intval($atts['order_id']);
        $button_text = sanitize_text_field($atts['button_text']);
        
        return $this->payment_gateway->render_payment_button($order_id, $button_text);
    }
    
    /**
     * Шорткод статуса платежа
     * [tochka_payment_status order_id="123"]
     */
    public function payment_status_shortcode($atts) {
        $atts = shortcode_atts(array(
            'order_id' => ''
        ), $atts);
        
        if (empty($atts['order_id'])) {
            return '<p style="color: red;">Ошибка: не указан ID заказа</p>';
        }
        
        $order_id = intval($atts['order_id']);
        
        $payment = $this->get_payment_record($order_id);

        if (!$payment) {
            return '<p style="color: red;">Платеж не найден</p>';
        }
        
        $status_messages = array(
            'pending' => 'Ожидает оплаты',
            'paid' => 'Оплачен',
            'cancelled' => 'Отменен',
            'refunding' => 'Возврат в обработке',
            'refunded' => 'Возвращен',
            'failed' => 'Ошибка оплаты'
        );
        
        $status = $this->normalize_status((string) ($payment['status'] ?? 'pending'));
        $message = $status_messages[$status] ?? 'Неизвестный статус';
        
        $status_class = array(
            'pending' => 'warning',
            'paid' => 'success',
            'cancelled' => 'error',
            'refunding' => 'warning',
            'refunded' => 'info',
            'failed' => 'error'
        );
        
        $class = $status_class[$status] ?? 'info';
        
        ob_start();
        ?>
        <div class="tochka-payment-status-widget">
            <div class="payment-status payment-status-<?php echo esc_attr($class); ?>">
                <strong>Статус заказа №<?php echo esc_html($order_id); ?>:</strong>
                <?php echo esc_html($message); ?>
            </div>
            
            <?php if ($status === 'paid' && !empty($payment['updated_at'])): ?>
            <div class="payment-date">
                Дата оплаты: <?php echo esc_html($payment['updated_at']); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($status === 'pending'): ?>
            <div class="payment-actions">
                <?php echo $this->payment_gateway->render_payment_button($order_id, 'Оплатить заказ'); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .tochka-payment-status-widget {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .payment-status {
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .payment-status-success {
            color: #155724;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }
        
        .payment-status-warning {
            color: #856404;
            background: #fff3cd;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ffeaa7;
        }
        
        .payment-status-error {
            color: #721c24;
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }
        
        .payment-date {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .payment-actions {
            margin-top: 15px;
        }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Получить актуальную запись платежа.
     *
     * @param int $order_id Идентификатор заказа/визита.
     * @return array|null
     */
    private function get_payment_record($order_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'tochka_payments';
        $order_id_str = sanitize_text_field((string) $order_id);

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE order_id = %s OR entity_id = %s ORDER BY id DESC LIMIT 1",
                $order_id_str,
                $order_id_str
            ),
            ARRAY_A
        );
    }

    /**
     * Нормализация внутреннего статуса для UI.
     *
     * @param string $status Статус из БД.
     * @return string
     */
    private function normalize_status($status) {
        $status = strtolower(sanitize_text_field($status));
        $map = array(
            'success' => 'paid',
            'completed' => 'paid',
            'approved' => 'paid',
            'declined' => 'failed',
            'expired' => 'cancelled'
        );

        return $map[$status] ?? $status;
    }
}
