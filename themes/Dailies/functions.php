<?php 

function basedailies_enqueue_style() {
	wp_enqueue_style( 'dailies-base', '/wp-content/themes/Dailies/style.css', false ); 
}
add_action( 'wp_enqueue_scripts', 'basedailies_enqueue_style' );

add_theme_support( 'post-thumbnails' );
add_image_size('small', 350, 800);
add_theme_support( 'title-tag' );


/*** Quicktags for post editor ***/
function appthemes_add_quicktags() {
    if (wp_script_is('quicktags')){
?>
<script type="text/javascript">
    QTags.addButton( 'embed_container', 'embed-container', `<div class="embed-container"></div>`);
</script>
<?php
    }
}

add_action( 'admin_print_footer_scripts', 'appthemes_add_quicktags' );
/*** End Quicktags ***/

function stepBackDate($steps) {
	global $year;
	global $month;
	global $day;
	$thirtyDays = array(4, 6, 9, 11); //these are the numbers of the months with 30 days
	$extraSteps = $day - $steps;
	if ($day > $steps) {
		$day = $day - $steps;
	} else {
		if ( $month == 1 ) {
			$month = 12;
			$year = $year - 1;
		} else {
			$month = $month - 1;
		}
		if ( in_array($month, $thirtyDays) ) {
			$day = 30 + $extraSteps;
		} elseif ($month == 2) {
			$day = 28 + $extraSteps;
		} else {
			$day = 31 + $extraSteps;
		}
	}
}

function increase_views() {
	$postID = $_POST['id'];
	$viewType = $_POST['viewType'];
	if ( $viewType === 'gfy' ) {
		increaseGFYViews($postID);
	} elseif ($viewType === 'fullClip') {
		increaseFullClipViews($postID);
	};
}
function increaseGFYViews($postID) {
	$old_gfy_viewcount = get_post_meta($postID, 'gfyViewcount', true);
	$new_gfy_viewcount = $old_gfy_viewcount + 1; 
	$gfy_viewcount_update_success = update_post_meta($postID, 'gfyViewcount', $new_gfy_viewcount);
}
function increaseFullClipViews($postID) {	
	$old_fullClip_viewcount = get_post_meta($postID, 'fullClipViewcount', true);
	$new_fullClip_viewcount = $old_fullClip_viewcount + 1; 
	$gfy_viewcount_update_success = update_post_meta($postID, 'fullClipViewcount', $new_fullClip_viewcount);
}

add_action( 'wp_enqueue_scripts', 'enqueue_increase_views');
function enqueue_increase_views() {
	wp_register_script( 'ajax-increase-views', '/wp-content/themes/Dailies/js/increase_views.js' );
	$increase_views_data = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	);
	wp_localize_script( 'ajax-increase-views', 'data_for_increasing_views', $increase_views_data );

	wp_enqueue_script('ajax-increase-views');
}

add_action( 'wp_ajax_increase_views', 'increase_views' );
add_action( 'wp_ajax_nopriv_increase_views', 'increase_views' );

?>