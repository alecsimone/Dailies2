<?php 
add_action( 'wp_ajax_get_contender_urls', 'get_contender_urls' );
function get_contender_urls() {
	$postDataLive = getLiveContenders();

	$contenderURLs = [];
	foreach ($postDataLive as $index => $postInfo) {
		$contenderURLs[] = turnMetasIntoURL($postInfo->ID);
		clearAllVotesOnPost($postInfo->ID);
	}
	killAjaxFunction($contenderURLs);
}

function turnMetasIntoURL($postID) {
	$GFYtitle = get_post_meta($postID, 'GFYtitle', true);
	$TwitchCode = get_post_meta($postID, 'TwitchCode', true);
	$TwitterCode = get_post_meta($postID, 'TwitterCode', true);
	$YouTubeCode = get_post_meta($postID, 'YouTubeCode', true);
	if ($TwitchCode !== '') {
		$urlBit = $TwitchCode;
	} elseif ($YouTubeCode !== '') {
		$urlBit = $YouTubeCode;
	} elseif ($GFYtitle !== '') {
		$urlBit = $GFYtitle;
	} elseif ($TwitterCode !== '') {
		$urlBit = $TwitterCode;
	}
	return $urlBit;
}

function clearAllVotesOnPost($postID) {
	update_post_meta($postID, 'voteledger', array());
	update_post_meta($postID, 'guestlist', array());
	update_post_meta($postID, 'twitchVoters', array());
	update_post_meta($postID, 'votecount', 0);
}

add_action( 'wp_ajax_update_vote_number', 'update_vote_number' );
add_action( 'wp_ajax_update_nopriv_vote_number', 'update_vote_number' );
function update_vote_number() {
	$votenumber = $_POST['voteNumber'];
	$votenumberID = getPageIDBySlug('votenumber');
	update_post_meta($votenumberID, 'votenumber', $votenumber);
	killAjaxFunction($votenumber);
}

add_action( 'wp_ajax_return_vote_number', 'return_vote_number' );
add_action( 'wp_ajax_nopriv_return_vote_number', 'return_vote_number' );
function return_vote_number() {
	$votenumberID = getPageIDBySlug('votenumber');
	$votenumber = get_post_meta($votenumberID, 'votenumber', true);
	killAjaxFunction($votenumber);
}

?>