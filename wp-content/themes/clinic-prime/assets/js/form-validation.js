/**
 * Валидация формы с чекбоксом согласия и обработка Contact Form 7
 */

document.addEventListener('DOMContentLoaded', function() {
    // Находим чекбокс и кнопку
    const agreeCheckbox = document.querySelector('input[name="agree"]');
    const submitButton = document.querySelector('button[type="submit"]');
    
    if (agreeCheckbox && submitButton) {
        // Функция для проверки состояния чекбокса
        function checkAgreement() {
            if (agreeCheckbox.checked) {
                submitButton.disabled = false;
                submitButton.classList.remove('disabled');
            } else {
                submitButton.disabled = true;
                submitButton.classList.add('disabled');
            }
        }
        
        // Проверяем при загрузке страницы
        checkAgreement();
        
        // Проверяем при изменении чекбокса
        agreeCheckbox.addEventListener('change', checkAgreement);
        
        // Дополнительно проверяем при клике (для надежности)
        agreeCheckbox.addEventListener('click', function() {
            setTimeout(checkAgreement, 10);
        });
    }
    
    // Обработка Contact Form 7
    if (typeof wpcf7 !== 'undefined') {
        // Скрываем стандартные сообщения CF7
        const cf7Messages = document.querySelectorAll('.wpcf7-response-output, .wpcf7-not-valid-tip, .wpcf7-validation-errors, .wpcf7-mail-sent-ok, .wpcf7-mail-sent-ng, .wpcf7-spam-blocked');
        cf7Messages.forEach(function(message) {
            message.style.display = 'none';
        });
        
        // Обработка успешной отправки формы
        document.addEventListener('wpcf7mailsent', function(event) {
            // Закрываем активное модальное окно с формой
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                activeModal.classList.remove('active');
            }
            
            // Убираем класс блокировки прокрутки
            document.body.classList.remove('modal-open');
            
            // Теперь открываем модальное окно благодарности
            const modalThanks = document.getElementById('modalThanks');
            if (modalThanks) {
                modalThanks.classList.add('active');
                
                // Добавляем класс для body чтобы заблокировать прокрутку
                document.body.classList.add('modal-open');
            }
        });
        
        // Обработка ошибок отправки (опционально)
        document.addEventListener('wpcf7invalid', function(event) {
            console.log('Форма содержит ошибки валидации');
        });
        
        document.addEventListener('wpcf7spam', function(event) {
            console.log('Форма заблокирована как спам');
        });
        
        document.addEventListener('wpcf7mailfailed', function(event) {
            console.log('Ошибка отправки формы');
        });
    }
    
    // Закрытие модального окна
    const modalThanks = document.getElementById('modalThanks');
    if (modalThanks) {
        const closeButton = modalThanks.querySelector('.modalWrapClose');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                modalThanks.classList.remove('active');
                document.body.classList.remove('modal-open');
            });
        }
        
        // Закрытие по клику вне модального окна
        modalThanks.addEventListener('click', function(event) {
            if (event.target === modalThanks) {
                modalThanks.classList.remove('active');
                document.body.classList.remove('modal-open');
            }
        });
        
        // Закрытие по клавише Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modalThanks.classList.contains('active')) {
                modalThanks.classList.remove('active');
                document.body.classList.remove('modal-open');
            }
        });
    }
});
