<?php /* Template Name: Secret Garden */ 
get_header(); 
$userRep = get_user_meta(get_current_user_id(), 'rep', true);
if ($userRep < 5) {
	echo "There's nothing here. How did you get here? Turn back now. Maybe try coming back when you have more rep. But there's definitely nothing here.";
} else { 

$currentTime = time();
	?>
	<div id="dataDrop" data-sluglist='<?php echo json_encode($currentTime); ?>'></div>
	<section id="secretGardenApp"></section>

<?php }
get_footer(); ?>