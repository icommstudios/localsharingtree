<?php 
include(SL_INCLUDES_PATH."/top-nav.php");
sl_move_upload_directories();

print "<div class='wrap'>";
print "<table class='widefat' cellpadding='0px' cellspacing='0px'>";

if (preg_match('@wordpress-store-locator-location-finder@', SL_DIR)) { 
	$icon_notification_msg="<p><div class='sl_admin_warning'>".__("<b>Note:</b> Your directory is <b>'wordpress-store-locator-location-finder'</b>. Please rename to <b>'store-locator'</b> to continue receiving notifications of future updates in your admin panel. After changing to <b>'store-locator'</b>, make sure to also update your icon URLs on the 'Map Designer' page.", SL_TEXT_DOMAIN)."</div></p>"; 
	print $icon_notification_msg;
	}
	elseif ((preg_match("@wordpress-store-locator-location-finder@", sl_data('sl_map_home_icon')) && preg_match("@store-locator@", SL_DIR)) || (preg_match("@wordpress-store-locator-location-finder@", sl_data('sl_map_end_icon')) && preg_match("@store-locator@", SL_DIR))) {
	$icon_notification_msg="<p><div class='sl_admin_warning'>You have switched from <strong>'wordpress-store-locator-location-finder'</strong> to <strong>'store-locator'</strong> --- great! <br>Now, please re-select your <b>'Home Icon'</b> and <b>'Destination Icon'</b> on the <a href='".SL_MAP_DESIGNER_PAGE."'>Map Designer</a> page, so that they show up properly on your store locator map.</div></p>";
	print $icon_notification_msg;
	}

print "<tr><td valign='top' width='50%' style='padding:0px'>

<table width='100%'><thead><tr>
<th>".
__("Latest News", SL_TEXT_DOMAIN).
"</th>
</tr>
</thead>
<tr>
<td width='50%'>
<div style='overflow:scroll; height:350px; padding:7px;'>

<script src='http://feeds2.feedburner.com/Viadat?format=sigpro' type='text/javascript' ></script><!--noscript><p>Subscribe to RSS headline updates from: <a href='http://feeds2.feedburner.com/Viadat'></a><br/>Powered by FeedBurner</p> </noscript-->";

/*
// include lastRSS library
include_once (SL_ACTIONS_PATH."/lastRSS.php");
// create lastRSS object
$rss = new lastRSS; 
// setup transparent cache
$rss->cache_dir = './cache'; 
$rss->cache_time = 3600; // one hour

// load some RSS file
if ($rs = $rss->get('http://feeds2.feedburner.com/Viadat')) {
	//var_dump($rs);
$c=1;
foreach ($rs[items] as $value) {

if ($c<=100) {
	print "<li><A href=\"$value[link]\" target='_blank' class='home_rss' style='font-size:12px'>
	<b>$value[title]</b></a></li><b>$value[pubDate]</b><br>
	<!--br-->
	<span class='home_rss'> ".
	str_replace("]]>","",str_replace("</p>", "", preg_replace("@<!\[CDATA\[@s", "", html_entity_decode(nl2br($value[description]))))).
	"</span><br><br>";
}
else {
	if ($c<=4)
	print "<li style='font-size:10px; color:black; position:relative; left:10px'><A href=\"$value[link]\" target='_blank' class='home_rss' style='font-size:11px'>$value[title]</a></li>";
	}
$c++;
	}	
}
*/

print "</td>
</tr></table>";

print "
<table width='100%' height='350px'><thead><tr>
<th width=''>".
__("For Your Information", SL_TEXT_DOMAIN).
"</th></tr></thead>
<tr>
<td><div style='overflow:scroll; height:350px'> ";
include(SL_INCLUDES_PATH."/thank-you.php");
print "</div></td></tr></table>";


/*print "
<table width='100%'>
<thead><tr>
<th colspan='1'>".
__("Activation Keys", SL_TEXT_DOMAIN).
"</th>
</tr></thead>
<tr>
<td colspan='1'><div style='overflow:scroll; height:300px; padding:7px;'>";


if ($_POST) {
	sl_update_keys($_POST);
	//include(SL_ACTIONS_PATH."/updateLicenses.php");
	//if (function_exists("do_sl_hook")) {do_sl_hook("sl_update_activation_keys");}
}

if (is_dir(SL_ADDONS_PATH)) {
	$a_lic_arr=array();
	$lic_str="";
	$ao_dir=opendir(SL_ADDONS_PATH); $ctr=0;
	print "<table width='100%' border='0'><tr>"; 
	while (false !== ($a_lic=readdir($ao_dir))) {
		if (!preg_match("@^\.{1,2}.*$@", $a_lic) && !preg_match("@\.(php|txt|htm(l)?)@", $a_lic)) {

			$style="style='border:red; background-color:salmon'";
			if (sl_data('sl_activation_'.$a_lic)!="") {
				$a_lic_arr["sl_activation_".$a_lic]=sl_data('sl_activation_'.$a_lic);
				$style="style='border:green; background-color:LightGreen'";
			}
			if (sl_data('sl_license_'.$a_lic)!="") {
				$a_lic_arr["sl_license_".$a_lic]=sl_data('sl_license_'.$a_lic);
			
			}
			$a_lic_arr["sl_license_".$a_lic]=(!empty($a_lic_arr["sl_license_".$a_lic]))? $a_lic_arr["sl_license_".$a_lic] : "";
			$a_lic_arr["sl_activation_".$a_lic]=(!empty($a_lic_arr["sl_activation_".$a_lic]))? $a_lic_arr["sl_activation_".$a_lic] : "";
			$lic_str.="<td><div class='sl_admin_success' $style><b>".ucwords(str_replace("-", " ", $a_lic))."</b></div>
<table style='border:none'>
<tr>
<td>".__("Key", SL_TEXT_DOMAIN).":&nbsp;&nbsp;<!--/td></tr>
<tr><td--><input name='sl_license_".$a_lic."' value='".$a_lic_arr["sl_license_".$a_lic]."' size='20'>
<input name='sl_activation_".$a_lic."' value='".$a_lic_arr["sl_activation_".$a_lic]."' type='hidden'></td></tr>
</table>
</td>";

			if ($ctr%2==1) {$lic_str.="</tr><tr>";}
			$ctr++; 
		}
	}
	print "</table>";
}

print "<form method='post' name='licenseForm'><table style='border:none'><tr>$lic_str</tr></table>
<br><input class='button-primary' type='submit' value='".__("Activate", SL_TEXT_DOMAIN)."'>&nbsp;&nbsp;".__("Looking for more addons & themes", SL_TEXT_DOMAIN)."? <a href='http://www.viadat.com/products-page/'>".__("It's all right here", SL_TEXT_DOMAIN)."</a><br><br>
</form>

</div>
</td></tr></table>";
*/

print "</td>
<td rowspan='2' valign='top' style='padding:0px'>

<table width='100%'><thead><tr>
<th width=''>".
__("Addons & Themes", SL_TEXT_DOMAIN).
"</th></tr></thead>
<tr>
<td><div style='overflow:scroll; height:750px; padding:7px;'>";
?>
<?php

// include lastRSS library
include_once (SL_ACTIONS_PATH."/lastRSS.php");
// create lastRSS object
$rss = new lastRSS; 
// setup transparent cache
$rss->cache_dir = SL_CACHE_PATH; 
$rss->cache_time = 3600; // one hour

// load some RSS file
if ($rs = $rss->get('http://www.viadat.com/index.php?rss=true&action=product_list&category_id=7')) {
	//var_dump($rs);
$c=1;
foreach ($rs['items'] as $value) {

if ($c<=100) {
	print "<li style='list-style-type:none; margin-top:10px; margin-bottom:0px;'><A href=\"$value[link]\" target='_blank' class='home_rss' style='font-size:16px'>
	<b>$value[title]</b></a></li>
	<!--br-->
	<div class='home_rss'> ".
	str_replace("]]>","",str_replace("</p>", "", html_entity_decode(nl2br($value['description'])))).
	"</div><br><br>";
}
else {
	if ($c<=4)
	print "<li style='font-size:10px; color:black; position:relative; left:10px'><A href=\"$value[link]\" target='_blank' class='home_rss' style='font-size:11px'>$value[title]</a></li>";
	}
$c++;
	}	
}

print "
</div>
</td>
</tr>
</table>


</td>
</tr>
</table>

</div>";

include(SL_INCLUDES_PATH."/sl-footer.php");
?>