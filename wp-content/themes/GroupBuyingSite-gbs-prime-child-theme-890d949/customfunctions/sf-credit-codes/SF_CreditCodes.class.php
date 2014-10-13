<?php
/**
 * SF_CreditCodes
 * By StudioFidelis.com
 */
 
class SF_CreditCodes extends Group_Buying_Controller {
	
	const POST_TYPE = 'sf_credit_codes';
	const APPLY_CODE_VAR = 'sf_credit_code_apply';

	private static $instances;

	private static $meta_keys = array(
		'code' => '_code', // string
		'expiration_date' => '_expiration_date', // int
		'amount' => '_amount', // float
		'usage' => '_usage', // string
		'number_of_uses' => '_number_of_uses', // int
		'usage_history' => '_usage_history', // array
		'bulk_batch' => '_bulk_batch', // array
	);

	public static function init() {
		
		// Register Post type
		add_action( 'init', array( get_class(), 'register_post_type') );
		
		// Admin menu - Bulk import not used
		//add_action("admin_menu",  array( get_class(), 'add_submenu_page') );
		
		// Admin columns
		add_filter( 'manage_edit-'.self::POST_TYPE.'_columns', array( get_class(), 'custom_register_columns' ), 11 );
		add_filter( 'manage_'.self::POST_TYPE.'_posts_custom_column', array( get_class(), 'custom_column_display' ), 11, 2 );
		
		// Admin post edit filter
		add_filter( 'enter_title_here', array (get_class(), 'enter_title_here'), 10, 2);
		
		// Meta Boxes
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'));
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		
		//Add CSS & JS for Datepicker
		add_action( 'admin_enqueue_scripts', array( get_class(), 'admin_enqueue' ) );
		
		//Handle apply code forms
		add_action( 'init', array( get_class(), 'handle_code_form'), 999 );  
		
		//Add form
		add_action( 'account_section_before_dash', array( get_class(), 'add_account_section') ); 
		add_filter( 'gb_account_register_contact_info_fields', array(get_class(), 'add_registration_field'), 10);
		add_action( 'gb_validate_account_registration',array(get_class(), 'validate_registration_fields'), 50, 4);
		add_action( 'gb_registration', array(get_class(), 'process_registration'), 50, 5);

	}
	
	//Admin post edit - title box label
	public function enter_title_here ($title, $post = null) {
		if ( is_admin() ) {
			if ( ($post && $post->post_type == self::POST_TYPE) || $_GET['post_type'] == self::POST_TYPE ) {
				return 'enter description here (not visible to public)';
			}
		}
		return $title;
	}
	
	public function add_registration_field($fields) {
			$fields[self::APPLY_CODE_VAR] = array(
					'weight' => 53,
					'label' => self::__('Promo code'),
					'type' => 'text',
					'required' => FALSE,
					'placeholder' => '',
					'default' => ( !empty($_POST['gb_contact_'.self::APPLY_CODE_VAR]) ) ? $_POST['gb_contact_'.self::APPLY_CODE_VAR] : '',
					'description' => self::__('If you have a valid promo code then enter it here to apply the credit to your account.'),
			);
		return $fields;
	}
	public function validate_registration_fields( $errors = null, $username = null, $email = null, $post = null ) {
		
		if ( isset($_POST['gb_contact_'.self::APPLY_CODE_VAR]) && !empty($_POST['gb_contact_'.self::APPLY_CODE_VAR]) ) {
			$code_string = trim($_POST['gb_contact_'.self::APPLY_CODE_VAR]);
			
			//Validate
			$error = self::validate_code($code_string, false); //validate but no user_id check	
			if ( !empty( $error ) ) {
				$errors[] = $error;
			}
		}
		return $errors;
		
	}
	public function process_registration( $user = null, $user_login = null, $user_email = null, $password = null, $post = null ) {
		
		if ( isset($_POST['gb_contact_'.self::APPLY_CODE_VAR]) && !empty($_POST['gb_contact_'.self::APPLY_CODE_VAR]) ) {
			
			$code_string = trim($_POST['gb_contact_'.self::APPLY_CODE_VAR]);
			$user_id = $user->ID;
			
			//Validate
			$error = self::validate_code($code_string, $user_id);	
			if ( empty( $error ) ) {
				$result = self::apply_code($code_string, $user_id);	
				if ( $result )  {
					//Show success message
					self::set_message( self::__( 'Success! Your Promo Code credit has been applied!' ), self::MESSAGE_STATUS_INFO );	
				}
			} else {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );	
			}
		}
	}
	
	
	public static function add_account_section() {
		$balance = gb_get_account_balance( get_current_user_id(), 'balance' );
		?>
        <div class="dash_section add_coupon_code">
			<h2 class="section_heading gb_ff"><?php gb_e('Your Credit Account Balance'); ?></h2>
            <div class="clearfix">
            	<div class="current_coupon_balance" style="float: left; width: 44%; margin-top: 8px;">
                	<p><strong>Available Credit: </strong> <?php gb_formatted_money( $balance ); ?></p>
                </div>
                <div class="add_coupon_balance" style="float: right; width: 55%; text-align: left;">
                	<form name="add_coupon_code_form" action="" method="post">
                        <span class="gb-form-field gb-form-field-text">
                            <input type="text" name="<?php echo self::APPLY_CODE_VAR; ?>" id="<?php echo self::APPLY_CODE_VAR; ?>" class="text-input" value="" placeholder="" size="10">
                        </span>
                        <input class="form-submit submit" type="submit" value="Apply Code">
                    </form>
                </div>
              
            </div>
        </div>
        <?php	
	}
	
	//Builk Import NOT USED
	public function add_submenu_page() {
		add_submenu_page('edit.php?post_type='.self::POST_TYPE, 'Bulk Create Credit Codes', 'Bulk Create', 'manage_options', 'bulk_create_codes', array( get_class(), 'show_import_menu' ));
	}
	//Builk Import NOT USED
	public function show_import_menu() {
		if ( $_POST['sf_bulk_create_codes'] ) {
			
			$list = array();
			
			$number = (int)$_POST['bulk_create_number'];
			$amount = (float)$_POST['bulk_create_amount'];
			$expiration = trim($_POST['bulk_create_exp']);
			
			$batch_id = time();
			
			$i = 1;
			while( $i <= $number ) {

				$random_code = wp_generate_password(8, FALSE, FALSE);
				$random_code = strtoupper($random_code);
				
				//create
				$post = array(
					'post_title' => 'Bulk Coupon - '.$random_code.' (Batch '.$batch_id.')',
					'post_status' => 'publish',
					'post_type' => self::POST_TYPE,
				);
				$post_id = wp_insert_post( $post );
				if ( !is_wp_error( $post_id ) ) {
					update_post_meta($post_id, self::$meta_keys['code'], $random_code );
					
					if ( !empty( $expiration ) ) {
						update_post_meta($post_id, self::$meta_keys['expiration_date'], strtotime($expiration) );
					} else {
						update_post_meta($post_id, self::$meta_keys['expiration_date'], "" );
					}
					update_post_meta($post_id, self::$meta_keys['amount'], $amount );
					update_post_meta($post_id, self::$meta_keys['usage'], 'single' ); //Set to single use
					
					//Set bulk batch
					update_post_meta($post_id, self::$meta_keys['bulk_batch'], $batch_id ); 
				}
				
				$list[] = $random_code;
				
				//iterate
				$i++;
					
			}
		}
		?>
        <div class="wrap">
		<h2>Bulk Create Codes</h2>
        <?php if ( isset($_POST['sf_bulk_create_codes']) ) : ?>
        	<p><strong>Success! <?php echo sizeof($list); ?> Credit Codes created.</strong></p>
        	<label>Created Codes</label><br>
            <em>Copy and Paste the data below into a Spreadsheet.</em>
            <br>
        	<textarea name="list" rows="15" cols="40"><?php 
			foreach ( $list as $l ) {
				echo $l."\n";
			}
			?></textarea>
        <?php else : ?>
		<form method="post" action="">
        <input type="hidden" name="sf_bulk_create_codes" value="1">
		<table class="form-table">
        	<tbody>
            	<tr>
                    <th scope="row">Number to Create</th>
                    <td><input type="text" name="bulk_create_number" class="text-input" id="bulk_create_number" value="" size="5"><br>
                     <em>How many Credit codes to create (Type numbers only. Eg. 5).</em>
                    </td>
               </tr>
               <tr>
                    <th scope="row">Credit Amount</th>
                    <td><input type="text" name="bulk_create_amount" class="text-input" id="bulk_create_amount" value="" size="10"><br>
                     <em>Set the amount of credits awarded (Type numbers only. Eg. 9).</em>
                    </td>
               </tr>
               <tr>
                    <th scope="row">Expiration</th>
                    <td><input type="text" name="bulk_create_exp" class="text-input" id="bulk_create_exp" value="" size="20"><br>
                     <em>Leave blank for no expiration.</em>
                    </td>
               </tr>
                <tr>
                    <th scope="row"> </th>
                    <td><input type="submit" class="button button-primary" value="Create">
                    <em>Coupons will be created for Single User use.</em>
                    </td>
               </tr>
            </tbody>
        </table>
        </form>
        <br class="clear">
        </div>
        <script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#bulk_create_exp').datetimepicker();
			});
		</script> 
        <?php endif; ?>
        <?php
	}
	
	public static function admin_enqueue() {
		wp_enqueue_script( 'gb_timepicker' );
		wp_enqueue_script( 'gb_admin_deal');
		wp_enqueue_style( 'gb_admin_deal' );
	}

	public static function register_post_type() {
		
		 $labels = array(
			'name'               => 'Promo Codes',
			'singular_name'      => 'Promo Code',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Promo Code',
			'edit_item'          => 'Edit Promo Code',
			'new_item'           => 'New Promo Code',
			'all_items'          => 'All Promo Codes',
			'view_item'          => 'View Promo Code',
			'search_items'       => 'Search Promo Codes',
			'not_found'          => 'No Promo Codes found',
			'not_found_in_trash' => 'No Promo Codes found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Promo Codes'
		  );
		
		  $args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => false,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
			//'menu_icon' 		 => SF_FV_URL."/assets/charity-icon.png"
		  );
		
		 register_post_type( self::POST_TYPE, $args );
		
	}
	
	//Admin post_type columns
	public static function custom_register_columns( $columns ) {
		$columns['sf_creditcode_code'] = self::__( 'Code' );
		$columns['sf_creditcode_usage'] = self::__( 'Usage Count' );
		$columns['sf_creditcode_amount'] = self::__( 'Amount' );
		$columns['sf_creditcode_restrictions'] = self::__( 'Restrictions' );
		$columns['sf_creditcode_exp_status'] = self::__( 'Status' );
		//Move prebuilt columns
		unset($columns['date']);
		$columns['date'] = self::__( 'Date' );
		return $columns;
	}
	public static function custom_column_display( $column_name, $id ) {
		global $post;

		switch ( $column_name ) {
		
		case 'sf_creditcode_code':
			$value = self::get_field($post->ID, 'code');
			echo '<p>'.$value.'</p>';
			break;
		case 'sf_creditcode_amount':
			$value = gb_get_formatted_money(self::get_field($post->ID, 'amount'));
			echo '<p>'.$value.'</p>';
			break;
		case 'sf_creditcode_restrictions':
			$value = self::get_field($post->ID, 'usage');
			if ( $value == 'single' ) {
				echo '<p>Single use</p>';
			} else {
				echo '<p>Multiple use</p>';	
			}

			break;
		case 'sf_creditcode_exp_status':
			$expiration = trim(self::get_field($post->ID, 'expiration_date'));
			
			if ( empty($expiration) ) {
				printf( '<span style="color:green">%1$s</span> <span style="color:silver">(id: %2$s)</span> <br/><span style="color:silver">expires: %3$s</span> ', self::__( 'Active' ), $id, gb__( 'Never' ) );
			} elseif ( $expiration > time() ) {
				printf( '<span style="color:green">%1$s</span> <span style="color:silver">(id: %2$s)</span> <br/><span style="color:silver">expires: %3$s</span> ', self::__( 'Active' ), $id, date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $expiration ) );
				break;
			} elseif  ( $expiration <= time() ) {
				printf( '<span style="color:#BC0B0B">%1$s</span> <span style="color:silver">(id: %2$s)</span> <br/><span style="color:silver">%3$s</span>', self::__( 'Expired' ), $id, $expiration );
				break;
			} else {
				echo '<span style="color:black">'.gb__( 'Unknown' ).'</span>';	
				break;
			}
			break;
			
		case 'sf_creditcode_usage':
			$number_of_uses = intval(self::get_field($post->ID, 'number_of_uses'));
			$value = floatval(self::get_field($post->ID, 'amount'));
			$total = gb_get_formatted_money($number_of_uses * $value);
			echo '<p>Count: '.$number_of_uses.'</p>';
			echo '<p>Credits Awarded: '.$total.'</p>';
			break;
		default:
			break;
		}
	}


	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 * Get the credit_code instance by id
	 */
	public static function get_instance( $id = 0 ) {
		if ( !$id )
			return NULL;
		
		if ( !isset( self::$instances[$id] ) || !self::$instances[$id] instanceof self )
			self::$instances[$id] = new self( $id );

		if ( !isset( self::$instances[$id]->post->post_type ) )
			return NULL;
		
		if ( self::$instances[$id]->post->post_type != self::POST_TYPE )
			return NULL;
		
		return self::$instances[$id];
	}
	
	
	public static function get_instance_by_code( $code ) {
		$codes = Group_Buying_Post_Type::find_by_meta( self::POST_TYPE, array( self::$meta_keys['code'] => $code ) );
		$code_id = array_pop($codes);
		$instance = self::get_instance($code_id);
		return $instance;
	}
	
	public static function get_id_by_code( $code ) {
		$codes = Group_Buying_Post_Type::find_by_meta( self::POST_TYPE, array( self::$meta_keys['code'] => $code ) );
		$code_id = array_pop($codes);
		return $code_id;
	}
	

	/**
	 * If the current query is for the credit code post type
	 */
	public static function is_credit_codes_query() {
		$post_type = get_query_var( 'post_type' );
		if ( $post_type == self::POST_TYPE ) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Get all credit_code posts
	 */
	public static function get_credit_codes() {
		$args = array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
		$credit_codes = new WP_Query( $args );
		return $credit_codes->posts;
	}
	
	
	/**
	 * Meta boxes
	 */
	
	public static function add_meta_boxes() {
		add_meta_box('sf_creditcode_fields_metabox', 'Details', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'normal', 'default');
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'sf_creditcode_fields_metabox':
				self::show_sf_creditcode_fields_metabox($post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}
	
	private static function show_sf_creditcode_fields_metabox( $post, $metabox ) {
		$expiration_timestamp = '';
		$expiration_date = '';
		$history = false;
		if ( $post && $post->ID ) {
			$expiration_timestamp = trim(self::get_field($post->ID, 'expiration_date') );
			$expiration_date = ($expiration_timestamp) ? date( 'Y-m-d G:i', $expiration_timestamp) : '';
			//Get usage history
			$history = self::get_field_multiple($post->ID, 'usage_history');
		}
		?>
        <p>
            <label for="<?php echo self::$meta_keys['code'] ?>"><strong><?php gb_e( 'Code' ); ?></strong></label><br />
            <input class="medium-text" type="text" name="<?php echo self::$meta_keys['code'] ?>" id="<?php echo self::$meta_keys['code'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'code'))); ?>"><br>
            <em>Set the code Promo Code (eg. CODE99ABC).</em>
        </p>
         <p>
            <label for="<?php echo self::$meta_keys['amount'] ?>"><strong><?php gb_e( 'Amount of Credits' ); ?></strong></label><br />
            <input class="small-text" type="text" name="<?php echo self::$meta_keys['amount'] ?>" id="<?php echo self::$meta_keys['amount'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'amount'))); ?>"><br>
            <em>Set the amount of credits awarded (Type numbers only. Eg. 9).</em>
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['expiration_date'] ?>"><strong><?php gb_e( 'Expiration Date' ); ?></strong></label><br />
            <input class="medium-text" type="text" name="<?php echo self::$meta_keys['expiration_date'] ?>" id="<?php echo self::$meta_keys['expiration_date'] ?>" value="<?php echo $expiration_date; ?>">
            <em>Leave blank for no expiration.</em>
        </p>
		<p>
            <label><strong><?php gb_e( 'Usage Restrictions' ); ?></strong><label><br>
            <input type="checkbox" name="<?php echo self::$meta_keys['usage'] ?>" id="<?php echo self::$meta_keys['usage'] ?>" value="single" <?php echo ( self::get_field($post->ID, 'usage') == 'single' ) ? 'checked="checked"' : ''; ?>> Restrict to single use</label><br>
            <em>Check this box to restrict this code to be used by only one user. If unchecked, the code can be used by multiple users.<br>
            Note: All codes (regardless of Usage Restrictions set above) cannot be used more than once by the same user.</em>
        </p>
      	<hr>
        <p>
        <p>
            <label><strong><?php gb_e( 'Usage History' ); ?></strong><label><br>
       		<?php
			if ( !empty( $history ) ) {
				foreach (  $history as $h ) {
					$user_meta = get_user_by('id', (int)$h['user_id']);
					$this_user_meta_email = ($user_meta->user_email) ? $user_meta->user_email : '';
					echo 'User: '.$this_user_meta_email.' (user id: '.$h['user_id'].')  on '.date('Y-m-d H:i:s', $h['timestamp']); 
					echo '<br>';
				}
			} else {
				echo 'No usage history';	
			}
			?>
        </p>
       
        <script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#_expiration_date').datetimepicker({ dateFormat: "yy-mm-dd" });

			});
		</script> 
        <script>
		jQuery(document).ready(function(){
		  document.getElementById('<?php echo self::$meta_keys['code'] ?>').focus();
		 });
		</script>
		<?php
	}

	public static function save_meta_boxes( $post_id, $post ) {
	
		// only continue if it's an credit_code post
		if ( $post->post_type != self::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined('DOING_AJAX') || isset($_GET['bulk_edit']) ) {
			return;
		}
		if (empty($_POST)) {
			return;	
		}
		
		self::save_meta_box($post_id, $post);
	}

	private static function save_meta_box( $post_id, $post ) {
		
		//Save all post form fields
		if ( isset( $_POST[self::$meta_keys['code']] ) ) {
			update_post_meta($post_id, self::$meta_keys['code'], stripslashes($_POST[self::$meta_keys['code']]) );
		}
		if ( isset( $_POST[self::$meta_keys['expiration_date']] ) ) {
			$expiration = $_POST[self::$meta_keys['expiration_date']];
			if ( !empty( $expiration ) ) {
				update_post_meta($post_id, self::$meta_keys['expiration_date'], strtotime($expiration) );
			} else {
				update_post_meta($post_id, self::$meta_keys['expiration_date'], "" );
			}
			
		}
		if ( isset( $_POST[self::$meta_keys['amount']] ) ) {
			update_post_meta($post_id, self::$meta_keys['amount'], stripslashes($_POST[self::$meta_keys['amount']]) );
		}
		//checkbox
		if ( isset( $_POST[self::$meta_keys['usage']] ) ) {
			update_post_meta($post_id, self::$meta_keys['usage'], stripslashes($_POST[self::$meta_keys['usage']]) );
		} else {
			update_post_meta($post_id, self::$meta_keys['usage'], '' );
		}
	}

	public static function get_field( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key) {
			$value  = get_post_meta( $post_id, self::$meta_keys[$meta_key], true );
		}
		return $value;
	}
	
	public static function save_field( $post_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($post_id && $meta_key) {
			$value  = update_post_meta( $post_id, self::$meta_keys[$meta_key], $new_value );
		}
		return $value;
	}
	
	public static function get_field_multiple( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key) {
			$value  = get_post_meta( $post_id, self::$meta_keys[$meta_key], false ); //return multiple
		}
		return $value;
	}
	
	public static function save_field_multiple( $post_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($post_id && $meta_key) {
			$value  = add_post_meta( $post_id, self::$meta_keys[$meta_key], $new_value );
		}
		return $value;
	}
	
	public static function increase_use_count($code_id, $user_id) {
		if ( $code_id ) {
			$number_of_uses = intval(self::get_field($code_id, 'number_of_uses') );
			$number_of_uses++;
			self::save_field($code_id, 'number_of_uses', $number_of_uses);
			
			//history
			$history = array('user_id' => $user_id, 'timestamp' => time());
			self::save_field_multiple($code_id, 'usage_history', $history );
			
		}
	}
	
	/* Public functions */
	
	//Apply Credit code
	public static function handle_code_form() {
		
		if ( isset($_POST[self::APPLY_CODE_VAR]) && !empty($_POST[self::APPLY_CODE_VAR]) ) {
			$code_string = trim($_POST[self::APPLY_CODE_VAR]);
			$user_id = get_current_user_id();
			
			//Validate
			$error = self::validate_code($code_string, $user_id);	
			if ( empty( $error ) ) {
				$result = self::apply_code($code_string, $user_id);	
				if ( $result )  {
					//Show success message
					self::set_message( self::__( 'Success! Your Promo Code credit has been applied!' ), self::MESSAGE_STATUS_INFO );	
				}
			} else {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );	
			}
		}
	}
	
	public static function validate_code( $code_string, $user_id = false ) {
		
		$code_id = self::get_id_by_code($code_string);
		
		if ( !$code_id ) {
			return self::__( 'Error: This Promo Code is invalid.' );
		}
		
		$expiration = trim(self::get_field($code_id, 'expiration_date'));
		if ( !empty($expiration) && $expiration <= time() ) {
			return self::__( 'Error: This Promo Code has expired.' );
		}
		
		$number_of_uses = intval(self::get_field($code_id, 'number_of_uses'));
		$usage = trim(self::get_field($code_id, 'usage'));
		if ( !empty($usage) && $usage <= 'single' && $number_of_uses > 0 ) {
			return self::__( 'Error: This Promo Code has already been used.' );
		}
		
		//Check if user already used the code
		if ( !empty($user_id) ) {
		$usage_history = self::get_field_multiple($code_id, 'usage_history');
			if ( !empty($usage_history) ) {
				foreach ($usage_history as $h ) {
					if ( (int)$h['user_id'] == $user_id ) {
						return self::__( 'Error: You cannot apply this Promo Code more than once.' );
					}
				}
			}
		}
		
		return ""; //return no errors
		
	}
	
	public static function apply_code( $code_string, $user_id ) {
		
		$code_id = self::get_id_by_code($code_string);
		
		$account = Group_Buying_Account::get_instance($user_id);
		if ( !$account ) {
			self::set_message( self::__( 'Error: Could not find the user to apply the code to.' ), self::MESSAGE_STATUS_ERROR );
			return;
		}
		
		$balance = $account->get_credit_balance( Group_Buying_Accounts::CREDIT_TYPE );
		//Add to total
		$amount = floatval(self::get_field($code_id, 'amount'));
		$total = $balance+$amount;

		$account->set_credit_balance( $total, Group_Buying_Accounts::CREDIT_TYPE );
		$data = array();
		$data['note'] = 'Promo Code Applied: '.$code_string.' on '.date('Y-m-d H:i:s');
		$data['adjustment_value'] = $value;
		$data['current_total'] = $total;
		$data['prior_total'] = $balance;
		do_action( 'gb_new_record', $data, Group_Buying_Accounts::$record_type . '_' . Group_Buying_Accounts::CREDIT_TYPE, 'Promo Code Applied: '.$code_string , $user_id, $account->get_ID() );

		//Increase count
		self::increase_use_count($code_id, $user_id);
		
		return true;
	}
	
}
SF_CreditCodes::init();
