<?php
function pull_all_clips() {
	pull_twitter_mentions();
	pull_twitch_clips();
	update_option("lastClipUpdateTime", time());
}

function getQueryPeriod() {
	$lastUpdateTime = get_option("lastClipUpdateTime");
	if (!$lastUpdateTime) {
		$weedPageID = getPageIDBySlug('weed');
		$lastUpdateTime = get_post_meta($weedPageID, 'lastClipTime', true);
	}
	$currentTime = time();
	$queryLength = $currentTime - $lastUpdateTime;
	if ($queryLength >= 60 * 60 * 24) {
		return "week";
	} else {
		return "day";
	}
}

function pull_twitch_clips() {
	$queryPeriod = getQueryPeriod();
	$cutoffTime = clipCutoffTimestamp();
	$clipsArray = get_twitch_clips("game=Rocket%20League", $queryPeriod)->clips;
	$tournamentsArray = generateTodaysStreamlist();
	foreach ($tournamentsArray as $streamName) {
		if ($streamName === "Rocket_Dailies") {continue;}
		$target = "channel=" . $streamName;
		$theseClips = get_twitch_clips($target, $queryPeriod);
		$clipsArray = array_merge($clipsArray, $theseClips->clips);
	}
	$existingClipData = getCleanPulledClipsDB();
	foreach ($clipsArray as $key => $clipData) {
		if ($clipData->game !== "Rocket League") {
			unset($clipsArray[$key]);
			continue;
		}
		if ((int)$clipData->views < 3) {
			unset($clipsArray[$key]);
			continue;
		}
		if ((int)convertTwitchTimeToTimestamp($clipData->created_at) < $cutoffTime) {
			unset($clipsArray[$key]);
			continue;
		}
		if ((int)$existingClipData[$clipData->slug]['nuked'] === 1) {
			unset($clipsArray[$key]);
			continue;
		}
		if ($clipData->vod) {
			$vodlink = $clipData->vod->url;
		} else {
			$vodlink = "none";
		}
		if ($clipData->thumbnails) {
			$thumb = $clipData->thumbnails->medium;
		} else {
			$thumb = null;
		}
		$thisClipArray = array(
			'slug' => $clipData->slug,
			'title' => $clipData->title,
			'views' => $clipData->views,
			'age' => $clipData->created_at,
			'source' => $clipData->broadcaster->display_name,
			'sourcepic' => $clipData->broadcaster->logo,
			'vodlink' => $vodlink,
			'clipper' => $clipData->curator->display_name,
			'score' => 0,
			'votecount' => 0,
			'thumb' => $thumb,
			'type' => "twitch",
		);

		if ($existingClipData[$clipData->slug]!== null) {
			$thisClipArray['score'] = $existingClipData[$clipData->slug]['score'];
			$thisClipArray['votecount'] = $existingClipData[$clipData->slug]['votecount'];
		}

		if ($existingClipData[$clipData->slug]) {
			editPulledClip($thisClipArray);
			continue;
		} else {
			$addSlugSuccess = addSlugToDB($thisClipArray);
			continue;
		}
	}
}

function generateTodaysStreamlist() {
	include( locate_template('schedule.php') );

	$todaysStreams = generateStreamListForDay($todaysSchedule);
	$todaysIndex = array_search($todaysSchedule, $myWeekdays);
	if ($todaysIndex === 0) {
		$yesterdaysIndex = count($myWeekdays) - 1;
	} else {
		$yesterdaysIndex = $todaysIndex - 1;
	}
	$yesterday = $myWeekdays[$yesterdaysIndex];
	$yesterdaysStreams = generateStreamListForDay($yesterday);

	$combinedStreams = array_merge($yesterdaysStreams, $todaysStreams);
	return $combinedStreams;
}
function generateStreamlistForDay($day) {
	include( locate_template('schedule.php') );
	$todaysChannels = $schedule[$day];
	$streamList = array();
	foreach ($todaysChannels as $channel) {
		$twitchWholeURL = get_term_meta($channel[2], 'twitch', true);
		$twitchChannel = substr($twitchWholeURL, 22);
		$streamList[] = $twitchChannel;
	}
	return $streamList;
}

function get_twitch_clips($target, $queryPeriod) {
	$url = "https://api.twitch.tv/kraken/clips/top?" . $target . "&period=" . $queryPeriod . "&limit=100";

	global $privateData;
	$args = array(
		"headers" => array(
			"Client-ID" => $privateData['twitchClientID'],
			'Accept' => 'application/vnd.twitchtv.v5+json',
		),
	);

	$response = wp_remote_get($url, $args);
	$responseBody = json_decode($response['body']);
	return $responseBody;
}

function pull_twitter_mentions() {
	$url = 'https://api.twitter.com/1.1/statuses/mentions_timeline.json';

	$authorization = generateTwitterAuthorization($url, "get");

	$args = array(
		"headers" => array(
			"Authorization" => $authorization,
		),
	);

	$response = wp_remote_get($url, $args);
	$responseBody = json_decode($response['body']);

	$cutoffTimestamp = clipCutoffTimestamp();
	foreach ($responseBody as $key => $tweetData) {
		// basicPrint($key);
		$tweetTimestamp = date(U, strtotime($tweetData->created_at));
		if ($tweetTimestamp < $cutoffTimestamp) {continue;}
		$tweetURL = "https://twitter.com/" . $tweetData->user->screen_name . "/status/" . $tweetData->id_str;
		if ($tweetData->in_reply_to_status_id_str) {
			$parentTweet = getTweet($tweetData->in_reply_to_status_id_str);
			if ($parentTweet->user->screen_name === "Rocket_Dailies") {continue;}
			$parentTweetTimestamp = date(U, strtotime($parentTweet->created_at));
			if ($parentTweetTimestamp < $cutoffTimestamp) {continue;}
			if ( tweetIsProbablySubmission($parentTweet) ) {
				$submission = submitTweet($parentTweet);
			}
		}

		if ($tweetData->entities->urls) {
			foreach ($tweetData->entities->urls as $urlArray) {
				if (!strpos($urlArray->expanded_url, 'twitter.com/i/') && strpos($urlArray->expanded_url, '/status/') >= 0) {
					$linkedTweetID = turnURLIntoTwitterCode($urlArray->expanded_url);
					$linkedTweet = getTweet($linkedTweetID);
					if ($linkedTweet->user->screen_name === "Rocket_Dailies") {continue;}
					$linkedTweetTimestamp = date(U, strtotime($linkedTweet->created_at));
			if ($linkedTweetTimestamp < $cutoffTimestamp) {continue;}
					if ( tweetIsProbablySubmission($linkedTweet) ) {
						$submission = submitTweet($linkedTweet);
					}
				}
			}
		}

		if ( tweetIsProbablySubmission($tweetData) ) {
			$submission = submitTweet($tweetData);
		}

	// basicPrint("------------------------------------");
	};
}

function pull_twitter_timeline($timeline) {

}

function tweetIsProbablySubmission($tweetData) {
	$entities = $tweetData->entities;
	if ($entities->media) {
		return true;
	}
	if ($entities->urls) {
		foreach ($entities->urls as $urlArray) {
			if (strpos($urlArray->expanded_url, "clips.twitch.tv") || strpos($urlArray->expanded_url, "gfycat.com") || strpos($urlArray->expanded_url, "youtube.com") ||strpos($urlArray->expanded_url, "youtu.be")) {
				return true;
			}
		}
	}
}

function generateTwitterAuthorization($url, $method) {
	global $privateData;
	$OAuth = array(
		urlencode("oauth_consumer_key") => $privateData['twitterConsumerKey'],
		urlencode("oauth_nonce") => generateString(),
		urlencode("oauth_signature_method") => "HMAC-SHA1",
		urlencode("oauth_timestamp") => time(),
		urlencode("oauth_token") => $privateData['twitterAccessToken'],
		urlencode("oauth_version") => "1.0",
	);
	
	$signature = createTwitterOauthSignature($url, $OAuth, $method);
	$OAuth['oauth_signature'] = $signature;
	ksort($OAuth);

	$authorization = "OAuth ";
	foreach ($OAuth as $key => $value) {
		$authorization .= urlencode($key) . '="' . urlencode($value) . '", ';
	}
	$authorization = substr($authorization, 0, strlen($authorization) - 2);

	return $authorization;
}

function createTwitterOauthSignature($url, $OAuth, $method) {
	$signatureBaseString = strtoupper($method) . "&" . urlencode($url) . "&";

	foreach ($OAuth as $key => $value) {
		$parameterString .= $key . "=" . $value . "&";
	}
	$parameterString = substr($parameterString, 0, strlen($parameterString) - 1);

	$signatureBaseString .= urlencode($parameterString);

	global $privateData;
	$signingKey = urlencode($privateData['twitterConsumerSecret']) . "&" . urlencode($privateData['twitterAccessTokenSecret']);

	$signature = base64_encode(hash_hmac("sha1", $signatureBaseString, $signingKey, true));
	return $signature;
}

function addSlugToDB($slugData) {
	global $wpdb;
	$table_name = $wpdb->prefix . "pulled_clips_db";
	
	$clipArray = array(
		'slug' => $slugData['slug'],
		'title' => $slugData['title'] ? remove_emoji($slugData['title']) : "No Title",
		'views' => $slugData['views'] ? $slugData['views'] : 0,
		'age' => $slugData['age'] ? $slugData['age'] : 0,
		'source' => $slugData['source'] ? $slugData['source'] : 'unknown',
		'sourcepic' => $slugData['sourcepic'] ? $slugData['sourcepic'] : 'unknown',
		'vodlink' => $slugData['vodlink'] ? $slugData['vodlink'] : 'none',
		'thumb' => $slugData['thumb'],
		'clipper' => $slugData['clipper'] ? $slugData['clipper'] : 'unknown',
		'votecount' => $slugData['votecount'] ? $slugData['votecount'] : 0,
		'score' => $slugData['score'] ? $slugData['score'] : 0,
		'nuked' => $slugData['nuked'] ? $slugData['nuked'] : 0,
		'type' => $slugData['type'],
	);

	$insertionSuccess = $wpdb->insert(
		$table_name,
		$clipArray
	);
	if ($insertionSuccess) {
		return $insertionSuccess;
	} else {
		return $wpdb->last_error;
	}
}

?>