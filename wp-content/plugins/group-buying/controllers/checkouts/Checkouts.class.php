<?php

/**
 * Checkout Controller
 *
 * @package GBS
 * @subpackage Checkout
 */
class Group_Buying_Checkouts extends Group_Buying_Controller {
	const CHECKOUT_PATH_OPTION = 'gb_checkout_path';
	const CHECKOUT_QUERY_VAR = 'gb_show_checkout';
	const CACHE_META_KEY = 'gb_checkout_cache'; // Combine with $blog_id to get the actual meta key
	const PAYMENT_PAGE = 'payment';
	const REVIEW_PAGE = 'review';
	const CONFIRMATION_PAGE = 'confirmation';
	const USE_SSL_OPTION = 'gb_checkout_use_ssl';
	private static $checkout_path = 'checkout';
	private static $checkout_controller = NULL;
	private static $use_ssl = TRUE;
	private $pages = array();
	private $current_page = '';
	private $cart; /** @var Group_Buying_Cart */
	private $payment_processor;
	private $checkout_complete = FALSE;
	public $cache = array();


	public static function init() {
		self::$use_ssl = get_option( self::USE_SSL_OPTION, FALSE );
		self::$checkout_path = get_option( self::CHECKOUT_PATH_OPTION, self::$checkout_path );
		self::register_settings();
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_checkout_callback' ), 10, 1 );

		add_filter( 'gbs_require_ssl', array( get_class(), 'require_ssl_on_checkout_pages' ), 10, 2 );
		add_filter( 'gb_get_form_field', array( get_class(), 'filter_required_attribute' ), 10, 4 );
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		// Settings
		$settings = array(
			'gb_url_path_checkout_path' => array(
				'weight' => 110,
				'settings' => array(
					self::CHECKOUT_PATH_OPTION => array(
						'label' => self::__( 'Checkout Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ),
							'type' => 'text',
							'default' => self::$checkout_path
							)
						)
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_UI::SETTINGS_PAGE );

		// Settings
		$settings = array(
			'gb_checkout_use_ssl' => array(
				'title' => self::__('Advanced Settings'),
				'weight' => 10000,
				'settings' => array(
					self::USE_SSL_OPTION => array(
						'label' => self::__( 'Force SSL on Checkout Pages' ),
						'option' => array(
							'label' => self::__('Advanced: SSL is highly recommended for production sites accepting credit cards.'),
							'type' => 'checkbox',
							'default' => self::$use_ssl,
							'value' => 'TRUE',
							'description' => self::__('GBS recommends changing your site address scheme to https:// instead of redirecting users on checkout to an SSL connection. Improperly setting up your server can cause adverse affects, including a redirect loop.')
							)
						)
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_Payment_Processors::SETTINGS_PAGE );
	}

	/**
	 * Register the path callback
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_checkout_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$checkout_path,
			'title' => 'Checkout',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_checkout_page' ),
			'access_callback' => array( get_class(), 'maybe_checkout_login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$checkout_path ).'.php',
				self::get_template_path().'/checkout.php', // theme override
				GB_PATH.'/views/public/checkout.php', // default
			),
		);
		$router->add_route( self::CHECKOUT_QUERY_VAR, $args );
	}


	/**
	 * We're on the checkout page. Time to auto-instantiate!
	 *
	 * @static
	 * @return void
	 */
	public static function on_checkout_page() {
		$checkout_page = self::get_instance();
		// View template
		$checkout_page->view_checkout();
	}

	public function maybe_checkout_login_required() {
		if ( apply_filters( 'checkout_login_required', FALSE ) ) {
			return self::login_required();
		}
		return TRUE;
	}

	/**
	 * Filter the gb_get_form_field to remove the "required" attribute since the form process is handling it.
	 * Causes issues with logins and credits too.
	 *
	 */
	function filter_required_attribute( $field, $key, $data, $category ) {
		if ( self::is_checkout_page() ) {
			return str_replace( 'required', '', $field );
		}
		return $field;
	}

	public static function require_ssl_on_checkout_pages( $required, WP $wp ) {
		if ( self::$use_ssl && self::is_checkout_page() ) {
			return TRUE;
		}
		return $required;
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
		if ( !( self::$checkout_controller && is_a( self::$checkout_controller, __CLASS__ ) ) ) {
			self::$checkout_controller = new self();
		}
		return self::$checkout_controller;
	}

	private function __construct() {
		self::do_not_cache(); // never cache the checkout pages
		
		wp_enqueue_script( 'gb_frontend_checkout' );
	
		$this->load_cache();
		$this->load_cart();
		$this->payment_processor = Group_Buying_Payment_Processors::get_payment_processor();
		$this->load_pages();
		$this->handle_action( isset($_REQUEST['gb_checkout_action'])?$_REQUEST['gb_checkout_action']:'' );
	}


	/**
	 * Update the global $pages array with the HTML for the current checkout page
	 *
	 * @static
	 * @param object  $post
	 * @return void
	 */
	public function view_checkout() {
		remove_filter( 'the_content', 'wpautop' );
		$panes = apply_filters( 'gb_checkout_panes_'.$this->current_page, array(), $this );
		$panes = apply_filters( 'gb_checkout_panes', $panes, $this );
		uasort( $panes, array( get_class(), 'sort_by_weight' ) );
		self::load_view( 'checkout/form', array(
				'panes' => $panes,
				'current_page' => $this->current_page,
			) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a account page
	 */
	public static function is_checkout_page() {
		return GB_Router_Utility::is_on_page( self::CHECKOUT_QUERY_VAR );
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
		$title = self::__( 'Checkout' );
		$checkout = self::get_instance();
		if ( isset( $checkout->pages[$checkout->current_page]['title'] ) ) {
			$title .= ': '.$checkout->pages[$checkout->current_page]['title'];
		}
		return $title;
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the checkout page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url( '', self::$use_ssl?'https':NULL ) ).trailingslashit( self::$checkout_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::CHECKOUT_QUERY_VAR, self::$use_ssl );
		}
	}

	private function handle_action( $action ) {
		// do the callback for the just-submitted checkout page
		if ( $action ) {
			if ( !$this->checkout_complete ) {
				// save state in case we're redirected elsewhere
				add_filter( 'wp_redirect', array( $this, 'save_cache_on_redirect' ), 10, 1 );

				// The action callback for the page should add data to the cache as necessary,
				// in addition to any other processing it needs to do.
				// Under no circumstances should credit card information be stored in the cache.
				do_action( 'gb_checkout_action_'.strtolower( $action ), $this );
				do_action( 'gb_checkout_action', $action, $this );
				$this->save_cache();
			}
			$current = $this->get_current_page();
			if ( $current == self::CONFIRMATION_PAGE && !$this->checkout_complete ) {
				$this->complete_checkout();
			}
		} else {
			// we're starting over. Clear any cached checkout data
			$this->clear_cache();
			$this->get_current_page();
		}
	}

	private function load_pages() {
		$pages = array(
			self::PAYMENT_PAGE => array(
				'title' => self::__( 'Payment' ),
				'weight' => 10,
			),
			self::REVIEW_PAGE => array(
				'title' => self::__( 'Review' ),
				'weight' => 1000,
			),
			self::CONFIRMATION_PAGE => array(
				'title' => self::__( 'Confirmation' ),
				'weight' => PHP_INT_MAX, // this must go last
			),
		);
		$this->pages = apply_filters( 'gb_checkout_pages', $pages );
		$this->pages[self::CONFIRMATION_PAGE]['weight'] = PHP_INT_MAX; // in case anything stupid happened
		uasort( $pages, array( $this, 'sort_by_weight' ) );

		$this->register_payment_page();
		$this->register_review_page();
		$this->register_confirmation_page();
	}

	/**
	 * Load all data cached from previous checkout pages
	 *
	 * @return void
	 */
	private function load_cache() {
		global $blog_id;
		$this->cache = get_user_meta( get_current_user_id(), $blog_id.'_'.self::CACHE_META_KEY, TRUE );
		if ( !is_array( $this->cache ) ) {
			$this->cache = array();
		}
		if ( isset( $this->cache['purchase_id'] ) && $this->cache['purchase_id'] ) {
			$this->checkout_complete = TRUE;
		}
	}

	/**
	 * Save all data from submitted checkout pages
	 *
	 * @return void
	 */
	private function save_cache() {
		global $blog_id;
		update_user_meta( get_current_user_id(), $blog_id.'_'.self::CACHE_META_KEY, $this->cache );
	}

	public function save_cache_on_redirect( $location ) {
		$this->save_cache();
		return $location;
	}

	/**
	 * Clear out all data from checkout
	 *
	 * @return void
	 */
	private function clear_cache() {
		global $blog_id;
		update_user_meta( get_current_user_id(), $blog_id.'_'.self::CACHE_META_KEY, array() );
		$this->cache = array();
	}

	private function load_cart() {
		$this->cart = Group_Buying_Cart::get_instance();
		if ( $this->cart->is_empty() && !$this->checkout_complete ) {
			self::set_message( self::__( 'Your cart is empty.' ) );
			wp_redirect( Group_Buying_Carts::get_url(), 303 );
			exit();
		}
		do_action( 'gb_load_cart', $this, $this->cart );
	}

	/**
	 *
	 *
	 * @return Group_Buying_Cart
	 */
	public function get_cart() {
		if ( $this->cart ) {
			return $this->cart;
		} else {
			return NULL;
		}
	}

	/**
	 * Sets $this->current_page to the next incomplete page
	 *
	 * @return string The current page key
	 */
	private function set_current_page() {
		if ( isset( $this->cache['completed'] ) && is_array( $this->cache['completed'] ) ) {
			foreach ( $this->pages as $key => $info ) {
				// get the next page that is not completed
				if ( !$this->is_page_complete( $key ) ) {
					$this->current_page = $key;
					return $this->current_page;
				}
			}
		} else {
			$this->current_page = array_shift( array_keys( $this->pages ) );
			return $this->current_page;
		}
	}

	/**
	 *
	 *
	 * @param bool    $reload Whether to check the page cache again
	 * @return string The key of the current page
	 */
	public function get_current_page( $reload = FALSE ) {
		if ( !$this->current_page || $reload ) {
			$this->set_current_page();
		}
		return $this->current_page;
	}

	/**
	 * Add the given page to the completed array
	 *
	 * @param string  $page
	 * @return void
	 */
	public function mark_page_complete( $page ) {
		if ( !isset( $this->cache['completed'] ) || !is_array( $this->cache['completed'] ) ) {
			$this->cache['completed'] = array();
		}
		if ( !in_array( $page, $this->cache['completed'] ) ) {
			$this->cache['completed'][] = $page;
		}
	}

	/**
	 * Remove the given page (and all following pages) from the
	 * completed pages array
	 *
	 * @param string  $page
	 * @return void
	 */
	public function mark_page_incomplete( $page ) {
		// if there are no completed page, there's nothing to do
		if ( !isset( $this->cache['completed'] ) || !is_array( $this->cache['completed'] ) ) {
			$this->cache['completed'] = array();
			return;
		}

		// get a list of page keys before the incomplete $page
		$keys = array_keys( $this->pages );
		$position = array_search( $page, $keys );
		$keep = array_slice( $keys, 0, $position );

		// remove the offending keys
		$this->cache['completed'] = array_intersect( $keep, $this->cache['completed'] );
	}

	/**
	 * Check if the given page is in the completed pages array
	 *
	 * @param string  $page
	 * @return bool
	 */
	public function is_page_complete( $page ) {
		if ( !isset( $this->cache['completed'] ) || !is_array( $this->cache['completed'] ) ) {
			$this->cache['completed'] = array();
		}
		return in_array( $page, $this->cache['completed'] );
	}

	/**
	 * Register action hooks for displaying and processing the payment page
	 *
	 * @return void
	 */
	private function register_payment_page() {
		add_filter( 'gb_checkout_panes_'.self::PAYMENT_PAGE, array( $this, 'display_payment_page' ), 0, 2 );
		add_action( 'gb_checkout_action_'.self::PAYMENT_PAGE, array( $this, 'process_payment_page' ), 10, 1 );
	}

	/**
	 * Register action hooks for displaying and processing the payment page
	 *
	 * @return void
	 */
	private function register_review_page() {
		add_filter( 'gb_checkout_panes_'.self::REVIEW_PAGE, array( $this, 'display_review_page' ), 0, 2 );
		add_action( 'gb_checkout_action_'.self::REVIEW_PAGE, array( $this, 'process_review_page' ), 10, 1 );
	}

	/**
	 * Register action hooks for displaying the confirmation page
	 *
	 * @return void
	 */
	private function register_confirmation_page() {
		add_filter( 'gb_checkout_panes_'.self::CONFIRMATION_PAGE, array( $this, 'display_confirmation_page' ), 0, 2 );
		// No action to process. This is the last page.
	}

	/**
	 * Display the payment form
	 *
	 * @return array
	 */
	public function display_payment_page( $panes, $checkout ) {
		$panes['cart'] = array(
			'weight' => 0,
			'body' => self::load_view_to_string( 'checkout/cart', Group_Buying_Carts::get_view_variables( $this->cart, TRUE ) ),
		);
		$panes['billing'] = array(
			'weight' => 10,
			'body' => self::load_view_to_string( 'checkout/billing', array( 'fields' => $this->get_billing_fields() ) ),
		);

		$controls = array(
			'review' => '<input class="form-submit submit checkout_next_step" type="submit" value="'.self::__( 'Review' ).'" name="gb_checkout_button" />',
		);
		$controls = apply_filters( 'gb_checkout_payment_controls', $controls, $checkout );
		$panes['controls'] = array(
			'weight' => 1000,
			'body' => self::load_view_to_string( 'checkout/payment_controls', array( 'form_controls' => $controls ) ),
		);
		return $panes;
	}

	private function get_billing_fields() {
		$fields = $this->get_standard_address_fields();
		$fields = apply_filters( 'gb_checkout_fields_billing', $fields );
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		return $fields;
	}

	/**
	 * Process the payment form
	 *
	 * @return void
	 */
	public function process_payment_page( Group_Buying_Checkouts $checkout ) {
		$valid = TRUE;
		if ( apply_filters( 'gb_valid_process_payment_page_fields', __return_true() ) ) {
			$fields = $checkout->get_billing_fields();
			foreach ( $fields as $key => $data ) {
				$checkout->cache['billing'][$key] = isset( $_POST['gb_billing_'.$key] )?$_POST['gb_billing_'.$key]:'';
				if ( isset( $data['required'] ) && $data['required'] && !( isset( $checkout->cache['billing'][$key] ) && $checkout->cache['billing'][$key] != '' ) ) {
					$valid = FALSE;
					self::set_message( sprintf( self::__( '"%s" field is required.' ), $data['label'] ), self::MESSAGE_STATUS_ERROR );
				}
			}
		}
		$valid = apply_filters( 'gb_valid_process_payment_page', $valid, $checkout );
		if ( $valid ) {
			$checkout->mark_page_complete( self::PAYMENT_PAGE );
		}
	}


	/**
	 * Display the final review page
	 *
	 * @param array   $panes
	 * @param Group_Buying_Checkout $checkout
	 * @return array
	 */
	public function display_review_page( $panes, $checkout ) {
		$panes['cart'] = array(
			'weight' => 0,
			'body' => self::load_view_to_string( 'checkout/cart', Group_Buying_Carts::get_view_variables( $this->cart, TRUE ) ),
		);
		if ( isset( $checkout->cache['billing'] ) ) {
			$panes['billing'] = array(
				'weight' => 10,
				'body' => self::load_view_to_string( 'checkout/billing-review', array( 'data' => $checkout->cache['billing'] ) ),
			);
		}
		$panes['controls'] = array(
			'weight' => 1000,
			'body' => self::load_view_to_string( 'checkout/review-controls', array() ),
		);
		return $panes;
	}

	/**
	 * Process the review page
	 *
	 * @param Group_Buying_Checkouts $checkout
	 * @return void
	 */
	public function process_review_page( Group_Buying_Checkouts $checkout ) {
		$checkout->mark_page_complete( self::REVIEW_PAGE );
	}

	/**
	 * Display the confirmation page
	 * Don't depend on anything being in the cache except the purchase ID
	 *
	 * @return array
	 */
	public function display_confirmation_page( $panes, $checkout ) {
		$purchase_id = $checkout->cache['purchase_id'];
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		global $gb_purchase_confirmation_id; // Used for addons that can't access the $order_number
		$gb_purchase_confirmation_id = $purchase_id;

		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		$account = Group_Buying_Account::get_instance_by_id( $purchase->get_account_id() );
		$account_address = $account->get_address();

		$lookup_url = ( isset( $address['city'] ) ) ? add_query_arg( array( Group_Buying_Purchases::AUTH_FORM_INPUT => $city ), get_permalink( $purchase_id ) ) : get_permalink( $purchase_id ) ;

		$args = array(
			'order_number' => $purchase_id,
			'tax' => $purchase->get_tax_total(),
			'shipping' => $purchase->get_shipping_total(),
			'total' => $purchase->get_total(),
			'products' => $purchase->get_products(),
			'checkout' => $checkout,
			'lookup_url' => $lookup_url,
		);
		$panes['confirmed'] = array(
			'weight' => 0,
			'body' => self::load_view_to_string( 'checkout/order-confirmation', $args ),
		);
		return $panes;
	}

	/**
	 * All the checkout pages have been processed. Data should be in the cache. Do something with it.
	 *
	 * @return void
	 */
	private function complete_checkout() {
		// make sure everything in the cart is still valid
		// this is the last check before payment
		$account = Group_Buying_Account::get_instance();
		foreach ( $this->cart->get_items() as $item ) {
			$deal = Group_Buying_Deal::get_instance( $item['deal_id'] );
			if ( !is_object( $deal ) || !$deal->is_open() || !$account->can_purchase( $item['deal_id'], $item['data'] ) ) {
				// item is no longer available (e.g., sold out, unpublished, expired, etc.)
				// probably happened between when the user submitted the initial payment page
				// and the subsequent review page
				self::set_message( self::__( 'Sorry, this item is no longer available.' ), self::MESSAGE_STATUS_ERROR );
				wp_redirect( Group_Buying_Carts::get_url() );
				exit();
			}
		}

		// create a new purchase
		$purchase_args = array(
			'user' => get_current_user_id(),
			'cart' => $this->cart,
			'checkout' => $this,
		);
		$purchase_args = apply_filters( 'gb_new_purchase_args', $purchase_args, $this );
		$purchase_id = Group_Buying_Purchase::new_purchase( $purchase_args );
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		$this->cache['purchase_id'] = $purchase->get_id();

		// process the payment
		do_action( 'processing_payment', $this, $purchase );
		$payment = $this->payment_processor->process_payment( $this, $purchase );
		do_action( 'gb_log', __CLASS__ . '::' . __FUNCTION__ . ' - complete_checkout payment', $payment );

		do_action( 'processed_payment', $payment, $this, $purchase );

		if ( !is_a( $payment, 'Group_Buying_Payment' ) ) {
			// payment wasn't successful; delete the purchase and go back to the payment page
			Group_Buying_Purchase::delete_purchase( $purchase_id );
			unset( $purchase );
			unset( $this->cache['purchase_id'] );
			$this->mark_page_incomplete( self::PAYMENT_PAGE );
			$this->get_current_page( TRUE );
			do_action( 'checkout_failed' );
			return;
		}

		// wrap up checkout and tell the purchase we're done
		do_action( 'completing_checkout', $this, $payment, $purchase );
		$this->cart->empty_cart();
		$purchase->complete();
		$this->save_cache();
		$this->checkout_complete = TRUE;
		do_action( 'checkout_completed', $this, $payment, $purchase );
	}
}
