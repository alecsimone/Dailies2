<?php

add_action( 'wp_ajax_chat_vote', 'chat_vote' );
function chat_vote() {
	if (!currentUserIsAdmin()) {
		wp_die("You are not an admin, sorry");
	}
	applyChatVote($_POST['voter'], $_POST['direction']);
	killAjaxFunction($currentVotersList);
}
function applyChatVote($voter, $direction) {
	$currentVotersList = getCurrentVotersList();
	$otherDirection = getOtherDirection($direction);
	if (!in_array($voter, $currentVotersList[$direction])) { 
		$currentVotersList[$direction][] = $voter;
	}
	if (in_array($voter, $currentVotersList[$otherDirection])) {
		$ourVoterKey = array_search($voter, $currentVotersList[$otherDirection]);
		array_splice($currentVotersList[$otherDirection], $ourVoterKey, 1);
	}
	updateCurrentVotersList($currentVotersList);
}
function getOtherDirection($direction) {
	if ($direction === 'yea') {
		$otherDirection = 'nay';
	} elseif ($direction === 'nay') {
		$otherDirection = 'yea';
	}
	return $otherDirection;
}

add_action( 'wp_ajax_chat_contender_vote', 'chat_contender_vote' );
function chat_contender_vote() {
	if (!currentUserIsAdmin()) {
		wp_die("You are not an admin, sorry");
	}	
	$voter = $_POST['voter'];
	$postIDToVoteOn = getPostIDForVoteNumber($_POST['voteNumber']);
	if (!$postIDToVoteOn) {
		wp_die("You've picked an invalid number");
	}
	$twitchUserDB = getTwitchUserDB();
	if (array_key_exists($voter, $twitchUserDB)) {
		knownVoterVote($voter, $postIDToVoteOn);
	} else {
		unknownVoterVote($voter, $postIDToVoteOn);
	}
	killAjaxFunction($twitchUserDB);
}
function getPostIDForVoteNumber($voteNumber) {
	$postDataArray = getLiveContenders();
	$voteIndex = $voteNumber - 1;
	if (!voteChoiceIsValid($voteIndex, $postDataArray)) {
		return false;
	} else {
		return $postDataArray[$voteIndex]->ID;
	}
}
function voteChoiceIsValid($voteChoice, $postDataArray) {
	$postCount = count($postDataArray);
	if ($voteChoice > $postCount) {
		return false;
	} else {
		return true;
	}
}
function getTwitchUserDB() {
	$liveID = getPageIDBySlug('live');
	return get_post_meta($liveID, 'twitchUserDB', true);
}
function updateTwitchUserDB($newUserDB) {
	$liveID = getPageIDBySlug('live');
	update_post_meta($liveID, 'twitchUserDB', $newUserDB);
}
function findUserIDByTwitchName($twitchName) {
	$userQueryString = 'http://www.twitch.tv/' . $twitchName;
	$userqueryargs = array(
		'search' => $userQueryString,
		'search_columns' => array('user_url'),
	);
	$user_query = new WP_User_Query($userqueryargs);
	if (!empty($user_query->get_results())) {
		return $user_query->get_results()[0]->ID;
	} else {
		return false;
	}
}
function knownVoterVote($voter, $postID) {
	$twitchUserDB = getTwitchUserDB();
	$voterDBEntry = $twitchUserDB[$voter];
	if ($voterDBEntry['dailiesUserID'] !== 'none') {
		user_vote($voterDBEntry['dailiesUserID'], $postID, false);
	} else {
		accountlessKnownVoterVote($voter, $postID);
	}
}
function accountlessKnownVoterVote($voter, $postID) {
	//Check if they made an account, then vote
	$dailiesID = findUserIDByTwitchName($voter);
	if ($dailiesID) {
		$twitchUserDB = getTwitchUserDB();
		$twitchUserDB[$voter]['dailiesUserID'] = $dailiesID;
		updateTwitchUserDB($twitchUserDB);
		user_vote($dailiesID, $postID, false);
	} else {
		twitch_vote($voter, $postID, false);
	}
}
function unknownVoterVote($voter, $postID) {
	//See if they made an account. vote. add to DB.
	$twitchUserDB = getTwitchUserDB();
	$dailiesID = findUserIDByTwitchName($voter);
	if ($dailiesID) {
		$twitchUserDB[$voter]['dailiesUserID'] = $dailiesID;
		updateTwitchUserDB($twitchUserDB);
		user_vote($dailiesID, $postID);
	} else {
		addAccountlessVoterToTwitchUserDB($voter);
		twitch_vote($voter, $postID, false);
	}
}
function addAccountlessVoterToTwitchUserDB($voter) {
	$twitchUserDB = getTwitchUserDB();
	$twitchUserDB[$voter] = array(
		'dailiesUserID' => 'none',
		'twitchPic' => 'none',
	);
	updateTwitchUserDB($twitchUserDB);
}

function absorb_votes($postID) {
	$liveID = getPageIDBySlug('live');
	$currentVotersList = getCurrentVotersList();
	$twitchUserDB = getTwitchUserDB();

	foreach ($currentVotersList['yea'] as $index => $twitchName) {
		if (array_key_exists($twitchName, $twitchUserDB)) {
			knownVoterVote($twitchName, $postID);
		} else {
			unknownVoterVote($twitchName, $postID);			
		}
	}
	foreach ($currentVotersList['nay'] as $index => $twitchName) {
		if (!array_key_exists($twitchName, $twitchUserDB)) {
			return;
		}
		$currentVoterDBEntry = $twitchUserDB[$twitchName];
		$dailiesID = $currentVoterDBEntry['dailiesUserID'];
		if ($dailiesID !== 'none') {
			removeVoterFromLedger($dailiesID, $postID);
		} else {
			//I'm assuming we don't need to check if they've created a dailies account here, because we're just trying to undo any previous votes, so their dailies account would have to have existed at the time of the previous vote, and would thus exist in our database already. IE, if they've added a dailies account since they voted before, we actually DON'T want to know that, because we're trying to undo something they must have done without an account
			removeTwitchVoter($twitchName, $postID);
		}
	}
	update_post_meta($postID, 'chatVotes', $currentVotersList);
	reset_chat_votes();
	return;
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

add_action( 'wp_ajax_reset_chat_votes', 'reset_chat_votes' );
function reset_chat_votes() {
	if (!currentUserIsAdmin()) {
		wp_die("You are not an admin, sorry");
	}

	$currentVotersList['yea'] = [];
	$currentVotersList['nay'] = [];

	updateCurrentVotersList($currentVotersList);
	killAjaxFunction("we resettin the votes!");
}

add_action( 'wp_ajax_get_chat_votes', 'get_chat_votes' );
add_action( 'wp_ajax_nopriv_get_chat_votes', 'get_chat_votes' );
function get_chat_votes() {
	$currentVotersList = getCurrentVotersList();
	killAjaxFunction($currentVotersList);
}

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
		user_vote($userID, $postID);
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
function changeVotecount($amountToChange, $postID) {
	$currentScore = get_post_meta($postID, 'votecount', true);
	$newScore = $currentScore + $amountToChange;
	update_post_meta($postID, 'votecount', $newScore);
}
function addCurrentGuestToGuestlist($postID) {
	$clientIP = $_SERVER['REMOTE_ADDR'];
	$guestlist = get_post_meta($postID, 'guestlist', true);
	$guestlist[] = $clientIP;
	update_post_meta($postID, 'guestlist', $guestlist);
}
function removeCurrentGuestFromGuestlists($postID) {
	$clientIP = $_SERVER['REMOTE_ADDR'];
	$guestlist = get_post_meta($postID, 'guestlist', true);
	$guestKey = array_search($clientIP, $guestlist);
	unset($guestlist[$guestKey]);
	update_post_meta($postID, 'guestlist', $guestlist);
}

function user_vote($userID, $postID, $additive) {
	$voteledger = getValidVoteledger($postID);

	if (!array_key_exists($userID, $voteledger)) {
		$rep = increaseRepWhenEarned($userID);
		addVoterToLedger($userID, $postID);
		changeVotecount($rep, $postID);
		addPostToVoteHistory($userID, $postID);
	} else {
		//if additive is true, skip this part
		if ($additive) {
			return;
		}
		changeVotecount(-$voteledger[$userID], $postID);
		removeVoterFromLedger($userID, $postID);
		removePostFromVoteHistory($userID, $postID);
	}
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
	}
	return $rep;
}
function addPostToVoteHistory($userID, $postID) {
	$voteHistory = get_user_meta($userID, 'voteHistory', true);
	$voteHistory[] = $postID;
	update_user_meta($userID, 'voteHistory', $voteHistory);
}
function removePostFromVoteHistory($userID, $postID) {
	$voteHistory = get_user_meta($userID, 'voteHistory', true);
	if ($voteHistory === false) {
		$voteHistory = array();
	}
	$unvotedPostKey = array_search($postID, $voteHistory);
	unset($voteHistory[$unvotedPostKey]);
	update_user_meta($userID, 'voteHistory', $voteHistory);
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
function update_twitch_db() {
	if (!currentUserIsAdmin()) {
		wp_die("You are not an admin, sorry");
	}
	$twitchName = $_POST['twitchName'];
	$twitchPic = $_POST['twitchPic'];
	$liveID = getPageIDBySlug('live');
	$twitchUserDB = getTwitchUserDB();
	$twitchUserDB[$twitchName] = array(
		'dailiesUserID' => 'none', 
		'twitchPic' => $twitchPic,
	);
	update_post_meta($liveID, 'twitchUserDB', $twitchUserDB);
	killAjaxFunction($twitchUserDB[$twitchName]);
}

?>