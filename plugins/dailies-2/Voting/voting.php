<?php

function getPersonVoteIDs($person) {
	$personRow = getPersonInDB($person);
	$hash = $personRow['hash'];
	$voteIDs = array();
	global $wpdb;
	$table_name = $wpdb->prefix . 'vote_history_db';
	$allVoteRows = $wpdb->get_results(
		"SELECT postid
		FROM $table_name
		WHERE hash = '$hash'
		",
		ARRAY_A
	);
	foreach ($allVoteRows as $voteID) {
		$voteIDs[] = $voteID['postid'];
	}
	return $voteIDs;
}

add_action( 'wp_ajax_judge_slug', 'judge_slug' );
function judge_slug() {
	$slug = $_POST['slug'];
	$judgment = $_POST['judgment'];
	$vodlink = $_POST['vodlink'];
	if ($judgment === "pass") {
		killAjaxFunction("Dummy just passed");
		wp_die();
		return;
	}

	$userID = get_current_user_id();

	// Update the score in the PulledClipsDB
	$clipArray = getSlugInPulledClipsDB($slug);

	if ($clipArray === null) {
		killAjaxFunction("Unknown Clip: " . $slug);
	}

	if ($judgment === 'strongNo') {
		$clipArray['score'] = $clipArray['score'] - getValidRep($userID) * .2;
	} elseif ($judgment === 'weakNo') {
		// $clipArray['score'] = $clipArray['score'] - 1;
	} elseif ($judgment === 'weakYes') {
		$clipArray['score'] = $clipArray['score'] + 1;
	} elseif ($judgment === 'strongYes') {
		$clipArray['score'] = $clipArray['score'] + getValidRep($userID);
	}
	if (!is_int($clipArray['votecount'])) {
		$clipArray['votecount'] = 0;
	}
	$clipArray['votecount'] = (int)$clipArray['votecount'] + 1;
	editPulledClip($clipArray);

	// Add the vote to Seen_Clips_DB
	$error = store_slug_judgment($userID, $slug, $judgment, $vodlink);
	global $wpdb;
	$clipArray['storeError'] = $wpdb->last_error;

	nukeAllDupeSlugs($slug);
	checkForRepIncrease($userID);

	killAjaxFunction($clipArray);
}

function getVotersForSlug($slug) {
	global $wpdb;
	// $table_name = $wpdb->prefix . "seen_slugs_db";
	$table_name = $wpdb->prefix . "vote_db";

	$voterData = $wpdb->get_results(
		"SELECT *
		FROM $table_name
		WHERE slug = '$slug'
		", ARRAY_A
	);
	return $voterData;
}


?>