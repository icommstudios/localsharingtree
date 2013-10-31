<?php 
/* 
Plugin Name: GBS - Adv. Voucher Expiry (GBS Addon)
Plugin URI: http://www.studiofidelis.com
Description: Advanced Voucher Expiry for GBS. Expire Voucher by # days after purchase.
Author: StudioFidelis.com / Daniel Schuring 
Version: 1.3
Author URI: http://www.studiofidelis.com
*/ 

// Load after all other plugins since we need to be compatible with GroupBuyingSite.com
add_action('plugins_loaded', 'gb_load_advvoucherexpiry_addon');
function gb_load_advvoucherexpiry_addon() {
	if (class_exists('Group_Buying_Controller')) {
		require_once('SF_AdvancedVoucherExpiry.class.php');
		Group_Buying_SF_AdvVoucherExpiry_Addon::init();
	}
}