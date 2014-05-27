<?php
global $wpdb;
$gb_lang = array();
$gb_translated_lang = array();
array_push($gb_lang, "ar", "bg_BG", "da_DK", "hu_HU", "id_ID", 
"ja", "ko_KR", "ms_MY", "pl_PL", "ro_RO", "sk_SK", "sl_SI", "sq_AL",
 "sr_RS", "th", "zh_CN");
array_push($gb_translated_lang, "en_GB", "en_US", "es_ES", "nl_NL", "uk", "sv_SE", "fr_FR", "pt_PT", "pt_BR", "et", "it_IT",
 "de_DE", "fi", "he_IL", "ru_RU", "be_BY", "tr");
$language = get_locale();
$show_banner = get_option("gallery-bank-banner");
if($show_banner == "")
{
	echo'<div id="ux_buy_pro" class="updated">
	 		<div class="gb_buy_pro">
		 		<div class="gb_text_control">
			 		It\'s time to upgrade your <strong>Gallery Bank Standard Edition</strong> to <strong>Premium</strong> Edition!<br />
			 		<span>Extend standard plugin functionality with 200+ awesome features! <br/>Go for Premium Version Now! Starting at <strong>11£/- only</strong></span>
		 		</div>
		 		<a class="button gb_message_buttons" href="admin.php?page=gallery_bank_purchase&msg=no">CLOSE</a>
		 		<a class="button gb_message_buttons" target="_blank" href="http://wordpress.org/support/view/plugin-reviews/gallery-bank?filter=5">RATE US 5 ★</a>
		 		<a class="button gb_message_buttons" target="_blank" href="http://tech-banker.com/gallery-bank/demo/">LIVE DEMO</a>
		 		<a class="button gb_message_buttons" target="_blank" href="http://tech-banker.com/gallery-bank/">UPGRADE NOW</a>
	 		</div>
	 	</div>';
}
?>
<img src="<?php echo GALLERY_BK_PLUGIN_URL . '/assets/images/gallery-bank-logo.png'; ?>" style="margin-top:20px"/>
<script>
jQuery(document).ready(function()
{
	jQuery(".nav-tab-wrapper > a#<?php echo $_REQUEST["page"];?>").addClass("nav-tab-active");
});
</script>
<?php
switch($_REQUEST["page"])
{
	case "gallery_bank":
		$page = "Dashboard";
	break;
	case "gallery_bank_shortcode":
		$page = "Short-Codes";
	break;
	case "gallery_album_sorting":
		$page = "Album Sorting";
	break;
	case "global_settings":
		$page = "Global Settings";
	break;
	case "gallery_bank_system_status":
		$page = "System Status";
	break;
	case "gallery_bank_purchase":
		$page = "Purchase Pro Version";
	break;
	case "save_album":
		$page = "Album";
	break;
	case "images_sorting":
		$page = "Re-order Images";
	break;
	case "album_preview":
		$page = "Album Preview";
	break;
}
?>
<ul class="breadcrumb" style="margin-top: 10px;">
	<li>
		<i class="icon-home"></i>
		<a href="admin.php?page=gallery_bank"><?php _e("Gallery Bank", gallery_bank); ?></a>
		<span class="divider">/</span>
		<a href="#"><?php _e($page, gallery_bank); ?></a>
	</li>
</ul>
<h2 class="nav-tab-wrapper">
	<a class="nav-tab " id="gallery_bank" href="admin.php?page=gallery_bank">Dashboard</a>
	<a class="nav-tab " id="gallery_bank_shortcode" href="admin.php?page=gallery_bank_shortcode"><?php _e("Short-Codes", gallery_bank);?></a>
	<a class="nav-tab " id="gallery_album_sorting" href="admin.php?page=gallery_album_sorting"><?php _e("Album Sorting", gallery_bank);?></a>
	<a class="nav-tab " id="global_settings" href="admin.php?page=global_settings"><?php _e("Global Settings", gallery_bank);?></a>
	<a class="nav-tab " id="gallery_bank_system_status" href="admin.php?page=gallery_bank_system_status"><?php _e("System Status", gallery_bank);?></a>
	<a class="nav-tab " id="gallery_bank_purchase" href="admin.php?page=gallery_bank_purchase"><?php _e("Purchase Pro Version", gallery_bank);?></a>
</h2>
<?php
if(in_array($language, $gb_lang))
{
	?>
	<div class="message red" style="display: block;margin-top:30px">
		<span style="padding: 4px 0;">
			<strong><p style="font:12px/1.0em Arial !important;">This plugin language is translated with the help of Google Translator.</p>
				<p style="font:12px/1.0em Arial !important;">If you would like to translate & help us, we will reward you with a free Pro Version License of Gallery Bank worth 18£.</p>
				<p style="font:12px/1.0em Arial !important;">Contact Us at <a target="_blank" href="http://tech-banker.com">http://tech-banker.com</a> or email us at <a href="mailto:support@tech-banker.com">support@tech-banker.com</a></p>
			</strong>
		</span>
	</div>
	<?php
}
elseif(!(in_array($language, $gb_translated_lang)) && !(in_array($language, $gb_lang)) && $language != "")
{
	?>
	<div class="message red" style="display: block;margin-top:30px">
		<span style="padding: 4px 0;">
			<strong><p style="font:12px/1.0em Arial !important;">If you would like to translate Gallery Bank in your native language, we will reward you with a free Pro Version License of Gallery Bank worth 18£.</p>
				<p style="font:12px/1.0em Arial !important;">Contact Us at <a target="_blank" href="http://tech-banker.com">http://tech-banker.com</a> or email us at <a href="mailto:support@tech-banker.com">support@tech-banker.com</a></p>
			</strong>
		</span>
	</div>
	<?php
}
if (!(is_dir(GALLERY_MAIN_THUMB_DIR)))
{
	if(!(is_dir_empty(GALLERY_MAIN_THUMB_DIR)))
	{
		?>
		<div class="message red" style="display: block;margin-top:15px">
			<span>
				<strong>If you are getting problems with thumbnails, then you need to set 777(write) permissions to <?php echo GALLERY_MAIN_DIR ?> (recursive files & directories) in order to save the images/thumbnails. </strong>
			</span>
		</div>
		<?php
	}
}
function is_dir_empty($dir)
{
	if (!is_readable($dir)) return NULL; 
	$handle = opendir($dir);
	while (false !== ($entry = readdir($handle))) {
		if ($entry != "." && $entry != "..") {
			return FALSE;
		}
	}
	return TRUE;
}
?>