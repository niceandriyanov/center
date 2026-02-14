<?php
/**
 * Шаблон для блока reviews
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}   
if( empty($args['reviews']) ) return;
?>
<section class="reviewsSection">
    <div class="container">
        <div class="reviewSliderWrap">
            <div class="swiper-container reviewSlider">
                <div class="swiper-wrapper">
                    <?php foreach( $args['reviews'] as $review ) { ?>
                    <div class="swiper-slide">
                        <div class="reviewSlideItem">
                            <div class="reviewSlideImg">
                                <img src="<?= $review['ava']['url']; ?>" alt="<?= $review['ava']['alt']; ?>">
                            </div>
                            <div class="reviewSlideInfo">
                                <div class="reviewSlideInfoTitle"><?= $review['name']; ?></div>
                                <div class="reviewSlideInfoSpec"><?= $review['job']; ?></div>
                                <div class="reviewSlideInfoText">
                                    <?= $review['text']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                </div>
            </div>

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
        </div>

    </div>
</section>