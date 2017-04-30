<div id="sidebar">
	<?php include( locate_template('userbox.php') ); 
	if ( is_home() ) {
		$since = '1 week ago';
	} else {
		$since = '1 month ago';
	}; ?>
	<header id="topHeader" class="sideHeader">Top Plays</header>
	<?php if ( !is_single() ) { ?>
		<?php $topPosts= array(
			'posts_per_page' => 10,
			'category_name' => 'noms',
			'orderby' => 'meta_value_num',
			'meta_key' => 'votecount',
			'date_query' => array(
				array(
					'after' => $since
				),
			),
		);
		if ( is_tax() ) {
			$thisTerm = get_queried_object(); // Get the thing this page is for
			$thisTax = $thisTerm->taxonomy; //Get the taxonomy our term is in
			$thisSlug = $thisTerm->slug; // Get the slug for the current term
			$newQueryVar = array(
				array( // Tax_Query takes an array of arrays, so we have to do this
				'taxonomy' => $thisTax,
				'field' => 'slug',
				'terms' => $thisSlug
				),
			);
			$topPosts['tax_query'] = $newQueryVar; ?>
			<div class="sidebarSectionTitle">In: <?php echo $thisTerm->name; ?></div>
		<?php }
		if ( is_tag() ) {
			$thisTerm = get_queried_object(); // Get the thing this page is for
			$thisSlug = $thisTerm->slug; // Get the slug for the current term
			$topPosts['tag'] = $thisSlug; ?>
			<div class="sidebarSectionTitle">In: <?php echo $thisTerm->name; ?></div>
		<?php };
		$postDataTop = get_posts($topPosts);
		$postCount = count($postDataTop);
		if ($postCount < 6) {
			unset($topPosts['date_query']);
			$postDataTop = get_posts($topPosts);
		};
		$topCount = 1;
		foreach ( $postDataTop as $post) : setup_postdata($post); 
			$thisID = get_the_ID();
			$thumbURLSmall = wp_get_attachment_image_src( get_post_thumbnail_id($thisID), 'small'); 
			$votecount = get_post_meta($thisID, 'votecount', true); ?>
			<div class="topPost" id="top<?php echo $topCount; ?>">
				<div class="pic-count">
					<a href="<?php the_permalink(); ?>">
						<div class="topPic" id="topPic<?php echo $topCount; ?>" style="background:url('<?php echo $thumbURLSmall[0]; ?>');background-size:cover;background-position:center;"></div>
						<div class="count" id="count<?php echo $topCount; ?>"><?php echo $topCount; ?></div>
					</a>
				</div>
				<div class="topTitle" id="topTitle<?php echo $topCount; ?>">
					<h3 class="sidebar"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> <span class="score">(+<?php echo $votecount; ?>)</span></a></h3>
				</div>
			</div>
			<?php $topCount++;
		endforeach; 
	} else {
		$theseTerms = array (
			'stars' => wp_get_post_terms( $post->ID, 'stars', array("fields" => "names") ),
			'source' => wp_get_post_terms( $post->ID, 'source', array("fields" => "names") ),
			'skills' => wp_get_post_terms( $post->ID, 'skills', array("fields" => "names") ),
		);
		$taxCount = 0;
		$relatedExcludes = array();
		foreach ( $theseTerms as $currentTax) {
			$taxNames = array_keys($theseTerms);
			$taxName = $taxNames[$taxCount];
			$taxCount++;
			foreach ($currentTax as $currentTerm) { 
				$thisTerm = get_term_by('name', $currentTerm, $taxName);
				$thisTax = $thisTerm->taxonomy;
				$thisSlug = $thisTerm->slug;
				?>
				<div class="sidebarSectionTitle">In: <a href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $thisSlug; ?>" class="sidebarSectionTitleLink"><?php echo $currentTerm ?></a></div>
				<?php $topPosts= array(
					'posts_per_page' => 1,
					'post__not_in' => $relatedExcludes,
					'orderby' => 'meta_value_num',
					'meta_key' => 'votecount',
					'tax_query' => array(
						array( // Tax_Query takes an array of arrays, so we have to do this
							'taxonomy' => $thisTax,
							'field' => 'slug',
							'terms' => $thisSlug
						),
					),
				);
				$postDataTop = get_posts($topPosts);
				$topCount = 1;
				foreach ( $postDataTop as $post) : setup_postdata($post); 
					$thisID = get_the_ID();
					$thumbURLSmall = wp_get_attachment_image_src( get_post_thumbnail_id($thisID), 'small'); 
					$votecount = get_post_meta($thisID, 'votecount', true); ?>
					<div class="topPost" id="top<?php echo $topCount; ?>">
						<div class="pic-count">
							<a href="<?php the_permalink(); ?>">
								<div class="topPic" id="topPic<?php echo $topCount; ?>" style="background:url('<?php echo $thumbURLSmall[0]; ?>');background-size:cover;background-position:center;"></div>
							</a>
						</div>
						<div class="topTitle" id="topTitle<?php echo $topCount; ?>">
							<h3 class="sidebar"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> <span class="score">(+<?php echo $votecount; ?>)</span></a></h3>
						</div>
					</div>
					<?php $topCount++;
					$relatedExcludes[] = $thisID;
				endforeach;
			};
		};
	} ?>
		
		<footer id="repFooter" class="sideHeader">Your Rep: <div class="repScore"><?php 
			if ( $user_id == 0 ) {
				echo "0.1";
			} else {
				echo $myrep; 
			};
		?></div></footer>
</div>