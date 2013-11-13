<?php

class Group_Buying_Deals_Submit extends Group_Buying_Controller {
	const SUBMIT_PATH_OPTION = 'gb_submit_deal_path';
	const SUBMIT_QUERY_VAR = 'gb_submit_deal';
	const FORM_ACTION = 'gb_submit_deal';
	private static $submit_path = 'merchant/submit-deal';
	private static $instance;

	public static function init() {
		self::$submit_path = get_option( self::SUBMIT_PATH_OPTION, self::$submit_path );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 1 );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_submit_callback' ), 10, 1 );
		add_action( 'parse_request', array( get_class(), 'maybe_process_form' ) );

		// AJAX
		add_action( 'wp_ajax_gb_location_add', array( get_class(), 'add_location' ) );
		add_action( 'wp_ajax_gb_deal_publish', array( get_class(), 'ajax_publish' ) );
		add_action( 'wp_ajax_gb_deal_draft', array( get_class(), 'ajax_draft' ) );
	}

	/**
	 * Register the path callback
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_submit_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$submit_path,
			'title' => 'Account Edit',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_submit_page' ),
			'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$submit_path ).'.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::SUBMIT_QUERY_VAR, $args );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_merchant_paths';

		// Settings
		register_setting( $page, self::SUBMIT_PATH_OPTION );
		add_settings_field( self::SUBMIT_PATH_OPTION, self::__( 'Merchant Submit Path' ), array( get_class(), 'display_path' ), $page, $section );
	}

	public static function display_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="' . self::SUBMIT_PATH_OPTION . '" id="' . self::SUBMIT_PATH_OPTION . '" value="' . esc_attr( self::$submit_path ) . '" size="40"/><br />';
	}

	public static function on_submit_page() {
		$submit_page = self::get_instance();
		// View template
		$submit_page->view_submit_form();
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
		wp_enqueue_media();
		add_action( 'wp_enqueue_scripts', array( get_class(), 'enqueue_resources' ) );
	}

	/**
	 * Check if form is submitted to another other page.
	 * @return  
	 */
	public function maybe_process_form() {
		if ( isset( $_POST['gb_deal_submission'] ) && $_POST['gb_deal_submission'] == self::FORM_ACTION ) {
			$submit_page = self::get_instance();
			$submit_page->process_form_submission();
		}
	}

	public function enqueue_resources() {
		// Timepicker
		wp_enqueue_script( 'gb_frontend_deal_submit' );
		wp_enqueue_style( 'gb_frontend_deal_submit_timepicker_css' );
	}

	public function view_submit_form() {
		remove_filter( 'the_content', 'wpautop' );
		// Load submitted, in case there is a problem and the merchant needs to resubmit
		$expiration = isset( $_POST['gb_deal_exp'] ) ? $_POST['gb_deal_exp'] : '';
		$capture_before_expiration = isset( $_POST['gb_deal_capture_before_expiration'] );
		$price = isset( $_POST['gb_deal_price'] ) ? $_POST['gb_deal_price'] : '';
		$deal_locations = isset( $_POST['gb_deal_locations'] ) ? $_POST['gb_deal_locations'] : array();
		//$dynamic_price = isset( $_POST['gb_deal_dynamic_price'] ) ? $_POST['gb_deal_dynamic_price'] : array();
		$shipping = isset( $_POST['gb_deal_shipping'] ) ? $_POST['gb_deal_shipping'] : '';
		// $thumb = isset( $_POST['gb_deal_thumbnail'] ) ? $_POST['gb_deal_thumbnail'] : '';
		$tax = isset( $_POST['gb_deal_tax'] ) ? $_POST['gb_deal_tax'] : '';
		$min = isset( $_POST['gb_deal_min_purchases'] ) ? (int)$_POST['gb_deal_min_purchases'] : 0;
		$max = isset( $_POST['gb_deal_max_purchases'] ) ? (int)$_POST['gb_deal_max_purchases'] : Group_Buying_Deal::NO_MAXIMUM;
		$max_per_user = isset( $_POST['gb_deal_max_per_user'] ) ? (int)$_POST['gb_deal_max_per_user'] : Group_Buying_Deal::NO_MAXIMUM;
		$value = isset( $_POST['gb_deal_value'] ) ? $_POST['gb_deal_value'] : '';
		$amount_saved = isset( $_POST['gb_deal_amount_saved'] ) ? $_POST['gb_deal_amount_saved'] : '';
		$highlights = isset( $_POST['gb_deal_highlights'] ) ? $_POST['gb_deal_highlights'] : '';
		$fine_print = isset( $_POST['gb_deal_fine_print'] ) ? $_POST['gb_deal_fine_print'] : '';
		$rss_excerpt = isset( $_POST['gb_deal_rss_excerpt'] ) ? $_POST['gb_deal_rss_excerpt'] : '';
		$voucher_expiration_date = isset( $_POST['gb_deal_voucher_expiration'] ) ? $_POST['gb_deal_voucher_expiration'] : '';
		$voucher_how_to_use = isset( $_POST['gb_deal_voucher_how_to_use'] ) ? $_POST['gb_deal_voucher_how_to_use'] : '';
		//$voucher_id_prefix = isset( $_POST['gb_deal_voucher_id_prefix'] ) ? $_POST['gb_deal_voucher_id_prefix'] : '';
		$voucher_locations = isset( $_POST['gb_deal_voucher_locations'] ) ? $_POST['gb_deal_voucher_locations'] : '';
		//$voucher_logo = isset( $_POST['gb_deal_voucher_logo'] ) ? $_POST['gb_deal_voucher_logo'] : '';
		$voucher_map = isset( $_POST['gb_deal_voucher_map'] ) ? $_POST['gb_deal_voucher_map'] : '';
		$voucher_serial_numbers = isset( $_POST['gb_deal_voucher_serial_numbers'] ) ? $_POST['gb_deal_voucher_serial_numbers'] : '';

		self::load_view( 'merchant/submit-deal', array( 'fields' => $this->deal_submission_fields(), 'form_action' => self::FORM_ACTION ) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a account page
	 */
	public static function is_submit_page() {
		return GB_Router_Utility::is_on_page( self::SUBMIT_QUERY_VAR );
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
		return self::__('Submit Deal');
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the merchant page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$submit_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::SUBMIT_QUERY_VAR );
		}
	}

	protected function deal_submission_fields() {
		$fields['title'] = array(
			'weight' => 1,
			'label' => self::__( 'Deal Name' ),
			'type' => 'text',
			'required' => TRUE,
			'default' => '',
			'description' => gb__('<span>Required:</span> Advertised title of deal.')
		);

		$fields['description'] = array(
			'weight' => 2,
			'label' => self::__( 'Deal Description' ),
			'type' => 'textarea',
			'required' => TRUE,
			'default' => '',
			'description' => gb__('<span>Required:</span> Full description of the deal.')
		);

		$fields['images'] = array(
			'weight' => 3,
			'label' => self::__( 'Deal Images' ),
			'type' => 'bypass',
			'required' => FALSE,
			'default' => '',
			'description' => gb__('<span>Optional:</span> Upload/Select images to be used for deal.'),
			'output' => '<span class="upload_image_button alt_button" data-input-name="submission_images" data-uploader-title="'.self::__('Upload Deal Images').'" data-uploader-button-text="'.self::__('Add to Submission').'" data-uploader-allow-multiple="true">'.self::__('Upload').'</span><div id="submission_images-thumbnails" class="submitted_thumbnails_wrap clearfix"></div>' // 'submission_images' is the unique and must be changed for jquery uploader functionality to work with multiple upload options; change data-input-name and div id. The form processor will need to handle this id manually.
		);

		$fields['exp'] = array(
			'weight' => 5,
			'label' => self::__( 'Deal Expiration' ),
			'type' => 'text',
			'required' => TRUE,
			'default' => '',
			'description' => gb__('<span>Required:</span> Expiration for the deal; purchases will not be allowed after this time.')
		);

		$fields['price'] = array(
			'weight' => 7,
			'label' => self::__( 'Deal Price' ),
			'type' => 'text',
			'required' => TRUE,
			'default' => '29',
			'description' => gb__('<span>Required:</span> Purchase price.')
		);

		$fields['shipping'] = array(
			'weight' => 10,
			'label' => self::__( 'Deal Shipping Cost' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '0',
			'description' => gb__('<span>Optional:</span> Shipping for each deal purchased.')
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
			'default' => '',
			'description' => gb__('Locations this deal will be available.')
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
			'default' => '1',
			'description' => gb__('<span>Required:</span> Number of purchases required before the deal is successfully made.')
		);

		$fields['max_purchases'] = array(
			'weight' => 25,
			'label' => self::__( 'Max Purchases' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '10000',
			'description' => gb__('<span>Required:</span> Maximum number of purchases allowed for this deal.')
		);

		$fields['max_per_user'] = array(
			'weight' => 30,
			'label' => self::__( 'Max Purchases Per User' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '10000',
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
			'default' => '',
			'description' => gb__('<span>Required:</span> Advertise worth.')
		);

		$fields['amount_saved'] = array(
			'weight' => 40,
			'label' => self::__( 'Savings' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '',
			'description' => gb__('<span>Optional:</span> Savings that&rsquo;s advertised to the visitors. Examples: "40% off" or "$25 Discount".')
		);

		$fields['highlights'] = array(
			'weight' => 45,
			'label' => self::__( 'Highlights' ),
			'type' => 'textarea',
			'required' => TRUE,
			'default' => '',
			'description' => gb__('<span>Required:</span> Highlights about the deal.')
		);

		$fields['fine_print'] = array(
			'weight' => 50,
			'label' => self::__( 'Fine Print' ),
			'type' => 'textarea',
			'required' => TRUE,
			'default' => '',
			'description' => gb__('<span>Required:</span> Fine print for this deal and voucher.')
		);

		// Heading

		$fields['voucher_expiration'] = array(
			'weight' => 54,
			'label' => self::__( 'Voucher Expiration' ),
			'type' => 'text',
			'required' => TRUE,
			'default' => '',
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
			'default' => '',
			'description' => gb__('<span>Required:</span> How the voucher should be used.')
		);

		for ($i=0; $i < Group_Buying_Deal::MAX_LOCATIONS; $i++) {
			$count = $i+1;
			$fields['voucher_locations['.$i.']'] = array(
				'weight' => 60+$i,
				'label' => self::__( 'Redemption Location' ) .'&nbsp;#'.$count,
				'type' => 'text',
				'required' => FALSE,
				'default' => '',
			);
		}

		$fields['voucher_map'] = array(
			'weight' => 65,
			'label' => self::__( 'Map ( Google Maps iframe )' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '',
			'description' => gb__('<span>Optional:</span> Go to <a href="http://www.mapquest.com/">MapQuest</a> or <a href="http://www.google.com/maps" title="Google Maps">Google Maps</a> and create a map with multiple or single locations. Click on "Link/Embed" at the the top right of your map (MapQuest) or the link icon to the left of your map (Google Maps), copy the code from "Paste HTML to embed in website" here.' )
		);

		$fields['voucher_serial_numbers'] = array(
			'weight' => 70,
			'label' => self::__( 'Voucher Codes' ),
			'type' => 'textarea',
			'required' => FALSE,
			'description' => gb__('<span>Optional:</span> Enter a comma separated list to use your own custom codes for this deal instead of them being dynamically generated. The amount of codes entered should not be less than that of the maximum purchases set above.')
		);

		$fields = apply_filters( 'gb_deal_submission_fields', $fields );
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		return $fields;
	}

	private function process_form_submission() {
		$errors = array();
		$title = isset( $_POST['gb_deal_title'] ) ? esc_html( $_POST['gb_deal_title'] ) : '';
		$allowed_tags = wp_kses_allowed_html( 'post' );
		$allowed_tags['iframe'] = array(
			'width' => true,
			'height' => true,
			'src' => true,
			'frameborder' => true,
			'webkitAllowFullScreen' => true,
			'mozallowfullscreen' => true,
			'allowfullscreen' => true
		);
		$description = isset( $_POST['gb_deal_description'] ) ? wp_kses( $_POST['gb_deal_description'], $allowed_tags ) : '';
		$locations = isset( $_POST['gb_deal_locations'] ) ? $_POST['gb_deal_locations'] : array();
		$expiration = isset( $_POST['gb_deal_exp'] ) ? $_POST['gb_deal_exp'] : '';
		//$capture_before_expiration = isset( $_POST['gb_deal_capture_before_expiration'] );
		$price = isset( $_POST['gb_deal_price'] ) ? $_POST['gb_deal_price'] : '';
		//$dynamic_price = isset( $_POST['gb_deal_dynamic_price'] ) ? $_POST['gb_deal_dynamic_price'] : array();
		$shipping = isset( $_POST['gb_deal_shipping'] ) ? $_POST['gb_deal_shipping'] : '';
		$min = isset( $_POST['gb_deal_min_purchases'] ) ? (int)$_POST['gb_deal_min_purchases'] : 0;
		$max = isset( $_POST['gb_deal_max_purchases'] ) ? (int)$_POST['gb_deal_max_purchases'] : Group_Buying_Deal::NO_MAXIMUM;
		$max_per_user = isset( $_POST['gb_deal_max_per_user'] ) ? (int)$_POST['gb_deal_max_per_user'] : Group_Buying_Deal::NO_MAXIMUM;
		$value = isset( $_POST['gb_deal_value'] ) ? $_POST['gb_deal_value'] : '';
		$amount_saved = isset( $_POST['gb_deal_amount_saved'] ) ? $_POST['gb_deal_amount_saved'] : '';
		$highlights = isset( $_POST['gb_deal_highlights'] ) ? $_POST['gb_deal_highlights'] : '';
		$fine_print = isset( $_POST['gb_deal_fine_print'] ) ? $_POST['gb_deal_fine_print'] : '';
		$rss_excerpt = isset( $_POST['gb_deal_rss_excerpt'] ) ? $_POST['gb_deal_rss_excerpt'] : '';
		$voucher_expiration_date = isset( $_POST['gb_deal_voucher_expiration'] ) ? $_POST['gb_deal_voucher_expiration'] : '';
		$voucher_how_to_use = isset( $_POST['gb_deal_voucher_how_to_use'] ) ? $_POST['gb_deal_voucher_how_to_use'] : '';
		//$voucher_id_prefix = isset( $_POST['gb_deal_voucher_id_prefix'] ) ? $_POST['gb_deal_voucher_id_prefix'] : '';
		$voucher_locations = isset( $_POST['gb_deal_voucher_locations'] ) ? $_POST['gb_deal_voucher_locations'] : '';
		//$voucher_logo = isset( $_POST['gb_deal_voucher_logo'] ) ? $_POST['gb_deal_voucher_logo'] : '';
		$voucher_map = isset( $_POST['gb_deal_voucher_map'] ) ? $_POST['gb_deal_voucher_map'] : '';
		$voucher_serial_numbers = isset( $_POST['gb_deal_voucher_serial_numbers'] ) ? $_POST['gb_deal_voucher_serial_numbers'] : '';

		$errors = array_merge( $errors, $this->validate_deal_submission_fields( $_POST ) );

		$errors = apply_filters( 'gb_validate_deal_submission', $errors, $_POST );

		if ( !empty( $errors ) ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
			// Add all selected images to the description.
			if ( !empty( $_POST['submission_images'] ) ) {
				foreach ( $_POST['submission_images'] as $image_id ) {
					$description .= '<p>'.wp_get_attachment_image( $image_id, 'full' ).'</p>';
				}
			}
			// Create Post
			$post_id = wp_insert_post( array(
					'post_status' => 'draft',
					'post_type' => Group_Buying_Deal::POST_TYPE,
					'post_title' => $title,
					'post_content' => $description
				) );

			// Terms
			wp_set_post_terms( $post_id, $locations, Group_Buying_Deal::LOCATION_TAXONOMY );

			// Meta
			$deal = Group_Buying_Deal::get_instance( $post_id );
			$deal->set_expiration_date( empty( $expiration ) ? Group_Buying_Deal::NO_EXPIRATION_DATE : strtotime( $expiration ) );
			$deal->set_prices( array( 0 => $price ) );
			$deal->set_shipping( $shipping );
			$deal->set_min_purchases( $min );
			$deal->set_max_purchases( $max );
			$deal->set_max_purchases_per_user( $max_per_user );
			$deal->set_value( $value );
			$deal->set_amount_saved( $amount_saved );
			$deal->set_highlights( $highlights );
			$deal->set_fine_print( $fine_print );
			$deal->set_voucher_expiration_date( $voucher_expiration_date );
			$deal->set_voucher_how_to_use( $voucher_how_to_use );
			$deal->set_voucher_map( $voucher_map );
			$deal->set_voucher_serial_numbers( explode( ',', $voucher_serial_numbers ) );
			$deal->set_merchant_id( Group_Buying_Merchant::get_merchant_id_for_user() );

			// voucher locations
			if ( !is_array( $voucher_locations ) ) {
				$voucher_locations = array();
			}
			while ( count( $voucher_locations ) < Group_Buying_Deal::MAX_LOCATIONS ) {
				$voucher_locations[] = '';
			}
			$deal->set_voucher_locations( $voucher_locations );

			// Handle images
			if ( !empty( $_POST['submission_images'] ) ) {
				// Add an array of images to a meta field for future features
				$deal->set_images( $_POST['submission_images'] );
				// loop through all the images and assign them to this new post
				// if the image isn't already attached to another.
				foreach ( $_POST['submission_images'] as $image_id ) {
					if ( !get_post_field( 'post_parent', $image_id ) ) { // check if committed
						wp_update_post( array(
								'ID' => $image_id, 
								'post_parent' => $post_id )
							);
					}
				}
				// Set a thumbnail without being smart about it.
				update_post_meta( $post_id, '_thumbnail_id', $image_id );
			}

			// Admin notification
			do_action( 'gb_admin_notification', array( 'subject' => self::__( 'New Deal Submission' ), 'content' => self::__( 'A user has submitted a new deal for your review.' ), $deal ) );

			// Action
			do_action( 'submit_deal', $deal );

			$url = Group_Buying_Accounts::get_url();
			self::set_message( __( 'Deal Submitted for Review.' ), self::MESSAGE_STATUS_INFO );
			wp_redirect( $url, 303 );
			exit();
		}
	}

	protected function validate_deal_submission_fields( $submitted ) {
		$errors = array();
		$fields = $this->deal_submission_fields();
		foreach ( $fields as $key => $data ) {
			if ( isset( $data['required'] ) && $data['required'] && !( isset( $submitted['gb_deal_'.$key] ) && $submitted['gb_deal_'.$key] != '' ) ) {
				$errors[] = sprintf( self::__( '"%s" field is required.' ), $data['label'] );
			}
		}
		return $errors;
	}

	public function get_form() {
		return self::load_view_to_string( 'merchant/submit-deal', array( 'fields' => $this->deal_submission_fields(), 'form_action' => self::FORM_ACTION, ) );
	}


	public function add_location() {
		wp_insert_term( $_REQUEST['location_name'], Group_Buying_Deal::LOCATION_TAXONOMY );
		echo '<span id="ajax_locations">'.gb_get_list_locations( 'ul', FALSE ).'</span>';
		die();
	}

	public static function ajax_publish() {
		if ( isset( $_REQUEST['deal_id'] ) && $_REQUEST['deal_id'] ) {
			$post = array();
			$post['ID'] = $_REQUEST['deal_id'];
			$post['post_name'] = sanitize_title( get_the_title( $_REQUEST['deal_id'] ) );
			$post['post_date_gmt'] = current_time( 'mysql', 1 );
			$post['post_status'] = 'publish';
			$post_id = wp_update_post( $post );
			echo apply_filters( 'gb_ajax_publish', $post_id );
		}
		die();
	}

	public static function ajax_draft() {
		if ( isset( $_REQUEST['deal_id'] ) && $_REQUEST['deal_id'] ) {
			$post = array();
			$post['ID'] = $_REQUEST['deal_id'];
			$post['post_status'] = 'draft';
			$post_id = wp_update_post( $post );
			echo apply_filters( 'gb_ajax_draft', $post_id );
		}
		die();
	}
}