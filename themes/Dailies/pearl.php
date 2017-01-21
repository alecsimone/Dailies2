<?php global $pearl;
$pearlID = $pearl->ID;
$pearlThumb = wp_get_attachment_image_src( get_post_thumbnail_id($pearlID), 'small'); ?>
<img src='<?php echo $pearlThumb; ?>'>