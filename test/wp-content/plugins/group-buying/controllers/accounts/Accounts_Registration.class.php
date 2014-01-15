<?php

class Group_Buying_Accounts_Registration extends Group_Buying_Controller {
	const REGISTER_PATH_OPTION = 'gb_account_register_path';
	const MINIMAL_REGISTRATION_OPTION = 'gb_minimal_registration';
	const REGISTER_QUERY_VAR = 'gb_account_register';
	const FORM_ACTION = 'gb_account_register';
	private static $register_path = 'account/register';
	private static $minimal_registration;
	private static $instance;

	public static function init() {
		self::$minimal_registration = get_option( self::MINIMAL_REGISTRATION_OPTION, 'FALSE' );
		self::$register_path = get_option( self::REGISTER_PATH_OPTION, self::$register_path );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 1 );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_registration_callback' ), 10, 1 );
	}

	/**
	 * Register the path callback
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_registration_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$register_path,
			'title' => 'Retrieve Password',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_registration_page' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$register_path ).'.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::REGISTER_QUERY_VAR, $args );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_url_paths';

		// Settings
		register_setting( $page, self::REGISTER_PATH_OPTION );
		// Fields
		add_settings_field( self::REGISTER_PATH_OPTION, self::__( 'Account Registration Path' ), array( get_class(), 'display_account_register_path' ), $page, $section );

		$section = 'gb_registration_settings';
		add_settings_section( $section, self::__( 'Registration Settings' ), array( get_class(), 'display_settings_section' ), $page );
		// Settings
		register_setting( $page, self::MINIMAL_REGISTRATION_OPTION );
		// Fields
		add_settings_field( self::MINIMAL_REGISTRATION_OPTION, self::__( 'Registration Fields' ), array( get_class(), 'display_registration_mini_option' ), $page, $section );
	}

	public static function display_registration_mini_option() {
		echo '<label><input type="radio" name="'.self::MINIMAL_REGISTRATION_OPTION.'" value="TRUE" '.checked( 'TRUE', self::$minimal_registration, FALSE ).'/> '.self::__( 'Minimal Registration with Username, E-Mail and Password.' ).'</label><br />';
		echo '<label><input type="radio" name="'.self::MINIMAL_REGISTRATION_OPTION.'" value="FALSE" '.checked( 'FALSE', self::$minimal_registration, FALSE ).'/> '.self::__( 'Full Registration with all contact fields' ).'</label><br />';
	}

	public static function display_account_register_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="' . self::REGISTER_PATH_OPTION . '" id="' . self::REGISTER_PATH_OPTION . '" value="' . esc_attr( self::$register_path ) . '" size="40"/><br />';
	}

	public static function on_registration_page() {
		// Registered users shouldn't be here. Send them elsewhere
		if ( get_current_user_id() ) {
			wp_redirect( Group_Buying_Accounts::get_url(), 303 );
			exit();
		}
		if ( !get_option( 'users_can_register' ) ) {
			wp_redirect( add_query_arg( array( 'message' => 'disabled' ), home_url() ) );
			exit();
		}
		$registration_page = self::get_instance();
		if ( isset( $_POST['gb_account_action'] ) && $_POST['gb_account_action'] == self::FORM_ACTION ) {
			$registration_page->process_form_submission();
			return;
		}
		// View template
		$registration_page->view_registration_form();
	}

	public function init_process_form_submission() {
		$registration_page = self::get_instance();
		if ( isset( $_POST['gb_account_action'] ) && $_POST['gb_account_action'] == self::FORM_ACTION ) {
			$registration_page->process_form_submission();
			return;
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


	private function __construct() {
		self::do_not_cache(); // never cache the account pages
	}

	//////////////
	// Routing //
	//////////////

	/**
	 * Update the global $pages array with the HTML for the page.
	 *
	 * @param object  $post
	 * @return void
	 */
	public function view_registration_form() {
		remove_filter( 'the_content', 'wpautop' );
		$panes = apply_filters( 'gb_account_registration_panes', $this->get_panes( array() ) );
		uasort( $panes, array( get_class(), 'sort_by_weight' ) );
		$args = array();
		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect = str_replace( home_url(), '', $_GET['redirect_to'] );
			$args['redirect'] = $redirect;
		}
		self::load_view( 'account/register', array(
				'panes' => $panes,
				'args' => $args
			) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a account page
	 */
	public static function is_registration_page() {
		return GB_Router_Utility::is_on_page( self::REGISTER_QUERY_VAR );
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
		return self::__('Account Registration');
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the merchant page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$register_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::REGISTER_QUERY_VAR );
		}
	}

	private function process_form_submission() {
		$errors = array();
		$email_address = isset( $_POST['gb_user_email'] )?$_POST['gb_user_email']:'';
		$username = isset( $_POST['gb_user_login'] )?$_POST['gb_user_login']:$email_address;
		$password = isset( $_POST['gb_user_password'] )?$_POST['gb_user_password']:'';
		$password2 = isset( $_POST['gb_user_password2'] )?$_POST['gb_user_password2']:'';
		$errors = array_merge( $errors, $this->validate_user_fields( $username, $email_address, $password, $password2 ) );
		if ( self::$minimal_registration == 'FALSE' ) {
			$errors = array_merge( $errors, $this->validate_contact_info_fields( $_POST ) );
		}
		$errors = apply_filters( 'gb_validate_account_registration', $errors, $username, $email_address, $_POST );
		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
			$sanitized_user_login = sanitize_user( $username );
			$user_email = apply_filters( 'user_registration_email', $email_address );
			$password = isset( $_POST['gb_user_password'] )?$_POST['gb_user_password']:'';
			$user_id = $this->create_user( $sanitized_user_login, $user_email, $password, $_POST );
			if ( $user_id ) {
				$user = wp_signon(
					array(
						'user_login' => $sanitized_user_login,
						'user_password' => $password,
						'remember' => false
					), false );
				do_action( 'gb_registration', $user, $sanitized_user_login, $user_email, $password, $_POST );

				if ( self::is_registration_page() ) {
					if ( isset( $_REQUEST['redirect_to'] ) && !empty( $_REQUEST['redirect_to'] ) ) {
						$redirect = str_replace( home_url(), '', $_REQUEST['redirect_to'] ); // in case the home_url is already added
						$url = home_url( $redirect );
					} else {
						$url = gb_get_last_viewed_redirect_url();
					}
					wp_redirect( apply_filters( 'gb_registration_redirect', $url ), 303 );
					exit();
				} else {
					wp_set_current_user( $user->ID );
					// Possible AJAX signon
				}
			}
		}
	}

	private function validate_user_fields( $username, $email_address, $password, $password2 ) {
		$errors = new WP_Error();
		if ( is_multisite() && GB_IS_AUTHORIZED_WPMU_SITE ) {
			$validation = wpmu_validate_user_signup( $username, $email_address );
			if ( $validation['errors']->get_error_code() ) {
				$errors = apply_filters( 'registration_errors_mu', $validation['errors'] );
			}
		} else { // Single-site install, so we don't have the wpmu functions
			// This is mostly just copied from register_new_user() in wp-login.php
			$sanitized_user_login = sanitize_user( $username );
			$user_email = apply_filters( 'user_registration_email', $email_address );

			if ( $password2 == '' )
				$password2 = $password;

			// check Password
			if ( $password == '' || $password2 == '' ) {
				$errors->add( 'empty_password', __( 'Please enter a password.' ) );
			} elseif ( $password != $password2 ) {
				$errors->add( 'password_mismatch', __( 'Passwords did not match.' ) );
			}
			// Check the username
			if ( $sanitized_user_login == '' ) {
				$errors->add( 'empty_username', __( 'Please enter a username.' ) );
			} elseif ( ! validate_username( $username ) ) {
				$errors->add( 'invalid_username', __( 'This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
				$sanitized_user_login = '';
			} elseif ( username_exists( $sanitized_user_login ) ) {
				$errors->add( 'username_exists', __( 'This username is already registered, please choose another one.' ) );
			}

			// Check the e-mail address
			if ( $user_email == '' ) {
				$errors->add( 'empty_email', __( 'Please type your e-mail address.' ) );
			} elseif ( ! is_email( $user_email ) ) {
				$errors->add( 'invalid_email', __( 'The email address isn&#8217;t correct.' ) );
				$user_email = '';
			} elseif ( email_exists( $user_email ) ) {
				$errors->add( 'email_exists', __( 'This email is already registered, please choose another one.' ) );
			}

			do_action( 'register_post', $sanitized_user_login, $user_email, $password, $password2, $errors );
			$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email, $password, $password2 );
		}
		if ( $errors->get_error_code() ) {
			return $errors->get_error_messages();
		} else {
			return array();
		}
	}

	private function validate_contact_info_fields( $submitted ) {
		$errors = array();
		$fields = $this->contact_info_fields();
		foreach ( $fields as $key => $data ) {
			if ( isset( $data['required'] ) && $data['required'] && !( isset( $submitted['gb_contact_'.$key] ) && $submitted['gb_contact_'.$key] != '' ) ) {
				$errors[] = sprintf( self::__( '"%s" field is required.' ), $data['label'] );
			}
		}
		return $errors;
	}

	public function create_user( $username, $email_address, $password = '', $submitted = array() ) {
		$password = ( $password != '' ) ? $password: wp_generate_password( 12, false );
		$username = ( !empty( $username ) ) ? $username : $email_address;
		$user_id = wp_create_user( $username, $password, $email_address );
		if ( !$user_id || is_wp_error( $user_id ) ) {
			self::set_message( self::__( 'Couldn&#8217;t register you... please contact the site administrator!' ) );
			return FALSE;
		}
		// Set contact info for the new account
		$account = Group_Buying_Account::get_instance( $user_id );

		if ( is_a( $account, 'Group_Buying_Account' ) ) {
			$first_name = isset( $submitted['gb_contact_first_name'] ) ? $submitted['gb_contact_first_name'] : '';
			$account->set_name( 'first', $first_name );
			$last_name = isset( $submitted['gb_contact_last_name'] ) ? $submitted['gb_contact_last_name'] : '';
			$account->set_name( 'last', $last_name );
			$address = array(
				'street' => isset( $submitted['gb_contact_street'] ) ? $submitted['gb_contact_street'] : '',
				'city' => isset( $submitted['gb_contact_city'] ) ? $submitted['gb_contact_city'] : '',
				'zone' => isset( $submitted['gb_contact_zone'] ) ? $submitted['gb_contact_zone'] : '',
				'postal_code' => isset( $submitted['gb_contact_postal_code'] ) ? $submitted['gb_contact_postal_code'] : '',
				'country' => isset( $submitted['gb_contact_country'] ) ? $submitted['gb_contact_country'] : '',
			);
			$account->set_address( $address );
		}

		wp_new_user_notification( $user_id );
		do_action( 'gb_account_created', $user_id, $_POST, $account );
		return $user_id;
	}

	/**
	 * For those needing to return the registration form
	 * @return [type] [description]
	 */
	public static function get_registration_form() {
		$registration = Group_Buying_Accounts_Registration::get_instance(); // make sure the class is instantiated
		$panes = apply_filters( 'gb_account_registration_panes', $registration->get_panes( array() ) );
		uasort( $panes, array( get_class(), 'sort_by_weight' ) );
		$args = array();
		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect = str_replace( home_url(), '', $_GET['redirect_to'] );
			$args['redirect'] = $redirect;
		}
		return self::load_view_to_string( 'account/register', array(
				'panes' => $panes,
				'args' => $args
			) );
	}

	/**
	 * Get the panes for the registration page
	 *
	 * @param array   $panes
	 * @return array
	 */
	public function get_panes( array $panes ) {
		$panes['user'] = array(
			'weight' => 0,
			'body' => $this->user_pane(),
		);
		if ( self::$minimal_registration == 'FALSE' ) {
			$panes['contact_info'] = array(
				'weight' => 10,
				'body' => $this->contact_info_pane(),
			);
		}
		$panes['controls'] = array(
			'weight' => 100,
			'body' => $this->load_view_to_string( 'account/register-controls', array() ),
		);
		return $panes;
	}

	private function user_pane() {
		return $this->load_view_to_string( 'account/register-user', array( 'fields' => $this->user_info_fields() ) );
	}

	private function user_info_fields() {
		$fields = array();
		$fields['login'] = array(
			'weight' => 0,
			'label' => self::__( 'Username' ),
			'type' => 'text',
			'required' => TRUE,
		);
		$fields['email'] = array(
			'weight' => 5,
			'label' => self::__( 'Email Address' ),
			'type' => 'text',
			'required' => TRUE,
		);
		$fields['password'] = array(
			'weight' => 10,
			'label' => self::__( 'Password' ),
			'type' => 'password',
			'required' => TRUE,
		);
		$fields['password2'] = array(
			'weight' => 15,
			'label' => self::__( 'Confirm Password' ),
			'type' => 'password',
			'required' => TRUE,
		);
		$fields = apply_filters( 'gb_account_register_user_fields', $fields );
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		return $fields;
	}

	private function contact_info_pane() {
		return $this->load_view_to_string( 'account/register-contact-info', array( 'fields' => $this->contact_info_fields() ) );
	}

	private function contact_info_fields() {
		$fields = $this->get_standard_address_fields();
		$fields = apply_filters( 'gb_account_register_contact_info_fields', $fields );
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		return $fields;
	}
}