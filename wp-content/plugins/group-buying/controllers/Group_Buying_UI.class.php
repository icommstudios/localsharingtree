<?php

/**
 * GBS UI related: widgets, resources, etc..
 *
 * @package GBS
 * @subpackage Theme
 */
class Group_Buying_UI extends Group_Buying_Controller {
	const TODAYSDEAL_PATH_OPTION = 'gb_todaysdeal_path';
	const TODAYSDEAL_QUERY_VAR = 'gb_todaysdeal_path';
	const REMOVE_EXPIRED_DEALS = 'gb_remove_expired';
	const COUNTRIES_OPTION = 'gb_countries_filter';
	const STATES_OPTION = 'gb_states_filter';
	public static $todays_deal_path;
	public static $remove_expired;
	protected static $settings_page;
	protected static $int_settings_page;
	protected static $countries;
	protected static $states;
	private static $instance;

	final public static function init() {
		self::get_instance();
		self::$settings_page = self::register_settings_page( 'gb_settings', self::__( 'Welcome to Group Buying' ), self::__( 'General Settings' ), 10, FALSE, 'general' );
		self::$todays_deal_path = trailingslashit( get_option( self::TODAYSDEAL_PATH_OPTION, 'todays-deal' ) );
		self::$remove_expired = get_option( self::REMOVE_EXPIRED_DEALS, FALSE );
		self::$countries = get_option( self::COUNTRIES_OPTION, FALSE );
		self::$states = get_option( self::STATES_OPTION, FALSE );

		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 0 );
		add_action( 'admin_init', array( get_class(), 'register_int_settings_fields' ), 100, 0 );

		// Callback
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_todays_deal_callback' ), 10, 1 );

		add_action( 'init', array( get_class(), 'register_resources' ) );
		add_action( 'wp_enqueue_scripts', array( get_class(), 'frontend_enqueue' ) );
		add_action( 'admin_enqueue_scripts', array( get_class(), 'admin_enqueue' ) );

		add_action( 'admin_head', array( get_class(), 'admin_footer' ) );
	}

	public static function admin_footer() {
		echo '<style type="text/css">';
		echo '#icon-edit.icon32-posts-gb_deal { background: url('.GB_URL.'/resources/img/deals-big.png) no-repeat 0 0; }';
		echo '#icon-edit.icon32-posts-gb_merchant { background: url('.GB_URL.'/resources/img/merchant-big.png) no-repeat 0 0; }';
		echo '</style>';
	}

	public static function register_resources() {
		// Timepicker
		wp_register_script( 'gb_timepicker', GB_URL . '/resources/plugins/frontend/timepicker/timepicker.jquery.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), Group_Buying::GB_VERSION );
		wp_register_script( 'gb_admin_deal', GB_URL . '/resources/js/admin/deal.admin.gbs.js', array( 'jquery', 'gb_timepicker' ), Group_Buying::GB_VERSION );
		wp_register_style( 'gb_admin_deal', GB_URL . '/resources/css/admin/deal.admin.gbs.css', array(), Group_Buying::GB_VERSION  );

		// Datepicker and misc.
		wp_register_script( 'gb_admin_settings', GB_URL . '/resources/js/admin/settings.admin.gbs.js', array( 'jquery', 'jquery-ui-draggable' ), Group_Buying::GB_VERSION );
		wp_register_style( 'gb_admin_settings_css', GB_URL . '/resources/css/admin/settings.admin.gbs.css', array(), Group_Buying::GB_VERSION );


		// Select2
		wp_register_script( 'select2', GB_URL . '/resources/plugins/admin/select2/select2.js', array( 'jquery' ), Group_Buying::GB_VERSION );
		wp_register_style( 'select2_css', GB_URL . '/resources/plugins/admin/select2/select2.css', array(), Group_Buying::GB_VERSION );

		// Marketplace
		wp_register_script( 'gb_admin_marketplace', GB_URL . '/resources/js/admin/marketplace.admin.gbs.js', array( 'jquery' ), Group_Buying::GB_VERSION );

		// Notifications
		wp_register_script( 'gb_admin_notifications', GB_URL . '/resources/js/admin/notification.admin.gbs.js', array( 'jquery' ), Group_Buying::GB_VERSION );

		//////////////
		// Frontend //
		//////////////

		// Frontend Timepicker styling
		wp_register_script( 'gb_frontend_deal_submit', GB_URL . '/resources/js/frontend/deal_submit.gbs.js', array( 'jquery', 'gb_timepicker' ), Group_Buying::GB_VERSION );
		wp_register_style( 'gb_frontend_deal_submit', GB_URL . '/resources/css/frontend/deal_submit.gbs.css', array(), Group_Buying::GB_VERSION  );
		wp_register_style( 'gb_frontend_deal_submit_timepicker_css', GB_URL . '/resources/css/frontend/dark-hive/jquery-ui.custom.css', array( 'gb_frontend_deal_submit' ), Group_Buying::GB_VERSION  );
		wp_register_style( 'gb_frontend_jquery_ui_style', GB_URL . '/resources/css/frontend/dark-hive/jquery-ui.custom.css', array( 'gb_frontend_deal_submit' ), Group_Buying::GB_VERSION  ); // duplicate with difference handle

		// Checkout
		wp_register_script( 'gb_frontend_checkout', GB_URL . '/resources/js/frontend/checkout.gbs.js', array( 'jquery' ), Group_Buying::GB_VERSION );

		// Validation Plugins (including credit card check)
		wp_register_script( 'gb_frontend_validation', GB_URL . '/resources/plugins/frontend/plugins.gbs.js', array( 'jquery' ), Group_Buying::GB_VERSION );

	}

	public static function frontend_enqueue() {
		wp_enqueue_script( 'gb_frontend_validation' );
	}

	public static function admin_enqueue() {
		wp_enqueue_script( 'gb_admin_settings' );
		wp_enqueue_style( 'gb_admin_settings_css' );

		wp_enqueue_script( 'gb_admin_marketplace' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_style( 'select2_css' );
	}

	/**
	 *
	 *
	 * @static
	 * @return string The ID of the payment settings page
	 */
	public static function get_settings_page() {
		return self::$settings_page;
	}

	/*
	 * Singleton Design Pattern
	 * ------------------------------------------------------------- */
	final protected function __clone() {
		// cannot be cloned
		trigger_error( __CLASS__.' may not be cloned', E_USER_ERROR );
	}

	final protected function __sleep() {
		// cannot be serialized
		trigger_error( __CLASS__.' may not be serialized', E_USER_ERROR );
	}
	/**
	 *
	 *
	 * @static
	 * @return Group_Buying_UI
	 */
	public static function get_instance() {
		if ( !( self::$instance && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	protected function __construct() {
		if ( 'FALSE' != self::$remove_expired ) {
			add_filter( 'pre_get_posts', array( get_class(), 'remove_expired_deals' ), 10, 1 );
		}
		if ( 'false' != self::$countries && !empty( self::$countries ) ) {
			add_filter( 'gb_country_options', array( get_class(), 'get_countries_option' ), 10, 2 );
		}
		if ( 'false' != self::$states && !empty( self::$states ) ) {
			add_filter( 'gb_state_options', array( get_class(), 'get_states_option' ), 10, 2 );
		}
	}

	/**
	 * Remove expired deals from loop
	 *
	 * @param string  $query
	 * @return void
	 * @author Dan Cameron
	 */
	public static function remove_expired_deals( &$query ) {
		if ( ( is_tax() || is_archive() ) && !is_page() && !is_single() && !is_admin() && !gb_on_voucher_page() ) {
			if ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == gb_get_deal_post_type()  ) {
				$query->set( 'meta_query', array(
						array(
							'key' => '_expiration_date',
							'value' => array( 0, current_time( 'timestamp' ) ),
							'compare' => 'NOT BETWEEN'
						)
					) );
			}
			if ( self::$remove_expired == 'ALL' ) {
				if (
					isset( $query->query[gb_get_deal_location_tax()] ) ||
					isset( $query->query[gb_get_deal_cat_slug()] ) ||
					isset( $query->query[gb_get_deal_tag_slug()] )
				) {
					$query->set( 'meta_query', array(
							array(
								'key' => '_expiration_date',
								'value' => array( 0, current_time( 'timestamp' ) ),
								'compare' => 'NOT BETWEEN'
							)
						) );
				}
			}
		}
	}

	/**
	 * Register the path callback
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_todays_deal_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$todays_deal_path,
			'title' => 'Redirect to Latest Deal',
			'page_callback' => array( get_class(), 'redirect_to_latest_deal' )
		);
		$router->add_route( self::TODAYSDEAL_QUERY_VAR, $args );
	}

	public static function redirect_to_latest_deal() {
		wp_redirect( gb_get_latest_deal_link() );
		exit();
		
	}

	public static function register_settings_fields() {
		$page = self::$settings_page;
		$section = 'gb_general_settings';
		add_settings_section( $section, self::__( 'General Options' ), array( get_class(), 'display_settings_section' ), $page );
		register_setting( self::$settings_page, self::TODAYSDEAL_PATH_OPTION );
		register_setting( self::$settings_page, self::REMOVE_EXPIRED_DEALS );
		add_settings_field( self::REMOVE_EXPIRED_DEALS, self::__( 'Expired Deals' ), array( get_class(), 'display_option_remove_expired' ), $page, $section );
		add_settings_field( self::TODAYSDEAL_PATH_OPTION, self::__( 'Latest Deal URL' ), array( get_class(), 'display_option_todays_deal' ), $page, $section );
	}

	public static function register_int_settings_fields() {
		$page = self::$settings_page;
		$int_section = 'gb_internationalization_settings';
		add_settings_section( $int_section, self::__( 'Form Options' ), array( get_class(), 'display_internationalization_section' ), $page );
		register_setting( self::$settings_page, self::STATES_OPTION, array( get_class(), 'save_states') );
		register_setting( self::$settings_page, self::COUNTRIES_OPTION, array( get_class(), 'save_countries') );
		add_settings_field( self::STATES_OPTION, self::display_option_states_title(), array( get_class(), 'display_option_states' ), $page, $int_section );
		add_settings_field( self::COUNTRIES_OPTION, self::display_option_countries_title(), array( get_class(), 'display_option_countries' ), $page, $int_section );
	}

	public static function display_option_todays_deal() {
		echo home_url().'/<input type="text" name="'.self::TODAYSDEAL_PATH_OPTION.'" value="'.self::$todays_deal_path.'">';
	}

	public static function display_option_remove_expired() {
		echo '<label><input type="radio" name="'.self::REMOVE_EXPIRED_DEALS.'" value="TRUE" '.checked( 'TRUE', self::$remove_expired, FALSE ).'/> '.self::__( 'Remove the expired deals from the main deals loop.' ).'</label><br />';
		echo '<label><input type="radio" name="'.self::REMOVE_EXPIRED_DEALS.'" value="ALL" '.checked( 'ALL', self::$remove_expired, FALSE ).'/> '.self::__( 'Remove the expired deals from location, tags and category loops.' ).'</label><br />';
		echo '<label><input type="radio" name="'.self::REMOVE_EXPIRED_DEALS.'" value="FALSE" '.checked( 'FALSE', self::$remove_expired, FALSE ).'/> '.self::__( 'Show expired deals. ' ).'</label><br />';
	}

	public static function display_internationalization_section() {
		echo '<p>'.self::_e( 'Select the states and countries/proviences you would like in your forms.' ).'</p>';

	}

	public static function display_option_states_title() {
		$title = '<strong>'.self::__( 'States' ).'</strong>';
		$title .= '<p>'.self::__( 'Additional states can be added by hooking into the <code>gb_state_options</code> filter.' ).'</p>';
		return $title;
	}

	public static function display_option_states() {
		echo '<div class="gb_state_options">';
		echo '<select name="'.self::STATES_OPTION.'[]" class="select2" multiple="multiple">';
		foreach ( parent::$grouped_states as $group => $states ) {
			echo '<optgroup label="'.$group.'">';
			foreach ($states as $key => $name) {
				$selected = ( in_array( $name, self::$states[$group] ) || empty( self::$states ) ) ? 'selected="selected"' : null ;
				echo '<option value="'.$key.'" '.$selected.'>&nbsp;'.$name.'</option>';
			}
			echo '</optgroup>';
		}
		echo '</select>';
		echo '</div>';
	}

	public static function display_option_countries_title() {
		$title = '<strong>'.self::__( 'Countries' ).'</strong>';
		$title .= '<p>'.self::__( 'Additional countries can be added by hooking into the <code>gb_country_options</code> filter.' ).'</p>';
		$title .= '<p>'.self::__( 'Note: Some payment processors country support is limited. For example, Paypal Pro only accepts <a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_country_codes">these countries</a>.' ).'</p>';
		return $title;
	}

	public static function display_option_countries() {
		echo '<div class="gb_country_options">';
		echo '<select name="'.self::COUNTRIES_OPTION.'[]" class="select2" multiple="multiple">';
		foreach ( parent::$countries as $key => $name ) {
			$selected = ( in_array( $name, self::$countries ) || empty( self::$countries ) ) ? 'selected="selected"' : null ;
			echo '<option value="'.$name.'" '.$selected.'>&nbsp;'.$name.'</option>';
		}
		echo '</select>';
		echo '</div>';
	}

	public static function save_states( $selected ) {
		$sanitized_options = array();
		foreach ( parent::$grouped_states as $group => $states ) {
			$sanitized_options[$group] = array();
			foreach ($states as $key => $name) {
				if ( in_array( $key, $selected ) ) {
					$sanitized_options[$group][$key] = $name;
				}
			}
			// Unset the empty groups
			if ( empty( $sanitized_options[$group] ) ) {
				unset( $sanitized_options[$group] );
			}
		}
		return $sanitized_options;
	}

	public static function save_countries( $options ) {
		$sanitized_options = array();
		foreach ( parent::$countries  as $key => $name ) {
			if ( in_array( $name, $options ) ) {
				$sanitized_options[$key] = $name;
			}
		}
		return $sanitized_options;
	}

	public static function get_states_option( $states = array(), $args = array() ) {
		if ( isset( $args['include_option_none'] ) && $args['include_option_none'] ) {
			$states = array( '' => $args['include_option_none'] ) + self::$states;
		}
		return $states;
	}

	public static function get_countries_option( $countries = array(), $args = array() ) {
		if ( isset( $args['include_option_none'] ) && $args['include_option_none'] ) {
			$countries = array( '' => $args['include_option_none'] ) + self::$countries;
		}
		return $countries;
	}

	public function load_wp_media() {
		if ( function_exists('gb_load_wp_media') ) { // Allow for easy overrides.
			gb_load_wp_media();
			return;
		}
		if ( !function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin' . '/includes/image.php';
			require_once ABSPATH . 'wp-admin' . '/includes/file.php';
			require_once ABSPATH . 'wp-admin' . '/includes/media.php';
		}
	}
}