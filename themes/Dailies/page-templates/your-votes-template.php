<?php /* Template Name: Your Votes */ 
get_header(); 
if ( is_user_logged_in() ) {
	$user_id = get_current_user_id(); // Get the user's ID
	$voteHistory = get_user_meta($user_id, 'voteHistory', true);
	$historyCount = count($voteHistory);
	$historyArgs = array(
		'post__in' => $voteHistory,
		'paged' => $paged,
		'posts_per_page' => 10,
	);
	$yourHistory = get_posts($historyArgs);
	$pageNo = get_query_var('paged', 1 );
	if ($pageNo == '0') { $pageNo = 1; };
	$nextPage = $pageNo + 1;
}; ?>
<div class="wrapper">
	<div class="contentContainer">
		<header id="archive-header" class="your-votes-header">
			<h2>Your Votes</h2>
		</header>
		<?php if ( !is_user_logged_in() ) { ?>
			<div class="thing your-votes-error">
				<p class="onboardText your-votes-error">You need to be logged in, dummy.</p>
				<p class="onboardText your-votes-error">Members' votes count 10x more. Build Rep and your multiplier grows.</p>
				<?php do_action( 'wordpress_social_login' ); ?>
			</div>
		<?php }
		foreach ($yourHistory as $post) {
			setup_postdata($post); 
			include(locate_template('thing.php'));
		}

		if ($pageNo * 10 < $historyCount) { ?>
			<a href="<?php echo $thisDomain; ?>/your-votes/page/<?php echo $nextPage; ?>" class="earlier more pull"><img src='<?php echo $thisDomain; ?>/wp-content/uploads/2016/09/More.png' class='earlierIMG'></a>
		<?php }; ?>
	</div><?php include(locate_template('sidebar.php')); ?>
</div>

<?php get_footer(); ?>