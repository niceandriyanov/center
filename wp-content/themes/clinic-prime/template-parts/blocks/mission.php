<?php
/**
 * Шаблон для блока mission
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="aboutMissionSection">
    <div class="container">
        <div class="aboutMissionWrap">
            <div class="aboutMissionLeft">
                <div class="aboutMissionImg"><img src="<?= THEME_URI; ?>/assets/img/main/img2.jpg" alt=""></div>
                <div class="aboutMissionLabel">
                    <div class="aboutMissionLabelTitle">Денис Туряница</div>
                    <div class="aboutMissionLabelSubTitle">Главный врач клиники «Справиться Проще»</div>
                </div>
            </div>
            <div class="aboutMissionRight">
                <h2 class="sectionBigTitle"><?= $args['title']; ?></h2>
                <?php if( !empty($args['desc']) ) { ?>
                <div class="aboutMissionRightText">
                    <p><?= $args['desc']; ?></p>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>