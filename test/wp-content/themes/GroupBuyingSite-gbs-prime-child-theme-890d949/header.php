<?php do_action( 'pre_gbs_head' ) ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
	<title>
	    <?php
		global $page, $paged;

		wp_title( '>', true, 'right' );

		// Add the blog name.
		bloginfo( 'name' );

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			echo " > $site_description";

		// Add a page number if necessary:
		if ( $paged >= 2 || $page >= 2 )
			echo ' ? ' . sprintf( gb__( 'Page %s' ), max( $paged, $page ) ); 

		?>
    </title>

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<!--[if lt IE 7]>
	<script src="//ie7-js.googlecode.com/svn/version/2.1(beta4)/IE7.js"></script>
	<![endif]-->
	<!--[if lt IE 8]>
	<script src="//ie7-js.googlecode.com/svn/version/2.1(beta4)/IE8.js"></script>
	<![endif]-->

	<!-- Facebook Conversion Code for Strung out ad -->
	<script type="text/javascript">
	var fb_param = {};
	fb_param.pixel_id = '6009510641503';
	fb_param.value = '0.01';
	fb_param.currency = 'USD';
	(function(){
	var fpw = document.createElement('script');
	fpw.async = true;
	fpw.src = '//connect.facebook.net/en_US/fp.js';
	var ref = document.getElementsByTagName('script')[0];
	ref.parentNode.insertBefore(fpw, ref);
	})();
	</script>
	<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6009510641503&amp;value=0.01&amp;currency=USD" /></noscript>

	<?php wp_head(); ?>
	<!-- Add fancyBox main JS and CSS files -->
	<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/customassets/fancybox/jquery.fancybox.js?v=2.1.5"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_directory_uri(); ?>/customassets/fancybox/jquery.fancybox.css?v=2.1.5" media="screen" />
    
</head>

<body <?php body_class(); ?>>

	<?php
		if ( is_home() || is_front_page() ) {
			get_template_part( 'inc/home-navigation', 'header' );
		} else {
			get_template_part( 'inc/navigation', 'header' );
		} ?>

	<div id="trigger_fancybox_message_banner" class="container background_alt cloak">
		<?php gb_display_messages(); ?>
	</div>

	<div id="wrapper" class="clearfix">
