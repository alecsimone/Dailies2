<?php if ( !is_user_logged_in() ) { 
	global $thisDomain; ?>
	<div class="explainer">
		Vote Here <img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/01/drawn-arrow-small.png" class="explainer-arrow">
	</div>
<?php }; ?>