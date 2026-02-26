# 🚀 Использование плагина "Оплата ТочкаБанка"

## ⚠️ Актуальная модель интеграции

- Плагин работает с универсальной сущностью оплаты: `entity_type` + `entity_id` (+ `entity_public_id`).
- Поле `order_id` сохранено для обратной совместимости, но для новых интеграций используйте `entity_*`.
- Webhook обрабатывается через встроенный REST endpoint: `/wp-json/tochka/v1/webhook`.

## 📋 Основные методы

### 1. Создание платежа
```php
$tochka_payment = new TochkaPayment();

$result = $tochka_payment->create_payment(
    $order_id,      // ID заказа
    $amount,        // Сумма в рублях
    $description,   // Описание платежа
    $return_url     // URL для возврата (опционально)
);
```

### 2. Проверка статуса платежа
```php
$status = $tochka_payment->get_payment_status($payment_id);
```

### 3. Создание платежной ссылки
```php
$link = $tochka_payment->create_payment_link($order_id, $amount, $description);
```

## 🎯 Примеры использования

### Простой платеж
```php
// Создаем платеж на 1000 рублей
$result = $tochka_payment->create_payment(12345, 1000.00, 'Оплата заказа №12345');

if (is_wp_error($result)) {
    echo 'Ошибка: ' . $result->get_error_message();
} else {
    // Перенаправляем на страницу оплаты
    wp_redirect($result['Data']['paymentUrl']);
    exit;
}
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
        'Оплата заказа WooCommerce №' . $order_id,
        $order->get_checkout_order_received_url()
    );
    
    if (!is_wp_error($result)) {
        // Сохраняем данные платежа
        $order->update_meta_data('_tochka_payment_id', $result['Data']['paymentId']);
        $order->update_meta_data('_tochka_payment_url', $result['Data']['paymentUrl']);
        $order->save();
    }
});
```

### Обработка webhook'ов
```php
// Ручной обработчик не нужен.
// Плагин сам обрабатывает webhook по адресу:
// /wp-json/tochka/v1/webhook
```

### Хуки интеграции (рекомендуется)
```php
// Универсальный хук изменения статуса
add_action('tochka_payment_entity_status_changed', function($entity_context, $provider_status, $amount, $payload) {
    // $entity_context: entity_type, entity_id, entity_public_id, order_id
}, 10, 4);

// Успешная оплата сущности
add_action('tochka_payment_entity_paid', function($entity_context, $provider_payment_id, $external_event_id, $payload) {
    // Здесь обновляйте вашу доменную модель
}, 10, 4);
```

### Интеграция с Center Med Renovatio
```php
// Плагин уже вызывает этот action при успешной оплате, если найден booking_public_id:
// do_action('center_med_renovatio_payment_paid', $booking_public_id, 'tochka', $payment_external_id, $payload, $external_event_id);
```

### Фильтры для gateway
```php
// Передать сущность в gateway (вместо старого events_order)
add_filter('tochka_payment_gateway_get_entity', function($entity, $order_id) {
    return [
        'status'  => 'pending',
        'payment' => '',
        'pay_at'  => '',
        'options' => wp_json_encode([
            ['price' => 3500, 'quantity' => 1],
        ]),
    ];
}, 10, 2);

// Переопределить расчет суммы
add_filter('tochka_payment_gateway_calculate_amount', function($amount, $entity) {
    return 3500.00;
}, 10, 2);
```

## 🔧 Настройка

### 1. Учетные данные
- **Client ID** - идентификатор приложения в Точка Банке
- **Client Secret** - секретный ключ приложения
- **Redirect URL** - URL для возврата после оплаты
- **Webhook URL** - URL для получения уведомлений

### 2. OAuth 2.0 настройка
1. Зарегистрируйте приложение в Точка Банке
2. Укажите необходимые права доступа (scope)
3. Выполните полный OAuth flow в настройках плагина
4. Получите финальный токен для работы с API

## 📊 Структура ответа API

### Успешное создание платежа
```json
{
    "Data": {
        "paymentId": "payment_123456",
        "paymentUrl": "https://pay.tochka.com/payment/123456",
        "amount": {
            "amount": "1000.00",
            "currency": "RUB"
        },
        "status": "Pending",
        "orderId": "12345",
        "expirationDateTime": "2024-01-01T12:00:00Z"
    }
}
```

### Статусы платежей
- **CREATED** - ожидает оплаты
- **APPROVED** - успешно оплачен
- **DECLINED** - ошибка оплаты
- **EXPIRED** - истек срок действия
- **REFUNDED / ON-REFUND** - возврат

## 🚨 Обработка ошибок

### Типичные ошибки
```php
$result = $tochka_payment->create_payment($order_id, $amount, $description);

if (is_wp_error($result)) {
    switch ($result->get_error_code()) {
        case 'no_credentials':
            echo 'Не настроены учетные данные';
            break;
        case 'no_final_token':
            echo 'Не завершена настройка OAuth 2.0';
            break;
        case 'api_error':
            echo 'Ошибка API: ' . $result->get_error_message();
            break;
        default:
            echo 'Неизвестная ошибка: ' . $result->get_error_message();
    }
}
```

## 🔄 Обновление токенов

Плагин автоматически обновляет токены через refresh_token, но вы можете принудительно обновить:

```php
// Получаем новый токен
$new_token = $tochka_payment->get_access_token();
```

## 📝 Логирование

Все операции записываются в лог WordPress:
```php
// Включить логирование
error_log('Tochka Bank: Создание платежа для заказа ' . $order_id);
```

## 🎯 Лучшие практики

1. **Всегда проверяйте ошибки** - используйте `is_wp_error()`
2. **Сохраняйте ID платежа** - для отслеживания статуса
3. **Используйте action-hooks** - для автоматического обновления доменных статусов
4. **Логируйте операции** - для отладки и мониторинга
5. **Используйте HTTPS** - для безопасности передачи данных

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

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи WordPress
2. Убедитесь, что OAuth 2.0 настроен правильно
3. Проверьте права доступа в Точка Банке
4. Обратитесь к документации API Точка Банка
