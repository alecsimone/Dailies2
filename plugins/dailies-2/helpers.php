<?php

function clipTypeDetector($clipURLRaw) {
	$clipURL = strtolower($clipURLRaw);
	$isTwitch = strpos($clipURL, 'twitch');
	$isYouTube = strpos($clipURL, 'youtube');
	$isYtbe = strpos($clipURL, 'youtu.be');
	$isTwitter = strpos($clipURL, 'twitter');
	$isGfy = strpos($clipURL, 'gfycat');

	if ($isTwitch !== false ) {
		return 'twitch';
	} elseif ($isYouTube !== false) {
		return 'youtube';
	} elseif ($isYtbe !== false) {
		return 'ytbe';
	} elseif ($isTwitter !== false) {
		return 'twitter';
	} elseif ($isGfy !== false) {
		return 'gfycat';
	}
}

function turnURLIntoTwitchCode($url) {
	$unCasedUrl = strtolower($url);
	if (!strpos($unCasedUrl, 'clips.twitch.tv')) {
		return false;
	}

	$twitchCodePosition = strpos($unCasedUrl, 'twitch.tv/') + 10;
	if (strpos($unCasedUrl, '?')) {
		$twitchCodeEnd = strpos($unCasedUrl, '?');
		$twitchCodeLength = $twitchCodeEnd - $twitchCodePosition;
		$twitchCode = substr($url, $twitchCodePosition, $twitchCodeLength);
	} else {
		$twitchCode = substr($url, $twitchCodePosition);
	}

	return $twitchCode;
}

function turnURLIntoYoutubeCode($url) {
	$unCasedUrl = strtolower($url);
	if (!strpos($unCasedUrl, 'youtube.com/watch?v=')) {
		return false;
	}

	$youtubeCodePosition = strpos($unCasedUrl, 'youtube.com/watch?v=') + 20;
	if (strpos($unCasedUrl, '&')) {
		$youtubeCodeEndPosition = strpos($unCasedUrl, '&');
		$youtubeCodeLength = $youtubeCodeEndPosition - $youtubeCodePosition;
		$youtubeCode = substr($url, $youtubeCodePosition, $youtubeCodeLength);
	} else {
		$youtubeCode = substr($url, $youtubeCodePosition);
	}

	return $youtubeCode;
}

function turnURLIntoYtbeCode($url) {
	$unCasedUrl = strtolower($url);
	if (!strpos($url, 'youtu.be/')) {
		return false;
	}

	$youtubeCodePosition = strpos($unCasedUrl, 'youtu.be/') + 9;
	if (strpos($unCasedUrl, '?')) {
		$youtubeCodeEndPosition = strpos($unCasedUrl, '?');
		$youtubeCodeLength = $youtubeCodeEndPosition - $youtubeCodePosition;
		$youtubeCode = substr($url, $youtubeCodePosition, $youtubeCodeLength);
	} else {
		$youtubeCode = substr($url, $youtubeCodePosition);
	}

	return $youtubeCode;
}

function turnURLIntoTwitterCode($url) {
	$unCasedUrl = strtolower($url);
	if (!strpos($unCasedUrl, 'twitter.com/') || !strpos($unCasedUrl, '/status/')) {
		return false;
	}
	$twitterCodePosition = strpos($unCasedUrl, '/status/') + 8;
	$twitterCode = substr($url, $twitterCodePosition);

	return $twitterCode;
}

function turnURLIntoGfycode($url) {
	$unCasedUrl = strtolower($url);
	if (!strpos($unCasedUrl, 'gfycat.com/')) {
		return false;
	}

	if (strpos($unCasedUrl, '/detail/')) {
		$gfyCodePosition = strpos($unCasedUrl, '/detail/') + 8;
		if (strpos($unCasedUrl, '?')) {
			$gfyCodeEndPosition = strpos($unCasedUrl, '?');
			$gfyCodeLength = $gfyCodeEndPosition - $gfyCodePosition;
			$gfyCode = substr($url, $gfyCodePosition, $gfyCodeLength);
		} else {
			$gfyCode = substr($url, $gfyCodePosition);
		}
	} else {
		$gfyCodePosition = strpos($unCasedUrl, 'gfycat.com/') + 11;
		if (strpos($unCasedUrl, '?')) {
			$gfyCodeEndPosition = strpos($unCasedUrl, '?');
			$gfyCodeLength = $gfyCodeEndPosition - $gfyCodePosition;
			$gfyCode = substr($url, $gfyCodePosition, $gfyCodeLength);
		} elseif (strpos($unCasedUrl, '.mp4')) {
			$gfyCodeEndPosition = strpos($unCasedUrl, '.mp4');
			$gfyCodeLength = $gfyCodeEndPosition - $gfyCodePosition;
			$gfyCode = substr($url, $gfyCodePosition, $gfyCodeLength);
		} else {
			$gfyCode = substr($url, $gfyCodePosition);
		}
	}

	return $gfyCode;
}

function sourceFinder($channelURL) {
	$sourceArgs = array(
		'taxonomy' => 'source'
	);
	$sources = get_terms($sourceArgs);
	$sourceID = 632; //632 is User Submits
	foreach ($sources as $source) {
		$key = get_term_meta($source->term_id, 'twitch', true);
		if (strcasecmp($key, $channelURL) == 0) {
			$sourceID = $source->term_id;
		}
	}
	return $sourceID;
}

function starChecker($thingTitle) {
	$titleWords = explode(" ", $thingTitle);
	$starNickname = strtolower($titleWords[0]);
	$starNickLength = strlen($starNickname);
	$star_args = array(
		'taxonomy' => 'stars',
	);
	$stars = get_terms($star_args);
	$postStar = 'X';
	$singleStar = true;
	foreach ($stars as $star) {
		$starSlug = $star->slug;
		$starShortSlug = substr($starSlug, 0, $starNickLength);
		if ($starShortSlug == $starNickname && $singleStar) {
			$postStar = $star->term_id;
			$singleStar = false;
		} elseif ($starShortSlug == $starNickname && !$singleStar) {
			$postStar = 'X';
		}
	};
	return $postStar;
}

?>