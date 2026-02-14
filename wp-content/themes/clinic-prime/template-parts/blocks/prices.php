<?php
/**
 * Шаблон для блока prices
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}
if( empty($args['list']) ) return;
?>

<div class="contentPriceItems">
    <?php foreach( $args['list'] as $item ) { ?>
    <div class="priceEl priceTopEl">
        <div class="priceElLeft">
            <div class="priceElInfo">
                <div class="priceElName"><?= $item['name']; ?></div>
            </div>
        </div>
        <div class="priceElRight">
            <div class="priceElGroup">
                <div class="priceElGroupTitle">Первичный</div>
                <div class="priceElGroupValue"><?= $item['cost_1']; ?> ₽</div>
            </div>
            <?php if( !empty($item['cost_2']) ) { ?>
            <div class="priceElGroup">
                <div class="priceElGroupTitle">Повторный</div>
                <div class="priceElGroupValue"><?= $item['cost_2']; ?> ₽</div>
            </div>
            <?php } ?>
            <div class="priceElBut">
                <a href="" data-profession="<?= $item['id']; ?>" class="but butPrimary butModalRnova">Записаться</a>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
