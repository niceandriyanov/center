<?php
/**
 * Шаблон для блока media
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

if( empty($args['media']) ) return;

?>

<section class="whiteSection">
    <div class="container">
        <h2 class="sectionTitle"><?= $args['title']; ?></h2>
        <div class="specArticlesWrap">
            <?php foreach( $args['media'] as $media ) { ?>

            <div class="specArticlesItem">
                <a href="<?= $media['link']; ?>" class="specArticle" target="_blank">
                    <?php if( !empty($media['img']) ) { ?>
                    <div class="specArticleImg"><img src="<?= $media['img']['sizes']['thumbnail']; ?>" alt="<?= $media['img']['alt']; ?>"></div>
                    <?php } ?>
                    <div class="specArticleDate"><?= clinic_format_date($media['date']); ?></div>
                    <div class="specArticleTitle"><?= $media['title']; ?></div>
                </a>
            </div>

            <?php } ?>
        </div>
    </div>
</section>