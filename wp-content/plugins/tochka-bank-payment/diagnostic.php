<?php
/**
 * Диагностический файл для проверки структуры WordPress
 * 
 * ВНИМАНИЕ: Этот файл только для диагностики!
 * Удалите его после решения проблемы!
 */

echo "<h1>🔍 Диагностика структуры WordPress</h1>";

$current_dir = dirname(__FILE__);
echo "<h2>Текущая директория:</h2>";
echo "<p><code>" . htmlspecialchars($current_dir) . "</code></p>";

echo "<h2>Проверка путей к WordPress:</h2>";

$paths_to_check = [
    '../../../wp-config.php',
    '../../../wp-load.php',
    '../../../../wp-config.php',
    '../../../../wp-load.php',
    dirname(__FILE__) . '/../../../wp-config.php',
    dirname(__FILE__) . '/../../../wp-load.php',
];

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Путь</th><th>Существует</th><th>Размер</th><th>Права доступа</th></tr>";

foreach ($paths_to_check as $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 'N/A';
    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
    
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($path) . "</code></td>";
    echo "<td>" . ($exists ? '✅ Да' : '❌ Нет') . "</td>";
    echo "<td>" . ($exists ? number_format($size) . ' байт' : 'N/A') . "</td>";
    echo "<td>" . $perms . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Попытка подключения WordPress:</h2>";

$wp_loaded = false;
$loaded_from = '';

foreach ($paths_to_check as $path) {
    if (file_exists($path)) {
        try {
            ob_start();
            require_once($path);
            $output = ob_get_clean();
            
            // Проверяем, загружен ли WordPress
            if (function_exists('wp_get_current_user') || defined('ABSPATH')) {
                $wp_loaded = true;
                $loaded_from = $path;
                break;
            }
        } catch (Exception $e) {
            echo "<p>❌ Ошибка при загрузке <code>" . htmlspecialchars($path) . "</code>: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

if ($wp_loaded) {
    echo "<p>✅ WordPress успешно загружен из: <code>" . htmlspecialchars($loaded_from) . "</code></p>";
    
    if (function_exists('wp_get_current_user')) {
        echo "<p>✅ Функции WordPress доступны</p>";
        
        // Проверяем настройки
        if (function_exists('get_option')) {
            echo "<h3>Настройки плагина:</h3>";
            $settings = [
                'tochka_client_id' => get_option('tochka_client_id', 'Не настроен'),
                'tochka_sandbox_mode' => get_option('tochka_sandbox_mode', 'Не настроен'),
            ];
            
            echo "<ul>";
            foreach ($settings as $key => $value) {
                echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</li>";
            }
            echo "</ul>";
        }
    }
} else {
    echo "<p>❌ WordPress не удалось загрузить</p>";
}

echo "<h2>Информация о сервере:</h2>";
echo "<ul>";
echo "<li><strong>PHP версия:</strong> " . phpversion() . "</li>";
echo "<li><strong>Операционная система:</strong> " . php_uname() . "</li>";
echo "<li><strong>Текущий пользователь:</strong> " . get_current_user() . "</li>";
echo "<li><strong>Рабочая директория:</strong> " . getcwd() . "</li>";
echo "</ul>";

echo "<h2>Рекомендации:</h2>";
echo "<ol>";
echo "<li>Убедитесь, что файл находится в папке плагина: <code>wp-content/plugins/tochka-bank-payment/</code></li>";
echo "<li>Проверьте, что WordPress установлен в правильной директории</li>";
echo "<li>Убедитесь, что файлы wp-config.php или wp-load.php существуют</li>";
echo "<li>Проверьте права доступа к файлам</li>";
echo "<li>Если проблема остается, обратитесь к хостинг-провайдеру</li>";
echo "</ol>";

echo "<p><strong>⚠️ ВАЖНО:</strong> Удалите этот файл после решения проблемы!</p>";
?>

