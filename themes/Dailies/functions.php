<?php 

function basedailies_enqueue_style() {
	wp_enqueue_style( 'dailies-base', '/wp-content/themes/Dailies/style.css', false ); 
}
add_action( 'wp_enqueue_scripts', 'basedailies_enqueue_style' );

add_theme_support( 'post-thumbnails' );
add_image_size('small', 350, 800);
add_theme_support( 'title-tag' );

$thisDomain = get_site_url();


/*** Quicktags for post editor ***/
function appthemes_add_quicktags() {
    if (wp_script_is('quicktags')){
?>
<script type="text/javascript">
    QTags.addButton( 'embed_container', 'embed-container', `<div class="embed-container"></div>`);
</script>
<?php
    }
}

add_action( 'admin_print_footer_scripts', 'appthemes_add_quicktags' );
/*** End Quicktags ***/

function stepBackDate($steps) {
	global $year;
	global $month;
	global $day;
	$thirtyDays = array(4, 6, 9, 11); //these are the numbers of the months with 30 days
	$extraSteps = $day - $steps;
	if ($day > $steps) {
		$day = $day - $steps;
	} else {
		if ( $month == 1 ) {
			$month = 12;
			$year = $year - 1;
		} else {
			$month = $month - 1;
		}
		if ( in_array($month, $thirtyDays) ) {
			$day = 30 + $extraSteps;
		} elseif ($month == 2) {
			$day = 28 + $extraSteps;
		} else {
			$day = 31 + $extraSteps;
		}
	}
}

function increase_views() {
	$postID = $_POST['id'];
	$viewType = $_POST['viewType'];
	if ( $viewType === 'gfy' ) {
		increaseGFYViews($postID);
	} elseif ($viewType === 'fullClip') {
		increaseFullClipViews($postID);
	};
}
function increaseGFYViews($postID) {
	$old_gfy_viewcount = get_post_meta($postID, 'gfyViewcount', true);
	$new_gfy_viewcount = $old_gfy_viewcount + 1; 
	$gfy_viewcount_update_success = update_post_meta($postID, 'gfyViewcount', $new_gfy_viewcount);
}
function increaseFullClipViews($postID) {	
	$old_fullClip_viewcount = get_post_meta($postID, 'fullClipViewcount', true);
	$new_fullClip_viewcount = $old_fullClip_viewcount + 1; 
	$gfy_viewcount_update_success = update_post_meta($postID, 'fullClipViewcount', $new_fullClip_viewcount);
}

add_action( 'wp_enqueue_scripts', 'enqueue_increase_views');
function enqueue_increase_views() {
	wp_register_script( 'ajax-increase-views', '/wp-content/themes/Dailies/js/increase_views.js' );
	$increase_views_data = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	);
	wp_localize_script( 'ajax-increase-views', 'data_for_increasing_views', $increase_views_data );

	wp_enqueue_script('ajax-increase-views');
}

add_action( 'wp_ajax_increase_views', 'increase_views' );
add_action( 'wp_ajax_nopriv_increase_views', 'increase_views' );

add_action( 'wp_enqueue_scripts', 'enqueue_refresh_live' );
function enqueue_refresh_live() {
	wp_register_script( 'ajax-refresh-live', '/wp-content/themes/Dailies/js/refresh_live.js' );
	$refresh_live_data = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	);
	wp_localize_script( 'ajax-refresh-live', 'data_for_refresh_live', $refresh_live_data );
	wp_enqueue_script('ajax-refresh-live'); 
}

add_action( 'wp_ajax_refresh_live', 'refresh_live' );
add_action( 'wp_ajax_nopriv_refresh_live', 'refresh_live' );

function thingifyID($ID) {
	global $post;
	$our_post = get_post($ID);
	$post = $our_post;
	setup_postdata($our_post);
	ob_start();
	get_template_part('thing');
	$newThing = ob_get_contents();
	ob_end_clean();
	return $newThing;
}

function refresh_live() {
	$oldData = $_POST['oldData'];
	$liveArgs = array(
		'category__not_in' => 4,
		'posts_per_page' => 40,
		'date_query' => array(
			array(
				'after' => '48 hours ago'
			)
		)
	);
	$postDataLive = get_posts($liveArgs); 
	$postsAndScores = array();
	foreach ( $postDataLive as $post ) {
		$pid = $post->ID;
		$postsAndScores[$pid] = get_post_meta($pid, 'votecount', true);
		$newPostIDs[] = $pid;
	};
	$newPostsAndScores = json_encode($postsAndScores);

	foreach ( $oldData as $pid => $score) {
		$oldPostIDs[] = $pid;
	};

	$freshPostIDs = array_diff($newPostIDs, $oldPostIDs);
	$freshPostIDs = array_reverse($freshPostIDs);
	foreach ($freshPostIDs as $freshPostID) {
		$freshPosts[] = thingifyID($freshPostID);
	};

	$stalePosts = array_diff($oldPostIDs, $newPostIDs);
	foreach ($stalePosts as $index => $pid) {
		$stalePostIDs[] = $pid;
	};
	$refreshReturn = array(
		'fresh' => $freshPosts,
		'stale' => $stalePostIDs,
		'newData' => $newPostsAndScores,
		'print' => $newPostIDs
	);
	echo json_encode($refreshReturn);
	wp_die();
}

add_action( 'wp_ajax_trash_post', 'trash_post' );
add_action( 'wp_ajax_nopriv_trash_post', 'trash_post' );

function trash_post() {
	$trashPostID = $_POST['trash'];
	wp_trash_post($trashPostID);
	wp_die();
};

add_action( 'wp_enqueue_scripts', 'enqueue_secret_garden' );
function enqueue_secret_garden() {
	wp_register_script( 'ajax-secret-garden', '/wp-content/themes/Dailies/js/secret_garden.js' );
	$secret_garden_data = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	);
	wp_localize_script( 'ajax-secret-garden', 'data_for_secret_garden', $secret_garden_data );
	wp_enqueue_script('ajax-secret-garden'); 
}

add_action( 'wp_ajax_secret_garden_cut', 'secret_garden_cut' );
add_action( 'wp_ajax_nopriv_secret_garden_cut', 'secret_garden_cut' );

function secret_garden_cut() {
	$cutSlug = $_POST['cutSlug'];
	$cutSlugsTime = $_POST['cutSlugsTime'];
	$cutSlugsVODBase = $_POST['cutSlugsVODBase'];
	$cutSlugsVODTimestamp = $_POST['cutSlugsVODTimestamp'];
	$cutSlugScope = $_POST['cutSlugScope'];
	if ( $cutSlugScope === 'everyone' ) {
		$gardenPostObject = get_page_by_path('secret-garden');
		$gardenID = $gardenPostObject->ID;
		$oldSlugList = get_post_meta($gardenID, 'slugList', true);
	} else {
		$userID = $cutSlugScope;
		$oldSlugList = get_user_meta($userID, 'slugList', true);
	}
	if ( !empty($oldSlugList) ) {
		$newSlugList = $oldSlugList;
	} else {
		$newSlugList = array();
	};
	$newSlugList[$cutSlug] = array(
		'createdAt' => $cutSlugsTime,
		'cutBoolean' => true,
		'VODBase' => $cutSlugsVODBase,
		'VODTime' => $cutSlugsVODTimestamp,
		'likeIDs' => 0
	);
	if ( $cutSlugScope === 'everyone' ) {
		$updateSuccess = update_post_meta($gardenID, 'slugList', $newSlugList);
	} else {
		$updateSuccess = update_user_meta($userID, 'slugList', $newSlugList);
	};
	echo json_encode($updateSuccess);
	wp_die();
}

add_action( 'wp_ajax_secret_garden_grow', 'secret_garden_grow' );
add_action( 'wp_ajax_nopriv_secret_garden_grow', 'secret_garden_grow' );

function secret_garden_grow() {
	$growSlug = $_POST['growSlug'];
	$growTitle = $_POST['growTitle'];
	$growSourceRaw = $_POST['growSource'];
	$growSourceFull = 'https://www.twitch.tv/' . $growSourceRaw;
	$growVotersRaw = '"' . $_POST['growVoters'] . '"';
	$growVoters = json_decode($growVotersRaw);
	$term_args = array(
		'taxonomy' => 'source'
	);
	$sources = get_terms( $term_args );
	foreach ($sources as $source) {
		$key = get_term_meta( $source->term_id, 'twitch', true);
		if ( $key == $growSourceFull ) {
			$growSource = $source->term_id;
		}
	}
	$voteCount = 0;
	$voteledger = array();
	if (is_string($growVoters)) {
		$voterID = substr($growVoters, 2, -2);
		$voterRep = get_user_meta($voterID, 'rep', true);
		if ( $voterRep === '' ) {$voterRep = 1;};
		$voteledger[$voterID] = $voterRep;
		$voteCount = $voteCount + $voterRep;
	} elseif (is_array($growVoters)) {
		foreach ($growVoters as $voter) {
			$voterRep = get_user_meta($voter, 'rep', true);
			if ( $voterRep == '' ) {$voterRep = 1;};
			$voteledger[$voter] = $voterRep;
			$voteCount = $voteCount + $voterRep;
		}
	} elseif (is_null($growVoters)) {$voteCount = $growVotersRaw;};
	$seedArray = array(
		'post_title' => $growTitle,
		'post_content' => '',
		'post_excerpt' => '',
		'tax_input' => array(
			'source' => $growSource,
			),
		'meta_input' => array(
			'TwitchCode' => $growSlug,
			'voteledger' => $voteledger,
			'votecount' => $voteCount
			),
	);
	$didPost = wp_insert_post($seedArray, true);
	echo json_encode($didPost);
	wp_die();
}

add_action( 'wp_ajax_secret_garden_vote', 'secret_garden_vote' );
add_action( 'wp_ajax_nopriv_secret_garden_vote', 'secret_garden_vote' );

function secret_garden_vote() {
	$voteSlug = $_POST['voteSlug'];
	$voteSlugsTime = $_POST['voteSlugsTime'];
	$voteSlugsVODBase = $_POST['voteSlugsVODBase'];
	$voteSlugsVODTimestamp = $_POST['voteSlugsVODTimestamp'];
	$voteSlugUser = $_POST['voteSlugScope'];
	$gardenPostObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPostObject->ID;
	$oldSlugList = get_post_meta($gardenID, 'slugList', true);
	if ( !empty($oldSlugList) ) {
		$newSlugList = $oldSlugList;
	} else {
		$newSlugList = array();
	};
	if ( array_key_exists($voteSlug, $newSlugList) ) {
		$oldLikes = $newSlugList[$voteSlug]['likeIDs'];
		if ($oldLikes === 0) {
			echo "This clip has already been cut";
			wp_die();
		} else {
			$newLikes = $oldLikes;
			$newLikes[] = $voteSlugUser;
			$newSlugList[$voteSlug]['likeIDs'] = $newLikes;
		}
	} else {
		$newSlugList[$voteSlug] = array(
			'createdAt' => $voteSlugsTime,
			'cutBoolean' => false,
			'VODBase' => $voteSlugsVODBase,
			'VODTime' => $voteSlugsVODTimestamp,
			'likeIDs' => array($voteSlugUser)
		);
	}
	$updateSuccess = update_post_meta($gardenID, 'slugList', $newSlugList);
	echo json_encode($updateSuccess);
	wp_die();
};

?>