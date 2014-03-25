<?php

/**
 * GBS Report Model
 *
 * @package GBS
 * @subpackage Report
 */
class Group_Buying_Report extends Group_Buying_Controller {
	public static $columns;
	public static $records;
	public static $csv_available;
	public static $report;
	private static $instances;

	public static function init() {}

	private function __construct( $report ) {
		$this->report = $report;
		self::set_data();
	}

	public static function get_instance( $report ) {
		if ( !isset( self::$instances[$report] ) || !self::$instances[$report] instanceof self ) {
			self::$instances[$report] = new self( $report );
		}
		return self::$instances[$report];
	}

	public function set_data() {
		do_action( 'gb_reports_set_data', $this );
		$set_data = 'set_'.$this->report.'_report_data';
		if ( is_callable( array( $this, $set_data ) ) )
			self::$set_data();
		do_action( 'gb_reports_set_data_post', $this );
	}

	private function data_access( $merchant_access = FALSE, $authorized = FALSE ) {
		do_action( 'gb_reports_data_access' );
		if ( current_user_can( 'manage_options' ) ) return TRUE; // admins always have access

		if ( $merchant_access ) {
			$id = ( isset( $_GET['id'] ) && $_GET['id'] ) ? $_GET['id'] : gb_account_merchant_id() ;
			$merchant = Group_Buying_Merchant::get_merchant_object( $id );
			if ( !is_object( $merchant ) )
				return FALSE;
			$current_user = wp_get_current_user();
			$authorized = $merchant->is_user_authorized( $current_user->ID );
			if ( $authorized )
				return TRUE;
		}
		return;
	}

	public function set_deal_purchase_report_data( $csv = TRUE, $merchant_access = FALSE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;

		$deal = Group_Buying_Deal::get_instance( $_GET['id'] );
		if ( !is_a( $deal, 'Group_Buying_Deal' ) ) return; // nothin' we can do

		$columns = array(
			'date' => self::__( 'Date' ),
			'id' => self::__( 'Order #' ),
			'quantity' => self::__( 'Quantity' ),
			'price' => self::__( 'Price' ),
			'total' => self::__( 'Purchase Total' ),
			'name' => self::__( 'Name' ),
			'email' => self::__( 'Email' ),
			'notes' => self::__( 'Admin Note' ),
			'source' => self::__( 'Share Source' ) );
		$this->columns = apply_filters( 'set_deal_purchase_report_data_column', $columns );

		// Get all the purchase ids
		$purchase_ids = Group_Buying_Purchase::get_purchases( array( 'deal' => $deal->get_ID() ) );
		// Pagination
		$pages = array_chunk( $purchase_ids, apply_filters( 'gb_reports_show_records', 100, 'deal_purchase' ) ); // chunk the purchase array into 100 increments
		$gb_report_pages = count( $pages ); // set the global for later pagination
		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage'] : 0 ;

		$purchase_array = array();
		if ( !empty( $pages ) ) {

			// Default the page to 0 if there are no results being queried
			if ( !isset( $pages[$showpage] ) ) {
				$showpage = 0;
			}

			foreach ( $pages[$showpage] as $purchase_id ) {
				// Get purchase object
				$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
				$total = $purchase->get_total();
				$payment_id = array_shift( $purchase->get_payments() );
				$payment = Group_Buying_Payment::get_instance( $payment_id );
				if ( is_a( $payment, 'Group_Buying_Payment' ) ) {
					$source = $payment->get_source();
				}
				$source = ( empty( $source ) ) ? self::__( 'N/A' ) : $source;

				$user_id = $purchase->get_user();
				if ( $user_id != -1 ) {
					$user = get_userdata( $user_id );
					$account_id = $purchase->get_account_id();
					$account = Group_Buying_Account::get_instance_by_id( $account_id );
					$get_name = $account->get_name();
					$name = ( strlen( $get_name ) <= 1  ) ? get_the_title( $account_id ) : $get_name;
					$email = $user->user_email;
				} elseif( class_exists('Group_Buying_Gift') ) {
					$gift_id = Group_Buying_Gift::get_gift_for_purchase( $purchase->get_ID() );
					$gift = Group_Buying_Gift::get_instance( $gift_id );
					$name = self::__( 'Unclaimed Gift' );
					$email = $gift->get_recipient();
				}
				// do_action( 'gb_log', __CLASS__ . '::' . __FUNCTION__ . ' - purchase', $purchase );
				$purchase_array[] = apply_filters( 'gb_deal_purchase_record_item', array(
						'date' => get_the_time( apply_filters( 'gb_reports_date_format', get_option( 'date_format' ) ), $purchase->get_ID() ),
						'id' => $purchase->get_ID(),
						'total' => gb_get_formatted_money( $total ),
						'quantity' => $purchase->get_product_quantity( $deal->get_ID() ),
						'price' => gb_get_formatted_money( $purchase->get_product_unit_price( $deal->get_ID() ) ),
						'name' => $name,
						'email' => $email,
						'source' => $source,
						'notes' => $purchase->get_admin_notes()
					), $purchase, $account );

				// Unset the purchase
				unset( $purchase );
			}
		}
		$this->records = apply_filters( 'set_deal_purchase_report_data_records', $purchase_array );
	}

	public function set_merchant_purchase_report_data( $csv = TRUE, $merchant_access = TRUE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;

		$deal = Group_Buying_Deal::get_instance( $_GET['id'] );
		if ( !is_a( $deal, 'Group_Buying_Deal' ) ) return; // nothin' we can do

		$columns = array(
			'date' => self::__( 'Date' ),
			'id' => self::__( 'Order #' ),
			'quantity' => self::__( 'Quantity' ),
			'price' => self::__( 'Price' ),
			'name' => self::__( 'Name' ),
			'postal' => self::__( 'Postal' ),
			'notes' => self::__( 'Admin Note' )
		);
		$this->columns = apply_filters( 'set_merchant_purchase_report_column', $columns );


		// Get all the purchase ids
		$purchase_ids = Group_Buying_Purchase::get_purchases( array( 'deal' => $deal->get_ID() ) );
		// Pagination
		$pages = array_chunk( $purchase_ids, apply_filters( 'gb_reports_show_records', 100, 'merchant_purchase' ) ); // chunk the purchase array into 100 increments
		$gb_report_pages = count( $pages ); // set the global for later pagination
		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage'] : 0 ;

		$purchase_array = array();
		if ( !empty( $pages ) ) {

			// Default the page to 0 if there are no results being queried
			if ( !isset( $pages[$showpage] ) ) {
				$showpage = 0;
			}

			foreach ( $pages[$showpage] as $purchase_id ) {
				// Get purchase object
				$purchase = Group_Buying_Purchase::get_instance( $purchase_id );

				$user_id = $purchase->get_user();
				if ( $user_id != -1 ) {
					// $user = get_userdata( $user_id );
					$account_id = $purchase->get_account_id();
					$account = Group_Buying_Account::get_instance_by_id( $account_id );
					if ( is_a( $account, 'Group_Buying_Account' ) ) {
						$address = $account->get_address();
						$get_name = $account->get_name();
						$name = ( strlen( $get_name ) <= 1  ) ? get_the_title( $account_id ) : $get_name;
					}
				} else {
					$address = null;
					$name = self::__( 'Unclaimed Gift' );
				}

				$purchase_array[] = apply_filters( 'gb_merch_purchase_record_item', array(
						'date' => get_the_time( apply_filters( 'gb_reports_date_format', get_option( 'date_format' ) ), $purchase->get_ID() ),
						'id' => $purchase->get_ID(),
						'quantity' => $purchase->get_product_quantity( $deal->get_ID() ),
						'price' => gb_get_formatted_money( $purchase->get_product_unit_price( $deal->get_ID() ) ),
						'name' => $name,
						'postal' => $address['postal_code'],
						'notes' => $purchase->get_admin_notes()
					), $purchase, $account );

				// Unset the purchase
				unset( $purchase );
			}
		}
		$this->records = apply_filters( 'set_merchant_purchase_report_records', $purchase_array );
	}

	public function set_deal_voucher_report_data( $csv = TRUE, $merchant_access = FALSE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;

		$deal = Group_Buying_Deal::get_instance( $_GET['id'] );
		if ( !is_a( $deal, 'Group_Buying_Deal' ) ) return; // nothin' we can do

		$columns = array(
			'id' => self::__( 'Order #' ),
			'claimed' => self::__( 'Redeemed' ),
			'quantity' => self::__( 'Quantity' ),
			'price' => self::__( 'Price' ),
			'voucher' => self::__( 'Voucher' ),
			'scode' => self::__( 'Security Code' ),
			'name' => self::__( 'Name' ),
			'email' => self::__( 'Email' ) );
		$this->columns = apply_filters( 'set_deal_voucher_report_data_column', $columns );

		// Get all the purchase ids
		$vouchers = Group_Buying_Voucher::get_vouchers_for_deal( $deal->get_ID() );
		// Pagination
		$pages = array_chunk( $vouchers, apply_filters( 'gb_reports_show_records', 100, 'deal_voucher' ) ); // chunk the purchase array into 100 increments
		$gb_report_pages = count( $pages ); // set the global for later pagination
		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage'] : 0 ;

		$i = 1; // To count the voucher quantity
		$purchase_array = array();
		if ( !empty( $pages ) ) {

			// Default the page to 0 if there are no results being queried
			if ( !isset( $pages[$showpage] ) ) {
				$showpage = 0;
			}

			foreach ( $pages[$showpage] as $voucher_id ) {
				$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
				$purchase = $voucher->get_purchase();
				if ( is_a( $purchase, 'Group_Buying_Purchase' ) ) {
					$user_id = $purchase->get_user();
					if ( $user_id != -1 ) {
						$user = get_userdata( $user_id );
						$account_id = $purchase->get_account_id();
						$account = Group_Buying_Account::get_instance_by_id( $account_id );
						if ( is_a( $account, 'Group_Buying_Account' ) ) {
							$address = $account->get_address();
							$get_name = $account->get_name();
							$name = ( strlen( $get_name ) <= 1  ) ? get_the_title( $account_id ) : $get_name;
						}
						$email = $user->user_email;
					} elseif( class_exists('Group_Buying_Gift') ) {
						$gift_id = Group_Buying_Gift::get_gift_for_purchase( $purchase->get_ID() );
						$gift = Group_Buying_Gift::get_instance( $gift_id );
						$address = null;
						$name = self::__( 'Unclaimed Gift' );
						$email = $gift->get_recipient();
					}
					$claimed = ( $voucher->get_claimed_date() != '' ) ? date( get_option( 'date_format' ), $voucher->get_claimed_date() ) : null ;

					// check if we finished the quantity count for a purchase
					if ( $i > $purchase->get_product_quantity( $deal->get_ID() ) ) {
						$i = 1;
					}
					$purchase_array[] = apply_filters( 'gb_deal_voucher_record_item', array(
							'id' => $purchase->get_ID(),
							'voucher_id' => $voucher_id,
							'claimed' => $claimed,
							'quantity' => $i . self::__( ' of ' ) . $purchase->get_product_quantity( $deal->get_ID() ),
							'price' => gb_get_formatted_money( $purchase->get_product_unit_price( $deal->get_ID() ) ),
							'voucher' => $voucher->get_serial_number(),
							'scode' => $voucher->get_security_code(),
							'name' => $name,
							'email' => $email,
						), $voucher, $purchase, $account );
					$i++;
					unset( $purchase );
				}
				unset( $voucher );
			}
		}
		$this->records = apply_filters( 'set_deal_voucher_report_data_records', $purchase_array );
	}

	public function set_merchant_voucher_report_data( $csv = TRUE, $merchant_access = TRUE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;

		$deal = Group_Buying_Deal::get_instance( $_GET['id'] );
		if ( !is_a( $deal, 'Group_Buying_Deal' ) ) return; // nothin' we can do

		$columns = array(
			'id' => self::__( 'Order #' ),
			'voucher_id' => self::__( 'Voucher ID' ),
			'claimed' => self::__( 'Redeemed' ),
			'quantity' => self::__( 'Quantity' ),
			'price' => self::__( 'Unit Price' ),
			'voucher' => self::__( 'Voucher(s)' ),
			'name' => self::__( 'Name' ),
			'postal' => self::__( 'Postal' ),
		);
		$this->columns = apply_filters( 'set_merchant_voucher_report_data_column', $columns );

		// Get all the purchase ids
		$vouchers = Group_Buying_Voucher::get_vouchers_for_deal( $deal->get_ID() );
		// Pagination
		$pages = array_chunk( $vouchers, apply_filters( 'gb_reports_show_records', 100, 'merchant_voucher' ) ); // chunk the purchase array into 100 increments
		$gb_report_pages = count( $pages ); // set the global for later pagination
		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage'] : 0 ;

		$i = 1;
		$purchase_array = array();
		if ( !empty( $pages ) ) {

			// Default the page to 0 if there are no results being queried
			if ( !isset( $pages[$showpage] ) ) {
				$showpage = 0;
			}

			foreach ( $pages[$showpage] as $voucher_id ) {
				$voucher = Group_Buying_Voucher::get_instance( $voucher_id );
				$purchase = $voucher->get_purchase();
				if ( is_a( $purchase, 'Group_Buying_Purchase' ) ) {
					$user_id = $purchase->get_user();
					if ( $user_id != -1 ) {
						// $user = get_userdata( $user_id );
						$account_id = $purchase->get_account_id();
						$account = Group_Buying_Account::get_instance_by_id( $account_id );
						if ( is_a( $account, 'Group_Buying_Account' ) ) {
							$address = $account->get_address();
							$get_name = $account->get_name();
							$name = ( strlen( $get_name ) <= 1  ) ? get_the_title( $account_id ) : $get_name;
						}

					} else {
						$address = null;
						$name = self::__( 'Unclaimed Gift' );
					}

					$claimed = ( $voucher->get_claimed_date() != '' ) ? date( get_option( 'date_format' ), $voucher->get_claimed_date() ) : '<a href="'.gb_get_voucher_claim_url( null, urlencode( gb_get_merchant_voucher_report_url() ) ).'">'.self::__( 'Mark Redeemed' ).'</a>' ;

					// check if we finished the quantity count for a purchase
					if ( $i > $purchase->get_product_quantity( $deal->get_ID() ) ) {
						$i = 1;
					}
					$purchase_array[] = apply_filters( 'gb_merch_deal_voucher_record_item', array(
							'id' => $purchase->get_ID(),
							'voucher_id' => $voucher_id,
							'claimed' => $claimed,
							'quantity' => $i . self::__( ' of ' ) . $purchase->get_product_quantity( $deal->get_ID() ),
							'price' => gb_get_formatted_money( $purchase->get_product_unit_price( $deal->get_ID() ) ),
							'voucher' => $voucher->get_serial_number(),
							'name' => $name,
							'postal' => $address['postal_code']
						), $voucher, $purchase, $account );
					$i++;
					unset( $purchase );
				}
				unset( $voucher );
			}
		}
		$this->records = apply_filters( 'set_merchant_voucher_report_data_records', $purchase_array );
	}

	public function set_purchases_report_data( $csv = TRUE, $merchant_access = FALSE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;

		$columns = array(
			'date' => self::__( 'Date' ),
			'id' => self::__( 'Order #' ),
			'voucher_id' => self::__( 'Voucher ID' ),
			'total' => self::__( 'Purchase Total' ),
			'name' => self::__( 'Name' ),
			'email' => self::__( 'Email' ),
			'notes' => self::__( 'Admin Note' ) );
		$this->columns = apply_filters( 'set_purchases_report_data_column', $columns );

		$filter = ( isset( $_GET['filter'] ) && in_array( $_GET['filter'], array( 'any', 'publish', 'draft', 'private', 'trash' ) ) ) ? $_GET['filter'] : 'publish';
		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage']+1 : 1 ;
		$args=array(
			'post_type' => Group_Buying_Purchase::POST_TYPE,
			'post_status' => $filter,
			'posts_per_page' => apply_filters( 'gb_reports_show_records', 100, 'purchases' ),
			'paged' => $showpage
		);
		$purchases = new WP_Query( $args );
		$gb_report_pages = $purchases->max_num_pages; // set the global for later pagination

		$purchase_array = array();
		while ( $purchases->have_posts() ) : $purchases->the_post();
			$purchase = Group_Buying_Purchase::get_instance( get_the_ID() );
			$user_id = $purchase->get_user();
			if ( $user_id != -1 ) {
				$user = get_userdata( $user_id );
				$account_id = $purchase->get_account_id();
				$account = Group_Buying_Account::get_instance_by_id( $account_id );
				if ( is_a( $account, 'Group_Buying_Account' ) ) {
					//$address = $account->get_address();
					$get_name = $account->get_name();
					$name = ( strlen( $get_name ) <= 1  ) ? get_the_title( $account_id ) : $get_name;
				}
				$email = $user->user_email;
			} else {
				$address = null;
				$name = self::__( 'Unclaimed Gift' );
				$email = null;
			}
			$total = $purchase->get_total();

			$purchase_array[] = apply_filters( 'gb_purchases_record_item', array(
					'date' => get_the_time( apply_filters( 'gb_reports_date_format', get_option( 'date_format' ) ), $purchase->get_ID() ),
					'id' => $purchase->get_ID(),
					'total' => gb_get_formatted_money( $total ),
					'name' => $name,
					'email' => $email,
					'credits' => $credits,
					'notes' => $purchase->get_admin_notes()
				), $purchase, $account );

		endwhile;
		$this->records = apply_filters( 'set_purchases_report_data_records', $purchase_array );
	}

	public function set_accounts_report_data( $csv = TRUE, $merchant_access = FALSE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;
		// FUTURE: clean up so rewards are dynamic
		$columns = array( 'id' => self::__( 'ID' ), 'name' => self::__( 'Name' ), 'username' => self::__( 'WP Username' ), 'state' => self::__( 'State' ), 'city' => self::__( 'City' ), 'credits' => self::__( 'Credits' ), 'reards' => self::__( 'Reward Points' ) );
		$this->columns = apply_filters( 'set_accounts_report_data_column', $columns );

		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage']+1 : 1 ;
		$args=array(
			'post_type' => Group_Buying_Account::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => apply_filters( 'gb_reports_show_records', 30, 'accounts' ),
			'paged' => $showpage
		);
		$account_query = new WP_Query( $args );
		$gb_report_pages = $account_query->max_num_pages; // set the global for later pagination

		$accounts = array();
		if ( $account_query->have_posts() ) {
			while ( $account_query->have_posts() ) : $account_query->the_post();
			$account = Group_Buying_Account::get_instance_by_id( get_the_ID() );
			if ( is_a( $account, 'Group_Buying_Account' ) ) {
				$get_name = $account->get_name();
				//$total = $purchase->get_total();
				$name = ( strlen( $get_name ) <= 1  ) ? get_the_title( $account->get_ID() ) : $get_name;
				$user_id = Group_Buying_Account::get_user_id_for_account( $account->get_ID() );
				$user_data = get_userdata( $user_id );
				$address = $account->get_address();
				$credits = gb_get_account_balance( $user_id );
				$reward_credits = gb_get_account_balance( $user_id, Group_Buying_Affiliates::CREDIT_TYPE );
				$accounts[] = apply_filters( 'gb_accounts_record_item', array(
						'id' => get_the_ID(),
						'name' => $name,
						'username' => $user_data->user_login,
						'state' => $address['state'],
						'city' => $address['city'],
						'credits' => $credits,
						'rewards' => $reward_credits
					), $account );
			}
			endwhile;
		}
		$this->records = apply_filters( 'set_accounts_report_data_records', $accounts );
	}

	public function set_merchant_purchases_report_data( $csv = TRUE, $merchant_access = TRUE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;

		$columns = array(
			'id' => self::__( 'Order #' ),
			'subtotal' => self::__( 'Subtotal' ),
			'tax' => self::__( 'Tax' ),
			'shipping' => self::__( 'Shipping' ),
			'total' => self::__( 'Total' ),
			'name' => self::__( 'Name' ),
			//'email' => self::__('Email')
		);
		$this->columns = apply_filters( 'set_merchant_purchases_report_data_column', $columns );

		$filter = ( isset( $_GET['filter'] ) && in_array( $_GET['filter'], array( 'any', 'publish', 'draft', 'private', 'trash' ) ) ) ? $_GET['filter'] : 'publish';
		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage']+1 : 1 ;
		$args=array(
			'post_type' => Group_Buying_Purchase::POST_TYPE,
			'post__in' => gb_get_merchants_purchase_ids( gb_account_merchant_id() ),
			'post_status' => $filter,
			'posts_per_page' => apply_filters( 'gb_reports_show_records', 100, 'merchant_purchases' ),
			'paged' => $showpage
		);
		$merch_purchases = new WP_Query( $args );
		$gb_report_pages = $merch_purchases->max_num_pages; // set the global for later pagination

		$purchase_array = array();
		if ( $merch_purchases->have_posts() ) {
			while ( $merch_purchases->have_posts() ) : $merch_purchases->the_post();
			$purchase = Group_Buying_Purchase::get_instance( get_the_ID() );
			$user_id = $purchase->get_user();
			if ( $user_id != -1 ) {
				$user = get_userdata( $user_id );
				$account_id = $purchase->get_account_id();
				$account = Group_Buying_Account::get_instance_by_id( $account_id );
				if ( is_a( $account, 'Group_Buying_Account' ) ) {
					$address = $account->get_address();
					$get_name = $account->get_name();
					$name = ( strlen( $get_name ) <= 1  ) ? get_the_title( $account_id ) : $get_name;
					$email = $user->user_email;
				}
			} elseif( class_exists('Group_Buying_Gift') ) {
				$gift_id = Group_Buying_Gift::get_gift_for_purchase( $purchase->get_ID() );
				$gift = Group_Buying_Gift::get_instance( $gift_id );
				$address = null;
				$name = self::__( 'Unclaimed Gift' );
				$email = $gift->get_recipient();
			}

			if ( is_a( $account, 'Group_Buying_Account' ) ) {
				$purchase_array[] = apply_filters( 'gb_merch_purchases_record_item', array(
						'id' => $purchase->get_ID(),
						'subtotal' => gb_get_formatted_money( $purchase->get_subtotal() ),
						'tax' => gb_get_formatted_money( $purchase->get_tax_total() ),
						'shipping' => gb_get_formatted_money( $purchase->get_shipping_total() ),
						'total' => gb_get_formatted_money( $purchase->get_total() ),
						'name' => $name,
						//'email' => $email,
					), $purchase, $account );
			}

			endwhile;
		}
		$this->records = apply_filters( 'set_merchant_purchases_report_data_records', $purchase_array );
	}

	public function set_credits_report_data( $csv = TRUE, $merchant_access = FALSE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;

		$columns = array(
			'date' => self::__( 'Date' ),
			'note' => self::__( 'Note' ),
			'account' => self::__( 'Account' ),
			'adjustment' => self::__( 'Adjustment' ),
			'balance' => self::__( 'New Balance' )
		);
		$this->columns = apply_filters( 'set_credit_report_data_column', $columns );

		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage']+1 : 1 ;

		$records = array();
		$credit_types = apply_filters( 'gb_account_credit_types', array() );
		foreach ( $credit_types as $key => $data ) {
			$records = array_merge( Group_Buying_Record::get_records_by_type( Group_Buying_Accounts::$record_type . '_' . $key ), $records );
		}

		$gb_report_pages = ceil( count( $records )/apply_filters( 'gb_reports_show_records', 100, 'credit_report' ) ); // set the global for later pagination

		$record_array = array();
		if ( !empty( $records ) ) {
			foreach ( $records as $record_id ) {
				$record = Group_Buying_Record::get_instance( $record_id );
				$record_data = $record->get_data();

				$balance = (int)$record_data['current_total'];
				$prior = (int)$record_data['prior_total'];
				$adjustment = ( $balance == (int)$record_data['adjustment_value'] ) ? (int)$record_data['adjustment_value'] - $prior : $balance - $prior ;
				$plusminus = ( $adjustment > 0 ) ? '+' : '';

				$record_array[] = apply_filters( 'gb_credits_record_item', array(
						'note' => get_the_title( $record_id ),
						'date' => date( get_option( 'date_format' ).', '.get_option( 'time_format' ), get_the_time( 'U', $record_id ) ),
						'account' => gb_get_name( $record_data['account_id'] ),
						'adjustment' => $plusminus . $adjustment,
						'balance' => $balance
					), $record_id );
			}
		}
		$this->records = apply_filters( 'set_credits_report_data_records', $record_array );
	}

	public function set_credit_purchases_report_data( $csv = TRUE, $merchant_access = FALSE ) {
		if ( !self::data_access( $merchant_access ) ) return; // for the cheaters
		set_time_limit( 0 ); // run script forever

		global $gb_report_pages;

		$this->csv_available = $csv;

		$columns = array(
			'date' => self::__( 'Date' ),
			'type' => self::__( 'Type' ),
			'payment_id' => self::__( 'Payment ID' ),
			'purchase_id' => self::__( 'Purchase ID' ),
			'purchased' => self::__( 'Purchased' ),
			'amount' => self::__( 'Amount' )
		);
		$this->columns = apply_filters( 'set_credit_purchases_report_data_column', $columns );

		$showpage = ( isset( $_GET['showpage'] ) ) ? (int)$_GET['showpage']+1 : 1 ;

		// The payment methods searched for this report.
		$credit_payment_methods = apply_filters( 'set_credit_purchases_methods', array( Group_Buying_Affiliate_Credit_Payments::PAYMENT_METHOD, Group_Buying_Account_Balance_Payments::PAYMENT_METHOD ) );

		$args = array(
			'post_type' => Group_Buying_Payment::POST_TYPE,
			'post_status' => array( 'any', 'publish', 'draft', 'private', 'trash' ),
			'posts_per_page' => apply_filters( 'gb_reports_show_records', 100, 'credit_purchase' ),
			'paged' => $showpage,
			'fields' => 'ids',
			'meta_query' => array( 'relation' => 'OR' ) // the key/values are built below.
		);
		foreach ( $credit_payment_methods as $method ) {
			$args['meta_query'][] =  array(
				'key' => '_payment_method',
				'value' => $method );
		}

		$credit_payment_ids = get_posts( $args );

		$gb_report_pages = ceil( count( $credit_payment_ids )/apply_filters( 'gb_reports_show_records', 100, 'credit_purchases_report' ) ); // set the global for later pagination

		$payment_array = array();
		if ( !empty( $credit_payment_ids ) ) {
			foreach ( $credit_payment_ids as $payment_id ) {
				$payment = Group_Buying_Payment::get_instance( $payment_id );
				$amount = ( $payment->get_payment_method() == Group_Buying_Affiliate_Credit_Payments::PAYMENT_METHOD ) ? $payment->get_amount() : gb_get_formatted_money( $payment->get_amount() ) ;
				$items = array();
				// do_action( 'gb_log', __CLASS__ . '::' . __FUNCTION__ . ' - deals', $payment->get_deals() );
				foreach ( $payment->get_deals() as $deal_id => $item ) {
					$deal = Group_Buying_Deal::get_instance( $item[0]['deal_id'] );
					$items[] = $deal->get_title( $item[0]['data'] ) . ' <small>' . 'quantity: ' . $item[0]['quantity'] . ', cost: ' . gb_get_formatted_money( $item[0]['price'] )  . '</small>';
				}
				$payment_array[] = apply_filters( 'gb_credits_record_item', array(
						'date' => date( get_option( 'date_format' ).', '.get_option( 'time_format' ), get_the_time( 'U', $payment_id ) ),
						'payment_id' => $payment_id,
						'purchase_id' => $payment->get_purchase(),
						'purchased' => implode( ',<br/> ', $items ),
						'amount' => $amount,
						'type' => $payment->get_payment_method(),

					), $payment_id );
			}
		}

		$this->records = apply_filters( 'set_credits_report_data_records', $payment_array );
	}
}