<?php

function add_custom_cron_schedules($schedules) {
	$schedules['twiceHourly'] = array(
		'interval' => 1800,
		'display' => __("Twice Hourly"),
	);

	$schedules['minute'] = array(
		'interval' => 60,
		'display' => __("Every Minute"),
	);

	$schedules['tenMinutes'] = array(
		'interval' => 600,
		'display' => __("Every Ten Minutes"),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'add_custom_cron_schedules' );

if( !wp_next_scheduled( 'pull_clips' ) ) {
   wp_schedule_event( time(), 'twiceHourly', 'pull_clips' );
}

add_action( 'pull_clips', 'pull_clips_cron_handler' );
function pull_clips_cron_handler() {
	pull_all_clips();
}

if( !wp_next_scheduled( 'populate_vote_db' ) ) {
   wp_schedule_event( time(), 'tenMinutes', 'populate_vote_db' );
}

add_action( 'populate_vote_db', 'populate_vote_db_handler' );
function populate_vote_db_handler() {
	populate_vote_db();
}

if( !wp_next_scheduled( 'clean_pulled_clips_db' ) ) {
   wp_schedule_event( time(), 'daily', 'clean_pulled_clips_db' );
}
add_action( 'clean_pulled_clips_db', 'clean_pulled_clips_db_cron_handler' );
function clean_pulled_clips_db_cron_handler() {
	$clipTimestamp = convertTwitchTimeToTimestamp($clipData['age']);
	if ($clipTimestamp < time() - 14 * 24 * 60 * 60) {
		deleteSlugFromPulledClipsDB($clipData['slug']);
		continue;
	}
}

?>