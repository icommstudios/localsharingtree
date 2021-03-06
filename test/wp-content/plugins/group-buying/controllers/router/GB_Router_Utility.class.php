<?php
/**
 * GBS Router Utility
 *
 * @package GBS
 * @subpackage Router
 */
class GB_Router_Utility {
	const QUERY_VAR = 'GB_Route';
	const PLUGIN_NAME = 'GB Router';
	const TEXT_DOMAIN = 'gb-router';
	const DEBUG = FALSE;
	const MIN_PHP_VERSION = '5.2';
	const MIN_WP_VERSION = '3.0';
	const DB_VERSION = 1;
	const PLUGIN_INIT_HOOK = 'gb_router_init';


	/**
	 * A wrapper around WP's __() to add the plugin's text domain
	 *
	 * @param string  $string
	 * @return string|void
	 */
	public static function __( $string ) {
		return __( $string, self::TEXT_DOMAIN );
	}

	/**
	 * A wrapper around WP's _e() to add the plugin's text domain
	 *
	 * @param string  $string
	 * @return void
	 */
	public static function _e( $string ) {
		_e( $string, self::TEXT_DOMAIN );
	}

	/**
	 *
	 *
	 * @static
	 * @return string The system path to this plugin's directory, with no trailing slash
	 */
	public static function plugin_path() {
		return WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) );
	}

	/**
	 *
	 *
	 * @static
	 * @return string The url to this plugin's directory, with no trailing slash
	 */
	public static function plugin_url() {
		return WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) );
	}

	/**
	 * Check that the minimum PHP and WP versions are met
	 *
	 * @static
	 * @param string  $php_version
	 * @param string  $wp_version
	 * @return bool Whether the test passed
	 */
	public static function prerequisites_met( $php_version, $wp_version ) {
		$pass = TRUE;
		$pass = $pass && version_compare( $php_version, self::MIN_PHP_VERSION, '>=' );
		$pass = $pass && version_compare( $wp_version, self::MIN_WP_VERSION, '>=' );
		return $pass;
	}

	public static function failed_to_load_notices( $php_version = self::MIN_PHP_VERSION, $wp_version = self::MIN_WP_VERSION ) {
		printf( '<div class="error"><p>%s</p></div>', sprintf( self::__( '%1$s requires WordPress %2$s or higher and PHP %3$s or higher.' ), self::PLUGIN_NAME, $wp_version, $php_version ) );
	}

	public static function init() {
		do_action( self::PLUGIN_INIT_HOOK );
	}

	/**
	 * Determine if the page loaded is the router page checked for.
	 * 
	 * @return boolean 
	 */
	public function is_on_page( $router = '' ) {
		$query_var = get_query_var( self::QUERY_VAR );
		if ( $query_var == $router ) {
			return TRUE;
		}
		if ( $query_var == '' ) {
			// Possibly checked before get_query_var function can work.
			global $wp;
			if ( isset( $wp->query_vars[self::QUERY_VAR] ) && $wp->query_vars[self::QUERY_VAR] == $router ) {
				return TRUE;
			}
		}
		return FALSE;
	}
}
