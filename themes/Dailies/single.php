<?php get_header(); ?>

<!-- Page-Level Anchor Ad Code -->
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
  (adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "ca-pub-9285384622456601",
    enable_page_level_ads: true
  });
</script>
<!-- End Anchor Code -->

<div class="wrapper">
<div class="contentContainer">

<?php the_post();
include(locate_template('thing.php')); ?>

<div class="adunit loop-ad pull" data-adunit="loopad-home" data-size-mapping="default-sizes"></div>

<section id="single-comments" class="single-comments">
	<?php comments_template(); ?>
</section>

</div><?php include(locate_template('sidebar.php')); ?>
</div>

<?php get_footer(); ?>