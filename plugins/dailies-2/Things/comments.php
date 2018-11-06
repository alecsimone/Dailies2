<?php 

function getCommentsForSlug($slug) {
	global $wpdb;
	$table_name = $wpdb->prefix . "clip_comments_db";

	$slugData = $wpdb->get_results(
		"SELECT *
		FROM $table_name
		WHERE slug = '$slug'
		", ARRAY_A
	);
	return $slugData;
}

?>