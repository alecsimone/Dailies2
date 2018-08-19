<?php
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>
 
    <h3>Extra profile information</h3> 
    <table class="form-table">
        <tr>
            <th><label for="rep">Rep</label></th>
            <td>
                <input type="text" name="rep" id="rep" value="<?php echo esc_attr( get_the_author_meta( 'rep', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description">Your Rep</span>
            </td>
        </tr>
    </table>
    <table class="form-table">
        <tr>
            <th><label for="customProfilePic">Custom Profile Picture</label></th>
            <td>
                <input type="text" name="customProfilePic" id="customProfilePic" value="<?php echo esc_attr( get_the_author_meta( 'customProfilePic', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description">Add a profile picture</span>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <th><label for="customProfilePic">Here's what that looks like: </label></th>
            <td>
            	<img src="<?php echo esc_attr( get_the_author_meta( 'customProfilePic', $user->ID ) ); ?>" class="adminCustomProfilePicture">
            </td>
        </tr>
    </table>    
<?php }

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );
add_action( 'admin_head', 'custom_profile_pic_css');
function custom_profile_pic_css() {
	echo '<style>img.adminCustomProfilePicture {max-width: 500px; height: auto;}</style>';
}


function my_save_extra_profile_fields( $user_id ) {
 
    if ( !current_user_can( 'edit_users', $user_id ) )
        return false;
    update_user_meta( absint( $user_id ), 'rep', wp_kses_post( $_POST['rep'] ) );
    update_user_meta( absint( $user_id ), 'customProfilePic', wp_kses_post( $_POST['customProfilePic'] ) );
}

function editUserInDB($userArray) {
    $userDB = getUserDB();
    $ourUserKey = "unset";
    if (isset($userArray['hash'])) {
        foreach ($userDB as $key => $data) {
            if ($data['hash'] == $userArray['hash']) {
                $ourUserKey = $key;
            }
        }
    } elseif (isset($userArray['dailiesID'])) {
        foreach ($userDB as $key => $data) {
            if ($data['dailiesID'] == $userArray['dailiesID']) {
                $ourUserKey = $key;
            }
        }
    } elseif (isset($userArray['twitchName'])) {
        foreach ($userDB as $key => $data) {
            if ($data['twitchName'] === $userArray['twitchName']) {
                $ourUserKey = $key;
            }
        }
    }
    if ($ourUserKey === "unset") {
        addUserToDB($userArray);
        return;
    } 
    $ourUser = $userDB[$ourUserKey];

    $userArrayKeys = array_keys($ourUser);
    foreach ($userArrayKeys as $userArrayKey) {
        if (isset($userArray[$userArrayKey])) {
            if ($userArrayKey === 'votes') {
                if ( !is_array($ourUser['votes']) ) {
                    if ($ourUser['votes'] === '--') {
                        $ourUser['votes'] = array();
                    } else {
                        $ourUser['votes'] = array(
                            0 => $ourUser['votes'],
                        );
                    }
                }
                if ( is_string($userArray['votes']) ) {
                    if ( !array_search($userArray['votes'], $ourUser['votes']) && !array_search(intval($userArray['votes']), $ourUser['votes']) ) {
                        $oldVoteList = $ourUser['votes'];
                        $oldVoteList[] = $userArray['votes'];
                        $ourUser['votes'] = $oldVoteList;
                    }
                } elseif ( is_int($userArray['votes']) ) {
                    if ( !array_search($userArray['votes'], $ourUser['votes']) && !array_search(strval($userArray['votes']), $ourUser['votes']) ) {
                        $oldVoteList = $ourUser['votes'];
                        $oldVoteList[] = strval($userArray['votes']);
                        $ourUser['votes'] = $oldVoteList;
                    }
                } else {
                    $ourUser['votes'] = $userArray['votes'];
                }
            } elseif ($userArrayKey === 'hash') {
            } else { 
                $ourUser[$userArrayKey] = $userArray[$userArrayKey];
            }
        }
    }
    $userDB[$ourUserKey] = $ourUser;

    simpleUpdateUserDB($userDB);

}

function addUserToDB($userArray) {
    $userDB = getUserDB();
    foreach ($userDB as $key => $data) {
        if ($data['dailiesID'] == $userArray['dailiesID'] || $data['twitchName'] === $userArray['twitchName']) {
            return;
        }
    }
    
    $hash = generateHashUniqueInTable(64, $userDB);

    $userRow = array(
        'hash' => $hash,
        'picture' => 'none',
        'dailiesID' => '--',
        'dailiesDisplayName' => '--',
        'twitchName' => '--',
        'rep' => 1,
        'lastRepTime' => time(),
        'votes' => array(),
        'email' => '--',
        'provider' => '--',
        'role' => '--',
        'starID' => '--',
        'special' => 0,
    );
    foreach ($userArray as $key => $value) {
        $userRow[$key] = $value;
    }
    $userDB[] = $userRow;
    simpleUpdateUserDB($userDB);
}

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
    editUserInDB($userArray);
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
    $userArray = array(
        'dailiesID' => $userID,
    );
    $userManagementPageID = getPageIDBySlug('user-management');
    update_post_meta($userManagementPageID, 'temporarystorage', $hybridauth_user_profile);
}

add_action( 'wp_ajax_collect_streamlabs_data', 'collect_streamlabs_data' );
function collect_streamlabs_data() {
    if (!currentUserIsAdmin()) {
        killAjaxFunction("You're not an admin");
    }

    $streamLabsData = $_POST['streamLabsData'];
    $userManagementPageID = getPageIDBySlug('user-management');
    $alreadyScrapedData = get_post_meta($userManagementPageID, 'scrapedData', true);
    if ($alreadyScrapedData === '') {
        $alreadyScrapedData = array();
    }

    foreach ($streamLabsData as $twitchName => $loyaltyPoints) {
        if (intval($loyaltyPoints) >= 100) {
            $alreadyScrapedData[$twitchName] = intval($loyaltyPoints);
        }
    }

    update_post_meta($userManagementPageID, 'scrapedData', $alreadyScrapedData);

    killAjaxFunction("scrape complete!");
}

function processScrapedData($count) {
    $counter = 1;
    $userManagementPageID = getPageIDBySlug('user-management');
    $scrapedData = get_post_meta($userManagementPageID, 'scrapedData', true);
    if ($scrapedData === '') {return;}
    foreach ($scrapedData as $name => $points) {
        if ($counter > $count) {
            // basicPrint($alreadyScrapedData);
            update_post_meta($userManagementPageID, 'scrapedData', $scrapedData);
            return;
        }
        $counter++;
        $trimmedName = trim($name);
        $person = getUserInDB($trimmedName);
        if ($person) {
            increase_rep($person['hash'], floor($points / 100));
            unset($scrapedData[$name]);
        } else {
            $userArray = array(
                'twitchName' => $trimmedName,
                'rep' => floor($points / 100), 
            );
            addUserToDB($userArray);
            unset($scrapedData[$name]);
        }
    }
    update_post_meta($userManagementPageID, 'scrapedData', $scrapedData);
}

?>