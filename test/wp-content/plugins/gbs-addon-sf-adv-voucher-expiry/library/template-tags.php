<?php 

function gb_get_adv_voucher_expiry_date( $voucher_id = 0, $format = NULL ) {
	if ( !$voucher_id ) {
		global $post;
		$voucher_id = $post->ID;
	}
	if ( !$voucher_id ) {
		return '';
	}
	if (empty($format)) {
		$format = get_option('date_format', 'Y-m-d');
	}
	//Expiration is filtered function gb_get_voucher_expiration_date
	$expiration = gb_get_voucher_expiration_date($voucher_id);
	$expiration = ( $expiration != '' ) ? date($format,$expiration) : '';
	return apply_filters('gb_get_adv_voucher_expiry_date', $expiration);
}
function gb_get_adv_voucher_expiry_valid_range( $voucher_id = 0, $range_sep = '-', $format = NULL ) {
	if ( !$voucher_id ) {
		global $post;
		$voucher_id = $post->ID;
	}
	if ( !$voucher_id ) {
		return '';
	}
	if (empty($format)) {
		$format = get_option('date_format', 'Y-m-d');
	}

	$voucher = Group_Buying_Voucher::get_instance($voucher_id);
	$deal = $voucher->get_deal();
	
	//Expiration is filtered function gb_get_voucher_expiration_date
	$expiration = gb_get_voucher_expiration_date($voucher_id);
	
	//Start time (purchase time)
	$purchased_date_timestamp = get_the_time('U', $voucher_id);
	
	//If Advanced Voucher Expiry is set
	if ( GBS_SF_AdvVoucherExpiry_Addon::get_expiry_onoff($deal) == 'on' && GBS_SF_AdvVoucherExpiry_Addon::get_expiry_count($deal) != '') {
		
		$voucher_expiration_days = floatval(GBS_SF_AdvVoucherExpiry_Addon::get_expiry_count($deal));
		$new_expiration = $purchased_date_timestamp + ($voucher_expiration_days  * 86400);
		
		$start_date = ( $purchased_date_timestamp != '' ) ? date($format,$purchased_date_timestamp) : '';
		$end_date = ( $new_expiration != '' ) ? date($format,$new_expiration) : '';		
		
		$range = $start_date." ".$range_sep." ".$end_date;
	} else {
		if ($expiration != '') {
			$start_date = ( $purchased_date_timestamp != '' ) ? date($format,$purchased_date_timestamp) : '';
			$end_date = ( $expiration != '' ) ? date($format,$expiration) : '';
			$range = $start_date." ".$range_sep." ".$end_date;
		} else {
			$range = '';
		}
	}
	return apply_filters('gb_get_adv_voucher_expiry_valid_range', $range);
}