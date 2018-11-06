<?php /* Template Name: Submit */ 
get_header(); ?>

<?php if (get_current_user_id() === 0) {
	?>
		<section id="loggedOutProspectForm">
			<?php do_action('wordpress_social_login'); ?>
		</section>
	<?php
} else {
	?><section id="AddProspectForm"></section><?php
} ?>

<?php get_footer(); ?>