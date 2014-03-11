<?php

class GB_SF_Charity extends Group_Buying_Post_Type {
	const POST_TYPE = 'gb_charities';
	const REWRITE_SLUG = 'charities';
	
	const CHARITY_TYPE_TAXONOMY = 'gb_charity_type';
	const CHARITY_TYPE_TAX_SLUG = 'charity-type';

	private static $instances = array();

	private static $meta_keys = array(
		'payment_notes' => '_payment_notes', // string
		'authorized_users' => '_authorized_users', // array
		//'username' => '_bluepay_username', // string
		//'password' => '_bluepay_password', // string
		'percentage' => '_percentage', // string
	);

	public static function init() {
		// Register
		self::register_charity_post_type();
		
		// register Charity Type taxonomy
		$singular = 'Charity Type';
		$plural = 'Charity Types';
		$taxonomy_args = array(
			'rewrite' => array(
				'slug' => self::CHARITY_TYPE_TAX_SLUG,
				'with_front' => FALSE,
				'hierarchical' => TRUE,
			),
		);
		self::register_taxonomy( self::CHARITY_TYPE_TAXONOMY, array( self::POST_TYPE ), $singular, $plural, $taxonomy_args );

	}

	public static function register_charity_post_type() {
		$post_type_args = array(
			'has_archive' => TRUE,
			'rewrite' => array(
				'slug' => self::REWRITE_SLUG,
				'with_front' => FALSE,
			),
			'supports' => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon' => GB_SF_CHARITY_URL."/assets/charity-icon.png"
		);
		self::register_post_type( self::POST_TYPE, 'Charity', 'Charities', $post_type_args );
	}


	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 *
	 *
	 * @static
	 * @param int     $id
	 * @return Group_Buying_Merchant
	 */
	public static function get_instance( $id = 0 ) {
		if ( !$id ) {
			return NULL;
		}
		if ( !isset( self::$instances[$id] ) || !self::$instances[$id] instanceof self ) {
			self::$instances[$id] = new self( $id );
		}
		if ( self::$instances[$id]->post->post_type != self::POST_TYPE ) {
			return NULL;
		}
		return self::$instances[$id];
	}

	/**
	 *
	 * @static
	 * @return bool Whether the current query is for the charity post type
	 */
	public static function is_charity_query() {
		$post_type = get_query_var( 'post_type' );
		if ( $post_type == self::POST_TYPE ) {
			return TRUE;
		}
		return FALSE;
	}

	public function get_payment_notes() {
		$payment_notes = $this->get_post_meta( self::$meta_keys['payment_notes'] );
		return $payment_notes;
	}

	public function set_payment_notes( $payment_notes ) {
		$this->save_post_meta( array(
				self::$meta_keys['payment_notes'] => $payment_notes
			) );
		return $payment_notes;
	}

	public function get_username() {
		$username = $this->get_post_meta( self::$meta_keys['username'] );
		return $username;
	}

	public function set_username( $username ) {
		$this->save_post_meta( array(
				self::$meta_keys['username'] => $username
			) );
		return $username;
	}

	public function get_password() {
		$password = $this->get_post_meta( self::$meta_keys['password'] );
		return $password;
	}

	public function set_password( $password ) {
		$this->save_post_meta( array(
				self::$meta_keys['password'] => $password
			) );
		return $password;
	}

	public function get_percentage() {
		$percentage = $this->get_post_meta( self::$meta_keys['percentage'] );
		return $percentage;
	}

	public function set_percentage( $percentage ) {
		$this->save_post_meta( array(
				self::$meta_keys['percentage'] => $percentage
			) );
		return $percentage;
	}
	
	public static function get_charity_id_for_user( $user_id = 0 ) {
		if ( !$user_id ) {
			$user_id = (int)get_current_user_id();
		}
		$authorized_ids = self::find_by_meta( self::POST_TYPE, array( self::$meta_keys['authorized_users'] => $user_id ) );
		if ( empty( $authorized_ids ) ) {
			//$account_id = self::blank_merchant();
			$account_id = 0;
		} else {
			$account_id = $authorized_ids[0];
		}
		return $account_id;
	}
	
	
	/**
	 * Can the given user edit the Charity's contact info, or draft deals for this Charity
	 *
	 * @param (int)   $user_id
	 * @return bool TRUE if the user is authorized to edit the Charity record
	 */
	public function is_user_authorized( $user_id ) {
		$authorized_users = $this->get_authorized_users();
		if ( empty( $authorized_users ) ) return;
		return in_array( $user_id, $authorized_users );
	}

	/**
	 * Get a list of all users who are authorized to edit this Charity
	 *
	 * @return array User IDs of all authorized users
	 */
	public function get_authorized_users() {
		$authorized_users = $this->get_post_meta( self::$meta_keys['authorized_users'], FALSE );
		if ( empty( $authorized_users ) ) {
			$authorized_users = array();
		}
		return $authorized_users;
	}

	/**
	 * Add a user to the list of authorized users
	 *
	 * @param int     $user_id
	 * @return void
	 */
	public function authorize_user( $user_id ) {
		$user = get_userdata( $user_id );
		if ( !is_a( $user, 'WP_User' ) ) { // Check if id is a WP User, if not try to find the id by cross referencing $id with an account.
			$account = Group_Buying_Account::get_instance_by_id( $user_id );
			$user_id = $account->get_user_id_for_account( $account->get_ID() );
		}
		if ( $user_id && !$this->is_user_authorized( $user_id ) ) {
			$this->add_post_meta( array(
					self::$meta_keys['authorized_users'] => $user_id
				) );
		}
	}

	/**
	 * Remove a user from the list of authorized users
	 *
	 * @param int     $user_id
	 * @return void
	 */
	public function unauthorize_user( $user_id ) {
		if ( $this->is_user_authorized( $user_id ) ) {
			$this->delete_post_meta( array(
					self::$meta_keys['authorized_users'] => $user_id
				) );
		}
	}
	
	/**
	 *
	 *
	 * @param int     $charity_id The charity to look for
	 * @return array List of IDs
	 */
	public static function get_charities_by_account( $user_id ) {
		$charities = self::find_by_meta( self::POST_TYPE, array( self::$meta_keys['authorized_users'] => $user_id ) );
		return $charities;
	}


	/**
	 * 
	 */
	public static function get_charities() {
		$args = array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
		$charities = new WP_Query( $args );
		return $charities->posts;
	}


}
