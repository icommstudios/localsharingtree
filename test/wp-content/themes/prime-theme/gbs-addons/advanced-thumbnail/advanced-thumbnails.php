<?php

/*
Plugin Name: Smart eCart Advanced Thumbnails
Version: 3.0
Plugin URI: http://smartecart.com/features
Description: Allows users to use TimThumb for thumbnail cropping
Author: Smart eCart
Author URI: http://smartecart.com/features
Plugin Author: Dan Cameron
Plugin Author URI: http://sproutventure.com/
*/


if ( class_exists( 'Group_Buying_Theme_UI' ) && !class_exists( 'Group_Buying_Advanced_Thumbs' ) ) {

	include 'template-tags.php';

	class Group_Buying_Advanced_Thumbs extends Group_Buying_Theme_UI {
		const ACTIVATED = 'gb_adv_thumbs';
		const QUALITY = 'gb_adv_thumbs_quality';
		const ZC = 'gb_adv_thumbs_sc';
		const ALIGN = 'gb_adv_thumbs_align';
		const SHARPEN = 'gb_adv_thumbs_sharpen';
		const COLOR = 'gb_adv_thumbs_cc';
		private static $instance;
		protected static $theme_settings_page;
		private static $version = '1.1';
		public static $documentation = 'http://groupbuyingsite.com/forum/showthread.php?2243-Advanced-Thumbnail-Setup-Instructions-(Premium-Theme-1.2)';
		private static $active;
		private static $quality;
		private static $align;
		private static $zc;
		private static $sharpen;
		private static $cc;


		public static function init() {
			self::$active = get_option( self::ACTIVATED, '0' );
			self::$quality = get_option( self::QUALITY, '89' );
			self::$align = get_option( self::ALIGN, '0' );
			self::$sharpen = get_option( self::SHARPEN, '0' );
			self::$zc = get_option( self::ZC, '2' );
			self::$cc = get_option( self::COLOR, '#0000000' );

			if ( is_admin() ) {
				add_action( 'init', array( get_class(), 'register_options') );
			}

			if ( self::$active == '1' ) {
				// reset the image_sizes
				add_image_size( 'gbs_deal_thumbnail', 656, 399, false );
				add_image_size( 'gbs_loop_thumb', 208, 120, true );
				add_image_size( 'gbs_widget_thumb', 60, 60, true );
				add_image_size( 'gbs_merchant_loop', 150, null, true );
				add_image_size( 'gbs_merchant_thumb', 255, null, false );
				add_image_size( 'gbs_voucher_thumb', 255, 220, false );
				add_image_size( 'merchant_post_thumb', 160, 100, true );
				add_image_size( 'gbs_700x400', 700, 400, false );
				add_image_size( 'gbs_300x180', 300, 180, true );
				add_image_size( 'gbs_250x110', 250, 110, true );
				add_image_size( 'gbs_150w', 150, null, true );
				add_image_size( 'gbs_100x100', 100, 100, false );
				add_image_size( 'gbs_200x150', 200, 150, false );
				add_image_size( 'gbs_160x100', 160, 100, true );
				// Do the work
				add_filter( 'image_downsize', array( get_class(), 'filter_image_downsize' ), 10, 3 );
			}


			add_action( 'load-group-buying_page_group-buying/theme_options', array( get_class(), 'options_help_section' ), 45 );
		}

		/**
		 * Hooked on init add the settings page and options.
		 *
		 */
		public static function register_options() {
			// Settings
			$settings = array(
				'gb_theme_thumbs' => array(
					'title' => self::__( 'Thumbnails' ),
					'description' => sprintf( self::__( 'Activate TimThumb for Thumbnail resizing and adjust quality, crop alignment and whether to sharpen the resized image. Documentation for the settings below and how to setup your server for caching can be found <a href="%s" target="_blank">here</a>.' ), self::$documentation ),
					'weight' => 200,
					'settings' => array(
						self::ACTIVATED => array(
							'label' => self::__( 'TimThumb Resizing' ),
							'option' => array(
								'type' => 'checkbox',
								'value' => '1',
								'default' => self::$active,
								'attributes' => array( 'class' => 'small-text' )
								)
							),
						self::QUALITY => array(
							'label' => self::__( 'Resize Quality' ),
							'option' => array(
								'type' => 'text',
								'default' => self::$quality,
								'attributes' => array( 'class' => 'small-text' )
								)
							),
						self::ZC => array(
							'label' => self::__( 'Zoom Crop' ),
							'option' => array(
								'type' => 'text',
								'default' => self::$zc,
								'attributes' => array( 'class' => 'small-text' )
								)
							),
						self::ALIGN => array(
							'label' => self::__( 'Crop Alignment' ),
							'option' => array(
								'type' => 'text',
								'default' => self::$align,
								'attributes' => array( 'class' => 'small-text' )
								)
							),
						self::SHARPEN => array(
							'label' => self::__( 'Sharpen' ),
							'option' => array(
								'type' => 'text',
								'default' => self::$sharpen,
								'attributes' => array( 'class' => 'small-text' )
								)
							),
						self::COLOR => array(
							'label' => self::__( 'Crop Background Color' ),
							'option' => array(
								'type' => 'text',
								'default' => self::$cc,
								'attributes' => array( 'class' => 'color_picker' )
								)
							)
						)
					)
				);
			do_action( 'gb_settings', $settings, Group_Buying_Theme_UI::SETTINGS_PAGE );
		}

		public static function options_help_section() {
			$screen = get_current_screen();
			$screen->add_help_tab( array(
					'id'      => 'theme-options-thumbs', // This should be unique for the screen.
					'title'   => self::__( 'Advanced Thumbnails' ),
					'content' =>
					'<p><strong>' . self::__( 'Advanced Thumbnails?' ) . '</strong></p>' .
					'<p>' . sprintf( self::__( 'The WordPress cropping functionality is rather limited, SeC includes TimThumb to allow advanced cropping and resizing. Documentation for the settings below and how to setup your server for caching can be found <a href="%s">here</a>. Warning: some hosts will not allow this functionality and some additional (unsupported) configuration may be required.' ), Group_Buying_Advanced_Thumbs::$documentation ) . '</p>'
				) );
		}

		public function filter_image_downsize( $bool, $id, $size ) {
			global $_wp_additional_image_sizes;

			if ( is_array( $_wp_additional_image_sizes ) && ( is_array( $size ) || array_key_exists( $size, $_wp_additional_image_sizes ) ) ) {

				$src = wp_get_attachment_image_src( $id, 'large' );
				$src = $src[0];

				if ( is_array( $size ) ) {
					$w = $size[0];
					$h = $size[1];
				} else {
					$w = $_wp_additional_image_sizes[$size]['width'];
					$h = $_wp_additional_image_sizes[$size]['height'];
				}

				$url = add_query_arg(
					array(
						'src' => $src,
						'w' => $w,
						'h' => $h,
						'zc' => self::$zc,
						's' => self::$sharpen,
						'a' => self::$align,
						'q' => self::$quality,
						'cc' => str_replace( '#', '', self::$cc),
					),
					SEC_Theme_Setup::addons_folder_url() . '/advanced-thumbnail/timthumb.php' );

				return array( $url, $w, $h, false );
			}
		}

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

		private function __construct() { }
	}
}

add_action( 'init', array( 'Group_Buying_Advanced_Thumbs', 'init' ), 5 );