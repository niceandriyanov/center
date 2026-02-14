<?php
/**
 * Template name: Специалисты
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 * @param array $args Дополнительные аргументы для шаблона
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<?php while (have_posts()) : the_post(); ?> 
    <section class="innerSection">
        <div class="container">
            <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
            <?php $header_h1 = !empty(get_field('header_h1')) ? get_field('header_h1') : get_the_title(); ?>
            <?php $header_lead = get_field('header_lead'); ?>
            <div class="innerPageMiddle">
                <h1 class="titleBig"><?= $header_h1; ?></h1>
                <?php if( !empty($header_lead) ) { ?>
                <div class="innerPageSubText">
                    <?= $header_lead; ?>
                </div>
                <?php } ?>
            </div>

            <div class="specItemsWrap">
                <form action="#" class="specItemsFilters">
                    <div class="specItemsFilterSearch">
                        <div class="specSearch">
                            <input type="search" name="search" class="field fieldSearch" placeholder="Поиск по ФИО" value="<?= esc_attr(isset($_GET['search']) ? sanitize_text_field($_GET['search']) : ''); ?>">
                            <button type="submit" class="but searchSubmit" aria-label="Поиск"><img src="<?= esc_url(THEME_URI); ?>/assets/img/ico/search.svg" alt=""></button>
                        </div>
                    </div>
                    <?php
                    $diseases = get_terms(array(
                        'taxonomy' => 'doctor_diseases',
                        'hide_empty' => false,
                        'meta_key' => 'taxonomy_order',
                        'orderby' => 'meta_value_num',
                        'order' => 'ASC',
                    ));
                    $specialties = get_terms(array(
                        'taxonomy' => 'doctor_specialty',
                        'hide_empty' => false,
                        'meta_key' => 'taxonomy_order',
                        'orderby' => 'meta_value_num',
                        'order' => 'ASC',
                    ));
                    ?>
                    <div class="specItemsFilterWrap">
                        <div class="specFilterForm">
                            <?php if( !empty($diseases) ) { ?>  
                            <div class="page_filters__filter">
                                <div class="page_filters__filter--main">
                                    <div class="page_filters__filter--main--btn">
                                        <?php 
                                        $text = 'Что вас беспокоит?';
                                        if( !empty($_GET['problems']) ) { 
                                            $term = get_term($_GET['problems'], 'doctor_diseases');
                                            $text = $term->name;
                                        }
                                        ?>
                                        <span><?= $text; ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 15.5002C11.744 15.5002 11.488 15.4023 11.293 15.2073L7.29301 11.2072C6.90201 10.8162 6.90201 10.1842 7.29301 9.79325C7.68401 9.40225 8.31601 9.40225 8.70701 9.79325L12.012 13.0982L15.305 9.91825C15.704 9.53525 16.335 9.54625 16.719 9.94325C17.103 10.3403 17.092 10.9742 16.695 11.3572L12.695 15.2193C12.5 15.4073 12.25 15.5002 12 15.5002Z" fill="#333333"/>
                                        </svg>
                                    </div>
                                    <div class="page_filters__filter--main--items">
                                        <div class="page_filters__filter--main--items--block">
                                            <div class="page_filters__filter--main--items--block--item">
                                                <input type="radio" name="problems" id="problem_0" value="0">
                                                <label for="problem_0">Все проблемы</label>
                                            </div>
                                            <?php foreach( $diseases as $disease ) { ?>
                                            <div class="page_filters__filter--main--items--block--item">
                                                <input type="radio" name="problems" value="<?= $disease->term_id; ?>" id="problem_<?= $disease->term_id; ?>">
                                                <label for="problem_<?= $disease->term_id; ?>"><?= $disease->name; ?></label>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if( !empty($specialties) ) { ?>  
                            <div class="page_filters__filter">
                                <div class="page_filters__filter--main">
                                    <div class="page_filters__filter--main--btn">
                                        <?php 
                                        $text = 'Специальность';
                                        if( !empty($_GET['specs']) ) { 
                                            $term = get_term($_GET['specs'], 'doctor_specialty');
                                            $text = $term->name;
                                        }
                                        ?>
                                        <span><?= $text; ?></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 15.5002C11.744 15.5002 11.488 15.4023 11.293 15.2073L7.29301 11.2072C6.90201 10.8162 6.90201 10.1842 7.29301 9.79325C7.68401 9.40225 8.31601 9.40225 8.70701 9.79325L12.012 13.0982L15.305 9.91825C15.704 9.53525 16.335 9.54625 16.719 9.94325C17.103 10.3403 17.092 10.9742 16.695 11.3572L12.695 15.2193C12.5 15.4073 12.25 15.5002 12 15.5002Z" fill="#333333"/>
                                        </svg>
                                    </div>
                                    <div class="page_filters__filter--main--items">
                                        <div class="page_filters__filter--main--items--block">
                                            <div class="page_filters__filter--main--items--block--item">
                                                <input type="radio" name="specs" id="spec_0" value="0">
                                                <label for="problem_0">Все специальности</label>
                                            </div>
                                            <?php foreach( $specialties as $specialty ) { ?>
                                            <div class="page_filters__filter--main--items--block--item">
                                                <input type="radio" name="specs" value="<?= $specialty->term_id; ?>" id="spec_<?= $specialty->term_id; ?>">
                                                <label for="spec_<?= $specialty->term_id; ?>"><?= $specialty->name; ?></label>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="filterReset">
                        <a href="#" class="filterResetLink">
                            <span class="withLine">Сбросить фильтр</span>
                            <img src="<?= THEME_URI; ?>/assets/img/ico/reset.svg" alt="">
                        </a>
                    </div>
                </form>
                <?php

                // Получаем параметры из URL
                $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
                $problems = isset($_GET['problems']) ? sanitize_text_field($_GET['problems']) : '';
                $specs = isset($_GET['specs']) ? sanitize_text_field($_GET['specs']) : '';

                $args = array(
                    'post_type' => 'doctors',
                    'posts_per_page' => -1,
                    'orderby' => array(
                        'menu_order' => 'ASC',
                        'date' => 'ASC'
                    ),
                    'post_status' => 'publish',
                );
                
                $meta_query = array();
                $tax_query = array();
                
                // Поиск по имени врача
                if (!empty($search)) {
                    $args['s'] = $search;
                }
                
                // Фильтр по проблемам (заболеваниям)
                if (!empty($problems)) {
                    $tax_query[] = array(
                        'taxonomy' => 'doctor_diseases',
                        'field'    => 'term_id',
                        'terms'    => $problems,
                    );
                }
                
                // Фильтр по специальностям
                if (!empty($specs)) {
                    $tax_query[] = array(
                        'taxonomy' => 'doctor_specialty',
                        'field'    => 'term_id',
                        'terms'    => $specs,
                    );
                }
                
                // Добавляем таксономические запросы
                if (!empty($tax_query)) {
                    $args['tax_query'] = $tax_query;
                }
                
                $doctors = new WP_Query($args);
                if($doctors->have_posts()) : ?>

                <div class="specItems_wrap">
                    <?php while($doctors->have_posts()) : $doctors->the_post(); 
                    get_template_part('template-parts/parts/doctor', null, array('id' => get_the_ID()));
                    endwhile; ?>
                </div>

                <div class="innerPageMiddle bottomTextResults">
                    <div class="innerPageSubText">
                        <p>Наши врачи-неврологи и психиатры используют современные методы диагностики и лечения, включая медикаментозную терапию и неврологическую реабилитацию.</p>
                    </div>
                </div>

                <?php else : ?>

                <div class="specItems_wrap">
                    <div class="no-results">
                        <p>По вашему запросу ничего не найдено</p>
                    </div>
                </div>

                <div class="innerPageMiddle bottomTextResults" style="display: none;">
                    <div class="innerPageSubText">
                        <p>Наши врачи-неврологи и психиатры используют современные методы диагностики и лечения, включая медикаментозную терапию и неврологическую реабилитацию.</p>
                    </div>
                </div>

                <?php endif; ?>
            </div>
        </div>

    </section>

    <section class="secondFeatures">
        <div class="container">
            <h2 class="sectionTitle">Каждый из наших специалистов</h2>
            <div class="secondFeaturesWrap">
                <div class="secondFeatureItem">
                    <div class="secondFeatureItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/ico5.svg" alt=""></div>
                    <div class="secondFeatureItemTitle">Опирается на современные клинические рекомендации</div>
                    <div class="secondFeatureItemText">Чтобы не навредить и не затягивать лечение</div>
                </div>

                <div class="secondFeatureItem">
                    <div class="secondFeatureItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/ico6.svg" alt=""></div>
                    <div class="secondFeatureItemTitle">Сдал этический экзамен</div>
                    <div class="secondFeatureItemText">Так мы уверены, что к пациентам будут относится бережно и внимательно</div>
                </div>

                <div class="secondFeatureItem">
                    <div class="secondFeatureItemIco"><img src="<?= THEME_URI; ?>/assets/img/main/ico7.svg" alt=""></div>
                    <div class="secondFeatureItemTitle">Проходит еженедельные интервизии</div>
                    <div class="secondFeatureItemText">И разбирает на них сложные случаи, чтобы получить взгляд со стороны от коллег</div>
                </div>
            </div>
        </div>
    </section>
<?php endwhile; ?>

<?php get_footer(); ?>