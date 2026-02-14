<?php
/**
 * Дополнительные функции шаблонов
 */

// Предотвращение прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Функция для отображения карточки врача
 */
function clinic_doctor_card($doctor_id = null) {
    if (!$doctor_id) {
        $doctor_id = get_the_ID();
    }
    
    $specialization = get_post_meta($doctor_id, '_doctor_specialization', true);
    $experience = get_post_meta($doctor_id, '_doctor_experience', true);
    $phone = get_post_meta($doctor_id, '_doctor_phone', true);
    $email = get_post_meta($doctor_id, '_doctor_email', true);
    
    ?>
    <div class="doctor-card">
        <?php if (has_post_thumbnail($doctor_id)) : ?>
            <div class="doctor-photo">
                <?php echo get_the_post_thumbnail($doctor_id, 'clinic-medium'); ?>
            </div>
        <?php endif; ?>
        
        <h3 class="doctor-name">
            <a href="<?php echo get_permalink($doctor_id); ?>">
                <?php echo get_the_title($doctor_id); ?>
            </a>
        </h3>
        
        <?php if ($specialization) : ?>
            <div class="doctor-specialization">
                <?php echo esc_html($specialization); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($experience) : ?>
            <div class="doctor-experience">
                Опыт работы: <?php echo esc_html($experience); ?>
            </div>
        <?php endif; ?>
        
        <div class="doctor-excerpt">
            <?php echo get_the_excerpt($doctor_id); ?>
        </div>
        
        <div class="doctor-contacts">
            <?php if ($phone) : ?>
                <a href="tel:<?php echo clinic_format_phone($phone); ?>" class="doctor-phone">
                    <i class="fas fa-phone"></i> <?php echo esc_html($phone); ?>
                </a>
            <?php endif; ?>
            
            <?php if ($email) : ?>
                <a href="mailto:<?php echo esc_attr($email); ?>" class="doctor-email">
                    <i class="fas fa-envelope"></i> <?php echo esc_html($email); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <a href="<?php echo get_permalink($doctor_id); ?>" class="btn btn-outline">
            Подробнее
        </a>
    </div>
    <?php
}



/**
 * Функция для получения похожих врачей
 * 
 * @param int $doctor_id ID текущего врача
 * @param int $limit Количество похожих врачей для вывода
 * @return array Массив похожих врачей
 */
function get_similar_doctors($doctor_id = null, $limit = 6) {
    if (!$doctor_id) {
        $doctor_id = get_the_ID();
    }
    
    // Получаем термины таксономий для текущего врача
    $specialties = wp_get_post_terms($doctor_id, 'doctor_specialty', array('fields' => 'ids'));
    $diseases = wp_get_post_terms($doctor_id, 'doctor_diseases', array('fields' => 'ids'));
    
    // Если нет терминов, возвращаем пустой массив
    if (empty($specialties) && empty($diseases)) {
        return array();
    }
    
    // Строим аргументы для WP_Query
    $args = array(
        'post_type' => 'doctors',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'post__not_in' => array($doctor_id), // Исключаем текущего врача
        'orderby' => 'rand', // Случайный порядок
        'meta_query' => array(
            'relation' => 'OR'
        ),
        'tax_query' => array(
            'relation' => 'OR'
        )
    );
    
    // Добавляем условия для таксономий
    if (!empty($specialties)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'doctor_specialty',
            'field' => 'term_id',
            'terms' => $specialties,
            'operator' => 'IN'
        );
    }
    
    if (!empty($diseases)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'doctor_diseases',
            'field' => 'term_id',
            'terms' => $diseases,
            'operator' => 'IN'
        );
    }
    
    $similar_doctors = new WP_Query($args);
    
    return $similar_doctors->posts;
}

/**
 * Функция для вывода похожих врачей
 * 
 * @param int $doctor_id ID текущего врача
 * @param int $limit Количество похожих врачей для вывода
 * @param string $title Заголовок секции
 */
function similar_doctors($doctor_id = null, $limit = 6, $title = 'Похожие специалисты') {
    if (!$doctor_id) {
        $doctor_id = get_the_ID();
    }
    
    $similar_doctors = get_similar_doctors($doctor_id, $limit);
    
    if (empty($similar_doctors)) {
        return;
    }
    
    ?>
    <section class="sectionSpec sectionSpecInner">
        <div class="container">
            <h2 class="sectionTitle"><?= esc_html($title); ?></h2>
            <div class="specSliderWrap">
                <div class="swiper-container specSlider">
                    <div class="swiper-wrapper">
                        <?php foreach ($similar_doctors as $doctor) : ?>
                            <div class="swiper-slide">
                                <?php 
                                // Используем существующий шаблон карточки врача
                                get_template_part('template-parts/parts/doctor', null, array('id' => $doctor->ID));
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php if( count($similar_doctors) > 3 ) { ?>
                <div class="swiper-pagination"></div>
                
                <div class="navArrow specSliderPrev">
                    <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 7H20M2 7L8 1M2 7L8 13" stroke="black" stroke-width="1.5"></path>
                    </svg>
                </div>
                <div class="navArrow specSliderNext" tabindex="0">
                    <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path id="Vector" d="M18 7H0M18 7L12 1M18 7L12 13" stroke="black" stroke-width="1.5"></path>
                    </svg>
                </div>
                <?php } ?>
            </div>
            
            <div class="butCenter">
                <a href="<?= get_field('theme_doctors_page', 'option'); ?>" class="but butPrimary">Все специалисты</a>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Вывод блоков из template-parts/blocks
 */
function clinic_output_template_blocks() {
    // Получаем блоки из ACF
    $blocks = get_field('template_blocks');
    if (!empty($blocks)) {
        foreach ($blocks as $block) {
            $block_name = $block['acf_fc_layout'] ?? '';
            if (!empty($block_name)) {
                get_template_part('template-parts/blocks/' . $block_name, null, $block);
            }
        }
    }
}
add_action('template_blocks', 'clinic_output_template_blocks');

/**
 * Вывод блоков из template-parts/blocks
 */
function clinic_output_template_blocks_single() {
    // Получаем блоки из ACF
    $blocks = get_field('blocks');
    if ( !empty($blocks) ) { ?>
        <section class="contentSection">
            <div class="container small">
                <div class="specInnerRightContent">
                <?php foreach ($blocks as $block) {
                    $block_name = $block['acf_fc_layout'] ?? '';
                    if (!empty($block_name)) {
                        get_template_part('template-parts/blocks/' . $block_name, null, $block);
                    }
                } ?>
                </div>
            </div>
        </section>
        <?php 
    }
}
add_action('template_blocks_single', 'clinic_output_template_blocks_single');
