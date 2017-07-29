<?php /* Template Name: Schedule */ 
get_header(); 
include(locate_template('schedule.php'));
?>

<section id="schedule-container">
	<?php $scheduleCounter = 0;
	date_default_timezone_set('America/Chicago');
	$firstDayString = $latestNomDate . '-' . $latestNomMonth . '-' . $latestNomYear;
	$date = new DateTime($firstDayString);
	foreach ($shiftedSchedule as $day => $daySchedule) { ?>
		<div class="schedule-day-title"><div id="scheduleFor"><?php echo $date->format('l') === 'Saturday' ? 'SCHEDULE FOR THE' : 'SCHEDULE FOR' ?></div><?php echo $date->format('l') === 'Saturday' ? 'WEEKEND OF' : strtoupper($date->format('l')) . ','; ?> <?php echo strtoupper($date->format('F')); ?> <?php echo strtoupper($date->format('j')); ?><span id="suffix"><?php echo strtoupper($date->format('S'));?></span></div>
		<?php $date->add(DateInterval::createFromDateString('tomorrow')); 
		if ($date->format('l') === 'Sunday') {
			$date->add(DateInterval::createFromDateString('tomorrow'));
		} ?>
		<div class="schedule-day">
		<?php foreach($daySchedule as $dayScheduleEntry) { ?>
			<div class="schedule-entry">
				<a href="<?php echo $thisDomain . '/source/' . $dayScheduleEntry[1]; ?>" class="schedule-entry-link"><img class="schedule-entry-logo" src="<?php echo get_term_meta($dayScheduleEntry[2], 'logo', true); ?>"></a>
				<div class="schedule-entry-info">
					<a href="<?php echo $thisDomain . '/source/' . $dayScheduleEntry[1]; ?>" class="schedule-entry-link"><div class="schedule-entry-source"><?php echo strtoupper($dayScheduleEntry[0]); ?></div></a>
					<?php $entryTwitchURL = get_term_meta($dayScheduleEntry[2], 'twitch', true);
					$lastSlashPos = strrpos($entryTwitchURL, '/');
					$entryTwitchCode = substr($entryTwitchURL, $lastSlashPos); ?>
					<a href="<?php echo $entryTwitchURL; ?>" class="schedule-entry-link twitch" target="_blank"><div class="schedule-entry-twitch-link"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/Twitch-purple-logo.png"><?php echo $entryTwitchCode; ?></div></a>
					<div class="schedule-entry-time"><?php echo $dayScheduleEntry[4];?></div> <div class="schedule-entry-title"><?php echo $dayScheduleEntry[3]; ?></div>
				</div>
			</div>
		<?php } ?>
		</div>
	<?php } ?>
</section>

<?php get_footer(); ?>