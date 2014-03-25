<?php

class Group_Buying_Merchants_Edit extends Group_Buying_Merchants {
	const EDIT_PATH_OPTION = 'gb_merchant_edit_path';
	const EDIT_QUERY_VAR = 'gb_merchant_edit';
	const FORM_ACTION = 'gb_merchant_edit';
	private static $edit_path = 'merchant/edit';
	private static $instance;

	public static function init() {
		self::$edit_path = get_option( self::EDIT_PATH_OPTION, self::$edit_path );
		self::register_settings();

		add_action( 'gb_router_generate_routes', array( get_class(), 'register_edit_callback' ), 10, 1 );
		add_action( 'parse_request', array( get_class(), 'maybe_process_form' ) );
	}
	
	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		// Settings
		$settings = array(
			'gb_url_path_merchant_edit' => array(
				'weight' => 132,
				'settings' => array(
					self::EDIT_PATH_OPTION => array(
						'label' => self::__( 'Merchant Edit Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ),
							'type' => 'text',
							'default' => self::$edit_path
							)
						)
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_UI::SETTINGS_PAGE );
	}

	/**
	 * Register the path callback for the merchant registration
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_edit_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$edit_path,
			'title' => 'Merchant Edit',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_edit_page' ),
			'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$edit_path ).'.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::EDIT_QUERY_VAR, $args );
	}

	public static function on_edit_page() {
		$merchant_edit_page = self::get_instance();
		// View template
		$merchant_edit_page->view_edit_form();
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

	private function __construct() {
		self::do_not_cache();
	}

	/**
	 * Check if form is submitted to another other page.
	 * @return  
	 */
	public function maybe_process_form() {
		if ( isset( $_POST['gb_merchant_action'] ) && $_POST['gb_merchant_action'] == self::FORM_ACTION ) {
			$merchant_edit = self::get_instance();
			$merchant_edit->process_form_submission();
		}
	}

	public function view_edit_form() {
		remove_filter( 'the_content', 'wpautop' );
		$merchant_id = Group_Buying_Merchant::get_merchant_id_for_user();
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		self::load_view( 'merchant/edit', array( 'fields' => $this->merchant_contact_info_fields( $merchant ) ) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a cart page
	 */
	public static function is_merchant_edit_page() {
		return GB_Router_Utility::is_on_page( self::EDIT_QUERY_VAR );
	}

	/**
	 * Filter 'the_title' to display the title.
	 *
	 * @static
	 * @param string  $title
	 * @param int     $post_id
	 * @return string
	 */
	public function get_title( $title ) {
		return self::__( "Edit Merchant" );
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the merchant page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$edit_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::EDIT_QUERY_VAR );
		}
	}

	private function process_form_submission() {
		$errors = array();
		$title = isset( $_POST['gb_contact_merchant_title'] ) ? esc_html( $_POST['gb_contact_merchant_title'] ) : '';
		$allowed_tags = wp_kses_allowed_html( 'post' );
		$allowed_tags['iframe'] = array(
			'width' => true,
			'height' => true,
			'src' => true,
			'frameborder' => true,
			'webkitAllowFullScreen' => true,
			'mozallowfullscreen' => true,
			'allowfullscreen' => true
		);
		$content = isset( $_POST['gb_contact_merchant_description'] ) ? wp_kses( $_POST['gb_contact_merchant_description'], $allowed_tags ) : '';
		$contact_title = isset( $_POST['gb_contact_title'] ) ? esc_html( $_POST['gb_contact_title'] ) : '';
		$contact_name = isset( $_POST['gb_contact_name'] ) ? esc_html( $_POST['gb_contact_name'] ) : '';
		$contact_street = isset( $_POST['gb_contact_street'] ) ? esc_html( $_POST['gb_contact_street'] ) : '';
		$contact_city = isset( $_POST['gb_contact_city'] ) ? esc_html( $_POST['gb_contact_city'] ) : '';
		$contact_state = isset( $_POST['gb_contact_zone'] ) ? esc_html( $_POST['gb_contact_zone'] ) : '';
		$contact_postal_code = isset( $_POST['gb_contact_postal_code'] ) ? $_POST['gb_contact_postal_code'] : '';
		$contact_country = isset( $_POST['gb_contact_country'] ) ? esc_html( $_POST['gb_contact_country'] ) : '';
		$contact_phone = isset( $_POST['gb_contact_phone'] ) ? esc_html( $_POST['gb_contact_phone'] ) : '';
		$website = isset( $_POST['gb_contact_website'] ) ? esc_url( $_POST['gb_contact_website'] ) : '';
		$facebook = isset( $_POST['gb_contact_facebook'] ) ? esc_url( $_POST['gb_contact_facebook'] ) : '';
		$twitter = isset( $_POST['gb_contact_twitter'] ) ? esc_url( $_POST['gb_contact_twitter'] ) : '';
		$errors = array_merge( $errors, $this->validate_merchant_contact_info_fields( $_POST ) );
		$errors = apply_filters( 'gb_validate_merchant_registration', $errors, $_POST );
		if ( !empty( $errors ) ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
			$post_id = Group_Buying_Merchant::get_merchant_id_for_user();
			$merchant = Group_Buying_Merchant::get_instance( $post_id );
			wp_update_post( array(
					'ID' => $post_id,
					'post_title' => $title,
					'post_content' => $content
				) );
			$merchant->set_contact_title( $contact_title );
			$merchant->set_contact_name( $contact_name );
			$merchant->set_contact_street( $contact_street );
			$merchant->set_contact_city( $contact_city );
			$merchant->set_contact_state( $contact_state );
			$merchant->set_contact_postal_code( $contact_postal_code );
			$merchant->set_contact_country( $contact_country );
			$merchant->set_contact_phone( $contact_phone );
			$merchant->set_website( $website );
			$merchant->set_facebook( $facebook );
			$merchant->set_twitter( $twitter );
			$merchant->authorize_user( get_current_user_id() );

			if ( !empty( $_FILES['gb_contact_merchant_thumbnail'] ) ) {
				// Set the uploaded field as an attachment
				$merchant->set_attachement( $_FILES, 'gb_contact_merchant_thumbnail' );
			}

			do_action( 'edit_merchant', $merchant );

			$url = Group_Buying_Merchants::get_url();
			$url = add_query_arg( 'message', 'updated', $url );
			self::set_message( self::__( 'Merchant Updated.' ), self::MESSAGE_STATUS_INFO );
			wp_redirect( $url, 303 );
			exit();
		}
	}
}