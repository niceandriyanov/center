# 🔧 Решение проблем с подключением

## ❌ Ошибка "HTTP 403: The access token is missing"

### Причина:
Неправильный URL для создания consent. Согласно документации Точка Банка, consent всегда создается через production URL, даже в sandbox режиме.

### ✅ Решение:
Плагин исправлен - теперь использует правильный URL:
- **Consent URL**: `https://enter.tochka.com/uapi/v1.0/consents` (всегда production)
- **OAuth URL**: `https://enter.tochka.com/connect/token` (всегда production)
- **API URL**: зависит от sandbox режима
- **Consent**: БЕССРОЧНЫЙ (без expirationDateTime)

## ❌ Ошибка "cURL error 28: Resolving timed out"

### Возможные причины:

1. **Проблемы с интернет-соединением**
2. **Блокировка хостинг-провайдером**
3. **Проблемы с DNS**
4. **Настройки прокси/VPN**
5. **Ограничения сервера**

### 🔍 Диагностика:

#### 1. Проверьте доступность API
```bash
# В терминале сервера выполните:
curl -I https://enter.tochka.com
curl -I https://enter.tochka.com/sandbox/v2/acquiring/v1
curl -I https://enter.tochka.com/uapi/acquiring/v1
```

#### 2. Проверьте DNS
```bash
nslookup enter.tochka.com
```

#### 3. Проверьте настройки PHP
```php
// Добавьте в wp-config.php для отладки:
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### 🛠 Решения:

#### 1. Обратитесь к хостинг-провайдеру
- Уточните, не блокируются ли исходящие HTTPS-запросы
- Проверьте настройки файрвола
- Убедитесь, что cURL включен

#### 2. Настройки cURL в PHP
```php
// В wp-config.php добавьте:
ini_set('default_socket_timeout', 60);
ini_set('max_execution_time', 60);
```

#### 3. Альтернативные настройки
```php
// В классе TochkaPayment измените:
$response = wp_remote_post($url, array(
    'timeout' => 60,
    'sslverify' => false,
    'httpversion' => '1.1',
    'headers' => array(
        'User-Agent' => 'WordPress/' . get_bloginfo('version'),
        'Connection' => 'close'
    )
));
```

#### 4. Использование прокси (если нужно)
```php
// Добавьте в wp-config.php:
define('WP_PROXY_HOST', 'your-proxy.com');
define('WP_PROXY_PORT', '8080');
define('WP_PROXY_USERNAME', 'username');
define('WP_PROXY_PASSWORD', 'password');
```

### 🔄 Альтернативные решения:

#### 1. Используйте тестовый режим
- Включите "Режим тестирования" в настройках
- Это использует упрощенную логику без реального API

#### 2. Ручная настройка
- Настройте плагин без тестирования подключения
- Проверьте работу через реальные платежи

#### 3. Обратитесь в поддержку
- Точка Банк: support@tochka.com
- Ваш хостинг-провайдер

### 📋 Чек-лист для хостинг-провайдера:

- [ ] cURL включен и работает
- [ ] SSL-сертификаты актуальны
- [ ] Исходящие HTTPS-запросы разрешены
- [ ] Нет блокировки по User-Agent
- [ ] Таймауты настроены корректно
- [ ] DNS резолвинг работает

### 🧪 Тестирование:

1. **Проверка настроек** - используйте кнопку "⚙️ Проверить настройки"
2. **Тест подключения** - используйте кнопку "🧪 Тестировать подключение"
3. **Проверка логов** - смотрите `/wp-content/debug.log`

### 📞 Контакты поддержки:

- **Точка Банк**: https://tochka.com/support
- **WordPress**: https://wordpress.org/support
- **Хостинг-провайдер**: обратитесь в техподдержку

### 💡 Дополнительные советы:

1. **Обновите WordPress** до последней версии
2. **Отключите другие плагины** временно для тестирования
3. **Проверьте .htaccess** на наличие блокировок
4. **Используйте HTTPS** для сайта
5. **Проверьте сертификаты SSL**

---

Если проблема не решается, обратитесь к разработчику плагина с подробным описанием ошибки и логами.
