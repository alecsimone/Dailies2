<?php

function vote($person, $thing) {
	$hasVoted = checkIfPersonHasVoted($person, $thing);
	if (!$hasVoted) {
		$person = getUserInDB($person);
		if ($person) {
			$rep = getValidRep($person['hash']);
			changeVotecount($rep, $thing);
			$voteledger = getValidVoteledger($thing);
			$voteledger[$person['hash']] = $rep;
			update_post_meta($thing, 'voteledger', $voteledger);
		} else {
			changeVotecount(1, $thing);
			addCurrentGuestToGuestlist($thing);
		}
	} else {
		unvote($person, $thing);
	}
	buildPostDataObject($thing);
}

function checkIfPersonHasVoted($person, $thing) {
	$hasVoted = false;
	$person = getUserInDB($person);
	$ledger = getValidVoteledger($thing);
	$twitchVoters = getValidTwitchVoters($thing);
	$guestlist = get_post_meta($thing, 'guestlist', true);
	if (isset($person['hash'])) {
		if (array_key_exists($person['hash'], $ledger)) {
			$hasVoted = true;
		}
	}
	if (isset($person['dailiesID'])) {
		if (array_key_exists($person['dailiesID'], $ledger)) {
			$hasVoted = true;
		}
	}
	if (isset($person['twitchName'])) {
		if (array_key_exists($person['twitchName'], $twitchVoters)) {
			$hasVoted = true;
		}
	}
	$ip = $_SERVER['REMOTE_ADDR'];
	if (in_array($ip, $guestlist)) {
		$hasVoted = true;
	}
	return $hasVoted;
}

function unvote($person, $thing) {
	$person = getUserInDB($person);

	$ledger = getValidVoteledger($thing);
	if (array_key_exists($person['hash'], $ledger)) {
		changeVotecount(-$ledger[$person['hash']], $thing);
		unset($ledger[$person['hash']]);
	}
	if (array_key_exists($person['dailiesID'], $ledger)) {
		changeVotecount(-$ledger[$person['dailiesID']], $thing);
		unset($ledger[$person['dailiesID']]);
	}
	update_post_meta($thing, 'voteLedger', $ledger);
	
	$twitchVoters = getValidTwitchVoters($thing);
	if (array_key_exists($person['twitchName'], $ledger)) {
		changeVotecount(-1, $thing);
		unset($twitchVoters[$person['twitchName']]);
	}
	update_post_meta($thing, 'twitchVoters', $twitchVoters);
	
	$guestlist = get_post_meta($thing, 'guestlist', true);
	$ip = $_SERVER['REMOTE_ADDR'];
	if (in_array($ip, $guestlist)) {
		$guestKey = array_search($ip, $guestlist);
		unset($guestlist[$guestKey]);
		changeVotecount(-1, $thing);
	}
	update_post_meta($thing, 'guestlist', $guestlist);
}
function checkForRepIncrease($person) {
	$person = getUserInDB($person);
	$lastNomTime = ensureTimestampInSeconds(getLastNomTimestamp());
	$lastRepTime = ensureTimestampInSeconds($person['lastRepTime']);
	$deservesNewRep = false;
	if ($lastRepTime <= $lastNomTime) {
		$newRep = increase_rep($person['hash'], 1);
		updateRepTime($person['hash']);
		$deservesNewRep = true;
	}
	if ($deservesNewRep) {
		return $newRep;
	} else {
		return false;
	}
}

add_action( 'wp_ajax_chat_vote', 'chat_vote' );
function chat_vote() {
	if (!currentUserIsAdmin()) {
		wp_die("You are not an admin, sorry");
	}
	applyChatVote($_POST['voter'], $_POST['direction']);
	killAjaxFunction($_POST['voter'] . ' voted ' . $_POST['direction']);
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
	vote($voter, $postIDToVoteOn);
	killAjaxFunction($voter. " voted on post " . $postIDToVoteOn);
}

function absorb_votes($postID) {
	$currentVotersList = getCurrentVotersList();
	$twitchUserDB = getTwitchUserDB();

	foreach ($currentVotersList['yea'] as $index => $twitchName) {
		$voter = getUserInDB($twitchName);
		if ($voter) {
			$hasVoted = checkIfPersonHasVoted($twitchName, $postID);
			if ($hasVoted) {
				continue;
			}
			vote($twitchName, $postID);
		} else {
			$userArray = array(
				'twitchName' => $twitchName,
			);
			addUserToDB($userArray);
			vote($twitchName, $postID);	
		}
	}
	foreach ($currentVotersList['nay'] as $index => $twitchName) {
		$voter = getUserInDB($twitchName);
		if ($voter) {
			$hasVoted = checkIfPersonHasVoted($twitchName, $postID);
			if (!$hasVoted) {
				continue;
			}
			vote($twitchName, $postID);
		}
	}
	reset_chat_votes();
	return;
}

add_action( 'wp_ajax_handle_vote', 'handle_vote' );
add_action( 'wp_ajax_nopriv_handle_vote', 'handle_vote' );
function handle_vote() {
	$nonce = $_POST['vote_nonce'];
	if (!wp_verify_nonce($nonce, 'vote_nonce')) {
		die("Busted!");
	}
	$thing = $_POST['id'];

	if (is_user_logged_in()) {
		$person = get_current_user_id();
	} else {
		$person = false;
	}
	vote($person, $thing);

	killAjaxFunction('You voted on ' . $thing);
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
function addPostToVoteHistory($userID, $postID) {
	$voteHistory = get_user_meta($userID, 'voteHistory', true);
	$voteHistory[] = $postID;
	update_user_meta($userID, 'voteHistory', $voteHistory);
	$userArray = array(
		'dailiesID' => $userID,
		'votes' => $postID,
	);
	editUserInDB($userArray);
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
	editUserInDB($userArray);
	killAjaxFunction($userArray);
}

?>