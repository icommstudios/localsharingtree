<?php
/**
 * Paypal Adaptive Payments offsite payment processor.
 *
 * @package GBS
 * @subpackage Payment Processing_Processor
 */
class Group_Buying_Paypal_AP extends Group_Buying_Offsite_Processors {
	// Endpoints
	const API_ENDPOINT_SANDBOX = 'https://svcs.sandbox.paypal.com/AdaptivePayments';
	const API_ENDPOINT_LIVE = 'https://svcs.paypal.com/AdaptivePayments';
	const API_REDIRECT_SANDBOX = 'https://www.sandbox.paypal.com/webscr?';
	const API_REDIRECT_LIVE = 'https://www.paypal.com/webscr?';
	// mode
	const MODE_TEST = 'sandbox';
	const MODE_LIVE = 'live';
	const API_MODE_OPTION = 'gb_paypal_ap_mode';
	// credentials
	const API_USERNAME_OPTION = 'gb_paypal_ap_username';
	const API_SIGNATURE_OPTION = 'gb_paypal_ap_signature';
	const API_PASSWORD_OPTION = 'gb_paypal_ap_password';
	const APP_ID_OPTION = 'gb_paypal_ap_id';
	// token
	const TOKEN_KEY = 'gb_token_key'; // Combine with $blog_id to get the actual meta key
	// options
	const CANCEL_URL_OPTION = 'gb_paypal_cancel_url';
	const RETURN_URL_OPTION = 'gb_paypal_return_url';
	const CURRENCY_CODE_OPTION = 'gb_paypal_ap_currency';
	// gbs
	const PAYMENT_METHOD = 'PayPal AP';
	// vars
	protected static $instance;
	protected static $api_mode = self::MODE_TEST;
	private static $api_username;
	private static $api_password;
	private static $api_signature;
	private static $app_id;
	private static $cancel_url = '';
	private static $return_url = '';
	private static $currency_code = 'USD';

	// deal meta keys
	private static $meta_keys = array(
		'primary' => '_adaptive_primary', // string
		'secondary' => '_adaptive_secondary', // string
		'secondary_share' => '_adaptive_primary_share', // string
		'share_percentage' => '_adaptive_share_percentage', // string
	);

	/**
	 * instance
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( !( isset( self::$instance ) && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get the API endpoint to use
	 *
	 */
	private function get_api_url() {
		if ( self::$api_mode == self::MODE_LIVE ) {
			return self::API_ENDPOINT_LIVE;
		} else {
			return self::API_ENDPOINT_SANDBOX;
		}
	}

	/**
	 * Redirect url for preapprovals
	 *
	 */
	private function get_redirect_url() {
		if ( self::$api_mode == self::MODE_LIVE ) {
			return self::API_REDIRECT_LIVE;
		} else {
			return self::API_REDIRECT_SANDBOX;
		}
	}

	/**
	 * Payment method function for GBS
	 *
	 */
	public function get_payment_method() {
		return self::PAYMENT_METHOD;
	}

	/**
	 * Register payment method
	 *
	 */
	public static function register() {
		self::add_payment_processor( __CLASS__, self::__( 'PayPal Adaptive Payments' ) );
	}

	public static function returned_from_offsite() {
		return isset( $_GET['gb_checkout_action'] );
	}

	/**
	 * Set variables, add meta boxes to the deal page, process payments and setting payments.
	 */
	protected function __construct() {
		parent::__construct();
		// variables
		self::$api_username = get_option( self::API_USERNAME_OPTION );
		self::$api_password = get_option( self::API_PASSWORD_OPTION );
		self::$api_signature = get_option( self::API_SIGNATURE_OPTION );
		self::$app_id = get_option( self::APP_ID_OPTION, 'APP-80W284485P519543T' );
		self::$api_mode = get_option( self::API_MODE_OPTION, self::MODE_TEST );
		self::$currency_code = get_option( self::CURRENCY_CODE_OPTION, 'USD' );
		self::$cancel_url = get_option( self::CANCEL_URL_OPTION, Group_Buying_Carts::get_url() );
		self::$return_url = get_option( self::RETURN_URL_OPTION, add_query_arg( array( 'gb_checkout_action' => 'back_from_paypal' ), Group_Buying_Checkouts::get_url() ) );

		// deal
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );

		// payment options
		add_action( 'admin_init', array( $this, 'register_settings' ), 10, 0 );

		// Send offsite and handle the return
		add_action( 'gb_send_offsite_for_payment', array( $this, 'send_offsite' ), 10, 1 );
		add_action( 'gb_load_cart', array( $this, 'back_from_paypal' ), 10, 0 );

		// Remove the review page since it's at payfast
		add_filter( 'gb_checkout_pages', array( $this, 'remove_review_page' ) );

		// payment processing
		add_action( 'purchase_completed', array( $this, 'capture_purchase' ), 10, 1 );
		add_action( self::CRON_HOOK, array( $this, 'capture_pending_payments' ) );
		if ( self::DEBUG ) {
			add_action( 'init', array( $this, 'capture_pending_payments' ), 10000 );
		}
		// checkout controls customizations
		add_filter( 'gb_checkout_payment_controls', array( $this, 'payment_controls' ), 20, 2 );
	}

	/**
	 * The review page is unnecessary (or, rather, it's offsite)
	 *
	 * @param array   $pages
	 * @return array
	 */
	public function remove_review_page( $pages ) {
		unset( $pages[Group_Buying_Checkouts::REVIEW_PAGE] );
		return $pages;
	}

	/**
	 * Instead of redirecting to the GBS checkout page,
	 * set up the Preapproval and redirect there
	 *
	 * @param Group_Buying_Carts $cart
	 * @return void
	 */
	public function send_offsite( Group_Buying_Checkouts $checkout ) {
		$cart = $checkout->get_cart();
		if ( $cart->get_total() < 0.01 ) { // for free deals.
			return;
		}

		// Don't send someone returning away again.
		if ( $_REQUEST['gb_checkout_action'] == Group_Buying_Checkouts::PAYMENT_PAGE ) {

			// get Preapproval_API_Operation
			$response = self::get_preapproval( $checkout );

			// paying for it some other way
			if ( !$response || empty( $response ) ) {
				return;
			}

			// check to see if the api call was a success
			if ( 'SUCCESS' == strtoupper( $response['responseEnvelope_ack'] ) ) {
				self::set_token( urldecode( $response['preapprovalKey'] ) ); // Set the preapproval key
				$cmd = "cmd=_ap-preapproval&preapprovalkey=" . urldecode( $response['preapprovalKey'] );
				$redirect_url = self::get_redirect_url() . $cmd;
			} else {
				self::set_message( $response['error(0)_message'] , self::MESSAGE_STATUS_ERROR );
				$redirect_url = Group_Buying_Carts::get_url();
			}

			wp_redirect( $redirect_url, 303 );
			exit();
		}
	}

	/**
	 * Build the Pre Approval array then post it to PP API.
	 * https://www.paypalobjects.com/webstatic/en_US/developer/docs/pdf/pp_adaptivepayments.pdf p165
	 *
	 * @param  Group_Buying_Checkouts $checkout
	 * @return
	 */
	public static function get_preapproval( Group_Buying_Checkouts $checkout ) {
		$cart = $checkout->get_cart();

		$user = get_userdata( get_current_user_id() );
		$filtered_total = self::get_payment_request_total( $checkout );
		if ( $filtered_total < 0.01 ) {
			return array();
		}

		$fields = array();
		$fields['requestEnvelope.errorLanguage'] = apply_filters( 'gb_paypal_ap_errorlanuage', 'en_US' );
		$fields['requestEnvelope.detailLevel'] = 'ReturnAll';
		$fields['endingDate'] = date( 'c', current_time('timestamp')+apply_filters( 'gb_paypal_ap_endingperiod_for_preapproval', 7776000 ) );
		$fields['startingDate'] = date( 'c' );
		$fields['maxTotalAmountOfAllPayments'] = gb_get_number_format( $filtered_total );
		$fields['currencyCode'] = self::get_currency_code();
		$fields['cancelUrl'] = self::$cancel_url;
		$fields['returnUrl'] = self::$return_url;
		$fields['ipnNotificationUrl'] = Group_Buying_Offsite_Processor_Handler::get_url();
		$fields['clientDetails'] = get_current_user_id();
		// added in 4.5
		$fields['displayMaxTotalAmount'] = TRUE;
		$fields['requireInstantFundingSource'] = TRUE;

		// Build an array of the product titles so they can be comma seperated below.
		$item_array = array();
		foreach ( $cart->get_products() as $item ) {
			$item_array[] = get_the_title( $item['deal_id'] );
		}
		// memo
		$fields['memo'] = self::__('Item(s) your Pre-Approving Payment: ') . implode( ', ', $item_array );

		$response = self::remote_post( 'Preapproval', $fields );

		if ( self::DEBUG ) {
			error_log( '----------PayPal EC Preapproval Response----------' );
			error_log( print_r( $response, TRUE ) );
		}

		return $response;
	}

	/**
	 * We're on the checkout page, just back from PayPal.
	 * Unset the token if they're not returning from paypal
	 *
	 * @return void
	 */
	public function back_from_paypal() {
		if ( self::returned_from_offsite() ) {
			// let the checkout know that this isn't a fresh start
			$_REQUEST['gb_checkout_action'] = 'back_from_paypal';
		} elseif ( !isset( $_REQUEST['gb_checkout_action'] ) ) {
			// this is a new checkout. clear the token so we don't give things away for free
			self::unset_token();
		}
	}


	////////////////////////////////////////////////////////
	// back from paypal and ready to process the payment //
	////////////////////////////////////////////////////////

	/**
	 * Process a payment
	 *
	 * @param Group_Buying_Checkouts $checkout
	 * @param Group_Buying_Purchase $purchase
	 * @return Group_Buying_Payment|bool FALSE if the payment failed, otherwise a Payment object
	 */
	public function process_payment( Group_Buying_Checkouts $checkout, Group_Buying_Purchase $purchase ) {

		if ( $purchase->get_total( self::get_payment_method() ) < 0.01 ) {
			// Nothing to do here, another payment handler intercepted and took care of everything
			// See if we can get that payment and just return it
			$payments = Group_Buying_Payment::get_payments_for_purchase( $purchase->get_id() );
			foreach ( $payments as $payment_id ) {
				$payment = Group_Buying_Payment::get_instance( $payment_id );
				return $payment;
			}
		}

		// get PreapprovalDetails_API_Operation
		$response = self::get_preapproval_detail( $checkout, $purchase );


		if ( 'SUCCESS' != strtoupper( $response['responseEnvelope_ack'] ) ) {
			self::set_message( $response['error(0)_message'], self::MESSAGE_STATUS_ERROR );
			return FALSE;
		}

		// create loop of deals for the payment post
		$deal_info = array();
		foreach ( $purchase->get_products() as $item ) {
			if ( isset( $item['payment_method'][self::get_payment_method()] ) ) {
				if ( !isset( $deal_info[$item['deal_id']] ) ) {
					$deal_info[$item['deal_id']] = array();
				}
				$deal_info[$item['deal_id']][] = $item;
			}
		}
		if ( isset( $checkout->cache['shipping'] ) ) {
			$shipping_address = array();
			$shipping_address['first_name'] = $checkout->cache['shipping']['first_name'];
			$shipping_address['last_name'] = $checkout->cache['shipping']['last_name'];
			$shipping_address['street'] = $checkout->cache['shipping']['street'];
			$shipping_address['city'] = $checkout->cache['shipping']['city'];
			$shipping_address['zone'] = $checkout->cache['shipping']['zone'];
			$shipping_address['postal_code'] = $checkout->cache['shipping']['postal_code'];
			$shipping_address['country'] = $checkout->cache['shipping']['country'];
		}

		// Send preapproval key with api response
		$response['preapproval_key'] = self::get_token();

		// create new payment
		$payment_id = Group_Buying_Payment::new_payment( array(
				'payment_method' => self::get_payment_method(),
				'purchase' => $purchase->get_id(),
				'amount' => $response['max_total_amount_of_all_payments'],
				'data' => array(
					'api_response' => $response,
					'uncaptured_deals' => $deal_info
				),
				'deals' => $deal_info,
				'shipping_address' => $shipping_address,
			), Group_Buying_Payment::STATUS_AUTHORIZED );
		if ( !$payment_id ) {
			return FALSE;
		}
		$payment = Group_Buying_Payment::get_instance( $payment_id );
		do_action( 'payment_authorized', $payment );

		// remove token so that user can purchase again.
		self::unset_token();

		// finalize
		return $payment;
	}

	public static function get_preapproval_detail( Group_Buying_Checkouts $checkout, Group_Buying_Purchase $purchase ) {
		$fields = array();
		$fields['requestEnvelope.errorLanguage'] = apply_filters( 'gb_paypal_ap_errorlanuage', 'en_US' );
		$fields['requestEnvelope.detailLevel'] = 'ReturnAll';
		$fields['preapprovalKey'] = self::get_token();

		$response = self::remote_post( 'PreapprovalDetails', $fields );

		if ( self::DEBUG ) {
			error_log( '----------PayPal EC PreapprovalDetails Response----------' );
			error_log( print_r( $response, TRUE ) );
		}

		return $response;
	}

	////////////////////////////
	// Post Purchase Methods //
	////////////////////////////

	/**
	 * Capture a pre-authorized payment
	 *
	 * @param Group_Buying_Purchase $purchase
	 * @return void
	 */
	public function capture_purchase( Group_Buying_Purchase $purchase ) {
		$payments = Group_Buying_Payment::get_payments_for_purchase( $purchase->get_id() );
		foreach ( $payments as $payment_id ) {
			$payment = Group_Buying_Payment::get_instance( $payment_id );
			$this->capture_payment( $payment );
		}
	}

	/**
	 * Try to capture all pending payments
	 *
	 * @return void
	 */
	public function capture_pending_payments() {
		// Filter the post query so that it returns only payments in the last 90 days
		add_filter( 'posts_where', array( __CLASS__, 'filter_where' ) );
		$payments = Group_Buying_Payment::get_pending_payments( self::get_payment_method(), FALSE );
		remove_filter( 'posts_where', array( __CLASS__, 'filter_where' ) );

		foreach ( $payments as $payment_id ) {
			$payment = Group_Buying_Payment::get_instance( $payment_id );
			$this->capture_payment( $payment );
		}
	}

	public function capture_payment( Group_Buying_Payment $payment ) {

		// is this the right payment processor? does the payment still need processing?
		if ( $payment->get_payment_method() == self::get_payment_method() && $payment->get_status() != Group_Buying_Payment::STATUS_COMPLETE ) {
			$data = $payment->get_data();

			if ( isset( $data['api_response']['preapproval_key'] ) && $data['api_response']['preapproval_key'] ) {

				// items we need to capture
				$items_to_capture = $this->items_to_capture( $payment );

				if ( $items_to_capture ) {

					if ( self::DEBUG ) {
						error_log( '----------PayPal AP items_to_capture ----------' );
						error_log( "items_to_capture: " . print_r( $items_to_capture, true ) );
					}

					// if not set create an array
					if ( !isset( $data['capture_response'] ) ) {
						$data['capture_response'] = array();
					}

					// Get Quantities
					$item_quantities = array();
					$purchase = Group_Buying_Purchase::get_instance( $payment->get_purchase() );
					foreach ( $purchase->get_products() as $item ) {
						$item_quantities[$item['deal_id']] += $item['quantity'];
					}

					$payment_captured = FALSE;
					foreach ( $items_to_capture as $deal_id => $amount ) {
						// capture the payment individually since each capture depends on deal meta
						$tracking_id = $payment->get_ID().$deal_id;
						// Get the payment
						$response = self::get_payment( $payment, $deal_id, $amount, $item_quantities[$deal_id], $data['api_response']['preapproval_key'], $tracking_id );

						if ( self::DEBUG ) {
							error_log( '----------PayPal AP Capture Cal Pay ----------' );
							error_log( "deal_id: " . print_r( $deal_id, true ) );
							error_log( "response: " . print_r( $response, true ) );
						}

						// check if response is returns a success response
						if ( 'SUCCESS' == strtoupper( $response['responseEnvelope_ack'] ) ) {
							// make sure the payment status is completed
							if ( 'COMPLETED' == strtoupper( $response["paymentExecStatus"] ) ) {
								$payment_captured = TRUE;
								unset( $data['uncaptured_deals'][$deal_id] );
							}
						}

						$response['tracking_id_var'] = $tracking_id;
						// set new response
						$data['capture_response'][] = $response;
					}

					// Set data regardless if a successful payment was captured this time around.
					$payment->set_data( $data );

					if ( $payment_captured ) {
						do_action( 'payment_captured', $payment, array_keys( $items_to_capture ) );

						// Set the status
						if ( count( $data['uncaptured_deals'] ) < 1 ) {
							$payment->set_status( Group_Buying_Payment::STATUS_COMPLETE );
							do_action( 'payment_complete', $payment );
						} else {
							$payment->set_status( Group_Buying_Payment::STATUS_PARTIAL );
						}
					}
				}
			}
		}
	}


	private function get_payment( $payment, $deal_id, $amount, $qty = 1, $pre_app_key = '', $tracking_id = 0 ) {
		$payment_data = $payment->get_data();
		$secondary_share_per = self::get_secondary_share( $deal_id );
		$is_share_percentage = self::is_share_percentage( $deal_id );

		if ( $is_share_percentage ) {
			// base share off a percentage of all items sold
			$secondary_share = ( $amount )*( $secondary_share_per*0.01 );
			$subtotal = $amount - $secondary_share;
		}
		else {
			// base share off all items sold
			$secondary_share = $secondary_share_per*$qty;
			$subtotal = $amount - $secondary_share;
		}
		$fields = array();
		$fields['actionType'] = 'PAY';
		$fields['requestEnvelope.errorLanguage'] = apply_filters( 'gb_paypal_ap_errorlanuage', 'en_US' );
		$fields['requestEnvelope.detailLevel'] = 'ReturnAll';
		$fields['currencyCode'] = self::get_currency_code();
		$fields['preapprovalKey'] = $pre_app_key;
		$fields['trackingId'] = $tracking_id;
		$fields['cancelUrl'] = self::$cancel_url;
		$fields['returnUrl'] = self::$return_url;
		$fields['reverseAllParallelPaymentsOnError'] = 'false';

		// Primary payer
		$fields['receiverList.receiver(0).email'] = self::get_primary( $deal_id );
		// check if chained or parallel payments
		if ( !apply_filters( 'gb_paypal_ap_use_parallel_payments', FALSE ) ) {
			$fields['receiverList.receiver(0).amount'] = apply_filters( 'gb_paypal_ap_primary_receiver_amount', number_format( floatval( $amount ), 2 ), $payment, $deal_id, $amount, $qty, $pre_app_key, $tracking_id ); // since chained the entire amount is sent to the primary receiver
			$fields['receiverList.receiver(0).primary'] = 'true';
		}
		else { // Using Parallel Payments
			if ( self::DEBUG ) {
				error_log( '----------PayPal AP Using Parallel Payments ----------' );
			}
			$fields['receiverList.receiver(0).amount'] = apply_filters( 'gb_paypal_ap_primary_receiver_amount', number_format( floatval( $subtotal ), 2 ), $payment, $deal_id, $amount, $qty, $pre_app_key, $tracking_id ); // since parallel the primary receiver gets the subtotal
			$fields['receiverList.receiver(0).primary'] = 'false';
		}

		// secondary payer
		$fields['receiverList.receiver(1).email'] = self::get_secondary( $deal_id );
		$fields['receiverList.receiver(1).amount'] = apply_filters( 'gb_paypal_ap_secondary_receiver_amount', number_format( floatval( $secondary_share ), 2 ), $payment, $deal_id, $amount, $qty, $pre_app_key, $tracking_id );
		$fields['receiverList.receiver(1).primary'] = 'false';

		$fields = apply_filters( 'gb_paypal_ap_nvpst', $fields, $payment, $deal_id, $amount, $qty, $pre_app_key, $tracking_id );

		if ( self::DEBUG ) {
			error_log( '----------PayPal AP Get Payment ----------' );
			error_log( "call: " . print_r( $fields, true ) );
		}
		// Make the call
		$response = self::remote_post( 'Pay', $fields );

		if ( self::DEBUG ) {
			error_log( '----------PayPal EC Get Payment Response----------' );
			error_log( print_r( $response, TRUE ) );
		}

		// Return response
		return $response;
	}


	///////////////
	// Utilities //
	///////////////

	/**
	 * Remote post function
	 *
	 * @param string  $method_name Method for the endpoint
	 * @param array   $post_array  body/post
	 * @return array
	 */
	private function remote_post( $method_name = 'Preapproval', $post_array = array() ) {
		$url = self::get_api_url().'/'.$method_name;
		$post_string = self::make_nvp( $post_array );
		$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'headers' => array(
					'X-PAYPAL-REQUEST-DATA-FORMAT' => 'NV',
					'X-PAYPAL-RESPONSE-DATA-FORMAT' => 'NV',
					'X-PAYPAL-SECURITY-USERID' => self::$api_username,
					'X-PAYPAL-SECURITY-PASSWORD' => self::$api_password,
					'X-PAYPAL-SECURITY-SIGNATURE' => self::$api_signature,
					'X-PAYPAL-SERVICE-VERSION' => '1.3.0',
					'X-PAYPAL-APPLICATION-ID' => self::$app_id
				),
				'body' => apply_filters( 'gb_paypal_ap_remote_post_post', $post_string, $method_name, $post_array ),
				'timeout' => apply_filters( 'http_request_timeout', 15 ),
				'sslverify' => false
			) );

		if ( self::DEBUG ) {
			error_log( '----------PayPal AP Remote Post ----------' );
			error_log( print_r( $response, TRUE ) );
		}

		if ( is_wp_error( $response ) ) {
			return FALSE;
		}

		return wp_parse_args( wp_remote_retrieve_body( $response ) );
	}


	/**
	 * Convert an associative array into an NVP string
	 *
	 * @param array   Associative array to create NVP string from
	 * @param string  Used to separate arguments (defaults to &)
	 *
	 * @return string NVP string
	 */
	public function make_nvp( $reqArray, $sep = '&' ) {
		if ( !is_array( $reqArray ) ) {
			return $reqArray;
		}
		return http_build_query( $reqArray, '', $sep );
	}

	/**
	 * get the currency code, which is filtered
	 *
	 */
	private function get_currency_code() {
		return apply_filters( 'gb_paypal_wpp_currency_code', self::$currency_code );
	}

	public static function set_token( $token ) {
		global $blog_id;
		update_user_meta( get_current_user_id(), $blog_id.'_'.self::TOKEN_KEY, $token );
	}

	public static function unset_token() {
		global $blog_id;
		delete_user_meta( get_current_user_id(), $blog_id.'_'.self::TOKEN_KEY );
	}

	public static function get_token() {
		global $blog_id;
		return get_user_meta( get_current_user_id(), $blog_id.'_'.self::TOKEN_KEY, TRUE );
	}


	/////////////
	// Options //
	/////////////


	public function register_settings() {
		$page = Group_Buying_Payment_Processors::get_settings_page();
		$section = 'gb_paypalwpp_settings';
		add_settings_section( $section, self::__( 'PayPal Adaptive Payments' ), array( $this, 'display_settings_section' ), $page );
		register_setting( $page, self::API_MODE_OPTION );
		register_setting( $page, self::API_USERNAME_OPTION );
		register_setting( $page, self::API_PASSWORD_OPTION );
		register_setting( $page, self::API_SIGNATURE_OPTION );
		register_setting( $page, self::APP_ID_OPTION );
		register_setting( $page, self::CURRENCY_CODE_OPTION );
		register_setting( $page, self::RETURN_URL_OPTION );
		register_setting( $page, self::CANCEL_URL_OPTION );
		add_settings_field( self::API_MODE_OPTION, self::__( 'Mode' ), array( $this, 'display_api_mode_field' ), $page, $section );
		add_settings_field( self::API_USERNAME_OPTION, self::__( 'API Username' ), array( $this, 'display_api_username_field' ), $page, $section );
		add_settings_field( self::API_PASSWORD_OPTION, self::__( 'API Password' ), array( $this, 'display_api_password_field' ), $page, $section );
		add_settings_field( self::API_SIGNATURE_OPTION, self::__( 'API Signature' ), array( $this, 'display_api_signature_field' ), $page, $section );
		add_settings_field( self::APP_ID_OPTION, self::__( 'Application ID' ), array( $this, 'display_app_id_field' ), $page, $section );
		add_settings_field( self::CURRENCY_CODE_OPTION, self::__( 'Currency Code' ), array( $this, 'display_currency_code_field' ), $page, $section );
		add_settings_field( self::RETURN_URL_OPTION, self::__( 'Return URL' ), array( $this, 'display_return_field' ), $page, $section );
		add_settings_field( self::CANCEL_URL_OPTION, self::__( 'Cancel URL' ), array( $this, 'display_cancel_field' ), $page, $section );
	}

	public function display_api_username_field() {
		echo '<input type="text" name="'.self::API_USERNAME_OPTION.'" value="'.self::$api_username.'" size="80" />';
	}

	public function display_api_password_field() {
		echo '<input type="text" name="'.self::API_PASSWORD_OPTION.'" value="'.self::$api_password.'" size="80" />';
	}

	public function display_api_signature_field() {
		echo '<input type="text" name="'.self::API_SIGNATURE_OPTION.'" value="'.self::$api_signature.'" size="80" />';
	}

	public function display_app_id_field() {
		echo '<input type="text" name="'.self::APP_ID_OPTION.'" value="'.self::$app_id.'" size="80" />';
	}

	public function display_return_field() {
		echo '<input type="text" name="'.self::RETURN_URL_OPTION.'" value="'.self::$return_url.'" size="80" />';
	}

	public function display_cancel_field() {
		echo '<input type="text" name="'.self::CANCEL_URL_OPTION.'" value="'.self::$cancel_url.'" size="80" />';
	}

	public function display_api_mode_field() {
		echo '<label><input type="radio" name="'.self::API_MODE_OPTION.'" value="'.self::MODE_LIVE.'" '.checked( self::MODE_LIVE, self::$api_mode, FALSE ).'/> '.self::__( 'Live' ).'</label><br />';
		echo '<label><input type="radio" name="'.self::API_MODE_OPTION.'" value="'.self::MODE_TEST.'" '.checked( self::MODE_TEST, self::$api_mode, FALSE ).'/> '.self::__( 'Sandbox' ).'</label>';
	}

	public function display_currency_code_field() {
		echo '<input type="text" name="'.self::CURRENCY_CODE_OPTION.'" value="'.self::$currency_code.'" size="5" />';
	}

	////////////////
	// Meta boxes //
	////////////////

	public static function add_meta_boxes() {
		add_meta_box( 'gb_adaptive_payments', self::__( 'Adaptive Payments' ), array( get_class(), 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'advanced', 'high' );
	}

	public static function show_meta_box( $post, $metabox ) {
		$deal = Group_Buying_Deal::get_instance( $post->ID );
		self::show_adaptive_meta_box( $deal, $post, $metabox );
	}

	/**
	 * Display the deal adaptive payment meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	public static function show_adaptive_meta_box( Group_Buying_Deal $deal, $post, $metabox ) {
		$primary = self::get_primary( $post->ID );
		$secondary = self::get_secondary( $post->ID );
		$secondary_share = self::get_secondary_share( $post->ID );
		$is_share_percentage = self::is_share_percentage( $post->ID );

		include dirname( __FILE__ ) .  '/meta-boxes/deal-adaptive-payments.php';
	}

	public static function save_meta_box( $post_id, $post ) {
		// only continue if it's a deal post
		if ( $post->post_type != Group_Buying_Deal::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		// save all the meta boxes
		$deal = Group_Buying_Deal::get_instance( $post_id );
		self::save_adaptive_meta_box( $deal, $post_id, $post );
	}

	/**
	 * Save the deal adaptive payment meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	private static function save_adaptive_meta_box( Group_Buying_Deal $deal, $post_id, $post ) {
		$primary = isset( $_POST['adaptive_primary'] ) ? $_POST['adaptive_primary'] : '';
		self::set_primary( $post_id, $primary, $deal );

		$secondary = isset( $_POST['adaptive_secondary'] ) ? $_POST['adaptive_secondary'] : '';
		self::set_secondary( $post_id, $secondary, $deal );

		$is_share_percentage = ( isset( $_POST['adaptive_share_percentage'] ) && $_POST['adaptive_share_percentage'] == '1' ) ? 1 : 0;
		self::set_share_percentage( $post_id, $is_share_percentage, $deal );

		$secondary_share = isset( $_POST['adaptive_secondary_share'] ) ? $_POST['adaptive_secondary_share'] : '';
		self::set_secondary_share( $post_id, $secondary_share, $deal );
	}

	public function set_primary( $post_id, $primary , Group_Buying_Deal $deal ) {
		update_post_meta( $post_id, self::$meta_keys['primary'], $primary );
		return $primary;
	}

	public function get_primary( $post_id, $primary = NULL ) {
		$primary = get_post_meta( $post_id, self::$meta_keys['primary'], true );
		return $primary;
	}

	public function set_secondary( $post_id, $secondary, Group_Buying_Deal $deal ) {
		update_post_meta( $post_id, self::$meta_keys['secondary'], $secondary );
		return $secondary;
	}

	public function get_secondary( $post_id, $secondary = NULL ) {
		$secondary = get_post_meta( $post_id, self::$meta_keys['secondary'], true );
		return $secondary;
	}

	public function set_share_percentage( $post_id, $is_share_percentage = FALSE, Group_Buying_Deal $deal ) {
		update_post_meta( $post_id, self::$meta_keys['share_percentage'], $is_share_percentage );
		return $share_percentage;
	}

	public function is_share_percentage( $post_id, $is_share_percentage = FALSE ) {
		$is_share_percentage = (bool) get_post_meta( $post_id, self::$meta_keys['share_percentage'], true );
		return $is_share_percentage;
	}

	public function set_secondary_share( $post_id, $secondary_share, Group_Buying_Deal $deal ) {
		$is_percentage = self::is_share_percentage( $post_id );
		if ( $is_percentage && $secondary_share > 50 ) {
			$secondary_share = 50;
		}
		elseif ( !$is_percentage && $deal->get_price() < $secondary_share ) {
			$secondary_share = $deal->get_price();
		}
		update_post_meta( $post_id, self::$meta_keys['secondary_share'], $secondary_share );
		return $secondary_share;
	}

	public function get_secondary_share( $post_id, $secondary_share = NULL ) {
		$secondary_share = get_post_meta( $post_id, self::$meta_keys['secondary_share'], true );
		return apply_filters( 'gb_paypal_ap_get_secondary_share', $secondary_share, $post_id );
	}

	//////////////
	// Filters //
	//////////////

	public static function checkout_icon() {
		return '<img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" title="Paypal Payments" id="paypal_icon"/>';
	}

	public function payment_controls( $controls, Group_Buying_Checkouts $checkout ) {
		if ( isset( $controls['review'] ) ) {
			$style = 'style="box-shadow: none;-moz-box-shadow: none;-webkit-box-shadow: none; display: block; width: 145px; height: 42px; background-color: transparent; background-image: url(https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif); background-position: 0 0; padding: 42px 0 0 0; border: none; cursor: pointer; text-indent: -9000px; margin-top: 12px;"';
			$controls['review'] = str_replace( 'value="'.self::__( 'Review' ).'"', $style . ' value="'.self::__( 'Paypal' ).'"', $controls['review'] );
		}
		return $controls;
	}


	public function filter_where( $where = '' ) {
		// posts 90 days old
		$where .= " AND post_date >= '" . date('Y-m-d', current_time('timestamp')-apply_filters( 'gb_paypal_ap_endingperiod_for_preapproval', 7776000 ) ) . "'";
		return $where;
	}
}
Group_Buying_Paypal_AP::register();