<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<input type="search" class="search-field" id="searchform" placeholder="Search…" value="<?php echo get_search_query(); ?>" name="s"/>
	</label>
	<input type="image" class="search-submit" src="<?php echo get_site_url(); ?>/wp-content/uploads/2017/07/Search.png" width="24px" height="24px"/>
</form>