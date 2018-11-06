<?php

function getPeopleDB() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'people_db';
    $peopleDB = $wpdb->get_results(
        "SELECT *
        FROM $table_name",
        ARRAY_A 
    );
    return $peopleDB;
}

function getPersonInDB($person) {
    global $wpdb;
    if (is_array($person)) {
        if (isset($person['hash'])) {
            $column = "hash";
            $value = $person['hash'];
        } elseif (isset($person['dailiesID'])) {
            $column = "dailiesID";
            $value = $person['dailiesID'];
        } elseif (isset($person['twitchName'])) {
            $column = "twitchName";
            $value = $person['twitchName'];
        }
    } elseif (is_string($person)) {
        $column = checkIfStringIsHashOrTwitchName($person);
        $value = $person;
    } elseif (is_numeric($person)) {
        $column = "dailiesID";
        $value = $person;
    }
    $personData = $wpdb->get_row(
        "SELECT * FROM wpik_people_db WHERE $column = '$value'",
        ARRAY_A
    );
    if ($personData === null) {$personData = false;}
    return $personData;
}

function addPersonToDB($personArray) {
    global $wpdb;
    $table_name = $wpdb->prefix . "people_db";

    if (getPersonInDB($personArray)) {
        return "That person already exists in the database";
    }

    if (!isset($personArray['hash'])) {
        $personArray['hash'] = generateHash();
    }
    if (!isset($personArray['lastRepTime'])) {
        $personArray['lastRepTime'] = time();
    }

    $wpdb->insert(
        $table_name,
        $personArray
    );
    return $wpdb->insert_id;
}

function editPersonInDB($personArray) {
    $personData = getPersonInDB($personArray);
    if (!$personData) {
        addPersonToDB($personArray);
    }

    // $personArrayKeys = array_keys($personArray);
    // foreach ($personArrayKeys as $key) {
    //     if ($key !== 'hash') {
    //         $personData[$key] = $personArray[$key];
    //     }
    // }

    $where = array();
    if (isset($personArray['hash'])) {
        $where['hash'] = $personArray['hash'];
    } elseif (isset($personArray['dailiesID'])) {
        $where['dailiesID'] = $personArray['dailiesID'];
    } elseif (isset($personArray['twitchName'])) {
        $where['twitchName'] = $personArray['twitchName'];
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "people_db";
    $wpdb->update(
        $table_name,
        $personArray,
        $where
    );
}

function deletePersonFromDB($person) {
    $personArray = array();
    if (is_array($person)) {
        if (isset($person['hash'])) {
            $personArray['hash'] = $person['hash'];
        }
        if (isset($person['twitchName'])) {
            $personArray['twitchName'] = $person['twitchName'];
        }
        if (isset($person['dailiesID'])) {
            $personArray['dailiesID'] = $person['dailiesID'];
        }
    } elseif (is_string($person)) {
        $personArray[checkIfStringIsHashOrTwitchName($person)] = $person;
    } elseif (is_numeric($person)) {
        $personArray['dailiesID'] = $person;
    }
    if (is_string($personArray['dailiesID'])) {
        $personArray['dailiesID'] = intval($personArray['dailiesID']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "people_db";
    $deleteCount = $wpdb->delete(
        $table_name,
        $personArray
    );
    return $deleteCount;
}

function buildFreshTwitchDB() {
    global $wpdb;
    $table_name = $wpdb->prefix . "people_db";
    $twitchAccounts = $wpdb->get_results(
        "
        SELECT twitchName, rep, picture
        FROM $table_name
        WHERE twitchName != '--'
        ",
        'OBJECT_K'
    );
    return $twitchAccounts;
}

function getSpecialPeople() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'people_db';

    $specialPeople = $wpdb->get_results(
        "SELECT *
        FROM $table_name
        WHERE special = 1
        ",
        ARRAY_A
    );
    return $specialPeople;
}

function togglePersonSpecialness($person) {
    $personArray = getPersonInDB($person);
    if ($personArray['special'] == 0) {
        $personArray['special'] = 1;
    } else {
        $personArray['special'] = 0;
    }
    editPersonInDB($personArray);
}

function getValidRep($person) {
	$userData = getPersonInDB($person);
	$rep = $userData['rep'];
	if ($rep == '' || $rep == 0) {
		$rep = 1;
	}
	return $rep;
}

function increase_rep($person, $additionalRep) {
    $oldRep = getValidRep($person);
    $newRep = $oldRep + $additionalRep;
    if ($newRep > 100) {$newRep = 100;}
    if (is_numeric($person)) {
        $personArray = array(
            'dailiesID' => intval($person),
            'rep' => $newRep,
        );
    } elseif (is_string($person)) {
        $typeOfString = checkIfStringIsHashOrTwitchName($person);
        $personArray = array(
            'rep' => $newRep,
        );
        $personArray[$typeOfString] = $person;
    }
    editPersonInDB($personArray);
    return $newRep;
}

function updateRepTime($person) {
    $personArray = array(
        'lastRepTime' => time(),
    );
    if (is_string($person)) {
        $personArray[checkIfStringIsHashOrTwitchName($person)] = $person;
    } elseif (is_int($person)) {
        $personArray['dailiesID'] = $person;
    }
    editPersonInDB($personArray);
}

function getPersonsHash($person) {
    $personRow = getPersonInDB($person);
    return $personRow['hash'];
}

function getPicForPerson($person) {
	$userRow = getPersonInDB($person);
	$userPic = $userRow['picture'];
    if ($userPic === '') {
        $userPic = "https://dailies.gg/wp-content/uploads/2017/03/default_pic.jpg";
    }
	return $userPic;
}


?>