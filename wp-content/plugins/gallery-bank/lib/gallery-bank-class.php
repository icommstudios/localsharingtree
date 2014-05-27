<?php
//--------------------------------------------------------------------------------------------------------------//
// CODE FOR CREATING MENUS
//---------------------------------------------------------------------------------------------------------------//

function create_global_menus_for_gallery_bank()
{
	global $wpdb;
    add_menu_page("Gallery Bank", __("Gallery Bank", gallery_bank), "read", "gallery_bank", "", GALLERY_BK_PLUGIN_URL . "/assets/images/icon.png");
    add_submenu_page("gallery_bank", "Dashboard", __("Dashboard", gallery_bank), "read", "gallery_bank", "gallery_bank");
	add_submenu_page("gallery_bank", "Short-Codes", __("Short-Codes", gallery_bank), "read", "gallery_bank_shortcode", "gallery_bank_shortcode");
    add_submenu_page("gallery_bank", "Album Sorting", __("Album Sorting", gallery_bank), "read", "gallery_album_sorting", "gallery_album_sorting");
    add_submenu_page("gallery_bank", "Gallery Bank", __("Global Settings", gallery_bank), "read", "global_settings", "global_settings");
    add_submenu_page("gallery_bank", "System Status", __("System Status", gallery_bank), "read", "gallery_bank_system_status", "gallery_bank_system_status");
	add_submenu_page("gallery_bank", "Purchase Pro Version", __("Purchase Pro Version", gallery_bank), "read", "gallery_bank_purchase", "gallery_bank_purchase");
    add_submenu_page("", "", "", "read", "view_album", "view_album");
    add_submenu_page("", "", "", "read", "album_preview", "album_preview");
    add_submenu_page("", "", "", "read", "save_album", "save_album");
    add_submenu_page("", "", "", "read", "images_sorting", "images_sorting");
}
//--------------------------------------------------------------------------------------------------------------//
// FUNCTIONS FOR REPLACING TABLE NAMES
//--------------------------------------------------------------------------------------------------------------//

function gallery_bank_albums()
{
    global $wpdb;
    return $wpdb->prefix . "gallery_albums";
}

function gallery_bank_pics()
{
    global $wpdb;
    return $wpdb->prefix . "gallery_pics";
}

function gallery_bank_settings()
{
    global $wpdb;
    return $wpdb->prefix . "gallery_settings";
}

//--------------------------------------------------------------------------------------------------------------//
// CODE FOR CREATING PAGES
//---------------------------------------------------------------------------------------------------------------//
function gallery_bank()
{
    include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
    include_once GALLERY_BK_PLUGIN_DIR . "/views/dashboard.php";
}


function gallery_bank_shortcode()
{
	include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
	include_once GALLERY_BK_PLUGIN_DIR . "/views/shortcode.php";
}
function save_album()
{
	global $wpdb;
	$album_count = $wpdb->get_var
	(
		"SELECT count(album_id) FROM ".gallery_bank_albums()
	);
	if($album_count < 3)
	{
		global $wpdb;
		include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
		include_once GALLERY_BK_PLUGIN_DIR . "/views/edit-album.php";
	}
	else 
	{
		?>
		<script type="text/javascript">
			window.location.href="admin.php?page=gallery_bank";
		</script>
		<?php
	}
}

function global_settings()
{
    include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
    include_once GALLERY_BK_PLUGIN_DIR . "/views/settings.php";
}

function gallery_album_sorting()
{
    include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
    include_once GALLERY_BK_PLUGIN_DIR . "/views/album-sorting.php";
}

function images_sorting()
{
    include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
    include_once GALLERY_BK_PLUGIN_DIR . "/views/images-sorting.php";
}

function album_preview()
{
    include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
    include_once GALLERY_BK_PLUGIN_DIR . "/views/album-preview.php";
}


function gallery_bank_system_status()
{
    include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
    include_once GALLERY_BK_PLUGIN_DIR . "/views/gallery-bank-system-report.php";
}

function gallery_bank_purchase()
{
	include_once GALLERY_BK_PLUGIN_DIR . "/views/header.php";
    include_once GALLERY_BK_PLUGIN_DIR . "/views/purchase_pro_version.php";
}
//--------------------------------------------------------------------------------------------------------------//
//CODE FOR CALLING JAVASCRIPT FUNCTIONS
//--------------------------------------------------------------------------------------------------------------//
function backend_scripts_calls()
{
    wp_enqueue_script("jquery");
    wp_enqueue_script("jquery-ui-draggable");
    wp_enqueue_script("jquery-ui-sortable");
    wp_enqueue_script("jquery-ui-dialog");
    wp_enqueue_script("farbtastic");
    wp_enqueue_script("imgLiquid.js", GALLERY_BK_PLUGIN_URL . "/assets/js/imgLiquid.js");
    wp_enqueue_script("jquery.dataTables.min.js", GALLERY_BK_PLUGIN_URL . "/assets/js/jquery.dataTables.min.js");
    wp_enqueue_script("jquery.validate.min.js", GALLERY_BK_PLUGIN_URL . "/assets/js/jquery.validate.min.js");
    wp_enqueue_script("plupload.full.min.js", GALLERY_BK_PLUGIN_URL . "/assets/js/plupload.full.min.js");
    wp_enqueue_script("jquery.plupload.queue.js", GALLERY_BK_PLUGIN_URL . "/assets/js/jquery.plupload.queue.js");
    wp_enqueue_script("jquery.Tooltip.js", GALLERY_BK_PLUGIN_URL . "/assets/js/jquery.Tooltip.js");
    wp_enqueue_script("bootstrap.js", GALLERY_BK_PLUGIN_URL . "/assets/js/bootstrap.js");
	wp_enqueue_script("jquery.prettyPhoto.js", GALLERY_BK_PLUGIN_URL . "/assets/js/jquery.prettyPhoto.js");
}

function frontend_plugin_js_scripts_gallery_bank()
{
    wp_enqueue_script("jquery");
    wp_enqueue_script("jquery.masonry.min.js", GALLERY_BK_PLUGIN_URL . "/assets/js/jquery.masonry.min.js");
    wp_enqueue_script("isotope.pkgd.js", GALLERY_BK_PLUGIN_URL . "/assets/js/isotope.pkgd.js");
    wp_enqueue_script("imgLiquid.js", GALLERY_BK_PLUGIN_URL . "/assets/js/imgLiquid.js");
	wp_enqueue_script("jquery.prettyPhoto.js", GALLERY_BK_PLUGIN_URL . "/assets/js/jquery.prettyPhoto.js");
}

//--------------------------------------------------------------------------------------------------------------//
// CODE FOR CALLING STYLE SHEETS
function backend_css_calls()
{
    wp_enqueue_style("farbtastic");
	wp_enqueue_style("wp-jquery-ui-dialog");
    wp_enqueue_style("jquery.plupload.queue.css", GALLERY_BK_PLUGIN_URL . "/assets/css/jquery.plupload.queue.css");
    wp_enqueue_style("stylesheet.css", GALLERY_BK_PLUGIN_URL . "/assets/css/stylesheet.css");
    wp_enqueue_style("font-awesome.css", GALLERY_BK_PLUGIN_URL . "/assets/css/font-awesome/css/font-awesome.css");
    wp_enqueue_style("system-message.css", GALLERY_BK_PLUGIN_URL . "/assets/css/system-message.css");
    wp_enqueue_style("gallery-bank.css", GALLERY_BK_PLUGIN_URL . "/assets/css/gallery-bank.css");
	wp_enqueue_style("prettyPhoto.css", GALLERY_BK_PLUGIN_URL . "/assets/css/prettyPhoto.css");
	wp_enqueue_style("css3_grid_style.css", GALLERY_BK_PLUGIN_URL . "/assets/css/css3_grid_style.css");
	wp_enqueue_style("responsive.css", GALLERY_BK_PLUGIN_URL . "/assets/css/responsive.css");
}

function frontend_plugin_css_scripts_gallery_bank()
{
    wp_enqueue_style("gallery-bank.css", GALLERY_BK_PLUGIN_URL . "/assets/css/gallery-bank.css");
	wp_enqueue_style("prettyPhoto.css", GALLERY_BK_PLUGIN_URL . "/assets/css/prettyPhoto.css");
}

//--------------------------------------------------------------------------------------------------------------//
// REGISTER AJAX BASED FUNCTIONS TO BE CALLED ON ACTION TYPE AS PER WORDPRESS GUIDELINES
//--------------------------------------------------------------------------------------------------------------//
if (isset($_REQUEST["action"])) {
    switch ($_REQUEST["action"]) {
        case "add_new_album_library":
            add_action("admin_init", "album_gallery_library");
            function album_gallery_library()
            {
                include_once GALLERY_BK_PLUGIN_DIR . "/lib/add-new-album-class.php";
            }
            break;
        case "front_view_all_albums_library":
            add_action("admin_init", "front_view_all_albums_library");
            function front_view_all_albums_library()
            {
                include_once GALLERY_BK_PLUGIN_DIR . "/lib/front-view-all-albums-class.php";
            }
            break;
    }
}
/*****************************************************************************************************************/
function gallery_bank_enqueue_pointer_script_style()
{
    $enqueue_pointer_script_style = false;

    // Get array list of dismissed pointers for current user and convert it to array

    $dismissed_pointers = explode(",", get_user_meta(get_current_user_id(), "dismissed_wp_pointers", true));

    // Check if our pointer is not among dismissed ones
    if (!in_array("gallery_bank_pointer", $dismissed_pointers)) {
        $enqueue_pointer_script_style = true;

        // Add footer scripts using callback function
        add_action("admin_print_footer_scripts", "gallery_bank_pointer_print_scripts");
    }

    // Enqueue pointer CSS and JS files, if needed
    if ($enqueue_pointer_script_style) {
        wp_enqueue_style("wp-pointer");
        wp_enqueue_script("wp-pointer");
    }
}

add_action("admin_enqueue_scripts", "gallery_bank_enqueue_pointer_script_style");

function gallery_bank_pointer_print_scripts()
{

    $pointer_content = "<h3>Gallery Bank</h3>";
    $pointer_content .= "<p>If you are using Gallery Bank for the first time, you can view this <a href='http://tech-banker.com/gallery-bank/' target='_blank'>link</a> to know about the features.</p>";
    ?>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $("#toplevel_page_gallery_bank").pointer({
                content: "<?php echo $pointer_content; ?>",
                position: {
                    edge: "left", // arrow direction
                    align: "center" // vertical alignment
                },
                pointerWidth: 350,
                close: function () {
                    $.post(ajaxurl, {
                        pointer: "gallery_bank_pointer", // pointer ID
                        action: "dismiss-wp-pointer"
                    });
                }
            }).pointer("open");
        });
    </script>

<?php
}

/**************************************************************************************************/
add_action("media_buttons_context", "add_gallery_shortcode_button", 1);
function add_gallery_shortcode_button($context)
{
    add_thickbox();
    $context .= "<a href=\"#TB_inline?width=500&height=600&inlineId=my-gallery-content-id\"  class=\"button thickbox\"
     title=\"" . __("Add Gallery using Gallery Bank", gallery_bank) . "\"><span class=\"gallery_icon\"></span> Gallery Bank</a>";
    return $context;
}

add_action("admin_footer", "add_gallery_bank_popup");

function add_gallery_bank_popup()
{
    add_thickbox();
    require_once GALLERY_BK_PLUGIN_DIR . "/front_views/gallery-bank-shortcode.php";
}

function gallery_bank_short_code($atts)
{
    extract(shortcode_atts(array(
        "album_id" => "",
        "type" => "",
        "format" => "",
        "title" => "",
        "desc" => "",
        "img_in_row" => "",
        "responsive" => "",
        "albums_in_row" => "",
        "special_effect" => "",
        "animation_effect" => "",
        "image_width" => "",
        "album_title" => "",
        "thumb_width" => "",
        "thumb_height" => "",
        "widget" => "",
    ), $atts));
    return extract_short_code_for_gallery_images($album_id, $type, $format, $title, $desc, $img_in_row, $responsive, $albums_in_row, $special_effect, $animation_effect, $image_width, $album_title, $thumb_width, $thumb_height, $widget);
}
function extract_short_code_for_gallery_images($album_id, $album_type, $gallery_type, $img_title, $img_desc, $img_in_row, $responsive, $albums_in_row, $special_effect, $animation_effect, $image_width, $album_title, $thumb_width, $thumb_height, $widget)
{
    ob_start();
    global $wpdb;
    include GALLERY_BK_PLUGIN_DIR . "/front_views/includes_common_before.php";

    switch ($album_type) {
        case "images":
            switch ($gallery_type) {
                case "masonry":
                    include GALLERY_BK_PLUGIN_DIR . "/front_views/masonry-gallery.php";
                    break;
                case "thumbnail":
                    include GALLERY_BK_PLUGIN_DIR . "/front_views/thumbnail-gallery.php";
                    break;
            }
            break;
        case "grid":
            include GALLERY_BK_PLUGIN_DIR . "/front_views/grid-albums.php";
            break;
        case "list":
            include GALLERY_BK_PLUGIN_DIR . "/front_views/listed-album.php";
            break;
        case "individual":
            include GALLERY_BK_PLUGIN_DIR . "/front_views/single-album.php";
            break;
    }
    include GALLERY_BK_PLUGIN_DIR . "/front_views/includes_common_after.php";
    $gallery_bank_output_album = ob_get_clean();
    wp_reset_query();
    return $gallery_bank_output_album;
}

function array_iunique($array)
{
    return array_intersect_key(
        $array,
        array_unique(array_map("StrToUpper", $array))
    );
}


/*****************************************************************************************************************/
add_shortcode("gallery_bank", "gallery_bank_short_code");
add_action("admin_init", "backend_scripts_calls");
add_action("admin_init", "backend_css_calls");
add_action("init", "frontend_plugin_js_scripts_gallery_bank");
add_action("init", "frontend_plugin_css_scripts_gallery_bank");
add_action("admin_menu", "create_global_menus_for_gallery_bank");