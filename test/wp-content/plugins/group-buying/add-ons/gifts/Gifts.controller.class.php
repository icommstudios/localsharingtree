<?php

/**
 * Gifts controller
 *
 * @package GBS
 * @subpackage Gift
 */
class Group_Buying_Gifts extends Group_Buying_Controller {
	const REDEMPTION_PATH_OPTION = 'gb_gift_redemption';
	const REDEMPTION_QUERY_VAR = 'gb_gift_redemption';
	const FORM_ACTION = 'gb_gift_redemption';
	private static $redemption_path = 'gifts';
	protected static $settings_page;
	private static $instance;

	public static function init() {
		self::register_payment_pane();
		self::register_review_pane();
		//self::register_confirmation_pane();
		
		// Checkout actions
		add_action( 'completing_checkout', array( get_class(), 'save_recipient_for_purchase' ), 10, 1 );
		add_action( 'purchase_completed', array( get_class(), 'activate_gifts_for_purchase' ), 10, 1 );

		// Redemption Templating
		self::$redemption_path = get_option( self::REDEMPTION_PATH_OPTION, self::$redemption_path );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 0 );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_gift_callback' ), 10, 1 );
		add_action( 'parse_request', array( get_class(), 'manually_resend_gift' ), 1, 0 );

		// Admin
		self::$settings_page = self::register_settings_page( 'gift_records', self::__( 'Gift Records' ), self::__( 'Gifts' ), 9.1, FALSE, 'records', array( get_class(), 'display_table' ) );
	}

	/**
	 * Register action hooks for displaying and processing the payment page
	 *
	 * @return void
	 */
	private static function register_payment_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'display_payment_page' ), 10, 2 );
		add_action( 'gb_checkout_action_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'process_payment_page' ), 15, 1 ); // higher priority than an offsite redirect.
	}

	private static function register_review_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::REVIEW_PAGE, array( get_class(), 'display_review_page' ), 10, 2 );
	}

	private static function register_confirmation_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::CONFIRMATION_PAGE, array( get_class(), 'display_confirmation_page' ), 10, 2 );
	}

	public static function display_payment_page( $panes, $checkout ) {
		$fields = array(
			'is_gift' => array(
				'type' => 'checkbox',
				'label' => self::__( 'Is this purchase a gift for someone?' ),
				'weight' => 0,
				'default' => ( ( isset( $checkout->cache['gift_recipient'] )&&$checkout->cache['gift_recipient'] )||isset( $_GET['gifter'] )&&$_GET['gifter'] )?TRUE:FALSE,
				'value' => 'is_gift',
			),
			'recipient' => array(
				'type' => 'text',
				'label' => self::__( "Recipient's Email Address" ),
				'weight' => 10,
				'default' => isset( $checkout->cache['gift_recipient'] )?$checkout->cache['gift_recipient']:'',
			),
			'message' => array(
				'type' => 'textarea',
				'label' => self::__( "Your Message" ),
				'weight' => 10,
				'default' => isset( $checkout->cache['gift_message'] )?$checkout->cache['gift_message']:'',
			),
		);
		$fields = apply_filters( 'gb_checkout_fields_gifting', $fields );
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		$panes['gifting'] = array(
			'weight' => 5,
			'body' => self::load_view_to_string( 'checkout/gifting', array( 'fields' => $fields ) ),
		);
		return $panes;
	}


	/**
	 * Display the final review pane
	 *
	 * @param array   $panes
	 * @param Group_Buying_Checkout $checkout
	 * @return array
	 */
	public static function display_review_page( $panes, $checkout ) {
		if ( !empty($checkout->cache['gift_recipient']) ) {
			$panes['gifting'] = array(
				'weight' => 5,
				'body' => self::load_view_to_string( 'checkout/gifting-review', array( 'recipient' => $checkout->cache['gift_recipient'], 'message' => $checkout->cache['gift_message'] ) ),
			);
		}
		return $panes;
	}

	/**
	 * Display the confirmation page
	 * Don't depend on anything being in the cache except the purchase ID
	 *
	 * @return array
	 */
	public static function display_confirmation_page( $panes, $checkout ) {
		// FUTURE
		return $panes;
	}

	public static function save_recipient_for_purchase( $checkout ) {
		if ( !empty($checkout->cache['gift_recipient']) && !empty($checkout->cache['purchase_id']) ) {
			$purchase = Group_Buying_Purchase::get_instance( $checkout->cache['purchase_id'] );
			$gift_id = Group_Buying_Gift::new_gift( $purchase->get_id(), $checkout->cache['gift_recipient'], $checkout->cache['gift_message'] );
		}
	}

	public static function activate_gifts_for_purchase( Group_Buying_Purchase $purchase ) {
		$gift_id = Group_Buying_Gift::get_gift_for_purchase( $purchase->get_id() );
		if ( $gift_id ) {
			$gift = Group_Buying_Gift::get_instance( $gift_id );
			$gift->activate();
		}
	}

	/**
	 * Register the path callback
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_gift_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$redemption_path,
			'title' => 'Redeem a Gift Certificate',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_redemption_page' ),
			'access_callback' => array( get_class(), 'login_required' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$redemption_path ).'.php',
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::REDEMPTION_QUERY_VAR, $args );
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_cart_paths';

		// Settings
		register_setting( $page, self::REDEMPTION_PATH_OPTION );
		add_settings_field( self::REDEMPTION_PATH_OPTION, self::__( 'Gift Redemption Path' ), array( get_class(), 'display_path' ), $page, $section );
	}

	public static function display_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="'.self::REDEMPTION_PATH_OPTION.'" id="'.self::REDEMPTION_PATH_OPTION.'" value="' . esc_attr( self::$redemption_path ) . '"  size="40" /><br />';
	}

	public static function on_redemption_page() {
		$redemption_page = self::get_instance();
		if ( isset( $_POST['gb_gift_action'] ) && $_POST['gb_gift_action'] == self::FORM_ACTION ) {
			$redemption_page->process_form_submission();
		}
		// View template
		$redemption_page->view_redemption_form();
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
		self::do_not_cache(); // never cache the redemption page
	}

	/**
	 * Update the global $pages array with the HTML for the page.
	 *
	 * @param object  $post
	 * @return void
	 */
	public function view_redemption_form() {
		remove_filter( 'the_content', 'wpautop' );
		$user = wp_get_current_user();
		self::load_view( 'gift/redemption', array( 'email' => $user->user_email ) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a account page
	 */
	public static function is_gift_redemption_page() {
		return GB_Router_Utility::is_on_page( self::REDEMPTION_QUERY_VAR );
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
		return self::__('Redeem a Gift Certificate');
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the merchant page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$redemption_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::REDEMPTION_QUERY_VAR );
		}
	}

	private function process_form_submission() {
		$valid = TRUE;
		$email = $_POST['gb_gift_redemption_email'];
		$code = $_POST['gb_gift_redemption_code'];

		if ( !sanitize_email( $email ) ) {
			self::set_message( self::__( 'A valid email address is required.' ), self::MESSAGE_STATUS_ERROR );
			$valid = FALSE;
		}
		if ( !$code ) {
			self::set_message( self::__( 'A valid coupon code is required.' ), self::MESSAGE_STATUS_ERROR );
			$valid = FALSE;
		}
		if ( !$valid ) {
			return;
		}

		$gift_id = Group_Buying_Gift::validate_gift( $email, $code );
		if ( !$gift_id ) {
			self::set_message( self::__( 'Invalid code: please confirm the email address and coupon code.' ), self::MESSAGE_STATUS_ERROR );
			return;
		}
		$gift = Group_Buying_Gift::get_instance( $gift_id );
		$purchase = $gift->get_purchase();
		if ( $purchase->get_user() != Group_Buying_Purchase::NO_USER ) {
			self::set_message( self::__( 'Error: this coupon code has already been used.' ), self::MESSAGE_STATUS_ERROR );
			return;
		}
		$purchase->set_user( get_current_user_id() );
		self::set_message( sprintf( self::__( 'Success! Visit <a href="%s">your purchases page</a> to print your vouchers.' ), gb_get_vouchers_url() ) );
		wp_redirect( apply_filters( 'gb_gifting_process_gift_redirection', gb_get_vouchers_url(), $purchase ) );
		exit();
	}

	///////////////////////
	// Checkout Actions //
	///////////////////////

	public static function process_payment_page( Group_Buying_Checkouts $checkout ) {
		$valid = TRUE;
		if ( isset( $_POST['gb_gifting_is_gift'] ) && $_POST['gb_gifting_is_gift'] == 'is_gift' ) {

			// Get current user email
			$user = get_userdata( get_current_user_id() );
			$user_email = $user->user_email;

			// Confirm an email was added
			if ( !isset( $_POST['gb_gifting_recipient'] ) || !$_POST['gb_gifting_recipient'] ) {
				self::set_message( "Recipient's Email Address is required for gift purchases", self::MESSAGE_STATUS_ERROR );
				$valid = FALSE;
			}
			//Check email validity
			elseif ( !sanitize_email( $_POST['gb_gifting_recipient'] ) ) {
				self::set_message( "A valid email address is required for the gift recipient", self::MESSAGE_STATUS_ERROR );
				$valid = FALSE;
			}
			//Check to see if they gave the same email that's tied to their account.
			elseif ( $user_email == sanitize_email( $_POST['gb_gifting_recipient'] ) ) {
				self::set_message( self::__( 'You deserve it but you may not gift yourself this purchase.' ), self::MESSAGE_STATUS_ERROR );
				$valid = FALSE;
			}
		}
		if ( !$valid ) {
			$checkout->mark_page_incomplete( Group_Buying_Checkouts::PAYMENT_PAGE );
		} elseif ( isset( $_POST['gb_gifting_is_gift'] ) && $_POST['gb_gifting_is_gift'] == 'is_gift' ) {
			$checkout->cache['gift_recipient'] = $_POST['gb_gifting_recipient'];
			$checkout->cache['gift_message'] = $_POST['gb_gifting_message'];
		}
	}

	///////////////////
	// AJAX Actions //
	///////////////////

	public static function manually_resend_gift( $gift_id = null ) {
		if ( !current_user_can( 'edit_posts' ) ) {
			return; // security check
		}
		if ( isset( $_REQUEST['resend_gift'] ) && $_REQUEST['resend_gift'] != '' ) {
			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'resend_gift' ) ) {
				$gift_id = $_REQUEST['resend_gift'];
				$recipient = $_REQUEST['recipient'];
			}
		}
		if ( is_numeric( $gift_id ) ) {
			$gift = Group_Buying_Gift::get_instance( $gift_id );
			if ( is_a( $gift, 'Group_Buying_Gift' ) ) {
				if ( $recipient != '' ) {
					$gift->set_recipient( $recipient );
				}
				do_action( 'gb_gift_notification', array( 'gift' => $gift, 'force_resend' => time() ) );
				return;
			}
		}
	}

	////////////
	// Admin //
	////////////

	public static function display_table() {
		//Create an instance of our package class...
		$wp_list_table = new Group_Buying_Gifts_Table();
		//Fetch, prepare, sort, and filter our data...
		$wp_list_table->prepare_items();

		?>
		<script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function($){
				jQuery(".gb_resend_gift").click(function(event) {
					event.preventDefault();
						if(confirm("Are you sure?")){
							var $link = $( this ),
							gift_id = $link.attr( 'ref' ),
							recipient = $( "#"+gift_id+"_recipient_input" ).val(),
							url = $link.attr( 'href' );
							$( "#"+gift_id+"_activate" ).fadeOut('slow');
							$.post( url, { resend_gift: gift_id, recipient: recipient },
								function( data ) {
										$( "#"+gift_id+"_activate_result" ).append( '<?php self::_e( 'Resent' ) ?>' ).fadeIn();
									}
								);
						} else {
							// nothing to do.
						}
				});
				jQuery(".editable_string").click(function(event) {
					event.preventDefault();
					var gift_id = $( this ).attr( 'ref' );
					$( '#' + gift_id + '_recipient_input').show();
					$(this).hide();
				});

			});
		</script>
		<style type="text/css">
			#payment_deal_id-search-input, #purchase_id-search-input, #payment_account_id-search-input { width:5em; margin-left: 10px;}
		</style>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php self::display_admin_tabs(); ?>
			</h2>

			 <?php $wp_list_table->views() ?>
			<form id="payments-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $wp_list_table->search_box( self::__( 'Purchase ID' ), 'purchase_id' ); ?>
				<p class="search-box deal_search">
					<label class="screen-reader-text" for="payment_deal_id-search-input"><?php self::_e('Deal ID:') ?></label>
					<input type="text" id="payment_deal_id-search-input" name="deal_id" value="">
					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e('Deal ID') ?>">
				</p>
				<p class="search-box account_search">
					<label class="screen-reader-text" for="payment_account_id-search-input"><?php self::_e('Account ID:') ?></label>
					<input type="text" id="payment_account_id-search-input" name="account_id" value="">
					<input type="submit" name="" id="search-submit" class="button" value="<?php self::_e('Account ID') ?>">
				</p>
				<?php $wp_list_table->display() ?>
			</form>
		</div>
		<?php
	}

}