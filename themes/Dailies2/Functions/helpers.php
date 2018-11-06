<?php 
function getTwitchNameRep($twitchName) {
	$twitchUserDB = getTwitchUserDB();
	if (array_key_exists($twitchName, $twitchUserDB)) {
		$rep = $twitchUserDB[$twitchName]['rep'];
	} else {
		$rep = 0;
	}
	return $rep;
}

function increase_account_rep($userID, $additionalRep) {	
	$currentRep = get_user_meta($userID, 'rep', true);
	$newRep = $currentRep + $additionalRep;
	if ($newRep > 100) {
		$newRep = 100;
	}
	update_user_meta( $userID, 'rep', $newRep);
	$thisUsersTwitchName = findTwitchUserByDailiesID($userID);
	if ($thisUsersTwitchName) {
		$userArray = array(
			'twitchName' => $thisUsersTwitchName,
			'dailiesUserID' => $userID,
			'rep' => $newRep,
			'lastRepTime' => time(),
		);
		editUserInTwitchDB($userArray);
	}
	return $newRep;
}
function increase_accountless_rep($twitchName, $additionalRep) {
	$twitchUserDB = getTwitchUserDB();
	if (isset($twitchUserDB[$twitchName])) {
		$currentRep = $twitchUserDB[$twitchName]['rep'];
	} else {
		$currentRep = 0;
	}
	$newRep = $currentRep + $additionalRep;
	if ($newRep > 100) {
		$newRep = 100;
	}
	$twitchUserArray = array(
		'twitchName' => $twitchName,
		'rep' => $newRep,
		'lastRepTime' => time(),
	);
	editUserInTwitchDB($twitchUserArray);
	if (findUserIDByTwitchName($twitchName)) {
		increase_account_rep(findUserIDByTwitchName($twitchName), $additionalRep);
	}
	return $newRep;
}

function findTwitchUserByDailiesID($dailiesID) {
	$twitchUserDB = getTwitchUserDB();
	foreach ($twitchUserDB as $twitchName => $data) {
		if ($data['dailiesUserID'] === $dailiesID) {
			return $twitchName;
		} else {
			continue;
		}
	}
	return false;
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

function editUserInTwitchDB($twitchUserArray) {
	$twitchUserDB = getTwitchUserDB();
	$twitchName = $twitchUserArray["twitchName"];

	if (isset($twitchUserArray["dailiesUserID"])) {
		$twitchUserDB[$twitchName]["dailiesUserID"] = $twitchUserArray["dailiesUserID"];
	} else {
		$dailiesUserID = findUserIDByTwitchName($twitchName);
		if (!$dailiesUserID) {
			$twitchUserDB[$twitchName]["dailiesUserID"] = 'none';
		} else {
			$twitchUserDB[$twitchName]["dailiesUserID"] = $dailiesUserID;
		}
	}	

	if (isset($twitchUserArray["twitchPic"])) {
		$twitchUserDB[$twitchName]["twitchPic"] = $twitchUserArray["twitchPic"];
	} else {
		if (!isset($twitchUserDB[$twitchName]["twitchPic"])) {
			$twitchUserDB[$twitchName]["twitchPic"] = 'none';
		}
	}

	if (isset($twitchUserArray["rep"])) {
		$twitchUserDB[$twitchName]["rep"] = $twitchUserArray["rep"];
	} else {
		if (isset($twitchUserArray["dailiesUserID"])) {
			$dailiesUserID = $twitchUserArray["dailiesUserID"];
			$twitchUserDB[$twitchName]["rep"] = getValidRep($dailiesUserID);
		} else {
			$dailiesUserID = findUserIDByTwitchName($twitchName);
			if (!$dailiesUserID) {
				$twitchUserDB[$twitchName]["rep"] = 1;
			} else {
				$twitchUserDB[$twitchName]["rep"] = getValidRep($dailiesUserID);
			}
		}
	}

	if (isset($twitchUserArray["lastRepTime"])) {
		$twitchUserDB[$twitchName]["lastRepTime"] = $twitchUserArray["lastRepTime"];
	} else {
		if (!isset($twitchUserDB[$twitchName]["lastRepTime"])) {
			$twitchUserDB[$twitchName]["lastRepTime"] = 0;
		}
	}
	updateTwitchUserDB($twitchUserDB);
}

function getPostStars($postID) {
	return get_the_terms($postID, 'stars');
}

function rebuildUserDB() {
	$userManagementPageID = getPageIDBySlug('user-management');
    update_post_meta($userManagementPageID, 'userDB', '');
    createUserDB();
}

add_filter( 'postmeta_form_limit', 'meta_limit_increase' );
function meta_limit_increase( $limit ) {
    return 50;
}
add_filter('show_admin_bar', '__return_false');

?>