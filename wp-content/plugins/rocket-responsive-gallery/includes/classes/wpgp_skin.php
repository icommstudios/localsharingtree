<?php

class Wpgp_Skin {

	static $active_skin;
	static $gallery_id;
	static $gallery_shortcode_atts;
	static $gallery_settings;

	function setup_gallery( $gallery_id, $settings ) {

		self::set_active_skin( get_post_meta( $gallery_id, 'skin', true ) );

		self::$gallery_id = $gallery_id;
		
		self::$gallery_shortcode_atts = $settings;

		self::$gallery_settings = wpgp_gallery_options( $gallery_id );

	}


	function get_skin_path() {

		$skin = self::get_active_skin();

		$default_skin  = 'wpgp_skins' . DIRECTORY_SEPARATOR . 'default' .
						DIRECTORY_SEPARATOR;
		
		if ( $skin == 'default' || ! self::validate_skin() )
			return wpgp_view_path( $default_skin, false );

		return $skin;

	}

	function validate_skin( $skin = NULL ) {

		if ( ! $skin )
			$skin = self::get_active_skin();

		if ( self::is_default_skin( $skin ) )
			return TRUE;

		if ( ! file_exists( $skin . 'gallery.php' ) )
			return FALSE;

		return TRUE;

	}

	public static function get_active_skin() {

		return self::$active_skin;

	}

	function set_active_skin( $skin_path ) {

		if ( self::is_default_skin( $skin_path ) ) {

			self::$active_skin = $skin_path;

			return;

		}

		if ( mb_substr( $skin_path, -1 ) !== DIRECTORY_SEPARATOR )
			$skin_path .= DIRECTORY_SEPARATOR;

		self::$active_skin = $skin_path;

	}

	public static function render_gallery() {

		$gallery_id = self::$gallery_id;

		$slides = wpgp_get_slides( $gallery_id );
		
		$shortcode_atts = self::$gallery_shortcode_atts;
		
		$gallery_settings = self::$gallery_settings;

		/** 
			When ID attribute is missing from gallery shortcode or slider does 
			not exist use default gallery options to prevent javascript errors in some skins
		**/ 
		if ( ! $gallery_settings )
			$gallery_settings = Wpgp_Gallery_Post_Type::default_options();

		$slides = apply_filters( 'wpgp_slides', $slides, $gallery_id, 
			$shortcode_atts );

		if ( ! $slides || empty( $slides ) )
			$slides = array();

		$skins = apply_filters( 'wpgp_skins_array', array() );

		if ( isset( $shortcode_atts['skin'] ) ):

			foreach( $skins as $skin ) {

				if ( $skin['name'] == $shortcode_atts['skin'] )
					self::set_active_skin( $skin['path'] );

			}

		endif;
		
		if ( empty( $gallery_settings['width'] ) && empty( $gallery_settings['height'] ) )
			$size = 'thumbnail';
		else
			$size = array( $gallery_settings['width'], $gallery_settings['height'] );

		if ( isset( $shortcode_atts['size'] ) )
			$size = $shortcode_atts['size'];

		include self::get_skin_path() . 'gallery.php';

	}

	function is_default_skin( $skin ) {

		$default_skins = array( 'default' );

		if ( in_array( $skin , $default_skins ) )
			return TRUE;

		return FALSE;

	}

	// retrieves the attachment ID from the file URL
	function get_image_id($image_url) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM " . $prefix . "posts" . " WHERE guid='%s';", $image_url )); 
	    return $attachment[0]; 
	}

	function get_image_thumb( $image_url, $size = 'medium' ) {

		$image_id = self::get_image_id( $image_url );

		$image_thumb = wp_get_attachment_image_src( $image_id, $size );

		return $image_thumb[0];

	}
}