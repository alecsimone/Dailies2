<?php get_header(); ?>

<div class="wrapper">
<div class="contentContainer">

<?php the_post();
include(locate_template('thing.php')); ?>

<section id="single-comments" class="single-comments">
	<?php comments_template(); ?>
</section>

<?php get_footer(); ?>