<?php
/*  
 *  SF_Account_Fields
 *	By StudioFidelis.com
 */
class SF_Account_Fields extends Group_Buying_Controller {

	const FIELD_PREFERRED_LOCATION = 'gb_contact_preferred_location';

	public static function init() {

		// Get Fields
		add_filter('gb_account_register_contact_info_fields', array(get_class(), 'get_custom_registration_fields'), 10);
		add_filter('gb_account_edit_contact_fields', array(get_class(), 'get_custom_edit_fields'), 1, 1);
		
		// Save Fields
		add_action('gb_registration', array(get_class(), 'process_registration'), 50, 5);
		add_action('gb_process_account_edit_form',array(get_class(), 'process_edit_account'));
		
		// Validate fields
		add_action('gb_validate_account_registration',array(get_class(), 'custom_validate_registration_fields'), 50, 4);
		add_action('gb_validate_account_edit_form',array(get_class(), 'custom_validate_edit_fields'), 50, 2);
		
		// Get Panes - Alternative display - NOT USED
		//add_filter('gb_account_registration_panes', array(get_class(), 'get_registration_custom_panes'), 50, 1);
		
		
		// Meta Boxes (Admin editing)
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'), 12);
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		
		// Set the users preferred location to global variable (used elsewhere)
		add_action('wp', array(get_class(), 'set_preferred_location_global'), 5);
		
	
	}
	
	//Set the users preferred location
	public function set_preferred_location_global() {
		global $preferred_location;
		if ( is_user_logged_in() ) {
			$account = Group_Buying_Account::get_instance(get_current_user_id());
			if ( $account ) {
				$preferred_location = get_post_meta( $account->get_ID(), '_'.self::FIELD_PREFERRED_LOCATION, TRUE );
			}
		}
		
	}
	
	/* Account registration and Edit */
	public function process_registration( $user = null, $user_login = null, $user_email = null, $password = null, $post = null ) {
		$account = Group_Buying_Account::get_instance($user->ID);
		
		self::process_form($account);
	
	}
	
	public function custom_validate_registration_fields( $errors = null, $username = null, $email = null, $post = null ) {
		
		$additional_errors = self::custom_validate_fields();
		
		if ( $additional_errors ) {
			$errors =  array_merge($errors, $additional_errors);
		}
		return $errors;
		
	}
	
	public static function process_edit_account( Group_Buying_Account $account ) {
		
		self::process_form($account);
	}
	
	public function custom_validate_edit_fields( $errors = null, $account = null ) {
		
		$additional_errors = self::custom_validate_fields();
		
		if ( $additional_errors ) {
			$errors =  array_merge($errors, $additional_errors);
		}
		return $errors;
	}
	
	public function custom_validate_fields() {
		
		$erros = false;
		/*
		if ( isset($_POST[self::FIELD_PREFERRED_LOCATION]) && empty($_POST[self::FIELD_PREFERRED_LOCATION]) ) {
			$errors[] = self::__('Please select your Preferred Location');
		}
		*/
		
		//NOTE: Required fields should be validated by GBS, so no validation needed here checking for required fields.
		
		return $errors;
	}
	
	
	/**
	 * Process the form submission and save the meta
	 *
	 * @param array  | Group_Buying_Account
	 * @return null 
	 */
	 
	public static function process_form( Group_Buying_Account $account ) {
		if ( isset($_POST[self::FIELD_PREFERRED_LOCATION]) ) {
			update_post_meta( $account->get_ID(), '_'.self::FIELD_PREFERRED_LOCATION, trim($_POST[self::FIELD_PREFERRED_LOCATION]) );
			//error_log("Setting phone: ".$_POST[self::FIELD_PREFERRED_LOCATION]);
		}
	
	}

	public function build_custom_fields() {
		//GBS Form fields use naming scheme: gb_contact_{field_key}
		$locations = gb_get_locations( $hide_empty = false );
		$select_locations = array();
		if ( !empty($locations) ) {
			foreach ($locations as $loc ) {
				$select_locations[$loc->slug] = $loc->name;
			}
		}
		$fields['preferred_location'] = array(
				'weight' => 51,
				'label' => self::__('Preferred Location'),
				'type' => 'select',
				'options' => $select_locations,
				'required' => TRUE,
				'default' => ( !empty($_POST[self::FIELD_PREFERRED_LOCATION]) ) ? $_POST[self::FIELD_PREFERRED_LOCATION] : '',
				'description' => self::__('Please Select A Location As Your Preferred Location; This Will Be Your Default Location On The Website.'),
		);
		
		return $fields;
	}

	public function get_custom_registration_fields ( $fields ) {
		
		$add_fields = self::build_custom_fields();
		
		return array_merge($fields, $add_fields);
	}
	
	public function get_custom_edit_fields ( $fields ) {
		$account_id = Group_Buying_Account::get_account_id_for_user();
		
		$add_fields = self::build_custom_fields();
		
		//Set default value from saved value
		$add_fields['preferred_location']['default'] = ($add_fields['preferred_location']['default']) ? $add_fields['preferred_location']['default'] : get_post_meta( $account_id, '_'.self::FIELD_PREFERRED_LOCATION, TRUE );
		
		return array_merge($fields, $add_fields);
	}
	
	//Change panes
	public function get_registration_custom_panes ( $panes ) {
		
		// DISABLED, we already added the custom fields to the other panes, so don't show again
		/*
		$add_fields = self::build_custom_fields();
	
		//Build Pane
		ob_start();
		?>
		 <fieldset id="gb-custom-fields-pane">
			<legend><?php self::_e('Select a Deal Location'); ?></legend>
			<table class="account collapsable custom-fields">
				<tbody>
					<?php foreach ( $add_fields as $key => $data ): ?>
						<tr>
							<?php if ( $data['type'] != 'checkbox' ): ?>
								<td><?php gb_form_label($key, $data, 'contact'); ?></td>
								<td>
									<?php gb_form_field($key, $data, 'contact'); ?>
									<?php if ( $data['desc'] != '' ): ?>
										<br/><small><?php echo $data['desc']  ?></small>	
									<?php endif ?>
								</td>
							<?php else: ?>
								<td colspan="2">
									<label for="gb_contact_<?php echo $key; ?>"><?php gb_form_field($key, $data, 'contact'); ?> <?php echo $data['label']; ?></label>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</fieldset>
		<?php
		$pane_body = ob_get_clean();		
		
		$panes['custom'] = array(
			'weight' => 99,
			'body'	 => $pane_body,
		);*/
		
		//Modify subscription page
	
		return $panes;
	}
	

	
	/* Admin editing */
	public static function add_meta_boxes() {
		add_meta_box('sf-account-custom-fields', self::__('Custom Fields'), array(get_class(), 'show_meta_boxes'), Group_Buying_Account::POST_TYPE, 'normal', 'low');
	}
	
	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'sf-account-custom-fields':
				self::show_meta_box( $post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}

	private static function show_meta_box( $post, $metabox ) {
		//post is account
		$account_id = $post->ID;
		
		$preferred_location = get_post_meta( $account_id, '_'.self::FIELD_PREFERRED_LOCATION, TRUE );
		$locations = gb_get_locations( $hide_empty = false );
		
		?>
			<table class="form-table">
				<tbody>
					<tr>
						<td>
							<label for="<?php echo self::FIELD_PREFERRED_LOCATION ?>"><?php echo self::__('Preferred Location'); ?></label><br />
							<select name="<?php echo self::FIELD_PREFERRED_LOCATION ?>">
                            	<?php
								foreach ($locations as $loc ) {
									$selected = ( $preferred_location == $loc->slug ) ? 'selected="selected"' : '';
									?>
                                    <option value="<?php echo $loc->slug; ?>" <?php echo $selected;?>><?php echo $loc->name; ?></option>
                                    <?php
								}
								?>
                            </select>
                    
						</td>
					</tr>
                   
				</tbody>
			</table>
		<?php
	}
	
	public static function save_meta_boxes( $post_id, $post ) {
		// only continue if it's an account post
		if ( $post->post_type != Group_Buying_Account::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined('DOING_AJAX') || isset($_GET['bulk_edit']) ) {
			return;
		}
		// save all the meta boxes
		$account = Group_Buying_Account::get_instance_by_id( $post_id );
		if ( !is_a( $account, 'Group_Buying_Account' ) ) {
			return; // The account doesn't exist
		}
		if (empty($_POST)) {
			return;	
		}
		self::save_meta_box($account, $post_id, $post);
	}


	private static function save_meta_box( Group_Buying_Account $account, $post_id, $post ) {

		if ( isset($_POST[self::FIELD_PREFERRED_LOCATION]) ) {
			update_post_meta( $account->get_ID(), '_'.self::FIELD_PREFERRED_LOCATION, $_POST[self::FIELD_PREFERRED_LOCATION] );
			//error_log("Setting phone: ".$_POST[self::FIELD_PREFERRED_LOCATION]);
		}
		
	}

}
SF_Account_Fields::init();