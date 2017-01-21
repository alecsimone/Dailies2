<?php get_header(); ?>

<header id="nominee-header" class="section-head archive">
	<h2><?php the_search_query(); ?></h2>
</header>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
include(locate_template('thing.php')); ?>

<?php endwhile; else : ?>
	<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
<?php endif; ?>

<div id="banner-ad-bot" class="banner-ad">
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- Bottom Ad -->
		<ins class="adsbygoogle"
		     style="display:block"
		     data-ad-client="ca-pub-9285384622456601"
		     data-ad-slot="4885333173"
		     data-ad-format="auto"></ins>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
</div>

<?php get_footer(); ?>