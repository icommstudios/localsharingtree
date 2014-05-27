<?php
/**
 * This class provides a model for a subscription processor. To implement a
 * different list service, create a new class that extends
 * Group_Buying_List_Services. The new class should implement
 * the following methods (at a minimum):
 *  - get_instance()
 *  - process_subscription()
 *  - process_registration_subscription()
 *  - register()
 *  - get_subscription_method()
 *
 * You may also want to register some settings for the Payment Options page
 */

class Group_Buying_ConstantContact extends Group_Buying_List_Services {
	const LOGIN = 'gb_constantcontact_login';
	const PASSWORD = 'gb_constantcontact_password';
	protected static $instance;
	protected static $ccListOBJ;
	protected static $ccContactOBJ;
	private static $login = '';
	private static $password = '';
	private static $email = '';
	private static $location = '';
	private static $list_id = array();
	private static $latest_deal_redirect = '';


	protected static function get_instance() {
		if ( !( isset( self::$instance ) && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_subscription_method() {
		return self::SUBSCRIPTION_SERVICE;
	}

	protected function __construct() {
		parent::__construct();
		self::$login = get_option( self::LOGIN, '' );
		self::$password = get_option( self::PASSWORD, '' );

		if ( is_admin() ) {
			add_action( 'init', array( get_class(), 'register_options') );
		}
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_options() {
		// Settings
		$settings = array(
			'gb_constantcontact_sub' => array(
				'title' => self::__( 'ConstantContact Subscription Service' ),
				'weight' => 500,
				'settings' => array(
					self::LOGIN => array(
						'label' => self::__( 'Login' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$login
							)
						),
					self::PASSWORD => array(
						'label' => self::__( 'Password' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$password
							)
						),
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_List_Services::SETTINGS_PAGE );
	}

	public static function register() {
		self::add_list_service( __CLASS__, self::__( 'ConstantContact' ) );
	}

	public static function init_cc() {

		self::$email = $_POST['email_address'];
		self::$location = $_POST['deal_location'];
		require_once 'utilities/ctct.class.php';
		self::$ccListOBJ = new CC_List();
		self::$ccContactOBJ = new CC_Contact();

		// Figure out which list to subscribe the user to.
		$allLists = self::$ccListOBJ->getLists();
		foreach ( $allLists as $k => $item ) {
			$list_name = strtolower( $item['title'] );
			if ( ( self::$location == $list_name ) || ( self::$location == str_replace( ' ', '-', $list_name ) ) ) {
				self::$list_id[] = $item['id'];
				break;
			}
		}
		if ( empty( self::$list_id ) ) {
			self::$list_id[] =  'http://api.constantcontact.com/ws/customers/'.self::$login.'/lists/1';
		}
	}
	public function process_subscription() {
		self::init_cc();
		$postFields = array();
		$postFields["email_address"] = self::$email;
		$postFields["mail_type"] = 'HTML';
		$postFields["city_name"] = self::$location;
		$postFields["lists"] = self::$list_id;
		do_action( 'gb_log', 'process_subscription - postFields', $postFields );
		$contactXML = self::$ccContactOBJ->createContactXML( null, $postFields );
		do_action( 'gb_log', 'process_subscription - contactXML', $contactXML );
		if ( !self::$ccContactOBJ->addSubscriber( $contactXML ) ) {
			$message = self::$ccContactOBJ->lastError;
			SEC_Controller::set_message( $message, 'error' );
		} else {
			$class = "success";
			parent::success( $postFields["city_name"], $postFields["email_address"] );
		}
	}

	public function process_registration_subscription( $user = null, $user_login = null, $user_email = null, $password = null, $post = null ) {
		if ( !$post[ parent::REGISTRATION_OPTIN ] )
			return;

		self::init_cc();
		$cookie = gb_get_preferred_location();
		if ( !empty( $cookie ) ) {
			$current_location = $cookie;
		} elseif ( isset( $_POST['deal_location'] ) ) {
			$current_location = $_POST['deal_location'];
		} elseif ( isset( $_POST['gb_contact_city'] ) ) {
			$current_location = $_POST['gb_contact_city'];
		} else {
			$current_location = 'unknown';
		}
		$postFields = array();
		$postFields["email_address"] = $user_email;
		$postFields["mail_type"] = 'HTML';
		$postFields["city_name"] = $current_location;
		$postFields["lists"] = self::$list_id;
		$contactXML = self::$ccContactOBJ->createContactXML( null, $postFields );
		if ( !self::$ccContactOBJ->addSubscriber( $contactXML ) ) {
			$error = true;
		} else {
			$error = false;
			$_POST = apply_filters( 'gb_cc_subscription_post', array(), $_POST );
			if ( $current_location != 'unknown' ) {
				$_POST['deal_location'] = $current_location;
			}
		}
		return;
	}
}
Group_Buying_ConstantContact::register();
