<?php

global $vote_history_db_version;
$vote_history_db_version = '0.1';

function createVoteHistoryDB() {
	global $wpdb;
	global $vote_history_db_version;
	$installed_version = get_option("vote_history_db_version");

	if ($installed_version != $vote_history_db_version) {
		$table_name = $wpdb->prefix . "vote_history_db";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = 
			"CREATE TABLE " . $table_name . " (
			id INT NOT NULL AUTO_INCREMENT,
			hash VARCHAR(255) NOT NULL,
			postid INT NOT NULL,
			PRIMARY KEY  (id)
		) " . $charset_collate . ";";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		basicPrint($wpdb->last_error);

		update_option('vote_history_db_version', $vote_history_db_version);
	}
}

add_action('plugins_loaded', 'update_vote_history_db_check');
function update_vote_history_db_check() {
	global $vote_history_db_version;
	if (get_site_option("vote_history_db_version") != $vote_history_db_version) {
		createVoteHistoryDB();
	}
}

global $vote_db_version;
$vote_db_version = '0.1';

function createVoteDB() {
	global $wpdb;
	global $vote_db_version;
	$installed_version = get_option("vote_db_version");

	if ($installed_version != $vote_db_version) {
		$table_name = $wpdb->prefix . "vote_db";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = 
			"CREATE TABLE " . $table_name . " (
			id INT NOT NULL AUTO_INCREMENT,
			slug NVARCHAR(255) NOT NULL,
			hash VARCHAR(255) NOT NULL,
			weight SMALLINT NOT NULL,
			PRIMARY KEY  (id)
		) " . $charset_collate . ";";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		basicPrint($wpdb->last_error);

		update_option('vote_db_version', $vote_db_version);
	}
}

add_action('plugins_loaded', 'update_vote_db_check');
function update_vote_db_check() {
	global $vote_db_version;
	if (get_site_option("vote_db_version") != $vote_db_version) {
		createVoteDB();
	}
}

?>