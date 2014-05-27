<?php


class Group_Buying_Merchants_Voucher_Claim extends Group_Buying_Controller {
	const BIZ_VOUCHER_PATH_OPTION = 'gb_biz_voucher_register_path';
	const BIZ_VOUCHER_QUERY_VAR = 'gb_merchant_biz_voucher';
	const BIZ_VOUCHER_CLAIM_ARG = 'gb_voucher_claim';
	const BIZ_VOUCHER_REDEMPTION_DATA = 'gb_voucher_redemption_data';
	private static $voucher_path = 'merchant/vouchers';
	private static $instance;

	public static function init() {
		self::$voucher_path = get_option( self::BIZ_VOUCHER_PATH_OPTION, self::$voucher_path );
		self::register_settings();
		
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_path_callback' ), 10, 1 );
		add_filter( 'set_merchant_voucher_report_data_column', array( get_class(), 'add_columns_merch_report' ), 10, 1 );
		add_filter( 'gb_merch_deal_voucher_record_item', array( get_class(), 'add_item_merch_report' ), 10, 4 );
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		// Settings
		$settings = array(
			'gb_url_path_merchant_voucher_mngt' => array(
				'weight' => 135,
				'settings' => array(
					self::BIZ_VOUCHER_PATH_OPTION => array(
						'label' => self::__( 'Merchant Voucher Management Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ),
							'type' => 'text',
							'default' => self::$voucher_path
							)
						)
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_UI::SETTINGS_PAGE );
	}

	public static function add_columns_merch_report( $array ) {
		$redemption_data = array(
			'redeem_name' => self::__( 'Redeemer Name' ),
			'redeem_date' => self::__( 'Redemption Date' ),
			'redeem_total' => self::__( 'Redemption Total' ),
			'redeem_notes' => self::__( 'Redemption Notes' )
		);
		return array_merge( $array, $redemption_data );
	}

	public static function add_item_merch_report( $array, $voucher, $purchase, $account ) {
		$redemption_data = $voucher->get_redemption_data();
		$filtered_redemption_data = array();
		if ( isset( $redemption_data['name'] ) ) {
			$filtered_redemption_data['redeem_name'] = $redemption_data['name'];
		}
		if ( isset( $redemption_data['date'] ) ) {
			$filtered_redemption_data['redeem_date'] = $redemption_data['date'];
		}
		if ( isset( $redemption_data['total'] ) ) {
			$filtered_redemption_data['redeem_total'] = $redemption_data['total'];
		}
		if ( isset( $redemption_data['notes'] ) ) {
			$filtered_redemption_data['redeem_notes'] = $redemption_data['notes'];
		}
		return array_merge( $array, $filtered_redemption_data );
	}

	/**
	 * Register the path callback for the cart page
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_path_callback( GB_Router $router ) {
		$path = str_replace( '/', '-', self::$voucher_path );
		$args = array(
			'path' => self::$voucher_path,
			'title' => self::__( 'Voucher Management' ),
			'title_callback' => array( self::__( 'Voucher Management' ) ),
			'page_callback' => array( get_class(), 'on_biz_voucher_page' ),
			// 'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$voucher_path ).'.php', // non-default merchant path
				self::get_template_path().'/merchant.php', // theme default path
				GB_PATH.'/views/public/merchant.php', // default
			),
		);
		$router->add_route( self::BIZ_VOUCHER_QUERY_VAR, $args );
	}

	public static function on_biz_voucher_page() {
		do_action( 'on_biz_voucher_page' );
		self::get_instance();
		self::view_voucher_mngmt();
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
		if ( isset( $_POST[self::BIZ_VOUCHER_CLAIM_ARG] ) && $_POST[self::BIZ_VOUCHER_CLAIM_ARG] != '' ) {
			self::set_claimed_date( $_POST[self::BIZ_VOUCHER_CLAIM_ARG] );
		}
	}

	public static function set_claimed_date( $security_code ) {
		$voucher_id = Group_Buying_Voucher::get_voucher_by_security_code( $security_code );
		$voucher = Group_Buying_Voucher::get_instance( array_shift( $voucher_id ) );
		$claimed = FALSE;
		if ( is_a( $voucher, 'Group_Buying_Voucher' ) ) {
			if ( FALSE != $voucher->set_claimed_date() ) {
				self::set_message( __( 'Serial claimed.' ), self::MESSAGE_STATUS_INFO );
				if ( isset( $_POST[self::BIZ_VOUCHER_REDEMPTION_DATA] ) && !empty( $_POST[self::BIZ_VOUCHER_REDEMPTION_DATA] ) ) {
					$voucher->set_redemption_data( $_POST[self::BIZ_VOUCHER_REDEMPTION_DATA] );
				}
				do_action( 'gb_voucher_merchant_redeemed', $voucher );
				$claimed = TRUE;
			}
		}
		if ( !$claimed ) {
			self::set_message( __( 'Error: Security code is not valid.' ), self::MESSAGE_STATUS_ERROR );
		}
		if ( isset( $_REQUEST['redirect_to'] ) && $_REQUEST['redirect_to'] != '' ) {
			wp_redirect( urldecode( $_REQUEST['redirect_to'] ) );
			exit();
		}
	}

	public static function view_voucher_mngmt() {
		echo self::load_view_to_string( 'merchant/voucher-claim.php', array(
				'claim_arg' => self::BIZ_VOUCHER_CLAIM_ARG,
				'data' => self::BIZ_VOUCHER_REDEMPTION_DATA,
			) );
	}

	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$voucher_path );
		} else {
			return add_query_arg( self::BIZ_VOUCHER_QUERY_VAR, 1, home_url() );
		}
	}
}