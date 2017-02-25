<?php $thisID = get_the_ID(); // Get the id of the thing
	$thumbURL = wp_get_attachment_url( get_post_thumbnail_id($thisID) );
	$thumbURLSmall = wp_get_attachment_image_src( get_post_thumbnail_id($thisID), 'small'); // We'll resize the thumb image based on viewport size, so we need to have the URL for each size in a variable that function can call
	$thumbURLMedium = wp_get_attachment_image_src( get_post_thumbnail_id($thisID), 'medium');
	$thumbURLLarge = wp_get_attachment_image_src( get_post_thumbnail_id($thisID), 'large');
	$gfytitle =  get_post_meta($thisID, 'GFYtitle', true); // If the post has a custom field "GFYtitle", we're going to store that in this variable and then call it up in the function that pulls in GFYcats
	$youtubecode = get_post_meta($thisID, 'YouTubeCode', true); // If the post has data in the YouTube custom field, use it to populate the embed code in the Content Box. This one gets the embed container that responsively resizes.
	$twitchcode = get_post_meta($thisID, 'TwitchCode', true); // Twitch's player is crazy heavy, so we're going to load a thumbnail that we swap for the player when it's clicked.
	$embedcode = get_post_meta($thisID, 'EmbedCode', true); // If the post has data in the Embed code field, just use it straight up for the content box
	$attribution = get_post_meta($thisID, 'Attribution', true); // If the post has data in the Attribution field, we'll put it right below the thumb
	$fullClip = get_post_meta($thisID, 'FullClip', true); // If the post has data in the FullClip field, we'll use it in our attribution section
	$score = get_post_meta($thisID, 'votecount', true); //get the post's votecount
	if ($score == '') {$score = 0;};
	$hash = rand(1, 99); //If the same thing appears twice on one page, we need a way to identify which instance of the thing we're in. This hash will do that.
	$winnerBanner = true; 
	$client_ip = $_SERVER['REMOTE_ADDR'];
	$guestlist = get_post_meta($thisID, 'guestlist', true);
	$voteledger = get_post_meta($thisID, 'voteledger', true);
	$user_id = get_current_user_id(); // Get the user's ID
	$voteContribution = $voteledger[$user_id];
	if ($voteContribution == '' && !in_array($client_ip, $guestlist)) {
		$voteContribution = 0;
	} elseif ( $voteContribution == '' && in_array($client_ip, $guestlist) ) {
		$voteContribution = 0.1;
	};
	global $thisDomain;
?>
<article id="thing<?php echo $thisID; ?>" class="thing <?php if (!$winnerSection) { echo "pull ";}; if ( is_single() ) { echo 'singlething '; }; if ( has_tag("winners") ) { echo "winner ";}; ?>">
	<?php if ( has_tag("winners") && $winnerBanner ) { ?>
		<section id="winnerbanner<?php echo $thisID; ?>" class="winnerbanner">
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/02/Winner-banner-black.jpg" class="winnerbannerIMG">
		</section>
	<?php } else {
	}; ?>
	<header id="thing<?php echo $thisID; ?>-titlebox" class="titlebox">
		<div id="thing<?php echo $thisID; ?>-votecount" class="votecount">
			<div id="thingScore<?php echo $thisID; ?>" data-score="<?php echo $score; ?>" data-contribution="<?php echo $voteContribution; ?>">(+<?php echo $score; ?>)</div>
		</div> <h3><a href="<?php the_permalink(); ?>"><?php the_title();?></a></h3>
	</header>
	</section>
	<section id="thing<?php echo $thisID; ?>-contentbox" class="contentbox">
		<?php if ( !empty($youtubecode) ) { ?>
			<img src="<?php echo $thumbURLSmall[0]; ?>" class='youtubeimg thumb' id='<?php echo $youtubecode; echo $hash; ?>' data-medium-version="<?php echo $thumbURLMedium[0]; ?>" data-large-version="<?php echo $thumbURLLarge[0]; ?>" onclick="youtubeReplacer('<?php echo $youtubecode; echo '\', \''; echo $youtubecode; echo $hash; ?>')">
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/08/playbutton.png" class="playbutton" id="<?php echo $youtubecode; ?>playbutton">
		<?php } elseif ( !empty($twitchcode) ) { ?>
			<img src="<?php echo $thumbURLSmall[0]; ?>" class='twitchimg thumb' id='<?php echo $twitchcode; echo $hash; ?>' data-medium-version="<?php echo $thumbURLMedium[0]; ?>" data-large-version="<?php echo $thumbURLLarge[0]; ?>" onclick="twitchReplacer('<?php echo $twitchcode; echo '\', \''; echo $twitchcode; echo $hash; ?>')">
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/08/playbutton.png" class="playbutton" id="<?php echo $twitchcode; ?>playbutton">
		<?php } elseif ( !empty($embedcode) ) {
			echo $embedcode;
		} elseif ( !empty($gfytitle) ) { ?>
			<img src="<?php echo $thumbURLSmall[0]; ?>" class='gfyitem thumb' id='<?php echo $gfytitle; echo $hash; ?>' data-id='<?php echo $gfytitle; ?>' data-autoplay=true data-controls=true data-expand=true data-medium-version="<?php echo $thumbURLMedium[0]; ?>" data-large-version="<?php echo $thumbURLLarge[0]; ?>">
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/08/playbutton.png" class="playbutton" id="<?php echo $gfytitle; echo $hash; ?>playbutton">
		<?php } else { ?>
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail('large'); ?>
			</a>
		<?php } ?>
	</section>
	<section id="thing<?php echo $thisID; ?>-storybox" class="storybox">
		<p class="attribution starsource">
		<?php $stars = get_the_terms( $post->ID, 'stars' );
		if ( !empty($attribution) ) { ?>
			<?php echo $attribution; // edit_post_link( 'this', ' Edit ', '.' ); That last thing is the "edit this" link for the thing. First thing is the attribution for the post, from the custom field ?>
		<?php } elseif ( !empty($stars) ) { ?>
			Starring 
				<?php 
				$starCount = count($stars); 
				$starCounter = 0;
				if ( $starCount == 1 ) { ?><a href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[0]->slug; ?>"><?php echo $stars[0]->name; ?></a>.
				<?php } elseif ($starCount == 2 ) { ?><a href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[0]->slug; ?>"><?php echo $stars[0]->name; ?></a> and <a href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[1]->slug; ?>"><?php echo $stars[1]->name; ?></a>.
				<?php } else {
					while ($starCounter < $starCount - 1) { ?>
						<a href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[$starCounter]->slug; ?>"><?php echo $stars[$starCounter]->name; ?></a>, 
						<?php $starCounter++;
					} ?> and <a href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[$starCounter]->slug; ?>"><?php echo $stars[$starCounter]->name; ?></a>.
				<?php } ?> 
			<?php the_terms( $post->ID, 'source', 'From ', ', ' ); ?>
			<?php edit_post_link( 'this', ' Edit ' ); ?>
		</p>
		<p class="attribution full-clip">
			<?php if ( !empty($fullClip) ) { ?>
				<a href="<?php echo $fullClip; ?>" target="_blank" class="fullClipLink">Full Clip</a>
			<?php }
		} ?>
		</p>
		<?php if ( !$tournament && (!is_home() || $underdogs) ) { ?>
			<div id="thing<?php echo $thisID; ?>-datebox" class="datebox">
				<?php echo get_the_date(); ?>
			</div>
		<?php }; ?>
		<?php if ('' !== get_post()->post_content && !has_tag("recaps") || is_single() ) {
			the_content();
		} ?>
	</section>
		<?php if ($user_id == 1) {
			$gfyPlayCount = get_post_meta($thisID, 'gfyViewcount', true);
			$fullClipPlayCount = get_post_meta($thisID, 'fullClipViewcount', true);
			$totalPlays = $gfyPlayCount + $fullClipPlayCount;
			?>
			<p class="attribution playcount">
				<?php echo $totalPlays; ?> plays.
			</p>
		<?php } ?>
	<section id="thing<?php echo $thisID; //to give each thing a unique ID so they can be told apart ?>-votebar" class="votebar">
		<?php if ( ( $user_id == 0 && !in_array($client_ip, $guestlist) ) || ( $user_id != 0 && !array_key_exists($user_id, $voteledger) ) ) { ?>
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/12/Vote-Icon-100.png" id="voteIcon<?php echo $thisID; ?>" class="voteIcon" data-id="<?php echo $thisID; ?>" data-vote="up" onclick="vote(<?php echo $thisID; ?>)">
		<?php } elseif ( ( $user_id == 0 && in_array($client_ip, $guestlist) ) || ( $user_id != 0 && array_key_exists($user_id, $voteledger) ) ) { ?>
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/12/Medal-small-100.png" id="voteIcon<?php echo $thisID; ?>" class="voteIcon" data-id="<?php echo $thisID; ?>" data-vote="down" onclick="vote(<?php echo $thisID; ?>)">
		<?php }; ?>
	</section>
	<div class="onboardbox" id="thing<?php echo $thisID; ?>-onboardbox">
		<?php if ( !is_user_logged_in() ) { ?>
			<p class="onboardText">Your votes count as much as your rep. New member start at 1</p>
			<p class="onboardText">Vote daily and your Rep will grow</p>
			<?php do_action( 'wordpress_social_login' );
		}; ?>
	</div>
	<?php if ( !is_single() ) { ?>
	<section id="thing<?php echo $thisID; ?>-commentbox" class="commentbox">
		<?php $commentArgs = array(
			'number' => 3,
			'post_id' => $post->ID,
			'status' => 'approve',
			'orderby' => 'comment_karma'
		);
		$previewComments = get_comments( $commentArgs );
		if ( $previewComments ) { 
			$rawCommentCount = wp_count_comments($post->ID); 
			$commentCount = $rawCommentCount->approved; ?>
			<div id="thing<?php echo $thisID; ?>-comments-header" class="comments-header"><a href="javascript:" onclick="showCommentForm(<?php echo $thisID; echo ", "; echo $hash; echo $thisID; ?>)">
				Discussion
			</a></div>
			<?php foreach( $previewComments as $comment ) {
				$commentContent = $comment->comment_content;
				$commentLength = strlen($commentContent); // get the length of the conmment
				if ( $commentLength > 100 ) { // if it's longer than 100 characters, we're going to...
					$commentContent = substr($commentContent, 0, 100); // cut out just the first 100 characters
					$commentContent = $commentContent . "..."; // and add an elipses to the end
				}; ?>
				<div class="comment-preview-author"><a href="<?php the_permalink(); ?>#comments"><?php echo $comment->comment_author; ?></a></div> - <?php echo $commentContent; ?> <br>
			<?php };
		} else { ?>
			<div id="thing<?php echo $thisID; ?>-comments-header" class="comments-header"><a href="javascript:" onclick="showCommentForm(<?php echo $thisID; echo ", "; echo $hash; echo $thisID; ?>)">Discuss</a></div>
		<?php }; ?>
		<div id="comment-form-container-<?php echo $hash; echo $thisID; ?>" class="comment-form-container">
			<?php $commentFormArgs = array(
				'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
				'must_log_in' => '<p class="members-only">Only members can comment</p>',
				'logged_in_as' => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s" class="userlink">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), $thisDomain . "/your-votes", $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) ) ) . '</p>',
				'label_submit' => 'Post',
				'id_submit' => 'comment-submit',
				'title_reply' => '',
				'title_reply_to' => '',
				'title_reply_before' => ''
			);
			comment_form($commentFormArgs, $thisID); ?>
		</div>
	</section>
	<section id="thing<?php echo $thisID; ?>-tagbox" class="tagbox">
		<?php $tag_list = get_the_tag_list( 'More: ', ',  ', '', $thisID);
		$skill_list = get_the_terms($thisID, 'skills');
		echo $tag_list;
		foreach ($skill_list as $skill) { ?>, <a href="<?php echo $thisDomain; ?>/skills/<?php echo $skill->slug; ?>" ><?php echo $skill->name; ?></a><?php }; ?>
	</section>
	<?php }; 
	$firstPost = false; ?>
</article>