<?php

/**
 * This function file is loaded after the parent theme's function file. It's a great way to override functions, e.g. add_image_size sizes.
 *
 *
 */
 

// Include Custom functions

require_once('customfunctions/SF_DealResetLimits.php');
require_once('customfunctions/sf-additional-notifications/SF_AdditionalNotifications.class.php');
require_once('customfunctions/sf-account-fields/SF_AccountFields.class.php');
require_once('customfunctions/sf-merchant-fields/SF_merchantFields.class.php');
require_once('customfunctions/sf-featured-deal/SF_Featured_Deals.class.php');
require_once('customfunctions/sf-deal-fields/SF_DealFields.class.php');
require_once('customfunctions/sf-custom-csv-reports/SF_CustomDealExport.class.php');
require_once('customfunctions/sf-custom-csv-reports/SF_CustomSalesExport.class.php');
require_once('customfunctions/sf-credit-codes/SF_CreditCodes.class.php');
require_once('customfunctions/SF_CustomSearch.php');
require_once('customfunctions/SF_CustomTaxonomies.php');
require_once('customfunctions/sf-deal-filters/SF_DealFilters.php');

// This theme uses wp_nav_menu() in one location.
register_nav_menus( array(
		'header' => gb__( 'Header Menu' ),
		'topnav' => gb__( 'TopNav Menu' )
	) );
	
// Register the sidebars 
add_action( 'widgets_init', 'register_custom_sidebars', 11 );
function register_custom_sidebars() {
	register_sidebar(
		array(
			'name' => 'Charities Sidebar',
			'id'            => 'charities-sidebar',
			'description'   => 'Used on the Charities archive page.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '<div class="clear"></div></div>',
			'before_title' => '<h2 class="widget-title gb_ff">',
			'after_title' => '</h2>'
		)
	);
	register_sidebar(
		array(
			'name' => 'Single Charity Sidebar',
			'id'            => 'charity-sidebar',
			'description'   => 'Used on the single Charity page.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '<div class="clear"></div></div>',
			'before_title' => '<h2 class="widget-title gb_ff">',
			'after_title' => '</h2>'
		)
	);
}

//Add Mailchimp Unsubscribe feature
add_filter('subscribe_mc_groupins','custom_do_unsubscribe_mailchimp', 99, 2);
function custom_do_unsubscribe_mailchimp( $merge_vars, $group_id = '' ) {

	// Get email
	if ( isset( $_POST['email_address'] ) && !empty($_POST['email_address']) ) {
		$email = $_POST['email_address'];
	} elseif ( isset( $_POST['gb_account_email'] ) && !empty($_POST['gb_account_email']) ) {
		$email = $_POST['gb_account_email'];
	} else {
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;
	}
	
	//Get list id
	$list_id = get_option('gb_mailchimp_list_id');
	$group_id = get_option( 'gb_mailchimp_group_id' );

	// Setup API instance so locations can be added before being added to the merge vars.
	require_once( get_template_directory() . '/gbs-addons/subscription/list-services/utilities/MCAPI.class.php');
	$mc_api = new GB_MCAPI(get_option('gb_mailchimp_api_key'));
	
	//Remove groupings ( locations ) so user has no locations ( allow subscribe to re-add them )
	$remove_groups_merge_vars = array(
			'GROUPINGS' => array(
				array( 'id' => $group_id, 'groups' => $groups ),

			),
			//'MC_LOCATION'=>array('LATITUDE'=>34.0413, 'LONGITUDE'=>-84.3473),
		);
	
	//Update list member
	$retval = $mc_api->listUpdateMember(
		$list_id,
		$email,
		$remove_groups_merge_vars,
		$email_type = 'html',
		$replace_interests = TRUE
	);
		
	// Continue with regular Mailchimp subscribe process
	return $merge_vars;
}
//Custom function to get user's Mailchimp location subscriptions
function sf_get_users_mailchimp_locations($user_id = null) {
	if ( !$user_id ) {
		$user_id = get_current_user_id();	
	}
	if ( !$user_id ) return false;
	$user_data = get_userdata( $user_id );
	
	//Email
	$email = $user_data->user_email;
	
	//Get list id
	$list_id = get_option('gb_mailchimp_list_id');
	$group_id = get_option( 'gb_mailchimp_group_id' );

	// Setup API instance so locations can be added before being added to the merge vars.
	require_once( get_template_directory() . '/gbs-addons/subscription/list-services/utilities/MCAPI.class.php');
	$mc_api = new GB_MCAPI(get_option('gb_mailchimp_api_key'));
	
	$response = $mc_api->listMemberInfo(
		$list_id,
		$email
	);
	
	//Get user's subscribed locations (groups)
	$user_sub_locations = array();
	if ( $response['success'] && $response['data']) {
		foreach ($response['data'] as $response_email) {
			if ( $response_email['list_id'] == $list_id && isset($response_email['merges']['GROUPINGS']) ) {
				foreach ($response_email['merges']['GROUPINGS'] as $response_grouping ) {
					if ( $response_grouping['id'] == $group_id && !empty($response_grouping['groups']) ) {
						$user_sub_locations = explode(',', $response_grouping['groups']);
						break;
					}
				}
				break;
			}
		}
	}
	//Cleanup
	if ( is_array( $user_sub_locations ) ) {
		foreach ( $user_sub_locations as $key => $sub ) {
			$user_sub_locations[$key] = trim($sub);
		}
	}
	
	//Update local database mailchimp option
	$account = Group_Buying_Account::get_instance();
	$db_options = get_post_meta( $account->get_ID(), '_'.Group_Buying_MailChimp::LOCATION_PREF_OPTION, true );
	if ( $user_sub_locations != $db_options ) {
		update_post_meta( $account->get_ID(), '_'.Group_Buying_MailChimp::LOCATION_PREF_OPTION, $user_sub_locations );
	}
	
	return $user_sub_locations;
}
add_filter('gb_account_edit_account_notificaiton_fields', 'custom_change_mailchimp_subscription_field', 99, 2);
function custom_change_mailchimp_subscription_field( $fields, $account ) {
	
	if ( isset($fields['mc_subscription']) ) {
		
		//Replace database stored options with actual Mailchimp options
		if ( function_exists('sf_get_users_mailchimp_locations' ) ) {
			$view = '';
			
			if ( $account ) {
				$user_id = $account->get_user_id_for_account();
			}
			
			$mc_options = sf_get_users_mailchimp_locations($user_id);
			
			//Rebuild view
			foreach ( gb_get_locations( FALSE ) as $location ) {
				$checked = ( in_array( $location->slug, $mc_options ) ) ? 'checked="checked"' : '' ;
				$view .= '<span class="location_pref_input_wrap"><label><input type="checkbox" name="'.Group_Buying_MailChimp::LOCATION_PREF_OPTION.'[]" value="'.$location->slug.'" '.$checked.'>'.$location->name.'</label></span>';
			}
			//Add an empty checkbox input ( to ensure the form still runs if all options are unselected )
			$view .= '<span style="display: none;"><input type="checkbox" name="'.Group_Buying_MailChimp::LOCATION_PREF_OPTION.'[]" value="" checked="checked"></span>';
			$fields['mc_subscription']['output'] = $view;
		}
	
	}
	return $fields;	
}

// Report records per page
add_filter('gb_reports_show_records', 'custom_gb_reports_show_records', 999, 2);
function custom_gb_reports_show_records( $number, $report = '' ) {
	if ( $report == 'accounts' ) {
		$is_csv = stripos($_SERVER['REQUEST_URI'], '/reports/csv');
		if ( $is_csv !== false ) {
			return 99999; //Show all records if showing CSV for accounts report
		}
	}
	return $number;
}
//Remove showpage from accounts CSV download url
add_filter('gb_get_current_report_csv_download_url', 'custom_gb_get_current_report_csv_download_url', 999, 1);
function custom_gb_get_current_report_csv_download_url( $url ) {
	//is csv report url
	if ( stripos($url, '/reports/csv') !== false) {
		//remove pagination var
		$url = remove_query_arg('showpage', $url);
	}
	return $url;
}


// Account Registration fields
add_filter('gb_account_register_contact_info_fields', 'custom_fields_changes', 999);
add_filter('gb_account_edit_contact_fields', 'custom_fields_changes', 999);
function custom_fields_changes($fields) {
	/*
	if (isset($fields['first_name'])) {
		$fields['first_name']['attributes'] = array_merge((array)$fields['first_name']['attributes'], array('maxlength' => 100));
	}
	if (isset($fields['last_name'])) {
		$fields['last_name']['attributes'] = array_merge((array)$fields['last_name']['attributes'], array('maxlength' => 100));
	}
	*/
	/*
	if (isset($fields['street'])) {
		$fields['street']['required'] = FALSE;
	}
	if (isset($fields['city'])) { 
		$fields['city']['required'] = FALSE;
	}
	if (isset($fields['zone'])) { //state, county
		$fields['zone']['required'] = FALSE;
	}
	if (isset($fields['postal_code'])) { //zip, postcode
		$fields['postal_code']['required'] = FALSE;
	}
	if (isset($fields['country'])) { //country 
		$fields['country']['required'] = FALSE;
	}
	*/
	unset($fields['street']);
	unset($fields['city']);
	unset($fields['zone']);
	unset($fields['postal_code']);
	unset($fields['country']);
	
	return $fields;
}
add_filter('gb_checkout_fields_billing', 'custom_checkout_fields_changes', 999);
//add_filter('gb_checkout_fields_shipping', 'custom_fields_changes', 999);
function custom_checkout_fields_changes($fields) {
	return array(); //no fields
}
//Remove billing pane
add_filter('gb_checkout_panes', 'custom_sf_gb_checkout_panes', 999, 2);
function custom_sf_gb_checkout_panes($panes, $checkout) {
	unset($panes['billing']); //remove billing address fields
	return $panes;
}


// Disable Guest Purchases
add_filter( 'gb_account_register_user_fields', 'remove_guest_registration_field', 100, 1 );
function remove_guest_registration_field( $fields = array() ) {
unset($fields['guest_purchase']);
return $fields;
}

//Replace scripts
add_action( 'wp_print_scripts', 'custom_gbs_scripts_changes', 50 );
function custom_gbs_scripts_changes() {
	wp_dequeue_script( 'gbs-jquery-template');
	wp_deregister_script( 'gbs-jquery-template');
}
add_action( 'init', 'custom_gbs_theme_register_scripts' );
function custom_gbs_theme_register_scripts() {
	wp_register_script( 'custom-gbs-jquery-template', get_stylesheet_directory_uri().'/js/custom-jquery.template.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs' ), gb_ptheme_current_version(), false );
}
add_action( 'wp_enqueue_scripts', 'custom_wp_enqueue_scripts' );
function custom_wp_enqueue_scripts() {
	wp_enqueue_script('custom-gbs-jquery-template');
}


//Custom footer scripts
add_action('wp_footer', 'custom_footer_scripts');
function custom_footer_scripts() {
	//Message banner close X 
	// (NOT USED NOW - Replaced with messages that appear in lighboxes - see: js/custom-jquery.template.js)
	?>
    <script type="text/javascript">
	/* After page is complete */
	jQuery(window).load(function() {
		jQuery('#message_banner').on("click", function() { 
			jQuery(this).hide();
		});
	});
	</script>
    <?php	
	//Add popup for requiring login before adding to cart
	/*
	if ( !is_user_logged_in() ) { 
	
	?>
    <script type="text/javascript">
		//On click show lighbox for login
		jQuery(window).load(function() {
			jQuery("#trigger_fancybox_message_banner").fancybox({
				'content': '<div class="fancybox_message_banner" style="display: block; !important">' + jQuery("#trigger_fancybox_message_banner").html() + '</div>',
				'hideOnOverlayClick': true,
				'hideOnContentClick': false,
				'showCloseButton': true,
				'autoDimensions': true,
				'autoScale': true,
				'overlayColor': '#000000',
				'width': 700,
				'height': 200,
				'overlayOpacity': 0.8,
				'padding': 0
			});
		});
	</script>
    <?php
	}
	*/
	
	//Add popup for on order confirmation page
	if ( gb_on_checkout_page() && gb_get_current_checkout_page() == 'confirmation') { 
		global $order_number;
		$checkout = Group_Buying_Checkouts::get_instance();
		$charity_id = ( $checkout ) ? $checkout->cache['gb_charity'] : '';
		if ( !$charity_id ) {
			$share_text_original = 'I just shopped, saved and supported charity at LocalSharingTree.com';
			$share_text = urlencode($share_text_original);
			$share_url = urlencode( add_query_arg(array('ref' => $order_number), site_url()));
		} else {
			$charity_title = get_the_title ( $charity_id );
			$charity_name_link = '<a href="'.get_permalink($charity_id).'" target="_blank">'.get_the_title( $charity_id ).'</a>';
			$share_text_original = 'I just shopped, saved and supported '.$charity_name_link .' at LocalSharingTree.com';
			$share_text = urlencode($share_text_original);
			$share_url = urlencode( add_query_arg(array('ref' => $order_number), site_url()));
		}
		?>
        <div id="trigger_fancybox_order_confirmation" style="display: none;">
            <div style="text-align: center; padding: 15px;">
            	 <h3 style="padding-bottom: 15px;">Thank you for your order!</h3>
                <p style="padding-bottom: 5px;"><strong>Share with your friends: </strong></p>
                <p style="padding-bottom: 5px;"><?php echo $share_text_original; ?></p>
                <a style="margin: 10px 2px; display: inline-block;" href="http://www.facebook.com/sharer.php?s=100&amp;p[title]=<?php echo $share_text; ?>&amp;p[url]=<?php echo $share_url; ?>&amp;p[summary]=" class="order_confirm_facebook" target="_blank"><img src="http://localsharingtree.com/wp-content/plugins/floating-social-media-icon/images/themes/1/facebook.png" style="border:0px;" alt="Share on Facebook"></a>
                <a style="margin: 10px 2px; display: inline-block;" href="http://twitter.com/intent/tweet?original_referer=<?php echo site_url() ?>&text=<?php echo $share_text; ?>&url=<?php echo $share_url; ?>" title="Share on Twitter" class="order_confirm_twitter" target="_blank"><img src="http://localsharingtree.com/wp-content/plugins/floating-social-media-icon/images/themes/1/twitter.png" style="border:0px;" alt="Share on Twitter"></a>
            </div>
        </div>
        <script type="text/javascript">
			jQuery("#trigger_fancybox_order_confirmation").fancybox({
				'content': '<div class="fancybox_order_confirmation" style="display: block; !important">' + jQuery("#trigger_fancybox_order_confirmation").html() + '</div>',
				'hideOnOverlayClick': true,
				'hideOnContentClick': false,
				'showCloseButton': true,
				'autoDimensions': true,
				'autoScale': true,
				'overlayColor': '#000000',
				'width': 700,
				'height': 200,
				'overlayOpacity': 0.8,
				'padding': 0
			});
            jQuery(document).ready(function() {
				setTimeout(function() {
					 jQuery("#trigger_fancybox_order_confirmation").trigger('click');
				}, 2000);
              
            });
        </script>
		<?php
	}
}

//Require Login before Adding to cart
add_action( 'parse_request', 'custom_force_login_before_cart', 1, 1);
function custom_force_login_before_cart(WP $wp) {
	if ( !is_user_logged_in() ) {
		if ( gb_on_cart_page() || gb_on_checkout_page() ) {
			Group_Buying_Controller::set_message( gb__( 'In order to Purchase a Deal, You Must Register as a User, or Log In First.' ) );
			Group_Buying_Controller::login_required();
		}
	}
}


//Change home from Subscription Landing
remove_action( 'pre_gbs_head', 'gb_redirect_from_home' );
add_action( 'pre_gbs_head', 'custom_gb_redirect_away_from_home' );
function custom_gb_redirect_away_from_home() {
	
	if ( !is_user_logged_in() && gb_force_login_option() != 'false' ) {
		if (
			( is_home() && 'subscriptions' == gb_force_login_option() ) ||
			gb_on_login_page() ||
			gb_on_reset_password_page() ) {
			return;
		} else {
			gb_set_message( gb__( 'Force Login Activated, Membership Required.' ) );
			gb_login_required();
			return;
		}
	}
	
	
	if ( is_home() || is_front_page() ) {
		
		//if redirecting to featured
		if ( isset($_GET['featured']) ) {
			$featured_deal_link = gb_get_latest_deal_link();
			wp_redirect( $featured_deal_link );
			exit();
		}
		
		//logged in, send to home
		if ( is_user_logged_in() ) {
				
			//$deals_link = gb_get_deals_link( gb_get_location_preference() );
			//$deals_link = gb_get_latest_deal_link();
			
			$featured_deal_link = gb_get_latest_deal_link();
			wp_redirect( $featured_deal_link );
			exit();
			
		} else {
			
			//Not logged in, but location set
			if ( isset($_GET['location']) && term_exists( $_GET[ 'location' ]) ) {
				//$deals_link = gb_get_deals_link( $_GET['location'] );
				//wp_redirect( $deals_link  );
				//wp_redirect( site_url('/home/') );
				$featured_deal_link = gb_get_latest_deal_link();
				wp_redirect( $featured_deal_link );
				exit();
			} elseif ( isset($_COOKIE['gb_location_preference']) && term_exists( $_COOKIE[ 'gb_location_preference' ]) ) {
				//$deals_link = gb_get_deals_link( $_COOKIE[ 'gb_location_preference' ] );
				//wp_redirect( $deals_link  );
				$featured_deal_link = gb_get_latest_deal_link();
				wp_redirect( $featured_deal_link );
				exit();
			}
			
		}
	}
	
	return;	
}

//Handle Merchant Register & Submit Deal links & redirect
add_action( 'init', 'custom_handle_merchant_register_deals', 99 );
function custom_handle_merchant_register_deals() {
	//Check if merchant register form and already hase mechant registered
	if ( stripos($_SERVER['REQUEST_URI'], 'merchant/register') !== false && is_user_logged_in() ) { //Group_Buying_Merchants_Registration::is_merchant_registration_page()
		//On merchant registration page and already has merchant
		if ( gb_get_merchants_by_account( get_current_user_id() ) ) {
			Group_Buying_Controller::set_message( gb__( 'You have already registered your business. <br>If you need to make changes, you can do so on your <a style="color: #FFF; text-decoration: underline;" href="'.gb_get_account_url().'">Account page</a>.' ), Group_Buying_Controller::MESSAGE_STATUS_INFO );
			wp_redirect( gb_get_merchant_account_url() );
			//wp_redirect( gb_get_account_url() );
			exit();
		}
	}
	
	if ( !isset( $_GET['action_page'] ) ) return;
	
	//Register merchant
	if ( $_GET['action_page'] == 'register_merchant' ) {
		if ( !is_user_logged_in() ) {
			Group_Buying_Controller::set_message( gb__( 'In order to Register Your Business You Must Register as a User, or Log In First.' ), Group_Buying_Controller::MESSAGE_STATUS_INFO );
			//Redirect to login
			Group_Buying_Controller::login_required();
		} else {
			//Already has merchant?
			$has_merchant = gb_get_merchants_by_account( get_current_user_id() );
			if ( empty($has_merchant) ) {
				wp_redirect( gb_get_merchant_registration_url() );
				exit();
			} else {
				Group_Buying_Controller::set_message( gb__( 'You have already registered your business. <br>If you need to make changes, you can do so on your <a style="color: #FFF; text-decoration: underline;" href="'.gb_get_account_url().'">Account page</a>.' ), Group_Buying_Controller::MESSAGE_STATUS_INFO );
				wp_redirect( gb_get_merchant_account_url() );
				//wp_redirect( gb_get_account_url() );
				exit();
					
			}
		}
		
	}
	
	//Deal submit
	if ( $_GET['action_page'] == 'deal_submit' ) {
		if ( !is_user_logged_in() ) {
			Group_Buying_Controller::set_message( gb__( 'You must register a User and as a Business first or log in.' ), Group_Buying_Controller::MESSAGE_STATUS_INFO );
			//Redirect to login
			Group_Buying_Controller::login_required();
		} else {
			//Already has merchant?
			$has_merchant = gb_get_merchants_by_account( get_current_user_id() );
			if ( empty($has_merchant) ) {
				Group_Buying_Controller::set_message( gb__( 'In Order to Run a Deal, You Must Register Your Business First.' ), Group_Buying_Controller::MESSAGE_STATUS_INFO );
				wp_redirect( gb_get_merchant_registration_url() );
				exit();
			} else {
				//Redirect to deal submit
				wp_redirect( gb_get_deal_submission_url() );
				exit();
						
			}
		}
		
	}
}


//Change upload limit on Business submit deal form
add_filter('gb_validate_deal_submission', 'custom_uploadlimit_gb_validate_deal_submission', 10, 2);
function custom_uploadlimit_gb_validate_deal_submission($errors, $post) {
	if ( !empty( $_FILES['gb_deal_thumbnail']['name'] ) ) {
		//Check size
		$max_bytes = ( 1048576 * 3 ); // bytes in MB * # of MB
		//$min_bytes = ( 1024 * 10 ); // bytes in KB * # of KB
		$uploaded_filesize = intval($_FILES['gb_deal_thumbnail']['size']);
		//if ($uploaded_filesize > $max_bytes || $uploaded_filesize < $min_bytes ) {
		if ($uploaded_filesize > $max_bytes) {
			$errors[] = sprintf( gb__( '"%s" file size cannot be greater than 3MB.' ), gb__( 'Deal Image' ) );
		}
	}
	return $errors;
}


//Adjust voucher previews to match voucher changes
add_filter('gb_voucher_preview_content', 'custom_gb_voucher_preview_content', 10, 2);
function custom_gb_voucher_preview_content( $content, $deal_id ) {
	$deal = Group_Buying_Deal::get_instance( $deal_id );
	
	$price = gb_get_formatted_money( gb_get_price($deal_id));
	$content = str_replace( '<?php gb_formatted_money( gb_get_price( gb_get_vouchers_deal_id() ) ); ?>', $price, $content );
	
	$worth = gb_get_formatted_money( gb_get_deal_worth($deal_id));
	$content = str_replace( '<?php gb_formatted_money( gb_get_deal_worth( gb_get_vouchers_deal_id() ) ); ?>', $worth, $content );
	
	$excerpt = gb_get_rss_excerpt($deal_id);
	$content = str_replace( '<?php gb_rss_excerpt( gb_get_vouchers_deal_id() ); ?>', $excerpt, $content );
	
	//If we have expiration comments
	$expiration = '';
	if ( class_exists('SF_Deal_Fields') ) {
		$expiration = SF_Deal_Fields::get_sf_custom_deal_field($deal_id, 'voucher_expiration_comments');
	}
	if ( empty($expiration) ) {
		$expiration = ( $deal->get_voucher_expiration_date() ) ? $deal->get_voucher_expiration_date() : time()+60*60*24*14;
		$expiration = date( $format, $expiration );
	}
	$content = str_replace( '<?php gb_voucher_expiration_date(get_the_ID()); ?>', $expiration, $content );
	
	//merchant title
	if ( gb_has_merchant($deal_id) ) {
		$merchant_id = gb_get_merchant_id($deal_id);
		$merchant = '<div class="clearfix"><p class="title" style="font-size: larger;">'.gb_get_merchant_name( $merchant_id ).'</p></div>';
	} else {
		$merchant = '';
	}
	$content = str_replace( '<div class="clearfix"><p class="title" style="font-size: larger;"><?php gb_merchant_name( gb_get_merchant_id(gb_get_vouchers_deal_id()) ); ?></p></div>', $merchant, $content );
	
	
	
	
	return $content;
}

//Add Admin option to Redeem voucher
add_filter('gb_mngt_vouchers_columns', 'custom_gb_mngt_vouchers_columns', 10, 1);
function custom_gb_mngt_vouchers_columns($columns) {
	//Rebuild claimed column (keep existing functionality)
	unset($columns['claimed']);
	$columns['claimed_custom'] = gb__( 'Redeemed' );
	return $columns;	
}
add_filter('gb_mngt_vouchers_column_claimed_custom', 'custom_gb_mngt_vouchers_column_claimed', 0, 1);
function custom_gb_mngt_vouchers_column_claimed($item) {
	$voucher = Group_Buying_Voucher::get_instance( $item->ID );
	$claim_date = $voucher->get_claimed_date();
	$status = '';
	if ( $claim_date ) {
		$status = '<p>' . mysql2date( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $claim_date ) . '</p>';
		$status .= '<p><span id="'.$item->ID.'_unclaim_result"></span><a href="javascript:void(0)" class="gb_unclaim button disabled" id="'.$item->ID.'_unclaim" ref="'.$item->ID.'">'.gb__( 'Remove Redemption' ).'</a></p>';
	} else {
		//Add claim option
		$status = '<p><span id="'.$item->ID.'_claim_result"></span><a href="javascript:void(0)" class="gb_claim button disabled" id="'.$item->ID.'_claim" ref="'.$item->ID.'">'.gb__( 'Mark as Redeemed' ).'</a></p>';
	}
	return $status;
}
add_action('admin_footer', 'custom_admin_footer_scripts');
function custom_admin_footer_scripts() {
	//Add claim ajax call (unclaim already exists in GBS)
	?>
    <script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function($){
				jQuery(".gb_claim").on('click', function(event) {
					event.preventDefault();
						if(confirm("Are you sure you want to mark as Redeemed?")){
							var $claim_button = $( this ),
							claim_voucher_id = $claim_button.attr( 'ref' );
							$( "#"+claim_voucher_id+"_claim" ).fadeOut('slow');
							$.post( ajaxurl, { action: 'gb_mark_voucher', voucher_id: claim_voucher_id, mark_voucher: 1 },
								function( data ) {
										$( "#"+claim_voucher_id+"_claim_result" ).append( '<?php gb_e( "Voucher marked as Redeemed." ) ?>' ).fadeIn();
									}
								);
						} else {
							// nothing to do.
						}
				});
			});
		</script>
    <?php	
}

// Cart remove on click - Add jquery functions
add_action('wp_footer', 'cart_add_remove_item_onclick');
function cart_add_remove_item_onclick() {
	if ( gb_on_cart_page() ) {
		?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#gb_cart .cart-remove input[type=checkbox]').change(function() {
		  //Click the update button
		  $('#gb_cart input[name=gb_cart_action-update]').click();
		});
	
	});
	
	function removeCartItem(key) {
		jQuery('#gb_cart #removekey_' + key + ' input').prop('checked', true);
		jQuery('#gb_cart input[name=gb_cart_action-update]').click();
		return false;
	}
	
</script>      
		<?php
	}
}

//Change Cart line items - add Remove link
add_filter('gb_cart_items', 'custom_cart_items', 10 , 2); 
function custom_cart_items($items, $cart) {
    
	foreach ($items as $key => $item) {
		$new_remove = '<span id="removekey_'.$key.'" style="display: none;">'.$items[$key]['remove'].'</span><a class="alt_button remove_button" href="#remove'.$key.'" onClick="removeCartItem(\''.$key.'\'); return false;">Remove</a>';
		$items[$key]['remove'] = $new_remove;
	}
    return $items;
}

// Facebook Connect - Subscribe to mailchimp
add_action('wp', 'custom_detect_facebook_registration');
function custom_detect_facebook_registration() {
	if ( isset($_GET['facebook_reg']) && is_user_logged_in() ) {
		//Subscribe them
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;
		
		if ( class_exists('Group_Buying_MailChimp') ) {
			$retval = Group_Buying_MailChimp::subscribe( $email, $_COOKIE[ 'gb_location_preference' ] );
		}
	}
}

if ($_GET['fbtest']) {
	add_action('wp_footer', 'custom_fbttest_footer');
	function custom_fbttest_footer() {
		global $blog_id;
		$uid = get_user_meta( get_current_user_id(), $blog_id.'_fb_uid', TRUE );	
		echo '_fb_uid: '.$uid;
		
	}
	add_filter('gb_facebook_scope', 'test_output_gb_facebook_scope', 10, 1);
	function test_output_gb_facebook_scope($scope) {
		echo ' <br>fb scope: '.$uid;
		die($scope);	
	}
}

if ($_GET['fbtestdelete']) {
	add_action('wp_footer', 'custom_fbttest_delete_footer');
	function custom_fbttest_delete_footer() {
		global $blog_id;
		$uid = delete_user_meta( get_current_user_id(), $blog_id.'_fb_uid' );	
		echo 'deleted _fb_uid: '.$uid;
		
	}
}


// Add Page number navigation

function wp_pagination($this_query = null) {
	global $wp_query;
	if ( !$this_query ) {
		$this_query = $wp_query;
	}
	$big = 12345678;
	$page_format = paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var('paged') ),
		'total' => $this_query->max_num_pages,
		'type'  => 'array'
	) );
	if( is_array($page_format) ) {
				$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');
				echo '<div class="pagination"><ul>';
				echo '<li><span>'. $paged . ' of ' . $this_query->max_num_pages .'</span></li>';
				foreach ( $page_format as $page ) {
						echo "<li>$page</li>";
				}
			   echo '</ul></div>';
	}
}

//Add Filter by letter
function custom_show_filter_letters() {

	//Also show locations filter
	//$locations = get_terms("gb_location", array('hide_empty' => false, 'fields' => 'all'));
	$locations = gb_get_locations(); //Use gb_get_locations so territory filters are applied
	
	$selected_locations = ( !empty($_GET['sf_filter_loc']) ) ? explode(',', $_GET['sf_filter_loc']) : array();
	echo '<div class="filter_by_location">';
	echo '<span class="filter_by_location_label">Locations: </span>';
	foreach ( $locations as $location ) {
		if ( !isset( $_GET['sf_filter_loc']) || in_array( $location->term_id, $selected_locations) ) {
			$checked_location = 'checked="checked"';
		} else {
			$checked_location = '';
		}
		echo '<label for="location_filter_'.$location->term_id.'"><input '.$checked_location.' type="checkbox" name="filter_by_location_checkbox" class="filter_by_location_checkbox" value="'.$location->term_id.'"> '.$location->name.'</label> ';
	}
	echo '</div>';
	
	//Show letters filter
	$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M',
					 'N', 'O','P','Q','R','S','T','U','V','W','X','Y','Z');
	
	echo '<div class="pagination filter_by_letter" style="margin-top: 0;"><ul style="padding-left: 0;">';
	echo '<li><span>Starts with: </span></li>';
	foreach ( $letters as $l) {
		$letter_url = add_query_arg(array('sf_filter_l' => $l), home_url($_SERVER['REQUEST_URI']));
		//replace page number back to 0
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		if ( $paged > 1 ) {
			$letter_url = str_replace('/page/'.$paged, '', $letter_url); //replace paged # in url with 1 ( return to beginning )
		}
		
		if ( $_GET['sf_filter_l'] == $l ) {
			echo '<li class="current_letter"><span class="current">'.$l.'</span></li>';
		} else {
			echo '<li><a href="'.$letter_url.'" class>'.$l.'</a></li>';
		}
	}
   	echo '</ul></div>';
	
	?>
    <script type="text/javascript">
	jQuery(document).ready(function($){
		
		jQuery('.filter_by_location input').click(function(e){
			
			<?php
				$filter_url = site_url($_SERVER['REQUEST_URI']);
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				if ( $paged > 1 ) {
					$filter_url = str_replace('/page/'.$paged, '', $filter_url); //replace paged # in url with 1 ( return to beginning )
				}
			?>
			var filter_url = '<?php echo remove_query_arg(array('sf_filter_loc', 'sf_filter_l'), $filter_url); echo '?sf_filter_l='.$_GET['sf_filter_l']; ?>';
			
			//var locations = $(".filter_by_location_checkbox").serialize();
			var checkedValues = $('.filter_by_location input:checked').map(function() {
				return this.value;
			}).get();
			
			if ( checkedValues ) {
				checkedValues.join(',');
			} else {
				checkedValues = '';	
			}
			
			//filter_url += '&loc=' + encodeURIComponent(locations);
			filter_url += '&sf_filter_loc=' + checkedValues;
			window.location = filter_url;
			return true; 
		});  
	});
	</script>

	<?php
}
//Filter the query to filter by letter (and locations)
add_filter('posts_where', 'sf_filter_archive_by_letter');
function sf_filter_archive_by_letter ( $where ) {
	global $wp_query, $wpdb;

	//Do not filter for some pages
	if ( isset( $_GET['sf_filter_l'] ) && !empty( $_GET['sf_filter_l'] ) && !is_admin() && is_main_query() ) {
	   
	   //Filter for these cases
	   if ( is_post_type_archive( 'gb_charities' ) 
	   			|| is_post_type_archive( 'gb_merchant' )
				|| is_tax( 'gb_charity_type' )
				|| is_tax( 'gb_merchant_type')  ) {
	   
			$letter_filter = $_GET['sf_filter_l'];
	
			if ( !empty($letter_filter) ) {
				$where .= " AND $wpdb->posts.post_title LIKE '".$letter_filter."%'";
				return $where;
			} 
	   }
	}
	return $where;
}
//Filters - add location taxonomy
add_filter('parse_query', 'sf_filter_archive_by_location_parse_query', 11 );
function sf_filter_archive_by_location_parse_query ( $query ) {
	
   if ( !is_admin() && $query->is_main_query() && isset( $_GET['sf_filter_loc'] ) && !empty( $_GET['sf_filter_loc'] ) ) {
	   
		$q_vars = &$query->query_vars;
		$taxonomy = 'gb_location';
		$terms = explode(',', $_GET['sf_filter_loc']);
		
		if ( !isset($q_vars['suppress_filters']) ) {
			
			if ( is_post_type_archive( 'gb_charities' ) 
	   			|| is_post_type_archive( 'gb_merchant' )
				|| is_tax( 'gb_charity_type' )
				|| is_tax( 'gb_merchant_type')  ) {
					
				if ( is_post_type_archive( 'gb_merchant' ) || is_tax( 'gb_merchant_type' ) ) {
					$query->set( 'post_type', 'gb_merchant' );
				} elseif (  is_post_type_archive( 'gb_charities' ) || is_tax( 'gb_charity_type' ) ) {
					$query->set( 'post_type', 'gb_charities' );
				}
			
				//Build tax query
				$tax_query = array(
						array(
							'taxonomy' => $taxonomy,
							'field' => 'id',
							'terms' => $terms,
							'operator' => 'IN'
							)
						);
						
				if ( isset($q_vars['tax_query']) && !empty($q_vars['tax_query']) ) {
					$tax_query = array_merge ($tax_query, $q_vars['tax_query']);
				}
				
				$query->set( 'tax_query', $tax_query );
				
				/*
				echo '<br><br>tax_query ';
				var_dump($tax_query );
				
				echo '<br><br>query ';
				var_dump($query );
				*/
			} //end if these archives
				
		}  // end if suppress filters

   }

   return $query;
}

//Order confirmation success
add_filter( 'wp_footer', 'custom_lst_order_confirmation_footer' );
function custom_lst_order_confirmation_footer() {
	//If on successful order page
	if ( gb_on_checkout_page() && gb_get_current_checkout_page() == 'confirmation' ) {
		// Get the Transaction
		global $gb_purchase_confirmation_id;
		//$purchase = Group_Buying_Purchase::get_instance($gb_purchase_confirmation_id);
		//$total = $purchase->get_total();
		//$products = $purchase->get_products();
		//$user_id = $purchase->get_user();
		//$account = Group_Buying_Account::get_instance($user_id);
		?>
<!-- Google Code for Deal Signup Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 971443539;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "Om0nCJ2IoAoQ05qczwM";
var google_conversion_value = 1.000000;
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">;
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/971443539/?value=1.000000&amp;label=Om0nCJ2IoAoQ05qczwM&amp;guid=ON&amp;script=0">
</div>
</noscript>
        <?php
	}
}

//New Recent Deals widget with Location filters
add_action( 'widgets_init', create_function( '', 'return register_widget("Custom_GroupBuying_RecentDeals");' ) );
class Custom_GroupBuying_RecentDeals extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Dan Cameron
	 */
	function Custom_GroupBuying_RecentDeals() {
		$widget_ops = array( 'description' => gb__( 'With filters by User location. Deals returned are randomized.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Custom Recent Deals (with Locations)' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		do_action( 'pre_custom_recent_deals', $args, $instance );
		global $gb, $wp_query;
		$temp = null;
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$buynow = empty( $instance['buynow'] ) ? 'Buy Now' : $instance['buynow'];
		$deals = apply_filters( 'custom_gb_recent_deals_widget_show', $instance['deals'] );
		if ( is_single() ) {
			$post_not_in = $wp_query->post->ID;
		}
		
		$location = '';

		if ( isset( $_COOKIE[ 'gb_location_preference' ] ) && $_COOKIE[ 'gb_location_preference' ] != '' ) {
			$location = $_COOKIE[ 'gb_location_preference' ];
		}
		if ( $location == '' ) {
			$locations = array();
			$terms = get_the_terms( $post->ID, gb_get_deal_location_tax() );
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$locations[] = $term->slug;
				}
			}
			if ( isset( $locations[0] ) ) {
				$location = $locations[0];
			}
		}
		
		$count = 1;
		$deal_query= null;
		$args=array(
			'post_type' => gb_get_deal_post_type(),
			'post_status' => 'publish',
			'orderby' => 'rand',
			'meta_query' => array(
				array(
					'key' => '_expiration_date',
					'value' => array( 0, current_time( 'timestamp' ) ),
					'compare' => 'NOT BETWEEN'
				) ),
			'posts_per_page' => $deals,
			'post__not_in' => array( $post_not_in )
		);
		
		if ( $location ) {
			$args[gb_get_deal_location_tax()] = apply_filters( 'gb_related_deals_widget_location', $location, $locations );	
		}

		$deal_query = new WP_Query( $args );
		if ( $deal_query->have_posts() ) {
			echo $before_widget;
			echo $before_title . $title . $after_title;
			while ( $deal_query->have_posts() ) : $deal_query->the_post();

			Group_Buying_Controller::load_view( 'widgets/recent-deals.php', array( 'buynow'=>$buynow ) );

			endwhile;
			echo $after_widget;
		}
		$deal_query = null; $deal_query = $temp;
		wp_reset_query();
		do_action( 'post_custom_recent_deals', $args, $instance );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['buynow'] = strip_tags( $new_instance['buynow'] );
		$instance['deals'] = strip_tags( $new_instance['deals'] );
		$instance['show_expired'] = strip_tags( $new_instance['show_expired'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$buynow = esc_attr( $instance['buynow'] );
		$deals = esc_attr( $instance['deals'] );
		$show_expired = esc_attr( $instance['show_expired'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id( 'buynow' ); ?>"><?php gb_e( 'Buy now link text:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'buynow' ); ?>" name="<?php echo $this->get_field_name( 'buynow' ); ?>" type="text" value="<?php echo $buynow; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id( 'deals' ); ?>"><?php gb_e( 'Number of deals to display:' ); ?>
            	<select id="<?php echo $this->get_field_id( 'deals' ); ?>" name="<?php echo $this->get_field_name( 'deals' ); ?>">
					<option value="1">1</option>
					<option value="2"<?php if ( $deals=="2" ) {echo ' selected="selected"';} ?>>2</option>
					<option value="3"<?php if ( $deals=="3" ) {echo ' selected="selected"';} ?>>3</option>
					<option value="4"<?php if ( $deals=="4" ) {echo ' selected="selected"';} ?>>4</option>
					<option value="5"<?php if ( $deals=="5" ) {echo ' selected="selected"';} ?>>5</option>
					<option value="10"<?php if ( $deals=="10" ) {echo ' selected="selected"';} ?>>10</option>
					<option value="15"<?php if ( $deals=="15" ) {echo ' selected="selected"';} ?>>15</option>
					<option value="-1"<?php if ( $deals=="-1" ) {echo ' selected="selected"';} ?>>All</option>
				 </select>
            </label></p>
        <?php
	}
}

//New More Deals widget with for a Merchant
add_action( 'widgets_init', create_function( '', 'return register_widget("Custom_Merchant_RecentDeals");' ) );
class Custom_Merchant_RecentDeals extends WP_Widget {
	function Custom_Merchant_RecentDeals() {
		$widget_ops = array( 'description' => gb__( 'More Deals for a merchant.' ) );
		parent::WP_Widget( false, $name = gb__( 'Group Buying :: Merchant Deals ' ), $widget_ops );
	}
	
	function widget( $args, $instance ) {
		do_action( 'pre_custom_merchant_recent_deals', $args, $instance );
		global $gb, $wp_query;
		$temp = null;
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$buynow = empty( $instance['buynow'] ) ? 'Buy Now' : $instance['buynow'];
		$deals = ($instance['deals']) ? $instance['deals'] : 2;
		
		$merchant_id = false;
		$post_not_in = false;
		if ( is_singular('gb_merchant') ) {
			$merchant_id = $wp_query->post->ID;
		} elseif ( is_singular('gb_deal') ) {
			$post_not_in = $wp_query->post->ID;
			if ( gb_has_merchant ( $wp_query->post->ID ) ) {
				$merchant_id = gb_get_merchant_id( $wp_query->post->ID ); 
			}
		}
		
		//If no merchant
		if ( empty($merchant_id) ) return;
		
		$count = 1;
		$deal_query= null;
		$args=array(
			'post_type' => gb_get_deal_post_type(),
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => '_expiration_date',
					'value' => array( 0, current_time( 'timestamp' ) ),
					'compare' => 'NOT BETWEEN'
				),
				array(
					'key' => '_merchant_id',
					'value' => $merchant_id,
					'compare' => '='
				)),
			'posts_per_page' => $deals,
			
		);
		
		if ( $post_not_in ) {
			$args['post__not_in'] = array( $post_not_in );
		}
		
		$deal_query = new WP_Query( $args );
		if ( $deal_query->have_posts() ) {
			echo $before_widget;
			echo $before_title . $title . $after_title;
			while ( $deal_query->have_posts() ) : $deal_query->the_post();

			Group_Buying_Controller::load_view( 'widgets/recent-deals.php', array( 'buynow'=>$buynow ) );

			endwhile;
			echo $after_widget;
		}
		$deal_query = null; $deal_query = $temp;
		wp_reset_query();
		do_action( 'post_custom_merchant_recent_deals', $args, $instance );
		
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['buynow'] = strip_tags( $new_instance['buynow'] );
		$instance['deals'] = strip_tags( $new_instance['deals'] );
		$instance['show_expired'] = strip_tags( $new_instance['show_expired'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$buynow = esc_attr( $instance['buynow'] );
		$deals = esc_attr( $instance['deals'] );
		$show_expired = esc_attr( $instance['show_expired'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id( 'buynow' ); ?>"><?php gb_e( 'Buy now link text:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'buynow' ); ?>" name="<?php echo $this->get_field_name( 'buynow' ); ?>" type="text" value="<?php echo $buynow; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id( 'deals' ); ?>"><?php gb_e( 'Number of deals to display:' ); ?>
            	<select id="<?php echo $this->get_field_id( 'deals' ); ?>" name="<?php echo $this->get_field_name( 'deals' ); ?>">
					<option value="1">1</option>
					<option value="2"<?php if ( $deals=="2" ) {echo ' selected="selected"';} ?>>2</option>
					<option value="3"<?php if ( $deals=="3" ) {echo ' selected="selected"';} ?>>3</option>
					<option value="4"<?php if ( $deals=="4" ) {echo ' selected="selected"';} ?>>4</option>
					<option value="5"<?php if ( $deals=="5" ) {echo ' selected="selected"';} ?>>5</option>
					<option value="10"<?php if ( $deals=="10" ) {echo ' selected="selected"';} ?>>10</option>
					<option value="15"<?php if ( $deals=="15" ) {echo ' selected="selected"';} ?>>15</option>
					<option value="-1"<?php if ( $deals=="-1" ) {echo ' selected="selected"';} ?>>All</option>
				 </select>
            </label></p>
        <?php
	}
}


//Add amdmin button to Fix for bit.ly share link transient cache bug
add_action('init', 'sf_fix_bitly_init_reset_deal_metabox', 20);
function sf_fix_bitly_init_reset_deal_metabox() {
	if ( is_admin() ) {
		add_action( 'add_meta_boxes', 'sf_fix_bitly_add_reset_deal_metabox' );
	}
}
function sf_fix_bitly_add_reset_deal_metabox() {
	add_meta_box( 'sf_bitly_reset_box', 'Fix Admin bit.ly Share URL', 'sf_fix_bitly_show_reset_deal_metabox', Group_Buying_Deal::POST_TYPE, 'side', 'high' );
}
function sf_fix_bitly_show_reset_deal_metabox( $post, $metabox ) {
	if ( $metabox['id'] == 'sf_bitly_reset_box' ) {
		$reset_url = add_query_arg('resetsharelink', 1, get_permalink($post->ID));
		?>
		Fix & Reset bit.ly Share URL for Admin user:
		<a type="button" class="button" target="_blank" href="<?php echo $reset_url; ?>" title="Reset Share Link"><?php gb_e( 'Reset Share Link' ); ?></a>
		<?php
	}
}
//Fix the bit.ly shareurl
add_action('template_redirect', 'init_sf_fix_bitly_url');
function init_sf_fix_bitly_url() {
	if ( is_singular('gb_deal') && isset($_GET['resetsharelink']) && current_user_can('administrator') ) {
		$deal_id = get_the_ID();
	
		$user_id = get_current_user_id();
		$userdata = get_user_by( 'id', $user_id );
		$member_login = $userdata->user_login;
		
		//Delete
		$cache_key = 'gb_bitly_share_v2_'.$member_login.'_dealid_'.$deal_id;
		$cache = get_transient( $cache_key );
		delete_transient($cache_key);
		
		$link = gb_get_share_link();
	}	
}

//add_filter('gb_get_share_link', 'sf_lst_bitly_fix_gb_get_share_link', 10, 4);
function sf_lst_bitly_fix_gb_get_share_link( $link, $deal_id, $member_login, $directlink ) {
	if ( $directlink == FALSE ) {
		//if ( $member_login && current_user_can('manage_options') ) {
		if ( is_singular('gb_deal') && $_GET['resetsharelink'] && current_user_can('administrator') ) {
			
			//Delete transient cache
			$cache_key = 'gb_bitly_share_v2_'.$member_login.'_dealid_'.$deal_id;
			$cache = get_transient( $cache_key );
			delete_transient($cache_key);
			
			//Get new share link
			$deal_url = get_permalink($deal_id);
			$link = Group_Buying_Affiliates::maybe_short_share_url($deal_url, $member_login, $deal_id, TRUE);
		}
	}
	return $link;
}



