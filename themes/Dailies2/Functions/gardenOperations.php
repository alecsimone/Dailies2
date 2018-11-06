<?php

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
			nukeSlug($slugObj['slug']);
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
		$userID = get_current_user_id();
		$userName = get_user_meta($userID, 'nickname', true);
		$userDataObject = get_userdata($userID);
		$userRole = $userDataObject->roles[0];
		if ($userRole ===  'administrator') {
			nukeSlug($slugObj['slug']);
		}
	}
	reset_chat_votes();
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
	nukeSlug($slug);
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
	nukeSlug($slugObj['slug']);
	// $gardenPostObject = get_page_by_path('secret-garden');
	// $gardenID = $gardenPostObject->ID;
	// $globalSlugList = get_post_meta($gardenID, 'slugList', true);
	// $newGlobalSlugList = $globalSlugList;
	// $newSlug = $slugObj['slug'];
	// if (array_key_exists($newSlug, $newGlobalSlugList)) {
	// 	$newGlobalSlugList[$newSlug]['cutBoolean'] = true;
	// } else {
	// 	$newGlobalSlugList[$newSlug] = $slugObj;
	// }
	// update_post_meta($gardenID, 'slugList', $newGlobalSlugList);

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
	if ($didPost > 0) {
		absorb_votes($didPost);
	}
	echo json_encode($didPost);
	wp_die();
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

	reset_chat_votes();
	echo json_encode($oldUserSubmits);
	wp_die();
}

?>