<!DOCTYPE html>
<head>
	<link rel='shortcut icon' href='/favicon.ico' type='image/ico'/ >
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<?php wp_head(); ?>
	<script src="https://use.typekit.net/vhv3omo.js"></script>
	<script>try{Typekit.load({ async: true });}catch(e){}</script>
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-5992564-7', 'auto');
	  ga('send', 'pageview');
	</script>
	<script src="//platform.twitter.com/widgets.js" charSet="utf-8"></script>
</head>

<body <?php body_class(); ?> >
	<a href="javascript:" id="return-to-top"><img src="<?php echo get_site_url(); ?>/wp-content/uploads/2016/11/up-arrow.png"></a>
	<nav id="menu-links">
		<a href="<?php echo get_site_url(); ?>" id="medalLink"><img src="<?php echo get_site_url(); ?>/wp-content/uploads/2016/10/Medal-small.png" class="headermedal"></a><a id="searchToggle">Search</a><div id="searchbox"><?php get_search_form(); ?></div><?php 
		$navLinks = [
			'Winners' => '/tag/winners',
			'Rules' => '/rules',
			'Schedule' => '/schedule',
			'Live' => '/live',
		];
		$baseURL = get_site_url();
		foreach ($navLinks as $name => $link) {
			echo "<a href='$baseURL$link' id='$name'>$name</a>";
		};
		$currentUser = wp_get_current_user();
		if ($currentUser->roles[0] === 'administrator' || $currentUser->roles[0] === 'editor' || $currentUser->roles[0] === 'contributor') {
			?><a href="#" id="post" class="postButton"><img src="<?php echo get_site_url(); ?>/wp-content/uploads/2017/12/green-plus.png" class="submitimg"></a><?php
		} ?>
		<a href="#" id="submit" class="submitButton">Submit</a>
		<a href="https://twitter.com/Rocket_Dailies" id="twitterLink"><img src="<?php echo get_site_url(); ?>/wp-content/uploads/2016/09/TWT.png" class="socialimg"></a>

	</nav>