<?php
/**
 * Каталог специалистов: URL /psychologists/{specialty}/, контекст страницы, SEO
 *
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ID страницы каталога врачей
 *
 * @return int
 */
function clinic_get_doctors_page_id() {
    static $page_id = null;

    if ($page_id !== null) {
        return $page_id;
    }

    $page = function_exists('get_field') ? get_field('theme_doctors_page', 'option') : null;

    if (is_object($page) && isset($page->ID)) {
        $page_id = (int) $page->ID;
    } elseif (is_numeric($page)) {
        $page_id = (int) $page;
    } elseif (is_string($page) && $page !== '') {
        $page_id = (int) url_to_postid($page);
    } else {
        $page_id = 0;
    }

    if (!$page_id) {
        $pages = get_posts(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'meta_key'       => '_wp_page_template',
            'meta_value'     => 'template-parts/doctors.php',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ));

        if (!empty($pages)) {
            $page_id = (int) $pages[0];
        }
    }

    return $page_id;
}

/**
 * Текущая страница — каталог врачей
 *
 * @return bool
 */
function clinic_is_doctors_catalog_page() {
    $page_id = clinic_get_doctors_page_id();

    if (!$page_id) {
        return false;
    }

    if (get_query_var('doctors_specialty_slug')) {
        return true;
    }

    return is_page($page_id);
}

/**
 * Подготавливает global $post для Yoast и шаблона на /psychologists/{spec}/
 */
function clinic_doctors_catalog_setup_queried_post() {
    $page_id = clinic_get_doctors_page_id();

    if (!$page_id || !clinic_is_doctors_catalog_page()) {
        return;
    }

    $page_post = get_post($page_id);

    if (!$page_post instanceof WP_Post) {
        return;
    }

    global $post, $wp_query;

    $post = $page_post;

    if ($wp_query instanceof WP_Query) {
        $wp_query->post = $page_post;
        $wp_query->posts = array($page_post);
        $wp_query->post_count = 1;
        $wp_query->found_posts = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_404 = false;
    }

    setup_postdata($post);
}
add_action('wp', 'clinic_doctors_catalog_setup_queried_post', 1);

/**
 * Yoast: привязка SEO-мета к странице каталога на /doctors/{spec}/
 *
 * @param int $page_id
 * @return int
 */
function clinic_doctors_catalog_yoast_page_id($page_id) {
    $doctors_page_id = clinic_get_doctors_page_id();

    if (!$doctors_page_id) {
        return $page_id;
    }

    if (get_query_var('doctors_specialty_slug') || is_page($doctors_page_id)) {
        return $doctors_page_id;
    }

    return $page_id;
}
add_filter('wpseo_frontend_page_type_simple_page_id', 'clinic_doctors_catalog_yoast_page_id');

/**
 * Термин специальности из URL /psychologists/{slug}/
 *
 * @return WP_Term|null
 */
function clinic_get_current_doctors_specialty() {
    $slug = get_query_var('doctors_specialty_slug');

    if (!$slug) {
        return null;
    }

    $term = get_term_by('slug', $slug, 'doctor_specialty');

    if (!$term || is_wp_error($term)) {
        return null;
    }

    return $term;
}

/**
 * URL каталога для специальности
 *
 * @param WP_Term $term
 * @return string
 */
function clinic_get_doctors_specialty_url($term) {
    $base = trailingslashit(get_permalink(clinic_get_doctors_page_id()));

    return $base . $term->slug . '/';
}

/**
 * Специальность «Стажер» — это грейд, не метод в табах каталога
 *
 * @param WP_Term $term
 * @return bool
 */
function clinic_is_intern_specialty_term($term) {
    if (!$term instanceof WP_Term) {
        return false;
    }

    $name = function_exists('mb_strtolower') ? mb_strtolower($term->name) : strtolower($term->name);
    $slug = $term->slug;
    $normalized = str_replace(array('ё', 'Ё'), array('е', 'Е'), $name);

    return $normalized === 'стажер' || in_array($slug, array('stazher', 'intern', 'stazhyor', 'stazher-2'), true);
}

/**
 * URL каталога (все врачи или конкретная специальность)
 *
 * @param WP_Term|null $specialty
 * @return string
 */
function clinic_get_doctors_catalog_url($specialty = null) {
    if ($specialty instanceof WP_Term) {
        return clinic_get_doctors_specialty_url($specialty);
    }

    return get_permalink(clinic_get_doctors_page_id());
}

/**
 * Контент каталога: поля страницы + переопределение полями специальности
 *
 * @return array<string, mixed>
 */
function clinic_get_doctors_catalog_context() {
    $page_id = clinic_get_doctors_page_id();
    $term = clinic_get_current_doctors_specialty();
    $term_key = $term ? 'doctor_specialty_' . $term->term_id : null;

    $get_field_value = function ($field) use ($page_id, $term_key) {
        if ($term_key) {
            $value = get_field($field, $term_key);

            if ($value !== null && $value !== false && $value !== '') {
                if (is_array($value) && empty($value)) {
                    return get_field($field, $page_id);
                }

                return $value;
            }
        }

        return get_field($field, $page_id);
    };

    $header_h1 = $get_field_value('header_h1');

    if (!$header_h1 && $term) {
        $header_h1 = $term->name;
    }

    if (!$header_h1 && $page_id) {
        $header_h1 = get_the_title($page_id);
    }

    $features_title = $get_field_value('features_title');

    if (!$features_title) {
        $features_title = 'Каждый из наших специалистов';
    }

    return array(
        'page_id'          => $page_id,
        'specialty'        => $term,
        'header_h1'        => $header_h1,
        'header_lead'      => $get_field_value('header_lead'),
        'bottom_text'      => $get_field_value('bottom_text'),
        'features_title'   => $features_title,
        'faq'              => $get_field_value('faq'),
        'seo_title'        => $get_field_value('seo_title'),
        'seo_description'  => $get_field_value('seo_description'),
    );
}

/**
 * ID специальности для фильтрации врачей
 *
 * @param string $specs
 * @param string $specialty_slug
 * @return int
 */
function clinic_resolve_doctors_specialty_term_id($specs = '', $specialty_slug = '') {
    if (!empty($specs) && $specs !== '0') {
        return absint($specs);
    }

    if ($specialty_slug) {
        $term = get_term_by('slug', sanitize_title($specialty_slug), 'doctor_specialty');

        if ($term && !is_wp_error($term)) {
            return (int) $term->term_id;
        }
    }

    $term = clinic_get_current_doctors_specialty();

    if ($term) {
        return (int) $term->term_id;
    }

    return 0;
}

/**
 * Аргументы WP_Query для каталога врачей
 *
 * @param array $filters search, problems, specs, grade, specialty_slug
 * @return array
 */
function clinic_build_doctors_query_args($filters = array()) {
    $search = isset($filters['search']) ? sanitize_text_field($filters['search']) : '';
    $problems = isset($filters['problems']) ? sanitize_text_field($filters['problems']) : '';
    $grade = isset($filters['grade']) ? absint($filters['grade']) : 0;
    $specialty_id = clinic_resolve_doctors_specialty_term_id(
        isset($filters['specs']) ? $filters['specs'] : '',
        isset($filters['specialty_slug']) ? $filters['specialty_slug'] : ''
    );

    $args = array(
        'post_type'      => 'doctors',
        'posts_per_page' => -1,
        'orderby'        => array(
            'menu_order' => 'ASC',
            'date'       => 'ASC',
        ),
        'post_status'    => 'publish',
    );

    if (!empty($search)) {
        global $wpdb;

        $post_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID
            FROM {$wpdb->posts}
            WHERE post_type = 'doctors'
            AND post_status = 'publish'
            AND post_title LIKE %s",
            '%' . $wpdb->esc_like($search) . '%'
        ));

        $args['post__in'] = !empty($post_ids) ? $post_ids : array(0);
    }

    $tax_query = array();

    if (!empty($problems) && $problems !== '0') {
        $tax_query[] = array(
            'taxonomy' => 'doctor_diseases',
            'field'    => 'term_id',
            'terms'    => $problems,
        );
    }

    if ($specialty_id) {
        $tax_query[] = array(
            'taxonomy' => 'doctor_specialty',
            'field'    => 'term_id',
            'terms'    => $specialty_id,
        );
    }

    if ($grade) {
        $tax_query[] = array(
            'taxonomy' => 'doctor_grade',
            'field'    => 'term_id',
            'terms'    => $grade,
        );
    }

    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    return $args;
}

/**
 * ID врачей каталога по набору фильтров
 *
 * @param array $filters
 * @return int[]
 */
function clinic_get_doctors_catalog_post_ids($filters = array()) {
    static $cache = array();

    $cache_key = md5(wp_json_encode($filters));

    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $args = clinic_build_doctors_query_args($filters);
    $args['fields'] = 'ids';
    $args['no_found_rows'] = true;

    $query = new WP_Query($args);
    $ids = !empty($query->posts) ? array_map('intval', (array) $query->posts) : array();

    wp_reset_postdata();

    $cache[$cache_key] = $ids;

    return $ids;
}

/**
 * ID врачей каталога в базовом контексте (только специальность)
 *
 * @param int $specialty_id
 * @return int[]
 */
function clinic_get_doctors_catalog_base_post_ids($specialty_id = 0) {
    return clinic_get_doctors_catalog_post_ids(array(
        'specs' => $specialty_id ? (string) $specialty_id : '',
    ));
}

/**
 * ID терминов таксономии, привязанных к списку врачей
 *
 * @param string $taxonomy
 * @param int[]  $doctor_ids
 * @return int[]
 */
function clinic_get_catalog_term_ids_for_doctors($taxonomy, $doctor_ids) {
    global $wpdb;

    $doctor_ids = array_values(array_filter(array_map('absint', (array) $doctor_ids)));

    if (empty($doctor_ids)) {
        return array();
    }

    $doctor_ids_sql = implode(',', $doctor_ids);

    $sql = $wpdb->prepare(
        "SELECT DISTINCT tt.term_id
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE tt.taxonomy = %s
        AND tr.object_id IN ({$doctor_ids_sql})",
        $taxonomy
    );

    return array_map('intval', (array) $wpdb->get_col($sql));
}

/**
 * Оставить только термины, у которых есть врачи в текущем контексте каталога
 *
 * @param WP_Term[] $terms
 * @param string    $taxonomy
 * @param int[]     $doctor_ids
 * @return WP_Term[]
 */
function clinic_filter_catalog_terms_by_doctors($terms, $taxonomy, $doctor_ids) {
    if (empty($terms) || is_wp_error($terms) || empty($doctor_ids)) {
        return array();
    }

    $available_ids = clinic_get_catalog_term_ids_for_doctors($taxonomy, $doctor_ids);

    if (empty($available_ids)) {
        return array();
    }

    $available_lookup = array_flip($available_ids);
    $filtered = array();

    foreach ($terms as $term) {
        if (isset($available_lookup[(int) $term->term_id])) {
            $filtered[] = $term;
        }
    }

    return $filtered;
}

/**
 * Доступные значения фильтров с учётом взаимного сужения
 *
 * @param array $filters specs, problems, grade, search
 * @return array{problems: int[], grades: int[]}
 */
function clinic_get_doctors_catalog_filter_availability($filters = array()) {
    $specs = isset($filters['specs']) ? $filters['specs'] : '';
    $problems = isset($filters['problems']) ? $filters['problems'] : '';
    $grade = isset($filters['grade']) ? absint($filters['grade']) : 0;
    $search = isset($filters['search']) ? $filters['search'] : '';

    $problem_doctor_ids = clinic_get_doctors_catalog_post_ids(array(
        'specs'  => $specs,
        'grade'  => $grade,
        'search' => $search,
    ));

    $grade_doctor_ids = clinic_get_doctors_catalog_post_ids(array(
        'specs'    => $specs,
        'problems' => $problems,
        'search'   => $search,
    ));

    return array(
        'problems' => clinic_get_catalog_term_ids_for_doctors('doctor_diseases', $problem_doctor_ids),
        'grades'   => clinic_get_catalog_term_ids_for_doctors('doctor_grade', $grade_doctor_ids),
    );
}

/**
 * Query vars для каталога
 *
 * @param array $vars
 * @return array
 */
function clinic_doctors_catalog_query_vars($vars) {
    $vars[] = 'doctors_specialty_slug';
    $vars[] = 'clinic_doctors_slug';

    return $vars;
}
add_filter('query_vars', 'clinic_doctors_catalog_query_vars');

/**
 * Rewrite rule с высоким приоритетом для /psychologists/{slug}/
 */
function clinic_doctors_catalog_register_rewrite_rule() {
    add_rewrite_rule(
        '^psychologists/([^/]+)/?$',
        'index.php?clinic_doctors_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'clinic_doctors_catalog_register_rewrite_rule', 10);

/**
 * Извлекает slug из query vars WP
 *
 * @param array $query_vars
 * @return string|null
 */
function clinic_doctors_catalog_extract_slug($query_vars) {
    if (!empty($query_vars['clinic_doctors_slug'])) {
        return sanitize_title($query_vars['clinic_doctors_slug']);
    }

    if (!empty($query_vars['doctors']) && empty($query_vars['page_id'])) {
        return sanitize_title($query_vars['doctors']);
    }

    if (!empty($query_vars['pagename']) && preg_match('#^psychologists/([^/]+)$#', $query_vars['pagename'], $matches)) {
        return sanitize_title($matches[1]);
    }

    return null;
}

/**
 * Настраивает query vars для страницы специальности
 *
 * @param array  $query_vars
 * @param int    $page_id
 * @param string $slug
 * @return array
 */
function clinic_doctors_catalog_apply_specialty_query($query_vars, $page_id, $slug) {
    unset(
        $query_vars['doctors'],
        $query_vars['name'],
        $query_vars['pagename'],
        $query_vars['clinic_doctors_slug'],
        $query_vars['error']
    );

    $query_vars['page_id'] = $page_id;
    $query_vars['post_type'] = 'page';
    $query_vars['doctors_specialty_slug'] = $slug;

    return $query_vars;
}

/**
 * Настраивает query vars для single врача
 *
 * @param array  $query_vars
 * @param string $slug
 * @return array
 */
function clinic_doctors_catalog_apply_doctor_query($query_vars, $slug) {
    unset(
        $query_vars['pagename'],
        $query_vars['page_id'],
        $query_vars['clinic_doctors_slug'],
        $query_vars['doctors_specialty_slug'],
        $query_vars['error']
    );

    $query_vars['post_type'] = 'doctors';
    $query_vars['name'] = $slug;
    $query_vars['doctors'] = $slug;

    return $query_vars;
}

/**
 * /psychologists/{slug}/ — специальность или single специалиста
 *
 * @param array $query_vars
 * @return array
 */
function clinic_doctors_catalog_parse_request($query_vars) {
    $slug = clinic_doctors_catalog_extract_slug($query_vars);

    if (!$slug) {
        return $query_vars;
    }

    $page_id = clinic_get_doctors_page_id();

    $term = get_term_by('slug', $slug, 'doctor_specialty');
    if ($term && !is_wp_error($term) && $page_id) {
        return clinic_doctors_catalog_apply_specialty_query($query_vars, $page_id, $slug);
    }

    $doctor = get_page_by_path($slug, OBJECT, 'doctors');
    if ($doctor instanceof WP_Post) {
        return clinic_doctors_catalog_apply_doctor_query($query_vars, $slug);
    }

    unset($query_vars['pagename'], $query_vars['page_id'], $query_vars['doctors'], $query_vars['clinic_doctors_slug']);
    $query_vars['error'] = '404';

    return $query_vars;
}
add_filter('request', 'clinic_doctors_catalog_parse_request', 1);

/**
 * Fallback: если WP всё же отдал 404, пробуем открыть страницу специальности
 */
function clinic_doctors_catalog_template_redirect_fallback() {
    if (!is_404()) {
        return;
    }

    global $wp;

    $request = isset($wp->request) ? trim($wp->request, '/') : '';

    if (!preg_match('#^psychologists/([^/]+)$#', $request, $matches)) {
        return;
    }

    $slug = sanitize_title($matches[1]);
    $page_id = clinic_get_doctors_page_id();
    $term = get_term_by('slug', $slug, 'doctor_specialty');

    if (!$term || is_wp_error($term) || !$page_id) {
        return;
    }

    global $wp_query;

    $wp_query = new WP_Query(array(
        'page_id'   => $page_id,
        'post_type' => 'page',
    ));

    if (!$wp_query->have_posts()) {
        return;
    }

    $page_post = get_post($page_id);

    if (!$page_post instanceof WP_Post) {
        return;
    }

    $wp_query->post = $page_post;
    $wp_query->posts = array($page_post);
    $wp_query->post_count = 1;
    $wp_query->found_posts = 1;
    $wp_query->max_num_pages = 1;
    $wp_query->is_page = true;
    $wp_query->is_singular = true;
    $wp_query->is_404 = false;
    $wp_query->is_home = false;

    $GLOBALS['post'] = $page_post;
    setup_postdata($page_post);

    $wp->query_vars['page_id'] = $page_id;
    $wp->query_vars['post_type'] = 'page';
    $wp->query_vars['doctors_specialty_slug'] = $slug;
    unset($wp->query_vars['error'], $wp->query_vars['pagename'], $wp->query_vars['clinic_doctors_slug']);

    status_header(200);
    nocache_headers();
}
add_action('template_redirect', 'clinic_doctors_catalog_template_redirect_fallback', 0);

/**
 * Редиректы legacy URL специальностей → /psychologists/{slug}/
 */
function clinic_doctors_catalog_redirect_legacy_urls() {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }

    if (!clinic_get_doctors_page_id()) {
        return;
    }

    if (is_tax('doctor_specialty')) {
        $term = get_queried_object();

        if ($term instanceof WP_Term) {
            wp_safe_redirect(clinic_get_doctors_specialty_url($term), 301);
            exit;
        }
    }

    if (!empty($_GET['doctor_specialty'])) {
        $slug = sanitize_title(wp_unslash($_GET['doctor_specialty']));
        $term = get_term_by('slug', $slug, 'doctor_specialty');

        if ($term && !is_wp_error($term)) {
            $params = wp_unslash($_GET);
            unset($params['doctor_specialty']);

            $url = clinic_get_doctors_specialty_url($term);

            if (!empty($params)) {
                $url = add_query_arg(array_map('sanitize_text_field', $params), $url);
            }

            wp_safe_redirect($url, 301);
            exit;
        }
    }

    if (!empty($_GET['specs'])) {
        $term_id = absint($_GET['specs']);

        if ($term_id) {
            $term = get_term($term_id, 'doctor_specialty');

            if ($term && !is_wp_error($term)) {
                $params = wp_unslash($_GET);
                unset($params['specs']);

                $url = clinic_get_doctors_specialty_url($term);

                if (!empty($params)) {
                    $url = add_query_arg(array_map('sanitize_text_field', $params), $url);
                }

                wp_safe_redirect($url, 301);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'clinic_doctors_catalog_redirect_legacy_urls', 0);

/**
 * Канонический URL термина специальности
 *
 * @param string  $termlink
 * @param WP_Term|int $term
 * @param string  $taxonomy
 * @return string
 */
function clinic_doctors_specialty_term_link($termlink, $term, $taxonomy) {
    if ($taxonomy !== 'doctor_specialty' || !clinic_get_doctors_page_id()) {
        return $termlink;
    }

    $term_obj = $term;

    if (is_numeric($term)) {
        $term_obj = get_term((int) $term, $taxonomy);
    }

    if (!$term_obj || is_wp_error($term_obj)) {
        return $termlink;
    }

    return clinic_get_doctors_specialty_url($term_obj);
}
add_filter('term_link', 'clinic_doctors_specialty_term_link', 10, 3);

/**
 * Yoast SEO: URL специальностей в sitemap → /psychologists/{slug}/
 *
 * @param array|string $url
 * @param string       $type
 * @param WP_Term      $object
 * @return array|string
 */
function clinic_doctors_catalog_yoast_sitemap_entry($url, $type, $object) {
    if ($type !== 'term' || !($object instanceof WP_Term) || $object->taxonomy !== 'doctor_specialty') {
        return $url;
    }

    if (!clinic_get_doctors_page_id()) {
        return $url;
    }

    $loc = clinic_get_doctors_specialty_url($object);

    if (is_array($url)) {
        $url['loc'] = $loc;
        return $url;
    }

    return $loc;
}
add_filter('wpseo_sitemap_entry', 'clinic_doctors_catalog_yoast_sitemap_entry', 10, 3);

/**
 * Отключаем canonical-редирект WP для страниц специальностей
 *
 * @param string|false $redirect_url
 * @return string|false
 */
function clinic_doctors_catalog_redirect_canonical($redirect_url) {
    if (get_query_var('doctors_specialty_slug')) {
        return false;
    }

    return $redirect_url;
}
add_filter('redirect_canonical', 'clinic_doctors_catalog_redirect_canonical');

/**
 * Yoast SEO: title из ACF (страница / специальность), иначе Yoast
 *
 * @param string $title
 * @return string
 */
function clinic_doctors_catalog_yoast_title($title) {
    if (!clinic_is_doctors_catalog_page()) {
        return $title;
    }

    $context = clinic_get_doctors_catalog_context();

    if (!empty($context['seo_title'])) {
        return $context['seo_title'];
    }

    return $title;
}
add_filter('wpseo_title', 'clinic_doctors_catalog_yoast_title');

/**
 * Yoast SEO: description из ACF (страница / специальность), иначе Yoast
 *
 * @param string $description
 * @return string
 */
function clinic_doctors_catalog_yoast_metadesc($description) {
    if (!clinic_is_doctors_catalog_page()) {
        return $description;
    }

    $context = clinic_get_doctors_catalog_context();

    if (!empty($context['seo_description'])) {
        return $context['seo_description'];
    }

    return $description;
}
add_filter('wpseo_metadesc', 'clinic_doctors_catalog_yoast_metadesc');

/**
 * Yoast SEO: canonical для страниц специальностей
 *
 * @param string $canonical
 * @return string
 */
function clinic_doctors_catalog_yoast_canonical($canonical) {
    $term = clinic_get_current_doctors_specialty();

    if (!$term) {
        return $canonical;
    }

    return clinic_get_doctors_specialty_url($term);
}
add_filter('wpseo_canonical', 'clinic_doctors_catalog_yoast_canonical');

/**
 * Flush rewrite rules после изменения таксономии
 */
function clinic_doctors_catalog_maybe_flush_rewrites() {
    if (get_option('clinic_psychologists_catalog_rewrite_version') === '1') {
        return;
    }

    clinic_doctors_catalog_register_rewrite_rule();
    flush_rewrite_rules(false);
    update_option('clinic_psychologists_catalog_rewrite_version', '1');
}
add_action('init', 'clinic_doctors_catalog_maybe_flush_rewrites', 99);

add_action('after_switch_theme', function () {
    delete_option('clinic_psychologists_catalog_rewrite_version');
    flush_rewrite_rules(false);
});

/**
 * Психологам со специальностью «Стажер» назначает грейд Стажер, если грейд ещё не задан
 */
function clinic_migrate_intern_specialty_to_grade() {
    if (get_option('clinic_intern_grade_migrated') === '4') {
        return;
    }

    $intern_grade = get_term_by('slug', 'intern', 'doctor_grade');

    if (!$intern_grade || is_wp_error($intern_grade)) {
        $intern_grade = get_term_by('name', 'Стажер', 'doctor_grade');
    }

    if (!$intern_grade || is_wp_error($intern_grade)) {
        return;
    }

    $doctors = get_posts(array(
        'post_type'      => 'doctors',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ));

    foreach ($doctors as $doctor_id) {
        $has_intern = false;
        $specs = function_exists('get_field') ? get_field('spec_filter', $doctor_id) : array();
        $taxonomy_specs = wp_get_post_terms((int) $doctor_id, 'doctor_specialty');

        if (!is_wp_error($taxonomy_specs) && !empty($taxonomy_specs)) {
            $specs = array_merge((array) $specs, $taxonomy_specs);
        }

        foreach ((array) $specs as $spec) {
            $term = $spec instanceof WP_Term ? $spec : null;

            if (!$term && is_numeric($spec)) {
                $term = get_term((int) $spec, 'doctor_specialty');
            }

            if ($term instanceof WP_Term && clinic_is_intern_specialty_term($term)) {
                $has_intern = true;
                break;
            }
        }

        if (!$has_intern) {
            continue;
        }

        $existing = wp_get_post_terms((int) $doctor_id, 'doctor_grade', array('fields' => 'ids'));

        if (empty($existing) || is_wp_error($existing)) {
            wp_set_object_terms((int) $doctor_id, (int) $intern_grade->term_id, 'doctor_grade', false);
        }
    }

    update_option('clinic_intern_grade_migrated', '4');
}
add_action('init', 'clinic_migrate_intern_specialty_to_grade', 99);
