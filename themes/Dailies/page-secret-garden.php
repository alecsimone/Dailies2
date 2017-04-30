<?php get_header();
include( locate_template('schedule.php') );
$cutList = get_post_meta(4599, 'cut', true);
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
function clipGetter() {
	var streamList = jQuery('#garden').attr('data-streams');
	var cutList = jQuery('#garden').attr('data-cut');
	var cutObj = JSON.parse(cutList);
	var cutKeys = Object.keys(cutObj);
	console.log(cutKeys);
	jQuery.ajax({
		type: 'GET',
		url: `https://api.twitch.tv/kraken/clips/top?channel=${streamList}&period=day&limit=100`,
		headers: {
			'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
			'Accept' : 'application/vnd.twitchtv.v5+json',
		},
		success: function(data) {
			var clips = data['clips'];
			var garden = jQuery('#garden');
			for (var i = 0; i < clips.length; i++) {
				var thisSlug = clips[i]['slug'];
				var thisTitle = clips[i]['title'];
				var thisTimeRaw = clips[i]['created_at'];
				var thisTime = Date.parse(thisTimeRaw);
				var thisGame = clips[i]['game'];
				var thisWholeSource = clips[i]['broadcaster']['channel_url'];
				var thisSource = thisWholeSource.substring(22);
				var thisViewCount = clips[i]['views'];
				var thisThumbURL = clips[i]['thumbnails']['medium'];
				if ( thisGame == 'Rocket League' && jQuery.inArray(thisSlug, cutKeys) == -1 ) {
					garden.append(
						`<div class='seedling'>
							<div class='seedling-title' data-slug='${thisSlug}' data-time='${thisTime}'>
								<a href='https://clips.twitch.tv/${thisSlug}' target='_blank'>${thisTitle}</a>
							</div>
							<div class='seedling-meta'>
								<div class='seedling-source'>${thisSource}</div> - ${thisViewCount} views
							</div>
							<div class='seedlingEmbedTarget'></div>
							<div class='seedlingAddTitleBox'><input type='text' class='seedling-title-input' name='addTitleBox' size='64'></div>
							<div class='seedling-controls'>
								<div class='seedling-plus'><img class='seedGrower seedControlImg' src='http://dailies.gg/wp-content/uploads/2017/04/blue-plus.png'></div>
								<div class='seedling-cross'><img class='seedCutter seedControlImg' src='http://dailies.gg/wp-content/uploads/2017/04/red-x.png'></div>
							</div>
						</div>`
					);
				};
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
	var thisSeedling = thisTitle.parent();
	var thisEmbedTarget = thisSeedling.find('.seedlingEmbedTarget');
	if ( thisEmbedTarget.is(':empty') ) {
		var thisSlug = thisTitle.attr("data-slug");
		var embedCode = `<iframe src="https://clips.twitch.tv/embed?clip=${thisSlug}&autoplay=true" width="640" height="360" frameborder="0" scrolling="no" allowfullscreen="true"></iframe>`
		thisEmbedTarget.html(embedCode);
	} else {
		thisEmbedTarget.html('');
	}
});

jQuery("#garden").on('click', '.seedling-cross', function() {
	var thisX = jQuery(this);
	var thisSeedling = thisX.parent().parent()
	var thisTitle = thisSeedling.find('.seedling-title');
	var thisSlug = thisTitle.attr("data-slug");
	var thisTime = thisTitle.attr("data-time");
	cutSlug(thisSlug, thisTime, thisSeedling);
});

jQuery("#garden").on('click', '.seedling-plus', function() {
	var thisPlus = jQuery(this);
	var thisSeedling = thisPlus.parent().parent()
	var thisTitle = thisSeedling.find('.seedling-title');
	var thisSlug = thisTitle.attr("data-slug");
	var thisTime = thisTitle.attr("data-time");
	var thisSource = thisSeedling.find('.seedling-source').text();
	var thisTextBox = thisSeedling.find('.seedlingAddTitleBox input');
	var thisTextEntry = thisTextBox.val();
	if ( thisTextEntry ) {
		var thisCustomTitle = thisTextEntry;
	} else {
		var thisCustomTitle = thisSlug;
	}
	growSeed(thisSlug, thisCustomTitle, thisSource, thisTime, thisSeedling);
});
</script>