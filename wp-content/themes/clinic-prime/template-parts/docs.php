<?php
/**
 * Template Name: Документы
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
        <?php $docs = get_field('docs'); ?>
        <?php if( !empty($docs) ) { ?>

        <div class="docsWrap">
            <?php foreach( $docs as $doc ) { ?>

            <a class="docsItem" href="<?= $doc['type'] == 'url' ? $doc['url'] : $doc['pdf']; ?>" target="_blank">
                <div class="docsTitleWrap">
                    <div class="docsTitleIco"><img src="<?= THEME_URI; ?>/assets/img/ico/<?= $doc['type']; ?>.svg" alt=""></div>
                    <div class="docsTitle"><?= $doc['name']; ?></div>
                </div>
                <div class="docsFooterIco">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.168 10.5019L11.168 0.501878L1.16797 0.501878L1.16797 2.50188L7.75375 2.50188L0.460862 9.79477L1.87508 11.209L9.16797 3.91609L9.16797 10.5019L11.168 10.5019Z"/>
                    </svg>
                </div>
            </a>
            <?php } ?>

        </div>

        <?php } ?>
    </div>

</section>

<?php endwhile; ?>

<?php get_footer(); ?>
