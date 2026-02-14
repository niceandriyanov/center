<?php
/**
 * Кастомное поле ACF для выбора цвета
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Field_Color_Select extends acf_field {
    
    function __construct() {
        $this->name = 'color_select';
        $this->label = 'Выбор цвета';
        $this->category = 'choice';
        $this->defaults = array(
            'choices' => array(),
            'default_value' => '',
            'allow_null' => 0,
            'return_format' => 'value',
        );
        
        // Поддержка таксономий
        $this->l10n = array(
            'multiple'      => 0,
            'allow_null'    => 0,
        );
        
        parent::__construct();
    }
    
    /**
     * Парсит строку вариантов в массив
     */
    private function parse_choices($choices_string) {
        if (empty($choices_string)) {
            return array();
        }
        
        $choices = array();
        $lines = explode("\n", $choices_string);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Проверяем, есть ли разделитель ":"
            if (strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                $value = trim($parts[0]);
                $label = trim($parts[1]);
            } else {
                // Если разделителя нет, используем всю строку как значение и этикетку
                $value = $line;
                $label = $line;
            }
            
            if (!empty($value)) {
                $choices[$value] = $label;
            }
        }
        
        return $choices;
    }
    
    function render_field($field) {
        $value = $field['value'];
        $choices = $this->parse_choices($field['choices']);
        
        // Определяем контекст (пост или таксономия)
        $is_taxonomy = isset($field['field_type']) && $field['field_type'] === 'taxonomy';

        echo '<div class="acf-color-select">';
        
        // Для таксономий используем другой name
        if ($is_taxonomy) {
            echo '<input type="hidden" name="acf[' . esc_attr($field['key']) . ']" value="' . esc_attr($value) . '" />';
        } else {
            echo '<input type="hidden" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
        }
        
        if (!empty($choices)) {
            foreach ($choices as $choice_value => $choice_label) {
                $selected = ($value == $choice_value) ? 'selected' : '';
                echo '<div class="color-option ' . $selected . '" data-value="' . esc_attr($choice_value) . '">';
                echo $choice_label; // HTML будет рендериться
                echo '</div>';
            }
        } else {
            // Если варианты не заданы, показываем сообщение
            echo '<p style="color: #666; font-style: italic;">Варианты цветов не заданы. Добавьте их в настройках поля.</p>';
        }
        
        echo '</div>';
        
        echo '<script>
        jQuery(document).ready(function($) {
            $(".acf-color-select .color-option").click(function() {
                var value = $(this).data("value");
                $(this).closest(".acf-color-select").find("input[type=hidden]").val(value);
                $(".color-option").removeClass("selected");
                $(this).addClass("selected");
            });
        });
        </script>';
    }
    
    function update_value($value, $post_id, $field) {
        // Для таксономий
        if (is_numeric($post_id)) {
            // Это ID поста
            return $value;
        } else {
            // Это таксономия
            $term = get_term($post_id);
            if ($term && !is_wp_error($term)) {
                update_field($field['name'], $value, $term);
            }
        }
        
        return $value;
    }
    
    function load_value($value, $post_id, $field) {
        // Для таксономий
        if (is_numeric($post_id)) {
            // Это ID поста
            return $value;
        } else {
            // Это таксономия
            $term = get_term($post_id);
            if ($term && !is_wp_error($term)) {
                return get_field($field['name'], $term);
            }
        }
        
        return $value;
    }
    
    function format_value($value, $post_id, $field) {
        // Если значение пустое, возвращаем false
        if (empty($value)) {
            return false;
        }
        
        // Получаем варианты
        $choices = $this->parse_choices($field['choices']);
        
        // Если варианты не заданы, возвращаем значение как есть
        if (empty($choices)) {
            return $value;
        }
        
        // В зависимости от формата возврата
        switch ($field['return_format']) {
            case 'label':
                return isset($choices[$value]) ? $choices[$value] : $value;
                
            case 'array':
                return array(
                    'value' => $value,
                    'label' => isset($choices[$value]) ? $choices[$value] : $value
                );
                
            case 'value':
            default:
                return $value;
        }
    }
    
    /**
     * Рендер настроек поля в админке
     */
    function render_field_settings($field) {
        // Варианты
        acf_render_field_setting($field, array(
            'label'         => __('Варианты','acf'),
            'instructions'  => __('Введите каждый вариант с новой строки. Для большего контроля вы можете указать и значение, и этикетку следующим образом:','acf') . '<br /><span style="font-family:monospace;">red : Красный</span>',
            'type'          => 'textarea',
            'name'          => 'choices',
        ));
        
        // Значение по умолчанию
        acf_render_field_setting($field, array(
            'label'         => __('Значение по умолчанию','acf'),
            'instructions'  => __('Введите значение по умолчанию','acf'),
            'type'          => 'text',
            'name'          => 'default_value',
        ));
        
        // Разрешить пустое значение
        acf_render_field_setting($field, array(
            'label'         => __('Разрешить пустое значение?','acf'),
            'instructions'  => __('Разрешить пользователю не выбирать значение?','acf'),
            'type'          => 'true_false',
            'name'          => 'allow_null',
            'ui'            => 1,
        ));
        
        // Формат возврата
        acf_render_field_setting($field, array(
            'label'         => __('Формат возврата','acf'),
            'instructions'  => __('Укажите формат возвращаемого значения','acf'),
            'type'          => 'radio',
            'name'          => 'return_format',
            'choices'       => array(
                'value'     => __('Значение','acf'),
                'label'     => __('Этикетка','acf'),
                'array'     => __('И значение, и этикетка','acf'),
            ),
            'layout'        => 'horizontal',
        ));
    }
}

// Регистрируем поле
acf_register_field_type('ACF_Field_Color_Select');
