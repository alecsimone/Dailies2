<?php //A bar that loops through a given list of tags, displaying each as the thumbnail with the title, scrollable to the right
$excludes = [];
// if ( is_single() ) { //First we need to check if we're on the bottom of a single page. If so we'll grab the post's skills, stars, and source and use that as our $taglist
// } else { //Else we need to grab the featured $taglist from the child theme's functions.php.
	$taglist = $trendingTags;
	if ( !is_home() ) { //If we're not on the homepage, we'll randomize the order of $taglist
        	$keys = array_keys($trendingTags);

        	shuffle($keys);

        	foreach($keys as $key) {
        	    $new[$key] = $trendingTags[$key];
        	}
	
	        $taglist= $new;
  
	};
// } 
?>

<nav id="tagbar"> <!-- Then we need a container that can have a themed background color and the title -->
	<a id="lessbar" class="barButton" href="javascript:"> < </a>
	<div id="taglist"> <!-- and a smaller div that can have a black background and the actual posts in them. The smaller div starts with a button (hidden when scroll=0) that scrolls left using javascript -->
		<?php foreach ($taglist as $barTag) { //Next we'll loop through $taglist,
			$term = get_term_by('slug', $barTag['slug'], $barTag['tax']);
			$termName = $term->name;
			$barArgs = array(
				'posts_per_page' => 1,
				'post__not_in' => $excludes, //Don't want to repeat, so we're adding each post to this array
				'tax_query' => array( //We're searching for posts in custom taxonomies
					array(
						'taxonomy' => $barTag['tax'],
						'field' => 'slug',
						'terms' => $barTag['slug'],
						'include_children' => true,
					),
				),
			);
			$postDataBar = get_posts($barArgs);
			foreach ( $postDataBar as $post) : setup_postdata($post); //grabbing the featured image for the most recent post in that term
				$barID = get_the_id(); 
				$barThumbURL = wp_get_attachment_image_src( get_post_thumbnail_id($barID), 'small'); ?>
				<div id="<?php echo $barTag['slug']; ?>" class="barItem" style="background:url('<?php echo $barThumbURL[0]; ?>') center center / cover"> <!-- Then we show the featured image with a link to the term page on top of it (with a dark background) and add the tag to $excludes -->
					<a href="<?php echo $thisDomain; ?>/<?php echo $barTag['tax']; echo '/'; echo $barTag['slug']; ?>" class="barItemLink <?php echo $barTag['tax']; ?>"><?php echo $termName; ?></a>
				</div>
				<?php $excludes[] = $barID;
			endforeach;
		}; ?>
	</div>
	<a id="morebar" class="barButton" href="javascript:"> > </a><!-- Finally we add a button that scrolls the list to the right using javascript -->
</nav>