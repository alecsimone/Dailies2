<?php /* Template Name: leaderboards */ 
get_header(); 

function makeLeaderList($postDataWinners) {
	$rlcsPlayers = ["Kaydop", "Turbopolsa", "ViolentPanda", "Fireburner", "GarretG", "JSTN", "Gimmick", "SquishyMuffinz", "Torment", "Yukeo", "Kuxir97", "Miztik", "JKnaps", "Kronovi", "Rizzo", "Mognus", "al0t", "gReazymeister", "Chausette45", "Ferra", "fruity", "AyyJayy", "PrimeThunder", "Wonder", "FlamE", "FreaKii", "Tylacto", "Fairy Peak", "Paschy90", "Scrub Killa", "Drippay", "Torsos", "BoritoB", "Kassio", "Chicago", "CorruptedG", "Klassux", "Ronaky", "Speed", "Tadpole", "Markydooda", "Nielskoek", "Pwndx", "EyeIgnite", "Metsanauris", "Remkoe", "Lethamyr", "Matt", "Zanejackey", "Maestro", "MummiSnow", "Snaski", "Allushin", "Sea Bass", "TyNotTyler", "Bluey", "Deevo", "Halcyon", "Rapid", "Vince"];
	$wincountArray = array();
	$wincountArray['all'] = 0;
	$wincountArray['RLCSPlayers'] = 0;
	foreach ($postDataWinners as $key => $postData) {
				// printKeyValue('key', $key);
		$thisID = $postData->ID;
		$stars = getPostStars($thisID);
		if (!is_array($stars)) {continue;}
		$postAlreadyCounted = false;			
		$wincountArray['all'] = $wincountArray['all'] + 1;
		foreach ($stars as $star) {
			$starName = $star->name;
					// printKeyValue('starname', $starName);
			if (array_key_exists($starName, $wincountArray)) {
				$wincountArray[$starName] = $wincountArray[$starName]+ 1;
			} else {
				$wincountArray[$starName] = 1;
			}
			if (array_search($starName, $rlcsPlayers) && !$postAlreadyCounted) {
				$wincountArray['RLCSPlayers'] = $wincountArray['RLCSPlayers'] + 1;
				$postAlreadyCounted = true;
			}
		}
	}
	arsort($wincountArray);
	foreach ($wincountArray as $player => $wincount) { 
		$starTermObject = get_term_by('name', $player, 'stars');
		$starSlug = $starTermObject->slug;
		$starLink = get_site_url() . '/stars/' . $starSlug;
		?>
		<div class='leader'>
			<a href="<?php echo $starLink; ?>" class="starLink">
				<?php printKeyValue($player, $wincount); ?>
			</a>
		</div>
	<?php }
}






?>

<section id="winLeaderboard">

	<div class="leaderboard" id="allTimeWinners">
		<h3 class="leaderboardTitle" id="allTimeHeader">All Time</h3>
		<?php 
			$winnerArgs = array(
				'tag' => 'winners',
				'posts_per_page' => -1,
			);
			$postDataWinners = get_posts($winnerArgs);
			makeLeaderList($postDataWinners);
		?>
	</div>

	<div class="leaderboard" id="sixMonthWinners">
		<h3 class="leaderboardTitle" id="sixMonthHeader">6 Months</h3>
		<?php 
			$winnerArgs = array(
				'tag' => 'winners',
				'posts_per_page' => -1,
				'date_query' => array(
					array(
						'after' => '6 months ago',
					),
				),
			);
			$postDataWinners = get_posts($winnerArgs);
			makeLeaderList($postDataWinners);
		?>
	</div>

	<div class="leaderboard" id="thirtyDaysWinners">
		<h3 class="leaderboardTitle" id="thirtyDaysHeader">30 Days</h3>
		<?php 
			$winnerArgs = array(
				'tag' => 'winners',
				'posts_per_page' => -1,
				'date_query' => array(
					array(
						'after' => '1 month ago',
					),
				),
			);
			$postDataWinners = get_posts($winnerArgs);
			makeLeaderList($postDataWinners);
		?>
	</div>

</section>

<style>
	#winLeaderboard {
		display: flex;
		justify-content: space-around;
		align-items: baseline;
		max-width: 2100px;
		margin: 100px auto;
	}
	.leaderboard {
		width: 500px;
		background: rgb(19, 23, 27);
		border-radius: 5px;
		box-shadow: 0 0 48px hsla(42, 90%, 80%, 0.1);
		border: 2px solid rgba(0,0,0,0.8);
	}
	.leaderboardTitle {
		display: block;
		padding: .6em;
		color: hsla(42, 79%, 64%, 1);
		font-size: 48px;
	}
	.leader {
    	padding: 24px 36px;
    	color: rgba(255,255,255,0.95);
    	text-align: left;
    	font-size: 32px;
    	border-top: 1px solid rgba(0,0,0,0.4);
	}
	.leader:nth-child(even) {
		background: hsla(30, 90%, 10%, 0.2);
	}
	a.starLink {
		color: inherit;
		text-decoration: none;
	}
	a.starLink:hover {	
		border-bottom: 1px solid rgba(255,255,255,0.2);
	}
</style>

<script>
</script>

<?php get_footer(); ?>