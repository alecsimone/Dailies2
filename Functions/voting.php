<?php

add_action( 'wp_ajax_official_vote', 'official_vote' );
add_action( 'wp_ajax_nopriv_official_vote', 'official_vote' );
function official_vote() {
	$nonce = $_POST['vote_nonce'];
	if (!wp_verify_nonce($nonce, 'vote_nonce')) {
		die("Busted!");
	}
	$postID = $_POST['id'];

	if (is_user_logged_in()) {
		$userID = get_current_user_id();
		user_vote($userID, $postID, false);
		return;
	}
		
	$guestRep = 1;
	$clientIP = $_SERVER['REMOTE_ADDR'];

	$oldGuestlist = get_post_meta($postID, 'guestlist', true);
	if (!in_array($clientIP, $oldGuestlist)) {
		addCurrentGuestToGuestlist($postID);
		changeVotecount($guestRep, $postID);
	} else {
		removeCurrentGuestFromGuestlists($postID);
		changeVoteCount(-$guestRep, $postID);
	}
	buildPostDataObject($postID);
	echo json_encode($newScore);
	wp_die();
}

function user_vote($userID, $postID, $additive) {
	$voteledger = getValidVoteledger($postID);

	if (!array_key_exists($userID, $voteledger)) {
		$rep = increaseRepWhenEarned($userID);
		addVoterToLedger($userID, $postID);
		changeVotecount($rep, $postID);
		addPostToVoteHistory($userID, $postID);
	} else {
		if ($additive) {
			return;
		}
		changeVotecount(-$voteledger[$userID], $postID);
		removeVoterFromLedger($userID, $postID);
		removePostFromVoteHistory($userID, $postID);
	}
}

function twitch_vote($twitchName, $postID, $additive) {
	$twitchVoters = getValidTwitchVoters($postID);
	if (!array_key_exists($twitchName, $twitchVoters)) {
		addTwitchVoter($twitchName, $postID);
	} else {
		if ($additive) {
			return;
		}
		removeTwitchVoter($twitchName, $postID);
	}
}

function unknownVoterVote($voter, $postID) {
	//See if they made an account. vote. add to DB.
	$twitchUserDB = getTwitchUserDB();
	$dailiesID = findUserIDByTwitchName($voter);
	if ($dailiesID) {
		$twitchUserArray = array(
			'twitchName' => $voter,
			'dailiesUserID' => $dailiesID,
			'lastRepTime' => time(),
		);
		editUserInTwitchDB($twitchUserArray);
		user_vote($dailiesID, $postID);
	} else {
		addAccountlessVoterToTwitchUserDB($voter);
		twitch_vote($voter, $postID, false);
	}
}

function addAccountlessVoterToTwitchUserDB($voter) {
	$twitchUserDB = getTwitchUserDB();
	$twitchUserArray = array(
		'twitchName' => $voter,
		'dailiesUserID' => 'none',
		'twitchPic' => 'none',
		'rep' => 1,
		'lastRepTime' => time(),
	);
	editUserInTwitchDB($twitchUserArray);
}

function addVoterToLedger($userID, $postID) {
	$voteledger = getValidVoteledger($postID);
	$rep = getValidRep($userID);
	$voteledger[$userID] = $rep;
	update_post_meta($postID, 'voteledger', $voteledger);
	return $voteledger;
}
function removeVoterFromLedger($voterID, $postID) {
	$voteLedger = getValidVoteledger($postID);
	if (array_key_exists($voterID, $voteLedger)) {
		unset($voteLedger[$voterID]);
		update_post_meta($postID, 'voteledger', $voteLedger);
	}
}

add_action( 'wp_ajax_load_votes', 'load_votes' );
function load_votes() {
	$postID = $_POST['postID'];
	$votesToLoad = get_post_meta($postID, 'chatVotes', true);
	$liveID = $getPageIDBySlug('live');
	updateCurrentVotersList($votesToLoad);
	killAjaxFunction($postID);
}

function increaseRepWhenEarned($userID) {
	$rep = getValidRep($userID);
	$currentTime = time();
	$lastRepTime = get_user_meta($userID, 'lastRepTime', true);
	if ($lastRepTime === '') {$lastRepTime = 0;};
	$repAddingThreshold = $currentTime - (24 * 60 *60);
	if ($lastRepTime <= $repAddingThreshold) {
		update_user_meta($userID, 'lastRepTime', $currentTime);
		$rep = increase_rep($userID, 1);
		$userArray = array(
			'twitchName' => findTwitchUserByDailiesID($userID),
			'rep' => $rep,
			'lastRepTime' => time(),
		);
		editUserInTwitchDB($userArray);
	}
	return $rep;
}

function increaseAccountlessRepWhenEarned($twitchName) {
	$twitchUserDB = getTwitchUserDB();
	$currentTime = time();
	$currentRep = $twitchUserDB[$twitchName]['rep'];
	$lastRepTime = $twitchUserDB[$twitchName]['lastRepTime'];
	$repAddingThreshold = $currentTime - (24 * 60 *60);
	if ($lastRepTime <= $repAddingThreshold) {
		$newRep = $currentRep + 1;
		$twitchUserArray = array(
			'twitchName' => $twitchName,
			'rep' => $newRep,
			'lastRepTime' => $currentTime,
		);
		editUserInTwitchDB($twitchUserArray);
		return $newRep;
	}
	return $currentRep;
}

// function removePostFromVoteHistory($userID, $postID) {
// 	$voteHistory = get_user_meta($userID, 'voteHistory', true);
// 	if ($voteHistory === false) {
// 		$voteHistory = array();
// 	}
// 	$unvotedPostKey = array_search($postID, $voteHistory);
// 	unset($voteHistory[$unvotedPostKey]);
// 	update_user_meta($userID, 'voteHistory', $voteHistory);
// }

function addTwitchVoter($twitchName, $postID) {
	$twitchVoters = getValidTwitchVoters($postID);
	$twitchUserDB = getTwitchUserDB();
	$twitchPic = $twitchUserDB[$twitchName]["twitchPic"];
	$twitchVoters[$twitchName] = $twitchPic;
	update_post_meta($postID, 'twitchVoters', $twitchVoters);
	changeVotecount(1, $postID);
}
function removeTwitchVoter($twitchName, $postID) {
	$twitchVoters = getValidTwitchVoters($postID);
	unset($twitchVoters[$twitchName]);
	update_post_meta($postID, 'twitchVoters', $twitchVoters);
	changeVotecount(-1, $postID);
}
function getValidTwitchVoters($postID) {
	$twitchVoters = get_post_meta($postID, 'twitchVoters', true);
	if ($twitchVoters === '') {
		$twitchVoters = array();
	}
	return $twitchVoters;
}

function get_dailies_account_by_twitch_name($twitchName) {
	$userQueryString = 'http://www.twitch.tv/' . $twitchName;
	$userqueryargs = array(
		'search' => $userQueryString,
		'search_columns' => array('user_url'),
	);
	$user_query = new WP_User_Query($userqueryargs);
	if ( !empty($user_query->get_results()) ) {
		$dailiesID = $user_query->get_results()[0]->ID;
		return $dailiesID;
	} else {
		return false;
	}
}

add_action( 'wp_ajax_update_twitch_db', 'update_twitch_db' );
// function update_twitch_db() {
// 	if (!currentUserIsAdmin()) {
// 		wp_die("You are not an admin, sorry");
// 	}
// 	$twitchName = $_POST['twitchName'];
// 	$twitchPic = $_POST['twitchPic'];
// 	$liveID = getPageIDBySlug('live');
// 	$twitchUserDB = getTwitchUserDB();
// 	$twitchUserArray = array(
// 		'twitchName' => $twitchName,
// 		'twitchPic' => $twitchPic,
// 	);
// 	editUserInTwitchDB($twitchUserArray);

// 	$userArray = array(
// 		'twitchName' => $twitchName,
// 		'picture' => $twitchPic,
// 	);
// 	editUserInDB($userArray);

// 	killAjaxFunction($twitchUserDB[$twitchName]);
// }
function update_twitch_db() {
	if (!currentUserIsAdmin()) {
		wp_die("You are not an admin, sorry");
	}
	$twitchName = $_POST['twitchName'];
	$twitchPic = $_POST['twitchPic'];
	$userArray = array(
		'twitchName' => $twitchName,
		'picture' => $twitchPic,
	);
	editPersonInDB($userArray);
	killAjaxFunction($userArray);
}

?>