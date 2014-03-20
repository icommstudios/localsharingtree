<?php
$gb_lang = array();
array_push($gb_lang, "ar", "bg_BG", "da_DK", "de_DE", "fi_FI", "fr_FR", "he_IL", "hu_HU", "id_ID",
 "it_IT", "ja", "ko_KR", "ms_MY", "nl_NL", "pl_PL", "pt_BR", "pt_PT", "ro_RO", "ru_RU", "sk_SK", "sl_SI", "sq_AL",
 "sr_RS", "sv_SE", "th", "tr", "zh_CN");
$language = get_locale();
?>

<img src="<?php echo GALLERY_BK_PLUGIN_URL . '/assets/images/gallery-bank-logo.png'; ?>" style="margin-top:20px"/>
<a href="http://tech-banker.com/gallery-bank/" target="_blank">
	<img src="<?php echo GALLERY_BK_PLUGIN_URL . '/assets/images/banner.jpg'; ?>" style="margin-top:20px; float:right;"/>
</a>
<?php
if(in_array($language, $gb_lang))
{
	?>
	<div class="message red" style="display: block;margin-top:30px">
		<span style="padding: 4px 0;">
			<strong><p style="font:12px/1.0em Arial !important;">This plugin language is translated with the help of Google Translator.</p>
				<p style="font:12px/1.0em Arial !important;">If you would like to translate & help us, we will reward you with a free Pro Version License of Gallery Bank worth 16£.</p>
				<p style="font:12px/1.0em Arial !important;">Contact Us at <a target="_blank" href="http://tech-banker.com">http://tech-banker.com</a> or email us at <a href="mailto:support@tech-banker.com">support@tech-banker.com</a></p>
			</strong>
		</span>
	</div>
	<?php
}
?>
<div class="message red" style="display: block;margin-top:30px">
	<span>
		<strong>You will be only allowed to add 2 galleries. Kindly purchase Premium Version for full access.</strong>
	</span>
</div>
<?php
if (is_dir(GALLERY_MAIN_THUMB_DIR))
{
	if(is_dir_empty(GALLERY_MAIN_THUMB_DIR))
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