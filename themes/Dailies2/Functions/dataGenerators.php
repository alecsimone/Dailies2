<?php 
function getLiveContenders() {
	$resetTime = getResetTime();
	$livePostArgs = array(
		'category_name' => 'contenders',
		'posts_per_page' => 50,
		'order' => 'asc',
		'date_query' => array(
			array(
			//	'after' => '240 hours ago',
				'after' => $resetTime,
			)
		)
	);
	return get_posts($livePostArgs);
}
function getResetTime() {
	$liveID = getPageIDBySlug('live');
	$resetTime = get_post_meta($liveID, 'liveResetTime', true);
	$resetTime = $resetTime / 1000;
	$wordpressUsableTime = date('c', $resetTime);
	return $wordpressUsableTime;
}

function generateUserData() {
	$userID = get_current_user_id();
	$personRow = getPersonInDB($userID);
	if ($userID === 0) {
		$userPic = get_site_url() . '/wp-content/uploads/2017/03/default_pic.jpg';
	} else {
		$userPic = $personRow['picture'];
	}
	$personData = array(
		'userID' => $userID,
		'userName' => $personRow['dailiesDisplayName'],
		'userRep' => $personRow['rep'],
		'userRepTime' => $personRow['lastRepTime'],
		'userRole' => $personRow['role'],
		'clientIP' => $_SERVER['REMOTE_ADDR'],
		'userPic' => $userPic,
		'hash' => $personRow['hash'],
	);
	return $personData;
}

function generateDayOneData() {
	date_default_timezone_set('UTC');
	$today = new DateTime();
	$year = $today->format('Y');
	$month = $today->format('n');
	$day = $today->format('j');

	$dayOneArgs = array(
		'category_name' => 'noms',
		'posts_per_page' => 10,
		'orderby' => 'meta_value_num',
		'meta_key' => 'votecount',
		'date_query' => array(
			array(
				'year'  => $year,
				'month' => $month,
				'day'   => $day,
				),
			),
		);
	$postDataNoms = get_posts($dayOneArgs);

	$i = 0;
	while ( !$postDataNoms && $i < 14 ) :
		$today->add(DateInterval::createFromDateString('yesterday'));
		$year = $today->format('Y');
		$month = $today->format('n');
		$day = $today->format('j');
		$newNomArgs = array(
			'category_name' => 'noms',
			'posts_per_page' => 10,
			'orderby' => 'meta_value_num',
			'meta_key' => 'votecount',
			'date_query' => array(
				array(
					'year'  => $year,
					'month' => $month,
					'day'   => $day,
					),
				),
			);
		$postDataNoms = get_posts($newNomArgs);
		$i++;
	endwhile;
	$dayOnePostDatas = [];
	$dayOneVoteDataArray = [];
	foreach ($postDataNoms as $post) {
		setup_postdata($post);
		$postData = buildPostDataObject($post->ID);
		$dayOnePostDatas[] = $postData;
		$dayOneVoteDataArray[$post->ID] = array(
			'voteledger' => get_post_meta($post->ID, 'voteledger', true),
			'guestlist' => get_post_meta($post->ID, 'guestlist', true),
			'votecount' => get_post_meta($post->ID, 'votecount', true),
			);
		$dayOneVoteData = json_encode($dayOneVoteDataArray);
	}	
	$dayOnePostData = array(
		'date' => array(
			'year'  => $year,
			'month' => $month,
			'day'   => $day,
			),
		'postDatas' => $dayOnePostDatas,
		'voteDatas' => $dayOneVoteData,
		);
	return $dayOnePostData;
}

function generateFirstWinner() {
	$winnerArgs = array(
		'tag' => 'winners',
		'category_name' => 'noms',
		'posts_per_page' => 1,
		);
	$postDataWinners = get_posts($winnerArgs);
	$post = $postDataWinners[0];
	setup_postdata($post); 
	$winnerDataObject = buildPostDataObject($post->ID);
	$winnerVoteData = array(
		'voteledger' => get_post_meta($post->ID, 'voteledger', true),
		'guestlist' => get_post_meta($post->ID, 'guestlist', true),
		'votecount' => get_post_meta($post->ID, 'votecount', true),
		);
	$firstWinnerData = array(
		'voteData' => $winnerVoteData,
		'postData' => $winnerDataObject,
	);
	return $firstWinnerData;
}

function generateArchiveHeaderData() {
	$thisTerm = get_queried_object();
	$headerData = array(
		'thisTerm' => $thisTerm,
		'logo_url' => get_term_meta($thisTerm->term_id, 'logo', true),
		'twitter' => get_term_meta($thisTerm->term_id, 'twitter', true),
		'twitch' => get_term_meta($thisTerm->term_id, 'twitch', true),
		'youtube' => get_term_meta($thisTerm->term_id, 'youtube', true),
		'website' => get_term_meta($thisTerm->term_id, 'website', true),
		'discord' => get_term_meta($thisTerm->term_id, 'discord', true),
		'donate' => get_term_meta($thisTerm->term_id, 'donate', true),
	);
	return $headerData;
}

function generateYourVotesHeaderData() {
	$thisTerm = 'Your Votes';
	$userID = get_current_user_id();
	$personRow = getPersonInDB($userID);
	$headerData = array(
		'thisTerm' => $thisTerm,
		'logo_url' => $personRow['picture'],
	);
	return $headerData;
}

function generateSearchHeaderData() {
	$thisTerm = get_search_query();
	$headerData = array(
		'thisTerm' => $thisTerm,
	);
	return $headerData;
}

function generateInitialArchivePostData() {
	$thisTerm = get_queried_object();
	$orderby = get_query_var('orderby', 'date');
	$order = get_query_var('order', 'ASC');

	$archiveArgs = array(
		'posts_per_page' => 10,
		'category_name' => 'noms',
		'paged' => $paged,
		'orderby' => $orderby,
		'order' => $order,
		'meta_key' => 'votecount',
		'tax_query' => array(
			array(
				'taxonomy' => $thisTerm->taxonomy,
				'field' => 'slug',
				'terms' => $thisTerm->slug,
				)
			), 
		);
	if ($thisTerm->taxonomy === 'post_tag') {
		unset($archiveArgs['tax_query']);
		$archiveArgs['tag'] = $thisTerm->slug;
	}
	$archivePostDatas = get_posts($archiveArgs);
	$initialPostData = [];
	$initialVoteDataArray = [];
	foreach ($archivePostDatas as $post) {
		setup_postdata($post);
		$postData = buildPostDataObject($post->ID);
		$initialPostDatas[] = $postData;
		$initialVoteDataArray[$post->ID] = array(
			'voteledger' => get_post_meta($post->ID, 'voteledger', true),
			'guestlist' => get_post_meta($post->ID, 'guestlist', true),
			'votecount' => get_post_meta($post->ID, 'votecount', true),
		);
	}
	$initialVoteData = $initialVoteDataArray;
	$initialPostData = $initialPostDatas;
	$orderby = get_query_var('orderby', 'date');
	if ($orderby = 'meta_value_num') {
		$orderby = 'meta_value_num&filter[meta_key]=votecount';
	}
	$initialArchiveData = array(
		'voteData' => $initialVoteData,
		'postData' => $initialPostData,
		'orderby' => $orderby,
		'order' => get_query_var('order', 'ASC'),
	);
	return $initialArchiveData;
}

function generateYourVotesPostData() {
	$yourVotesIDs = getPersonVoteIDs(get_current_user_id());
	$yourVotesArgs = array(
		'posts_per_page' => 10,
		'paged' => $paged,
		'post__in' => $yourVotesIDs,
	);
	$archivePostDatas = get_posts($yourVotesArgs);
	$initialPostData = [];
	$initialVoteDataArray = [];
	foreach ($archivePostDatas as $post) {
		setup_postdata($post);
		$postData = buildPostDataObject($post->ID);
		$initialPostDatas[] = $postData;
		$initialVoteDataArray[$post->ID] = array(
			'voteledger' => get_post_meta($post->ID, 'voteledger', true),
			'guestlist' => get_post_meta($post->ID, 'guestlist', true),
			'votecount' => get_post_meta($post->ID, 'votecount', true),
		);
	}
	$initialVoteData = $initialVoteDataArray;
	$initialPostData = $initialPostDatas;
	$orderby = get_query_var('orderby', 'date');
	if ($orderby = 'meta_value_num') {
		$orderby = 'meta_value_num&filter[meta_key]=votecount';
	}
	$initialArchiveData = array(
		'voteData' => $initialVoteData,
		'postData' => $initialPostData,
		'orderby' => $orderby,
		'order' => get_query_var('order', 'ASC'),
	);
	return $initialArchiveData;
}

function generateSearchResultsData() {
	global $wp_query;
	$searchResultPostObjects = $wp_query->posts;
	$searchResultIDs = [];
	foreach ($searchResultPostObjects as $post) {
		$searchResultIDs[] = $post->ID;
	}
	$initialPostDatas = [];
	$initialVoteData = [];
	foreach ($searchResultIDs as $postID) {
		$postData = buildPostDataObject($postID);
		$initialPostDatas[] = $postData;
		$initialVoteData[$postID] = array(
			'voteledger' => get_post_meta($postID, 'voteledger', true),
			'guestlist' => get_post_meta($postID, 'guestlist', true),
			'votecount' => get_post_meta($postID, 'votecount', true),
		);
	}
	$initialSearchData = array(
		'voteData' => $initialVoteData,
		'postData' => $initialPostDatas,
	);
	return $initialSearchData;
}

function generateSingleData() {
	$post = get_post();
	$postData = buildPostDataObject($post->ID);
	$voteData[$post->ID] = array(
		'voteledger' => get_post_meta($post->ID, 'voteledger', true),
		'guestlist' => get_post_meta($post->ID, 'guestlist', true),
		'votecount' => get_post_meta($post->ID, 'votecount', true),
	);
	$singleData = array(
		'postData' => $postData,
		'voteData' => $voteData,
	);
	return $singleData;
}

function generateStreamList() {
	include( locate_template('schedule.php') );
	$todaysChannels = $schedule[$todaysSchedule];
	$streamList = array();
	foreach ($todaysChannels as $channel) {
		$twitchWholeURL = get_term_meta($channel[2], 'twitch', true);
		$twitchChannel = substr($twitchWholeURL, 22);
		$viewThreshold = get_term_meta($channel[2], 'viewThreshold', true);
		if ($viewThreshold == '') {$viewThreshold = "0";};
		$viewThresholds[$twitchChannel] = $viewThreshold;
		$streamList[$twitchChannel] = array(
			'viewThreshold' => $viewThreshold,
			'cursor' => 'none',
		);
	}
	//Check if there are any user submits, if there are add an entry streamList['user_submits']
	$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;
	$userSubmits = get_post_meta($gardenID, 'userSubmitData', true);
	if ($userSubmits !== '') {
		$streamList['User_Submits'] = array(
			'viewThreshold' => 0,
			'cursor' => 'none',
		);
	}
	$streamList['Cuts'] = array(
			'viewThreshold' => 0,
			'cursor' => 'none',
		);

	return $streamList;
}

add_action( 'wp_ajax_generateCutSlugsHandler', 'generateCutSlugsHandler' );
function generateCutSlugsHandler() {
	$cutSlugList = generateCutSlugs();
	echo json_encode($cutSlugList);
	wp_die();
}
function generateCutSlugs() {
	$gardenPostObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPostObject->ID;
	$globalSlugList = get_post_meta($gardenID, 'slugList', true);
	$queryHours = get_post_meta($gardenID, 'queryHours', true);
	if ($globalSlugList === '') {
		$globalSlugList = array();
	} elseif (empty($globalSlugList)) {
		$globalSlugList = array();
	};
	$globalSlugIndexes = array_keys($globalSlugList);
	foreach ($globalSlugIndexes as $slugIndex) {
		$currentTime = time();
		$slugTime = $globalSlugList[$slugIndex]['createdAt'];
		$timeAgo = ($currentTime * 1000) - $slugTime;
		$hoursAgo = $timeAgo / 1000 / 60 / 60;
		if ($hoursAgo >= 168) {
			unset($globalSlugList[$slugIndex]);
		};
	};
	update_post_meta($gardenID, 'slugList', $globalSlugList);

	$userID = get_current_user_id();
	$userSlugList = get_user_meta($userID, 'slugList', true);
	if ($userSlugList === '') {
		$userSlugList = array();
	} elseif (empty($userSlugList)) {
		$userSlugList = array();
	};
	$userSlugIndexes = array_keys($userSlugList);
	foreach ($userSlugIndexes as $slugIndex) {
		$currentTime = time();
		$slugTime = $userSlugList[$slugIndex]['createdAt'];
		$timeAgo = ($currentTime * 1000) - $slugTime;
		$hoursAgo = $timeAgo / 1000 / 3600;
		if ($hoursAgo >= 168) {
			unset($userSlugList[$slugIndex]);
		};
	};
	update_user_meta($userID, 'slugList', $userSlugList);

	$slugList = array_merge($globalSlugList, $userSlugList);
	foreach ($globalSlugIndexes as $slugIndex) {
		$slugLikes = $globalSlugList[$slugIndex]['likeIDs'];
		$slugList[$slugIndex]['likeIDs'] = $slugLikes;
	}

	return $slugList;
}

function generateSubmissionSeedlingsData() {
	$gardenPageObject = get_page_by_path('secret-garden');
	$gardenID = $gardenPageObject->ID;
	$submissionSeedlingData = get_post_meta($gardenID, 'userSubmitData', true);
	return $submissionSeedlingData;
}

function convertTwitchTimeToTimestamp($twitchTime) {
	return date("U",strtotime($twitchTime));
}

function getCurrentUsersSeenSlugs() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'seen_slugs_db';

	$hash = getPersonsHash(get_current_user_id());

	$seenSlugs = $wpdb->get_results(
		"
		SELECT *
		FROM $table_name
		WHERE hash = '$hash'
		",
		ARRAY_A
	);

	foreach ($seenSlugs as $key => $slugData) {
		if (time() - $slugData['time'] > 60 * 60 * 24 * 7) {
			deleteJudgmentFromSeenSlugsDB($slugData['id']);
			unset($seenSlugs[$key]);
		}
	}
	return $seenSlugs;
}

function generateChannelChangerData() {
	include( locate_template('schedule.php') );
	$todaysChannels = $schedule[$todaysSchedule];
	$channelData = [];
	foreach ($todaysChannels as $name => $channel) {
		$channelData[$name]['displayName'] = $channel[0];
		$channelData[$name]['slug'] = $channel[1];
		$channelData[$name]['logo'] = get_term_meta($channel[2], 'logo', true);
		$channelData[$name]['details'] = $channel[3];
		$channelData[$name]['time'] = $channel[4];
		$channelData[$name]['twitchURL'] = get_term_meta($channel[2], 'twitch', true);
		$channelData[$name]['active'] = false;
	}
	return $channelData;
}

function generateLivePostsData() {
	$livePageObject = get_page_by_path('live');
	$liveID = $livePageObject->ID;
	$resetTime = get_post_meta($liveID, 'liveResetTime', true);
	$resetTime = $resetTime / 1000;
	$wordpressUsableTime = date('c', $resetTime);
	$livePostArgs = array(
		'category__not_in' => 4,
		'posts_per_page' => 50,
		'date_query' => array(
			array(
			//	'after' => '240 hours ago',
				'after' => $wordpressUsableTime,
			)
		)
	);
	$postDataLive = get_posts($livePostArgs);
	$postDatas = [];
	foreach ($postDataLive as $post) {
		$postID = $post->ID;
		$postDatas[$postID] = buildPostDataObject($postID, 'postDataObj', true);
	}
	//$postDatas = array_reverse($postDatas);
	return $postDatas;
}

function generateLiveVoteData() {
	$livePostArgs = array(
		'category__not_in' => 4,
		'posts_per_page' => 50,
		'date_query' => array(
			array(
				'after' => '240 hours ago',
			)
		)
	);
	$postDataLive = get_posts($livePostArgs);
	$voteDatas = [];
	foreach ($postDataLive as $post) {
		$postID = $post->ID;
		$voteDatas[$postID] = array(
			'score' => get_post_meta($postID, 'votecount', true),
			'voteledger' => get_post_meta($postID, 'voteledger', true),
			'guestlist' => get_post_meta($postID, 'guestlist', true),
		);
	}
	return $voteDatas;
}

function generateHopefulsData() {
	$hopefulsData = getHopefuls();
	return $hopefulsData;
}

function generateCohostData() {
	//cohost slugs: 'dazerin', 'inanimatej', 'ninjarider'
	$cohosts = [];
	$cohostData = [];
	foreach ($cohosts as $cohost) {
		$hostObject = get_term_by('slug', $cohost, 'stars');
		$hostID = $hostObject->term_id;
		$cohostData[$cohost]['hostName'] = $hostObject->name;
		$cohostData[$cohost]['slug'] = $cohost;
		$cohostData[$cohost]['logo_url'] = get_term_meta($hostID, 'logo', true);
		$cohostData[$cohost]['links']['twitter_url'] = get_term_meta($hostID, 'twitter', true);
		$cohostData[$cohost]['links']['twitch_url'] = get_term_meta($hostID, 'twitch', true);
		$cohostData[$cohost]['links']['youtube_url'] = get_term_meta($hostID, 'youtube', true);
		$cohostData[$cohost]['links']['donate_url'] = get_term_meta($hostID, 'donate', true);
	}
	return $cohostData;
} 

function stepBackDate($steps) {
	global $year;
	global $month;
	global $day;
	$thirtyDays = array(4, 6, 9, 11); //these are the numbers of the months with 30 days
	$extraSteps = $day - $steps;
	if ($day > $steps) {
		$day = $day - $steps;
	} else {
		if ( $month == 1 ) {
			$month = 12;
			$year = $year - 1;
		} else {
			//$month = $month - 1;
		}
		if ( in_array($month, $thirtyDays) ) {
			$day = 30 + $extraSteps;
		} elseif ($month == 2) {
			$day = 28 + $extraSteps;
		} else {
			$day = 31 + $extraSteps;
		}
	}
}

?>