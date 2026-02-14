/**
 * Drag & Drop сортировка таксономий в админке
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */



var ClinicTaxonomySorting = {
    
    // Инициализация
    init: function() {
        this.bindEvents();
        this.initSortable();
        this.addDragHandles();
        this.addSortingHint();
    },
    
    // Привязка событий
    bindEvents: function() {
        jQuery(function($){
            // Текущая таксономия: ?taxonomy=...
            var params = new URLSearchParams(window.location.search);
            var taxonomy = params.get('taxonomy') || '';
          
            // Каждая строка термина имеет id вида "tag-123"
            $('#the-list tr').each(function(){
              var tr = $(this);
              var m = (this.id || '').match(/^tag-(\d+)$/);
              if (!m) return;
          
              var termId = m[1];
              var slug   = tr.find('td.slug').text().trim(); // колонка "Ярлык / Slug"
              // имя термина: сперва ссылка в колонке name, иначе текст ячейки
              var name   = tr.find('td.name a.row-title, td.name strong a').first().text().trim() || tr.find('td.name').text().trim();
          
              tr.attr({
                'data-term-id': termId,
                'data-taxonomy': taxonomy,
                'data-term-slug': slug,
                'data-term-name': name
              });
            });
        });
    },
    
    // Добавление ручек для перетаскивания
    addDragHandles: function() {
        var $rows = jQuery('.wp-list-table tbody tr');
        
        $rows.each(function() {
            var $row = jQuery(this);
            var $firstCell = $row.find('td:first');
            
            // Добавляем ручку для перетаскивания
            if (!$firstCell.find('.drag-handle').length) {
                $firstCell.prepend('<span class="drag-handle dashicons dashicons-menu" title="Перетащите для изменения порядка"></span>');
            }
            
            // Добавляем data-атрибуты, если их нет
            //ClinicTaxonomySorting.addDataAttributes($row);
        });
    },
    
    // Добавление data-атрибутов к строкам
    addDataAttributes: function($row) {
        // Ищем ячейку с именем (обычно вторая колонка)
        var $nameCell = $row.find('td:nth-child(2)');
        
        if ($nameCell.length && !$nameCell.find('.term-row').length) {
            // Извлекаем term_id из ссылки редактирования
            var $editLink = $nameCell.find('a[href*="tag_ID="]');
            if ($editLink.length) {
                var href = $editLink.attr('href');
                var match = href.match(/tag_ID=(\d+)/);
                if (match) {
                    var termId = match[1];
                    var $nameContent = $nameCell.contents();
                    
                    // Обертываем содержимое в div с data-атрибутами
                    $nameCell.html('<div class="term-row" data-term-id="' + termId + '" data-order="0">' + $nameContent + '</div>');
                }
            }
        }
    },
    
    // Добавление подсказки о сортировке
    addSortingHint: function() {
        var $table = jQuery('.wp-list-table');
        
        if ($table.length && !$table.find('.sorting-hint').length) {
            $table.before('<div class="sorting-hint notice notice-info"><p><strong>Подсказка:</strong> Перетащите строки для изменения порядка. Порядок сохранится автоматически.</p></div>');
        }
    },
    
    // Инициализация сортируемых элементов
    initSortable: function() {
        var $table = jQuery('.wp-list-table');
        
        if ($table.length === 0) {
            return;
        }
    
        // Проверяем, что jQuery UI Sortable доступен
        if (typeof jQuery.fn.sortable === 'undefined') {
            return;
        }
    
        // Добавляем класс для таблицы
        $table.addClass('taxonomy-table');
    
        // Инициализируем сортировку для строк
        try {
            var $tbody = $table.find('tbody');
            
            $tbody.sortable({
                items: 'tr',
                handle: '.drag-handle',
                axis: 'y',
                cursor: 'move',
                opacity: 0.8,
                scroll: true,
                tolerance: 'pointer',
                distance: 3,
                start: this.onSortStart.bind(this),
                stop: this.onSortStop.bind(this),
                update: this.onSortUpdate.bind(this)
            });
        } catch (error) {
            // Ошибка инициализации сортировки
        }
    },
    
    // Событие начала сортировки
    onSortStart: function(event, ui) {
        ui.item.addClass('sorting');
        this.showNotice('Начинаем сортировку...', 'info');
    },
    
    // Событие окончания сортировки
    onSortStop: function(event, ui) {
        ui.item.removeClass('sorting');
    },
    
    // Событие обновления порядка
    onSortUpdate: function(event, ui) {
        this.saveOrder();
    },
    
    // Сохранение нового порядка
    saveOrder: function() {
        var $table = jQuery('.wp-list-table');
        var $rows = $table.find('tbody tr');
        var order = [];
        var taxonomy = clinicTaxonomySorting.taxonomy;
    
        // Собираем порядок терминов из data-атрибутов
        $rows.each(function(index) {
            var $row = jQuery(this);
            var termId = $row.data('term-id');
            
            if (termId) {                
                order.push(termId);
            } else {
                // Fallback: ищем term-id в других местах
                var termId = $row.find('[data-term-id]').data('term-id');
                
                if (termId) {
                    order.push(termId);
                }
            }
        });
    
        if (order.length === 0) {
            return;
        }
    
        // Показываем уведомление о сохранении
        this.showNotice('Сохранение порядка...', 'saving');
    
        // Отправляем AJAX запрос
        jQuery.ajax({
            url: clinicTaxonomySorting.ajaxUrl,
            type: 'POST',
            data: {
                action: 'clinic_save_taxonomy_order',
                nonce: clinicTaxonomySorting.nonce,
                taxonomy: taxonomy,
                order: order
            },
            success: function(response) {
                if (response.success) {
                    ClinicTaxonomySorting.showNotice('Порядок успешно сохранен!', 'success');
                    ClinicTaxonomySorting.updateOrderNumbers();
                } else {
                    ClinicTaxonomySorting.showNotice('Ошибка при сохранении порядка!', 'error');
                }
            },
            error: function(xhr, status, error) {
                ClinicTaxonomySorting.showNotice('Ошибка при сохранении порядка!', 'error');
            }
        });
    },
    
    // Обновление номеров порядка
    updateOrderNumbers: function() {
        var $rows = jQuery('.wp-list-table tbody tr');
        
        $rows.each(function(index) {
            var $row = jQuery(this);
            var $termRow = $row.find('.term-row');
            
            if ($termRow.length) {
                $termRow.attr('data-order', index);
            }
        });
    },
    
    // Показ уведомлений
    showNotice: function(message, type) {
        // Удаляем существующие уведомления
        jQuery('.clinic-notice').remove();
        
        var noticeClass = 'notice-' + type;
        var $notice = jQuery('<div class="clinic-notice notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Добавляем уведомление после заголовка страницы
        jQuery('.wp-heading-inline').after($notice);
        
        // Автоматически скрываем через 3 секунды
        setTimeout(function() {
            jQuery('.clinic-notice').fadeOut();
        }, 3000);
    }
};

// Инициализация при загрузке DOM
jQuery(document).ready(function() {
    // Небольшая задержка для полной загрузки всех скриптов
    setTimeout(function() {
        ClinicTaxonomySorting.init();
    }, 500);
});
