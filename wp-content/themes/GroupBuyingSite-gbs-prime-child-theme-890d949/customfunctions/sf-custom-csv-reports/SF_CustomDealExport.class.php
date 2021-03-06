<?php

/**
 * Custom CSV Reports for GBS
 * By StudioFidelis.com
 */
 
class SF_CustomCSVReports extends Group_Buying_Controller {
	
	private static $instance;

	public static function init() {
		
		//Add CSS & JS for Datepicker
		add_action( 'admin_enqueue_scripts', array( get_class(), 'queue_admin_resources' ) );
		
		// Changes to GBS Purchase Reports
		add_filter('set_deal_purchase_report_data_column', array( get_class(),'purchase_report_data_column'), 999, 1);
		add_filter('set_deal_purchase_report_data_records', array( get_class(),'purchase_report_data_records'), 999, 1);
		
		// Merchant Deal purcahse Report
		add_filter('set_merchant_purchase_report_column', array( get_class(),'purchase_report_data_column'), 999, 1);
		add_filter('set_merchant_purchase_report_records', array( get_class(),'purchase_report_data_records'), 999, 1);
		
		// Merchant all purchases Report
		add_filter('set_merchant_purchases_report_data_column', array( get_class(),'purchase_report_data_column'), 999, 1);
		add_filter('set_merchant_purchases_report_data_records', array( get_class(),'purchase_report_data_records'), 999, 1);
		
		// Changes to Voucher Reports
		add_filter('set_deal_voucher_report_data_column', array( get_class(),'voucher_report_data_column'), 999, 1);
		add_filter('set_deal_voucher_report_data_records',  array( get_class(),'voucher_report_data_records'), 999, 1);
		add_filter('set_merchant_voucher_report_data_column', array( get_class(),'voucher_report_data_column'), 999, 1);
		add_filter('set_merchant_voucher_report_data_records', array( get_class(),'voucher_report_data_records'), 999, 1);
		
		//Changes to Account reports
		add_filter('set_accounts_report_data_column', array( get_class(),'account_report_data_column'), 999, 1);
		add_filter('set_accounts_report_data_records',  array( get_class(),'account_report_data_records'), 999, 1);
		
		// Admin columns
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_columns', array( get_class(), 'deal_register_columns' ), 11 );
		add_filter( 'manage_'.Group_Buying_Deal::POST_TYPE.'_posts_custom_column', array( get_class(), 'deal_column_display' ), 11, 2 );
		// Handle download
		add_action('wp_loaded', array( get_class(), 'tigger_report_download'));
		
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
	
	/* Changes to GBS Purchase reports */
	
	public function purchase_report_data_column( $columns ) {
		
		unset($columns['date']); //unset so we can reorder
		unset($columns['id']); //unset so we can reorder
		
		$add_columns['date'] = gb__('Date of Purchase');
		$add_columns['id'] = gb__('Order #');
		$add_columns['deal_names'] = gb__('Deal Name(s)');
		$add_columns['voucher_ids'] = gb__('Voucher ID(s)');
		$add_columns['voucher_codes'] = gb__('Voucher Code(s)');
		
		$new_columns = array_merge($add_columns, $columns); //add to front of columns
		return $new_columns;
	}
	public function purchase_report_data_records( $array ) {
		if ( !is_array($array) ) {
			return; // nothing to do.
		}
		$new_array = array();
		foreach ( $array as $records ) {
			
			$merge_record = false;
			
			$items = array();
			$purchase = Group_Buying_Purchase::get_instance($records['id']);
			
			//set date if not set
			if ( !isset($records['date']) ) {
				$merge_record['date'] = get_the_time( apply_filters( 'gb_reports_date_format', get_option( 'date_format' ) ), $purchase->get_ID() );
			}
			
			//Get Vouchers & prepare for being matched up with deals
			$deal_vouchers = array();
			$deal_names = array();
			$voucher_codes = array();
			$voucher_ids = array();
			$vouchers = $purchase->get_vouchers();
			if (!empty($vouchers)) {
				foreach ($vouchers as $voucher_id) {
					$this_voucher = Group_Buying_Voucher::get_instance($voucher_id);
					$this_voucher_deal_id 	= $this_voucher->get_deal_id();
					
					//Exclude any non merchant deal ( if merchant report )
					if ( ($_GET['report'] == 'merchant_purchase' || $_GET['report'] == 'deal_purchase' ) && $_GET['id'] ) {
						$order_item_merchant_id = gb_get_merchant_id ($this_voucher_deal_id );
						$current_merchant_id = gb_get_merchant_id ($_GET['id']);
						if ( $current_merchant_id && $order_item_merchant_id && $order_item_merchant_id == $current_merchant_id ) {
							//this is the merchant id for the current merchant report
						} else {
							continue; //skip this	
						}
						
					}
					$this_voucher_code 		= $this_voucher->get_serial_number();
					$deal_names[] = get_the_title( $this_voucher_deal_id );
					$voucher_codes[] = $this_voucher_code;
					$voucher_ids[] = $voucher_id;
				}
			}
			
			$merge_record['deal_names'] = implode(' | ', str_replace(array('"', ','), '', $deal_names));
			$merge_record['voucher_ids'] = implode(' | ', $voucher_ids);
			$merge_record['voucher_codes'] = implode(' | ', $voucher_codes);
			
			if ($merge_record) {
				$new_array[] = array_merge($records, $merge_record);
			} else {
				$new_array[] = $records;	
			}
			
		}
		return $new_array;
	}
	
	//Voucher reports
	public function voucher_report_data_column( $columns ) {
		
		unset($columns['id']); //unset so we can reorder
		unset($columns['voucher_id']); //unset so we can reorder
		//unset($columns['date']); //unset so we can reorder
		
		$add_columns['date'] = gb__('Date of Purchase');
		$add_columns['id'] = gb__('Order #');
		$add_columns['voucher_id'] = gb__('Voucher ID');
		
		$new_columns = array_merge($add_columns, $columns); //add to front of columns
		return $new_columns;
	}
	public function voucher_report_data_records( $array ) {
		if ( !is_array($array) ) {
			return; // nothing to do.
		}
		$new_array = array();
		foreach ( $array as $records ) {
			
			$merge_record = false;
			
			$items = array();
			$voucher = Group_Buying_Voucher::get_instance($records['voucher_id']);
			$purchase = $voucher->get_purchase();
			
			//set date if not set
			if ( !isset($records['date']) ) {
				$merge_record['date'] = get_the_time( apply_filters( 'gb_reports_date_format', get_option( 'date_format' ) ), $purchase->get_ID() );
			}
			
			if ($merge_record) {
				$new_array[] = array_merge($records, $merge_record);
			} else {
				$new_array[] = $records;	
			}
			
		}
		return $new_array;
	}
	
	//Account reports
	public function account_report_data_column( $columns ) {
		
		unset($columns['id']); //unset so we can reorder
		unset($columns['name']); //unset so we can reorder
		
		$add_columns['id'] = gb__('Account ID');
		$add_columns['name'] = gb__('Name');
		$add_columns['user_id'] = gb__('WP User ID');
		$add_columns['email'] = gb__('Email');
		
		$new_columns = array_merge($add_columns, $columns); //add to front of columns
		
		//Add to end
		$new_columns['preferred_location'] = gb__('Preferred Location');
		return $new_columns;
	}
	public function account_report_data_records( $array ) {
		if ( !is_array($array) ) {
			return; // nothing to do.
		}
		$new_array = array();
		foreach ( $array as $records ) {
			
			$merge_record = false;
			
			$items = array();
			$account = Group_Buying_Account::get_instance_by_id($records['id']);
			$user_id = Group_Buying_Account::get_user_id_for_account( $account->get_ID() );
			$user_data = get_userdata( $user_id );
			
			$merge_record['user_id'] = $user_id;
			$merge_record['email'] = $user_data->user_email;
			$merge_record['preferred_location'] = get_post_meta( $account->get_ID(), '_gb_contact_preferred_location', TRUE );
			
			
			if ($merge_record) {
				$new_array[] = array_merge($records, $merge_record);
			} else {
				$new_array[] = $records;	
			}
			
		}
		return $new_array;
	}
	
	
	/* Custom Deal & Merchant Data Export to CSV */
	public static function deal_register_columns( $columns ) {
		$columns['sf_deal_data_download'] = self::__( 'Export Deal & Merchant' );
		return $columns;
	}
	public static function deal_column_display( $column_name, $id ) {
		global $post;
		$deal = Group_Buying_Deal::get_instance( $id );

		if ( !$deal )
			return; // return for that temp post

		switch ( $column_name ) {
	
		case 'sf_deal_data_download':
			echo '<p><a href="/wp-admin/?sf_deal_data_download='.$id.'" class="button">'.self::__( 'Download File' ).'</a></p>';
			break;
		default:
			break;
		}
	}
	
	public function tigger_report_download() {
		if (is_admin() && isset($_GET['sf_deal_data_download']) ) {
			self::deal_export($_GET['sf_deal_data_download']);
		}
	}
	
	public function deal_export($deal_id) {
			
		$fields = array();
		$deal = Group_Buying_Deal::get_instance($deal_id);
		
		if ( is_a( $deal, 'Group_Buying_Deal' ) ) {
			
			//DEAL FIELDS
			//from Group_Buying_Deal_Edit::edit_fields
			$post_obj = get_post( $deal->get_ID() );
			$title = $deal->get_title();
			$content = apply_filters( 'the_content', $post_obj->post_content );
			$expiration = $deal->get_expiration_date();
			if ( $expiration > 0 ) {
				$expiration = date( 'm/d/Y G:i', $expiration );
			} else {
				$expiration = 'Deal Does not Expire';
			}
			$deal_locations = implode(' | ', wp_get_object_terms( $deal->get_ID(), Group_Buying_Deal::LOCATION_TAXONOMY, array( 'fields' => 'names' ) ));
			$price = $deal->get_price();
			$shipping = $deal->get_shipping_meta();
			$min = $deal->get_min_purchases();
			$max = $deal->get_max_purchases();
			$max_per_user = $deal->get_max_purchases_per_user();
			$value = $deal->get_value();
			$amount_saved = $deal->get_amount_saved();
			$highlights = $deal->get_highlights();
			$fine_print = $deal->get_fine_print();
			$voucher_expiration = ( $deal->get_voucher_expiration_date() ) ? date( 'm/d/Y G:i', $deal->get_voucher_expiration_date() ) : date( 'm/d/Y G:i', time()+60*60*24 ) ;
			$voucher_how_to_use = $deal->get_voucher_how_to_use();
			$voucher_map = $deal->get_voucher_map();

			$voucher_locations = $deal->get_voucher_locations();
			while ( count( $voucher_locations ) < Group_Buying_Deal::MAX_LOCATIONS ) {
				$voucher_locations[] = '';
			}
			$voucher_serial_numbers = implode( ',', $deal->get_voucher_serial_numbers() );

			if ( is_a($deal,'Group_Buying_Deal') ) {
				$post_id = $deal->get_id();
				$img_array = wp_get_attachment_image_src(get_post_thumbnail_id( $post_id ));
				$deal_image = $img_array[0];
			}
			
			//Fields
			$fields['title'] = array(
				'weight' => 1,
				'label' => self::__( 'Deal Name' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => $title,
				'description' => gb__('<span>Required:</span> Advertised title of deal.')
			);
			
	
			$fields['description'] = array(
				'weight' => 2,
				'label' => self::__( 'Deal Description' ),
				'type' => 'textarea',
				'required' => TRUE,
				'default' => $content,
				'description' => gb__('<span>Required:</span> Full description of the deal.')
			);
	
			$fields['thumbnail'] = array(
				'weight' => 3,
				'label' => self::__( 'Deal Image' ),
				'type' => 'file',
				'required' => FALSE,
				'default' => $deal_image,
				'description' => gb__('<span>Optional:</span> Featured image for the deal.')
			);
	
			$fields['exp'] = array(
				'weight' => 5,
				'label' => self::__( 'Deal Expiration' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => $expiration,
				'description' => gb__('<span>Required:</span> Expiration for the deal; purchases will not be allowed after this time.')
			);
	
			$fields['price'] = array(
				'weight' => 7,
				'label' => self::__( 'Deal Price' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => $price,
				'description' => gb__('<span>Required:</span> Purchase price.')
			);
	
			$fields['shipping'] = array(
				'weight' => 10,
				'label' => self::__( 'Deal Shipping Cost' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => $shipping,
				'description' => gb__('<span>Optional:</span> Locations this deal will be available.')
			);
	
			$site_locations = get_terms( array( Group_Buying_Deal::LOCATION_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all' ) );
			$location_options = array();
			foreach ( $site_locations as $site_local ) {
				$location_options[$site_local->term_id] = $site_local->name;
			}
			$fields['locations'] = array(
				'weight' => 12,
				'label' => self::__( 'Locations' ),
				'type' => 'multiselect',
				'required' => FALSE,
				'options' => $location_options,
				'default' => $deal_locations,
				'description' => gb__('<span>Required:</span> Locations this deal will be available.')
			);
	
			// Heading
			$fields['purchase_limits'] = array(
				'weight' => 16,
				'label' => self::__( 'Purchase Limits' ),
				'type' => 'heading',
				'required' => FALSE,
			);
	
			$fields['min_purchases'] = array(
				'weight' => 20,
				'label' => self::__( 'Minimum Purchases' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => $min,
				'description' => gb__('<span>Required:</span> Number of purchases required before the deal is successfully made.')
			);
	
			$fields['max_purchases'] = array(
				'weight' => 25,
				'label' => self::__( 'Max Purchases' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => $max,
				'description' => gb__('<span>Required:</span> Maximum number of purchases allowed for this deal.')
			);
	
			$fields['max_per_user'] = array(
				'weight' => 30,
				'label' => self::__( 'Max Purchases Per User' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => $max_per_user,
				'description' => gb__('<span>Required:</span> Maximum number of purchases allowed for this deal for one user.')
			);
	
			// Heading
			$fields['deal_details'] = array(
				'weight' => 31,
				'label' => self::__( 'Deal Details' ),
				'type' => 'heading',
				'required' => FALSE,
			);
	
			$fields['value'] = array(
				'weight' => 35,
				'label' => self::__( 'Value' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => $value,
				'description' => gb__('<span>Required:</span> Advertise worth.')
			);
	
			$fields['amount_saved'] = array(
				'weight' => 40,
				'label' => self::__( 'Savings' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => $amount_saved,
				'description' => gb__('<span>Optional:</span> Savings that&rsquo;s advertised to the visitors. Examples: "40% off" or "$25 Discount".')
			);
	
			$fields['highlights'] = array(
				'weight' => 45,
				'label' => self::__( 'Highlights' ),
				'type' => 'textarea',
				'required' => TRUE,
				'default' => $highlights,
				'description' => gb__('<span>Required:</span> Highlights about the deal.')
			);
	
			$fields['fine_print'] = array(
				'weight' => 50,
				'label' => self::__( 'Fine Print' ),
				'type' => 'textarea',
				'required' => TRUE,
				'default' => $fine_print,
				'description' => gb__('<span>Required:</span> Fine print for this deal and voucher.')
			);
	
			// Heading
	
			$fields['voucher_expiration'] = array(
				'weight' => 54,
				'label' => self::__( 'Voucher Expiration' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => $voucher_expiration,
				'description' => gb__('<span>Required:</span> Voucher expiration.')
			);
	
			$fields['voucher_details'] = array(
				'weight' => 54,
				'label' => self::__( 'Voucher' ),
				'type' => 'heading',
				'required' => FALSE,
			);
	
			$fields['voucher_how_to_use'] = array(
				'weight' => 55,
				'label' => self::__( 'How to use' ),
				'type' => 'textarea',
				'required' => TRUE,
				'default' => $voucher_how_to_use,
				'description' => gb__('<span>Required:</span> How the voucher should be used.')
			);
	
			foreach ( $voucher_locations as $index => $location ) {
				$count = (int)$index+1;
				$fields['voucher_locations['.$index.']'] = array(
					'weight' => 60+$index,
					'label' => self::__( 'Redemption Location' ) .' #'.$count,
					'type' => 'text',
					'required' => FALSE,
					'default' => $location,
				);
			}
	
			$fields['voucher_map'] = array(
				'weight' => 65,
				'label' => self::__( 'Map ( Google Maps iframe )' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => esc_html__( $voucher_map ),
				'description' => gb__('<span>Optional:</span> Go to <a href="http://www.mapquest.com/">MapQuest</a> or <a href="http://www.google.com/maps" title="Google Maps">Google Maps</a> and create a map with multiple or single locations. Click on "Link/Embed" at the the top right of your map (MapQuest) or the link icon to the left of your map (Google Maps), copy the code from "Paste HTML to embed in website" here.' )
			);
	
			$fields['voucher_serial_numbers'] = array(
				'weight' => 70,
				'label' => self::__( 'Voucher Codes' ),
				'type' => 'textarea',
				'required' => FALSE,
				'default' => $voucher_serial_numbers,
				'description' => gb__('<span>Optional:</span> Enter a comma separated list to use your own custom codes for this deal instead of them being dynamically generated. The amount of codes entered should not be less than that of the maximum purchases set above.')
			);
			
			
	
			$fields = apply_filters( 'gb_deal_submission_fields', $fields, $deal );
			$fields = apply_filters( 'gb_edit_deal_submission_fields', $fields, $deal );
			uasort( $fields, array( get_class(), 'sort_by_weight' ) );
			
			//Remove unwanted fields
			unset($fields['images']);
			unset($fields['thumbnail']);
			unset($fields['voucher_details']);
			
			//Change field labels
			//Change field lables
			if ( isset($fields['agree_terms']) ) {
				$fields['agree_terms']['label'] = 'Deal Submission Merchant Terms Agreement';
			}
			if ( isset($fields['agree_reviewed_information']) ) {
				$fields['agree_reviewed_information']['label'] = 'Deal Submission Reviewed Information Agreement';
			}
			
			//MERCHANT FIELDS
			//from Group_Buying_Merchants::merchant_contact_info_fields()
			
			$merchant_fields = Group_Buying_Controller::get_standard_address_fields();

			unset( $merchant_fields['first_name'] );
			unset( $merchant_fields['last_name'] );
			
			$merchant_fields['merchant_title'] = array(
				'weight' => 0,
				'label' => self::__( 'Merchant Name' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => ''
			);
	
			$merchant_fields['merchant_description'] = array(
				'weight' => 5,
				'label' => self::__( 'Merchant Description' ),
				'type' => 'textarea',
				'required' => TRUE,
				'default' => ''
			);
			
			/*
			$merchant_fields['merchant_thumbnail'] = array(
				'weight' => 7,
				'label' => self::__( 'Merchant Image' ),
				'type' => 'file',
				'required' => FALSE,
				'default' => '',
				'description' => gb__('<span>Optional:</span> Featured image for the merchant.')
			);
			*/
	
			$merchant_fields['name'] = array(
				'weight' => 11,
				'label' => self::__( 'Contact Name' ),
				'type' => 'text',
				'required' => TRUE,
				'default' => '',
			);
			/*/
			$merchant_fields['title'] = array(
				'weight' => 5,
				'label' => self::__('Contact Title'),
				'type' => 'text',
				'required' => TRUE,
				'default' => '',
			);
			/**/
			$merchant_fields['phone'] = array(
				'weight' => 16,
				'label' => self::__( 'Contact Phone' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => '',
			);
	
			$merchant_fields['website'] = array(
				'weight' => 26,
				'label' => self::__( 'Website' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => '',
			);
			$merchant_fields['facebook'] = array(
				'weight' => 27,
				'label' => self::__( 'Facebook' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => '',
			);
			$merchant_fields['twitter'] = array(
				'weight' => 28,
				'label' => self::__( 'Twitter' ),
				'type' => 'text',
				'required' => FALSE,
				'default' => '',
			);
			
			
			$merchant = $deal->get_merchant();
			
			if ( is_a( $merchant, 'Group_Buying_Merchant' ) ) {
				$merchant_post = $merchant->get_post();
				$merchant_fields['merchant_title']['default'] = $merchant_post->post_title;
				$merchant_fields['merchant_description']['default'] = $merchant_post->post_content;
				$merchant_fields['name']['default'] = $merchant->get_contact_name();
				$merchant_fields['street']['default'] = $merchant->get_contact_street();
				$merchant_fields['city']['default'] = $merchant->get_contact_city();
				$merchant_fields['zone']['default'] = $merchant->get_contact_state();
				$merchant_fields['postal_code']['default'] = $merchant->get_contact_postal_code();
				$merchant_fields['country']['default'] = $merchant->get_contact_country();
				$merchant_fields['phone']['default'] = $merchant->get_contact_phone();
				$merchant_fields['website']['default'] = $merchant->get_website();
				$merchant_fields['facebook']['default'] = $merchant->get_facebook();
				$merchant_fields['twitter']['default'] = $merchant->get_twitter();
	
	
				$img_array = wp_get_attachment_image_src(get_post_thumbnail_id( $merchant->get_id() ));
				$merchant_fields['merchant_thumbnail']['default'] = $img_array[0];
			}
	
			$merchant_fields = apply_filters( 'gb_merchant_register_contact_info_fields', $merchant_fields, $merchant );
			uasort( $merchant_fields, array( get_class(), 'sort_by_weight' ) );
			
			//Remove unwanted fields
			//unset($merchant_fields['label_mailing_address']);
			//unset($merchant_fields['label_business_address']);
			unset($merchant_fields['copy_address']);
			
			//Change field lables
			if ( isset($merchant_fields['agree_terms']) ) {
				$merchant_fields['agree_terms']['label'] = 'Merchant Account Terms Agreement';
			}
			if ( isset($merchant_fields['label_mailing_address']) ) {
				$merchant_fields['label_mailing_address']['label'] = 'Merchant Mailing Address';
			}
			if ( isset($merchant_fields['label_business_address']) ) {
				$merchant_fields['label_business_address']['label'] = 'Merchant Business Address';
			}
			
			//Merge merchant_fields with deal fields
			foreach ($merchant_fields as $mer_key => $mer_field) {
				//prepend to field key so we don't accidently overwrite anything
				$merchant_fields['mer_'.$mer_key] = $mer_field;
				unset($merchant_fields[$mer_key]);
			}
			//$fields = array_merge($fields, $merchant_fields);
			
		}
		//Do we have anything to export?
		if ( !empty($fields) || !empty($merchant_fields) ) {
			self::download_data('deal_merchant_submission_'.$deal_id.'.html', $fields, $merchant_fields);
		}
	}
	
	public function download_data($filename = 'gbs_deal_export.html', $deal_fields = false, $merchant_fields = false) {
	
		
		// set headers
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: private" );
		header( "Content-type: application/octet-stream" );
		header( "Content-Disposition: attachment; filename=$filename" );
		header( "Accept-Ranges: bytes" );
		
		echo '<!doctype html>
			<html>
			<head>
			<meta charset="UTF-8">
			<title>'.$filename.'</title>
		<style>
		body {
			margin: 5px 15px;	
		}
		body, table, p, td {
			font-family: Arial, Tahoma, serif;
			font-size: 14px;	
			color: #000000;
		}
		.label {
			color: #444;
			font-size: 16px;
		}
		.label_big {
			color: #222;
			font-size: 18px;
		}
		h1, h2, h3, h4, h5 {
			color: #444;
		}
		p {
			margin: 0;
			padding: 0;	
		}
		</style>
		</head>
		<body>';
		
		//Header
		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td align="right" width="45%" valign="top">
				<img src="'.get_stylesheet_directory_uri()."/customfunctions/sf-custom-csv-reports/assets/export_logo_header.png".'" width="200" border="0"></td>
			<td align="left" valign="top" width="30">
				&nbsp;
			</td>
			<td align="left" valign="top">
				<br>
				Support Team<br>
				704-380-3250 Office<br>
				704-838-0678 Fax<br>
				Support@LocalSharingTree.com
			</td>
		  </tr>
		</table>';
		
		//Business
		echo '<h2 align="center">Business Information</h2><br>
		<table width="100%" border="0" cellspacing="5" cellpadding="0">
		';
		foreach ( $merchant_fields as $field ) {
			if ( $field['type'] == 'heading' || $field['type'] == 'hidden' ) {
				?>
              <tr>
                <td colspan="2" align="left" class="label_big" valign="top">&nbsp;</td>
              </tr>
              <tr>
                <td colspan="2" align="left" class="label_big" valign="top"><?php echo $field['label']; ?></td>
              </tr>
             	<?php
			} elseif ( $field['type'] == 'checkbox' ) {
				?>
              <tr>
                <td nowrap="nowrap" align="left" class="label" width="200" valign="top"><?php echo $field['label']; ?></td>
                <td align="left" valign="top"><?php echo '&#10003; YES'; //($field['default']) ? '&#10003; YES' : 'NO'; ?></td>
              </tr>
				<?php
			} else {
				?>
              <tr>
                <td nowrap="nowrap" align="left" class="label" width="200" valign="top"><?php echo $field['label']; ?></td>
                <td align="left" valign="top"><?php echo $field['default']; ?></td>
              </tr>
          		<?php
			}
		}
		echo '</table>';
		
		//Deal
		echo '<h2 align="center">Deal Information</h2><br>
		<table width="100%" border="0" cellspacing="5" cellpadding="0">
		';
		foreach ( $deal_fields as $field ) {
			if ( $field['type'] == 'heading' || $field['type'] == 'hidden' ) {
				?>
              <tr>
                <td colspan="2" align="left" class="label_big" valign="top">&nbsp;</td>
              </tr>
              <tr>
                <td colspan="2" align="left" class="label_big" valign="top"><?php echo $field['label']; ?></td>
              </tr>
           		<?php
			} elseif ( $field['type'] == 'checkbox' ) {
				?>
              <tr>
                <td nowrap="nowrap" align="left" class="label" width="200" valign="top"><?php echo $field['label']; ?></td>
                <td align="left" valign="top"><?php echo '&#10003; YES'; //($field['default']) ? '&#10003; YES' : 'NO'; ?></td>
              </tr>
          		<?php
			} else {
				?>
              <tr>
                <td nowrap="nowrap" align="left" class="label" width="200" valign="top"><?php echo $field['label']; ?></td>
                <td align="left" valign="top"><?php echo $field['default']; ?></td>
              </tr>
          		<?php
			}
		}
		echo '</table>';

		
		echo '</body>
		</html>';
		
		exit();
	}

}
SF_CustomCSVReports::init();