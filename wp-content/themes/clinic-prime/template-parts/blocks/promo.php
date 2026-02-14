<?php
/**
 * Шаблон для блока promo
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<a href="<?= $args['link']; ?>" class="promoItem" target="_blank">
    <div class="promoItemLeft"><img src="<?= $args['img']['url']; ?>" alt="<?= $args['img']['alt']; ?>"></div>
    <div class="promoItemMiddle"><?= $args['text']; ?></div>
    <div class="promoItemRight">
        <div class="docsFooterIco">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.168 10.5019L11.168 0.501878L1.16797 0.501878L1.16797 2.50188L7.75375 2.50188L0.460862 9.79477L1.87508 11.209L9.16797 3.91609L9.16797 10.5019L11.168 10.5019Z"/>
            </svg>
        </div>
    </div>
</a>