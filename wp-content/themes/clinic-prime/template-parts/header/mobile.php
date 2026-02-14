<?php
/**
 * Шаблон для верхнего меню
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="dropdown topMenuWrap">

    <div class="mobileItems">

        <div class="mobileItemsBody">
            <?php
            wp_nav_menu(array(
                'theme_location'    => 'primary',
                'menu_class'        => 'list',
                'container'         => 'nav',
                'container_class'   => 'topMenu',
                'walker'            => new Clinic_Walker_Mobile_Menu(),
                'fallback_cb'       => false,
                'depth'             => 1,
            ));
            ?>
        
            <?php if ($args['phone']): ?>
                <?php $clean_phone = preg_replace('/[^0-9+]/', '', $args['phone']); ?>
            <a href="tel:<?= $clean_phone; ?>" class="mobileTel"><?php echo $args['phone']; ?></a>
            <?php endif; ?>
            <?php $social = get_field('theme_social', 'option'); ?>
            <?php if ($social): ?>
            <div class="mobileItemsSoc">
                <?php foreach ($social as $k => $item): ?>
                    <a href="<?= get_field($item, 'option'); ?>" target="_blank" aria-label="<?= $item; ?>"><img src="<?= THEME_URI; ?>/assets/img/ico/<?= $item; ?>.svg" alt="<?= $item; ?>"></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($args['top_bar']): 
            wp_nav_menu(array(
                'theme_location'    => 'top',
                'menu_class'        => 'list',
                'container'         => 'div',
                'container_class'   => 'mobileMenuFooter',
                'walker'            => new Clinic_Walker_Mobile_Menu(),
                'fallback_cb'       => false,
                'depth'             => 1,
            ));
        endif; ?>
    </div>

</div>