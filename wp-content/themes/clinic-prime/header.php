<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, user-scalable=0, minimum-scale=1, maximum-scale=1">
    <?php wp_head(); ?>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <script type="text/javascript">
    if (!window.dgSocialWidgetData) { window.dgSocialWidgetData = []; }
    window.dgSocialWidgetData.push({
        widgetId: '9be6d553-b2d7-429d-9953-f97b32838478',
        apiUrl: 'https://app.daily-grow.com/sw/api/v1',
    });
</script>
<script type="text/javascript" src="https://app.daily-grow.com/social-widget/init.js" defer></script>
</head>
<body <?php body_class(); ?>>
    <?php $theme_header = get_field('theme_header', 'option'); ?>
    <?php get_template_part('template-parts/header/top', '', $theme_header); ?>
    <header class="headerMiddle">
        <div class="container">
            <div class="row rowMiddle rowBetween">
                <div class="col colmGrow">
                    <?php clinic_header_logo($theme_header['logo']); ?>
                </div>
                <?php clinic_header_navigation(); ?>
                <?php if( $theme_header['btn_appointment'] ): ?>
                <div class="col hidden_m">
                    <?php $doctors = get_field('theme_doctors_page', 'option'); ?>
                    <div class="headerRight">
                        <a href="<?= $doctors; ?>" class="but butPrimary">Подобрать психолога</a>
                    </div>
                </div>

                <div class="col mobileButCol">
                    <a href="<?= $doctors; ?>" class="mobileBut" aria-label="Подобрать психолога"><img src="<?php echo THEME_URI; ?>/assets/img/ico/list.svg" alt=""></a>
                </div>
                <?php endif; ?>

                <button type="button" class="but dropdownBut" aria-label="Открыть меню">
                    <span class="icoMenu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>
        </div>
    </header>
    <?php get_template_part('template-parts/header/mobile', '', $theme_header); ?>