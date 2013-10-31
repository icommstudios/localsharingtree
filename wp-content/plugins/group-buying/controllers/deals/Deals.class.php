<?php

/**
 * Deal controller
 *
 * @package GBS
 * @subpackage Deal
 */
class Group_Buying_Deals extends Group_Buying_Controller {
	const CRON_HOOK = 'gb_deals_cron';

	public static function init() {
		if ( is_admin() ) {
			// deals submitted on the front-end won't have meta boxes
			add_action( 'add_meta_boxes', array( get_class(), 'add_meta_boxes' ) );
			add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		}
		add_filter( 'template_include', array( get_class(), 'override_template' ) );
		add_action( 'admin_init', array( get_class(), 'schedule_cron' ), 10, 0 );
		add_action( self::CRON_HOOK, array( get_class(), 'check_for_expired_deals' ), 10, 0 );
		add_action( 'purchase_completed', array( get_class(), 'purchase_completed' ), 5, 1 ); // run before vouchers are created
		add_action( 'admin_enqueue_scripts', array( get_class(), 'queue_admin_resources' ) );
		add_filter( 'gb_admin_bar', array( get_class(), 'add_link_to_admin_bar' ), 10, 1 );

		// Admin columns
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_columns', array( get_class(), 'register_columns' ) );
		add_filter( 'manage_'.Group_Buying_Deal::POST_TYPE.'_posts_custom_column', array( get_class(), 'column_display' ), 10, 2 );
		add_filter( 'manage_edit-'.Group_Buying_Deal::POST_TYPE.'_sortable_columns', array( get_class(), 'sortable_columns' ) );
		add_filter( 'request', array( get_class(), 'column_orderby' ) );

		// AJAX Actions
		add_action( 'wp_ajax_nopriv_gbs_ajax_get_deal_info',  array( get_class(), 'ajax_get_deal_info' ), 10, 0 );
		add_action( 'wp_ajax_gbs_ajax_get_deal_info',  array( get_class(), 'ajax_get_deal_info' ), 10, 0 );
	}

	public static function ajax_get_deal_info() {
		$id = $_POST['id'];
		if ( get_post_type( $id ) != Group_Buying_Deal::POST_TYPE ) {
			exit();
		}
		$deal = Group_Buying_Deal::get_instance( $id );
		if ( is_a( $deal, 'Group_Buying_Deal' ) ) {

			header( 'Content-Type: application/json' );
			$response = array(
				'deal_id' => $deal->get_ID(),
				'title' => $deal->get_title(),
				'status' => $deal->get_status(),
				'amount_saved' => $deal->get_amount_saved(), // string
				'capture_before_expiration' => $deal->capture_before_expiration(), // bool
				'dynamic_price' => $deal->get_dynamic_price(), // array
				'expiration_date' => $deal->get_expiration_date(), // int
				'fine_print' => $deal->get_fine_print(), // string
				'highlights' => $deal->get_highlights(), // string
				'max_purchases' => $deal->get_max_purchases_per_user(), // int
				'max_purchases_per_user' => $deal->get_max_purchases(), // int
				'merchant_id' => $deal->get_merchant_id(), // int
				'min_purchases' => $deal->get_min_purchases(), // int
				'number_of_purchases' => $deal->get_number_of_purchases(), // int
				'price' => $deal->get_price(), // float
				'remaining_allowed_purchases' => $deal->get_remaining_allowed_purchases(), // int
				'remaining_required_purchases' => $deal->get_remaining_required_purchases(), // int
				'taxable' => $deal->get_taxable(), // bool
				'shippable' => $deal->get_shipping(), // string
				'rss_excerpt' => $deal->get_rss_excerpt(), // string
				'value' => $deal->get_value(), // string
				'voucher_expiration_date' => $deal->get_voucher_expiration_date(), // string
				'voucher_how_to_use' => $deal->get_voucher_how_to_use(), // string
				'voucher_id_prefix' => $deal->get_voucher_id_prefix(), //string
				'voucher_locations' => $deal->get_voucher_locations(), // array
				'voucher_logo' => $deal->get_voucher_logo(), // int
				'voucher_map' => $deal->get_voucher_map(), // string

			);
			echo json_encode( $response );
		}
		exit();
	}

	public static function add_meta_boxes() {
		add_meta_box( 'gb_deal_expiration', self::__( 'Expiration Date' ), array( get_class(), 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'side', 'high' );
		add_meta_box( 'gb_deal_price', self::__( 'Pricing' ), array( get_class(), 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_deal_limits', self::__( 'Purchase Limits' ), array( get_class(), 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_deal_details', self::__( 'Deal Details' ), array( get_class(), 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_deal_voucher', self::__( 'Voucher' ), array( get_class(), 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_deal_merchant', self::__( 'Merchant' ), array( get_class(), 'show_meta_box' ), Group_Buying_Deal::POST_TYPE, 'advanced', 'high' );
	}

	public static function show_meta_box( $post, $metabox ) {
		$deal = Group_Buying_Deal::get_instance( $post->ID );
		switch ( $metabox['id'] ) {
		case 'gb_deal_expiration':
			self::show_meta_box_gb_deal_expiration( $deal, $post, $metabox );
			break;
		case 'gb_deal_price':
			self::show_meta_box_gb_deal_price( $deal, $post, $metabox );
			break;
		case 'gb_deal_limits':
			self::show_meta_box_gb_deal_limits( $deal, $post, $metabox );
			break;
		case 'gb_deal_details':
			self::show_meta_box_gb_deal_details( $deal, $post, $metabox );
			break;
		case 'gb_deal_voucher':
			self::show_meta_box_gb_deal_voucher( $deal, $post, $metabox );
			break;
		case 'gb_deal_merchant':
			self::show_meta_box_gb_deal_merchant( $deal, $post, $metabox );
			break;
		default:
			self::unknown_meta_box( $metabox['id'] );
			break;
		}
	}

	public static function save_meta_boxes( $post_id, $post ) {
		// Don't save meta boxes when the importer is used.
		if ( isset( $_GET['import'] ) && $_GET['import'] == 'wordpress' ) {
			return;
		}

		// only continue if it's a deal post
		if ( $post->post_type != Group_Buying_Deal::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		// Since the save_box_gb_deal_[meta] functions don't check if there's a _POST, a nonce was added to safe guard save_post actions from ... scheduled posts, etc.
		if ( !isset( $_POST['gb_deal_submission'] ) && ( empty( $_POST ) || !check_admin_referer( 'gb_save_metaboxes', 'gb_save_metaboxes_field' ) ) ) {
			return;
		}
		// save all the meta boxes
		$deal = Group_Buying_Deal::get_instance( $post_id );
		self::save_meta_box_gb_deal_price( $deal, $post_id, $post );
		self::save_meta_box_gb_deal_limits( $deal, $post_id, $post );
		self::save_meta_box_gb_deal_details( $deal, $post_id, $post );
		self::save_meta_box_gb_deal_voucher( $deal, $post_id, $post );
		self::save_meta_box_gb_deal_merchant( $deal, $post_id, $post );

		// save expiration last, since it depends on the value of the deal_price meta box
		self::save_meta_box_gb_deal_expiration( $deal, $post_id, $post );
	}

	public static function queue_admin_resources() {
		if ( is_admin() ) {
			$post_id = isset( $_GET['post'] ) ? (int)$_GET['post'] : -1;
			if (
				( isset( $_GET['post_type'] ) && Group_Buying_Deal::POST_TYPE == $_GET['post_type'] ) ||
				Group_Buying_Deal::POST_TYPE == get_post_type( $post_id ) ||
				( isset( $_GET['page'] ) && $_GET['page'] == 'group-buying/gb_settings' )
			) {
				wp_enqueue_script( 'gb_timepicker' );
				wp_enqueue_script( 'gb_admin_deal' );
				wp_enqueue_style( 'gb_admin_deal' );
			}
		}
	}

	/**
	 * Display the deal expiration meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_deal_expiration( Group_Buying_Deal $deal, $post, $metabox ) {
		$expiration = $deal->get_expiration_date();
		self::load_view( 'meta_boxes/deal-expiration', array(
				'timestamp' => ( $expiration == Group_Buying_Deal::NO_EXPIRATION_DATE )?( current_time( 'timestamp' )+24*60*60 ):$expiration,
				'never_expires' => ( $expiration == Group_Buying_Deal::NO_EXPIRATION_DATE ),
				'show_vouchers' => $deal->capture_before_expiration(),
			) );
		wp_nonce_field( 'gb_save_metaboxes', 'gb_save_metaboxes_field' );
	}

	/**
	 * Save the deal expiration meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	private static function save_meta_box_gb_deal_expiration( Group_Buying_Deal $deal, $post_id, $post ) {
		if ( $deal->has_dynamic_price() ) {
			// these options are incompatible with dynamic pricing
			unset( $_POST['deal_expiration_never'] );
			unset( $_POST['deal_capture_before_expiration'] );
		}
		if ( isset( $_POST['deal_expiration_never'] ) && $_POST['deal_expiration_never'] ) {
			$deal->set_expiration_date( Group_Buying_Deal::NO_EXPIRATION_DATE );
			$_POST['deal_capture_before_expiration'] = TRUE; // if it never expires, you have to capture earlier than expiration
		} else {
			$deal->set_expiration_date( strtotime( $_POST['deal_expiration'] ) );
		}
		if ( isset( $_POST['deal_capture_before_expiration'] ) && $_POST['deal_capture_before_expiration'] ) {
			$deal->set_capture_before_expiration( TRUE );
		} else {
			$deal->set_capture_before_expiration( FALSE );
		}
	}

	/**
	 * Display the deal price meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_deal_price( Group_Buying_Deal $deal, $post, $metabox ) {
		self::load_view( 'meta_boxes/deal-price', array(
				'price' => $deal->get_price( 0 ),
				'dynamic_price' => $deal->get_dynamic_price(),
				'shipping' => $deal->get_shipping_meta(),
				'shippable' => $deal->get_shippable(),
				'shipping_dyn' => $deal->get_shipping_dyn_price(),
				'shipping_mode' => $deal->get_shipping_mode(),
				'tax' => $deal->get_tax(),
				'taxable' => $deal->get_taxable(),
				'taxrate' => $deal->get_tax_mode()
			) );
	}

	/**
	 * Save the deal price meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	private static function save_meta_box_gb_deal_price( Group_Buying_Deal $deal, $post_id, $post ) {
		$prices = array( 0=>0 );
		if ( isset( $_POST['deal_base_price'] ) ) {
			if ( is_numeric( $_POST['deal_base_price'] ) ) {
				$prices[0] = $_POST['deal_base_price'];
			}
			$dynamic_prices = isset( $_POST['deal_dynamic_price'] ) ? (array) $_POST['deal_dynamic_price'] : array();
			foreach ( $dynamic_prices as $qty => $price ) {
				if ( is_numeric( $qty ) && is_numeric( $price ) ) {
					$prices[(int)$qty] = $price;
				}
			}
		}
		ksort( $prices );
		$deal->set_prices( $prices );

		$taxable = isset( $_POST['deal_base_taxable'] ) ? $_POST['deal_base_taxable'] : '';
		$deal->set_taxable( $taxable );
		$tax = isset( $_POST['deal_base_tax'] ) ? $_POST['deal_base_tax'] : '';
		$deal->set_tax( $tax );
		$shipping = isset( $_POST['deal_shipping'] ) ? $_POST['deal_shipping'] : '';
		$deal->set_shipping( $shipping );
		$deal_base_shippable = isset( $_POST['deal_base_shippable'] ) ? $_POST['deal_base_shippable'] : '';
		$deal->set_shippable( $deal_base_shippable );
		$shipping_mode = isset( $_POST['deal_base_shipping_mode'] ) ? $_POST['deal_base_shipping_mode'] : '';
		$deal->set_shipping_mode( $shipping_mode );

		$shipping_rates = array();
		if ( isset( $_POST['deal_dynamic_shipping'] ) ) {
			foreach ( $_POST['deal_dynamic_shipping']['quantity'] as $key => $rate_id ) {
				if ( $_POST['deal_dynamic_shipping']['quantity'][$key] > 0 && $_POST['deal_dynamic_shipping']['quantity'][$key] != '' ) {
					if ( $_POST['deal_dynamic_shipping']['rate'][$key] == '' ) $_POST['deal_dynamic_shipping']['rate'][$key] = 0;
					$shipping_rates[] = array(
						'quantity' => $_POST['deal_dynamic_shipping']['quantity'][$key],
						'rate' => $_POST['deal_dynamic_shipping']['rate'][$key],
						'per_item' => $_POST['deal_dynamic_shipping']['per_item'][$key]
					);
				}
			}
		}
		$deal->set_shipping_dyn_price( $shipping_rates );
	}

	/**
	 * Display the deal limits meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_deal_limits( Group_Buying_Deal $deal, $post, $metabox ) {
		$deal = Group_Buying_Deal::get_instance( $post->ID );
		$min = $deal->get_min_purchases();
		$max = $deal->get_max_purchases();
		$max_per_user = $deal->get_max_purchases_per_user();
		self::load_view( 'meta_boxes/deal-limits', array(
				'minimum' => ( $min > 0 )?$min:0,
				'maximum' => ( $max == Group_Buying_Deal::NO_MAXIMUM )?'':$max,
				'max_per_user' => ( $max_per_user == Group_Buying_Deal::NO_MAXIMUM )?'':$max_per_user,
			) );
	}

	/**
	 * Save the deal limits meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	private static function save_meta_box_gb_deal_limits( Group_Buying_Deal $deal, $post_id, $post ) {
		$min = 0;
		if ( isset( $_POST['deal_min_purchases'] ) && (int)$_POST['deal_min_purchases'] > 0 ) {
			$min = (int)$_POST['deal_min_purchases'];
		}
		$deal->set_min_purchases( $min );

		$max = Group_Buying_Deal::NO_MAXIMUM;
		if ( isset( $_POST['deal_max_purchases'] )
			&& $_POST['deal_max_purchases'] != '' // blank means no maximum
			&& (int)$_POST['deal_max_purchases'] >= 0
		) {
			$max = (int)$_POST['deal_max_purchases'];
		}
		$deal->set_max_purchases( $max );

		$max_per_user = Group_Buying_Deal::NO_MAXIMUM;
		if ( isset( $_POST['deal_max_purchases_per_user'] )
			&& $_POST['deal_max_purchases_per_user'] != '' // blank means no maximum
			&& (int)$_POST['deal_max_purchases_per_user'] >= 0
		) {
			$max_per_user = (int)$_POST['deal_max_purchases_per_user'];
		}
		$deal->set_max_purchases_per_user( $max_per_user );
		do_action( 'save_gb_meta_box_deal_limits', $deal, $post_id, $post );
	}

	/**
	 * Display the deal details meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_deal_details( Group_Buying_Deal $deal, $post, $metabox ) {
		$value = $deal->get_value();
		$amount_saved = $deal->get_amount_saved();
		$highlights = $deal->get_highlights();
		$fine_print = $deal->get_fine_print();
		$rss_excerpt = $deal->get_rss_excerpt();
		self::load_view( 'meta_boxes/deal-details', array(
				'deal_value' => is_null( $value ) ? '' : $value,
				'deal_amount_saved' => is_null( $amount_saved ) ? '' : $amount_saved,
				'deal_highlights' => is_null( $highlights ) ? '' : $highlights,
				'deal_fine_print' => is_null( $fine_print ) ? '' : $fine_print,
				'deal_rss_excerpt' => is_null( $rss_excerpt ) ? '' : $rss_excerpt,
			) );
	}

	/**
	 * Save the deal details meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	private static function save_meta_box_gb_deal_details( Group_Buying_Deal $deal, $post_id, $post ) {
		$value = isset( $_POST['deal_value'] ) ? $_POST['deal_value'] : '';
		$deal->set_value( $value );

		$amount_saved = isset( $_POST['deal_amount_saved'] ) ? $_POST['deal_amount_saved'] : '';
		$deal->set_amount_saved( $amount_saved );

		$highlights = isset( $_POST['deal_highlights'] ) ? $_POST['deal_highlights'] : '';
		$deal->set_highlights( $highlights );

		$fine_print = isset( $_POST['deal_fine_print'] ) ? $_POST['deal_fine_print'] : '';
		$deal->set_fine_print( $fine_print );

		$rss_excerpt = isset( $_POST['deal_rss_excerpt'] ) ? $_POST['deal_rss_excerpt'] : '';
		$deal->set_rss_excerpt( $rss_excerpt );
	}

	/**
	 * Display the deal voucher meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_deal_voucher( Group_Buying_Deal $deal, $post, $metabox ) {
		$voucher_expiration_date = $deal->get_voucher_expiration_date();
		$voucher_how_to_use = $deal->get_voucher_how_to_use();
		$voucher_id_prefix = $deal->get_voucher_id_prefix();
		$voucher_locations = $deal->get_voucher_locations();
		while ( count( $voucher_locations ) < Group_Buying_Deal::MAX_LOCATIONS ) {
			$voucher_locations[] = '';
		}
		$voucher_logo = $deal->get_voucher_logo();
		$voucher_map = $deal->get_voucher_map();
		$voucher_serial_numbers = implode( ',', $deal->get_voucher_serial_numbers() );

		self::load_view( 'meta_boxes/deal-voucher', array(
				'voucher_expiration_date' => is_null( $voucher_expiration_date ) ? '' : $voucher_expiration_date,
				'voucher_how_to_use' => is_null( $voucher_how_to_use ) ? '' : $voucher_how_to_use,
				'voucher_id_prefix' => is_null( $voucher_id_prefix ) ? '' : $voucher_id_prefix,
				'voucher_locations' => $voucher_locations,
				'voucher_logo' => is_null( $voucher_logo ) ? '' : $voucher_logo,
				'voucher_map' => is_null( $voucher_map ) ? '' : $voucher_map,
				'voucher_serial_numbers' => $voucher_serial_numbers,
			) );
	}

	/**
	 * Save the deal voucher meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	private static function save_meta_box_gb_deal_voucher( Group_Buying_Deal $deal, $post_id, $post ) {
		$expiration_date = isset( $_POST['voucher_expiration_date'] ) ? $_POST['voucher_expiration_date'] : '';
		$deal->set_voucher_expiration_date( $expiration_date );

		$how_to_use = isset( $_POST['voucher_how_to_use'] ) ? $_POST['voucher_how_to_use'] : '';
		$deal->set_voucher_how_to_use( $how_to_use );

		$id_prefix = isset( $_POST['voucher_id_prefix'] ) ? $_POST['voucher_id_prefix'] : '';
		$deal->set_voucher_id_prefix( $id_prefix );

		$locations = isset( $_POST['voucher_locations'] ) ? $_POST['voucher_locations'] : '';
		if ( !is_array( $locations ) ) {
			$locations = array();
		}
		while ( count( $locations ) < Group_Buying_Deal::MAX_LOCATIONS ) {
			$locations[] = '';
		}
		$deal->set_voucher_locations( $locations );

		$logo = isset( $_POST['voucher_logo'] ) ? $_POST['voucher_logo'] : '';
		$deal->set_voucher_logo( $logo );

		$map = isset( $_POST['voucher_map'] ) ? $_POST['voucher_map'] : '';
		$deal->set_voucher_map( $map );

		$serial_numbers = isset( $_POST['voucher_serial_numbers'] ) ? $_POST['voucher_serial_numbers'] : '';
		$serial_numbers = explode( ',', $serial_numbers );
		$serial_numbers = array_map( 'trim', $serial_numbers );
		$deal->set_voucher_serial_numbers( $serial_numbers );
	}

	/**
	 * Display the deal merchant meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_deal_merchant( Group_Buying_Deal $deal, $post, $metabox ) {
		$merchants = get_posts( array( 'numberposts' => -1, 'post_type' => Group_Buying_Merchant::POST_TYPE, 'post_status' => array( 'publish', 'draft' ) ) );
		$merchant_id = $deal->get_merchant_id();
		self::load_view( 'meta_boxes/deal-merchant', array(
				'merchants' => $merchants,
				'merchant_id' => $merchant_id
			) );
	}

	/**
	 * Save the deal merchant meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $deal
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	private static function save_meta_box_gb_deal_merchant( Group_Buying_Deal $deal, $post_id, $post ) {
		$merchant_id = isset( $_POST['deal_merchant'] ) ? $_POST['deal_merchant'] : '';
		$deal->set_merchant_id( $merchant_id );
	}

	public static function register_columns( $columns ) {
		unset( $columns['date'] );
		unset( $columns['title'] );
		unset( $columns['comments'] );
		unset( $columns['author'] );
		$columns['title'] = self::__( 'Deal' );
		$columns['status'] = self::__( 'Status' );
		$columns['sold'] = self::__( 'Records' );
		$columns['records'] = self::__( 'Reports' );
		$columns['merchant'] = self::__( 'Merchant' );
		$columns['date'] = self::__( 'Published' );
		$columns['comments'] = '<span><span class="vers"><img alt="Comments" src="images/comment-grey-bubble.png"></span></span>';
		return $columns;
	}


	public static function column_display( $column_name, $id ) {
		global $post;
		$deal = Group_Buying_Deal::get_instance( $id );

		if ( !$deal )
			return; // return for that temp post

		switch ( $column_name ) {
		case 'merchant':
			$merchant = Group_Buying_Merchant::get_merchant_object( $id );
			if ( !is_a( $merchant, 'Group_Buying_Merchant' ) ) return;
			printf( '<a href="%1$s">%2$s</a><br/>', get_edit_post_link( $merchant->get_ID() ), get_the_title( $merchant->get_ID() ) );
			printf( self::__( '<a href="%1$s" style="color:silver">%1$s</a><br/>' ), $merchant->get_website() );
			printf( self::__( '<span style="color:silver">%1$s</span>' ), $merchant->get_contact_phone() );
			printf( '<div class="row-actions"><span class="payment"><a href="%1$s">View</a></span></div>', get_permalink( $merchant->get_ID() ) );
			break;
		case 'status':
			$expiration = ( Group_Buying_Deal::NO_EXPIRATION_DATE == $deal->get_expiration_date() ) ? self::__( 'none' ) : date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $deal->get_expiration_date() );
			switch ( $deal->get_status() ) {
			case 'open':
				printf( '<span style="color:green">%1$s</span> <span style="color:silver">(id: %2$s)</span> <br/><span style="color:silver">expires: %3$s</span> ', self::__( 'Active' ), $id, $expiration );
				break;
			case 'closed':
				printf( '<span style="color:#BC0B0B">%1$s</span> <span style="color:silver">(id: %2$s)</span> <br/><span style="color:silver">%3$s</span>', self::__( 'Expired' ), $id, $expiration );
				break;
			case 'closed':
				printf( '<span style="color:orange">%1$s</span> <span style="color:silver">(id: %2$s)</span> <br/><span style="color:silver">expiration: %3$s</span>', self::__( 'Pending' ), $id, $expiration );
				break;
			case 'closed':
			default:
				echo '<span style="color:black">'.gb__( 'Unknown' ).'</span>';
				break;
			}
			break;
		case 'sold':
			printf( self::__( 'Sold: %s' ), $deal->get_number_of_purchases() );
			printf( self::__( '<br/><span style="color:silver">Current Price: %s</span>' ), gb_get_formatted_money( $deal->get_price() ) );
			if ( $deal->get_remaining_allowed_purchases() > 0 ) {
				printf( self::__( '<br/><span style="color:silver">Remaining allowed: %s</span>' ), $deal->get_remaining_allowed_purchases() );
			}
			$remaining = (int) $deal->get_remaining_required_purchases();
			if ( $remaining ) {
				printf( self::__( '<br/><span style="color:silver">Remaining required: %s</span>' ), $remaining );
			}
			printf( '<div class="row-actions"><span class="payment"><a href="admin.php?page=group-buying/voucher_records&amp;deal_id=%1$s">Vouchers</a> | <span class="payments"><a href="admin.php?page=group-buying/payment_records&amp;deal_id=%1$s">Payments</a> | </span><span class="purchases"><a href="admin.php?page=group-buying/purchase_records&amp;deal_id=%1$s">Orders</a> | </span><span class="gifts"><a href="admin.php?page=group-buying/gift_records&amp;deal_id=%1$s">Gifts</a></span></div>', $id );

			break;
		case 'records':
			echo '<p><a href="'.gb_get_deal_purchase_report_url( $id ).'" class="button">'.self::__( 'Purchases' ).'</a>&nbsp;&nbsp;<a href="'.gb_get_deal_voucher_report_url( $id ).'" class="button">'.self::__( 'Vouchers' ).'</a></p>';
			break;
		default:
			break;
		}
	}

	public function sortable_columns( $columns ) {
		//$columns['status'] = 'status';
		//$columns['sold'] = 'sold';
		//$columns['expires'] = 'expires';
		$columns['id'] = 'id';
		return $columns;
	}
	public function column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && is_admin() ) {
			switch ( $vars['orderby'] ) {
			case 'status':
				$vars = array_merge( $vars, array(
						'orderby' => 'SQL' // FUTURE: SQL
					) );
				break;
			case 'expires':
				$vars = array_merge( $vars, array(
						'orderby' => 'SQL' // FUTURE SQL
					) );
				break;
			case 'sold':
				$vars = array_merge( $vars, array(
						'orderby' => 'SQL' // FUTURE SQL
					) );
				break;
			default:
				// code...
				break;
			}
		}

		return $vars;
	}

	public static function override_template( $template ) {
		if ( Group_Buying_Deal::is_deal_query() ) {
			if ( is_single() ) {
				$template = self::locate_template( array(
						'products/single-product.php',
						'products/single.php',
						'products/product.php',
						'product.php',
						'deals/single-deal.php',
						'deals/single.php',
						'deals/deal.php',
						'deal.php',
					), $template );
			} else {
				$template = self::locate_template( array(
						'products/products.php',
						'products/index.php',
						'products/archive.php',
						'deals/deals.php',
						'deals/index.php',
						'deals/archive.php',
					), $template );
			}
		}
		if ( Group_Buying_Deal::is_deal_tax_query() ) {
			$taxonomy = get_query_var( 'taxonomy' );
			$template = self::locate_template( array(
					'products/product-'.$taxonomy.'.php',
					'products/product-type.php',
					'products/product-types.php',
					'products/product.php',
					'products/index.php',
					'products/archive.php',
					'deals/deal-'.$taxonomy.'.php',
					'deals/deal-type.php',
					'deals/deal-types.php',
					'deals/deals.php',
					'deals/index.php',
					'deals/archive.php',
				), $template );
		}
		return $template;
	}

	public static function schedule_cron() {
		if ( !wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'halfhour', self::CRON_HOOK );
		}
	}

	public static function clear_schedule() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	public static function check_for_expired_deals() {
		// in case two processes are kicked off automatically decrease the chances of them conflicting.
		usleep( rand( 1, 1000000 ) );
		$transient = 'check_for_expired_deals_in_progress';
		$in_progress = (int)get_transient( $transient );
		if ( $in_progress ) {
			return;
		}
		// Set in progress transient
		set_transient( $transient, time(), 1801 );

		$now = current_time( 'timestamp' );
		$last_check = (int)get_option( 'gb_expiration_check', 0 );
		$deals = Group_Buying_Deal::get_expired_deals( $last_check );
		foreach ( $deals as $deal_id ) {
			$deal = Group_Buying_Deal::get_instance( $deal_id );
			if ( is_a( $deal, 'Group_Buying_Deal' ) ) {
				do_action( 'deal_expired', $deal );
				if ( $deal->is_successful() ) {
					do_action( 'deal_success', $deal );
				} else {
					do_action( 'deal_failed', $deal );
				}
			}
		}

		delete_transient( $transient );
		update_option( 'gb_expiration_check', $now );
	}

	public static function purchase_completed( Group_Buying_Purchase $purchase ) {
		$products = $purchase->get_products();
		foreach ( $products as $product ) {
			$deal = Group_Buying_Deal::get_instance( $product['deal_id'] );
			$deal->get_number_of_purchases( TRUE ); // recalculate based on latest purchase
		}
	}

	public static function add_link_to_admin_bar( $items ) {
		$items[] = array(
			'id' => 'edit_deals',
			'title' => self::__( 'Edit Deals' ),
			'href' => admin_url( 'edit.php?post_type='.Group_Buying_Deal::POST_TYPE ),
			'weight' => 0,
		);
		return $items;
	}
}
