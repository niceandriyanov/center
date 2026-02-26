jQuery(document).ready(function($) {
    
    // Тестирование подключения
    $('#test-connection').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        
        button.prop('disabled', true).text('Тестирование...');
        
        $.ajax({
            url: tochka_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tochka_test_connection',
                nonce: tochka_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ ' + response.data);
                    // Показываем кнопку для подтверждения разрешений
                    $('#create-consent').show();
                } else {
                    alert('❌ ' + response.data);
                }
            },
            error: function() {
                alert('❌ Ошибка при тестировании подключения');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Проверка статуса consent
    $('#check-consent').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        
        button.prop('disabled', true).text('Проверка...');
        
        $.ajax({
            url: tochka_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tochka_check_consent',
                nonce: tochka_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('🔍 ' + response.data);
                } else {
                    alert('❌ ' + response.data);
                }
            },
            error: function() {
                alert('❌ Ошибка при проверке consent');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Создание consent и получение ссылки для подтверждения
    $('#create-consent').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        
        button.prop('disabled', true).text('Создание consent...');
        
        $.ajax({
            url: tochka_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tochka_create_consent',
                nonce: tochka_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.auth_url) {
                        // Показываем форму с ссылкой для копирования
                        $('#auth-url').val(response.data.auth_url);
                        $('#auth-url-form').show();
                        alert('✅ ' + response.data.message);
                    } else {
                        alert('🎉 ' + response.data);
                        // Скрываем кнопку после успешного создания consent
                        $('#create-consent').hide();
                    }
                } else {
                    alert('❌ ' + response.data);
                }
            },
            error: function() {
                alert('❌ Ошибка при создании consent');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Копирование ссылки авторизации
    $('#copy-auth-url').on('click', function(e) {
        e.preventDefault();
        
        var authUrl = $('#auth-url');
        authUrl.select();
        document.execCommand('copy');
        
        var button = $(this);
        var originalText = button.text();
        button.text('✅ Скопировано!');
        
        setTimeout(function() {
            button.text(originalText);
        }, 2000);
    });
    
    // Обмен кода авторизации на финальный токен
    $('#exchange-code').on('click', function(e) {
        e.preventDefault();
        
        var authCode = $('#auth-code').val();
        if (!authCode) {
            alert('Введите код авторизации');
            return;
        }
        
        var button = $(this);
        var originalText = button.text();
        
        button.prop('disabled', true).text('Обработка...');
        
        $.ajax({
            url: tochka_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tochka_exchange_code',
                auth_code: authCode,
                state: $('#auth-code').data('state') || '',
                nonce: tochka_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('🎉 ' + response.data);
                    // Показываем кнопку для подтверждения разрешений
                    $('#create-consent').show();
                } else {
                    alert('❌ ' + response.data);
                }
            },
            error: function() {
                alert('❌ Ошибка при обмене кода');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Показываем форму при получении ссылки для авторизации
    $(document).on('tochka_show_auth_form', function() {
        $('#auth-code-form').show();
    });
    
    // Копирование URL в буфер обмена
    $('.copy-url').on('click', function(e) {
        e.preventDefault();
        
        var url = $(this).data('url');
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function() {
                alert('URL скопирован в буфер обмена!');
            });
        } else {
            // Fallback для старых браузеров
            var textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('URL скопирован в буфер обмена!');
        }
    });
    
    // Валидация формы
    $('form').on('submit', function(e) {
        var clientId = $('input[name="tochka_client_id"]').val();
        var clientSecret = $('input[name="tochka_client_secret"]').val();
        
        if (!clientId || !clientSecret) {
            e.preventDefault();
            alert('Пожалуйста, заполните все обязательные поля');
            return false;
        }
    });
    
    // Показ/скрытие пароля
    $('.toggle-password').on('click', function(e) {
        e.preventDefault();
        
        var input = $(this).siblings('input');
        var icon = $(this).find('span');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.text('👁️');
        } else {
            input.attr('type', 'password');
            icon.text('👁️‍🗨️');
        }
    });
});
