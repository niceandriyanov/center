<?php get_header(); ?>

<?php get_template_part('template-parts/parts/breadcrumbs'); ?>

<div class="container">
    <header class="page-header">
        <h1 class="page-title">Результаты поиска: <?php echo get_search_query(); ?></h1>
    </header>

    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h2 class="entry-title">', '</h2>'); ?>
                </header>
                <div class="entry-summary">
                    <?php the_excerpt(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p>По вашему запросу ничего не найдено.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
