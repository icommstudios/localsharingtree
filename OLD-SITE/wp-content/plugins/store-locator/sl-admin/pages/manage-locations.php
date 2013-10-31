<?php
include_once(SL_INCLUDES_PATH."/top-nav.php");
?>
<div class='wrap'>
<?php

print "<style>#wpadminbar {display:none !important;}</style>";

sl_initialize_variables();

$hidden="";
foreach($_GET as $key=>$val) {
	//hidden keys to keep same view after form submission
	if ($key!="q" && $key!="o" && $key!="d" && $key!="changeView" && $key!="start") {
		$hidden.="<input type='hidden' value='$val' name='$key'>\n"; 
	}
}

include(SL_ACTIONS_PATH."/processLocationData.php");

print "<table style='width:100%'><tr><td>";
print "<div class='mng_loc_forms_links'>";

if (empty($_GET['q'])){ $_GET['q']=""; }
$search_value = ($_GET['q']==="")? "Search" : comma(stripslashes($_GET['q'])) ;

print "<div><form name='searchForm'><!--input type='button' class='button-primary' value='Add New' onclick=\"\$aLD=jQuery('#addLocationsDiv');if(\$aLD.css('display')!='block'){\$aLD.fadeIn();}else{\$aLD.fadeOut();}return false;\">&nbsp;|&nbsp;--><input value='".$search_value."' name='q' style='color:gray' onfocus='if(this.value==\"Search\" || this.value==\"".$search_value."\"){this.value=\"\";this.style.color=\"black\";}'>$hidden</form></div>";

print "<div> | 
<nobr><select name='sl_admin_locations_per_page' onchange=\"LF=document.forms['locationForm'];salpp=document.createElement('input');salpp.type='hidden';salpp.value=this.value;salpp.name='sl_admin_locations_per_page';LF.appendChild(salpp);LF.act.value='locationsPerPage';LF.submit();\">
<optgroup label='# ".__("Locations", SL_TEXT_DOMAIN)."'>";

$opt_arr=array(10,25,50,100,200,300,400,500,1000,2000,4000,5000,10000);
foreach ($opt_arr as $value) {
	$selected=($sl_admin_locations_per_page==$value)? " selected " : "";
	print "<option value='$value' $selected>$value</option>";
}
print "</optgroup></select>
</nobr>
</div>";

$table_view_label = ($sl_vars['location_table_view']=="Normal")? __("Normal", SL_TEXT_DOMAIN) : __("Expanded", SL_TEXT_DOMAIN);
$table_view_label = "<b>".$table_view_label."</b>";
$table_view_link = ($sl_vars['location_table_view']=="Normal")? __("Expanded", SL_TEXT_DOMAIN) : __("Normal", SL_TEXT_DOMAIN);
$table_view_link = "<a href='".str_replace("&changeView=1", "", $_SERVER['REQUEST_URI'])."&changeView=1' title='Click to change view' style='cursor:question'>".$table_view_link."</a>";
$table_view = ($sl_vars['location_table_view']=="Normal")? $table_view_label."&nbsp;:&nbsp;".$table_view_link : $table_view_link."&nbsp;:&nbsp;".$table_view_label;

$updater_type_label = (sl_data('sl_location_updater_type')=="Tagging")? __("Tagging", SL_TEXT_DOMAIN) : __("Multiple Fields", SL_TEXT_DOMAIN);
$updater_type_label = "<b>".$updater_type_label."</b>";
$updater_type_link = (sl_data('sl_location_updater_type')=="Tagging")? __("Multiple Fields", SL_TEXT_DOMAIN) : __("Tagging", SL_TEXT_DOMAIN);
$updater_type_link = "<a href='".str_replace("&changeUpdater=1", "", $_SERVER['REQUEST_URI'])."&changeUpdater=1' title='Click to change which updater is seen'>".$updater_type_link."</a>";
$updater_type = (sl_data('sl_location_updater_type')=="Tagging")? $updater_type_label."&nbsp;:&nbsp;".$updater_type_link : $updater_type_link."&nbsp;:&nbsp;".$updater_type_label;

print "<div> | ".$table_view."</div>";

if ($num_ugc=$wpdb->get_var("SELECT COUNT(sl_id) FROM ".SL_TABLE." WHERE sl_longitude=0 OR sl_longitude='' OR sl_longitude IS NULL OR sl_latitude=0 OR sl_latitude='' OR sl_latitude IS NULL")) {
	if (!empty($_GET['ugc']) && $_GET['ugc']==1) {
		//Step 2 - submit selected un-geocoded locations
		$ugc_button_text=__("Re-Geocode Selected", SL_TEXT_DOMAIN);
		$ugc_num=2;
		$_GET['q']="";$_GET['start']=0; //so that a search query won't interfere with showing ungeocoded locations
		$onclick_text="onclick=\"LF=document.forms['locationForm'];LF.act.value='regeocode';LF.submit();return false;\"";
		$cancel_link="(<a href='".str_replace("&ugc=$_GET[ugc]","",$_SERVER['REQUEST_URI'])."'>Cancel</a>)";
		$link_title="title='Attempt to give coordinates to the checked locations'";
	} else {
		//Step 1 - Show un-geocoded locations
		$ugc_button_text=__("Un-Geocoded", SL_TEXT_DOMAIN)." ($num_ugc)";
		$ugc_num=1; $ugc=(!empty($_GET['ugc']))? $_GET['ugc'] : "" ;
		$onclick_text="onclick='location.href=\"".str_replace("&ugc=$ugc","",$_SERVER['REQUEST_URI'])."&ugc=$ugc_num\";return false;'";
		$cancel_link="";
		$link_title="title='Locations that do not have coordinates assigned to them'";
	}
	print "<div> | <nobr><a href='#' $onclick_text $link_title>$ugc_button_text</a> $cancel_link</nobr></div>";
}

if (file_exists(SL_ADDONS_PATH."/multiple-field-updater/multiLocationUpdate.php") && !function_exists("do_sl_hook")) {
	print "<div> | ".$updater_type."</div>";
}

print "</div>";
print "</td><td>";

//establishes WHERE clause in query from URL querystring
sl_set_query_defaults();

//overrides WHERE clause to show ungeocoded locations only
if(!empty($_GET['ugc']) && $_GET['ugc']==1) {
	$where="WHERE sl_longitude=0 OR sl_longitude='' OR sl_longitude IS NULL OR sl_latitude=0 OR sl_latitude='' OR sl_latitude IS NULL";
	print "<script>window.onload=function(){checkAll(document.getElementById('master_checkbox'), document.forms[\"locationForm\"]);}</script>";
	$master_check="checked='checked'";
} elseif (!empty($_GET['ugc']) && $_GET['ugc']==2) {
	
} else {
	$master_check="";
}

//for search links
	$numMembers=$wpdb->get_results("SELECT sl_id FROM ".SL_TABLE." $where");
	$numMembers2=count($numMembers); 
	$start=(empty($_GET['start']))? 0 : $_GET['start'];
	$num_per_page=$sl_vars['admin_locations_per_page']; //edit this to determine how many locations to view per page of 'Manage Locations' page
	if ($numMembers2!=0) {include(SL_INCLUDES_PATH."/search-links.php");}
//end of for search links

print "</td></tr></table>";

print "<form name='locationForm' method='post'>";

if(empty($_GET['d'])) {$_GET['d']="";} if(empty($_GET['o'])) {$_GET['o']="";}

//print "<br>";
include(SL_INCLUDES_PATH."/mgmt-buttons-links.php");
print "<table class='widefat' cellspacing=0 id='loc_table'>
<thead><tr >
<th colspan='1'><input type='checkbox' onclick='checkAll(this,document.forms[\"locationForm\"])' id='master_checkbox' $master_check></th>
<th colspan='1'>".__("Actions", SL_TEXT_DOMAIN)."</th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_id&d=$d'>".__("ID", SL_TEXT_DOMAIN)."</a></th>";

if (function_exists("do_sl_hook") && !empty($sl_columns)){
	do_sl_location_table_header();
} else {
	print "<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_store&d=$d'>".__("Name", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_address&d=$d'>".__("Street", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_address2&d=$d'>".__("Street2", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_city&d=$d'>".__("City", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_state&d=$d'>".__("State", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_zip&d=$d'>".__("Zip", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_tags&d=$d'>".__("Tags", SL_TEXT_DOMAIN)."</a></th>";

	if ($sl_vars['location_table_view']!="Normal") {
		print "<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_description&d=$d'>".__("Description", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_url&d=$d'>".__("URL", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_hours&d=$d'>".__("Hours", SL_TEXT_DOMAIN)."</th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_phone&d=$d'>".__("Phone", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_fax&d=$d'>".__("Fax", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_email&d=$d'>".__("Email", SL_TEXT_DOMAIN)."</a></th>
<th><a href='".str_replace("&o=$_GET[o]&d=$_GET[d]", "", $_SERVER['REQUEST_URI'])."&o=sl_image&d=$d'>".__("Image", SL_TEXT_DOMAIN)."</a></th>";
	}
}

print "<th>(Lat, Lon)</th>
</tr></thead>";

	$o=mysql_real_escape_string($o); $d=mysql_real_escape_string($d); 
	$start=mysql_real_escape_string($start); $num_per_page=mysql_real_escape_string($num_per_page); 
	if ($locales=$wpdb->get_results("SELECT * FROM ".SL_TABLE." $where ORDER BY $o $d LIMIT $start, $num_per_page", ARRAY_A)) { 
		if (function_exists("do_sl_hook") && !empty($sl_columns)){
			# +4 : Represents the 5 db fields (organized in 4 columns) that aren't dynamically placed on location table (Checkbox, Actions, ID, 'Lat, Lon' <-1 column), but need to be part of the column count
			# -3 : Represents the 3 db fields (ID, 'Lat, Lon') that are part of normal columns, but aren't dynamically placed on location table
			$colspan=($sl_vars['location_table_view']!="Normal")? 	(count($sl_columns)-count($sl_omitted_columns)+4) : (count($sl_normal_columns)-3+4);
		} else {
			$colspan=($sl_vars['location_table_view']!="Normal")? 	18 : 11;
		}
		
		$bgcol="";
		
		foreach ($locales as $value) {
			$bgcol=($bgcol==="" || $bgcol=="#eee")?"#fff":"#eee";			
			$bgcol=($value['sl_latitude']=="" || $value['sl_longitude']=="")? "salmon" : $bgcol;			
			$value=array_map("trim",$value);
			
			if (!empty($_GET['edit']) && $value['sl_id']==$_GET['edit']) {
				sl_single_location_info($value, $colspan, $bgcol);
			}
			else {
				$value['sl_url']=(!url_test($value['sl_url']) && trim($value['sl_url'])!="")? "http://".$value['sl_url'] : $value['sl_url'] ;
				$value['sl_url']=($value['sl_url']!="")? "<a href='$value[sl_url]' target='blank'>".__("View", SL_TEXT_DOMAIN)."</a>" : "" ;
				$value['sl_image']=($value['sl_image']!="")? "<a href='$value[sl_image]' target='blank'>".__("View", SL_TEXT_DOMAIN)."</a>" : "" ;
				$value['sl_description']=($value['sl_description']!="")? "<a href='#description-$value[sl_id]' rel='sl_pop'>".__("View", SL_TEXT_DOMAIN)."</a><div id='description-$value[sl_id]' style='display:none;'>".comma($value['sl_description'])."</div>" : "" ;
			
				if(empty($_GET['edit'])) {$_GET['edit']="";}
				$edit_link = str_replace("&edit=$_GET[edit]", "",$_SERVER['REQUEST_URI'])."&edit=" . $value['sl_id'] ."#a$value[sl_id]'";
				
				print "<tr style='background-color:$bgcol' id='sl_tr-$value[sl_id]'>
			<th><input type='checkbox' name='sl_id[]' value='$value[sl_id]'></th>
			<td><a class='edit_loc_link' href='".$edit_link." id='$value[sl_id]'>".__("Edit", SL_TEXT_DOMAIN)."</a>&nbsp;|&nbsp;<a class='del_loc_link' href='$_SERVER[REQUEST_URI]&delete=$value[sl_id]' onclick=\"confirmClick('Sure?', this.href); return false;\" id='$value[sl_id]'>".__("Delete", SL_TEXT_DOMAIN)."</a></td>
			<td> $value[sl_id] </td>";

				if (function_exists("do_sl_hook") && !empty($sl_columns)){
					do_sl_location_table_body($value);
				} else {
					print "<td> $value[sl_store] </td>
<td>$value[sl_address]</td>
<td>$value[sl_address2]</td>
<td>$value[sl_city]</td>
<td>$value[sl_state]</td>
<td>$value[sl_zip]</td>
<td>$value[sl_tags]</td>";

					if ($sl_vars['location_table_view']!="Normal") {
						print "<td>$value[sl_description]</td>
<td>$value[sl_url]</td>
<td>$value[sl_hours]</td>
<td>$value[sl_phone]</td>
<td>$value[sl_fax]</td>
<td>$value[sl_email]</td>
<td>$value[sl_image]</td>";
					}
				}
				print "<td>(".round($value['sl_latitude'],2).",&nbsp;".round($value['sl_longitude'],2).")</td></tr>";
			}
		}
	} else {
		$cleared=(!empty($_GET['q']))? str_replace("q=".str_replace(" ", "+", $_GET['q']) , "", $_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'] ;
		$notice=(!empty($_GET['q']))? __("No Locations Showing for this Search of ", SL_TEXT_DOMAIN)."<b>\"$_GET[q]\"</b> | <a href='$cleared'>".__("Clear&nbsp;Results", SL_TEXT_DOMAIN)."</a> $view_link" : __("No Locations Currently in Database", SL_TEXT_DOMAIN);
		print "<tr><td colspan='5'>$notice | <a href='".SL_ADD_LOCATIONS_PAGE."'>".__("Add Locations", SL_TEXT_DOMAIN)."</a></td></tr>";
	}
	print "</table>
	<input name='act' type='hidden'><br>";

if ($numMembers2!=0) {include(SL_INCLUDES_PATH."/search-links.php");}

print "</form>"; 
?>
</div>
<?php include(SL_INCLUDES_PATH."/sl-footer.php"); ?>