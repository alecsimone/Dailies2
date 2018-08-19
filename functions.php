<?php 

add_theme_support( 'post-thumbnails' );
add_image_size('small', 350, 800);
add_theme_support( 'title-tag' );

$thisDomain = get_site_url();

require_once( __DIR__ . '/Functions/helpers.php');
require_once( __DIR__ . '/Functions/scriptSetup.php');
require_once( __DIR__ . '/Functions/restModifications.php');
require_once( __DIR__ . '/Functions/postDataObj.php');
require_once( __DIR__ . '/Functions/voting.php');
require_once( __DIR__ . '/Functions/votenumberOperations.php');
require_once( __DIR__ . '/Functions/gardenOperations.php');
require_once( __DIR__ . '/Functions/liveOperations.php');
require_once( __DIR__ . '/Functions/postManagement.php');
require_once( __DIR__ . '/Functions/dataGenerators.php');
require_once( __DIR__ . '/Functions/userProfileModifications.php');

//These  are commented out because they only needed to be run once, but I still want a record of them.
//$role = get_role( 'contributor' );
//$role->add_cap( 'publish_posts' ); 
//$role->add_cap( 'edit_published_posts' );

//Get the twitch user db, on each one if there's a dailies account and no rep, get the picture from the dailies account, add to db. If there's no dailies account, set rep to 1. Then update db.
//$twitchUserDB = getTwitchUserDB();
/*foreach ($twitchUserDB as $user => $data) {
	if ($data['dailiesUserID'] !== 'none') {
		if (!array_key_exists('rep', $data)) {
			$twitchUserDB[$user]['rep'] = getValidRep($data['dailiesUserID']);
		}
	} else {
		if (!array_key_exists('rep', $data)) {
			$twitchUserDB[$user]['rep'] = 1;
		}
	}
}
updateTwitchUserDB($twitchUserDB); */
//print_r($twitchUserDB);

?>