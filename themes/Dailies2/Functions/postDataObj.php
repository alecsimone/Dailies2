<?php

function buildPostDataObject($id) {
	$postDataObject = [];
	$postDataObject['id'] = $id;
	$postDataObject['date'] = get_the_date('F jS, Y', $id);
	$postDataObject['link'] = get_permalink($id);
	$postDataObject['title'] = get_the_title($id);
	$postDataObject['thumbs'] = array(
		'small' => wp_get_attachment_image_src( get_post_thumbnail_id($id), 'small'),
		'medium' => wp_get_attachment_image_src( get_post_thumbnail_id($id), 'medium'),
		'large' => wp_get_attachment_image_src( get_post_thumbnail_id($id), 'large'),
	);
	$authorID = get_post_field('post_author', $id);
	$authorDefaultPicture = wsl_get_user_custom_avatar( $authorID );
	$authorCustomPicture = get_user_meta($authorID, 'customProfilePic', true);
	if ($authorCustomPicture === '') {
		$authorPic = $authorDefaultPicture;
	} else {
		$authorPic = $authorCustomPicture;
	}
	$postDataObject['author'] = array(
		'id' => $authorID,
		'name' => get_user_meta($authorID, 'nickname', true),
		'logo' => $authorPic, 
	);
	$postDataObject['votecount'] = get_post_meta($id, 'votecount', true);
	if ($postDataObject['votecount'] === '') {$postDataObject['votecount'] = 0;}
	$postDataObject['voteledger'] = get_post_meta($id, 'voteledger', true);
	if ($postDataObject['voteledger'] === '' || $postDataObject['voteledger'] === [] || $postDataObject['voteledger'] === null) {
		$postDataObject['voteledger'] = [];
		$postDataObject['voterData'] = [];
	}
	foreach ($postDataObject['voteledger'] as $voterID => $votedRep) {
		$voter = getPersonInDB($voterID);
		$voterName = $voter['dailiesDisplayName'];
		if ($voterName === '--') {
			$voterName = $voter['twitchName'];
		}
		$postDataObject['voterData'][$voterID] = array(
			'name' => $voterName,
			'picture' => getPicForPerson($voterID),
			'rep' => getValidRep($voterID),
		);
	}
	$postDataObject['guestlist'] = getValidGuestlist($id);
	$postDataObject['twitchVoters'] = get_post_meta($id, 'twitchVoters', true);
	if (is_array($postDataObject['twitchVoters'])) {
		foreach ($postDataObject['twitchVoters'] as $voter => $pic) {
			if ($pic === 'none' || $pic === null) {
				//if $pic is none, we want to replace it with the current twitchpic from the twitchUserDB
				$livePageObject = get_page_by_path('live');
				$liveID = $livePageObject->ID;
				$twitchUserDB = get_post_meta($liveID, 'twitchUserDB', true);
				$newPic = $twitchUserDB[$voter]['twitchPic'];
				$postDataObject['twitchVoters'][$voter] = $newPic;
			}
		}
		update_post_meta($id, 'twitchVoters', $postDataObject['twitchVoters']);
	}
	$postDataObject['EmbedCodes'] = array(
		'TwitchCode' => get_post_meta($id, 'TwitchCode', true),
		'GFYtitle' => get_post_meta($id, 'GFYtitle', true),
		'YouTubeCode' => get_post_meta($id, 'YouTubeCode', true),
		'TwitterCode' => get_post_meta($id, 'TwitterCode', true),
		'EmbedCode' => get_post_meta($id, 'EmbedCode', true),
	);
	$allCatData = get_the_category($id);
	$postDataObject['categories'] = $allCatData[0]->cat_name;
	$postDataObject['taxonomies'] = array(
		'tags' => get_the_terms($id, 'post_tag'),
		'skills' => get_the_terms($id, 'skills'),
	);
	$stars = get_the_terms($id, 'stars');
	if ($stars !== false) {
		foreach ($stars as $star) {
			$postDataObject['taxonomies']['stars'][] = array(
				'name' => $star->name,
				'logo' => get_term_meta($star->term_taxonomy_id, 'logo', true),
				'slug' => $star->slug,
			);
		}
	} else {
		$postDataObject['taxonomies']['stars'][] = array();
	}
	$source = get_the_terms($id, 'source');
	if ($source !== false) {
		foreach ($source as $singleSource) {
			$sourcepicMetaValue = get_post_meta($id, 'sourcepic', true);
			$sourceLogo = get_term_meta($singleSource->term_taxonomy_id, 'logo', true);
			if ($singleSource->slug === 'user-submits' && $sourcepicMetaValue != '') {
				$sourceLogoToUse = $sourcepicMetaValue;
			}  else {
				$sourceLogoToUse = $sourceLogo;
			}
			$postDataObject['taxonomies']['source'][] = array(
				'name' => $singleSource->name,
				'logo' => $sourceLogoToUse,
				'slug' => $singleSource->slug,
			);
		}
	} else {
		$postDataObject['taxonomies']['source'][] = array(
			'name' => 'User Submits',
			'logo' => get_term_meta(632, 'logo', true),
			'slug' => 'user-submits',
		);
	}
	$postDataObject['playCount'] =  get_post_meta($id, 'fullClipViewcount', true);
	$postDataObject['addedScore'] =  get_post_meta($id, 'addedScore', true);
	//$postDataBlob = html_entity_decode(json_encode($postDataObject, JSON_HEX_QUOT));
	//update_post_meta( $id, 'postDataObj', $postDataBlob);
	return $postDataObject;
}

?>