<?php get_header(); ?>
<div class="wrapper">
<div class="contentContainer">

	<div id="propbox">
		<div class="propaganda" id="propLeft">Today's Prize: $25.00</div>
		<div class="propaganda" id="propRight">More Coming Soon...</div>
	</div>

	<?php include( locate_template('userbox.php') ); ?>

	<div id="winnersection">

	<?php $winnerSection = true;
		$winnerArgs = array(
		'tag' => 'winners',
		'category_name' => 'noms',
		'posts_per_page' => 1,
		);
	$postDataWinners = get_posts($winnerArgs);
	foreach ( $postDataWinners as $post) : setup_postdata($post); 
		include(locate_template('thing.php'));
	endforeach;
	$winnerSection = false; ?>
	</div>

	<?php date_default_timezone_set('America/Chicago'); // So this midnight is my midnight
	$today = getdate(); //returns an array with the following keys
	$year = $today[year];
	$month = $today[mon];
	$day = $today[mday]; //day as a number
	$weekday = $today[wday]; //day as day of the week (monday, tuesday, thursday, wednesday, sunday, saturday)
	($paged == 0 ) ? $my_page = 0 : $my_page = $paged - 1;
	stepBackDate($my_page); // Go back a day for each page that we've scrolled, because each page is a day
	$my_page++;

	$nomArgs = array(
		'category_name' => 'noms',
		'posts_per_page' => 10,
		'orderby' => 'meta_value_num',
		'meta_key' => 'votecount',
		'date_query' => array(
			array(
				'year'  => $year,
				'month' => $month,
				'day'   => $day,
			),
		),
	);
	$postDataNoms = get_posts($nomArgs); 
	$i = 0;
	while ( !$postDataNoms && $i < 14 ) : // If there's no posts for the day, go back up to 14 more days looking for posts. If we get to a period of 14 days without posts, that's probably the end of the line
		stepBackDate(1);
		$newNomArgs = array(
			'category_name' => 'noms',
			'posts_per_page' => 10,
			'orderby' => 'meta_value_num',
			'meta_key' => 'votecount',
			'date_query' => array(
				array(
					'year'  => $year,
					'month' => $month,
					'day'   => $day,
				),
			),
		);
		$postDataNoms = get_posts($newNomArgs);
		$i++;
		$my_page++; //Since pages are how we keep track of the day, we need to tick up my_page even for days with no posts
	endwhile;
	if ($i == 13) { ?>
		<a class="earlier nomore pull">That's all, folks</a>
	<?php } else {

	$dateObj   = DateTime::createFromFormat('!m', $month); 
	$monthName = $dateObj->format('F'); // These two lines turn $month into the name of the month, but I've forgotten how the syntax works exactly ?>

	<div class="daytitle pull">Nominees for <?php echo $monthName; echo " "; echo $day; 
		if ( $day == 1 || $day == 31 || $day == 21 ) {
			echo "st";
		} elseif ( $day == 2 || $day == 22 ) {
			echo "nd";
		} elseif ( $day == 3 || $day == 23 ) {
			echo "rd";
		} else {
			echo "th";
		}; ?> 
	</div>

	<?php $adCounter = 0;
	$adsCounted = 0;
	foreach ( $postDataNoms as $post) : setup_postdata($post);
		include(locate_template('thing.php'));
	endforeach;
	$my_page++; ?>

	<a href="<?php echo $thisDomain; ?>/page/<?php echo $my_page; ?>" class="earlier more pull"><img src='<?php echo $thisDomain; ?>/wp-content/uploads/2016/09/More.png' class='earlierIMG'></a>
	<?php }; ?>
	
</div><?php include(locate_template('sidebar.php')); ?>
</div>

<?php get_footer(); ?>