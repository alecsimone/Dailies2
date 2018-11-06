<?php

add_action( 'wp_ajax_keepSlug', 'keepSlug' );
function keepSlug() {
	$slug = $_POST['slug'];
	$postTitle = $_POST['newThingName'];
	$slugData = getSlugInPulledClipsDB($slug);

	if ($slugData['source'] === "User Submit") {
		$postSource = 632; //This is the source ID for user submits
	} else {
		$postSource = sourceFinder($slugData['source']);
	}

	$postStar = starChecker($postTitle);

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
			'category' => 1125,
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

add_action( 'wp_ajax_hopefuls_cutter', 'hopefuls_cutter' );
function hopefuls_cutter() {
	$slugToNuke = $_POST['slug'];
	nukeSlug($slugToNuke);
	reset_chat_votes();
	killAjaxFunction($slugToNuke);
}

?>