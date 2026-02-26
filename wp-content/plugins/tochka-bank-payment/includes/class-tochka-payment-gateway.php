<?php
/**
 * Класс для интеграции с системой заказов
 */

if (!defined('ABSPATH')) {
    exit;
}

class TochkaPaymentGateway {
    
    private $payment_handler;
    
    public function __construct() {
        $this->payment_handler = new TochkaPayment();
        $this->init_hooks();
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        add_action('wp_ajax_tochka_create_payment_link', array($this, 'create_payment_link_ajax'));
        add_action('wp_ajax_nopriv_tochka_create_payment_link', array($this, 'create_payment_link_ajax'));
        add_action('wp_ajax_tochka_check_payment_status', array($this, 'check_payment_status_ajax'));
        add_action('wp_ajax_nopriv_tochka_check_payment_status', array($this, 'check_payment_status_ajax'));
    }
    
    /**
     * Создание ссылки на оплату для заказа
     */
    public function create_payment_for_order($order_id) {
        // Получаем данные заказа
        $order = $this->get_order($order_id);
        if (!$order) {
            return new WP_Error('order_not_found', 'Заказ не найден');
        }
        
        // Проверяем, что заказ еще не оплачен
        if ($order['status'] === 'paid') {
            return new WP_Error('order_already_paid', 'Заказ уже оплачен');
        }
        
        // Получаем сумму заказа
        $amount = $this->calculate_order_amount($order);
        if ($amount <= 0) {
            return new WP_Error('invalid_amount', 'Неверная сумма заказа');
        }
        
        // Создаем платеж
        $payment_result = $this->payment_handler->create_payment_link(
            $order_id,
            $amount,
            "Оплата заказа №{$order_id}"
        );
        
        if (is_wp_error($payment_result)) {
            return $payment_result;
        }
        
        // Обновляем статус заказа
        $this->update_order_status($order_id, 'pending', 'tochka_bank');
        
        return $payment_result;
    }
    
    /**
     * Получение заказа
     */
    private function get_order($order_id) {
        $order_id = sanitize_text_field((string) $order_id);

        /**
         * Фильтр получения сущности для платежа.
         *
         * @param array|null $entity Данные сущности.
         * @param int        $order_id ID сущности.
         */
        $entity = apply_filters('tochka_payment_gateway_get_entity', null, $order_id);
        if (is_array($entity) && !empty($entity)) {
            $normalized = array(
                'status' => sanitize_text_field((string) ($entity['status'] ?? 'pending')),
                'payment' => sanitize_text_field((string) ($entity['payment'] ?? '')),
                'pay_at' => sanitize_text_field((string) ($entity['pay_at'] ?? '')),
                'options' => isset($entity['options']) ? (string) $entity['options'] : '[]'
            );

            if (isset($entity['_amount']) && is_numeric($entity['_amount'])) {
                $normalized['_amount'] = (float) $entity['_amount'];
            }

            return $normalized;
        }

        return null;
    }
    
    /**
     * Расчет суммы заказа
     */
    private function calculate_order_amount($order) {
        /**
         * Фильтр расчета суммы для платежа.
         *
         * @param float|null $amount Сумма.
         * @param array      $order Данные сущности.
         */
        $filtered_amount = apply_filters('tochka_payment_gateway_calculate_amount', null, $order);
        if (is_numeric($filtered_amount)) {
            return (float) $filtered_amount;
        }

        // Парсим данные заказа
        $options = json_decode($order['options'], true);
        $amount = 0;
        
        if (is_array($options)) {
            foreach ($options as $item) {
                if (isset($item['price']) && isset($item['quantity'])) {
                    $amount += floatval($item['price']) * intval($item['quantity']);
                }
            }
        }
        
        return $amount;
    }
    
    /**
     * Обновление статуса заказа
     */
    private function update_order_status($order_id, $status, $payment_method = '') {
        $order_id = sanitize_text_field((string) $order_id);
        $entity_context = array(
            'entity_type' => 'visit',
            'entity_id' => $order_id,
            'entity_public_id' => '',
            'order_id' => $order_id,
            'payment_id' => ''
        );

        $payload = array(
            'source' => 'payment_gateway',
            'payment_method' => sanitize_text_field((string) $payment_method),
            'status' => sanitize_text_field((string) $status)
        );

        do_action('tochka_payment_entity_status_changed', $entity_context, $status, null, $payload);
        if ($status === 'paid') {
            do_action('tochka_payment_entity_paid', $entity_context, '', '', $payload);
        }
    }
    
    /**
     * AJAX создание ссылки на оплату
     */
    public function create_payment_link_ajax() {
        check_ajax_referer('tochka_payment_nonce', 'nonce');
        
        $order_id = sanitize_text_field(wp_unslash($_POST['order_id'] ?? ''));
        
        if (empty($order_id)) {
            wp_send_json_error('Не указан ID заказа');
        }
        
        $result = $this->create_payment_for_order($order_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success(array(
            'payment_url' => $result,
            'order_id' => $order_id
        ));
    }
    
    /**
     * AJAX проверка статуса платежа
     */
    public function check_payment_status_ajax() {
        check_ajax_referer('tochka_payment_nonce', 'nonce');
        
        $order_id = sanitize_text_field(wp_unslash($_POST['order_id'] ?? ''));
        
        if (empty($order_id)) {
            wp_send_json_error('Не указан ID заказа');
        }
        
        $order = $this->get_order($order_id);
        if (!$order) {
            wp_send_json_error('Заказ не найден');
        }
        
        wp_send_json_success(array(
            'status' => $order['status'],
            'payment' => $order['payment'],
            'pay_at' => $order['pay_at']
        ));
    }
    
    /**
     * Получение всех платежей Точка Банка
     */
    public function get_tochka_payments($limit = 50, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tochka_payments';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
    }
    
    /**
     * Получение статистики платежей
     */
    public function get_payment_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tochka_payments';
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN status IN ('success', 'completed', 'paid', 'approved') THEN 1 ELSE 0 END) as successful_payments,
                SUM(CASE WHEN status IN ('success', 'completed', 'paid', 'approved') THEN amount ELSE 0 END) as total_amount,
                AVG(CASE WHEN status IN ('success', 'completed', 'paid', 'approved') THEN amount ELSE NULL END) as avg_amount
            FROM $table_name",
            ARRAY_A
        );
        
        return $stats;
    }
    
    /**
     * Создание кнопки оплаты
     */
    public function render_payment_button($order_id, $button_text = 'Оплатить через Точка Банк') {
        if (empty($order_id)) {
            return '';
        }
        
        $order = $this->get_order($order_id);
        if (!$order || $order['status'] === 'paid') {
            return '';
        }
        
        $amount = $this->calculate_order_amount($order);
        if ($amount <= 0) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="tochka-payment-widget" data-order-id="<?php echo esc_attr($order_id); ?>">
            <button type="button" class="tochka-pay-button" data-order-id="<?php echo esc_attr($order_id); ?>">
                <?php echo esc_html($button_text); ?> (<?php echo number_format($amount, 2); ?> ₽)
            </button>
            <div class="tochka-payment-status" style="display: none;"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.tochka-pay-button').on('click', function() {
                var orderId = $(this).data('order-id');
                var button = $(this);
                var statusDiv = button.siblings('.tochka-payment-status');
                
                button.prop('disabled', true).text('Создание платежа...');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'tochka_create_payment_link',
                        order_id: orderId,
                        nonce: '<?php echo wp_create_nonce('tochka_payment_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.data.payment_url;
                        } else {
                            alert('Ошибка: ' + response.data);
                            button.prop('disabled', false).text('<?php echo esc_js($button_text); ?>');
                        }
                    },
                    error: function() {
                        alert('Ошибка при создании платежа');
                        button.prop('disabled', false).text('<?php echo esc_js($button_text); ?>');
                    }
                });
            });
        });
        </script>
        
        <style>
        .tochka-pay-button {
            background: #0073aa;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .tochka-pay-button:hover {
            background: #005a87;
        }
        
        .tochka-pay-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .tochka-payment-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        
        .tochka-payment-status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .tochka-payment-status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
