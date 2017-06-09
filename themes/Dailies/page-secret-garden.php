<?php get_header();
$currentUser = get_current_user_id();
if ( !current_user_can('edit_posts') ) {
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
//update_post_meta($gardenID, 'slugList', '');

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
//update_user_meta($currentUser, 'slugList', '');

$slugList = array_merge($globalSlugList, $userSlugList);
foreach ($globalSlugIndexes as $slugIndex) {
	$slugLikes = $globalSlugList[$slugIndex]['likeIDs'];
	$slugList[$slugIndex]['likeIDs'] = $slugLikes;
}

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

<section id="garden" data-streams="<?php echo $streamList; ?>" data-view-thresholds='<?php echo json_encode($streamViewThresholds); ?>' data-slugs='<?php echo json_encode($slugList); ?>' data-user-id='<?php echo json_encode($currentUser); ?>' data-user-can-publish='<?php echo json_encode($canPublish); ?>'>
	<div class="sgButtons">
		<button id="injectRL" class="sgButton">Add RL</button>
	</div>
</section>

<script>
function clipGetter(query, cursor, queryTwo, cursorTwo) {
	var canPublish = garden.attr('data-user-can-publish');
	if (canPublish === 'true') {
		var nuke = "<button class='universalCut'>Nuke</button>";
		var keep = "<div class='seedlingAddTitleBox'><input type='text' class='seedling-title-input' name='addTitleBox' placeholder='Keep?'></div>"
	} else {
		var nuke = '';
		var keep = '';
	}
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
	var streamCount = 1;
	var pos = query.indexOf(',');
	while (pos !== -1) {
		streamCount++;
		pos = query.indexOf(',', pos + 1);
		if (streamCount === 10) {
			querySplitIndex = pos;
		}
	}
	console.log(`You queried ${streamCount} streams`);
	if (streamCount > 10) {
		queryTwo = 'channel=' + query.substring(querySplitIndex + 1);
		query = query.substring(0,querySplitIndex);
		console.log(`queryTwo is ${queryTwo}`);
	}
	if (typeof queryTwo === 'undefined') {
		if (typeof cursor == 'string' && cursor != 'false') {
			var queryURL = `https://api.twitch.tv/kraken/clips/top?${query}&period=day&limit=100&cursor=${cursor}`;
		} else {
			var queryURL = `https://api.twitch.tv/kraken/clips/top?${query}&period=day&limit=100`;
		}
		jQuery.ajax({
			type: 'GET',
			url: queryURL,
			headers: {
				'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
				'Accept' : 'application/vnd.twitchtv.v5+json',
			},
			success: function(data) {
				cursor = data['_cursor'];
				parseClips(data);
			},
			error: function() {
				console.log("Request Denied!");
			}
		})
	} else {
		if (typeof cursor == 'string' && cursor != 'false') {
			var queryURL = `https://api.twitch.tv/kraken/clips/top?${query}&period=day&limit=100&cursor=${cursor}`;
		} else {
			var queryURL = `https://api.twitch.tv/kraken/clips/top?${query}&period=day&limit=100`;
		}
		if (typeof cursorTwo == 'string' && cursorTwo != 'false') {
			var queryTwoURL = `https://api.twitch.tv/kraken/clips/top?${queryTwo}&period=day&limit=100&cursor=${cursorTwo}`;
		} else {
			var queryTwoURL = `https://api.twitch.tv/kraken/clips/top?${queryTwo}&period=day&limit=100`;
		}
		jQuery.ajax({
			type: 'GET',
			url: queryURL,
			headers: {
				'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
				'Accept' : 'application/vnd.twitchtv.v5+json',
			},
			success: function(dataOne) {
				jQuery.ajax({
					type: 'GET',
					url: queryTwoURL,
					headers: {
						'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
						'Accept' : 'application/vnd.twitchtv.v5+json',
					},
					success: function(dataTwo) {
						var combinedData = {};
						allClips = dataOne['clips'].concat(dataTwo['clips']);
						function clipsByViews(a,b) {
							viewsA = a['views'];
							viewsB = b['views'];
							return viewsB - viewsA;
						}
						allClipsSorted = allClips.sort(clipsByViews);
						combinedData['clips'] = allClipsSorted;
						combinedData['cursor'] = dataOne['_cursor'];
						combinedData['cursorTwo'] = dataTwo['_cursor'];
						cursor = dataOne['_cursor'];
						cursorTwo = dataTwo['_cursor'];
						console.log(combinedData);
						parseClips(combinedData);
					},
					error: function() {
						console.log("Request Denied!");
					}
				})
			},
			error: function() {
				console.log("Request Denied!");
			}
		})
	};
	function parseClips(data) {
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
			if (typeof slugObj[thisSlug] !== 'undefined') {
				var thisVoters = slugObj[thisSlug]['likeIDs'];
				var thisVoteCount = thisVoters.length;
				if (typeof thisVoteCount === 'undefined') {
					thisVoteCount = 0;
				}
			} else {
				var thisVoteCount = 0;
			};
			if (thisVoteCount === 0) {
				var thisVoteScore = '';
				var thisVotersObj = '0';
			} else {
				var thisVoteScore = `(+${thisVoteCount})`;
				var thisVotersObj = JSON.stringify(thisVoters);
			}
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
					if (thisVODBase === cutMoments[momentCounter]['VODBase'] && thisVODTimestamp + 15 >= cutMoments[momentCounter]['VODTime'] && thisVODTimestamp - 15 <= cutMoments[momentCounter]['VODTime'] ) {
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
						<div class='seedling-controls'>
							<a href="${thisWholeSource}/clips" target="_blank"><img src='${thisLogo}' class='seedling-logo'></a>
							<div class='seedling-vote'><img class='seedVoter seedControlImg hoverReplacer' src='http://dailies.gg/wp-content/uploads/2017/04/Vote-Icon-line.png' data-replace-src='http://dailies.gg/wp-content/uploads/2016/12/Medal-small-100.png'></div>
							<div class='seedling-cross'><img class='seedCutter seedControlImg' src='http://dailies.gg/wp-content/uploads/2017/04/red-x.png'></div>
							${nuke}
						</div>
						<div class='seedling-meta'>
							<div class='seedling-title' data-slug='${thisSlug}' data-time='${thisTime}' data-voters='${thisVotersObj}'>
								<a href='https://clips.twitch.tv/${thisSlug}' target='_blank'>${thisVoteScore} ${thisTitle}</a>
							</div>
							<div class='seedling-views'>${thisViewCount} views. clipped by ${thisCurator} about ${hoursAgo} hours ago. <a href='${thisVODLink}' target='_blank' data-vodbase='${thisVODBase}' data-vodtimestamp='${thisVODTimestamp}'>VOD Link</a></div>
							${keep}
							<div class='seedlingEmbedTarget'></div>
						</div>
					</div>`
				);
			};
		};
		if (clipCount >= 100) {
			var clipCounterSpan = jQuery('.clipCounter');
			var oldClipCount = parseInt(clipCounterSpan.text());
			garden.append(`<button class='moreClips' data-query='${query}' data-cursor='${cursor}' data-query-two='${queryTwo}' data-cursor-two='${cursorTwo}'>Load More</button>`);
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
	};
}
var garden = jQuery('#garden');
var streamList = garden.attr('data-streams');
var query = 'channel=' + streamList;
jQuery(window).load( clipGetter(query) );

jQuery("#garden").on('click', '.seedling-title', function() {
	event.preventDefault();
	var thisTitle = jQuery(this);
	var thisSeedling = thisTitle.parent().parent();
	var thisSeedlingMeta = thisSeedling.find('.seedling-meta');
	var thisSeedlingMetaWidth = thisSeedlingMeta.width();
	var heightByWidth = thisSeedlingMetaWidth / 16 * 9;
	var viewportHeight = jQuery(window).height();
	var baseSeedlingHeight = thisSeedling.outerHeight();
	var heightByViewport = viewportHeight - baseSeedlingHeight - 48;
	if (heightByViewport < heightByWidth) {
		var thisEmbedHeight = heightByViewport;
	} else {
		var thisEmbedHeight = heightByWidth;
	}
	var thisEmbedTarget = thisSeedling.find('.seedlingEmbedTarget');
	var thisNuke = thisSeedling.find('.universalCut');
	var thisVote = thisSeedling.find('.seedling-vote');
	var thisCut = thisSeedling.find('.seedling-cross');
	if ( thisEmbedTarget.is(':empty') ) {
		var thisSlug = thisTitle.attr("data-slug");
		var embedCode = `<iframe src="https://clips.twitch.tv/embed?clip=${thisSlug}&autoplay=true" width="100%" height="${thisEmbedHeight}" frameborder="0" scrolling="no" allowfullscreen="true"></iframe>`
		thisEmbedTarget.html(embedCode);
		thisNuke.fadeIn();
		thisVote.fadeIn();
		thisCut.fadeIn();
	} else {
		thisEmbedTarget.html('');
		thisNuke.css("display", "none");
		thisVote.css("display", "none");
		thisCut.css("display", "none");
	}
});

function cutSeed(thisSeedling, button, scope) {
	var thisTitle = thisSeedling.find('.seedling-title');
	var thisSlug = thisTitle.attr("data-slug");
	var thisTime = thisTitle.attr("data-time");
	var thisVODLink = thisSeedling.find('.seedling-views a');
	var thisVODBase = thisVODLink.attr("data-vodbase");
	var thisVODTimestamp = thisVODLink.attr("data-vodtimestamp");
	button.fadeOut();
	cutSlug(thisSlug, thisTime, thisSeedling, thisVODBase, thisVODTimestamp, scope);
}

function tickUpCutCounter() {
	var cutCounterSpan = jQuery('.cutCounter');
	var oldCutCount = parseInt(cutCounterSpan.text());
	var newCutCounter = oldCutCount + 1;
	cutCounterSpan.text(newCutCounter);
}

jQuery("#garden").on('click', '.universalCut', function() {
	var thisButton = jQuery(this);
	var thisSeedling = thisButton.parent().parent();
	cutSeed(thisSeedling, thisButton, 'everyone');
});

jQuery("#garden").on('click', '.seedling-cross', function() {
	var thisButton = jQuery(this);
	var thisSeedling = thisButton.parent().parent();
	var garden = jQuery("#garden");
	var userID = garden.attr("data-user-id");
	cutSeed(thisSeedling, thisButton, userID);
});

jQuery("#garden").on('click', '.seedling-vote', function() {
	var thisButton = jQuery(this);
	var thisSeedling = thisButton.parent().parent();
	var thisTitle = thisSeedling.find('.seedling-title');
	var thisSlug = thisTitle.attr("data-slug");
	var thisTime = thisTitle.attr("data-time");
	var thisVODLink = thisSeedling.find('.seedling-views a');
	var thisVODBase = thisVODLink.attr("data-vodbase");
	var thisVODTimestamp = thisVODLink.attr("data-vodtimestamp");
	var garden = jQuery("#garden");
	var userID = garden.attr("data-user-id");
	thisButton.fadeOut();
	voteSlug(thisSlug, thisTime, thisSeedling, thisVODBase, thisVODTimestamp, userID);
});

jQuery("#garden").on('keypress', '.seedling-title-input', function(e) {
	if(e.which === 13) {
		var thisPlus = jQuery(this);
		thisPlus.attr("disabled", "disabled");
		var thisSeedling = thisPlus.parent().parent().parent();
		var thisTitle = thisSeedling.find('.seedling-title');
		var thisSlug = thisTitle.attr("data-slug");
		var thisTime = thisTitle.attr("data-time");
		var thisVoters = thisTitle.attr("data-voters");
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
		growSeed(thisSlug, thisCustomTitle, thisSource, thisTime, thisSeedling, thisVODBase, thisVODTimestamp, thisVoters);
	}
});

jQuery("#garden").on('click', 'button.moreClips', function() {
	var thisLink = jQuery(this);
	var query = thisLink.attr("data-query");
	var cursor = thisLink.attr("data-cursor");
	var queryTwo = thisLink.attr("data-query-two");
	var cursorTwo = thisLink.attr("data-cursor-two");
	if ( jQuery('.moreClips').attr('data-cursor') && cursor !== 'undefined' ) {
		if ( jQuery('.moreClips').attr('data-cursor-two') && cursorTwo !== 'undefined') {
			clipGetter(query, cursor, queryTwo, cursorTwo);
		} else {
			clipGetter(query, cursor);
		}
	} else if ( jQuery('.moreClips').attr('data-cursor-two') && cursorTwo !== 'undefined') {
		clipGetter(queryTwo, cursorTwo);
	}
	thisLink.fadeOut();
});

jQuery("#garden").on('click', '#injectRL', function() {
	console.log("getting rocket league clips!");
	clipGetter('game=Rocket%20League');
});

</script>

<?php }; //this is closing the conditional that keeps out unwelcome guests 
get_footer(); ?>