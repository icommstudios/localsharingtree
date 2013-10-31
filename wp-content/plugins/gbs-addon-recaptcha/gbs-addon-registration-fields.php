<?php
/*
Plugin Name: Group Buying Addon - Registration Captcha
Version: 1
Plugin URI: http://groupbuyingsite.com/marketplace
Description: Adds a Captcha (using reCaptcha) to the Registration page.
Author: Sprout Venture
Author URI: http://sproutventure.com/wordpress
Plugin Author: Dan Cameron
Text Domain: group-buying
*/


define ('GBS_REG_CAP_FIELDS', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );

// Load after all other plugins since we need to be compatible with groupbuyingsite
add_action('plugins_loaded', 'gb_load_registration_captcha');
function gb_load_registration_captcha() {
	if (class_exists('Group_Buying_Controller')) {
		require_once('registrationCaptchaFields.class.php');
		Group_Buying_Registration_Captcha_Addon::init();
	}
}