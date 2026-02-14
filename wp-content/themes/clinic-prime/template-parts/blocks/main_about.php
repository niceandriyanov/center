<?php
/**
 * Шаблон для блока главного блока о клинике
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="innerSection">
    <div class="container">
        <?php get_template_part('template-parts/parts/breadcrumbs'); ?>

        <div class="innerPageMiddle">
            <h1 class="titleBig"><?= $args['title']; ?></h1>
            <?php if( !empty($args['desc']) ) { ?>
            <div class="innerPageSubText">
                <p><?= $args['desc']; ?></p>
            </div>
            <?php } ?>
        </div>
        <?php if( !empty($args['images']) ) { ?>
        <div class="specSliderWrap">
            <div class="swiper-container aboutSlider">
                <div class="swiper-wrapper">
                    <?php foreach( $args['images'] as $image ) { ?>
                    <div class="swiper-slide">
                        <img src="<?= $image['sizes']['about_slider']; ?>" alt="<?= $image['alt']; ?>">
                    </div>
                    <?php } ?>
                </div>

            </div>
            <?php if( count($args['images']) > 3 ) { ?>
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
        <?php } ?>
    </div>
</section>
