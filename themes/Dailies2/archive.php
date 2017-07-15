<?php get_header();

$thisTerm = get_queried_object();

$headerData = array(
	'thisTerm' => $thisTerm,
	'logo_url' => get_term_meta($thisTerm->term_id, 'logo', true),
	'twitter' => get_term_meta($thisTerm->term_id, 'twitter', true),
	'twitch' => get_term_meta($thisTerm->term_id, 'twitch', true),
	'youtube' => get_term_meta($thisTerm->term_id, 'youtube', true),
	'website' => get_term_meta($thisTerm->term_id, 'website', true),
	'discord' => get_term_meta($thisTerm->term_id, 'discord', true),
	'donate' => get_term_meta($thisTerm->term_id, 'donate', true),
);

$userID = get_current_user_id();
$userRep = get_user_meta($userID, 'rep', true);
$userRepTime = get_user_meta($userID, 'repVotes', true);

$archiveArgs = array(
	'posts_per_page' => 10,
	'category_name' => 'noms',
	'paged' => $paged,
	'orderby' => $orderby,
	'order' => $order,
	'meta_key' => 'votecount',
	'tax_query' => array(
		array(
			'taxonomy' => $thisTerm->taxonomy,
			'field' => 'slug',
			'terms' => $thisTerm->slug,
		)
	), 
);
if ($thisTerm->taxonomy === 'post_tag') {
	unset($archiveArgs['tax_query']);
	$archiveArgs['tag'] = $thisTerm->slug;
}
$archivePostDatas = get_posts($archiveArgs);
$initialPostData = [];
$initialVoteDataArray = [];
foreach ($archivePostDatas as $post) {
	setup_postdata($post);
	$postData = get_post_meta($post->ID, 'postDataObj', true);
	$initialPostDatas[] = $postData;
	$initialVoteDataArray[$post->ID] = array(
		'voteledger' => get_post_meta($post->ID, 'voteledger', true),
		'guestlist' => get_post_meta($post->ID, 'guestlist', true),
		'votecount' => get_post_meta($post->ID, 'votecount', true),
	);
}
$initialVoteData = json_encode($initialVoteDataArray);
$initialPostData = json_encode($initialPostDatas);
?>

<div id="dataDrop" data-user-id="<?php echo $userID; ?>" data-rep="<?php echo $userRep; ?>" data-rep-time='<?php echo json_encode($userRepTime); ?>' data-client-ip="<?php echo $_SERVER['REMOTE_ADDR']; ?>" data-archive-header='<?php echo json_encode($headerData); ?>' data-initial-postdata='<?php echo $initialPostData; ?>' data-initial-votedata='<?php echo $initialVoteData; ?>' data-orderby="<?php echo $orderby; ?>" data-order="<?php echo $order; ?>"></div>

<section id="archiveApp">
</section>

<?php get_footer(); ?>