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

function validateUserInfo() {
    //When I figure out what needs to be fixed when people login, do it here
}
add_action('wp_login', 'validateUserInfo');

?>