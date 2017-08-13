<?php get_header();

/*function bigQuery($i) {	
	$bigQueryOffset = $i * 200;
	$bigQueryArgs = array(
		'posts_per_page' => 200,
		'offset' => $bigQueryOffset
		);
	$bigQueryPosts = get_posts($bigQueryArgs);
	$postCount = count($bigQueryPosts);
	foreach ($bigQueryPosts as $post) : setup_postdata($post);
		buildPostDataObject($post->ID);
		$lastPostID = $post->ID;
	endforeach;
	return $postCount;
}
$count = bigQuery(0);
$counter = 1;
while ($count !== 0) {
	$count = bigQuery($counter);
	$counter++;
}*/
//buildPostDataObject(3936);

/*$embedQueryArgs = array(
	'posts_per_page' => 200,
	'meta_key' => 'EmbedCode'
);
$embedPosts = get_posts($embedQueryArgs);
foreach ($embedPosts as $post) : setup_postdata($post);
	print_r($post->ID . ', ');
endforeach; */

?>
<div id="wp-social-login" class="hidden"><?php do_action('wordpress_social_login'); ?></div>

<section id="homepageApp">
</section>

<?php get_footer(); ?>