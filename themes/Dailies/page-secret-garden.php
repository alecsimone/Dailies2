<?php get_header();
$acceptedUsers = array(1, 337, 183, 321, 4);
$currentUser = get_current_user_id();
if ( !in_array($currentUser, $acceptedUsers) ) {
	echo "There's nothing here. How did you get here? Turn back now. Maybe try logging in and coming back. But there's definitely nothing here.";
} else { //Sorry. This hurts me too. But not as much as having the whole fucking page indented a tab. Close bracket is at the end, I promise.
include( locate_template('schedule.php') );
$gardenPostObject = get_page_by_path('secret-garden');
$gardenID = $gardenPostObject->ID;

if ( current_user_can('publish_posts', $gardenID) ) {
	$canPublish = true;
} else {
	$canPublish = false;
};

$globalSlugList = get_post_meta($gardenID, 'slugList', true);
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
	$hoursAgo = $timeAgo / 1000 / 3600;
	if ($hoursAgo > 24) {
		unset($globalSlugList[$slugIndex]);
	};
};
update_post_meta($gardenID, 'slugList', $globalSlugList);

$userSlugList = get_user_meta($currentUser, 'slugList', true);
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
	if ($hoursAgo > 24) {
		unset($userSlugList[$slugIndex]);
	};
};
update_user_meta($currentUser, 'slugList', $userSlugList);

$slugList = array_merge($globalSlugList, $userSlugList);

$todaysChannels = $schedule[$todaysSchedule];
$streamList = '';
$streamViewThrewsholds = '';
foreach ($todaysChannels as $channel) {
	$twitchWholeURL = get_term_meta($channel[2], 'twitch', true);
	$twitchChannel = substr($twitchWholeURL, 22);
	$streamList = $streamList . $twitchChannel . ',';
	$viewThreshold = get_term_meta($channel[2], 'viewThreshold', true);
	if ($viewThreshold == '') {$viewThreshold = "0";};
	$streamViewThresholds[$twitchChannel] = $viewThreshold;
}
$streamList = rtrim($streamList,',');
?>

<div id="garden" data-streams="<?php echo $streamList; ?>" data-view-thresholds='<?php echo json_encode($streamViewThresholds); ?>' data-slugs='<?php echo json_encode($slugList); ?>' data-user-id='<?php echo json_encode($currentUser); ?>' data-user-can-publish='<?php echo json_encode($canPublish); ?>'>
</div>

<script>
function clipGetter(cursor) {
	var garden = jQuery('#garden');
	var streamList = garden.attr('data-streams');
	var slugList = garden.attr('data-slugs');
	var slugObj = JSON.parse(slugList);
	var slugs = Object.keys(slugObj);
	cutSlugs = [];
	cutMoments = [];
	if (slugs.length > 0) {
		for (var i = 0; i < slugs.length; i++) {
			if (slugObj[slugs[i]]['cutBoolean'] === true) {
				cutSlugs.push(slugs[i])
				currentVODBase = slugObj[slugs[i]]['VODBase'];
				currentVODTime = slugObj[slugs[i]]['VODTime'];
				cutMoments.push({VODBase:currentVODBase, VODTime:currentVODTime, cutSlug:slugs[i]});
			}
		};
	};
	var viewThresholdsRaw = garden.attr('data-view-thresholds');
	var viewThresholds = JSON.parse(viewThresholdsRaw);
	if (typeof cursor == 'string' && cursor != 'false') {
		var queryURL = `https://api.twitch.tv/kraken/clips/top?channel=${streamList}&period=day&limit=100&cursor=${cursor}`;
	} else {
		var queryURL = `https://api.twitch.tv/kraken/clips/top?channel=${streamList}&period=day&limit=100`;
	}
	jQuery.ajax({
		type: 'GET',
		url: queryURL,
		headers: {
			'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
			'Accept' : 'application/vnd.twitchtv.v5+json',
		},
		success: function(data) {
			console.log("Hot clips comin your way!")
			var clips = data['clips'];
			var clipCount = clips.length;
			var cutCount = 0;
			var currentTime = + new Date();
			for (var i = 0; i < clips.length; i++) {
				var thisSlug = clips[i]['slug'];
				var thisTitle = clips[i]['title'];
				var thisTimeRaw = clips[i]['created_at'];
				var thisTime = Date.parse(thisTimeRaw);
				var timeSince = currentTime - thisTime;
				var hoursAgo = Math.floor(timeSince / 3600000);
				var thisGame = clips[i]['game'];
				var thisWholeSource = clips[i]['broadcaster']['channel_url'];
				var thisSource = thisWholeSource.substring(22);
				var thisViewCount = clips[i]['views'];
				var thisViewThreshold = viewThresholds[thisSource];
				var thisLogo = clips[i]['broadcaster']['logo'];
				var thisCurator = clips[i]['curator']['display_name'];
				var newCursor = data['_cursor'];
				var dupe = false;
				if ( clips[i]['vod'] ) {
					var thisVODLink = clips[i]['vod']['url'];
					var thisTimestampIndex = thisVODLink.lastIndexOf('t=');
					var thisTimestamp = thisVODLink.substring(thisTimestampIndex + 2);
					var thisVODBase = thisVODLink.substring(29, thisTimestampIndex - 1);
					var thisHourMark = thisTimestamp.lastIndexOf('h');
					if (thisHourMark > -1) {
						var thisHourCount = thisTimestamp.substring(0, thisHourMark);	
					} else {
						var thisHourCount = 0;
					}
					var thisMinuteMark = thisTimestamp.lastIndexOf('m');
					if (thisMinuteMark > -1) {
						var thisMinuteCount = thisTimestamp.substring(thisHourMark + 1, thisMinuteMark);	
					} else {
						var thisMinuteCount = 0;
					}
					var thisSecondMark = thisTimestamp.lastIndexOf('s');
					if (thisSecondMark > -1) {
						var thisSecondCount = thisTimestamp.substring(thisMinuteMark + 1, thisSecondMark);	
					} else {
						var thisSecondCount = 0;
					}
					var thisVODTimestamp = 3600 * thisHourCount + 60 * thisMinuteCount + 1 * thisSecondCount;
					for (var momentCounter = 0; momentCounter < cutMoments.length; momentCounter++) {
						if (thisVODBase === cutMoments[momentCounter]['VODBase'] && thisVODTimestamp + 10 >= cutMoments[momentCounter]['VODTime'] && thisVODTimestamp - 10 <= cutMoments[momentCounter]['VODTime'] ) {
							console.log(`${thisSlug} is the same as ${cutMoments[momentCounter]['cutSlug']}`);
							var dupe = true;
						};
					};
				} else {
					var thisVODLink = false;
					var thisVODTimestamp = false;
					var thisVODBase = false;
				};
				if ( thisGame !== 'Rocket League') {
					console.log(`${thisSlug} cut because it wasnt Rocket League`);
					cutCount++;
				} else if ( jQuery.inArray(thisSlug, cutSlugs) !== -1 ) {
					console.log(`${thisSlug} cut because it was in the list of cut plays`);
					cutCount++;
				} else if (dupe) {
					console.log(`${thisSlug} cut because it was the same as another clip`);
					cutCount++;
				} else if (thisViewCount <= thisViewThreshold) {
					console.log(`${thisSlug} cut because it didn't meet the channel's view threshold`);
					thisSourceComma = thisSource + ',';
					pastThresholdChannelIndex = streamList.indexOf(thisSourceComma);
					newStreamlistStart = streamList.substring(0, pastThresholdChannelIndex);
					pastThresholdChannelFinishedIndex = pastThresholdChannelIndex + thisSourceComma.length;
					newStreamlistEnd = streamList.substring(pastThresholdChannelFinishedIndex);
					newStreamlist = newStreamlistStart + newStreamlistEnd;
					garden.attr('data-streams', newStreamlist);
					newCursor = false;
					cutCount++;
				} else {
					garden.append(
						`<div class='seedling' data-source='${thisSource}'>
							<div class='seedling-meta'>
								<a href="${thisWholeSource}/clips" target="_blank"><img src='${thisLogo}' class='seedling-logo'></a>
								<div class="seedling-meta-info">
									<div class='seedling-title' data-slug='${thisSlug}' data-time='${thisTime}'>
										<a href='https://clips.twitch.tv/${thisSlug}' target='_blank'>${thisTitle}</a>
									</div>
									<div class='seedlingAddTitleBox'><input type='text' class='seedling-title-input' name='addTitleBox' placeholder='Keep?'></div>
									<div class='seedling-controls'>
										<div class='seedling-cross'><img class='seedCutter seedControlImg' src='http://dailies.gg/wp-content/uploads/2017/04/red-x.png'></div>
										<div class='seedling-views'>${thisViewCount} views. clipped by ${thisCurator} about ${hoursAgo} hours ago. <a href='${thisVODLink}' target='_blank' data-vodbase='${thisVODBase}' data-vodtimestamp='${thisVODTimestamp}'>VOD Link</a></div>
									</div>
									<div class='personalCut'>Personal Cut</div>
									<div class='seedling-vote'>Vote</div>
								</div>
							</div>
							<div class='seedlingEmbedTarget'></div>
						</div>`
					);
				};
			};
			if (clipCount == 100) {
				var clipCounterSpan = jQuery('.clipCounter');
				var oldClipCount = parseInt(clipCounterSpan.text());
				if (cutCount > oldClipCount + 90) {
					clipGetter(newCursor);
					console.log(newCursor);
					garden.append(`<p class='moreClips' data-cursor='${newCursor}'>Load More</p>`);
				} else {
					garden.append(`<p class='moreClips' data-cursor='${newCursor}'>Load More</p>`);
				}
			}
			if (jQuery('.clipCount').length) {
				var clipCounterSpan = jQuery('.clipCounter');
				var oldClipCount = parseInt(clipCounterSpan.text());
				var newClipCounter = oldClipCount + clipCount;
				clipCounterSpan.text(newClipCounter);

				var cutCounterSpan = jQuery('.cutCounter');
				var oldCutCount = parseInt(cutCounterSpan.text());
				var newCutCounter = oldCutCount + cutCount;
				cutCounterSpan.text(newCutCounter);
			} else {
				garden.prepend(`<p class='clipCount'>Cut: <span class='cutCounter'>${cutCount}</span>`);
				garden.prepend(`<p class='clipCount'>Returned: <span class='clipCounter'>${clipCount}</span>`);
			};
		},
		error: function() {
			console.log("Request Denied!");
		}
	})
}
jQuery(window).load(clipGetter);

jQuery("#garden").on('click', '.seedling-title', function() {
	event.preventDefault();
	var thisTitle = jQuery(this);
	var thisSeedling = thisTitle.parent().parent().parent();
	var thisSeedlingMeta = thisSeedling.find('.seedling-meta');
	var thisSeedlingMetaWidth = thisSeedlingMeta.width();
	var thisEmbedHeight = thisSeedlingMetaWidth / 16 * 9 + 'px';
	var thisEmbedTarget = thisSeedling.find('.seedlingEmbedTarget');
	if ( thisEmbedTarget.is(':empty') ) {
		var thisSlug = thisTitle.attr("data-slug");
		var embedCode = `<iframe src="https://clips.twitch.tv/embed?clip=${thisSlug}&autoplay=true" width="100%" height="${thisEmbedHeight}" frameborder="0" scrolling="no" allowfullscreen="true"></iframe>`
		thisEmbedTarget.html(embedCode);
	} else {
		thisEmbedTarget.html('');
	}
});

function cutSeed(thisSeedling, button, scope) {
	var thisTitle = thisSeedling.find('.seedling-title');
	var thisSlug = thisTitle.attr("data-slug");
	var thisTime = thisTitle.attr("data-time");
	var cutCounterSpan = jQuery('.cutCounter');
	var oldCutCount = parseInt(cutCounterSpan.text());
	var newCutCounter = oldCutCount + 1;
	var thisVODLink = thisSeedling.find('.seedling-views a');
	var thisVODBase = thisVODLink.attr("data-vodbase");
	var thisVODTimestamp = thisVODLink.attr("data-vodtimestamp");
	cutCounterSpan.text(newCutCounter);
	button.fadeOut();
	cutSlug(thisSlug, thisTime, thisSeedling, thisVODBase, thisVODTimestamp, scope);
}

jQuery("#garden").on('click', '.seedling-cross', function() {
	var thisX = jQuery(this);
	var thisSeedling = thisX.parent().parent().parent().parent();
	cutSeed(thisSeedling, thisX, 'everyone');
	
});

jQuery("#garden").on('click', '.personalCut', function() {
	var thisButton = jQuery(this);
	var thisSeedling = thisButton.parent().parent().parent();
	var garden = jQuery("#garden");
	var userID = garden.attr("data-user-id");
	cutSeed(thisSeedling, thisButton, userID);
});

jQuery("#garden").on('click', '.seedling-vote', function() {
	var thisButton = jQuery(this);
	var thisSeedling = thisButton.parent().parent().parent();
	var thisTitle = thisSeedling.find('.seedling-title');
	var thisSlug = thisTitle.attr("data-slug");
	var thisTime = thisTitle.attr("data-time");
	var cutCounterSpan = jQuery('.cutCounter');
	var oldCutCount = parseInt(cutCounterSpan.text());
	var newCutCounter = oldCutCount + 1;
	var thisVODLink = thisSeedling.find('.seedling-views a');
	var thisVODBase = thisVODLink.attr("data-vodbase");
	var thisVODTimestamp = thisVODLink.attr("data-vodtimestamp");
	var garden = jQuery("#garden");
	var userID = garden.attr("data-user-id");
	cutCounterSpan.text(newCutCounter);
	thisButton.fadeOut();
	voteSlug(thisSlug, thisTime, thisSeedling, thisVODBase, thisVODTimestamp, userID);
});

jQuery("#garden").on('keypress', '.seedling-title-input', function(e) {
	if(e.which === 13) {
		var thisPlus = jQuery(this);
		thisPlus.attr("disabled", "disabled");
		var thisSeedling = thisPlus.parent().parent().parent().parent();
		var thisTitle = thisSeedling.find('.seedling-title');
		var thisSlug = thisTitle.attr("data-slug");
		var thisTime = thisTitle.attr("data-time");
		var thisSource = thisSeedling.attr("data-source");
		var thisVODLink = thisSeedling.find('.seedling-views a');
		var thisVODBase = thisVODLink.attr("data-vodbase");
		var thisVODTimestamp = thisVODLink.attr("data-vodtimestamp");
		var thisTextBox = thisSeedling.find('.seedlingAddTitleBox input');
		var thisTextEntry = thisTextBox.val();
		if ( thisTextEntry ) {
			var thisCustomTitle = thisTextEntry;
		} else {
			var thisCustomTitle = thisSlug;
		}
		growSeed(thisSlug, thisCustomTitle, thisSource, thisTime, thisSeedling, thisVODBase, thisVODTimestamp);
	}
});

jQuery("#garden").on('click', 'p.moreClips', function() {
	var thisLink = jQuery(this);
	var thisCursor = thisLink.attr("data-cursor");
	clipGetter(thisCursor);
	thisLink.fadeOut();
});

</script>

<?php }; //this is closing the conditional that keeps out unwelcome guests ?>