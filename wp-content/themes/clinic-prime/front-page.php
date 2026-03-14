<?php get_header(); ?>
<style type="text/css">
	.topMenu li.active > a:after {
		width: 0;
	}
	@media (max-width: 569px){
		.fixed-vidjet-mini, .fixed-vidjet-open {
	        bottom: 70px;
	    }		
	}
</style>

<?php while (have_posts()) : the_post(); ?>

    <?php do_action('template_blocks'); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
