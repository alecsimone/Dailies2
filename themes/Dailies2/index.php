<?php get_header(); 
/*$bigQueryArgs = array(
	'posts_per_page' => 200,
);
$bigQueryPosts = get_posts($bigQueryArgs);
foreach ($bigQueryPosts as $post) : setup_postdata($post);
	buildPostDataObject($post->ID);
endforeach;*/

?>
<div id="wp-social-login" class="hidden"><?php do_action('wordpress_social_login'); ?></div>

<section id="homepageApp">
</section>

<?php get_footer(); ?>