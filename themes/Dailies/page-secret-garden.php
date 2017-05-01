<?php get_header();
include( locate_template('schedule.php') );
$gardenPostObject = get_page_by_path('secret-garden');
$gardenID = $gardenPostObject->ID;
$cutList = get_post_meta($gardenID, 'cut', true);
$todaysChannels = $schedule[$todaysSchedule];
$streamList = '';
foreach ($todaysChannels as $channel) {
	$twitchWholeURL = get_term_meta($channel[2], 'twitch', true);
	$twitchChannel = substr($twitchWholeURL, 22);
	$streamList = $streamList . $twitchChannel . ',';
}
$streamList = rtrim($streamList,',');
?>

<div id="garden" data-streams="<?php echo $streamList; ?>" data-cut='<?php echo json_encode($cutList); ?>'>
</div>

<script>
function clipGetter(cursor) {
	var streamList = jQuery('#garden').attr('data-streams');
	var cutList = jQuery('#garden').attr('data-cut');
	var cutObj = JSON.parse(cutList);
	var cutKeys = Object.keys(cutObj);
	if (typeof cursor == 'string') {
		var queryURL = `https://api.twitch.tv/kraken/clips/top?channel=${streamList}&period=day&limit=100&cursor=${cursor}`;
	} else {
		var queryURL = `https://api.twitch.tv/kraken/clips/top?channel=${streamList}&period=day&limit=100`;
	}
	console.log(queryURL);
	jQuery.ajax({
		type: 'GET',
		url: queryURL,
		headers: {
			'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
			'Accept' : 'application/vnd.twitchtv.v5+json',
		},
		success: function(data) {
			var clips = data['clips'];
			var garden = jQuery('#garden');
			var clipCount = clips.length;
			console.log("returned " + clipCount + " clips");
			var cutCount = 0;
			var currentTime = + new Date();
			console.log(currentTime);
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
				var thisLogo = clips[i]['broadcaster']['logo'];
				var thisCurator = clips[i]['curator']['display_name'];
				var newCursor = data['_cursor'];
				if ( clips[i]['vod'] ) {
					var thisVODLink = clips[i]['vod']['url'];
				};
				if ( thisGame == 'Rocket League' && jQuery.inArray(thisSlug, cutKeys) == -1 ) {
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
										<div class='seedling-views'>${thisViewCount} views. clipped by ${thisCurator} about ${hoursAgo} hours ago. <a href='${thisVODLink}' target='_blank'>VOD Link</a></div>
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
	var thisEmbedTarget = thisSeedling.find('.seedlingEmbedTarget');
	if ( thisEmbedTarget.is(':empty') ) {
		var thisSlug = thisTitle.attr("data-slug");
		var embedCode = `<iframe src="https://clips.twitch.tv/embed?clip=${thisSlug}&autoplay=true" width="1280" height="720" frameborder="0" scrolling="no" allowfullscreen="true"></iframe>`
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
	cutCounterSpan.text(newCutCounter);
	thisX.fadeOut();
	cutSlug(thisSlug, thisTime, thisSeedling);
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
		var thisTextBox = thisSeedling.find('.seedlingAddTitleBox input');
		var thisTextEntry = thisTextBox.val();
		if ( thisTextEntry ) {
			var thisCustomTitle = thisTextEntry;
		} else {
			var thisCustomTitle = thisSlug;
		}
		growSeed(thisSlug, thisCustomTitle, thisSource, thisTime, thisSeedling);
	}
});

jQuery("#garden").on('click', 'p.moreClips', function() {
	var thisLink = jQuery(this);
	var thisCursor = thisLink.attr("data-cursor");
	clipGetter(thisCursor);
	thisLink.fadeOut();
});

</script>