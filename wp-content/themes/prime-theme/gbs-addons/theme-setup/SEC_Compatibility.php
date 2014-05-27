<?php 

/**
 * Create alias (if possible) and fallback to extending the class.
 *
 * class_alias is not equivalent to class extending because of private methods/properties are unseen.
 */
if ( !class_exists( 'SEC_Controller' ) ) :
	if ( function_exists('class_alias') ) {
		class_alias( 'Group_Buying_Controller', 'SEC_Controller' );
	}
	else {
		class SEC_Controller extends Group_Buying_Controller {
			private function __construct() {
			}
		}
	}
endif;

////////////////
// GBS Router //
////////////////

if ( !class_exists( 'SEC_Router' ) ) :
	if ( function_exists('class_alias') ) {
		class_alias( 'GB_Router', 'SEC_Router' );
	}
	else {
		class SEC_Router extends GB_Router {
			private function __construct() {
				parent::__construct();
			}
		}
	}
endif;

if ( !class_exists( 'SEC_Router_Utility' ) ) :
	if ( function_exists('class_alias') ) {
		class_alias( 'GB_Router_Utility', 'SEC_Router_Utility' );
	}
	else {
		class SEC_Router_Utility extends GB_Router_Utility {
			private function __construct() {
				parent::__construct();
			}
		}
	}
endif;


///////////////////
// Template tags //
///////////////////

if ( !function_exists( 'sec__' ) ) :
function sec__( $string = '' ) {
	return gb__( $string );
}
endif;

if ( !function_exists( 'sec_e' ) ) :
function sec_e( $string = '' ) {
	gb_e( $string );
}
endif;

if ( !function_exists( 'sec_is_offer_type_page' ) ) :
function sec_is_offer_type_page() {
	return FALSE;
}
endif;