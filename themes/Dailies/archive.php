<?php get_header(); ?>

<div class="wrapper">
<div class="contentContainer">

<?php include(locate_template('archive-header.php')); ?>

<nav id="order-selector">
	Sort by: <a class="orderLink <?php if ($our_order == 'newest') { echo 'currentOrder'; }; ?>" href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $thisSlug; ?>/?orderby=date&order=desc">Newest</a> <a class="orderLink <?php if ($our_order == 'oldest') { echo 'currentOrder'; }; ?>" href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $thisSlug; ?>/?orderby=date&order=asc">Oldest</a> <a class="orderLink <?php if ($our_order == 'top') { echo 'currentOrder'; }; ?>" href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $thisSlug; ?>/?orderby=meta_value_num&order=desc">Top</a>
</nav>

	<?php $archArgs = array(
		'posts_per_page' => 10,
		'category_name' => 'noms',
		'paged' => $paged,
		'orderby' => $orderby,
		'order' => $order,
		'meta_key' => 'votecount',
		'tax_query' => array(
			array( // Tax_Query takes an array of arrays, so we have to do this
				'taxonomy' => $thisTax,
				'field' => 'slug',
				'terms' => $thisSlug
			),
		),
	);
	if ($thisTax == 'tag') {
		unset($archArgs['tax_query']);
		$archArgs['tag'] = $thisSlug;
	}
	query_posts($archArgs);
	$max = $wp_query->max_num_pages; // redo this for the new query
	if ( have_posts() ) : while ( have_posts() ) : the_post(); 
		include(locate_template('thing.php')); ?>

	<?php endwhile; else : ?>
		<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
	<?php endif; ?>

	<?php ($paged == 0) ? $earlier = $paged + 2 : $earlier = $paged + 1;
	if ( $earlier <= $max ) { 
		if ($underdogs) { ?>
			<a href="<?php echo $thisDomain; ?>/underdogs/page/<?php echo $earlier; ?>/?orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" class="earlier more pull"><img src='<?php echo $thisDomain; ?>/wp-content/uploads/2016/09/More.png' class='earlierIMG'></a>
		<?php } else { ?>
			<a href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $thisSlug; echo "/"; ?>page/<?php echo $earlier; ?>/?orderby=<?php echo $orderby; ?>&order=<?php echo $order; ?>" class="earlier more pull"><img src='<?php echo $thisDomain; ?>/wp-content/uploads/2016/09/More.png' class='earlierIMG'></a>
		<?php };
	} else { ?>
		<a class="earlier nomore pull">That's all, folks</a>
	<?php }; ?>
</div><?php include(locate_template('sidebar.php')); ?>
</div>
<?php get_footer(); ?>