<?php /* Template Name: Tax Page */ 
get_header();
$post = $wp_query->get_queried_object();
$taxSlug = $pagename;
$taxName = $post->post_title;
$pageNo = get_query_var('paged', 1 );
if ($pageNo == '0') { $pageNo = 1; };
$nextPage = $pageNo + 1;
if ($taxSlug == 'stars') {
	$termsArgsNA = array(
		'taxonomy' => 'stars',
		'parent' => 373
	);
	$termsArgsEU = array(
		'taxonomy' => 'stars',
		'parent' => 374
	);
	$theseTermsNA = get_terms($termsArgsNA); 
	$theseTermsEU = get_terms($termsArgsEU);
	$allStars = array_merge($theseTermsNA, $theseTermsEU);
	foreach ($allStars as $star) {
		$starID = $star->term_id;
		$starWins = get_term_meta($starID, 'wins', true);
		$allStarIDs[$starID] = $starWins;
	}
	arsort($allStarIDs);
	foreach ($allStarIDs as $starryID => $starryWins) {
		$theseTerms[] = get_term($starryID, 'stars');
	}
	$termsCount = count($theseTerms); //and count it so we know how many pages to show
} elseif ($taxSlug == 'source') {
	$tournamentArgs = array(
		'taxonomy' => 'source',
		'parent' => 0
	);
	$tournaments = get_terms($tournamentArgs);
	$tournamentOrder = array();
	foreach ($tournaments as $tournament) {
		$tournamentTermID = $tournament->term_id;
		$tournamentPostArgs = array(
			'posts_per_page' => 1,
			'orderby' => 'date',
			'order' => 'DESC',
			'tax_query' => array(
				array(
					'taxonomy' => 'source',
					'field' => 'id',
					'terms' => $tournamentTermID
				),
			)
		);
		$tournamentPost = get_posts($tournamentPostArgs);
		$tournamentUnixTime = strtotime($tournamentPost[0]->post_date);
		$now = time();
		$timeSince = $now - $tournamentUnixTime;
		$tournamentOrder[$tournamentTermID] = $timeSince;
	};
	asort($tournamentOrder);
	foreach ($tournamentOrder as $tournyID => $tournySince) {
		$theseTerms[] = get_term_by('id', $tournyID, 'source');
	};
	$termsCount = count($theseTerms);
} else {
	$termArgs = array(
		'taxonomy' => $taxSlug,
		'orderby' => 'count',
		'order' => 'DESC'
	);
	$theseTerms = get_terms($termArgs);
	$termsCount = count($theseTerms);
};

?>
<div class="wrapper">
	<section class="contentContainer" id="strings">
		<h2 id="taxHeader"><?php echo $taxName; ?></h2>
		<?php for ($i=10*($pageNo - 1); $i < 10*$pageNo && $i < $termsCount; $i++) { 
			$name = $theseTerms[$i]->name; 
			$slug = $theseTerms[$i]->slug; ?>
			<section class="string pull">
				<?php include(locate_template('archive-header.php')); ?>
				<!--<h3 class="stringName"><a href="<?php// echo $thisDomain; echo '/'; echo $taxSlug; echo '/'; echo $slug; ?>" class="stringNameLink"><?php// echo $name; ?></a></h3> -->
				<?php $stringArgs = array(
					'posts_per_page' => 10,
					'orderby' => 'meta_value_num',
					'order' => 'DESC',
					'meta_key' => 'votecount',
					'tax_query' => array(
						array( //tax query requires this barbarity. Apologies.
							'taxonomy' => $taxSlug,
							'field' => 'slug',
							'terms' => $name
						),
					),
				);
				$pearls = get_posts($stringArgs); ?>
				<div class="pearls">
					<?php foreach ($pearls as $pearl) {
						include('pearl.php');
					}; ?>
				</div>
				<div class="thingLanding" id="<?php echo $slug; ?>-landing">
				</div>
			</section>
		<?php }; ?>

		<?php if ($pageNo * 10 < $termsCount) { ?>
			<a href="<?php echo $thisDomain; ?>/<?php echo $taxSlug; ?>/page/<?php echo $nextPage; ?>" class="earlier more pull"><img src='<?php echo $thisDomain; ?>/wp-content/uploads/2016/09/More.png' class='earlierIMG'></a>
		<?php }; ?>

	</section>
	<?php include(locate_template('sidebar.php')); ?>
</div>

<?php get_footer();
?>