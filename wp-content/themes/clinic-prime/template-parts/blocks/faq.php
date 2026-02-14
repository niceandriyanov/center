<?php
/**
 * Шаблон для блока FAQ
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

$faqs = get_field('faqs', 'faq');
if( empty($faqs) ) return;
?>

<section class="faqSection">
    <div class="container">
        <div class="faqItemsWrap">
            <div class="faqItemsLeft">
                <div class="faqItemsLeftSticky">
                     <h2 class="sectionTitle">FAQ</h2>
                    <div class="faqItemsLeftText">Отвечаем на самые часто задаваемые вопросы</div>
                </div>               
            </div>

            <div class="faqItemsRight">
                <div class="faqItems">
                    <?php foreach( $faqs as $faq ) { ?>
                    <div class="faqItem">
                        <div class="faqItemTitle"><span><?= $faq['q']; ?></span></div>
                        <div class="faqItemSpoiler">
                            <div class="faqItemSpoilerContent">
                            <?= $faq['a']; ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>