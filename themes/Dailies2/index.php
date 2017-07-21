<?php get_header(); 
/*$bigQueryArgs = array(
	'posts_per_page' => 200,
);
$bigQueryPosts = get_posts($bigQueryArgs);
foreach ($bigQueryPosts as $post) : setup_postdata($post);
	buildPostDataObject($post->ID);
endforeach;*/

$userID = get_current_user_id();
$userRep = get_user_meta($userID, 'rep', true);
$userRepTime = get_user_meta($userID, 'repVotes', true);
$winnerArgs = array(
	'tag' => 'winners',
	'category_name' => 'noms',
	'posts_per_page' => 1,
);
$postDataWinners = get_posts($winnerArgs);
foreach ( $postDataWinners as $post) : setup_postdata($post); 
	$winnerDataObject = get_post_meta($post->ID, 'postDataObj', true);
	$winnerVoteDataArray = array(
		'voteledger' => get_post_meta($post->ID, 'voteledger', true),
		'guestlist' => get_post_meta($post->ID, 'guestlist', true),
		'votecount' => get_post_meta($post->ID, 'votecount', true),
	);
	$winnerVoteData = json_encode($winnerVoteDataArray);
endforeach;

?>
<div id="wp-social-login" class="hidden"><?php do_action('wordpress_social_login'); ?></div>

<section id="homepageApp">
</section>

<?php get_footer(); ?>