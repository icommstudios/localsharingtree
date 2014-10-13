<?php
/*
 Plugin Name: Gallery Bank Standard Edition
 Plugin URI: http://tech-banker.com
 Description: Gallery Bank is an easy to use Responsive WordPress Gallery Plugin for photos, videos, galleries and albums.
 Author: Tech Banker
 Version: 3.0.62
 Author URI: http://tech-banker.com
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   Define   Constants  ///////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!defined("GALLERY_MAIN_DIR")) define("GALLERY_MAIN_DIR", dirname(dirname(dirname(__FILE__)))."/gallery-bank");
if (!defined("GALLERY_MAIN_UPLOAD_DIR")) define("GALLERY_MAIN_UPLOAD_DIR", dirname(dirname(dirname(__FILE__)))."/gallery-bank/gallery-uploads/");
if (!defined("GALLERY_MAIN_THUMB_DIR")) define("GALLERY_MAIN_THUMB_DIR", dirname(dirname(dirname(__FILE__)))."/gallery-bank/thumbs/");
if (!defined("GALLERY_MAIN_ALB_THUMB_DIR")) define("GALLERY_MAIN_ALB_THUMB_DIR", dirname(dirname(dirname(__FILE__)))."/gallery-bank/album-thumbs/");
if (!defined("GALLERY_BK_PLUGIN_DIRNAME")) define("GALLERY_BK_PLUGIN_DIRNAME", plugin_basename(dirname(__FILE__)));
if (!defined("GALLERY_BK_PLUGIN_DIR")) define("GALLERY_BK_PLUGIN_DIR",  plugin_dir_path( __FILE__ ));
if (!defined("GALLERY_BK_THUMB_URL")) define("GALLERY_BK_THUMB_URL", content_url()."/gallery-bank/gallery-uploads/");
if (!defined("GALLERY_BK_THUMB_SMALL_URL")) define("GALLERY_BK_THUMB_SMALL_URL", content_url()."/gallery-bank/thumbs/");
if (!defined("GALLERY_BK_ALBUM_THUMB_URL")) define("GALLERY_BK_ALBUM_THUMB_URL", content_url()."/gallery-bank/album-thumbs/");
if (!defined("gallery_bank")) define("gallery_bank", "gallery-bank");

if (!is_dir(GALLERY_MAIN_DIR))
{
	wp_mkdir_p(GALLERY_MAIN_DIR);
}
if (!is_dir(GALLERY_MAIN_UPLOAD_DIR))
{
	wp_mkdir_p(GALLERY_MAIN_UPLOAD_DIR);
}
if (!is_dir(GALLERY_MAIN_THUMB_DIR))
{
	wp_mkdir_p(GALLERY_MAIN_THUMB_DIR);
}
if (!is_dir(GALLERY_MAIN_ALB_THUMB_DIR))
{
	wp_mkdir_p(GALLERY_MAIN_ALB_THUMB_DIR);
}
/*************************************************************************************/
if (file_exists(GALLERY_BK_PLUGIN_DIR . "/lib/gallery-bank-class.php")) {
    require_once(GALLERY_BK_PLUGIN_DIR . "/lib/gallery-bank-class.php");
}
/*************************************************************************************/
function plugin_install_script_for_gallery_bank()
{
    include_once GALLERY_BK_PLUGIN_DIR . "/lib/install-script.php";
}
/*************************************************************************************/
function plugin_uninstall_script_for_gallery_bank()
{
	delete_option("gallery-bank-info-popup");
	delete_option("gallery-bank-banner");
}
/*************************************************************************************/
function gallery_bank_plugin_load_text_domain()
{
    if (function_exists("load_plugin_textdomain")) {
        load_plugin_textdomain(gallery_bank, false, GALLERY_BK_PLUGIN_DIRNAME . "/lang");
    }
}

/*************************************************************************************/
function add_gallery_bank_icon($meta = TRUE)
{
    global $wp_admin_bar,$wpdb,$current_user;
    if (!is_user_logged_in()) {
        return;
    }
    
	$last_album_id = $wpdb->get_var
	(
		"SELECT album_id FROM " .gallery_bank_albums(). " order by album_id desc limit 1"
	);
	$id = count($last_album_id) == 0 ? 1 : $last_album_id + 1;
	$album_count = $wpdb->get_var
	(
		"SELECT count(album_id) FROM ".gallery_bank_albums()
	);
	
	$role = $wpdb->prefix . "capabilities";
	$current_user->role = array_keys($current_user->$role);
	$role = $current_user->role[0];
	
	switch ($role) {
		case "administrator":
			$wp_admin_bar->add_menu(array(
		        "id" => "gallery_bank_links",
		        "title" => __("<img src=\"" . plugins_url("/assets/images/icon.png",__FILE__)."\" width=\"25\"
		        height=\"25\" style=\"vertical-align:text-top; margin-right:5px;\" />Gallery Bank"),
		        "href" => __(site_url() . "/wp-admin/admin.php?page=gallery_bank"),
		    ));
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "dashboard_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank",
		        "title" => __("Dashboard", gallery_bank))
		    );
			if($album_count < 3)
			{
			    $wp_admin_bar->add_menu(array(
			        "parent" => "gallery_bank_links",
			        "id" => "add_new_album_links",
			        "href" => site_url() . "/wp-admin/admin.php?page=save_album&album_id=".$id,
			        "title" => __("Add New Album", gallery_bank))
			    );
			}
			
			$wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "shortcode_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_shortcode",
		        "title" => __("Short-Codes", gallery_bank))
		    );
			
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "sorting_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_album_sorting",
		        "title" => __("Album Sorting", gallery_bank))
		    );
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "global_settings_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=global_settings",
		        "title" => __("Global Settings", gallery_bank))
		    );
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "system_status_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_system_status",
		        "title" => __("System Status", gallery_bank))
		    );
		
			$wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "purchase_pro_version_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_purchase",
		        "title" => __("Purchase Pro Version", gallery_bank))
		    );
		break;
		case "editor":
			$wp_admin_bar->add_menu(array(
		        "id" => "gallery_bank_links",
		        "title" => __("<img src=\"" . plugins_url("/assets/images/icon.png",__FILE__)."\" width=\"25\"
		        height=\"25\" style=\"vertical-align:text-top; margin-right:5px;\" />Gallery Bank"),
		        "href" => __(site_url() . "/wp-admin/admin.php?page=gallery_bank"),
		    ));
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "dashboard_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank",
		        "title" => __("Dashboard", gallery_bank))
		    );
			if($album_count < 3)
			{
			    $wp_admin_bar->add_menu(array(
			        "parent" => "gallery_bank_links",
			        "id" => "add_new_album_links",
			        "href" => site_url() . "/wp-admin/admin.php?page=save_album&album_id=".$id,
			        "title" => __("Add New Album", gallery_bank))
			    );
			}
			
			$wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "shortcode_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_shortcode",
		        "title" => __("Short-Codes", gallery_bank))
		    );
			
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "sorting_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_album_sorting",
		        "title" => __("Album Sorting", gallery_bank))
		    );
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "global_settings_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=global_settings",
		        "title" => __("Global Settings", gallery_bank))
		    );
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "system_status_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_system_status",
		        "title" => __("System Status", gallery_bank))
		    );
		
			$wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "purchase_pro_version_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_purchase",
		        "title" => __("Purchase Pro Version", gallery_bank))
		    );
		break;
		case "author":
			$wp_admin_bar->add_menu(array(
		        "id" => "gallery_bank_links",
		        "title" => __("<img src=\"" . plugins_url("/assets/images/icon.png",__FILE__)."\" width=\"25\"
		        height=\"25\" style=\"vertical-align:text-top; margin-right:5px;\" />Gallery Bank"),
		        "href" => __(site_url() . "/wp-admin/admin.php?page=gallery_bank"),
		    ));
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "dashboard_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank",
		        "title" => __("Dashboard", gallery_bank))
		    );
			if($album_count < 3)
			{
			    $wp_admin_bar->add_menu(array(
			        "parent" => "gallery_bank_links",
			        "id" => "add_new_album_links",
			        "href" => site_url() . "/wp-admin/admin.php?page=save_album&album_id=".$id,
			        "title" => __("Add New Album", gallery_bank))
			    );
			}
			
			$wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "shortcode_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_shortcode",
		        "title" => __("Short-Codes", gallery_bank))
		    );
			
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "sorting_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_album_sorting",
		        "title" => __("Album Sorting", gallery_bank))
		    );
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "global_settings_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=global_settings",
		        "title" => __("Global Settings", gallery_bank))
		    );
		
		    $wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "system_status_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_system_status",
		        "title" => __("System Status", gallery_bank))
		    );
		
			$wp_admin_bar->add_menu(array(
		        "parent" => "gallery_bank_links",
		        "id" => "purchase_pro_version_links",
		        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank_purchase",
		        "title" => __("Purchase Pro Version", gallery_bank))
		    );
		break;
		case "contributor":
			break;
		case "subscriber":
			break;
	}
}


$version = get_option("gallery-bank-pro-edition");
if($version == "" || $version == "3.0")
{
	add_action("admin_init", "plugin_install_script_for_gallery_bank");
}

add_action("admin_bar_menu", "add_gallery_bank_icon", 100);
add_action("plugins_loaded", "gallery_bank_plugin_load_text_domain");
register_activation_hook(__FILE__, "plugin_install_script_for_gallery_bank");
register_uninstall_hook(__FILE__, "plugin_uninstall_script_for_gallery_bank");
/*************************************************************************************/
?>