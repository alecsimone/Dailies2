<?php get_header();
include( locate_template('schedule.php') );
?>

<section class="live-header">
	<section class="moneystuff">	
		<section id="donate-box">
			<a href="https://www.patreon.com/rocket_dailies" class="patreon-link"><div class="patreon-button">
				<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/Patreon.png" alt="patreon" class="patreon-logo">
			</div></a><a href="https://twitch.streamlabs.com/the_rocket_dailies" target="_blank" class="donate-link"><div class="donate-button">
				Donate
			</div></a>
		</section>
		<section class="streamlabs-bar">
			<iframe src="https://streamlabs.com/widgets/donation-goal?token=8BE4E105DA0C64096FD6" height="100%" width="100%" name="donation-bar" frameborder="0" scrolling="no" id="donation-bar">You need an iframes capable browser to view this content.</iframe>
		</section>
	</section><?php include( locate_template('userbox.php') ); ?>
</section>

<!--<p class="channel-changer-title">Today's Shows:</p>-->
<script src= "http://player.twitch.tv/js/embed/v1.js"></script>
<section id="live-player-container">
</section>

<nav id="channel-changer">
	<div class="scrollButton scrollLeft">&lt</div>
	<div class="channel-changer-buttons">
		<div id="sort-button" class="channel-changer-button inactive offline sort">
			<div class="cc-logo"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/sort.png"></div>
		</div>
		<?php $todaysChannels = $schedule[$todaysSchedule];
		foreach ($todaysChannels as $channel) { 
			$twitchWholeURL = get_term_meta($channel[2], 'twitch', true);
			$twitchChannel = substr($twitchWholeURL, 22); ?>
			<div class="channel-changer-button inactive offline" data-channel-name="<?php echo $twitchChannel; ?>" data-channel-slug="<?php echo $channel[1]; ?>">
				<div class="cc-logo">
					<a href="<?php echo $thisDomain; ?>/source/<?php echo $channel[1]; ?>/" target="_blank"><img src="<?php $sourcepic = get_term_meta($channel[2], 'logo', true); echo $sourcepic; ?>"></a>
				</div>
				<div class="channel-live-now <?php echo $channel[0]; ?>"><a href="<?php echo $twitchWholeURL; ?>" target="_blank" class="liveNowLink">&#9679; Live Now!</a></div>
				<div class="cc-details">
					<div class="channel-display-name" data-display-name="<?php echo $channel[0]; ?>"><?php echo $channel[0]; ?></div>
					<div class="channel-info"><?php echo $channel[3]; ?></div>
					<div class="channel-time"><?php echo $channel[4]; ?></div>
				</div>
			</div>
		<?php }; ?>
	</div>
	<div class="scrollButton scrollRight">></div>
</nav>

<!-- <section id="cohosts">
	<h3 class="live">Co-Hosts:</h3>
	<?php $cohosts = ['dazerin', 'ninjarider', 'inanimatej']; 
	foreach ($cohosts as $cohost) { ?>
		<div class="cohost-button">
			<?php $hostObject = get_term_by('slug', $cohost, 'stars');
			$hostID = $hostObject->term_id;
			$hostName = $hostObject->name;
			$logo_url = get_term_meta($hostID, 'logo', true);
			$twitter_url = get_term_meta($hostID, 'twitter', true);
			$twitch_url = get_term_meta($hostID, 'twitch', true);
			$youtube_url = get_term_meta($hostID, 'youtube', true);
			$donate_url = get_term_meta($hostID, 'donate', true); ?>
			<div class="cohost-logo"><img src="<?php echo $logo_url; ?>"></div>
			<div class="cohost-meta">	
				<div class="cohost-name"><?php echo $hostName; ?></div>
				<div class="cohost-links">	
					<?php if ($twitter_url != '') { ?><a href="<?php echo $twitter_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button twitter"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/Twitter-logo.png" alt="twitter link"></div></a><?php }; ?>
					<?php if ($twitch_url != '') { ?><a href="<?php echo $twitch_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button twitch"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/Twitch-purple-logo.png" alt="twitch link"></div></a><?php }; ?>
					<?php if ($youtube_url != '') { ?><a href="<?php echo $youtube_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button youtube"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/youtube-logo.png" alt="youtube link"></div></a><?php }; ?>
					<?php if ($donate_url != '') { ?><a href="<?php echo $donate_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button donate"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/03/Donate-logo.png" alt="donate link"></div></a><?php }; ?>
				</div>
			</div>
		</div>
	<?php }; ?>
</section> -->

<?php $liveArgs = array(
	'category__not_in' => 4,
	'posts_per_page' => 40,
	'date_query' => array(
		array(
			'after' => '240 hours ago'
		)
	)
);
$postDataLive = get_posts($liveArgs); 
$postsAndScores = array();
if ($postDataLive) {
	foreach ( $postDataLive as $post ) {
		$pid = $post->ID;
		$postsAndScores[$pid] = get_post_meta($pid, 'votecount', true);
	}; 
} else { ?>
	<div class="noPosts">
		No highlights yet today. Wanna <a href="mailto:submit@therocketdailies.com?Subject=Check%20out%20this%20play" class="noPostsSuggest">suggest</a> one?
	</div>
<?php }; ?>
<div id="live-posts-data" class="hidden-data"><?php echo json_encode($postsAndScores); ?></div>
<div id="live-userbox" style="display:none"><?php include( locate_template('userbox.php') ); ?></div>

<?php thingifyID(3873); ?>

<section id="live-posts-loop">
	<?php foreach ( $postDataLive as $post) : setup_postdata($post);
		include(locate_template('thing.php'));
	endforeach; ?>

</section>


<script>
var grid = jQuery('#live-posts-loop').isotope({
	//options
	itemSelector: '.little-thing',
	percentPosition: true,
	masonry: {
		gutter: 18,
	},
	getSortData: {
		score: function( itemElem ) {
			var thisThingID = jQuery(itemElem).attr("id");
			var thisID = thisThingID.substring(13);
			var thisScore = jQuery(`#thingScore${thisID}`).attr("data-score");
			return parseFloat(thisScore);
		},
	}
});

var refreshRate = 5000;
window.setInterval(function() {refreshLive()}, refreshRate);

var streamCheckRate = 300000;
jQuery(window).load(streamChecker);
window.setInterval(function() {streamChecker()}, streamCheckRate);

jQuery('.little-thing').on('click', '.little-title', function() {
	event.preventDefault();
	var thisTitle = jQuery(this).find('.little-title-link');
	var thisLittleClass = thisTitle.attr("class");
	var thisCode = thisTitle.attr("data-id");
	var thisWholeThing = jQuery(this).parent().parent();
	var thisEmbedTarget = thisWholeThing.find('.little-thing-embed');
	var embedExistenceChecker = thisEmbedTarget.find('.embed-container');
	if (embedExistenceChecker.length) {
		embedExistenceChecker.remove();
	} else if ( thisLittleClass.includes('twitch-little-thing') ) {
		var embedCode = generateTwitchReplacementCode(thisCode);
		thisEmbedTarget.append(embedCode);
	} else if ( thisLittleClass.includes('gfy-little-thing') ) {
		var embedCode = generateGfyReplacementCode(thisCode);
		thisEmbedTarget.append(embedCode);
	} else if ( thisLittleClass.includes('yt-little-thing') ) {
		var embedCode = generateYoutubeReplacementCode(thisCode);
		thisEmbedTarget.append(embedCode);
	} else if (thisLittleClass.includes('raw-embed-little-thing') ) {
		if (thisEmbedTarget.css("display") === "none") {			
			thisEmbedTarget.css("display", "block");
		} else {
			thisEmbedTarget.css("display", "none");
		}
	}
	grid.isotope();
});

jQuery('#channel-changer').on('click', '.channel-changer-button', function() {
	var thisButton = jQuery(this);
	/* if ( thisButton.hasClass('live') ) {
		event.preventDefault();
		var channel = jQuery(this).attr("data-channel-name");
		if ( jQuery(this).hasClass('inactive') ) {
			showChannel(channel);
			var oldActive = jQuery('.active');
			oldActive.removeClass('active');
			oldActive.addClass('inactive');
			jQuery(this).removeClass('inactive');
			jQuery(this).addClass('active');
		} else {
			killPlayer();
		}
	} else */ if ( thisButton.hasClass('filter') ) {
		var activeButton = jQuery('.active');
		var activeSlug = activeButton.attr("data-channel-slug");
		filterSource(activeSlug);
		var filterButton = jQuery('#filter-button');
		if ( filterButton.hasClass('isFiltering') ) {
			filterButton.removeClass('isFiltering');
		} else {
			filterButton.addClass('isFiltering');
		};
	} else if ( thisButton.hasClass('sort') ) {
		var loop = jQuery('#live-posts-loop');
		if ( !loop.hasClass('sorted') ) {
			grid.isotope({
				sortBy: 'score',
				sortAscending: false
			});
			grid.isotope('updateSortData').isotope();
			loop.addClass('sorted');
			jQuery(this).addClass('sorting');
		} else {
			grid.isotope({
				sortBy: 'original-order',
				sortAscending: true,
			});
			grid.isotope('updateSortData').isotope();
			loop.removeClass('sorted');
			jQuery(this).removeClass('sorting');
		}
	} else if ( thisButton.hasClass('inactive') /* && thisButton.hasClass('offline') */ ) {
		//event.preventDefault();
		var thisSlug = jQuery(this).attr("data-channel-slug");
		filterSource(thisSlug);
	}
});
jQuery('#channel-changer').on('click', '.channel-live-now', function(e) {
	e.stopPropagation();
});

function filterSource(sourceSlug) {
	console.log(sourceSlug);
	var channelChangerButton = jQuery(`[data-channel-slug=${sourceSlug}]`);
	if ( !channelChangerButton.hasClass('filtering') ) {
		var oldFilter = jQuery('.filtering');
		oldFilter.removeClass('filtering');
		grid.isotope({ filter: `.${sourceSlug}` });
		channelChangerButton.addClass('filtering');
	} else {
		grid.isotope({ filter: '*' });
		channelChangerButton.removeClass('filtering');
	}
}

function showChannel(channel) {
	var twitchEmbedHTML = jQuery('')
	var livePlayer = jQuery('#live-player-container');
	if ( livePlayer.css('display') == 'none') {
		livePlayer.css("display", "block");
		var livePlayerWidth = livePlayer.width();
		var windowWidth = jQuery(window).width();
		var chatWidth = 340;
		if (windowWidth < 850) {
			var chatWidth = 0;
		}
		var livePlayerHeight = (livePlayerWidth - chatWidth) * 9 / 16;
		var options = {
			width: "100%",
			height: livePlayerHeight,
			channel: channel
		};
		livePlayer.html(`<div id="live-twitch-player"></div><div id="live-twitch-chat"><iframe frameborder="0" scrolling="yes" id="the_rocket_dailies" src="https://www.twitch.tv/the_rocket_dailies/chat" height="${livePlayerHeight}" width="100%"></iframe></div>`);
		var player = new Twitch.Player("live-twitch-player", options);
		player.setVolume(0.5);
		livePlayer.css("height", livePlayerHeight);
	} else {
		var twitchPlayer = jQuery('#live-twitch-player');
		twitchPlayer.html('');
		var livePlayerWidth = livePlayer.width();
		var windowWidth = jQuery(window).width();
		var chatWidth = 340;
		if (windowWidth < 850) {
			var chatWidth = 0;
		}
		var livePlayerHeight = (livePlayerWidth - chatWidth) * 9 / 16;
		var options = {
			width: "100%",
			height: livePlayerHeight,
			channel: channel
		}; 
		var player = new Twitch.Player("live-twitch-player", options);
	};
	var filterButton = jQuery('#filter-button');
	filterButton.css("display", "inline-flex");
}
function killPlayer() {
	var livePlayer = jQuery('#live-player-container');
	livePlayer.css("display", "none");
	livePlayer.html('');
	var filterButton = jQuery('#filter-button');
	filterButton.css("display", "none");
	var oldActive = jQuery('.active');
	oldActive.removeClass('active');
	oldActive.addClass('inactive');
	grid.isotope({ filter: '*' });
}

function streamChecker() {
	var channelButtons = jQuery('.channel-changer-button');
	var streamList = '';
	jQuery.each(channelButtons, function() {
		var thisName = jQuery(this).attr("data-channel-name");
		if (typeof thisName != 'undefined') {
			streamList = streamList + thisName + ',';
		};
	});
	streamList = streamList.substring(0, streamList.length - 1);
	jQuery.ajax({
		type: 'GET',
		url: `https://api.twitch.tv/kraken/users?login=${streamList}`,
		headers: {
			'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
			'Accept' : 'application/vnd.twitchtv.v5+json',
		},
		success: function(data) {
			var streamIDs = '';
			jQuery.each(data['users'], function(index, value) {
				streamIDs = streamIDs + value['_id'] + ',';
			})
			streamIDs = streamIDs.substring(0, streamIDs.length - 1);
			jQuery.ajax({
				type: 'GET',
				url: `https://api.twitch.tv/kraken/streams?channel=${streamIDs}`,
				headers: {
					'Client-ID' : 'r7cqs4kgrg1sknyz32brgy9agivw9n',
					'Accept' : 'application/vnd.twitchtv.v5+json',
				},
				success: function(data) {
					var liveStreams = data['streams'];
					var liveStreamNames = [];
					jQuery.each(liveStreams, function(index, value) {
						if (value['game'] == "Rocket League") {
							var liveStreamName = value['channel']['display_name'].toLowerCase();
							liveStreamNames.push(liveStreamName);
						}
						var allButtons = jQuery('.channel-changer-button');
						jQuery.each(allButtons, function() {
							var thisButton = jQuery(this);
							var thisName = thisButton.attr("data-channel-name");
							if (thisName !== undefined) {
								thisName = thisName.toLowerCase();
							}
							console.log(thisName);
							console.log(liveStreamNames);
							if ( jQuery.inArray(thisName, liveStreamNames) > -1 ) {
								var liveNowBox = thisButton.find('.channel-live-now');
								liveNowBox.css("maxHeight", "24px");
								liveNowBox.css("marginTop", "2px");
								thisButton.removeClass('offline');
								thisButton.addClass('live');
							} else if ( jQuery.inArray(thisName, liveStreamNames) == -1 && thisButton.hasClass('live') ) {
								var liveNowBox = thisButton.find('.channel-live-now');
								liveNowBox.css("maxHeight", "0");
								liveNowBox.css("marginTop", "0");
								thisButton.removeClass('live');
								thisButton.addClass('offline');
							}
						});
					});
				},
				error: function() {
					console.log("YOU FAILED THE SECOND REQUEST!")
				}
			})
		},
		error: function() {
			console.log("YOU FAILED!");
		}
	});
};
jQuery(window).load(ccWidthChecker);
jQuery(window).resize(ccWidthChecker);

function ccWidthChecker() {
	var windowWidth = jQuery(window).width();
	var ccButtons = jQuery('.channel-changer-button');
	var visibleButtons = 0;
	jQuery.each(ccButtons, function() {
		if ( jQuery(this).css("display") !== "none" ) {
			visibleButtons++;
		}
	})
	var ccButtonBuffer = parseFloat(jQuery('.channel-changer-button:last').outerWidth(true)) - parseFloat(jQuery('.channel-changer-button:last').width());
	var channelChangerPadding = parseFloat(jQuery('#channel-changer').css("paddingLeft"));
	var newButtonWidth = windowWidth / visibleButtons - ccButtonBuffer - channelChangerPadding - 3;
	var sortButtonIMG = jQuery("#sort-button img");
	var sortButtonIMGHeight = sortButtonIMG.height();
	var newSortButtonIMGPadding = (newButtonWidth - sortButtonIMGHeight) / 2;
	if (newSortButtonIMGPadding >= 0 && newButtonWidth >= 60) {
		if ( newButtonWidth < 120) {
			jQuery('.channel-changer-button').css("maxWidth", newButtonWidth);
			sortButtonIMG.css("paddingTop", newSortButtonIMGPadding);
			sortButtonIMG.css("paddingBottom", newSortButtonIMGPadding);
			jQuery('.scrollRight').fadeOut(200);
			jQuery('.scrollLeft').fadeOut(200);
		}
	} else {
		jQuery('.scrollRight').fadeIn(100);
		jQuery('.scrollRight').css("display", "flex");
	}
}

function firstRightness() {
	var firstChannel = jQuery('#sort-button');
	var firstChannelRightness = firstChannel.offset().left;
	return firstChannelRightness;
}
function lastRightness() {
	var lastChannel = jQuery('.channel-changer-button:last');
	var lastChannelRightness = lastChannel.offset().left;
	var lastChannelWidth = lastChannel.outerWidth();
	var lastChannelRightEdge = lastChannelWidth + lastChannelRightness;
	return lastChannelRightEdge;
}

jQuery(".scrollButton").click(function() {
	if (jQuery(this).css("display") === 'flex') {
		if (jQuery(this).hasClass("scrollRight")) {
			var direction = "Right";
		} else if (jQuery(this).hasClass("scrollLeft")) {
			var direction = "Left";
		}
		scrollbar(direction);
	}
})

function scrollbar(direction) {
	var channelChanger = jQuery('.channel-changer-buttons');
	var scrollLeft = jQuery('.scrollLeft');
	var scrollRight = jQuery('.scrollRight');
	var windowWidth = jQuery(window).width();
	if (direction === 'Right') {
		var newRightness = lastRightness() - windowWidth;
		if (newRightness <= windowWidth) {
			scrollRight.fadeOut(); 
			scrollLeft.fadeIn(150);
			scrollLeft.css("display", "flex");
		}
		channelChanger.animate( { scrollLeft: `+=${windowWidth}` }, 200 );
	} else if (direction === 'Left') {
		var newRightness = firstRightness() + windowWidth;
		if (newRightness >= 0) {
			scrollLeft.fadeOut();
			scrollRight.fadeIn(150);
			scrollRight.css("display", "flex");
		};
		channelChanger.animate( { scrollLeft: `-=${windowWidth}` }, 200 );
	}
}

</script>
<?php get_footer(); ?>