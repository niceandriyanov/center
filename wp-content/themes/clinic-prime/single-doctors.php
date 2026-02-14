<?php get_header(); ?>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.css"
/>

<?php while (have_posts()) : the_post(); ?>
    <?php $doctor_id = get_the_ID(); ?>
    <?php
    $specialization = get_field('spec_filter', $doctor_id);
    $specialization_out = '';
    if( !is_wp_error($specialization) && !empty($specialization) ) {
        ob_start();
        foreach( $specialization as $spec ) { ?>
            <?php $color = get_field('color', $spec->taxonomy.'_'.$spec->term_id); ?>
            <div class="specItemFeature<?= !empty($color) ? ' color'.$color : ''; ?>"><?= $spec->name; ?></div>
        <?php }
        $specialization_out = ob_get_clean();
    }
    $btn = get_field('btn', $doctor_id);
    ?>
    <section class="innerSection">
        <div class="container">
            <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
            <?php if( !empty($btn) ) { ?>
            <div class="butFixed">
                <a href="#" data-doctor="<?= get_field('doctor_id', $doctor_id); ?>" class="but butPrimary butModalRnova"><?= $btn; ?></a>
            </div>
            <?php } ?>
            <div class="specInnerWrap">
                <div class="specInnerLeft">
                    <div class="specInnerItemWrap">
                        <div class="specInnerLeftMobile">
                            <div class="titleMedium"><?php the_title(); ?></div>
                            <?php if( !empty($specialization_out) ) { ?>
                            <div class="specItemFeatures">
                                <?= $specialization_out; ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="specInnerItem">
                            <?php $image = get_field('img', $doctor_id); ?>
                            <?php if( !empty($image) ) { ?>
                            <div class="specItemImg">
                                <img src="<?= $image['url']; ?>" alt="<?= $image['alt']; ?>">
                                <?php the_doctor_experience($doctor_id); ?>
                            </div>
                            <?php } ?>
                            <div class="specItemInfoWrap">
                                <div class="specItemOnlineModal">
                                    Возможен онлайн-приём у этого врача. Он отличается от визита в клинику и чаще подходит для получения второго мнения. Подробнее об ограничениях — <a href="https://clinic.handlingbetter.ru/online/" target="_blank">в&nbsp;этой статье.</a>
                                </div>
                                <div class="specItemOnlineLabel">
                                    <img src="<?= THEME_URI; ?>/assets/img/ico/online.svg">
                                    <span>Можно онлайн</span>
                                </div>
                                <div class="specItemInfo">
                                    <div class="specItemTitle"><?php the_title(); ?></div>
                                    <div class="specItemText"><?php the_excerpt(); ?></div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="specInnerItemButs">
                            <?php if( !empty($btn) ) { ?>
                            <a href="#" data-doctor="<?= get_field('doctor_id', $doctor_id); ?>" class="but butPrimary butModalRnova hidden_m"><?= $btn; ?></a>
                            <?php } ?>
                            <?php $telegram = get_field('tg', $doctor_id); ?>
                            <?php if( !empty($telegram) ) { ?>
                            <a href="<?= $telegram; ?>" target="_blank" class="but butTelegram">
                                <span class="butText">Ведет телеграм-канал</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M21.5 5.01663C21.4485 5.48367 21.3454 6.00259 21.2424 6.46963C20.3665 10.6211 19.4907 14.7725 18.6148 18.924C18.5633 19.1835 18.5117 19.391 18.4087 19.5986C18.2026 19.9618 17.8935 20.0656 17.4813 19.9619C17.2237 19.91 17.0176 19.8062 16.8115 19.6505C15.472 18.6645 14.1839 17.6786 12.8444 16.6926C12.6898 16.5888 12.5867 16.5888 12.4322 16.7445C11.7624 17.3672 11.1441 18.0418 10.4744 18.6645C10.2167 18.924 9.95913 19.0797 9.59848 19.0278C9.34087 19.0278 9.18631 18.924 9.13479 18.6645C8.72262 17.3672 8.31045 16.1218 7.89827 14.8244C7.74371 14.4093 7.58915 13.9422 7.48611 13.5271C7.43458 13.3714 7.38306 13.2676 7.17697 13.2157C5.88893 12.8006 4.60089 12.3855 3.31284 12.0222C3.15828 11.9703 2.95219 11.9184 2.79763 11.8146C2.43698 11.6071 2.38545 11.2957 2.7461 10.9843C2.95219 10.7768 3.2098 10.673 3.51893 10.5692C5.27067 9.89458 7.02241 9.21997 8.77415 8.54536C12.5868 7.09235 16.3478 5.58745 20.1604 4.13444C20.212 4.13444 20.212 4.13444 20.2635 4.08255C20.9333 3.82308 21.5 4.18634 21.5 5.01663ZM9.59848 17.8342C9.65 17.7823 9.65001 17.7304 9.65001 17.7304C9.75305 16.7964 9.80457 15.8623 9.90762 14.9282C9.90762 14.7206 10.0107 14.5131 10.1652 14.3574C12.6383 12.126 15.1113 9.89458 17.5843 7.66317C17.7389 7.50749 17.8935 7.40371 18.0481 7.24803C18.0996 7.19614 18.2026 7.14424 18.1511 7.04046C18.0996 6.93667 17.9965 6.93667 17.8935 6.93667C17.6874 6.93667 17.5328 7.04046 17.3783 7.14425C14.3385 9.06429 11.2987 10.9843 8.25893 12.9563C8.10437 13.0601 8.05283 13.1638 8.10436 13.3714C8.46501 14.5131 8.82567 15.6547 9.23784 16.7964C9.34088 17.1077 9.49544 17.471 9.59848 17.8342Z" />
                                </svg>
                            </a>
                            <?php } ?>
                        </div>
                    </div>

                </div>

                <div class="specInnerRight">
                    <h1 aria-hidden="true" class="titleMedium visually-hidden"><?php the_title(); ?></h1>
                    <div class="specInnerRightContent">
                        <?php if( !empty($specialization_out) ) { ?>
                        <div class="specItemFeatures hidden_m">
                            <?= $specialization_out; ?>
                        </div>
                        <?php } ?>

                        <?php $quote = get_field('quote', $doctor_id); ?>
                        <?php if( !empty($quote) ) { ?>
                        <div class="specQuote">
                            <?= $quote; ?>
                        </div>
                        <?php } ?>

                        <?php the_content(); ?>

                        <?php $interests = get_field('tags_favorite', $doctor_id); ?>
                        <?php if( !empty($interests) ) { ?>
                        <h2>Особый интерес</h2>
                        <div class="tagsWrap">
                            <?php foreach( $interests as $interest ) { ?>
                                <div class="tagsItem">
                                    <a href="<?= $interest['link']; ?>"><?= $interest['name']; ?></a>
                                </div>
                            <?php } ?>
                        </div>
                        <?php } ?>

                        <?php $video = get_field('video', $doctor_id); ?>
                        <?php if( !empty($video) ) { ?>
                            <h2>Ролик со специалистом</h2>
                            <?= $video; ?>
                        <?php } ?>

                        <div class="specInnerSliderWrap">
                            <div class="specInnerSliderTopWrap">
                                <h2>Тут я веду прием</h2>
                                <div class="specInnerSliderNavs hiddensm">
                                    <div class="navArrow specInnerSlidePrev2">
                                        <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2 7H20M2 7L8 1M2 7L8 13" stroke="black" stroke-width="1.5"></path>
                                        </svg>
                                    </div>
                                    <div class="navArrow specInnerSlideNext2">
                                        <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Vector" d="M18 7H0M18 7L12 1M18 7L12 13" stroke="black" stroke-width="1.5"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-container photoSlider">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <a href="<?= THEME_URI; ?>/assets/img/photoSlider/1_big.jpg" class="photoImg" data-fancybox="gallery">
                                            <img src="<?= THEME_URI; ?>/assets/img/photoSlider/1.jpg">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a href="<?= THEME_URI; ?>/assets/img/photoSlider/2_big.png" class="photoImg" data-fancybox="gallery">
                                            <img src="<?= THEME_URI; ?>/assets/img/photoSlider/2.jpg">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a href="<?= THEME_URI; ?>/assets/img/photoSlider/3_big.jpg" class="photoImg" data-fancybox="gallery">
                                            <img src="<?= THEME_URI; ?>/assets/img/photoSlider/3.jpg">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a href="<?= THEME_URI; ?>/assets/img/photoSlider/4_big.jpg" class="photoImg" data-fancybox="gallery">
                                            <img src="<?= THEME_URI; ?>/assets/img/photoSlider/4.jpg">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a href="<?= THEME_URI; ?>/assets/img/photoSlider/1_big.jpg" class="photoImg" data-fancybox="gallery">
                                            <img src="<?= THEME_URI; ?>/assets/img/photoSlider/1.jpg">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a href="<?= THEME_URI; ?>/assets/img/photoSlider/2_big.png" class="photoImg" data-fancybox="gallery">
                                            <img src="<?= THEME_URI; ?>/assets/img/photoSlider/2.jpg">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a href="<?= THEME_URI; ?>/assets/img/photoSlider/3_big.jpg" class="photoImg" data-fancybox="gallery">
                                            <img src="<?= THEME_URI; ?>/assets/img/photoSlider/3.jpg">
                                        </a>
                                    </div>
                                    <div class="swiper-slide">
                                        <a href="<?= THEME_URI; ?>/assets/img/photoSlider/4_big.jpg" class="photoImg" data-fancybox="gallery">
                                            <img src="<?= THEME_URI; ?>/assets/img/photoSlider/4.jpg">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-pagination3"></div>
                        </div>
                        
                        <?php $articles = get_field('articles', $doctor_id); ?>
                        <?php if( !empty($articles) ) { ?>
                        <div class="specInnerSliderWrap">
                            <div class="specInnerSliderTopWrap">
                                <h2>Статьи, подкасты и видео</h2>
                                <?php if( count($articles) > 3 ) { ?>
                                <div class="specInnerSliderNavs hiddensm">
                                    <div class="navArrow specInnerSlidePrev">
                                        <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2 7H20M2 7L8 1M2 7L8 13" stroke="black" stroke-width="1.5"></path>
                                        </svg>
                                    </div>
                                    <div class="navArrow specInnerSlideNext">
                                        <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Vector" d="M18 7H0M18 7L12 1M18 7L12 13" stroke="black" stroke-width="1.5"></path>
                                        </svg>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="swiper-container specSliderInner">
                                <div class="swiper-wrapper">
                                    <?php foreach( $articles as $article ) { ?>
                                    <div class="swiper-slide">
                                        <a href="<?= $article['link']; ?>" class="specArticle" target="_blank">
                                            <?php if( !empty($article['img']) ) { ?>
                                            <div class="specArticleImg"><img src="<?= $article['img']['url']; ?>" alt="<?= $article['img']['alt']; ?>"></div>
                                            <?php } ?>
                                            <div class="specArticleDate"><?= clinic_format_date($article['date']); ?></div>
                                            <div class="specArticleTitle"><?= $article['name']; ?></div>
                                        </a>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="swiper-pagination2"></div>
                        </div>
                        <?php } ?>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <?php 
    // Выводим похожих врачей на основе таксономий doctor_specialty и doctor_diseases
    similar_doctors($doctor_id, 6, 'Похожие специалисты');
    ?>

<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.umd.js"></script>
<script type="text/javascript">
    Fancybox.bind("[data-fancybox]", {
            // Your custom options
          });
</script>
<?php get_footer(); ?>