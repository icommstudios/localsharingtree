<?php
/*
 Plugin Name: Gallery Bank Standard Edition
 Plugin URI: http://tech-banker.com
 Description: Gallery Bank is an easy to use Responsive WordPress Gallery Plugin for photos, videos, galleries and albums.
 Author: Tech Banker
 Version: 3.0.5
 Author URI: http://tech-banker.com
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   Define   Constants  ///////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!defined("GALLERY_DEBUG_MODE")) define("GALLERY_DEBUG_MODE", false);
if (!defined("GALLERY_BK_FILE")) define("GALLERY_BK_FILE", __FILE__);
if (!defined("GALLERY_CONTENT_DIR")) define("GALLERY_CONTENT_DIR", ABSPATH . "wp-content");
if (!defined("GALLERY_MAIN_DIR")) define("GALLERY_MAIN_DIR", ABSPATH . "wp-content/gallery-bank");
if (!defined("GALLERY_MAIN_UPLOAD_DIR")) define("GALLERY_MAIN_UPLOAD_DIR", ABSPATH . "wp-content/gallery-bank/gallery-uploads/");
if (!defined("GALLERY_MAIN_THUMB_DIR")) define("GALLERY_MAIN_THUMB_DIR", ABSPATH . "wp-content/gallery-bank/thumbs/");
if (!defined("GALLERY_MAIN_ALB_THUMB_DIR")) define("GALLERY_MAIN_ALB_THUMB_DIR", ABSPATH . "wp-content/gallery-bank/album-thumbs/");
if (!defined("GALLERY_CONTENT_URL")) define("GALLERY_CONTENT_URL", site_url() . "/wp-content");
if (!defined("GALLERY_PLUGIN_DIR")) define("GALLERY_PLUGIN_DIR", GALLERY_CONTENT_DIR . "/plugins");
if (!defined("GALLERY_PLUGIN_URL")) define("GALLERY_PLUGIN_URL", GALLERY_CONTENT_URL . "/plugins");
if (!defined("GALLERY_BK_PLUGIN_FILENAME")) define("GALLERY_BK_PLUGIN_FILENAME", basename(__FILE__));
if (!defined("GALLERY_BK_PLUGIN_DIRNAME")) define("GALLERY_BK_PLUGIN_DIRNAME", plugin_basename(dirname(__FILE__)));
if (!defined("GALLERY_BK_PLUGIN_DIR")) define("GALLERY_BK_PLUGIN_DIR", GALLERY_PLUGIN_DIR . "/" . GALLERY_BK_PLUGIN_DIRNAME);
if (!defined("GALLERY_BK_PLUGIN_URL")) define("GALLERY_BK_PLUGIN_URL", site_url() . "/wp-content/plugins/" . GALLERY_BK_PLUGIN_DIRNAME);

if (!defined("GALLERY_BK_THUMB_URL")) define("GALLERY_BK_THUMB_URL", site_url() . "/wp-content/gallery-bank/gallery-uploads/");
if (!defined("GALLERY_BK_THUMB_SMALL_URL")) define("GALLERY_BK_THUMB_SMALL_URL", site_url() . "/wp-content/gallery-bank/thumbs/");
if (!defined("GALLERY_BK_ALBUM_THUMB_URL")) define("GALLERY_BK_ALBUM_THUMB_URL", site_url() . "/wp-content/gallery-bank/album-thumbs/");
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
    global $wp_admin_bar;
    if (!is_user_logged_in()) {
        return;
    }
	$wp_admin_bar->add_menu(array(
        "id" => "gallery_bank_links",
        "title" => __("<img src=\"" . GALLERY_BK_PLUGIN_URL . "/assets/images/icon.png\" width=\"25\"
        height=\"25\" style=\"vertical-align:text-top; margin-right:5px;\" />Gallery Bank"),
        "href" => __(site_url() . "/wp-admin/admin.php?page=gallery_bank"),
    ));

    $wp_admin_bar->add_menu(array(
        "parent" => "gallery_bank_links",
        "id" => "dashboard_links",
        "href" => site_url() . "/wp-admin/admin.php?page=gallery_bank",
        "title" => __("Dashboard", gallery_bank))
    );

    $wp_admin_bar->add_menu(array(
        "parent" => "gallery_bank_links",
        "id" => "add_new_album_links",
        "href" => site_url() . "/wp-admin/admin.php?page=add_album",
        "title" => __("Add New Album", gallery_bank))
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
}
$version = get_option("gallery-bank-pro-edition");
if($version == "" || $version == "3.0")
{
	add_action('admin_init', 'plugin_install_script_for_gallery_bank');
} 
add_action("admin_bar_menu", "add_gallery_bank_icon", 100);
add_action("plugins_loaded", "gallery_bank_plugin_load_text_domain");
register_activation_hook(__FILE__, "plugin_install_script_for_gallery_bank");
register_uninstall_hook(__FILE__, "plugin_uninstall_script_for_gallery_bank");
/*************************************************************************************/
?>