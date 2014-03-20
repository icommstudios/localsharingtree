<?php 

class SF_GBS_Merchant_Fields extends Group_Buying_Controller {
	
	private static $meta_keys = array(
		'checks_payable' => '_custom_checks_payable', // string
		'ein' => '_custom_ein', // string
		'contact_email' => '_custom_contact_email', // string
		'agree_terms' => '_custom_agree_terms', // string
		'business_street' => '_custom_business_street', // string
		'business_city' => '_custom_business_city', // string
		'business_zone' => '_custom_business_zone', // string
		'business_postal_code' => '_custom_business_postal_code', // string
		'business_country' => '_custom_business_country', // string
	);
	
	public static function init() {
  		
		// Add Meta
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'), 9);
		// Save meta
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		
		// Merchant Registration & Edit
		add_filter('gb_merchant_register_contact_info_fields', array(get_class(), 'add_merchant_register_fields'), 10, 2 );
		//add_filter('gb_validate_merchant_registration', array(get_class(), 'validate_merchant_fields'), 10, 2); //NOT REQUIRED - GBS validates field marked required
		add_action('register_merchant', array(get_class(), 'process_merchant_form_submit'), 50, 1);
		add_action('edit_merchant', array(get_class(), 'process_merchant_form_submit'), 50, 1);
		
		//Add locations taxonomy to Merchants & charities
		//add_action('init', array(get_class(), 'custom_add_locations_taxonomy'), 999);
		add_filter( 'pre_get_posts', array(get_class(), 'custom_query_deals_only_locations') ); //Remove merchants and other post_types from Location taxonomy lists (only show deals)
		
		add_action('wp_footer', array(get_class(), 'add_scripts_footer') );
		
	}
	
	// Admin Meta boxes 
	
	public static function add_meta_boxes() {
		add_meta_box('sf_merchant_fields', self::__('Additional Merchant Fields'), array(get_class(), 'show_meta_boxes'), Group_Buying_Merchant::POST_TYPE, 'advanced', 'high');
		add_meta_box('sf_charity_fields', self::__('Additional Charity Fields'), array(get_class(), 'show_meta_boxes'), 'gb_charities', 'advanced', 'high');
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'sf_merchant_fields':
				self::show_meta_merchant_box($post, $metabox);
				break;
			case 'sf_charity_fields':
				self::show_meta_charity_box($post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}

	private static function show_meta_merchant_box( $post, $metabox ) {
		?>
			<table class="form-table">
				<tbody>
					<tr>
						<td>
							<label for="<?php echo self::$meta_keys['checks_payable'] ?>"><?php gb_e('Checks Payable To'); ?></label><br />
							<input type="text" name="<?php echo self::$meta_keys['checks_payable'] ?>" value="<?php echo self::get_meta_value($post->ID, 'checks_payable') ?>" id="<?php echo self::$meta_keys['checks_payable'] ?>" class="large-text">
                    
						</td>
					</tr>
					<tr>
						<td>
							<label for="<?php echo self::$meta_keys['ein'] ?>"><?php gb_e('EIN #'); ?></label><br />
							<input type="text" name="<?php echo self::$meta_keys['ein'] ?>" value="<?php echo self::get_meta_value($post->ID, 'ein') ?>" id="<?php echo self::$meta_keys['ein'] ?>" class="large-text">
                    
						</td>
					</tr>
			          <tr>
						<td>
							<label for="<?php echo self::$meta_keys['contact_email'] ?>"><?php gb_e('Contact Email'); ?></label><br />
							<input type="text" name="<?php echo self::$meta_keys['contact_email'] ?>" value="<?php echo self::get_meta_value($post->ID, 'contact_email') ?>" id="<?php echo self::$meta_keys['contact_email'] ?>" class="large-text">
                    
						</td>
					</tr>
				</tbody>
			</table>
            <h4>Business Address</h4>
            <table class="form-table">
				<tbody>
					<tr>
						<td>
							<label for="<?php echo self::$meta_keys['business_street'] ?>"><?php gb_e('Street Address'); ?></label><br />
							<input type="text" name="<?php echo self::$meta_keys['business_street'] ?>" value="<?php echo self::get_meta_value($post->ID, 'business_street') ?>" id="<?php echo self::$meta_keys['business_street'] ?>" class="large-text">
                    
						</td>
					</tr>
					<tr>
						<td>
							<label for="<?php echo self::$meta_keys['business_city'] ?>"><?php gb_e('City'); ?></label><br />
							<input type="text" name="<?php echo self::$meta_keys['business_city'] ?>" value="<?php echo self::get_meta_value($post->ID, 'business_city') ?>" id="<?php echo self::$meta_keys['business_city'] ?>" class="large-text">
                    
						</td>
					</tr>
                    <tr>
						<td>
							<label for="<?php echo self::$meta_keys['business_zone'] ?>"><?php gb_e('State'); ?></label><br />
                            <select name="<?php echo self::$meta_keys['business_zone'] ?>" id="<?php echo self::$meta_keys['business_zone'] ?>" class="select2" style="width:350px">
                                <option></option>
                                <?php $options = Group_Buying_Controller::get_state_options(); 
								$business_zone = self::get_meta_value($post->ID, 'business_zone');
								?>
                                <?php foreach ( $options as $group => $states ) : ?>
                                    <optgroup label="<?php echo $group ?>">
                                        <?php foreach ( $states as $option_key => $option_label ): ?>
                                            <option value="<?php echo $option_key; ?>" <?php selected( $option_key, $business_zone ) ?>><?php echo $option_label; ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                        </select>
					
						</td>
					</tr>
                   
                    <tr>
						<td>
							<label for="<?php echo self::$meta_keys['business_postal_code'] ?>"><?php gb_e('ZIP'); ?></label><br />
							<input type="text" name="<?php echo self::$meta_keys['business_postal_code'] ?>" value="<?php echo self::get_meta_value($post->ID, 'business_postal_code') ?>" id="<?php echo self::$meta_keys['business_postal_code'] ?>" class="large-text">
                    
						</td>
					</tr>
                     <tr>
						<td>
							<label for="<?php echo self::$meta_keys['business_country'] ?>"><?php gb_e( 'Country' ); ?></label><br />
                            <select name="<?php echo self::$meta_keys['business_country'] ?>" id="<?php echo self::$meta_keys['business_country'] ?>" class="select2" style="width:350px">
                                <option></option>
                                <?php $options = Group_Buying_Controller::get_country_options(); 
								$business_country = self::get_meta_value($post->ID, 'business_country');
								?>
                                <?php foreach ( $options as $key => $label ): ?>
                                    <option value="<?php esc_attr_e( $key ); ?>" <?php selected( $key, $business_country ); ?>><?php esc_html_e( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                    
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}
	
	private static function show_meta_charity_box( $post, $metabox ) {
		?>
			<table class="form-table">
				<tbody>
					<tr>
						<td>
							<label for="<?php echo self::$meta_keys['checks_payable'] ?>"><?php gb_e('Checks Payable To'); ?></label><br />
							<input type="text" name="<?php echo self::$meta_keys['checks_payable'] ?>" value="<?php echo self::get_meta_value($post->ID, 'checks_payable') ?>" id="<?php echo self::$meta_keys['checks_payable'] ?>" class="large-text">
                    
						</td>
					</tr>
					<tr>
						<td>
							<label for="<?php echo self::$meta_keys['ein'] ?>"><?php gb_e('EIN #'); ?></label><br />
							<input type="text" name="<?php echo self::$meta_keys['ein'] ?>" value="<?php echo self::get_meta_value($post->ID, 'ein') ?>" id="<?php echo self::$meta_keys['ein'] ?>" class="large-text">
                    
						</td>
					</tr>
          		</tbody>
			</table>
		<?php
	}

	public static function save_meta_boxes( $post_id, $post ) {
		// only continue if it's a merchant post
		if ( $post->post_type != Group_Buying_Merchant::POST_TYPE && $post->post_type != 'gb_charities' ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined('DOING_AJAX') || isset($_GET['bulk_edit']) ) {
			return;
		}
		if ( empty($_POST) ) {
			return;	
		}
		
		//meta post field are the same for merchant & charity 
		self::save_meta_box($post_id, $post);
	}

	private static function save_meta_box( $post_id, $post ) {

		$checks_payable = isset( $_POST[self::$meta_keys['checks_payable']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['checks_payable']])) : '';
		$ein = isset( $_POST[self::$meta_keys['ein']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['ein']])) : '';
		$agree_terms = isset( $_POST[self::$meta_keys['agree_terms']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['agree_terms']])) : '';
		$contact_email = isset( $_POST[self::$meta_keys['contact_email']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['contact_email']])) : '';
		
		$business_street = isset( $_POST[self::$meta_keys['business_street']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['business_street']])) : '';
		$business_city = isset( $_POST[self::$meta_keys['business_city']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['business_city']])) : '';
		$business_zone = isset( $_POST[self::$meta_keys['business_zone']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['business_zone']])) : '';
		$business_postal_code = isset( $_POST[self::$meta_keys['business_postal_code']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['business_postal_code']])) : '';
		$business_country = isset( $_POST[self::$meta_keys['business_country']] ) ? stripslashes(esc_attr($_POST[self::$meta_keys['business_country']])) : '';
		
		
		self::set_meta_value( $post_id, $checks_payable, 'checks_payable' );
		self::set_meta_value( $post_id, $ein, 'ein' );
		self::set_meta_value( $post_id, $contact_email, 'contact_email' );
		self::set_meta_value( $post_id, $agree_terms, 'agree_terms' );
		
		self::set_meta_value( $post_id, $business_street, 'business_street' );
		self::set_meta_value( $post_id, $business_city, 'business_city' );
		self::set_meta_value( $post_id, $business_zone, 'business_zone' );
		self::set_meta_value( $post_id, $business_postal_code, 'business_postal_code' );
		self::set_meta_value( $post_id, $business_country, 'business_country' );
		
		
	}

	public static function get_meta_value( $post_id, $meta_key = '') {
		$meta_value = get_post_meta( $post_id, self::$meta_keys[$meta_key], TRUE );
		return $meta_value;
	}
    
	public static function set_meta_value( $post_id, $meta_value, $meta_key = '' ) {
		update_post_meta ( $post_id, self::$meta_keys[$meta_key], $meta_value );
		return $meta_value;
	}
    
	
	// Merchant Registration & Edit
	public function validate_merchant_fields( $errors, $post ) {
		

		/*
		if ( (isset($post['gb_contact_checks_payable']) && trim($post['gb_contact_checks_payable']) == '') ) {
			$errors[] = sprintf(self::__('"%s" field is required.'), self::__('Checks Payable To'));
		}
		if ( (isset($post['gb_contact_ein']) && trim($post['gb_contact_ein']) == '') ) {
			$errors[] = sprintf(self::__('"%s" field is required.'), gb__('Company Start Date'));
		}
		*/
		
		return $errors;
	}
	
	public function process_merchant_form_submit ( Group_Buying_Merchant $merchant ) {
		$merchant_id = $merchant->get_ID();
		self::process_merchant_save_form($merchant_id);
	}
	
	public static function process_merchant_save_form( $merchant_id) {
		if ( isset($_POST['gb_contact_checks_payable']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_checks_payable'])), 'checks_payable' );
		}
		if ( isset($_POST['gb_contact_ein']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_ein'])), 'ein' );
		}
		if ( isset($_POST['gb_contact_contact_email']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_contact_email'])), 'contact_email' );
		}
		
		if ( isset($_POST['gb_contact_agree_terms']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_agree_terms'])), 'agree_terms' );
		}
		if ( isset($_POST['gb_contact_business_street']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_business_street'])), 'business_street' );
		}
		if ( isset($_POST['gb_contact_business_city']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_business_city'])), 'business_city' );
		}
		if ( isset($_POST['gb_contact_business_zone']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_business_zone'])), 'business_zone' );
		}
		if ( isset($_POST['gb_contact_business_postal_code']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_business_postal_code'])), 'business_postal_code' );
		}
		if ( isset($_POST['gb_contact_business_country']) ) {
			self::set_meta_value($merchant_id, stripslashes(esc_html($_POST['gb_contact_business_country'])), 'business_country' );
		}
	
		//handle locations field
		/*
		$locations = isset( $_POST['gb_contact_locations'] ) ? $_POST['gb_contact_locations'] : array();
		wp_set_post_terms( $merchant_id, $locations, Group_Buying_Deal::LOCATION_TAXONOMY );
		*/
	}
	
	public static function add_merchant_register_fields($fields, $merchant = null) {
		$merchant_id = ($merchant) ? $merchant->get_ID() : '';
		
		$fields['label_mailing_address'] = array(
			'weight' => 9.5,
			'label' => '<h3>'.self::__( 'Mailing Address' ). '</h3>',
			'type' => 'hidden',
		);
		
		$fields['contact_email'] = array(
			'weight' => 17,
			'label' => self::__('Contact Email'),
			'type' => 'text',
			'required' => TRUE,
			'default' => self::get_meta_value( $merchant_id, 'contact_email' ),
		);
		
		$fields['checks_payable'] = array(
			'weight' => 50,
			'label' => self::__('Checks Payable To'),
			'type' => 'text',
			'required' => TRUE,
			'default' => self::get_meta_value( $merchant_id, 'checks_payable' ),
		);
		
		$fields['ein'] = array(
			'weight' => 51,
			'label' => self::__('Tax Identification Number (EIN or SSN)'),
			'type' => 'text',
			'required' => TRUE,
			'default' => self::get_meta_value( $merchant_id, 'ein' ),
		);
		
		$fields['name']['weight'] = 8;
		$fields['phone']['required'] = TRUE;
		
		// Billing address fields
		$fields['label_business_address'] = array(
			'weight' => 60,
			'label' => '<h3>'.self::__( 'Business Address' ). '</h3>',
			'type' => 'hidden',
		);
		$fields['copy_address'] = array(
			'weight' => 60.1,
			'label' => self::__( 'Same As Mailing Address' ),
			'type' => 'checkbox',
			'required' => FALSE
		);
		
		$fields['business_street'] = array(
			'weight' => 61,
			'label' => self::__( 'Street Address' ),
			'type' => 'textarea',
			'rows' => 2,
			'required' => TRUE,
			'default' => self::get_meta_value( $merchant_id, 'business_street' )
		);
		$fields['business_city'] = array(
			'weight' => 62,
			'label' => self::__( 'City' ),
			'type' => 'text',
			'required' => TRUE,
			'default' => self::get_meta_value( $merchant_id, 'business_city' ),
		);
		$fields['business_zone'] = array(
			'weight' => 63,
			'label' => self::__( 'State' ),
			'type' => 'select-state',
			'options' => Group_Buying_Controller::get_state_options( array( 'include_option_none' => ' -- '.self::__( 'Select a State' ).' -- ' ) ),
			'default' => self::get_meta_value( $merchant_id, 'business_zone' ),
		); // TODO: 3.x Add some JavaScript to switch between select box/text-field depending on country

		$fields['business_postal_code'] = array(
			'weight' => 64,
			'label' => self::__( 'ZIP Code' ),
			'type' => 'text',
			'required' => TRUE,
			'default' => self::get_meta_value( $merchant_id, 'business_postal_code' ),
		);
		$fields['business_country'] = array(
			'weight' => 65,
			'label' => self::__( 'Country' ),
			'type' => 'select',
			'required' => TRUE,
			'options' => Group_Buying_Controller::get_country_options( array( 'include_option_none' => ' -- '.self::__( 'Select a Country' ).' -- ' ) ),
			'default' => self::get_meta_value( $merchant_id, 'business_country' ),
		);
		
		$link_terms = site_url('/merchant-account-terms-and-conditions/');
		$fields['agree_terms'] = array(
			'weight' => 99,
			'label' => sprintf(self::__( 'I have read and agree to the <a href="%s" target="_blank">Merchant Account Terms and Conditions</a>' ), $link_terms),
			'type' => 'checkbox',
			'required' => TRUE,
			'value' => 'yes',
			'default' => self::get_meta_value( $merchant_id, 'agree_terms' ),
		);
		
		
		//Add Locations to merchant form
		/*
		$site_locations = get_terms( array( Group_Buying_Deal::LOCATION_TAXONOMY ), array( 'hide_empty'=>FALSE, 'fields'=>'all' ) );
		$location_options = array();
		foreach ( $site_locations as $site_local ) {
			$location_options[$site_local->term_id] = $site_local->name;
		}
		$fields['locations'] = array(
			'weight' => 52,
			'label' => self::__( 'Locations' ),
			'type' => 'multiselect',
			'required' => FALSE,
			'options' => $location_options,
			'default' => wp_get_post_terms($merchant_id, Group_Buying_Deal::LOCATION_TAXONOMY, array("fields" => "ids")),
			'description' => gb__('Locations this merchant will be listing deals for.')
		);
		*/
		
		return $fields;
	}
	
	public static function custom_add_locations_taxonomy() {
		register_taxonomy_for_object_type( Group_Buying_Deal::LOCATION_TAXONOMY, 'gb_merchant' );
	}
	
	public static function custom_query_deals_only_locations( $query ) {

		// if we're not in the Dashboard & this is a location taxonomy archive
		if( !is_admin() && is_tax(Group_Buying_Deal::LOCATION_TAXONOMY) && $query->is_main_query() ) {
			$query->set('post_type', Group_Buying_Deal::POST_TYPE);
			
		}
		return $query;
	}

	
	public function add_scripts_footer() {
		?>
        <script type="text/javascript">
		jQuery( document ).ready(function($) {
		/**
		 * copy billing to shipping
		 */
		var $copy_address_option = $('#gb_contact_copy_address');
		var $business_option_cache = {}; // cache options
		$copy_address_option.bind( 'change', function() {
			// Loop over all gb_shipping options, unknowingly what is actually set because of customizations.
			$('#gb_merchant_register [name^="gb_contact_business_"]').each(function () {
				if ( $( $copy_address_option ).is(':checked') ) {
					var $address_name = this.name.replace('gb_contact_business_', 'gb_contact_'); // Search for a matching field
					$business_option_cache[this.name] = $(this).val(); // Cache the original option so it can be used later.
					$( this ).val( $( '[name="' + $address_name + '"]' ).val() ); // set the value
				}
				else {
					$( this ).val( $business_option_cache[this.name] );
				};
			});
		});
		});
			
		
		</script>
        
        <?php	
	}
    
}
SF_GBS_Merchant_Fields::init();