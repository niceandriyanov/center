<?php
/**
 * Шаблон для блока специалистов
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

if( empty($args['doctors']) ) return;

?>

<section class="sectionSpec">
    <div class="container">
        <h2 class="sectionTitle"><?= $args['title']; ?></h2>
        <?php if( !empty($args['desc']) ) { ?>
        <div class="specTitleText"><?= $args['desc']; ?></div>
        <?php } ?>
        <div class="specSliderWrap">
            <div class="swiper-container specSlider">
                <div class="swiper-wrapper">

                <?php foreach( $args['doctors'] as $doctor ) { ?>
                    <div class="swiper-slide">
                        <?php get_template_part('template-parts/parts/doctor', '', array('id' => $doctor)); ?>  
                    </div>
                <?php } ?>

                </div>

            </div>

            <?php if( count($args['doctors']) > 3 ) { ?>
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
            <a href="<?= get_field('theme_doctors_page', 'option'); ?>" class="but butPrimary">Посмотреть всех специалистов</a>
        </div>
    </div>
</section>