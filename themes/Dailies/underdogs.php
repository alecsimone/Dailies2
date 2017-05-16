<header id="runners-header" class="section-head">
	<img src="http://therocketdailies.com/wp-content/uploads/2016/08/Underdog-Banner.jpg" class="nombanner">
</header>

<?php if ($paged == 0) {
	$underdogsOffset = 0;
} else {
	$underdogsOffset = ($paged - 1) * 4;
}
$runnerArgs = array(
	'category_name' => $thisCategory,
	'tag__not_in' => 4, //4 = Noms
	'posts_per_page' => 4,
	'offset' => $underdogsOffset,
	);
$postDataRunners = get_posts($runnerArgs);
foreach ( $postDataRunners as $post) : setup_postdata($post); 
	include(locate_template('thing.php'));
endforeach; ?>