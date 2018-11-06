<?php $userID = get_current_user_id();
$repVotes = get_user_meta(get_current_user_id(), 'repVotes', true);
if ($repVotes === false) {
	$lastRepVote = 0;
} else {
	$lastRepVote = end( $repVotes );
}
if ($userID === 0) {
	$userRep = 0.1;
	$lastRepVote = 0;
}

?>

<div id="userbox" data-rep="<?php echo getValidRep(get_current_user_id()); ?>" data-repvotetime="<?php echo $lastRepVote; ?>">
	<header id="repHeader">Your Rep: <span class="repText"><?php echo $userRep; ?></span></header>
	<div id="userbox-links">
		<?php if ( $userID === 0 ) { ?>
			<p class="userbox">Your votes count as much as your Rep. New members get 1</p>
			<p class="userbox">Vote daily and your Rep will grow</p>
			<?php do_action( 'wordpress_social_login' ); ?>
		<?php } else { ?>
			<p class="userbox"><a href="<?php echo get_site_url(); ?>/your-votes">Your Votes</a></p>
			<p class="userbox"><a href="<?php echo get_site_url(); ?>/secret-garden">Secret Garden</a></p>
			<p class="userbox"><a href="<?php echo wp_logout_url(); ?>">Logout</a></p>
		<?php }; ?>
	</div>
</div>