<?php get_header(); ?>

<section id="donate-box">
	<a href="https://www.patreon.com/rocket_dailies" class="patreon-link"><div class="patreon-button">
		<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/Patreon.png" alt="patreon" class="patreon-logo">
	</div></a><div class="streamlabs-bar">
		<iframe src="https://streamlabs.com/widgets/donation-goal?token=8BE4E105DA0C64096FD6" height="100%" width="100%" name="donation-bar" frameborder="0" scrolling="no" id="donation-bar">You need an iframes capable browser to view this content.</iframe>
	</div><a href="https://twitch.streamlabs.com/the_rocket_dailies" class="donate-link"><div class="donate-button">
		Donate
	</div></a>
</section>

<p class="channel-changer-title">Today's Tournaments:</p>
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
		0 => ['Mockit League', 'mockit-league', 85, 'MCS - 3v3 - $5000', '7PM EST'],
		1 => ['Mythical', 'mythical-esports', 251, 'NA - 2v2 $100', '8PM EST'],
		2 => ['vVv Gaming', 'vvv-gaming', 239, 'NA - 3v3 $300', '8PM EST'],
		3 => ['Rocket Street', 'rocketstreet', 518, 'SAM - 2v2', '7PM EST'],
		4 => ['Liquor League', 'liquor-league', 559, 'NA - 3v3 - $100', '8PM EST'],
		5 => ['Drop, shot, & Roll', 'liefx', 662, 'Dropshot 1v1 - $100', '4PM EST'],
		6 => ['Rocket Dailies', 'rocket-dailies', 688, 'Nomination Stream', 'Midnight EST'],
	); 
	foreach ($todaysChannels as $channel) { 
		$twitchWholeURL = get_term_meta($channel[2], 'twitch', true);
		$twitchChannel = substr($twitchWholeURL, 22); ?>
		<div class="channel-changer-button inactive" data-channel-name="<?php echo $twitchChannel; ?>">
			<div class="cc-logo">
				<a href="<?php echo $thisDomain; ?>/source/<?php echo $channel[1]; ?>/" target="_blank"><img src="<?php $sourcepic = get_term_meta($channel[2], 'logo', true); echo $sourcepic; ?>"></a>
			</div>
			<div class="cc-details">
				<div class="channel-display-name"><a href="<?php echo $thisDomain; ?>/source/<?php echo $channel[1]; ?>/" target="_blank"><?php echo $channel[0]; ?></a></div>
				<div class="channel-info"><?php echo $channel[3]; ?></div>
				<div class="channel-time"><?php echo $channel[4]; ?></div>
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
var refreshRate = 15000;
window.setInterval(function() {refreshLive()}, refreshRate);

var grid = jQuery('#live-posts-loop').isotope({
	//options
	itemSelector: '.little-thing',
	percentPosition: true,
	masonry: {
		gutter: 18,
	}
});

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

jQuery('#channel-changer').on('click', '.channel-changer-button.inactive', function() {
	event.preventDefault();
	var channel = jQuery(this).attr("data-channel-name");
	showChannel(channel);
	var oldActive = jQuery('.active');
	oldActive.removeClass('active');
	oldActive.addClass('inactive');
	jQuery(this).removeClass('inactive');
	jQuery(this).addClass('active');
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

</script>
<?php get_footer(); ?>