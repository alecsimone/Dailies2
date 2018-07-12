<?php 

function getPageIDBySlug($slug) {
	$pageObject = get_page_by_path($slug);
	return $pageObject->ID;
}

function currentUserIsAdmin() {
	$userID = get_current_user_id();
	$userDataObject = get_userdata($userID);
	$userRole = $userDataObject->roles[0];
	if ($userRole === 'administrator') {
		return true;
	} else {
		return false;
	}
}

function getCurrentVotersList() {
	$liveID = getPageIDBySlug('live');
	$currentVotersList = get_post_meta($liveID, 'currentVoters', true);
	if ($currentVotersList === '') {
		$currentVotersList = [];
	}
	return $currentVotersList;
}

function updateCurrentVotersList($newList) {
	$liveID = getPageIDBySlug('live');
	update_post_meta($liveID, 'currentVoters', $newList);
}

function killAjaxFunction($response) {
	echo json_encode($response);
	wp_die();
}

function getValidVoteledger($postID) {
	$voteledger = get_post_meta($postID, 'voteledger', true);
	if ($voteLedger === '') {
		$voteLedger = [];
	}
	return $voteledger;
}

function getValidRep($userID) {
	$rep = get_user_meta($userID, 'rep', true);
	if ($rep == '') {
		$rep = 10;
	}
	return $rep;
}

function increase_rep($userID, $additionalRep) {
	$currentRep = get_user_meta($userID, 'rep', true);
	$newRep = $currentRep + $additionalRep;
	if ($newRep > 100) {
		$newRep = 100;
	}
	update_user_meta( $userID, 'rep', $newRep);
	return $newRep;
}

?>