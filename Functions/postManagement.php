<?php

add_action('publish_post', 'set_default_custom_fields');
function set_default_custom_fields($ID){
	global $wpdb;
    if( !wp_is_post_revision($ID) ) {add_post_meta($ID, 'votecount', 0, true);};
};

add_action( 'wp_ajax_submitClip', 'submitClip' );
add_action( 'wp_ajax_nopriv_submitClip', 'submitClip' );
function submitClip () {
	if (!is_user_logged_in()) {
		wp_die("You have to be logged in to submit");
	}

	// killAjaxFunction("Clip posting temporarily on hold");

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
		$gfyCode = turnGfycatURLIntoGfycode($newSeedlingUrl);
		$clipMeta['GFYtitle'] = $gfyCode;
	} else {
		killAjaxFunction("Invalid URL");
	}

	$submitterID = get_current_user_id();
	$submitter = get_user_meta($submitterID, 'nickname', true);

	$time = time();

	if ($twitchCode) {
		$slug = $twitchCode;
	} elseif ($youtubeCode) {
		$slug = $youtubeCode;
	} elseif ($twitterCode) {
		$slug = $twitterCode;
	} elseif ($gfyCode) {
		$slug = $gfyCode;
	}

	$clipArray = array(
		'slug' => $slug,
		'title' => $newSeedlingTitle,
		'views' => 0,
		'age' => date('c'),
		'source' => "User Submit",
		'sourcepic' => 'unknown',
		'vodlink' => 'none',
		'thumb' => 'none',
		'clipper' => $submitter,
		'votecount' => 0,
		'score' => 0,
		'nuked' => 0,
		'type' => $clipType,
	);

	$existingSlug = getSlugInPulledClipsDB($slug);
	if ($existingSlug !== null) {
		killAjaxFunction("That clip has already been submitted");
	} else {
		$addSlugSuccess = addSlugToDB($clipArray);
	}


	// $seedlingArray = array(
	// 	'clipURL' => $newSeedlingUrl,
	// 	'post_title' => $newSeedlingTitle,
	// 	'sourcePic' => 'default',
	// 	'sourceURL' => '',
	// 	'submitter' => $submitter,
	// 	'submitTime' => $time,
	// 	'tax_input' => $clipTax,
	// 	'meta_input' => $clipMeta,
	// );
	
	// $gardenPageObject = get_page_by_path('secret-garden');
	// $gardenID = $gardenPageObject->ID;

	// $submissionResult = addSeedling($seedlingArray);

	killAjaxFunction($addSlugSuccess);
}

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
		nukeSlug($twitchCode);
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
	if ($didPost > 0) {
		absorb_votes($didPost);
	}

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
	$clipType = clipTypeDetector($_POST['url']);

	if ($clipType === 'twitch') {
		$clipArray = array(
			'slug' => $_POST['gussyData']['slug'],
			'age' => $_POST['gussyData']['timestamp'],
			'thumb' => $_POST['gussyData']['thumb'],
			'views' => $_POST['gussyData']['views'],
			'vodlink' => $_POST['gussyData']['vodlink'],
		);
	} elseif ($clipType === "gfycat") {
		$clipArray = array(
			'slug' => $_POST['gussyData']['slug'],
			'age' => date('c', $_POST['gussyData']['timestamp']),
			'thumb' => $_POST['gussyData']['thumb'],
			'views' => $_POST['gussyData']['views'],
		);
	} elseif ($clipType === "youtube" || $clipType === "ytbe") {
		$clipArray = array(
			'slug' => $_POST['gussyData']['slug'],
			'age' => date('c', $_POST['gussyData']['timestamp'] / 1000),
			'thumb' => $_POST['gussyData']['thumb'],
			'views' => $_POST['gussyData']['views'],
		);
	}
	editPulledClip($clipArray);

	killAjaxFunction($editedClip);
}

add_action( 'wp_ajax_keepSlug', 'keepSlug' );
function keepSlug() {
	$slug = $_POST['slug'];
	$postTitle = $_POST['newThingName'];
	$slugData = getSlugInPulledClipsDB($slug);

	if ($slugData['source'] === "User Submit") {
		$postSource = 632; //This is the source ID for user submits
	} else {
		$postSource = 632;
		$sourceArgs = array(
			'taxonomy' => 'source'
		);
		$sources = get_terms($sourceArgs);
		foreach ($sources as $source) {
			$key = get_term_meta($source->term_id, 'twitch', true);
			if (strcasecmp($key, $slugData['source']) == 0) {
				$postSource = $source->term_id;
			}
		}
	}

	$titleWords = explode(" ", $postTitle);
	$starNickname = strtolower($titleWords[0]);
	$starNickLength = strlen($starNickname);
	$star_args = array(
		'taxonomy' => 'stars',
	);
	$stars = get_terms($star_args);
	$postStar = 'X';
	$singleStar = true;
	foreach ($stars as $star) {
		$starSlug = $star->slug;
		$starShortSlug = substr($starSlug, 0, $starNickLength);
		if ($starShortSlug == $starNickname && $singleStar) {
			$postStar = $star->term_id;
			$singleStar = false;
		} elseif ($starShortSlug == $starNickname && !$singleStar) {
			$postStar = 'X';
		}
	};

	$slugVoters = getVotersForSlug($slugData['slug']);
	$voteledger = array();
	foreach ($slugVoters as $voter) {
		$voteledger[$voter['hash']] = getValidRep($voter['hash']);
	}

	$thingArray = array(
		'post_title' => $postTitle,
		'post_content' => '',
		'post_excerpt' => '',
		'post_status' => 'publish',
		'tax_input' => array(
			'source' => $postSource,
			'stars' => $postStar,
		),
		'meta_input' => array(
			'voteledger' => $voteledger,
			'votecount' => $slugData['score'],
		), 
	);

	if ($slugData['type'] === "twitch") {
		$thingArray['meta_input']['TwitchCode'] = $slugData['slug'];
	} elseif ($slugData['type'] === "gfycat") {
		$thingArray['meta_input']['GFYtitle'] = $slugData['slug'];
	} elseif ($slugData['type'] === "youtube" || $slugData['type'] === "ytbe") {
		$thingArray['meta_input']['YouTubeCode'] = $slugData['slug'];
	} elseif ($slugData['type'] === "twitter") {
		$thingArray['meta_input']['TwitterCode'] = $slugData['slug'];
	}

	$didPost = wp_insert_post($thingArray, true);
	if ($didPost > 0) {
		absorb_votes($didPost);
	}

	$dupes = get_dupe_clips($slugData['slug']);
	if ($dupes) {
		foreach ($dupes as $dupe) {
			nukeSlug($dupe);
		}
	}
	nukeSlug($slugData['slug']);

	killAjaxFunction("Post added for " . $slugData['slug']);
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

?>