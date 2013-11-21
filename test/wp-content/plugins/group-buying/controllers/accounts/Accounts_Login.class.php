<?php

class Group_Buying_Accounts_Login extends Group_Buying_Accounts {
	const LOGIN_PATH_OPTION = 'gb_account_login_path';
	const LOGIN_QUERY_VAR = 'gb_account_login';
	const FORM_ACTION = 'gb_account_login';
	private static $login_path = 'account/login';
	private static $instance;
	private static $on_login_page = FALSE;

	public static function init() {
		self::$login_path = get_option( self::LOGIN_PATH_OPTION, self::$login_path );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 1 );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_edit_callback' ), 10, 1 );

		// Hooked login
		add_action( 'wp_login_failed', array( get_class(), 'login_failed' ), 10, 1 );

		// wp-login.php
		add_action( 'init', array( get_class(), 'redirect_away_from_login' ) );

		// Replace WP Login URIs
		add_filter( 'login_url', array( get_class(), 'login_url' ), 10, 2 );
		add_filter( 'logout_url' , array( get_class(), 'log_out_url' ), 100, 2 );
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
			'path' => self::$login_path,
			'title' => 'Account Login',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_login_page' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$login_path ).'.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::LOGIN_QUERY_VAR, $args );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_url_paths';

		// Settings
		register_setting( $page, self::LOGIN_PATH_OPTION );
		add_settings_field( self::LOGIN_PATH_OPTION, self::__( 'Account Login Path' ), array( get_class(), 'display_account_registration_path' ), $page, $section );
	}

	public static function display_account_registration_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="' . self::LOGIN_PATH_OPTION . '" id="' . self::LOGIN_PATH_OPTION . '" value="' . esc_attr( self::$login_path ) . '" size="40"/><br />';
	}

	public static function on_login_page() {
		// Registered users shouldn't be here. Send them elsewhere
		if ( get_current_user_id() && !self::log_out_attempt() ) {
			wp_redirect( Group_Buying_Accounts::get_url(), 303 );
			exit();
		}
		// Login Attempt
		if ( !empty( $_POST['gb_login'] ) && wp_verify_nonce( $_POST['gb_login'], 'gb_login_action' ) ) {
			$user = wp_signon();
			if ( !is_wp_error( $user ) ) {
				$user_id = $user->ID;
				do_action( 'gb_user_logged_in', $user, $_REQUEST );
				if ( isset( $_POST['redirect_to'] ) && !empty( $_POST['redirect_to'] ) ) {
					$redirect_str = str_replace( home_url(), '', $_POST['redirect_to'] ); // in case the home_url is already added
					$redirect = home_url( $redirect_str );
					wp_redirect( apply_filters( 'gb_login_success_redirect', $redirect, $user_id ) );
				} else {
					wp_redirect( apply_filters( 'gb_login_success_redirect', gb_get_last_viewed_redirect_url(), $user_id ) );
				}
				exit();
			}
		}
		// Logout attempt
		elseif ( self::log_out_attempt() ) {
			// logout
			wp_logout();

			if ( isset( $_GET['redirect_to'] ) ) {
				$redirect_to = add_query_arg( array( 'loggedout' => 'true', 'message' => 'loggedout' ), home_url( $_GET['redirect_to'] ) );
			} else {
				$redirect_to = add_query_arg( array( 'loggedout' => 'true', 'message' => 'loggedout' ), self::get_url() );
			}
			wp_redirect( $redirect_to );
			exit();
		} // No attempts yet.

		$login_page = self::get_instance();
		// View template
		$login_page->view_login_form();
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
	public function view_login_form() {
		remove_filter( 'the_content', 'wpautop' );
		$args = array();
		// Set the redirect_to arg if the url has set it.
		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect = str_replace( home_url(), '', $_GET['redirect_to'] );
			$args['redirect'] = $redirect;
		}
		if ( self::is_login_page() ) {
			$args['submit'] = '<input type="submit" name="submit" value="'.self::__( 'Sign In Now' ).'" class="form-submit" />';
		} else {
			$args['submit'] = '';
		}
		self::load_view( 'account/login', array( 'args' => $args ) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a account page
	 */
	public static function is_login_page() {
		return GB_Router_Utility::is_on_page( self::LOGIN_QUERY_VAR );
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
		return self::__( 'Login' );
	}

	/////////////////////
	// Error Messaging //
	/////////////////////

	public static function login_failed( $username ) {
		// recap a lot of wp-login.php
		if ( !empty( $_GET['loggedout'] ) )
			return;

		// If cookies are disabled we can't log in even with a valid user+pass
		if ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[TEST_COOKIE] ) )
			$message = self::__( 'Cookies are Disabled' );

		if ( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] )
			$message = self::__( 'Registration Disabled' );
		elseif ( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] )
			$message = self::__( 'Registered' );
		elseif ( isset( $_REQUEST['interim-login'] ) )
			$message = self::__( 'Error: Expired' );
		else
			$message = self::__( 'Username and/or Password Incorrect.' );

		$url = self::get_url();
		$url = add_query_arg( 'message', $message, self::get_url() );
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$url = add_query_arg( 'redirect_to', $_REQUEST['redirect_to'], $url );
		}
		self::set_message( $message, self::MESSAGE_STATUS_ERROR );
		wp_redirect( $url );
		exit();
	}

	///////////
	// URLS //
	///////////

	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$login_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::LOGIN_QUERY_VAR );
		}
	}

	public static function login_url( $url, $redirect ) {
		$url = self::get_url();
		$redirect = apply_filters( 'gb_login_url_redirect', $redirect );
		if ( $redirect ) {
			$redirect = str_replace( home_url(), '', $redirect );
			$url = add_query_arg( 'redirect_to', $redirect, $url );
		} else {
			$redirect = str_replace( home_url(), '', Group_Buying_Accounts::get_url() );
			$url = add_query_arg( 'redirect_to', $redirect, $url );
		}
		return $url;
	}

	public static function log_out_url(  $url = null, $redirect = null ) {
		$url = self::get_url();
		if ( $redirect ) {
			$redirect = str_replace( home_url(), '', $redirect );
			$url = add_query_arg( array( 'redirect_to' => $redirect, 'action' => 'logout', 'message' => 'loggedout' ), $url );
		} else {
			$url = add_query_arg( array( 'action' => 'logout', 'message' => 'loggedout' ), $url );
		}
		return $url;
	}

	public static function log_out_attempt() {
		return ( isset( $_GET['action'] ) && 'logout' == $_GET['action'] ) ? TRUE : FALSE;
	}

	/**
	 * Redirects away from the login page.
	 *
	 */
	public function redirect_away_from_login() {
		global $pagenow;

		// check for password protected content
		if ( isset( $_GET['action'] ) && isset( $_POST['post_password'] ) && $_GET['action'] == 'postpass' )
			return;

		// check if it's part of a flash upload.
		if ( isset( $_POST ) && !empty( $_POST['_wpnonce'] ) )
			return;

		// always redirect away from wp-login.php but check if the user is an admin before redirecting them.
		if ( 'wp-login.php' == $pagenow || 'wp-activate.php' == $pagenow || 'wp-signup.php' == $pagenow || ( !current_user_can( 'edit_posts' ) && is_admin() && !defined( 'DOING_AJAX' ) ) ) {
			// If they're logged in, direct to the account page
			if ( is_user_logged_in() ) {
				wp_redirect( apply_filters( 'gb_redirect_away_from_login', Group_Buying_Accounts::get_url() ) );
				exit();
			} else { // everyone else needs to login
				if ( !defined( 'DOING_AJAX' ) ) {
					$redirect = ( isset( $_GET['action'] ) ) ? add_query_arg( array( 'action' => $_GET['action'] ), self::get_url() ) : self::get_url() ;
					wp_redirect( apply_filters( 'gb_redirect_away_from_login', $redirect ) );
					exit();
				}
			}
		}
	}
}
