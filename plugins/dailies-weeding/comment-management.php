<?php 

function addCommentToDB($commentData) {
	global $wpdb;
	$table_name = $wpdb->prefix . "clip_comments_db";
	
	$commentArray = array(
		'slug' => $commentData['slug'],
		'commenter' => getPersonsHash(get_current_user_id()),
		'comment' => $commentData['comment'],
		'score' => 0,
		'time' => time(),
		'replytoid' => $commentData['replytoid'] ? $commentData['replytoid'] : null,
	);

	$insertionSuccess = $wpdb->insert(
		$table_name,
		$commentArray
	);
	if ($insertionSuccess) {
		return $wpdb->insert_id;
	} else {
		return $wpdb->last_error;
	}
}

add_action( 'wp_ajax_post_comment', 'postCommentHandler' );
function postCommentHandler() {
	$commentData = array(
		'slug' => $_POST['slug'], 
		'comment' => $_POST['commentObject']['comment'],
	);
	if ($_POST['commentObject']['replytoid'] !== undefined) {
		$commentData['replytoid'] = $_POST['commentObject']['replytoid'];
	}

	$status = addCommentToDB($commentData);
	killAjaxFunction($status);
}

function getCommentByID($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "clip_comments_db";
	$comment = $wpdb->get_row(
		"SELECT *
		FROM $table_name
		WHERE id = '$id'
		", ARRAY_A
	);
	return $comment;
}

add_action( 'wp_ajax_yea_comment', 'yea_comment' );
function yea_comment() {
	$commentID = $_POST['commentID'];

	global $wpdb;
	$table_name = $wpdb->prefix . "clip_comments_db";
	$comment = getCommentByID($commentID);

	$commentPerson = getPersonInDB($comment['commenter']);
	$currentPersonsHash = getPersonsHash(get_current_user_id());
	if ($commentPerson['hash'] === $currentPersonsHash) {
		killAjaxFunction("you can't yea your own comment");
	}

	$comment['score'] = intval($comment['score']) + 1;

	$update_array = array('score' => $comment['score']);

	$where_array = array(
		'id' => $commentID,
	);

	$wpdb->update(
		$table_name,
		$update_array,
		$where_array
	);

	killAjaxFunction($comment);
}

add_action( 'wp_ajax_del_comment', 'del_comment' );
function del_comment() {
	$commentID = $_POST['commentID'];

	global $wpdb;
	$table_name = $wpdb->prefix . "clip_comments_db";
	$where = array(
		'id' => $commentID,
	);
	
	$wpdb->delete(
		$table_name,
		$where
	);
}

?>