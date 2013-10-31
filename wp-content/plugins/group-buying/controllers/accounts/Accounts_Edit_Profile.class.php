<?php

class Group_Buying_Accounts_Edit_Profile extends Group_Buying_Accounts {
	const EDIT_PROFILE_PATH_OPTION = 'gb_account_edit_profile_path';
	const EDIT_PROFILE_QUERY_VAR = 'gb_account_edit';
	const FORM_ACTION = 'gb_account_edit';
	private static $edit_path = 'account/edit';
	private static $instance;

	public static function init() {
		self::$edit_path = get_option( self::EDIT_PROFILE_PATH_OPTION, self::$edit_path );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 1 );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_edit_callback' ), 10, 1 );
		add_action( 'parse_request', array( get_class(), 'maybe_process_form' ) );
	}

	/**
	 * Register the path callback
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_edit_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$edit_path,
			'title' => 'Account Edit',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_edit_page' ),
			'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$edit_path ).'.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::EDIT_PROFILE_QUERY_VAR, $args );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_url_paths';

		// Settings
		register_setting( $page, self::EDIT_PROFILE_PATH_OPTION );
		add_settings_field( self::EDIT_PROFILE_PATH_OPTION, self::__( 'Account Edit Path' ), array( get_class(), 'display_account_edit_path' ), $page, $section );
	}

	public static function display_account_edit_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="' . self::EDIT_PROFILE_PATH_OPTION . '" id="' . self::EDIT_PROFILE_PATH_OPTION . '" value="' . esc_attr( self::$edit_path ) . '" size="40"/><br />';
	}

	public static function on_edit_page() {
		$edit_account_page = self::get_instance();
		// View template
		$edit_account_page->view_profile_form();
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
		self::do_not_cache(); // never cache the account pages
		add_filter( 'gb_validate_account_edit_form', array( $this, 'validate_account_fields' ), 0, 2 );
	}

	/**
	 * Check if form is submitted to another other page.
	 * @return  
	 */
	public function maybe_process_form() {
		if ( isset( $_POST['gb_account_action'] ) && $_POST['gb_account_action'] == self::FORM_ACTION ) {
			$edit_account_page = self::get_instance();
			$edit_account_page->process_form_submission();
		}
	}

	/**
	 * Update the global $pages array with the HTML for the page.
	 *
	 * @param object  $post
	 * @return void
	 */
	public function view_profile_form() {
		remove_filter( 'the_content', 'wpautop' );
		$account = Group_Buying_Account::get_instance();
		$panes = apply_filters( 'gb_account_edit_panes', $this->get_panes( array(), $account ), $account );
		uasort( $panes, array( get_class(), 'sort_by_weight' ) );
		self::load_view( 'account/edit', array(
				'panes' => $panes,
			) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a account page
	 */
	public static function is_account_edit_page() {
		return GB_Router_Utility::is_on_page( self::EDIT_PROFILE_QUERY_VAR );
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
		return sprintf( self::__( 'Editing %s&rsquo;s Profile' ), gb_get_name() );
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
			return $router->get_url( self::LOGIN_QUERY_VAR );
		}
	}

	////////////
	// Panes //
	////////////



	/**
	 * Add the default pane to the account edit form
	 *
	 * @param array   $panes
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	public function get_panes( array $panes, Group_Buying_Account $account ) {
		$panes['account'] = array(
			'weight' => 0,
			'body' => self::load_view_to_string( 'account/edit-account-info', array( 'fields' => $this->account_info_fields( $account ) ) ),
		);
		$panes['contact'] = array(
			'weight' => 1,
			'body' => self::load_view_to_string( 'account/edit-contact-info', array( 'fields' => $this->contact_info_fields( $account ) ) ),
		);
		$panes['controls'] = array(
			'weight' => 1000,
			'body' => self::load_view_to_string( 'account/edit-controls', array() ),
		);
		return $panes;
	}

	private function account_info_fields( $account = NULL ) {
		if ( !$account ) {
			$account = Group_Buying_Account::get_instance();
		}
		$user = $account->get_user();
		$fields = array(
			'email' => array(
				'weight' => 0,
				'label' => self::__( 'Email Address' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => $user->user_email,
			),
			'password' => array(
				'weight' => 10,
				'label' => self::__( 'Password' ),
				'type' => 'password',
				'required' => FALSE,
				'default' => '',
			),
			'password_confirm' => array(
				'weight' => 10.01,
				'label' => self::__( 'Confirm Password' ),
				'type' => 'password',
				'required' => FALSE,
				'default' => '',
			),
		);
		$fields = apply_filters( 'gb_account_edit_account_fields', $fields );
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		return $fields;
	}

	private function contact_info_fields( $account = NULL ) {
		$fields = $this->get_standard_address_fields( $account );
		$fields = apply_filters( 'gb_account_edit_contact_fields', $fields );
		foreach ( $fields as $key => $value ) { // Remove all the required fields since we don't validate any of it anyway.
			if ( $value['required'] ) {
				unset( $fields[$key]['required'] );
			}
		}
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		return $fields;
	}

	/////////////////////////
	// Process Submission //
	/////////////////////////

	private function process_form_submission() {
		$errors = array();
		$account = Group_Buying_Account::get_instance();
		$user = $account->get_user();

		$errors = apply_filters( 'gb_validate_account_edit_form', $errors, $account );

		$user_email = apply_filters( 'user_registration_email', $_POST['gb_account_email'] );
		if ( !$errors && ( $user->user_email != $user_email || $_POST['gb_account_password'] ) ) { // we have wordpress account info to update
			$_POST['email'] = $user_email;
			if ( $_POST['gb_account_password'] ) {
				$_POST['pass1'] = $_POST['gb_account_password'];
				$_POST['pass2'] = $_POST['gb_account_password_confirm'];
			}
			require_once ABSPATH . 'wp-admin/includes/admin.php'; // so we can have the edit_user function
			$password_errors = edit_user( $account->get_user_id() );
			if ( is_wp_error( $password_errors ) ) {
				$errors = $password_errors->get_error_messages();
			}
		}

		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		}

		$first_name = isset( $_POST['gb_contact_first_name'] ) ? $_POST['gb_contact_first_name'] : '';
		$account->set_name( 'first', $first_name );
		$last_name = isset( $_POST['gb_contact_last_name'] ) ? $_POST['gb_contact_last_name'] : '';
		$account->set_name( 'last', $last_name );
		$address = array(
			'street' => isset( $_POST['gb_contact_street'] ) ? $_POST['gb_contact_street'] : '',
			'city' => isset( $_POST['gb_contact_city'] ) ? $_POST['gb_contact_city'] : '',
			'zone' => isset( $_POST['gb_contact_zone'] ) ? $_POST['gb_contact_zone'] : '',
			'postal_code' => isset( $_POST['gb_contact_postal_code'] ) ? $_POST['gb_contact_postal_code'] : '',
			'country' => isset( $_POST['gb_contact_country'] ) ? $_POST['gb_contact_country'] : '',
		);
		$account->set_address( $address );

		do_action( 'gb_process_account_edit_form', $account );

		self::set_message( self::__( 'Account updated' ) );
		wp_redirect( Group_Buying_Accounts::get_url(), 303 );
		exit;
	}

	public function validate_account_fields( $errors, $account ) {
		$user = $account->get_user();
		$user_email = apply_filters( 'user_registration_email', $_POST['gb_account_email'] );
		// Check the e-mail address
		if ( $user_email == '' ) {
			$errors[] = 'Please type your e-mail address.';
		} elseif ( ! is_email( $user_email ) ) {
			$errors[] = 'The email address isn&#8217;t correct.';
		} elseif ( $user_email != $user->user_email && email_exists( $user_email ) ) {
			$errors[] = 'This email is already registered, please choose another one.';
		}

		if ( $_POST['gb_account_password'] && !$_POST['gb_account_password_confirm'] ) {
			$errors[] = 'Please confirm your password.';
		} elseif ( $_POST['gb_account_password'] != $_POST['gb_account_password_confirm'] ) {
			$errors[] = 'The passwords you entered to not match.';
		}
		return $errors;
	}
}