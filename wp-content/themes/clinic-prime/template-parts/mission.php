<?php
/**
 * Template Name: Миссия и ценности
 */

if (!defined('ABSPATH')) {
    exit;
}
get_header(); ?>

<section class="missionSection">
    <div class="container">
        <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
        
        <div class="missionLabel">Наша миссия</div>
        <h1 class="titleBig">
            Предоставлять поддержку ментального здоровья, основанную на&nbsp;доказательных подходах
        </h1>
    </div>
</section>

<section class="mainFeatures">
    <div class="container">
        <div class="missionItemsTop">
            <div class="missionLabel">Наши ценности</div>
            <h2 class="missionItemsTopTitle">На чём строится наша работа</h2>
        </div>
        <div class="missionItemsWrap">
            <div class="secondFeatureItem mediumWidth">
                <div class="secondFeatureItemIco">
                    <img src="<?= THEME_URI; ?>/assets/img/missionIco/1.svg" alt="">
                </div>
                <div class="secondFeatureItemTitle">
                    Опора на принципы доказательной психотерапии
                </div>
                <div class="secondFeatureItemText">
                    Используем методы с научно доказанной эффективностью, чтобы клиент мог получить необходимую помощь и приобрести те навыки, которые реально изменят его жизнь.
                </div>
            </div>

            <div class="secondFeatureItem">
                <div class="secondFeatureItemIco">
                    <img src="<?= THEME_URI; ?>/assets/img/missionIco/2.svg" alt="">
                </div>
                <div class="secondFeatureItemTitle">
                    Уважение к каждому
                </div>
                <div class="secondFeatureItemText">
                    Создаём безопасное пространство для каждого клиента, независимо от его пола, возраста, национальности, вероисповедания и социального положения.
                </div>
            </div>

            <div class="secondFeatureItem">
                <div class="secondFeatureItemIco">
                    <img src="<?= THEME_URI; ?>/assets/img/missionIco/3.svg" alt="">
                </div>
                <div class="secondFeatureItemTitle">
                    Постоянное развитие
                </div>
                <div class="secondFeatureItemText">
                    Специалисты непрерывно обучаются, повышают квалификацию и развивают свои навыки, чтобы соответствовать современным стандартам психологической помощи.
                </div>
            </div>

            <div class="secondFeatureItem">
                <div class="secondFeatureItemIco">
                    <img src="<?= THEME_URI; ?>/assets/img/missionIco/4.svg" alt="">
                </div>
                <div class="secondFeatureItemTitle">
                    Качество
                </div>
                <div class="secondFeatureItemText">
                    Проводим обязательные интервизии и супервизии для психологов, организуем регулярные внутренние обучения, а также контролируем качество работы специалистов.
                </div>
            </div>

            <div class="secondFeatureItem">
                <div class="secondFeatureItemIco">
                    <img src="<?= THEME_URI; ?>/assets/img/missionIco/5.svg" alt="">
                </div>
                <div class="secondFeatureItemTitle">
                    Командная работа
                </div>
                <div class="secondFeatureItemText">
                    Взаимодействуем с другими специалистами, у которых наблюдается клиент: психиатрами и неврологами. Это помогает учесть разные причины проблемы и выбрать эффективное решение.
                </div>
            </div>
        </div>
    </div>
</section>

<section class="sectionSpec">
    <div class="container">
        <div class="missionFeaturesWrap">
            <div class="missionFeaturesLeft">
                <div class="missionLabel">Подбор специалиста</div>
                <h2 class="missionItemsTopTitle">Подобрать психолога, который подойдёт именно вам</h2>
                <div class="missionFeaturesText">
                    Заполните короткую анкету — и мы предложим специалистов центра, чей опыт и подход лучше всего соответствуют вашему запросу.
                </div>
                <div class="missionFeaturesListItems">
                    <div class="missionFeaturesListItem">
                        <div class="missionFeaturesListItemIco">
                            <img src="<?= THEME_URI; ?>/assets/img/ico/ico_check.svg">
                        </div>
                        <div class="missionFeaturesListItemText">Работа только в доказательных подходах: КПТ, ACT, ДБТ, CFT и другие</div>
                    </div>
                    <div class="missionFeaturesListItem">
                        <div class="missionFeaturesListItemIco">
                            <img src="<?= THEME_URI; ?>/assets/img/ico/ico_check.svg">
                        </div>
                        <div class="missionFeaturesListItemText">Строгий отбор и супервизия каждого специалиста</div>
                    </div>
                    <div class="missionFeaturesListItem">
                        <div class="missionFeaturesListItemIco">
                            <img src="<?= THEME_URI; ?>/assets/img/ico/ico_check.svg">
                        </div>
                        <div class="missionFeaturesListItemText">Подбор по запросу, опыту и человеческой совместимости</div>
                    </div>
                    <div class="missionFeaturesListItem">
                        <div class="missionFeaturesListItemIco">
                            <img src="<?= THEME_URI; ?>/assets/img/ico/ico_check.svg">
                        </div>
                        <div class="missionFeaturesListItemText">Онлайн-сессии из любой точки мира</div>
                    </div>
                </div>
                <div class="missionFeaturesBut">
                    <a href="/appointment" class="but butPrimary">Подобрать психолога</a>
                </div>
            </div>
            <div class="missionFeaturesRight">
                <img src="<?= THEME_URI; ?>/assets/img/img9.jpg">
            </div>
        </div>
    </div>
</section>

<?php get_footer(); 