<?php $thisID = get_the_ID();
global $thisDomain; 
if (has_category('noms')) { ?>

<article id="thing<?php echo $thisID; ?>" class="thing <?php if (!$winnerSection) { echo "pull ";}; if ( is_single() ) { echo 'singlething '; }; if ( has_tag("winners") ) { echo "winner ";}; ?>">

	<?php if ( has_tag("winners") ) { ?>
		<section id="winnerbanner<?php echo $thisID; ?>" class="winnerbanner">
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/02/Winner-banner-black.jpg" class="winnerbannerIMG">
		</section>
	<?php } else {}; ?>

	<header class="titlebox" id="thing<?php echo $thisID; ?>-titlebox">
		<div id="thing<?php echo $thisID; ?>-votecount" class="votecount">
			<?php $score = get_post_meta($thisID, 'votecount', true);
			if ($score == '') {$score = 0;};
			$voteledger = get_post_meta($thisID, 'voteledger', true);
			$user_id = get_current_user_id();
			$client_ip = $_SERVER['REMOTE_ADDR'];
			$voteContribution = $voteledger[$user_id];
			$guestlist = get_post_meta($thisID, 'guestlist', true);
			if ($voteContribution == '' && !in_array($client_ip, $guestlist)) {
				$voteContribution = 0;
			} elseif ( $voteContribution == '' && in_array($client_ip, $guestlist) ) {$voteContribution = 0.1; }; ?>
			<div id="thingScore<?php echo $thisID; ?>" data-score="<?php echo $score; ?>" data-contribution="<?php echo $voteContribution; ?>">(+<?php echo $score; ?>)</div>
		</div> <h3><a href="<?php the_permalink(); ?>"><?php the_title();?></a></h3>
	</header>

	<section class="contentbox" id="thing<?php echo $thisID; ?>-contentbox">
		<?php $thumbURL = wp_get_attachment_url( get_post_thumbnail_id($thisID) );
		$thumbURLSmall = wp_get_attachment_image_src( get_post_thumbnail_id($thisID), 'small');
		$thumbURLMedium = wp_get_attachment_image_src( get_post_thumbnail_id($thisID), 'medium');
		$thumbURLLarge = wp_get_attachment_image_src( get_post_thumbnail_id($thisID), 'large');
		$gfytitle =  get_post_meta($thisID, 'GFYtitle', true);
		$youtubecode = get_post_meta($thisID, 'YouTubeCode', true);
		$twitchcode = get_post_meta($thisID, 'TwitchCode', true);
		$embedcode = get_post_meta($thisID, 'EmbedCode', true);
		$hash = rand(1, 99); ?>
		<?php if ( !empty($embedcode) ) {
			echo $embedcode;
		} elseif ( !empty($gfytitle) || !empty($twitchcode) || !empty($youtubecode) ) { ?>
			<img src="<?php echo $thumbURLSmall[0]; ?>" data-medium-version="<?php echo $thumbURLMedium[0]; ?>" data-large-version="<?php echo $thumbURLLarge[0]; ?>" <?php 
				if ( !empty($gfytitle) ) { 
					?>class='gfyitem thumb' id='<?php echo $gfytitle; echo $hash; ?>' data-id='<?php echo $gfytitle; ?>' data-autoplay=true data-controls=true data-expand=true <?php }
				elseif ( !empty($twitchcode) ) { 
					?>class='twitchimg thumb' id='<?php echo $twitchcode; echo $hash; ?>' onclick="twitchReplacer('<?php echo $twitchcode; echo '\', \''; echo $twitchcode; echo $hash; ?>')" <?php }
				elseif ( !empty($youtubecode) ) { 
					?>class='youtubeimg thumb' id='<?php echo $youtubecode; echo $hash; ?>' onclick="youtubeReplacer('<?php echo $youtubecode; echo '\', \''; echo $youtubecode; echo $hash; ?>')" <?php 
				}
			?> >
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/08/playbutton.png" class="playbutton" <?php
				if ( !empty($gfytitle) ) { 
					?>id="<?php echo $gfytitle; echo $hash; ?>playbutton"<?php }
				elseif ( !empty($twitchcode) ) {
					?>id="<?php echo $twitchcode; ?>playbutton"<?php }
				elseif ( !empty($youtubecode) ) { 
					?>id="<?php echo $youtubecode; ?>playbutton"<?php }
			?> >
		<?php } else { ?>
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail('large'); ?>
			</a>
		<?php } ?>
	</section>

	<section class="storybox" id="thing<?php echo $thisID; ?>-storybox">
		<?php if (!empty($twitchcode)) {
			$fullClip = 'https://clips.twitch.tv/' . $twitchcode;
		} else {
			$fullClip = get_post_meta($thisID, 'FullClip', true);
		}
		if ( !empty($fullClip) ) { ?>
			<p class="attribution full-clip">
				<a href="<?php echo $fullClip; ?>" target="_blank" class="fullClipLink">Full Clip</a>
			</p>
		<?php } ?>
		<p class="attribution stars">
			<?php $attribution = get_post_meta($thisID, 'Attribution', true); // For a brief period I lumped all attributions into one custom field. This is to support that
			$stars = get_the_terms( $post->ID, 'stars' );
			if ( !empty($attribution) ) {
				echo $attribution;
			} elseif ( !empty($stars) ) { 
				$starCount = count($stars); 
				$starCounter = 0;
				$defaultPic = $thisDomain . '/wp-content/uploads/2017/03/default_pic.jpg';
				while ($starCounter < $starCount) {
					$starpic = get_term_meta($stars[$starCounter]->term_taxonomy_id, 'logo', true);
					if ( empty($starpic) ) {
						$starpic = $defaultPic;
					}; ?>
					<a class="starsourceImgLink" href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[$starCounter]->slug; ?>"><img class="starpic" src="<?php echo $starpic; ?>"></a><a class="starsourceLink" href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[$starCounter]->slug; ?>"><?php echo $stars[$starCounter]->name; ?></a>
					<?php $starCounter++;
				};
			}; ?>
		</p>
		<p class="attribution source">
			<?php $source = get_the_terms( $post->ID, 'source');
			if ( !empty($source) ) {
				$sourcepic = get_term_meta($source[0]->term_taxonomy_id, 'logo', true);
				if ( empty($sourcepic) ) {
					$sourcepic = $defaultPic;
				}; ?>
				<a class="starsourceImgLink" href="<?php echo $thisDomain; ?>/source/<?php echo $source[0]->slug; ?>"><img class="starpic" src="<?php echo $sourcepic; ?>"></a><a class="starsourceLink" href="<?php echo $thisDomain; ?>/source/<?php echo $source[0]->slug; ?>"><?php echo $source[0]->name; ?></a>
			<?php } ?>
		</p>
		<?php if ( !is_home() ) { ?>
			<div id="thing<?php echo $thisID; ?>-datebox" class="datebox">
				<?php echo get_the_date(); ?>
			</div>
		<?php }; ?>
	</section>

	<?php if ($user_id == 1) {
		$gfyPlayCount = get_post_meta($thisID, 'gfyViewcount', true);
		$fullClipPlayCount = get_post_meta($thisID, 'fullClipViewcount', true);
		$totalPlays = $gfyPlayCount + $fullClipPlayCount; ?>
		<p class="attribution playcount">
			<?php echo $totalPlays; ?> plays. <?php edit_post_link('Edit this'); ?>
			<?php print_r($voteledger); print_r($guestlist); ?>
		</p>
	<?php } ?>

	<section class="votebar" id="thing<?php echo $thisID; ?>-votebar">
		<?php if ( ( $user_id == 0 && !in_array($client_ip, $guestlist) ) || ( $user_id != 0 && !array_key_exists($user_id, $voteledger) ) ) { ?>
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/Vote-Icon-line-100.png" id="voteIcon<?php echo $thisID; ?>" class="voteIcon" data-id="<?php echo $thisID; ?>" data-vote="up" onclick="vote(<?php echo $thisID; ?>)">
		<?php } elseif ( ( $user_id == 0 && in_array($client_ip, $guestlist) ) || ( $user_id != 0 && array_key_exists($user_id, $voteledger) ) ) { ?>
			<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/12/Medal-small-100.png" id="voteIcon<?php echo $thisID; ?>" class="voteIcon" data-id="<?php echo $thisID; ?>" data-vote="down" onclick="vote(<?php echo $thisID; ?>)">
		<?php }; ?>
	</section>
	<div class="onboardbox" id="thing<?php echo $thisID; ?>-onboardbox">
		<?php if ( !is_user_logged_in() ) { ?>
			<p class="onboardText">Your votes count as much as your rep. New members get 1</p>
			<p class="onboardText">Vote daily and your Rep will grow</p>
			<?php do_action( 'wordpress_social_login' );
		}; ?>
	</div>

	<?php include( locate_template('commentbox.php') ); ?>

	<section class="tagbox" id="thing<?php echo $thisID; ?>-tagbox">
		<div class="tags" id="thing<?php echo $thisID; ?>-tags">
			<?php $tag_list = get_the_tag_list( 'More: ', ',  ', '', $thisID);
			$skill_list = get_the_terms($thisID, 'skills');
			echo $tag_list;
			foreach ($skill_list as $skill) { ?>, <a href="<?php echo $thisDomain; ?>/skills/<?php echo $skill->slug; ?>" ><?php echo $skill->name; ?></a><?php }; ?>
		</div>
		<div class="discuss-button-container"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/comment-icon-outline.png" class="discuss-button" onclick="showCommentForm(<?php echo $thisID; echo ", "; echo $hash; echo $thisID; ?>)"></div>
	</section>
</article>

<?php } else { ?>
<?php $source = get_the_terms( $post->ID, 'source'); 
$sourceSlug = $source[0]->slug; ?>
<article id="little-thing-<?php echo $thisID; ?>" class="thing little-thing <?php echo $sourceSlug; ?>">
	<section class="little-thing-top" id="ltt-<?php echo $thisID; ?>">
		<?php if ( !empty($source) ) { ?>
			<p class="attribution source">
				<?php $sourcepic = get_term_meta($source[0]->term_taxonomy_id, 'logo', true);
				if ( empty($sourcepic) ) {
					$sourcepic = $defaultPic;
				}; ?>
				<a class="starsourceImgLink" href="<?php echo $thisDomain; ?>/source/<?php echo $source[0]->slug; ?>"><img class="starpic" src="<?php echo $sourcepic; ?>"></a>
			</p>
		<?php }; ?>
		<div class="little-title titlebox">
			<?php $gfytitle =  get_post_meta($thisID, 'GFYtitle', true);
			$youtubecode = get_post_meta($thisID, 'YouTubeCode', true);
			$twitchcode = get_post_meta($thisID, 'TwitchCode', true);
			$embedcode = get_post_meta($thisID, 'EmbedCode', true);
			$hash = rand(1, 99); ?>
			<h3><a href="<?php
				if ( !empty($gfytitle) ) {
					echo "http://gfycat.com/";
					echo $gfytitle;
				} elseif ( !empty($twitchcode) ) {
					echo "http://clips.twitch.tv/";
					echo $twitchcode;
				} elseif ( !empty($youtubecode) ) {
					echo "https://youtube.com/watch?v=";
					echo $youtubecode;
				}
			 ?>" target="_blank"<?php 
				if ( !empty($gfytitle) ) {
					?>class='gfy-little-thing little-title-link' id='<?php echo $gfytitle; echo $hash; ?>' onclick="littleReplacer('<?php echo $gfytitle; echo '\', \''; echo $gfytitle; echo $hash; ?>')" data-id='<?php echo $gfytitle; ?>' data-autoplay=true data-controls=true data-expand=true <?php }
				elseif ( !empty($twitchcode) ) { 
						?>class='twitch-little-thing little-title-link' id='<?php echo $twitchcode; echo $hash; ?>' data-id="<?php echo $twitchcode; ?>" onclick="littleReplacer('<?php echo $twitchcode; echo '\', \''; echo $twitchcode; echo $hash; ?>')" <?php }
				elseif ( !empty($youtubecode) ) { 
						?>class='yt-little-thing little-title-link' id='<?php echo $youtubecode; echo $hash; ?>' data-id="<?php echo $youtubecode; ?>" onclick="littleReplacer('<?php echo $youtubecode; echo '\', \''; echo $youtubecode; echo $hash; ?>')" <?php 
					}
				?>><?php the_title();?></a></h3> <div id="little-thing<?php echo $thisID; ?>-votecount" class="votecount little-thing-votecount">
			<?php $score = get_post_meta($thisID, 'votecount', true);
			if ($score == '') {$score = 0;};
			$voteledger = get_post_meta($thisID, 'voteledger', true);
			$user_id = get_current_user_id();
			$client_ip = $_SERVER['REMOTE_ADDR'];
			if ($voteledger == '') {
				$voteContribution = 0;
				$onLedger = false;
			} else {
				$voteContribution = $voteledger[$user_id];
				$onLedger = array_key_exists($user_id, $voteledger);
			}
			$guestlist = get_post_meta($thisID, 'guestlist', true);
			if ($guestlist == '') {
				$onGuestlist = false;
			} else {
				$onGuestlist = in_array($client_ip, $guestlist);
			}
			if ($voteContribution == '' && !$onGuestlist) {
				$voteContribution = 0;
			} elseif ( $voteContribution == '' && $onGuestlist ) {$voteContribution = 0.1; }; ?>
			<div id="thingScore<?php echo $thisID; ?>" data-score="<?php echo $score; ?>" data-contribution="<?php echo $voteContribution; ?>">(+<?php echo $score; ?>)</div>
		</div>
		</div><div class="little-votebox">
			<?php $voteledger = get_post_meta($thisID, 'voteledger', true);
			$guestlist = get_post_meta($thisID, 'guestlist', true);
			$user_id = get_current_user_id();
			$client_ip = $_SERVER['REMOTE_ADDR'];
			if ( ( $user_id == 0 && !$onGuestlist ) || ( $user_id != 0 && !$onLedger ) ) { ?>
				<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/Vote-Icon-line-100.png" id="voteIcon<?php echo $thisID; ?>" class="voteIcon" data-id="<?php echo $thisID; ?>" data-vote="up" onclick="vote(<?php echo $thisID; ?>)">
			<?php } elseif ( ( $user_id == 0 && in_array($client_ip, $guestlist) ) || ( $user_id != 0 && array_key_exists($user_id, $voteledger) ) ) { ?>
				<img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/12/Medal-small-100.png" id="voteIcon<?php echo $thisID; ?>" class="voteIcon" data-id="<?php echo $thisID; ?>" data-vote="down" onclick="vote(<?php echo $thisID; ?>)">
			<?php }; ?>
		</div>
	</section>
	<section class="little-thing-embed" id="lte-<?php echo $thisID; ?>">
	</section>
	<?php $stars = get_the_terms( $post->ID, 'stars' );
	$defaultPic = $thisDomain . '/wp-content/uploads/2017/03/default_pic.jpg'; ?>
	<section class="little-thing-attribution" id="lta-<?php echo $thisID; ?>">
		<?php if ( !empty($stars) ) {
			$starCount = count($stars); 
			$starCounter = 0; ?>
			<p class="attribution stars">
				<?php while ($starCounter < $starCount) {
					$starpic = get_term_meta($stars[$starCounter]->term_taxonomy_id, 'logo', true);
					if ( empty($starpic) ) {
						$starpic = $defaultPic;
					}; ?>
					<a class="starsourceImgLink" href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[$starCounter]->slug; ?>"><img class="starpic" src="<?php echo $starpic; ?>"></a><a class="starsourceLink" href="<?php echo $thisDomain; ?>/stars/<?php echo $stars[$starCounter]->slug; ?>"><?php echo $stars[$starCounter]->name; ?></a>
					<?php $starCounter++;
				};
				edit_post_link('Edit this'); ?>
			</p>
		<?php } else { ?>
			<p class="attribution stars">
				<img class="starpic nostars" src="<?php echo $defaultPic; ?>">
				<?php edit_post_link('Edit this'); ?>
			</p>
		<?php }; ?>
		<div class="little-discuss-button">
			<?php if ( current_user_can('delete_published_posts') ) { ?><div class="post-trasher" onclick="postTrasher(<?php echo $thisID; ?>)"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/red-x.png"></div><?php }; ?><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2017/04/comment-icon-outline.png" onclick="showCommentForm(<?php echo $thisID; echo ", "; echo $hash; echo $thisID; ?>)">
		</div>
	</section>
	<?php include(locate_template('commentbox.php')); ?>
</article>

<?php } ?>
