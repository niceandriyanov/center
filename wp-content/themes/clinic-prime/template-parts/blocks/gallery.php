<?php
/**
 * Шаблон для блока gallery
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="greySection" style="display: none">
    <div class="container">
        <div class="innerPageMiddle">
            <h2 class="sectionBigTitle"><?= $args['title']; ?></h2>
            <?php if( !empty($args['desc']) ) { ?>
            <div class="innerPageSubText">
                <p><?= $args['desc']; ?></p>
            </div>
            <?php } ?>
        </div>
        <?php if( !empty($args['images']) ) { ?>
        <div class="aboutImagesWrap">
            <?php foreach( $args['images'] as $image ) { ?>
            <div class="aboutImageItem">
                <div class="aboutImageItemImg"><img src="<?= $image['sizes']['about_gallery']; ?>" alt="<?= $image['alt']; ?>"></div>
                <?php if( !empty($image['caption']) ) { ?>
                <figcaption><?= $image['caption']; ?></figcaption>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</section>