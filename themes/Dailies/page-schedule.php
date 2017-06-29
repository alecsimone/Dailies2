<?php get_header(); ?>

<style media="screen" type="text/css">
body:before {
	background: #222; /* Old browsers */
	background: -moz-radial-gradient(center, ellipse cover, #262626 2%, #161616 100%); /* FF3.6-15 */
	background: -webkit-radial-gradient(center, ellipse cover, #262626 2%,#161616 100%); /* Chrome10-25,Safari5.1-6 */
	background: radial-gradient(ellipse at center, #262626 2%, #161616 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
}
a {
	text-decoration: none;
}
</style> 

<div id="schedule-container">
	<?php include( locate_template('schedule.php') );
	foreach ($shiftedSchedule as $day => $daySchedule ) { ?>
		<div class="schedule-day-title"><?php echo $day; ?></div> 
		<div class="schedule-day">
			<?php foreach ($daySchedule as $dayScheduleEntry) { 
				$entryLogo = get_term_meta($dayScheduleEntry[2], 'logo', true);
				$entrySourcePageURL = $thisDomain . "/source/" . $dayScheduleEntry[1];
				$entryTwitchURL = get_term_meta($dayScheduleEntry[2], 'twitch', true);
				$lastSlashPos = strrpos($entryTwitchURL, '/');
				$entryTwitchCode = substr($entryTwitchURL, $lastSlashPos);
				?>
				<a href="<?php echo $entrySourcePageURL; ?>" class="schedule-entry-link"><div class="schedule-entry">
					<img class="schedule-entry-logo" src="<?php echo $entryLogo; ?>">
					<div class="schedule-entry-info">	
						<div class="schedule-entry-source"><?php echo $dayScheduleEntry[0]; ?></div>
						<div class="schedule-entry-title"><?php echo $dayScheduleEntry[3]; ?></div>
						<div class="schedule-entry-time"><?php echo $dayScheduleEntry[4];?></div>
						<a href="<?php echo $entryTwitchURL; ?>" class="schedule-entry-link" target="_blank"><div class="schedule-entry-twitch-link"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/Twitch-purple-logo.png"><?php echo $entryTwitchCode; ?></div></a>
					</div>
				</div></a>
			<?php } ?>
		</div>
	<?php }
	?>
</div>
<?php get_footer(); ?>