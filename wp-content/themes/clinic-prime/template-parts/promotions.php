<?php
/**
 * Template Name: Акции
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
    <section class="innerSection">
        <div class="container">
            <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
            <h1 class="titleMedium"><?php the_title(); ?></h1>
            <div class="promoItemsWrap">
            <?php $promotions = get_field('promotions'); ?>
            <?php if( $promotions ) : ?>
                <?php foreach( $promotions as $promotion ) : ?>
                    <div class="promoItemInner">
                        <?php if( !empty($promotion['img']) ) { ?>
                        <a href="<?= $promotion['link']; ?>" class="promoItemImg">
                            <img src="<?= $promotion['img']['url']; ?>" alt="<?= $promotion['img']['alt']; ?>">
                            <div class="promoItemLabel">
                                <img src="<?= THEME_URI; ?>/assets/img/promo/time.svg">
                                <span><?= $promotion['date']; ?></span>
                            </div>
                        </a>
                        <?php } ?>
                        <a href="<?= $promotion['link']; ?>" class="promoItemBody">
                            <div class="promoItemTitle">
                                <?= $promotion['name']; ?>
                            </div>
                            <div class="promoItemIco">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.168 10.5019L11.168 0.501878L1.16797 0.501878L1.16797 2.50188L7.75375 2.50188L0.460862 9.79477L1.87508 11.209L9.16797 3.91609L9.16797 10.5019L11.168 10.5019Z"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
    </section>
<?php endwhile; ?>
<?php get_footer(); ?>