<?php
/**
 * Шаблон для блока features
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

<section class="mainFeatures">
    <div class="container">
        <div class="mainFeatureItems">
            <?php foreach($args['features'] as $feature): ?>

            <div class="mainFeatureItem">
                <div class="mainFeatureItemIco"><img src="<?= $feature['img']['url']; ?>" alt="<?= $feature['img']['alt']; ?>"></div>
                <div class="mainFeatureItemText"><?= $feature['name']; ?></div>
            </div>
            
            <?php endforeach; ?>
        </div>
    </div>
</section>