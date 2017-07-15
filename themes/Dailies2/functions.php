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
		$nonce = wp_create_nonce('main_script_nonce');
		$main_script_data = array(
			'nonce' => $nonce,
		);
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
		wp_enqueue_script( 'liveScripts', get_template_directory_uri() . '/Bundles/live-bundle.js', ['jquery'], '', true );
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
	//$postDataObject['votecount'] = get_post_meta($id, 'votecount', true);
	$postDataObject['EmbedCodes'] = array(
		'TwitchCode' => get_post_meta($id, 'TwitchCode', true),
		'GFYtitle' => get_post_meta($id, 'GFYtitle', true),
		'YouTubeCode' => get_post_meta($id, 'YouTubeCode', true),
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
	$postDataBlob = json_encode($postDataObject);
	update_post_meta( $id, 'postDataObj', $postDataBlob);
}

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

add_action( 'wp_ajax_official_vote', 'official_vote' );
add_action( 'wp_ajax_nopriv_official_vote', 'official_vote' );

function official_vote() {
	$nonce = $_POST['vote_nonce'];
	if (!wp_verify_nonce($nonce, 'main_script_nonce')) {
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
	echo json_encode($return);
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

function generateUserData() {
	$userID = get_current_user_id();
	$userRep = get_user_meta($userID, 'rep', true);
	$userRepTime = get_user_meta($userID, 'repVotes', true);
	$userData = array(
		'userID' => $userID,
		'userRep' => $userRep,
		'userRepTime' => $userRepTime,
	);
	return $userData;
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

?>