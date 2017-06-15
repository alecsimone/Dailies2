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
	<div class="garden-top">
		<div class="sgInfo">
			<p class='clipCount'>Returned: <span class='clipCounter'>0</span>
			<p class='clipCount'>Cut: <span class='cutCounter'>0</span>
		</div>
		<div class="sgAddStream">
			<input type='text' class='sgAddStreamInput' name='addStreamInput' placeholder='Add Stream?'>
		</div>
		<div class="sgButtons">
			<button id="injectRL" class="sgButton">Add RL</button>
		</div>
	</div>
</section>
<button class='moreClips'>Load More</button>

<script>
function clipGetter(queryCursorPairsArray) {
	var canPublish = garden.attr('data-user-can-publish');
	if (canPublish === 'true') {
		var nuke = "<button class='universalCut'>Nuke</button>";
		var nukeAndNext = "<button class='nukeAndNext extraButton'>N</button>"
		var keep = "<div class='seedlingAddTitleBox'><input type='text' class='seedling-title-input' name='addTitleBox' placeholder='Who and Why?'><button class='keepbutton'>nom</button></div>"
	} else {
		var nuke = '';
		var nukeAndNext = '';
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
	if (typeof queryCursorPairsArray == 'object') {
		var combinedData = {};	
		var datas = [];
		var queries = Object.keys(queryCursorPairsArray);
		var queryCounter = 0;
		var killQueryIndex = [];
		jQuery.each(queries, function() {
		 	if (queryCursorPairsArray[this] !== 'noCursor') {
		 		var thisCursor = queryCursorPairsArray[this];
				var queryCursor = `&cursor=${thisCursor}`;
			} else {
				queryCursor = '';
			}
			if (queryCursorPairsArray[this] !== 'done') {
				var queryURL = `https://api.twitch.tv/kraken/clips/top?${this}&period=day&limit=100${queryCursor}`;
				var ajax = jQuery.ajax({
					type: 'GET',
					url: queryURL,
					headers: {
						'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
						'Accept' : 'application/vnd.twitchtv.v5+json',
					},
				});
				datas.push(ajax);
		 	} else {
		 		killQueryIndex.push(queryCounter);
		 	}
		 	queryCounter++;
		});
		if (killQueryIndex.length > 0) {
			jQuery.each(killQueryIndex, function() {
				queries.splice(this, 1);
			})
		};

		jQuery.when.apply(jQuery, datas).then(function() {
			var allClips = [];
			var cursorCounter = 0;
			combinedData['cursors'] = {};
			jQuery.each(datas, function() {
				var clipData = JSON.parse(this.responseText);
				jQuery.each(clipData.clips, function() {
					allClips.push(this);
				})
				var cursorQuery = queries[cursorCounter];
				combinedData['cursors'][cursorQuery] = clipData['_cursor'];
				cursorCounter++;
			})
			function clipsByViews(a,b) {
				viewsA = a['views'];
				viewsB = b['views'];
				return viewsB - viewsA;
			}
			allClipsSorted = allClips.sort(clipsByViews);
			combinedData['clips'] = allClipsSorted;
			parseClips(combinedData);
		}) 
	}
	function parseClips(data) {
		console.log("Hot clips comin your way!");
		var clips = data['clips'];
		var cursors = data['cursors'];
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
					`<div class='seedling' id='${thisSlug}-seedling' data-source='${thisSource}' data-views='${thisViewCount}'>
						<div class='seedling-controls'>
							<a href="${thisWholeSource}/clips" target="_blank"><img src='${thisLogo}' class='seedling-logo'></a>
							<div class="cutVoteContainer">
								<div class='seedling-vote'><img class='seedVoter seedControlImg hoverReplacer' src='http://dailies.gg/wp-content/uploads/2017/04/Vote-Icon-line.png' data-replace-src='http://dailies.gg/wp-content/uploads/2016/12/Medal-small-100.png'></div>
								<div class='seedling-cross'><img class='seedCutter seedControlImg' src='http://dailies.gg/wp-content/uploads/2017/04/red-x.png'></div>
							</div>
							${nuke}
						</div>
						<div class="seedling-extra-buttons">
							${nukeAndNext}
							<button class="cutAndNext extraButton">X</button>
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
		var seedlings = garden.children('.seedling');
		seedlings.sort(function(a,b) {
			var aviews = a.getAttribute("data-views");
			var bviews = b.getAttribute("data-views");
			return bviews - aviews;
		});
		seedlings.detach().appendTo(garden);
		var moreButton = jQuery('.moreClips');
		var oldQueryCursorPairsString = moreButton.attr("data-queries-cursors-array");
		if (oldQueryCursorPairsString !== undefined) {
			var oldQueryCursorPairs = JSON.parse(oldQueryCursorPairsString);
		} else {
			var oldQueryCursorPairs = {};
		};
		var newQueryCursorPairs = oldQueryCursorPairs;
		var moreCursorsCounter = 0;
		jQuery.each(queries, function() {
			var cursorCheck = cursors[this];
			if (cursorCheck === undefined) {
				console.log(cursorCheck);
			} else if (cursorCheck.length > 0) {
				newQueryCursorPairs[this] = cursorCheck;
				moreButton.fadeIn();
			} else {
				newQueryCursorPairs[this] = 'done';
			}
		})
		var encodedQueryCursorObject = JSON.stringify(newQueryCursorPairs);
		moreButton.attr("data-queries-cursors-array", encodedQueryCursorObject);
		var clipCounterSpan = jQuery('.clipCounter');
		var oldClipCount = parseInt(clipCounterSpan.text());
		var newClipCounter = oldClipCount + clipCount;
		clipCounterSpan.text(newClipCounter);

		var cutCounterSpan = jQuery('.cutCounter');
		var oldCutCount = parseInt(cutCounterSpan.text());
		var newCutCounter = oldCutCount + cutCount;
		cutCounterSpan.text(newCutCounter);
	};
}
var queryCursorPairsArray = {};
var garden = jQuery('#garden');
var streamList = garden.attr('data-streams');
var query = 'channel=' + streamList;
var streamCount = 1;
var pos = query.indexOf(',');
while (pos !== -1) {
	streamCount++;
	pos = query.indexOf(',', pos + 1);
	if (streamCount === 10) {
		querySplitIndex = pos;
	}
}
if (streamCount > 10) {
	var queryTwo = 'channel=' + query.substring(querySplitIndex + 1);
	queryCursorPairsArray[queryTwo] = 'noCursor';
	query = query.substring(0,querySplitIndex);
}
queryCursorPairsArray[query] = 'noCursor';
//queryCursorPairsArray['game=Rocket%20League'] = 'noCursor';
jQuery(window).load( clipGetter(queryCursorPairsArray) );

jQuery("#garden").on('click', '.seedling-title', function() {
	event.preventDefault();
	var thisTitle = jQuery(this);
	var thisSeedling = thisTitle.parent().parent();
	expandSeedling(thisSeedling);	
});
function expandSeedling(seedling) {
	var thisSeedlingMeta = seedling.find('.seedling-meta');
	var thisSeedlingMetaWidth = thisSeedlingMeta.width();
	var heightByWidth = thisSeedlingMetaWidth / 16 * 9;
	var viewportHeight = jQuery(window).height();
	var baseSeedlingHeight = seedling.outerHeight();
	var heightByViewport = viewportHeight - baseSeedlingHeight - 48;
	if (heightByViewport < heightByWidth) {
		var thisEmbedHeight = heightByViewport;
	} else {
		var thisEmbedHeight = heightByWidth;
	}
	var thisEmbedTarget = seedling.find('.seedlingEmbedTarget');
	var thisNuke = seedling.find('.universalCut');
	var thisVote = seedling.find('.seedling-vote');
	var thisCut = seedling.find('.seedling-cross');
	var thisExtraButtons = seedling.find('.seedling-extra-buttons');
	var thisCutVoteContainer = thisCut.parent();
	if ( thisEmbedTarget.is(':empty') ) {
		var thisTitle = seedling.find('.seedling-title');
		var thisSlug = thisTitle.attr("data-slug");
		var embedCode = `<iframe src="https://clips.twitch.tv/embed?clip=${thisSlug}&autoplay=true" width="100%" height="${thisEmbedHeight}" frameborder="0" scrolling="no" allowfullscreen="true"></iframe>`
		thisEmbedTarget.html(embedCode);
		thisNuke.fadeIn();
		thisVote.fadeIn();
		thisCut.fadeIn();
		thisExtraButtons.fadeIn();
		thisExtraButtons.css("display", "flex");
		thisCutVoteContainer.css("height", "36px");
	} else {
		thisEmbedTarget.html('');
		thisNuke.css("display", "none");
		thisVote.css("display", "none");
		thisCut.css("display", "none");
		thisExtraButtons.css("display", "none");
		thisCutVoteContainer.css("height", "0px");
	}
}

function cutSeed(thisSeedling, scope) {
	var thisTitle = thisSeedling.find('.seedling-title');
	var thisSlug = thisTitle.attr("data-slug");
	var thisTime = thisTitle.attr("data-time");
	var thisVODLink = thisSeedling.find('.seedling-views a');
	var thisVODBase = thisVODLink.attr("data-vodbase");
	var thisVODTimestamp = thisVODLink.attr("data-vodtimestamp");
	var button = thisSeedling.find('.seedCutter');
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
	cutSeed(thisSeedling, 'everyone');
});

jQuery("#garden").on('click', '.seedling-cross', function() {
	var thisButton = jQuery(this);
	var thisSeedling = thisButton.parent().parent().parent();
	var garden = jQuery("#garden");
	var userID = garden.attr("data-user-id");
	cutSeed(thisSeedling, userID);
});

jQuery("#garden").on('click', '.seedling-vote', function() {
	var thisButton = jQuery(this);
	var thisSeedling = thisButton.parent().parent().parent();
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
		plantSeed(thisSeedling);
	}
});
jQuery("#garden").on('click', '.keepbutton', function() {
	var thisKeepButton = jQuery(this);
	var thisSeedling = thisKeepButton.parent().parent().parent();
	plantSeed(thisSeedling);
});

function plantSeed(thisSeedling) {
	console.log("we doin it this way yall");
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

jQuery("body").on('click', 'button.moreClips', function() {
	var thisLink = jQuery(this);
	var queryCursorObjectString = thisLink.attr("data-queries-cursors-array");
	var queryCursorObject = JSON.parse(queryCursorObjectString);
	clipGetter(queryCursorObject);
	thisLink.fadeOut();
});

jQuery("#garden").on('click', '#injectRL', function() {
	var thisbtn = jQuery(this);
	if (!thisbtn.hasClass('injected')) {
		console.log("getting rocket league clips!");
		var addRocket = {};
		addRocket['game=Rocket%20League'] = 'noCursor';
		clipGetter(addRocket);
		thisbtn.css("opacity", ".3");
		thisbtn.addClass('injected');
	}

});

jQuery("#garden").on('keypress', '.sgAddStreamInput', function(e) {
	if(e.which === 13) {
		var thisInput = jQuery(this);
		var input = thisInput.val();
		var loadMoreBtn = jQuery('.moreClips');
		var existingQueries = loadMoreBtn.attr("data-queries-cursors-array");
		var queryCheck = existingQueries.indexOf(input);
		if (queryCheck < 0) {
			var query = `channel=${input}`;
			var addStream = {};
			addStream[query] = 'noCursor';
			clipGetter(addStream);
		} else {
			console.log("You already added that stream, dipshit");
		}
		thisInput.val('');
	}
});

jQuery(window).on('click', function(e) {
	jQuery('.focus').removeClass('focus');
}); 
jQuery("#garden").on('click', '.seedling', function(e) {
	jQuery('.focus').removeClass('focus');
	jQuery(this).addClass('focus');
	e.stopPropagation();
});
jQuery(window).on('keypress', function(e) {
	var focusedSeedling = jQuery('.seedling.focus');
	if (focusedSeedling.length) {
		var nextSeedling = focusedSeedling.next();
		focusedSeedling.removeClass('focus');
		if (e.which === 120) {
			cutAndNext(focusedSeedling);
		} else if (e.which === 110) {
			nukeAndNext(focusedSeedling);
		}
		nextSeedling.addClass('focus');
		expandSeedling(nextSeedling);
	}
})
jQuery("#garden").on('click', '.cutAndNext', function() {
	var focusedSeedling = jQuery('.seedling.focus');
	if (focusedSeedling.length) {
		var nextSeedling = focusedSeedling.next();
		focusedSeedling.removeClass('focus');
		cutAndNext(focusedSeedling);
		nextSeedling.addClass('focus');
		expandSeedling(nextSeedling);
	}
});
jQuery("#garden").on('click', '.nukeAndNext', function() {
	var focusedSeedling = jQuery('.seedling.focus');
	if (focusedSeedling.length) {
		var nextSeedling = focusedSeedling.next();
		focusedSeedling.removeClass('focus');
		nukeAndNext(focusedSeedling);
		nextSeedling.addClass('focus');
		expandSeedling(nextSeedling);
	}
});
function cutAndNext(focusedSeedling) {	
	var garden = jQuery('#garden');
	var userID = garden.attr("data-user-id");
	cutSeed(focusedSeedling, userID);
}
function nukeAndNext(focusedSeedling) {
	var garden = jQuery('#garden');
	var canNuke = garden.attr("data-user-can-publish");
	if (canNuke === 'true') {
		cutSeed(focusedSeedling, 'everyone');
	}
}

</script>

<?php }; //this is closing the conditional that keeps out unwelcome guests 
get_footer(); ?>