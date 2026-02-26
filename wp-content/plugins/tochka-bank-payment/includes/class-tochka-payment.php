<?php
/**
 * Основной класс для работы с платежами Точка Банка
 */

if (!defined('ABSPATH')) {
    exit;
}

class TochkaPayment {
    
    private $client_id;
    private $client_secret;
    private $redirect_url;
    private $sandbox_mode;
    private $api_url;
    
    public function __construct() {
        $this->client_id = get_option('tochka_client_id', '');
        $this->client_secret = get_option('tochka_client_secret', '');
        $this->redirect_url = get_option('tochka_redirect_url', '');
        $this->sandbox_mode = get_option('tochka_sandbox_mode', '1');
        
        // URL API в зависимости от режима (корректные эндпоинты из документации)
        $this->api_url = $this->sandbox_mode === '1' 
            ? 'https://enter.tochka.com/sandbox/v2/acquiring/v1.0' 
            : 'https://enter.tochka.com/uapi/acquiring/v1.0';
            
        $this->init_hooks();
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        add_action('wp_ajax_tochka_create_payment', array($this, 'create_payment_ajax'));
        add_action('wp_ajax_nopriv_tochka_create_payment', array($this, 'create_payment_ajax'));
    }
    
    /**
     * Создание платежа
     */
    public function create_payment($order_id, $amount, $description = '', $return_url = '') {
        if (empty($this->client_id) || empty($this->client_secret)) {
            return new WP_Error('no_credentials', 'Не настроены учетные данные Точка Банка');
        }
        
        // Получаем токен доступа
        $access_token = $this->get_access_token();
        if (is_wp_error($access_token)) {
            return $access_token;
        }
        
        // Дополнительная проверка токена
        if (empty($access_token)) {
            return new WP_Error('no_token', 'Токен доступа пустой');
        }
        
        error_log('Tochka Bank: Используемый токен: ' . substr($access_token, 0, 20) . '...');
        
        // Получаем customerCode
        $customer_code = $this->get_customer_code();
        
        // Проверяем, что customerCode получен
        if (empty($customer_code)) {
            return new WP_Error('no_customer_code', 
                'CustomerCode не найден. Завершите OAuth flow для получения реального customerCode.'
            );
        }
        
        // Получаем webhook URL для redirect'ов
        $webhook_url = get_option('tochka_webhook_url', home_url('/tochka-payment/webhook/'));
        
        // Формируем redirect URL с параметрами
        $success_url = $return_url ?: $webhook_url;
        $fail_url = $return_url ?: $webhook_url;
        
        // Добавляем параметры к URL
        $success_url = add_query_arg(array(
            'order_id' => $order_id
        ), $success_url);
        
        $fail_url = add_query_arg(array(
            'order_id' => $order_id,
            'status' => 'fail'
        ), $fail_url);
        
        // Формируем данные платежа согласно API Точка Банка (формат из документации)
        $payment_data = array(
            'Data' => array(
                'customerCode' => $customer_code,
                'amount' => number_format($amount, 2, '.', ''),
                'purpose' => $description ?: "Оплата заказа №{$order_id}",
                'redirectUrl' => $success_url,
                'failRedirectUrl' => $fail_url,
                'paymentMode' => array('card', 'sbp'), // Способы оплаты
                'saveCard' => false,
                'preAuthorization' => false,
                'ttl' => 10080 // Время жизни в минутах (7 дней)
            )
        );
        
        // Получаем merchantId (необязательный)
        $merchant_id = $this->get_merchant_id();
        if (!empty($merchant_id)) {
            $payment_data['Data']['merchantId'] = $merchant_id;
        }
        
        // Добавляем consumerId (необязательный)
        $consumer_id = $this->get_consumer_id();
        if (!empty($consumer_id)) {
            $payment_data['Data']['consumerId'] = $consumer_id;
        }
        
        // Добавляем paymentLinkId (необязательный) - используем номер заказа
        $payment_link_id = $this->get_payment_link_id($order_id);
        if (!empty($payment_link_id)) {
            $payment_data['Data']['paymentLinkId'] = $payment_link_id;
        }
        
        // Логируем данные для отладки
        error_log('Tochka Bank: Создание платежа - данные: ' . json_encode($payment_data));
        error_log('Tochka Bank: Создание платежа - токен: ' . substr($access_token, 0, 20) . '...');
        error_log('Tochka Bank: Создание платежа - URL: ' . $this->api_url . '/payments');
        
        // Отправляем запрос в API Точка Банка
        $response = $this->make_api_request('/payments', $payment_data, $access_token);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Сохраняем данные платежа в БД
        $this->save_payment_data($order_id, $response);
        
        return $response;
    }
    
    /**
     * Получение токена доступа (публичный статический метод)
     * Используется во всех классах плагина для получения актуального токена
     */
    public static function get_valid_token() {
        $sandbox_mode = get_option('tochka_sandbox_mode', '1');
        
        // Если тестовый режим включен - используем специальный токен для песочницы
        if ($sandbox_mode === '1') {
            return 'sandbox.jwt.token';
        }
        
        // Если тестовый режим отключен - используем финальный токен
        $final_token = get_option('tochka_final_access_token', '');
        if (!empty($final_token)) {
            if (self::check_token_validity($final_token)) {
                return $final_token;
            } 
        }
        
        // Если финального токена нет или он истек, пытаемся обновить через refresh_token
        $refresh_token = get_option('tochka_refresh_token', '');
        if (!empty($refresh_token)) {
            $new_token = self::refresh_token_static($refresh_token);
            if ($new_token) {
                return $new_token;
            }
        }
        
        // Если финального токена нет - OAuth flow не завершен
        return new WP_Error('no_final_token', 
            'Финальный токен не найден. Обратитесь к администратору для завершения настройки OAuth 2.0. ' .
            'Перейдите в настройки плагина и выполните полный OAuth flow.'
        );
    }
    
    /**
     * Получение токена доступа (приватный метод для использования внутри класса)
     */
    private function get_access_token() {
        return self::get_valid_token();
    }
    
    /**
     * Создание платежной ссылки (упрощенная версия)
     */
    public function create_payment_link($order_id, $amount, $description = '') {
        if (empty($this->client_id) || empty($this->client_secret)) {
            return new WP_Error('no_credentials', 'Не настроены учетные данные Точка Банка');
        }
        
        // Для тестирования создаем простую ссылку
        $payment_data = array(
            'order_id' => $order_id,
            'amount' => $amount,
            'description' => $description ?: "Оплата заказа №{$order_id}",
            'return_url' => $this->redirect_url,
            'client_id' => $this->client_id
        );
        
        // Сохраняем данные платежа
        $this->save_payment_data($order_id, $payment_data);
        
        // Возвращаем URL для redirect'а (в реальной интеграции это будет URL от банка)
        $redirect_params = http_build_query(array(
            'tochka_payment' => 'redirect',
            'payment_id' => 'test_' . $order_id,
            'order_id' => $order_id,
            'status' => 'pending'
        ));
        
        return home_url('/tochka-payment/redirect/?' . $redirect_params);
    }
    
    /**
     * Создание платежа с чеком (новый метод)
     */
    public function create_payment_with_receipt($order_id, $amount, $description = '', $return_url = '', $receipt_data = array()) {
        // Валидация данных чека
        if (empty($receipt_data['customer']) || empty($receipt_data['items'])) {
            return new WP_Error('invalid_receipt_data', 'Receipt data is required (customer and items)');
        }
        
        // Получаем токен доступа
        $access_token = $this->get_access_token();
        if (is_wp_error($access_token)) {
            return $access_token;
        }
        
        // Формируем URL для создания платежа с чеком
        $payment_url = $this->api_url . '/payments-with-receipt';
        
        // Получаем необходимые данные
        $customer_code = $this->get_customer_code();
        $merchant_id = $this->get_merchant_id();
        $consumer_id = $this->get_consumer_id();
        $payment_link_id = $this->get_payment_link_id($order_id);
        
        // Формируем redirect URL с параметрами
        $webhook_url = get_option('tochka_webhook_url', home_url('/tochka-payment/webhook/'));
        $success_url = $return_url ?: $webhook_url;
        $fail_url = $return_url ?: $webhook_url;
        
        // Добавляем параметры к URL
        $success_url = add_query_arg(array(
            'order_id' => $order_id,
            'status' => 'success'
        ), $success_url);
        
        $fail_url = add_query_arg(array(
            'order_id' => $order_id,
            'status' => 'fail'
        ), $fail_url);
        
        // Формируем данные для API
        $payment_data = array(
            'Data' => array(
                'customerCode' => $customer_code,
                'amount' => number_format($amount, 2, '.', ''),
                'purpose' => $description,
                'redirectUrl' => $success_url,
                'failRedirectUrl' => $fail_url,
                'paymentMode' => array('sbp', 'card', 'tinkoff', 'dolyame'),
                'saveCard' => true,
                'consumerId' => $consumer_id,
                'paymentLinkId' => $payment_link_id,
                'preAuthorization' => false,
                'ttl' => 10080
            )
        );
        
        // Добавляем данные чека на том же уровне
        $receipt_data_formatted = $this->format_receipt_data($receipt_data);
        $payment_data['Data'] = array_merge($payment_data['Data'], $receipt_data_formatted);
        
        // Добавляем merchantId если указан
        if (!empty($merchant_id)) {
            $payment_data['Data']['merchantId'] = $merchant_id;
        }
        
        error_log('Tochka Bank: Создание платежа с чеком - URL: ' . $payment_url);
        error_log('Tochka Bank: Данные платежа: ' . json_encode($payment_data));
        
        // Отправляем запрос
        $response = $this->make_api_request('/payments-with-receipt', $payment_data, $access_token);
        
        if (is_wp_error($response)) {
            error_log('Tochka Bank: Ошибка создания платежа с чеком: ' . $response->get_error_message());
            return $response;
        }
        
        // Сохраняем данные платежа в БД
        $this->save_payment_data($order_id, $response);
        
        error_log('Tochka Bank: Платеж с чеком создан успешно - order_id: ' . $order_id);
        
        return $response;
    }
    
    /**
     * Форматирование данных чека для API
     */
    private function format_receipt_data($receipt_data) {
        // Формируем данные клиента
        $client = array(
            'name' => $receipt_data['customer']['name'] ?? '',
            'email' => $receipt_data['customer']['email'] ?? '',
            'phone' => $receipt_data['customer']['phone'] ?? ''
        );
        
        // Формируем позиции чека
        $items = array();
        foreach ($receipt_data['items'] as $item) {
            $items[] = array(
                'vatType' => $item['vatType'] ?? 'none',
                'name' => $item['name'] ?? '',
                'amount' => number_format(floatval($item['amount'] ?? 0), 2, '.', ''),
                'quantity' => floatval($item['quantity'] ?? 1),
                'paymentMethod' => $item['paymentMethod'] ?? 'full_payment',
                'paymentObject' => $item['paymentObject'] ?? 'service',
                'measure' => $item['measure'] ?? 'шт.',
                'Supplier' => array(
                    'phone' => get_option('tochka_supplier_phone', ''),
                    'name' => get_option('tochka_supplier_name', ''),
                    'taxCode' => get_option('tochka_supplier_tax_code', '')
                )
            );
        }
        
        // Формируем данные поставщика из настроек
        $supplier = array(
            'phone' => get_option('tochka_supplier_phone', ''),
            'name' => get_option('tochka_supplier_name', ''),
            'taxCode' => get_option('tochka_supplier_tax_code', '')
        );
        
        return array(
            'taxSystemCode' => $receipt_data['taxSystemCode'] ?? 'osn',
            'Client' => $client,
            'Items' => $items,
            'Supplier' => $supplier
        );
    }
    
    /**
     * Выполнение API запроса
     */
    private function make_api_request($endpoint, $data, $access_token) {
        $url = $this->api_url . $endpoint;
        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        );
        
        error_log('Tochka Bank: API запрос - URL: ' . $url);
        error_log('Tochka Bank: API запрос - заголовки: ' . json_encode($headers));
        error_log('Tochka Bank: API запрос - тело: ' . json_encode($data));
        error_log('Tochka Bank: API запрос - токен в заголовке: ' . substr($access_token, 0, 20) . '...');
        
        // Используем cURL напрямую для правильной обработки Authorization
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $access_token
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        
        $response_body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            error_log('Tochka Bank: cURL ошибка: ' . $curl_error);
            return new WP_Error('curl_error', 'cURL ошибка: ' . $curl_error);
        }
        
        error_log('Tochka Bank: cURL ответ - код: ' . $http_code);
        error_log('Tochka Bank: cURL ответ - тело: ' . $response_body);
        
        $data = json_decode($response_body, true);
        
        if ($http_code >= 400) {
            return new WP_Error('api_error', 'Ошибка API: ' . $response_body);
        }
        
        return $data;
    }
    
    
    /**
     * AJAX обработчик создания платежа
     */
    public function create_payment_ajax() {
        check_ajax_referer('tochka_payment_nonce', 'nonce');
        
        $order_id = intval($_POST['order_id']);
        $amount = floatval($_POST['amount']);
        $description = sanitize_text_field($_POST['description'] ?? '');
        
        $result = $this->create_payment($order_id, $amount, $description);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Получение статуса платежа
     */
    public function get_payment_status($payment_id) {
        // Проверяем, есть ли финальный токен
        $final_token = get_option('tochka_final_access_token', '');
        if (empty($final_token)) {
            return new WP_Error('no_final_token', 
                'Финальный токен не найден. Обратитесь к администратору для завершения настройки OAuth 2.0. ' .
                'Перейдите в настройки плагина и выполните полный OAuth flow.'
            );
        }
        
        $access_token = $this->get_access_token();
        if (is_wp_error($access_token)) {
            return $access_token;
        }
        
        $response = wp_remote_get($this->api_url . "/payments/{$payment_id}", array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    
    /**
     * Статическая проверка валидности токена (для использования в других классах)
     */
    public static function check_token_validity($token) {
        // Сначала проверяем кэш времени истечения
        $token_expires = get_option('tochka_token_expires', 0);
        if ($token_expires > time()) {
            error_log('Токен еще не истек');
            return true; // Токен еще не истек
        }
        
        // Если время истекло, проверяем через API
        error_log('Tochka Bank: Проверяем валидность токена через API...');
        
        $api_url = 'https://enter.tochka.com/uapi/open-banking/v1.0';
        $test_url = $api_url . '/customers';
        
        $response = wp_remote_get($test_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ),
            'timeout' => 10,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        
        // Если токен валиден, получим 200 или другой не 401 код
        // Если токен истек, получим 401 (Unauthorized)
        $is_valid = $http_code !== 401 && $http_code !== 403;
        
        if ($is_valid) {
            // Обновляем время истечения (токен валиден еще 20 часов)
            update_option('tochka_token_expires', time() + (20 * 3600));
        }
        
        return $is_valid;
    }
    
    /**
     * Проверка валидности токена (приватный метод для обратной совместимости)
     */
    private function is_token_valid($token) {
        return self::check_token_validity($token);
    }
    
    /**
     * Получение customerCode
     */
    private function get_customer_code() {
        // Если тестовый режим включен - всегда возвращаем тестовый код
        if ($this->sandbox_mode === '1') {
            return '1234567ab';
        }
        
        // Если тестовый режим отключен - берем из опции
        $customer_code = get_option('tochka_customer_code', '');
        
        if (empty($customer_code)) {
            error_log('Tochka Bank: customerCode не найден! Необходимо завершить OAuth flow для получения реального customerCode.');
            return '';
        }
        
        return $customer_code;
    }
    
    /**
     * Получение merchantId
     */
    private function get_merchant_id() {
        // Если тестовый режим включен - всегда возвращаем тестовый ID
        if ($this->sandbox_mode === '1') {
            return '200000000001056';
        }
        
        // Если тестовый режим отключен - берем из опции
        $merchant_id = get_option('tochka_merchant_id', '');
        
        if (empty($merchant_id)) {
            error_log('Tochka Bank: merchantId не указан! Укажите в настройках плагина.');
            return '';
        }
        
        return $merchant_id;
    }
    
    /**
     * Получение consumerId
     */
    private function get_consumer_id() {
        // Если тестовый режим включен - всегда возвращаем тестовый ID
        if ($this->sandbox_mode === '1') {
            return 'fedac807-078d-45ac-a43b-5c01c57edbf8';
        }
        
        // Если тестовый режим отключен - используем ID текущего пользователя WordPress
        $current_user = wp_get_current_user();
        if ($current_user && $current_user->ID) {
            return 'wp_user_' . $current_user->ID;
        }
        
        // Если пользователь не авторизован - используем значение из настроек
        // (поле убрано из настроек, так как используется ID пользователя WordPress)
        
        // Если ничего не найдено - используем анонимный ID
        return 'wp_anonymous_' . uniqid();
    }
    
    /**
     * Получение paymentLinkId
     */
    private function get_payment_link_id($order_id = null) {
        // Если тестовый режим включен - всегда возвращаем тестовый ID
        if ($this->sandbox_mode === '1') {
            return 'test_payment_link_' . time();
        }
        
        // Если тестовый режим отключен - используем номер заказа
        if (!empty($order_id)) {
            return 'order_' . $order_id;
        }
        
        // Если номер заказа не передан - используем значение из настроек
        // (поле убрано из настроек, так как используется номер заказа)
        
        // Если ничего не найдено - генерируем уникальный ID
        return 'payment_' . uniqid();
    }
    
    /**
     * Статическое обновление токена через refresh_token (для использования в других классах)
     */
    public static function refresh_token_static($refresh_token) {
        $client_id = get_option('tochka_client_id', '');
        $client_secret = get_option('tochka_client_secret', '');
        
        if (empty($client_id) || empty($client_secret)) {
            error_log('Tochka Bank: Не удалось обновить токен - отсутствуют client_id или client_secret');
            return false;
        }
        
        $token_data = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        );
        
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
            error_log('Tochka Bank: Ошибка при обновлении токена - ' . $response->get_error_message());
            return false;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['access_token'])) {
            // Обновляем сохраненный токен
            update_option('tochka_final_access_token', $data['access_token']);
            if (isset($data['refresh_token'])) {
                update_option('tochka_refresh_token', $data['refresh_token']);
            }
            
            // Сохраняем время истечения токена (обычно 24 часа)
            $expires_in = isset($data['expires_in']) ? $data['expires_in'] : 86400; // 24 часа по умолчанию
            update_option('tochka_token_expires', time() + $expires_in);
            
            error_log('Tochka Bank: Токен успешно обновлен, истекает через ' . $expires_in . ' секунд');
            return $data['access_token'];
        }
        
        error_log('Tochka Bank: Не удалось обновить токен - код ответа: ' . $code);
        return false;
    }
    
    /**
     * Обновление токена через refresh_token (приватный метод для обратной совместимости)
     */
    private function refresh_access_token($refresh_token) {
        return self::refresh_token_static($refresh_token);
    }
    
    
    /**
     * Сохранение данных платежа в БД
     */
    private function save_payment_data($order_id, $response) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tochka_payments';
        $entity_context = $this->resolve_entity_context($order_id, $response);
        
        // Извлекаем данные из ответа API
        $payment_id = $response['Data']['operationId'] ?? '';
        $user_id = get_current_user_id();
        $amount = floatval($response['Data']['amount'] ?? 0);
        $currency = 'RUB';
        $payment_url = $response['Data']['paymentLink'] ?? '';
        $status = 'pending';
        
        // Сохраняем данные (REPLACE для обработки дубликатов)
        $result = $wpdb->replace(
            $table_name,
            array(
                'provider' => 'tochka',
                'entity_type' => $entity_context['entity_type'],
                'entity_id' => $entity_context['entity_id'],
                'entity_public_id' => $entity_context['entity_public_id'],
                'order_id' => $order_id,
                'payment_id' => $payment_id,
                'user_id' => $user_id,
                'status' => $status,
                'amount' => $amount,
                'currency' => $currency,
                'payment_url' => $payment_url,
                'callback_data' => json_encode($response),
                'created_at' => current_time('mysql')
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%f',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
        
        if ($result === false) {
            error_log('Tochka Bank: Ошибка сохранения данных платежа в БД: ' . $wpdb->last_error);
        } else {
            error_log("Tochka Bank: Данные платежа сохранены - order_id: {$order_id}, payment_id: {$payment_id}");
        }
    }

    /**
     * Формирует универсальный контекст сущности для сохранения платежа.
     *
     * @param string|int $order_id Идентификатор сущности.
     * @param array      $response Ответ API.
     * @return array
     */
    private function resolve_entity_context($order_id, $response = array()) {
        $order_id = sanitize_text_field((string) $order_id);
        $context = array(
            'entity_type' => 'visit',
            'entity_id' => $order_id,
            'entity_public_id' => ''
        );

        // Если передали UUID как order_id, используем его как публичный ID сущности.
        if (preg_match('/^[a-f0-9-]{36}$/i', $order_id)) {
            $context['entity_public_id'] = $order_id;
        }

        $context = apply_filters('tochka_payment_resolve_entity_context', $context, $order_id, $response);

        $entity_type = sanitize_text_field((string) ($context['entity_type'] ?? 'visit'));
        $entity_id = sanitize_text_field((string) ($context['entity_id'] ?? $order_id));
        $entity_public_id = sanitize_text_field((string) ($context['entity_public_id'] ?? ''));

        if ($entity_id === '') {
            $entity_id = $order_id;
        }

        return array(
            'entity_type' => $entity_type !== '' ? $entity_type : 'visit',
            'entity_id' => $entity_id,
            'entity_public_id' => $entity_public_id
        );
    }
    
    /**
     * Проверка статуса платежа через API
     */
    public function check_payment_status($payment_id) {
        if (empty($payment_id)) {
            return new WP_Error('empty_payment_id', 'Payment ID не может быть пустым');
        }
        
        // Получаем токен доступа
        $access_token = $this->get_access_token();
        if (is_wp_error($access_token)) {
            return $access_token;
        }
        
        // Формируем URL для запроса статуса
        $status_url = $this->api_url . '/payments/' . $payment_id;
        
        error_log("Tochka Bank: Проверка статуса платежа - payment_id: {$payment_id}, URL: {$status_url}");
        
        // Отправляем GET запрос
        $response = wp_remote_get($status_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            error_log('Tochka Bank: Ошибка проверки статуса платежа: ' . $response->get_error_message());
            return $response;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        error_log("Tochka Bank: Ответ проверки статуса - код: {$code}, тело: {$body}");
        
        if ($code === 200) {
            return $data;
        } else {
            return new WP_Error('api_error', 'Ошибка API при проверке статуса: ' . $body, array('code' => $code));
        }
    }
    
    /**
     * Обновление статуса платежа в БД
     */
    public function update_payment_status($order_id, $payment_id, $status_response) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tochka_payments';
        
        // Определяем статус из ответа API
        $status = 'pending';
        
        // Получаем последний элемент из массива Operation
        if (isset($status_response['Data']['Operation']) && is_array($status_response['Data']['Operation'])) {
            $operations = $status_response['Data']['Operation'];
            $last_operation = end($operations);

            if (isset($last_operation['status'])) {
                switch ($last_operation['status']) {
                    case 'APPROVED':
                        $status = 'completed';
                        break;
                    case 'CREATED':
                        $status = 'pending';
                        break;
                    case 'ON-REFUND':
                        $status = 'refunding';
                        break;
                    case 'REFUNDED':
                        $status = 'refunded';
                        break;
                    case 'EXPIRED':
                        $status = 'expired';
                        break;
                    default:
                        $status = 'unknown';
                }
            }
        }
        
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'callback_data' => json_encode($status_response),
                'updated_at' => current_time('mysql')
            ),
            array('order_id' => $order_id),
            array('%s', '%s', '%s'),
            array('%s')
        );
        
        if ($result === false) {
            error_log('Tochka Bank: Ошибка обновления статуса платежа в БД: ' . $wpdb->last_error);
        } else {
            error_log("Tochka Bank: Статус платежа обновлен - order_id: {$order_id}, status: {$status}");
        }
    }
}
