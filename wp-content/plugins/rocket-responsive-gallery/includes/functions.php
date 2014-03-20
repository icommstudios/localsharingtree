<?php

function wpgp_load_plugin() {

	wpgp_load_classes();
	wpgp_load_default_gallery_skin();

}

function wpgp_load_classes() {

	wpgp_include( 'classes/wpgp_gallery_post_type.php' );
	wpgp_include( 'classes/wpgp_settings.php' );
	wpgp_include( 'classes/wpgp_skin.php' );
	wpgp_include( 'classes/wpgp_frontend_gallery.php' );

	new Wpgp_Gallery_Post_Type( true );
	new Wpgp_Settings( true );
	new Wpgp_Frontend_Gallery( true );

}

function wpgp_load_default_gallery_skin() {

	$default_skin  = 'wpgp_skins' . DIRECTORY_SEPARATOR . 'default' .
		DIRECTORY_SEPARATOR . 'functions.php';

	include wpgp_view_path( $default_skin, FALSE );

	wpgp_default_gallery_skin_hooks();

}

function wpgp_loaded() { do_action( 'ssp_loaded' ); }

function wpgp_include( $file_name, $require = true ) {

	if ( ! $require )
		require GALLERY_PLUGIN_INCLUDE_DIRECTORY . $file_name;

	include GALLERY_PLUGIN_INCLUDE_DIRECTORY . $file_name;

}

function wpgp_view_path( $view_name, $is_php = true ) {

	if ( strpos( $view_name, '.php' ) === FALSE && $is_php )
		return GALLERY_PLUGIN_VIEW_DIRECTORY . $view_name . '.php';

	return GALLERY_PLUGIN_VIEW_DIRECTORY . $view_name;

}

function wpgp_get_slides( $gallery_id ) {

	$slides = get_post_meta( $gallery_id, 'slides', true );

	return apply_filters( 'wpgp_get_gallery_slides' , $slides, $gallery_id );

}

function wpgp_gallery_options( $gallery_id ) {

	$options = get_post_meta( $gallery_id, 'options', true );

	return apply_filters( 'wpgp_get_gallery_options' , $options, $gallery_id );

}

//print out or render the gallery
function wpgp_slider( $gallery_id, $shortcode_atts = NULL ) {

	Wpgp_Skin::setup_gallery( $gallery_id, $shortcode_atts );

	Wpgp_Skin::render_gallery();

}

//get gallery active skin
function wpgp_get_gallery_skin( $gallery_id ) {

	$skin = get_post_meta( $gallery_id, 'skin', true );

	if ( ! $skin  )
		return 'default';

	return $skin;

}

function wpgp_gallery_fixes() {

	remove_filter( 'the_content', 'wpautop' );

	add_filter( 'the_content', 'wpautop', 10 );

}


?>
