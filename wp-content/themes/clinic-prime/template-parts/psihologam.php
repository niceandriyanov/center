<?php
/**
 * Template Name: Психологам
 */

if (!defined('ABSPATH')) {
    exit;
}
get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
    <?php do_action('template_blocks'); ?>

<?php endwhile; ?>

<?php get_footer(); 