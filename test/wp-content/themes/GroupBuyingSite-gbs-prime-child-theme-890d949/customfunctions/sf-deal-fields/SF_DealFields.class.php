<?php 
// SF_Deal_Fields
// By StudioFidelis.com
class SF_Deal_Fields extends Group_Buying_Controller {

	const DEBUG = TRUE;
	
	private static $meta_keys = array(
		'start_date' => '_custom_start_date', // string
		'voucher_expiration_comments' => '_voucher_expiration_comments', // string
		'agree_terms' => '_custom_agree_terms', // string
		'agree_reviewed_information' => '_custom_agree_reviewed_information', // string
		'deal_location' => '_custom_deal_location', // string
	); // A list of meta keys this class cares about. Try to keep them in alphabetical order.
	
	public static function init() {

		// Meta Boxes
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'));
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		
		//Deal submission fields changes
		add_filter('gb_deal_submission_fields', array( get_class(), 'custom_gb_deal_submission_fields'), 10, 2);
		add_action('submit_deal', array( get_class(), 'custom_submit_deal'), 10, 1);
		
		//Change voucher expiration date to expiration comments
		add_filter('gb_voucher_expiration_date', array( get_class(), 'custom_gb_voucher_expiration_date'), 10, 2);
		add_filter('gb_get_voucher_expiration_date', array( get_class(), 'custom_gb_voucher_expiration_date'), 10, 2);
		
	}
	
	public function custom_gb_voucher_expiration_date( $date, $voucher_id ) {
		//Get deal
		$deal_id = gb_get_vouchers_deal_id( $voucher_id );
		
		//Only change if there is an expriation comment
		$expiration_comments = self::get_field($deal_id, self::$meta_keys['voucher_expiration_comments']);
		
		if ( !empty($expiration_comments) )  {
			return $expiration_comments;	
		}
		return $date;
	}
	
	//Deal submission fields changes
	public function custom_gb_deal_submission_fields( $fields, $deal ) {
		
		$deal_id = ($deal) ? $deal->get_ID() : '';
		
		
		//Add start date and checkbox option
		$js_functions = '<script type="text/javascript">
			jQuery(\'#gb_deal_start_date\').datetimepicker({minDate: 0, maxDate: "+90D"});
			jQuery(\'#gb_deal_start_date\').change(function(){ 
				jQuery(\'#gb_deal_start_date_now\').attr(\'checked\', false); 
			}); 
			jQuery(\'#gb_deal_start_date_now\').change(function(){ 
				if (jQuery(this).attr(\'checked\') ) { 
					jQuery(\'#gb_deal_start_date\').val(0); 
				} 
			});</script>';
		
		$fields['start_date'] = array(
			'weight' => 4,
			'label' => gb__( 'Deal Start Date' ),
			'type' => 'text',
			'required' => FALSE,
			'default' => ($_POST['gb_deal_start_date']) ? $_POST['gb_deal_start_date'] : self::get_field($deal_id, self::$meta_keys['start_date']),
			'description' => 'Choose the date your deal with start running ( No more than 90 days in advance ). You can also choose Publish now.<br><input type="checkbox" id="gb_deal_start_date_now"> Publish now ( as soon as approved ) '.str_replace(array("\r\n", "\r"), "\n", $js_functions)
		);
		
		//Remove voucher codes
		unset( $fields['voucher_serial_numbers'] );
		
		//Unset new media_uploader image
		unset( $fields['images'] );
		$fields['thumbnail'] = array(
			'weight' => 3,
			'label' => gb__( 'Deal Image' ),
			'type' => 'file',
			'required' => FALSE,
			'default' => '',
			'description' => gb__('<span>Optional:</span> Featured image for the deal.')
		);
		
		//Add No expiration date checkbox option
		$js_functions = '<script type="text/javascript">
			jQuery(\'#gb_deal_exp\').change(function(){ 
				jQuery(\'#gb_deal_exp_none\').attr(\'checked\', false); 
			}); 
			jQuery(\'#gb_deal_exp_none\').change(function(){ 
				if (jQuery(this).attr(\'checked\') ) { 
					jQuery(\'#gb_deal_exp\').val(0); 
				} 
			});</script>';
		
		$fields['exp']['description'] = 'When your deal will expire on the site and no longer be available for purchase - does not represent when the voucher expires.<br><input type="checkbox" id="gb_deal_exp_none"> Deal does not expire ( Deals can cancelled or changed at anytime ) '.str_replace(array("\r\n", "\r"), "\n", $js_functions);
		
		//Add new voucher expriation comments field
		unset($fields['voucher_expiration']);
		//if (isset($fields['voucher_expiration']) ) $fields['voucher_expiration']['weight'] = 52;
		$fields['voucher_expiration_comments'] = array(
			'weight' => 53,
			'label' => self::__( 'Voucher Expiration Comments' ),
			'type' => 'textarea',
			'required' => FALSE,
			'default' => ($_POST['gb_deal_voucher_expiration_comments']) ? $_POST['gb_deal_voucher_expiration_comments'] : self::get_field($deal_id, self::$meta_keys['voucher_expiration_comments']),
			'description' => gb__('All Vouchers Expire 6 Months After Purchase Unless Otherwise Specified Here.')
		);
		
		//Change labels
		$fields['title']['description'] = gb__('<span>Required:</span> Advertised Title of Deal; Example: $25 gift certificate for $10 Towards Lunch for 2 at Pizzeria.');
		$fields['locations']['description'] = gb__('Press CTRL to select multiple locations.');
		$fields['highlights']['description'] = gb__('<span>Required:</span> Main Highlights About Deal; Example: $25 gift certificate for $10.');
		$fields['fine_print']['description'] = gb__('<span>Required:</span> Fine Print of This Deal and Voucher; Example: Can only be used towards lunch for a party of 2 or more.');
		$fields['voucher_how_to_use']['description'] = gb__('<span>Required:</span> How to Redeem the Voucher; Example: Bring printed copy into restaurant and give to waitress before ordering to receive discount.');
		
		$fields['thumbnail']['description'] = gb__('Please Upload an Image That Represents Your Deal. For Example: A deal for lunch at a deli would have a picture of a deli sandwich. Every image is left to our discretion. If you do not upload an image or if your image is not approved, we will use a stock image.');
		
		$fields['max_purchases']['description'] =  gb__('<span>Optional:</span> Maximum number of purchases allowed for this deal.');
		$fields['max_per_user']['description'] =  gb__('Optional: Maximum number of purchases allowed for this deal for one user per 6 weeks. After 6 weeks, the Max Purchases Per User will reset.');
		
		$fields['agree_reviewed_information'] = array(
			'weight' => 200,
			'label' => sprintf(self::__( 'I have reviewed the information I submitted and hereby certify that the statements and information in this form are true and correct to the best of my knowledge and belief, and I authorize <a href="http://www.LocalSharingTree.com" target="_blank">www.LocalSharingTree.com</a> to post this deal and any attachments submitted with it on their site.' ), $link_terms),
			'type' => 'checkbox',
			'required' => TRUE,
			'value' => 'yes',
			'default' => ($_POST['gb_deal_agree_reviewed_information']) ? $_POST['gb_deal_agree_reviewed_information'] : self::get_field($deal_id, self::$meta_keys['agree_reviewed_information']),
		);
		
		$link_terms = site_url('/merchant-account-terms-and-conditions/');
		$fields['agree_terms'] = array(
			'weight' => 201,
			'label' => sprintf(self::__( 'I have read and agree to the <a href="%s" target="_blank">Merchant Account Terms and Conditions</a>' ), $link_terms),
			'type' => 'checkbox',
			'required' => TRUE,
			'value' => 'yes',
			'default' =>($_POST['gb_deal_agree_terms']) ? $_POST['gb_deal_agree_terms'] : self::get_field($deal_id, self::$meta_keys['agree_terms']),
		);
		
		return $fields;
	}
	
	public function custom_submit_deal( $deal ) {
		if ( !$deal ) return;
		
		if ( isset( $_POST['gb_deal_start_date'] ) ) { //notice post field title for deal submit forms
			$deal->save_post_meta( array(
				self::$meta_keys['start_date'] => stripslashes($_POST['gb_deal_start_date'])
			));
		}
		
		if ( isset( $_POST['gb_deal_voucher_expiration_comments'] ) ) { //notice post field title for deal submit forms
			$deal->save_post_meta( array(
				self::$meta_keys['voucher_expiration_comments'] => stripslashes($_POST['gb_deal_voucher_expiration_comments'])
			));
		}
		
		if ( isset( $_POST['gb_deal_agree_terms'] ) ) {
			$deal->save_post_meta( array(
				self::$meta_keys['agree_terms'] => stripslashes($_POST['gb_deal_agree_terms'])
			));
		}
		if ( isset( $_POST['gb_deal_agree_reviewed_information'] ) ) {
			$deal->save_post_meta( array(
				self::$meta_keys['agree_reviewed_information'] => stripslashes($_POST['gb_deal_agree_reviewed_information'])
			));
		}
		
		//Save thumbnail (replacing the new media_uploader)
		if ( !empty( $_FILES['gb_deal_thumbnail'] ) ) {
			// Set the uploaded field as an attachment
			$deal->set_attachement( $_FILES, 'gb_deal_thumbnail' );
		}
	}

	
	/**
	 * @return int Meta boxes
	 */
	
	public static function add_meta_boxes() {
		add_meta_box('sf_add_custom_fields', 'Custom Fields', array(get_class(), 'show_meta_boxes'), Group_Buying_Deal::POST_TYPE, 'advanced', 'default');
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'sf_add_custom_fields':
				self::show_meta_box($post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}

	private static function show_meta_box( $post, $metabox ) {
		
		?>
        	<p>
                <label for="<?php echo self::$meta_keys['deal_location'] ?>"><?php gb_e( 'Deal Display Location (Online, Multiple or name of the location) ' ); ?></label><br />
                <input style="width:98%;" name="<?php echo self::$meta_keys['deal_location'] ?>" id="<?php echo self::$meta_keys['deal_location'] ?>" value="<?php echo esc_attr_e( self::get_field($post->ID, self::$meta_keys['deal_location'])) ?>">
                <em>Note: This information appears on the Deals list pages in the upper right of each deal.</em>
            </p>   
			<p>
                <label for="<?php echo self::$meta_keys['voucher_expiration_comments'] ?>"><?php gb_e( 'Voucher Expiration Comments' ); ?></label><br />
                <textarea style="width:98%;" name="<?php echo self::$meta_keys['voucher_expiration_comments'] ?>" id="<?php echo self::$meta_keys['voucher_expiration_comments'] ?>"><?php echo esc_attr_e( self::get_field($post->ID, self::$meta_keys['voucher_expiration_comments'])) ?></textarea>
            </p>        
			<p>
                <label for="<?php echo self::$meta_keys['start_date'] ?>"><?php gb_e( 'Deal Start Date (requested by merchant)' ); ?></label><br />
                <input style="width:98%;" name="<?php echo self::$meta_keys['start_date'] ?>" id="<?php echo self::$meta_keys['start_date'] ?>" value="<?php echo esc_attr_e( self::get_field($post->ID, self::$meta_keys['start_date'])) ?>">
                <em>Note: If "0", then merchant requests to Publish Now.</em>
           </p>
           
		<?php
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
		if (empty($_POST)) {
			return;	
		}
		self::save_meta_box($post_id, $post);
	}

	private static function save_meta_box( $post_id, $post ) {
		
		$deal = Group_Buying_Deal::get_instance($post_id);
		
		if ( isset( $_POST[self::$meta_keys['start_date']] ) ) {
			$deal->save_post_meta( array(
				self::$meta_keys['start_date'] => stripslashes($_POST[self::$meta_keys['start_date']])
			));
		}
		
		if ( isset( $_POST[self::$meta_keys['voucher_expiration_comments']] ) ) {
			$deal->save_post_meta( array(
				self::$meta_keys['voucher_expiration_comments'] => stripslashes($_POST[self::$meta_keys['voucher_expiration_comments']])
			));
		}
		if ( isset( $_POST[self::$meta_keys['agree_terms']] ) ) {
			$deal->save_post_meta( array(
				self::$meta_keys['agree_terms'] => stripslashes($_POST[self::$meta_keys['agree_terms']])
			));
		}
		if ( isset( $_POST[self::$meta_keys['agree_reviewed_information']] ) ) {
			$deal->save_post_meta( array(
				self::$meta_keys['agree_reviewed_information'] => stripslashes($_POST[self::$meta_keys['agree_reviewed_information']])
			));
		}
		if ( isset( $_POST[self::$meta_keys['deal_location']] ) ) {
			$deal->save_post_meta( array(
				self::$meta_keys['deal_location'] => stripslashes($_POST[self::$meta_keys['deal_location']])
			));
		}
		
	}

	public static function get_field( $deal_id, $meta_key = '' ) {
		$value = '';
		if ($deal_id && $meta_key) {
			$deal = Group_Buying_Deal::get_instance($deal_id);
			$value = $deal->get_post_meta( $meta_key, true );
		}
		return $value;
	}
    
	// Public function to get field
	public static function get_sf_custom_deal_field( $deal_id, $field_key = '' ) {
		if (!$deal_id) {
			global $post;
			$deal_id = $post->ID;		
		}
		//Get the meta_key from the field key sent
		$meta_key = self::$meta_keys[$field_key];
		$value = '';
		if ($deal_id && $meta_key) {
			$deal = Group_Buying_Deal::get_instance($deal_id);
			if ($deal) {
				$value = $deal->get_post_meta( $meta_key, true );
			}
		}
		return $value;
	}

}
SF_Deal_Fields::init();