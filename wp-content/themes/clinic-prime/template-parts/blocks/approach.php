<?php 
/**
 * Шаблон для блока approach
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<section class="mainWorks">
    <div class="container">
        <h2 class="sectionTitle">Работаем в подходах с доказанной медицинской эффективностью</h2>
        <div class="mainWorksItems">
            <a href="<?= $args['link_1']; ?>" class="mainWorksItem">
                <div class="mainWorksItemTitle">Когнитивно-поведенческая терапия (КПТ)</div>
                <div class="mainWorksItemText">Подход, который называют золотым стандартом психотерапии</div>
                <div class="mainWorksItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/new/ico1.svg" alt=""></div>
            </a>
            <a href="<?= $args['link_2']; ?>" class="mainWorksItem">
                <div class="mainWorksItemTitle">Терапия принятия и ответственности (ACT)</div>
                <div class="mainWorksItemText">Научиться строить жизнь в соответствии со своими ценностями</div>
                <div class="mainWorksItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/new/ico1.svg" alt=""></div>
            </a>
            <a href="<?= $args['link_3']; ?>" class="mainWorksItem">
                <div class="mainWorksItemTitle">Диалектическая поведенческая терапия (ДБТ)</div>
                <div class="mainWorksItemText">Тем, кому мешают сильные эмоции, суицидальные мысли и самоповреждение</div>
                <div class="mainWorksItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/new/ico1.svg" alt=""></div>
            </a>
            <a href="<?= $args['link_4']; ?>" class="mainWorksItem">
                <div class="mainWorksItemTitle">Когнитивно-процессуальная терапия (CPT)</div>
                <div class="mainWorksItemText">Снизить влияние травмы на ваше настоящее</div>
                <div class="mainWorksItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/new/ico1.svg" alt=""></div>
            </a>
            <a href="<?= $args['link_5']; ?>" class="mainWorksItem">
                <div class="mainWorksItemTitle">Рационально-эмоционально-поведенческая терапия (РЭПТ) </div>
                <div class="mainWorksItemText">Работа с убеждениями, эмоциями и поведением</div>
                <div class="mainWorksItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/new/ico1.svg" alt=""></div>
            </a>
            <a href="<?= $args['link_6']; ?>" class="mainWorksItem">
                <div class="mainWorksItemTitle">Терапия, сфокусированная на сострадании (CFT)</div>
                <div class="mainWorksItemText">Тем, кто испытывает сильное чувство стыда и часто себя критикует</div>
                <div class="mainWorksItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/new/ico1.svg" alt=""></div>
            </a>
            <a href="<?= $args['link_7']; ?>" class="mainWorksItem">
                <div class="mainWorksItemTitle">Схема-терапия</div>
                <div class="mainWorksItemText">Перестать попадать в одни и те же ловушки и улучшить качество жизни</div>
                <div class="mainWorksItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/new/ico1.svg" alt=""></div>
            </a>
            <div class="mainWorksItem last" style="background-image: url('<?= THEME_URI; ?>/assets/img/main/new/img2.jpg')">
                <div class="mainWorksItemLastTitle">Если вы практикующий психолог с образованием в доказательном подходе, то можете сотрудничать с нами</div>
                <a href="<?= $args['link_8']; ?>" class="but butWork">Работать у нас</a>
            </div>
        </div>
    </div>
</section>