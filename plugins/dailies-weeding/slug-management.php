<?php 
add_action( 'wp_ajax_store_pulled_clips', 'store_pulled_clips' );
function store_pulled_clips() {
	$clipsArray = $_POST['clips'];
	if (count($clipsArray) === 0) {
		killAjaxFunction("No clips from this stream");
	}

	foreach ($clipsArray as $slug => $slugData) {
		$existingSlug = getSlugInPulledClipsDB($slug);
		if ($existingSlug !== null) {
			$slugData['score'] = $existingSlug['score'];
			$slugData['nuked'] = $existingSlug['nuked'];
			$slugData['votecount'] = $existingSlug['votecount'];
			editPulledClip($slugData);
			continue;
		} else {
			$slugData['score'] = 0;
			$slugData['nuked'] = 0;
			$slugData['votecount'] = 0;
			$addSlugSuccess = addSlugToDB($slugData);
		}
	}

	$weedPageID = getPageIDBySlug('weed');
	update_post_meta($weedPageID, 'lastClipTime', time());

	global $wpdb;
	killAjaxFunction($clipsArray);
}

function getSlugInPulledClipsDB($slug) {
	global $wpdb;
	$table_name = $wpdb->prefix . "pulled_clips_db";

	$slugData = $wpdb->get_row(
		"SELECT *
		FROM $table_name
		WHERE slug = '$slug'
		", ARRAY_A
	);
	return $slugData;
}

function deleteSlugFromPulledClipsDB($slug) {
	global $wpdb;
	$table_name = $wpdb->prefix . "pulled_clips_db";

	$where = array(
		'slug' => $slug,
	);

	$wpdb->delete($table_name, $where);
}
function deleteJudgmentFromSeenSlugsDB($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "seen_slugs_db";

	$where = array(
		'id' => $id,
	);

	$wpdb->delete($table_name, $where);
}

// $pulledClipsDB = getPulledClipsDB();
// foreach ($pulledClipsDB as $key => $clipData) {
// 	if ($clipData['score'] != '0' && $clipData['votecount'] == 0) {
// 		basicPrint($key);
// 		$clipData['votecount'] = 1;
// 		editPulledClip($clipData);
// 	}
// }

function getHopefuls() {
	global $wpdb;
	$table_name = $wpdb->prefix . "pulled_clips_db";

	$pulledClipsDB = $wpdb->get_results(
		"
		SELECT *
		FROM $table_name
		WHERE nuked = 0 AND score > 0
		",
		ARRAY_A
	);

	return $pulledClipsDB;
}

function nukeSlug($slug) {
	$slugToNuke = getSlugInPulledClipsDB($slug);
	if ($slugToNuke === null) {
		$slugData = array(
			'slug' => $slug,
			'nuked' => 1,
		);
		addSlugToDB($slugData);
	} else {
		$slugToNuke['nuked'] = 1;
		editPulledClip($slugToNuke);
	}
	return $slug;

	// $time = time() * 1000;
	// $slugObj = array(
	// 	'slug' => $slug,
	// 	'createdAt' => $time,
	// 	'cutBoolean' => true,
	// 	'VODBase' => "null",
	// 	'VOD' => "null",
	// );
	// $gardenPostObject = get_page_by_path('secret-garden');
	// $gardenID = $gardenPostObject->ID;
	// $globalSlugList = get_post_meta($gardenID, 'slugList', true);
	// $newGlobalSlugList = $globalSlugList;
	// $newGlobalSlugList[$slug] = $slugObj;
	// update_post_meta($gardenID, 'slugList', $newGlobalSlugList );
}

add_action( 'wp_ajax_nuke_slug', 'nuke_slug_handler' );
function nuke_slug_handler() {
	$slugToNuke = $_POST['slug'];
	nukeSlug($slugToNuke);
	nukeAllDupeSlugs($slug);
	killAjaxFunction($slugToNuke);
}

function editPulledClip($clipArray) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'pulled_clips_db';

	$where = array(
		'slug' => $clipArray['slug'],
	);

	$wpdb->update(
		$table_name,
		$clipArray,
		$where
	);
}

function store_slug_judgment($person, $slug, $judgment, $vodlink) {
	$vote = 0;
	if ($judgment === 'strongNo') {
		$vote = -2;
	} elseif ($judgment === 'weakNo') {
		$vote = -1;
	} elseif ($judgment === 'weakYes') {
		$vote = 1;
	} elseif ($judgment === 'strongYes') {
		$vote = 2;
	}
	if ($vote === 0) {return;}

	$seenClipArray = array(
		'hash' => getPersonsHash($person),
		'slug' => $slug,
		'vote' => $vote,
		'vodlink' => $vodlink,
		'time' => time(),
	);

	$previousJudgment = get_slug_judgment($person, $slug);
	global $wpdb;
	$table_name = $wpdb->prefix . 'seen_slugs_db';
	if ($previousJudgment === null) {
		$wpdb->insert($table_name, $seenClipArray);
	} else {
		$where = array(
			'hash' => getPersonsHash($person),
			'slug' => $slug,
		);
		$wpdb->update($table_name, $seenClipArray, $where);
	}
	return $wpdb->last_error;
}

function get_slug_judgment($person, $slug) {
	$hash = getPersonsHash($person);

	global $wpdb;
	$table_name = $wpdb->prefix . 'seen_slugs_db';

	$slugJudgment = $wpdb->get_row(
		"SELECT *
		FROM $table_name
		WHERE hash = '$hash' AND slug = '$slug'
		",
		ARRAY_A
	);
	return $slugJudgment;
}

function get_dupe_clips($string) {
	if (!strpos($string, '/videos/')) {
		$slugData = getSlugInPulledClipsDB($string);
		if ($slugData === null) {
			return "Slug not found";
		}
		$string = $slugData['vodlink'];
	}

	if ($string === "none") {
		return "That slug doesn't have a vodlink";
	}
	
	$slugMoment = convertVodlinkToMomentObject($string);

	global $wpdb;
	$table_name = $wpdb->prefix . "pulled_clips_db";
	$vodlinkQuery = "https://www.twitch.tv/videos/" . $slugMoment['vodID'] . '%';

	$sameVodSlugs = $wpdb->get_results(
		"
		SELECT slug, vodlink
		FROM $table_name
		WHERE vodlink LIKE '$vodlinkQuery'
		"
	);

	$dupeSlugs = [];
	foreach ($sameVodSlugs as $key => $slugAndLink) {
		$thisMoment = convertVodlinkToMomentObject($slugAndLink->vodlink);
		$thisTime = $thisMoment['vodTime'];
		if ((int)$thisTime + 25 >= (int)$slugMoment['vodTime'] && (int)$thisTime - 25 <= (int)$slugMoment['vodTime']) {
			$dupeSlugs[] = $slugAndLink->slug;
		}
	}

	return $dupeSlugs;
}

function convertVodlinkToMomentObject($vodlink) {
	$vodIDStart = strpos($vodlink, '/videos/') + 8;
	$vodIDEnd = strpos($vodlink, '?t=');
	$vodID = substr($vodlink, $vodIDStart, $vodIDEnd - $vodIDStart);

	$vodTimeStart = $vodIDEnd + 3;
	$vodTime = substr($vodlink, $vodTimeStart);
	$hIndex = strpos($vodTime, 'h');
	if ($hIndex) {
		$hours = substr($vodTime, 0, $hIndex);
	} else {
		$hours = 0;
	}
	$mIndex = strpos($vodTime, 'm');
	if ($mIndex) {
		if ($hIndex) {
			$minutes = substr($vodTime, $hIndex + 1, $mIndex - $hIndex - 1);
		} else {
			$minutes = substr($vodTime, 0, $mIndex);
		}
	} else {
		$minutes = 0;
	}
	$sIndex = strpos($vodTime, 's');
	if ($sIndex) {
		if ($mIndex) {
			$seconds = substr($vodTime, $mIndex + 1, $sIndex - $mIndex - 1);
		} elseif ($hIndex) {
			$seconds = substr($vodTime, $hIndex + 1, $sIndex - $hIndex - 1);
		} else {
			$seconds = substr($vodTime, 0, $sIndex);
		}
	} else {
		$sIndex = 0;
	}
	
	$vodTime = (int)$seconds + 60 * (int)$minutes + 3600 * (int)$hours;

	$momentObject = array(
		'vodID' => $vodID,
		'vodTime' => $vodTime,
	);
	return $momentObject;
}

function nukeAllDupeSlugs($slug) {
	$dupes = get_dupe_clips($slug);
	if (is_array($dupes)) {
		foreach ($dupes as $key => $dupeSlug) {
			if ($dupeSlug !== $slug) {
				nukeSlug($dupeSlug);
			}
		}
	} else {
		return $dupes;
	}
}

?>