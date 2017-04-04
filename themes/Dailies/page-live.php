<?php get_header(); ?>
<p>This shit's mad Beta, yo. <a href="mailto:mrpres@therocketdailies.com?subject=Live%20Page%20Feedback" target="_blank">Lemme know</a> anything you like/don't like/would like.</p>
<script src= "http://player.twitch.tv/js/embed/v1.js"></script>
<section id="live-player-container">
</section>

<div class="toggle-player-button" onclick="toggleLivePlayer()">
	V Show Live Player
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

<script>
var $grid = jQuery('#live-posts-loop').isotope({
	//options
	itemSelector: '.little-thing',
	percentPosition: true,
	masonry: {
		gutter: 18,
	}
});
</script>
<?php get_footer(); ?>