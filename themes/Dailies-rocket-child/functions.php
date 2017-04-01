<?php 

function childdailies_enqueue_style() {
	wp_enqueue_style( 'dailies-child', '/wp-content/themes/Dailies-rocket-child/style.css', array( 'dailies-base' ), false ); 
}
add_action( 'wp_enqueue_scripts', 'childdailies_enqueue_style' );

$thisDomain = get_site_url();

function this_dailies_vars() {
	global $thisDomain;
	$thisDailiesVars = array(
		'domain' => $thisDomain,
		'nav-links' => array( //This function iterates through each of element of this top level array, creating a link for each array within it. The ID of the link is the first element in the sub-array, the href of the link is second, adn the display text is third.
		0 => ['medal', 'href="' . $thisDomain . '"',  '<img src="' . $thisDomain . '/wp-content/uploads/2016/10/Medal-small.png" class="headermedal">'],
		1 => ['search', 'onclick="toggleSearch()"', 'Search'],
		2 => ['winners', 'href="' . $thisDomain . '/tag/winners"', 'Winners'],
		3 => ['rules', 'href="' . $thisDomain . '/rules/"', 'Rules'],
		4 => ['stars', 'href="' . $thisDomain . '/stars/"', 'Stars'],
		5 => ['source', 'href="' . $thisDomain . '/source/"', 'Tournaments'],
		7 => ['live', 'href="' . $thisDomain . '/live/"', 'Live'],
		8 => ['enter', 'href="mailto:submit@therocketdailies.com?subject=Rocket%20Dailies%20Submission"', 'Suggest'],
		9 => ['twitter', 'href="https://twitter.com/Rocket_Dailies"', '<img src="' . $thisDomain . '/wp-content/uploads/2016/09/TWT.png" class="socialimg">'],
//		8 => ['youtube', 'href="https://www.youtube.com/channel/UCj_8OxVh2r8ruUlPKZ74kYw"', '<img src="' . $thisDomain . '/wp-content/uploads/2016/08/YT.png" class="socialimg">'],
//		9 => ['twitch', 'href="https://www.twitch.tv/the_rocket_dailies"', '<img src="' . $thisDomain . '/wp-content/uploads/2016/09/TW.png" class="socialimg">']
		)
	);
	return $thisDailiesVars;
};

$thisLogoSmall = '';

?>