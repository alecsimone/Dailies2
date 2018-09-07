<?php /* Template Name: Weed */ 
get_header();

if (is_user_logged_in()) { ?>

<section id="weedApp"></section>

<?php } else { ?>

<div id="wp-social-login"><?php do_action('wordpress_social_login'); ?></div>

<?php }
get_footer(); ?>