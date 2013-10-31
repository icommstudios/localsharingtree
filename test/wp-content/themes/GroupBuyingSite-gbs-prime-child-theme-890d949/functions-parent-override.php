<?php

/**
 * This function file is loaded after the parent theme's function file. It's a great way to override functions, e.g. add_image_size sizes.
 *
 *
 */


// Include Custom functions
require_once('customfunctions/sf-additional-notifications/SF_AdditionalNotifications.class.php');
require_once('customfunctions/sf-account-fields/SF_AccountFields.class.php');
require_once('customfunctions/sf-merchant-fields/SF_merchantFields.class.php');
require_once('customfunctions/sf-featured-deal/SF_Featured_Deals.class.php');
require_once('customfunctions/sf-deal-fields/SF_DealFields.class.php');

// Account Registration fields
add_filter('gb_account_register_contact_info_fields', 'custom_fields_changes', 999);
add_filter('gb_account_edit_contact_fields', 'custom_fields_changes', 999);
//add_filter('gb_checkout_fields_billing', 'custom_fields_changes', 999);
//add_filter('gb_checkout_fields_shipping', 'custom_fields_changes', 999);
function custom_fields_changes($fields) {
	/*
	if (isset($fields['first_name'])) {
		$fields['first_name']['attributes'] = array_merge((array)$fields['first_name']['attributes'], array('maxlength' => 100));
	}
	if (isset($fields['last_name'])) {
		$fields['last_name']['attributes'] = array_merge((array)$fields['last_name']['attributes'], array('maxlength' => 100));
	}
	*/
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
	
	return $fields;
	
}

// Disable Guest Purchases
add_filter( 'gb_account_register_user_fields', 'remove_guest_registration_field', 100, 1 );
function remove_guest_registration_field( $fields = array() ) {
unset($fields['guest_purchase']);
return $fields;
}

//Custom footer scripts
add_action('wp_footer', 'custom_footer_scripts');
function custom_footer_scripts() {
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
			
			wp_redirect( site_url('/home/') );
			exit();
			
		} else {
			
			//Not logged in, but location set
			if ( isset($_GET['location']) && term_exists( $_GET[ 'location' ]) ) {
				//$deals_link = gb_get_deals_link( $_GET['location'] );
				//wp_redirect( $deals_link  );
				wp_redirect( site_url('/home/') );
				exit();
			} elseif ( isset($_COOKIE['gb_location_preference']) && term_exists( $_COOKIE[ 'gb_location_preference' ]) ) {
				//$deals_link = gb_get_deals_link( $_COOKIE[ 'gb_location_preference' ] );
				//wp_redirect( $deals_link  );
				wp_redirect( site_url('/home/') );
				exit();
			}
			
		}
	}
	
	return;	
}

//Handle Merchant Register & Submit Deal links & redirect
add_action( 'init', 'custom_handle_merchant_register_deals', 99 );
function custom_handle_merchant_register_deals() {
	if ( !isset( $_GET['action_page'] ) ) return;
	
	//Register merchant
	if ( $_GET['action_page'] == 'register_merchant' ) {
		if ( !is_user_logged_in() ) {
			Group_Buying_Controller::set_message( gb__( 'In order to Register Your Business, You Must Register as a User, or Log In First.' ), Group_Buying_Controller::MESSAGE_STATUS_INFO );
			//Redirect to login
			Group_Buying_Controller::login_required();
		} else {
			//Already has merchant?
			$has_merchant = gb_get_merchants_by_account( get_current_user_id() );
			if ( empty($has_merchant) ) {
				wp_redirect( gb_get_merchant_registration_url() );
				exit();
			} else {
				Group_Buying_Controller::set_message( gb__( 'You have already registered your business, if you need to make changes, you can do so on your Account page.' ), Group_Buying_Controller::MESSAGE_STATUS_INFO );
				//wp_redirect( gb_get_merchant_account_url() );
				wp_redirect( gb_get_account_url() );
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


// Add Page number navigation

function wp_pagination() {
global $wp_query;
$big = 12345678;
$page_format = paginate_links( array(
    'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
    'format' => '?paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' => $wp_query->max_num_pages,
    'type'  => 'array'
) );
if( is_array($page_format) ) {
            $paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');
            echo '<div><ul>';
            echo '<li><span>'. $paged . ' of ' . $wp_query->max_num_pages .'</span></li>';
            foreach ( $page_format as $page ) {
                    echo "<li>$page</li>";
            }
           echo '</ul></div>';
}
}
