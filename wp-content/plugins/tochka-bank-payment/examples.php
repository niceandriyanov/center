<?php
/**
 * Примеры использования плагина "Оплата ТочкаБанка"
 * 
 * ВНИМАНИЕ: Этот файл только для тестирования!
 * Не используйте в продакшене!
 */

// Подключаем WordPress
require_once('../../../wp-config.php');

// Проверяем, что плагин активен
if (!class_exists('TochkaPayment')) {
    die('Плагин "Оплата ТочкаБанка" не активен!');
}

// Проверяем права доступа
if (!current_user_can('manage_options')) {
    die('Недостаточно прав для тестирования!');
}

// Создаем экземпляр класса
$tochka_payment = new TochkaPayment();

// Проверяем настройки
$client_id = get_option('tochka_client_id', '');
$client_secret = get_option('tochka_client_secret', '');
$final_token = get_option('tochka_final_access_token', '');

echo '<h1>🧪 Тестирование плагина "Оплата ТочкаБанка"</h1>';

echo '<h2>📋 Проверка настроек</h2>';
echo '<p><strong>Client ID:</strong> ' . (!empty($client_id) ? '✅ Настроен' : '❌ Не настроен') . '</p>';
echo '<p><strong>Client Secret:</strong> ' . (!empty($client_secret) ? '✅ Настроен' : '❌ Не настроен') . '</p>';
echo '<p><strong>Финальный токен:</strong> ' . (!empty($final_token) ? '✅ Получен' : '❌ Не получен') . '</p>';

if (!empty($final_token)) {
    echo '<p><strong>Токен (первые 20 символов):</strong> ' . substr($final_token, 0, 20) . '...</p>';
}

// Показываем customerCode и merchantId
$customer_code = get_option('tochka_customer_code', '');
$merchant_id = get_option('tochka_merchant_id', '');

echo '<h2>🔑 Коды и идентификаторы:</h2>';
echo '<p><strong>Customer Code:</strong> ' . (!empty($customer_code) ? $customer_code : '❌ Не получен') . '</p>';
echo '<p><strong>Merchant ID:</strong> ' . (!empty($merchant_id) ? $merchant_id : '❌ Не указан (будет использован тестовый)') . '</p>';

if (empty($final_token)) {
    echo '<p style="color: red;"><strong>⚠️ Ошибка:</strong> Финальный токен не найден. Завершите настройку OAuth 2.0 в админ-панели.</p>';
    echo '<p><a href="' . admin_url('options-general.php?page=tochka-bank-settings') . '">Перейти в настройки плагина</a></p>';
    exit;
}

// Обработка POST запроса для тестирования
if ($_POST['action'] ?? '' === 'test_payment') {
    $order_id = sanitize_text_field($_POST['order_id'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $description = sanitize_text_field($_POST['description'] ?? '');
    
    if (empty($order_id) || $amount <= 0) {
        echo '<p style="color: red;">Ошибка: заполните все поля</p>';
    } else {
        echo '<h2>💳 Результат тестирования</h2>';
        $result = $tochka_payment->create_payment($order_id, $amount, $description);
        
        if (is_wp_error($result)) {
            echo '<p style="color: red;"><strong>❌ Ошибка создания платежа:</strong></p>';
            echo '<p>' . $result->get_error_message() . '</p>';
            echo '<p><strong>Код ошибки:</strong> ' . $result->get_error_code() . '</p>';
        } else {
            echo '<p style="color: green;"><strong>✅ Платеж создан успешно!</strong></p>';
            echo '<pre>' . print_r($result, true) . '</pre>';
            
            // Если есть URL для оплаты, показываем его
            if (isset($result['Data']['paymentUrl'])) {
                echo '<p><strong>🔗 Ссылка для оплаты:</strong></p>';
                echo '<p><a href="' . esc_url($result['Data']['paymentUrl']) . '" target="_blank" style="background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Перейти к оплате</a></p>';
            }
        }
    }
}

/**
 * Пример 1: Создание простого платежа
 */
function example_create_simple_payment() {
    global $tochka_payment;
    
    $order_id = 12345;
    $amount = 1000.00; // 1000 рублей
    $description = 'Оплата заказа №12345';
    
    $result = $tochka_payment->create_payment($order_id, $amount, $description);
    
    if (is_wp_error($result)) {
        echo 'Ошибка: ' . $result->get_error_message();
    } else {
        echo 'Платеж создан успешно!';
        echo 'ID платежа: ' . $result['Data']['paymentId'];
        echo 'Ссылка для оплаты: ' . $result['Data']['paymentUrl'];
    }
}

/**
 * Пример 2: Создание платежа с возвратом
 */
function example_create_payment_with_return() {
    global $tochka_payment;
    
    $order_id = 12346;
    $amount = 2500.50;
    $description = 'Оплата товара';
    $return_url = home_url('/thank-you/'); // Страница благодарности
    
    $result = $tochka_payment->create_payment($order_id, $amount, $description, $return_url);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    // Перенаправляем пользователя на страницу оплаты
    wp_redirect($result['Data']['paymentUrl']);
    exit;
}

/**
 * Пример 3: Проверка статуса платежа
 */
function example_check_payment_status($payment_id) {
    global $tochka_payment;
    
    $result = $tochka_payment->get_payment_status($payment_id);
    
    if (is_wp_error($result)) {
        echo 'Ошибка: ' . $result->get_error_message();
    } else {
        echo 'Статус платежа: ' . $result['Data']['status'];
        echo 'Сумма: ' . $result['Data']['amount']['amount'] . ' ' . $result['Data']['amount']['currency'];
    }
}

/**
 * Пример 4: Интеграция с WooCommerce
 */
function example_woocommerce_integration($order_id) {
    global $tochka_payment;
    
    if (!function_exists('wc_get_order')) {
        return new WP_Error('woocommerce_not_active', 'WooCommerce не активен');
    }
    
    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_Error('invalid_order', 'Заказ не найден');
    }
    
    $amount = $order->get_total();
    $description = 'Оплата заказа WooCommerce №' . $order_id;
    $return_url = $order->get_checkout_order_received_url();
    
    $result = $tochka_payment->create_payment($order_id, $amount, $description, $return_url);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    // Сохраняем ID платежа в мета-данные заказа
    $order->update_meta_data('_tochka_payment_id', $result['Data']['paymentId']);
    $order->update_meta_data('_tochka_payment_url', $result['Data']['paymentUrl']);
    $order->save();
    
    return $result;
}

/**
 * Пример 5: Обработка webhook'а от банка
 */
function example_handle_webhook() {
    // Получаем данные от банка
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['Data']['paymentId'])) {
        http_response_code(400);
        exit('Invalid webhook data');
    }
    
    $payment_id = $data['Data']['paymentId'];
    $status = $data['Data']['status'];
    
    // Обновляем статус платежа в базе данных
    global $wpdb;
    $table_name = $wpdb->prefix . 'tochka_payments';
    
    $wpdb->update(
        $table_name,
        array(
            'status' => $status,
            'callback_data' => $input,
            'updated_at' => current_time('mysql')
        ),
        array('payment_id' => $payment_id)
    );
    
    // Если платеж успешен, обновляем заказ
    if ($status === 'Completed') {
        $order_id = $wpdb->get_var($wpdb->prepare(
            "SELECT order_id FROM {$table_name} WHERE payment_id = %s",
            $payment_id
        ));
        
        if ($order_id && function_exists('wc_get_order')) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->payment_complete();
                $order->add_order_note('Платеж успешно обработан через Точка Банк');
            }
        }
    }
    
    http_response_code(200);
    exit('OK');
}

/**
 * Пример 6: Создание платежной ссылки для email
 */
function example_create_payment_link_for_email($order_id, $amount, $description) {
    global $tochka_payment;
    
    $result = $tochka_payment->create_payment($order_id, $amount, $description);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    // Отправляем email с ссылкой на оплату
    $payment_url = $result['Data']['paymentUrl'];
    $subject = 'Ссылка для оплаты заказа №' . $order_id;
    $message = "Для оплаты заказа перейдите по ссылке: {$payment_url}";
    
    wp_mail('customer@example.com', $subject, $message);
    
    return $result;
}

/**
 * Пример 7: Массовое создание платежей
 */
function example_bulk_create_payments($orders) {
    global $tochka_payment;
    
    $results = array();
    
    foreach ($orders as $order) {
        $result = $tochka_payment->create_payment(
            $order['id'],
            $order['amount'],
            $order['description']
        );
        
        $results[] = array(
            'order_id' => $order['id'],
            'result' => $result
        );
    }
    
    return $results;
}

// Примеры использования:
// example_create_simple_payment();
// example_check_payment_status('payment_123');
// example_woocommerce_integration(12345);

// Показываем форму для тестирования
echo '<h2>🧪 Тестирование создания платежа</h2>';
echo '<form method="POST" style="background: #f9f9f9; padding: 20px; border-radius: 4px; max-width: 500px;">';
echo '<input type="hidden" name="action" value="test_payment">';
echo '<p><label><strong>ID заказа:</strong><br>';
echo '<input type="text" name="order_id" value="test_' . time() . '" required style="width: 100%; padding: 8px; margin-top: 5px;"></label></p>';
echo '<p><label><strong>Сумма (руб.):</strong><br>';
echo '<input type="number" name="amount" value="100.00" step="0.01" min="0.01" required style="width: 100%; padding: 8px; margin-top: 5px;"></label></p>';
echo '<p><label><strong>Описание:</strong><br>';
echo '<input type="text" name="description" value="Тестовый платеж через API" style="width: 100%; padding: 8px; margin-top: 5px;"></label></p>';
echo '<p><button type="submit" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">💳 Создать тестовый платеж</button></p>';
echo '</form>';

// Добавляем отладочную информацию
echo '<h2>🔍 Отладочная информация</h2>';

$sandbox_mode = get_option('tochka_sandbox_mode', '1');
$api_url = $sandbox_mode === '1' 
    ? 'https://enter.tochka.com/sandbox/v2/acquiring/v1.0' 
    : 'https://enter.tochka.com/uapi/acquiring/v1.0';

echo '<p><strong>Режим sandbox:</strong> ' . ($sandbox_mode === '1' ? '✅ Включен' : '❌ Выключен') . '</p>';
echo '<p><strong>API URL:</strong> ' . $api_url . '</p>';
echo '<p><strong>Полный URL:</strong> ' . $api_url . '/payments</p>';

if ($sandbox_mode === '1') {
    echo '<p style="color: orange;"><strong>⚠️ Внимание:</strong> Песочница Точка Банка может не работать!</p>';
    echo '<p><a href="' . plugin_dir_url(__FILE__) . 'switch-to-production.php" style="background: #d63638; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px;">🔧 Переключиться на Production</a></p>';
} else {
    echo '<p style="color: green;"><strong>✅ Используется Production API</strong></p>';
}

// Показываем заголовки, которые будут отправлены
echo '<h3>🔑 Заголовки запроса</h3>';
$example_headers = array(
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
    'Authorization' => 'Bearer ' . (!empty($final_token) ? substr($final_token, 0, 20) . '...' : 'ТОКЕН_НЕ_НАЙДЕН')
);
echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto;">' . json_encode($example_headers, JSON_PRETTY_PRINT) . '</pre>';

// Показываем пример данных, которые будут отправлены
echo '<h3>📤 Пример отправляемых данных</h3>';
$example_data = array(
    'Data' => array(
        'customerCode' => !empty($customer_code) ? $customer_code : '1234567ab',
        'amount' => '100.00',
        'purpose' => 'Тестовый платеж через API',
        'redirectUrl' => home_url('/?tochka_oauth=redirect'),
        'failRedirectUrl' => home_url('/?tochka_oauth=redirect'),
        'paymentMode' => array('card', 'sbp'),
        'saveCard' => false,
        'preAuthorization' => false,
        'ttl' => 10080,
        'merchantId' => !empty($merchant_id) ? $merchant_id : '200000000001056'
    )
);

echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto;">' . json_encode($example_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';

// Показываем сохраненные платежи
echo '<h2>💾 Последние платежи</h2>';

global $wpdb;
$saved_payments = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}tochka_payments ORDER BY created_at DESC LIMIT 10"
);

if ($saved_payments) {
    echo '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
    echo '<tr style="background: #f0f0f0;"><th>ID</th><th>Order ID</th><th>Payment ID</th><th>Amount</th><th>Status</th><th>Created</th></tr>';
    
    foreach ($saved_payments as $payment) {
        echo '<tr>';
        echo '<td>' . $payment->id . '</td>';
        echo '<td>' . $payment->order_id . '</td>';
        echo '<td>' . $payment->payment_id . '</td>';
        echo '<td>' . $payment->amount . '</td>';
        echo '<td>' . $payment->status . '</td>';
        echo '<td>' . $payment->created_at . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
} else {
    echo '<p>Сохраненных платежей не найдено.</p>';
}

// Показываем информацию о логах
echo '<h2>📝 Логи и отладка</h2>';
echo '<p><strong>Все операции записываются в лог WordPress.</strong></p>';
echo '<p>Для просмотра логов:</p>';
echo '<ul>';
echo '<li>Проверьте файл <code>wp-content/debug.log</code></li>';
echo '<li>Или используйте плагин для просмотра логов</li>';
echo '<li>Ищите записи с префиксом "Tochka Bank:"</li>';
echo '</ul>';

echo '<hr>';
echo '<p><a href="' . admin_url('options-general.php?page=tochka-bank-settings') . '">← Вернуться в настройки плагина</a></p>';
echo '<p><a href="' . plugin_dir_url(__FILE__) . 'test-payment.php">🧪 Универсальный тест платежей</a></p>';

// Показываем информацию о проблеме с песочницей
if ($sandbox_mode === '1') {
    echo '<h2>📞 Сообщение для техподдержки</h2>';
    echo '<p><strong>Песочница Точка Банка не работает!</strong></p>';
    echo '<p>Если у вас есть доступ к техподдержке, отправьте им сообщение:</p>';
    echo '<p><a href="' . plugin_dir_url(__FILE__) . 'support-message.md" target="_blank">📄 Подробное сообщение (MD)</a></p>';
}
echo '<p><a href="' . home_url() . '">← На главную страницу</a></p>';
?>
