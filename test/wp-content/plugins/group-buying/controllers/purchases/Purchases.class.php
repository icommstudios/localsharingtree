<?php

/**
 * Purchase Controller
 *
 * @package GBS
 * @subpackage Purchase
 */
class Group_Buying_Purchases extends Group_Buying_Controller {
	const SETTINGS_PAGE = 'purchase_records';
	const ORDER_LU_OPTION = 'gb_order_lookup_path';
	const AUTH_FORM_INPUT = 'order_billing_city';
	const AUTH_FORM_ID_INPUT = 'order_id';
	const NONCE_ID = 'gb_order_lookup_nonce';
	private static $lookup_path = 'order-lookup';

	public static function get_admin_page( $prefixed = TRUE ) {
		return ( $prefixed ) ? self::TEXT_DOMAIN . '/' . self::SETTINGS_PAGE : self::SETTINGS_PAGE ;
	}

	public static function init() {
		self::$lookup_path = get_option( self::ORDER_LU_OPTION, self::$lookup_path );
		self::register_settings();

		// Wrapper Template
		add_filter( 'template_include', array( get_class(), 'override_template' ) );
		// Modify Content for purchase template
		add_action( 'the_post', array( get_class(), 'purchase_content' ), 10, 1 );
		add_filter( 'the_title', array( get_class(), 'get_title' ), 10, 2 );

		// Order Lookup
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_path_callback' ), 10, 1 );
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		
		// Option page
		$args = array(
			'slug' => self::SETTINGS_PAGE,
			'title' => self::__( 'Orders' ),
			'menu_title' => self::__( 'Orders' ),
			'weight' => 12,
			'reset' => FALSE, 
			'section' => 'records',
			'callback' => array( get_class(), 'display_table' )
			);
		do_action( 'gb_settings_page', $args );

		// Settings
		$settings = array(
			'gb_url_path_order_lookup' => array(
				'weight' => 140,
				'settings' => array(
					self::ORDER_LU_OPTION => array(
						'label' => self::__( 'Order Lookup Path' ),
						'option' => array(
							'label' => trailingslashit( get_home_url() ),
							'type' => 'text',
							'default' => self::$lookup_path
							)
						)
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_UI::SETTINGS_PAGE );
	}

	/**
	 * Override template for the purchase post type
	 *
	 * @param string  $template
	 * @return
	 */
	public static function override_template( $template ) {
		$post_type = get_query_var( 'post_type' );
		if ( $post_type == Group_Buying_Purchase::POST_TYPE ) {
			if ( is_single() ) {
				$template = self::locate_template( array(
						'account/single-purchase.php',
						'account/single-order.php',
						'order/single.php',
						'orders/single.php',
						'purchase/single.php',
						'purchases/single.php',
						'order.php',
						'purchase.php',
						'account.php'
					), $template );
			}
		}
		return $template;
	}

	/**
	 * Update the global $pages array with the HTML for the current checkout page
	 *
	 * @static
	 * @param object  $post
	 * @return void
	 */
	public function purchase_content( $post ) {
		if ( $post->post_type == Group_Buying_Purchase::POST_TYPE && is_single() ) {
			$purchase_id = $post->ID;
			$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
			// Remove content filter
			remove_filter( 'the_content', 'wpautop' );

			if ( self::authorized_user( $purchase_id ) ) {
				$args = array(
					'order_number' => $purchase_id,
					'tax' => $purchase->get_tax_total(),
					'shipping' => $purchase->get_shipping_total(),
					'total' => $purchase->get_total(),
					'products' => $purchase->get_products()
				);
				$view = self::load_view_to_string( 'purchase/order', $args );
			}
			else { // show the authentication form
				$view = self::lookup_page( TRUE );
			}
			// Display content
			global $pages;
			$pages = array( $view );
		}
	}

	/**
	 * Filter 'the_title' to display the title of the current page, purchase or lookup
	 *
	 * @static
	 * @param string  $title
	 * @param int     $post_id
	 * @return string
	 */
	public function get_title( $title = '', $post_id = 0 ) {
		if ( Group_Buying_Purchase::POST_TYPE == get_post_type( $post_id ) ) {
			if ( is_single() ) {
				$filtered = self::__( 'Order Lookup' );
				if ( self::authorized_user( $post_id ) ) {
					$filtered .= ': '.str_replace( 'Order ', '', $title );
				}
				return $filtered;
			}
		}
		return $title;
	}

	/**
	 * Lookup view
	 *
	 * @return
	 */
	public function lookup_page( $return = FALSE ) {
		$args = array( 'action' => self::get_url(), 'nonce_id' => self::NONCE_ID, 'city_option_name' => self::AUTH_FORM_INPUT, 'order_option_name' => self::AUTH_FORM_ID_INPUT );
		remove_filter( 'the_content', 'wpautop' );
		if ( !$return ) {
			self::load_view( 'purchase/order-lookup', $args );
		} else {
			return self::load_view_to_string( 'purchase/order-lookup', $args );
		}
	}

	/**
	 * Check to see if the user has access to view the purchase content.
	 *
	 * @param int     $purchase_id
	 * @param int     $user_id
	 * @return bool|string
	 */
	public static function authorized_user( $purchase_id, $user_id = 0 ) {
		$return = FALSE;
		if ( !$user_id ) {
			$user_id = get_current_user_id();
		}
		if ( Group_Buying_Purchase::POST_TYPE != get_post_type( $purchase_id ) ) {
			return FALSE;
		}
		$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
		$purchase_user_id = $purchase->get_user();
		// If logged in or manually checked
		if ( $user_id ) {
			// Purchaser or admin
			if ( ( $user_id == $purchase_user_id ) || current_user_can( 'manage_options' ) ) {
				$return = TRUE;
			}
		}
		// Submitted form
		if ( // handle both submissions and $_GET variables from a redirect
			( isset( $_POST['gb_order_lookup_'.self::AUTH_FORM_INPUT] ) && $_POST['gb_order_lookup_'.self::AUTH_FORM_INPUT] != '' ) ||
			( isset( $_REQUEST[self::AUTH_FORM_INPUT] ) && $_REQUEST[self::AUTH_FORM_INPUT] != '' )
		) {
			// submitted form and has a matching billing city
			$account = Group_Buying_Account::get_instance( $purchase_user_id );
			$address = $account->get_address();
			$query = ( isset( $_REQUEST[self::AUTH_FORM_INPUT] ) ) ? $_REQUEST[self::AUTH_FORM_INPUT] : $_POST['gb_order_lookup_'.self::AUTH_FORM_INPUT] ;
			if ( strtolower( $address['city'] ) == strtolower( $query ) ) {
				$return = strtolower( $address['city'] );
			}
		}
		return apply_filters( 'gb_purchase_view_authorized_user', $return, $purchase_id, $user_id );
	}

	/**
	 * Register the path callback for the order lookup page
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_path_callback( GB_Router $router ) {
		$path = str_replace( '/', '-', self::$lookup_path );
		$args = array(
			'path' => self::$lookup_path,
			'title' => self::__( 'Order Lookup' ),
			'page_callback' => array( get_class(), 'lookup_page' ),
			'access_callback' => array( get_class(), 'process_form' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$lookup_path ).'.php',
				self::get_template_path().'/order.php', // theme override
				GB_PATH.'/views/public/order.php', // default
			),
		);
		$router->add_route( 'gb_show_order_lookup', $args );
	}

	public function process_form() {
		$message = FALSE;
		if ( isset( $_POST['gb_order_lookup_'.self::AUTH_FORM_ID_INPUT] ) && $_POST['gb_order_lookup_'.self::AUTH_FORM_ID_INPUT] ) {
			if ( !wp_verify_nonce( $_POST['_wpnonce'], self::NONCE_ID ) ) {
				$message = self::__( 'Invalid Submission Attempt' );
			}
			else {
				$purchase_id = $_POST['gb_order_lookup_'.self::AUTH_FORM_ID_INPUT];
				if ( Group_Buying_Purchase::POST_TYPE == get_post_type( $purchase_id ) ) {
					if ( $billing_auth = self::authorized_user( $purchase_id ) ) {
						$url = add_query_arg( array( self::AUTH_FORM_INPUT => $billing_auth ), get_permalink( $purchase_id ) );
						wp_redirect( $url );
						exit();
					} else {
						$message = self::__( 'Invalid Billing City' );
					}
				} else {
					$message = self::__( 'Order ID not found' );
				}
			}
		}
		if ( $message ) {
			self::set_message( $message );
		}
		return TRUE;
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the cart page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$lookup_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( 'gb_show_order_lookup' );
		}
	}


	public static function display_table() {
		add_thickbox();
		//Create an instance of our package class...
		$wp_list_table = new Group_Buying_Purchases_Table();
		//Fetch, prepare, sort, and filter our data...
		$wp_list_table->prepare_items();

		?>
		<script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function($){
				jQuery(".gb_delete_payment").on('click', function(event) {
					event.preventDefault();
					var $delete_button = $( this ),
					destroy_purchase_id = $delete_button.attr( 'ref' ),
					notes_form = $( '#delete_note_' + destroy_purchase_id ).val();
					$delete_button.html("<?php gb_e('Working...') ?>");
					$.post( ajaxurl, { action: 'gbs_destroyer', type: 'purchase', id: destroy_purchase_id, notes: notes_form, destroyer_nonce: '<?php echo wp_create_nonce( Group_Buying_Destroy::NONCE ) ?>' },
						function( data ) {
							self.parent.tb_remove();
							$('#void_link_'+destroy_purchase_id).closest('tr').fadeOut('slow');
						}
					);
				});
			});
			jQuery(document).ready(function($){
				jQuery(".gb_void_purchase").on('click', function(event) {
					event.preventDefault();
					var $void_button = $( this ),
					void_purchase_id = $void_button.attr( 'ref' ),
					notes_form = $( '#transaction_data_' + void_purchase_id ).val();
					$void_button.html("<?php gb_e('Working...') ?>");
					$.post( ajaxurl, { action: 'gbs_void_purchase', purchase_id: void_purchase_id, notes: notes_form, void_purchase_nonce: '<?php echo wp_create_nonce( Group_Buying_Destroy::NONCE ) ?>' },
						function( data ) {
							self.parent.tb_remove();
							$('#void_link_'+void_purchase_id).closest('.column-status').html('<?php self::_e("Voided") ?>');
						}
					);
				});
			});
		</script>
		<style type="text/css">
			#payment_deal_id-search-input, #purchase_id-search-input, #payment_account_id-search-input { width:5em; margin-left: 10px;}
		</style>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php do_action( 'gb_settings_tabs' ); ?>
			</h2>

			 <?php $wp_list_table->views() ?>
			<form id="payments-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $wp_list_table->search_box( self::__( 'Order ID' ), 'purchase_id' ); ?>
				<p class="search-box deal_search">

					<label class="screen-reader-text" for="payment_deal_id-search-input"><?php self::_e( 'Deal ID:' ) ?></label>
					<input type="text" id="payment_deal_id-search-input" name="deal_id" value="">
					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e( 'Deal ID' ) ?>">
				</p>
				<p class="search-box account_search">
					<label class="screen-reader-text" for="payment_account_id-search-input"><?php self::_e( 'Account ID:' ) ?></label>
					<input type="text" id="payment_account_id-search-input" name="account_id" value="">
					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e( 'Account ID' ) ?>">
				</p>
				<?php $wp_list_table->display() ?>
			</form>
		</div>
		<?php
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

	private function __construct() {}
}