<?php
/*
 * Adds Notifications for Merchant Registration and Submit Deal
 * By StudioFidelis.com
 */
 
class SF_Additional_Notifications extends Group_Buying_Controller
{
	protected static $instance;
	
	public static function init()
	{
		
		//Add to notifications
		add_filter('gb_notification_types', array(get_class(), 'add_notifications'), 11, 1);
		
		//Replace default admin notfication
		remove_action( 'gb_admin_notification', array('Group_Buying_Notifications', 'admin_notification' ), 10, 2 );
		add_action('gb_admin_notification', array(get_class(),'new_admin_notification'), 10, 2);
		
		//Add Shortcode
		add_filter('gb_notification_shortcodes', array(get_class(), 'add_shortcodes'), 10, 1);
		
		//Add hooks
		add_action( 'register_merchant', array(get_class(), 'send_user_register_merchant'), 10, 1);
		add_action( 'edit_merchant', array(get_class(), 'send_admin_merchant_edit'), 999, 1);
		add_action( 'submit_deal', array(get_class(), 'send_user_submit_deal'), 10, 1);
		add_action( 'transition_post_status', array( get_class(), 'handle_status_change'), 10, 3);
	
	}
	
	public static function add_notifications( $notifications ) {
		//Admin notifications
		$notifications['merchant_registration'] = array(
					'name' => self::__( 'Merchant Registration' ),
					'description' => self::__( 'Customize the notification email that is sent to Admins when a merchant registers' ),
					'shortcodes' => array( 'date', 'username', 'site_title', 'site_url', 'merchant_name' ),
					'default_title' => self::__( 'Merchant Registered at ' . get_bloginfo( 'name' ) ),
					'default_content' => self::default_merchant_registration_content(),
					'default_disabled' => FALSE
				);
		$notifications['merchant_edited'] = array(
					'name' => self::__( 'Merchant Edited' ),
					'description' => self::__( 'Customize the notification email that is sent to Admins when a merchant edits their profile.' ),
					'shortcodes' => array( 'date', 'username', 'site_title', 'site_url', 'merchant_name' ),
					'default_title' => self::__( 'Merchant Profile Edited at ' . get_bloginfo( 'name' ) ),
					'default_content' => 'A merchant has edited their profile and needs your review.',
					'default_disabled' => FALSE
				);
		$notifications['merchant_deal_submitted'] = array(
					'name' => self::__( 'Merchant Deal Submission' ),
					'description' => self::__( 'Customize the notification email that is sent to Admins when a merchant submits a deal for review.' ),
					'shortcodes' => array( 'date', 'username', 'site_title', 'site_url', 'deal_title', 'deal_url', 'merchant_name' ),
					'default_title' => self::__( 'New Deal Submission at ' . get_bloginfo( 'name' ) ),
					'default_content' => self::default_merchant_deal_submitted_content(),
					'default_disabled' => FALSE
				);
			
		//User notifications
		$notifications['merchant_published_user'] = array(
					'name' => self::__( 'Merchant Registration Approved (sent to user)' ),
					'description' => self::__( 'Customize the notification email that is sent to the User when a deal is approved (published).' ),
					'shortcodes' => array( 'date', 'username', 'site_title', 'site_url', 'merchant_name' ),
					'default_title' => self::__( 'Merchant Approved at ' . get_bloginfo( 'name' ) ),
					'default_content' => 'Your merchant account has been approved.',
					'default_disabled' => FALSE
				);
		$notifications['merchant_deal_published_user'] = array(
					'name' => self::__( 'Merchant Deal Approved (sent to user)' ),
					'description' => self::__( 'Customize the notification email that is sent to the User when a merchant is approved (published).' ),
					'shortcodes' => array( 'date', 'username', 'site_title', 'site_url', 'deal_title', 'deal_url', 'merchant_name' ),
					'default_title' => self::__( 'Deal Approved at ' . get_bloginfo( 'name' ) ),
					'default_content' => 'Your deal has been approved for listing.',
					'default_disabled' => FALSE
				);
				
		$notifications['merchant_registration_user'] = array(
					'name' => self::__( 'Merchant Registration (sent to user)' ),
					'description' => self::__( 'Customize the notification email that is sent to the User when they register a merchant' ),
					'shortcodes' => array( 'date', 'username', 'site_title', 'site_url', 'merchant_name' ),
					'default_title' => self::__( 'Merchant Registration received at ' . get_bloginfo( 'name' ) ),
					'default_content' => 'Your merchant registration has been received and is pending review.',
					'default_disabled' => FALSE
				);
		$notifications['merchant_deal_submitted_user'] = array(
					'name' => self::__( 'Merchant Deal Submission (sent to user)' ),
					'description' => self::__( 'Customize the notification email that is sent to the User when a merchant submits a deal for review.' ),
					'shortcodes' => array( 'date', 'username', 'site_title', 'site_url', 'deal_title', 'deal_url', 'merchant_name' ),
					'default_title' => self::__( 'Deal Submission received at ' . get_bloginfo( 'name' ) ),
					'default_content' => 'Your deal submission has been received and is pending review.',
					'default_disabled' => FALSE
				);
				
		return $notifications;
	}
	
	/* Handle the change of statuses */
	/* WP Post Statuses:
		'new' - When there's no previous status
		'publish' - A published post or page
		'pending' - post in pending review
		'draft' - a post in draft status
		'auto-draft' - a newly created post, with no content
		'future' - a post to publish in the future
		'private' - not visible to users who are not logged in
		'inherit' - a revision or attachment. see get_children.
		'trash' - post is in trashbin. added with Version 2.9.
		*/
	public static function handle_status_change ($status, $previous_status, $post) {
		
		//Should we go any further
		if ( empty($post) ) {
			//if (self::DEBUG) error_log('status change: '.$post->ID.' not a valid deal');
			return;
		}
		
		//Break, if new status is the same as previous status
		if ($status == $previous_status) {
			//if (self::DEBUG) error_log('status change: '.$post->ID.' no change in status');
			return;
		}
		
		//If newly approved (published )
		if ( in_array($previous_status, array('new', 'draft', 'auto-draft', 'trash', 'private', 'pending') ) && in_array($status, array('future', 'publish') ) ) {
			
			if ( $post->post_type == 'gb_deal' ) {
				
				$deal = Group_Buying_Deal::get_instance( $post->ID );
				
				//Get deals merchant id
				$merchant = Group_Buying_Merchant::get_merchant_object( $post->ID ); //from function gb_get_merchant_id
				if ( !is_object( $merchant ) ) return;
				$users = $merchant->get_authorized_users();
				if ( empty( $users ) ) return;
				$user_id = $users[0]; //Get the first
				if ( is_numeric( $user_id ) ) {
					$user = get_userdata( $user_id );
				}
				if ( !is_a( $user, 'WP_User' ) ) {
					if ( self::DEBUG ) error_log( "Get User Email FAILED: " . print_r( $user_id, true ) );
					return;
				}
				$user_email = $user->user_email;
				$name = gb_get_name( $user->ID );
		
				if ( empty( $name ) ) {
					$recipient = $user_email;
				} else {
					$recipient = "$name <$user_email>";
				}
				
				$data['user'] = ($user) ? $user : $user_id;
				$data['deal'] = $deal;
				
				Group_Buying_Notifications::send_notification( 'merchant_deal_published_user', $data, $recipient );
				
			} elseif ( $post->post_type == 'gb_merchant' ) {
				
				$merchant = Group_Buying_Merchant::get_instance( $post->ID );
				if ( !is_object( $merchant ) ) return;
				$users = $merchant->get_authorized_users();
				if ( empty( $users ) ) return;
				$user_id = $users[0]; //Get the first
				if ( is_numeric( $user_id ) ) {
					$user = get_userdata( $user_id );
				}
				if ( !is_a( $user, 'WP_User' ) ) {
					if ( self::DEBUG ) error_log( "Get User Email FAILED: " . print_r( $user_id, true ) );
					return;
				}
				$user_email = $user->user_email;
				$name = gb_get_name( $user->ID );
		
				if ( empty( $name ) ) {
					$recipient = $user_email;
				} else {
					$recipient = "$name <$user_email>";
				}
				
				$data['user'] = ($user) ? $user : $user_id;
				$data['merchant'] = $merchant; 
				
				Group_Buying_Notifications::send_notification( 'merchant_published_user', $data, $recipient );
				
			}
		}
	
	}
	
	public static function send_user_register_merchant($merchant) {

		//Also get the user
		$user_id = get_current_user_id();
		if ( is_numeric( $user_id ) ) {
			$user = get_userdata( $user_id );
		}
		
		if ( $user_id !== -1 ) { //
			$recipient = Group_Buying_Notifications::get_user_email( $user_id );

			$data['user'] = ($user) ? $user : $user_id;
			$data['rand'] = mt_rand();
			$data['merchant'] = $merchant;
			Group_Buying_Notifications::send_notification( 'merchant_registration_user', $data, $recipient );
		}
	}
	
	public static function send_user_submit_deal( $deal ) {
		//Also get the user
		$user_id = get_current_user_id();
		if ( is_numeric( $user_id ) ) {
			$user = get_userdata( $user_id );
		}
		
		if ( $user_id !== -1 ) { //
			$recipient = Group_Buying_Notifications::get_user_email( $user_id );

			$data['user'] = ($user) ? $user : $user_id;
			$data['rand'] = mt_rand();
			$data['deal'] = $deal;
			Group_Buying_Notifications::send_notification( 'merchant_deal_submitted_user', $data, $recipient );
		}
	}
	
	public static function new_admin_notification( $info, $data = array() ) { 
		
		$to = get_option( 'admin_email' );
		$from = get_option( 'blogname' );
		$headers = array( "From: ".$from." <".$to.">" );
		$header = implode( "\r\n", $headers ) . "\r\n";
		
		//Also get the user
		$user = get_current_user_id();
		if ( is_numeric( $user ) ) {
			$user = get_userdata( $user );
		}
		
		if ( $info['subject'] == self::__( 'New Deal Submission' ) ) {
			$newdata['user'] = $user;
			$newdata['rand'] = mt_rand();
			$newdata['deal'] = $info[0]; //deal is info array (should be in data - gbs bug)
			
			Group_Buying_Notifications::send_notification( 'merchant_deal_submitted', $newdata, $to );
			
		} elseif ( $info['subject'] == self::__( 'New Merchant Registration' ) ) {
			
			$newdata['user'] = $user;
			$newdata['rand'] = mt_rand();
			$newdata['merchant'] = $info[0]; //merchant is info array (should be in data - gbs bug)
			
			Group_Buying_Notifications::send_notification( 'merchant_registration', $newdata, $to );
			
		} else {
			
			//send it like a regular admin_notification
			wp_mail( $to, $info['subject'], $info['content'], $header );
		}
	}
	
	public static function default_merchant_registration_content() {
		$text = 'A user has registered as a merchant and needs your review.';
		return $text;
	}
	public static function default_merchant_deal_submitted_content() {
		$text = 'A user has submitted a new deal for your review.';
		return $text;
	}
	
	public static function add_shortcodes($shortcodes) {
		$shortcodes['merchant_name'] = array(
					'description' => self::__( 'Used to display the merchants name.' ),
					'callback' => array( get_class(), 'shortcode_merchant_name' )
				);
		
		return $shortcodes;	
	}
	
	public static function shortcode_merchant_name( $atts, $content, $code, $data ) {
		if ( isset( $data['merchant'] ) ) {
			$merchant = $data['merchant'];
			return get_the_title( $merchant->get_ID() );
		} elseif ( isset( $data['deal'] ) ) {
			$deal = $data['deal'];
			$deal_merchant_id = $deal->get_merchant_id();
			if ( $deal_merchant_id ) {
				return get_the_title($deal_merchant_id);
			}
		}
		return '';
	}
	
	
	public static function send_admin_merchant_edit($merchant) {
		global $wpdb;
		
		// Set status to draft (use wpdb query, because of issue with wp_update_post deleting meta)
		$merchant_id = $merchant->get_ID();
		$update_result = $wpdb->query(
				"
				UPDATE $wpdb->posts 
				SET post_status = 'draft'
				WHERE ID = ".$merchant_id."
				"
			);
		
		//$my_post['ID'] = $merchant->get_ID();
		//$my_post['post_status'] = 'pending';
		//wp_update_post( $my_post );
		
		// Send admin email
		
		//$to = get_option( 'admin_email' );
		$to = 'daniel@studiofidelis.com';
		
		//Also get the user
		$user = get_current_user_id();
		if ( is_numeric( $user ) ) {
			$user = get_userdata( $user );
		}
		
		$newdata['user'] = $user;
		$newdata['rand'] = mt_rand();
		$newdata['merchant'] = $merchant; 
		
		Group_Buying_Notifications::send_notification( 'merchant_edited', $newdata, $to );
	}
	

	/* Singleton Design Pattern
	 * ------------------------------------------------------------- */

	private function __clone() {
		// cannot be cloned
		trigger_error(__CLASS__.' may not be cloned', E_USER_ERROR);
	}
	private function __sleep() {
		// cannot be serialized
		trigger_error(__CLASS__.' may not be serialized', E_USER_ERROR);
	}
	public static function get_instance() {
		if ( !(self::$instance && is_a(self::$instance, __CLASS__)) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
	}
}
SF_Additional_Notifications::init();