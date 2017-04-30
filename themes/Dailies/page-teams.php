<?php get_header(); ?>



<section id="teams">

	<div class= "league-column" id="north-america">

		<h3 class="league-header">America</h3>

		<?php foreach ($NAteams as $team) { ?>

			<a href="<?php echo $thisDomain; ?>/stars/<?php echo $team; ?>">

				<div class="team-logo">

					<img src="<?php echo $thisDomain; ?>/wp-content/uploads/teams/<?php echo $team; ?>.png" class="team-logo-img">

					<div class="team-name" id="team-<?php echo $team; ?>"><?php echo $roster['na'][$team][0]; ?></div>

				</div>

			</a>

		<?php }; ?>

	</div>

	<div class= "league-column" id="europe">

		<h3 class="league-header">Europe</h3>

		<?php foreach ($EUteams as $team) { ?>

			<a href="<?php echo $thisDomain; ?>/stars/<?php echo $team; ?>">

				<div class="team-logo">

					<img src="<?php echo $thisDomain; ?>/wp-content/uploads/teams/<?php echo $team; ?>.png" class="team-logo-img">

					<div class="team-name" id="team-<?php echo $team; ?>"><?php echo $roster['eu'][$team][0]; ?></div>

				</div>

			</a>

		<?php }; ?>

	</div>

</section>

<?php get_footer(); ?>