<?php
/*
Plugin Name: GBS - Charities (GBS Add-on)
Version: 1.0
Plugin URI: http://studiofidelis.com
Description: Advanced Charity functionality.
Plugin Author StudioFidelis.com
Based on Plugin Dynamic Charities by Author: Dan Cameron
Text Domain: group-buying
*/

define ('GB_SF_CHARITY_URL', plugins_url( '', __FILE__) );
define( 'GB_SF_CHARITY_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );

// Load after all other plugins since we need to be compatible with groupbuyingsite
add_action( 'plugins_loaded', 'gb_load_sf_charities_addon' );
function gb_load_sf_charities_addon() {
	
	$gbs_min_version = '4.3';
	if ( class_exists( 'Group_Buying_Controller' ) && version_compare( Group_Buying::GB_VERSION, $gbs_min_version, '>=' ) ) {
		require_once 'classes/GBS_SF_Charities_Addon.php';

		// Hook this plugin into the GBS add-ons controller
		add_filter( 'gb_addons', array( 'GBS_SF_Charities_Addon', 'gb_addon' ), 10, 1 );
	}
}