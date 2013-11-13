<?php

/**
 * Print the facebook button for logging, logging out, etc.
 *
 * @param string  $button_text Text the button should have
 * @return string              Uses Group_Buying_Facebook_Connect::button to print FB scripts.
 */
if ( !function_exists( 'gb_facebook_button' ) ) {
	function gb_facebook_button( $button_text = 'Login with Facebook' ) {
		if ( !is_user_logged_in() ) {
			echo gb_get_facebook_button( $button_text );
		}
	}
}
if ( !function_exists( 'gb_get_facebook_button' ) ) {
	function gb_get_facebook_button( $button_text = 'Login with Facebook' ) {
		if ( !is_user_logged_in() ) {
			return Group_Buying_Facebook_Connect::button( $button_text );
		}
	}
}