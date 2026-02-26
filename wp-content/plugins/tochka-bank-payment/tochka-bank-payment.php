<?php
/**
 * Plugin Name: Оплата ТочкаБанка
 * Plugin URI: https://conf.ru
 * Description: Плагин для приема платежей через API Точка Банка
 * Version: 1.0.0
 * Author: Conf.ru
 * License: GPL v2 or later
 * Text Domain: tochka-bank-payment
 */

// Запрет прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

// Константы плагина
define('TOCHKA_BANK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TOCHKA_BANK_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TOCHKA_BANK_PLUGIN_VERSION', '1.0.0');

// Основной класс плагина
class TochkaBankPayment {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Загрузка зависимостей
     */
    private function load_dependencies() {
        // Подключаем Composer autoload если есть
        if (file_exists(TOCHKA_BANK_PLUGIN_PATH . 'vendor/autoload.php')) {
            require_once TOCHKA_BANK_PLUGIN_PATH . 'vendor/autoload.php';
        }
        
        // Подключаем классы плагина
        require_once TOCHKA_BANK_PLUGIN_PATH . 'includes/class-tochka-payment.php';
        require_once TOCHKA_BANK_PLUGIN_PATH . 'includes/class-tochka-admin.php';
        require_once TOCHKA_BANK_PLUGIN_PATH . 'includes/class-tochka-redirect-handler.php';
        require_once TOCHKA_BANK_PLUGIN_PATH . 'includes/class-tochka-payment-gateway.php';
        require_once TOCHKA_BANK_PLUGIN_PATH . 'includes/class-tochka-shortcodes.php';
        require_once TOCHKA_BANK_PLUGIN_PATH . 'includes/class-tochka-rest-api.php';
    }
    
    /**
     * Инициализация плагина
     */
    public function init() {
        // Инициализируем классы
        new TochkaPayment();
        new TochkaAdmin();
        // Инициализируем redirect handler статически
        TochkaRedirectHandler::init();
        new TochkaPaymentGateway();
        new TochkaShortcodes();
        new TochkaRestAPI();
    }
    
    /**
     * Активация плагина
     */
    public function activate() {
        // Создаем таблицы если нужно
        $this->create_tables();
        
        // Устанавливаем опции по умолчанию
        $this->set_default_options();
        
        // Flush rewrite rules для новых URL
        flush_rewrite_rules();
    }
    
    /**
     * Деактивация плагина
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Создание таблиц
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Универсальная таблица платежей Точка Банка
        $table_name = $wpdb->prefix . 'tochka_payments';
        
        $sql = "CREATE TABLE $table_name (
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
    }
    
    /**
     * Установка опций по умолчанию
     */
    private function set_default_options() {
        $default_options = array(
            'tochka_client_id' => '',
            'tochka_client_secret' => '',
            'tochka_redirect_url' => home_url('/?tochka_oauth=redirect'),
            'tochka_sandbox_mode' => '1',
            'tochka_webhook_url' => home_url('/tochka-payment/webhook/')
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Добавление меню в админку
     */
    public function add_admin_menu() {
        add_options_page(
            'Настройки Точка Банка',
            'Точка Банк',
            'manage_options',
            'tochka-bank-settings',
            array($this, 'admin_page')
        );
        
        // Добавляем подменю для вебхуков
        add_submenu_page(
            'options-general.php',
            'Вебхуки Точка Банка',
            'Вебхуки',
            'manage_options',
            'tochka-bank-webhooks',
            array($this, 'webhooks_page')
        );
    }
    
    /**
     * Регистрация настроек
     */
    public function register_settings() {
        register_setting('tochka_bank_settings', 'tochka_client_id');
        register_setting('tochka_bank_settings', 'tochka_client_secret');
        register_setting('tochka_bank_settings', 'tochka_redirect_url');
        register_setting('tochka_bank_settings', 'tochka_sandbox_mode');
        register_setting('tochka_bank_settings', 'tochka_webhook_url');
        register_setting('tochka_bank_settings', 'tochka_merchant_id');
        register_setting('tochka_bank_settings', 'tochka_supplier_phone');
    }
    
    /**
     * Страница настроек
     */
    public function admin_page() {
        // Показываем уведомление о завершении настройки
        $final_token = get_option('tochka_final_access_token', '');
        if (!empty($final_token) && !get_transient('tochka_setup_completed_notice_shown')) {
            echo '<div class="notice notice-success is-dismissible" style="border-left-color: #00a32a;">';
            echo '<p><strong>🎉 Настройка плагина "Оплата ТочкаБанка" завершена успешно!</strong></p>';
            echo '<p><strong>✅ Финальный токен получен и сохранен!</strong></p>';
            echo '<p><strong>🚀 Плагин готов к работе с API Точка Банка!</strong></p>';
            echo '<p>Теперь вы можете принимать платежи через Точка Банк.</p>';
            echo '</div>';
            set_transient('tochka_setup_completed_notice_shown', true, 300); // Показываем 5 минут
        }
        
        if (isset($_POST['submit'])) {
            update_option('tochka_client_id', sanitize_text_field($_POST['tochka_client_id']));
            update_option('tochka_client_secret', sanitize_text_field($_POST['tochka_client_secret']));
            update_option('tochka_redirect_url', esc_url_raw($_POST['tochka_redirect_url']));
            update_option('tochka_sandbox_mode', sanitize_text_field($_POST['tochka_sandbox_mode']));
            update_option('tochka_webhook_url', esc_url_raw($_POST['tochka_webhook_url']));
            update_option('tochka_merchant_id', sanitize_text_field($_POST['tochka_merchant_id']));
            update_option('tochka_supplier_phone', sanitize_text_field($_POST['tochka_supplier_phone']));
            
            echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';
        }
        
        $client_id = get_option('tochka_client_id', '');
        $client_secret = get_option('tochka_client_secret', '');
        $redirect_url = get_option('tochka_redirect_url', home_url('/tochka-payment/redirect/'));
        $sandbox_mode = get_option('tochka_sandbox_mode', '1');
        $webhook_url = get_option('tochka_webhook_url', home_url('/tochka-payment/webhook/'));
        
        ?>
        <div class="wrap tochka-admin-page">
            <h1>Настройки Точка Банка</h1>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Client ID</th>
                        <td>
                            <input type="text" name="tochka_client_id" value="<?php echo esc_attr($client_id); ?>" class="regular-text" required />
                            <p class="description">Получите в личном кабинете Точка Банка при регистрации OAuth 2.0 приложения</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Client Secret</th>
                        <td>
                            <div class="password-field">
                                <input type="password" name="tochka_client_secret" value="<?php echo esc_attr($client_secret); ?>" class="regular-text" required />
                                <button type="button" class="toggle-password" title="Показать/скрыть пароль">
                                    <span>👁️‍🗨️</span>
                                </button>
                            </div>
                            <p class="description">Секретный ключ приложения (хранится в зашифрованном виде)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Redirect URL</th>
                        <td>
                            <input type="url" name="tochka_redirect_url" value="<?php echo esc_attr($redirect_url); ?>" class="regular-text" required />
                            <p class="description">URL для возврата после оплаты. Укажите этот URL в настройках приложения в Точка Банке</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Webhook URL</th>
                        <td>
                            <input type="url" name="tochka_webhook_url" value="<?php echo esc_attr($webhook_url); ?>" class="regular-text" required />
                            <p class="description">URL для получения уведомлений от банка о статусе платежей</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Merchant ID</th>
                        <td>
                            <input type="text" name="tochka_merchant_id" value="<?php echo esc_attr(get_option('tochka_merchant_id', '')); ?>" class="regular-text" />
                            <p class="description">ID мерчанта (необязательно, только для тех у кого несколько торговых точек)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Телефон поставщика</th>
                        <td>
                            <input type="text" name="tochka_supplier_phone" value="<?php echo esc_attr(get_option('tochka_supplier_phone', '')); ?>" class="regular-text" required />
                            <p class="description">Телефон поставщика для чеков (обязательно, формат: +7999999999)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Customer Code</th>
                        <td>
                            <?php 
                            $customer_code = get_option('tochka_customer_code', '');
                            if (!empty($customer_code)): 
                            ?>
                                <p><strong>Текущий Customer Code:</strong> <code><?php echo esc_html($customer_code); ?></code></p>
                            <?php else: ?>
                                <p><em>Customer Code не получен</em></p>
                            <?php endif; ?>
                            <button type="button" id="get-customer-code" class="button button-secondary">
                                Получить Customer Code
                            </button>
                            <p class="description">Получить Customer Code через API (требует действительный токен)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Режим тестирования</th>
                        <td>
                            <label>
                                <input type="checkbox" name="tochka_sandbox_mode" value="1" <?php checked($sandbox_mode, '1'); ?> />
                                Включить режим тестирования (рекомендуется для начала)
                            </label>
                            <p class="description">В тестовом режиме платежи не проводятся реально</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Сохранить настройки'); ?>
                
                <?php if (!empty($client_id) && !empty($client_secret)): ?>
                <p>
                    <button type="button" id="test-connection" class="test-connection-btn">
                        🧪 Тестировать подключение
                    </button>
                    <button type="button" id="create-consent" class="test-connection-btn" style="background: #00a32a; margin-left: 10px; display: none;">
                        🔐 Подтвердить разрешения
                    </button>
                    <button type="button" id="check-consent" class="test-connection-btn" style="background: #0073aa; margin-left: 10px;">
                        🔍 Проверить статус consent
                    </button>
                    <button type="button" id="create-tables" class="test-connection-btn" style="background: #d63638; margin-left: 10px;">
                        🗄️ Создать таблицы БД
                    </button>
                    <?php if (!empty(get_option('tochka_refresh_token', ''))): ?>
                    <button type="button" id="refresh-token" class="test-connection-btn" style="background: #ff6b35; margin-left: 10px;">
                        🔄 Обновить токен
                    </button>
                    <?php endif; ?>
                </p>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php if (!empty($final_token)): ?>
                    // Если финальный токен уже есть, скрываем кнопку создания consent
                    jQuery('#create-consent').hide();
                    <?php else: ?>
                    // Кнопка будет показана после успешного тестирования подключения
                    <?php endif; ?>
                    
                    // Обработка кнопки получения Customer Code
                    document.getElementById('get-customer-code').addEventListener('click', function() {
                        const button = this;
                        const originalText = button.textContent;
                        
                        button.textContent = '⏳ Получаем...';
                        button.disabled = true;
                        
                        fetch(tochka_ajax.ajax_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=get_customer_code&nonce=' + '<?php echo wp_create_nonce('tochka_bank_nonce'); ?>'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Обновляем страницу для показа нового Customer Code
                                location.reload();
                            } else {
                                alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Ошибка при получении Customer Code');
                        })
                        .finally(() => {
                            button.textContent = originalText;
                            button.disabled = false;
                        });
                    });
                    
                    // Обработка кнопки создания таблиц
                    document.getElementById('create-tables').addEventListener('click', function() {
                        const button = this;
                        const originalText = button.textContent;
                        
                        if (!confirm('Создать/обновить таблицы БД? Это безопасная операция.')) {
                            return;
                        }
                        
                        button.textContent = '⏳ Создаем...';
                        button.disabled = true;
                        
                        fetch(tochka_ajax.ajax_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=tochka_create_tables&nonce=' + '<?php echo wp_create_nonce('tochka_bank_nonce'); ?>'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('✅ ' + data.data);
                            } else {
                                alert('❌ Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('❌ Ошибка при создании таблиц');
                        })
                        .finally(() => {
                            button.textContent = originalText;
                            button.disabled = false;
                        });
                    });
                    
                    // Обработка кнопки обновления токена
                    document.getElementById('refresh-token').addEventListener('click', function() {
                        const button = this;
                        const originalText = button.textContent;
                        
                        if (!confirm('Обновить токен доступа через refresh_token? Это безопасная операция.')) {
                            return;
                        }
                        
                        button.textContent = '⏳ Обновляем...';
                        button.disabled = true;
                        
                        fetch(tochka_ajax.ajax_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=tochka_refresh_token&nonce=' + '<?php echo wp_create_nonce('tochka_admin_nonce'); ?>'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('✅ ' + data.data);
                                // Обновляем страницу для показа нового токена
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            } else {
                                alert('❌ Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('❌ Ошибка при обновлении токена');
                        })
                        .finally(() => {
                            button.textContent = originalText;
                            button.disabled = false;
                        });
                    });
                });
                </script>
                
                <div id="auth-url-form" style="display: none; margin-top: 20px; padding: 15px; background: #f0f8ff; border: 1px solid #0073aa; border-radius: 4px;">
                    <h3>🔗 Ссылка для подтверждения прав</h3>
                    <p>Скопируйте ссылку ниже и откройте её в браузере для подтверждения прав в Точка Банке:</p>
                    <p>
                        <input type="text" id="auth-url" readonly style="width: 100%; padding: 8px; font-family: monospace; font-size: 12px;" />
                        <button type="button" id="copy-auth-url" class="test-connection-btn" style="background: #0073aa; margin-top: 10px;">
                            📋 Скопировать ссылку
                        </button>
                    </p>
                </div>
                
                <div id="auth-code-form" style="display: none; margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                    <h3>🔐 Введите код авторизации</h3>
                    <p>После подтверждения прав в Точка Банке вы получите код авторизации. Введите его ниже:</p>
                    <p>
                        <input type="text" id="auth-code" placeholder="Код авторизации" style="width: 300px; padding: 8px;" />
                        <button type="button" id="exchange-code" class="test-connection-btn" style="background: #00a32a;">
                            ✅ Получить финальный токен
                        </button>
                    </p>
                </div>
                <?php endif; ?>
            </form>
            
            <div class="tochka-info-card">
                <h2>📋 Информация для настройки в Точка Банке</h2>
                <p><strong>Redirect URL:</strong> 
                    <code><?php echo esc_html($redirect_url); ?></code>
                    <button type="button" class="copy-url" data-url="<?php echo esc_attr($redirect_url); ?>">📋 Копировать</button>
                </p>
                <p><strong>Webhook URL:</strong> 
                    <code><?php echo esc_html($webhook_url); ?></code>
                    <button type="button" class="copy-url" data-url="<?php echo esc_attr($webhook_url); ?>">📋 Копировать</button>
                </p>
                <p class="description">Скопируйте эти URL и укажите их в настройках вашего OAuth 2.0 приложения в Точка Банке</p>
            </div>
            
            <div class="tochka-info-card">
                <h2>🚀 Как начать работу</h2>
                <ol>
                    <li>Зарегистрируйте OAuth 2.0 приложение в личном кабинете Точка Банка</li>
                    <li>Получите Client ID и Client Secret</li>
                    <li>Настройте права доступа (scope) для приложения</li>
                    <li>Укажите Redirect URL и Webhook URL из карточки выше</li>
                    <li>Сохраните настройки и протестируйте подключение</li>
                    <li>Подайте заявку на подключение эквайринга</li>
                </ol>
                <p><strong>📖 Подробная инструкция:</strong> <a href="<?php echo TOCHKA_BANK_PLUGIN_URL; ?>OAUTH_SETUP.md" target="_blank">Настройка OAuth 2.0 приложения</a></p>
            </div>
            
            <div class="tochka-info-card">
                <h2>🔄 Управление токенами</h2>
                <p><strong>🔄 Обновление токена:</strong></p>
                <ul>
                    <li><strong>🔄 Обновить токен</strong> - используйте кнопку "🔄 Обновить токен" для обновления access_token через refresh_token</li>
                    <li><strong>⏰ Автоматическое обновление</strong> - плагин автоматически обновляет токен при необходимости</li>
                    <li><strong>🧪 Тестирование</strong> - кнопка полезна для тестирования механизма обновления токенов</li>
                    <li><strong>🔒 Безопасность</strong> - refresh_token позволяет обновлять access_token без повторной авторизации</li>
                </ul>
                <p class="description">💡 Кнопка "🔄 Обновить токен" доступна только после завершения OAuth 2.0 flow и получения refresh_token</p>
            </div>
            
            <div class="tochka-info-card">
                <h2>🔐 Настройка прав доступа</h2>
                <p><strong>Этап 1:</strong> В настройках OAuth приложения укажите scope:</p>
                <ul>
                    <li><code>accounts</code> - доступ к счетам</li>
                    <li><code>balances</code> - просмотр балансов</li>
                    <li><code>customers</code> - работа с клиентами</li>
                    <li><code>statements</code> - получение выписок</li>
                    <li><code>sbp</code> - Система быстрых платежей</li>
                    <li><code>payments</code> - проведение платежей</li>
                </ul>
                
                <p><strong>Этап 2:</strong> После получения токена плагин автоматически создаст consent с правами:</p>
                <ul>
                    <li><code>ReadAccountsBasic</code> - чтение базовой информации о счетах</li>
                    <li><code>MakeAcquiringOperation</code> - проведение эквайринговых операций</li>
                    <li><code>CreatePaymentOrder</code> - создание платежных поручений</li>
                    <li><code>ReadSBPData</code> - работа с СБП</li>
                    <li>И другие необходимые права...</li>
                </ul>
                
                <p><strong>Этап 3:</strong> Полный OAuth 2.0 flow (ОДИН РАЗ НАВСЕГДА):</p>
                <ol>
                    <li>Получите токен для работы с разрешениями</li>
                    <li>Создайте consent с необходимыми правами (БЕССРОЧНО!)</li>
                    <li>Получите ссылку для авторизации пользователя</li>
                    <li>Пользователь подтверждает права в Точка Банке</li>
                    <li>Обменяйте код авторизации на финальный токен</li>
                    <li><strong>🎉 Готово! Больше подтверждать не нужно!</strong></li>
                </ol>
                
                <?php 
                $final_token = get_option('tochka_final_access_token', '');
                $refresh_token = get_option('tochka_refresh_token', '');
                $consent_id = get_option('tochka_consent_id', '');
                ?>
                
                <?php if (!empty($final_token)): ?>
                <div style="margin-top: 20px; padding: 15px; background: #d1edff; border: 1px solid #0073aa; border-radius: 4px;">
                    <h3>🎉 Настройка завершена успешно!</h3>
                    <p><strong>✅ Финальный токен получен и сохранен!</strong></p>
                    <p><strong>Финальный токен:</strong> <?php echo substr($final_token, 0, 20); ?>...</p>
                    <?php if (!empty($refresh_token)): ?>
                    <p><strong>Refresh токен:</strong> <?php echo substr($refresh_token, 0, 20); ?>...</p>
                    <?php endif; ?>
                    <?php if (!empty($consent_id)): ?>
                    <p><strong>Consent ID:</strong> <?php echo $consent_id; ?></p>
                    <?php endif; ?>
                    <p><strong>🚀 Плагин "Оплата ТочкаБанка" готов к работе!</strong></p>
                    <p><em>Теперь вы можете принимать платежи через Точка Банк.</em></p>
                </div>
                <?php else: ?>
                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
                    <h3>⚠️ OAuth 2.0 не завершен</h3>
                    <p><strong>Финальный токен не найден!</strong></p>
                    <p>Для работы с API необходимо завершить настройку OAuth 2.0:</p>
                    <ol>
                        <li>Нажмите "🧪 Тестировать подключение"</li>
                        <li>Нажмите "🔐 Подтвердить разрешения"</li>
                        <li>Скопируйте ссылку и подтвердите права в Точка Банке</li>
                        <li>После подтверждения плагин автоматически получит финальный токен</li>
                    </ol>
                    <p><em>Без финального токена плагин не может работать с API!</em></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Страница управления вебхуками
     */
    public function webhooks_page() {
        $final_token = get_option('tochka_final_access_token', '');
        $webhook_url = home_url('/wp-json/tochka/v1/webhook');
        ?>
        <div class="wrap tochka-webhooks-page">
            <h1>🔗 Управление вебхуками Точка Банка</h1>
            
            <?php if (empty($final_token)): ?>
            <div class="notice notice-error">
                <p><strong>⚠️ Ошибка:</strong> Финальный токен не найден. Завершите настройку OAuth 2.0 в <a href="<?php echo admin_url('options-general.php?page=tochka-bank-settings'); ?>">основных настройках</a>.</p>
            </div>
            <?php else: ?>
            
            <div class="tochka-info-card">
                <h2>📋 Информация о вебхуках</h2>
                <p><strong>URL для настройки в Точке:</strong></p>
                <p>
                    <code style="background: #f0f0f0; padding: 8px; display: block; font-family: monospace; word-break: break-all;"><?php echo esc_html($webhook_url); ?></code>
                    <button type="button" class="copy-url" data-url="<?php echo esc_attr($webhook_url); ?>" style="margin-top: 10px;">📋 Копировать URL</button>
                </p>
                <p class="description">Скопируйте этот URL и укажите его при создании вебхука в Точка Банке</p>
            </div>
            
            <div class="tochka-info-card" style="background: #e7f3ff; border-left: 4px solid #0073aa;">
                <h3>🧪 Тестирование вебхуков</h3>
                <p><strong>Как протестировать вебхук:</strong></p>
                <ol>
                    <li>Нажмите кнопку <strong>"📋 Получить список вебхуков"</strong> ниже</li>
                    <li>В таблице найдите нужный тип вебхука</li>
                    <li>Нажмите кнопку <strong>"🧪 Send"</strong> для отправки тестового уведомления</li>
                    <li>Точка Банк отправит тестовое событие на ваш webhook URL</li>
                    <li>Проверьте логи вебхуков ниже, чтобы убедиться, что уведомление получено</li>
                </ol>
                <p class="description">💡 Тестовый вебхук содержит примерные данные и не влияет на реальные операции</p>
            </div>
            
            <div class="webhook-actions">
                <h2>🛠️ Управление вебхуками</h2>
                
                <div class="webhook-buttons">
                    <button type="button" id="get-webhooks" class="button button-primary">
                        📋 Получить список вебхуков
                    </button>
                    <button type="button" id="create-webhook" class="button button-secondary">
                        ➕ Создать вебхук
                    </button>
                </div>
                
                <div id="webhook-list" style="margin-top: 20px;"></div>
                
                <div id="create-webhook-form" style="display: none; margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                    <h3>➕ Создание нового вебхука</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">URL вебхука</th>
                            <td>
                                <input type="url" id="webhook-url" value="<?php echo esc_attr($webhook_url); ?>" class="regular-text" required />
                                <p class="description">URL для получения уведомлений</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Тип вебхука</th>
                            <td>
                                <select id="webhook-type" class="regular-text">
                                    <option value="acquiringInternetPayment" selected>acquiringInternetPayment</option>
                                    <option value="incomingPayment">incomingPayment</option>
                                    <option value="outgoingPayment">outgoingPayment</option>
                                    <option value="incomingSbpPayment">incomingSbpPayment</option>
                                    <option value="incomingSbpB2BPayment">incomingSbpB2BPayment</option>
                                </select>
                                <p class="description">Тип события для отслеживания</p>
                            </td>
                        </tr>
                    </table>
                    <p>
                        <button type="button" id="submit-create-webhook" class="button button-primary">Создать вебхук</button>
                        <button type="button" id="cancel-create-webhook" class="button">Отмена</button>
                    </p>
                </div>
            </div>
            
            <div class="webhook-logs">
                <h2>📊 Логи вебхуков</h2>
                <p>Просмотр последних полученных вебхуков:</p>
                <button type="button" id="view-webhook-logs" class="button button-secondary">
                    📋 Показать логи
                </button>
                <div id="webhook-logs" style="margin-top: 20px;"></div>
            </div>
            
            <style>
            .webhook-type-badge {
                display: inline-block;
                background: #0073aa;
                color: white;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: bold;
                margin: 2px;
            }
            .webhook-info {
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
                margin: 10px 0;
            }
            details summary {
                cursor: pointer;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .tochka-info-card {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 20px;
                margin: 20px 0;
            }
            .button.button-small {
                padding: 4px 8px;
                font-size: 12px;
                height: auto;
                line-height: 1.5;
            }
            .webhook-test-btn {
                background: #0073aa !important;
                border-color: #0073aa !important;
                color: white !important;
                font-weight: 600;
                transition: all 0.2s ease;
            }
            .webhook-test-btn:hover {
                background: #005a87 !important;
                border-color: #005a87 !important;
                transform: translateY(-1px);
                box-shadow: 0 2px 5px rgba(0, 115, 170, 0.3);
            }
            .webhook-test-btn:active {
                transform: translateY(0);
            }
            .webhook-test-btn:disabled {
                background: #ccc !important;
                border-color: #ccc !important;
                cursor: not-allowed;
                transform: none;
            }
            </style>
            
            <script>
            // Определяем переменные для AJAX
            var tochka_ajax = {
                ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
                nonce: '<?php echo wp_create_nonce('tochka_admin_nonce'); ?>'
            };
            
            document.addEventListener('DOMContentLoaded', function() {
                // Копирование URL
                document.querySelectorAll('.copy-url').forEach(button => {
                    button.addEventListener('click', function() {
                        const url = this.getAttribute('data-url');
                        navigator.clipboard.writeText(url).then(() => {
                            this.textContent = '✅ Скопировано!';
                            setTimeout(() => {
                                this.textContent = '📋 Копировать URL';
                            }, 2000);
                        });
                    });
                });
                
                // Получение списка вебхуков
                document.getElementById('get-webhooks').addEventListener('click', function() {
                    const button = this;
                    const originalText = button.textContent;
                    
                    button.textContent = '⏳ Загружаем...';
                    button.disabled = true;
                    
                    fetch(tochka_ajax.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=tochka_get_webhooks&nonce=' + tochka_ajax.nonce
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayWebhooks(data.data);
                        } else {
                            alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ошибка при получении вебхуков');
                    })
                    .finally(() => {
                        button.textContent = originalText;
                        button.disabled = false;
                    });
                });
                
                // Показать форму создания вебхука
                document.getElementById('create-webhook').addEventListener('click', function() {
                    document.getElementById('create-webhook-form').style.display = 'block';
                });
                
                // Скрыть форму создания вебхука
                document.getElementById('cancel-create-webhook').addEventListener('click', function() {
                    document.getElementById('create-webhook-form').style.display = 'none';
                });
                
                // Создание вебхука
                document.getElementById('submit-create-webhook').addEventListener('click', function() {
                    const url = document.getElementById('webhook-url').value;
                    const type = document.getElementById('webhook-type').value;
                    
                    if (!url) {
                        alert('Укажите URL вебхука');
                        return;
                    }
                    
                    const button = this;
                    const originalText = button.textContent;
                    
                    button.textContent = '⏳ Создаем...';
                    button.disabled = true;
                    
                    fetch(tochka_ajax.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=tochka_create_webhook&url=' + encodeURIComponent(url) + '&webhook_type=' + encodeURIComponent(type) + '&nonce=' + tochka_ajax.nonce
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ ' + data.data);
                            document.getElementById('create-webhook-form').style.display = 'none';
                            // Обновляем список вебхуков
                            document.getElementById('get-webhooks').click();
                        } else {
                            alert('❌ Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ Ошибка при создании вебхука');
                    })
                    .finally(() => {
                        button.textContent = originalText;
                        button.disabled = false;
                    });
                });
                
                // Просмотр логов
                document.getElementById('view-webhook-logs').addEventListener('click', function() {
                    const button = this;
                    const originalText = button.textContent;
                    
                    button.textContent = '⏳ Загружаем...';
                    button.disabled = true;
                    
                    fetch(tochka_ajax.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=tochka_get_webhook_logs&nonce=' + tochka_ajax.nonce
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayWebhookLogs(data.data);
                        } else {
                            alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ошибка при получении логов');
                    })
                    .finally(() => {
                        button.textContent = originalText;
                        button.disabled = false;
                    });
                });
                
                function displayWebhooks(response) {
                    const container = document.getElementById('webhook-list');
                    
                    // Проверяем структуру ответа
                    if (!response || (!response.data && !response.webhooksList)) {
                        container.innerHTML = '<p>Вебхуки не найдены</p>';
                        return;
                    }
                    
                    // Извлекаем данные из ответа
                    const webhooksList = response.data?.webhooksList || response.webhooksList || [];
                    const webhookUrl = response.url || 'N/A';
                    
                    if (webhooksList.length === 0) {
                        container.innerHTML = '<p>Вебхуки не найдены</p>';
                        return;
                    }
                    
                    let html = '<div class="webhook-info">';
                    html += '<h3>📋 Настроенные вебхуки</h3>';
                    html += '<table class="wp-list-table widefat fixed striped">';
                    html += '<thead><tr><th>URL</th><th>Типы событий</th><th>Действия</th></tr></thead>';
                    html += '<tbody>';
                    
                    // Показываем каждый тип вебхука отдельной строкой
                    webhooksList.forEach((type, index) => {
                        html += '<tr>';
                        html += '<td><code>' + webhookUrl + '</code></td>';
                        html += '<td><span class="webhook-type-badge">' + type + '</span></td>';
                        html += '<td>';
                        html += '<button onclick="sendTestWebhook(\'' + type + '\')" class="button button-small webhook-test-btn" title="Отправить тестовый вебхук">🧪 Send Webhook</button> ';
                        html += '<button onclick="deleteWebhook(\'' + type + '\')" class="button button-small" style="color: #d63638;" title="Удалить вебхук">🗑️</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    html += '</div>';
                    
                    // Добавляем информацию о структуре ответа для отладки
                    html += '<details style="margin-top: 20px;">';
                    html += '<summary>🔍 Структура ответа API</summary>';
                    html += '<pre style="background: #f0f0f0; padding: 15px; border-radius: 4px; overflow-x: auto;">';
                    html += JSON.stringify(response, null, 2);
                    html += '</pre>';
                    html += '</details>';
                    
                    container.innerHTML = html;
                }
                
                function displayWebhookLogs(logs) {
                    const container = document.getElementById('webhook-logs');
                    
                    if (!logs || logs.length === 0) {
                        container.innerHTML = '<p>Логи не найдены</p>';
                        return;
                    }
                    
                    let html = '<table class="wp-list-table widefat fixed striped">';
                    html += '<thead><tr><th>Дата</th><th>Тип</th><th>Operation ID</th><th>Данные</th></tr></thead>';
                    html += '<tbody>';
                    
                    logs.forEach(log => {
                        html += '<tr>';
                        html += '<td>' + (log.created_at || 'N/A') + '</td>';
                        html += '<td>' + (log.webhook_type || 'N/A') + '</td>';
                        html += '<td>' + (log.operation_id || 'N/A') + '</td>';
                        html += '<td><button onclick="viewWebhookData(\'' + log.id + '\')" class="button button-small">👁️</button></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    container.innerHTML = html;
                }
                
                // Глобальные функции для действий с вебхуками
                window.editWebhook = function(webhookId) {
                    // TODO: Реализовать редактирование
                    alert('Редактирование вебхука ' + webhookId + ' (в разработке)');
                };
                
                window.deleteWebhook = function(webhookId) {
                    if (!confirm('Удалить вебхук ' + webhookId + '?')) return;
                    
                    fetch(tochka_ajax.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=tochka_delete_webhook&webhook_id=' + encodeURIComponent(webhookId) + '&nonce=' + tochka_ajax.nonce
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ ' + data.data);
                            document.getElementById('get-webhooks').click();
                        } else {
                            alert('❌ Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                        }
                    });
                };
                
                window.sendTestWebhook = function(webhookId) {
                    if (!confirm('Отправить тестовый вебхук для типа "' + webhookId + '"?\n\nТочка отправит тестовое уведомление на ваш webhook URL.')) return;
                    
                    // Находим кнопку и показываем статус загрузки
                    const buttons = document.querySelectorAll('button[onclick*="' + webhookId + '"]');
                    let button = null;
                    buttons.forEach(btn => {
                        if (btn.textContent.includes('Send')) {
                            button = btn;
                        }
                    });
                    
                    if (button) {
                        button.disabled = true;
                        button.innerHTML = '⏳ Отправка...';
                    }
                    
                    fetch(tochka_ajax.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=tochka_send_test_webhook&webhook_id=' + encodeURIComponent(webhookId) + '&nonce=' + tochka_ajax.nonce
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Тестовый вебхук отправлен успешно!\n\n' + data.data + '\n\nЛоги будут автоматически обновлены через несколько секунд...');
                            
                            // Автоматически обновляем логи через 3 секунды
                            setTimeout(() => {
                                const viewLogsBtn = document.getElementById('view-webhook-logs');
                                if (viewLogsBtn) {
                                    viewLogsBtn.click();
                                    
                                    // Прокручиваем к логам
                                    setTimeout(() => {
                                        const logsContainer = document.getElementById('webhook-logs');
                                        if (logsContainer) {
                                            logsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                        }
                                    }, 500);
                                }
                            }, 3000);
                        } else {
                            alert('❌ Ошибка отправки тестового вебхука:\n\n' + (data.data || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ Ошибка при отправке тестового вебхука:\n\n' + error.message);
                    })
                    .finally(() => {
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = '🧪 Send Webhook';
                        }
                    });
                };
                
                window.viewWebhookData = function(logId) {
                    fetch(tochka_ajax.ajax_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=tochka_get_webhook_details&log_id=' + encodeURIComponent(logId) + '&nonce=' + tochka_ajax.nonce
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showWebhookDetails(data.data);
                        } else {
                            alert('❌ Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('❌ Ошибка при получении данных вебхука');
                    });
                };
                
                function showWebhookDetails(webhook) {
                    let html = '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; overflow-y: auto;">';
                    html += '<div style="background: white; margin: 20px; padding: 20px; border-radius: 8px; max-width: 90%;">';
                    html += '<h2>📊 Детальные данные вебхука #' + webhook.id + '</h2>';
                    
                    // Основная информация
                    html += '<h3>📋 Основная информация</h3>';
                    html += '<table class="wp-list-table widefat fixed striped">';
                    html += '<tr><th>Поле</th><th>Значение</th></tr>';
                    html += '<tr><td>Тип вебхука</td><td>' + (webhook.webhook_type || 'N/A') + '</td></tr>';
                    html += '<tr><td>Operation ID</td><td>' + (webhook.operation_id || 'N/A') + '</td></tr>';
                    html += '<tr><td>Сумма</td><td>' + (webhook.amount || 'N/A') + ' ' + (webhook.currency || '') + '</td></tr>';
                    html += '<tr><td>Статус</td><td>' + (webhook.status || 'N/A') + '</td></tr>';
                    html += '<tr><td>ID заказа</td><td>' + (webhook.order_id || 'N/A') + '</td></tr>';
                    html += '<tr><td>Код клиента</td><td>' + (webhook.customer_code || 'N/A') + '</td></tr>';
                    html += '<tr><td>ID мерчанта</td><td>' + (webhook.merchant_id || 'N/A') + '</td></tr>';
                    html += '<tr><td>ID терминала</td><td>' + (webhook.terminal_id || 'N/A') + '</td></tr>';
                    html += '<tr><td>ID транзакции</td><td>' + (webhook.transaction_id || 'N/A') + '</td></tr>';
                    html += '<tr><td>Номер чека</td><td>' + (webhook.receipt_number || 'N/A') + '</td></tr>';
                    html += '<tr><td>Описание</td><td>' + (webhook.description || 'N/A') + '</td></tr>';
                    html += '<tr><td>Дата создания</td><td>' + (webhook.created_at || 'N/A') + '</td></tr>';
                    html += '</table>';
                    
                    // Анализ структуры
                    if (webhook.analysis_data_parsed) {
                        html += '<h3>🔍 Анализ структуры данных</h3>';
                        html += '<pre style="background: #f0f0f0; padding: 15px; border-radius: 4px; overflow-x: auto;">';
                        html += JSON.stringify(webhook.analysis_data_parsed, null, 2);
                        html += '</pre>';
                    }
                    
                    // Полные данные
                    if (webhook.raw_data_parsed) {
                        html += '<h3>📄 Полные данные вебхука</h3>';
                        html += '<pre style="background: #f0f0f0; padding: 15px; border-radius: 4px; overflow-x: auto; max-height: 400px; overflow-y: auto;">';
                        html += JSON.stringify(webhook.raw_data_parsed, null, 2);
                        html += '</pre>';
                    }
                    
                    html += '<div style="margin-top: 20px;">';
                    html += '<button onclick="closeWebhookDetails()" class="button button-primary">Закрыть</button>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    
                    document.body.insertAdjacentHTML('beforeend', html);
                }
                
                window.closeWebhookDetails = function() {
                    const modal = document.querySelector('div[style*="position: fixed"]');
                    if (modal) {
                        modal.remove();
                    }
                };
            });
            </script>
            
            <?php endif; ?>
        </div>
        <?php
    }
}

// Инициализация плагина
function tochka_bank_payment_init() {
    return TochkaBankPayment::get_instance();
}

// Запуск плагина
add_action('plugins_loaded', 'tochka_bank_payment_init');
