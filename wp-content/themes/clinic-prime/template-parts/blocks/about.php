<?php
/**
 * Шаблон для блока about
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="aboutSection">
    <div class="container">
        <div class="aboutWrap">
            <div class="aboutLeftImg">
                <img src="<?= THEME_URI; ?>/assets/img/main/img1.jpg" alt="Основательницы">
            </div>
            <div class="aboutRightItems">
                <div class="abotInfoWrap">
                    <?php if( !empty($args['label']) ): ?>
                    <div class="aboutInfoLabel"><?= $args['label']; ?></div>
                    <?php endif; ?>
                    <?php if( !empty($args['title']) ): ?>
                    <h2 class="aboutInfoTitle">
                        <?= $args['title']; ?>
                    </h2>
                    <?php endif; ?>
                    <?php if( !empty($args['text']) ): ?>
                    <div class="aboutInfoText">
                        <?= $args['text']; ?>
                    </div>
                    <?php endif; ?>
                    <?php if( !empty($args['page']) ): ?>
                    <div class="aboutInfoBut">
                        <a href="<?= $args['page']; ?>" class="but butPrimary">О клинике</a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="abotInfoFooter">
                    <div class="abotInfoNumbers">
                        <div class="abotInfoNumberItem">
                            <div class="abotInfoNumber">100+</div>
                            <div class="abotInfoNumberText">выпусков о ментальном здоровье</div>
                        </div>
                        <div class="abotInfoNumberItem">
                            <div class="abotInfoNumber">>300 тыс</div>
                            <div class="abotInfoNumberText">подписчиков на YouTube</div>
                        </div>
                        <div class="abotInfoNumberItem">
                            <div class="abotInfoNumber">>1 млн</div>
                            <div class="abotInfoNumberText">прослушиваний подкаста</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>