<?php

/**
 * Affiliates Controller
 *
 * @package GBS
 * @subpackage Affiliate
 */
class Group_Buying_Affiliates extends Group_Buying_Controller {
	const CREDIT_TYPE = 'affiliate';
	const AFFILIATE_CREDIT_OPTION = 'gb_affiliate_credit';
	const AFFILIATE_COOKIE = 'affiliated_with';
	const AFFILIATE_COOKIE_EXP_OPTION = 'gb_affiliate_cookie_exp';
	const AFFILIATE_QUERY_ARG = 'affiliated-member';
	const SHARED_POST_QUERY_ARG = 'shared-post';
	const SHARE_PATH_OPTION = 'gb_share_path';
	const BITLY_API_LOGIN = 'gb_bitly_login';
	const BITLY_API_KEY = 'gb_bitly_api_key';
	const WP_AFFILIATE_POST = 'gb_affiliate_url_option';
	const WP_AFFILIATE_KEY = 'gb_affiliate_key_option';
	const PURCHASE_WPAF_APPLIED_META = '_gb_wpaffiliate_applied';
	protected static $settings_page;
	private static $affiliate_credit;
	private static $affiliate_cookie_exp;
	private static $share_path = 'share';
	private static $bitly_login;
	private static $bitly_api;
	private static $affiliate_payment_processor;
	private static $affiliate_post;
	private static $affiliate_key;

	final public static function init() {
		// Options
		self::$share_path = get_option( self::SHARE_PATH_OPTION, self::$share_path );
		self::$bitly_login = get_option( self::BITLY_API_LOGIN );
		self::$bitly_api = get_option( self::BITLY_API_KEY );
		self::$affiliate_credit = get_option( self::AFFILIATE_CREDIT_OPTION, '0' );
		self::$affiliate_cookie_exp = (int)get_option( self::AFFILIATE_COOKIE_EXP_OPTION, '3600' );
		self::$affiliate_post = get_option( self::WP_AFFILIATE_POST, trailingslashit( WP_PLUGIN_URL ) . 'wp-affiliate-platform/api/post.php' );
		self::$affiliate_key = get_option( self::WP_AFFILIATE_KEY );

		// This shouldn't ever be instantiated through the normal process. We want to add it on.
		self::$affiliate_payment_processor = Group_Buying_Affiliate_Credit_Payments::get_instance();
		add_filter( 'gb_account_credit_types', array( get_class(), 'register_credit_type' ), 10, 1 );

		// Routing
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_path_callback' ), 100, 1 );

		add_action( 'payment_authorized', array( get_class(), 'set_source' ), 10, 1 ); // for onsite purchases
		add_action( 'payment_pending', array( get_class(), 'set_source' ), 10, 1 ); // for those offsite purchases
		add_action( 'payment_complete', array( get_class(), 'apply_credits' ), 10, 1 ); // Do the dirty work

		// WP Affiliate
		add_action( 'payment_authorized', array( get_class(), 'set_ad_id' ), 20, 1 );
		add_action( 'payment_pending', array( get_class(), 'set_ad_id' ), 20, 1 );
		add_action( 'payment_complete', array( get_class(), 'wp_affiliate' ), 5, 1 ); // Come before apply_credits

		// Register settings after WP_AFFILIATE
		self::register_settings();
	}

	///////////////
	// Settings //
	///////////////

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		// Settings
		$settings = array(
			'gb_affiliate_settings' => array(
				'title' => self::__('Affiliate/Share Settings'),
				'weight' => 60,
				'callback' => array( get_class(), 'display_settings_section' ),
				'settings' => array(
					self::AFFILIATE_CREDIT_OPTION => array(
						'label' => self::__( 'Social/Affiliate Credit' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$affiliate_credit,
							'attributes' => array( 'class' => 'small-text' )
							)
						),
					self::AFFILIATE_COOKIE_EXP_OPTION => array(
						'label' => self::__( 'Cookie Expiration' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$affiliate_cookie_exp,
							'description' => self::__('In Seconds'),
							'attributes' => array( 'class' => 'small-text' )
							)
						),
					self::SHARE_PATH_OPTION => array(
						'label' => self::__( 'Share Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ),
							'type' => 'text',
							'default' => self::$share_path,
							'description' => trailingslashit( get_home_url() ) . trailingslashit( self::$share_path ) . '&lt;'.self::__('account slug').'&gt;/&lt;'.self::__('deal slug').'&gt;/',
							'attributes' => array( 'class' => 'small-text' )
							)
						),
					self::BITLY_API_LOGIN => array(
						'label' => self::__( 'Bitly Login' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$bitly_login,
							'description' => self::__('Use the Bitly API to shorten URLs and enables stat functions (e.g. total shares).  Be aware that shortened URLs can sometimes cause emails to be marked as spam or blocked.')
							)
						),
					self::BITLY_API_KEY => array(
						'label' => self::__( 'Bitly API Key' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$bitly_api,
							'description' => self::__('Find this API key on your <a href="http://bitly.com/a/account">account page</a>.')
							)
						),
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_UI::SETTINGS_PAGE );
	}

	public function display_settings_section() {
		if ( function_exists( 'wp_aff_record_remote_click' ) ) {
			printf( self::__( '<a href="%s" target="_blank">WP Affiliate Platform</a> Has Been Automatically Integrated' ), 'http://smartecart.com/goto/WPAffiliatePlatform' );
		} else {
			printf( self::__( 'GBS supports basic integration with <a href="%s" target="_blank">WordPress Affiliate Platform</a> an easy to use WordPress plugin for affiliate recruitment, management and tracking that can be used on any WordPress blog/site.' ), 'http://smartecart.com/goto/WPAffiliatePlatform' );
		}
	}

	public static function validate_share_path_field( $value ) {
		$value = trim( $value, "/" );
		return $value;
	}

	///////////////
	// Payments //
	///////////////

	public static function register_credit_type( $credit_types = array() ) {
		$credit_types[self::CREDIT_TYPE] = self::__( 'Reward Points' );
		return $credit_types;
	}

	///////////////
	// redirect //
	///////////////

	/**
	 * Register the path callback for the share redirect
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_path_callback( GB_Router $router ) {
		global $wp_rewrite;
		$args = array(
			'path' => trailingslashit( self::$share_path ). '([^/]+)/([\w-]+)/?$',
			'query_vars' => array(
				self::AFFILIATE_QUERY_ARG => 1,
				self::SHARED_POST_QUERY_ARG => 2
			),
			'title' => 'Shared Redirect',
			'page_arguments' => array( self::AFFILIATE_QUERY_ARG, self::SHARED_POST_QUERY_ARG ),
			'page_callback' => array( get_class(), 'shared_redirect' )
		);
		$router->add_route( self::AFFILIATE_QUERY_ARG, $args );
	}

	/**
	 * Redirects based on variables
	 * @param  integer $affiliate_member 
	 * @param  string  $shared_post      
	 * @return                     
	 */
	public static function shared_redirect( $affiliate_member = 0, $shared_post = '' ) {
		if ( !$affiliate_member || !$shared_post ) {
			wp_redirect( add_query_arg( 'socializer', 'null', home_url() ) );
			exit();
		}

		// Set an affiliate cookie
		self::set_cookie( $affiliate_member );

		// Redirect
		$post = get_page_by_path( $shared_post, OBJECT, Group_Buying_Deal::POST_TYPE );
		$post_id = ( is_a( $post, 'WP_Post' ) ) ? $post->ID : $shared_post;
		do_action( 'gb_shared_post_redirection', $affiliate_member, $shared_post, $post_id );
		if ( get_post_type( $post_id ) == Group_Buying_Deal::POST_TYPE ) {
			wp_redirect( add_query_arg( 'socializer', $affiliate_member, get_permalink( $post_id ) ) );
			exit();
		} else {
			wp_redirect( add_query_arg( 'socializer', $affiliate_member, home_url() ) );
			exit();
		}
	}

	////////////////////
	// Apply credits //
	////////////////////

	/**
	 * Set the source or affiliate within the payment record.
	 *
	 * @param string
	 * @return void
	 */
	public static function set_source( $payment ) {
		if ( isset( $_COOKIE[self::AFFILIATE_COOKIE] ) && $_COOKIE[self::AFFILIATE_COOKIE] != '' ) { // be careful not to overwrite the source with something blank, since this function gets called after a successful IPN validation too.
			$member_login = $_COOKIE[self::AFFILIATE_COOKIE];
			$payment->set_source( $member_login );
		}
	}

	/**
	 * Give credits to deserving users when a purchase is completed
	 *
	 * @param Group_Buying_Purchase $purchase
	 * @return void
	 */
	public static function apply_credits( Group_Buying_Payment $payment ) {
		// Get the referrers account
		$member_id = self::get_source( $payment );
		if ( !empty( $member_id ) ) {
			$purchaser_account = $payment->get_account();
			$affiliate_account = Group_Buying_Account::get_instance( $member_id );
			if ( !is_a( $affiliate_account, 'Group_Buying_Account' ) || !is_a( $purchaser_account, 'Group_Buying_Account' ) ) {
				return;
			}
			if ( $purchaser_account->get_ID() != $affiliate_account->get_ID() ) {
				$affiliate_account->add_credit( self::$affiliate_credit, self::CREDIT_TYPE );
				do_action( 'gb_apply_credits', $affiliate_account, $payment, self::$affiliate_credit, self::CREDIT_TYPE );
				self::set_cookie( null, TRUE ); // in case we still have access.
				// Loop through all of the payments, including this one and reset the source to something more readable, it also works as a failsafe for double counts.
				$payments = Group_Buying_Payment::get_payments_for_purchase( $payment->get_purchase() );
				foreach ( $payments as $payment_id ) {
					$payment = Group_Buying_Payment::get_instance( $payment_id );
					$account = self::get_source( $payment, TRUE );
					$source = self::__( 'Member: ' ).$account->get_name();
					$payment->set_source( $source );
				}
				self::affiliate_record( $affiliate_account, $purchaser_account, $payment->get_ID(), self::$affiliate_credit, self::CREDIT_TYPE );
			}
		}

	}

	// FUTURE move this to records controller
	public static function affiliate_record( $account, $purchaser_account, $payment_id, $credits, $type ) {
		$account_id = $account->get_ID();
		$purchaser_id = $purchaser_account->get_ID();
		$purchaser_name = $purchaser_account->get_name();
		$balance = $account->get_credit_balance( $type );
		$data = array();
		$data['account_id'] = $account_id;
		$data['payment_id'] = $payment_id;
		$data['credits'] = $credits;
		$data['type'] = $type;
		$data['current_total_'.$type] = $balance;
		$data['change_'.$type] = $credits;
		$data['adjustment_value'] = $credits;
		$data['current_total'] = $balance;
		$data['prior_total'] = $balance-$credits;
		do_action( 'gb_new_record', sprintf( self::__( '%s Points from %s (#%s)' ), ucfirst( $type ), $purchaser_name, $purchaser_id ), Group_Buying_Accounts::$record_type . '_' . $type, sprintf( self::__( '%s Points from %s (#%s)' ), ucfirst( $type ), $purchaser_name, $purchaser_id ), 1, $account_id, $data );
	}

	//////////////
	// utility //
	//////////////

	/**
	 * Get the account ID via the source/membername
	 *
	 * @static
	 * @return int
	 */
	public static function get_source( Group_Buying_Payment $payment, $account = false ) {
		$member_login = $payment->get_source();
		if ( !$member_login ) {
			return FALSE;
		}
		$user = get_user_by( 'login', urldecode( $member_login ) );
		if ( !$user && !is_int( $user->ID ) ) {
			return FALSE;
		}
		if ( $account ) {
			return Group_Buying_Account::get_instance( $user->ID );
		}
		return $user->ID;
	}

	public static function set_cookie( $affiliate_member = null, $destroy = FALSE ) {
		if ( null == $affiliate_member || !$destroy ) {
			setcookie( self::AFFILIATE_COOKIE, $affiliate_member, time()+self::$affiliate_cookie_exp, '/' );
		} else {
			setcookie( self::AFFILIATE_COOKIE, '', current_time( 'timestamp' )-( 60*60 ), '/' );
		}
	}

	///////////////////
	// WP Affiliate //
	///////////////////

	public static function wp_affiliate( Group_Buying_Payment $payment ) {
		// Make sure WP Affiliate is installed
		if ( !function_exists( 'wp_aff_record_remote_click' ) )
			return;

		$source = $payment->get_source();
		if ( $source ) {

			// Get Purchase
			$transaction_id = $payment->get_purchase();
			$purchase = Group_Buying_Purchase::get_instance( $transaction_id );

			if ( !$purchase->get_post_meta( self::PURCHASE_WPAF_APPLIED_META ) ) {

				// Hook Latest Versions of WP Affiliate Platform
				if ( TRUE ) {
					do_action( 'wp_affiliate_process_cart_commission',
						array(
							'referrer' => apply_filters( 'gb_wp_affiliate_referrer', $source, $purchase),
							'sale_amt' => apply_filters( 'gb_wp_affiliate_sale_amt', $purchase->get_subtotal(), $purchase),
							'txn_id' => apply_filters( 'gb_wp_affiliate_txn_id', $transaction_id, $purchase )
							) );

				}
				// Older versions of WP Affiliate Platform
				else {
					// Prepare the data
					$data = array();
					$data['secret'] = self::$affiliate_key;
					$data['ap_id'] = apply_filters( 'gb_wp_affiliate_referrer', $source, $purchase);
					$data['sale_amt'] = apply_filters( 'gb_wp_affiliate_sale_amt', $purchase->get_subtotal(), $purchase);
					$data['txn_id'] = apply_filters( 'gb_wp_affiliate_txn_id', $transaction_id, $purchase);
					$data['item_id'] = '';
					// Post data
					$response = wp_remote_post( self::$affiliate_post,
						array(
							'method' => 'POST',
							'body' => $data,
							'timeout' => 15,
							'sslverify' => false )
					);
				}

				self::set_wpap( $purchase );
			}

		}
	}

	public static function set_wpap( Group_Buying_Purchase $purchase ) {
		$purchase->save_post_meta( array(
				self::PURCHASE_WPAF_APPLIED_META => 1
			) );
	}

	public static function set_ad_id( Group_Buying_Payment $payment ) {
		// Make sure WP Affiliate is installed
		if ( !function_exists( 'wp_aff_record_remote_click' ) )
			return;
		
		if ( isset( $_COOKIE['ap_id'] ) && $_COOKIE['ap_id'] != '' ) {
			$payment->set_source( $_COOKIE['ap_id'] );
		}
	}

	///////////////
	// Settings //
	///////////////

	/**
	 *
	 *
	 * @static
	 * @return string The ID of the payment settings page
	 */
	public static function get_settings_page() {
		return self::$settings_page;
	}

	///////////////
	// Get URLS //
	///////////////


	/**
	 * Get the URL for sharing a post
	 *
	 * @static
	 * @param int|null $postID
	 * @param string|null $member_login
	 * @param boolean|false $directlink
	 * @return string
	 */
	public static function get_share_link( $deal_id, $member_login = NULL, $directlink = FALSE ) {
		if ( NULL === $member_login ) {
			$current_user = wp_get_current_user();
			$member_login = ( !empty( $current_user->user_login ) ) ? $current_user->user_login : 'guest' ;
		}
		$permalink = get_permalink( $deal_id );
		if ( $directlink ) {
			return add_query_arg( array( 'socializer' => urlencode( $member_login ) ), $permalink );
		}

		if ( self::using_permalinks() ) {
			$post = get_post( $deal_id );
			$link = home_url( trailingslashit( self::$share_path ) . urlencode( $member_login ) . '/' .$post->post_name.'/' );
		} else {
			$link = add_query_arg( array( self::AFFILIATE_QUERY_ARG => urlencode( $member_login ) ), $permalink );
		}

		$link = self::maybe_short_share_url( $link, $member_login, $deal_id );
		return $link;
	}

	public static function is_bitly_active() {
		return self::$bitly_api != '' && self::$bitly_login != '';
	}

	public static function maybe_short_share_url( $url, $member_login, $deal_id, $refresh = FALSE ) {
		if ( self::is_bitly_active() ) {
			// Check transient cache
			$cache_key = 'gb_bitly_share_v2_'.$member_login.'_dealid_'.$deal_id;
			if ( !$refresh ) {
				$cache = get_transient( $cache_key );
				if ( !empty( $cache ) ) {
					return $cache;
				}
			}
			// Get short URL
			$url = self::get_short_url( $url );
			// set transient cache for a week.
			set_transient( $cache_key, $url, 604800 ); // cache for a week.
		}
		return $url;
	}

	public static function get_short_url( $url ) {
		if ( self::is_bitly_active() ) {
			$bitly = 'https://api-ssl.bitly.com/v3/shorten?&longUrl='.urlencode( $url ).'&login='.self::$bitly_login.'&apiKey='.self::$bitly_api.'&format=json';
			$raw_response = wp_remote_get( $bitly );
			if ( !$raw_response || is_wp_error( $raw_response ) ) {
				return $url;
			}
			$response = json_decode( wp_remote_retrieve_body( $raw_response ) );
			if ( $response->status_code == 200 ) {
				$url = $response->data->url;
			}
		}
		return $url;
	}

	public static function get_bitly_short_url_stats( $short_url ) {
		if ( self::is_bitly_active() ) {
			$bitly = 'https://api-ssl.bitly.com/v3/clicks?&shortUrl='.urlencode( $short_url ).'&login='.self::$bitly_login.'&apiKey='.self::$bitly_api.'&format=json';
			$raw_response = wp_remote_get( $bitly );
			if ( !$raw_response || is_wp_error( $raw_response ) ) {
				return FALSE;
			}
			$response = json_decode( wp_remote_retrieve_body( $raw_response ) );
			if ( $response->status_code == 200 ) {
				$data = $response->data;
				return $data;
			}
		}
		return FALSE;

	}

	public static function get_bitly_short_url_clicks( $short_url ) {
		if ( self::is_bitly_active() ) {
			$data = self::get_bitly_short_url_stats( $short_url );
			return $data->clicks[0]->global_clicks;
		}
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

	protected function __construct() { }

	public static function get_affiliate_credit() {
		return self::$affiliate_credit;
	}

	public static function get_affiliate_cookie_exp() {
		return self::$affiliate_cookie_exp;
	}
}
