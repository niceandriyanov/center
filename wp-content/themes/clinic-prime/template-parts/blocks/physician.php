<?php
/**
 * Шаблон для блока главный врач
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<section class="mainGreenSection">
    <div class="container">
        <div class="mainGreenWrap">
            <div class="mainGreenLeft">
                <div class="aboutInfoLabel">Главный врач</div>
                <h2 class="aboutInfoTitle"><?= $args['title']; ?></h2>
                <?php if( !empty($args['desc']) ) { ?>
                <div class="aboutInfoText">
                    <p>
                        <?= $args['desc']; ?>
                    </p>
                </div>
                <?php } ?>
                <div class="aboutInfoBut">
                    <a href="#" class="but butPrimary butModal" data-modal="feedback">Обратная связь</a>
                </div>
            </div>
            <div class="mainGreenRight">
                <img src="<?= THEME_URI; ?>/assets/img/main/img2.jpg" alt="Главный врач">
            </div>
        </div>
    </div>
</section>
<div class="modal" id="modalForm">
    <div class="modalWrap">
        <div class="modalWrapHead">
            <div class="modalWrapTitle">Чем я могу помочь?</div>
            <div class="modalWrapClose">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M4.92896 19.0711L19.0711 4.92893M4.92896 4.92893L19.0711 19.0711" stroke-width="2" stroke-linecap="square"/>
                </svg>
            </div>
        </div>
        <div class="modalFormBody">
            <?php echo do_shortcode('[contact-form-7 id="d932d69"]'); ?>
        </div>
    </div>
</div>