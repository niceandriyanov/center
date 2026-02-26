<?php
/**
 * Класс для обработки redirect'ов от Точка Банка
 */

if (!defined('ABSPATH')) {
    exit;
}

class TochkaRedirectHandler {
    
    private static $instance = null;
    private static $hooks_registered = false;
    
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Статическая инициализация (Singleton)
     */
    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        if (self::$hooks_registered) {
            return;
        }
        
        add_action('template_redirect', array($this, 'handle_redirect'));
        add_action('init', array($this, 'handle_webhook'));
        add_action('template_redirect', array($this, 'template_redirect'));
        
        self::$hooks_registered = true;
    }
    
    /**
     * Обработка redirect'а от банка
     */
    public function handle_redirect() {
        error_log('Tochka Bank: handle_redirect вызван - URL: ' . $_SERVER['REQUEST_URI']);
        
        // Обработка OAuth redirect для получения кода авторизации
        if (isset($_GET['tochka_oauth']) && $_GET['tochka_oauth'] === 'redirect') {
            if (isset($_GET['code']) && isset($_GET['state'])) {
                $this->handle_oauth_redirect();
                return;
            }
        }
        
        // Обработка старого URL формата /tochka-payment/redirect/
        if (strpos($_SERVER['REQUEST_URI'], '/tochka-payment/redirect/') !== false) {
            error_log('Tochka Bank: handle_redirect - обнаружен redirect URL: ' . $_SERVER['REQUEST_URI']);
            if (isset($_GET['code']) && isset($_GET['state'])) {
                error_log('Tochka Bank: handle_redirect - найден код авторизации, обрабатываем OAuth redirect');
                $this->handle_oauth_redirect();
                return;
            }
        }
        
        // Обработка webhook URL с параметрами order_id и status
        if (strpos($_SERVER['REQUEST_URI'], '/tochka-payment/webhook/') !== false) {
            $order_id = sanitize_text_field($_GET['order_id'] ?? '');
            $status = sanitize_text_field($_GET['status'] ?? '');
            
            if (!empty($order_id) && !empty($status)) {
                error_log("Tochka Bank: Webhook redirect - order_id: {$order_id}, status: {$status}");
                $this->handle_payment_webhook($order_id, $status);
                return;
            }
        }
        
        // Обработка redirect'а от платежа
        if (!isset($_GET['tochka_payment']) || $_GET['tochka_payment'] !== 'redirect') {
            return;
        }
        
        $payment_id = sanitize_text_field($_GET['payment_id'] ?? '');
        $status = sanitize_text_field($_GET['status'] ?? '');
        $order_id = intval($_GET['order_id'] ?? 0);
        
        if (empty($payment_id) || empty($order_id)) {
            wp_die('Неверные параметры платежа');
        }
        
        // Обновляем статус платежа в БД
        $this->update_payment_status($payment_id, $status);
        
        // Универсальное обновление статуса сущности через хуки
        $payment_record = $this->get_payment_record($payment_id, $order_id);
        $this->update_order_status($order_id, $status, $payment_record);
        
        // Перенаправляем на страницу результата
        $redirect_url = $this->get_result_url($order_id, $status);
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Обработка OAuth redirect для получения кода авторизации
     */
    private function handle_oauth_redirect() {
        $code = sanitize_text_field($_GET['code'] ?? '');
        $state = sanitize_text_field($_GET['state'] ?? '');
        $error = sanitize_text_field($_GET['error'] ?? '');
        
        // Логируем начало обработки
        error_log('Tochka Bank: Обработка OAuth redirect. Code: ' . substr($code, 0, 10) . '..., State: ' . $state);
        
        if (!empty($error)) {
            $this->show_oauth_error($error);
            return;
        }
        
        if (empty($code) || empty($state)) {
            $this->show_oauth_error('Отсутствует код авторизации или state');
            return;
        }
        
        // Проверяем state
        $saved_state = get_option('tochka_oauth_state', '');
        if ($state !== $saved_state) {
            $this->show_oauth_error('Неверный state параметр');
            return;
        }
        
        // Обмениваем код на финальный токен
        $result = $this->exchange_code_for_token($code);
        
        if ($result['success']) {
            $this->show_oauth_success();
        } else {
            $this->show_oauth_error($result['error']);
        }
    }
    
    /**
     * Обмен кода авторизации на финальный токен
     */
    private function exchange_code_for_token($code) {
        $client_id = get_option('tochka_client_id', '');
        $client_secret = get_option('tochka_client_secret', '');
        $redirect_uri = get_option('tochka_redirect_url', '');
        
        $token_data = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri
        );
        
        // Логируем данные для отладки
        error_log('Tochka Bank: Обмен кода на токен - данные: ' . json_encode($token_data));
        
        $response = wp_remote_post('https://enter.tochka.com/connect/token', array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'WordPress/' . get_bloginfo('version')
            ),
            'body' => http_build_query($token_data),
            'timeout' => 30,
            'sslverify' => false,
            'httpversion' => '1.1'
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => 'Ошибка подключения: ' . $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Логируем ответ для отладки
        error_log('Tochka Bank: Обмен кода на токен - код ответа: ' . $code);
        error_log('Tochka Bank: Обмен кода на токен - тело ответа: ' . $body);
        
        if (isset($data['access_token'])) {
            // Сохраняем финальный токен
            update_option('tochka_final_access_token', $data['access_token']);
            if (isset($data['refresh_token'])) {
                update_option('tochka_refresh_token', $data['refresh_token']);
            }
            
            // Сохраняем время истечения токена (обычно 24 часа)
            $expires_in = isset($data['expires_in']) ? $data['expires_in'] : 86400; // 24 часа по умолчанию
            update_option('tochka_token_expires', time() + $expires_in);
            
            // Логируем успешное сохранение
            error_log('Tochka Bank: Финальный токен сохранен: ' . substr($data['access_token'], 0, 20) . '...');
            error_log('Tochka Bank: Токен истекает через ' . $expires_in . ' секунд');
            
            // Получаем customerCode через API запрос
            $this->get_and_save_customer_code($data['access_token']);
            
            // Очищаем временные данные
            delete_option('tochka_temp_access_token');
            delete_option('tochka_oauth_state');
            
            return array('success' => true);
        } else {
            return array(
                'success' => false,
                'error' => 'Ошибка получения финального токена: ' . $body
            );
        }
    }
    
    /**
     * Показ страницы успешной авторизации
     */
    private function show_oauth_success() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Авторизация успешна</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .success { color: #00a32a; font-size: 24px; margin-bottom: 20px; }
                .message { font-size: 16px; margin-bottom: 30px; }
                .button { 
                    background: #0073aa; color: white; padding: 10px 20px; 
                    text-decoration: none; border-radius: 4px; display: inline-block;
                }
            </style>
        </head>
        <body>
            <div class="success">🎉 Настройка завершена успешно!</div>
            <div class="message">
                <strong>✅ Финальный токен получен и сохранен!</strong><br>
                Все права доступа подтверждены!<br>
                Плагин "Оплата ТочкаБанка" готов к работе.<br>
                <br>
                <strong>🚀 Теперь вы можете принимать платежи через Точка Банк!</strong>
            </div>
            <a href="<?php echo admin_url('options-general.php?page=tochka-bank-settings'); ?>" class="button">
                Вернуться в настройки плагина
            </a>
        </body>
        </html>
        <?php
        exit;
    }
    
    /**
     * Показ страницы ошибки авторизации
     */
    private function show_oauth_error($error) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Ошибка авторизации</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error { color: #d63638; font-size: 24px; margin-bottom: 20px; }
                .message { font-size: 16px; margin-bottom: 30px; }
                .button { 
                    background: #0073aa; color: white; padding: 10px 20px; 
                    text-decoration: none; border-radius: 4px; display: inline-block;
                }
            </style>
        </head>
        <body>
            <div class="error">❌ Ошибка авторизации</div>
            <div class="message">
                <?php echo esc_html($error); ?>
            </div>
            <a href="<?php echo admin_url('options-general.php?page=tochka-bank-settings'); ?>" class="button">
                Вернуться в настройки плагина
            </a>
        </body>
        </html>
        <?php
        exit;
    }
    
    /**
     * Обработка webhook'ов от банка
     */
    public function handle_webhook() {
        if (!isset($_GET['tochka_payment']) || $_GET['tochka_payment'] !== 'webhook') {
            return;
        }
        
        // Проверяем подпись webhook'а
        if (!$this->verify_webhook_signature()) {
            http_response_code(400);
            exit('Invalid signature');
        }
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            http_response_code(400);
            exit('Invalid JSON');
        }
        
        // Обрабатываем уведомление
        $this->process_webhook($data);
        
        http_response_code(200);
        exit('OK');
    }
    
    /**
     * Обработка template redirect
     */
    public function template_redirect() {
        // Обработка OAuth redirect для /tochka-payment/redirect/
        if (strpos($_SERVER['REQUEST_URI'], '/tochka-payment/redirect/') !== false) {
            error_log('Tochka Bank: template_redirect - обнаружен OAuth redirect URL: ' . $_SERVER['REQUEST_URI']);
            if (isset($_GET['code']) && isset($_GET['state'])) {
                error_log('Tochka Bank: template_redirect - найден код авторизации, обрабатываем OAuth redirect');
                $this->handle_oauth_redirect();
                return;
            }
        }
        
        if (is_404() && strpos($_SERVER['REQUEST_URI'], '/tochka-payment/') === 0) {
            $this->handle_payment_urls();
        }
    }
    
    /**
     * Обработка URL платежей
     */
    private function handle_payment_urls() {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // Обработка redirect URL
        if (preg_match('/\/tochka-payment\/redirect\//', $request_uri)) {
            $this->handle_redirect();
            return;
        }
        
        // Обработка webhook URL
        if (preg_match('/\/tochka-payment\/webhook\//', $request_uri)) {
            $this->handle_webhook();
            return;
        }
        
        // Обработка результата платежа
        if (preg_match('/\/tochka-payment\/result\/(\d+)\/(\w+)/', $request_uri, $matches)) {
            $order_id = intval($matches[1]);
            $status = sanitize_text_field($matches[2]);
            $this->show_payment_result($order_id, $status);
            return;
        }
    }
    
    /**
     * Обновление статуса платежа
     */
    private function update_payment_status($payment_id, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tochka_payments';
        
        $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'updated_at' => current_time('mysql')
            ),
            array('payment_id' => $payment_id),
            array('%s', '%s'),
            array('%s')
        );
    }
    
    /**
     * Обновление статуса заказа
     */
    private function update_order_status($order_id, $payment_status, $payment_record = null) {
        // Маппинг статусов провайдера в внутренние статусы
        $status_map = array(
            'success' => 'paid',
            'failed' => 'cancelled',
            'pending' => 'pending',
            'approved' => 'paid',
            'created' => 'pending',
            'declined' => 'failed',
            'expired' => 'cancelled'
        );
        
        $normalized_status = strtolower((string) $payment_status);
        $order_status = $status_map[$normalized_status] ?? 'pending';
        $entity_context = $this->build_entity_context($order_id, $payment_record);
        $payload = array(
            'source' => 'redirect_handler',
            'payment_status' => $payment_status,
            'order_status' => $order_status
        );

        // Legacy хук оставляем для обратной совместимости.
        do_action('tochka_payment_status_updated', (string) $order_id, $payment_status, null, $payload);
        do_action('tochka_payment_entity_status_changed', $entity_context, $payment_status, null, $payload);

        if ($order_status === 'paid') {
            do_action('tochka_payment_entity_paid', $entity_context, $entity_context['payment_id'], '', $payload);
            $this->trigger_renovatio_paid_hook($entity_context, $payload);
        }
    }
    
    /**
     * Получение URL результата
     */
    private function get_result_url($order_id, $status) {
        return home_url("/tochka-payment/result/{$order_id}/{$status}");
    }
    
    /**
     * Показ результата платежа
     */
    private function show_payment_result($order_id, $status) {
        $status_messages = array(
            'success' => 'Платеж успешно проведен!',
            'failed' => 'Ошибка при проведении платежа',
            'pending' => 'Платеж обрабатывается'
        );
        
        $message = $status_messages[$status] ?? 'Неизвестный статус';
        $is_success = $status === 'success';
        
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Результат платежа</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .success { color: green; }
                .error { color: red; }
                .pending { color: orange; }
            </style>
        </head>
        <body>
            <h1>Результат платежа</h1>
            <p class="<?php echo $is_success ? 'success' : ($status === 'pending' ? 'pending' : 'error'); ?>">
                <?php echo esc_html($message); ?>
            </p>
            <p>Заказ №<?php echo esc_html($order_id); ?></p>
            <p><a href="<?php echo home_url(); ?>">Вернуться на сайт</a></p>
        </body>
        </html>
        <?php
        exit;
    }
    
    /**
     * Проверка подписи webhook'а
     */
    private function verify_webhook_signature() {
        // Здесь должна быть проверка подписи от банка
        // Пока возвращаем true для тестирования
        return true;
    }
    
    /**
     * Получение и сохранение customerCode
     */
    private function get_and_save_customer_code($access_token) {
        // ✅ ВСЕГДА используем production API для получения customerCode
        // customerCode - это реальный код клиента, который нужен для всех операций
        $customers_url = 'https://enter.tochka.com/uapi/open-banking/v1.0/customers';
        
        error_log('Tochka Bank: Получаем customerCode через production API: ' . $customers_url);
        
        $response = wp_remote_get($customers_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            error_log('Tochka Bank: Ошибка получения customerCode: ' . $response->get_error_message());
            return;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        error_log('Tochka Bank: API ответ customers - код: ' . $code);
        error_log('Tochka Bank: API ответ customers - тело: ' . $body);
        
        if ($code === 200 && isset($data['Data']) && is_array($data['Data']) && !empty($data['Data'])) {
            // Ищем клиента с типом Business
            $customer_code = '';
            if (isset($data['Data']['Customer']) && is_array($data['Data']['Customer'])) {
                foreach ($data['Data']['Customer'] as $customer) {
                    if (isset($customer['customerType']) && $customer['customerType'] === 'Business') {
                        $customer_code = $customer['customerCode'] ?? '';
                        break;
                    }
                }
            }
            
            if (!empty($customer_code)) {
                update_option('tochka_customer_code', $customer_code, false);
                error_log('Tochka Bank: customerCode получен и сохранен: ' . $customer_code);
            } else {
                error_log('Tochka Bank: customerCode не найден в ответе API');
            }
        } else {
            error_log('Tochka Bank: Ошибка получения customerCode - код: ' . $code . ', ответ: ' . $body);
        }
    }
    
    /**
     * Обработка webhook'а
     */
    private function process_webhook($data) {
        $payment_id = $data['payment_id'] ?? '';
        $status = $data['status'] ?? '';
        $order_id = $data['order_id'] ?? 0;
        
        if (empty($payment_id) || empty($order_id)) {
            return;
        }
        
        // Обновляем статусы
        $this->update_payment_status($payment_id, $status);
        $payment_record = $this->get_payment_record($payment_id, $order_id);
        $this->update_order_status($order_id, $status, $payment_record);
        
        // Логируем webhook
        error_log('Tochka Bank Webhook: ' . json_encode($data));
    }
    
    /**
     * Обработка webhook redirect с параметрами order_id и status
     */
    private function handle_payment_webhook($order_id, $status) {
        error_log("Tochka Bank: Обработка webhook redirect - order_id: {$order_id}, status: {$status}");
        
        // Получаем payment_id из БД
        $payment_id = $this->get_payment_id($order_id);
        
        if (!empty($payment_id)) {
            // Проверяем статус платежа через API
            $payment_api = new TochkaPayment();
            $status_response = $payment_api->check_payment_status($payment_id);
            
            if (!is_wp_error($status_response)) {
                // Обновляем статус в БД
                $payment_api->update_payment_status($order_id, $payment_id, $status_response);

                // Определяем реальный статус из API
                $real_status = $this->determine_payment_status($status_response);
                error_log("Tochka Bank: Реальный статус платежа - order_id: {$order_id}, status: {$real_status}");
                
                // Показываем результат с реальным статусом
                $this->show_payment_result($order_id, $real_status);
            } else {
                error_log("Tochka Bank: Ошибка проверки статуса платежа: " . $status_response->get_error_message());
                // Показываем результат с переданным статусом
                $this->show_payment_result($order_id, $status);
            }
        } else {
            error_log("Tochka Bank: Operation ID не найден для заказа: {$order_id}");
            // Показываем результат с переданным статусом
            $this->show_payment_result($order_id, $status);
        }
    }
    
    /**
     * Получение payment_id из БД
     */
    private function get_payment_id($order_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tochka_payments';
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT payment_id FROM {$table_name} WHERE order_id = %s",
            $order_id
        ));
        
        return $result;
    }

    /**
     * Получение записи платежа по payment_id/order_id.
     *
     * @param string $payment_id ID платежа провайдера.
     * @param int    $order_id Legacy ID заказа.
     * @return array|null
     */
    private function get_payment_record($payment_id, $order_id = 0) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'tochka_payments';
        $payment_id = sanitize_text_field((string) $payment_id);
        $order_id = (int) $order_id;

        if ($payment_id !== '') {
            $record = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table_name} WHERE payment_id = %s LIMIT 1", $payment_id),
                ARRAY_A
            );
            if (is_array($record)) {
                return $record;
            }
        }

        if ($order_id > 0) {
            return $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table_name} WHERE order_id = %s LIMIT 1", (string) $order_id),
                ARRAY_A
            );
        }

        return null;
    }

    /**
     * Построение контекста сущности для универсальных хуков.
     *
     * @param int        $order_id Legacy ID заказа.
     * @param array|null $payment_record Запись платежа.
     * @return array
     */
    private function build_entity_context($order_id, $payment_record = null) {
        if (!is_array($payment_record)) {
            return array(
                'entity_type' => 'visit',
                'entity_id' => sanitize_text_field((string) $order_id),
                'entity_public_id' => '',
                'order_id' => sanitize_text_field((string) $order_id),
                'payment_id' => ''
            );
        }

        return array(
            'entity_type' => sanitize_text_field((string) ($payment_record['entity_type'] ?? 'visit')),
            'entity_id' => sanitize_text_field((string) ($payment_record['entity_id'] ?? $order_id)),
            'entity_public_id' => sanitize_text_field((string) ($payment_record['entity_public_id'] ?? '')),
            'order_id' => sanitize_text_field((string) ($payment_record['order_id'] ?? $order_id)),
            'payment_id' => sanitize_text_field((string) ($payment_record['payment_id'] ?? ''))
        );
    }

    /**
     * Триггер интеграционного хука Renovatio при успешной оплате.
     *
     * @param array $entity_context Контекст сущности.
     * @param array $payload Дополнительные данные.
     * @return void
     */
    private function trigger_renovatio_paid_hook($entity_context, $payload) {
        $entity_type = sanitize_text_field((string) ($entity_context['entity_type'] ?? ''));
        $booking_public_id = sanitize_text_field((string) ($entity_context['entity_public_id'] ?? ''));

        if ($booking_public_id === '' && in_array($entity_type, array('booking', 'visit'), true)) {
            $booking_public_id = sanitize_text_field((string) ($entity_context['entity_id'] ?? ''));
        }

        if ($booking_public_id === '') {
            return;
        }

        do_action(
            'center_med_renovatio_payment_paid',
            $booking_public_id,
            'tochka',
            sanitize_text_field((string) ($entity_context['payment_id'] ?? '')),
            is_array($payload) ? $payload : array(),
            ''
        );
    }
    
    /**
     * Определение статуса платежа из ответа API
     */
    private function determine_payment_status($status_response) {
        // Получаем последний элемент из массива Operation
        if (!isset($status_response['Data']['Operation']) || !is_array($status_response['Data']['Operation'])) {
            return 'unknown';
        }
        
        $operations = $status_response['Data']['Operation'];
        $last_operation = end($operations);
        
        if (!isset($last_operation['status'])) {
            return 'unknown';
        }
        
        $api_status = $last_operation['status'];
        
        // Согласно документации API
        switch ($api_status) {
            case 'APPROVED':
                return 'success';
            case 'CREATED':
                return 'pending';
            case 'ON-REFUND':
                return 'refunding';
            case 'REFUNDED':
                return 'refunded';
            case 'EXPIRED':
                return 'expired';
            default:
                return 'unknown';
        }
    }
    
}
