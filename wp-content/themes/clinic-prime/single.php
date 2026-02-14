<?php 
/**
 * Шаблон для отдельного поста
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

    <?php while (have_posts()) : the_post(); ?>

        <section class="innerSection">
            <div class="container small">
                <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
                <?php $header_h1 = get_field('header_h1'); ?>
                <?php $header_lead = get_field('header_lead'); ?>

                <h1 class="titleBig"><?= $header_h1; ?></h1>
                <?php if( !empty($header_lead) ) { ?>
                <div class="leadText">
                    <?= $header_lead; ?>
                </div>
                <?php } ?>

            </div>

        </section>
        <?php do_action('template_blocks_single'); ?>
        
        <?php
        $specialty_id = get_field('specialty');
        if ($specialty_id) {
            $args = array(
                'post_type' => 'doctors',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => array(
                    'menu_order' => 'ASC',
                    'date' => 'ASC'
                ),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'doctor_specialty',
                        'field' => 'term_id',
                        'terms' => $specialty_id
                    )
                )
            );

            $doctors = new WP_Query($args);
            if ($doctors->have_posts()) : ?>
                <section class="sectionSpecContent">
                    <div class="container">
                        <h2 class="sectionTitle">Специалисты по направлению</h2>
                        <div class="specSliderWrap">
                            <div class="swiper-container specSlider">
                                <div class="swiper-wrapper">
                                    <?php while ($doctors->have_posts()) : $doctors->the_post(); ?>
                                        <div class="swiper-slide">
                                            <?php get_template_part('template-parts/parts/doctor', null, array('id' => get_the_ID())); ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <?php if ($doctors->post_count > 3) : ?>
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
                            <?php endif; ?>
                        </div>

                        <div class="butCenter">
                            <a href="<?= get_field('theme_doctors_page', 'option'); ?>" class="but butPrimary">Все специалисты</a>
                        </div>
                    </div>
                </section>
            <?php
            endif;
            wp_reset_postdata();
        }
        ?>
    <?php endwhile; ?>

<?php get_footer(); 