<?php get_header(); ?>

<style media="screen" type="text/css">
body:before {
	background: #181818; /* Old browsers */
	background: -moz-radial-gradient(center, ellipse cover, #202020 2%, #121212 100%); /* FF3.6-15 */
	background: -webkit-radial-gradient(center, ellipse cover, #202020 2%,#121212 100%); /* Chrome10-25,Safari5.1-6 */
	background: radial-gradient(ellipse at center, #202020 2%,#121212 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
}
#menu-links-wrapper {
	background: rgba(13,56,6,.9);
}
a {
	text-decoration: none;
}
a#live {
	color: rgba(16,80,143,.85);
}
</style>

<div id="schedule-container">
	<?php include( locate_template('schedule.php') );
	foreach ($schedule as $day => $daySchedule ) { ?>
		<div class="schedule-day-title"><?php echo $day; ?></div> 
		<div class="schedule-day">
			<?php foreach ($daySchedule as $dayScheduleEntry) { 
				$entryLogo = get_term_meta($dayScheduleEntry[2], 'logo', true);
				$entrySourcePageURL = $thisDomain . "/source/" . $dayScheduleEntry[1];
				?>
				<a href="<?php echo $entrySourcePageURL; ?>"><div class="schedule-entry">
					<img class="schedule-entry-logo" src="<?php echo $entryLogo; ?>">
					<div class="schedule-entry-info">	
						<div class="schedule-entry-source"><?php echo $dayScheduleEntry[0]; ?></div>
						<div class="schedule-entry-title"><?php echo $dayScheduleEntry[3]; ?></div>
						<div class="schedule-entry-time"><?php echo $dayScheduleEntry[4];?></div>
					</div>
				</div></a>
			<?php } ?>
		</div>
	<?php }
	?>
</div>

<?php get_footer(); ?>