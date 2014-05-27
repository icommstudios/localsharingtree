<?php

/**
 * Load via GBS Add-On API
 */
class Group_Buying_Registration_Captcha_Addon extends Group_Buying_Controller {

	public static function init() {
		// Hook this plugin into the GBS add-ons controller
		add_filter( 'gb_addons', array( get_class(), 'gb_addon' ), 10, 1 );
	}

	public static function gb_addon( $addons ) {
		$addons['registration_captcha'] = array(
			'label' => self::__( 'Registration Captcha' ),
			'description' => self::__( 'Adds a Captcha to the Registration page.' ),
			'files' => array(
				__FILE__,
				dirname( __FILE__ ) . '/library/template_tags.php',
			),
			'callbacks' => array(
				array( 'Registration_Captcha', 'init' ),
			),
		);
		return $addons;
	}

}

class Registration_Captcha extends Group_Buying_Controller {

	const SOURCE = 'gb_account_fields_source';
	const RECAPTCHA_OPTION = 'gb_recaptcha_key';
	const RECAPTCHA_PRIVATE_OPTION = 'gb_recaptcha_private_key';
	const RECAPTCHA_ONPASS_RESET_OPTION = 'gb_recaptcha_on_pr';
	private static $publickey;
	private static $privatekey;
	private static $add_to_reset_password_page = TRUE;


	public static function init() {

		self::$publickey = get_option( self::RECAPTCHA_OPTION, '6LeDTMgSAAAAAAvNyCBTtrZ4hcLLv046cfgdB0-7' );
		self::$privatekey = get_option( self::RECAPTCHA_PRIVATE_OPTION, '6LeDTMgSAAAAAA0SFr3OUq6RzHIJdc4oTYFbiW9w' );
		self::$add_to_reset_password_page = get_option( self::RECAPTCHA_ONPASS_RESET_OPTION, 1 );

		// registration hooks
		add_filter( 'gb_account_registration_panes', array( get_class(), 'get_registration_panes' ), 100 );
		add_filter( 'gb_validate_account_registration', array( get_class(), 'validate_account_fields' ), 10, 4 );

		if ( self::$add_to_reset_password_page ) {
			// registration hooks
			add_action( 'lostpassword_form', array( get_class(), 'lostpassword_form_captcha' ) );
			add_action( 'parse_request', array( get_class(), 'validate_for_password_reset' ), 0 );
			add_filter( 'gb_validate_password_reset', array( get_class(), 'validate_account_fields' ), 10, 4 );
		}
		// Options
		add_action( 'admin_init', array( get_class(), 'register_settings_fields' ), 10, 0 );

	}

	/**
	 * Add the default pane to the account edit form
	 *
	 * @param array   $panes
	 * @return array
	 */
	public function get_registration_panes( array $panes ) {
		$panes['captcha'] = array(
			'weight' => 100,
			'body' => self::get_catpha(),
		);
		return $panes;
	}

	public function lostpassword_form_captcha() {
		?>
			<tr>
				<td>&nbsp;</td>
				<td><?php echo self::get_catpha() ?></td>
			</tr>
		<?php
	}

	public static function get_catpha() {
		if ( !function_exists( 'recaptcha_get_html' ) ) {
			require_once GBS_REG_CAP_FIELDS . '/library/recaptchalib.php';
		}
		return '<script type="text/javascript"> var RecaptchaOptions = { theme : "clean" };</script><div class="clearfix" style="width:450px;">'.recaptcha_get_html( self::$publickey, null, TRUE ).'</div>';
	}

	/**
	 * Validate the form submitted
	 *
	 * @return array
	 */
	public function validate_account_fields( $errors = array(), $username, $email_address, $post ) {
		if ( !function_exists( 'recaptcha_get_html' ) ) {
			require_once GBS_REG_CAP_FIELDS . '/library/recaptchalib.php';
		}
		$resp = recaptcha_check_answer(
			self::$privatekey,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"] );

		if ( !$resp->is_valid ) {
			$errors[] = 'The reCAPTCHA wasn’t entered correctly. Go back and try it again.';
			return $errors;
		}
	}

	public function validate_for_password_reset() {
		if ( isset( $_POST['user_login'] ) && !empty( $_POST['user_login'] ) ) {
			if ( !function_exists( 'recaptcha_get_html' ) ) {
				require_once GBS_REG_CAP_FIELDS . '/library/recaptchalib.php';
			}
			$resp = recaptcha_check_answer(
				self::$privatekey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"] );

			if ( !$resp->is_valid ) {
				gb_set_message( gb__('The reCAPTCHA wasn’t entered correctly. Try again.'), 'error' );
				wp_redirect( add_query_arg( array( 'message' => 'captcha_error', 'user_login' => urlencode( $_POST['user_login'] ) ), Group_Buying_Accounts_Retrieve_Password::get_url() ) );
				exit();
			}
		}
	}

	public static function register_settings_fields() {
		$page = Group_Buying_UI::get_settings_page();
		$section = 'gb_basic_rewards_settings';
		add_settings_section( $section, self::__( 'reCaptcha Settings' ), array( get_class(), 'display_settings_section' ), $page );
		// Settings
		register_setting( $page, self::RECAPTCHA_OPTION );
		register_setting( $page, self::RECAPTCHA_PRIVATE_OPTION );
		register_setting( $page, self::RECAPTCHA_ONPASS_RESET_OPTION );
		// Fields
		add_settings_field( self::RECAPTCHA_OPTION, self::__( 'Public Key' ), array( get_class(), 'display_key' ), $page, $section );
		add_settings_field( self::RECAPTCHA_PRIVATE_OPTION, self::__( 'Private Key' ), array( get_class(), 'display_private_key' ), $page, $section );
		add_settings_field( self::RECAPTCHA_ONPASS_RESET_OPTION, self::__( 'Place on Password Reset Page' ), array( get_class(), 'display_on_pr' ), $page, $section );
	}
	public function display_settings_section() {
		printf( self::_e( '<a href="%s">Signup</a> and create your public and private keys first.' ), 'http://www.google.com/recaptcha/whyrecaptcha' );
	}

	public static function display_key() {
		echo '<input type="text" name="'.self::RECAPTCHA_OPTION.'" value="'.self::$publickey.'" />';
	}

	public static function display_private_key() {
		echo '<input type="text" name="'.self::RECAPTCHA_PRIVATE_OPTION.'" value="'.self::$privatekey.'" />';
	}

	public static function display_on_pr() {
		echo '<select name="'.self::RECAPTCHA_ONPASS_RESET_OPTION.'"><option value="1" '.selected( self::$add_to_reset_password_page, '1', FALSE ).'>Yes</option><option value="0" '.selected( self::$add_to_reset_password_page, '0', FALSE ).'>No</option></select>';
	}

}
