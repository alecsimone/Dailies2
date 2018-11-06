<?php

global $people_db_version;
$people_db_version = '0.2';

function createPeopleDB() {
	global $wpdb;
	global $people_db_version;
	$installed_version = get_option("people_db_version");

	if ($installed_version != $people_db_version) {
		$table_name = $wpdb->prefix . "people_db";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = 
			"CREATE TABLE " . $table_name . " (
			id INT NOT NULL AUTO_INCREMENT,
			hash VARCHAR(255) NOT NULL,
			picture VARCHAR(1028) DEFAULT 'none',
			dailiesID INT DEFAULT '-1',
			dailiesDisplayName NVARCHAR(255) DEFAULT '--',
			twitchName NVARCHAR(64) DEFAULT '--',
			rep TINYINT DEFAULT 1,
			lastRepTime VARCHAR(64) DEFAULT '--',
			email NVARCHAR(320) DEFAULT '--',
			provider VARCHAR(64) DEFAULT '--',
			role VARCHAR(64) DEFAULT '--',
			starID INT DEFAULT '-1',
			special TINYINT DEFAULT false,
			PRIMARY KEY  (id)
		) " . $charset_collate . ";";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		basicPrint($wpdb->last_error);

		update_option('people_db_version', $people_db_version);
	}
}

add_action('plugins_loaded', 'update_people_db_check');
function update_people_db_check() {
	global $people_db_version;
	if (get_site_option("people_db_version") != $people_db_version) {
		createPeopleDB();
	}
}

?>