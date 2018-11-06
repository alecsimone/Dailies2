<?php

add_action('publish_post', 'set_default_custom_fields');
function set_default_custom_fields($ID){
	global $wpdb;
    if( !wp_is_post_revision($ID) ) {add_post_meta($ID, 'votecount', 0, true);};
};

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
	buildPostDataObject($postID);
	echo json_encode($newScore);
	wp_die();
}

// add_action( 'wp_ajax_submitClip', 'submitClip' );
// function submitClip () {
// 	if (!is_user_logged_in()) {
// 		wp_die("You have to be logged in to submit");
// 	}

// 	// killAjaxFunction("Clip posting temporarily on hold");

// 	$newSeedlingTitle = substr(sanitize_text_field($_POST['title']), 0, 80);
// 	$newSeedlingUrl = substr(esc_url($_POST['url']), 0, 140);

// 	$starID = starChecker($newSeedlingTitle);

// 	$clipTax = array(
// 		'stars' => $starID,
// 	);

// 	$clipMeta = array();

// 	$clipType = clipTypeDetector($newSeedlingUrl);

// 	if ($clipType === 'twitch') {
// 		$twitchCodePosition = strpos($newSeedlingUrl, 'twitch.tv/') + 10;
// 		if (strpos($newSeedlingUrl, '?')) {
// 			$twitchCodeEnd = strpos($newSeedlingUrl, '?');
// 			$twitchCodeLength = $twitchCodeEnd - $twitchCodePosition;
// 			$twitchCode = substr($newSeedlingUrl, $twitchCodePosition, $twitchCodeLength);
// 			$newSeedlingUrl = substr($newSeedlingUrl, 0, $twitchCodeEnd);
// 		} else {
// 			$twitchCode = substr($newSeedlingUrl, $twitchCodePosition);
// 		}
// 		$clipMeta['TwitchCode'] = $twitchCode;
// 	} elseif ($clipType === 'youtube') {
// 		$youtubeCodePosition = strpos($newSeedlingUrl, 'youtube.com/watch?v=') + 20;
// 		if (strpos($newSeedlingUrl, '&')) {
// 			$youtubeCodeEndPosition = strpos($newSeedlingUrl, '&');
// 			$youtubeCodeLength = $youtubeCodeEndPosition - $youtubeCodePosition;
// 			$youtubeCode = substr($newSeedlingUrl, $youtubeCodePosition, $youtubeCodeLength);
// 			$newSeedlingUrl = substr($newSeedlingUrl, 0, $youtubeCodeEndPosition);
// 		} else {
// 			$youtubeCode = substr($newSeedlingUrl, $youtubeCodePosition);
// 		}
// 		$clipMeta['YouTubeCode'] = $youtubeCode;
// 	} elseif ($clipType === 'ytbe') {
// 		$youtubeCodePosition = strpos($newSeedlingUrl, 'youtu.be/') + 9;
// 		if (strpos($newSeedlingUrl, '?')) {
// 			$youtubeCodeEndPosition = strpos($newSeedlingUrl, '?');
// 			$youtubeCodeLength = $youtubeCodeEndPosition - $youtubeCodePosition;
// 			$youtubeCode = substr($newSeedlingUrl, $youtubeCodePosition, $youtubeCodeLength);
// 			$newSeedlingUrl = substr($newSeedlingUrl, 0, $youtubeCodeEndPosition);
// 		} else {
// 			$youtubeCode = substr($newSeedlingUrl, $youtubeCodePosition);
// 		}
// 		$clipMeta['YouTubeCode'] = $youtubeCode;
// 	} elseif ($clipType === 'twitter') {
// 		$twitterCodePosition = strpos($newSeedlingUrl, '/status/') + 8;
// 		$twitterCode = substr($newSeedlingUrl, $twitterCodePosition);
// 		$clipMeta['TwitterCode'] = $twitterCode;
// 	} elseif ($clipType === 'gfycat') {
// 		$gfyCode = turnGfycatURLIntoGfycode($newSeedlingUrl);
// 		$clipMeta['GFYtitle'] = $gfyCode;
// 	} else {
// 		killAjaxFunction("Invalid URL");
// 	}

// 	$submitterID = get_current_user_id();
// 	$submitter = get_user_meta($submitterID, 'nickname', true);

// 	$time = time();

// 	if ($twitchCode) {
// 		$slug = $twitchCode;
// 	} elseif ($youtubeCode) {
// 		$slug = $youtubeCode;
// 	} elseif ($twitterCode) {
// 		$slug = $twitterCode;
// 	} elseif ($gfyCode) {
// 		$slug = $gfyCode;
// 	}

// 	$clipArray = array(
// 		'slug' => $slug,
// 		'title' => $newSeedlingTitle,
// 		'views' => 0,
// 		'age' => date('c'),
// 		'source' => "User Submit",
// 		'sourcepic' => 'unknown',
// 		'vodlink' => 'none',
// 		'thumb' => 'none',
// 		'clipper' => $submitter,
// 		'votecount' => 0,
// 		'score' => 0,
// 		'nuked' => 0,
// 		'type' => $clipType,
// 	);

// 	$existingSlug = getSlugInPulledClipsDB($slug);
// 	if ($existingSlug !== null) {
// 		killAjaxFunction("That clip has already been submitted");
// 	} else {
// 		$addSlugSuccess = addSlugToDB($clipArray);
// 	}


// 	// $seedlingArray = array(
// 	// 	'clipURL' => $newSeedlingUrl,
// 	// 	'post_title' => $newSeedlingTitle,
// 	// 	'sourcePic' => 'default',
// 	// 	'sourceURL' => '',
// 	// 	'submitter' => $submitter,
// 	// 	'submitTime' => $time,
// 	// 	'tax_input' => $clipTax,
// 	// 	'meta_input' => $clipMeta,
// 	// );
	
// 	// $gardenPageObject = get_page_by_path('secret-garden');
// 	// $gardenID = $gardenPageObject->ID;

// 	// $submissionResult = addSeedling($seedlingArray);

// 	killAjaxFunction($addSlugSuccess);
// }

// function addSeedling($seedlingArray) {
// 	$gardenPageObject = get_page_by_path('secret-garden');
// 	$gardenID = $gardenPageObject->ID;

// 	$oldUserSubmits = get_post_meta($gardenID, 'userSubmitData', true);
// 	$newSubmissionURL = $seedlingArray['clipURL'];

// 	$newSubmissionAlreadyExists = false;
// 	foreach ($oldUserSubmits as $key => $value) {
// 		if ($value['clipURL'] === $newSubmissionURL) {
// 			$newSubmissionAlreadyExists = true;
// 		}
// 	}

// 	if ($newSubmissionAlreadyExists) {
// 		return "That clip has already been submitted";
// 	} else {
// 		$oldUserSubmits[] = $seedlingArray;
// 		$newUserSubmits = update_post_meta( $gardenID, 'userSubmitData', $oldUserSubmits);
// 		return "Clip added!";
// 	}
// }

?>