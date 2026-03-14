<?php $footer = get_field('theme_footer', 'option'); ?>
<footer class="footer">
    <div class="container">
        <div class="footerTop">
            <?php if( !empty($footer['logo']) ) { ?>
            <div class="footerLogo">
                <img src="<?= $footer['logo']['url']; ?>" alt="<?= $footer['logo']['alt']; ?>">
            </div>
            <?php } ?>
            <div class="footerMiddleLeft">
                <?php
                wp_nav_menu(array(
                    'theme_location'    => 'footer',
                    'menu_class'        => '',
                    'container'         => '',
                    'items_wrap'        => '%3$s',
                    'walker'            => new Clinic_Walker_Footer_Menu(),
                    'fallback_cb'       => false,
                    'depth'             => 1,
                ));
                ?>
            </div>
        </div>

        <div class="footerBottom">
            <div class="footerCopy">© <?= date('Y'); ?> Справиться проще</div>
            <?php $social = get_field('theme_social', 'option'); ?>
            <?php if( !empty($social) ) { ?>    
            <div class="footerMiddleRight">
                <div class="footerButtons">
                    <?php foreach( $social as $k => $item ) { ?>
                    <a href="<?= get_field($item, 'option'); ?>" target="_blank" class="footerButton">
                        <img src="<?= THEME_URI; ?>/assets/img/ico/<?= $item; ?>_w.svg" alt="<?= $item; ?>">
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            <a href="<?= get_field('theme_person_page', 'option'); ?>" class="footerLink" target="_blank">Юридическая информация</a>
        </div>
    </div>
</footer>
<div class="modal" id="modalThanks">
    <div class="modalWrap thanks">
        <div class="modalWrapClose">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M4.92896 19.0711L19.0711 4.92893M4.92896 4.92893L19.0711 19.0711" stroke-width="2" stroke-linecap="square"/>
            </svg>
        </div>
        <div class="modalWrapTitle">Спасибо!</div>
        <div class="modalWrapText">Cкоро мы с вами свяжемся</div>
    </div>
</div>
<!-- <div class="modal" id="modalImage">
    <div class="modalImageWrap">
        <div class="modalWrapClose">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M4.92896 19.0711L19.0711 4.92893M4.92896 4.92893L19.0711 19.0711" stroke-width="2" stroke-linecap="square"/>
            </svg>
        </div>
        <div class="modalImage">
        	<img src="<?= THEME_URI; ?>/assets/img/bans/ban2.png">
        </div>
    </div>
</div> -->
<?php wp_footer(); ?>
<div class="modal" id="rnovaForm">
    <div class="modalWrap">
        <div class="modalWrapClose">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M4.92896 19.0711L19.0711 4.92893M4.92896 4.92893L19.0711 19.0711" stroke-width="2" stroke-linecap="square"/>
            </svg>
        </div>
        <div class="modalWrapText" id="rnova-a_form_loader"></div>
        
    </div>
</div>

<!-- Отдельное модальное окно для butModalRnovaLk -->
<div class="modal" id="rnovaFormLk">
    <div class="modalWrap">
        <div class="modalWrapClose">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M4.92896 19.0711L19.0711 4.92893M4.92896 4.92893L19.0711 19.0711" stroke-width="2" stroke-linecap="square"/>
            </svg>
        </div>
        <div class="modalWrapText" id="rnova-a_form_loader_lk"></div>
        
    </div>
</div>

<!-- Фиксированные элементы -->

<div class="fixed-vidjet-open">
    <div class="fixed-vidjet-close">
        <img src="<?= THEME_URI; ?>/assets/img/onlineForm/close.svg" alt="">
    </div>
    <div class="fixed-vidjet-img">
        <img src="<?= THEME_URI; ?>/assets/img/onlineForm/img2.jpg" alt="">
    </div>
    <div class="fixed-vidjet-text">
        Если в процессе возникнут вопросы, вы можете обратиться к <a href="https://t.me/handlingbetter" target="_blank">менеджеру Центра</a>
    </div>
</div>
<div class="fixed-vidjet-mini active">
    <img src="<?= THEME_URI; ?>/assets/img/onlineForm/ico8.svg" alt="">
</div>

</body>
</html>
