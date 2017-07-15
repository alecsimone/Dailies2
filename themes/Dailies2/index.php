<?php get_header(); 
/*$bigQueryArgs = array(
	'posts_per_page' => 200,
);
$bigQueryPosts = get_posts($bigQueryArgs);
foreach ($bigQueryPosts as $post) : setup_postdata($post);
	buildPostDataObject($post->ID);
endforeach; */

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

date_default_timezone_set('America/Chicago');
$today = getdate();
$year = $today[year];
$month = $today[mon];
$day = $today[mday];
($paged === 0) ? $my_page = 0 : $my_page = $paged - 1;
stepBackDate($my_page);

$dayOneArgs = array(
	'category_name' => 'noms',
	'posts_per_page' => 10,
	'orderby' => 'meta_value_num',
	'meta_key' => 'votecount',
	'date_query' => array(
		array(
			'year'  => $year,
			'month' => $month,
			'day'   => $day,
		),
	),
);
$postDataNoms = get_posts($dayOneArgs);

$i = 0;
while ( !$postDataNoms && $i < 14 ) :
	stepBackDate(1);
	$newNomArgs = array(
		'category_name' => 'noms',
		'posts_per_page' => 10,
		'orderby' => 'meta_value_num',
		'meta_key' => 'votecount',
		'date_query' => array(
			array(
				'year'  => $year,
				'month' => $month,
				'day'   => $day,
				),
			),
		);
	$postDataNoms = get_posts($newNomArgs);
	$i++;
	$my_page++; //Since pages are how we keep track of the day, we need to tick up my_page even for days with no posts
endwhile;
$dayOnePostDatas = [];
$dayOneVoteDataArray = [];
foreach ($postDataNoms as $post) {
	setup_postdata($post);
	$postData = get_post_meta($post->ID, 'postDataObj', true);
	$dayOnePostDatas[] = $postData;
	$dayOneVoteDataArray[$post->ID] = array(
		'voteledger' => get_post_meta($post->ID, 'voteledger', true),
		'guestlist' => get_post_meta($post->ID, 'guestlist', true),
		'votecount' => get_post_meta($post->ID, 'votecount', true),
	);
	$dayOneVoteData = json_encode($dayOneVoteDataArray);
}	
$dayOnePostDataAll = array(
	'date' => array(
		'year'  => $year,
		'month' => $month,
		'day'   => $day,
	),
	'postDatas' => $dayOnePostDatas,
	'voteDatas' => $dayOneVoteData,
);
$dayOnePostData = json_encode($dayOnePostDataAll);

?>
<div id="wp-social-login" class="hidden"><?php do_action('wordpress_social_login'); ?></div>

<div id="dataDrop" data-user-id="<?php echo $userID; ?>" data-rep="<?php echo $userRep; ?>" data-rep-time='<?php echo json_encode($userRepTime); ?>' data-client-ip="<?php echo $_SERVER['REMOTE_ADDR']; ?>" data-winnerobject='<?php echo $winnerDataObject; ?>' data-winnervotedata='<?php echo $winnerVoteData; ?>' data-dayoneobject='<?php echo $dayOnePostData ?>'></div>

<section id="homepageApp">
</section>

<?php get_footer(); ?>