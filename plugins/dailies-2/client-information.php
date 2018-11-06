<?php

add_action("wp_enqueue_scripts", "client_information");
function client_information() {
	$version = '-v1.931';
	if (is_page('weed') || is_page('1r') || is_page('scout')) {
		wp_register_script( 'weedScripts', get_template_directory_uri() . '/Bundles/weed-bundle' . $version . '.js', ['jquery'], '', true );
		$weedData = generateWeedData();
		wp_localize_script('weedScripts', 'weedData', $weedData);
		wp_enqueue_script('weedScripts');
	}
}

function generateWeedData() {
	$weedDataArray = array();
	$weedDataArray['streamList'] = generateTodaysStreamlist();
	$lastUpdateTime = get_option("lastClipUpdateTime");
	if (!$lastUpdateTime) {
		$weedPageID = getPageIDBySlug('weed');
		$lastUpdateTime = get_post_meta($weedPageID, 'lastClipTime', true);
	}
	$weedDataArray['lastUpdate'] = $lastUpdateTime;
	$weedDataArray['cutoffTimestamp'] = clipCutoffTimestamp();
	$weedDataArray['clips'] = getCleanPulledClipsDB();
	$weedDataArray['seenSlugs'] = getCurrentUsersSeenSlugs();
	
	return $weedDataArray;

	// $weedDataArray['goodStreams'] = ["jessie", "scrub", "johnnyboi_i", "SubParButInHD", "orionrl", "callumtheshogun", "dazerin", "achievestv", "primethunderrl", "napp", "deevorl", "maestro", "vincerl", "sizz", "drippay", "halcyon", "chicago_rl", "godsmilla", "familiarleaf", "freakiirl", "mognus1", "gschwind", "memoryrl", "jwismont", "lethamyr_rl", "fairypeak", "metsanauris", "frinteerspot", "allushin", "dareyck", "dudewiththenose", "satthew", "paschy90", "gregan", "turbopolsa", "miztik", "corruptedg", "sebadam2011", "greazymeister", "killerno7", "bluey", "atr_realize", "al0t97", "timi_f", "insolences", "moses", "chrome", "turtle", "garrettg", "wavepunk", "jknapsrl", "snaski", "plutorl", "tormentrl", "espeon", "karmaah", "remkoe", "liefx", "lawler", "jamesbot", "squishymuffinz", "jacobrl", "dappur", "klassux", "findablecarpet", "seismicwhite", "markydooda", "fireburner", "sad_junior", "doomsee", "lachinio", "rizzo", "jhzer", "kronovi", "kuxir97", "m1k3rules"];
	// $currentTime = time();
	// $fiveMinutesAgo = $currentTime - 5 * 60;
	// $oneMinuteAgo = $currentTime - 60;
	// if ($lastUpdateTime < $oneMinuteAgo) {
	// 	$needsFreshQuery = 'true';
	// } else {
	// 	$needsFreshQuery = 'false';
	// }
	// $weedDataArray['needsFreshQuery'] = $needsFreshQuery;
	// $weedDataArray['lastNomTime'] = getLastNomTimestamp();
	// $weedDataArray['totalClips'] = count($weedDataArray['clips']);
}

?>