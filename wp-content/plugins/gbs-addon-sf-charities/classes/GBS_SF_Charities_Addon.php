<?php

// Start classes for add-on GBS_SF_Charities_Addon
// Based on: GBS_Charities_Addon by Dan Cameron

class GBS_SF_Charities_Addon {
	
	public static function init() {

		// Post Type
		require_once 'GBS_SF_Charity_Post_Type.php';
		GB_SF_Charity::init();

		// Controller
		require_once 'GBS_SF_Charities.php';
		GB_SF_Charities::init();

		// Checkout
		require_once 'GBS_SF_Charity_Checkout.php';
		GB_SF_Charities_Checkout::init();

		// Reports
		require_once 'GBS_SF_Charity_Reports.php';
		GBS_SF_Charity_Reports::init();

		// Template tags
		require_once GB_SF_CHARITY_PATH . '/library/template-tags.php';
	}

	public static function init_purchase() {
		// Cart
		require_once 'GBS_SF_Charity_Cart.php';
		GBS_SF_Charity_Cart::init();
	}

	public static function gb_addon( $addons ) {
		$addons['gbs_sf_charities'] = array(
			'label' => gb__( 'GBS Charities' ),
			'description' => gb__( 'Charities - user selects charity to recieve donation at checkout.' ),
			'files' => array(),
			'callbacks' => array(
				array( __CLASS__, 'init' ),
			)
		);
		/*
		// DISABLED
		$addons['gbs_sf_charities_variable'] = array(
			'label' => gb__( 'GBS Charities: Variable Donation' ),
			'description' => gb__( 'Allow for customer to choose donation.' ),
			'files' => array(),
			'callbacks' => array(
				array( __CLASS__, 'init_purchase' ),
			)
		);
		*/
		return $addons;
	}
}
