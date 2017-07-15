<?php get_header(); 
$postDataRaw = get_post_meta($post->ID, 'postDataObj', true);
$voteDataArray[$post->ID] = array(
	'voteledger' => get_post_meta($post->ID, 'voteledger', true),
	'guestlist' => get_post_meta($post->ID, 'guestlist', true),
	'votecount' => get_post_meta($post->ID, 'votecount', true),
);
$postData = json_encode($postDataRaw);
$voteData = json_encode($voteDataArray);

$userID = get_current_user_id();
$userRep = get_user_meta($userID, 'rep', true);
$userRepTime = get_user_meta($userID, 'repVotes', true);

?>

<div id="dataDrop" data-user-id="<?php echo $userID; ?>" data-rep="<?php echo $userRep; ?>" data-rep-time='<?php echo json_encode($userRepTime); ?>' data-client-ip="<?php echo $_SERVER['REMOTE_ADDR']; ?>" data-postdata='<?php echo $postData; ?>' data-voteData='<?php echo $voteData; ?>'></div>
<section id="singleApp"></section>

<?php get_footer(); ?>