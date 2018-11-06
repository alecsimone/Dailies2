<?php

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
			// wp_remove_object_terms($postID, 'contenders', 'category');
			// wp_add_object_terms( $postID, 'prospects', 'category' );
			post_trasher($postID);
		} elseif ($category_name === 'Prospects') {
			post_trasher($postID);
		}
	};

	killAjaxFunction($postID);
}

function post_trasher($postID) {
	if (current_user_can('delete_published_posts', $postID)) {
		wp_trash_post($postID);
	};
	reset_chat_votes();
	return ($postID);
}

?>