<?php

function wpgp_default_gallery_skin_enqueue() {

	if ( is_admin() ) return FALSE;

	$flex_stylesheet = plugins_url( 'lib/style.css', __FILE__ );
	$flex_script = plugins_url( 'lib/jquery.magpopup-min.js', __FILE__ );

	wp_enqueue_style( 'wpgp-default-skin', $flex_stylesheet, array(), GALLERY_PLUGIN_VERSION );

	wp_enqueue_script( 'wpgp-lightbox', $flex_script, array( 'jquery' ), GALLERY_PLUGIN_VERSION );

}



function wpgp_default_gallery_skin_theme_option( $gallery_id ) {

	$skin = wpgp_get_gallery_skin( $gallery_id );

	if ( $skin !== 'default' )
		return FALSE;
	
 
}

function wpgp_default_gallery_skin_hooks() {

	add_action( 'wp_enqueue_scripts', 'wpgp_default_gallery_skin_enqueue', 99 );

	add_action( 'wpgp_options_before_control_option', 
		'wpgp_default_gallery_skin_theme_option' );

}

?>