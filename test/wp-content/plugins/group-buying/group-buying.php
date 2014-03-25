<?php
/*
Plugin Name: Group Buying Plugin
Version: 4.7.1.1
Plugin URI: http://groupbuyingsite.com/feature-tour/
Description: Allows for groupon like functionality. By installing this plugin you agree to the <a href="http://groupbuyingsite.com/tos/" title="I agree">terms and conditions</a> of GroupBuyingSite.
Author: GroupBuyingSite.com
Author URI: http://groupbuyingsite.com/
Plugin Author: Dan Cameron
Plugin Author URI: http://sproutventure.com/
Contributors: Dan Cameron, Jonathan Brinley & Nathan Stryker
Text Domain: group-buying
Domain Path: /lang
*/


/**
 * GBS directory
 */
define( 'GB_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );
/**
 * GB URL
 */
define( 'GB_URL', plugins_url( '', __FILE__ ) );
/**
 * URL to resources directory
 */
define( 'GB_RESOURCES', plugins_url( 'resources/', __FILE__ ) );
/**
 * Minimum supported version of WordPress
 */
define( 'GBS_SUPPORTED_WP_VERSION', version_compare( get_bloginfo( 'version' ), '3.4', '>=' ) );
/**
 * Minimum supported version of PHP
 */
define( 'GBS_SUPPORTED_PHP_VERSION', version_compare( phpversion(), '5.2.4', '>=' ) );

/**
 * Compatibility check
 */
if ( GBS_SUPPORTED_WP_VERSION && GBS_SUPPORTED_PHP_VERSION ) {
	group_buying_load();
	do_action( 'group_buying_load' );
} else {
	/**
	 * Disable GBS and add fail notices if compatibility check fails
 	 * @package GBS
 	 * @subpackage Base
	 * @return string inserted within the WP dashboard
	 */
	gb_deactivate_plugin();
	add_action( 'admin_head', 'gbs_fail_notices' );
	function gbs_fail_notices() {
		if ( !GBS_SUPPORTED_WP_VERSION ) {
			echo '<div class="error"><p><strong>Group Buying Plugin</strong> requires WordPress 3.3 or higher. Please upgrade WordPress and activate the Group Buying Plugin again.</p></div>';
		}
		if ( !GBS_SUPPORTED_PHP_VERSION ) {
			echo '<div class="error"><p><strong>Group Buying Plugin</strong> requires PHP 5.2.4 or higher. Talk to your web host about using a secure version of PHP and activate the Group Buying Plugin after they upgrade your server.</p></div>';
		}
	}
}

/**
 * Load the GBS application
 * @package GBS
 * @subpackage Base
 * @return void
 */
function group_buying_load() {
	if ( class_exists( 'Group_Buying' ) ) {
		gb_deactivate_plugin();
		return; // already loaded, or a name collision
	}
	//////////////////////////////
	// router plugin dependency //
	//////////////////////////////
	require_once GB_PATH.'/controllers/router/gb-router.php';

	//////////////////
	// base classes //
	//////////////////
	require_once GB_PATH.'/Group_Buying.class.php';
	require_once GB_PATH.'/models/Group_Buying_Model.class.php';
	require_once GB_PATH.'/models/Group_Buying_Post_Type.class.php';
	require_once GB_PATH.'/controllers/Group_Buying_Controller.class.php';
	require_once GB_PATH.'/controllers/payment-processing/Payment_Processors.class.php';
	require_once GB_PATH.'/controllers/payment-processing/Offsite_Processors.class.php';
	require_once GB_PATH.'/controllers/payment-processing/Credit_Card_Processors.class.php';
	require_once GB_PATH.'/controllers/payment-processing/Hybrid_Payment_Processor.class.php';

	////////////
	// models //
	////////////
	require_once GB_PATH.'/models/Deal.class.php';
	require_once GB_PATH.'/models/Account.class.php';
	require_once GB_PATH.'/models/Cart.class.php';
	// require_once GB_PATH.'/models/Gift.class.php'; // Made an add-on in 4.6
	require_once GB_PATH.'/models/Merchant.class.php';
	require_once GB_PATH.'/models/Notification.class.php';
	require_once GB_PATH.'/models/Payment.class.php';
	require_once GB_PATH.'/models/Purchase.class.php';
	require_once GB_PATH.'/models/Record.class.php';
	require_once GB_PATH.'/models/Report.class.php';
	require_once GB_PATH.'/models/Voucher.class.php';

	/////////////////
	// controllers //
	/////////////////

	// accounts
	require_once GB_PATH.'/controllers/accounts/Accounts.class.php';
	require_once GB_PATH.'/controllers/accounts/Accounts_Checkout.class.php';
	require_once GB_PATH.'/controllers/accounts/Accounts_Edit_Profile.class.php';
	require_once GB_PATH.'/controllers/accounts/Accounts_Login.class.php';
	require_once GB_PATH.'/controllers/accounts/Accounts_Registration.class.php';
	require_once GB_PATH.'/controllers/accounts/Accounts_Retrieve_Password.class.php';
	require_once GB_PATH.'/controllers/accounts/Accounts_Upgrade.class.php';

	// admin
	require_once GB_PATH.'/controllers/admin/Admin_Settings.class.php'; // v. 3.7
	require_once GB_PATH.'/controllers/admin/Admin_Purchases.class.php';
	require_once GB_PATH.'/controllers/admin/Destroyer.class.php'; // v. 3.9
	require_once GB_PATH.'/controllers/admin/Help.class.php'; // v. 3.4

	// affiliates
	require_once GB_PATH.'/controllers/affiliates/Affiliates.class.php';

	// carts
	require_once GB_PATH.'/controllers/carts/Carts.class.php';

	// checkouts
	require_once GB_PATH.'/controllers/checkouts/Checkouts.class.php';

	// deals
	require_once GB_PATH.'/controllers/deals/Deals.class.php';
	require_once GB_PATH.'/controllers/deals/Deals_Edit.class.php';
	require_once GB_PATH.'/controllers/deals/Deals_Preview.class.php';
	require_once GB_PATH.'/controllers/deals/Deals_Submit.class.php';
	require_once GB_PATH.'/controllers/deals/Deals_Upgrade.class.php';

	// dev
	require_once GB_PATH.'/controllers/developer/Dev_Logs.class.php'; // v. 4.6

	// feeds
	require_once GB_PATH.'/controllers/feeds/Feeds.class.php';

	// gifts
	// require_once GB_PATH.'/controllers/Gifts.class.php'; // Made an add-on in 4.6

	// merchants
	require_once GB_PATH.'/controllers/merchants/Merchants.class.php';
	require_once GB_PATH.'/controllers/merchants/Merchants_Dashboard.class.php';
	require_once GB_PATH.'/controllers/merchants/Merchants_Edit.class.php';
	require_once GB_PATH.'/controllers/merchants/Merchants_Registration.class.php';
	require_once GB_PATH.'/controllers/merchants/Merchants_Upgrade.class.php';
	require_once GB_PATH.'/controllers/merchants/Merchants_Voucher_Claim.class.php';

	// notifications
	require_once GB_PATH.'/controllers/notifications/Notifications.class.php';

	// payments
	require_once GB_PATH.'/controllers/payments/Payments.class.php';

	// purchases
	require_once GB_PATH.'/controllers/purchases/Purchases.class.php';

	// reports
	require_once GB_PATH.'/controllers/reports/Reports.class.php';

	// records
	require_once GB_PATH.'/controllers/records/Records.class.php';
	require_once GB_PATH.'/controllers/records/Records_Upgrade.class.php';

	// shipping
	require_once GB_PATH.'/controllers/shipping/Shipping.class.php'; // v. 3.4

	// tax
	require_once GB_PATH.'/controllers/tax/Tax.class.php'; // v. 3.4

	// ui
	require_once GB_PATH.'/controllers/Group_Buying_UI.class.php';

	// updates
	require_once GB_PATH.'/controllers/updates/Update_Check.class.php';

	// upgrades
	require_once GB_PATH.'/controllers/upgrades/Upgrades.class.php';

	// vouchers
	require_once GB_PATH.'/controllers/vouchers/Vouchers.class.php';

	// widgets
	require_once GB_PATH.'/controllers/widgets/Widgets.class.php';

	// add-ons
	require_once GB_PATH.'/add-ons/Addons.class.php';

	////////////////////////
	// payment processors //
	////////////////////////

	// balance
	require_once GB_PATH.'/controllers/payment-processing/payment-processors/Account_Balance_Payments.class.php';
	require_once GB_PATH.'/controllers/payment-processing/payment-processors/Affiliate_Credit_Payments.class.php';
	// offsite
	require_once GB_PATH.'/controllers/payment-processing/payment-processors/Paypal.class.php';
	require_once GB_PATH.'/controllers/payment-processing/payment-processors/Paypal_AP.class.php';
	// credit cards
	require_once GB_PATH.'/controllers/payment-processing/payment-processors/Paypal_WPP.class.php';
	require_once GB_PATH.'/controllers/payment-processing/payment-processors/Authorize_Net.class.php';
	require_once GB_PATH.'/controllers/payment-processing/payment-processors/NMI.class.php';
	do_action( 'gb_register_processors' );

	///////////////////
	// template tags //
	///////////////////

	require_once GB_PATH.'/template-tags/account.php';
	require_once GB_PATH.'/template-tags/affiliate.php';
	require_once GB_PATH.'/template-tags/cart.php';
	require_once GB_PATH.'/template-tags/checkout.php';
	require_once GB_PATH.'/template-tags/deals.php';
	require_once GB_PATH.'/template-tags/deprecated.php';
	require_once GB_PATH.'/template-tags/forms.php';
	require_once GB_PATH.'/template-tags/location.php';
	require_once GB_PATH.'/template-tags/merchant.php';
	require_once GB_PATH.'/template-tags/payment.php';
	require_once GB_PATH.'/template-tags/reports.php';
	require_once GB_PATH.'/template-tags/ui.php';
	require_once GB_PATH.'/template-tags/utility.php';
	require_once GB_PATH.'/template-tags/voucher.php';
	// require_once GB_PATH.'/template-tags/gifts.php'; // Made an add-on in 4.6

	//////////////////////////
	// syndication service //
	//////////////////////////

	// require_once GB_PATH.'/controllers/syndication-service/group-buying-aggregator.php'; // Made an add-on in 4.6

	////////////
	// admin //
	////////////

	require_once GB_PATH.'/controllers/admin/tables/Accounts_Table.class.php';
	require_once GB_PATH.'/controllers/admin/tables/Gifts_Table.class.php';
	require_once GB_PATH.'/controllers/admin/tables/Notifications_Table.class.php';
	require_once GB_PATH.'/controllers/admin/tables/Payments_Table.class.php';
	require_once GB_PATH.'/controllers/admin/tables/Purchases_Table.class.php';
	require_once GB_PATH.'/controllers/admin/tables/Records_Table.class.php';
	require_once GB_PATH.'/controllers/admin/tables/Vouchers_Table.class.php';


	/////////////////////////
	// initialize objects //
	/////////////////////////


	// development classes load last
	Group_Buying_Record::init();
	Group_Buying_Records::init();
	Group_Buying_Dev_Logs::init();
	
	// models
	Group_Buying_Post_Type::init(); // initialize query caching
	Group_Buying_Deal::init();
	Group_Buying_Account::init();
	Group_Buying_Cart::init();
	// Group_Buying_Gift::init();  // Made an add-on in 4.6
	Group_Buying_Merchant::init();
	Group_Buying_Notification::init();
	Group_Buying_Payment::init();
	Group_Buying_Purchase::init();
	// Group_Buying_Report::init(); // nothing to initiate
	Group_Buying_Voucher::init();


	// controllers
	Group_Buying_Update_Check::init();
	Group_Buying_Controller::init();
	GB_Admin_Settings::init();
	Group_Buying_Deals::init();
		Group_Buying_Deals_Submit::init();
		Group_Buying_Deals_Preview::init();
	Group_Buying_Accounts::init();
		Group_Buying_Accounts_Login::init();
		Group_Buying_Accounts_Registration::init();
		Group_Buying_Accounts_Edit_Profile::init();
		Group_Buying_Accounts_Retrieve_Password::init();
		Group_Buying_Accounts_Checkout::init();
	Group_Buying_Merchants::init();
		Group_Buying_Merchants_Registration::init();
		Group_Buying_Merchants_Edit::init();
		Group_Buying_Merchants_Dashboard::init();
		Group_Buying_Merchants_Voucher_Claim::init();
	Group_Buying_Carts::init();
	Group_Buying_Checkouts::init();
	Group_Buying_Notifications::init();
	Group_Buying_Vouchers::init();
	// Group_Buying_Gifts::init(); // Made an add-on in 4.6
	Group_Buying_Offsite_Processors::init();
	Group_Buying_Payment_Processors::init();
	Group_Buying_Purchases::init();
	Group_Buying_Payments::init();
	Group_Buying_Reports::init();
	Group_Buying_Core_Shipping::init();
	Group_Buying_Core_Tax::init();
	Group_Buying_UI::init();
	Group_Buying_Upgrades::init();
	Group_Buying_Affiliates::init();
	Group_Buying_Admin_Purchases::init();
	Group_Buying_Addons::init();
	Group_Buying_Feeds::init();
	Group_Buying_Help::init();
	Group_Buying_Destroy::init();
	Group_Buying_Widgets::init();
}

/**
 * do_action when plugin is activated.
 * @package GBS
 * @subpackage Base
 * @ignore
 */
register_activation_hook( __FILE__, 'gb_plugin_activated' );
function gb_plugin_activated() {
	do_action( 'gb_plugin_activation_hook' );
}
/**
 * do_action when plugin is deactivated.
 * @package GBS
 * @subpackage Base
 * @ignore
 */
register_deactivation_hook( __FILE__, 'gb_plugin_deactivated' );
function gb_plugin_deactivated() {
	do_action( 'gb_plugin_deactivation_hook' );
}

function gb_deactivate_plugin() {
	if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
		require_once ABSPATH.'/wp-admin/includes/plugin.php';
		deactivate_plugins( __FILE__ );
	}
}