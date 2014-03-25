<?php
class Group_Buying_Infusionsoft extends Group_Buying_List_Services {
	const APPNAME = 'gb_infusionsoft_name';
	const APPKEY = 'gb_infusionsoft_key';
	protected static $instance;
	private static $app_name = '';
	private static $app_key = '';
	private static $email = '';
	private static $location = '';


	protected static function get_instance() {
		if ( !( isset( self::$instance ) && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_subscription_method() {
		return self::SUBSCRIPTION_SERVICE;
	}

	protected function __construct() {
		parent::__construct();
		self::$app_name = get_option( self::APPNAME, '' );
		self::$app_key = get_option( self::APPKEY, '' );
		if ( is_admin() ) {
			add_action( 'init', array( get_class(), 'register_options') );
		}

		// Location meta
		add_action( gb_get_location_tax_slug().'_edit_form_fields', array( get_class(), 'infusion_input_metabox' ), 10, 2 );
		add_action( gb_get_location_tax_slug().'_add_form_fields', array( get_class(), 'infusion_input_metabox' ), 2 );
		add_action( 'edited_terms', array( get_class(), 'save_infusion_meta_data' ) );

		// Meta box
		add_action( gb_get_location_tax_slug().'_edit_form_fields', array( get_class(), 'infusion_input_metabox' ), 10, 2 );
		add_action( 'edited_terms', array( get_class(), 'save_infusion_meta_data' ) );
	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_options() {
		// Settings
		$settings = array(
			'infusionsoft' => array(
				'title' => self::__( 'Inufusionsoft Subscription Service' ),
				'weight' => 500,
				'settings' => array(
					self::APPNAME => array(
						'label' => self::__( 'App Name' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$app_name
							)
						),
					self::APPKEY => array(
						'label' => self::__( 'App Key' ),
						'option' => array(
							'type' => 'text',
							'default' => self::$app_key
							)
						),
					)
				)
			);
		do_action( 'gb_settings', $settings, Group_Buying_List_Services::SETTINGS_PAGE );
	}

	public static function register() {
		self::add_list_service( __CLASS__, self::__( 'Infusionsoft' ) );
	}

	public function process_subscription() {

		//##Include our XMLRPC Library###
		include 'utilities/infusion/xmlrpc-2.0/lib/xmlrpc.inc';

		//##Set our Infusionsoft application as the client###
		$client = new xmlrpc_client( "https://".self::$app_name.".infusionsoft.com/api/xmlrpc" );

		//##Return Raw PHP Types###
		$client->return_type = "phpvals";

		//##Dont bother with certificate verification###
		$client->setSSLVerifyPeer( FALSE );

		//##Build a Key-Value Array to store a contact###
		$contact = array(
			"Email" =>   $_POST['email_address'],
		);

		do_action( 'gb_log', 'process_subscription - key', self::$app_name );
		//##Set up the call###
		$call = new xmlrpcmsg( "ContactService.add", array(
				php_xmlrpc_encode( self::$app_key ),   //The encrypted API key
				php_xmlrpc_encode( $contact )  //The contact array
			) );

		do_action( 'gb_log', 'process_subscription - call', $call );
		//##Send the call###
		$result = $client->send( $call );

		//##Check the returned value to see if it was successful and set it to a variable/display the results###
		if ( !$result->faultCode() ) {

			$term = get_term_by( 'slug', $_POST['deal_location'], gb_get_location_tax_slug() );
			$groupID = get_metadata( 'location_terms', $term->term_id, 'infusion_group', TRUE );
			$conID = $result->value();

			//##Opt In###
			$call = new xmlrpcmsg( "APIEmailService.optIn", array(
					php_xmlrpc_encode( self::$app_key ),   //The encrypted API key
					php_xmlrpc_encode( $_POST['email_address'] ),  //The contact ID
					php_xmlrpc_encode( 'API Opt In' ),  //The Group ID
				) );
			//##Send the call###
			do_action( 'gb_log', 'process_subscription - opt call', $call );
			$result = $client->send( $call );
			do_action( 'gb_log', 'process_subscription - opt result', $result );

			//##Set up the call to add to the group###
			$call = new xmlrpcmsg( "ContactService.addToGroup", array(
					php_xmlrpc_encode( self::$app_key ),   //The encrypted API key
					php_xmlrpc_encode( (int)$conID ),  //The contact ID
					php_xmlrpc_encode( (int)$groupID ),  //The Group ID
				) );
			//##Send the call###
			do_action( 'gb_log', 'process_subscription - atg call', $call );
			$result = $client->send( $call );

			//##Set up the call to add to the group###
			$call = new xmlrpcmsg( "ContactService.runActionSequence", array(
					php_xmlrpc_encode( self::$app_key ),     //The encrypted API key
					php_xmlrpc_encode( (int)$conID ),   //The contact ID
					php_xmlrpc_encode( (int)$groupID ),  //The Action ID
				) );
			//##Send the call###
			do_action( 'gb_log', 'process_subscription - as call', $call );
			$result = $client->send( $call );

			// finally
			parent::success( $_POST['deal_location'], $_POST['email_address'] );

		} else {
			Group_Buying_Controller::set_message( $result->faultString() );
		}

		return;
	}

	public function process_registration_subscription( $user = null, $user_login = null, $user_email = null, $password = null, $post = null ) {
		if ( !$post[ parent::REGISTRATION_OPTIN ] )
			return;

		require_once 'utilities/infusion/isdk.php';
		$iSDK = new iSDK();

		//connect to the API
		if ( $iSDK->cfgCon( self::$app_name ) ) {

			//grab our posted contact fields
			$contact=array( 'Email' => $_POST['email_address'] );

			//dup check on email if it exists.
			if ( !empty( $contact['Email'] ) ) {
				//check for existing contact;
				$returnFields = array( 'Id' );
				$dups = $iSDK->findByEmail( $contact['Email'], $returnFields );

				if ( !empty( $dups ) ) {
					//update contact
					$iSDK->updateCon( $dups[0]['Id'], $contact );
					//run an action set on the contact
					$iSDK->runAS( $dups[0]['Id'], $actionId );
				} else {
					//Add new contact
					$newCon = $iSDK->addCon( $contact );
					//run an action set on the contact
					$iSDK->runAS( $newCon, $actionId );
				}
				return true;
			}
			else {
				return;
			}
		} // connection end
		return;
	}


	public static function infusion_input_metabox( $tag ) {
		$infusion_group = get_metadata( 'location_terms', $tag->term_id, 'infusion_group', TRUE ); ?>
			</tbody>
		</table>
		<h3>Infusionsoft</h3>
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="infusion_group"><?php _e( '	Infusionsoft Tag or Action ID' ) ?></label></th>
					<td><input type="text" size="40" value="<?php echo $infusion_group; ?>" id="infusion_group" name="infusion_group" /></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	public static function save_infusion_meta_data( $term_id ) {
		if ( isset( $_POST['infusion_group'] ) ) {
			$infusion_group = esc_attr( $_POST['infusion_group'] );
			update_metadata( 'location_terms', $term_id, 'infusion_group', $infusion_group );
		}
	}
}
Group_Buying_Infusionsoft::register();
