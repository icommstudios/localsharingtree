<?php

if ( is_admin() )
{

	add_action('admin_menu', 'fsp_menu');
	

}

function fsp_menu()
{
	add_menu_page('Full Screen Popup Settings', 'XYZ Full Screen Popup', 'manage_options', 'full-screen-popup-settings', 'fsp_settings');
	

	// Add a submenu to the Dashboard:
	$page=add_submenu_page('full-screen-popup-settings', 'Full Screen Popup Settings', 'Full Screen Popup', 'manage_options', 'full-screen-popup-settings' ,'fsp_settings'); // 8 for admin
	add_submenu_page('full-screen-popup-settings','Fulls Sreen Popup - Basic Settings', 'Basic Settings', 'manage_options', 'full-screen-popup-basic-settings', 'fsp_basic_settings');
	add_submenu_page('full-screen-popup-settings', 'Full Screen Popup- About', 'About', 'manage_options', 'full-screen-popup-about' ,'fsp_about'); // 8 for admin

	
	
	add_action( "admin_print_scripts-$page", 'fsp_farbtastic_script' );
	add_action( "admin_print_styles-$page", 'fsp_farbtastic_style' );
}



function fsp_basic_settings()
{
	require( dirname( __FILE__ ) . '/header.php' );
	require( dirname( __FILE__ ) . '/settings.php' );
	require( dirname( __FILE__ ) . '/footer.php' );
}

function fsp_settings()
{
	
	require( dirname( __FILE__ ) . '/header.php' );
	require( dirname( __FILE__ ) . '/fullscreen-settings.php' );
	require( dirname( __FILE__ ) . '/footer.php' );
}


function fsp_about()
{
	require( dirname( __FILE__ ) . '/header.php' );
	require( dirname( __FILE__ ) . '/about.php' );
	require( dirname( __FILE__ ) . '/footer.php' );
}





function fsp_farbtastic_script() 
{
	wp_enqueue_script('jquery');
	wp_enqueue_script('farbtastic');

}

function fsp_farbtastic_style() 
{
	wp_enqueue_style('farbtastic');
}
function xyz_fsp_admin_style()
{
	require( dirname( __FILE__ ) . '/style.php' );

}
	wp_enqueue_script('jquery');
add_action('admin_print_styles', 'xyz_fsp_admin_style');

?>