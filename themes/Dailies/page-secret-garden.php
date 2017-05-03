<?php get_header();
include( locate_template('schedule.php') );
$gardenPostObject = get_page_by_path('secret-garden');
$gardenID = $gardenPostObject->ID;
$cutList = get_post_meta($gardenID, 'cut', true);
foreach ($cutList as $cutClipSlug => $cutClipTime) {
	$currentTime = time();
	$timeAgo = ($currentTime * 1000) - $cutClipTime;
	$hoursAgo = $timeAgo / 1000 / 3600;
	if ($hoursAgo > 24) {
		unset($cutList[$cutClipSlug]);
	}
} 
update_post_meta($gardenID, 'cut', $cutList);
$cutVods = get_post_meta($gardenID, 'cutVods', true);
$cutVodIndex = 0;
foreach ($cutVods as $cutVod) {
	$currentTime = time();
	$timeAgo = ($currentTime * 1000) - $cutVod['clipCreatedAt'];
	$hoursAgo = $timeAgo / 1000 / 3600;
	if ($hoursAgo > 24) {	
		unset($cutVods[$cutVodIndex]);
	};
	$cutVodIndex++;
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

<div id="garden" data-streams="<?php echo $streamList; ?>" data-view-thresholds='<?php echo json_encode($streamViewThresholds); ?>' data-cut='<?php echo json_encode($cutList); ?>' data-cut-vods='<?php echo json_encode($cutVods); ?>'>
</div>

<script>
function clipGetter(cursor) {
	var garden = jQuery('#garden');
	var streamList = garden.attr('data-streams');
	var cutList = garden.attr('data-cut');
	var cutObj = JSON.parse(cutList);
	var cutKeys = Object.keys(cutObj);
	var cutVodsRaw = garden.attr('data-cut-vods');
	var cutVods = JSON.parse(cutVodsRaw);
	var viewThresholdsRaw = garden.attr('data-view-thresholds');
	var viewThresholds = JSON.parse(viewThresholdsRaw);
	if (typeof cursor == 'string') {
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
					var thisVODTimestamp = 3600 * thisHourCount + 60 * thisMinuteCount + thisSecondCount;
					var sameVODIndexes = [];
					for (var vodCounter = 0; vodCounter < cutVods.length; vodCounter++) {
						if ( cutVods[vodCounter]['VODBase'] === thisVODBase ) {
							sameVODIndexes.push(vodCounter);
						}
					}
					for (var timestampCounter = 0; timestampCounter < sameVODIndexes.length; timestampCounter++) {
						var currentIndex = sameVODIndexes[timestampCounter];
						var timeDifference = thisVODTimestamp - cutVods[currentIndex]['VODTimestamp'];
						if ( -15 <= timeDifference && timeDifference <= 15 ) {
							var dupe = true;
						} else {
							var dupe = false;
						}
					};
				};
				if ( thisGame == 'Rocket League' && jQuery.inArray(thisSlug, cutKeys) == -1 && !dupe && thisViewCount >= thisViewThreshold ) {
					garden.append(
						`<div class='seedling' data-source='${thisSource}'>
							<div class='seedling-meta'>
								<img src='${thisLogo}' class='seedling-logo'>
								<div class="seedling-meta-info">
									<div class='seedling-title' data-slug='${thisSlug}' data-time='${thisTime}'>
										<a href='https://clips.twitch.tv/${thisSlug}' target='_blank'>${thisTitle}</a>
									</div>
									<div class='seedlingAddTitleBox'><input type='text' class='seedling-title-input' name='addTitleBox' placeholder='Keep?'></div>
									<div class='seedling-controls'>
										<div class='seedling-cross'><img class='seedCutter seedControlImg' src='http://dailies.gg/wp-content/uploads/2017/04/red-x.png'></div>
										<div class='seedling-views'>${thisViewCount} views. clipped by ${thisCurator} about ${hoursAgo} hours ago. <a href='${thisVODLink}' target='_blank' data-vodbase='${thisVODBase}' data-vodtimestamp='${thisVODTimestamp}'>VOD Link</a></div>
									</div>
								</div>
							</div>
							<div class='seedlingEmbedTarget'></div>
						</div>`
					);
				} else {
					cutCount++;
				};
			};
			if (clipCount == 100) {
				if (cutCount > 90) {
					clipGetter(newCursor);
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

jQuery("#garden").on('click', '.seedling-cross', function() {
	var thisX = jQuery(this);
	var thisSeedling = thisX.parent().parent().parent().parent();
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
	thisX.fadeOut();
	cutSlug(thisSlug, thisTime, thisSeedling, thisVODBase, thisVODTimestamp);
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