<?php 
/**
 * Шаблон для страниц
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<section class="contentSection">
    <div class="container small">
    <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
        <?php while (have_posts()) : the_post(); ?>
            <h1 class="titleBig"><?php the_title(); ?></h1>
            <div class="specInnerRightContent">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<?php get_footer(); ?>
