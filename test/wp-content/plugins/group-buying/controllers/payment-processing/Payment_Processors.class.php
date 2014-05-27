<?php

/**
 * Payment processor controller
 *
 * @package GBS
 * @subpackage Payment Processing
 */
abstract class Group_Buying_Payment_Processors extends Group_Buying_Controller {
	const SETTINGS_PAGE = 'payment';
	const PAYMENT_PROCESSOR_OPTION = 'gb_payment_processor';
	const CURRENCY_SYMBOL_OPTION = 'gb_currency_symbol';
	const MONEY_FORMAT_OPTION = 'gb_money_format';
	const CREDIT_TYPE_EXCHANGE_RATES = 'gb_credit_exchange_rates';
	const AJAX_NONCE = 'gbs_payment_processors_nonce';
	private static $payment_processor;
	private static $active_payment_processor_class;
	private static $potential_processors = array();
	private static $currency_symbol;
	private static $money_format;

	public static function get_settings_page( $prefixed = TRUE ) {
		return ( $prefixed ) ? self::TEXT_DOMAIN . '/' . self::SETTINGS_PAGE : self::SETTINGS_PAGE ;
	}

	final public static function init() {
		self::$currency_symbol = get_option( self::CURRENCY_SYMBOL_OPTION, '$' );
		self::$money_format = get_option( self::MONEY_FORMAT_OPTION, '%0.2f' );
		self::get_payment_processor();
		self::register_settings();

		add_action( 'wp_ajax_gb_manually_capture_payment',  array( get_class(), 'manually_capture_payment' ), 10, 0 );
	}

	public function register_settings() {

		// Addon page
		$args = array(
			'slug' => self::get_settings_page( FALSE ),
			'title' => self::__( 'Group Buying Payment Options' ),
			'menu_title' => self::__( 'Payment Settings' ),
			'weight' => 3,
			'reset' => FALSE, 
			'section' => 'general',
			'ajax' => TRUE,
			'ajax_full_page' => TRUE
			);
		do_action( 'gb_settings_page', $args );


		// Settings
		$settings = array(
			'gb_general_settings' => array(
				'title' => 'General Options',
				'weight' => 0,
				'settings' => array(
					self::PAYMENT_PROCESSOR_OPTION => array(
						'label' => self::__( 'Payment Processor' ),
						'option' => array(
							'type' => 'select',
							'options' => self::$potential_processors,
							'default' => self::$active_payment_processor_class
						)
					),
					self::CURRENCY_SYMBOL_OPTION => array(
						'label' => self::__( 'Currency Symbol' ),
						'option' => array(
							'type' => 'text',
							'label' => '',
							'default' => self::$currency_symbol,
							'attributes' => array( 'class' => 'small-text' )
						),
						'description' => self::__( 'If you want the symbol after the value, use a % before your currency symbol. Example, %&pound; ' )
					),
					self::CREDIT_TYPE_EXCHANGE_RATES => array(
						'label' => self::__( 'Credit Exchange Rates' ),
						'option' => array( get_class(), 'display_credit_type_exchange' ),
						'sanitize_callback' => array( get_class(), 'save_exchange_rates' )
					)
				)
			)
		);
		do_action( 'gb_settings', $settings, self::SETTINGS_PAGE );

	}

	public static function display_credit_type_exchange() {
		$types = apply_filters( 'gb_account_credit_types', array() );
		foreach ( $types as $type => $name ) {
			?>
			<p>
				<em><?php echo $name ?>:</em> <input type="number" name="<?php echo self::CREDIT_TYPE_EXCHANGE_RATES.'_'.$type ?>" value="<?php echo self::get_credit_exchange_rate( $type ) ?>" class="small-text"> <?php printf( self::__('credit equals %s for purchases.'), gb_get_formatted_money(1,FALSE) ) ?>
			</p>
			<?php
		}
		echo '<p class="description">'.self::__( 'Changing a ratio does not change your customers/accounts credit balance. It is not recommended to change the "Account Balance" ratio, since most GBS themes have this "credit" formatted as money.' ).'</p>';
	}

	public static function save_exchange_rates( $value = null ) {
		$types = apply_filters( 'gb_account_credit_types', array() );
		foreach ( $types as $type => $name ) {
			$key = self::CREDIT_TYPE_EXCHANGE_RATES . '_' . $type;
			if ( isset( $_POST[ $key ] ) ) {
				update_option( $key, $_POST[ $key ] );
			}
		}
	}

	/**
	 * Get an instance of the active payment processor
	 *
	 * @static
	 * @return Group_Buying_Payment_Processors|NULL
	 */
	public static function get_payment_processor() {
		// Get the option specifying which payment processor to use
		self::$active_payment_processor_class = get_option( self::PAYMENT_PROCESSOR_OPTION, 'Group_Buying_Paypal_WPP' );
		if ( class_exists( self::$active_payment_processor_class ) ) {
			self::$payment_processor = call_user_func( array( self::$active_payment_processor_class, 'get_instance' ) );
			return self::$payment_processor;
		} else {
			return NULL;
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

	/**
	 *
	 *
	 * @static
	 * @abstract
	 * @return Group_Buying_Payment_Processor|NULL
	 */
	public static abstract function get_instance();

	protected function __construct() {
		add_action( 'gb_new_purchase', array( $this, 'register_as_payment_method' ), 10, 1 );
	}

	/**
	 * Process a payment
	 *
	 * @abstract
	 * @param Group_Buying_Checkouts $checkout
	 * @param Group_Buying_Purchase $purchase
	 * @return Group_Buying_Payment|bool FALSE if the payment failed, otherwise a Payment object
	 */
	public abstract function process_payment( Group_Buying_Checkouts $checkout, Group_Buying_Purchase $purchase );

	/**
	 * Subclasses have to register to be listed as payment options
	 *
	 * @abstract
	 * @return void
	 */
	public abstract static function register();

	/**
	 * Generate a list of months
	 *
	 * @static
	 * @return array
	 */
	public static function get_month_options() {
		$months = array(
			1 => self::__( '01 - January' ),
			2 => self::__( '02 - February' ),
			3 => self::__( '03 - March' ),
			4 => self::__( '04 - April' ),
			5 => self::__( '05 - May' ),
			6 => self::__( '06 - June' ),
			7 => self::__( '07 - July' ),
			8 => self::__( '08 - August' ),
			9 => self::__( '09 - September' ),
			10 => self::__( '10 - October' ),
			11 => self::__( '11 - November' ),
			12 => self::__( '12 - December' ),
		);
		return apply_filters( 'gb_payment_month_options', $months );
	}

	/**
	 * Generate an array of years, starting with the current year, with keys matching values
	 *
	 * @static
	 * @param int     $number The number of values in the list
	 * @return array
	 */
	public static function get_year_options( $number = 10 ) {
		$this_year = (int)date( 'Y' );
		$years = array();
		for ( $i = 0 ; $i < $number ; $i++ ) {
			$years[$this_year+$i] = $this_year+$i;
		}
		return apply_filters( 'gb_payment_year_options', $years );
	}


	/**
	 * Remove the payments page from the list of completed checkout pages
	 *
	 * @param Group_Buying_Checkouts $checkout
	 * @return void
	 */
	protected function invalidate_checkout( Group_Buying_Checkouts $checkout ) {
		$checkout->mark_page_incomplete( Group_Buying_Checkouts::PAYMENT_PAGE );
	}

	public static function get_registered_processors( $filter = '' ) {
		$processors = self::$potential_processors;
		switch ( $filter ) {
			case 'offsite':
				foreach ( $processors as $class => $label ) {
					if ( !self::is_offsite_processor($class) ) {
						unset($processors[$class]);
					}
				}
				break;
			case 'credit':
				foreach ( $processors as $class => $label ) {
					if ( !self::is_cc_processor($class) ) {
						unset($processors[$class]);
					}
				}
				break;
			default:
				break; // do not filter
		}
		return $processors;
	}

	public static function is_cc_processor( $class ) {
		return is_subclass_of($class, 'Group_Buying_Credit_Card_Processors');
	}

	public static function is_offsite_processor( $class ) {
		return is_subclass_of($class, 'Group_Buying_Offsite_Processors');
	}

	public static function get_credit_exchange_rate( $credit_type ) {
		return get_option( self::CREDIT_TYPE_EXCHANGE_RATES . '_' . $credit_type, 1 );
	}

	final protected static function add_payment_processor( $class, $label ) {
		self::$potential_processors[$class] = $label;
	}

	public static function get_currency_symbol() {
		return self::$currency_symbol;
	}

	/**
	 *
	 *
	 * @abstract
	 * @return string
	 */
	public abstract function get_payment_method();

	/**
	 * Register as the payment method for each unpaid-for item in the purchase
	 *
	 * @param Group_Buying_Purchase $purchase
	 * @return void
	 */
	public function register_as_payment_method( $purchase ) {
		$items = $purchase->get_products();
		foreach ( $items as $key => $item ) {
			$remaining = $item['price'];
			foreach ( $item['payment_method'] as $processor => $amount ) {
				$remaining -= $amount;
			}
			if ( $remaining >= 0.01 || $item['price'] == 0 ) { // leave a bit of room for floating point arithmetic
				$items[$key]['payment_method'][$this->get_payment_method()] = $remaining;
			}
		}
		$purchase->set_products( $items );
	}


	/**
	 * Determine which items in a purchase for which the Payment is ready to capture funds
	 *
	 * @param Group_Buying_Payment $payment
	 * @return array An array with deal IDs as key and the amount to capture for that ID as value
	 */
	protected function items_to_capture( Group_Buying_Payment $payment, $release_payment = FALSE ) {
		// wp_delete_post($payment->get_ID(),TRUE); return;
		$data = $payment->get_data();
		$items_to_capture = array();
		$purchase = Group_Buying_Purchase::get_instance( $payment->get_purchase() );
		if ( !is_a( $purchase, 'Group_Buying_Purchase' ) ) return; // nothing to do if the object is not a purchase
		foreach ( $purchase->get_products() as $item ) {
			// is this payment processor used for this item (in case of mixed payment for a purchase)?
			if ( isset( $item['payment_method'][$this->get_payment_method()] ) ) {
				// do we still need to capture this payment
				if ( isset( $data['uncaptured_deals'][$item['deal_id']] ) && count( $data['uncaptured_deals'][$item['deal_id']] ) > 0 ) {
					$deal = Group_Buying_Deal::get_instance( $item['deal_id'] );
					if ( is_a( $deal, 'Group_Buying_Deal' ) ) {
						// is the deal successful OR is this returning payments for release?
						if ( $deal->is_successful() || ( $release_payment && !$deal->is_successful() && $deal->is_expired() ) ) {
							// how much do we need to capture for this deal
							$items_to_capture[$item['deal_id']] = 0;
							foreach ( $data['uncaptured_deals'][$item['deal_id']] as $item ) {
								// if the deal has a dynamic price, or simply the admin changed the price.
								$subtotal = $deal->get_price( null, $item['data'] )*$item['quantity']; // don't forget about quantity purchases and attributes.
								$tax = $purchase->get_item_tax( $item );
								$shipping = $purchase->get_item_shipping( $item );

								// only capture the portion handled by this payment method
								$ratio = 1;
								if ( $item['payment_method'][$this->get_payment_method()] != $item['price'] && $item['price'] > 0 ) {
									$ratio = $item['payment_method'][$this->get_payment_method()] / $item['price'];
								}

								$total = ( $subtotal + $tax + $shipping ) * $ratio;

								$items_to_capture[$item['deal_id']] += apply_filters( 'gb_item_to_capture_total', number_format( floatval( $total ), 2, '.', '' ), $total, $item, $release_payment ); // Make sure it's a number others can use, otherwise gateways will complain about x.00000001.
							}
						}
					}

				}
			}
		}
		return apply_filters( 'gb_pp_items_to_capture', $items_to_capture, $this, $payment );
	}

	/**
	 * Check if a recurring payment is still active with the payment processor
	 *
	 * @param Group_Buying_Payment $payment
	 * @return void
	 */
	public function verify_recurring_payment( Group_Buying_Payment $payment ) {
		// default implementation does nothing
		// it's up to the individual payment processor to verify
	}

	/**
	 * Cancel a recurring payment
	 *
	 * @param Group_Buying_Payment $payment
	 * @return void
	 */
	public function cancel_recurring_payment( Group_Buying_Payment $payment ) {
		$payment->set_status( Group_Buying_Payment::STATUS_CANCELLED );
		// it's up to the individual payment processor to handle any other details
	}

	public function get_checkout_local( Group_Buying_Checkouts $checkout, Group_Buying_Purchase $purchase, $billing = FALSE ) {
		$local = array(
			'zone' => $checkout->cache['billing']['zone'],
			'country' => $checkout->cache['billing']['country'],
		);
		if ( !$billing && isset( $checkout->cache['shipping'] ) ) {
			$local = array(
				'zone' => $checkout->cache['shipping']['zone'],
				'country' => $checkout->cache['shipping']['country'],
			);
		}
		if ( empty( $local['zone'] ) || empty( $local['country'] ) ) {
			$user_id = $purchase->get_user();
			$account = Group_Buying_Account::get_instance( $user_id );
			$address = $account->get_address();
			if ( !empty( $address ) ) {
				$account_local = array(
					'zone' => $address['zone'],
					'country' => $address['country'],
				);
			}
			$local = wp_parse_args( $local, $account_local );
		}
		return $local;
	}

	public static function get_credit_types() {
		$types = array(
			self::CREDIT_TYPE => self::__( 'Account Balance' ),
		);
		return apply_filters( 'gb_account_credit_types', $types );
	}

	public static function manually_capture_payment() {
		
		if ( !isset( $_REQUEST['capture_payment_nonce'] ) )
			wp_die( 'Forget something?' );

		$nonce = $_REQUEST['capture_payment_nonce'];
		if ( !wp_verify_nonce( $nonce, self::AJAX_NONCE ) )
        	wp_die( 'Not going to fall for it!' );

        if ( !current_user_can( 'delete_posts' ) )
        	return;

		$payment_id = $_REQUEST['payment_id'];
		$payment = Group_Buying_Payment::get_instance( $payment_id );
		if ( !is_a( $payment, 'Group_Buying_Payment' ) )
			wp_die( 'Payment ID Error.' );

		$status = $payment->get_status();

		// Payment processors need to allow for this functionality.
		do_action( 'gb_manually_capture_purchase', $payment );

		if ( $payment->get_status() != $status ) {
			gb_e( 'Payment status updated.' );
		}
		else {
			gb_e('Failed payment capture.');
		}
		die();
	}
}
