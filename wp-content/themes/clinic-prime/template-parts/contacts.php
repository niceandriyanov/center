<?php
/**
 * Template Name: Контакты
 */

if (!defined('ABSPATH')) {
    exit;
}
get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
<section class="innerSection">
    <div class="container small">
        <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
        <h1 class="titleBig"><span>Контакты</span></h1>

        <?php $header = get_field('theme_header', 'option'); ?>
        <div class="contactsGroupWrap">
            <div class="contactsGroupLeft">
                <?php if( !empty($header['phone']) ) { ?>
                <div class="contactsGroupEl">
                    <?php $clean_phone = preg_replace('/[^0-9+]/', '', $header['phone']); ?>
                    <div class="contactsGroupTitle">Телефон:</div>
                    <div class="contactsGroup">
                        <div class="contactsGroupIco"><img src="<?= THEME_URI; ?>/assets/img/ico/tel2.svg" alt=""></div>
                        <a class="contactsGroupTel" href="tel:<?= $clean_phone; ?>"><?= $header['phone']; ?></a>
                    </div>
                </div>
                <?php } ?>
                <?php $email = get_field('email');
                if( !empty($email) ) { ?>
                <div class="contactsGroupEl">
                    <div class="contactsGroupTitle">E-mail:</div>
                    <div class="contactsGroup">
                        <div class="contactsGroupIco"><img src="<?= THEME_URI; ?>/assets/img/ico/mail.svg" alt=""></div>
                        <a class="contactsGroupMail" href="mailto:<?= $email; ?>"><?= $email; ?></a>
                    </div>
                </div>
                <?php } ?>
                <?php $telegram = get_field('telegram'); ?>
                <?php if( !empty($telegram) ) { ?>
                <div class="contactsGroupEl">
                    <div class="contactsGroupTitle">Telegram:</div>
                    <div class="contactsGroup">
                        <div class="contactsGroupIco"><img src="<?= THEME_URI; ?>/assets/img/ico/tg_g.svg" alt=""></div>
                        <a class="contactsGroupMail" href="<?= $telegram['account']; ?>" target="_blank"><?= $telegram['account']; ?></a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php $requisites = get_field('details'); ?>
        <div class="contactsGroupWrap bottom">
            <div class="contactsGroupLeft">
                <div class="contactsGroupEl">
                    <div class="contactsGroupTitle">Реквизиты:</div>
                    <div class="contactsGroup">
                        <div class="contactsGroupText"><?= $requisites['name']; ?></div>
                    </div>
                </div>
                <div class="contactsGroupEl">
                    <div class="contactsGroup">
                        <div class="contactsGroupText"><?= $requisites['ogrn']; ?></div>
                    </div>
                </div>
                <div class="contactsGroupEl">
                    <div class="contactsGroup">
                        <div class="contactsGroupText"><?= $requisites['inn']; ?></div>
                    </div>
                </div>
                <div class="contactsGroupEl">
                    <div class="contactsGroup">
                        <div class="contactsGroupText"><?= $requisites['kpp']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $faq = get_field('faq'); ?>
<?php if( !empty($faq) ) { ?>
<section class="mapSection">
    <div class="container small">
        
        
        <div class="faqItems">
            <?php foreach( $faq as $item ) { ?>
            <div class="faqItem">
                <div class="faqItemTitle"><span><?= $item['q']; ?></span></div>
                <div class="faqItemSpoiler">
                    <div class="faqItemSpoilerContent">
                        <?= $item['a']; ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
    </div>
</section>
<?php } ?>

<?php endwhile; ?>

<?php get_footer(); ?>