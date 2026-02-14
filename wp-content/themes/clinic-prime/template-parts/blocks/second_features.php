<?php
/**
 * Шаблон для блока features второго блока
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

if( empty($args['features']) ) return;

?>

<section class="secondFeatures">
    <div class="container">
        <h2 class="sectionTitle"><?= $args['title']; ?></h2>
        <div class="secondFeaturesWrap">
            <?php foreach($args['features'] as $feature): ?>

            <div class="secondFeatureItem">
            <div class="secondFeatureItemIco"><img src="<?= $feature['img']['url']; ?>" alt="<?= $feature['img']['alt']; ?>"></div>
                    <div class="secondFeatureItemTitle"><?= $feature['name']; ?></div>
                    <?php if( !empty($feature['desc']) ) { ?>   
                    <div class="secondFeatureItemText"><?= $feature['desc']; ?></div>
                    <?php } ?>
            </div>
            
            <?php endforeach; ?>
        </div>
    </div>
</section>