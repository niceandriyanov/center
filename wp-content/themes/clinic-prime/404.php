<?php get_header(); ?>
<div class="container">
    <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
</div>
<section class="whiteSection">
    <div class="container">
        
        <div class="notFoundWrap">
            <div class="notFoundLeft">
                <div class="notFoundTitle">404</div>
                <div class="notFoundText">Страница не найдена</div>
                <div class="notFoundSmallText">К сожалению мы ничего не нашли по вашему запросу</div>
                <a href="<?php echo home_url('/'); ?>" class="but butPrimary">На главную</a>
            </div>
            <div class="notFoundRight">
                <img src="<?= THEME_URI; ?>/assets/img/dog.png" alt="">
            </div>

        </div>

    </div>
</section>

<?php get_footer(); ?>
