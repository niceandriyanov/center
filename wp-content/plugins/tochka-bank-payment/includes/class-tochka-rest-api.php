<?php
/**
 * REST API для работы с платежами Точка Банка
 */

if (!defined('ABSPATH')) {
    exit;
}

class TochkaRestAPI {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Регистрация REST API маршрутов
     */
    public function register_routes() {
        // Создание платежа
        register_rest_route('tochka/v1', '/payments', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_payment'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'order_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'ID заказа'
                ),
                'amount' => array(
                    'required' => true,
                    'type' => 'number',
                    'description' => 'Сумма платежа'
                ),
                'description' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => '',
                    'description' => 'Описание платежа'
                ),
                'return_url' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => '',
                    'description' => 'URL для возврата после оплаты'
                )
            )
        ));
        
        // Создание платежа с чеком
        register_rest_route('tochka/v1', '/payments-with-receipt', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_payment_with_receipt'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'order_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'ID заказа'
                ),
                'amount' => array(
                    'required' => true,
                    'type' => 'number',
                    'description' => 'Сумма платежа'
                ),
                'description' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => '',
                    'description' => 'Описание платежа'
                ),
                'return_url' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => '',
                    'description' => 'URL для возврата после оплаты'
                ),
                'receipt_data' => array(
                    'required' => true,
                    'type' => 'object',
                    'description' => 'Данные чека (customer и items)'
                )
            )
        ));
        
        // Проверка статуса платежа
        register_rest_route('tochka/v1', '/payments/(?P<order_id>[a-zA-Z0-9_-]+)/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_payment_status'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'order_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'ID заказа'
                )
            )
        ));
        
        // Получение информации о платеже
        register_rest_route('tochka/v1', '/payments/(?P<order_id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_payment_info'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'order_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'ID заказа'
                )
            )
        ));
        
        // Эндпоинт для получения вебхуков от Точки
        register_rest_route('tochka/v1', '/webhook', array(
            'methods' => ['POST', 'GET'],
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true', // Без аутентификации для вебхуков
            'args' => array()
        ));
    }
    
    /**
     * Проверка прав доступа
     */
    public function check_permission($request) {
        // Используем существующую систему авторизации
        global $rest_users;
        $auth = $rest_users->check_auth($request);
        if ($auth !== true) {
            return $auth;
        }
        
        // Дополнительная проверка прав пользователя
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return new WP_Error('no_user', 'User not found', array('status' => 401));
        }
        
        return true;
    }
    
    /**
     * Создание платежа
     */
    public function create_payment($request) {
        $order_id = $request->get_param('order_id');
        $amount = floatval($request->get_param('amount'));
        $description = $request->get_param('description');
        $return_url = $request->get_param('return_url');
        
        // Валидация
        if (empty($order_id)) {
            return new WP_Error('missing_order_id', 'Order ID is required', array('status' => 400));
        }
        
        if ($amount <= 0) {
            return new WP_Error('invalid_amount', 'Amount must be greater than 0', array('status' => 400));
        }
        
        // Получаем авторизованного пользователя
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return new WP_Error('no_user', 'User not found', array('status' => 401));
        }
        
        // Создаем платеж используя существующий функционал
        $payment_handler = new TochkaPayment();
        $result = $payment_handler->create_payment($order_id, $amount, $description, $return_url);
        
        if (is_wp_error($result)) {
            return new WP_Error('payment_creation_failed', $result->get_error_message(), array('status' => 500));
        }
        
        // Возвращаем результат
        return array(
            'success' => true,
            'data' => array(
                'order_id' => $order_id,
                'payment_id' => $result['Data']['operationId'] ?? '',
                'payment_url' => $result['Data']['paymentLink'] ?? '',
                'amount' => $amount,
                'status' => 'created',
                'user_id' => $user->ID,
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Создание платежа с чеком
     */
    public function create_payment_with_receipt($request) {
        $order_id = $request->get_param('order_id');
        $amount = floatval($request->get_param('amount'));
        $description = $request->get_param('description');
        $return_url = $request->get_param('return_url');
        $receipt_data = $request->get_param('receipt_data');
        
        // Валидация
        if (empty($order_id)) {
            return new WP_Error('missing_order_id', 'Order ID is required', array('status' => 400));
        }
        
        if ($amount <= 0) {
            return new WP_Error('invalid_amount', 'Amount must be greater than 0', array('status' => 400));
        }
        
        if (empty($receipt_data) || !is_array($receipt_data)) {
            return new WP_Error('missing_receipt_data', 'Receipt data is required', array('status' => 400));
        }
        
        if (empty($receipt_data['customer']) || empty($receipt_data['items'])) {
            return new WP_Error('invalid_receipt_data', 'Receipt data must contain customer and items', array('status' => 400));
        }
        
        // Получаем авторизованного пользователя
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return new WP_Error('no_user', 'User not found', array('status' => 401));
        }
        
        // Создаем платеж с чеком используя новый метод
        $payment_handler = new TochkaPayment();
        $result = $payment_handler->create_payment_with_receipt($order_id, $amount, $description, $return_url, $receipt_data);
        
        if (is_wp_error($result)) {
            return new WP_Error('payment_creation_failed', $result->get_error_message(), array('status' => 500));
        }
        
        // Возвращаем результат
        return array(
            'success' => true,
            'data' => array(
                'order_id' => $order_id,
                'payment_id' => $result['Data']['operationId'] ?? '',
                'payment_url' => $result['Data']['paymentLink'] ?? '',
                'amount' => $amount,
                'status' => 'created',
                'user_id' => $user->ID,
                'receipt_sent' => true,
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Получение статуса платежа
     */
    public function get_payment_status($request) {
        $order_id = $request->get_param('order_id');
        
        if (empty($order_id)) {
            return new WP_Error('missing_order_id', 'Order ID is required', array('status' => 400));
        }
        
        // Получаем payment_id из БД
        global $wpdb;
        $table_name = $wpdb->prefix . 'tochka_payments';
        $payment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT payment_id FROM {$table_name} WHERE order_id = %s",
            $order_id
        ));
        
        if (empty($payment_id)) {
            return new WP_Error('payment_not_found', 'Payment not found for this order', array('status' => 404));
        }
        
        // Проверяем статус через API
        $payment_handler = new TochkaPayment();
        $status_response = $payment_handler->check_payment_status($payment_id);
        
        if (is_wp_error($status_response)) {
            return new WP_Error('status_check_failed', $status_response->get_error_message(), array('status' => 500));
        }
        
        // Определяем статус
        $status = 'unknown';
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
        
        return array(
            'success' => true,
            'data' => array(
                'order_id' => $order_id,
                'payment_id' => $payment_id,
                'status' => $status,
                'api_response' => $status_response
            )
        );
    }
    
    /**
     * Получение информации о платеже
     */
    public function get_payment_info($request) {
        $order_id = $request->get_param('order_id');
        
        if (empty($order_id)) {
            return new WP_Error('missing_order_id', 'Order ID is required', array('status' => 400));
        }
        
        // Получаем информацию из БД
        global $wpdb;
        $table_name = $wpdb->prefix . 'tochka_payments';
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE order_id = %s",
            $order_id
        ), ARRAY_A);
        
        if (empty($payment)) {
            return new WP_Error('payment_not_found', 'Payment not found for this order', array('status' => 404));
        }
        
        return array(
            'success' => true,
            'data' => array(
                'order_id' => $payment['order_id'],
                'payment_id' => $payment['payment_id'],
                'status' => $payment['status'],
                'amount' => $payment['amount'],
                'currency' => $payment['currency'],
                'payment_url' => $payment['payment_url'],
                'created_at' => $payment['created_at'],
                'updated_at' => $payment['updated_at']
            )
        );
    }
    
    /**
     * Обработка вебхуков от Точки
     * КРИТИЧЕСКИ ВАЖНО: Всегда возвращать HTTP 200, иначе Точка отключит вебхуки
     */
    public function handle_webhook($request) {
        try {
            // Получаем тело запроса
            $webhook_data = $request->get_body();
            if (empty($webhook_data)) {
                return new WP_REST_Response(array('status' => 'error', 'message' => 'Empty data'), 200);
            }
            
            // Декодируем JWT без проверки подписи
            $jwt_parts = explode('.', $webhook_data);
            if (count($jwt_parts) === 3) {
                $payload = json_decode(base64_decode($jwt_parts[1]), true);
                if ($payload) {
                    error_log('Webhook: '.print_r($payload, true));
                    $this->process_webhook_data($payload);
                } else {
                    return new WP_REST_Response(array('status' => 'error', 'message' => 'Invalid JWT payload'), 200);
                }
            } else {
                return new WP_REST_Response(array('status' => 'error', 'message' => 'Invalid JWT format'), 200);
            }
            
            // ВСЕГДА возвращаем 200, даже при ошибках
            return new WP_REST_Response(array('status' => 'success', 'message' => 'Webhook processed'), 200);
            
        } catch (Exception $e) {
            // КРИТИЧЕСКИ ВАЖНО: Логируем ошибку, но возвращаем 200
            $this->log_webhook('error', 'Exception in webhook handler: ' . $e->getMessage());
            return new WP_REST_Response(array('status' => 'error', 'message' => 'Internal error'), 200);
        }
    }
    
    /**
     * Валидация JWT токена вебхука
     */
    private function validate_webhook_jwt($jwt_token) {
        try {
            // Получаем публичный ключ Точки
            $public_key = $this->get_tochka_public_key();
            if (is_wp_error($public_key)) {
                return $public_key;
            }
            
            // Проверяем, что это JWT токен
            $parts = explode('.', $jwt_token);
            if (count($parts) !== 3) {
                return new WP_Error('invalid_jwt', 'Invalid JWT format');
            }
            
            // Декодируем заголовок для проверки алгоритма
            $header = json_decode(base64_decode($parts[0]), true);
            if (!$header || !isset($header['alg']) || $header['alg'] !== 'RS256') {
                return new WP_Error('invalid_algorithm', 'Invalid JWT algorithm. Expected RS256');
            }
            
            // Проверяем подпись
            $signature_valid = $this->verify_jwt_signature($jwt_token, $public_key);
            if (!$signature_valid) {
                return new WP_Error('invalid_signature', 'Invalid JWT signature');
            }
            
            // Декодируем payload
            $payload = json_decode(base64_decode($parts[1]), true);
            if (!$payload) {
                return new WP_Error('invalid_payload', 'Invalid JWT payload');
            }
            
            return $payload;
            
        } catch (Exception $e) {
            return new WP_Error('jwt_validation_error', 'JWT validation error: ' . $e->getMessage());
        }
    }
    
    /**
     * Получение публичного ключа Точки
     */
    private function get_tochka_public_key() {
        try {
            // Получаем актуальный публичный ключ с сервера Точки
            $response = wp_remote_get('https://enter.tochka.com/doc/openapi/static/keys/public', array(
                'timeout' => 10,
                'sslverify' => false
            ));
            
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $key_data = json_decode($body, true);
                
                if ($key_data && isset($key_data['kty'])) {
                    $this->log_webhook('key', 'Public key fetched from Tochka server');
                    return $key_data;
                }
            }
            
            // Fallback на статический ключ из документации
            $this->log_webhook('key', 'Using fallback public key from documentation');
            $json_key = '{"kty":"RSA","e":"AQAB","n":"rwm77av7GIttq-JF1itEgLCGEZW_zz16RlUQVYlLbJtyRSu61fCec_rroP6PxjXU2uLzUOaGaLgAPeUZAJrGuVp9nryKgbZceHckdHDYgJd9TsdJ1MYUsXaOb9joN9vmsCscBx1lwSlFQyNQsHUsrjuDk-opf6RCuazRQ9gkoDCX70HV8WBMFoVm-YWQKJHZEaIQxg_DU4gMFyKRkDGKsYKA0POL-UgWA1qkg6nHY5BOMKaqxbc5ky87muWB5nNk4mfmsckyFv9j1gBiXLKekA_y4UwG2o1pbOLpJS3bP_c95rm4M9ZBmGXqfOQhbjz8z-s9C11i-jmOQ2ByohS-ST3E5sqBzIsxxrxyQDTw--bZNhzpbciyYW4GfkkqyeYoOPd_84jPTBDKQXssvj8ZOj2XboS77tvEO1n1WlwUzh8HPCJod5_fEgSXuozpJtOggXBv0C2ps7yXlDZf-7Jar0UYc_NJEHJF-xShlqd6Q3sVL02PhSCM-ibn9DN9BKmD"}';
            
            return json_decode($json_key, true);
            
        } catch (Exception $e) {
            $this->log_webhook('error', 'Failed to get public key: ' . $e->getMessage());
            return new WP_Error('key_error', 'Failed to get public key: ' . $e->getMessage());
        }
    }
    
    /**
     * Проверка подписи JWT токена
     */
    private function verify_jwt_signature($jwt_token, $public_key) {
        try {
            // Разбиваем JWT на части
            $parts = explode('.', $jwt_token);
            if (count($parts) !== 3) {
                return false;
            }
            
            $header = $parts[0];
            $payload = $parts[1];
            $signature = $parts[2];
            
            // Создаем данные для проверки подписи
            $data = $header . '.' . $payload;
            $signature_binary = base64_decode(strtr($signature, '-_', '+/'));
            
            // Создаем публичный ключ из JWK
            $public_key_pem = $this->jwk_to_pem($public_key);
            if (!$public_key_pem) {
                return false;
            }
            
            // Проверяем подпись
            $public_key_resource = openssl_pkey_get_public($public_key_pem);
            if (!$public_key_resource) {
                return false;
            }
            
            $result = openssl_verify($data, $signature_binary, $public_key_resource, OPENSSL_ALGO_SHA256);
            
            // Освобождаем ресурс (функция устарела в PHP 8.0+)
            if (function_exists('openssl_pkey_free')) {
                openssl_pkey_free($public_key_resource);
            }
            
            return $result === 1;
            
        } catch (Exception $e) {
            $this->log_webhook('error', 'Signature verification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Конвертация JWK в PEM формат
     */
    private function jwk_to_pem($jwk) {
        try {
            $n = base64_decode(strtr($jwk['n'], '-_', '+/'));
            $e = base64_decode(strtr($jwk['e'], '-_', '+/'));
            
            // Создаем RSA ключ
            $rsa = array(
                'n' => $n,
                'e' => $e
            );
            
            // Конвертируем в PEM
            $public_key = openssl_pkey_new(array(
                'n' => $rsa['n'],
                'e' => $rsa['e']
            ));
            
            if (!$public_key) {
                return false;
            }
            
            $public_key_pem = '';
            openssl_pkey_export($public_key, $public_key_pem);
            openssl_pkey_free($public_key);
            
            return $public_key_pem;
            
        } catch (Exception $e) {
            $this->log_webhook('error', 'JWK to PEM conversion error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обработка данных вебхука
     */
    private function process_webhook_data($webhook_data) {
        try {
            // Проверяем тип вебхука
            $webhook_type = $webhook_data['webhookType'] ?? 'unknown';
            
            // Сохраняем данные
            $this->save_webhook_data($webhook_data);
            
            // Обрабатываем только acquiringInternetPayment
            if ($webhook_type === 'acquiringInternetPayment') {
                $this->process_acquiring_payment($webhook_data);
            }
            
        } catch (Exception $e) {
            error_log('Tochka Bank: Error processing webhook data: ' . $e->getMessage());
        }
    }
    
    /**
     * Анализ структуры вебхука
     */
    private function analyze_webhook_structure($webhook_data) {
        try {
            $this->log_webhook('analysis', '=== WEBHOOK STRUCTURE ANALYSIS ===');
            
            // Анализируем все ключи верхнего уровня
            $top_level_keys = array_keys($webhook_data);
            $this->log_webhook('analysis', 'Top-level keys: ' . implode(', ', $top_level_keys));
            
            // Анализируем каждый ключ
            foreach ($webhook_data as $key => $value) {
                $this->log_webhook('analysis', "Key '{$key}': " . gettype($value) . 
                    (is_array($value) ? ' (array with ' . count($value) . ' items)' : ' = ' . (is_string($value) ? substr($value, 0, 100) : $value)));
                
                // Если это массив, анализируем его структуру
                if (is_array($value)) {
                    $this->analyze_array_structure($key, $value, 1);
                }
            }
            
            $this->log_webhook('analysis', '=== END STRUCTURE ANALYSIS ===');
            
        } catch (Exception $e) {
            $this->log_webhook('error', 'Error analyzing webhook structure: ' . $e->getMessage());
        }
    }
    
    /**
     * Рекурсивный анализ структуры массива
     */
    private function analyze_array_structure($key, $array, $depth = 1) {
        $indent = str_repeat('  ', $depth);
        
        foreach ($array as $sub_key => $sub_value) {
            $this->log_webhook('analysis', "{$indent}{$key}.{$sub_key}: " . gettype($sub_value) . 
                (is_array($sub_value) ? ' (array with ' . count($sub_value) . ' items)' : ' = ' . (is_string($sub_value) ? substr($sub_value, 0, 50) : $sub_value)));
            
            // Если это массив и глубина меньше 3, анализируем дальше
            if (is_array($sub_value) && $depth < 3) {
                $this->analyze_array_structure($key . '.' . $sub_key, $sub_value, $depth + 1);
            }
        }
    }
    
    /**
     * Обработка эквайрингового платежа
     */
    private function process_acquiring_payment($webhook_data) {
        try {
            // Извлекаем основные поля
            $operation_id = $webhook_data['operationId'] ?? null;
            $amount = $webhook_data['amount'] ?? null;
            $status = $webhook_data['status'] ?? null;
            
            // Обрабатываем платеж и обновляем статус в таблице платежей
            $payment_record = $this->update_order_status($operation_id, $status, $amount, $webhook_data);
            $entity_context = $this->build_entity_context($payment_record);
            $external_event_id = $this->extract_external_event_id($webhook_data);
            
            /**
             * Action: tochka_acquiring_payment_processed
             * 
             * Вызывается после обработки эквайрингового платежа
             * Можно использовать для отправки уведомлений админу
             *
             * @param array|null $order Legacy-параметр для совместимости
             * @param string $status Статус платежа
             * @param float $amount Сумма платежа
             * @param array $webhook_data Полные данные вебхука
             */
            do_action('tochka_acquiring_payment_processed', null, $status, $amount, $webhook_data);

            /**
             * Универсальный хук: статус платежа по сущности изменен.
             *
             * @param array  $entity_context Контекст сущности: entity_type/entity_id/entity_public_id/order_id.
             * @param string $status Статус провайдера.
             * @param mixed  $amount Сумма.
             * @param array  $webhook_data Полный payload.
             */
            do_action('tochka_payment_entity_status_changed', $entity_context, $status, $amount, $webhook_data);

            if ($this->is_paid_status($status)) {
                /**
                 * Универсальный хук успешной оплаты.
                 *
                 * @param array  $entity_context Контекст сущности.
                 * @param string $provider_payment_id ID платежа у провайдера.
                 * @param string $external_event_id Внешний ID события (для идемпотентности).
                 * @param array  $webhook_data Полный payload.
                 */
                do_action(
                    'tochka_payment_entity_paid',
                    $entity_context,
                    sanitize_text_field((string) ($operation_id ?? '')),
                    $external_event_id,
                    $webhook_data
                );
            }

            $this->trigger_renovatio_payment_hook($entity_context, $operation_id, $external_event_id, $webhook_data, $status);
            
        } catch (Exception $e) {
            error_log('Tochka Bank: Error processing acquiring payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Поиск полей связанных с платежом
     */
    private function find_payment_fields($webhook_data) {
        $payment_fields = array();
        
        // Ищем поля которые могут содержать информацию о платеже
        $possible_fields = array(
            'orderId', 'order_id', 'orderNumber', 'order_number',
            'customerCode', 'customer_code', 'customerId', 'customer_id',
            'merchantId', 'merchant_id', 'terminalId', 'terminal_id',
            'transactionId', 'transaction_id', 'paymentId', 'payment_id',
            'receiptNumber', 'receipt_number', 'checkNumber', 'check_number',
            'description', 'comment', 'purpose', 'reason'
        );
        
        foreach ($possible_fields as $field) {
            if (isset($webhook_data[$field])) {
                $payment_fields[$field] = $webhook_data[$field];
                $this->log_webhook('acquiring', "Found payment field '{$field}': " . $webhook_data[$field]);
            }
        }
        
        // Ищем вложенные объекты
        foreach ($webhook_data as $key => $value) {
            if (is_array($value)) {
                $this->find_payment_fields_in_array($key, $value, $payment_fields);
            }
        }
        
        if (!empty($payment_fields)) {
            $this->log_webhook('acquiring', 'Payment fields found: ' . json_encode($payment_fields, JSON_UNESCAPED_UNICODE));
        }
    }
    
    /**
     * Рекурсивный поиск полей платежа в массивах
     */
    private function find_payment_fields_in_array($parent_key, $array, &$payment_fields, $depth = 1) {
        if ($depth > 3) return; // Ограничиваем глубину поиска
        
        foreach ($array as $key => $value) {
            $full_key = $parent_key . '.' . $key;
            
            if (is_array($value)) {
                $this->find_payment_fields_in_array($full_key, $value, $payment_fields, $depth + 1);
            } else {
                // Проверяем, не является ли это полем платежа
                $lower_key = strtolower($key);
                if (strpos($lower_key, 'order') !== false || 
                    strpos($lower_key, 'customer') !== false || 
                    strpos($lower_key, 'merchant') !== false ||
                    strpos($lower_key, 'transaction') !== false ||
                    strpos($lower_key, 'payment') !== false ||
                    strpos($lower_key, 'receipt') !== false ||
                    strpos($lower_key, 'check') !== false) {
                    
                    $payment_fields[$full_key] = $value;
                    $this->log_webhook('acquiring', "Found nested payment field '{$full_key}': " . $value);
                }
            }
        }
    }
    
    /**
     * Сохранение данных вебхука в БД
     */
    private function save_webhook_data($webhook_data) {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'tochka_webhooks';
            
            // Создаем таблицу если не существует
            $this->create_webhooks_table();
            
            // Извлекаем структурированные данные
            $extracted_data = $this->extract_structured_data($webhook_data);
            $external_event_id = $extracted_data['external_event_id'];

            // Идемпотентность: если событие уже обработано, повторно не сохраняем.
            if (!empty($external_event_id)) {
                $existing_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE external_event_id = %s LIMIT 1",
                    $external_event_id
                ));

                if (!empty($existing_id)) {
                    $this->log_webhook('duplicate', 'Duplicate webhook skipped: ' . $external_event_id);
                    return;
                }
            }
            
            $result = $wpdb->insert(
                $table_name,
                array(
                    'external_event_id' => $external_event_id,
                    'webhook_type' => $webhook_data['webhookType'] ?? 'unknown',
                    'operation_id' => $extracted_data['operation_id'],
                    'amount' => $extracted_data['amount'],
                    'currency' => $extracted_data['currency'],
                    'status' => $extracted_data['status'],
                    'entity_type' => $extracted_data['entity_type'],
                    'entity_id' => $extracted_data['entity_id'],
                    'entity_public_id' => $extracted_data['entity_public_id'],
                    'order_id' => $extracted_data['order_id'],
                    'customer_code' => $extracted_data['customer_code'],
                    'merchant_id' => $extracted_data['merchant_id'],
                    'terminal_id' => $extracted_data['terminal_id'],
                    'transaction_id' => $extracted_data['transaction_id'],
                    'receipt_number' => $extracted_data['receipt_number'],
                    'description' => $extracted_data['description'],
                    'raw_data' => json_encode($webhook_data, JSON_UNESCAPED_UNICODE),
                    'analysis_data' => json_encode($extracted_data['analysis'], JSON_UNESCAPED_UNICODE),
                    'created_at' => current_time('mysql')
                )
            );
            
            if ($result) {
                $this->log_webhook('saved', 'Webhook data saved to database with ID: ' . $wpdb->insert_id);
            } else {
                $this->log_webhook('error', 'Failed to save webhook data: ' . $wpdb->last_error);
            }
            
        } catch (Exception $e) {
            $this->log_webhook('error', 'Error saving webhook data: ' . $e->getMessage());
        }
    }
    
    /**
     * Извлечение структурированных данных из вебхука
     */
    private function extract_structured_data($webhook_data) {
        $extracted = array(
            'external_event_id' => null,
            'operation_id' => null,
            'amount' => null,
            'currency' => null,
            'status' => null,
            'entity_type' => null,
            'entity_id' => null,
            'entity_public_id' => null,
            'order_id' => null,
            'customer_code' => null,
            'merchant_id' => null,
            'terminal_id' => null,
            'transaction_id' => null,
            'receipt_number' => null,
            'description' => null,
            'analysis' => array()
        );
        
        // Извлекаем основные поля
        $extracted['operation_id'] = $webhook_data['operationId'] ?? $webhook_data['operation_id'] ?? null;
        $extracted['external_event_id'] = $this->extract_external_event_id($webhook_data);
        $extracted['amount'] = $webhook_data['amount'] ?? null;
        $extracted['currency'] = $webhook_data['currency'] ?? null;
        $extracted['status'] = $webhook_data['status'] ?? null;
        $extracted['entity_type'] = sanitize_text_field((string) ($webhook_data['entityType'] ?? $webhook_data['entity_type'] ?? ''));
        $extracted['entity_id'] = sanitize_text_field((string) ($webhook_data['entityId'] ?? $webhook_data['entity_id'] ?? $webhook_data['bookingPublicId'] ?? ''));
        $extracted['entity_public_id'] = sanitize_text_field((string) ($webhook_data['entityPublicId'] ?? $webhook_data['entity_public_id'] ?? $webhook_data['bookingPublicId'] ?? ''));
        
        // Ищем поля заказа
        $extracted['order_id'] = $webhook_data['orderId'] ?? $webhook_data['order_id'] ?? 
                                $webhook_data['orderNumber'] ?? $webhook_data['order_number'] ?? null;
        
        // Ищем поля клиента
        $extracted['customer_code'] = $webhook_data['customerCode'] ?? $webhook_data['customer_code'] ?? 
                                     $webhook_data['customerId'] ?? $webhook_data['customer_id'] ?? null;
        
        // Ищем поля мерчанта
        $extracted['merchant_id'] = $webhook_data['merchantId'] ?? $webhook_data['merchant_id'] ?? null;
        $extracted['terminal_id'] = $webhook_data['terminalId'] ?? $webhook_data['terminal_id'] ?? null;
        
        // Ищем поля транзакции
        $extracted['transaction_id'] = $webhook_data['transactionId'] ?? $webhook_data['transaction_id'] ?? 
                                      $webhook_data['paymentId'] ?? $webhook_data['payment_id'] ?? null;
        
        // Ищем поля чека
        $extracted['receipt_number'] = $webhook_data['receiptNumber'] ?? $webhook_data['receipt_number'] ?? 
                                      $webhook_data['checkNumber'] ?? $webhook_data['check_number'] ?? null;
        
        // Ищем описание
        $extracted['description'] = $webhook_data['description'] ?? $webhook_data['comment'] ?? 
                                   $webhook_data['purpose'] ?? $webhook_data['reason'] ?? null;
        
        // Анализируем вложенные структуры
        $extracted['analysis'] = $this->analyze_nested_structures($webhook_data);
        
        return $extracted;
    }

    /**
     * Получить внешний ID события из payload.
     *
     * @param array $webhook_data Payload вебхука.
     * @return string
     */
    private function extract_external_event_id($webhook_data) {
        $event_id = $webhook_data['eventId'] ?? $webhook_data['event_id'] ?? $webhook_data['id'] ?? '';
        $event_id = sanitize_text_field((string) $event_id);

        if ($event_id !== '') {
            return $event_id;
        }

        // Фолбек-ключ для идемпотентности, если провайдер не прислал event ID.
        $operation_id = sanitize_text_field((string) ($webhook_data['operationId'] ?? $webhook_data['operation_id'] ?? ''));
        $status = sanitize_text_field((string) ($webhook_data['status'] ?? ''));
        $webhook_type = sanitize_text_field((string) ($webhook_data['webhookType'] ?? 'unknown'));

        if ($operation_id === '') {
            return null;
        }

        return substr("op:{$operation_id}|status:{$status}|type:{$webhook_type}", 0, 191);
    }
    
    /**
     * Анализ вложенных структур
     */
    private function analyze_nested_structures($webhook_data) {
        $analysis = array();
        
        foreach ($webhook_data as $key => $value) {
            if (is_array($value)) {
                $analysis[$key] = array(
                    'type' => 'array',
                    'count' => count($value),
                    'keys' => array_keys($value),
                    'sample_values' => array_slice($value, 0, 3) // Первые 3 элемента для анализа
                );
                
                // Если это объект с известными полями
                if (isset($value['id']) || isset($value['name']) || isset($value['code'])) {
                    $analysis[$key]['structure'] = 'object';
                } elseif (is_numeric(array_keys($value)[0] ?? '')) {
                    $analysis[$key]['structure'] = 'indexed_array';
                } else {
                    $analysis[$key]['structure'] = 'associative_array';
                }
            } else {
                $analysis[$key] = array(
                    'type' => gettype($value),
                    'value' => is_string($value) ? substr($value, 0, 100) : $value
                );
            }
        }
        
        return $analysis;
    }
    
    /**
     * Создание таблицы для вебхуков
     */
    private function create_webhooks_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tochka_webhooks';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            external_event_id varchar(191) DEFAULT NULL,
            webhook_type varchar(50) NOT NULL,
            operation_id varchar(255) DEFAULT '',
            amount decimal(10,2) DEFAULT NULL,
            currency varchar(3) DEFAULT NULL,
            status varchar(50) DEFAULT NULL,
            entity_type varchar(50) DEFAULT NULL,
            entity_id varchar(191) DEFAULT NULL,
            entity_public_id varchar(191) DEFAULT NULL,
            order_id varchar(255) DEFAULT NULL,
            customer_code varchar(255) DEFAULT NULL,
            merchant_id varchar(255) DEFAULT NULL,
            terminal_id varchar(255) DEFAULT NULL,
            transaction_id varchar(255) DEFAULT NULL,
            receipt_number varchar(255) DEFAULT NULL,
            description text DEFAULT NULL,
            raw_data longtext NOT NULL,
            analysis_data longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY external_event_id (external_event_id),
            KEY webhook_type (webhook_type),
            KEY operation_id (operation_id),
            KEY entity_lookup (entity_type, entity_id),
            KEY order_id (order_id),
            KEY customer_code (customer_code),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Логирование вебхуков
     */
    private function log_webhook($type, $message) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        );
        
        // Записываем в лог файл
        $log_file = WP_CONTENT_DIR . '/tochka-webhook.log';
        $log_line = '[' . $log_entry['timestamp'] . '] ' . $log_entry['type'] . ': ' . $log_entry['message'] . ' (IP: ' . $log_entry['ip'] . ')' . PHP_EOL;
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
        
        // Также записываем в WordPress debug.log если включен
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Tochka Webhook [' . $type . ']: ' . $message);
        }
    }
    
    /**
     * Обновление статуса заказа на основе вебхука
     */
    private function update_order_status($operation_id, $status, $amount, $webhook_data) {
        try {
            if (empty($operation_id)) {
                return null;
            }
            
            // Ищем заказ по operation_id в таблице tochka_payments
            global $wpdb;
            $table_name = $wpdb->prefix . 'tochka_payments';
            
            $payment_record = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE payment_id = %s",
                $operation_id
            ), ARRAY_A);
            
            if (!$payment_record) {
                return null;
            }
            
            $order_id = $payment_record['order_id'];
            
            // Определяем новый статус заказа на основе статуса платежа
            $new_order_status = $this->map_payment_status_to_order_status($status);
            
            // Обновляем статус в таблице tochka_payments
            $wpdb->update(
                $table_name,
                array(
                    'status' => $new_order_status,
                    'callback_data' => json_encode($webhook_data),
                    'updated_at' => current_time('mysql')
                ),
                array('payment_id' => $operation_id),
                array('%s', '%s', '%s'),
                array('%s')
            );
            
            do_action('tochka_payment_status_updated', $order_id, $status, $amount, $webhook_data);
            return $payment_record;
            
        } catch (Exception $e) {
            error_log('Tochka Bank: Error updating order status: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Собирает универсальный контекст сущности из записи платежа.
     *
     * @param array|null $payment_record Запись платежа.
     * @return array
     */
    private function build_entity_context($payment_record) {
        if (!is_array($payment_record)) {
            return array(
                'entity_type' => 'visit',
                'entity_id' => '',
                'entity_public_id' => '',
                'order_id' => ''
            );
        }

        $entity_type = !empty($payment_record['entity_type'])
            ? sanitize_text_field((string) $payment_record['entity_type'])
            : 'visit';
        $entity_id = !empty($payment_record['entity_id'])
            ? sanitize_text_field((string) $payment_record['entity_id'])
            : sanitize_text_field((string) ($payment_record['order_id'] ?? ''));
        $entity_public_id = sanitize_text_field((string) ($payment_record['entity_public_id'] ?? ''));
        $order_id = sanitize_text_field((string) ($payment_record['order_id'] ?? ''));

        return array(
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'entity_public_id' => $entity_public_id,
            'order_id' => $order_id
        );
    }

    /**
     * Проверка, что провайдерный статус означает успешную оплату.
     *
     * @param string|null $status Статус провайдера.
     * @return bool
     */
    private function is_paid_status($status) {
        return strtoupper((string) $status) === 'APPROVED';
    }

    /**
     * Триггерит интеграционный хук для Renovatio при успешной оплате.
     *
     * @param array  $entity_context Контекст сущности.
     * @param string $operation_id ID платежа у провайдера.
     * @param string $external_event_id Внешний ID события.
     * @param array  $webhook_data Payload.
     * @param string $status Статус провайдера.
     * @return void
     */
    private function trigger_renovatio_payment_hook($entity_context, $operation_id, $external_event_id, $webhook_data, $status) {
        if (!$this->is_paid_status($status)) {
            return;
        }

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
            sanitize_text_field((string) $operation_id),
            $webhook_data,
            $external_event_id
        );
    }
    
    /**
     * Маппинг статуса платежа на статус заказа
     */
    private function map_payment_status_to_order_status($payment_status) {
        switch (strtoupper($payment_status)) {
            case 'APPROVED':
                return 'completed';
            case 'CREATED':
                return 'pending';
            case 'ON-REFUND':
                return 'refunding';
            case 'REFUNDED':
                return 'refunded';
            case 'EXPIRED':
                return 'cancelled';
            case 'DECLINED':
                return 'failed';
            default:
                return 'pending';
        }
    }
    
    /**
     * Обновление заказа WooCommerce
     */
    private function update_woocommerce_order($order_id, $status, $amount, $webhook_data) {
        try {
            // Проверяем, что WooCommerce активен
            if (!function_exists('wc_get_order')) {
                $this->log_webhook('error', 'WooCommerce is not active, cannot update order');
                return;
            }
            
            $order = wc_get_order($order_id);
            if (!$order) {
                $this->log_webhook('error', "WooCommerce order not found: {$order_id}");
                return;
            }
            
            $this->log_webhook('woocommerce', "Updating WooCommerce order {$order_id} to status: {$status}");
            
            // Обновляем статус заказа
            $order->update_status($status, 'Платеж обработан через Точка Банк');
            
            // Добавляем заметку о платеже
            $order->add_order_note(
                "Платеж обработан через Точка Банк. " .
                "Сумма: {$amount} руб. " .
                "Статус: {$status}"
            );
            
            // Сохраняем мета-данные платежа
            $order->update_meta_data('_tochka_payment_status', $status);
            $order->update_meta_data('_tochka_payment_amount', $amount);
            $order->update_meta_data('_tochka_payment_data', json_encode($webhook_data));
            $order->save();
            
            $this->log_webhook('woocommerce', "WooCommerce order {$order_id} updated successfully");
            
        } catch (Exception $e) {
            $this->log_webhook('error', 'Error updating WooCommerce order: ' . $e->getMessage());
        }
    }
}
