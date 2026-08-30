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

<?php while (have_posts()) : the_post();
$catalog = function_exists('clinic_get_doctors_catalog_context') ? clinic_get_doctors_catalog_context() : array();
$header_h1 = !empty($catalog['header_h1']) ? $catalog['header_h1'] : (!empty(get_field('header_h1')) ? get_field('header_h1') : get_the_title());
$header_lead = !empty($catalog['header_lead']) ? $catalog['header_lead'] : get_field('header_lead');
$bottom_text = !empty($catalog['bottom_text']) ? $catalog['bottom_text'] : '';
?>
    <section class="innerSection">
        <div class="container">
            <?php get_template_part('template-parts/parts/breadcrumbs'); ?>
            <div class="innerPageMiddle">
                <h1 class="titleBig"><?= wp_kses_post($header_h1); ?></h1>
                <?php if (!empty($header_lead)) { ?>
                <div class="innerPageSubText">
                    <?= $header_lead; ?>
                </div>
                <?php } ?>
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
            $grades = get_terms(array(
                'taxonomy' => 'doctor_grade',
                'hide_empty' => false,
                'meta_key' => 'taxonomy_order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
            ));

            $current_specialty = function_exists('clinic_get_current_doctors_specialty') ? clinic_get_current_doctors_specialty() : null;
            $catalog_base_url = function_exists('clinic_get_doctors_catalog_url') ? clinic_get_doctors_catalog_url() : get_permalink();

            $search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
            $problems = isset($_GET['problems']) ? sanitize_text_field(wp_unslash($_GET['problems'])) : '';
            $grade = isset($_GET['grade']) ? absint($_GET['grade']) : 0;
            $specs = isset($_GET['specs']) ? sanitize_text_field(wp_unslash($_GET['specs'])) : '';

            if (empty($specs) && $current_specialty) {
                $specs = (string) $current_specialty->term_id;
            }

            $specialty_id = function_exists('clinic_resolve_doctors_specialty_term_id')
                ? clinic_resolve_doctors_specialty_term_id($specs, '')
                : (int) $specs;

            $base_doctor_ids = function_exists('clinic_get_doctors_catalog_base_post_ids')
                ? clinic_get_doctors_catalog_base_post_ids($specialty_id)
                : array();

            if (function_exists('clinic_filter_catalog_terms_by_doctors')) {
                $diseases = clinic_filter_catalog_terms_by_doctors($diseases, 'doctor_diseases', $base_doctor_ids);
            }

            $filter_context = array(
                'specs'    => $specs,
                'problems' => $problems,
                'grade'    => $grade,
                'search'   => $search,
            );

            $filter_availability = function_exists('clinic_get_doctors_catalog_filter_availability')
                ? clinic_get_doctors_catalog_filter_availability($filter_context)
                : array('problems' => array(), 'grades' => array());

            if ($problems && !in_array((int) $problems, $filter_availability['problems'], true)) {
                $problems = '';
                $filter_context['problems'] = '';
                $filter_availability = clinic_get_doctors_catalog_filter_availability($filter_context);
            }

            if ($grade && !in_array($grade, $filter_availability['grades'], true)) {
                $grade = 0;
                $filter_context['grade'] = 0;
                $filter_availability = clinic_get_doctors_catalog_filter_availability($filter_context);
            }

            $available_problems_lookup = array_flip($filter_availability['problems']);
            $available_grades_lookup = array_flip($filter_availability['grades']);
            $has_assigned_grades = !empty($filter_availability['grades']);

            $problems_text = 'Что вас беспокоит?';
            if (!empty($problems)) {
                $problem_term = get_term((int) $problems, 'doctor_diseases');
                if ($problem_term && !is_wp_error($problem_term)) {
                    $problems_text = $problem_term->name;
                }
            }

            $active_grade_description = '';
            if ($grade && !empty($grades)) {
                foreach ($grades as $grade_term) {
                    if ((int) $grade_term->term_id === $grade) {
                        $active_grade_description = get_field('filter_description', 'doctor_grade_' . $grade_term->term_id);
                        break;
                    }
                }
            }

            $doctor_query_args = function_exists('clinic_build_doctors_query_args')
                ? clinic_build_doctors_query_args(array(
                    'search'   => $search,
                    'problems' => $problems,
                    'specs'    => $specs,
                    'grade'    => $grade,
                ))
                : array(
                    'post_type'      => 'doctors',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                );

            $doctors = new WP_Query($doctor_query_args);
            $doctors_count = (int) $doctors->found_posts;

            $default_bottom_text = '<p>Услуги психолога оказывают дипломированные специалисты с внушительным практическим опытом работы в сфере решения проблем ментального здоровья.</p><p>Прием психолога центра «Справиться Проще» поможет выявить и проанализировать имеющиеся проблемы, сформировать индивидуальный план их коррекции, а также получить рекомендации по улучшению эмоционального состояния и повышению качества жизни.</p>';
            if (empty($bottom_text)) {
                $bottom_text = $default_bottom_text;
            }
            ?>

            <div class="specItemsWrap">
                <div
                    class="specItemsFilters"
                    id="specItemsFilters"
                    data-specialty-id="<?= $current_specialty ? (int) $current_specialty->term_id : ''; ?>"
                    data-catalog-url="<?= esc_url($catalog_base_url); ?>"
                >
                    <div class="specItemsFiltersTop">
                        <a href="<?= esc_url($catalog_base_url); ?>" class="specItemsFiltersTopItem<?= !$current_specialty ? ' active' : ''; ?>">Все</a>
                        <?php if (!empty($specialties) && !is_wp_error($specialties)) { ?>
                            <?php foreach ($specialties as $specialty) {
                                if (function_exists('clinic_is_intern_specialty_term') && clinic_is_intern_specialty_term($specialty)) {
                                    continue;
                                }
                                $is_active = $current_specialty && (int) $current_specialty->term_id === (int) $specialty->term_id;
                                $specialty_url = function_exists('clinic_get_doctors_specialty_url')
                                    ? clinic_get_doctors_specialty_url($specialty)
                                    : add_query_arg('specs', $specialty->term_id, $catalog_base_url);
                            ?>
                            <a href="<?= esc_url($specialty_url); ?>" class="specItemsFiltersTopItem<?= $is_active ? ' active' : ''; ?>"><?= esc_html($specialty->name); ?></a>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div class="specItemsFiltersSwitch">
                        <button type="button" class="but specItemsFiltersSwitchBut">
                            <img src="<?= esc_url(THEME_URI); ?>/assets/img/ico/filter.svg" alt="">
                            <span>Фильтры</span>
                        </button>
                        <div class="specItemGradesResult mobile">Найдено: <span class="specDoctorsCount"><?= $doctors_count; ?></span></div>

                        <div class="specItemsFiltersSwitchWrap">
                            <div class="specItemsFiltersMiddle">
                                <div class="specItemsFiltersTitle">Фильтры</div>
                                <div class="modalWrapClose">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M4.92896 19.0711L19.0711 4.92893M4.92896 4.92893L19.0711 19.0711" stroke-width="2" stroke-linecap="square"></path>
                                    </svg>
                                </div>

                                <?php if (!empty($diseases) && !is_wp_error($diseases)) { ?>
                                <form action="#" class="specFilterForm">
                                    <div class="page_filters__filter--main">
                                        <div class="page_filters__filter--main--btn">
                                            <span><?= esc_html($problems_text); ?></span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 15.5002C11.744 15.5002 11.488 15.4023 11.293 15.2073L7.29301 11.2072C6.90201 10.8162 6.90201 10.1842 7.29301 9.79325C7.68401 9.40225 8.31601 9.40225 8.70701 9.79325L12.012 13.0982L15.305 9.91825C15.704 9.53525 16.335 9.54625 16.719 9.94325C17.103 10.3403 17.092 10.9742 16.695 11.3572L12.695 15.2193C12.5 15.4073 12.25 15.5002 12 15.5002Z" fill="#333333"/>
                                            </svg>
                                        </div>
                                        <div class="page_filters__filter--main--items">
                                            <div class="page_filters__filter--main--items--search">
                                                <input type="text" class="filter-search-input" placeholder="Поиск..." data-filter="problems">
                                            </div>
                                            <div class="page_filters__filter--main--items--block">
                                                <div class="page_filters__filter--main--items--block--item">
                                                    <input type="radio" name="problems" id="problem_0" value="0"<?= empty($problems) ? ' checked' : ''; ?>>
                                                    <label for="problem_0">Все проблемы</label>
                                                </div>
                                                <?php foreach ($diseases as $disease) {
                                                    $problem_unavailable = !isset($available_problems_lookup[(int) $disease->term_id]);
                                                ?>
                                                <div class="page_filters__filter--main--items--block--item<?= $problem_unavailable ? ' filter-unavailable' : ''; ?>" data-term-id="<?= (int) $disease->term_id; ?>" data-term-name="<?= esc_attr(strtolower($disease->name)); ?>">
                                                    <input type="radio" name="problems" value="<?= (int) $disease->term_id; ?>" id="problem_<?= (int) $disease->term_id; ?>"<?= (string) $problems === (string) $disease->term_id ? ' checked' : ''; ?>>
                                                    <label for="problem_<?= (int) $disease->term_id; ?>"><?= esc_html($disease->name); ?></label>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <?php } ?>

                                <div class="specItemsFilterSearch">
                                    <div class="specSearch">
                                        <input type="search" name="search" class="field fieldSearch" placeholder="Поиск по ФИО" value="<?= esc_attr($search); ?>">
                                        <button type="submit" class="but searchSubmit" aria-label="Поиск"><img src="<?= esc_url(THEME_URI); ?>/assets/img/ico/search.svg" alt=""></button>
                                    </div>
                                </div>

                                <div class="filterReset">
                                    <a href="#" class="filterResetLink">
                                        <span class="withLine">Сбросить фильтр</span>
                                        <img src="<?= esc_url(THEME_URI); ?>/assets/img/ico/reset.svg" alt="">
                                    </a>
                                </div>
                            </div>

                            <div class="specItemsFiltersFooter">
                                <?php if (!empty($grades) && !is_wp_error($grades)) { ?>
                                <div class="specItemGrades">
                                    <a href="#" class="specItemGrade<?= !$grade ? ' active' : ''; ?>" data-grade="0">Все грейды</a>
                                    <?php foreach ($grades as $grade_term) {
                                        $grade_description = get_field('filter_description', 'doctor_grade_' . $grade_term->term_id);
                                        $grade_unavailable = $has_assigned_grades && !isset($available_grades_lookup[(int) $grade_term->term_id]);
                                    ?>
                                    <a href="#" class="specItemGrade<?= $grade === (int) $grade_term->term_id ? ' active' : ''; ?><?= $grade_unavailable ? ' filter-unavailable' : ''; ?>" data-grade="<?= (int) $grade_term->term_id; ?>" data-description="<?= esc_attr($grade_description); ?>"><?= esc_html($grade_term->name); ?></a>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                                <div class="specItemGradesResult">Найдено: <span class="specDoctorsCount"><?= $doctors_count; ?></span></div>
                            </div>

                            <div class="specItemGradeDescription"<?= $active_grade_description ? '' : ' style="display:none;"'; ?>><?= esc_html($active_grade_description); ?></div>

                            <div class="specItemsFiltersMobileButs">
                                <a href="#" class="but butReset filterResetLink">Сбросить</a>
                                <a href="#" class="but butPrimary butShow">Показать психологов</a>
                            </div>
                        </div>

                        <div class="specItemsFiltersOverlay"></div>
                    </div>
                </div>

                <a href="#specItemsFilters" class="but filterScroll">
                    <img src="<?= esc_url(THEME_URI); ?>/assets/img/ico/filter.svg" alt="">
                </a>

                <?php if ($doctors->have_posts()) : ?>

                <div class="specItems_wrap">
                    <?php while ($doctors->have_posts()) : $doctors->the_post();
                        get_template_part('template-parts/parts/doctor', null, array('id' => get_the_ID()));
                    endwhile; ?>
                </div>

                <div class="innerPageMiddle bottomTextResults">
                    <div class="innerPageSubText">
                        <?= $bottom_text; ?>
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
                        <?= $bottom_text; ?>
                    </div>
                </div>

                <?php endif; wp_reset_postdata(); ?>
            </div>
        </div>

    </section>

    <section class="secondFeatures">
        <div class="container">
            <h2 class="sectionTitle">Чтобы попасть к нам, все психологи проходят строгий отбор</h2>
            <div class="secondFeaturesWrap">

                <div class="secondFeatureItem">
                <div class="secondFeatureItemIco"><img src="/wp-content/uploads/2026/02/ico2.svg" alt=""></div>
                        <div class="secondFeatureItemTitle">Долгосрочное обучение</div>

                        <div class="secondFeatureItemText">Все наши психологи постоянно повышают квалификацию и&nbsp;имеют высшее образование</div>
                                    </div>


                <div class="secondFeatureItem">
                <div class="secondFeatureItemIco"><img src="/wp-content/uploads/2026/02/ico3.svg" alt=""></div>
                        <div class="secondFeatureItemTitle">Супервизии и интервизии</div>

                        <div class="secondFeatureItemText">Наши специалисты не&nbsp;остаются наедине с&nbsp;проблемными вопросами и&nbsp;имеют поддержку коллег</div>
                                    </div>


                <div class="secondFeatureItem">
                <div class="secondFeatureItemIco"><img src="/wp-content/uploads/2026/02/ico4.svg" alt=""></div>
                        <div class="secondFeatureItemTitle">Этика</div>

                        <div class="secondFeatureItemText">Все наши специалисты прошли тестирование на&nbsp;приверженность этическим стандартам и&nbsp;придерживаются их&nbsp;в&nbsp;своей работе</div>
                                    </div>


                <div class="secondFeatureItem">
                <div class="secondFeatureItemIco"><img src="/wp-content/uploads/2026/02/ico5.svg" alt=""></div>
                        <div class="secondFeatureItemTitle">Контроль качества</div>

                        <div class="secondFeatureItemText">Собираем обратную связь по&nbsp;работе специалистов, а&nbsp;все новые психологи проходят полугодовую стажировку</div>
                                    </div>

                        </div>
        </div>
    </section>

<?php endwhile; ?>

<?php while (have_posts()) : the_post(); ?>
    <?php do_action('template_blocks'); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
