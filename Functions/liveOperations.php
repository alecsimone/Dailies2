<?php

function post_trasher() {
	$postID = $_POST['id'];
	if (current_user_can('delete_published_posts', $postID)) {
		wp_trash_post($postID);
	};
	reset_chat_votes();
	echo json_encode($postID);
	wp_die();
}

add_action( 'wp_ajax_post_promoter', 'post_promoter' );
function post_promoter() {
	$postID = $_POST['id'];
	if (current_user_can('edit_others_posts', $postID)) {
		$category_list = get_the_category($postID);
		$category_name = $category_list[0]->cat_name;
		$authorID = get_post_field('post_author', $postID);
		if ($category_name === 'Prospects') {
			wp_remove_object_terms($postID, 'prospects', 'category');
			wp_add_object_terms( $postID, 'contenders', 'category' );
			absorb_votes($postID);
		} elseif ($category_name === 'Contenders') {
			wp_remove_object_terms($postID, 'contenders', 'category');
			wp_add_object_terms( $postID, 'nominees', 'category' );
		}
	};
	echo json_encode($postID);
	wp_die();
}

add_action( 'wp_ajax_post_demoter', 'post_demoter' );
function post_demoter() {
	$postID = $_POST['id'];
	if (current_user_can('edit_others_posts', $postID)) {
		$category_list = get_the_category($postID);
		$category_name = $category_list[0]->cat_name;
		$authorID = get_post_field('post_author', $postID);
		if ($category_name === 'Nominees') {
			wp_remove_object_terms($postID, 'nominees', 'category');
			wp_add_object_terms( $postID, 'contenders', 'category' );
		} elseif ($category_name === 'Contenders') {
			wp_remove_object_terms($postID, 'contenders', 'category');
			wp_add_object_terms( $postID, 'prospects', 'category' );
		} elseif ($category_name === 'Prospects') {
			post_trasher($postID);
		}
	};
}

add_action( 'wp_ajax_reset_live', 'reset_live' );
function reset_live() {
	$reset_time_to = $_POST['timestamp'];
	$livePageObject = get_page_by_path('live');
	$liveID = $livePageObject->ID;
	update_post_meta($liveID, 'liveResetTime', $reset_time_to);
	echo json_encode($reset_time_to);
	wp_die();
}

?>