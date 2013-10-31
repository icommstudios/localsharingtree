<?php
/* =============================================================================
   DEBUG OPTIONS
   ========================================================================== */

	//ini_set( 'display_errors', 1 );
	//error_reporting( E_ALL );
	//define('SAVEQUERIES', true);
	//define("PREMIUMPRESS_DEMO","1");

/* =============================================================================
   LOAD IN FRAMEWORK
   ========================================================================== */
	  	
	if (!headers_sent()){
	session_start();
	if(isset($_GET['emptyCart'])){ foreach($_SESSION as $key => $value){ unset($_SESSION[$key]); }}	 
	if(!isset($_SESSION['ddc']['cartqty'])) $_SESSION['ddc']['cartqty'] = 0;
	if(!isset($_SESSION['ddc']['price'])) $_SESSION['ddc']['price'] = 0.00;
	}

	define("PREMIUMPRESS_SYSTEM","ShopperPress");  
	define("PREMIUMPRESS_VERSION","7.1.4");
	define("PREMIUMPRESS_VERSION_DATE","5th April, 2013");
 
	
	// LOAD THE PREMIUMPRESS THEME FRAMEWORK

	if(defined('TEMPLATEPATH')){ include("PPT/_config.php"); }
	
/* =============================================================================
   -- END PREMIUMPRESS // ADD YOUR CUSTOM CODE BELOW THIS LINE // PLEASE :)
   ========================================================================== */
/* ============ Add code for show the organization filed in the admin side =============================*/

add_action( 'show_user_profile', 'yoursite_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'yoursite_extra_user_profile_fields' );
function yoursite_extra_user_profile_fields( $user ) {
?>
 <h3><?php _e("Extra profile information", "blank"); ?></h3>
 <table class="form-table">
   <tr>
     <th><label for="organization"><?php _e("Organization"); ?></label></th>
     <td>
       <input type="text" name="organization" id="organization" class="regular-text" 
           value="<?php echo esc_attr( get_the_author_meta( 'organization', $user->ID ) ); ?>" /><br />
       <span class="description"><?php _e("Please enter your organization."); ?></span>
   </td>
   </tr>
 </table>
<?php
}

add_action( 'personal_options_update', 'yoursite_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'yoursite_save_extra_user_profile_fields' );
function yoursite_save_extra_user_profile_fields( $user_id ) {
 $saved = false;
 if ( current_user_can( 'edit_user', $user_id ) ) {
   update_user_meta( $user_id, 'organization', $_POST['organization'] );
   $saved = true;
 }
 return true;
}


?>
