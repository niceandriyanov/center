<?php
/**
 * Универсальный тест плагина "Оплата ТочкаБанка"
 * 
 * ВНИМАНИЕ: Этот файл только для тестирования!
 * Не используйте в продакшене!
 */

// Подключаем WordPress - универсальный способ
$wp_config_paths = [
    '../../../wp-load.php',              // Предпочтительный способ через wp-load
    '../../../wp-config.php',           // Стандартный путь
    '../../../../wp-load.php',           // Альтернативный путь
    '../../../../wp-config.php',         // Если структура отличается
    dirname(__FILE__) . '/../../../wp-load.php',    // Абсолютный путь к wp-load
    dirname(__FILE__) . '/../../../wp-config.php',  // Абсолютный путь
];

$wp_loaded = false;
foreach ($wp_config_paths as $path) {
    if (file_exists($path)) {
        try {
            require_once($path);
            $wp_loaded = true;
            break;
        } catch (Exception $e) {
            // Продолжаем поиск
            continue;
        }
    }
}

// Если не удалось подключить стандартными способами, пробуем найти wp-config.php
if (!$wp_loaded) {
    $current_dir = dirname(__FILE__);
    $search_paths = [
        $current_dir . '/../../../',
        $current_dir . '/../../../../',
        $current_dir . '/../../../../../',
    ];
    
    foreach ($search_paths as $search_path) {
        $wp_config = $search_path . 'wp-config.php';
        $wp_load = $search_path . 'wp-load.php';
        
        if (file_exists($wp_config)) {
            try {
                require_once($wp_config);
                $wp_loaded = true;
                break;
            } catch (Exception $e) {
                continue;
            }
        } elseif (file_exists($wp_load)) {
            try {
                require_once($wp_load);
                $wp_loaded = true;
                break;
            } catch (Exception $e) {
                continue;
            }
        }
    }
}

// Если WordPress не загружен, показываем ошибку с диагностикой
if (!$wp_loaded) {
    $current_dir = dirname(__FILE__);
    $diagnostic_info = "
    <h3>Диагностика подключения WordPress:</h3>
    <p><strong>Текущая директория:</strong> " . htmlspecialchars($current_dir) . "</p>
    <p><strong>Проверенные пути:</strong></p>
    <ul>";
    
    foreach ($wp_config_paths as $path) {
        $exists = file_exists($path) ? '✅ Существует' : '❌ Не найден';
        $diagnostic_info .= "<li>" . htmlspecialchars($path) . " - " . $exists . "</li>";
    }
    
    $diagnostic_info .= "</ul>
    <p><strong>Дополнительные проверки:</strong></p>
    <ul>";
    
    $search_paths = [
        $current_dir . '/../../../',
        $current_dir . '/../../../../',
        $current_dir . '/../../../../../',
    ];
    
    foreach ($search_paths as $search_path) {
        $wp_config_exists = file_exists($search_path . 'wp-config.php') ? '✅' : '❌';
        $wp_load_exists = file_exists($search_path . 'wp-load.php') ? '✅' : '❌';
        $diagnostic_info .= "<li>" . htmlspecialchars($search_path) . "wp-config.php - " . $wp_config_exists . "</li>";
        $diagnostic_info .= "<li>" . htmlspecialchars($search_path) . "wp-load.php - " . $wp_load_exists . "</li>";
    }
    
    $diagnostic_info .= "</ul>
    <p><strong>Рекомендации:</strong></p>
    <ul>
        <li>Убедитесь, что файл test-payment.php находится в папке плагина</li>
        <li>Проверьте, что WordPress установлен в правильной директории</li>
        <li>Убедитесь, что файлы wp-config.php или wp-load.php существуют</li>
    </ul>";
    
    die('Ошибка: Не удалось подключить WordPress. ' . $diagnostic_info);
}

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

// Получаем настройки
$client_id = get_option('tochka_client_id', '');
$client_secret = get_option('tochka_client_secret', '');
$final_token = get_option('tochka_final_access_token', '');
$customer_code = get_option('tochka_customer_code', '');
$merchant_id = get_option('tochka_merchant_id', '');
$sandbox_mode = get_option('tochka_sandbox_mode', '1');

// Определяем режим работы
$is_sandbox = ($sandbox_mode === '1');
$mode_text = $is_sandbox ? 'Тестовый режим (Sandbox)' : 'Production режим';
$mode_color = $is_sandbox ? '#ff9800' : '#4caf50';

// Обработка POST запроса для тестирования
if ($_POST['action'] ?? '' === 'test_payment') {
    $order_id = sanitize_text_field($_POST['order_id'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $description = sanitize_text_field($_POST['description'] ?? '');
    
    if (empty($order_id) || $amount <= 0) {
        $error_message = 'Ошибка: заполните все поля';
    } else {
        $result = $tochka_payment->create_payment($order_id, $amount, $description);
        
        if (is_wp_error($result)) {
            $error_message = 'Ошибка создания платежа: ' . $result->get_error_message();
        } else {
            $success_message = 'Платеж создан успешно!';
            $payment_data = $result;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Тест плагина "Оплата ТочкаБанка"</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .mode-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .status-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #0073aa;
        }
        .status-card.success {
            border-left-color: #4caf50;
        }
        .status-card.error {
            border-left-color: #f44336;
        }
        .status-card.warning {
            border-left-color: #ff9800;
        }
        .form-container {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #005a87;
        }
        .btn-success {
            background: #4caf50;
        }
        .btn-warning {
            background: #ff9800;
        }
        .btn-danger {
            background: #f44336;
        }
        .result-box {
            margin: 20px 0;
            padding: 15px;
            border-radius: 4px;
        }
        .result-box.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result-box.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .code-block {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .info-table th, .info-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .info-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .links {
            margin: 20px 0;
        }
        .links a {
            margin-right: 15px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Тест плагина "Оплата ТочкаБанка"</h1>
        
        <div class="mode-badge" style="background: <?php echo $mode_color; ?>">
            <?php echo $mode_text; ?>
        </div>
        
        <div class="status-grid">
            <div class="status-card <?php echo !empty($client_id) ? 'success' : 'error'; ?>">
                <h3>🔑 Client ID</h3>
                <p><?php echo !empty($client_id) ? '✅ Настроен' : '❌ Не настроен'; ?></p>
                <?php if (!empty($client_id)): ?>
                <small><?php echo substr($client_id, 0, 10) . '...'; ?></small>
                <?php endif; ?>
            </div>
            
            <div class="status-card <?php echo !empty($client_secret) ? 'success' : 'error'; ?>">
                <h3>🔐 Client Secret</h3>
                <p><?php echo !empty($client_secret) ? '✅ Настроен' : '❌ Не настроен'; ?></p>
            </div>
            
            <div class="status-card <?php echo ($is_sandbox || !empty($final_token)) ? 'success' : 'error'; ?>">
                <h3>🎫 Токен доступа</h3>
                <?php if ($is_sandbox): ?>
                <p>✅ Используется токен песочницы</p>
                <small>sandbox.jwt.token</small>
                <?php else: ?>
                <p><?php echo !empty($final_token) ? '✅ Получен' : '❌ Не получен'; ?></p>
                <?php if (!empty($final_token)): ?>
                <small><?php echo substr($final_token, 0, 20) . '...'; ?></small>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="status-card <?php echo !empty($customer_code) ? 'success' : 'warning'; ?>">
                <h3>👤 Customer Code</h3>
                <p><?php echo !empty($customer_code) ? '✅ Получен' : ($is_sandbox ? '⚠️ Будет использован тестовый' : '❌ Не получен'); ?></p>
                <?php if (!empty($customer_code)): ?>
                <small><?php echo $customer_code; ?></small>
                <?php elseif ($is_sandbox): ?>
                <small>1234567ab (тестовый)</small>
                <?php endif; ?>
            </div>
            
            <div class="status-card <?php echo !empty($merchant_id) ? 'success' : 'warning'; ?>">
                <h3>🏪 Merchant ID</h3>
                <p><?php echo !empty($merchant_id) ? '✅ Указан' : ($is_sandbox ? '⚠️ Будет использован тестовый' : '⚠️ Не указан (необязательно)'); ?></p>
                <?php if (!empty($merchant_id)): ?>
                <small><?php echo $merchant_id; ?></small>
                <?php elseif ($is_sandbox): ?>
                <small>200000000001056 (тестовый)</small>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!$is_sandbox && empty($final_token)): ?>
        <div class="result-box error">
            <h3>⚠️ OAuth 2.0 не завершен</h3>
            <p><strong>Финальный токен не найден!</strong></p>
            <p>Для работы с API необходимо завершить настройку OAuth 2.0 в админ-панели.</p>
            <a href="<?php echo admin_url('options-general.php?page=tochka-bank-settings'); ?>" class="btn">
                Перейти в настройки плагина
            </a>
        </div>
        <?php else: ?>
        
        <div class="form-container">
            <h2>💳 Тестирование создания платежа</h2>
            
            <?php if (isset($error_message)): ?>
            <div class="result-box error">
                <strong>❌ Ошибка:</strong> <?php echo esc_html($error_message); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
            <div class="result-box success">
                <strong>✅ Успех:</strong> <?php echo $success_message; ?>
                
                <?php if (isset($payment_data) && isset($payment_data['Data']['paymentUrl'])): ?>
                <p><strong>🔗 Ссылка для оплаты:</strong></p>
                <p><a href="<?php echo esc_url($payment_data['Data']['paymentUrl']); ?>" target="_blank" class="btn btn-success">
                    Перейти к оплате
                </a></p>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div class="code-block">
                        <strong>📤 Отправленные данные:</strong><br>
                        <?php 
                        // Получаем webhook URL
                        $webhook_url = get_option('tochka_webhook_url', home_url('/tochka-payment/webhook/'));
                        
                        // Формируем URL с параметрами как в реальном коде
                        $test_order_id = $_POST['order_id'] ?? '12345';
                        $success_url = add_query_arg(array(
                            'order_id' => $test_order_id,
                            'status' => 'success'
                        ), $webhook_url);
                        
                        $fail_url = add_query_arg(array(
                            'order_id' => $test_order_id,
                            'status' => 'fail'
                        ), $webhook_url);
                        
                        // Получаем данные, которые были отправлены
                        $sent_data = array(
                            'Data' => array(
                                'customerCode' => $is_sandbox ? '1234567ab' : (!empty($customer_code) ? $customer_code : 'Не получен'),
                                'amount' => number_format($_POST['amount'] ?? 0, 2, '.', ''),
                                'purpose' => $_POST['description'] ?? '',
                                'redirectUrl' => $success_url,
                                'failRedirectUrl' => $fail_url,
                                'paymentMode' => array('card', 'sbp'),
                                'saveCard' => false,
                                'preAuthorization' => false,
                                'ttl' => 10080
                            )
                        );
                        
                        // Добавляем merchantId если есть
                        if (!empty($merchant_id) || $is_sandbox) {
                            $sent_data['Data']['merchantId'] = $is_sandbox ? '200000000001056' : $merchant_id;
                        }
                        
                        // Добавляем consumerId если есть
                        if ($is_sandbox) {
                            $sent_data['Data']['consumerId'] = 'fedac807-078d-45ac-a43b-5c01c57edbf8';
                        } else {
                            // В production - используем ID пользователя WordPress
                            $current_user = wp_get_current_user();
                            if ($current_user && $current_user->ID) {
                                $sent_data['Data']['consumerId'] = 'wp_user_' . $current_user->ID;
                            } else {
                                $sent_data['Data']['consumerId'] = 'wp_anonymous_' . uniqid();
                            }
                        }
                        
                        // Добавляем paymentLinkId если есть - используем номер заказа
                        if ($is_sandbox) {
                            $sent_data['Data']['paymentLinkId'] = 'test_payment_link_' . time();
                        } else {
                            // В production - используем номер заказа
                            $test_order_id = $_POST['order_id'] ?? '12345';
                            $sent_data['Data']['paymentLinkId'] = 'order_' . $test_order_id;
                        }
                        
                        echo esc_html(json_encode($sent_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
                        ?>
                    </div>
                    
                    <div class="code-block">
                        <strong>📥 Данные ответа:</strong><br>
                        <?php echo esc_html(json_encode($payment_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="test_payment">
                
                <div class="form-group">
                    <label for="order_id">ID заказа:</label>
                    <input type="text" name="order_id" id="order_id" value="test_<?php echo time(); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="amount">Сумма (руб.):</label>
                    <input type="number" name="amount" id="amount" value="100.00" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание:</label>
                    <input type="text" name="description" id="description" value="Тестовый платеж через API" required>
                </div>
                
                <button type="submit" class="btn">
                    💳 Создать тестовый платеж
                </button>
            </form>
        </div>
        
        <div class="form-container">
            <h2>📊 Информация о настройках</h2>
            
            <table class="info-table">
                <tr>
                    <th>Параметр</th>
                    <th>Значение</th>
                    <th>Статус</th>
                </tr>
                <tr>
                    <td>Режим работы</td>
                    <td><?php echo $mode_text; ?></td>
                    <td><?php echo $is_sandbox ? '🟠 Тестовый' : '🟢 Production'; ?></td>
                </tr>
                <tr>
                    <td>API URL</td>
                    <td><?php echo $is_sandbox ? 'https://enter.tochka.com/sandbox/v2/acquiring/v1.0' : 'https://enter.tochka.com/uapi/acquiring/v1.0'; ?></td>
                    <td><?php echo $is_sandbox ? '🟠 Sandbox' : '🟢 Production'; ?></td>
                </tr>
                <tr>
                    <td>Токен доступа</td>
                    <td><?php echo $is_sandbox ? 'sandbox.jwt.token' : (!empty($final_token) ? substr($final_token, 0, 20) . '...' : 'Не получен'); ?></td>
                    <td><?php echo $is_sandbox ? '🟠 Песочница' : (!empty($final_token) ? '🟢 Реальный' : '🔴 Не найден'); ?></td>
                </tr>
                <tr>
                    <td>Customer Code</td>
                    <td><?php echo !empty($customer_code) ? $customer_code : ($is_sandbox ? '1234567ab (тестовый)' : 'Не получен'); ?></td>
                    <td><?php echo !empty($customer_code) ? '🟢 Реальный' : ($is_sandbox ? '🟠 Тестовый' : '🔴 Не найден'); ?></td>
                </tr>
                <tr>
                    <td>Merchant ID</td>
                    <td><?php echo !empty($merchant_id) ? $merchant_id : ($is_sandbox ? '200000000001056 (тестовый)' : 'Не указан'); ?></td>
                    <td><?php echo !empty($merchant_id) ? '🟢 Указан' : ($is_sandbox ? '🟠 Тестовый' : '🟡 Не указан'); ?></td>
                </tr>
            </table>
        </div>
        
        <?php endif; ?>
        
        <div class="links">
            <a href="<?php echo admin_url('options-general.php?page=tochka-bank-settings'); ?>" class="btn">
                ⚙️ Настройки плагина
            </a>
            <a href="<?php echo home_url(); ?>" class="btn">
                🏠 На главную
            </a>
        </div>
    </div>
</body>
</html>
