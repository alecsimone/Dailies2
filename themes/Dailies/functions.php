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

?>