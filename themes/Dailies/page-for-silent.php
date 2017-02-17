<?php get_header(); ?>

<div class="wrapper">
<div class="contentContainer">

<?php include(locate_template('archive-header.php')); ?>

	<?php $adCounter = 0;
	$adsCounted = 0;
	//this is just a comment to test changing files.
	$archArgs = array(
		'posts_per_page' => 10,
		'paged' => $paged,
		'orderby' => 'meta_value_num',
		'order' => 'desc',
		'meta_key' => 'votecount',
		'date_query' => array(
			array(
				'year'  => 2016,
			),
		),
	);
	query_posts($archArgs);
	$max = $wp_query->max_num_pages; // redo this for the new query
	if ( have_posts() ) : while ( have_posts() ) : the_post(); 
		 if ($adCounter == 3) { ?>
			<div class="adunit loop-ad pull" data-adunit="loopad-home" data-size-mapping="default-sizes"></div>
			<?php $adCounter = 0;
			$adsCounted++; 
		}; 
		include(locate_template('thing.php')); 
		$adCounter++; ?>

	<?php endwhile; else : ?>
		<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
	<?php endif; ?>

	<?php ($paged == 0) ? $earlier = $paged + 2 : $earlier = $paged + 1;
	if ( $earlier <= $max ) { 
		if ($underdogs) { ?>
			<a href="http://therocketdailies.com/for-silent/page/<?php echo $earlier; ?>" class="earlier more pull"><img src='<?php echo $thisDomain; ?>/wp-content/uploads/2016/09/More.png' class='earlierIMG'></a>
		<?php } else { ?>
			<a href="http://therocketdailies.com/for-silent/page/<?php echo $earlier; ?>" class="earlier more pull"><img src='<?php echo $thisDomain; ?>/wp-content/uploads/2016/09/More.png' class='earlierIMG'></a>
		<?php };
	} else { ?>
		<a class="earlier nomore pull">That's all, folks</a>
	<?php }; ?>
</div><?php include(locate_template('sidebar.php')); ?>
</div>
<?php get_footer(); ?>