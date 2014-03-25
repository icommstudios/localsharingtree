<?php

/*
Plugin Name: Group Buying Advanced Thumbnails
Version: 3.0
Plugin URI: http://groupbuyingsite.com/features
Description: Allows users to use TimThumb for thumbnail cropping
Author: GroupBuyingSite.com
Author URI: http://groupbuyingsite.com/features
Plugin Author: Dan Cameron
Plugin Author URI: http://sproutventure.com/
*/


if ( class_exists( 'Group_Buying_Theme_UI' ) ) {

	include 'template-tags.php';

	class Group_Buying_Sharing extends Group_Buying_Theme_UI {
		const SHARETHISAPI = 'gb_sharethis_api';
		private static $instance;
		protected static $theme_settings_page;
		private static $sharethisapi;

		public static function init() {
			self::$sharethisapi = get_option( self::SHARETHISAPI, 'dfb4a8c1-a6bb-4bdc-ac31-445ce0d0208c' );
			
			if ( is_admin() ) {
				add_action( 'init', array( get_class(), 'register_options') );
			}
			
			self::register_scripts();
			add_action( 'wp_print_scripts', array( get_class(), 'enqueue_scripts' ) );
			add_action( 'wp_footer', array( get_class(), 'wp_footer' ), 10, 0 );
		}

		/**
		 * Hooked on init add the settings page and options.
		 *
		 */
		public static function register_options() {
			// Settings
			$settings = array(
				'gb_theme_thumbs' => array(
					'title' => self::__( 'Sharing Settings' ),
					'weight' => 200,
					'settings' => array(
						self::SHARETHISAPI => array(
							'label' => self::__( 'ShareThis API' ),
							'option' => array(
								'type' => 'text',
								'default' => self::$sharethisapi,
								'description' => sprintf( self::__( 'This theme uses the <a href="%s" target="_blank">ShareThis WebShare API</a>.' ), 'http://developer.sharethis.com/Web_Share_API' )
								)
							)
						)
					)
				);
			do_action( 'gb_settings', $settings, Group_Buying_Theme_UI::SETTINGS_PAGE );
		}

		public static function register_scripts() {
			if ( !is_admin() ) {
				wp_register_script( 'sharethis', 'https://ws.sharethis.com/button/buttons.js' );
			}
		}

		public static function enqueue_scripts() {
			if ( !is_admin() ) {
				wp_enqueue_script( 'sharethis' );
			}
		}
		

		public static function wp_footer() {
			if ( !is_admin() ) {
				?>
				<script type="text/javascript">stLight.options({publisher: "<?php echo self::$sharethisapi ?>"}); </script>
				<?php
			}
		}

		private function __construct() {}

		/*
		 * Singleton Design Pattern
		 * ------------------------------------------------------------- */
		private function __clone() {
			// cannot be cloned
			trigger_error( __CLASS__.' may not be cloned', E_USER_ERROR );
		}
		private function __sleep() {
			// cannot be serialized
			trigger_error( __CLASS__.' may not be serialized', E_USER_ERROR );
		}
		public static function get_instance() {
			if ( !( self::$instance && is_a( self::$instance, __CLASS__ ) ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

	}
}
add_action( 'init', array( 'Group_Buying_Sharing', 'init' ), 5  );
