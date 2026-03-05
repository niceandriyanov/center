<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, user-scalable=0, minimum-scale=1, maximum-scale=1">
    <?php wp_head(); ?>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    
    <?php if( defined('PRODUCTION_ENV') && PRODUCTION_ENV ) { ?>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=107157853', 'ym');

        ym(107157853, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/107157853" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
    <?php } ?>
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
                    <?php $doctors = get_field('theme_online_form_page', 'option'); ?>
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