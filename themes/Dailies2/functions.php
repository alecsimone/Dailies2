<?php 

add_theme_support( 'post-thumbnails' );
add_image_size('small', 350, 800);
add_theme_support( 'title-tag' );

$thisDomain = get_site_url();

add_action("wp_enqueue_scripts", "script_setup");
function script_setup() {
	wp_register_script('globalScripts', get_template_directory_uri() . '/Bundles/global-bundle.js', ['jquery'], '', true );
	$thisDomain = get_site_url();
	$global_data = array(
		'thisDomain' => $thisDomain,
		'userData' => generateUserData(),
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'logoutURL' => wp_logout_url(),
	);
	wp_localize_script( 'globalScripts', 'dailiesGlobalData', $global_data );
	wp_enqueue_script( 'globalScripts' );
	wp_enqueue_style( 'globalStyles', get_template_directory_uri() . '/style.css');
	if ( !is_page() && !is_attachment() ) {
		wp_register_script( 'mainScripts', get_template_directory_uri() . '/Bundles/main-bundle.js', ['jquery'], '', true );
		$nonce = wp_create_nonce('vote_nonce');
		$main_script_data = array(
			'nonce' => $nonce,
		);
		if (is_home()) {
			$main_script_data['dayOne'] = generateDayOneData();
			$main_script_data['firstWinner'] = generateFirstWinner();
		} elseif (is_single()) {
			$main_script_data['singleData'] = generateSingleData();
		} elseif (is_search()) {
			$main_script_data['headerData'] = generateSearchHeaderData();
			$main_script_data['initialArchiveData'] = generateSearchResultsData();
		} else {
			$main_script_data['headerData'] = generateArchiveHeaderData();
			$main_script_data['initialArchiveData'] = generateInitialArchivePostData();
		}
		wp_localize_script('mainScripts', 'dailiesMainData', $main_script_data);
		wp_enqueue_script( 'mainScripts' );
	} else if (is_page('Secret Garden')) {
		wp_register_script('secretGardenScripts', get_template_directory_uri() . '/Bundles/secretGarden-bundle.js', ['jquery'], '', true);
		$secretGardenData = array(
			'streamList' => generateStreamList(),
			'cutSlugs' => generateCutSlugs(),
		);
		wp_localize_script('secretGardenScripts', 'gardenData', $secretGardenData);
		wp_enqueue_script('secretGardenScripts');
	} else if (is_page('Live')) {
		wp_register_script( 'liveScripts', get_template_directory_uri() . '/Bundles/live-bundle.js', ['jquery'], '', true );
		$nonce = wp_create_nonce('vote_nonce');
		$liveData = array(
			'nonce' => $nonce,
			'channels' => generateChannelChangerData(),
			'postData' => generateLivePostsData(),
			'voteData' => generateLiveVoteData(),
			'cohosts' => generateCohostData(),
		);
		wp_localize_script('liveScripts', 'liveData', $liveData);
		wp_enqueue_script('liveScripts');
		wp_enqueue_script('isotope', 'https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js');
	} /*else if (is_page('Schedule')) {
		wp_enqueue_script( 'scheduleScripts', get_template_directory_uri() . '/Bundles/schedule-bundle.js', ['jquery'], '', true );
	} */
}

function rest_get_post_meta_cb( $object, $field_name, $request ) {
    return get_post_meta( $object[ 'id' ], $field_name );
}
function rest_update_post_meta_cb( $value, $object, $field_name ) {
    return update_post_meta( $object[ 'id' ], $field_name, $value );
}

add_action( 'rest_api_init', function() {
	register_api_field( 'post',
		'postDataObj',
		array(
		   'get_callback'    => 'rest_get_post_meta_cb',
		   'update_callback' => 'rest_update_post_meta_cb',
		   'schema'          => null,
		)
	);
});
add_action( 'rest_api_init', function() {
	register_api_field( 'post',
		'votecount',
		array(
		   'get_callback'    => 'rest_get_post_meta_cb',
		   'update_callback' => 'rest_update_post_meta_cb',
		   'schema'          => null,
		)
	);
});
add_action( 'rest_api_init', function() {
	register_api_field( 'post',
		'voteledger',
		array(
		   'get_callback'    => 'rest_get_post_meta_cb',
		   'update_callback' => 'rest_update_post_meta_cb',
		   'schema'          => null,
		)
	);
});
add_action( 'rest_api_init', function() {
	register_api_field( 'post',
		'guestlist',
		array(
		   'get_callback'    => 'rest_get_post_meta_cb',
		   'update_callback' => 'rest_update_post_meta_cb',
		   'schema'          => null,
		)
	);
});

add_action('edit_post', 'buildPostDataObject', 10, 1);
function buildPostDataObject($id) {
	$postDataObject = [];
	$postDataObject['id'] = $id;
	$postDataObject['date'] = get_the_date('F jS, Y', $id);
	$postDataObject['link'] = get_permalink($id);
	$postDataObject['title'] = get_the_title($id);
	$postDataObject['thumbs'] = array(
		'small' => wp_get_attachment_image_src( get_post_thumbnail_id($id), 'small'),
		'medium' => wp_get_attachment_image_src( get_post_thumbnail_id($id), 'medium'),
		'large' => wp_get_attachment_image_src( get_post_thumbnail_id($id), 'large'),
	);
	$authorID = get_post_field('post_author', $id);
	$postDataObject['author'] = array(
		'id' => $authorID,
		'name' => get_user_meta($authorID, 'nickname', true),
		'logo' => get_user_meta($authorID, 'customProfilePic', true), 
	);
	$postDataObject['votecount'] = get_post_meta($id, 'votecount', true);
	if ($postDataObject['votecount'] === '') {$postDataObject['votecount'] = 0;}
	$postDataObject['voteledger'] = get_post_meta($id, 'voteledger', true);
	$postDataObject['guestlist'] = get_post_meta($id, 'guestlist', true);
	$postDataObject['EmbedCodes'] = array(
		'TwitchCode' => get_post_meta($id, 'TwitchCode', true),
		'GFYtitle' => get_post_meta($id, 'GFYtitle', true),
		'YouTubeCode' => get_post_meta($id, 'YouTubeCode', true),
		'TwitterCode' => get_post_meta($id, 'TwitterCode', true),
		'EmbedCode' => get_post_meta($id, 'EmbedCode', true),
	);
	$postDataObject['taxonomies'] = array(
		'tags' => get_the_terms($id, 'post_tag'),
		'skills' => get_the_terms($id, 'skills'),
	);
	$stars = get_the_terms($id, 'stars');
	foreach ($stars as $star) {
		$postDataObject['taxonomies']['stars'][] = array(
			'name' => $star->name,
			'logo' => get_term_meta($star->term_taxonomy_id, 'logo', true),
			'slug' => $star->slug,
		);
	}$source = get_the_terms($id, 'source');
	foreach ($source as $singleSource) {
		$postDataObject['taxonomies']['source'][] = array(
			'name' => $singleSource->name,
			'logo' => get_term_meta($singleSource->term_taxonomy_id, 'logo', true),
			'slug' => $singleSource->slug,
		);
	}
	//$postDataObject['voteledger'] = get_post_meta($id, 'voteledger', true);
	//$postDataObject['guestlist'] = get_post_meta($id, 'guestlist', true);
	$postDataObject['playCount'] =  get_post_meta($id, 'fullClipViewcount', true);
	$postDataObject['addedScore'] =  get_post_meta($id, 'addedScore', true);
	$postDataBlob = html_entity_decode(json_encode($postDataObject, JSON_HEX_QUOT));
	update_post_meta( $id, 'postDataObj', $postDataBlob);
}

add_action('publish_post', 'set_default_custom_fields');
function set_default_custom_fields($ID){
	global $wpdb;
    if( !wp_is_post_revision($ID) ) {add_post_meta($ID, 'votecount', 0, true);};
};

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
			//$month = $month - 1;
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

add_action( 'wp_ajax_official_vote', 'official_vote' );
add_action( 'wp_ajax_nopriv_official_vote', 'official_vote' );

function official_vote() {
	$nonce = $_POST['vote_nonce'];
	if (!wp_verify_nonce($nonce, 'vote_nonce')) {
		die("Busted!");
	}
	$postID = $_POST['id'];
	$return = '';

	if (is_user_logged_in()) {
		$userID = get_current_user_id();
		$rep = get_user_meta($userID, 'rep', true);
		if ($rep == '') {$rep = 1;}

		$oldVoteledger = get_post_meta($postID, 'voteledger', true);
		if (!array_key_exists($userID, $oldVoteledger)) {
			$voteledger = $oldVoteledger;
			$currentTime = time();
			$repVotes = get_user_meta($userID, 'repVotes', true);
			$repVotesKeys = array_keys($repVotes);
			$repVotesCount = count($repVotesKeys);
			if($repVotesCount >= 1) {
				$targetCount = $repVotesCount - 1;
				$targetKey = $repVotesKeys[$targetCount];
				$targetTime = $repVotes[$targetKey];
			} else {$targetTime = 0;}

			$addRepThreshold = $currentTime - (24 * 60 * 60);
			$return = array(
				'addRepThreshold' => $addRepThreshold,
				'currentTime' => $currentTime,
				'targetTime' => $targetTime,
			);
			if ($targetTime <= $addRepThreshold) {
				$newRep = $rep + .1;
				//$repVotes[$postID] = $currentTime;
				$repVotes = array(
					$postID => $currentTime
				);
				update_user_meta($userID, 'rep', $newRep);
				update_user_meta($userID, 'repVotes', $repVotes);
			} else {$newRep = $rep;}

			$voteledger[$userID] = $newRep;
			update_post_meta($postID, 'voteledger', $voteledger);

			$currentScore = get_post_meta($postID, 'votecount', true);
			$newScore = $currentScore + $newRep;
			update_post_meta($postID, 'votecount', $newScore);

			$oldVoteHistory = get_user_meta($userID, 'voteHistory', true);
			$newVoteHistory = $oldVoteHistory;
			$newVoteHistory[] = $postID;
			update_user_meta($userID, 'voteHistory', $newVoteHistory);
		} else {
			/* Transitioning to not losing rep when unvoting a repvote
			$repVotes = get_user_meta($userID, 'repVotes', true);
			if (array_key_exists($postID, $repVotes)) {
				$newRep = $rep - .1;
				update_user_meta($userID, 'rep', $newRep);
				unset($repVotes[$postID]);
				update_user_meta($userID, 'repVotes', $repVotes);
			} else {$newRep = $rep;} */

			$currentScore = get_post_meta($postID, 'votecount', true);
			$newScore = $currentScore - $oldVoteledger[$userID];
			update_post_meta($postID, 'votecount', $newScore);
			unset($oldVoteledger[$userID]);
			update_post_meta($postID, 'voteledger', $oldVoteledger);

			$oldVoteHistory = get_user_meta($userID, 'voteHistory', true);
			$newVoteHistory = $oldVoteHistory;
			$unvotedPostKey = array_search($postID, $newVoteHistory);
			unset($newVoteHistory[$unvotedPostKey]);
			update_user_meta($userID, 'voteHistory', $newVoteHistory);
		}
	}
		
	$guestRep = .1;
	$clientIP = $_SERVER['REMOTE_ADDR'];

	$oldGuestlist = get_post_meta($postID, 'guestlist', true);
	if (!in_array($clientIP, $oldGuestlist)) {
		if(!is_user_logged_in()) {
			$newGuestlist = $oldGuestlist;
			$newGuestlist[] = $clientIP;
			update_post_meta($postID, 'guestlist', $newGuestlist);

			$currentScore = get_post_meta($postID, 'votecount', true);
			$newScore = $currentScore + $guestRep;
			update_post_meta($postID, 'votecount', $newScore);
		}
	} else {
		$newGuestslist = $oldGuestlist;
		$guestKey = array_search($clientIP, $newGuestlist);
		unset($newGuestlist[$guestKey]);
		update_post_meta($postID, 'guestlist', $newGuestlist);

		$currentScore = get_post_meta($postID, 'votecount', true);
		$newScore = $currentScore - $guestRep;
		update_post_meta($postID, 'votecount', $newScore);
	}
	buildPostDataObject($postID);
	echo json_encode($newScore);
	wp_die();
}

add_action( 'wp_ajax_declare_winner', 'declare_winner' );
function declare_winner() {
	$nonce = $_POST['vote_nonce'];
	if (!wp_verify_nonce($nonce, 'vote_nonce')) {
		die("Busted!");
	}
	$postID = $_POST['id'];
	if (!current_user_can('edit_others_posts', $postID)) {
		die("You can't do that!");
	}
	wp_set_post_tags($postID, 'Winners', true);
	buildPostDataObject($postID);
	wp_die();
}

add_action( 'wp_ajax_add_score', 'add_score' );
function add_score() {
	$nonce = $_POST['vote_nonce'];
	if (!wp_verify_nonce($nonce, 'vote_nonce')) {
		die("Busted!");
	}
	$postID = $_POST['id'];
	if (!current_user_can('edit_others_posts', $postID)) {
		die("You can't do that!");
	}
	$currentScore = get_post_meta($postID, 'votecount', true);
	$scoreToAdd = $_POST['scoreToAdd'];
	$newScore = $currentScore + $scoreToAdd;
	update_post_meta($postID, 'votecount', $newScore);
	$oldAddedScore = get_post_meta($postID, 'addedScore', true);
	$newAddedScore = $oldAddedScore + $scoreToAdd;
	update_post_meta($postID, 'addedScore', $newAddedScore);
	echo json_encode($newScore);
	wp_die();
}

add_action( 'wp_ajax_cut_slug', 'cut_slug' );

function cut_slug() {
	$slugObj = $_POST['slugObj'];
	$scope = $_POST['scope'];
	if ($scope === "all") {
		$gardenPostObject = get_page_by_path('secret-garden');
		$gardenID = $gardenPostObject->ID;
		$globalSlugList = get_post_meta($gardenID, 'slugList', true);
		$newGlobalSlugList = $globalSlugList;
		$newSlug = $slugObj['slug'];
		$newGlobalSlugList[$newSlug] = $slugObj;
		update_post_meta($gardenID, 'slugList', $newGlobalSlugList );
	} else {
		$userID = $scope;
		$userSlugList = get_user_meta($userID, 'slugList', true);
		$newUserSlugList =  $userSlugList;
		$newSlug = $slugObj['slug'];
		$newUserSlugList[$newSlug] = $slugObj;
		update_user_meta($userID, 'slugList', $newUserSlugList);
		echo json_encode($newUserSlugList);
	}
	wp_die();
}

add_action( 'wp_ajax_vote_slug', 'vote_slug' );

function vote_slug() {
	$slugObj = $_POST['slugObj'];
	$userID = get_current_user_id();
	$userIDString = strval($userID);
	$gardenPostObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPostObject->ID;
	$globalSlugList = get_post_meta($gardenID, 'slugList', true);
	$newGlobalSlugList = $globalSlugList;
	$newSlug = $slugObj['slug'];
	if (array_key_exists($newSlug, $newGlobalSlugList)) {
		if (in_array($userIDString, $newGlobalSlugList[$newSlug]['likeIDs'])) {
			$yourIndex = array_search($userIDString, $newGlobalSlugList[$newSlug]['likeIDs']);
			unset($newGlobalSlugList[$newSlug]['likeIDs'][$yourIndex]);
		} else {
			$newGlobalSlugList[$newSlug]['likeIDs'][] = $userID;
		}
	} else {
		$newGlobalSlugList[$newSlug] = $slugObj;
	}
	echo json_encode($test);
	update_post_meta($gardenID, 'slugList', $newGlobalSlugList);
	wp_die();
}

add_action( 'wp_ajax_plant_seed', 'plant_seed' );

function plant_seed() {
	$slugObj = $_POST['slugObj'];
	$thingData = $_POST['thingData'];
	$gardenPostObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPostObject->ID;
	$globalSlugList = get_post_meta($gardenID, 'slugList', true);
	$newGlobalSlugList = $globalSlugList;
	$newSlug = $slugObj['slug'];
	if (array_key_exists($newSlug, $newGlobalSlugList)) {
		$newGlobalSlugList[$newSlug]['cutBoolean'] = true;
	} else {
		$newGlobalSlugList[$newSlug] = $slugObj;
	}
	update_post_meta($gardenID, 'slugList', $newGlobalSlugList);
	$thingSource = $thingData['source'];
	$thingTitle = $thingData['name'];
	$sourceArgs = array(
		'taxonomy' => 'source'
	);
	$sources = get_terms($sourceArgs);
	$growSource = 632;
	foreach ($sources as $source) {
		$key = get_term_meta($source->term_id, 'twitch', true);
		if (strcasecmp($key, $thingSource) == 0) {
			$growSource = $source->term_id;
		}
	}
	$titleWords = explode(" ", $thingTitle);
	$starNickname = strtolower($titleWords[0]);
	$starNickLength = strlen($starNickname);
	$star_args = array(
		'taxonomy' => 'stars',
	);
	$stars = get_terms($star_args);
	$postStar = 'X';
	$singleStar = false;
	foreach ($stars as $star) {
		$starSlug = $star->slug;
		$starShortSlug = substr($starSlug, 0, $starNickLength);
		if ($starShortSlug == $starNickname && !$singleStar) {
			$postStar = $star->term_id;
			$singleStar = true;
		}
	};
	$voteCount = 0;
	$voteledger = array();
	$voters = $slugObj['likeIDs'];
	foreach ($voters as $index => $voterID) {
		$voterRep = get_user_meta($voterID, 'rep', true);
		if ($voterRep === '') {$voterRep = 1;};
		$voteledger[$voterID] = $voterRep;
		$voteCount = $voteCount + $voterRep;
	}
	$seedArray = array(
		'post_title' => $thingTitle,
		'post_content' => '',
		'post_excerpt' => '',
		'tax_input' => array(
			'source' => $growSource,
			'stars' => $postStar,
			),
		'meta_input' => array(
			'TwitchCode' => $slugObj['slug'],
			'voteledger' => $voteledger,
			'voteCount' => $voteCount,
			),
	);
	$didPost = wp_insert_post($seedArray, true);
	echo json_encode($didPost);
	wp_die();
}

add_action( 'wp_ajax_post_trasher', 'post_trasher' );
function post_trasher() {
	$postID = $_POST['id'];
	if (current_user_can('delete_published_posts', $postID)) {
		wp_trash_post($postID);
	};
	echo json_encode($postID);
	wp_die();
}

function generateUserData() {
	$userID = get_current_user_id();
	$userRep = get_user_meta($userID, 'rep', true);
	$userRepTime = get_user_meta($userID, 'repVotes', true);
	$userData = array(
		'userID' => $userID,
		'userRep' => $userRep,
		'userRepTime' => $userRepTime,
		'clientIP' => $_SERVER['REMOTE_ADDR'],
	);
	return $userData;
}

function generateDayOneData() {
	date_default_timezone_set('America/Chicago');
	$today = new DateTime();
	$year = $today->format('Y');
	$month = $today->format('n');
	$day = $today->format('j');

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
		$today->add(DateInterval::createFromDateString('yesterday'));
		$year = $today->format('Y');
		$month = $today->format('n');
		$day = $today->format('j');
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
	$dayOnePostData = array(
		'date' => array(
			'year'  => $year,
			'month' => $month,
			'day'   => $day,
			),
		'postDatas' => $dayOnePostDatas,
		'voteDatas' => $dayOneVoteData,
		);
	return $dayOnePostData;
}

function generateFirstWinner() {
	$winnerArgs = array(
		'tag' => 'winners',
		'category_name' => 'noms',
		'posts_per_page' => 1,
		);
	$postDataWinners = get_posts($winnerArgs);
	$post = $postDataWinners[0];
	setup_postdata($post); 
	$winnerDataObject = get_post_meta($post->ID, 'postDataObj', true);
	$winnerVoteData = array(
		'voteledger' => get_post_meta($post->ID, 'voteledger', true),
		'guestlist' => get_post_meta($post->ID, 'guestlist', true),
		'votecount' => get_post_meta($post->ID, 'votecount', true),
		);
	$firstWinnerData = array(
		'voteData' => $winnerVoteData,
		'postData' => $winnerDataObject,
	);
	return $firstWinnerData;
}

function generateArchiveHeaderData() {
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
	return $headerData;
}

function generateSearchHeaderData() {
	$thisTerm = get_search_query();
	$headerData = array(
		'thisTerm' => $thisTerm,
	);
	return $headerData;
}

function generateInitialArchivePostData() {
	$thisTerm = get_queried_object();
	$orderby = get_query_var('orderby', 'date');
	$order = get_query_var('order', 'ASC');

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
	$initialVoteData = $initialVoteDataArray;
	$initialPostData = $initialPostDatas;
	$initialArchiveData = array(
		'voteData' => $initialVoteData,
		'postData' => $initialPostData,
	);
	return $initialArchiveData;
}

function generateSearchResultsData() {
	global $wp_query;
	$searchResultPostObjects = $wp_query->posts;
	$searchResultIDs = [];
	foreach ($searchResultPostObjects as $post) {
		$searchResultIDs[] = $post->ID;
	}
	$initialPostDatas = [];
	$initialVoteData = [];
	foreach ($searchResultIDs as $postID) {
		$postData = get_post_meta($postID, 'postDataObj', true);
		$initialPostDatas[] = $postData;
		$initialVoteData[$postID] = array(
			'voteledger' => get_post_meta($postID, 'voteledger', true),
			'guestlist' => get_post_meta($postID, 'guestlist', true),
			'votecount' => get_post_meta($postID, 'votecount', true),
		);
	}
	$initialSearchData = array(
		'voteData' => $initialVoteData,
		'postData' => $initialPostDatas,
	);
	return $initialSearchData;
}

function generateSingleData() {
	$post = get_post();
	$postData = get_post_meta($post->ID, 'postDataObj', true);
	$voteData[$post->ID] = array(
		'voteledger' => get_post_meta($post->ID, 'voteledger', true),
		'guestlist' => get_post_meta($post->ID, 'guestlist', true),
		'votecount' => get_post_meta($post->ID, 'votecount', true),
	);
	$singleData = array(
		'postData' => $postData,
		'voteData' => $voteData,
	);
	return $singleData;
}

function generateStreamList() {
	include( locate_template('schedule.php') );
	$todaysChannels = $schedule[$todaysSchedule];
	$streamList = array();
	foreach ($todaysChannels as $channel) {
		$twitchWholeURL = get_term_meta($channel[2], 'twitch', true);
		$twitchChannel = substr($twitchWholeURL, 22);
		$viewThreshold = get_term_meta($channel[2], 'viewThreshold', true);
		if ($viewThreshold == '') {$viewThreshold = "0";};
		$viewThresholds[$twitchChannel] = $viewThreshold;
		$streamList[$twitchChannel] = array(
			'viewThreshold' => $viewThreshold,
			'cursor' => 'none',
		);
	}
	return $streamList;
}
function generateCutSlugs() {
	$gardenPostObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPostObject->ID;
	$globalSlugList = get_post_meta($gardenID, 'slugList', true);
	if ($globalSlugList === '') {
		$globalSlugList = array();
	} elseif (empty($globalSlugList)) {
		$globalSlugList = array();
	};
	$globalSlugIndexes = array_keys($globalSlugList);
	foreach ($globalSlugIndexes as $slugIndex) {
		$currentTime = time();
		$slugTime = $globalSlugList[$slugIndex]['createdAt'];
		$timeAgo = ($currentTime * 1000) - $slugTime;
		$hoursAgo = $timeAgo / 1000 / 60 / 60;
		if ($hoursAgo > 24) {
			unset($globalSlugList[$slugIndex]);
		};
	};
	update_post_meta($gardenID, 'slugList', $globalSlugList);

	$userID = get_current_user_id();
	$userSlugList = get_user_meta($userID, 'slugList', true);
	if ($userSlugList === '') {
		$userSlugList = array();
	} elseif (empty($userSlugList)) {
		$userSlugList = array();
	};
	$userSlugIndexes = array_keys($userSlugList);
	foreach ($userSlugIndexes as $slugIndex) {
		$currentTime = time();
		$slugTime = $userSlugList[$slugIndex]['createdAt'];
		$timeAgo = ($currentTime * 1000) - $slugTime;
		$hoursAgo = $timeAgo / 1000 / 3600;
		if ($hoursAgo > 24) {
			unset($userSlugList[$slugIndex]);
		};
	};
	update_user_meta($userID, 'slugList', $userSlugList);

	$slugList = array_merge($globalSlugList, $userSlugList);
	foreach ($globalSlugIndexes as $slugIndex) {
		$slugLikes = $globalSlugList[$slugIndex]['likeIDs'];
		$slugList[$slugIndex]['likeIDs'] = $slugLikes;
	}

	return $slugList;
}

function generateChannelChangerData() {
	include( locate_template('schedule.php') );
	$todaysChannels = $schedule[$todaysSchedule];
	$channelData = [];
	foreach ($todaysChannels as $name => $channel) {
		$channelData[$name]['displayName'] = $channel[0];
		$channelData[$name]['slug'] = $channel[1];
		$channelData[$name]['logo'] = get_term_meta($channel[2], 'logo', true);
		$channelData[$name]['details'] = $channel[3];
		$channelData[$name]['time'] = $channel[4];
		$channelData[$name]['twitchURL'] = get_term_meta($channel[2], 'twitch', true);
		$channelData[$name]['active'] = false;
	}
	return $channelData;
}

function generateLivePostsData() {
	$livePostArgs = array(
		'category__not_in' => 4,
		'posts_per_page' => 50,
		'date_query' => array(
			array(
				'after' => '240 hours ago',
			)
		)
	);
	$postDataLive = get_posts($livePostArgs);
	$postDatas = [];
	foreach ($postDataLive as $post) {
		$postID = $post->ID;
		$postDatas[$postID] = get_post_meta($postID, 'postDataObj', true);
	}
	return $postDatas;
}

function generateLiveVoteData() {
	$livePostArgs = array(
		'category__not_in' => 4,
		'posts_per_page' => 50,
		'date_query' => array(
			array(
				'after' => '240 hours ago',
			)
		)
	);
	$postDataLive = get_posts($livePostArgs);
	$voteDatas = [];
	foreach ($postDataLive as $post) {
		$postID = $post->ID;
		$voteDatas[$postID] = array(
			'score' => get_post_meta($postID, 'votecount', true),
			'voteledger' => get_post_meta($postID, 'voteledger', true),
			'guestlist' => get_post_meta($postID, 'guestlist', true),
		);
	}
	return $voteDatas;
}

function generateCohostData() {
	//cohost slugs: 'dazerin', 'inanimatej', 'ninjarider'
	$cohosts = ['dazerin', 'inanimatej', 'ninjarider'];
	$cohostData = [];
	foreach ($cohosts as $cohost) {
		$hostObject = get_term_by('slug', $cohost, 'stars');
		$hostID = $hostObject->term_id;
		$cohostData[$cohost]['hostName'] = $hostObject->name;
		$cohostData[$cohost]['slug'] = $cohost;
		$cohostData[$cohost]['logo_url'] = get_term_meta($hostID, 'logo', true);
		$cohostData[$cohost]['links']['twitter_url'] = get_term_meta($hostID, 'twitter', true);
		$cohostData[$cohost]['links']['twitch_url'] = get_term_meta($hostID, 'twitch', true);
		$cohostData[$cohost]['links']['youtube_url'] = get_term_meta($hostID, 'youtube', true);
		$cohostData[$cohost]['links']['donate_url'] = get_term_meta($hostID, 'donate', true);
	}
	return $cohostData;
}

?>