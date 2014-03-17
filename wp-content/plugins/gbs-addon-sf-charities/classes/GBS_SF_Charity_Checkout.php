<?php

class GB_SF_Charities_Checkout extends Group_Buying_Controller {
	
	const DEFAULT_DONATION_PERCT = 10;

	public static function init() {
		parent::init();

		// Checkout panes
		self::register_payment_pane();
		self::register_review_pane();
		
		// Process payment page
		add_action( 'gb_checkout_action_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'process_payment_page' ), 11, 1 );

		// Save charity record for purchase
		add_action( 'completing_checkout', array( get_class(), 'save_charity' ), 10, 1 );
		
		add_action( 'wp_ajax_nopriv_gbs_ajax_query_charities',  array( get_class(), 'ajax_query_charities' ), 10, 0 );
		add_action( 'wp_ajax_gbs_ajax_query_charities',  array( get_class(), 'ajax_query_charities' ), 10, 0 );

	}
	
	
	/**
	 * Print a JSON object with the attributes for the requested deal
	 *
	 * @static
	 * @return void
	 */
	public static function ajax_query_charities() {
	
		//$response = 2;

		$args = array(
				'post_type' => GB_SF_Charity::POST_TYPE,
				'order' => 'ASC',
				'orderby' => 'id',
				'numberposts' => -1,
			);
		wp_parse_str( $_POST['selections'], $selections );
		foreach ( $selections as $term_name => $term_id ) {
			if ( !empty($term_id ) && $term_id > 0) {
				$args['tax_query']['relation'] = 'AND'; // in case it wasn't set earlier
				$args['tax_query'][] = array(
						'taxonomy' => $term_name,
						'field' => 'id',
						'terms' => array($term_id),
						'operator' => 'IN'
						);
			}
		}
		
		$charity_ids = get_posts($args);
		foreach ( $charity_ids as $charity ) {
			$response[] = array('id' => $charity->ID, 'title' => $charity->post_title);
		}
		
		header( 'Content-Type: application/json' );
		echo json_encode( $response );
		exit();
	}

	/**
	 * Register action hooks for displaying and processing the payment page
	 *
	 * @return void
	 */
	private static function register_payment_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'display_payment_page' ), 10, 2 );
	}

	private static function register_review_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::REVIEW_PAGE, array( get_class(), 'display_review_page' ), 10, 2 );
	}

	private static function register_confirmation_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::CONFIRMATION_PAGE, array( get_class(), 'display_confirmation_page' ), 10, 2 );
	}

	public static function display_payment_page( $panes, $checkout ) {
		$charities = GB_SF_Charity::get_charities();
		$percentage = ( self::DEFAULT_DONATION_PERCT ) ? self::DEFAULT_DONATION_PERCT : 0;
		if ( !empty( $charities ) ) {
			$panes['charity'] = array(
				'weight' => 1,
				'body' => self::_load_view_to_string( 'checkout/charities', array( 'charity_ids' => $charities, 'donation_percentage' => $percentage ) ),
			);
		}
		return $panes;
	}

	public static function process_payment_page( Group_Buying_Checkouts $checkout ) {
		$valid = TRUE;
		if ( isset( $_POST['gb_charity'] ) ) {
			if ( $_POST['gb_charity'] == '' ) {
				self::set_message( "A Non Profit Selection is Required. ", self::MESSAGE_STATUS_ERROR );
				$valid = FALSE;
			}
		}
		if ( !$valid ) {
			$checkout->mark_page_incomplete( Group_Buying_Checkouts::PAYMENT_PAGE );
		} else {
			$checkout->cache['gb_charity'] = $_POST['gb_charity'];
		}
	}


	/**
	 * Display the review pane with a message about their generosity.
	 *
	 * @param array   $panes
	 * @param Group_Buying_Checkout $checkout
	 * @return array
	 */
	public static function display_review_page( $panes, $checkout ) {
		$charity_id = $checkout->cache['gb_charity'];
		$percentage = ( self::DEFAULT_DONATION_PERCT ) ? self::DEFAULT_DONATION_PERCT : 0;
		if ( $checkout->cache['gb_charity'] ) {
			$panes['gb_charity'] = array(
				'weight' => 5,
				'body' => self::_load_view_to_string( 'checkout/charity-review', array( 'charity_id' => $charity_id, 'donation_percentage' => $percentage ) ),
			);
		}
		return $panes;
	}

	public static function save_charity( $checkout ) {
		if ( $checkout->cache['gb_charity'] && $checkout->cache['purchase_id'] ) {
			$purchase = Group_Buying_Purchase::get_instance( $checkout->cache['purchase_id'] );
			
			//Calculate donation amount
			$percentage = ( self::DEFAULT_DONATION_PERCT ) ? self::DEFAULT_DONATION_PERCT : 0;
			$donation = ( $percentage ) ? $purchase->get_subtotal()*($percentage*0.01) : 0 ;
			GB_SF_Charities::set_purchase_charity( $purchase, $checkout->cache['gb_charity'], $donation, $percentage);
		}
	}



	private static function _load_view_to_string( $path, $args ) {
		ob_start();
		if ( !empty( $args ) ) extract( $args );
		// Check if there's a template specific file
		$file = ( file_exists( GB_SF_CHARITY_PATH . '/views/' . GBS_THEME_SLUG . '/' . $path . '.php' ) ) ? GB_SF_CHARITY_PATH . '/views/' . GBS_THEME_SLUG . '/' . $path . '.php' : GB_SF_CHARITY_PATH . '/views/prime_theme/' . $path . '.php' ;
		@include $file;
		return ob_get_clean();
	}

}