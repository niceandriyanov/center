# Плагин "Оплата ТочкаБанка"

Плагин для приема платежей через API Точка Банка в WordPress.

## 🚀 Возможности

- ✅ Интеграция с API Точка Банка
- ✅ OAuth 2.0 авторизация
- ✅ Redirect-метод оплаты (безопасно)
- ✅ Webhook обработка уведомлений
- ✅ Универсальная интеграция через `entity_type` / `entity_id`
- ✅ Шорткоды для отображения кнопок оплаты
- ✅ Админ-панель с настройками
- ✅ Тестовый режим

## 📋 Установка

1. Загрузите папку плагина в `/wp-content/plugins/`
2. Активируйте плагин в админ-панели WordPress
3. Перейдите в "Настройки → Точка Банк"
4. Настройте параметры подключения

## ⚙️ Настройка

### 1. Регистрация в Точка Банке

1. Войдите в личный кабинет Точка Банка
2. Перейдите в раздел "API" → "OAuth 2.0 приложения"
3. Создайте новое приложение
4. Получите Client ID и Client Secret

### 2. Настройка плагина

В админ-панели WordPress:

1. **Client ID** - ID вашего OAuth приложения
2. **Client Secret** - секретный ключ приложения
3. **Redirect URL** - URL для возврата после оплаты
4. **Webhook URL** - URL для уведомлений от банка
5. **Режим тестирования** - включите для тестов

### 3. Настройка в Точка Банке

Укажите в настройках OAuth приложения:

- **Redirect URL**: `https://ваш-сайт.ru/tochka-payment/redirect/`
- **Webhook URL**: `https://ваш-сайт.ru/tochka-payment/webhook/`

## 💳 Использование

### Создание платежа через API
```php
$tochka_payment = new TochkaPayment();
$result = $tochka_payment->create_payment($order_id, $amount, $description);

if (is_wp_error($result)) {
    echo 'Ошибка: ' . $result->get_error_message();
} else {
    // Перенаправляем на страницу оплаты
    wp_redirect($result['Data']['paymentUrl']);
    exit;
}
```

### Проверка статуса платежа
```php
$status = $tochka_payment->get_payment_status($payment_id);
```

### Шорткоды

#### Кнопка оплаты
```
[tochka_pay_button order_id="123" button_text="Оплатить"]
```

#### Статус платежа
```
[tochka_payment_status order_id="123"]
```

### Хуки интеграции
```php
add_action('tochka_payment_entity_status_changed', function($entity_context, $provider_status, $amount, $payload) {
    // Обновление вашей бизнес-модели
}, 10, 4);

add_action('tochka_payment_entity_paid', function($entity_context, $provider_payment_id, $external_event_id, $payload) {
    // Оплата подтверждена
}, 10, 4);
```

### Интеграция с Center Med Renovatio
```php
add_action('center_med_renovatio_payment_paid', function($booking_public_id, $provider, $payment_external_id, $payload, $external_event_id) {
    // В плагине center-med-renovatio уже есть обработчик этого action
}, 10, 5);
```

### Фильтры gateway
```php
add_filter('tochka_payment_gateway_get_entity', function($entity, $order_id) {
    return [
        'status'  => 'pending',
        'payment' => '',
        'pay_at'  => '',
        'options' => wp_json_encode([
            ['price' => 2500, 'quantity' => 1],
        ]),
    ];
}, 10, 2);

add_filter('tochka_payment_gateway_calculate_amount', function($amount, $entity) {
    return 2500.00;
}, 10, 2);
```

### Интеграция с WooCommerce
```php
// В хуке обработки заказа
add_action('woocommerce_checkout_process', function() {
    $order_id = WC()->session->get('order_id');
    $order = wc_get_order($order_id);
    
    $result = $tochka_payment->create_payment(
        $order_id,
        $order->get_total(),
        'Оплата заказа WooCommerce №' . $order_id
    );
    
    if (!is_wp_error($result)) {
        // Сохраняем данные платежа
        $order->update_meta_data('_tochka_payment_id', $result['Data']['paymentId']);
        $order->update_meta_data('_tochka_payment_url', $result['Data']['paymentUrl']);
        $order->save();
    }
});
```

## 🧪 Тестирование

Для тестирования плагина используйте файл:
```
https://ваш-сайт.ru/wp-content/plugins/tochka-bank-payment/examples.php
```

Этот файл позволяет:
- ✅ Проверить настройки плагина
- ✅ Создать тестовый платеж
- ✅ Посмотреть отладочную информацию
- ✅ Просмотреть последние платежи
- ✅ Проверить логи

### PHP код

```php
// Создание платежа
$payment_gateway = new TochkaPaymentGateway();
$payment_url = $payment_gateway->create_payment_for_order($order_id);

// Отображение кнопки оплаты
echo $payment_gateway->render_payment_button($order_id, 'Оплатить');
```

## 🔧 API методы

### TochkaPaymentGateway

- `create_payment_for_order($order_id)` - создание платежа для заказа
- `render_payment_button($order_id, $text)` - отображение кнопки оплаты
- `get_tochka_payments($limit, $offset)` - получение списка платежей
- `get_payment_stats()` - статистика платежей

### TochkaPayment

- `create_payment_link($order_id, $amount, $description)` - создание ссылки на оплату
- `get_payment_status($payment_id)` - получение статуса платежа
- `update_payment_status($payment_id, $status)` - обновление статуса

### 🌐 API Endpoints

**OAuth (общий для всех режимов):**
- OAuth: `https://enter.tochka.com/connect/token`

**Тестовый режим:**
- Payments: `https://enter.tochka.com/sandbox/v2/acquiring/v1/payments`

**Боевой режим:**
- Payments: `https://enter.tochka.com/uapi/acquiring/v1/payments`

**Scope для OAuth:**
- `accounts balances customers statements sbp payments`

## 📊 База данных

### Таблица `wp_tochka_payments`

| Поле | Тип | Описание |
|------|-----|----------|
| id | bigint | ID записи |
| provider | varchar | Провайдер платежа (`tochka`) |
| entity_type | varchar | Тип сущности (`visit`, `booking`, ...) |
| entity_id | varchar | ID сущности |
| entity_public_id | varchar | Публичный ID сущности (например UUID брони) |
| order_id | varchar | Legacy ID заказа (обратная совместимость) |
| payment_id | varchar | ID платежа в банке |
| external_event_id | varchar | Внешний ID события для идемпотентности |
| status | varchar | Статус платежа |
| amount | decimal | Сумма |
| currency | varchar | Валюта |
| payment_url | text | URL для оплаты |
| callback_data | longtext | Данные от банка |
| created_at | datetime | Дата создания |
| updated_at | datetime | Дата обновления |

## 🔒 Безопасность

- Все данные передаются по HTTPS
- OAuth 2.0 авторизация
- Валидация всех входящих данных
- Проверка nonce для AJAX запросов
- Санитизация вывода

## 🐛 Отладка

Включите отладку в `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Логи сохраняются в `/wp-content/debug.log`

## 📞 Поддержка

При возникновении проблем:

1. Проверьте настройки в админ-панели
2. Убедитесь, что URL корректны
3. Проверьте логи ошибок
4. Обратитесь в службу поддержки Точка Банка

## 📝 Лицензия

GPL v2 или более поздняя версия
