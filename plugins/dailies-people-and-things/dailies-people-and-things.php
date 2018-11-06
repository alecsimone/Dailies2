<?php
/*
Plugin Name: Dailies People and Things
Plugin URI:  https://dailies.gg/
Description: Manages all the people and things
Version:     0.1
Author:      Alec Simone
License:     Do whatever the hell you want with it, it's mostly pretty shit code
*/

require_once( __DIR__ . '/voting.php');
require_once( __DIR__ . '/people-management.php');

function copyUserDBtoPeopleDB($count, $offset=0) {
	$userDB = getUserDB();
	for ($i=$offset; $i < $count + $offset; $i++) { 
		$thisRow = $userDB[$i];
		if ($thisRow === null) {
			continue;
		}
		unset($thisRow['votes']);
		if ($thisRow['dailiesID'] === '--') {
			$thisRow['dailiesID'] = -1;
		}
		if ($thisRow['starID'] === '--') {
			$thisRow['starID'] = -1;
		}
		addPersonToDB($thisRow);
	}
}
// copyUserDBtoPeopleDB(1000, 0);

function copyVoteHistoryToDB($count, $offset=0) {
	$userDB = getUserDB();
	for ($i=$offset; $i < $count + $offset; $i++) {
		$person = $userDB[$i]['hash'];
		if (is_array($userDB[$i]['votes'])) {
			foreach ($userDB[$i]['votes'] as $thing) {
				addVoteToHistory($person, $thing);
			}
		}
	}
}
// copyVoteHistoryToDB(2000);

?>