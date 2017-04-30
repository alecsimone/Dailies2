<?php
/*
Plugin Name: Dailies Menu Bar
Plugin URI:  http://therocketdailies.com/
Description: Creates a menu bar from a given list of links
Version:     0.1
Author:      Alec Simone
License:     Do whatever the hell you want with it, it's mostly pretty shit code
*/

//First up, let's get rid of that stupid fucking admin bar
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}

function navLinks($navLinks) { // The array with data for the links should be in the child theme's functions.php file, inside the $thisDailiesVars array. At the start of header.php, the navlinks data array gets assigned to a variable, which is then passed to this function. 
	$thisDomain = get_site_url();
	$navCounter = 0;
	$mobileDisplay = 'mobileShow';
	foreach ($navLinks as $navLink) {
		if ($navCounter == 3) {
			$mobileDisplay = 'mobileHide';
		};
		if ($navLink[0] == 'search') {
			echo "<a $navLink[1] id='$navLink[0]' class='$mobileDisplay'>$navLink[2]</a>";
			echo "<div id='searchbox'>"; get_search_form(); echo "</div>";
			$navCounter++;
		} else {
			echo "<a $navLink[1] id='$navLink[0]' class='$mobileDisplay'>$navLink[2]</a>";
			$navCounter++;
		} 
	};
	echo "<a href='javascript:' class='hamburger' onclick='navBurger();'><img src='http://dailies.gg/wp-content/uploads/2016/12/Hamburger_icon.png' class='hamburger-icon'></a>";
};

function enqueue_dailies_menu_bar() {
	wp_register_script( 'dailies-menu-bar', plugin_dir_url(__FILE__) . 'dailies-menu-bar.js' );
	wp_enqueue_script('dailies-menu-bar');
};

add_action('wp_enqueue_scripts', 'enqueue_dailies_menu_bar');