<?php 
/**
 * Шаблон для архивов и таксономий
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>


<section class="contentSection">
    <div class="container">
        <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
        <h1 class="titleBig"><?php the_archive_title(); ?></h1>
        <?php if (have_posts()) : ?>
            <div class="specArticlesWrap">
            <?php while (have_posts()) : the_post(); ?>
                <?php $h1 = get_field('header_h1'); ?>
                <div class="specArticlesItem">
                    <a href="<?= get_permalink(); ?>" class="specArticle">
                        <?php if( has_post_thumbnail(get_the_ID()) ) { ?>
                        <div class="specArticleImg"><img src="<?= get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'); ?>" alt=""></div>
                        <?php } ?>
                        <div class="specArticleDate"><?= clinic_format_date(get_the_date('d.m.Y')); ?></div>
                        <div class="specArticleTitle"><?= !empty($h1) ? strip_tags($h1) : get_the_title(); ?></div>
                    </a>
                </div>
            <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p>Записи не найдены.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
