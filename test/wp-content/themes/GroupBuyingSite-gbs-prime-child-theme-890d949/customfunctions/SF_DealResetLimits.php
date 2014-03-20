<?php

/**
 * Reset Deal Limits on selected deals
 * By StudioFidelis.com
 */
 
class SF_DealResetLimits extends Group_Buying_Controller {
	
	const DEBUG = GBS_DEV; // Debug for troubleshooting - Change to: GBS_DEV
	
	const META_RESET = '_sf_deal_reset_limits';
	const META_RESET_NOTIFY = '_sf_deal_reset_limits_notification';
	
	public static function init() {
		
		// Meta Boxes
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'));
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		
		//Filter can purchase
		add_filter('account_can_purchase', array( get_class(), 'filter_sf_reset_can_purchase'), 99, 4);
	}
	
	// Rebuild can_purchase
	public function filter_sf_reset_can_purchase( $qty, $deal_id, $data, $account  ) {
		//Important, recount purchaes regardless of reset to properly handle if amount of purchaes is more than the max per user ( if user turned OFF reset after it being on).
		
		//Get reset
		$reset = get_post_meta($deal_id, self::META_RESET, TRUE);
		
		$qty = Group_Buying_Account::NO_MAXIMUM;
		$deal = Group_Buying_Deal::get_instance( $deal_id );
		if ( !is_a( $deal, 'Group_Buying_Deal' ) ) {
			return $qty;
		}
		if ( $deal->is_closed() ) {
			return $qty;
		}
		
		//Get the last reset date
		$last_reset_timestamp = self::last_reset_timestamp($reset, $deal_id);
		
		$max_purchases_per_user = $deal->get_max_purchases_per_user();
		$exceeded_qty_max_purchases_per_user = FALSE;
		if ( $max_purchases_per_user >= 0 ) {
			$total_purchased = 0;
			$purchases = $deal->get_purchases_by_account( $account->get_ID() );
			
			if ( $purchases ) {
				foreach ( $purchases as $purchase_id ) {
					$purchase = Group_Buying_Purchase::get_instance( $purchase_id );
					
					//Only count if purchase was since last reset
					$gmt_purchased_on = get_post_time( 'U', true, $purchase_id );
					if ( (!$reset || $reset == 'off' ) ||  $gmt_purchased_on >= $last_reset_timestamp ) {
						$number_purchased = $purchase->get_product_quantity( $deal_id );
						$total_purchased += $number_purchased;
					}
					
				}
			}
			$qty = $max_purchases_per_user - $total_purchased;
			if ( $qty < 0 ) {
				$exceeded_qty_max_purchases_per_user = TRUE;	
				$qty = 0; //bug fix
			}
		}
		$remaining = $deal->get_remaining_allowed_purchases();
		if ( $remaining >= 0 && ( $remaining < $qty || $qty == Group_Buying_Account::NO_MAXIMUM ) ) {
			$qty = $remaining;
		}
		return $qty;
	}
	
	// Last Reset date
	public function last_reset_timestamp($reset, $deal_id) {
		
		$gmt_timestamp = get_post_time( 'U', true, $deal_id );
		$gmt_now = time();
		$gmt_time_elapsed = $gmt_now - $gmt_timestamp;
		
		if ( $reset == '42days' ) {
			$each_reset_in_seconds = 42 * 86400; //days X seconds in a day
			//How many reset_days fit into time elapsed
			$reset_periods = floor($gmt_time_elapsed / $each_reset_in_seconds); //always round down
			//Add to start date to find the last period that was reached
			if ( $reset_periods > 0 ) {
				$gmt_timestamp = $gmt_timestamp + ( $reset_periods * $each_reset_in_seconds);
			}
		} 
		return $gmt_timestamp;
	}
	
	// Next (upcoming) Reset date
	public function next_reset_timestamp($reset, $deal_id) {
		
		$gmt_timestamp = get_post_time( 'U', true, $deal_id );
		$gmt_now = time();
		$gmt_time_elapsed = $gmt_now - $gmt_timestamp;
		
		if ( $reset == '42days' ) {
			$each_reset_in_seconds = 42 * 86400; //days X seconds in a day
			//How many reset_days fit into time elapsed
			$reset_periods = ceil($gmt_time_elapsed / $each_reset_in_seconds); //always round UP
			//Add to start date to find the last period that was reached
			if ( $reset_periods > 0 ) {
				$gmt_timestamp = $gmt_timestamp + ( $reset_periods * $each_reset_in_seconds);
			}
		} 
		return $gmt_timestamp;
	}
	
	// NOT USED - Calculate Deal Reset amount for max_purchasers_per_user by the maount of reset periods elapsed - NOT USED
	public function recalculate_qty_allowed_per_user($reset, $deal) {
		
		$max_purchases_per_user = $deal->get_max_purchases_per_user();
		
		$gmt_timestamp = get_post_time( 'U', true, $deal->get_ID() );
		$gmt_now = time();
		$gmt_time_elapsed = $gmt_now - $gmt_timestamp;
		
		if ( $reset == '42days' ) {
			$each_reset_in_seconds = 42 * 86400; //days X seconds in a day
			//How many reset_days fit into time elapsed
			$reset_multiplier = floor($gmt_time_elapsed / $each_reset_in_seconds); //always round down
			//muliply by max_purchases_per_user
			if ( $reset_multiplier > 0 ) {
				$max_purchases_per_user = $max_purchases_per_user * $reset_multiplier;
			}
		} 
		return $max_purchases_per_user;
		
	}
	
	/**
	 * @return int Meta boxes
	 */
	
	public static function add_meta_boxes() {
		add_meta_box('sf_reset_deal_limits', 'Reset Deal Limits', array(get_class(), 'show_meta_boxes'), Group_Buying_Deal::POST_TYPE, 'normal', 'default');
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'sf_reset_deal_limits':
				self::show_meta_box($post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}

	private static function show_meta_box( $post, $metabox ) {
		$reset = get_post_meta($post->ID, self::META_RESET, TRUE);
		?>
		<p>
			<?php gb_e( 'Reset "Maximum Purchases per User"' ); ?> after:<br>
            <label for="<?php echo self::META_RESET; ?>_off"><input type="radio" id="<?php echo self::META_RESET; ?>_off" name="<?php echo self::META_RESET ?>" <?php if ( $reset == '' || $reset == 'off' ) echo 'checked="checked"'; ?> value="off"> Off</label>
            <br><label for="<?php echo self::META_RESET; ?>_42days"><input type="radio" id="<?php echo self::META_RESET; ?>_42days" name="<?php echo self::META_RESET ?>" <?php if ( $reset == '42days' ) echo 'checked="checked"'; ?> value="42days"> 42 days ( 6 weeks )</label>
            <br><em>Resets the purchase limit for a user ("Maximum Purchases per User:") after each period defined above. <br>
            Note: A deal must have both "Maximum Purchases: " and "Maximum Purchases per User: " defined for this to work.</em>
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

		if ( isset( $_POST[self::META_RESET] ) ) {
			$deal->save_post_meta( array(
				self::META_RESET => stripslashes($_POST[self::META_RESET])
			));
		}
		
	}
}
SF_DealResetLimits::init();