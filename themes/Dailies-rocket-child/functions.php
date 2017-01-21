<?php 

function childdailies_enqueue_style() {
	wp_enqueue_style( 'dailies-child', '/wp-content/themes/Dailies-rocket-child/style.css', array( 'dailies-base' ), false ); 
}
add_action( 'wp_enqueue_scripts', 'childdailies_enqueue_style' );

function trendingTags() {
$trendingTags = array(
		0 => array(
			'slug' => 'jukes',
			'tax' => 'skills'
		),
		1 => array(
			'slug' => 'garrettg',
			'tax' => 'stars'
		),
		2 => array(
			'slug' => 'air-dribbles',
			'tax' => 'skills'
		),
		3 => array(
			'slug' => 'kuxir97',
			'tax' => 'stars'
		),
		4 => array(
			'slug' => 'dunks',
			'tax' => 'skills'
		),
		5 => array(
			'slug' => 'pinches',
			'tax' => 'skills'
		),
		6 => array(
			'slug' => 'rizzo',
			'tax' => 'stars'
		),
		7 => array(
			'slug' => 'aerials',
			'tax' => 'skills'
		),
		8 => array(
			'slug' => 'team-play',
			'tax' => 'skills'
		),
		9 => array(
			'slug' => 'rlcs',
			'tax' => 'source'
		),
		10 => array(
			'slug' => 'saves',
			'tax' => 'skills'
		),
		11 => array(
			'slug' => 'klassux',
			'tax' => 'stars'
		),
		12 => array(
			'slug' => 'gfinity',
			'tax' => 'source'
		),
		13 => array(
			'slug' => 'goals',
			'tax' => 'skills'
		),
		14 => array(
			'slug' => 'torment',
			'tax' => 'stars'
		),
		15 => array(
			'slug' => 'wall-shots',
			'tax' => 'skills'
		),
		16 => array(
			'slug' => 'prorl',
			'tax' => 'source'
		),
		17 => array(
			'slug' => 'deevo',
			'tax' => 'stars'
		),
		18 => array(
			'slug' => 'dribbles',
			'tax' => 'skills'
		),
		19 => array(
			'slug' => 'mind-games',
			'tax' => 'skills'
		),
		20 => array(
			'slug' => 'zero-angle',
			'tax' => 'skills'
		),
	); 
	return $trendingTags;
};

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
		6 => ['skills', 'href="' . $thisDomain . '/skills/"', 'Skills'],
		7 => ['underdogs', 'href="' . $thisDomain . '/underdogs/"', 'Underdogs'],
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