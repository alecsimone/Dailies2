<?php global $thisDomain;
$isChild = false;
$theTag = single_tag_title("", false); // get the title of the current tag with no prefix and don't display it
$thisTerm = get_queried_object(); // Get the thing this page is for
if ( is_page() ) {
	global $slug;
	global $taxSlug;
	$thisTerm = get_term_by('slug', $slug, $taxSlug);
}
$thisSlug = $thisTerm->slug; // Get the slug for the current tag
$thisTermID = $thisTerm->term_id; //Get the term's ID
$thisTermName = $thisTerm->name; // and name
$thisTermCount = $thisTerm->count; //and count for tax pages

$logo_url = get_term_meta($thisTermID, 'logo', true);
$twitter_url = get_term_meta($thisTermID, 'twitter', true);
$twitch_url = get_term_meta($thisTermID, 'twitch', true);
$youtube_url = get_term_meta($thisTermID, 'youtube', true);
$website_url = get_term_meta($thisTermID, 'website', true);
$discord_url = get_term_meta($thisTermID, 'discord', true);

$orderby = get_query_var('orderby', 'date');
$order = get_query_var('order', 'DESC');
if ( $orderby == 'date' && $order == 'ASC' ) {
	$our_order = 'oldest';
} elseif ( $orderby == 'date' && $order == 'DESC' ) {
	$our_order = 'newest';
} else {
	$our_order = 'top';
};

$max = $wp_query->max_num_pages; // Figure out how many pages there are for this tag 

if ( is_tax('stars') ) { 
	$thisTax = 'stars';
	$these_children = get_term_children($thisTermID, $thisTax); 
	$winCount = get_term_meta($thisTermID, 'wins', true); 
}
elseif ( is_tax('skills') ) { $thisTax = 'skills';}
elseif ( is_tax('source') ) { 
	$thisTax = 'source';
	$logo_slug = $thisSlug;
} elseif ( is_page() ) {
	$thisTax = $taxSlug;
	if ($thisTax == 'stars') {
		$these_children = get_term_children($thisTermID, $thisTax); 
		$winCount = get_term_meta($thisTermID, 'wins', true);
	};
} else { $thisTax = 'tag';};

$this_parent_id = 0;
$this_parent_id = $thisTerm->parent;
if ($this_parent_id !== 0) {
		$isChild = true;
		$this_parent = get_term( $this_parent_id, $thisTax );
		$this_parent_slug = $this_parent->slug;
		$logo_slug = $this_parent_slug;
		$this_parent_title = $this_parent->name;
		if ($logo_url == '') {
			$logo_url = get_term_meta($this_parent_id, 'logo', true);
		};
		if ($twitter_url == '') {
			$twitter_url = get_term_meta($this_parent_id, 'twitter', true);
		};
		if ($twitch_url == '') {
			$twitch_url = get_term_meta($this_parent_id, 'twitch', true);
		};
		if ($youtube_url == '') {
			$youtube_url = get_term_meta($this_parent_id, 'youtube', true);
		};
		if ($website_url == '') {
			$website_url = get_term_meta($this_parent_id, 'website', true);
		};
};

if ($twitter_url != '' || $twitch_url != '' || $youtube_url != '' || $website_url != '') {
	$has_data = true;
}
if ($underdogs) {
	$theTag = 'Underdogs';
	$thisTermName = 'Underdogs';
} ?>

<header id="archive-header">
	<div id="archive-left">
		<div id="archive-logo">
			<?php if ($logo_url != '') { ?>
				<img src="<?php echo $logo_url; ?>" class="archive-logo-img">
			<?php } else { ?>
				<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/rl_trans.png" class="archive-logo-img">
			<?php } ?>
		</div>
	</div><div id="archive-right">
		<div id="archive-title" <?php if ($has_data) { ?>class="with-data"<?php }; ?>>
			<h2>
				<?php if ($isChild && $thisTax == 'source') { ?>
					<a href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $this_parent_slug; ?>"><?php echo $this_parent_title; ?></a>: 
				<?php }; ?>
				<a href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $thisSlug; ?>"><?php echo $thisTermName; ?></a>
				<?php if ($isChild && $thisTax == 'stars') { ?>
					(<a href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $this_parent_slug; ?>"><?php echo $this_parent_title; ?></a>)
				<?php }; 
				if ($thisTax == 'stars') {
					echo " - "; print_r($winCount); echo " Wins"; 
				}; ?>
			</h2>
		</div>
		<?php if ($has_data) { ?>
			<div id="archive-data">
				<?php if ( !empty($these_children) && !is_wp_error($these_children) ) { ?>
					<div id="archive-children">
						<?php foreach ($these_children as $child) {
							$child_term = get_term($child, $thisTax);
							$child_slug = $child_term->slug;
							$child_name = $child_term->name; ?>
							<a href="<?php echo $thisDomain; ?>/<?php echo $thisTax; ?>/<?php echo $child_slug; ?>" class="archive-data-child-link"><?php echo $child_name; ?></a>
						<?php }; ?>
					</div>
				<?php }; ?>
				<?php if ($website_url != '') { ?><a href="<?php echo $website_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button website"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/internet-logo.png" alt="website link"></div></a><?php }; ?>
				<?php if ($twitter_url != '') { ?><a href="<?php echo $twitter_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button twitter"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/Twitter-logo.png" alt="twitter link"></div></a><?php }; ?>
				<?php if ($twitch_url != '') { ?><a href="<?php echo $twitch_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button twitch"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/Twitch-purple-logo.png" alt="twitch link"></div></a><?php }; ?>
				<?php if ($youtube_url != '') { ?><a href="<?php echo $youtube_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button youtube"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/youtube-logo.png" alt="youtube link"></div></a><?php }; ?>
				<?php if ($discord_url != '') { ?><a href="<?php echo $discord_url; ?>" class="archive-data-link" target="_blank"><div class="archive-data-button discord"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/Discord-logo.png" alt="discord link"></div></a><?php }; ?>
			</div>
		<?php }; ?>
	</div>
</header>