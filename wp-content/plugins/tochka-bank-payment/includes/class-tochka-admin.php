<?php
/**
 * Класс для админ-панели плагина
 */

if (!defined('ABSPATH')) {
    exit;
}

class TochkaAdmin {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_tochka_test_connection', array($this, 'test_connection'));
        add_action('wp_ajax_tochka_create_consent', array($this, 'create_consent_and_get_url'));
        add_action('wp_ajax_tochka_exchange_code', array($this, 'exchange_code_for_token'));
        add_action('wp_ajax_tochka_check_consent', array($this, 'check_consent_status'));
        add_action('wp_ajax_get_customer_code', array($this, 'get_customer_code_ajax'));
        add_action('wp_ajax_tochka_create_tables', array($this, 'create_tables_ajax'));
        
        // AJAX обработчики для вебхуков
        add_action('wp_ajax_tochka_get_webhooks', array($this, 'get_webhooks_ajax'));
        add_action('wp_ajax_tochka_create_webhook', array($this, 'create_webhook_ajax'));
        add_action('wp_ajax_tochka_edit_webhook', array($this, 'edit_webhook_ajax'));
        add_action('wp_ajax_tochka_delete_webhook', array($this, 'delete_webhook_ajax'));
        add_action('wp_ajax_tochka_send_test_webhook', array($this, 'send_test_webhook_ajax'));
        add_action('wp_ajax_tochka_get_webhook_logs', array($this, 'get_webhook_logs_ajax'));
        add_action('wp_ajax_tochka_get_webhook_details', array($this, 'get_webhook_details_ajax'));
        add_action('wp_ajax_tochka_refresh_token', array($this, 'refresh_token_ajax'));
    }
    
    /**
     * Подключение скриптов и стилей
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'settings_page_tochka-bank-settings' && $hook !== 'settings_page_tochka-bank-webhooks') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'tochka-admin-js',
            TOCHKA_BANK_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            TOCHKA_BANK_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'tochka-admin-css',
            TOCHKA_BANK_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            TOCHKA_BANK_PLUGIN_VERSION
        );
        
        wp_localize_script('tochka-admin-js', 'tochka_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tochka_admin_nonce')
        ));
    }
    
    /**
     * Тестирование подключения к API
     */
    public function test_connection() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        $client_id = get_option('tochka_client_id', '');
        $client_secret = get_option('tochka_client_secret', '');
        
        if (empty($client_id) || empty($client_secret)) {
            wp_send_json_error('Не заполнены учетные данные');
        }
        
        $token_data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'scope' => 'accounts balances customers statements sbp payments acquiring'
        );

        $oauth_url = 'https://enter.tochka.com/connect/token';
        
        $response = wp_remote_post($oauth_url, array(
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
            $error_message = $response->get_error_message();
            
            if (strpos($error_message, 'cURL error 28') !== false) {
                wp_send_json_error('Таймаут подключения. Проверьте интернет-соединение и настройки прокси.');
            } elseif (strpos($error_message, 'cURL error 6') !== false) {
                wp_send_json_error('Ошибка DNS. Не удается найти сервер enter.tochka.com');
            } elseif (strpos($error_message, 'cURL error 35') !== false) {
                wp_send_json_error('Ошибка SSL. Проблемы с сертификатом безопасности');
            } else {
                wp_send_json_error('Ошибка подключения: ' . $error_message);
            }
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['access_token'])) {
            $access_token = $data['access_token'];
            $success_message = '🎉 Подключение успешно! Токен получен: ' . substr($access_token, 0, 20) . '...';
            $success_message .= "\n\n✅ Готово к созданию consent для получения прав доступа.";
            
            // Сохраняем токен для дальнейшего использования
            update_option('tochka_temp_access_token', $access_token);
            
            wp_send_json_success($success_message);
        } else {
            $error_message = 'Ошибка авторизации (HTTP ' . $code . ')';
            if (isset($data['error'])) {
                $error_message .= ': ' . $data['error'];
                if (isset($data['error_description'])) {
                    $error_message .= ' - ' . $data['error_description'];
                }
            }
            wp_send_json_error($error_message);
        }
    }
    
    /**
     * Создание consent и получение ссылки для подтверждения
     */
    public function create_consent_and_get_url() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        $access_token = get_option('tochka_temp_access_token', '');
        if (empty($access_token)) {
            wp_send_json_error('Сначала протестируйте подключение');
        }
        
        // Создаем consent для получения конкретных прав
        $consent_result = $this->create_consent($access_token);
        if ($consent_result['success']) {
            $success_message = '✅ Согласие (consent) создано успешно!';
            $success_message .= "\nConsent ID: " . $consent_result['consent_id'];
            $success_message .= "\nСтатус: " . $consent_result['status'];
            
            if ($consent_result['status'] === 'AwaitingAuthorisation') {
                // Формируем URL для подтверждения прав пользователем
                $auth_url = $this->generate_authorization_url($consent_result['consent_id']);
                
                $success_message .= "\n\n⚠️ Требуется подтверждение прав пользователем!";
                $success_message .= "\n\n📋 Инструкция:";
                $success_message .= "\n1. Скопируйте ссылку из поля ниже";
                $success_message .= "\n2. Откройте её в браузере";
                $success_message .= "\n3. Войдите в личный кабинет Точка Банка";
                $success_message .= "\n4. Подтвердите права доступа";
                $success_message .= "\n5. После подтверждения получите код авторизации";
                $success_message .= "\n6. Используйте код для получения финального токена";
                $success_message .= "\n\n💡 После подтверждения consent будет действовать НАВСЕГДА!";
                
                wp_send_json_success(array(
                    'message' => $success_message,
                    'auth_url' => $auth_url,
                    'consent_id' => $consent_result['consent_id']
                ));
            } elseif ($consent_result['status'] === 'Authorised') {
                $success_message .= "\n\n🎉 Все права доступа подтверждены! Плагин готов к работе!";
                wp_send_json_success($success_message);
            }
        } else {
            wp_send_json_error('Ошибка создания согласия: ' . $consent_result['error']);
        }
    }
    
    /**
     * Создание consent для получения прав доступа
     */
    private function create_consent($access_token) {
        // Для consent всегда используем production URL согласно документации
        $api_url = 'https://enter.tochka.com/uapi';
        
        $consent_data = array(
            'Data' => array(
                'permissions' => array(
                    'ReadAccountsBasic',
                    'ReadAccountsDetail',
                    'MakeAcquiringOperation',
                    'ReadAcquiringData',
                    'ReadBalances',
                    'ReadStatements',
                    'ReadCustomerData',
                    'ReadSBPData',
                    'EditSBPData',
                    'CreatePaymentForSign',
                    'CreatePaymentOrder',
                    'ManageWebhookData',
                    'ManageInvoiceData'
                )
                // Не передаем expirationDateTime - consent будет бессрочным
            )
        );
        
        $response = wp_remote_post($api_url . '/v1.0/consents', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version')
            ),
            'body' => json_encode($consent_data),
            'timeout' => 30,
            'sslverify' => false,
            'httpversion' => '1.1'
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($code >= 200 && $code < 300 && isset($data['Data'])) {
            // Сохраняем consent_id для дальнейшего использования
            if (isset($data['Data']['consentId'])) {
                update_option('tochka_consent_id', $data['Data']['consentId']);
            }
            
            return array(
                'success' => true,
                'consent_id' => $data['Data']['consentId'] ?? 'N/A',
                'status' => $data['Data']['status'] ?? 'Unknown',
                'permissions' => $data['Data']['permissions'] ?? array()
            );
        } else {
            return array(
                'success' => false,
                'error' => 'HTTP ' . $code . ': ' . $body
            );
        }
    }
    
    /**
     * Генерация URL для подтверждения прав пользователем
     */
    private function generate_authorization_url($consent_id) {
        $client_id = get_option('tochka_client_id', '');
        $redirect_uri = get_option('tochka_redirect_url', '');
        $state = wp_generate_password(12, false); // Генерируем случайную строку
        
        // Сохраняем state для проверки
        update_option('tochka_oauth_state', $state);
        
        $params = array(
            'client_id' => $client_id,
            'response_type' => 'code',
            'state' => $state,
            'redirect_uri' => $redirect_uri,
            'scope' => 'accounts balances customers statements sbp payments acquiring',
            'consent_id' => $consent_id
        );
        
        return 'https://enter.tochka.com/connect/authorize?' . http_build_query($params);
    }
    
    /**
     * Обмен кода авторизации на финальный токен
     */
    public function exchange_code_for_token() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        $code = sanitize_text_field($_POST['auth_code'] ?? '');
        $state = sanitize_text_field($_POST['state'] ?? '');
        
        if (empty($code)) {
            wp_send_json_error('Не указан код авторизации');
        }
        
        // Проверяем state
        $saved_state = get_option('tochka_oauth_state', '');
        if ($state !== $saved_state) {
            wp_send_json_error('Неверный state параметр');
        }
        
        $client_id = get_option('tochka_client_id', '');
        $client_secret = get_option('tochka_client_secret', '');
        $redirect_uri = get_option('tochka_redirect_url', '');
        
        $token_data = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code',
            'scope' => 'accounts balances customers statements sbp payments acquiring',
            'code' => $code,
            'redirect_uri' => $redirect_uri
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
            wp_send_json_error('Ошибка подключения: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['access_token'])) {
            // Сохраняем финальный токен
            update_option('tochka_final_access_token', $data['access_token']);
            if (isset($data['refresh_token'])) {
                update_option('tochka_refresh_token', $data['refresh_token']);
            }
            
            wp_send_json_success('🎉 Финальный токен получен! Плагин готов к работе!');
        } else {
            wp_send_json_error('Ошибка получения финального токена: ' . $body);
        }
    }
    
    /**
     * Проверка статуса существующего consent
     */
    public function check_consent_status() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        $consent_id = get_option('tochka_consent_id', '');
        if (empty($consent_id)) {
            wp_send_json_error('Consent ID не найден. Создайте новый consent.');
        }
        
        // Получаем токен для проверки
        $client_id = get_option('tochka_client_id', '');
        $client_secret = get_option('tochka_client_secret', '');
        
        $token_data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'scope' => 'accounts balances customers statements sbp payments acquiring'
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
            wp_send_json_error('Ошибка получения токена: ' . $response->get_error_message());
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($data['access_token'])) {
            wp_send_json_error('Не удалось получить токен для проверки');
        }
        
        // Проверяем статус consent
        $check_response = wp_remote_get('https://enter.tochka.com/uapi/v1.0/consents/' . $consent_id, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $data['access_token'],
                'User-Agent' => 'WordPress/' . get_bloginfo('version')
            ),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($check_response)) {
            wp_send_json_error('Ошибка проверки consent: ' . $check_response->get_error_message());
        }
        
        $check_data = json_decode(wp_remote_retrieve_body($check_response), true);
        if (isset($check_data['Data']['status'])) {
            $status = $check_data['Data']['status'];
            if ($status === 'Authorised') {
                wp_send_json_success('✅ Consent подтвержден! Статус: ' . $status);
            } else {
                wp_send_json_success('⚠️ Consent ожидает подтверждения. Статус: ' . $status);
            }
        } else {
            wp_send_json_error('Не удалось получить статус consent');
        }
    }
    
    /**
     * AJAX обработчик для получения Customer Code
     */
    public function get_customer_code_ajax() {
        // Проверяем nonce
        if (!wp_verify_nonce($_POST['nonce'], 'tochka_bank_nonce')) {
            wp_send_json_error('Ошибка безопасности');
        }
        
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        // Используем функцию с проверкой актуальности токена
        $final_token = TochkaPayment::get_valid_token();
        if (is_wp_error($final_token)) {
            wp_send_json_error($final_token->get_error_message());
        }
        
        // Получаем Customer Code через API
        $customers_url = 'https://enter.tochka.com/uapi/open-banking/v1.0/customers';

        $response = wp_remote_get($customers_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $final_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Ошибка получения Customer Code: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
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
                // Сохраняем Customer Code
                update_option('tochka_customer_code', $customer_code, false);
                
                // Сохраняем данные поставщика (taxCode и shortName)
                $tax_code = $customer['taxCode'] ?? '';
                $short_name = $customer['shortName'] ?? '';
                
                if (!empty($tax_code)) {
                    update_option('tochka_supplier_tax_code', $tax_code, false);
                }
                if (!empty($short_name)) {
                    update_option('tochka_supplier_name', $short_name, false);
                }
                
                wp_send_json_success('✅ Customer Code получен и сохранен: ' . $customer_code . 
                    ($tax_code ? ' (Tax Code: ' . $tax_code . ')' : '') . 
                    ($short_name ? ' (Name: ' . $short_name . ')' : ''));
            } else {
                wp_send_json_error('Customer Code не найден в ответе API');
            }
        } else {
            wp_send_json_error('Ошибка получения Customer Code - код: ' . $code . ', ответ: ' . $body);
        }
    }
    
    /**
     * AJAX обработчик для создания таблиц
     */
    public function create_tables_ajax() {
        // Проверяем nonce
        if (!wp_verify_nonce($_POST['nonce'], 'tochka_bank_nonce')) {
            wp_send_json_error('Ошибка безопасности');
        }
        
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        // Создаем таблицы
        $this->create_tables();
        
        wp_send_json_success('✅ Таблицы созданы/обновлены успешно!');
    }
    
    /**
     * Создание таблиц
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Универсальная таблица платежей Точка Банка
        $table_name = $wpdb->prefix . 'tochka_payments';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            provider varchar(50) NOT NULL DEFAULT 'tochka',
            entity_type varchar(50) NOT NULL DEFAULT 'visit',
            entity_id varchar(191) NOT NULL,
            entity_public_id varchar(191) DEFAULT NULL,
            order_id varchar(255) NOT NULL,
            payment_id varchar(255) NOT NULL,
            external_event_id varchar(191) DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            amount decimal(10,2) NOT NULL,
            currency varchar(3) NOT NULL DEFAULT 'RUB',
            payment_url text,
            callback_data longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_id (order_id),
            UNIQUE KEY payment_id (payment_id),
            UNIQUE KEY external_event_id (external_event_id),
            KEY entity_lookup (entity_type, entity_id),
            KEY provider (provider),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        error_log('Tochka Bank: Таблицы созданы/обновлены через AJAX');
    }
    
    /**
     * AJAX: Получение списка вебхуков
     */
    public function get_webhooks_ajax() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        // Используем функцию с проверкой актуальности токена
        $final_token = TochkaPayment::get_valid_token();
        if (is_wp_error($final_token)) {
            wp_send_json_error($final_token->get_error_message());
        }
        
        $client_id = get_option('tochka_client_id', '');
        if (empty($client_id)) {
            wp_send_json_error('Client ID не найден. Завершите настройку сначала.');
        }
        
        $response = wp_remote_get('https://enter.tochka.com/uapi/webhook/v1.0/' . $client_id, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $final_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Ошибка получения вебхуков: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($code === 200 && isset($data['Data'])) {
            wp_send_json_success($data['Data']);
        } else {
            wp_send_json_error('Ошибка получения вебхуков - код: ' . $code . ', ответ: ' . $body);
        }
    }
    
    /**
     * AJAX: Создание вебхука
     */
    public function create_webhook_ajax() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $url = sanitize_url($_POST['url'] ?? '');
        $webhook_type = sanitize_text_field($_POST['webhook_type'] ?? 'acquiringInternetPayment');
        
        if (empty($url)) {
            wp_send_json_error('URL вебхука не указан');
        }
        
        // Используем функцию с проверкой актуальности токена
        $final_token = TochkaPayment::get_valid_token();
        if (is_wp_error($final_token)) {
            wp_send_json_error($final_token->get_error_message());
        }
        
        $client_id = get_option('tochka_client_id', '');
        if (empty($client_id)) {
            wp_send_json_error('Client ID не найден. Завершите настройку сначала.');
        }
        
        $webhook_data = array(
            'url' => $url,
            'webhooksList' => !is_array($webhook_type) ? array($webhook_type) : $webhook_type
        );

        $response = wp_remote_request('https://enter.tochka.com/uapi/webhook/v1.0/' . $client_id, array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Bearer ' . $final_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($webhook_data),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Ошибка создания вебхука: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($code >= 200 && $code < 300 && isset($data['Data'])) {
            wp_send_json_success('Вебхук создан успешно! ID: ' . ($data['Data']['webhookId'] ?? 'N/A'));
        } else {
            wp_send_json_error('Ошибка создания вебхука - код: ' . $code . ', ответ: ' . $body);
        }
    }
    
    /**
     * AJAX: Редактирование вебхука
     */
    public function edit_webhook_ajax() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $webhook_id = sanitize_text_field($_POST['webhook_id'] ?? '');
        $url = sanitize_url($_POST['url'] ?? '');
        $webhook_type = sanitize_text_field($_POST['webhook_type'] ?? 'acquiringInternetPayment');
        
        if (empty($webhook_id) || empty($url)) {
            wp_send_json_error('ID вебхука и URL обязательны');
        }
        
        // Используем функцию с проверкой актуальности токена
        $final_token = TochkaPayment::get_valid_token();
        if (is_wp_error($final_token)) {
            wp_send_json_error($final_token->get_error_message());
        }
        
        $client_id = get_option('tochka_client_id', '');
        if (empty($client_id)) {
            wp_send_json_error('Client ID не найден. Завершите настройку сначала.');
        }
        
        $webhook_data = array(
            'Data' => array(
                'url' => $url,
                'webhookType' => $webhook_type
            )
        );
        
        $response = wp_remote_request('https://enter.tochka.com/uapi/webhook/v1.0/' . $client_id . '/' . $webhook_id, array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Bearer ' . $final_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($webhook_data),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Ошибка редактирования вебхука: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code >= 200 && $code < 300) {
            wp_send_json_success('Вебхук обновлен успешно!');
        } else {
            wp_send_json_error('Ошибка редактирования вебхука - код: ' . $code . ', ответ: ' . $body);
        }
    }
    
    /**
     * AJAX: Удаление вебхука
     */
    public function delete_webhook_ajax() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $webhook_id = sanitize_text_field($_POST['webhook_id'] ?? '');
        
        if (empty($webhook_id)) {
            wp_send_json_error('ID вебхука не указан');
        }
        
        // Используем функцию с проверкой актуальности токена
        $final_token = TochkaPayment::get_valid_token();
        if (is_wp_error($final_token)) {
            wp_send_json_error($final_token->get_error_message());
        }
        
        $client_id = get_option('tochka_client_id', '');
        if (empty($client_id)) {
            wp_send_json_error('Client ID не найден. Завершите настройку сначала.');
        }
        
        $response = wp_remote_request('https://enter.tochka.com/uapi/webhook/v1.0/' . $client_id . '/' . $webhook_id, array(
            'method' => 'DELETE',
            'headers' => array(
                'Authorization' => 'Bearer ' . $final_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Ошибка удаления вебхука: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code >= 200 && $code < 300) {
            wp_send_json_success('Вебхук удален успешно!');
        } else {
            wp_send_json_error('Ошибка удаления вебхука - код: ' . $code . ', ответ: ' . $body);
        }
    }
    
    /**
     * AJAX: Отправка тестового вебхука
     */
    public function send_test_webhook_ajax() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $webhook_id = sanitize_text_field($_POST['webhook_id'] ?? '');
        
        if (empty($webhook_id)) {
            wp_send_json_error('ID вебхука не указан');
        }
        
        // Используем функцию с проверкой актуальности токена
        $final_token = TochkaPayment::get_valid_token();
        if (is_wp_error($final_token)) {
            wp_send_json_error($final_token->get_error_message());
        }
        
        $client_id = get_option('tochka_client_id', '');
        if (empty($client_id)) {
            wp_send_json_error('Client ID не найден. Завершите настройку сначала.');
        }
        
        $webhook_data = array(
            'webhookType' => $webhook_id
        );
        
        $response = wp_remote_post('https://enter.tochka.com/uapi/webhook/v1.0/' . $client_id . '/test_send', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $final_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($webhook_data),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Ошибка отправки тестового вебхука: ' . $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code >= 200 && $code < 300) {
            wp_send_json_success('Тестовый вебхук отправлен успешно!');
        } else {
            wp_send_json_error('Ошибка отправки тестового вебхука - код: ' . $code . ', ответ: ' . $body);
        }
    }
    
    /**
     * AJAX: Получение логов вебхуков
     */
    public function get_webhook_logs_ajax() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'tochka_webhooks';
        
        // Получаем последние 50 записей
        $logs = $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 50",
            ARRAY_A
        );
        
        if ($wpdb->last_error) {
            wp_send_json_error('Ошибка получения логов: ' . $wpdb->last_error);
        }
        
        wp_send_json_success($logs);
    }
    
    /**
     * AJAX: Получение детальных данных вебхука
     */
    public function get_webhook_details_ajax() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        $log_id = intval($_POST['log_id'] ?? 0);
        if (!$log_id) {
            wp_send_json_error('ID лога не указан');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'tochka_webhooks';
        
        $webhook = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $log_id
        ), ARRAY_A);
        
        if (!$webhook) {
            wp_send_json_error('Вебхук не найден');
        }
        
        // Декодируем JSON данные
        $raw_data = json_decode($webhook['raw_data'], true);
        $analysis_data = json_decode($webhook['analysis_data'], true);
        
        $webhook['raw_data_parsed'] = $raw_data;
        $webhook['analysis_data_parsed'] = $analysis_data;
        
        wp_send_json_success($webhook);
    }
    
    /**
     * AJAX: Обновление токена через refresh_token
     */
    public function refresh_token_ajax() {
        check_ajax_referer('tochka_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        // Получаем refresh_token из настроек
        $refresh_token = get_option('tochka_refresh_token', '');
        if (empty($refresh_token)) {
            wp_send_json_error('Refresh token не найден. Завершите OAuth 2.0 flow для получения refresh token.');
        }
        
        // Используем статический метод из TochkaPayment для обновления токена
        $new_token = TochkaPayment::refresh_token_static($refresh_token);
        
        if ($new_token) {
            $success_message = '🎉 Токен успешно обновлен!';
            $success_message .= "\n\n✅ Новый токен: " . substr($new_token, 0, 20) . '...';
            $success_message .= "\n🔄 Refresh token также обновлен";
            $success_message .= "\n⏰ Токен действителен 24 часа";
            
            wp_send_json_success($success_message);
        } else {
            wp_send_json_error('Не удалось обновить токен. Проверьте refresh_token и настройки подключения.');
        }
    }
}
