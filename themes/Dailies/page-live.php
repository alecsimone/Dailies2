<?php get_header(); ?>
<p>This shit's mad Beta, yo. <a href="mailto:mrpres@therocketdailies.com?subject=Live%20Page%20Feedback" target="_blank">Lemme know</a> anything you like/don't like/would like.</p>
<section id="live-player-container">
	<script src= "http://player.twitch.tv/js/embed/v1.js"></script>
	<div id="live-twitch-player"></div><script type="text/javascript">
		var options = {
			width: '100%',
			height: '100%',
			channel: "dappur"
		};
		var player = new Twitch.Player("live-twitch-player", options);
		player.setVolume(0.5);
	</script><div id="live-twitch-chat">
		<iframe frameborder="0"
	        scrolling="yes"
	        id="the_rocket_dailies"
	        src="https://www.twitch.tv/the_rocket_dailies/chat"
	        height="100%"
	        width="100%">
		</iframe>
	</div>
</section>

<div class="toggle-player-button" onclick="toggleLivePlayer()">
	X Hide Player
</div>

<section id="live-posts-loop">
	<?php $liveArgs = array(
		'category__not_in' => 4,
		'posts_per_page' => 20,
		'date_query' => array(
			array(
				'after' => '24 hours ago'
			)
		)
	);
	$postDataLive = get_posts($liveArgs);
	foreach ( $postDataLive as $post) : setup_postdata($post);
		include(locate_template('thing.php'));
	endforeach; ?>

</section>

<?php get_footer(); ?>