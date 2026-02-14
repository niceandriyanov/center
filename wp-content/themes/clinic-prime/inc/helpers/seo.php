<?php
/**
 * SEO функции
 * 
 * @package Clinic_Prime
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Функция для добавления мета-тегов SEO
 */
function clinic_seo_meta_tags() {
    if (is_single()) {
        $excerpt = wp_strip_all_tags(get_the_excerpt());
        if (empty($excerpt)) {
            $excerpt = wp_trim_words(get_the_content(), 20, '...');
        }
        
        echo '<meta name="description" content="' . esc_attr($excerpt) . '">';
        
        if (has_post_thumbnail()) {
            $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
            echo '<meta property="og:image" content="' . esc_url($image[0]) . '">';
        }
    }
}
add_action('wp_head', 'clinic_seo_meta_tags');

/**
 * Функция для добавления схемы разметки
 */
function clinic_schema_markup() {
    if (is_single()) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author()
            ),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url()
                )
            )
        );
        
        echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
    }
}
add_action('wp_footer', 'clinic_schema_markup');
