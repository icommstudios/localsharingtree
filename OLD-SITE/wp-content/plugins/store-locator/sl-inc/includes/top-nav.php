<?php
$sl_top_nav_hash[]='information';
$sl_top_nav_links[SL_INFORMATION_PAGE]=__("News & Upgrades", SL_TEXT_DOMAIN);

$sl_top_nav_hash[]='locations';
$sl_top_nav_links[SL_MANAGE_LOCATIONS_PAGE]=__("Locations", SL_TEXT_DOMAIN);
	$sl_top_nav_sub_links['locations'][__("Manage", SL_TEXT_DOMAIN)] = SL_MANAGE_LOCATIONS_PAGE;
	$sl_top_nav_sub_links['locations'][__("Add", SL_TEXT_DOMAIN)] = SL_ADD_LOCATIONS_PAGE;

$sl_top_nav_hash[]='mapdesigner'; 
$sl_top_nav_links[SL_MAP_DESIGNER_PAGE]=__("MapDesigner&trade;", SL_TEXT_DOMAIN);

if (function_exists("do_sl_hook")){
	do_sl_hook("sl_top_nav_links");
}
//do_sl_top_nav();
//function do_sl_top_nav(){
//	global $sl_top_nav_links, $sl_top_nav_sub_links, $sl_top_nav_hash, $sl_version;
	
print "<br>";
$style_var = "";
if (!empty($_POST['sl_thanks'])) {$sl_vars['thanks'] = $_POST['sl_thanks']; unset($_POST);}
$sl_thanks = (!empty($sl_vars['thanks']))? $sl_vars['thanks'] : "";
print <<<EOQ
<ul class="tablist">\n
EOQ;
$ctr=0; $tsn_links_js="<script>\nvar tsn_link_arr = [];"; $tsn_links_output="";
$tm_st = ((time() - strtotime($sl_vars["start"]))/60/60/24>=30);
foreach ($sl_top_nav_links as $key=>$value) {
	$current_var=(preg_match("@$_GET[page]@",$key))? "current_top_link" : "" ;
	if (preg_match("@$_GET[page]@",$key)){
		//$style_var .= "sl_top_nav_init(tsn_link_arr);\n";
	}
print "<li class=\"top_nav_li $sl_top_nav_hash[$ctr]\" id=\"$current_var\"><a href=\"$key\"  id='__$sl_top_nav_hash[$ctr]' class='top_nav_a' style=''>$value</a></li>\n";

	$tsn_links_js.="tsn_link_arr['$sl_top_nav_hash[$ctr]']='';";

	if (!empty($sl_top_nav_sub_links[$sl_top_nav_hash[$ctr]])) {
		$cur = "";
		foreach ($sl_top_nav_sub_links[$sl_top_nav_hash[$ctr]] as $key2=>$value2) {
			if (preg_match("@$sl_top_nav_hash[$ctr]@", $_SERVER['REQUEST_URI'])) {
				if (empty($_GET['pg']) && !preg_match("@&pg@", $value2)) {
					$cur = "current_sub_link";
				} elseif (!empty($_GET['pg']) && preg_match("@$_GET[pg]@", $value2)) {
					$cur = "current_sub_link";
				}  else {
					$cur = "";
				}
				$tsn_links_output.="<a href='$value2' id='$cur'>$key2</a>";
			}
			$tsn_links_js.="tsn_link_arr['$sl_top_nav_hash[$ctr]']+=\"<a href='$value2' id='$cur'>$key2</a>\";";
		}
	}
	$ctr++;
}

$thnks_ct = (!empty($sl_vars['thanks_count']))? $sl_vars['thanks_count'] : 0 ;
if (empty($thnks_ct) || $thnks_ct<=20){
	if (!empty($thnks_ct)){ $sl_vars['thanks_count'] = ($thnks_ct+1); } else { $sl_vars['thanks_count'] = 1; }
} elseif ($thnks_ct>=21 && $sl_thanks!="false" && $sl_thanks!="true") {
	$sl_vars['thanks_count'] = ($thnks_ct+1);
	$style_var .= ($tm_st && $sl_thanks!="false" && $sl_thanks!="true")? "jQuery('#thanks_button').click();\njQuery('.pp_close').css('visibility', 'hidden');\n" : "" ;
}
$tsn_links_js.="jQuery(document).ready(function(){ {$style_var} });\n";
$tsn_links_js.="</script>";

print "</ul>\n";
//if (preg_match("@addon-settings@", $_GET['page'])){
	print "<div class='top_sub_nav' id='top_sub_nav'><div id='inner_div' style='display:inline; height:inherit;'>$tsn_links_js{$tsn_links_output}</div>";
	if (function_exists("do_sl_hook")) { do_sl_hook("sl_nav_buttons_right"); }
	if ($sl_thanks!="false" && $sl_thanks!="true") {
		//&& $tm_st
		print "<input rel='sl_pop' type='button' class='button-primary' onclick='return false;' id='thanks_button' href='".SL_INCLUDES_BASE."/thank-you.php?ajax=true' style='margin-right:10px; font-weight:bold; margin:5px; background:green; float:right;' value='Rate Us!'/>";
	}
	print "</div>";
//}

if (!empty($_GET['sl_a_s']) && $_GET['sl_a_s'] == 1){sl_data('sl_a_s_check', 'add', 'hide'); }
if (!file_exists(SL_ADDONS_PATH."/addons-platform/addons-platform.php") && sl_data('sl_a_s_check')!='hide') {
print "<br><div class='sl_admin_success' style='line-height: 22px; width:100%'>
<div style='background-color:lightYellow; border-top:solid gold 1px; border-bottom:solid gold 1px; padding:7px; background-image:url(".SL_IMAGES_BASE_ORIGINAL."/logo.small.png); background-repeat:no-repeat; padding-left:45px; background-position-y:4px;'><b>Welcome to LotsOfLocales&trade; -- WordPress Store Locator, v{$sl_version}. What's New?: </b></div>
<div style='padding:7px'><strong style='font-size:1.0em'>A.</strong>&nbsp;<b>Pull-out Dashboard:</b> Latest news, instructions, server information, and activate your addon purchases (top right)</div>
<div style='background-color:lightYellow; /*border-top:solid gold 1px; border-bottom:solid gold 1px; */padding:7px'><strong style='font-size:1.0em'>B.</strong>&nbsp;<b>Boost capabilities:</b> <a href='http://www.viadat.com/products-page/' target='_blank'>LotsOfLocales&trade; Addons Platform</a>. 11+ free G2 addons and counting, featuring your most requested upgrades & customizations (<a href='#las-info' rel='sl_pop'>More Information</a>)</div>
<div style='padding:7px'><strong style='font-size:1.0em'>C.</strong>&nbsp;<b>Other new features:</b> Google Maps V3, auto-locate your visitors, streamlined interface & improved coding for faster experience (reduced database interaction by 81%), new map icons, 40+ new Google Maps domains</div>
(<a href='".$_SERVER['REQUEST_URI']."&sl_a_s=1'>Hide Message</a>)
</div>

 <div id='las-info' style='display:none; line-height:20px;'>
 <b style='font-size:1.3em'><br>LotsOfLocales&trade; Addons Platform</b>
<ol style='line-height:20px'>
 <li>Provides you with an addons management page for updating settings and activating each of your addons</li>
 <li>Allows you to conveniently browse and install addons directly from this admin area from the Addons Marketplace to enhance your Store Locator's abilities</li>
<li>Create multiple Store locators on different Pages or Posts on your website using the format: <div class='code sl_code'>[STORE-LOCATOR {tag_name}={tag_value}, ...] (One of the many addon features available)</div> </li>
<li>Backwards-compatible with addons purchased before version 2.0, including the CSV Importer, DB Importer, & Multiple Field Updater</li>
<li>Learn more about G2 Addons that come with the purchase of the LotsOfLocales&trade; Addons Platform from the 'ReadMe Instructions' in the pull-out Dashboard</li>
</ol>
</div>";
}

if (!empty($_POST) && function_exists("do_sl_hook")){ do_sl_hook("sl_admin_form_post"); /*print "<br>";*/ }
if (function_exists("do_sl_hook")) { do_sl_hook("sl_admin_data"); } 
?>
<div id="slideout">
	<div id="clickme"><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style='font-family:georgia; position:relative; top:-4px;'><?php print __("Dashboard", SL_TEXT_DOMAIN); ?></b>
	</div>
	<div id="slidecontent">
	   <div id='slidecontainer'>
		<div style='width:50%; float:left'><?php sl_module("thanks", __("Rate Us", SL_TEXT_DOMAIN)."!", "240px");  ?></div>
		<div style='width:50%; float:right'><?php sl_module("readme", __("Information & ReadMe Instructions", SL_TEXT_DOMAIN), "240px");  ?></div>
		<div style='width:50%; float:left'><?php sl_module("news", __("Latest News", SL_TEXT_DOMAIN), "270px"); ?></div>
		<div style='width:50%; float:left'><?php sl_module("keys", __("Activation Keys", SL_TEXT_DOMAIN)."<img style='float:right; opacity:0; height:20px;' id='module-keys' src='".SL_IMAGES_BASE_ORIGINAL."/loading.gif'>", "270px"); ?></div>
	   </div>
	</div>
</div>
<div id='validation_status' style='display:none;'><h3 style='margin-top:0px'>Validation Status</h3><div style='width:90%'></div></div>
<?php  sl_data('sl_vars', 'update', $sl_vars); ?>