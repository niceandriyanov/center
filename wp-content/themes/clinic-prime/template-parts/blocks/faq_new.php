<?php 
/**
 * Шаблон для блока faq_new
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}
if( empty($args['faq']) ) return;
?>
<section class="faqSection">
    <div class="container">
        <div class="faqItemsWrap">
            <div class="faqItemsLeft">
                <h2 class="sectionTitle">FAQ</h2>
                <div class="faqItemsLeftText">Отвечаем на самые часто задаваемые опросы</div>
            </div>

            <div class="faqItemsRight">
                
                <div class="faqItems">
                    <?php foreach( $args['faq'] as $faq ) { ?>
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