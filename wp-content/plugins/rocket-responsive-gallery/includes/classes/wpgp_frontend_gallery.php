<?php

class Wpgp_Frontend_Gallery{

	function __construct( $do_start = false ) {

		if ( $do_start )
			$this->start();

	}

	function start() {

		if ( is_admin() ) return;

		$this->hooks();
		$this->filters();
		$this->shortcodes();

	}

	function hooks() {

		add_action( 'init', array( $this, 'enqueue_scripts' ) );

	}

	function filters() {



	}

	function enqueue_scripts() {

		wp_enqueue_script( 'jquery' );

	}

	function shortcodes() {

		add_shortcode( GALLERY_PLUGIN_GALLERY_SHORTCODE, 
					array( $this, 'shortcode_gallery' ) );

	}

	function shortcode_gallery( $atts, $content = null ) {
		
		global $post;

		extract( shortcode_atts( array(
			'id' => NULL,
			'slides_src' => NULL
		), $atts ));


		if ( ( $id === NULL || is_int( $id ) ) && ! $slides_src  ) {
			_e( 'Error: The shortcode attribute "id" is not a integer or is missing.', 'wpgp' );
			return FALSE;
		}

		if ( get_post_status( $id ) == 'trash' && ! $slides_src ) {
			_e( 'Error: Gallery is in trash.', 'wpgp' );
			return FALSE;
		}

		if ( get_post_status( $id ) !== 'publish' && ! $slides_src )
			return FALSE;

		$atts['slides_src'] = $slides_src;

		if ( $post )
			$atts['post_id'] = $post->ID;
		else
			$atts['post_id'] = NULL;

		$atts = apply_filters( 'wpgp_shortcode_atts', $atts );

		ob_start();

		Wpgp_Skin::setup_gallery( $id, $atts );
		Wpgp_Skin::render_gallery();

		return ob_get_clean();
		
	}

}