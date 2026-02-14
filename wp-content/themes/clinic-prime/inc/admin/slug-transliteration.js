/**
 * JavaScript для автоматической транслитерации slug'ов в админке WordPress
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

(function($) {
    'use strict';
    
    // Таблица транслитерации
    var transliterationMap = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
        'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i',
        'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
        'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
        'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch',
        'ш': 'sh', 'щ': 'sch', 'ь': '', 'ы': 'y', 'ъ': '',
        'э': 'e', 'ю': 'yu', 'я': 'ya',
        'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D',
        'Е': 'E', 'Ё': 'E', 'Ж': 'Zh', 'З': 'Z', 'И': 'I',
        'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N',
        'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T',
        'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C', 'Ч': 'Ch',
        'Ш': 'Sh', 'Щ': 'Sch', 'Ь': '', 'Ы': 'Y', 'Ъ': '',
        'Э': 'E', 'Ю': 'Yu', 'Я': 'Ya'
    };
    
    /**
     * Функция транслитерации
     */
    function transliterate(text) {
        var result = '';
        for (var i = 0; i < text.length; i++) {
            var char = text.charAt(i);
            result += transliterationMap[char] || char;
        }
        
        // Очистка от лишних символов
        result = result.toLowerCase();
        result = result.replace(/[^-a-z0-9_]+/g, '-');
        result = result.replace(/^-+|-+$/g, '');
        
        return result;
    }
    
    /**
     * Проверка на наличие кириллицы
     */
    function containsCyrillic(text) {
        return /[а-яё]/ui.test(text);
    }
    
    /**
     * Проверка на стандартные WordPress slug'и
     */
    function isWordPressDefaultSlug(slug) {
        var defaultSlugs = ['chernovik', 'auto-draft', 'revision', 'attachment'];
        
        for (var i = 0; i < defaultSlugs.length; i++) {
            var defaultSlug = defaultSlugs[i];
            if (slug === defaultSlug || new RegExp('^' + defaultSlug + '-\\d+$').test(slug)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Проверка, нужно ли транслитерировать slug
     */
    function shouldTransliterateSlug(slug, title) {
        // Если slug пустой - транслитерируем
        if (!slug) {
            return true;
        }
        
        // Если slug содержит кириллицу - транслитерируем
        if (containsCyrillic(decodeURIComponent(slug))) {
            return true;
        }
        
        // Если slug является стандартным WordPress slug'ом - транслитерируем
        if (isWordPressDefaultSlug(slug)) {
            return true;
        }
        
        // Если заголовок содержит кириллицу, а slug пустой или стандартный - транслитерируем
        if (title && containsCyrillic(title) && (!slug || isWordPressDefaultSlug(slug))) {
            return true;
        }
        
        return false;
    }
    
    /**
     * AJAX транслитерация
     */
    function ajaxTransliterate(text, callback) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'clinic_transliterate_slug',
                text: text,
                nonce: clinic_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    callback(response.data.slug);
                }
            }
        });
    }
    
    /**
     * Обработка изменения заголовка
     */
    function handleTitleChange() {
        var titleInput = $('#title');
        var slugInput = $('#post_name');
        
        if (titleInput.length && slugInput.length) {
            var title = titleInput.val();
            var currentSlug = slugInput.val();
            
            // Проверяем, нужно ли транслитерировать slug
            if (shouldTransliterateSlug(currentSlug, title)) {
                // Используем AJAX для транслитерации
                if (typeof clinic_ajax !== 'undefined') {
                    ajaxTransliterate(title, function(newSlug) {
                        slugInput.val(newSlug);
                    });
                } else {
                    // Fallback на клиентскую транслитерацию
                    var newSlug = transliterate(title);
                    slugInput.val(newSlug);
                }
            }
        }
    }
    
    /**
     * Обработка изменения slug'а
     */
    function handleSlugChange() {
        var slugInput = $('#post_name');
        var titleInput = $('#title');
        
        if (slugInput.length) {
            var slug = slugInput.val();
            var title = titleInput.length ? titleInput.val() : '';
            
            // Проверяем, нужно ли транслитерировать slug
            if (shouldTransliterateSlug(slug, title)) {
                var newSlug = transliterate(title || slug);
                slugInput.val(newSlug);
            }
        }
    }
    
    /**
     * Инициализация
     */
    function init() {
        // Обработка изменения заголовка
        $(document).on('input', '#title', function() {
            setTimeout(handleTitleChange, 100);
        });
        
        // Обработка изменения slug'а
        $(document).on('input', '#post_name', function() {
            setTimeout(handleSlugChange, 100);
        });
        
        // Обработка кнопки "ОК" в диалоге редактирования slug'а
        $(document).on('click', '#edit-slug-buttons .button', function() {
            setTimeout(handleSlugChange, 100);
        });
        
        // Обработка при загрузке страницы
        $(document).ready(function() {
            handleTitleChange();
            handleSlugChange();
        });
    }
    
    // Запуск инициализации
    init();
    
})(jQuery);
