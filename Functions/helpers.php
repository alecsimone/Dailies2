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

function getValidGuestlist($postID) {
	$guestlist = get_post_meta($postID, 'guestlist', true);
	if ($voteLedger = '') {
		$guestlist = [];
	}
	return $guestlist;
}

function getValidRep($user) {
	$userData = getUserInDB($user);
	$rep = $userData['rep'];
	if ($rep == '' || $rep == 0) {
		$rep = 1;
	}
	return $rep;
}

function getTwitchNameRep($twitchName) {
	$twitchUserDB = getTwitchUserDB();
	if (array_key_exists($twitchName, $twitchUserDB)) {
		$rep = $twitchUserDB[$twitchName]['rep'];
	} else {
		$rep = 0;
	}
	return $rep;
}

function increase_rep($user, $additionalRep) {
	$oldRep = getValidRep($user);
	$newRep = $oldRep + $additionalRep;
	if ($newRep > 100) {$newRep = 100;}
	if (is_numeric($user)) {
		$userArray = array(
			'dailiesID' => intval($user),
			'rep' => $newRep,
		);
	} elseif (is_string($user)) {
		$typeOfString = checkIfStringIsHashOrTwitchName($user);
		$userArray = array(
			'rep' => $newRep,
		);
		$userArray[$typeOfString] = $user;
	}
	editUserInDB($userArray);
	return $newRep;
}
function updateRepTime($person) {
	$userArray = array(
		'lastRepTime' => time(),
	);
	if (is_string($person)) {
		$userArray[checkIfStringIsHashOrTwitchName($person)] = $person;
	} elseif (is_int($person)) {
		$userArray['dailiesID'] = $person;
	}
	editUserInDB($userArray);
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

function getPicByUser($user) {
	$userRow = getUserInDB($user);
	$userPic = $userRow['picture'];
	return $userPic;
}

function getUserDB() {
	$userManagementPageID = getPageIDBySlug('user-management');
	$userDB = get_post_meta($userManagementPageID, 'userDB', true);
	return $userDB;
}

function getUserInDB($user) {
	$userDB = getUserDB();
	$userData = false;
	if (is_array($user)) {
		if (isset($user['hash'])) {
			foreach ($userDB as $key => $data) {
				if ($data['hash'] === $user['hash']) {
					$userData = $userDB[$key];
				}
			}
		} elseif (isset($user['dailiesID'])) {
			foreach ($userDB as $key => $data) {
				if ($data['dailiesID'] == $user['dailiesID']) {
					$userData = $userDB[$key];
				}
			}
		} elseif (isset($user['twitchName'])) {
			foreach ($userDB as $key => $data) {
				if ($data['twitchName'] === $user['twitchName']) {
					$userData = $userDB[$key];
				}
			}
		}
	} elseif (is_string($user)) {
		foreach ($userDB as $key => $data) {
			if ($data['hash'] === $user) {
				$userData = $userDB[$key];
			} elseif (strtolower($data['twitchName']) === strtolower($user)) {
				$userData = $userDB[$key];
			}
		}
	} elseif (is_numeric($user)) {
		foreach ($userDB as $key => $data) {
			if ($data['dailiesID'] === intval($user)) {
				$userData = $userDB[$key];
			}
		}
	}
	return $userData;
}

function simpleUpdateUserDB($newUserDB) {
	$userManagementPageID = getPageIDBySlug('user-management');
	update_post_meta($userManagementPageID, 'userDB', $newUserDB);
}

function getTwitchUserDB() {
	$liveID = getPageIDBySlug('live');
	return get_post_meta($liveID, 'twitchUserDB', true);
}

function buildFreshTwitchUserDB() {
	$userDB = getUserDB();
	$freshTwitchUserDB = array();
	foreach ($userDB as $key => $data) {
		if ($data['twitchName'] !== '--') {
			$freshTwitchUserDB[$data['twitchName']] = array(
				'dailiesUserID' => $data['dailiesID'],
				'lastRepTime' => $data['lastRepTime'],
				'rep' => $data['rep'],
				'twitchPic' => $data['picture'],
			);
		}
	}
	return $freshTwitchUserDB;
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

function linkAccounts($dailiesID, $twitchName) {
	if (!is_string($twitchName) || !is_numeric($dailiesID)) {
		return;
	}

	$userTest = get_userdata($dailiesID);
	if ($userTest === false) {
		return "No user with that ID";
	}

	$twitchUserDB = getTwitchUserDB();
	if ( !array_key_exists($twitchName, $twitchUserDB) ) {
		return "No twitch user by that name";
	}

	$dailiesRep = getValidRep($dailiesID);
	$twitchNameRep = getTwitchNameRep($twitchName);
	if (intval($dailiesRep) > intval($twitchNameRep)) {
		$repToSet = $dailiesRep;
	} else {
		$repToSet = $twitchNameRep;
	}

	$twitchURL = "http://www.twitch.tv/" . $twitchName;
	wp_update_user( array(
		'ID' => $dailiesID, 
		'user_url' => $twitchURL,
	));
	update_user_meta($dailiesID, 'rep', $repToSet);

	$userArray = array(
		"twitchName" => $twitchName,
		"dailiesUserID" => $dailiesID,
		"rep" => $repToSet,
	);
	editUserInTwitchDB($userArray);

	$twitchDBEntry = getUserInDB($twitchName);
	$dailiesDBEntry = getUserInDB($dailiesID);
	if ($twitchDBEntry['counter'] !== $dailiesDBEntry['counter']) {
		$deleteUserArray = array(
			'twitchName' => $twitchName,
		);
		deleteUserDBEntry($deleteUserArray);
	}
	editUserinDB($userArray);
}

function deleteUserFromDB($userArray) {
	if (isset($userArray['hash'])) {
		$ourUser = getUserInDB($userArray['hash']);
	} elseif (isset($userArray['twitchName'])) {
		$ourUser = getUserInDB($userArray['twitchName']);
	} elseif (isset($userArray['dailiesID'])) {
		$ourUser = getUserInDB($userArray['dailiesID']);
	}
	foreach ($ourUser as $key => $value) {
		if (isset($userArray[$key])) {
			if ($userArray[$key] != $ourUser[$key]) {
				return;
			}
		}
	}
	$userDB = getUserDB();
	foreach ($userDB as $key => $value) {
		if ($value['hash'] === $ourUser['hash']) {
			unset($userDB[$key]);
		}
	}
	simpleUpdateUserDB($userDB);
}

function getLastNomTime() {
	$latestNomArgs = array(
		'category_name' => 'noms',
		'posts_per_page' => 1,
	);
	$latestNom = get_posts($latestNomArgs);
	$latestNomTime = strtotime($latestNom[0]->post_date);
	$latestNomDay = date('l', $latestNomTime);
	$latestNomDate = date('j', $latestNomTime);
	$latestNomDateSuffix = date('S', $latestNomTime);
	$latestNomMonth = date('F', $latestNomTime);
	$latestNomYear = date('Y', $latestNomTime);
	$latestNomDateArray = array(
		"Year" => $latestNomYear,
		"Month" => $latestNomMonth,
		"Date" => $latestNomDate,
	);
	return $latestNomDateArray;
}
function getLastNomTimestamp() {
	$lastNomDateArray = getLastNomTime();
	$lastNomDateString = $lastNomDateArray['Date'] . '-' . $lastNomDateArray['Month'] . ' ' . $lastNomDateArray['Year'];
	$lastNomTimestamp = strtotime($lastNomDateString);
	return $lastNomTimestamp;
}

function basicPrint($val) {
	print_r($val); echo "<br/>";
}
function printKeyValue($key, $value) {
	echo $key . ": ";
	print_r($value);
	echo "<br/>";
}

function getPostStars($postID) {
	return get_the_terms($postID, 'stars');
}

function createUserDB() {
    if (!currentUserIsAdmin()) {
        // killAjaxFunction("You're not an admin");
    }

    $preexistingUserDB = getUserDB();
    if ($preexistingUserDB !== '') {
        return;
    }

    $allUsers = get_users();
    $wordpressUserData = array();
    foreach ($allUsers as $user => $data) {
        $wordpressUserData[$user]['basic'] = $data;
        $wordpressUserData[$user]['meta'] = get_user_meta($data->ID);
        $wordpressUserData[$user]['custom']['rep'] = getValidRep($data->ID);
        $wordpressUserData[$user]['custom']['custom_pic'] = get_user_meta($data->ID, 'customProfilePic', true);
        $wordpressUserData[$user]['custom']['lastRepTime'] = get_user_meta($data->ID, 'lastRepTime', true);
        $wordpressUserData[$user]['custom']['voteHistory'] = get_user_meta($data->ID, 'voteHistory', true);
    }

    $twitchUserData = getTwitchUserDB();

    $userDB = array();
    $alreadyProcessedDailiesIDs = array();

    foreach ($twitchUserData as $user => $twitchData) {

        $dailiesID = $twitchData['dailiesUserID'];
        if ($dailiesID !== 'none') {
            foreach ($wordpressUserData as $key => $wpdata) {
                if ($wpdata['basic']->ID === $dailiesID) {
                    $thisGuysWPData = $wpdata; 
                }
            }

            if ($twitchData['twitchPic'] !== 'none') {
                $picture = $twitchData['twitchPic'];
            } elseif ($wpdata['custom']['custom_pic'] !== '') {
                $picture = $thisGuysWPData['custom']['custom_pic'];
            } elseif (array_key_exists('wsl_current_user_image', $thisGuysWPData['meta'])) {
                $picture = $thisGuysWPData['meta']['wsl_current_user_image'][0];
            } else {
                $picture = 'http://dailies.gg/wp-content/uploads/2017/03/default_pic.jpg';
            }

            if (intval($twitchData['rep']) >= intval($thisGuysWPData['custom']['rep'])) {
                $rep = $twitchData['rep'];
            } else {
                $rep = $wpdata['custom']['rep'];
            }
            if ($rep > 100) {$rep = 100;}

            $wpLastRepTime = $thisGuysWPData['custom']['lastRepTime'];
            if ($wpLastRepTime === '') { 
                $wpLastRepTime = 0;
            } else {
                $wpLastRepTime = $wpLastRepTime * 1000;
            }
            if ($twitchData['lastRepTime'] >= $wpLastRepTime) {
                $lastRepTime = $twitchData['lastRepTime'];
            } else {
                $lastRepTime = $wpLastRepTime;
            }

            $email = $thisGuysWPData['basic']->user_email;
            if (strpos($email, 'example.com')) {$email = '--';}

            if ( !array_key_exists('wsl_current_provider', $thisGuysWPData['meta']) ) {
                $provider = '--';
            } else {
                $provider = $thisGuysWPData['meta']['wsl_current_provider'][0];
            }

            $userRow = array(
                'hash' => generateHash(),
                'picture' => $picture,
                'dailiesID' => $dailiesID,
                'dailiesDisplayName' => $thisGuysWPData['basic']->display_name,
                'twitchName' => $user,
                'rep' => $rep,
                'lastRepTime' => $lastRepTime,
                'votes' => $thisGuysWPData['custom']['voteHistory'],
                'email' => $email,
                'provider' => $provider,
                'role' => array_keys($thisGuysWPData['basic']->caps)[0],
                'starID' => '--',
                'special' => 0,
            );

            $alreadyProcessedDailiesIDs[] = $dailiesID;
            $userDB[] = $userRow;
        } else {
            $rep = $twitchData['rep'];
            if ($rep > 100) {$rep = 100;}

            $userRow = array(
                'hash' => generateHash(),
                'picture' => $twitchData['twitchPic'],
                'dailiesID' => '--',
                'dailiesDisplayName' => '--',
                'twitchName' => $user,
                'rep' => $rep,
                'lastRepTime' => $twitchData['lastRepTime'],
                'votes' => '--',
                'email' => '--',
                'provider' => 'Twitch',
                'role' => '--',
                'starID' => '--',
                'special' => 0,
            );
            $userDB[] = $userRow;
        }
    }

    foreach ($wordpressUserData as $key => $data) {
        if (in_array($data['basic']->ID, $alreadyProcessedDailiesIDs)) {
            continue;
        }
        
        if ( array_key_exists('wsl_current_user_image', $data['meta']) ) {
            $picture = $data['meta']['wsl_current_user_image'][0];
        } else {
            $picture = $data['custom']['custom_pic'];
        }

        $rep = $data['custom']['rep'];
        if ($rep > 100) {$rep = 100;}

        $email = $data['basic']->user_email;
        if (strpos($email, 'example.com')) {$email = '--';}

        $lastRepTime = $data['custom']['lastRepTime'];
        if ($lastRepTime === '') {
            $lastRepTime = 0;
        } else {
            $lastRepTime = $lastRepTime * 1000;
        }

        if ( !array_key_exists('wsl_current_provider', $data['meta']) ) {
            $provider = '--';
        } else {
            $provider = $data['meta']['wsl_current_provider'][0];
        }

        $userRow = array(
            'hash' => generateHash(),
            'picture' => $picture,
            'dailiesID' => $data['basic']->ID,
            'dailiesDisplayName' => $data['basic']->display_name,
            'twitchName' => '--',
            'rep' => $rep,
            'lastRepTime' => $lastRepTime,
            'votes' => $data['custom']['voteHistory'],
            'email' => $email,
            'provider' => $provider,
            'role' => array_keys($data['basic']->caps)[0],
            'starID' => '--',
            'special' => 0,
        );
        $userDB[] = $userRow;
    }

    $userManagementPageID = getPageIDBySlug('user-management');
    update_post_meta($userManagementPageID, 'userDB', $userDB);
    return $userDB;
}

function rebuildUserDB() {
	$userManagementPageID = getPageIDBySlug('user-management');
    update_post_meta($userManagementPageID, 'userDB', '');
    createUserDB();
}

function generateHash($length = 64) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$hash = '_';
	for ($i = 1; $i <= $length; $i++) {
		$hash .= $characters[random_int(0, $charactersLength - 1)];
	}
	return $hash;
}

function checkHashUniqueness($hash, $table) {
	$isUnique = true;
	foreach ($table as $key => $data) {
		$thisRowsHash = $data['hash'];
		if ($hash == $thisRowsHash) {$isUnique = false;}
	}
	return $isUnique;
}

function generateHashUniqueInTable($length, $table) {
	$hash = generateHash($length);
	$hashIsUnique = checkHashUniqueness($hash, $table);
	while (!$hashIsUnique) {
		$hash = generateHash($length);
		$hashIsUnique = checkHashUniqueness($hash, $table);
	}
	return $hash;
}

function checkIfStringIsHashOrTwitchName($string) {
	if (is_numeric($string)) {
		return 'dailiesID';
	}
	if (strlen($string) === 65 && $string[0] === '_') {
		return 'hash';
	} else {
		return 'twitchName';
	}
}

function ensureTimestampInSeconds($timestamp) {
	$timestampInteger = intval($timestamp);
	if ($timestampInteger <= 9999999999) {
		return $timestampInteger;
	} else {
		return floor($timestampInteger / 1000);
	}
}

?>