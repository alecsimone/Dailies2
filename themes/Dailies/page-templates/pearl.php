<?php global $pearl;
global $thisDomain;
global $taxSlug;
global $slug;
$pearlID = $pearl->ID;
$pearlThumbArray = wp_get_attachment_image_src( get_post_thumbnail_id($pearlID), 'small');
$pearlThumb = $pearlThumbArray[0]; 
$pearlPerma = get_permalink($pearlID);
$pearlTitle = $pearl->post_title;
$pearlLength = strlen($pearlTitle);
$pearlTitleShort = substr($pearlTitle, 0, 72);
if ( $pearlLength > 72 ) {
	$pearlTitleShorty = $pearlTitleShort . '...';
	$pearlTitle = $pearlTitleShorty;
} 
//print_r($pearl); ?>

<div id="pearl-<?php echo $pearlID; ?>" class="pearl" data-parentslug="<?php echo $slug; ?>" data-perma="<?php echo $pearlPerma; ?>" style="background:url('<?php echo $pearlThumb; ?>') center center / cover">
	<a href="<?php echo $pearlPerma; ?>" class="pearlLink"><div class="pearlOverlay">
		<div class="pearlLinkText"><?php echo $pearlTitle; ?></div>
	</div></a>
</div>