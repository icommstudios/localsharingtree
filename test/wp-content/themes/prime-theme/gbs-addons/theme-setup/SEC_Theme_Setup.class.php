<?php 

///////////////////////
// GBS Compatibility //
///////////////////////

if ( !class_exists( 'SEC_Controller' ) ) {
	require 'SEC_Compatibility.php';
}

if ( !defined( 'SEC_ADDONS_DIR' ) ) {
	@define( 'SEC_ADDONS_DIR', '/gbs-addons' );
}

/**
* Class to setup the current theme.
* 
*/
class SEC_Theme_Setup extends SEC_Controller {
	
	public static function init() {
		add_filter( 'sec_get_active_offer_types', array( get_class(), 'current_themes_compatible_offer_types') );
	}

	public static function current_themes_compatible_offer_types( $default_types ) {
		if ( function_exists( 'compatible_offer_types' ) ) {
			$default_types = compatible_offer_types(  $default_types );
		}
		return $default_types;
	}

	public static function addons_folder_directory_name() {
		return basename( dirname( dirname(__FILE__) ) );
	}

	public static function addons_folder_url() {
		return get_bloginfo( 'template_url' ) . '/' . self::addons_folder_directory_name() ;
	}

	public static function addons_folder_directory() {
		return get_template_directory() . '/' . self::addons_folder_directory_name() ;
	}

}
add_action( 'init', array( 'SEC_Theme_Setup', 'init' )  );
