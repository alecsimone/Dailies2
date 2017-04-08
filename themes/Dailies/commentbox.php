<section class="commentbox" id="thing<?php echo $thisID; ?>-commentbox">
	<?php $commentArgs = array(
		'number' => 3,
		'post_id' => $post->ID,
		'status' => 'approve',
		'orderby' => 'comment_karma'
		);
	$previewComments = get_comments( $commentArgs );
	$rawCommentCount = wp_count_comments($post->ID); 
	$commentCount = $rawCommentCount->approved;
	foreach( $previewComments as $comment ) {
		$commentContent = $comment->comment_content;
		$commentLength = strlen($commentContent);
		if ( $commentLength > 100 ) {
			$commentContent = substr($commentContent, 0, 100);
			$commentContent = $commentContent . "...";
		}; ?>
		<div class="comment-preview-author"><a href="<?php the_permalink(); ?>#comments"><?php echo $comment->comment_author; ?></a></div> - <?php echo $commentContent; ?> <br>
	<?php }; ?>
	<div class="comment-form-container" id="comment-form-container-<?php echo $hash; echo $thisID; ?>">
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