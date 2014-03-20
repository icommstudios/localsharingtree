<?php

class Wpgp_Settings {

	function __construct( $do_start = false ) {

		if ( $do_start )
			$this->start();

	}

	function start() {

		$this->hooks();
		$this->filters();

	}



	function hooks() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}

	function filters() {



	}

	function admin_menu() {

		$page_title = __( 'Gallery Settings', GALLERY_PLUGIN_PREFIX );
		$menu_title = __( 'WP Gallery', GALLERY_PLUGIN_PREFIX );
		$capability = 'edit_posts';
		$menu_slug = 'edit.php?post_type=' . GALLERY_PLUGIN_GALLERY_POST_TYPE;

		add_object_page( $page_title, $menu_title, $capability,
			$menu_slug );

	}

}