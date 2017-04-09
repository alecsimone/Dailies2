<?php get_header(); ?>

<section id="donate-box">
	<a href="https://www.patreon.com/rocket_dailies" class="patreon-link"><div class="patreon-button">
		<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/Patreon.png" alt="patreon" class="patreon-logo">
	</div></a><div class="streamlabs-bar">
		<iframe src="https://streamlabs.com/widgets/donation-goal?token=8BE4E105DA0C64096FD6" height="100%" width="100%" name="donation-bar" frameborder="0" scrolling="no" id="donation-bar">You need an iframes capable browser to view this content.</iframe>
	</div><a href="https://twitch.streamlabs.com/the_rocket_dailies" target="_blank" class="donate-link"><div class="donate-button">
		Donate
	</div></a>
</section>

<p class="channel-changer-title">Today's Tournaments:</p>
<p class="cctitle-instructions">(click to watch/filter)</p>
<script src= "http://player.twitch.tv/js/embed/v1.js"></script>
<section id="live-player-container">
</section>

<nav id="channel-changer">
	<div id="kill-player-button" onclick="killPlayer()">
		<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/red-x.png">
		Kill Player
	</div>
	<?php $todaysChannels = array(
		//0-Display Name, 1-link, 2-logo 3-Description, 4-Time
		'metaleak' => ['Team Metaleak', 'metaleak', 521, 'EU - 2v2 - &euro;50', '10 AM EST'],
		'prl' => ['Pro Rivalry, EU & NA', 'prorl', 81, 'EU $150 3v3 / NA Bragging Rights', '11 AM EST / 9PM EST'],
		'rewind' => ['Rewind Gaming', 'rewindrl', 583, 'EU - 3v3 - &euro;45', '2:30 PM EST'],
		'rlcs' => ['RLCS', 'rlcs', 79, 'Midseason Mayhem', '3PM EST'],
		'boost' => ['Boost Legacy', 'boost-legacy', 401, 'NA - 2v2 - $50', '3PM EST'],
		'nexus' => ['Nexus Gaming', 'nexus-gaming', 389, 'NA - 3v3 - $150', '8PM EST'],
	//	'me' => ['Rocket Dailies', 'rocket-dailies', 688, 'Nomination Stream', 'Midnight EST'],
	); 
	foreach ($todaysChannels as $channel) { 
		$twitchWholeURL = get_term_meta($channel[2], 'twitch', true);
		$twitchChannel = substr($twitchWholeURL, 22); ?>
		<div class="channel-changer-button inactive offline" data-channel-name="<?php echo $twitchChannel; ?>" data-channel-slug="<?php echo $channel[1]; ?>">
			<div class="cc-logo">
				<a href="<?php echo $thisDomain; ?>/source/<?php echo $channel[1]; ?>/" target="_blank"><img src="<?php $sourcepic = get_term_meta($channel[2], 'logo', true); echo $sourcepic; ?>"></a>
			</div>
			<div class="cc-details">
				<div class="channel-display-name"><a href="<?php echo $thisDomain; ?>/source/<?php echo $channel[1]; ?>/" target="_blank"><?php echo $channel[0]; ?></a></div>
				<div class="channel-info"><?php echo $channel[3]; ?></div>
				<div class="channel-time" data-time-string="<?php echo $channel[4]; ?>"><?php echo $channel[4]; ?></div>
			</div>
		</div>
	<?php }; ?>
</nav>

<?php $liveArgs = array(
	'category__not_in' => 4,
	'posts_per_page' => 40,
	'date_query' => array(
		array(
			'after' => '24 hours ago'
		)
	)
);
$postDataLive = get_posts($liveArgs); 
$postsAndScores = array();
foreach ( $postDataLive as $post ) {
	$pid = $post->ID;
	$postsAndScores[$pid] = get_post_meta($pid, 'votecount', true);
}; ?>
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
	}
});

var refreshRate = 15000;
window.setInterval(function() {refreshLive()}, refreshRate);

var streamCheckRate = 600000;
jQuery(window).load(streamChecker);
window.setInterval(function() {streamChecker()}, streamCheckRate);

jQuery('.little-title').on('click', 'a', function() {
	event.preventDefault();
	var thisLittleClass = jQuery(this).attr("class");
	var thisCode = jQuery(this).attr("data-id");
	var thisWholeThing = jQuery(this).parent().parent().parent().parent();
	var thisEmbedTarget = thisWholeThing.find('.little-thing-embed');
	var embedExistenceChecker = thisEmbedTarget.find('.embed-container');
	if (embedExistenceChecker.length) {
		embedExistenceChecker.remove();
	} else if ( thisLittleClass == 'twitch-little-thing' ) {
		var embedCode = generateTwitchReplacementCode(thisCode);
		thisEmbedTarget.append(embedCode);
	} else if ( thisLittleClass == 'gfy-little-thing' ) {
		var embedCode = generateGfyReplacementCode(thisCode);
		thisEmbedTarget.append(embedCode);
	} else if ( thisLittleClass == 'yt-little-thing' ) {
		var embedCode = generateYoutubeReplacementCode(thisCode);
		thisEmbedTarget.append(embedCode);
	}
	grid.isotope();
});

jQuery('#channel-changer').on('click', '.channel-changer-button.inactive.live', function() {
	event.preventDefault();
	var channel = jQuery(this).attr("data-channel-name");
	showChannel(channel);
	var oldActive = jQuery('.active');
	oldActive.removeClass('active');
	oldActive.addClass('inactive');
	jQuery(this).removeClass('inactive');
	jQuery(this).addClass('active');
});

jQuery('#channel-changer').on('click', '.channel-changer-button.inactive.offline', function() {
	event.preventDefault();
	if ( !jQuery(this).hasClass('filtering') ) {
		var thisSlug = jQuery(this).attr("data-channel-slug");
		var oldFilter = jQuery('.filtering');
		oldFilter.removeClass('filtering');
		grid.isotope({ filter: `.${thisSlug}` });
		jQuery(this).addClass('filtering');
	} else {
		grid.isotope({ filter: '*' });
		jQuery(this).removeClass('filtering');
	}
});

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
	var killButton = jQuery('#kill-player-button');
	killButton.css("display", "inline-flex");
}
function killPlayer() {
	var livePlayer = jQuery('#live-player-container');
	livePlayer.css("display", "none");
	livePlayer.html('');
	var killButton = jQuery('#kill-player-button');
	killButton.css("display", "none");
	var oldActive = jQuery('.active');
	oldActive.removeClass('active');
	oldActive.addClass('inactive');
}

function streamChecker() {
	var channelButtons = jQuery('.channel-changer-button');
	var streamList = '';
	jQuery.each(channelButtons, function() {
		streamList = streamList + jQuery(this).attr("data-channel-name") + ',';
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
							if ( jQuery.inArray(thisName, liveStreamNames) > -1 ) {
								var liveStreamTimebox = thisButton.find('.channel-time');
								liveStreamTimebox.html('&#9679; Live Now!');
								thisButton.removeClass('offline');
								thisButton.addClass('live');
							} else if ( jQuery.inArray(thisName, liveStreamNames) == -1 && thisButton.hasClass('live') ) {
								var thisTimebox = thisButton.find('.channel-time');
								var originalTimeString = thisTimebox.attr("data-time-string");
								thisTimebox.html(originalTimeString);
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

</script>
<?php get_footer(); ?>