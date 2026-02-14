<?php
/**
 * Шаблон для блока spoillers
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

if( empty($args['items']) ) return;
?>

<div class="faqItems">
    <?php foreach( $args['items'] as $item ) { ?>
    <div class="faqItem">
        <div class="faqItemTitle"><span><?= $item['name']; ?></span></div>
        <div class="faqItemSpoiler">
            <div class="faqItemSpoilerContent">
                <?= $item['desc']; ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
