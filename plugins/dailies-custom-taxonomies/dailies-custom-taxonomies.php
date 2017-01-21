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

$stars_metas = ['logo', 'twitter', 'twitch', 'youtube'];
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