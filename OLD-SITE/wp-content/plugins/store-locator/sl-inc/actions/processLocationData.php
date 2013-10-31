<?php

	if (!empty($_GET['delete'])) {
		//If delete link is clicked
		$wpdb->query($wpdb->prepare("DELETE FROM ".SL_TABLE." WHERE sl_id='%d'", $_GET['delete'])); 
		sl_process_tags("", "delete", $_GET['delete']); 
	}
	if (!empty($_POST) && !empty($_GET['edit']) && $_POST['act']!="delete") {
		$field_value_str=""; 
		foreach ($_POST as $key=>$value) {
			if (preg_match("@\-$_GET[edit]@", $key)) {
				$key=str_replace("-$_GET[edit]", "", $key); // stripping off number at the end (giving problems when constructing address string below)
				if ($key=="sl_tags") {
					//print "before: $value <br><br>";
					$value=sl_prepare_tag_string($value);
					//print "after: $value \r\n"; die();
				}
				
				if (is_array($value)){
					$value=serialize($value); //for arrays being submitted
					$field_value_str.=$key."='$value',";
				} else {
					$field_value_str.=$key."=".$wpdb->prepare("%s", trim(comma(stripslashes($value)))).", "; 
				}
				$_POST["$key"]=$value; 
			}
		}
		
		$field_value_str=substr($field_value_str, 0, strlen($field_value_str)-2);
		$edit=$_GET['edit']; extract($_POST);
		$the_address="$sl_address $sl_address2, $sl_city, $sl_state $sl_zip";
		
		if (empty($_POST['no_geocode']) || $_POST['no_geocode']!=1) { //no_geocode sent by addons that manually edit the the coordinates. Prevents sl_do_geocoding() from overwriting the manual edit.
			$old_address=$wpdb->get_results("SELECT * FROM ".SL_TABLE." WHERE sl_id='".mysql_real_escape_string($_GET['edit'])."'", ARRAY_A); 
		}
		//die("UPDATE ".SL_TABLE." SET $field_value_str WHERE sl_id='%d'");
		$wpdb->query($wpdb->prepare("UPDATE ".SL_TABLE." SET $field_value_str WHERE sl_id='%d'", $_GET['edit'])); 
		if(!empty($_POST['sl_tags'])){sl_process_tags($_POST['sl_tags'], "insert", $_GET['edit']);}
		
		if ((empty($_POST['sl_longitude']) || $_POST['sl_longitude']==$old_address[0]['sl_longitude']) && (empty($_POST['sl_latitude']) || $_POST['sl_latitude']==$old_address[0]['sl_latitude'])) {
			if ($the_address!=$old_address[0]['sl_address']." ".$old_address[0]['sl_address2'].", ".$old_address[0]['sl_city'].", ".$old_address[0]['sl_state']." ".$old_address[0]['sl_zip'] || ($old_address[0]['sl_latitude']==="" || $old_address[0]['sl_longitude']==="")) {
				sl_do_geocoding($the_address,$_GET['edit']);
			}
		}
		print "<script>location.replace('".str_replace("&edit=$_GET[edit]", "", $_SERVER['REQUEST_URI'])."');</script>";
	}
	
	if (!empty($_POST['act']) && !empty($_POST['sl_id']) && $_POST['act']=="delete") {
		//If bulk delete is used
		include(SL_ACTIONS_PATH."/deleteLocations.php");
	}
	if (!empty($_POST['act']) && !empty($_POST['sl_id']) && preg_match("@tag@", $_POST['act'])) {
		//if bulk tagging is used
		include(SL_ACTIONS_PATH."/tagLocations.php");
	}
	if (!empty($_POST['act']) && ($_POST['act']=='add_multi' || $_POST['act']=='remove_multi')) {
		//if bulk updating is used
		include(SL_ADDONS_PATH."/multiple-field-updater/multiLocationUpdate.php");
	}
	if (!empty($_POST['act']) && $_POST['act']=="locationsPerPage") {
		//If change in locations per page
		$sl_vars['admin_locations_per_page']=$_POST['sl_admin_locations_per_page'];
		sl_data('sl_vars', 'update', $sl_vars);
		extract($_POST);
	}
	if (!empty($_POST['act']) && $_POST['act']=="regeocode") {
		//var_dump($_POST); die();
		if ($_POST) {extract($_POST);}
		if (is_array($sl_id)) {
			$rplc_arr=array_fill(0, count($sl_id), "%d"); //var_dump($rplc_arr); //die(); 
			$id_string=implode(",", array_map(array($wpdb, "prepare"), $rplc_arr, $sl_id)); 
		} else {
			$id_string=$wpdb->prepare("%d", $sl_id); 
		}

		$locs=$wpdb->get_results("SELECT * FROM ".SL_TABLE." WHERE sl_id IN ($id_string)", ARRAY_A);
		foreach ($locs as $value) {
			sl_do_geocoding("$value[sl_address] $value[sl_address2], $value[sl_city], $value[sl_state] $value[sl_zip]", $value['sl_id']);
		}
		print "<script>location.replace('".str_replace("&ugc=$_GET[ugc]", "", $_SERVER['REQUEST_URI'])."');</script>";
	}
	if (!empty($_GET['changeView']) && $_GET['changeView']==1) {
		if ($sl_vars['location_table_view']=="Normal") {
			$sl_vars['location_table_view']='Expanded';
			sl_data('sl_vars', 'update', $sl_vars);
			//$tabViewText="Expanded";
		} else {
			$sl_vars['location_table_view']='Normal';
			sl_data('sl_vars', 'update', $sl_vars);
			//$tabViewText="Normal";
		}
		print "<script>location.replace('".str_replace("&changeView=1", "", $_SERVER['REQUEST_URI'])."');</script>";
	}
	if (!empty($_GET['changeUpdater']) && $_GET['changeUpdater']==1) {
		if (sl_data('sl_location_updater_type')=="Tagging") {
			sl_data('sl_location_updater_type', 'update', 'Multiple Fields');
			//$updaterTypeText="Multiple Fields";
		} else {
			sl_data('sl_location_updater_type', 'update', 'Tagging');
			//$updaterTypeText="Tagging";
		}
		$_SERVER['REQUEST_URI']=str_replace("&changeUpdater=1", "", $_SERVER['REQUEST_URI']);
		print "<script>location.replace('$_SERVER[REQUEST_URI]');</script>";
	}
	
?>