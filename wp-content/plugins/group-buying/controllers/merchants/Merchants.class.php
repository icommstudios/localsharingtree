<?php

/**
 * Merchant controller
 *
 * @package GBS
 * @subpackage Merchant
 */
class Group_Buying_Merchants extends Group_Buying_Controller {
	const MERCHANT_PATH_OPTION = 'gb_merchant_path';
	const MERCHANT_QUERY_VAR = 'gb_account_merchant';
	private static $merchant_path = 'merchant';
	private static $instance;

	public static function init() {
		// Default Account Merchant Template
		self::$merchant_path = get_option( self::MERCHANT_PATH_OPTION, self::$merchant_path );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 50, 0 );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_path_callback' ), 10, 1 );

		// Template overrides
		add_filter( 'template_include', array( get_class(), 'override_template' ) );

		// Admin
		add_action( 'add_meta_boxes', array( get_class(), 'add_meta_boxes' ) );
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		add_filter( 'gb_admin_bar', array( get_class(), 'add_link_to_admin_bar' ), 10, 1 );

		// Admin columns
		add_filter ( 'manage_edit-'.Group_Buying_Merchant::POST_TYPE.'_columns', array( get_class(), 'register_columns' ) );
		add_filter ( 'manage_'.Group_Buying_Merchant::POST_TYPE.'_posts_custom_column', array( get_class(), 'column_display' ), 10, 2 );
		add_filter( 'manage_edit-'.Group_Buying_Merchant::POST_TYPE.'_sortable_columns', array( get_class(), 'sortable_columns' ) );

		// Allow merchants to upload files
		add_filter( 'user_has_cap', array( get_class(), 'allow_file_uploads' ), 10, 3 );
	}

	/**
	 * Register the path callback for the merchant page
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_path_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$merchant_path,
			'title' => 'Merchant',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_account_merchant_page' ),
			'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$merchant_path ).'-info.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::MERCHANT_QUERY_VAR, $args );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_merchant_paths';
		add_settings_section( $section, null, array( get_class(), 'display_merchant_paths_section' ), $page );

		// Settings
		register_setting( $page, self::MERCHANT_PATH_OPTION );
		add_settings_field( self::MERCHANT_PATH_OPTION, self::__( 'Merchant Path' ), array( get_class(), 'display_merchant_path' ), $page, $section );
	}

	public static function display_merchant_paths_section() {
		echo self::__( '<h4>Customize the Merchant paths.</h4>' );
	}

	public static function display_merchant_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="'.self::MERCHANT_PATH_OPTION.'" id="'.self::MERCHANT_PATH_OPTION.'" value="' . esc_attr( self::$merchant_path ) . '"  size="40"/><br />';
	}

	public static function on_account_merchant_page() {
		$merchant_page = self::get_instance();
		// View template
		$merchant_page->view_account_merchant();
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

	/**
	 *
	 *
	 * @static
	 * @return Group_Buying_Merchants
	 */
	public static function get_instance() {
		if ( !( self::$instance && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		self::do_not_cache(); // never cache the merchant account page
		do_action( 'processing_merchant_account_page' );
	}

	public function view_account_merchant() {
		remove_filter( 'the_content', 'wpautop' );
		$merchant_id = Group_Buying_Merchant::get_merchant_id_for_user();
		if ( !$merchant_id ) {
			self::load_view( 'merchant/info-none', array() );
		} elseif ( 'draft' == get_post_status( $merchant_id ) ) {
			self::load_view( 'merchant/info-pending', array() );
		} else {
			$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
			self::load_view( 'merchant/info-published', array( 'fields' => $this->merchant_contact_info_fields( $merchant ) ) );
		}
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a cart page
	 */
	public static function is_merchant_account_page() {
		return GB_Router_Utility::is_on_page( self::MERCHANT_QUERY_VAR );
	}

	/**
	 * Filter 'the_title' to display the title of the page rather than the user name
	 *
	 * @static
	 * @param string  $title
	 * @param int     $post_id
	 * @return string
	 */
	public function get_title( $title ) {
		$merchant_id = Group_Buying_Merchant::get_merchant_id_for_user();
		if ( !$merchant_id ) {
			return self::__( "Merchant Unavailable" );
		} elseif ( 'draft' == get_post_status( $merchant_id ) ) {
			return self::__( "Merchant Registration Pending" );
		} else {
			return get_the_title( $merchant_id );
		}
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the merchant page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$merchant_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::MERCHANT_QUERY_VAR );
		}
	}

	///////////////////////////
	// Post Type Templating //
	///////////////////////////

	public static function override_template( $template ) {
		if ( Group_Buying_Merchant::is_merchant_query() ) {
			if ( is_single() ) {
				$template = self::locate_template( array(
						'business/business.php',
						'business/single.php',
						'merchant/business.php',
						'merchant/single.php'
					), $template );
			} elseif ( is_archive() ) {
				$template = self::locate_template( array(
						'business/businesses.php',
						'business/index.php',
						'business/archive.php',
						'business/business-index.php',
						'business/business-archive.php',
						'merchant/businesses.php',
						'merchant/index.php',
						'merchant/archive.php',
						'merchant/business-index.php',
						'merchant/business-archive.php',
					), $template );
			}
		}
		if ( Group_Buying_Merchant::is_merchant_tax_query() ) {
			$taxonomy = get_query_var( 'taxonomy' );
			$template = self::locate_template( array(
					'business/business-'.$taxonomy.'.php',
					'business/business-type.php',
					'business/business-types.php',
					'business/businesses.php',
					'business/business-index.php',
					'business/business-archive.php',
					'business/archive.php',
					'merchant/business-'.$taxonomy.'.php',
					'merchant/business-type.php',
					'merchant/business-types.php',
					'merchant/businesses.php',
					'merchant/business-index.php',
					'merchant/business-archive.php',
					'merchant/archive.php',
				), $template );
		}
		return $template;
	}

	/////////////////
	// Admin meta //
	/////////////////

	public static function add_meta_boxes() {
		add_meta_box( 'gb_merchant_details', self::__( 'Merchant Details' ), array( get_class(), 'show_meta_box' ), Group_Buying_Merchant::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_merchant_authorized_users', self::__( 'Authorized Users' ), array( get_class(), 'show_meta_box' ), Group_Buying_Merchant::POST_TYPE, 'advanced', 'high' );
	}

	public static function show_meta_box( $post, $metabox ) {
		$merchant = Group_Buying_Merchant::get_instance( $post->ID );
		switch ( $metabox['id'] ) {
		case 'gb_merchant_details':
			self::show_meta_box_gb_merchant_details( $merchant, $post, $metabox );
			break;
		case 'gb_merchant_authorized_users':
			self::show_meta_box_gb_merchant_authorized_users( $merchant, $post, $metabox );
			break;
		default:
			self::unknown_meta_box( $metabox['id'] );
			break;
		}
	}

	public static function save_meta_boxes( $post_id, $post ) {
		// only continue if it's a deal post
		if ( $post->post_type != Group_Buying_Merchant::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		// save all the meta boxes
		$merchant = Group_Buying_Merchant::get_instance( $post_id );
		self::save_meta_box_gb_merchant_details( $merchant, $post_id, $post );
		self::save_meta_box_gb_merchant_authorized_users( $merchant, $post_id, $post );
	}

	/**
	 * Display the deal details meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $merchant
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_merchant_details( Group_Buying_Merchant $merchant, $post, $metabox ) {
		$contact_name = $merchant->get_contact_name();
		$contact_title = $merchant->get_contact_title();
		$contact_street = $merchant->get_contact_street();
		$contact_city = $merchant->get_contact_city();
		$contact_state = $merchant->get_contact_state();
		$contact_postal_code = $merchant->get_contact_postal_code();
		$contact_country = $merchant->get_contact_country();
		$contact_phone = $merchant->get_contact_phone();
		$website = $merchant->get_website();
		$facebook = $merchant->get_facebook();
		$twitter = $merchant->get_twitter();

		self::load_view( 'meta_boxes/merchant-details', array(
				'contact_name' => is_null( $contact_name ) ? '' : $contact_name,
				'contact_title' => is_null( $contact_title ) ? '' : $contact_title,
				'contact_street' => is_null( $contact_street ) ? '' : $contact_street,
				'contact_city' => is_null( $contact_city ) ? '' : $contact_city,
				'contact_state' => is_null( $contact_state ) ? '' : $contact_state,
				'contact_postal_code' => is_null( $contact_postal_code ) ? '' : $contact_postal_code,
				'contact_country' => is_null( $contact_country ) ? '' : $contact_country,
				'contact_phone' => is_null( $contact_phone ) ? '' : $contact_phone,
				'website' => is_null( $website ) ? '' : $website,
				'facebook' => is_null( $facebook ) ? '' : $facebook,
				'twitter' => is_null( $twitter ) ? '' : $twitter
			) );
	}

	private static function show_meta_box_gb_merchant_authorized_users( Group_Buying_Merchant $merchant, $post, $metabox ) {
		$authorized_users = $merchant->get_authorized_users();
		$args = apply_filters( 'gb_get_users_args', null );
		$users = get_users( $args );
		self::load_view( 'meta_boxes/merchant-authorized-users', array(
				'authorized_users' => $authorized_users,
				'users' => $users
			) );
	}

	/**
	 * Save the deal details meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $merchant
	 * @param int     $post_id
	 * @param object  $post
	 * @return void
	 */
	private static function save_meta_box_gb_merchant_details( Group_Buying_Merchant $merchant, $post_id, $post ) {
		$contact_name = isset( $_POST['contact_name'] ) ? $_POST['contact_name'] : '';
		$contact_title = isset( $_POST['contact_title'] ) ? $_POST['contact_title'] : '';
		$contact_street = isset( $_POST['contact_street'] ) ? $_POST['contact_street'] : '';
		$contact_city = isset( $_POST['contact_city'] ) ? $_POST['contact_city'] : '';
		$contact_state = isset( $_POST['contact_state'] ) ? $_POST['contact_state'] : '';
		$contact_postal_code = isset( $_POST['contact_postal_code'] ) ? $_POST['contact_postal_code'] : '';
		$contact_country = isset( $_POST['contact_country'] ) ? $_POST['contact_country'] : '';
		$contact_phone = isset( $_POST['contact_phone'] ) ? $_POST['contact_phone'] : '';
		$website = isset( $_POST['website'] ) ? esc_url( $_POST['website'] ) : '';
		$facebook = isset( $_POST['facebook'] ) ? esc_url( $_POST['facebook'] ) : '';
		$twitter = isset( $_POST['twitter'] ) ? esc_url( $_POST['twitter'] ) : '';

		$merchant->set_contact_name( $contact_name );
		$merchant->set_contact_title( $contact_title );
		$merchant->set_contact_street( $contact_street );
		$merchant->set_contact_city( $contact_city );
		$merchant->set_contact_state( $contact_state );
		$merchant->set_contact_postal_code( $contact_postal_code );
		$merchant->set_contact_country( $contact_country );
		$merchant->set_contact_phone( $contact_phone );
		$merchant->set_website( $website );
		$merchant->set_facebook( $facebook );
		$merchant->set_twitter( $twitter );
	}

	private static function save_meta_box_gb_merchant_authorized_users( Group_Buying_Merchant $merchant, $post_id, $post ) {
		if ( isset( $_POST['authorized_user'] ) && ( $_POST['authorized_user'] != '' ) ) {
			$authorized_user = $_POST['authorized_user'];
			$merchant->authorize_user( $authorized_user );
		}
		if ( isset( $_POST['unauthorized_user'] ) && ( $_POST['unauthorized_user'] != '' ) ) {
			$unauthorized_user = $_POST['unauthorized_user'];
			$merchant->unauthorize_user( $unauthorized_user );
		}
	}

	///////////////////////
	// Admin management //
	///////////////////////

	public static function register_columns( $columns ) {
		unset( $columns['date'] );
		unset( $columns['author'] );
		$columns['authorized'] = __( 'Authorized' );
		$columns['phone'] = __( 'Contact Phone' );
		$columns['website'] = __( 'Website' );
		$columns['date'] = __( 'Published' );
		return $columns;
	}

	public static function column_display( $column_name, $id ) {
		$merchant = Group_Buying_Merchant::get_instance( $id );

		if ( !$merchant )
			return; // return for that temp post

		switch ( $column_name ) {
		case 'authorized':
			$display = '';
			$authorized_users = $merchant->get_authorized_users();
			foreach ( $authorized_users as $user_id ) {
				$user = get_userdata( $user_id );
				$display = $user->user_firstname . ' ' . $user->user_lastname;
				if ( ' ' == $display ) {
					$display = $user->user_login;
				}
				if ( !empty( $user->user_email ) ) {
					$display .= " (".$user->user_email.")";
				}
			}
			echo $display;
			break;
		case 'phone':
			echo $merchant->get_contact_phone();
			break;
		case 'website':
			echo '<a href="'. $merchant->get_website().'">'.$merchant->get_website().'</a>';
			break;
		default:
			break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['id'] = 'id';
		return $columns;
	}

	public static function add_link_to_admin_bar( $items ) {
		$items[] = array(
			'id' => 'edit_merchants',
			'title' => self::__( 'Edit Merchants' ),
			'href' => admin_url( 'edit.php?post_type='.Group_Buying_Merchant::POST_TYPE ),
			'weight' => 10,
		);
		return $items;
	}

	protected function validate_merchant_contact_info_fields( $submitted ) {
		$errors = array();
		$fields = $this->merchant_contact_info_fields();
		foreach ( $fields as $key => $data ) {
			if ( isset( $data['required'] ) && $data['required'] && !( isset( $submitted['gb_contact_'.$key] ) && $submitted['gb_contact_'.$key] != '' ) ) {
				$errors[] = sprintf( self::__( '"%s" field is required.' ), $data['label'] );
			}
		}
		return $errors;
	}

	protected function merchant_contact_info_fields( Group_Buying_Merchant $merchant = null ) {
		$fields = $this->get_standard_address_fields();

		unset( $fields['first_name'] );
		unset( $fields['last_name'] );

		$fields['merchant_title'] = array(
			'weight' => 0,
			'label' => self::__( 'Merchant Name' ),
			'type' => 'text',
			'required' => TRUE,
			'default' => ''
		);

		$fields['merchant_description'] = array(
			'weight' => 5,
			'label' => self::__( 'Merchant Description' ),
			'type' => 'textarea',
			'required' => TRUE,
			'default' => ''
		);

		$fields['merchant_thumbnail'] = array(
			'weight' => 7,
			'label' => self::__( 'Merchant Image' ),
			'type' => 'file',
			'required' => FALSE,
			'default' => '',
			'description' => gb__('<span>Optional:</span> Featured image for the merchant.')
		);

		$fields['name'] = array(
			'weight' => 11,
			'label' => self::__( 'Contact Name' ),
			'type' => 'text',
			'required' => TRUE,
			'default' => '',
		);
		/*/
		$fields['title'] = array(
			'weight' => 5,
			'label' => self::__('Contact Title'),
			'type' => 'text',
			'required' => TRUE,
			'default' => '',
		);
		/**/
		$fields['phone'] = array(
			'weight' => 16,
			'label' => self::__( 'Contact Phone' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '',
		);

		$fields['website'] = array(
			'weight' => 26,
			'label' => self::__( 'Website' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '',
		);
		$fields['facebook'] = array(
			'weight' => 27,
			'label' => self::__( 'Facebook' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '',
		);
		$fields['twitter'] = array(
			'weight' => 28,
			'label' => self::__( 'Twitter' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => '',
		);

		if ( is_a( $merchant, 'Group_Buying_Merchant' ) ) {
			$merchant_post = $merchant->get_post();
			$fields['merchant_title']['default'] = $merchant_post->post_title;
			$fields['merchant_description']['default'] = $merchant_post->post_content;
			$fields['name']['default'] = $merchant->get_contact_name();
			$fields['street']['default'] = $merchant->get_contact_street();
			$fields['city']['default'] = $merchant->get_contact_city();
			$fields['zone']['default'] = $merchant->get_contact_state();
			$fields['postal_code']['default'] = $merchant->get_contact_postal_code();
			$fields['country']['default'] = $merchant->get_contact_country();
			$fields['phone']['default'] = $merchant->get_contact_phone();
			$fields['website']['default'] = $merchant->get_website();
			$fields['facebook']['default'] = $merchant->get_facebook();
			$fields['twitter']['default'] = $merchant->get_twitter();


			$img_array = wp_get_attachment_image_src(get_post_thumbnail_id( $merchant->get_id() ));
			$fields['merchant_thumbnail']['default'] = $img_array[0];
		}

		$fields = apply_filters( 'gb_merchant_register_contact_info_fields', $fields, $merchant );
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		return $fields;
	}

	public function allow_file_uploads( $allcaps, $cap, $args ) {
		// Bail out if we're not asking about uploading files:
		if ( 'upload_files' != $cap[0] )
			return $allcaps;

		// Make sure the capabilities checked is for the current user
		if ( get_current_user_id() != $args[1] )
			return $allcaps;

		if ( !gb_account_has_merchant( get_current_user_id() ) )
			return $allcaps;

		$allcaps['upload_files'] = true;
		return $allcaps;
	}
}