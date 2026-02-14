<?php
/**
 * Шаблон для блока history
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<section class="aboutHistorySection">
    <div class="container">
        <div class="innerPageMiddle">
            <h2 class="sectionBigTitle"><?= $args['title']; ?></h2>
            <?php if( !empty($args['desc']) ) { ?>
            <div class="innerPageSubText">
                <?= $args['desc']; ?>
            </div>
            <?php } ?>
            <?php if( !empty($args['video']) ) { ?>
            <div class="aboutHistoryFrame">
                <?= $args['video']; ?>
            </div>
            <?php } ?>
        </div>
    </div>
</section>