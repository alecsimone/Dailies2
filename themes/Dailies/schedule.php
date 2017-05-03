<?php $schedule = array(
	'Monday' => array(
		'vapour' => ['Vapour', 'oce-rocket-league', 654, 'Monday Night Legends 2v2', '5 AM EST'],
		'gfinity' => ['GFinity', 'gfinity', 194, 'EU - 3v3 - &pound;150', '2 PM EST'],
		'baguette' => ['Baguette', 'rocket-baguette', 585, 'EU GFinity, French Cast', '2 PM EST'],
		'mockit' => ['Mockit', 'mockit-league', 85, 'NA - 1v1 - $50', '7 PM EST'],
		'prl' => ['ProRL', 'prorl', 81, 'NA - 3v3 - $150', '8 PM EST'],
		'vvv' => ['vVv', 'vvv-gaming', 239, 'NA - 3v3 - $50', '9 PM EST'],
		'bl' => ['Boost', 'boost-legacy', 401, 'NA - 1v1 - $10', '10 PM EST'],
		'me' => ['The Dailies', 'rocket-dailies', 688, 'Nom Stream', 'Midnight EST'],
	),
	'Tuesday' => array(
		//'esloce' => ['ESL OCE', 'esl-australia', 616, 'OCE - 3v3', '5 AM EST'],
		//'gfinity' => ['GFinity', 'gfinity', 194, 'EU - 2v2 - &pound;60', '2 PM EST'],
		'mockit' => ['Mockit', 'mockit-league', 85, 'MCS EU & NA', '1PM / 6PM EST'],
		'nexus' => ['Nexus', 'nexus-gaming', 389, 'NA - 1v1 - $50', '8 PM EST'],
		'nl' => ['NorthernLight', 'northern-light', 734, 'NA - 2v2 - $10', '8PM EST'],
		'collision' => ['Collision', 'collision', 735, 'NA - 2v2', '8PM EST'],
		'myth' => ['Mythical', 'mythical-esports', 251, 'NA - 3v3 - $45', '9 PM EST'],
		//'beyond' => ['Astronauts', 'teambeyondnet', 540, 'NA - 3v3 - $1000', '10PM EST'],
		'me' => ['The Dailies', 'rocket-dailies', 688, 'Nom Stream', 'Midnight EST'],
	),
	'Wednesday' => array(
		'jebdan' => ['JebroUnity', 'jebrounity', 619, 'EU - 2v2 - MidWeek Madness', '3 PM EST'],
		'RLES' => ['RL ES', 'rocket-league-es', 711, 'EU 3v3 #TGXRLP', '3 PM EST'],
		//'meta' => ['Metaleak', 'metaleak', 521, 'EU - 3v3 - &euro;200', '3 PM EST'],
		//'orsa' => ['Orsa', 'orsa', 307, 'Season 5 Playoffs', '6 PM EST'],
		'jboi' => ['JohnnyBoi_i', 'johnnyboi_i', 215, '1v1s', '6PM EST'],
		'rocketstreet' => ['RocketStreet', 'rocketstreet', 518, 'SAM - 1v1', '6:30PM EST'],
		'nexus' => ['Nexus', 'nexus-gaming', 389, 'NA - 2v2 - $100', '7 PM EST'],
		'bl' => ['Boost', 'boost-legacy', 401, 'NA - 3v3 - $40', '10 PM EST'],
		'me' => ['The Dailies', 'rocket-dailies', 688, 'Nom Stream', 'Midnight EST'],
	),
	'Thursday' => array(
		'gfinity' => ['GFinity', 'gfinity', 194, 'EU - 2v2 - &pound;60', '2 PM EST'],
		'rewind' => ['Rewind', 'rewindrl', 583, 'EU - 1v1 - $15', '2 PM EST'],
		'mockit' => ['Mockit', 'mockit-league', 85, 'MCS League Play', '3 PM EST'],
		'lief' => ['LiefX', 'liefx', 662, 'Dropshot & Roll', '3 PM EST'],
		'liquor' => ['Liquor', 'liquor-league', 559, 'NA - 3v3 - $100', '8 PM EST'],
		'myth' => ['Mythical', 'mythical-esports', 251, 'NA - 2v2 - $50', '9 PM EST'],
		'me' => ['The Dailies', 'rocket-dailies', 688, 'Nom Stream', 'Midnight EST'],
	),
	'Friday' => array(
		'OCE' => ['OCE RL', 'oce-rocket-league', 654, 'OCE - 3v3 - $100', '5:30 AM EST'],
		'gfinity' => ['GFinity', 'gfinity', 194, 'EU 3v3 / NA 2v2', '2 PM EST / 8PM EST'],
		'mockit' => ['Mockit', 'mockit-league', 85, 'MCS League Play', '3 PM EST'],
		'boost' => ['Boost', 'boost-legacy', 401, 'NA - 2v2 - $40', '10 PM EST'],
		'me' => ['The Dailies', 'rocket-dailies', 688, 'Nom Stream', 'Midnight EST'],
	),
	'Weekend' => array(
		'rla' => ['RL Asia', 'rocket-league-asia', 280, 'Asia - 3v3', '2 AM EST'],
		'metaleak' => ['Metaleak', 'metaleak', 521, 'EU - 2v2 - &euro;50', '10 AM EST'],
		'prl' => ['ProRL', 'prorl', 81, 'EU $150 3v3 / NA Bragging Rights', '11 AM EST / 9PM EST'],
		'rewind' => ['Rewind', 'rewindrl', 583, 'EU - 3v3 - &euro;45', '2:30 PM EST'],
		'rlcs' => ['RLCS', 'rlcs', 79, 'League Play', 'Sat & Sun'],
		'boost' => ['Boost', 'boost-legacy', 401, 'NA - 2v2 - $50', '3PM EST'],
		'nexus' => ['Nexus', 'nexus-gaming', 389, 'NA - 3v3 - $150', '8PM EST'],
		'throwdown' => ['Throwdown', 'throwdowntv', 531, 'OCE RLCS', '10PM EST'],
		'rlo' => ['RLO', 'rocket-league-oceania', 646, 'OCE 3v3', '3AM EST'],
	),
); 

$latestNomArgs = array(
	'category_name' => 'noms',
	'posts_per_page' => 1,
);
$latestNom = get_posts($latestNomArgs);
$latestNomDate = strtotime($latestNom[0]->post_date);
$latestNomDay = date('l', $latestNomDate);
if ($latestNomDay == 'Saturday' || $latestNomDay == 'Sunday') {
	$todaysSchedule = 'Weekend';
} else {
	$todaysSchedule = $latestNomDay;
}

?>