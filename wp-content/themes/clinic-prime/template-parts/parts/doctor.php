<?php
/**
 * Шаблон для карточки врача в списке специалистов
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

if( !empty($args['id']) ) {
    $doctor_id = $args['id'];
}
else {
    $doctor_id = get_the_ID();
}

$image = get_field('img', $doctor_id);
$specialization = get_field('spec_filter', $doctor_id);
?>

<div class="specItem">
    <?php if( $image ) { ?>
    <a class="specItemImg" href="<?= get_permalink($doctor_id); ?>">
        <img src="<?= $image['url']; ?>" alt="<?= $image['alt']; ?>">
        <?php the_doctor_experience($doctor_id); ?>
        </a>
    <?php } ?>
    </a>
    <div class="specItemInfoWrap">
        <div class="specItemOnlineLabel">
            <img src="<?= THEME_URI; ?>/assets/img/ico/online.svg">
            <span>Можно онлайн</span>
        </div>
        <div class="specItemInfo">
            <a class="specItemTitle" href="<?= get_permalink($doctor_id); ?>"><?= get_the_title($doctor_id); ?></a>
            <a class="specItemText" href="<?= get_permalink($doctor_id); ?>"><?= get_the_excerpt($doctor_id); ?></a>
            <?php if( !is_wp_error($specialization) && !empty($specialization) ) { ?>
            <div class="specItemFeatures">
                <?php foreach( $specialization as $spec ) { ?>
                    <?php $color = get_field('color', $spec->taxonomy.'_'.$spec->term_id); ?>
                <div class="specItemFeature<?= !empty($color) ? ' color'.$color : ''; ?>"><?= $spec->name; ?></div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php $cost = get_field('cost_1', $doctor_id); ?>
            <?php if( !empty($cost) ) { ?>
            <div class="specItemPrice"><?= $cost; ?> ₽</div>
            <?php } ?>
        </div>
    </div>
    
</div>
