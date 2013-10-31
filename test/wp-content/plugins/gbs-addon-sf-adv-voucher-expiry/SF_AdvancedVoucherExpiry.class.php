<?php 

/**
 * Load via GBS Add-On API
 * NOTE: Based on original code from GroupBuyingSite.com & from Dan Cameron (SproutVenture.com)
 */
class Group_Buying_SF_AdvVoucherExpiry_Addon extends Group_Buying_Controller {
	
	private static $instance;

	public static function init() {
		// Hook this plugin into the GBS add-ons controller
		add_filter('gb_addons', array(get_class(),'gb_addon'), 10, 1);
	}
	public static function get_instance() {
		if ( !(self::$instance && is_a(self::$instance, __CLASS__)) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function gb_addon( $addons ) {
		$addons['gbs_sf_advvoucherexpiry_addon'] = array(
			'label' => self::__('Adv. Voucher Expiry (by Days)'),
			'description' => self::__('Add option for Voucher Expiration by Days after Purchase. Disable access to vouchers after expiration.'),
			'files' => array(
				__FILE__,
				dirname( __FILE__ ) . '/library/template-tags.php',
			),
			'callbacks' => array(
				array('GBS_SF_AdvVoucherExpiry_Addon', 'init'),
			),
		);
		return $addons;
	}

}

class GBS_SF_AdvVoucherExpiry_Addon extends Group_Buying_Controller {

	private static $meta_keys = array(
		'expiry_onoff' => 'gbs_adv_voucher_expiry_onoff', // string
		'expiry_count' => 'gbs_adv_voucher_expiry_count', // int
	);

	public static function init() {

		// Meta Boxes
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'));
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
				
		//Set new Expiration date on GBS Voucher (or display the existing date)
		add_filter('gb_get_voucher_expiration_date',  array(get_class(), 'override_adv_voucher_expiration_date'), 11, 2);
		
		//Set new Expiration date on Notification shortcode
		add_filter('gb_notification_shortcodes',  array(get_class(), 'override_shortcode_adv_voucher_expiration_date'), 11, 1);
		
		//Check if the voucher page is expired
		//add_action('template_redirect',  array(get_class(), 'check_voucher_expiration'), 999, 1);
		
	}
	
	public static function override_shortcode_adv_voucher_expiration_date($default_shortcodes) {
		$default_shortcodes['voucher_expiration'] = array(
					'description' => self::__( 'Used to display the voucher&rsquo;s expiration.' ),
					'callback' => array( get_class(), 'adv_exp_shortcode_voucher_exp' )
				);
		return $default_shortcodes;
	}
	public static function adv_exp_shortcode_voucher_exp( $atts, $content, $code, $data ) {
		if ( isset( $data['voucher'] ) ) {
			$voucher = $data['voucher'];
			$date = self::override_adv_voucher_expiration_date( '', $voucher->get_ID() );
			$formated_date = ( $date != '' ) ? date( 'm/d/Y', $date ) : '';
			if ( !$date ) {
				//Return regular expiration
				$date = $voucher->get_expiration_date();
				$formated_date = ( $date != '' ) ? date( 'm/d/Y', $date ) : '';
			}
			return $formated_date;
		}
		return '';
	}
	
	public static function check_voucher_expiration() {
		global $post;
		
		//Are we on a single voucher page
		if ( is_single() && Group_Buying_Voucher::is_voucher_query() ) {
			
			$voucher_id = $post->ID;
			
			if ( self::check_if_voucher_expired( $voucher_id ) ) {
				//Kill page
				die(gb__('Voucher has Expired.'));
			}
			
		}
	}
	
	public static function check_if_voucher_expired ($post_id = NULL) {
		if (!$post_id) return;
		
		if (gb_get_voucher_expiration_date( $post_id ) ) { 
			if (gb_get_voucher_expiration_date( $post_id ) < time() ) { 
				return TRUE; //Expired
			}
		}
		return FALSE;
	}
	
	public function override_adv_voucher_expiration_date ( $date = '', $voucher_id = null) {
		
		if ( !$voucher_id ) {
			global $post;
			$voucher_id = $post->ID;
		}
		if ( !$voucher_id ) {
			return '';
		}
		
		$voucher = Group_Buying_Voucher::get_instance($voucher_id);
		$deal = $voucher->get_deal();
		
		//If Advanced Voucher Expiry is set
		if ( self::get_expiry_onoff($deal) == 'on' && self::get_expiry_count($deal) != '') {
			$purchased_date_timestamp = get_the_time('U', $voucher_id);
			$voucher_expiration_days = floatval(self::get_expiry_count($deal));
			$new_date = $purchased_date_timestamp + ($voucher_expiration_days  * 86400);
			return $new_date;
		}
		
		return $date;
	}
	
	public static function add_meta_boxes() {
		add_meta_box('advvoucherexpiry', self::__('Adv. Voucher Expiry (by Days)'), array(get_class(), 'show_meta_boxes'), Group_Buying_Deal::POST_TYPE, 'normal', 'high');
	}
	
	public static function get_expiry_onoff( Group_Buying_Deal $deal ) {
		$deal_id = $deal->get_id();
		$expiry_onoff = $deal->get_post_meta("_".self::$meta_keys['expiry_onoff']);
		return apply_filters('gb_get_adv_voucher_setting_expiry_onoff', $expiry_onoff, $deal_id);
	}
	public static function set_expiry_onoff( Group_Buying_Deal $deal, $expiry_onoff ) {
		return $deal->save_post_meta(array("_".self::$meta_keys['expiry_onoff'] => $expiry_onoff));
	}

	public static function get_expiry_count( Group_Buying_Deal $deal ) {
		$deal_id = $deal->get_id();
		$expiry_count = $deal->get_post_meta("_".self::$meta_keys['expiry_count']);
		return apply_filters('gb_get_adv_voucher_setting_expiry_days', $expiry_count, $deal_id);
	}
	public static function set_expiry_count( Group_Buying_Deal $deal, $expiry_count ) {
		return $deal->save_post_meta(array("_".self::$meta_keys['expiry_count'] => $expiry_count));
	}

	public static function show_meta_boxes( $post, $metabox ) {
		$deal = Group_Buying_Deal::get_instance($post->ID);
		switch ( $metabox['id'] ) {
			case 'advvoucherexpiry':
				self::show_meta_box($deal, $post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}

	private static function show_meta_box( Group_Buying_Deal $deal, $post, $metabox ) {
		$expiry_onoff = self::get_expiry_onoff($deal);
		$expiry_count = self::get_expiry_count($deal);
		include('views/metabox.php');
	}
	
	public static function save_meta_boxes( $post_id, $post ) {
		// only continue if it's an account post
		if ( $post->post_type != Group_Buying_Deal::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined('DOING_AJAX') || isset($_GET['bulk_edit']) ) {
			return;
		}
		// save all the meta boxes
		$deal = Group_Buying_Deal::get_instance($post_id);
		if ( !is_a($deal, 'Group_Buying_Deal') ) {
			return; // The account doesn't exist
		}
		self::save_meta_box($deal, $post_id, $post);
	}


	private static function save_meta_box( Group_Buying_Deal $deal, $post_id, $post ) {

		if ( isset($_POST[self::$meta_keys['expiry_onoff']]) && $_POST[self::$meta_keys['expiry_onoff']] != 'none' ) {
			self::set_expiry_onoff($deal, $_POST[self::$meta_keys['expiry_onoff']]);
		}
		
		if ( isset($_POST[self::$meta_keys['expiry_count']]) && $_POST[self::$meta_keys['expiry_count']] != 'none' ) {
			self::set_expiry_count($deal, $_POST[self::$meta_keys['expiry_count']]);
		}
	}
	

}