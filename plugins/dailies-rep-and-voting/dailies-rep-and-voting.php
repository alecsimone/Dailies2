<?php
/*
Plugin Name: Dailies Rep and Voting
Plugin URI:  http://therocketdailies.com/
Description: Enables User Rep, Post Scores, and Voting
Version:     0.1
Author:      Alec Simone
License:     Do whatever the hell you want with it, it's mostly pretty shit code
*/

function enqueue_ajax_vote() {
	wp_register_script( 'ajax-vote', plugin_dir_url(__FILE__) . 'dailies-ajax-vote.js' );
	
	$nonce = wp_create_nonce('daily_vote_nonce');

	$daily_vote_data = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce' => $nonce,
		'medal' => 'http://therocketdailies.com/wp-content/uploads/2016/12/Medal-small-100.png',
		'emptyVoteIcon' => 'http://therocketdailies.com/wp-content/uploads/2016/12/Vote-Icon-100.png'
	);
	wp_localize_script( 'ajax-vote', 'daily_vote', $daily_vote_data );

	wp_enqueue_script('ajax-vote');
};

add_action( 'wp_ajax_daily_vote', 'daily_ajax_vote' );
add_action( 'wp_ajax_nopriv_daily_vote', 'daily_ajax_vote' );

add_action( 'wp_ajax_daily_unvote', 'daily_ajax_unvote' );
add_action( 'wp_ajax_nopriv_daily_unvote', 'daily_ajax_unvote' );

add_action('wp_enqueue_scripts', 'enqueue_ajax_vote' );

function daily_ajax_vote() { // Our function for voting
	$nonce = $_POST['vote_nonce']; // Grab the nonce we passed through in the AJAX call
	if ( !wp_verify_nonce($nonce, 'daily_vote_nonce') ) { // Check if it's valid. If not...
		die("Busted!");
	}

	$vote_post_id = $_POST['id']; // Get the ID of the post we're currently operating on

	if ( is_user_logged_in() ) {

		$user_id = get_current_user_id(); // Get the user's ID
		$user_rep = get_user_meta($user_id, 'rep', true); // And their rep
		if ( $user_rep == '' ) {$user_rep = 1;}; // If they are logged in, but don't have Rep yet, set their rep to 1

		$old_voteledger = get_post_meta( $vote_post_id, 'voteledger', true); // Grab the list of registered users who have voted on this post

		if ( !array_key_exists($user_id, $old_voteledger) ) { // If the userID is not in the voteledger for this post
			$voteledger = $old_voteledger; 
			$voteledger[$user_id] = $user_rep; // Add a new element to our array with the User ID as the Key and their current rep as the value. We'll use this to make sure we take away the right amount of points when they unvote
			$ledger_update_success = update_post_meta($vote_post_id, 'voteledger', $voteledger); // Update the ledger, store the success

			$current_count = get_post_meta( $vote_post_id, 'votecount', true); // Get the current score
			$new_count = $current_count + $user_rep; // Add the user's rep to it
			$update_success = update_post_meta( $vote_post_id, 'votecount', $new_count); // Update, store

			$current_time = time(); // Get the current time
			$rep_votes = get_user_meta($user_id, 'repVotes', true); // Get the array of posts for which the user got rep
			$rep_votes_keys = array_keys($rep_votes); // get an array of the post IDs for which the user got rep
			$rep_votes_count = count($rep_votes_keys); // Count that array

			if ($rep_votes_count >= 1) { // If the user has gotten rep for 3 or more posts
				$target_count = $rep_votes_count; //We need the last element in the array
				$target_key = $rep_votes_keys[$target_count]; // Get the post ID for the 3rd most recent vote
				$third_time = $rep_votes[$target_key]; // This will return the timestamp of the third to last post
			} else { $third_time = 0; }; 
			
			$goaltime = $third_time + 86400;
			$test = $current_time - $goaltime;
			if ( $current_time >= $goaltime ) { // If the third vote back was more than 1 day ago, or if the user doesn't have 5 elements in their repVotes list
				$test = "Conditional isn't working";
				$new_rep = $user_rep + .1; // tick up their rep
				$rep_votes[$vote_post_id] = $current_time; // Add the current post to rep_votes with the time at which the vote was cast
				$user_update_success = update_user_meta($user_id, 'rep', $new_rep); // And update it
				$rep_votes_success = update_user_meta($user_id, 'repVotes', $rep_votes); //update, store

			} else { $new_rep = $user_rep; };

			$old_vote_history = get_user_meta($user_id, 'voteHistory', true); // get the user's vote history
			$new_vote_history = $old_vote_history; // Make a new array, fill it with the vote history
			$new_vote_history[] = $vote_post_id; // Add this post to the vote history
			$history_update_success = update_user_meta($user_id, 'voteHistory', $new_vote_history); // Update, store

			$voted = true;

		} else {

			$rep_votes = get_user_meta($user_id, 'repVotes', true); // get the list of posts the user has gotten rep for voting on

			if ( array_key_exists($vote_post_id, $rep_votes) ) { // if this post was one of the ones they got rep for
				$new_rep = $user_rep - .1; // Take back that rep we gave them
				$user_update_success = update_user_meta($user_id, 'rep', $new_rep); // Update their rep and store the success

				unset($rep_votes[$vote_post_id]); // Take the key for this post out of the array
				$user_rep_votes_success = update_user_meta($user_id, 'repVotes', $rep_votes);
			} else { $new_rep = $user_rep; };

			$testvar = $old_voteledger[$user_id];

			$current_count = get_post_meta( $vote_post_id, 'votecount', true); // get the current score of the post
			$new_count = $current_count - $old_voteledger[$user_id]; // Take away the amount of rep this user contributed to that
			$update_success = update_post_meta( $vote_post_id, 'votecount', $new_count); // Update the score, store the success

			unset($voteledger[$user_id]); // Take this user out of the list of users who voted for this post
			$ledger_update_success = update_post_meta( $vote_post_id, 'voteledger', $voteledger); // Update the new voteledger, store success

			$old_vote_history = get_user_meta($user_id, 'voteHistory', true); // get the user's vote history
			$new_vote_history = $old_vote_history; // copy it into a new array
			$unvoted_post_key = array_search($vote_post_id, $new_vote_history); // Find this post in the history
			unset($new_vote_history[$unvoted_post_key]); // kill it
			$history_update_success = update_user_meta($user_id, 'voteHistory', $new_vote_history); // Update, Store

			$voted = false;
		}

	} else {

		$user_rep = .1; //  set their rep to .1
		$client_ip = $_SERVER['REMOTE_ADDR']; // And get their IP address

		$old_guestlist = get_post_meta($vote_post_id, 'guestlist', true); // Grab the array of IP addresses of unregistered users who have voted on this post

		if (!in_array($client_ip, $old_guestlist)) { // If the guest hasn't voted on this post yet

			$new_guestlist = $old_guestlist;
			$new_guestlist[] = $client_ip; // Add our user to the guestlist
			$ip_update_success = update_post_meta( $vote_post_id, 'guestlist', $new_guestlist); // update the guestlist, and store a variable indicating if the update was successful

			$new_rep = $user_rep; // We still need to set $new_rep

			$current_count = get_post_meta( $vote_post_id, 'votecount', true); // Get the current score of the post
			$new_count = $current_count + $user_rep; // Add the user's rep to the score
			$update_success = update_post_meta( $vote_post_id, 'votecount', $new_count); // Update the score, store the success as a variable

			$voted = true;

		} else {

			$new_guestlist = $old_guestlist; // Make a new variable just for clarity
			$guest_key = array_search($client_ip, $new_guestlist); // Find their IP address in the guestlist
			unset($new_guestlist[$guest_key]);  // Get it out of there
			$ip_update_success = update_post_meta( $vote_post_id, 'guestlist', $new_guestlist); // Update the guestlist and store the success as a variable

			$current_count = get_post_meta( $vote_post_id, 'votecount', true); // get the current score of the post
			$new_count = $current_count - $user_rep; // Subtract their rep
			$update_success = update_post_meta( $vote_post_id, 'votecount', $new_count); // Update the score, store the success as a variable

			$new_rep = $user_rep; // Still have to set $new_rep

			$voted = false;
		}
	}

	$results = array ( // This is all the data we're passing back to our AJAX function
		'vote_success' => $update_success,
		'rep_success' => $user_update_success,
		'ip_success' => $ip_update_success,
		'ledger_success' => $ledger_update_success,
		'history_success' => $history_update_success,
		'rep_votes_success' => $rep_votes_success,
		'new_rep' => $new_rep,
		'new_score' => $new_count,
		'voted' => $voted,
		'log' => 'voted!'
	); 
	
	echo json_encode($results); //We'll encode it as JSON so it will work

	wp_die(); // And then, as all things must, we die.
};

add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>
 
    <h3>Extra profile information</h3> 
    <table class="form-table">
        <tr>
            <th><label for="rep">Rep</label></th>
            <td>
                <input type="text" name="rep" id="rep" value="<?php echo esc_attr( get_the_author_meta( 'rep', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description">Your Rep</span>
            </td>
        </tr>
    </table>
<?php }

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id ) {
 
    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;
    update_user_meta( absint( $user_id ), 'rep', wp_kses_post( $_POST['rep'] ) );
}

add_action('publish_post', 'set_default_custom_fields');
function set_default_custom_fields($ID){
	global $wpdb;
    if( !wp_is_post_revision($post_ID) ) {add_post_meta($ID, 'votecount', 0, true);};
};