<?php
class SF_CustomSalesExport extends Group_Buying_Controller {
	
	private static $instance;

	public static function init() {
		
		//Add CSS & JS for Datepicker
		add_action( 'admin_enqueue_scripts', array( get_class(), 'queue_admin_resources' ) );
		
		//Add Menu
		add_action('admin_menu', array( get_class(), 'gbs_sfsalesexport_admin_actions' ) );  
		
		//Add action for download interupt
		add_action('wp_loaded', array( get_class(), 'do_sales_export_download') );
		
	}
	
	public static function get_instance() {
		if ( !(self::$instance && is_a(self::$instance, __CLASS__)) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public static function queue_admin_resources() {
		wp_enqueue_script( 'gb_timepicker' );
		wp_enqueue_script( 'gb_admin_deal' );
		wp_enqueue_style( 'gb_admin_deal' );
	}
	
	/**
	 * Return sales export columns.
	 */
	public function sales_export_columns() {
		//define column titles
		$sales_columns = array(
			'merchant' => 'Merchant',
			'deal_location' => 'Location',
			'id' => 'Order #',
			'date' => 'Date of Purchase',
			'item_title' => 'Deal Title',
			'deal_id' => 'Deal ID',
			'voucher_code' => 'Voucher Code',
			'voucher_id' => 'Voucher ID',
			'voucher_status' => 'Redeem Status',
			'attribute_title' => 'Attribute Title',
			'item_price' => 'Deal Price',
			//'item_shipping' => 'Deal Item Shipping',
			'total' => 'Order Total',
			'shipping_total' => 'Shipping Total',
			'tax_total' => 'Tax Total',
			'credits_applied' => 'Credits Applied',
			'user_id' => 'User ID',
			'email' => 'User Email',
			'fname' => 'First Name',
			'lname' => 'Last Name',
			'notes' => 'Order Notes',
			'charity' => 'Charity',
			'donation_total' => 'Purchase Donation Total',
			'donation_perct' => 'Purchase Donation Percent',
			'redeem_name' => 'Redeemer Name',
			'redeem_date' => 'Redemption Date',
			'redeem_total' => 'Redemption Total',
			'redeem_notes' => 'Redemption Notes',
			);
							
		return $sales_columns;
	
	}
	/**
	 * Return sales export purchase data.
	 */
	public function sales_export_get_data( $post_id = false, $start = false, $end = false ) {
		$purchase_array = array();
		
		$purchase = Group_Buying_Purchase::get_instance($post_id);
		$purchase_id = $purchase->get_ID();
		
		if ($purchase) : //If is purchase object		

			$purchase_timestamp = get_the_time('U', $purchase_id);
			
			//If in date range
			if ($start <= $purchase_timestamp && $end >= $purchase_timestamp) : //filter dates

				$purchase_date = get_the_time( apply_filters( 'gb_reports_date_format', get_option( 'date_format' ) ), $purchase_id );
				$userID = $purchase->get_user();
				$account = null;
				$address = '';
				$street_address_1 = '';
				$street_address_2 = '';
				$first_name = '';
				$last_name = '';
				
				$email = '';
				
				if ( $userID != -1 ) {
					$user = get_userdata($userID);
					$accountID = Group_Buying_Account::get_account_id_for_user($userID);
					$account = Group_Buying_Account::get_instance_by_id($accountID);
		
					$address = $account->get_address();	
					//$get_name = $account->get_name();
					$first_name = $account->get_name('first');
					$last_name = $account->get_name('last');
					$first_name = ( strlen($first_name) <= 1  ) ? get_the_title($accountID) : $first_name;
					$email = $user->user_email;
				} else {
					$address = null;	
					$first_name = 'Unclaimed Gift';
					$last_name = '';
					$userID = '';
					$email = '';
				}
				
			
				//Check for shipping address & retreive payment methods
				$payments = Group_Buying_Payment::get_payments_for_purchase($purchase_id);
				$get_shipping = false;
				$set_ship = false;
				$payment_method_cleaned = '';
				$payment_method = '';
				$credits_used = 0;
					
				$total = number_format($purchase->get_total(), 2);
				$tax_total = number_format($purchase->get_tax_total(), 2);
				$shipping_total = number_format($purchase->get_shipping_total(), 2);
				//$subtotal = gb_get_formatted_money($purchase->get_subtotal());
				
				//Get credits used (if only 1 payment method
				if (sizeof($payments) == 1 && ($payment_method == Group_Buying_Account_Balance_Payments::PAYMENT_METHOD || $payment_method == Group_Buying_Affiliate_Credit_Payments::PAYMENT_METHOD ) ) { 
					//If only one payment then just use total to calculate credits used
					$credits_used = $purchase->get_total();
				} 
				
				//Notes
				$notes = '';
				
				//Get Vouchers & prepare for being matched up with deals
				$deal_vouchers = array();
				$vouchers = $purchase->get_vouchers();
				if (!empty($vouchers)) {
					foreach ($vouchers as $voucher_id) {
						$this_voucher = Group_Buying_Voucher::get_instance($voucher_id);
						$this_voucher_deal_id 	= $this_voucher->get_deal_id();
						$this_voucher_code 		= $this_voucher->get_serial_number();
						$this_voucher_redeemed_status  = ( $this_voucher->get_claimed_date() != '' ) ? date( get_option( 'date_format' ), $this_voucher->get_claimed_date() ) : 'Unredeemed' ;
						$this_voucher_redemption_data = $this_voucher->get_redemption_data();
				
						$deal_vouchers[$this_voucher_deal_id][] = array('code' => $this_voucher_code, 'voucher_id' => $voucher_id, 'status' => $this_voucher_redeemed_status, 'redemption_data' => $this_voucher_redemption_data);
					}
				}
				
				$charity = '';
				$donation_amt = '';
				$donation_perct = '';
				if ( class_exists('GB_SF_Charities') && is_a( $purchase, 'Group_Buying_Purchase' ) ) {
					$charity_id = GB_SF_Charities::get_purchase_charity_id( $purchase );
					$donation_amt = GB_SF_Charities::get_purchase_charity_donation_amount($purchase);
					$donation_amt = ( $donation_amt ) ? gb_get_formatted_money($donation_amt) : '';
					$donation_perct = GB_SF_Charities::get_purchase_charity_donation_percentage($purchase);
					$donation_perct = ( $donation_perct ) ? $donation_perct.'%' : '';
					$charity = ( $charity_id) ? get_the_title( $charity_id ) : 'N/A';
				}
				
				
				//Add record for each product
				$products = $purchase->get_products();
				
				foreach ($products as $product => $item) {
					
					$deal = Group_Buying_Deal::get_instance( $item['deal_id']);
					
					$item_title = stripslashes(html_entity_decode(get_the_title($item['deal_id'])));
					
					//If no title
					if ($item_title == '') {
						$notes = 'NOTICE: DEAL RECORD WAS DELETED. '.$notes;
					}
					
					//Get merchant
					$merchant_name = '';
					if ($deal) {
						$merchant = $deal->get_merchant();
						if ($merchant) {
							$merchantID = $merchant->get_id();
							if ($merchantID) {
								$merchant_name = stripslashes(html_entity_decode(get_the_title($merchantID)));
							} 
						} //end if merchant
					} //end if deal
					
					$deal_locations = wp_get_object_terms( $deal->get_ID(), Group_Buying_Deal::LOCATION_TAXONOMY, array( 'fields' => 'names' ) );
					$deal_locations = implode(' | ', $deal_locations);
					
					//get attribute labels
					$attribute_title = '';
					if (!empty($item['data'])) {
						if ( !empty( $item['data']['attribute_id'] ) ) {
							$attribute_title = get_the_title($item['data']['attribute_id']);
						}
					}
					
					//Get deal's individual shipping cost
					//$deal_item_shipping = $deal->get_shipping();
					
				
					//Add one per Qty
					$qty = (!empty($item['quantity'])) ? $item['quantity'] : 1;
					$curr_qty = 1;
					while($qty >= $curr_qty) {
						$curr_qty++; //next
						
						//Get one of the available vouchers
						$voucher_deal_id = $item['deal_id'];
						if ( !empty( $deal_vouchers[$voucher_deal_id] )) {
							foreach($deal_vouchers[$voucher_deal_id] as $v_key => $v_data) {
								//code
								$voucher_code = $v_data['code'];
								$voucher_redeemed_status = $v_data['status'];
								$voucher_redemption_data = $v_data['redemption_data'];
								
								//unset so we don't use again
								unset($deal_vouchers[$voucher_deal_id][$v_key]);
								break;
							}
						}
						
						//Calculate credits used here, if we don't have it yet
						if (empty($credits_used)) {
							foreach( $item['payment_method'] as $payment_method_deal => $payment_method_deal_amt) {
								if ( $payment_method_deal == Group_Buying_Account_Balance_Payments::PAYMENT_METHOD || $payment_method_deal == Group_Buying_Affiliate_Credit_Payments::PAYMENT_METHOD ) { 
									$credits_used = number_format($payment_method_deal_amt, 2); //Use get_amount - not purcase->get_total(creditpaymentmethod) , bugfix - issue with credits recalculating total wrong
								}
							}
						}
						
						$purchase_array[] = array(
							'merchant' => $merchant_name,
							'deal_location' => $deal_locations,
							'id' => $purchase_id,
							'date' => $purchase_date,
							'voucher_code' => $voucher_code,
							'voucher_id' => $voucher_id,
							'voucher_status' => $voucher_redeemed_status,
							'item_title' => $item_title,
							'deal_id' => $item['deal_id'],
							'attribute_title' => $attribute_title,
							'item_price' => number_format($item['unit_price'], 2),
							'item_shipping' => number_format($deal_item_shipping, 2),
							'total' => $total,
							'shipping_total' => $shipping_total,
							'tax_total' => $tax_total,
							'credits_applied' => number_format($credits_used, 2),
							'user_id' => $userID,
							'email' => $email,
							'fname' => $first_name,
							'lname' => $last_name,
							'notes' => str_replace(array('"', ','), '', stripslashes(html_entity_decode($notes))),
							'charity' => $charity,
							'donation_total' => $donation_amt,
							'donation_perct' => $donation_perct,
							'redeem_name' => $voucher_redemption_data['name'],
							'redeem_date' => $voucher_redemption_data['date'],
							'redeem_total' => $voucher_redemption_data['total'],
							'redeem_notes' => str_replace(array('"', ','), '', stripslashes(html_entity_decode($voucher_redemption_data['notes']))),
							);
					} //While qty
				}
				
			else :
				return false;
			endif; //if in date range
			
		else :
			return false;
		endif; //if purchase object
		
		return $purchase_array;
		
	}
	
		/* Lookup Start & end Filter 
	*		Get the POST filter values and start initial values
	*/
	public function get_sales_export_startup_filter() {
		
		if (self::on_sales_export_gbs_page()) {
		
				//Set php to Wordprss local timezone for date calulations
			if (get_option('timezone_string')) {
				date_default_timezone_set(get_option('timezone_string'));
			}
			$filter_start = false;
			$filter_end = false;
			if (isset($_POST['gbsstat_preset'])) {
				
				switch ($_POST['gbsstat_preset']) {
					
					case 'lastyear': 
						$filter_start = date('Y-01-01 00:00:00', strtotime('last year'));
						$filter_end = date('Y-12-31 23:59:59', strtotime('last year'));
						break;
						
					case 'thisyear': 
						$filter_start = date('Y-01-01 00:00:00', strtotime('this year'));
						$filter_end = date('Y-12-31 23:59:59', strtotime('this year'));
						break;
						
					case 'lastmonth':
						$filter_start = date('Y-m-01 00:00:00', strtotime('last month'));
						$filter_end = date('Y-m-t 23:59:59', strtotime('last month'));
						break;
						
					case 'thismonth':
						$filter_start = date('Y-m-01 00:00:00', strtotime('this month'));
						$filter_end = date('Y-m-t 23:59:59', strtotime('this month'));
						break;
						
					case 'lastweek':
						//caculates week start and end based on current date
						$filter_start = date('Y-m-d 00:00:00', mktime(0, 0, 0, date("m"), date("d")  - 7 - date("w"), date("Y")));
						$filter_end = date('Y-m-d 23:59:59', mktime(0, 0, 0, date("m"), date("d") - 7 - date("w") + 6, date("Y")));
						break;
						
					case 'thisweek':
						//caculates week start and end based on current date
						$filter_start = date('Y-m-d 00:00:00', mktime(0, 0, 0, date("m"), date("d") - date("w"), date("Y")));
						$filter_end = date('Y-m-d 23:59:59', mktime(0, 0, 0, date("m"), date("d") - date("w") + 6, date("Y")));
						//$filter_start = date('Y-m-d 00:00:01', strtotime('Monday this week', strtotime($timestamp_base)));
						//$filter_end = date('Y-m-d 23:59:59', strtotime('Sunday this week', strtotime($timestamp_base)));
						break;
					
					case 'yesterday':
						$filter_start = date('Y-m-d 00:00:00', strtotime('yesterday'));
						$filter_end = date('Y-m-d 23:59:59', strtotime('yesterday'));
						break;
					
					case 'today':
						$filter_start = date('Y-m-d 00:00:00', strtotime('today'));
						$filter_end = date('Y-m-d 23:59:59', strtotime('today'));
						break;
						
					case 'custom':
						if ($_POST['datepickerStart'] != '' && $_POST['datepickerEnd'] != '') {
							//Ensure sent dates are formatted in ISO8601 to avoid abiguity with day and month 
							$filter_start = date('Y-m-d 00:00:00', strtotime($_POST['datepickerStart']));
							$filter_end = date('Y-m-d 23:59:59', strtotime($_POST['datepickerEnd']));
						}
						break;
				}		 
			} else {
				//Set default filter, if none is set (first page view)
				$_POST['gbsstat_preset'] = 'today';
				$filter_start = date('Y-m-d 00:00:00', strtotime('today'));
				$filter_end = date('Y-m-d 23:59:59', strtotime('today'));
			}
			
			//Set timestamp values
			$filter_start_timestamp = strtotime($filter_start); //Convert to timestamp
			$filter_end_timestamp = strtotime($filter_end); //Convert to timestamp
			$filter_preset = $_POST['gbsstat_preset']; //Set selected 
			
			return array('filter_start' => $filter_start, 
							'filter_end' => $filter_end, 
							'filter_start_timestamp' => $filter_start_timestamp,
							'filter_end_timestamp' => $filter_end_timestamp,
							'filter_preset' => $filter_preset
							);
			
		} //End if on_sales_export_gbs_page
	}
	
	/* Filter Where - apply to query */
	public function filter_where( $where = '' ) {
		$filter = self::get_sales_export_startup_filter();
		error_log('custom purchase filter: '.$filter['filter_start'].' to: '.$filter['filter_end']);
		// posts filter
		if (is_array($filter)) {
			
			$where .= " AND post_date >= '".$filter['filter_start']."' AND post_date <= '".$filter['filter_end']."'";
		}
		return $where;
	}
	
	/* Do download CSV */
	public function do_sales_export_download() {
		
		if (self::on_sales_export_gbs_page()) {
		
			if (isset($_POST['do']) && $_POST['do'] == 'download' && isset($_POST['tmpfile']) ) {
			
				$filename = 'sales.csv';
				$tmpfile = $_POST['tmpfile'];
								
				// set headers
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: private");
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$filename");
				header("Accept-Ranges: bytes");
			
				print file_get_contents($tmpfile);
				
				//Delete the temp file
				//unset($tmpfile);
				unlink($tmpfile);
				
				exit();

			} 
		}
	}

	
	private static function error_display($message = false) {
		if ($message)	echo '<div class="error fade"><p>' . $message. '</p></div>';
	}
	
	/* If viewing Sales Export add-on page */
	private static function on_sales_export_gbs_page() {
		global $pagenow;
		if ($pagenow == 'admin.php' && $_GET['page'] == 'gbs-sales-export-page-custom') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check WP version to set JQuery version
	 */
	public function get_jqueryui_ver() {
		global $wp_version;
		if (version_compare($wp_version, '3.1', '>=')) {
			return '1.8.10';
		}
		return '1.7.3';
	}
	
	public function view_gbs_sfsalesexport() {  
		include( 'views/view-sfsalesexport.php' );  
	}  
  
	public function gbs_sfsalesexport_admin_actions() {  
		add_menu_page( "Sales Export", "Sales Export", "manage_options", "gbs-sales-export-page-custom", array( get_class(), 'view_gbs_sfsalesexport'), get_stylesheet_directory_uri()."/customfunctions/sf-custom-csv-reports/assets/export-icon-sm.png", -18 );
	}
	
	private static function _load_view_to_string( $path, $args ) {
		ob_start();
		if (!empty($args)) extract($args);
		@include('views/'.$path.'.php');
		return ob_get_clean();
	}

}
SF_CustomSalesExport::init();