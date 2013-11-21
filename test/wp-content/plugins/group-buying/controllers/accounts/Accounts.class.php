<?php

/**
 * Accounts Controller: Registration, Login, Edit, Password Recovery, etc.
 *
 * @package GBS
 * @subpackage Account
 */
class Group_Buying_Accounts extends Group_Buying_Controller {
	const ACCOUNT_PATH_OPTION = 'gb_account_path';
	const ACCOUNT_QUERY_VAR = 'gb_view_account';
	const CREDIT_TYPE = 'balance';
	private static $account_path = 'account';
	public static $record_type = 'credit_history';
	private static $balance_payment_processor;
	private static $instance;

	/**
	 * Init
	 *
	 * @return void
	 */
	public static function init() {
		self::$account_path = get_option( self::ACCOUNT_PATH_OPTION, self::$account_path );
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 50, 0 );
		add_action( 'gb_router_generate_routes', array( get_class(), 'register_callback' ), 10, 1 );

		// Checkout actions
		add_action( 'completing_checkout', array( get_class(), 'save_contact_info_from_checkout' ), 10, 1 );

		// Payment type
		add_filter( 'gb_account_credit_types', array( get_class(), 'register_credit_type' ), 10, 1 );
		// This shouldn't ever be instantiated through the normal process. We want to add it on.
		self::$balance_payment_processor = Group_Buying_Account_Balance_Payments::get_instance();

		// Admin meta
		add_action( 'admin_head', array( get_class(), 'admin_style' ) );
		add_action( 'add_meta_boxes', array( get_class(), 'add_meta_boxes' ) );
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );

		// Admin Mngt.
		self::$settings_page = self::register_settings_page( 'account_records', self::__( 'Accounts' ), self::__( 'Accounts' ), 9, FALSE, 'records', array( get_class(), 'display_table' ) );

		// User Admin columns
		add_filter ( 'manage_users_columns', array( get_class(), 'user_register_columns' ) );
		add_filter ( 'manage_users_custom_column', array( get_class(), 'user_column_display' ), 10, 3 );

		// Profile Views
		add_action( 'show_user_profile', array( get_class(), 'account_info' ) );
		add_action( 'edit_user_profile', array( get_class(), 'account_info' ) );
		
		// AJAX Actions
		add_action( 'wp_ajax_nopriv_gbs_ajax_get_account',  array( get_class(), 'ajax_get_account' ), 10, 0 );
		add_action( 'wp_ajax_gbs_ajax_get_account',  array( get_class(), 'ajax_get_account' ), 10, 0 );

		// Misc.
		add_filter( 'gb_admin_bar', array( get_class(), 'add_link_to_admin_bar' ), 10, 1 );
		add_action( 'parse_request', array( get_class(), 'forced_messaging' ) );
	}

	/**
	 * Register the path callback for the merchant registration
	 *
	 * @static
	 * @param GB_Router $router
	 * @return void
	 */
	public static function register_callback( GB_Router $router ) {
		$args = array(
			'path' => self::$account_path,
			'title' => 'Your Account',
			'title_callback' => array( get_class(), 'get_title' ),
			'page_callback' => array( get_class(), 'on_account_page' ),
			'template' => array(
				self::get_template_path().'/'.str_replace( '/', '-', self::$account_path ).'-info.php', // non-default cart path
				self::get_template_path().'/account.php', // theme override
				GB_PATH.'/views/public/account.php', // default
			),
		);
		$router->add_route( self::ACCOUNT_QUERY_VAR, $args );
	}

	public static function register_credit_type( $credit_types = array() ) {
		$credit_types[self::CREDIT_TYPE] = self::__( 'Account Balance' );
		return $credit_types;
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_url_paths';
		add_settings_section( $section, self::__( 'Custom URL Paths' ), array( get_class(), 'display_account_paths_section' ), $page );

		// Settings
		register_setting( $page, self::ACCOUNT_PATH_OPTION );
		add_settings_field( self::ACCOUNT_PATH_OPTION, self::__( 'Account Path' ), array( get_class(), 'display_account_path' ), $page, $section );
	}

	public static function display_account_paths_section() {
		Group_Buying_Controller::flush_rewrite_rules();
		echo self::__( '<h4>Customize the Account paths</h4>' );
	}

	public static function display_account_path() {
		echo trailingslashit( get_home_url() ) . ' <input type="text" name="'.self::ACCOUNT_PATH_OPTION.'" id="'.self::ACCOUNT_PATH_OPTION.'" value="' . esc_attr( self::$account_path ) . '"  size="40" /><br />';
	}

	public static function admin_style() {
		global $post;
		if ( $post && $post->post_type == Group_Buying_Account::POST_TYPE ) {
		?>
			<style type="text/css">
				#minor-publishing-actions, #misc-publishing-actions, #delete-action { display:none; }
			</style>
		<?php
		}
	}

	public static function add_meta_boxes() {
		add_meta_box( 'gb_account_contact_info', self::__( 'Contact Info' ), array( get_class(), 'show_meta_box' ), Group_Buying_Account::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_account_purchases', self::__( 'Purchases' ), array( get_class(), 'show_meta_box' ), Group_Buying_Account::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_account_credits', self::__( 'Credits' ), array( get_class(), 'show_meta_box' ), Group_Buying_Account::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_account_user', self::__( 'User Profile' ), array( get_class(), 'show_meta_box' ), Group_Buying_Account::POST_TYPE, 'side', 'high' );
	}

	public static function show_meta_box( $post, $metabox ) {
		$account = Group_Buying_Account::get_instance_by_id( $post->ID );
		switch ( $metabox['id'] ) {
		case 'gb_account_credits':
			self::show_meta_box_gb_account_credits( $account, $post, $metabox );
			break;
		case 'gb_account_contact_info':
			self::show_meta_box_gb_account_contact_info( $account, $post, $metabox );
			break;
		case 'gb_account_purchases':
			self::show_meta_box_gb_account_purchases( $account, $post, $metabox );
			break;
		case 'gb_account_user':
			self::show_meta_box_gb_account_user( $account, $post, $metabox );
			break;
		default:
			self::unknown_meta_box( $metabox['id'] );
			break;
		}
	}

	public static function save_meta_boxes( $post_id, $post ) {
		// only continue if it's an account post
		if ( $post->post_type != Group_Buying_Account::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		// save all the meta boxes
		$account = Group_Buying_Account::get_instance_by_id( $post_id );
		if ( !is_a( $account, 'Group_Buying_Account' ) ) {
			return; // The account doesn't exist
		}
		self::save_meta_box_gb_account_contact_info( $account, $post_id, $post );
		self::save_meta_box_gb_account_credits( $account, $post_id, $post );
	}

	private static function show_meta_box_gb_account_contact_info( Group_Buying_Account $account, $post, $metabox ) {
		$address = $account->get_address();
		self::load_view( 'meta_boxes/account-contact-info', array(
				'first_name' => $account->get_name( 'first' ),
				'last_name' => $account->get_name( 'last' ),
				'street' => isset( $address['street'] )?$address['street']:'',
				'city' => isset( $address['city'] )?$address['city']:'',
				'zone' => isset( $address['zone'] )?$address['zone']:'',
				'postal_code' => isset( $address['postal_code'] )?$address['postal_code']:'',
				'country' => isset( $address['country'] )?$address['country']:'',
			), FALSE );
	}

	private static function save_meta_box_gb_account_contact_info( Group_Buying_Account $account, $post_id, $post ) {
		$first_name = isset( $_POST['account_first_name'] ) ? $_POST['account_first_name'] : '';
		$account->set_name( 'first', $first_name );
		$last_name = isset( $_POST['account_last_name'] ) ? $_POST['account_last_name'] : '';
		$account->set_name( 'last', $last_name );
		$address = array(
			'street' => isset( $_POST['account_street'] ) ? $_POST['account_street'] : '',
			'city' => isset( $_POST['account_city'] ) ? $_POST['account_city'] : '',
			'zone' => isset( $_POST['account_zone'] ) ? $_POST['account_zone'] : '',
			'postal_code' => isset( $_POST['account_postal_code'] ) ? $_POST['account_postal_code'] : '',
			'country' => isset( $_POST['account_country'] ) ? $_POST['account_country'] : '',
		);
		$account->set_address( $address );
	}

	private static function show_meta_box_gb_account_credits( Group_Buying_Account $account, $post, $metabox ) {
		$types = self::account_credit_types();
		$credit_fields = array();
		foreach ( $types as $key => $label ) {
			$credit_fields[$key] = array(
				'balance' => $account->get_credit_balance( $key ),
				'label' => $label,
			);
		}
		$credit_types = apply_filters( 'gb_account_meta_box_credit_types', $credit_fields, $account );
		self::load_view( 'meta_boxes/account-credits', array(
				'account' => $account,
				'credit_types' => $credit_fields
			), FALSE );
	}

	private static function account_credit_types() {
		return apply_filters( 'gb_account_credit_types', array() );
	}

	private static function save_meta_box_gb_account_credits( Group_Buying_Account $account, $post_id, $post ) {
		if ( isset( $_POST['account_credit_balance'] ) && is_array( $_POST['account_credit_balance'] ) ) {
			$types = array_keys( self::account_credit_types() );
			foreach ( $_POST['account_credit_balance'] as $key => $value ) {
				if ( in_array( $key, $types ) && is_numeric( $value ) ) {
					$balance = $account->get_credit_balance( $key );
					switch ( $_POST['account_credit_action'][$key] ) {
					case 'add':
						$total = $balance+$value;
						break;
					case 'deduct':
						$total = $balance-$value;
						break;
					case 'change':
						$total = $value;
						break;
					}
					$account->set_credit_balance( $total, $key );
					$data = array();
					$data['note'] = $_POST['account_credit_notes'][$key];
					$data['adjustment_value'] = $value;
					$data['current_total'] = $total;
					$data['prior_total'] = $balance;
					do_action( 'gb_new_record', $data, Group_Buying_Accounts::$record_type . '_' . $key, gb__( 'Credit Adjustment' ), get_current_user_id(), $account->get_ID() );
					do_action( 'gb_save_meta_box_gb_account_credits', $account, $post_id, $_POST );
				}
			}
		}
	}

	private static function show_meta_box_gb_account_purchases( Group_Buying_Account $account, $post, $metabox ) {
		do_action( 'gb_account_purchases_meta_box_top', $account, $post );
		self::load_view( 'meta_boxes/account-purchases', array( 'account'=>$account ), TRUE );
		do_action( 'gb_account_purchases_meta_box_bottom', $account, $post );
	}

	private static function show_meta_box_gb_account_user( Group_Buying_Account $account, $post, $metabox ) {
		do_action( 'gb_account_user_meta_box_top', $account, $post );
		self::load_view( 'meta_boxes/account-user', array( 'account' => $account ), TRUE );
		do_action( 'gb_account_user_meta_box_bottom', $account, $post );
	}

	/**
	 * If a user's contact info isn't saved, try to get if from their billing
	 * information when checkout is finished.
	 *
	 * @static
	 * @param Group_Buying_Checkouts $checkout
	 * @return void
	 */
	public static function save_contact_info_from_checkout( Group_Buying_Checkouts $checkout ) {
		$account = Group_Buying_Account::get_instance();
		$address = $account->get_address();
		$new_address = $address;
		$first_name = $account->get_name( 'first' );
		$last_name = $account->get_name( 'last' );
		if ( !$first_name && isset( $checkout->cache['billing']['first_name'] ) && $checkout->cache['billing']['first_name'] ) {
			$account->set_name( 'first', $checkout->cache['billing']['first_name'] );
		}
		if ( !$last_name && isset( $checkout->cache['billing']['last_name'] ) && $checkout->cache['billing']['last_name'] ) {
			$account->set_name( 'last', $checkout->cache['billing']['last_name'] );
		}
		foreach ( array( 'street', 'city', 'zone', 'postal_code', 'country' ) as $key ) {
			if ( ( !isset( $address[$key] ) || !$address[$key] ) && isset( $checkout->cache['billing'][$key] ) && $checkout->cache['billing'][$key] ) {
				$new_address[$key] = $checkout->cache['billing'][$key];
			}
		}
		if ( $address != $new_address ) {
			$account->set_address( $new_address );
		}
	}

	//////////////////
	// Admin Mngt. //
	//////////////////

	public static function user_register_columns( $columns ) {
		// create a new array with account just after username, then everything else
		$new_columns = array();
		if ( isset($columns['username']) ) {
			$new_columns['username'] = $columns['username'];
			unset($columns['username']);
		}
		$new_columns['account'] = self::__( 'Account' );
		$new_columns = array_merge($new_columns, $columns);
		return $new_columns;
	}

	public static function user_column_display( $empty='', $column_name, $id ) {
		$account = Group_Buying_Account::get_instance( $id );

		if ( !$account )
			return; // return for that temp post

		switch ( $column_name ) {
		case 'account':
			$account_id = $account->get_ID();
			$user_id = $account->get_user_id_for_account( $account_id );
			$user = get_userdata( $user_id );
			$get_name = $account->get_name();
			$name = ( strlen( $get_name ) <= 1  ) ? '' : $get_name;

			//Build row actions
			$actions = array(
				'edit'    => sprintf( '<a href="post.php?post=%s&action=edit">'.self::__( 'Manage' ).'</a>', $account_id ),
				'payments'    => sprintf( '<a href="admin.php?page=group-buying/payment_records&account_id=%s">'.self::__( 'Payments' ).'</a>', $account_id ),
				'purchases'    => sprintf( '<a href="admin.php?page=group-buying/purchase_records&account_id=%s">'.self::__( 'Orders').'</a>', $account_id ),
				'vouchers'    => sprintf( '<a href="admin.php?page=group-buying/voucher_records&account_id=%s">'.self::__( 'Vouchers').'</a>', $account_id ),
				'gifts'    => sprintf( '<a href="admin.php?page=group-buying/gift_records&account_id=%s">'.self::__( 'Gifts').'</a>', $account_id )
			);

			//Return the title contents
			return sprintf( self::__( '%1$s <span style="color:silver">(account&nbsp;id:%2$s)</span> <span style="color:silver">(user&nbsp;id:%3$s)</span>%4$s' ),
				$name,
				$account_id,
				$user_id,
				WP_List_Table::row_actions( $actions )
			);
			break;

		default:
			// code...
			break;
		}
	}

	public function account_info( $profileuser ) {
		$user_id = $profileuser->ID;
		$account = Group_Buying_Account::get_instance( $user_id );
		$account_id = $account->get_id();
		?>
			<h3><?php self::_e('GBS Account Profile') ?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php self::_e('Contact Information') ?></th>
						<td>
							<p><?php echo $account->get_name(); ?><br />
							<?php echo gb_format_address( $account->get_address(), 'string', '<br />' ); ?></p>
							<?php printf( '<a href="post.php?post=%s&action=edit" class="button">%s</a>', $account_id, self::__( 'Manage' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php self::_e('Activity') ?></th>
						<td>
							<p>
								<?php
									printf( '<a href="admin.php?page=group-buying/payment_records&account_id=%s" class="button">%s</a>', $account_id, self::__( 'Payments' ) ); ?>&nbsp;&nbsp;
								<?php
									printf( '<a href="admin.php?page=group-buying/purchase_records&account_id=%s" class="button">%s</a>', $account_id, self::__( 'Orders') ); ?>&nbsp;&nbsp;
								<?php
									printf( '<a href="admin.php?page=group-buying/voucher_records&account_id=%s" class="button">%s</a>', $account_id, self::__( 'Vouchers') ); ?>&nbsp;&nbsp;
								<?php
									printf( '<a href="admin.php?page=group-buying/gift_records&account_id=%s" class="button">%s</a>', $account_id, self::__( 'Gifts') ); ?>
							</p>
						<span class="description"><?php self::_e('') ?></span></td>
					</tr>
				</tbody>
			</table>
		<?php

		
	}

	public static function ajax_get_account() {
		$id = $_POST['id'];
		if ( !$id ) {
			return;
		}
		$user = get_userdata( $id );
		if ( is_a( $user, 'WP_User' ) ) { // Check if id is a WP User
			$account = Group_Buying_Account::get_instance( $id );
		} else {
			$account = Group_Buying_Account::get_instance_by_id( $id );
		}
		if ( is_a( $account, 'Group_Buying_Account' ) ) {

			header( 'Content-Type: application/json' );
			$response = array(
				'account_id' => $account->get_ID(),
				'user_id' => $account->get_user_id(),
				'name' => gb_get_name( $account->get_user_id() ),
				'rewards' => $account->get_credit_balance( Group_Buying_Affiliates::CREDIT_TYPE ),
				'credits' => $account->get_credit_balance( Group_Buying_Accounts::CREDIT_TYPE ),
				'merchant_id' => gb_account_merchant_id( $account->get_user_id() ),
				'address' => gb_format_address( $account->get_address() )
			);
			echo json_encode( $response );
		}
		exit();
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
		self::do_not_cache(); // never cache the account pages
	}


	//////////////
	// Routing //
	//////////////

	public static function on_account_page() {
		self::login_required();
		$account_page = self::get_instance();
		// View template
		$account_page->view_profile();

	}

	/**
	 * Update the global $pages array with the HTML for the page.
	 *
	 * @param object  $post
	 * @return void
	 */
	public function view_profile() {
		remove_filter( 'the_content', 'wpautop' );
		$account = Group_Buying_Account::get_instance();
		$panes = apply_filters( 'gb_account_view_panes', array(), $account );
		uasort( $panes, array( get_class(), 'sort_by_weight' ) );
		self::load_view( 'account/view', array(
				'panes' => $panes,
			) );
	}

	/**
	 *
	 *
	 * @static
	 * @return bool Whether the current query is a cart page
	 */
	public static function is_account_page() {
		return GB_Router_Utility::is_on_page( self::ACCOUNT_QUERY_VAR );
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
		return self::__( "Your Account" );
	}

	/**
	 *
	 *
	 * @static
	 * @return string The URL to the merchant page
	 */
	public static function get_url() {
		if ( self::using_permalinks() ) {
			return trailingslashit( home_url() ).trailingslashit( self::$account_path );
		} else {
			$router = GB_Router::get_instance();
			return $router->get_url( self::ACCOUNT_QUERY_VAR );
		}
	}

	/**
	 * Filter the array of panes for the account view page
	 *
	 * @param array   $panes
	 * @param Group_Buying_Account $account
	 * @return array
	 */
	public function get_panes( array $panes, Group_Buying_Account $account ) {
		$panes['contact'] = array(
			'weight' => 0,
			'body' => self::load_view_to_string( 'account/contact-info', array(
					'first_name' => $account->get_name( 'first' ),
					'last_name' => $account->get_name( 'last' ),
					'name' => $account->get_name(),
					'address' => $account->get_address(),
				) ),
		);
		return $panes;
	}

	////////////
	// Misc. //
	////////////

	public static function add_link_to_admin_bar( $items ) {
		$items[] = array(
			'id' => 'edit_accounts',
			'title' => self::__( 'Edit Accounts' ),
			'href' => gb_admin_url( 'account_records' ),
			'weight' => 5,
		);
		return $items;
	}

	public function forced_messaging() {
		$messages = array(
			'test_cookie' => self::__( "Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use this site." ),
			'loggedout' => self::__( 'You are now logged out.' ),
			'disabled' => self::__( 'Registration Disabled.' ),
			'registerdisabled' => self::__( 'User registration is currently not allowed.' ),
			'registered' => self::__( 'Registration complete. Please log in' ),
			'expired' => self::__( 'Your session has expired. Please log in again.' ),
			'confirm' => self::__( 'Check your e-mail for the password reset link.' ),
			'newpass' => self::__( 'Password reset, change your password to something more memorable now.' ),
			'incorrect' => self::__( 'Your username or email is incorrect' ),
			'notallowed' => self::__( 'Password reset is not allowed.' ),
			'blank' => self::__( 'Your username or email is incorrect' ),
			'invalidkey' => self::__( 'Invalid Password Reset Key.' ),
		);
		if ( isset( $_GET['message'] ) && isset( $messages[$_GET['message']] ) ) {
			self::set_message( $messages[$_GET['message']], self::MESSAGE_STATUS_INFO, FALSE );
		}
	}

	///////////////////////
	// Management Table //
	///////////////////////

	public static function display_table() {
		//Create an instance of our package class...
		$wp_list_table = new Group_Buying_Accounts_Table();
		//Fetch, prepare, sort, and filter our data...
		$wp_list_table->prepare_items();
?>
		<script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function($){
				jQuery(".gb_suspend").on('click', function(event) {
					event.preventDefault();
						if( confirm( '<?php gb_e("This will modify a users access to your site. Are you sure?") ?>' ) ) {
							var $suspend_link = $( this ),
							account_id = $suspend_link.attr( 'ref' );
							$.post( ajaxurl, { action: 'gbs_destroyer', type: 'account', id: account_id, destroyer_nonce: '<?php echo wp_create_nonce( Group_Buying_Destroy::NONCE ) ?>' },
								function( data ) {
										$suspend_link.parent().html( '<?php gb_e('Modified') ?>' );
									}
								);
						} else {
							// nothing to do.
						}
				});
			});
		</script>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php self::display_admin_tabs(); ?>
			</h2>

			 <?php $wp_list_table->views() ?>
			<form id="payments-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $wp_list_table->search_box( gb__( 'Account ID' ), 'account_id' ); ?>
				<?php $wp_list_table->display() ?>
			</form>
		</div>
		<?php
	}
}