<?php

class Group_Buying_Accounts_Retrieve_Password extends Group_Buying_Accounts {
	const RP_PATH_OPTION = 'gb_account_rp_path';
	const RP_QUERY_VAR = 'gb_account_rp';
	private static $rp_path = 'account/retrievepassword';
	private static $instance;

	public static function init() {
		self::$rp_path = get_option( self::RP_PATH_OPTION, self::$rp_path );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 1 );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_rp_callback' ), 10, 1 );

		// Replace WP Login URIs
		add_filter( 'lostpassword_url', array( get_class(), 'get_url' ), 10, 2 );
	}

	/**
	 * Register the path callback
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_rp_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$rp_path,
			'title' => 'Retrieve Password',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_rp_page' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$rp_path ).'.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::RP_QUERY_VAR, $args );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_url_paths';

		// Settings
		register_setting( $page, self::RP_PATH_OPTION );
		add_settings_field( self::RP_PATH_OPTION, self::__( 'Account Retrieve Password Path' ), array( get_class(), 'display_account_login_path' ), $page, $section );
	}

	public static function display_account_login_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="' . self::RP_PATH_OPTION . '" id="' . self::RP_PATH_OPTION . '" value="' . esc_attr( self::$rp_path ) . '" size="40"/><br />';
	}


	public static function on_rp_page() {

		// Registered users shouldn't be here. Send them elsewhere
		if ( get_current_user_id() && !Group_Buying_Accounts_Login::log_out_attempt() ) {
			wp_redirect( Group_Buying_Accounts::get_url(), 303 );
			exit();
		}

		// Reset Attempt
		if ( isset( $_GET['key'] ) ) {
			if ( self::reset_password( $_GET['key'] ) ) {
				wp_redirect( add_query_arg( array( 'message' => 'newpass' ), Group_Buying_Accounts_Edit_Profile::get_url() ) );
				exit();
			}
			// invalid password reset key
			wp_redirect( add_query_arg( array( 'message' => 'invalidkey' ), self::get_url() ) );
			exit();
		}
		// Reset Attempt via Form
		elseif ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$message = self::retrieve_password();
			if ( $message == 'confirm' ) {
				wp_redirect( add_query_arg( array( 'message' => $message ), Group_Buying_Accounts_Login::get_url() ) );
			} else {
				wp_redirect( add_query_arg( array( 'message' => $message ), self::get_url() ) );
			}
			exit();
		}

		$rp_login_page = self::get_instance();
		// View template
		$rp_login_page->view_rp_form();
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


	/**
	 * Update the global $pages array with the HTML for the page.
	 *
	 * @param object  $post
	 * @return void
	 */
	public function view_rp_form() {
		remove_filter( 'the_content', 'wpautop' );
		$args = array();
		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect = str_replace( home_url(), '', $_GET['redirect_to'] );
			$args['redirect'] = $redirect;
		}
		self::load_view( 'account/retrievepassword', array( 'args' => $args ) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a account page
	 */
	public static function is_rp_page() {
		return GB_Router_Utility::is_on_page( self::RP_QUERY_VAR );
	}

	/**
	 * Filter 'the_title' to display the title of the page rather than the user name
	 *
	 * @static
	 * @param string  $title
	 * @param int     $post_id
	 * @return string
	 */
	public function get_title() {
		return self::__( "Retrieve Password" );
	}

	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$rp_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::RP_QUERY_VAR );
		}
	}

	/**
	 * Handles sending password retrieval email to user.
	 */
	private static function retrieve_password() {
		global $wpdb;

		if ( empty( $_POST['user_login'] ) ) {
			return 'blank';
		}

		if ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
			if ( empty( $user_data ) )
				return 'incorrect';
		}

		if ( !$user_data || empty( $user_data ) ) {
			$login = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		if ( !$user_data ) {
			return 'incorrect';
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', TRUE, $user_data->ID );

		if ( !$allow ) {
			return 'notallowed';
		}

		$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );

		if ( empty( $key ) ) {
			// Generate something random for a key...
			$key = wp_generate_password( 20, false );
			do_action( 'gb_retrieve_password_key', $user_login, $key );
			// Now insert the new md5 key into the db
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
		}

		$data = array(
			'key' => $key,
			'user' => $user_data
		);

		do_action( 'gb_retrieve_password_notification', $data );

		return 'confirm';
	}

	/**
	 * Handles resetting the user's password.
	 */
	private static function reset_password( $key ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || is_array( $key ) )
			return false;

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s", $key ) );

		if ( empty( $user ) )
			return false;

		// Generate random password w/o those special_chars that we all hate
		$new_pass = wp_generate_password( 8, false );

		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );
		$user = wp_signon(
			array(
				'user_login' => $user->user_login,
				'user_password' => $new_pass,
				'remember' => false
			), false );

		$data = array(
			'user' => $user,
			'new_pass' => $new_pass
		);

		do_action( 'gb_password_reset_notification', $data );

		wp_password_change_notification( $user );

		return true;
	}
}
