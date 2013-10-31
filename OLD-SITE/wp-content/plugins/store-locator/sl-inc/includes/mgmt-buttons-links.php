<?php

print "<table width='100%' cellpadding='5px' cellspacing='0' style='border:solid silver 1px' id='rightnow' class='widefat'>
<thead><tr>
<td style='/*background-color:#000;*/ width:10%; vertical-align:middle'><input class='button' type='button' value='".__("Delete", SL_TEXT_DOMAIN)."' onclick=\"if(confirm('".__("You sure", SL_TEXT_DOMAIN)."?')){LF=document.forms['locationForm'];LF.act.value='delete';LF.submit();}else{return false;}\"></td>";
$extra=(!empty($extra))? $extra : "" ;

print "<td style='width:30%; text-align:center; color:black; /*background-color:white*/' class='youhave'>";
function export_links() {
		global $sl_uploads_path, $web_domain, $extra, $sl_base, $sl_uploads_base, $text_domain;
		if (file_exists(SL_ADDONS_PATH."/csv-xml-importer-exporter/export-links.php")) {
			$sl_real_base=$sl_base; $sl_base=$sl_uploads_base;
			include(SL_ADDONS_PATH."/csv-xml-importer-exporter/export-links.php");
			$sl_base=$sl_real_base;
		}
		
} 
if (function_exists("addto_sl_hook")) {addto_sl_hook('sl_mgmt_bar_links', 'export_links', '', '', 'csv-xml-importer-exporter');} else {export_links();}
if (function_exists("do_sl_hook")) { do_sl_hook('sl_mgmt_bar_links', 'select-right');  }

print "</td>";
print "<td style='/*background-color:#000;*/ width:50%; text-align:right; color:white; vertical-align:middle'>";

  function multi_updater() {
	global $sl_uploads_path, $text_domain, $web_domain;
	if (file_exists(SL_ADDONS_PATH."/multiple-field-updater/multiple-field-update-form.php") && (sl_data('sl_location_updater_type')=="Multiple Fields" || function_exists("do_sl_hook"))) {
		include(SL_ADDONS_PATH."/multiple-field-updater/multiple-field-update-form.php");
	}
  }
	
	function tagger() {
		print "<strong>".__("Tags", SL_TEXT_DOMAIN)."</strong>&nbsp;<input name='sl_tags'>&nbsp;<input class='button' type='button' value='".__("Add Tag", SL_TEXT_DOMAIN)."' onclick=\"LF=document.forms['locationForm'];LF.act.value='add_tag';LF.submit();\">&nbsp;<input class='button' type='button' value='".__("Remove Tag", SL_TEXT_DOMAIN)."' onclick=\"if(confirm('".__("You sure", SL_TEXT_DOMAIN)."?')){LF=document.forms['locationForm'];LF.act.value='remove_tag';LF.submit();}else{return false;}\">";
	}
  
	if (function_exists("addto_sl_hook")) {
	    if (is_dir(SL_ADDONS_PATH."/multiple-field-updater/")){
		addto_sl_hook('sl_mgmt_bar_form', 'multi_updater', '', '', 'multiple-field-updater');
	    }
	    addto_sl_hook('sl_mgmt_bar_form', 'tagger');
	} elseif (!function_exists("addto_sl_hook")) {
		if (file_exists(SL_ADDONS_PATH."/multiple-field-updater/multiple-field-update-form.php") && sl_data('sl_location_updater_type')=="Multiple Fields") {
			multi_updater();
		} else {
			 tagger();
		}
	}

	if (function_exists("do_sl_hook")) {do_sl_hook('sl_mgmt_bar_form', 'select');
  }
print "</td></tr></thead></table>
";

?>