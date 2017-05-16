<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<?php 
	$thisDailyVars = this_dailies_vars(); // header.php doesn't seem to be able to use variables defined normally in the child theme's functions.php, so instead we have to define them inside a function which we call here. This is very inelegant, and if you know an elegant way to do it instead, please tell me.
	$thisNavLinks = $thisDailyVars['nav-links'];
	global $thisDomain;
	$thisDomain = get_site_url();
?>
<head>
	<link rel='shortcut icon' href='/favicon.ico' type='image/ico'/ >
	<!-- Get charset -->
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<!-- Set Viewport Width -->
	<meta name="viewport" content="width=device-width">
	<?php wp_head(); ?>
	<!-- jQUERY FOR LOCAL HOST, DELETE FOR PRODUCTION -->
	<script type="text/javascript" src="<?php echo $thisDomain; ?>/wp-includes/js/jquery/jquery.js?ver=1.12.4"></script>
	<script type="text/javascript" src="<?php echo $thisDomain; ?>/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1"></script>
	<!-- jQUERY FOR LOCAL HOST, DELETE FOR PRODUCTION -->
	<!-- Get Some Fonts -->
	<script src="https://use.typekit.net/vhv3omo.js"></script>
	<script>try{Typekit.load({ async: true });}catch(e){}</script>
	<!-- Google Analytics -->
	<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-5992564-7', 'auto');
  ga('send', 'pageview');

	</script>
	<script type='text/javascript'>
	window.__lo_site_id = 72686;

	(function() {
		var wa = document.createElement('script'); wa.type = 'text/javascript'; wa.async = true;
		wa.src = 'https://d10lpsik1i8c69.cloudfront.net/w.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(wa, s);
	  })();
	</script>
	<!-- Enable javascript gfycatting -->
	<script async type="text/javascript" src="<?php echo $thisDomain; ?>/wp-content/themes/Dailies/js/onClickGFYfy.js"></script>
	<!-- Enable jQuery Kinetic -->
	<script async type="text/javascript" src="<?php echo $thisDomain; ?>/wp-content/themes/Dailies/js/jquery.kinetic.min.js"></script>
	<!-- Isotope -->
	<script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>
</head>

<!-- Show me that body -->
<body <?php body_class(); ?> >
<!-- Page Header. Logo that links to homepage, title, tagline, and social links -->
<div id="menu-links-wrapper">
	<nav id="menu-links">
		<?php navLinks($thisNavLinks); ?>
	</nav>
</div>
<a href="javascript:" id="return-to-top"><img src="<?php echo $thisDomain; ?>/wp-content/uploads/2016/11/up-arrow.png"></a>