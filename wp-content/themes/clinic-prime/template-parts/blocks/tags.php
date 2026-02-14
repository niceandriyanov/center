<?php
/**
 * Шаблон для блока болезней
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

if( !empty($args['shoose_tags']) && empty($args['tags']) ) return;
$tags = [];
if( empty($args['shoose_tags']) ) {
    $tags = get_terms(array(
        'taxonomy' => 'post_tag',
        'hide_empty' => false,
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_key' => 'taxonomy_order',
    ));
}
else {
    $tags = $args['tags'];
}

if( !is_wp_error($tags) && !empty($tags) ) { ?>

<section class="tagsSection">
    <div class="container">
        <h2 class="sectionTitle"><?= $args['title']; ?></h2>
        <div class="tagsWrap">
            <?php foreach( $tags as $tag ) { ?>
            <div class="tagsItem"><a href="<?= get_field('theme_doctors_page', 'option'); ?>?problems=<?= $tag->term_id; ?>"><?= $tag->name; ?></a></div>
            <?php } ?>
        </div>
    </div>
</section>

<?php }