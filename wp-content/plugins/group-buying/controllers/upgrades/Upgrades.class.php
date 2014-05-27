<?php

/**
 * Manages upgrades. Example, the major upgrades from version 2.x to version 3.x
 *
 * @package GBS
 * @subpackage Base
 */
class Group_Buying_Upgrades extends Group_Buying_Controller {
	const SETTINGS_PAGE = 'gb_update';
	const FORM_ACTION = 'perform_upgrade';

	public static function get_admin_page( $prefixed = TRUE ) {
		return ( $prefixed ) ? self::TEXT_DOMAIN . '/' . self::SETTINGS_PAGE : self::SETTINGS_PAGE ;
	}

	static function init() {
		add_action( 'init', array( get_class(), 'check_for_upgrade' ), 100, 0 );
	}

	static function check_for_upgrade() {
		// Check whether a version upgrade needs to be applied
		$old_version = self::get_old_version();
		$new_version = self::get_new_version();

		// If old version is 3.0 or greater, automatically upgrade
		// Otherwise, site owner will have to manually choose to perform upgrade
		if ( version_compare( $new_version, $old_version, '>' ) ) {
			if ( version_compare( $old_version, '3.0', '>=' ) ) {
				do_action( 'gb_log', 'upgrade process: old version', $old_version );
				self::upgrade( $old_version, $new_version );
			} else {
				self::add_admin_page();
			}
		} else {
			$current_version = get_option( 'gb_version' );
			if ( false == $current_version ) {
				add_option( 'gb_version', Group_Buying::GB_VERSION );
			}
		}
	}

	static function upgrade( $old_version, $new_version ) {
		$complete = TRUE;
		if ( $old_version != $new_version ) {
			// Give 15 minutes for each upgrade pass
			// This should be more than enough time
			set_time_limit( 15*60 );

			if ( $old_version < '4.6.3' ) {
				$complete = Group_Buying_Records_Upgrade::upgrade_4_6();
			}
		}
		if ( $complete ) {
			error_log( 'complete: ' . print_r( TRUE, TRUE ) );
			update_option( 'gb_version', $new_version );
		}
	}

	static function get_old_version() {

		// If there's a stored version, that's the version of the plugin
		$stored_version = get_option( 'gb_version', false );
		if ( $stored_version ) {
			return $stored_version;
		} else {
			global $wpdb;

			// Version 2.1 stored purchases as 'codes' associated with deals
			$codes_count = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_dealsCodesArray'" );
			if ( $codes_count ) {
				return '2.1';
			}

			// Version 2.3 stored purchases as 'purchase records' associated with deals
			$purchase_count = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_purchaseRecords'" );
			if ( $purchase_count ) {
				return '2.3';
			}

			// If there have been no purchases, but there are old deals, we need to upgrade from 2.3
			$old_deals = $wpdb->get_var( "SELECT Count(ID) FROM {$wpdb->posts} WHERE post_type = 'deal'" );
			if ( $old_deals ) {
				return '2.3';
			}

			// If all else fails, this is probably a fresh install, so the 'old' version is 3.0
			return '3.0';
		}
	}

	static function get_new_version() {
		return Group_Buying::GB_VERSION;
	}

	static function add_admin_page() {
		// Option page
		$args = array(
			'slug' => self::SETTINGS_PAGE,
			'title' => self::__( 'Update Group Buying' ),
			'menu_title' => self::__( 'Update' ),
			'weight' => 2,
			'reset' => FALSE, 
			'section' => '',
			'callback' => array( get_class(), 'display_upgrade_page' )
			);
		do_action( 'gb_settings_page', $args );
	}

	static function display_upgrade_page() {
		if ( isset( $_GET['action'] ) && self::FORM_ACTION == $_GET['action'] ) {
			self::load_view( 'admin/perform-upgrade', array() );
		} else {
			self::load_view( 'admin/upgrade', array() );
		}
	}

	static function perform_upgrade() {
		$old_version = self::get_old_version();
		$new_version = self::get_new_version();

		if ( $old_version == $new_version ) {
			echo '<p>' . self::__( 'No updates are available' ) . '</p>';
		} else {
			self::upgrade( $old_version, $new_version );
		}
	}
}
