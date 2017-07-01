<?php //the rep box at the top of the sidebar, or beneath the propbox on small screens
$user_id = get_current_user_id();
$myrep = get_user_meta($user_id, 'rep', true);
if ( $user_id == 0 ) {
	$myrep = 0.1;
}
?>

<section id="userbox-wrapper">
	<header id="repHeader" class="sideHeader">Your Rep: <div class="repScore" data-rep="<?php echo $myrep; ?>"><?php 
		echo $myrep; 
	?></div></header>
	<div id="userbox">
		<?php if ( $user_id == 0 ) {
			if ( !is_page('live') ) { ?>
				<p class="userbox">Your votes count as much as your Rep. New members get 1</p>
				<p class="userbox">Vote daily and your Rep will grow</p>
			<?php } ?>
			<?php do_action( 'wordpress_social_login' ); ?>
		<?php } else { ?>
			<p class="userbox"><a href="<?php echo $thisDomain; ?>/your-votes">Your Votes</a></p>
			<p class="userbox"><a href="<?php echo $thisDomain; ?>/secret-garden">Secret Garden</a></p>
			<p class="userbox"><a href="<?php echo wp_logout_url(); ?>">Logout</a></p>
		<?php }; 
		?>
	</div>
</section>