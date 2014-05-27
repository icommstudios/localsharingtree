<?php

class Group_Buying_Accounts_Checkout extends Group_Buying_Controller {
	private static $instance;
	const GUEST_PURCHASE_USER_FLAG = 'guest_purchase_user_flag';

	public static function init() {
		self::get_instance();
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
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( $this, 'display_checkout_registration_form' ), 10, 2 );
		add_action( 'gb_checkout_action_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( $this, 'process_checkout_registration_form' ), 0, 1 );
		add_filter( 'gb_valid_process_payment_page', array( $this, 'validate_payment_page' ), 10, 2 );
		add_action( 'gb_user_logged_in', array( $this, 'claim_cart' ) );
		add_filter( 'gb_checkout_account_registration_panes', array( $this, 'filter_registration_panes' ) );
		add_filter( 'gb_account_register_user_fields', array( $this, 'filter_register_user_fields' ) );
		add_filter( 'gb_account_register_contact_info_fields', array( $this, 'filter_contact_fields_on_checkout_registration' ) );

		add_filter( 'gb_account_register_user_fields', array( $this, 'registration_fields' ), 10, 1 );
		add_action( 'checkout_completed', array( $this, 'checkout_complete' ), 10, 3 );
		add_action( self::CRON_HOOK, array( get_class(), 'delete_temp_users' ), 10, 0 );
	}

	public function display_checkout_registration_form( $panes, $checkout ) {

		// Registered users shouldn't see the
		if ( get_current_user_id() ) {
			return $panes;
		}
		if ( !get_option( 'users_can_register' ) ) {
			$error_new_pane['error'] = array(
				'weight' => 1,
				'body' => gb__( 'Contact an Administrator, registrations are disabled.' ),
			);
			return $error_new_pane;
		}
		if ( !get_current_user_id() ) {
			$args = array();
			if ( get_option( 'users_can_register' ) ) {
				$args['registration_form'] = $this->get_registration_form();
			}
			$args['login_form'] = $this->get_login_form();
			$panes['account'] = array(
				'weight' => 1,
				'body' => self::load_view_to_string( 'checkout/login-or-register', $args ),
			);
		}
		return $panes;
	}

	public function registration_fields( $fields = array() ) {
		if ( gb_on_checkout_page() ) {
			$fields['guest_purchase'] = array(
				'weight' => -10,
				'label' => self::__( 'Guest Purchase' ),
				'type' => 'checkbox',
				'required' => FALSE,
				'value' => 1,
			);
			unset($fields['password2']);
		}
		return $fields;
	}

	private function get_registration_form() {
		$registration = Group_Buying_Accounts_Registration::get_instance(); // make sure the class is instantiated
		$panes = apply_filters( 'gb_account_registration_panes', $registration->get_panes( array() ) );
		$panes = apply_filters( 'gb_checkout_account_registration_panes', $panes );
		uasort( $panes, array( get_class(), 'sort_by_weight' ) );
		$args = apply_filters( 'gb_checkout_account_registration_args', array() );
		$view = self::load_view_to_string( 'checkout/register', array( 'panes' => $panes, 'args' => $args ) );
		return $view;
	}

	public function filter_registration_panes( $panes ) {
		//mc_subs - for design purposes but possibly wanted.
		//contact_info - Make it a minimal registration since the checkout process will save the billing info.

		// Create an array of unregistered panes so it can be more easily filtered.
		$unregistered_checkout_panes = apply_filters( 'unregistered_registration_checkout_panes', array( 'mc_subs', 'contact_info' ) );
		foreach ( $unregistered_checkout_panes as $pane_key ) {
			unset( $panes[$pane_key] );
		}
		return $panes;
	}

	/**
	 * Registration fields on the checkout page should not be required
	 * @param  array $fields 
	 * @return array         
	 */
	public function filter_register_user_fields( $fields ) {
		if ( gb_on_checkout_page() ) {
			foreach ( $fields as $key => $value ) {
				$fields[$key]['required'] = FALSE;
			}
		}
		return $fields;
	}

	public function filter_contact_fields_on_checkout_registration( $fields ) {
		if ( isset( $_POST['gb_login_or_register'] ) ) {
			return array();
		}
		return $fields;
	}

	private function get_login_form() {
		$args = apply_filters( 'gb_checkout_account_login_args', array() );
		$view = self::load_view_to_string( 'checkout/login', array( 'args' => $args ) );
		return $view;
	}

	/**
	 * Hook into the payment process page and check to see if the the user is trying to login or register.
	 * Check to see if the user has selected guest checkout as well.
	 * @param  Group_Buying_Checkouts $checkout
	 * @return
	 */
	public function process_checkout_registration_form( Group_Buying_Checkouts $checkout ) {
		if ( !isset( $_POST['gb_login_or_register'] ) ) {
			return;
		}

		if ( $_POST['log'] != '' ) {
			$login = Group_Buying_Accounts_Login::get_instance(); // make sure the class is instantiated
			$user = wp_signon();
			wp_set_current_user( $user->ID );
			if ( !$user || !$user->ID ) {
				self::set_message( self::__( 'Login unsuccessful. Please try again.' ), self::MESSAGE_STATUS_ERROR );
			}
		} else {

			// Guest Checkout
			if ( isset( $_POST['gb_user_guest_purchase'] ) && $_POST['gb_user_guest_purchase'] ) {
				$cart = &$checkout->get_cart();
				$cart_id = $cart->get_id();

				// User
				$user_login = $cart_id;
				// Check if user exists
				if ( $user_id = username_exists( $user_login ) ) {
					// $user_id already set
				} else {
					$email = $cart_id . '-guestpurchase@' . str_replace( 'http://', '', site_url('', 'http') );
					$password = wp_generate_password();
					// Account info so that the user
					$account_info = array();
					$account_info['gb_contact_first_name'] = self::__('Guest');
					$account_info['gb_contact_last_name'] = self::__('Purchase');
					// Create user
					$user_id = Group_Buying_Accounts_Registration::create_user( $user_login, $email, $password, $account_info );
					update_user_meta( $user_id, self::GUEST_PURCHASE_USER_FLAG, 1 );
				}

				$user = get_user_by( 'id', $user_id );
				if ( $user_id ) {
					$user = wp_signon(
						array(
							'user_login' => $user_login,
							'user_password' => $password,
							'remember' => false
						), false );
					wp_set_current_user( $user->ID );
				}
			}
			else { // Registration
				Group_Buying_Accounts_Registration::init_process_form_submission(); // instantiating should process the form
				$user = wp_get_current_user();
				if ( $user && $user->ID ) {
					self::set_message( self::__( 'Registration complete. Please continue with your purchase.' ) );
				}
			}
		}
		if ( $user && $user->ID ) {
			$cart = &$checkout->get_cart();
			$cart_id = $cart->get_id();
			Group_Buying_Cart::claim_anonymous_cart( $cart_id, $user->ID );
		}

		// Don't validate billing fields
		add_filter( 'gb_valid_process_payment_page_fields', '__return_false');
		// mark checkout incomplete
		add_filter( 'gb_valid_process_payment_page', '__return_false');
	}

	/**
	 * After checkout is complete check whether the purchase was made by a temp account and
	 * update that WP User with the Purchase ID, log the user out and change the guest purchase flag
	 * @param  Group_Buying_Checkouts $checkout
	 * @param  Group_Buying_Payment   $payment
	 * @param  Group_Buying_Purchase  $purchase
	 * @return
	 */
	public static function checkout_complete( Group_Buying_Checkouts $checkout, Group_Buying_Payment $payment, Group_Buying_Purchase $purchase ) {
		$user_id = get_current_user_id();
		// $account_id = Group_Buying_Account::get_account_id_for_user( $user_id );
		if ( get_user_meta( $user_id, self::GUEST_PURCHASE_USER_FLAG, TRUE ) == 1 ) { // If the user is flagged as a guest user

			global $wpdb;
			$purchase_id = $purchase->get_id();
			$wpdb->query( $wpdb->prepare(  "UPDATE $wpdb->users SET user_login = %s WHERE ID = %s", $purchase_id, $user_id ) ); // Not sure why the $wpdb->udpate method is undefined at this point.

			// Logout
	 		wp_logout();
	 		// Set the flag to the purchase ID so we can clean up the temp users that never purchase via a cron
	 		update_user_meta( $user_id, self::GUEST_PURCHASE_USER_FLAG, $purchase_id );
		}
	}

	/**
	 * Delete temporary users without a purchase
	 * @return
	 */
	public static function delete_temp_users() {
		return; // TODO wp_delete_user isn't available?
		
		// get users with self::GUEST_PURCHASE_USER_FLAG set to 1.
		// any guest user not set to 1 would have purchased an item.
		// check registration date and delete if older than x days.
		$users = get_users( array(
				'meta_key' => self::GUEST_PURCHASE_USER_FLAG,
				'meta_value' => 1,
				'fields' => 'all_with_meta'
				) );
		foreach ( $users as $user_id => $user ) {
			$user_registered = strtotime( $user->data->user_registered );
			if ( $user_registered < ( time()-60*60*24*30 ) ) {
				do_action( 'gb_log', 'Deleted Guest User without Follow-up Purchase', $user );
				wp_delete_user( $user_id );
			}
		}
	}

	public function validate_payment_page( $valid, $checkout ) {
		$user = wp_get_current_user();
		if ( !$user || !$user->ID ) {
			return FALSE;
		}
		return $valid;
	}

	/**
	 * Claim the anonymous cart after login.
	 *
	 * @param object  $user
	 * @return void
	 */
	public static function claim_cart( $user ) {
		$cart = Group_Buying_Cart::get_anonymous_cart_id();
		if ( $cart ) {
			Group_Buying_Cart::claim_anonymous_cart( $cart, $user->ID, TRUE );
		}
	}
}