<?php



class Group_Buying_Merchants_Dashboard extends Group_Buying_Controller {
	const BIZ_DASH_PATH_OPTION = 'gb_biz_dash_register_path';
	const BIZ_DASH_QUERY_VAR = 'gb_merchant_biz_dash';
	private static $dash_path = 'merchant/dashboard';
	private static $instance;

	public static function init() {
		self::$dash_path = get_option( self::BIZ_DASH_PATH_OPTION, self::$dash_path );
		self::register_settings();

		add_action( 'gb_router_generate_routes', array( get_class(), 'register_registration_callback' ), 10, 1 );
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		// Settings
		$settings = array(
			'gb_url_path_merchant_dash' => array(
				'weight' => 131,
				'settings' => array(
					self::BIZ_DASH_PATH_OPTION => array(
						'label' => self::__( 'Merchant Dashboard Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ),
							'type' => 'text',
							'default' => self::$dash_path
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
	public static function register_registration_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$dash_path,
			'title' => 'Merchant Dashboard',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_biz_dash_page' ),
			'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$dash_path ).'.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::BIZ_DASH_QUERY_VAR, $args );
	}

	public static function on_biz_dash_page() {
		$merchant_dash_page = self::get_instance();
		// View template
		$merchant_dash_page->view_dashboard();
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

	public function view_dashboard() {
		remove_filter( 'the_content', 'wpautop' );
		self::load_view( 'merchant/dashboard', array() );
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
		return self::__( "Merchant Dashboard" );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a account page
	 */
	public static function is_merchant_dash() {
		return GB_Router_Utility::is_on_page( self::BIZ_DASH_QUERY_VAR );
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the merchant page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$dash_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::BIZ_DASH_QUERY_VAR );
		}
	}
}