<?php 

add_theme_support( 'post-thumbnails' );
add_image_size('small', 350, 800);
add_theme_support( 'title-tag' );

$thisDomain = get_site_url();

add_action("wp_enqueue_scripts", "script_setup");
function script_setup() {
	$version = '-v1.3';
	wp_register_script('globalScripts', get_template_directory_uri() . '/Bundles/global-bundle' . $version . '.js', ['jquery'], '', true );
	$thisDomain = get_site_url();
	$global_data = array(
		'thisDomain' => $thisDomain,
		'userData' => generateUserData(),
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'logoutURL' => wp_logout_url(),
		'submissionsOpen' => checkSubmissionOpenness(),
	);
	wp_localize_script( 'globalScripts', 'dailiesGlobalData', $global_data );
	wp_enqueue_script( 'globalScripts' );
	wp_enqueue_style( 'globalStyles', get_template_directory_uri() . '/style' . $version . '.css');
	if ( !is_page() && !is_attachment() ) {
		wp_register_script( 'mainScripts', get_template_directory_uri() . '/Bundles/main-bundle' . $version . '.js', ['jquery'], '', true );
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
		wp_register_script('secretGardenScripts', get_template_directory_uri() . '/Bundles/secretGarden-bundle' . $version . '.js', ['jquery'], '', true);
		include( locate_template('schedule.php') );
		$gardenPostObject = get_page_by_path('secret-garden');
		$gardenID = $gardenPostObject->ID;
		$gardenQueryHours = get_post_meta($gardenID, 'queryHours', true);
		$secretGardenData = array(
			'streamList' => generateStreamList(),
			'cutSlugs' => generateCutSlugs(),
			'submissionSeedlings' => generateSubmissionSeedlingsData(),
			'currentDay' => $todaysSchedule,
			'queryHours' => $gardenQueryHours,
		);
		wp_localize_script('secretGardenScripts', 'gardenData', $secretGardenData);
		wp_enqueue_script('secretGardenScripts');
	} else if (is_page('Live')) {
		wp_register_script( 'liveScripts', get_template_directory_uri() . '/Bundles/live-bundle' . $version . '.js', ['jquery'], '', true );
		$nonce = wp_create_nonce('vote_nonce');
		$livePageObject = get_page_by_path('live');
		$liveID = $livePageObject->ID;
		$resetTime = get_post_meta($liveID, 'liveResetTime', true);
		$resetTime = $resetTime / 1000 - 21600;
		$wordpressUsableTime = date('c', $resetTime);
		$liveData = array(
			'nonce' => $nonce,
			'channels' => generateChannelChangerData(),
			'postData' => generateLivePostsData(),
			'voteData' => generateLiveVoteData(),
			'cohosts' => generateCohostData(),
			'resetTime' => $resetTime,
			'wordpressUsableTime' => $wordpressUsableTime,
		);
		wp_localize_script('liveScripts', 'liveData', $liveData);
		wp_enqueue_script('liveScripts');
		wp_enqueue_script('isotope', 'http://dailies.gg/wp-content/themes/Dailies2/js/isotope.pkgd.min.js');
	} else if (is_page('Submit')) {
		wp_enqueue_script( 'scheduleScripts', get_template_directory_uri() . '/Bundles/submit-bundle' . $version . '.js', ['jquery'], '', true );
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

add_filter('rest_endpoints', 'my_modify_rest_routes');
function my_modify_rest_routes( $routes ) {
  array_push( $routes['/wp/v2/posts'][0]['args']['orderby']['enum'], 'meta_value_num' );
  return $routes;
}

// add custom fields query to WP REST API v2
// https://1fix.io/blog/2015/07/20/query-vars-wp-api/
function my_allow_meta_query( $valid_vars ) {
    $valid_vars = array_merge( $valid_vars, array( 'meta_key', 'meta_value' ) );
    return $valid_vars;
}
add_filter( 'rest_query_vars', 'my_allow_meta_query' );

//add_action('edit_post', 'buildPostDataObject', 10, 1);
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
	$authorDefaultPicture = wsl_get_user_custom_avatar( $authorID );
	$authorCustomPicture = get_user_meta($authorID, 'customProfilePic', true);
	if ($authorCustomPicture === '') {
		$authorPic = $authorDefaultPicture;
	} else {
		$authorPic = $authorCustomPicture;
	}
	$postDataObject['author'] = array(
		'id' => $authorID,
		'name' => get_user_meta($authorID, 'nickname', true),
		'logo' => $authorPic, 
	);
	$postDataObject['votecount'] = get_post_meta($id, 'votecount', true);
	if ($postDataObject['votecount'] === '') {$postDataObject['votecount'] = 0;}
	$postDataObject['voteledger'] = get_post_meta($id, 'voteledger', true);
	if ($postDataObject['voteledger'] === '' || $postDataObject['voteledger'] === []) {
		$postDataObject['voteledger'] = [];
		$postDataObject['voterData'] = [];
	}
	foreach ($postDataObject['voteledger'] as $voterID => $votedRep) {
		$voterName = get_user_meta($voterID, 'nickname', true);
		$voterDefaultPicture = wsl_get_user_custom_avatar( $voterID );
		$voterCustomPicture = get_user_meta($voterID, 'customProfilePic', true);
		if ($voterCustomPicture === '') {
			$voterPic = $voterDefaultPicture;
		} else {
			$voterPic = $voterCustomPicture;
		}
		$postDataObject['voterData'][$voterID] = array(
			'name' => $voterName,
			'picture' => $voterPic,
		);
	}
	$postDataObject['guestlist'] = get_post_meta($id, 'guestlist', true);
	$postDataObject['EmbedCodes'] = array(
		'TwitchCode' => get_post_meta($id, 'TwitchCode', true),
		'GFYtitle' => get_post_meta($id, 'GFYtitle', true),
		'YouTubeCode' => get_post_meta($id, 'YouTubeCode', true),
		'TwitterCode' => get_post_meta($id, 'TwitterCode', true),
		'EmbedCode' => get_post_meta($id, 'EmbedCode', true),
	);
	$allCatData = get_the_category($id);
	$postDataObject['categories'] = $allCatData[0]->cat_name;
	$postDataObject['taxonomies'] = array(
		'tags' => get_the_terms($id, 'post_tag'),
		'skills' => get_the_terms($id, 'skills'),
	);
	$stars = get_the_terms($id, 'stars');
	if ($stars !== false) {
		foreach ($stars as $star) {
			$postDataObject['taxonomies']['stars'][] = array(
				'name' => $star->name,
				'logo' => get_term_meta($star->term_taxonomy_id, 'logo', true),
				'slug' => $star->slug,
			);
		}
	} else {
		$postDataObject['taxonomies']['stars'][] = array();
	}
	$source = get_the_terms($id, 'source');
	if ($source !== false) {
		foreach ($source as $singleSource) {
			$sourcepicMetaValue = get_post_meta($id, 'sourcepic', true);
			$sourceLogo = get_term_meta($singleSource->term_taxonomy_id, 'logo', true);
			if ($singleSource->slug === 'user-submits' && $sourcepicMetaValue != '') {
				$sourceLogoToUse = $sourcepicMetaValue;
			}  else {
				$sourceLogoToUse = $sourceLogo;
			}
			$postDataObject['taxonomies']['source'][] = array(
				'name' => $singleSource->name,
				'logo' => $sourceLogoToUse,
				'slug' => $singleSource->slug,
			);
		}
	} else {
		$postDataObject['taxonomies']['source'][] = array(
			'name' => 'User Submits',
			'logo' => get_term_meta(632, 'logo', true),
			'slug' => 'user-submits',
		);
	}
	$postDataObject['playCount'] =  get_post_meta($id, 'fullClipViewcount', true);
	$postDataObject['addedScore'] =  get_post_meta($id, 'addedScore', true);
	//$postDataBlob = html_entity_decode(json_encode($postDataObject, JSON_HEX_QUOT));
	//update_post_meta( $id, 'postDataObj', $postDataBlob);
	return $postDataObject;
}
add_action( 'rest_api_init', 'dailies_add_extra_data_to_rest' );
function dailies_add_extra_data_to_rest() {
	register_rest_field('post', 'postDataObj', array(
			'get_callback' => function($postData) {
				$thisID = $postData[id];
				$postDataObj = buildPostDataObject($thisID);
				return $postDataObj;
			},
		)
	);
};

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
		if ($rep == '') {$rep = 10;}

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
				$newRep = $rep + 1;
				//$repVotes[$postID] = $currentTime;
				$repVotes = array(
					$postID => $currentTime
				);
				increase_rep($userID, 1);
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
		
	$guestRep = 1;
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

function increase_rep($userID, $additionalRep) {
	$currentRep = get_user_meta($userID, 'rep', true);
	$newRep = $currentRep + $additionalRep;
	update_user_meta( $userID, 'rep', $newRep);
	role_check($userID);
}

function role_check($userID) {
	$currentRep = intval(get_user_meta($userID, 'rep', true));
	$currentRoles = get_userdata($userID)->roles;
	$currentRole = $currentRoles[0];
	if ($currentRep >= 20 && $currentRole == 'subscriber') {
		wp_update_user(
			array(
				'ID' => $userID,
				'role' => 'contributor'
			) 
		);
	} elseif ($currentRep < 20 && $currentRole == 'contributor') {
		wp_update_user(
			array(
				'ID' => $userID,
				'role' => 'subscriber'
			) 
		);
	}
}

//These  are commented out because they only need to be run once, but I still want a record of them.
//$role = get_role( 'contributor' );
//$role->add_cap( 'publish_posts' ); 
//$role->add_cap( 'edit_published_posts' ); 

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
	$authorID = get_post_field('post_author', $postID);
	increase_rep($authorID, 2);
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
	buildPostDataObject($postID);
	echo json_encode($newScore);
	wp_die();
}

add_action( 'wp_ajax_cut_slug', 'cut_slug' );

function cut_slug() {
	$slugObj = $_POST['slugObj'];
	$scope = $_POST['scope'];
	if ($scope === "all") {
		$userID = get_current_user_id();
		$userName = get_user_meta($userID, 'nickname', true);
		$userDataObject = get_userdata($userID);
		$userRole = $userDataObject->roles[0];
		if ($userRole ===  'administrator' || $userRole === 'editor' || $userRole === 'author') {
			$gardenPostObject = get_page_by_path('secret-garden');
			$gardenID = $gardenPostObject->ID;
			$globalSlugList = get_post_meta($gardenID, 'slugList', true);
			$newGlobalSlugList = $globalSlugList;
			$newSlug = $slugObj['slug'];
			$newGlobalSlugList[$newSlug] = $slugObj;
			$newGlobalSlugList[$newSlug]['Nuker'] = $userName;
			update_post_meta($gardenID, 'slugList', $newGlobalSlugList );
			echo json_encode($newGlobalSlugList);
		} else {
			wp_die("You can't do that!");
		}
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

add_action( 'wp_ajax_nuke_slug', 'nuke_slug' );

function nuke_slug() {
	$gardenPostObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPostObject->ID;
	$globalSlugList = get_post_meta($gardenID, 'slugList', true);
	$newGlobalSlugList = $globalSlugList;
	$slugObj = $_POST['slugObj'];
	$slug = $slugObj['slug'];
	$newGlobalSlugList[$slug] = $slugObj;
	update_post_meta($gardenID, 'slugList', $newGlobalSlugList );
	//echo json_encode($newGlobalSlugList);
	wp_die();
}

add_action( 'wp_ajax_vote_slug', 'vote_slug' );

function vote_slug() {
	$userID = get_current_user_id();
	$slugObj = $_POST['slugObj'];
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

/*	$gardenPostObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPostObject->ID;
	$backupSlugList = get_post_meta($gardenID, 'backupSlugList', true);
	update_post_meta($gardenID, 'slugList', $backupSlugList);
*/

add_action( 'wp_ajax_tag_slug', 'tag_slug' );
function tag_slug() {
	$userID = get_current_user_id();
	$userDataObject = get_userdata($userID);
	$userRole = $userDataObject->roles[0];
	if ($userRole ===  'administrator' || $userRole === 'editor' || $userRole === 'author') {
		$tagObj = $_POST['tagObj'];
		$slugToTag = $tagObj['slugToTag'];
		$createdAt = $tagObj['createdAt'];
		$gardenPostObject = get_page_by_path('secret-garden');
		$gardenID = $gardenPostObject->ID;
		$globalSlugList = get_post_meta($gardenID, 'slugList', true);
		if (array_key_exists($slugToTag, $globalSlugList)) {
			if (!isset($globalSlugList[$slugToTag]['tags'])) {
				$globalSlugList[$slugToTag]['tags'] = array();
			}
			foreach ($tagObj['tags'] as $index => $tag) {
				array_push($globalSlugList[$slugToTag]['tags'], $tag);	
			}
		} else {
			$globalSlugList[$slugToTag]['tags'] = $tagObj['tags'];
			$globalSlugList[$slugToTag]['createdAt'] = $createdAt;
			$globalSlugList[$slugToTag]['cutBoolean'] = 'false';
			$globalSlugList[$slugToTag]['slug'] = $slugToTag;
			$globalSlugList[$slugToTag]['likeIDs'] = '';
			$globalSlugList[$slugToTag]['VODBase'] = $tagObj['VODBase'];
			$globalSlugList[$slugToTag]['VODTime'] = $tagObj['VODTime'];
		}
		update_post_meta($gardenID, 'slugList', $globalSlugList);
		echo json_encode($globalSlugList);
	}
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
	$votecount = 0;
	$voteledger = array();
	$voters = $slugObj['likeIDs'];
	foreach ($voters as $index => $voterID) {
		$voterRep = get_user_meta($voterID, 'rep', true);
		if ($voterRep === '') {$voterRep = 10;};
		$voteledger[$voterID] = $voterRep;
		$votecount = $votecount + $voterRep;
	}
	$seedArray = array(
		'post_title' => $thingTitle,
		'post_content' => '',
		'post_excerpt' => '',
		'post_status' => 'publish',
		'tax_input' => array(
			'source' => $growSource,
			'stars' => $postStar,
			),
		'meta_input' => array(
			'TwitchCode' => $slugObj['slug'],
			'voteledger' => $voteledger,
			'votecount' => $votecount,
			),
	);
	$didPost = wp_insert_post($seedArray, true);
	echo json_encode($didPost);
	wp_die();
}

function post_trasher() {
	$postID = $_POST['id'];
	if (current_user_can('delete_published_posts', $postID)) {
		wp_trash_post($postID);
		$authorID = get_post_field('post_author', $postID);
		increase_rep($authorID, -1);
	};
	echo json_encode($postID);
	wp_die();
}

add_action( 'wp_ajax_post_promoter', 'post_promoter' );
function post_promoter() {
	$postID = $_POST['id'];
	if (current_user_can('edit_others_posts', $postID)) {
		$category_list = get_the_category($postID);
		$category_name = $category_list[0]->cat_name;
		$authorID = get_post_field('post_author', $postID);
		if ($category_name === 'Prospects') {
			wp_remove_object_terms($postID, 'prospects', 'category');
			wp_add_object_terms( $postID, 'contenders', 'category' );
		} elseif ($category_name === 'Contenders') {
			wp_remove_object_terms($postID, 'contenders', 'category');
			wp_add_object_terms( $postID, 'nominees', 'category' );
		}
	};
	echo json_encode($postID);
	wp_die();
}

add_action( 'wp_ajax_post_demoter', 'post_demoter' );
function post_demoter() {
	$postID = $_POST['id'];
	if (current_user_can('edit_others_posts', $postID)) {
		$category_list = get_the_category($postID);
		$category_name = $category_list[0]->cat_name;
		$authorID = get_post_field('post_author', $postID);
		if ($category_name === 'Nominees') {
			wp_remove_object_terms($postID, 'nominees', 'category');
			wp_add_object_terms( $postID, 'contenders', 'category' );
		} elseif ($category_name === 'Contenders') {
			wp_remove_object_terms($postID, 'contenders', 'category');
			wp_add_object_terms( $postID, 'prospects', 'category' );
		} elseif ($category_name === 'Prospects') {
			post_trasher($postID);
		}
	};
}

add_action( 'wp_ajax_reset_live', 'reset_live' );
function reset_live() {
	$reset_time_to = $_POST['timestamp'];
	$livePageObject = get_page_by_path('live');
	$liveID = $livePageObject->ID;
	update_post_meta($liveID, 'liveResetTime', $reset_time_to);
	echo json_encode($reset_time_to);
	wp_die();
}

add_action( 'wp_ajax_submitClip', 'submitClip' );
add_action( 'wp_ajax_nopriv_submitClip', 'submitClip' );
function submitClip () {
	if (!is_user_logged_in()) {
		wp_die("You have to be logged in to submit");
	}

	$newSeedlingTitle = substr(sanitize_text_field($_POST['title']), 0, 80);
	$newSeedlingUrl = substr(esc_url($_POST['url']), 0, 140);

	$starID = starChecker($newSeedlingTitle);

	$clipTax = array(
		'stars' => $starID,
	);

	$clipMeta = array();

	$clipType = clipTypeDetector($newSeedlingUrl);

	if ($clipType === 'twitch') {
		$twitchCodePosition = strpos($newSeedlingUrl, 'twitch.tv/') + 10;
		if (strpos($newSeedlingUrl, '?')) {
			$twitchCodeEnd = strpos($newSeedlingUrl, '?');
			$twitchCodeLength = $twitchCodeEnd - $twitchCodePosition;
			$twitchCode = substr($newSeedlingUrl, $twitchCodePosition, $twitchCodeLength);
			$newSeedlingUrl = substr($newSeedlingUrl, 0, $twitchCodeEnd);
		} else {
			$twitchCode = substr($newSeedlingUrl, $twitchCodePosition);
		}
		$clipMeta['TwitchCode'] = $twitchCode;
	} elseif ($clipType === 'youtube') {
		$youtubeCodePosition = strpos($newSeedlingUrl, 'youtube.com/watch?v=') + 20;
		if (strpos($newSeedlingUrl, '&')) {
			$youtubeCodeEndPosition = strpos($newSeedlingUrl, '&');
			$youtubeCodeLength = $youtubeCodeEndPosition - $youtubeCodePosition;
			$youtubeCode = substr($newSeedlingUrl, $youtubeCodePosition, $youtubeCodeLength);
			$newSeedlingUrl = substr($newSeedlingUrl, 0, $youtubeCodeEndPosition);
		} else {
			$youtubeCode = substr($newSeedlingUrl, $youtubeCodePosition);
		}
		$clipMeta['YouTubeCode'] = $youtubeCode;
	} elseif ($clipType === 'ytbe') {
		$youtubeCodePosition = strpos($newSeedlingUrl, 'youtu.be/') + 9;
		if (strpos($newSeedlingUrl, '?')) {
			$youtubeCodeEndPosition = strpos($newSeedlingUrl, '?');
			$youtubeCodeLength = $youtubeCodeEndPosition - $youtubeCodePosition;
			$youtubeCode = substr($newSeedlingUrl, $youtubeCodePosition, $youtubeCodeLength);
			$newSeedlingUrl = substr($newSeedlingUrl, 0, $youtubeCodeEndPosition);
		} else {
			$youtubeCode = substr($newSeedlingUrl, $youtubeCodePosition);
		}
		$clipMeta['YouTubeCode'] = $youtubeCode;
	} elseif ($clipType === 'twitter') {
		$twitterCodePosition = strpos($newSeedlingUrl, '/status/') + 8;
		$twitterCode = substr($newSeedlingUrl, $twitterCodePosition);
		$clipMeta['TwitterCode'] = $twitterCode;
	} elseif ($clipType === 'gfycat') {
		if (strpos($newSeedlingUrl, '/detail/')) {
			$gfyCodePosition = strpos($newSeedlingUrl, '/detail/') + 8;
			if (strpos($newSeedlingURL, '?')) {
				$gfyCodeEndPosition = strpos($newSeedlingUrl, '?');
				$gfyCodeLength = $gfyCodeEndPosition - $gfyCodePosition;
				$gfyCode = substr($newSeedlingUrl, $gfyCodePosition, $gfyCodeLength);
				$newSeedlingUrl = substr($newSeedlingUrl, 0, $gfyCodeEndPosition);
			} else {
				$gfyCode = substr($newSeedlingUrl, $gfyCodePosition);
			}
		} else {
			$gfyCodePosition = strpos($newSeedlingUrl, 'gfycat.com/') + 11;
			if (strpos($newSeedlingURL, '?')) {
				$gfyCodeEndPosition = strpos($newSeedlingUrl, '?');
				$gfyCodeLength = $gfyCodeEndPosition - $gfyCodePosition;
				$gfyCode = substr($newSeedlingUrl, $gfyCodePosition, $gfyCodeLength);
				$newSeedlingUrl = substr($newSeedlingUrl, 0, $gfyCodeEndPosition);
			} else {
				$gfyCode = substr($newSeedlingUrl, $gfyCodePosition);
			}
		}
		$clipMeta['GFYtitle'] = $gfyCode;
	}

	$submitterID = get_current_user_id();
	$submitter = get_user_meta($submitterID, 'nickname', true);

	$time = time();

	$seedlingArray = array(
		'clipURL' => $newSeedlingUrl,
		'post_title' => $newSeedlingTitle,
		'sourcePic' => 'default',
		'sourceURL' => '',
		'submitter' => $submitter,
		'submitTime' => $time,
		'tax_input' => $clipTax,
		'meta_input' => $clipMeta,
	);
	
	$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;

	$submissionResult = addSeedling($seedlingArray);

	echo json_encode($submissionResult);
	wp_die();
}

/*$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;

	$oldUserSubmits = get_post_meta($gardenID, 'userSubmitData', true);
	print_r($oldUserSubmits);
*/
function addSeedling($seedlingArray) {
	$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;

	$oldUserSubmits = get_post_meta($gardenID, 'userSubmitData', true);
	$newSubmissionURL = $seedlingArray['clipURL'];

	$newSubmissionAlreadyExists = false;
	foreach ($oldUserSubmits as $key => $value) {
		if ($value['clipURL'] === $newSubmissionURL) {
			$newSubmissionAlreadyExists = true;
		}
	}

	if ($newSubmissionAlreadyExists) {
		return "That clip has already been submitted";
	} else {
		$oldUserSubmits[] = $seedlingArray;
		$newUserSubmits = update_post_meta( $gardenID, 'userSubmitData', $oldUserSubmits);
		return "Clip added!";
	}
}


add_action( 'wp_ajax_cutSubmission', 'cutSubmission' );
function cutSubmission() {
	$userID = get_current_user_id();
	$userDataObject = get_userdata($userID);
	$userRole = $userDataObject->roles[0];
	if ($userRole !== 'administrator') {
		wp_die("You are not an admin, sorry");
	}
	$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;
	$oldUserSubmits = get_post_meta($gardenID, 'userSubmitData', true);

	$deadSubmissionMetaInput = $_POST['metaInput'];
	$deadSubmissionMetaType = array_keys($deadSubmissionMetaInput)[0];
	$deadSubmissionMetaValue = $deadSubmissionMetaInput[$deadSubmissionMetaType];

	foreach ($oldUserSubmits as $key => $value) {
		if ($value['meta_input'][$deadSubmissionMetaType] === $deadSubmissionMetaValue) {
			unset($oldUserSubmits[$key]);
		}
	}

	$newUserSubmits = update_post_meta( $gardenID, 'userSubmitData', $oldUserSubmits);

	echo json_encode($oldUserSubmits);
	wp_die();
}

add_action( 'wp_ajax_addProspect', 'addProspect' );
function addProspect () {
	$userID = get_current_user_id();
	$userDataObject = get_userdata($userID);
	$userRole = $userDataObject->roles[0];
	if ($userRole !== 'administrator') {
		wp_die();
	}
	$newProspectTitle = substr(sanitize_text_field($_POST['title']), 0, 80);
	$newProspectUrl = substr(esc_url($_POST['url']), 0, 140);

	$starID = starChecker($newProspectTitle);

	$clipTax = array(
		'stars' => $starID,
	);

	$clipMeta = array();

	$clipType = clipTypeDetector($newProspectUrl);

	if ($clipType === 'twitch') {
		$twitchCodePosition = strpos($newProspectUrl, 'twitch.tv/') + 10;
		if (strpos($newProspectUrl, '?')) {
			$twitchCodeEnd = strpos($newProspectUrl, '?');
			$twitchCodeLength = $twitchCodeEnd - $twitchCodePosition;
			$twitchCode = substr($newProspectUrl, $twitchCodePosition, $twitchCodeLength);
		} else {
			$twitchCode = substr($newProspectUrl, $twitchCodePosition);
		}
		$clipMeta['TwitchCode'] = $twitchCode;
	} elseif ($clipType === 'youtube') {
		$youtubeCodePosition = strpos($newProspectUrl, 'youtube.com/watch?v=') + 20;
		if (strpos($newProspectUrl, '&')) {
			$youtubeCodeEndPosition = strpos($newProspectUrl, '&');
			$youtubeCodeLength = $youtubeCodeEndPosition - $youtubeCodePosition;
			$youtubeCode = substr($newProspectUrl, $youtubeCodePosition, $youtubeCodeLength);
		} else {
			$youtubeCode = substr($newProspectUrl, $youtubeCodePosition);
		}
		$clipMeta['YouTubeCode'] = $youtubeCode;
	} elseif ($clipType === 'ytbe') {
		$youtubeCodePosition = strpos($newProspectUrl, 'youtu.be/') + 9;
		if (strpos($newProspectUrl, '?')) {
			$youtubeCodeEndPosition = strpos($newProspectUrl, '?');
			$youtubeCodeLength = $youtubeCodeEndPosition - $youtubeCodePosition;
			$youtubeCode = substr($newProspectUrl, $youtubeCodePosition, $youtubeCodeLength);
		} else {
			$youtubeCode = substr($newProspectUrl, $youtubeCodePosition);
		}
		$clipMeta['YouTubeCode'] = $youtubeCode;
	} elseif ($clipType === 'twitter') {
		$twitterCodePosition = strpos($newProspectUrl, '/status/') + 8;
		$twitterCode = substr($newProspectUrl, $twitterCodePosition);
		$clipMeta['TwitterCode'] = $twitterCode;
	} elseif ($clipType === 'gfycat') {
		if (strpos($newProspectUrl, '/detail/')) {
			$gfyCodePosition = strpos($newProspectUrl, '/detail/') + 8;
			$gfyCode = substr($newProspectUrl, $gfyCodePosition);
		} else {
			$gfyCodePosition = strpos($newProspectUrl, 'gfycat.com/') + 11;
			$gfyCode = substr($newProspectUrl, $gfyCodePosition);
		}
		$clipMeta['GFYtitle'] = $gfyCode;
	}

	$prospectArray = array(
		'post_title' => $newProspectTitle,
		'post_content' => '',
		'post_excerpt' => '',
		'post_status' => 'publish',
		'tax_input' => $clipTax,
		'meta_input' => $clipMeta,
	);
	$didPost = wp_insert_post($prospectArray, true);

	echo json_encode($didPost);
	wp_die();
}

function clipTypeDetector($clipURLRaw) {
	$clipURL = strtolower($clipURLRaw);
	$isTwitch = strpos($clipURL, 'twitch');
	$isYouTube = strpos($clipURL, 'youtube');
	$isYtbe = strpos($clipURL, 'youtu.be');
	$isTwitter = strpos($clipURL, 'twitter');
	$isGfy = strpos($clipURL, 'gfycat');

	if ($isTwitch !== false ) {
		return 'twitch';
	} elseif ($isYouTube !== false) {
		return 'youtube';
	} elseif ($isYtbe !== false) {
		return 'ytbe';
	} elseif ($isTwitter !== false) {
		return 'twitter';
	} elseif ($isGfy !== false) {
		return 'gfycat';
	}
}

add_action( 'wp_ajax_gussyProspect', 'gussyProspect' );
function gussyProspect() {
	$channelURL = $_POST['channelURL'];
	$channelPic = $_POST['channelPic'];
	$postID = $_POST['postID'];
	$sourceID = sourceFinder($channelURL);
	wp_set_post_terms( $postID, $sourceID, 'source');
	update_post_meta( $postID, 'sourcepic', $channelPic);
	echo json_encode($sourceID);
	wp_die();
}

add_action( 'wp_ajax_gussySeedling', 'gussySeedling' );
function gussySeedling() {
	$channelURL = $_POST['channelURL'];
	$channelPic = $_POST['channelPic'];
	$vodlink = $_POST['VODLink'];
	$submissionURL = $_POST['postID'];
	$sourceID = sourceFinder($channelURL);

	$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;
	$allUserSubmits = get_post_meta($gardenID, 'userSubmitData', true);

	$clipType = clipTypeDetector($submissionURL);

	if ($clipType === 'twitch') {
		$parameterPosition = strpos($submissionURL, '?');
		if ($parameterPosition) {
			$submissionURL = substr($submissionURL, 0, $parameterPosition);
		}
	}

	$ourClipIndex = '';
	foreach ($allUserSubmits as $index => $submissionData) {
		if ($submissionData['clipURL'] === $submissionURL) {
			$ourClipIndex = $index;
		}
	}

	$allUserSubmits[$ourClipIndex]['sourcePic'] = $channelPic;
	$allUserSubmits[$ourClipIndex]['vodlink'] = $vodlink;
	update_post_meta($gardenID, 'userSubmitData', $allUserSubmits);

	echo json_encode($allUserSubmits[$ourClipIndex]);
	wp_die();
}

function sourceFinder($channelURL) {
	$sourceArgs = array(
		'taxonomy' => 'source'
	);
	$sources = get_terms($sourceArgs);
	$sourceID = 632; //632 is User Submits
	foreach ($sources as $source) {
		$key = get_term_meta($source->term_id, 'twitch', true);
		if (strcasecmp($key, $channelURL) == 0) {
			$sourceID = $source->term_id;
		}
	}
	return $sourceID;
}

function starChecker($thingTitle) {
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
	return $postStar;
}

function checkSubmissionOpenness() {
	$livePageObject = get_page_by_path('live');
	$liveID = $livePageObject->ID;
	$submissionOpenness = get_post_meta( $liveID, 'submissionOpenness', true);
	return $submissionOpenness;
}

add_action( 'wp_ajax_toggleSubmissions', 'toggleSubmissions' );
function toggleSubmissions () {
	$userID = get_current_user_id();
	$userDataObject = get_userdata($userID);
	$userRole = $userDataObject->roles[0];
	if ($userRole !== 'administrator') {
		wp_die();
	}
	$livePageObject = get_page_by_path('live');
	$liveID = $livePageObject->ID;
	$submissionOpenness = get_post_meta( $liveID, 'submissionOpenness', true);
	$intendedToggle = $_POST['intendedToggle'];
	if ($intendedToggle !== $submissionOpenness) {
		update_post_meta($liveID, 'submissionOpenness', $intendedToggle);
	}
	echo json_encode('submission openness toggled');
	wp_die();
}

function generateUserData() {
	$userID = get_current_user_id();
	$userRep = get_user_meta($userID, 'rep', true);
	$userRepTime = get_user_meta($userID, 'repVotes', true);
	$userDataObject = get_userdata($userID);
	$userRole = $userDataObject->roles[0];
	$userName = get_user_meta($userID, 'nickname', true);
	$userDefaultPicture = wsl_get_user_custom_avatar( $userID );
	$userCustomPicture = get_user_meta($userID, 'customProfilePic', true);
	if ($userCustomPicture === '') {
		$userPic = $userDefaultPicture;
	} else {
		$userPic = $userCustomPicture;
	}
	if ($userID === 0) {
		$userPic = get_site_url() . '/wp-content/uploads/2017/03/default_pic.jpg';
	}
	$userData = array(
		'userID' => $userID,
		'userName' => $userName,
		'userRep' => $userRep,
		'userRepTime' => $userRepTime,
		'userRole' => $userRole,
		'clientIP' => $_SERVER['REMOTE_ADDR'],
		'userPic' => $userPic,
	);
	return $userData;
}

function generateDayOneData() {
	date_default_timezone_set('UTC');
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
		$postData = buildPostDataObject($post->ID);
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
	$winnerDataObject = buildPostDataObject($post->ID);
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
		$postData = buildPostDataObject($post->ID);
		$initialPostDatas[] = $postData;
		$initialVoteDataArray[$post->ID] = array(
			'voteledger' => get_post_meta($post->ID, 'voteledger', true),
			'guestlist' => get_post_meta($post->ID, 'guestlist', true),
			'votecount' => get_post_meta($post->ID, 'votecount', true),
		);
	}
	$initialVoteData = $initialVoteDataArray;
	$initialPostData = $initialPostDatas;
	$orderby = get_query_var('orderby', 'date');
	if ($orderby = 'meta_value_num') {
		$orderby = 'meta_value_num&filter[meta_key]=votecount';
	}
	$initialArchiveData = array(
		'voteData' => $initialVoteData,
		'postData' => $initialPostData,
		'orderby' => $orderby,
		'order' => get_query_var('order', 'ASC'),
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
		$postData = buildPostDataObject($postID);
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
	$postData = buildPostDataObject($post->ID);
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
	//Check if there are any user submits, if there are add an entry streamList['user_submits']
	$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;
	$userSubmits = get_post_meta($gardenID, 'userSubmitData', true);
	if ($userSubmits !== '') {
		$streamList['User_Submits'] = array(
			'viewThreshold' => 0,
			'cursor' => 'none',
		);
	}
	$streamList['Cuts'] = array(
			'viewThreshold' => 0,
			'cursor' => 'none',
		);

	return $streamList;
}
add_action( 'wp_ajax_generateCutSlugsHandler', 'generateCutSlugsHandler' );
function generateCutSlugsHandler() {
	$cutSlugList = generateCutSlugs();
	echo json_encode($cutSlugList);
	wp_die();
}
function generateCutSlugs() {
	$gardenPostObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPostObject->ID;
	$globalSlugList = get_post_meta($gardenID, 'slugList', true);
	$queryHours = get_post_meta($gardenID, 'queryHours', true);
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
		if ($hoursAgo >= 168) {
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
		if ($hoursAgo >= 168) {
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

function generateSubmissionSeedlingsData() {
	$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;
	$submissionSeedlingData = get_post_meta($gardenID, 'userSubmitData', true);
	return $submissionSeedlingData;
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
	$livePageObject = get_page_by_path('live');
	$liveID = $livePageObject->ID;
	$resetTime = get_post_meta($liveID, 'liveResetTime', true);
	$resetTime = $resetTime / 1000 - 21600;
	$wordpressUsableTime = date('c', $resetTime);
	$livePostArgs = array(
		'category__not_in' => 4,
		'posts_per_page' => 50,
		'date_query' => array(
			array(
			//	'after' => '240 hours ago',
				'after' => $wordpressUsableTime,
			)
		)
	);
	$postDataLive = get_posts($livePostArgs);
	$postDatas = [];
	foreach ($postDataLive as $post) {
		$postID = $post->ID;
		$postDatas[$postID] = buildPostDataObject($postID, 'postDataObj', true);
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
	$cohosts = [];
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

add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>
 
    <h3>Extra profile information</h3> 
    <table class="form-table">
        <tr>
            <th><label for="rep">Rep</label></th>
            <td>
                <input type="text" name="rep" id="rep" value="<?php echo esc_attr( get_the_author_meta( 'rep', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description">Your Rep</span>
            </td>
        </tr>
    </table>
    <table class="form-table">
        <tr>
            <th><label for="customProfilePic">Custom Profile Picture</label></th>
            <td>
                <input type="text" name="customProfilePic" id="customProfilePic" value="<?php echo esc_attr( get_the_author_meta( 'customProfilePic', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description">Add a profile picture</span>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <th><label for="customProfilePic">Here's what that looks like: </label></th>
            <td>
            	<img src="<?php echo esc_attr( get_the_author_meta( 'customProfilePic', $user->ID ) ); ?>" class="adminCustomProfilePicture">
            </td>
        </tr>
    </table>    
<?php }

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );
add_action( 'admin_head', 'custom_profile_pic_css');
function custom_profile_pic_css() {
	echo '<style>img.adminCustomProfilePicture {max-width: 500px; height: auto;}</style>';
}


function my_save_extra_profile_fields( $user_id ) {
 
    if ( !current_user_can( 'edit_users', $user_id ) )
        return false;
    update_user_meta( absint( $user_id ), 'rep', wp_kses_post( $_POST['rep'] ) );
    update_user_meta( absint( $user_id ), 'customProfilePic', wp_kses_post( $_POST['customProfilePic'] ) );
}

/*$allUsers = get_users();
$testAccountRep = get_user_meta(337, 'rep', true);
if ($testAccountRep === '1.1') {
	foreach ($allUsers as $key => $data) {
		$userRep = get_user_meta($data->ID, 'rep', true);
		$newUserRep = $userRep * 10;
		update_user_meta($data->ID, 'rep', $newUserRep);
	}
}*/

?>