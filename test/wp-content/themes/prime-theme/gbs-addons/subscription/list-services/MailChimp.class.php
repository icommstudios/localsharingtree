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

class Group_Buying_MailChimp extends Group_Buying_List_Services {
	const API_KEY = 'gb_mailchimp_api_key';
	const LIST_ID = 'gb_mailchimp_list_id';
	const GROUP_ID = 'gb_mailchimp_group_id';
	const FIELD_ID = 'gb_mailchimp_field_id';
	const SIGNUP_DOUBLEOPT_OPTION = 'gb_mailchimp_doubleopt';
	const SIGNUP_SENDWELCOME_OPTION = 'gb_mailchimp_sendwelcome';
	const LOCATION_PREF_OPTION = 'gb_location_prefs';
	protected static $instance;
	protected static $api;
	private static $api_key = '';
	private static $list_id = '';
	private static $group_id = '';
	private static $field_id = '';
	protected static $signup_doubleopt;
	protected static $signup_sendwelcome;
	protected static $listing_options = array();
	protected static $grouping_options = array();


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

		self::$api_key = get_option( 'gb_mailchimp_api_key', '' );
		self::$list_id = get_option( self::LIST_ID, '' );
		self::$group_id = get_option( self::GROUP_ID, '' );
		self::$field_id = get_option( self::FIELD_ID, '' );
		self::$signup_doubleopt = get_option( self::SIGNUP_DOUBLEOPT_OPTION, 'true' );
		self::$signup_sendwelcome = get_option( self::SIGNUP_SENDWELCOME_OPTION, 'true' );
		
		if ( is_admin() ) {
			add_action( 'init', array( get_class(), 'register_options') );
		}

		add_filter( 'gb_account_registration_panes', array( $this, 'get_registration_panes' ), 100 );
		if ( !version_compare( Group_Buying::GB_VERSION, '4.2', '>=' ) ) { // TODO remove deprecated method and functions
			add_filter( 'gb_account_edit_panes', array( $this, 'get_edit_panes' ), 0, 2 );
		} else {
			add_filter( 'gb_account_edit_account_notificaiton_fields', array( $this, 'account_notification_fields' ), 10, 2 );
		}
		add_action( 'gb_process_account_edit_form', array( $this, 'process_form' ) );
		add_filter( 'gb_account_view_panes', array( $this, 'get_panes' ), 0, 2 );

		// AJAX options
		if ( is_admin() ) {
			add_filter( 'admin_head', array( get_class(), 'head' ) );
		}
		add_action( 'wp_ajax_mc_ajax_callback', array( get_class(), 'return_mc_options' ) );

	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_options() {
		// Settings
		$settings = array(
			'mailchimp' => array(
				'title' => self::__( 'MailChimp API Configuration' ),
				'weight' => 500,
				'settings' => array(
					self::API_KEY => array(
						'label' => self::__( 'API Key' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$api_key
							)
						),
					self::LIST_ID => array(
						'label' => self::__( 'Mailing List' ),
						'option' => array(
							'type' => 'select',
							'options' => self::list_options(),
							'default' => self::$list_id,
							'description' => ( empty( self::$listing_options ) ) ? self::__('No lists were found using that API key.') : ''
							)
						),
					self::GROUP_ID => array(
						'label' => self::__( 'Location Group' ),
						'option' => array(
							'type' => 'select',
							'options' => self::group_options(),
							'default' => self::$group_id,
							'description' => ( empty( self::$grouping_options ) ) ? self::__('No groups were found under the list selected above.') : ''
							)
						),
					self::SIGNUP_DOUBLEOPT_OPTION => array(
						'label' => self::__( 'Double Opt-in' ),
						'option' => array(
							'label' => self::__( 'Explicit double opt-in.' ),
							'type' => 'checkbox',
							'value' => 'true',
							'default' => self::$signup_doubleopt
							)
						),
					self::SIGNUP_SENDWELCOME_OPTION => array(
						'label' => self::__( 'Welcome Message' ),
						'option' => array(
							'label' => self::__( 'Send welcome message after subscribing.' ),
							'type' => 'checkbox',
							'value' => 'true',
							'default' => self::$signup_sendwelcome
							)
						),
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_List_Services::SETTINGS_PAGE );
	}

	public static function list_options() {
		// See if already set.
		if ( self::$listing_options ) {
			return self::$listing_options;
		}
		$api = self::init_mc( self::$api_key );
		$listings = $api->lists();

		// Build options
		$options = array();
		if ( !empty( $listings ) ) {
			if ( !empty( $listings ) || self::$listings['total'] == '0' ) {
				foreach ( $listings['data'] as $key ) {
					$options[$key['id']] = $key['name'];
				}
			}
		}
		self::$listing_options = $options;
		return $options;
	}

	public static function group_options() {
		// Must have a list_id
		if ( !self::$list_id ) {
			return array();
		}
		// See if already set.
		if ( self::$grouping_options ) {
			return self::$grouping_options;
		}

		$api = self::init_mc( self::$api_key );
		$groupings = $api->listInterestGroupings( self::$list_id);

		// Build options
		$options = array();
		if ( !empty( $groupings ) ) {			
			foreach ( $groupings as $key ) {
				$options[$key['id']] = $key['name'];
			}
		}
		self::$grouping_options = $options;
		return $options;
	}

	public static function head() {
		if ( !isset( $_GET['page'] ) || $_GET['page'] != 'group-buying/subscription' ) {
			return;
		} ?>
		<script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function($){

				var list_ajax_gif = '<span id="<?php echo self::LIST_ID ?>">'+gb_ajax_gif+'</span>';
				var group_ajax_gif = '<span id="<?php echo self::GROUP_ID ?>">'+gb_ajax_gif+'</span>';

				// After an API key is entered
				jQuery("#<?php echo self::API_KEY ?>").live('keyup', function() {
					// Var
					var api_key = $(this).val();

					// hide and show the ajax loader
					$("#<?php echo self::LIST_ID ?>").replaceWith(list_ajax_gif);
					$("#<?php echo self::GROUP_ID ?>").hide();

					// Get new select list
					$.post( gb_ajax_url, { action: 'mc_ajax_callback', mail_chimp_get_lists: api_key },
						function( data ) {
							if ( data ) {
								$("#<?php echo self::LIST_ID ?>").replaceWith(data);
								$("#<?php echo self::GROUP_ID ?>").replaceWith(group_ajax_gif);
							};
						}
					);
				});

				// After the list is changed
				jQuery("select#<?php echo self::LIST_ID ?>").live('change', function() {
					// Var
					var list = $(this).val();

					// show the ajax loader
					$("#<?php echo self::GROUP_ID ?>").replaceWith(group_ajax_gif);

					// Get and replace with the groups select list
					$.post( post_url, { action: 'mc_ajax_callback', mail_chimp_get_groups: list },
						function( data ) {
							if ( data ) {
								$("#<?php echo self::GROUP_ID ?>").replaceWith(data);
							};
						}
					);
				});
			});
		</script>
		<?php
	}

	public static function return_mc_options() {

		if ( !current_user_can( 'edit_posts' ) ) {
			return; // security check
		}
		if ( isset( $_REQUEST['mail_chimp_get_lists'] ) && $_REQUEST['mail_chimp_get_lists'] != '' ) {
			update_option( self::API_KEY, $_REQUEST['mail_chimp_get_lists'] );
			self::display_list_id_field( null, $_REQUEST['mail_chimp_get_lists'] );
			exit();
		} elseif ( isset( $_REQUEST['mail_chimp_get_groups'] ) && $_REQUEST['mail_chimp_get_groups'] != '' ) {
			update_option( self::LIST_ID, $_REQUEST['mail_chimp_get_groups'] );
			self::display_group_id_field( null, $_REQUEST['mail_chimp_get_groups'] );
			exit();
		} elseif ( isset( $_REQUEST['mail_chimp_get_lists'] ) || isset( $_REQUEST['mail_chimp_get_groups'] ) ) {
			exit();
		}

	}

	public static function register() {
		do_action( 'gb_register_mailchimp' );
		self::add_list_service( __CLASS__, self::__( 'MailChimp' ) );
	}

	public static function init_mc( $api_key = NULL ) {
		require_once 'utilities/MCAPI.class.php';
		if ( NULL === $api_key ) {
			$api_key = self::$api_key;
		}
		self::$api = new GB_MCAPI( $api_key );
		self::$api->setTimeout( 5 );
		return self::$api;
	}

	public function process_subscription( $email = null, $location = null ) {

		$retval = self::subscribe( $_POST['email_address'], $_POST['deal_location'] );

		if ( self::$api->errorCode == '79' ) {
			parent::success( $_POST['deal_location'], $_POST['email_address'] );
		}
		if ( self::$api->errorMessage ) {
			SEC_Controller::set_message( apply_filters( 'subscribe_mc_error', self::$api->errorMessage ), 'error' );
		}
		// if it's a success, set a cookie and redirect
		if ( !self::$api->errorCode || self::$api->errorCode == '214' ) {
			if ( $_POST['deal_location'] != null ) {
				parent::success( $_POST['deal_location'], $_POST['email_address'] );
			} else {
				parent::success( 0, $_POST['email_address'] );
			}
		}
	}

	public function process_registration_subscription( $user = null, $user_login = null, $user_email = null, $password = null, $post = null ) {

		if ( isset( $post[self::LOCATION_PREF_OPTION] ) ) {
			// Set the location options
			$account = Group_Buying_Account::get_instance( $user->ID );
			add_post_meta( $account->get_ID(), '_'.self::LOCATION_PREF_OPTION, $post[self::LOCATION_PREF_OPTION] );
			self::subscribe( $user_email, $post[self::LOCATION_PREF_OPTION] );
		}
	}

	public static function subscribe( $email = null, $locations = null, $account = null ) {

		if ( null == $email ) {
			if ( isset( $_POST['email_address'] ) ) {
				$email = $_POST['email_address'];
			} else {
				$current_user = wp_get_current_user();
				$email = $current_user->user_email;
			}

		}
		if ( null == $locations && isset( $_POST['deal_location'] ) ) {
			$locations = $_POST['deal_location'];
		}
		if ( null == $account || !is_a( $account, 'Group_Buying_Account' ) ) {
			$user = get_user_by( 'email', $email );
			if ( is_a( $user, 'WP_User' ) ) {
				$account = Group_Buying_Account::get_instance( $user->ID );
			}
		}

		self::init_mc();

		if ( is_array( $locations ) ) {
			$groups = implode( ",", $locations );
			foreach ( $locations as $location ) {
				// Add the location just in case it's not there already.
				$response = self::$api->listInterestGroupAdd( self::$list_id, $location, self::$group_id );
				//logs
			}
		} else {
			$groups = $locations;
			// Add the location just in case it's not there already.
			$response = self::$api->listInterestGroupAdd( self::$list_id, $locations, self::$group_id );
		}

		// default merge variables
		$merge_vars = array(
			'GROUPINGS' => array(
				array( 'id' => self::$group_id, 'groups' => $groups ),

			),
			//'MC_LOCATION'=>array('LATITUDE'=>34.0413, 'LONGITUDE'=>-84.3473),
		);
		if ( $account ) {
			$merge_vars['FNAME'] = $account->get_name( 'first' );
			$merge_vars['LNAME'] = $account->get_name( 'last' );
		}
		$merge_vars = apply_filters( 'subscribe_mc_groupins', $merge_vars, self::$group_id );
		//logs
		do_action( 'gb_log', 'subscribe - merge_vars', $merge_vars );

		$welcome = ( self::$signup_sendwelcome ) ? 'true' : 'false' ;
		$doubleopt = ( self::$signup_doubleopt ) ? 'true' : 'false' ;
		// subscribe the email already.
		$retval = self::$api->listSubscribe(
			self::$list_id,
			$email,
			$merge_vars,
			$email_type = 'html',
			$doubleopt,
			$update_existing = TRUE,
			$replace_interests = FALSE,
			$welcome  // If double_optin is true, this has no effect.
		);

		//logs
		do_action( 'gb_log', 'subscribe - retval: ', $retval );
		do_action( 'gb_log', 'subscribe - error code: ', self::$api->errorCode );
		do_action( 'gb_log', 'subscribe - error: ', self::$api->errorMessage );
		return $response;
	}


	/**
	 * Add the default pane to the account edit form
	 *
	 * @param array   $panes
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	public function get_registration_panes( array $panes ) {
		if ( parent::$registration_option !== 'false' ) {
			unset( $panes['subscription'] );
			$preference = null;
			if ( parent::$registration_option == 'checked' ) {
				$preference = ( isset( $_COOKIE[ 'gb_location_preference' ] ) ) ? $_COOKIE[ 'gb_location_preference' ] : '' ;
			}
			$panes['mc_subs'] = array(
				'weight' => 99,
				'body' => self::load_view_string( 'account-prefs', array( 'name' => self::LOCATION_PREF_OPTION, 'options' => (array)$preference, 'optin' => parent::$registration_option ) ),
			);
		}
		return $panes;
	}

	/**
	 * Add the default pane to the account overview form
	 *
	 * @param array   $panes
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	public function get_panes( array $panes, Group_Buying_Account $account ) {
		$options = get_post_meta( $account->get_ID(), '_'.self::LOCATION_PREF_OPTION );
		$panes['mc_subs'] = array(
			'weight' => 500,
			'body' => self::load_view_string( 'account-subscriptions', array( 'name' => self::LOCATION_PREF_OPTION, 'options' => (array)$options[0] ) ),
		);
		return $panes;
	}

	/**
	 * Add the default pane to the account edit form
	 * @deprecated Deprecated in version 2.2 in favor of account_notification_fields
	 * @param array   $panes
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	public function get_edit_panes( array $panes, Group_Buying_Account $account ) {
		$options = get_post_meta( $account->get_ID(), '_'.self::LOCATION_PREF_OPTION );
		$panes['mc_subs'] = array(
			'weight' => 10,
			'body' => self::load_view_string( 'account-prefs', array( 'name' => self::LOCATION_PREF_OPTION, 'options' => $options[0] ) ),
		);
		return $panes;
	}



	/**
	 * Add the daily email preferences to the notification section already within the account edit.
	 *
	 * @param array   $fields
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	public function account_notification_fields( $fields, Group_Buying_Account $account ) {
		$options = get_post_meta( $account->get_ID(), '_'.self::LOCATION_PREF_OPTION, TRUE );

		$view = '';
		foreach ( gb_get_locations( FALSE ) as $location ) {
			$checked = ( in_array( $location->slug, (array)$options ) ) ? 'checked="checked"' : '' ;
			$view .= '<span class="location_pref_input_wrap"><label><input type="checkbox" name="'.self::LOCATION_PREF_OPTION.'[]" value="'.$location->slug.'" '.$checked.'>'.$location->name.'</label></span>';
		}

		$mc_fields = array(
			'mc_subscription' => array(
				'weight' => 10,
				'label' => self::__( 'Subscriptions' ),
				'type' => 'bypass',
				'required' => FALSE,
				'output' => $view
			)
		);
		$fields = array_merge( $fields, $mc_fields );
		uasort( $fields, array( get_class(), 'sort_by_weight' ) );
		return $fields;
	}

	/**
	 * Process the form submission and save the meta
	 *
	 * @param string
	 * @return string
	 * @author Dan Cameron
	 */
	public static function process_form( Group_Buying_Account $account ) {
		$locations = isset( $_POST[self::LOCATION_PREF_OPTION] ) ? $_POST[self::LOCATION_PREF_OPTION] : null;
		if ( !empty( $locations ) ) {
			$user = $account->get_user();
			$retval = self::subscribe( $user->user_email, $locations, $account );
			delete_post_meta( $account->get_ID(), '_'.self::LOCATION_PREF_OPTION );
			add_post_meta( $account->get_ID(), '_'.self::LOCATION_PREF_OPTION, $locations );
		}

	}

	private static function load_view_string( $path, $args ) {
		ob_start();
		if ( !empty( $args ) ) extract( $args );
		$template = locate_template( SEC_ADDONS_DIR . '/subscription/list-services/mc-views/'.$path.'.php', FALSE );
		include $template;
		return ob_get_clean();
	}

	public static function sync_mailchimp_locations() {
		$locations = get_terms( gb_get_location_tax_slug(), array( 'fields'=>'all', 'hide_empty' => 0 ) );
		foreach ( $locations as $location ) {
			self::$api->listInterestGroupAdd( self::$list_id, $location->slug, self::$group_id );
		}
	}
}
Group_Buying_MailChimp::register();