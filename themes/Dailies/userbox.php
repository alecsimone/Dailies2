<?php //the rep box at the top of the sidebar, or beneath the propbox on small screens
$user_id = get_current_user_id();
$myrep = get_user_meta($user_id, 'rep', true);
?>

<header id="repHeader" class="sideHeader">Your Rep: <div class="repScore"><?php 
	if ( $user_id == 0 ) {
		echo "0.1";
	} else {
		echo $myrep; 
	};
?></div></header>
<div id="userbox">
	<?php if ( $user_id == 0 ) { ?>
		<p class="userbox">Members' votes count 10x more.</p>
		<p class="userbox">Build Rep and your multiplier grows.</p>
		<?php do_action( 'wordpress_social_login' ); ?>
	<?php } else { ?>
		<p class="userbox"><a href="<?php echo $thisDomain; ?>/your-votes">Your Votes</a></p>
		<p class="userbox"><a href="<?php echo wp_logout_url(); ?>">Logout</a></p>
	<?php }; 
	?>
</div>