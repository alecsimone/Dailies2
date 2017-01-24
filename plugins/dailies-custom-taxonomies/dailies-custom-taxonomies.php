<?php
/*
Plugin Name: Dailies Custom Taxonomies
Plugin URI:  http://therocketdailies.com/
Description: Adds skills, source, and stars taxonomies, as well as various meta fields for each of them
Version:     0.1
Author:      Alec Simone
License:     Do whatever the hell you want with it, it's mostly pretty shit code
*/

add_action( 'init', 'create_stars_taxonomy' );
function create_stars_taxonomy() {
	$labels = array(
		'name'                           => 'Stars',
		'singular_name'                  => 'Star',
		'search_items'                   => 'Search Stars',
		'all_items'                      => 'All Stars',
		'edit_item'                      => 'Edit Stars',
		'update_item'                    => 'Update Stars',
		'add_new_item'                   => 'Add New Star',
		'new_item_name'                  => 'New StarName',
		'menu_name'                      => 'Stars',
		'view_item'                      => 'View Star',
		'popular_items'                  => 'Popular Stars',
		'separate_items_with_commas'     => 'Separate stars with commas',
		'add_or_remove_items'            => 'Add or remove stars',
		'choose_from_most_used'          => 'Choose from the most used stars',
		'not_found'                      => 'No stars found'
	);

	register_taxonomy(
		'stars',
		'post',
		array(
			'hierarchical' => true,
			'labels' => $labels
		)
	);
};

add_action( 'init', 'create_skills_taxonomy' );
function create_skills_taxonomy() {
	register_taxonomy(
		'skills',
		'post',
		array(
			'label' => 'Skills',
		)
	);
}

add_action( 'init', 'create_source_taxonomy' );
function create_source_taxonomy() {

	$labels = array(
		'name'                           => 'Sources',
		'singular_name'                  => 'Source',
		'search_items'                   => 'Search Sources',
		'all_items'                      => 'All Sources',
		'edit_item'                      => 'Edit Sources',
		'update_item'                    => 'Update Sources',
		'add_new_item'                   => 'Add New Source',
		'new_item_name'                  => 'New SourceName',
		'menu_name'                      => 'Sources',
		'view_item'                      => 'View Source',
		'popular_items'                  => 'Popular Sources',
		'separate_items_with_commas'     => 'Separate sources with commas',
		'add_or_remove_items'            => 'Add or remove sources',
		'choose_from_most_used'          => 'Choose from the most used sources',
		'not_found'                      => 'No sources found'
	);

	register_taxonomy(
		'source',
		'post',
		array(
			'labels' => $labels,
			'hierarchical' => true
		)
	);
}

$stars_metas = ['logo', 'twitter', 'twitch', 'youtube', 'wins'];
add_action( 'stars_edit_form_fields', 'edit_star_form_fields', 10, 2 );
function edit_star_form_fields($term, $taxonomy) {
	global $stars_metas;
	foreach ($stars_metas as $meta) {
		$existing_meta = get_term_meta($term->term_id, $meta, true); ?>
		<tr class='form-field term-group-wrap'>
			<th scope="row"><label for="<?php echo $meta; ?>-input"><?php echo $meta; ?></label></th>
			<td><input type="text" id="<?php echo $meta; ?>-input" name="<?php echo $meta; ?>" value="<?php print_r($existing_meta); ?>"></input></td>
		</tr>
	<?php };
};

add_action( 'edited_stars', 'update_stars_meta', 10, 2 );
function update_stars_meta( $term_id, $tt_id) {
	global $stars_metas;
	foreach ($stars_metas as $meta) {
		if( isset($_POST[$meta])  ) {
			$new_meta = $_POST[$meta];
			update_term_meta( $term_id, $meta, $new_meta);
		};
	};
};

add_filter('manage_edit-stars_columns', 'add_stars_columns' );
function add_stars_columns( $columns ){
	global $stars_metas;
	foreach ($stars_metas as $meta) {
	    $columns[$meta] = $meta;
	};
    return $columns;
};

add_filter('manage_stars_custom_column', 'add_stars_column_content', 10, 3 );
function add_stars_column_content( $content, $column_name, $term_id ) {
	global $stars_metas;
	foreach ($stars_metas as $meta) {
		if ($column_name == $meta) {
			$term_id = absint($term_id);
			$meta_val = get_term_meta($term_id, $meta, true);

			if( !empty ($meta_val) ) {
				$content .= esc_attr($meta_val);
			};

		};
	};
	return $content;
};

$source_metas = ['logo', 'twitch', 'website', 'twitter', 'discord'];
add_action( 'source_edit_form_fields', 'edit_source_form_fields', 10, 2 );
function edit_source_form_fields($term, $taxonomy) {
	global $source_metas;
	foreach ($source_metas as $meta) {
		$existing_meta = get_term_meta($term->term_id, $meta, true); ?>
		<tr class='form-field term-group-wrap'>
			<th scope="row"><label for="<?php echo $meta; ?>-input"><?php echo $meta; ?></label></th>
			<td><input type="text" id="<?php echo $meta; ?>-input" name="<?php echo $meta; ?>" value="<?php print_r($existing_meta); ?>"></input></td>
		</tr>
	<?php };
};

add_action( 'edited_source', 'update_source_meta', 10, 2 );
function update_source_meta( $term_id, $tt_id) {
	global $source_metas;
	foreach ($source_metas as $meta) {
		if( isset($_POST[$meta])  ) {
			$new_meta = $_POST[$meta];
			update_term_meta( $term_id, $meta, $new_meta);
		};
	};
};

add_action('set_object_terms', 'update_winners', 10, 6);
function update_winners($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
	if ( in_array(29, $tt_ids) && !in_array(29, $old_tt_ids) ) {
		$updatedPostStars = get_the_terms($object_id, 'stars');
		foreach ($updatedPostStars as $winningStar) {
			$winnerID = $winningStar->term_id;
			$oldWinCount = get_term_meta($winnerID, 'wins', true);
			$newWinCount = $oldWinCount + 1;
			update_term_meta( $winnerID, 'wins', $newWinCount );
		}
	} elseif ( !in_array(29, $tt_ids) && in_array(29, $old_tt_ids) ) {
		$updatedPostStars = get_the_terms($object_id, 'stars');
		foreach ($updatedPostStars as $winningStar) {
			$winnerID = $winningStar->term_id;
			$oldWinCount = get_term_meta($winnerID, 'wins', true);
			$newWinCount = $oldWinCount - 1;
			update_term_meta( $winnerID, 'wins', $newWinCount );
		}
	}
}

add_action('post_updated', 'update_win_counts', 10, 2 );
/*function update_win_counts() {
	$termArgsEU = array(
		'taxonomy' => 'stars',
		'parent' => 374 //EU
	);
	$termArgsNA = array(
		'taxonomy' => 'stars',
		'parent' => 373 //NA
	);
	$NAStars = get_terms($termArgsNA);
	$EUStars = get_terms($termArgsEU);
	$allStars = array_merge($NAStars, $EUStars);

	foreach ($allStars as $star) { //For every child of the Regions, here's what we're going to do
		$starWinCount = 0; // Start off with a count of 0
		$starID = $star->term_id; //Get the ID of this team/player
		$starchildren = get_term_children( $starID, 'stars' ); // Then use that to get all its children (will return array of IDs)
		$childCount = count($starchildren); // Count that array
		if ($childCount > 0) { // And if there's anything in it, (ie, if we were dealing with a team, not a player)
			foreach ($starchildren as $childID) { //Take each player individually
				$childWinQueryArgs = array( //Args for a query that will return all of this child's wins
					'posts_per_page' => -1,
					'tax_query' => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'post_tag',
							'field' => 'slug',
							'terms' => 'winners'
						),
						array(
							'taxonomy' => 'stars',
							'field' => 'id',
							'terms' => $childID
						)
					)
				);
				$childWins = get_posts($childWinQueryArgs); //Run that query
				$starChildCount = count($childWins);//And count how many posts it returns
				update_term_meta( $childID, 'wins', $starChildCount);
				$starWinCount += $starChildCount; // Add that count to our total win count for the parent term
			}
		} else {
			$starWinQueryArgs = array( //Args for a query that will return all of this star's wins
					'posts_per_page' => -1,
					'tax_query' => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'post_tag',
							'field' => 'slug',
							'terms' => 'winners'
						),
						array(
							'taxonomy' => 'stars',
							'field' => 'id',
							'terms' => $starID
						)
					)
				);
			$starWins = get_posts($starWinQueryArgs);//Run that query
			$starWinCount = count($starWins);//And count how many posts it returns
		}
		update_term_meta( $starID, 'wins', $starWinCount);
	}
} */