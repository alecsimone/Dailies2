<?php

//Top Level Functions
// function createUserDB() {
//     if (!currentUserIsAdmin()) {
//         // killAjaxFunction("You're not an admin");
//     }

//     $preexistingUserDB = getUserDB();
//     if ($preexistingUserDB !== '') {
//         return;
//     }

//     $allUsers = get_users();
//     $wordpressUserData = array();
//     foreach ($allUsers as $user => $data) {
//         $wordpressUserData[$user]['basic'] = $data;
//         $wordpressUserData[$user]['meta'] = get_user_meta($data->ID);
//         $wordpressUserData[$user]['custom']['rep'] = getValidRep($data->ID);
//         $wordpressUserData[$user]['custom']['custom_pic'] = get_user_meta($data->ID, 'customProfilePic', true);
//         $wordpressUserData[$user]['custom']['lastRepTime'] = get_user_meta($data->ID, 'lastRepTime', true);
//         $wordpressUserData[$user]['custom']['voteHistory'] = get_user_meta($data->ID, 'voteHistory', true);
//     }

//     $twitchUserData = getTwitchUserDB();

//     $userDB = array();
//     $alreadyProcessedDailiesIDs = array();

//     foreach ($twitchUserData as $user => $twitchData) {

//         $dailiesID = $twitchData['dailiesUserID'];
//         if ($dailiesID !== 'none') {
//             foreach ($wordpressUserData as $key => $wpdata) {
//                 if ($wpdata['basic']->ID === $dailiesID) {
//                     $thisGuysWPData = $wpdata; 
//                 }
//             }

//             if ($twitchData['twitchPic'] !== 'none') {
//                 $picture = $twitchData['twitchPic'];
//             } elseif ($wpdata['custom']['custom_pic'] !== '') {
//                 $picture = $thisGuysWPData['custom']['custom_pic'];
//             } elseif (array_key_exists('wsl_current_user_image', $thisGuysWPData['meta'])) {
//                 $picture = $thisGuysWPData['meta']['wsl_current_user_image'][0];
//             } else {
//                 $picture = 'https://dailies.gg/wp-content/uploads/2017/03/default_pic.jpg';
//             }

//             if (intval($twitchData['rep']) >= intval($thisGuysWPData['custom']['rep'])) {
//                 $rep = $twitchData['rep'];
//             } else {
//                 $rep = $wpdata['custom']['rep'];
//             }
//             if ($rep > 100) {$rep = 100;}

//             $wpLastRepTime = $thisGuysWPData['custom']['lastRepTime'];
//             if ($wpLastRepTime === '') { 
//                 $wpLastRepTime = 0;
//             } else {
//                 $wpLastRepTime = $wpLastRepTime * 1000;
//             }
//             if ($twitchData['lastRepTime'] >= $wpLastRepTime) {
//                 $lastRepTime = $twitchData['lastRepTime'];
//             } else {
//                 $lastRepTime = $wpLastRepTime;
//             }

//             $email = $thisGuysWPData['basic']->user_email;
//             if (strpos($email, 'example.com')) {$email = '--';}

//             if ( !array_key_exists('wsl_current_provider', $thisGuysWPData['meta']) ) {
//                 $provider = '--';
//             } else {
//                 $provider = $thisGuysWPData['meta']['wsl_current_provider'][0];
//             }

//             $userRow = array(
//                 'hash' => generateHash(),
//                 'picture' => $picture,
//                 'dailiesID' => $dailiesID,
//                 'dailiesDisplayName' => $thisGuysWPData['basic']->display_name,
//                 'twitchName' => $user,
//                 'rep' => $rep,
//                 'lastRepTime' => $lastRepTime,
//                 'votes' => $thisGuysWPData['custom']['voteHistory'],
//                 'email' => $email,
//                 'provider' => $provider,
//                 'role' => array_keys($thisGuysWPData['basic']->caps)[0],
//                 'starID' => '--',
//                 'special' => 0,
//             );

//             $alreadyProcessedDailiesIDs[] = $dailiesID;
//             $userDB[] = $userRow;
//         } else {
//             $rep = $twitchData['rep'];
//             if ($rep > 100) {$rep = 100;}

//             $userRow = array(
//                 'hash' => generateHash(),
//                 'picture' => $twitchData['twitchPic'],
//                 'dailiesID' => '--',
//                 'dailiesDisplayName' => '--',
//                 'twitchName' => $user,
//                 'rep' => $rep,
//                 'lastRepTime' => $twitchData['lastRepTime'],
//                 'votes' => '--',
//                 'email' => '--',
//                 'provider' => 'Twitch',
//                 'role' => '--',
//                 'starID' => '--',
//                 'special' => 0,
//             );
//             $userDB[] = $userRow;
//         }
//     }

//     foreach ($wordpressUserData as $key => $data) {
//         if (in_array($data['basic']->ID, $alreadyProcessedDailiesIDs)) {
//             continue;
//         }
        
//         if ( array_key_exists('wsl_current_user_image', $data['meta']) ) {
//             $picture = $data['meta']['wsl_current_user_image'][0];
//         } else {
//             $picture = $data['custom']['custom_pic'];
//         }

//         $rep = $data['custom']['rep'];
//         if ($rep > 100) {$rep = 100;}

//         $email = $data['basic']->user_email;
//         if (strpos($email, 'example.com')) {$email = '--';}

//         $lastRepTime = $data['custom']['lastRepTime'];
//         if ($lastRepTime === '') {
//             $lastRepTime = 0;
//         } else {
//             $lastRepTime = $lastRepTime * 1000;
//         }

//         if ( !array_key_exists('wsl_current_provider', $data['meta']) ) {
//             $provider = '--';
//         } else {
//             $provider = $data['meta']['wsl_current_provider'][0];
//         }

//         $userRow = array(
//             'hash' => generateHash(),
//             'picture' => $picture,
//             'dailiesID' => $data['basic']->ID,
//             'dailiesDisplayName' => $data['basic']->display_name,
//             'twitchName' => '--',
//             'rep' => $rep,
//             'lastRepTime' => $lastRepTime,
//             'votes' => $data['custom']['voteHistory'],
//             'email' => $email,
//             'provider' => $provider,
//             'role' => array_keys($data['basic']->caps)[0],
//             'starID' => '--',
//             'special' => 0,
//         );
//         $userDB[] = $userRow;
//     }

//     $userManagementPageID = getPageIDBySlug('user-management');
//     update_post_meta($userManagementPageID, 'userDB', $userDB);
//     return $userDB;
// }

// function getUserDB() {
// 	$userManagementPageID = getPageIDBySlug('user-management');
// 	$userDB = get_post_meta($userManagementPageID, 'userDB', true);
// 	return $userDB;
// }

// function getUserInDB($user) {
// 	$userDB = getUserDB();
// 	$userData = false;
// 	if (is_array($user)) {
// 		if (isset($user['hash'])) {
// 			foreach ($userDB as $key => $data) {
// 				if ($data['hash'] === $user['hash']) {
// 					$userData = $userDB[$key];
// 				}
// 			}
// 		} elseif (isset($user['dailiesID'])) {
// 			foreach ($userDB as $key => $data) {
// 				if ($data['dailiesID'] == $user['dailiesID']) {
// 					$userData = $userDB[$key];
// 				}
// 			}
// 		} elseif (isset($user['twitchName'])) {
// 			foreach ($userDB as $key => $data) {
// 				if ($data['twitchName'] === $user['twitchName']) {
// 					$userData = $userDB[$key];
// 				}
// 			}
// 		}
// 	} elseif (is_string($user)) {
// 		foreach ($userDB as $key => $data) {
// 			if ($data['hash'] === $user) {
// 				$userData = $userDB[$key];
// 			} elseif (strtolower($data['twitchName']) === strtolower($user)) {
// 				$userData = $userDB[$key];
// 			}
// 		}
// 	} elseif (is_numeric($user)) {
// 		foreach ($userDB as $key => $data) {
// 			if ($data['dailiesID'] === intval($user)) {
// 				$userData = $userDB[$key];
// 			}
// 		}
// 	}
// 	return $userData;
// }

// function editUserInDB($userArray) {
//     $userDB = getUserDB();
//     $ourUserKey = "unset";
//     if (isset($userArray['hash'])) {
//         foreach ($userDB as $key => $data) {
//             if ($data['hash'] == $userArray['hash']) {
//                 $ourUserKey = $key;
//             }
//         }
//     } elseif (isset($userArray['dailiesID'])) {
//         foreach ($userDB as $key => $data) {
//             if ($data['dailiesID'] == $userArray['dailiesID']) {
//                 $ourUserKey = $key;
//             }
//         }
//     } elseif (isset($userArray['twitchName'])) {
//         foreach ($userDB as $key => $data) {
//             if ($data['twitchName'] === $userArray['twitchName']) {
//                 $ourUserKey = $key;
//             }
//         }
//     }
//     if ($ourUserKey === "unset") {
//         addUserToDB($userArray);
//         return;
//     } 
//     $ourUser = $userDB[$ourUserKey];

//     $userArrayKeys = array_keys($ourUser);
//     foreach ($userArrayKeys as $userArrayKey) {
//         if (isset($userArray[$userArrayKey])) {
//             if ($userArrayKey === 'votes') {
//                 if ( !is_array($ourUser['votes']) ) {
//                     if ($ourUser['votes'] === '--') {
//                         $ourUser['votes'] = array();
//                     } else {
//                         $ourUser['votes'] = array(
//                             0 => $ourUser['votes'],
//                         );
//                     }
//                 }
//                 if ( is_string($userArray['votes']) ) {
//                     if ( !array_search($userArray['votes'], $ourUser['votes']) && !array_search(intval($userArray['votes']), $ourUser['votes']) ) {
//                         $oldVoteList = $ourUser['votes'];
//                         $oldVoteList[] = $userArray['votes'];
//                         $ourUser['votes'] = $oldVoteList;
//                     } else {
//                         $oldVoteList = $ourUser['votes'];
//                         $thisThingsArrayPosition = array_search($userArray['votes'], $ourUser['votes']);
//                         if (!$thisThingsArrayPosition) {
//                             $thisThingsArrayPosition = !array_search(intval($userArray['votes']), $ourUser['votes']);
//                         }
//                         array_splice($oldVoteList, $thisThingsArrayPosition, 1);
//                         $ourUser['votes'] = $oldVoteList;
//                     }
//                 } elseif ( is_int($userArray['votes']) ) {
//                     if ( !array_search($userArray['votes'], $ourUser['votes']) && !array_search(strval($userArray['votes']), $ourUser['votes']) ) {
//                         $oldVoteList = $ourUser['votes'];
//                         $oldVoteList[] = strval($userArray['votes']);
//                         $ourUser['votes'] = $oldVoteList;
//                     } else {
//                         $oldVoteList = $ourUser['votes'];
//                         $thisThingsArrayPosition = array_search($userArray['votes'], $ourUser['votes']);
//                         if (!$thisThingsArrayPosition) {
//                             $thisThingsArrayPosition = !array_search(intval($userArray['votes']), $ourUser['votes']);
//                         }
//                         array_splice($oldVoteList, $thisThingsArrayPosition, 1);
//                         $ourUser['votes'] = $oldVoteList;
//                     }
//                 } else {
//                     $ourUser['votes'] = $userArray['votes'];
//                 }
//             } elseif ($userArrayKey === 'hash') {
//             } else { 
//                 $ourUser[$userArrayKey] = $userArray[$userArrayKey];
//             }
//         }
//     }
//     $userDB[$ourUserKey] = $ourUser;

//     simpleUpdateUserDB($userDB);

// }

// function addUserToDB($userArray) {
//     $userDB = getUserDB();
//     foreach ($userDB as $key => $data) {
//         if ($data['dailiesID'] == $userArray['dailiesID'] || $data['twitchName'] === $userArray['twitchName']) {
//             return;
//         }
//     }
    
//     $hash = generateHashUniqueInTable(64, $userDB);

//     $userRow = array(
//         'hash' => $hash,
//         'picture' => 'none',
//         'dailiesID' => -1,
//         'dailiesDisplayName' => '--',
//         'twitchName' => '--',
//         'rep' => 1,
//         'lastRepTime' => time(),
//         'votes' => array(),
//         'email' => '--',
//         'provider' => '--',
//         'role' => '--',
//         'starID' => -1,
//         'special' => 0,
//     );
//     foreach ($userArray as $key => $value) {
//         $userRow[$key] = $value;
//     }
//     $userDB[] = $userRow;
//     simpleUpdateUserDB($userDB);

//     unset($userRow['votes']);
// }

function linkAccounts($dailiesID, $twitchName) {
	if (!is_string($twitchName) || !is_numeric($dailiesID)) {
		return;
	}

	// $userTest = get_userdata($dailiesID);
	// if ($userTest === false) {
	// 	return "No user with that ID";
	// }

	// $twitchUserDB = getTwitchUserDB();
	// if ( !array_key_exists($twitchName, $twitchUserDB) ) {
	// 	return "No twitch user by that name";
	// }

	$dailiesRep = getValidRep($dailiesID);
	$twitchNameRep = getTwitchNameRep($twitchName);
	if (intval($dailiesRep) > intval($twitchNameRep)) {
		$repToSet = $dailiesRep;
	} else {
		$repToSet = $twitchNameRep;
	}

	// $twitchURL = "http://www.twitch.tv/" . $twitchName;
	// wp_update_user( array(
	// 	'ID' => $dailiesID, 
	// 	'user_url' => $twitchURL,
	// ));
	// update_user_meta($dailiesID, 'rep', $repToSet);

	$userArray = array(
		"twitchName" => $twitchName,
		"dailiesUserID" => $dailiesID,
		"rep" => $repToSet,
	);
	// editUserInTwitchDB($userArray);

	$twitchDBEntry = getPersonInDB($twitchName);
	$dailiesDBEntry = getPersonInDB($dailiesID);
	if ($twitchDBEntry['hash'] !== $dailiesDBEntry['hash']) {
		$deleteUserArray = array(
			'twitchName' => $twitchName,
		);
		deleteUserFromDB($deleteUserArray);
	}
	editPersonInDB($userArray);
}

// function deleteUserFromDB($userArray) {
// 	if (isset($userArray['hash'])) {
// 		$ourUser = getUserInDB($userArray['hash']);
// 	} elseif (isset($userArray['twitchName'])) {
// 		$ourUser = getUserInDB($userArray['twitchName']);
// 	} elseif (isset($userArray['dailiesID'])) {
// 		$ourUser = getUserInDB($userArray['dailiesID']);
// 	}
// 	foreach ($ourUser as $key => $value) {
// 		if (isset($userArray[$key])) {
// 			if ($userArray[$key] != $ourUser[$key]) {
// 				return;
// 			}
// 		}
// 	}
// 	$userDB = getUserDB();
// 	foreach ($userDB as $key => $value) {
// 		if ($value['hash'] === $ourUser['hash']) {
// 			unset($userDB[$key]);
// 		}
// 	}
// 	simpleUpdateUserDB($userDB);
// }

//Getters and Setters
// function simpleUpdateUserDB($newUserDB) {
// 	$userManagementPageID = getPageIDBySlug('user-management');
// 	update_post_meta($userManagementPageID, 'userDB', $newUserDB);
// }

function buildFreshTwitchUserDB() {
	$userDB = getPeopleDB();
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

//AJAX Handlers
add_action( 'wp_ajax_deleteUser', 'deleteUser' );
function deleteUser() {
    if (!currentUserIsAdmin()) {
        killAjaxFunction("You're not an admin");
    }

    $deadUserObject = $_POST['deadUserObject'];
    $dailiesID = $deadUserObject['dailiesID'];
    $twitchName = $deadUserObject['twitchName'];

    if (intval($dailiesID) > 0) {
        wp_delete_user($dailiesID);
        $userArray = array(
            'dailiesID' => $dailiesID,
        );
        deleteUserFromDB($userArray);
    }

    if ($twitchName !== '--') {
        $twitchUserDB = getTwitchUserDB();
        unset($twitchUserDB[$twitchName]);
        updateTwitchUserDB($twitchUserDB);
        $userArray = array(
            'twitchName' => $twitchName,
        );
        deleteUserFromDB($userArray);
    }

    killAjaxFunction($deadUserObject);
}

add_action( 'wp_ajax_linkUser', 'linkUser' );
function linkUser() {
    if (!currentUserIsAdmin()) {
        killAjaxFunction("You're not an admin");
    }

    $linkUserObject = $_POST['linkUserObject'];
    $dailiesID = $linkUserObject['dailiesID'];
    $twitchName = $linkUserObject['twitchName'];

    if (intval($dailiesID) === 0) {
        killAjaxFunction("DailiesID wasn't a number");
    }

    linkAccounts($dailiesID, $twitchName);

    killAjaxFunction($linkUserObject);
}

add_action( 'wp_ajax_addRepToUser', 'addRepToUser' );
function addRepToUser() {
    if (!currentUserIsAdmin()) {
        killAjaxFunction("You're not an admin");
    }

    $addRepObject = $_POST['addRepObject'];
    $user = $addRepObject['user'];
    $repToAdd = intval($addRepObject['repToAdd']);

    if (intval($user) > 0) {
        $user = intval($user);
    }

    increase_rep($user, $repToAdd);

    killAjaxFunction($addRepObject);
}

add_action( 'set_user_role', 'updateRole', 10, 3);
function updateRole($dailiesID, $role, $old_roles) {
    $userArray = array(
        'dailiesID' =>$dailiesID,
        'role' => $role,
    );
    editPersonInDB($userArray);
}

add_action( 'wp_ajax_UMselectUserRole', 'UMselectUserRole' );
function UMselectUserRole() {
    if (!currentUserIsAdmin()) {
        killAjaxFunction("You're not an admin");
    }

    $dailiesID = $_POST['dailiesID'];
    $newRole = $_POST['newRole'];

    if (!is_numeric($dailiesID)) {
        killAjaxFunction('Invalid dailiesID');
    }

    wp_update_user( array(
        'ID' => $dailiesID,
        'role' => $newRole,
    ));

    killAjaxFunction($newRole);
}
add_action( 'wsl_hook_process_login_after_create_wp_user', 'newUserCreatedByWSL', 10, 3 );
function newUserCreatedByWSL($userID, $provider, $hybridauth_user_profile) {
    $userData = get_user_by('ID', $userID);
    $userMeta = get_user_meta($userID);
    $userArray = array(
        'dailiesID' => $userID,
        'dailiesDisplayName' => $userData->display_name,
        'picture' => $userMeta['wsl_current_user_image'][0],
    );
    addPersonToDB($userArray);
}

function fixBrokenPeople() {
    $umID = getPageIDBySlug("user-management");
    $lastFixPeopleTime = get_post_meta($umID, 'lastFixPeopleTime', true);
    if ((int)$lastFixPeopleTime + 6 * 60 * 60 > time()) {
        return;
    }
    update_post_meta($umID, "lastFixPeopleTime", time());
    $peopleDB = getPeopleDB();
    foreach ($peopleDB as $key => $person) {
        $personArray = array(
            'hash' => $person['hash'],
        );
        $personNeedsUpdate = false;
        if ($person['dailiesID'] != -1 && $person['dailiesDisplayName'] === '--') {
            $userData = get_user_by('ID', $person['dailiesID']);
            if ($userData) {
                $personNeedsUpdate = true;
                $personArray['dailiesDisplayName'] = $userData->display_name;
            }
        }
        if ($person['dailiesID'] != -1 && $person['picture'] === 'none') {
            $userMeta = get_user_meta($person['dailiesID']);
            if ($userMeta) {
                $personNeedsUpdate = true;
                $personArray['picture'] = $userMeta['wsl_current_user_image'][0];
            }
        }
        if ($person['dailiesID'] != -1 && $person['email'] === '--') {
            $userData = get_user_by('ID', $person['dailiesID']);
            if ($userData && !strpos($userData->user_email, 'example.com')) {
                $personNeedsUpdate = true;
                $personArray['email'] = $userData->user_email;
            }
        }
        if ($person['dailiesID'] != -1 && $person['provider'] === '--') {
            $userMeta = get_user_meta($person['dailiesID']);
            if ($userMeta['wsl_current_provider'][0]) {
                $personNeedsUpdate = true;
                $personArray['provider'] = $userMeta['wsl_current_provider'][0];
                if ($personArray['provider'] === "TwitchTV") {
                    $userData = get_user_by('ID', $person['dailiesID']);
                    $twitchURL = $userData->user_url;
                    $tvpos = strpos($twitchURL, '.tv/') + 4;
                    $twitchName = substr($twitchURL, $tvpos);
                    $personArray['twitchName'] = $twitchName;
                }
            }
        }
        if ($personNeedsUpdate) {
            // basicPrint($personArray);
            editPersonInDB($personArray);
        }
    }
}

function mergeTwitchAccounts() {
    $umID = getPageIDBySlug("user-management");
    $lastMergeTwitchTime = get_post_meta($umID, 'lastMergeTwitchTime', true);
    if ((int)$lastMergeTwitchTime + 6 * 60 * 60 > time()) {
        return;
    }
    update_post_meta($umID, "lastMergeTwitchTime", time());
    global $wpdb;
    $table_name = $wpdb->prefix . 'people_db';

    $twitchUsers = $wpdb->get_results(
        "
        SELECT id, hash, dailiesID, twitchName, rep, provider
        FROM $table_name
        WHERE twitchName != '--' 
        ",
        ARRAY_A 
    );

    $skippableIDs = [];
    foreach ($twitchUsers as $keyOne => $userOne) {
        if (in_array($userOne['id'], $skippableIDs)) {continue;}
        foreach ($twitchUsers as $keyTwo => $userTwo) {
            if (strtolower($userOne['twitchName']) === strtolower($userTwo['twitchName']) && $userOne['hash'] !== $userTwo['hash']) {
                $skippableIDs[] = $userTwo['id'];
                if ($userOne['dailiesID'] == -1 || $userTwo['dailiesID'] == -1) {
                    if ($userOne['dailiesID'] == -1) {
                        $userToDelete = array(
                            'hash' => $userOne['hash'],
                        );
                        $userToKeep = array(
                            'hash' => $userTwo['hash'],
                        );
                    } else {
                         $userToDelete = array(
                            'hash' => $userTwo['hash'],
                        );
                        $userToKeep = array(
                            'hash' => $userOne['hash'],
                        );
                    }
                    // basicPrint($userOne);
                    // basicPrint($userTwo);
                    $userToKeep['rep'] = (int)$userOne['rep'] + (int)$userTwo['rep'];
                    if ($userToKeep['rep'] > 100) {$userToKeep['rep'] = 100;}
                   deletePersonFromDB($userToDelete);
                   editPersonInDB($userToKeep);
                } else {
                    // basicPrint($userOne);
                    // basicPrint($userTwo);
                    $newRep = (int)$userOne['rep'] + (int)$userTwo['rep'];
                    if ($newRep > 100) {$newRep = 100;}
                    // basicPrint("Total rep: " . $newRep);
                    // basicPrint("------");
                }
            }
        }
    }
}
function recognizeTwitchChatters() {
    $umID = getPageIDBySlug("user-management");
    $lastRecognizeTwitchTime = get_post_meta($umID, 'lastRecognizeTwitchTime', true);
    if ((int)$lastRecognizeTwitchTime + 6 * 60 * 60 > time()) {
        return;
    }
    update_post_meta($umID, "lastRecognizeTwitchTime", time());
    global $wpdb;
    $table_name = $wpdb->prefix . 'people_db';

    $twitchDailiesAccounts = $wpdb->get_results(
        "
        SELECT hash, dailiesID, twitchName, rep
        FROM $table_name
        WHERE provider = 'TwitchTV' 
        ",
        ARRAY_A 
    );

    foreach ($twitchDailiesAccounts as $person) {
        if ($person['twitchName'] !== '--') {
            continue;
        }
        $userData = get_user_by('ID', $person['dailiesID']);
        $twitchURL = $userData->user_url;
        $tvpos = strpos($twitchURL, '.tv/') + 4;
        $twitchName = substr($twitchURL, $tvpos);
        $existingTwitchPerson = $wpdb->get_results(
            "
            SELECT hash, dailiesID, twitchName, rep
            FROM $table_name
            WHERE twitchName = '$twitchName' 
            ",
            ARRAY_A 
        );
        if ($existingTwitchPerson) {
            if ((int)$person['rep'] >= (int)$existingTwitchPerson[0]['rep']) {
                deletePersonFromDB($existingTwitchPerson[0]);
            } else {
                $person['twitchName'] = $twitchName;
                $person['rep'] = $existingTwitchPerson[0]['rep'];
                deletePersonFromDB($existingTwitchPerson[0]);
                editPersonInDB($person);
            }
        } else {
            $person['twitchName'] = $twitchName;
            editPersonInDB($person);
        }
        
    }
}
add_action('init', 'fixBrokenPeople');
add_action('init', 'mergeTwitchAccounts');
add_action('init', 'recognizeTwitchChatters');


?>