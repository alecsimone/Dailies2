<?php /* Template Name: Schedule */ 
get_header(); 
include(locate_template('schedule.php'));
?>

<section id="schedule-container">
	<?php foreach ($shiftedSchedule as $day => $daySchedule) { ?>
		<div class="schedule-day-title"><?php echo $day; ?></div>
		<div class="schedule-day">
		<?php foreach($daySchedule as $dayScheduleEntry) { ?>
			<a href="<?php echo $thisDomain . '/source/' . $dayScheduleEntry[1]; ?>" class="schedule-entry-link"><div class="schedule-entry">
				<img class="schedule-entry-logo" src="<?php echo get_term_meta($dayScheduleEntry[2], 'logo', true); ?>">
				<div class="schedule-entry-info">
					<div class="schedule-entry-source"><?php echo $dayScheduleEntry[0]; ?></div>
					<div class="schedule-entry-title"><?php echo $dayScheduleEntry[3]; ?></div>
					<div class="schedule-entry-time"><?php echo $dayScheduleEntry[4];?></div>
					<?php $entryTwitchURL = get_term_meta($dayScheduleEntry[2], 'twitch', true);
					$lastSlashPos = strrpos($entryTwitchURL, '/');
					$entryTwitchCode = substr($entryTwitchURL, $lastSlashPos); ?>
					<a href="<?php echo $entryTwitchURL; ?>" class="schedule-entry-link" target="_blank"><div class="schedule-entry-twitch-link"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/Twitch-purple-logo.png"><?php echo $entryTwitchCode; ?></div></a>
				</div>
			</div></a>
		<?php } ?>
		</div>
	<?php } ?>
</section>

<?php get_footer(); ?>