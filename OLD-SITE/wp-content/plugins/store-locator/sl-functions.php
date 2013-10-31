<?php

function sl_move_upload_directories() {
	global $sl_uploads_path, $sl_path;
	
	$sl_uploads_arr=wp_upload_dir();
	if (!is_dir($sl_uploads_arr['baseurl'])) {
		mkdir($sl_uploads_arr['baseurl'], 0755);
	}
	if (!is_dir(SL_UPLOADS_PATH)) {
		mkdir(SL_UPLOADS_PATH, 0755);
	}
	if (is_dir(SL_ADDONS_PATH_ORIGINAL) && !is_dir(SL_ADDONS_PATH)) {
		copyr(SL_ADDONS_PATH_ORIGINAL, SL_ADDONS_PATH);
	}
	if (is_dir(SL_THEMES_PATH_ORIGINAL) && !is_dir(SL_THEMES_PATH)) {
		copyr(SL_THEMES_PATH_ORIGINAL, SL_THEMES_PATH);
	}
	if (is_dir(SL_LANGUAGES_PATH_ORIGINAL) && !is_dir(SL_LANGUAGES_PATH)) {
		copyr(SL_LANGUAGES_PATH_ORIGINAL, SL_LANGUAGES_PATH);
	}
	if (is_dir(SL_IMAGES_PATH_ORIGINAL) && !is_dir(SL_IMAGES_PATH)) {
		copyr(SL_IMAGES_PATH_ORIGINAL, SL_IMAGES_PATH);
	}
	if (!is_dir(SL_CUSTOM_ICONS_PATH)) {
		mkdir(SL_CUSTOM_ICONS_PATH, 0755);
	}
	if (!is_dir(SL_CUSTOM_CSS_PATH)) {
		mkdir(SL_CUSTOM_CSS_PATH, 0755);
	}
	if (!is_dir(SL_CACHE_PATH)) {
	      mkdir(SL_CACHE_PATH, 0755);
	}
	sl_ht(SL_CACHE_PATH);
	sl_ht(SL_UPLOADS_PATH);
	sl_ht(SL_ADDONS_PATH);
}
function sl_ht($path){
if(is_dir($path) && !is_file($path."/.htaccess")) {
$htaccess = <<<EOQ
<FilesMatch "(\.php|\.gif|\.jpe?g|\.png|\.css|\.js|\.csv|\.xml|\.json)$">
Allow from all
</FilesMatch>
order deny,allow
deny from all
allow from none
EOQ;
$filename = $path."/.htaccess";
$file_handle = @ fopen($filename, 'w+');
@ fwrite($file_handle, $htaccess);
@ fclose($file_handle);
@ chmod($file_handle, 0644);
}
}
/* -----------------*/
function parseToXML($htmlStr) 
{ 
$xmlStr=str_replace('<','&lt;',$htmlStr); 
$xmlStr=str_replace('>','&gt;',$xmlStr); 
$xmlStr=str_replace('"','&quot;',$xmlStr); 
$xmlStr=str_replace("'",'&#39;',$xmlStr); 
$xmlStr=str_replace("&",'&amp;',$xmlStr); 
$xmlStr=str_replace("," ,"&#44;" ,$xmlStr);
return $xmlStr; 
} 

/*-----------------*/
function sl_initialize_variables() {

global $sl_height, $sl_width, $sl_width_units, $sl_height_units, $sl_radii;
global $sl_icon, $sl_icon2, $sl_google_map_domain, $sl_google_map_country, $sl_theme, $sl_base, $sl_uploads_base, $sl_location_table_view;
global $sl_search_label, $sl_zoom_level, $sl_use_city_search, $sl_use_name_search, $sl_name;
global $sl_radius_label, $sl_website_label, $sl_directions_label, $sl_num_initial_displayed, $sl_load_locations_default;
global $sl_distance_unit, $sl_map_overview_control, $sl_admin_locations_per_page, $sl_instruction_message;
global $sl_map_character_encoding, $sl_start, $sl_map_language, $sl_map_region, $sl_sensor, $sl_geolocate;
global $sl_map_type, $sl_remove_credits, $sl_api_key, $sl_location_not_found_message, $sl_no_results_found_message; 
global $sl_load_results_with_locations_default, $sl_vars;

//$sl_vars=sl_data('sl_vars');
//$sl_google_map_domain=sl_data('sl_google_map_domain');
if (empty($sl_vars)){
	//transition from individual variables to single array of variables
	$sl_vars['height']=sl_data('sl_map_height'); $sl_vars['width']=sl_data('sl_map_width'); $sl_vars['width_units']=sl_data('sl_map_width_units'); $sl_vars['height_units']=sl_data('sl_map_height_units'); $sl_vars['radii']=sl_data('sl_map_radii'); $sl_vars['icon']=sl_data('sl_map_home_icon'); $sl_vars['icon2']=sl_data('sl_map_end_icon2'); $sl_vars['google_map_domain']=sl_data('sl_google_map_domain'); $sl_vars['google_map_country']=sl_data('sl_google_map_country'); $sl_vars['theme']=sl_data('sl_map_theme'); $sl_vars['location_table_view']=sl_data('sl_location_table_view'); $sl_vars['search_label']=sl_data('sl_search_label'); $sl_vars['zoom_level']=sl_data('sl_zoom_level'); $sl_vars['use_city_search']=sl_data('sl_use_city_search'); $sl_vars['use_name_search']=sl_data('sl_use_name_search'); $sl_vars['name']=sl_data('sl_name'); $sl_vars['radius_label']=sl_data('sl_radius_label'); $sl_vars['website_label']=sl_data('sl_website_label'); $sl_vars['directions_label']=sl_data('sl_directions_label'); $sl_vars['num_initial_displayed']=sl_data('sl_num_initial_displayed'); $sl_vars['load_locations_default']=sl_data('sl_load_locations_default'); $sl_vars['distance_unit']=sl_data('sl_distance_unit'); $sl_vars['map_overview_control']=sl_data('sl_map_overview_control'); $sl_vars['admin_locations_per_page']=sl_data('sl_admin_locations_per_page'); $sl_vars['instruction_message']=sl_data('sl_instruction_message'); $sl_vars['map_character_encoding']=sl_data('sl_map_character_encoding'); $sl_vars['start']=sl_data('sl_start'); $sl_vars['map_language']=sl_data('sl_map_language'); $sl_vars['map_region']=sl_data('sl_map_region'); $sl_vars['sensor']=sl_data('sl_sensor'); $sl_vars['geolocate']=sl_data('sl_geolocate'); $sl_vars['map_type']=sl_data('sl_map_type'); $sl_vars['remove_credits']=sl_data('sl_remove_credits'); $sl_vars['api_key']=sl_data('store_locator_api_key');
}

if (strlen(trim($sl_vars['geolocate'])) == 0) {	$sl_vars['geolocate']="0";	}
$sl_geolocate=$sl_vars['geolocate'];

if (strlen(trim($sl_vars['sensor'])) == 0) {	$sl_vars['sensor'] = ($sl_vars['geolocate'] == '1')? "true" : "false";	}
$sl_sensor=$sl_vars['sensor'];

if ($sl_vars['map_region'] === NULL) {	$sl_vars['map_region']="";	}
$sl_map_region=$sl_vars['map_region'];

if (strlen(trim($sl_vars['map_language'])) == 0) {	$sl_vars['map_language']="en";	}
$sl_map_language=$sl_vars['map_language'];

if (strlen(trim($sl_vars['start'])) == 0) { 	$sl_vars['start']=date("Y-m-d H:i:s"); 	} 
$sl_start=$sl_vars['start']; 

if ($sl_vars['map_character_encoding'] === NULL) {	$sl_vars['map_character_encoding']="";		}
$sl_map_character_encoding=$sl_vars['map_character_encoding'];

if ($sl_vars['instruction_message'] === NULL) {	$sl_vars['instruction_message']="Enter Your Address or Zip Code Above.";	}
$sl_instruction_message=$sl_vars['instruction_message'];

if (empty($sl_vars['location_not_found_message']) || $sl_vars['location_not_found_message'] === NULL) {	$sl_vars['location_not_found_message']="";	}
$sl_location_not_found_message=$sl_vars['location_not_found_message'];

if (empty($sl_vars['no_results_found_message']) || $sl_vars['no_results_found_message'] === NULL) {	$sl_vars['no_results_found_message']="No Results Found";	}
$sl_no_results_found_message=$sl_vars['no_results_found_message'];

if (strlen(trim($sl_vars['admin_locations_per_page'])) == 0) {	$sl_vars['admin_locations_per_page']="100";	}
$sl_admin_locations_per_page=$sl_vars['admin_locations_per_page'];

if (strlen(trim($sl_vars['map_overview_control'])) == 0) {	$sl_vars['map_overview_control']="0";	}
$sl_map_overview_control=$sl_vars['map_overview_control'];

if (strlen(trim($sl_vars['distance_unit'])) == 0) {	$sl_vars['distance_unit']="miles";	}
$sl_distance_unit=$sl_vars['distance_unit'];

if (strlen(trim($sl_vars['load_locations_default'])) == 0) {	$sl_vars['load_locations_default']="1";	}
$sl_load_locations_default=$sl_vars['load_locations_default'];

if (strlen(trim($sl_vars['load_results_with_locations_default'])) == 0) {	$sl_vars['load_results_with_locations_default']="1";	}
$sl_load_results_with_locations_default=$sl_vars['load_results_with_locations_default'];

if (strlen(trim($sl_vars['num_initial_displayed'])) == 0) {	$sl_vars['num_initial_displayed']="25";	}
$sl_num_initial_displayed=$sl_vars['num_initial_displayed'];

if (strlen(trim($sl_vars['website_label'])) == 0) {	$sl_vars['website_label']="Website";	}
$sl_website_label=$sl_vars['website_label'];

if (strlen(trim($sl_vars['directions_label'])) == 0) {	$sl_vars['directions_label']="Directions";	}
$sl_directions_label=$sl_vars['directions_label'];

if (strlen(trim($sl_vars['radius_label'])) == 0) {	$sl_vars['radius_label']="Radius";	}
$sl_radius_label=$sl_vars['radius_label'];

if (strlen(trim($sl_vars['map_type'])) == 0) {	$sl_vars['map_type']="google.maps.MapTypeId.ROADMAP";}
elseif ($sl_vars['map_type']=="G_NORMAL_MAP"){	$sl_vars['map_type']='google.maps.MapTypeId.ROADMAP';}
elseif ($sl_vars['map_type']=="G_SATELLITE_MAP"){	$sl_vars['map_type']='google.maps.MapTypeId.SATELLITE';}
elseif ($sl_vars['map_type']=="G_HYBRID_MAP"){	$sl_vars['map_type']='google.maps.MapTypeId.HYBRID';}
elseif ($sl_vars['map_type']=="G_PHYSICAL_MAP"){	$sl_vars['map_type']='google.maps.MapTypeId.TERRAIN';}
$sl_map_type=$sl_vars['map_type'];

if (strlen(trim($sl_vars['remove_credits'])) == 0) {	$sl_vars['remove_credits']="0";	}
$sl_remove_credits=$sl_vars['remove_credits'];

if (strlen(trim($sl_vars['use_name_search'])) == 0) {	$sl_vars['use_name_search']="0";	}
$sl_use_name_search=$sl_vars['use_name_search'];

if (strlen(trim($sl_vars['use_city_search'])) == 0) {	$sl_vars['use_city_search']="1";	}
$sl_use_city_search=$sl_vars['use_city_search'];

if (strlen(trim($sl_vars['name'])) == 0) {	$sl_vars['name']="LotsOfLocales";	}  
$sl_name=$sl_vars['name'];

if (strlen(trim($sl_vars['zoom_level'])) == 0) {	$sl_vars['zoom_level']="4";	}
$sl_zoom_level=$sl_vars['zoom_level'];

if (strlen(trim($sl_vars['search_label'])) == 0) {	$sl_vars['search_label']="Address";	}
$sl_search_label=$sl_vars['search_label'];

if (strlen(trim($sl_vars['location_table_view'])) == 0) {	$sl_vars['location_table_view']="Normal";	}
$sl_location_table_view=$sl_vars['location_table_view'];

if ($sl_vars['theme'] === NULL) {	$sl_vars['theme']="";	}
$sl_theme=$sl_vars['theme'];

if (strlen(trim($sl_vars['google_map_country'])) == 0) {	$sl_vars['google_map_country']="United States";}
$sl_google_map_country=$sl_vars['google_map_country'];

if (strlen(trim($sl_vars['google_map_domain'])) == 0) {	$sl_vars['google_map_domain']="maps.google.com";}
$sl_google_map_domain=$sl_vars['google_map_domain'];

if (strlen(trim($sl_vars['icon2'])) == 0) {	$sl_vars['icon2']=SL_ICONS_BASE."/droplet_red.png";}
$sl_icon2=$sl_vars['icon2'];

if (strlen(trim($sl_vars['icon'])) == 0) {	$sl_vars['icon']=SL_ICONS_BASE."/droplet_green.png";}
$sl_icon=$sl_vars['icon'];

if (strlen(trim($sl_vars['height'])) == 0) {	$sl_vars['height']="350";	}
$sl_height=$sl_vars['height'];

if (strlen(trim($sl_vars['height_units'])) == 0) {	$sl_vars['height_units']="px";	}
$sl_height_units=$sl_vars['height_units'];

if (strlen(trim($sl_vars['width'])) == 0) {	$sl_vars['width']="100";	}
$sl_width=$sl_vars['width'];

if (strlen(trim($sl_vars['width_units'])) == 0) {	$sl_vars['width_units']="%";	}
$sl_width_units=$sl_vars['width_units'];

if (strlen(trim($sl_vars['radii'])) == 0) {	$sl_vars['radii']="1,5,10,25,(50),100,200,500";	}
$sl_radii=$sl_vars['radii'];

if ($sl_vars['api_key'] === NULL) {	$sl_vars['api_key']="";	}
$sl_api_key=$sl_vars['api_key'];
	
	sl_data('sl_vars', 'add', $sl_vars);
}
/*--------------------------*/
function choose_units($unit, $input_name) {
	$unit_arr[]="%";$unit_arr[]="px";$unit_arr[]="em";$unit_arr[]="pt";
	$select_field="<select name='$input_name'>";
	
	//global $height_units, $width_units;
	
	foreach ($unit_arr as $value) {
		$selected=($value=="$unit")? " selected='selected' " : "" ;
		if (!($input_name=="height_units" && $unit=="%")) {
			$select_field.="\n<option value='$value' $selected>$value</option>";
		}
	}
	$select_field.="</select>";
	return $select_field;
}
/*----------------------------*/
function sl_do_geocoding($address,$sl_id="") {
   if (empty($_POST['no_geocode']) || $_POST['no_geocode']!=1){
	global $wpdb, $text_domain, $sl_vars;

	// Initialize delay in geocode speed
	$delay = 0; $ccTLD=$sl_vars['map_region']; $sensor=$sl_vars['sensor'];
	$base_url = "http://maps.googleapis.com/maps/api/geocode/json?";

	if ($sensor!="" && !empty($sensor)) {$base_url .= "sensor=".$sensor;} else {$base_url .= "sensor=false";}

	//Adding ccTLD (Top Level Domain) to help perform more accurate geocoding according to selected Google Maps Domain - 12/16/09
	if ($ccTLD!="") {
		$base_url .= "&region=".$ccTLD;
		//die($base_url);
	}

	//Map Character Encoding
	if (!empty($sl_vars['map_language'])) {
		$base_url .= "&language=".$sl_vars['map_language'];
	}
	
	//API Key
	/*if (!empty($sl_vars['api_key'])) {
		$base_url .= "&key=".$sl_vars['api_key'];
	}*/

	// Iterate through the rows, geocoding each address
		$request_url = $base_url . "&address=" . urlencode(trim($address)); //print($request_url );
   
	//New code to accomdate those without 'file_get_contents' functionality for their server - added 3/27/09 8:56am - provided by Daniel C. - thank you
	if (extension_loaded("curl") && function_exists("curl_init")) {
		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, $request_url);
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
		$resp_json = curl_exec($cURL);
		curl_close($cURL);  
	}else{
		$resp_json = file_get_contents($request_url) or die("url not loading");
	}
	//End of new code

	$resp = json_decode($resp_json, true); //var_dump($resp);
    $status = $resp['status'];
    $lat = (!empty($resp['results'][0]['geometry']['location']['lat']))? $resp['results'][0]['geometry']['location']['lat'] : "" ;
    $lng = (!empty($resp['results'][0]['geometry']['location']['lng']))? $resp['results'][0]['geometry']['location']['lng'] : "" ;
	//die("<br>compare: ".strcmp($status, "OK")."<br>status: $status<br>");
    if (strcmp($status, "OK") == 0) {
		// successful geocode
		$geocode_pending = false;
		$lat = $resp['results'][0]['geometry']['location']['lat'];
		$lng = $resp['results'][0]['geometry']['location']['lng'];

		if ($sl_id==="") {
			$query = sprintf("UPDATE ".SL_TABLE." SET sl_latitude = '%s', sl_longitude = '%s' WHERE sl_id = '%s' LIMIT 1;", mysql_real_escape_string($lat), mysql_real_escape_string($lng), mysql_real_escape_string($wpdb->insert_id)); //die($query); 
		} else {
			$query = sprintf("UPDATE ".SL_TABLE." SET sl_latitude = '%s', sl_longitude = '%s' WHERE sl_id = '%s' LIMIT 1;", mysql_real_escape_string($lat), mysql_real_escape_string($lng), mysql_real_escape_string($sl_id)); 
		}
		$update_result = mysql_query($query);
		if (!$update_result) {
			die("Invalid query: " . mysql_error());
		}
    } else if (strcmp($status, "OVER_QUERY_LIMIT") == 0) {
		// sent geocodes too fast
		$delay += 100000;
    } else {
		// failure to geocode
		$geocode_pending = false;
		echo __("Address " . $address . " <font color=red>failed to geocode</font>. ", SL_TEXT_DOMAIN);
		echo __("Received status " . $status , SL_TEXT_DOMAIN)."\n<br>";
    }
    usleep($delay);
  } else {
  	//print __("Geocoding bypassed ", SL_TEXT_DOMAIN);
  }
}
/*-------------------------------*/
function sl_install_tables() {
	global $wpdb, $sl_db_version, $sl_path, $sl_uploads_path, $sl_hook;

	if (!defined("SL_TABLE") || !defined("SL_TAG_TABLE") || !defined("SL_SETTING_TABLE")){ 
		add_option("sl_db_prefix", $wpdb->prefix); $sl_db_prefix = get_option('sl_db_prefix'); 
	}
	if (!defined("SL_TABLE")){ define("SL_TABLE", $sl_db_prefix."store_locator");}
	if (!defined("SL_TAG_TABLE")){ define("SL_TAG_TABLE", $sl_db_prefix."sl_tag"); }
	if (!defined("SL_SETTING_TABLE")){ define("SL_SETTING_TABLE", $sl_db_prefix."sl_setting"); }
	
	$table_name = SL_TABLE;
	$sql = "CREATE TABLE " . $table_name . " (
			sl_id mediumint(8) unsigned NOT NULL auto_increment,
			sl_store varchar(255) NULL,
			sl_address varchar(255) NULL,
			sl_address2 varchar(255) NULL,
			sl_city varchar(255) NULL,
			sl_state varchar(255) NULL,
			sl_country varchar(255) NULL,
			sl_zip varchar(255) NULL,
			sl_latitude varchar(255) NULL,
			sl_longitude varchar(255) NULL,
			sl_tags mediumtext NULL,
			sl_description varchar(255) NULL,
			sl_url varchar(255) NULL,
			sl_hours varchar(255) NULL,
			sl_phone varchar(255) NULL,
			sl_fax varchar(255) NULL,
			sl_email varchar(255) NULL,
			sl_image varchar(255) NULL,
			sl_private varchar(1) NULL,
			sl_neat_title varchar(255) NULL,
			PRIMARY KEY  (sl_id)
			) ENGINE=innoDB  DEFAULT CHARACTER SET=utf8  DEFAULT COLLATE=utf8_unicode_ci;";
			
	$table_name_2 = SL_TAG_TABLE;
	$sql .= "CREATE TABLE " . $table_name_2 . " (
			sl_tag_id bigint(20) unsigned NOT NULL auto_increment,
			sl_tag_name varchar(255) NULL,
			sl_tag_slug varchar(255) NULL,
			sl_id mediumint(8) NULL,
			PRIMARY KEY  (sl_tag_id)
			) ENGINE=innoDB  DEFAULT CHARACTER SET=utf8  DEFAULT COLLATE=utf8_unicode_ci;";
	
	$table_name_3 = SL_SETTING_TABLE;
	$sql .= "CREATE TABLE " . $table_name_3 . " (
			sl_setting_id bigint(20) unsigned NOT NULL auto_increment,
			sl_setting_name varchar(255) NULL,
			sl_setting_value longtext NULL,
			PRIMARY KEY  (sl_setting_id)
			) ENGINE=innoDB  DEFAULT CHARACTER SET=utf8  DEFAULT COLLATE=utf8_unicode_ci;";
	//$sql .= "INSERT INTO " . $table_name_3 . " (sl_setting_name, sl_setting_value) VALUES ('sl_db_prefix', '" . $wpdb->prefix . "');";
			
	if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name || $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name_2)) != $table_name_2 || $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name_3)) != $table_name_3) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		sl_data("sl_db_version", 'add', $sl_db_version);
	}
	
	$installed_ver = sl_data("sl_db_version");
	if( $installed_ver != $sl_db_version ) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		sl_data("sl_db_version", 'update', $sl_db_version);
	}
	
	if (sl_data("sl_db_prefix")===""){
		sl_data('sl_db_prefix', 'update', $sl_db_prefix);
	}
	
	sl_move_upload_directories();
}
/*-------------------------------*/
function sl_head_scripts() {
	global $sl_dir, $sl_base, $sl_uploads_base, $sl_path, $sl_uploads_path, $wpdb, $sl_version, $pagename, $sl_map_language, $post, $sl_vars, $sl_version; 		
	
	print "\n<!-- ========= WordPress Store Locator (v$sl_version) | http://www.viadat.com/store-locator/ ========== -->\n";
	//print "<!-- ========= Learn More & Download Here: http://www.viadat.com/store-locator ========== -->\n";
		
	//Check if currently on page with shortcode
	if(empty($_GET['p'])){ $_GET['p']=""; } if(empty($_GET['page_id'])){ $_GET['page_id']=""; }
	$on_sl_page=$wpdb->get_results("SELECT post_name, post_content FROM ".SL_DB_PREFIX."posts WHERE LOWER(post_content) LIKE '%[store-locator%' AND post_status IN ('publish', 'draft') AND (post_name='$pagename' OR ID='".mysql_real_escape_string($_GET['p'])."' OR ID='".mysql_real_escape_string($_GET['page_id'])."')", ARRAY_A);		
	//Checking if code used in posts	
	$sl_code_is_used_in_posts=$wpdb->get_results("SELECT post_name, ID FROM ".SL_DB_PREFIX."posts WHERE LOWER(post_content) LIKE '%[store-locator%' AND post_type='post'", ARRAY_A);
	//If shortcode used in posts, put post IDs into array of numbers
	if ($sl_code_is_used_in_posts) {
		$sl_post_ids=$sl_code_is_used_in_posts;
		foreach ($sl_post_ids as $val) { $post_ids_array[]=$val['ID'];}
	} else {		
		$post_ids_array=array(pow(10,15)); //post number that'll never be reached
	}
	//print_r($post_ids_array);
	
	//If on page with store locator shortcode, on an archive, search, or 404 page while shortcode has been used in a post, on the front page, or a specific post with shortcode, display code, otherwise, don't
	if ($on_sl_page || is_search() || ((is_archive() || is_404()) && $sl_code_is_used_in_posts) || is_front_page() || is_single($post_ids_array) || function_exists('show_sl_scripts')) {
		$google_map_domain=($sl_vars['google_map_domain']!="")? $sl_vars['google_map_domain'] : "maps.google.com";
		
		//print "<meta name='viewport' content='initial-scale=1.0, user-scalable=no' />\n";
		$sens=(!empty($sl_vars['sensor']))? "&sensor=".$sl_vars['sensor'] : "&sensor=false" ;
		$lang_loc=(!empty($sl_vars['map_language']))? "&language=".$sl_vars['map_language'] : "" ; 
		$region_loc=(!empty($sl_vars['map_region']))? "&region=".$sl_vars['map_region'] : "" ;
		$key=(!empty($sl_vars['api_key']))? "&key=".$sl_vars['api_key'] : "" ;
		print "<script src='http://maps.googleapis.com/maps/api/js?v=3{$sens}{$lang_loc}{$region_loc}{$key}' type='text/javascript'></script>\n";
		//print "<script src='//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js'></script>\n";
		print "<script src='".SL_JS_BASE."/functions.js' type='text/javascript'></script>\n";
		if (empty($_POST) && 1==2) {
			$nm=(!empty($post->post_name))? $post->post_name : $pagename ;
			$p=(!empty($post->ID))? $post->ID : mysql_real_escape_string($_GET['p']) ;
			//$pg=(!empty($post->page_ID))? $post->post_ID : mysql_real_escape_string($_GET['page_id']) ;
			print "<script src='".SL_JS_BASE."/store-locator-js.php?nm=$nm&p=$p'  type='text/javascript'></script>\n";
		} else {
			//sl_dyn_js($on_sl_page[0]['post_content']);
			sl_dyn_js();
		}
		print "<script src='".SL_JS_BASE."/store-locator.js?v=$sl_version' type='text/javascript'></script>\n";
		//if store-locator.css exists in custom-css/ folder in uploads/ dir it takes precedence over, store-locator.css in store-locator plugin directory to allow for css customizations to be preserved after updates
		$has_custom_css=(file_exists(SL_CUSTOM_CSS_PATH."/store-locator.css"))? SL_CUSTOM_CSS_BASE : SL_CSS_BASE; 
		print "<link  href='".$has_custom_css."/store-locator.css' type='text/css' rel='stylesheet'/>\n";
		$theme=$sl_vars['theme'];
		if ($theme!="") {print "<link  href='".SL_THEMES_BASE."/$theme/style.css' rel='stylesheet' type='text/css'/>\n";}
		if (function_exists("do_sl_hook")){do_sl_hook('sl_addon_head_styles');}
		//print "<style></style>";
		sl_move_upload_directories();
	} else {
		$sl_page_ids=$wpdb->get_results("SELECT ID FROM ".SL_DB_PREFIX."posts WHERE LOWER(post_content) LIKE '%[store-locator%' AND post_status='publish'", ARRAY_A);
		print "<!-- No store locator on this page, so no unnecessary scripts for better site performance. (";
		if ($sl_page_ids) {
			foreach ($sl_page_ids as $value) { print "$value[ID],";}
		}
		print ")-->\n";
	}
	print "<!-- ========= End WordPress Store Locator ========== -->\n\n";
}
function sl_jq() {wp_enqueue_script( 'jquery');}
add_action('wp_enqueue_scripts', 'sl_jq');
/*-----------------------------------*/
function sl_add_options_page() {
	global $sl_dir, $sl_base, $sl_uploads_base, $text_domain, $sl_top_nav_links;
	$parent_url = SL_PARENT_URL; //SL_PAGES_DIR.'/information.php';
	
	$sl_menu_pages['main'] = array('title' => __("Store Locator", SL_TEXT_DOMAIN), 'capability' => 'administrator', 'page_url' =>  $parent_url, 'icon' => SL_BASE.'/images/logo.ico.png', 'menu_position' => 25.1);
	$sl_menu_pages['sub']['information'] = array('parent_url' => $parent_url, 'title' => __("News & Upgrades", SL_TEXT_DOMAIN), 'capability' => 'administrator', 'page_url' => $parent_url);
	$sl_menu_pages['sub']['locations'] = array('parent_url' => $parent_url, 'title' => __("Locations", SL_TEXT_DOMAIN), 'capability' => 'administrator', 'page_url' => SL_PAGES_DIR.'/locations.php');
	$sl_menu_pages['sub']['mapdesigner'] = array('parent_url' => $parent_url, 'title' => __("MapDesigner&trade;", SL_TEXT_DOMAIN), 'capability' => 'administrator', 'page_url' => SL_PAGES_DIR.'/mapdesigner.php');
	
	sl_menu_pages_filter($sl_menu_pages);
	
	/*
	add_menu_page(__("Store Locator", SL_TEXT_DOMAIN), __("Store Locator", SL_TEXT_DOMAIN), 'administrator', SL_PAGES_DIR.'/locations.php', '', SL_BASE.'/images/logo.ico.png', 25.1);
	$sl_pg_loc = add_submenu_page(SL_PAGES_DIR.'/locations.php', __("Locations", SL_TEXT_DOMAIN), __("Locations", SL_TEXT_DOMAIN), 'administrator', SL_PAGES_DIR.'/locations.php');
	$sl_pg_md = add_submenu_page(SL_PAGES_DIR.'/locations.php', __("MapDesigner&trade;", SL_TEXT_DOMAIN), __("MapDesigner&trade;", SL_TEXT_DOMAIN), 'administrator', SL_PAGES_DIR.'/mapdesigner.php');*/
	
	//}
}

function sl_menu_pages_filter(&$sl_menu_pages) {
	//if (function_exists('do_sl_hook')){do_sl_hook('sl_menu_pages');}
	
	foreach ($sl_menu_pages as $menu_type => $value) {
		if ($menu_type == 'main') {
			add_menu_page ($value['title'], $value['title'], $value['capability'], $value['page_url'], '', $value['icon'], $value['menu_position']);
		}
		if ($menu_type == 'sub'){
			foreach ($value as $sub_value) {
				 add_submenu_page($sub_value['parent_url'], $sub_value['title'], $sub_value['title'], $sub_value['capability'], $sub_value['page_url']);
			}
		}
	}
}
/*---------------------------------------------------*/
function sl_add_admin_javascript() {
        global $sl_base, $sl_uploads_base, $sl_dir, $google_map_domain, $sl_path, $sl_uploads_path, $sl_map_language, $sl_vars;

       // print "<script src='".SL_JS_BASE."/functions.js'></script>\n";
		wp_enqueue_script( 'prettyPhoto', SL_JS_BASE."/jquery.prettyPhoto.js", "jQuery");
		wp_enqueue_script( 'sl_func', SL_JS_BASE."/functions.js", "jQuery");
        print "<script type='text/javascript'>";
        $admin_js = "
        var sl_dir='".$sl_dir."';
        var sl_google_map_country='".$sl_vars['google_map_country']."';
        var sl_base='".SL_BASE."';
        var sl_path='".SL_PATH."';
        var sl_uploads_base='".SL_UPLOADS_BASE."';
        var sl_uploads_path='".SL_UPLOADS_PATH."';
        var sl_addons_base=sl_uploads_base+'".str_replace(SL_UPLOADS_BASE, '', SL_ADDONS_BASE)."';
        var sl_addons_path=sl_uploads_path+'".str_replace(SL_UPLOADS_PATH, '', SL_ADDONS_PATH)."';
        var sl_includes_base=sl_base+'".str_replace(SL_BASE, '', SL_INCLUDES_BASE)."';
        var sl_includes_path=sl_path+'".str_replace(SL_PATH, '', SL_INCLUDES_PATH)."';
        var sl_cache_path=sl_uploads_path+'".str_replace(SL_UPLOADS_PATH, '', SL_CACHE_PATH)."';
        var sl_pages_base=sl_base+'".str_replace(SL_BASE, '', SL_PAGES_BASE)."'";
        print preg_replace("@[\\\]@", "\\\\\\", $admin_js); //Windows-based server path backslash escape fix
        print "</script>\n";
        if (preg_match("@add-locations\.php|locations\.php@", $_SERVER['REQUEST_URI'])) {
			if (!file_exists(SL_ADDONS_PATH."/point-click-add/point-click-add.js")) {
				$sens=($sl_vars['sensor']!="")? "sensor=".$sl_vars['sensor'] : "sensor=false" ;
				$lang_loc=($sl_vars['map_language']!="")? "&language=".$sl_vars['map_language'] : "" ; 
				$region_loc=($sl_vars['map_region']!="")? "&region=".$sl_vars['map_region'] : "" ;
				$key=(!empty($sl_vars['api_key']))? "&key=".$sl_vars['api_key'] : "" ;
				//print "<script src='http://maps.googleapis.com/maps/api/js?{$sens}{$lang_loc}{$region_loc}{$key}' type='text/javascript'></script>\n";
			}
            if (file_exists(SL_ADDONS_PATH."/point-click-add/point-click-add.js")) {
				$sens=($sl_vars['sensor']!="")? "sensor=".$sl_vars['sensor'] : "sensor=false" ;
				$char_enc='&oe='.$sl_vars['map_character_encoding'];
				$google_map_domain=($sl_vars['google_map_domain']!="")? $sl_vars['google_map_domain'] : "maps.google.com";
				$api=sl_data('store_locator_api_key');
				print "<script src='http://$google_map_domain/maps?file=api&amp;v=2&amp;key=$api&amp;{$sens}{$char_enc}' type='text/javascript'></script>\n";
				print "<script src='".SL_ADDONS_BASE."/point-click-add/point-click-add.js'></script>\n";
			}
        }
		if (function_exists('do_sl_hook')){do_sl_hook('sl_addon_admin_scripts');}
}

function sl_remove_conflicting_scripts(){
	if (preg_match("@".SL_DIR."@", $_SERVER['REQUEST_URI'])){
		wp_dequeue_script('ui-tabs'); //Firefox-only conflict with 'ui-tabs' (jquery.tabs.pack.js) from wp-shopping-cart
	}
}
add_action('admin_enqueue_scripts', 'sl_remove_conflicting_scripts');

function sl_add_admin_stylesheet() {
  global $sl_base;
  print "<link rel='stylesheet' type='text/css' href='".SL_CSS_BASE."/admin.css'>\n";
  print "<link rel='stylesheet' href='".SL_CSS_BASE."/sl-pop.css' type='text/css' media='screen' charset='utf-8' />\n";
}
/*---------------------------------*/
function sl_set_query_defaults() {
	global $where, $o, $d, $sl_searchable_columns, $wpdb;
	$extra="";  //var_dump($sl_searchable_columns); die();
	if (function_exists("do_sl_hook") && !empty($sl_searchable_columns) && !empty($_GET['q'])) {
		foreach ($sl_searchable_columns as $value) {
			$extra .= $wpdb->prepare(" OR $value LIKE '%%%s%%'", $_GET['q']);
		}
	}
	
	$where=(!empty($_GET['q']))? $wpdb->prepare(" WHERE sl_store LIKE '%%%s%%' OR sl_address LIKE '%%%s%%' OR sl_city LIKE '%%%s%%' OR sl_state LIKE '%%%s%%' OR sl_zip LIKE '%%%s%%' OR sl_tags LIKE '%%%s%%'", $_GET['q'], $_GET['q'], $_GET['q'], $_GET['q'], $_GET['q'], $_GET['q'])." ".$extra : "" ; //die($where);
	//$where = (trim($where)!="")? $where." AND sl_private<>'1' " : " WHERE sl_private<>'1' ";
	$o=(!empty($_GET['o']))? mysql_real_escape_string($_GET['o']) : "sl_store";
	$d=(empty($_GET['d']) || $_GET['d']=="DESC")? "ASC" : "DESC";
}
function set_query_defaults() {sl_set_query_defaults();}
/*--------------------------------------------------------------*/
function do_hyperlink(&$text, $target="'_blank'", $type="both"){
  if ($type=="both" || $type=="protocol") {	
   // match protocol://address/path/
   $text = preg_replace("@[a-zA-Z]+://([.]?[a-zA-Z0-9_/?&amp;%20,=\-\+\-\#])+@s", "<a href=\"\\0\" target=$target>\\0</a>", $text);
  }
  if ($type=="both" || $type=="noprotocol") {
   // match www.something
   $text = preg_replace("@(^| )(www([.]?[a-zA-Z0-9_/=-\+-\#])*)@s", "\\1<a href=\"http://\\2\" target=$target>\\2</a>", $text);
  }
  return $text;
}
/*-------------------------------------------------------------*/
function comma($a) {
	$a=str_replace('"', "&quot;", $a);
	$a=str_replace("'", "&#39;", $a);
	$a=str_replace(">", "&gt;", $a);
	$a=str_replace("<", "&lt;", $a);
	$a=str_replace(" & ", " &amp; ", $a);
	return str_replace("," ,"&#44;" ,$a);
	
}
/*------------------------------------------------------------*/
if (!function_exists('addon_activation_message')) {
	function addon_activation_message($url_of_upgrade="") {
		global $sl_dir, $text_domain;
		print "<div style='background-color:#eee; border:solid silver 1px; padding:7px; color:black'>".__("You haven't activated this upgrade yet", SL_TEXT_DOMAIN).". ";
		if (function_exists('do_sl_hook') && $url_of_upgrade != "addons-platform"){
			print "<a href='".SL_ADDONS_PAGE."'>".__("Activate", SL_TEXT_DOMAIN)."</a></div><br>";
		} else {
			print __("Go to pull-out Dashboard, and activate under 'Activation Keys' section.", SL_TEXT_DOMAIN);
		}
	}
}
/*-----------------------------------------------------------*/
function url_test($url){
	if(strtolower(substr($url,0,7))=="http://")	{
		return TRUE; }
	else{
		return FALSE; }
}
/*---------------------------------------------------------------*/
function sl_neat_title($ttl,$seperator="_") {
	$ttl=preg_replace("/@+/", "$seperator", preg_replace("/[^[:alnum:]]/", "@", trim(preg_replace("/[^[:alnum:]]/", " ", str_replace("'", "", sl_truncate(trim(strtolower(html_entity_decode(str_replace("&#39;","'",$ttl)))), 100))))));
	return $ttl;
}
/*-------------------------------*/
function sl_truncate($var,$length=50,$mode="return", $type=1) {
	
	if (strlen($var)>$length) {
		if ($type==1) {
			$var=substr($var,0,$length);
			$var=preg_replace("@[[:space:]]{1}.{1,10}$@s", "", $var); //making sure it doesn't cut word in half
			$var=$var."...";
		}
		elseif ($type==2) {
			$r_num=mt_rand();
			$r_num2=$r_num."_2";
			$var1=substr($var,0,$length);
			$var2=substr($var,$length, strlen($var)-$length);
			$var="<span id='$r_num'>$var1</span><span id='$r_num2' style='display:none'>".$var1.$var2."</span><a href='#' onclick=\"show('$r_num');show('$r_num2');this.innerHTML=(this.innerHTML.indexOf('more')!=-1)?'(...less)':'(more...)';return false;\">(more...)</a>";
		}
		elseif ($type==3) {
			//exact length truncation
			$var=substr($var,0,$length);
			$var=$var."...";
		}
	}
	if ($mode!="print") {
		return $var;
	}
	else {
		print $var;
	}
}
/*-----------------------------------------------------------*/
function sl_process_tags($tag_string, $db_action="insert", $sl_id="") {
	global $wpdb;
	$id_string="";
	
	if (!is_array($sl_id) && preg_match("@,@", $sl_id)) {
		$id_string=$sl_id;
		$sl_id=explode(",",$id_string);
		$rplc_arr=array_fill(0, count($sl_id), "%d"); //var_dump($rplc_arr); //die(); 
		$id_string=implode(",", array_map(array($wpdb, "prepare"), $rplc_arr, $sl_id)); 
	} elseif (is_array($sl_id)) {
		$rplc_arr=array_fill(0, count($sl_id), "%d"); //var_dump($rplc_arr); //die(); 
 		$id_string=implode(",", array_map(array($wpdb, "prepare"), $rplc_arr, $sl_id)); 
	} else {
		$id_string=$wpdb->prepare("%d", $sl_id); 
	}
	
	//creating array of tags 
	if (preg_match("@,@", $tag_string)) { 
		$tag_string=preg_replace('/[^A-Za-z0-9_\-, ]/', '', $tag_string); 
		$sl_tag_array=array_map('trim',explode(",",trim($tag_string))); 
		$sl_tag_array=array_map('strtolower', $sl_tag_array); 
	} else { 
		$tag_string=preg_replace('/[^A-Za-z0-9_\-, ]/', '', $tag_string); 
		$sl_tag_array[]=strtolower(trim($tag_string)); 
	} 
	
	if ($db_action=="insert") {
		$wpdb->query("DELETE FROM ".SL_TAG_TABLE." WHERE sl_id IN ($id_string)");  //clear current tags for locations being modified 
		//build insert query
		$query="INSERT INTO ".SL_TAG_TABLE." (sl_tag_slug, sl_id) VALUES ";
		if (!is_array($sl_id)) {
			$main_sl_id=($sl_id==="")? $wpdb->insert_id : $sl_id ; 
			foreach ($sl_tag_array as $value)  {
				if (trim($value)!="") {
					$query.="('$value', '$main_sl_id'),";
				}
			}
		} elseif (is_array($sl_id)) {
			foreach ($sl_id as $value2) {
				$main_sl_id=$value2;
				foreach ($sl_tag_array as $value)  {
					if (trim($value)!="") {
						$query.="('$value', '$main_sl_id'),";
					}
				}
			}
		}
		$query=substr($query, 0, strlen($query)-1); // remove last comma 
		//print($query);
	} elseif ($db_action=="delete") {
		if (trim($tag_string)==="") {
			$query="DELETE FROM ".SL_TAG_TABLE." WHERE sl_id IN ($id_string)";
		} else {
			$t_string=implode("','", $sl_tag_array); //die($t_string); 
			$query="DELETE FROM ".SL_TAG_TABLE." WHERE sl_id IN ($id_string) AND sl_tag_slug IN ('".trim($t_string)."')"; 
			//die($query."\n"); 
		}
	} 
	$wpdb->query($query);
}
/*-----------------------------------------------------------*/
function sl_ty($file){
$ty['http'] = $_SERVER['HTTPS'] = 'on' ? 'https://':'http://';
$ty['url']	= urlencode("http://locl.es/Lcatr");
$ty['text'] = urlencode(__("Love it! I've made my site more user-friendly with", SL_TEXT_DOMAIN)." LotsOfLocales - #WordPress #StoreLocator #GoogleMaps");
$ty['text2'] = urlencode(__("Great! I can now easily display my locations using", SL_TEXT_DOMAIN)." LotsOfLocales - #GoogleMaps #StoreLocator #WordPress");
$ty['is_included']=(basename($file) != basename($_SERVER['SCRIPT_FILENAME']) )? true : false;
if (!$ty['is_included']) {
	$ty['thanks_msg'] = __("<b>Let us know how fantastic you think WordPress Store Locator is!</b> <br><a href='#' class='star_button'>Go to our page in the WordPress plugin repository and rate us</a>.<br><br><b>Any problems?</b><br><a href='http://docs.viadat.com/' target='_blank'>Documentation</a> is available or <a href='http://www.viadat.com/contact/' target='_blank'>contact us</a>.", SL_TEXT_DOMAIN)."<br><br>";
	$ty['thanks_msg_style'] = "style='line-height:20px; font-familty:helvetica; text-align:left; font-size:15px'";
	$ty['thanks_heading'] = "<br>".__("Want to Help Us Improve WordPress?", SL_TEXT_DOMAIN)."<br><br>";
	$ty['action_call'] =  __("Buttons to Spread the Word!", SL_TEXT_DOMAIN);
	$ty['action_call_style'] = "style='font-size:20px; text-align:left; display:block;  font-family:Georgia;'";
	$ty['action_buttons_style'] = "style='text-align:left; padding-top:11px; padding-left:0px;font-weight:normal;font-size:15px'";
} else {
	$ty['thanks_msg'] = __("<b>Let us know how fantastic you think WordPress Store Locator is!</b> <br><a href='#' class='star_button'>Go to our page in the WordPress plugin repository and rate us</a>.<br><br><b>Any problems?</b><br><a href='http://docs.viadat.com/' target='_blank'>Documentation</a> is available or <a href='http://www.viadat.com/contact/' target='_blank'>contact us</a>.", SL_TEXT_DOMAIN)."";
	$ty['thanks_msg_style'] = "";
	$ty['thanks_heading'] ="";
	$ty['action_call'] ="";
	$ty['action_call_style'] = "";
	$ty['action_buttons_style'] = "";
}
$ty['done_msg'] = __("Done!  I've tweeted about / rated it", SL_TEXT_DOMAIN);
$ty['noshow_msg'] = __("Don't show this message again", SL_TEXT_DOMAIN);
	return $ty;
}
/*-----------------------------------------------------------*/
function sl_prepare_tag_string($sl_tags) {
	//$sl_tags=preg_replace('/[ ]*\&\#44\;[ ]*/', '&#44; ', $sl_tags); 
	$sl_tags=preg_replace('/\,+/', ', ', $sl_tags); 
	$sl_tags=preg_replace('/(\&\#44\;)+/', '&#44; ', $sl_tags); 
	$sl_tags=preg_replace('/[^A-Za-z0-9_\-,]/', '', $sl_tags); 
	if (substr($sl_tags, 0, 1) == ",") {
		$sl_tags=substr($sl_tags, 1, strlen($sl_tags));
	}
	if (substr($sl_tags, strlen($sl_tags)-1, 1) != "," && trim($sl_tags)!="") {
		$sl_tags.=",";
	}
	$sl_tags=preg_replace('/\,+/', ', ', $sl_tags);
	$sl_tags=preg_replace('/(\&\#44\;)+/', '&#44; ', $sl_tags);
	$sl_tags=preg_replace('/[ ]*,[ ]*/', ', ', $sl_tags); 
 	$sl_tags=preg_replace('/[ ]*\&\#44\;[ ]*/', '&#44; ', $sl_tags); 
	$sl_tags=trim($sl_tags);
	return $sl_tags;
}
/*-----------------------------------------------------------*/
function sl_data($setting_name, $i_u_d_s="select", $setting_value="") {
	global $wpdb;
	//$$addon_slug[trim($setting[0])] = trim($setting[1]);
	if ($i_u_d_s == "insert" || $i_u_d_s == "add" || $i_u_d_s == "update") {
		//$setting = explode("=", $setting);
		$setting_value = (is_array($setting_value))? serialize($setting_value) : $setting_value;
		$exists = $wpdb->get_var($wpdb->prepare("SELECT sl_setting_id FROM ".SL_SETTING_TABLE." WHERE sl_setting_name = %s", $setting_name));
		if (!$exists) {	
			$q = $wpdb->prepare("INSERT INTO ".SL_SETTING_TABLE." (sl_setting_name, sl_setting_value) VALUES (%s, %s)", $setting_name, $setting_value); 
		} else { 
			$q = $wpdb->prepare("UPDATE ".SL_SETTING_TABLE." SET sl_setting_value = %s WHERE sl_setting_name = %s", $setting_value, $setting_name);
		}
		$wpdb->query($q);
	} elseif ($i_u_d_s == "delete") {
		$q = $wpdb->prepare("DELETE FROM ".SL_SETTING_TABLE." WHERE sl_setting_name = %s", $setting_name);
		$wpdb->query($q);
	} elseif ($i_u_d_s == "select" || $i_u_d_s == "get") {
		$q = $wpdb->prepare("SELECT sl_setting_value FROM ".SL_SETTING_TABLE." WHERE sl_setting_name = %s", $setting_name);
		$r = $wpdb->get_var($q);
		$r = (@unserialize($r) !== false || $r === 'b:0;')? unserialize($r) : $r;  //checking if stored in serialized form
		return $r;
	}
}
/*----------------------------------------------------------------*/
function sl_dyn_js($post_content=""){
	global $sl_dir, $sl_base, $sl_uploads_base, $sl_path, $sl_uploads_path, $wpdb, $sl_version, $pagename, $sl_map_language, $post, $sl_vars;
	print "<script type=\"text/javascript\">\n";

	$zl=(trim($sl_vars['zoom_level'])!="")? $sl_vars['zoom_level'] : 4;
	$mt=(trim($sl_vars['map_type'])!="")? $sl_vars['map_type'] : "google.maps.MapTypeId.ROADMAP"; $mt=(preg_match("@G\_@", $mt))? "'$mt'" : $mt;
	$wl=(trim($sl_vars['website_label'])!="")? parseToXML($sl_vars['website_label']) : "Website";
	$dl=(trim($sl_vars['directions_label'])!="")? parseToXML($sl_vars['directions_label']) : "Directions";
	$du=(trim($sl_vars['distance_unit'])!="")? $sl_vars['distance_unit'] : "miles";
	$oc=(trim($sl_vars['map_overview_control'])!="")? $sl_vars['map_overview_control'] : 0;
	$ic=(trim($sl_vars['icon'])!="")? $sl_vars['icon'] : SL_ICONS_BASE."/droplet_green.png";
	$ic2=(trim($sl_vars['icon2'])!="")? $sl_vars['icon2'] : SL_ICONS_BASE."/droplet_red.png";
	$gmc=(trim($sl_vars['google_map_country'])!="")? parseToXML($sl_vars['google_map_country']) : "United States" ;
	$gmd=(trim($sl_vars['google_map_domain'])!="")? $sl_vars['google_map_domain'] : "maps.google.com" ;
	$lld=(trim($sl_vars['load_locations_default'])!="")? $sl_vars['load_locations_default'] : 1 ;
	$lrwld=(trim($sl_vars['load_results_with_locations_default'])!="")? $sl_vars['load_results_with_locations_default'] : 1 ;
	$geo=(trim($sl_vars['geolocate'])!="")? $sl_vars['geolocate'] : 0 ;
	$nrf=(trim($sl_vars['no_results_found_message'])!="")? addslashes($sl_vars['no_results_found_message']) : "No Results Found";
	$lnf=(trim($sl_vars['location_not_found_message'])!="")? addslashes($sl_vars['location_not_found_message']) : "";
	
print  
"var sl_base='".SL_BASE."';
var sl_uploads_base='".SL_UPLOADS_BASE."';
var sl_addons_base=sl_uploads_base+'".str_replace(SL_UPLOADS_BASE, '', SL_ADDONS_BASE)."';
var sl_includes_base=sl_base+'".str_replace(SL_BASE, '', SL_INCLUDES_BASE)."';
var sl_map_home_icon='".$ic."'; 
var sl_map_end_icon='".$ic2."'; 
var sl_google_map_country='".$gmc."'; 
var sl_google_map_domain='".$gmd."'; 
var sl_zoom_level=$zl; 
var sl_map_type=$mt; 
var sl_website_label='$wl'; 
var sl_directions_label='$dl';
var sl_load_locations_default='".$lld."'; 
var sl_load_results_with_locations_default='".$lrwld."'; 
var sl_geolocate='".$geo."'; 
var sl_distance_unit='$du'; 
var sl_map_overview_control='$oc';
var sl_no_results_found_message='$nrf';
var sl_location_not_found_message='$lnf';\n";
	if (preg_match("@".SL_UPLOADS_BASE."@", $ic)){
		$home_icon_path=str_replace(SL_UPLOADS_BASE, SL_UPLOADS_PATH, $ic);
	} else {
		$home_icon_path=str_replace(SL_BASE, SL_PATH, $ic);
	}
	$home_size=(function_exists("getimagesize") && file_exists($home_icon_path))? getimagesize($home_icon_path) : array(0 => 26, 1 => 35);
	print "var sl_map_home_icon_width=$home_size[0];\n";
	print "var sl_map_home_icon_height=$home_size[1];\n";
	if (preg_match("@".SL_UPLOADS_BASE."@", $ic2)){
		$end_icon_path=str_replace(SL_UPLOADS_BASE, SL_UPLOADS_PATH, $ic2);
	} else {
		$end_icon_path=str_replace(SL_BASE, SL_PATH, $ic2);
	}
	$end_size=(function_exists("getimagesize") && file_exists($end_icon_path))? getimagesize($end_icon_path) : array(0 => 26, 1 => 35);
	print "var sl_map_end_icon_width=$end_size[0];\n";
	print "var sl_map_end_icon_height=$end_size[1];\n";
	
	print "</script>\n";
	if (function_exists("do_sl_hook")){do_sl_hook('sl_addon_head_scripts'); }
	if (function_exists("do_sl_hook")){ 
		print "<script>";
		sl_js_hooks();
		print "\n</script>\n";
	}
}
/*---------------------------------------*/
function sl_location_form($mode="add", $pre_html="", $post_html=""){
	$html="<form name='manualAddForm' method='post'>
	$pre_html
	<table cellpadding='0' class='widefat'>
	<thead><tr><th>".__("Type&nbsp;Address", SL_TEXT_DOMAIN)."</th></tr></thead>
	<tr>
		<td>
		<div style='display:inline; width:50%'>
		<b>".__("The General Address Format", SL_TEXT_DOMAIN).": </b>(<a href=\"#\" onclick=\"show('format'); return false;\">".__("show/hide", SL_TEXT_DOMAIN)."</a>)
		<span id='format' style='display:none'><br><i>".__("Name of Location", SL_TEXT_DOMAIN)."<br>
		".__("Address (Street - Line1)", SL_TEXT_DOMAIN)."<br>
		".__("Address (Street - Line2 - optional)", SL_TEXT_DOMAIN)."<br>
		".__("City, State Zip", SL_TEXT_DOMAIN)."</i></span><br><hr>
		".__("Name of Location", SL_TEXT_DOMAIN)."<br><input name='sl_store' size=40><br><br>
		".__("Address", SL_TEXT_DOMAIN)."<br><input name='sl_address' size=21>&nbsp;<small>(".__("Street - Line1", SL_TEXT_DOMAIN).")</small><br>
		<input name='sl_address2' size=21>&nbsp;<small>(".__("Street - Line 2 - optional", SL_TEXT_DOMAIN).")</small><br>
		<table cellpadding='0px' cellspacing='0px'><tr><td style='padding-left:0px' class='nobottom'><input name='sl_city' size='21'><br><small>".__("City", SL_TEXT_DOMAIN)."</small></td>
		<td><input name='sl_state' size='7'><br><small>".__("State", SL_TEXT_DOMAIN)."</small></td>
		<td><input name='sl_zip' size='10'><br><small>".__("Zip", SL_TEXT_DOMAIN)."</small></td></tr></table><br>
		</div><div style='display:inline; width:50%'>
		".__("Additional Information", SL_TEXT_DOMAIN)."<br>
		<textarea name='sl_description' rows='5' cols='17'></textarea>&nbsp;<small>".__("Description", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_tags'>&nbsp;<small>".__("Tags (seperate with commas)", SL_TEXT_DOMAIN)."</small><br>		
		<input name='sl_url'>&nbsp;<small>".__("URL", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_hours'>&nbsp;<small>".__("Hours", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_phone'>&nbsp;<small>".__("Phone", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_fax'>&nbsp;<small>".__("Fax", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_email'>&nbsp;<small>".__("Email", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_image'>&nbsp;<small>".__("Image URL (shown with location)", SL_TEXT_DOMAIN)."</small>";
		
		$html.=(function_exists("do_sl_hook"))? do_sl_hook("sl_add_location_fields",  "append-return") : "" ;
		
		$html.="<br><br>
	<input type='submit' value='".__("Add Location", SL_TEXT_DOMAIN)."' class='button-primary'>
	</div>
	</td>
		</tr>
	</table>
	$post_html
</form>";
	return $html;
}
function sl_add_location() {
	global $wpdb;
	$fieldList=""; $valueList="";
	foreach ($_POST as $key=>$value) {
		if (preg_match("@sl_@", $key)) {
			if ($key=="sl_tags") {
				$value=sl_prepare_tag_string($value);
			}
			$fieldList.="$key,";
			
			if (is_array($value)){
				$value=serialize($value); //for arrays being submitted
				$valueList.="'$value',";
				//$field_value_str.=$key."='$value',";
			} else {
				$valueList.=$wpdb->prepare("%s", comma(stripslashes($value))).",";
				//$field_value_str.=$key."=".$wpdb->prepare("%s", trim(comma(stripslashes($value)))).", "; 
			}
		}
	}
	$fieldList=substr($fieldList, 0, strlen($fieldList)-1);
	$valueList=substr($valueList, 0, strlen($valueList)-1);
	$wpdb->query("INSERT INTO ".SL_TABLE." ($fieldList) VALUES ($valueList)");
	$new_loc_id=$wpdb->insert_id;
	$address="$_POST[sl_address], $_POST[sl_city], $_POST[sl_state] $_POST[sl_zip]";
	sl_do_geocoding($address);
	if (!empty($_POST['sl_tags'])){
		sl_process_tags($_POST['sl_tags'], "insert", $new_loc_id);
	}
}
/*--------------------------------------------------*/
function sl_define_db_tables() {
	//since it can't use sl_data() in the sl-define.php, placed here
	$sl_db_prefix = get_option('sl_db_prefix'); 
	define('SL_DB_PREFIX', $sl_db_prefix);
	if (!empty($sl_db_prefix)) {
		define('SL_TABLE', SL_DB_PREFIX."store_locator");
		define('SL_TAG_TABLE', SL_DB_PREFIX."sl_tag");
		define('SL_SETTING_TABLE', SL_DB_PREFIX."sl_setting");
	}
}
sl_define_db_tables(); 
/*----------------------------------------------------*/
function sl_single_location_info($value, $colspan, $bgcol) {
	global $sl_hooks;
	$_GET['edit'] = $value['sl_id']; //die("edit: ".var_dump($_GET)); die();
	
	print "<tr style='background-color:$bgcol' id='sl_tr_data-$value[sl_id]'>";
	
	print "<td colspan='$colspan'><form name='manualAddForm' method=post>
	<a name='a$value[sl_id]'></a>
	<table cellpadding='0' class='manual_update_table'>
	<tr>
		<td style='vertical-align:top !important; width:30%'><b>".__("Name of Location", SL_TEXT_DOMAIN)."</b><br><input name='sl_store-$value[sl_id]' id='sl_store-$value[sl_id]' value='$value[sl_store]' size=30><br><br>
		<b>".__("Address", SL_TEXT_DOMAIN)."</b><br><input name='sl_address-$value[sl_id]' id='sl_address-$value[sl_id]' value='$value[sl_address]' size='13'>&nbsp;<small>(".__("Street - Line1", SL_TEXT_DOMAIN).")</small><br>
		<input name='sl_address2-$value[sl_id]' id='sl_address2-$value[sl_id]' value='$value[sl_address2]' size='13'>&nbsp;<small>(".__("Street - Line 2 - optional", SL_TEXT_DOMAIN).")</small><br>
		<table cellpadding='0px' cellspacing='0px'><tr><td style='padding-left:0px' class='nobottom'><input name='sl_city-$value[sl_id]' id='sl_city-$value[sl_id]' value='$value[sl_city]' size='13'><br><small>".__("City", SL_TEXT_DOMAIN)."</small></td>
		<td><input name='sl_state-$value[sl_id]' id='sl_state-$value[sl_id]' value='$value[sl_state]' size='4'><br><small>".__("State", SL_TEXT_DOMAIN)."</small></td>
		<td><input name='sl_zip-$value[sl_id]' id='sl_zip-$value[sl_id]' value='$value[sl_zip]' size='6'><br><small>".__("Zip", SL_TEXT_DOMAIN)."</small></td></tr></table>";
		
		if (function_exists("do_sl_hook")) {
			sl_show_custom_fields();
		}
		
		$cancel_onclick = "location.href=\"".str_replace("&edit=$_GET[edit]", "",$_SERVER['REQUEST_URI'])."\"";
		print "<br><br>
		<nobr><input type='submit' value='".__("Update", SL_TEXT_DOMAIN)."' class='button-primary'><input type='button' class='button' value='".__("Cancel", SL_TEXT_DOMAIN)."' onclick='$cancel_onclick'></nobr>
		</td><td style='width:30%; vertical-align:top !important;'>
		<b>".__("Additional Information", SL_TEXT_DOMAIN)."</b><br>
		<textarea name='sl_description-$value[sl_id]' id='sl_description-$value[sl_id]' rows='5' cols='17'>$value[sl_description]</textarea>&nbsp;<small>".__("Description", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_tags-$value[sl_id]' id='sl_tags-$value[sl_id]' value='$value[sl_tags]' size='13'>&nbsp;<small>".__("Tags (seperate with commas)", SL_TEXT_DOMAIN)."</small><br>		
		<input name='sl_url-$value[sl_id]' id='sl_url-$value[sl_id]' value='$value[sl_url]' size='13'>&nbsp;<small>".__("URL", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_hours-$value[sl_id]' id='sl_hours-$value[sl_id]' value='$value[sl_hours]' size='13'>&nbsp;<small>".__("Hours", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_phone-$value[sl_id]' id='sl_phone-$value[sl_id]' value='$value[sl_phone]' size='13'>&nbsp;<small>".__("Phone", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_fax-$value[sl_id]' id='sl_fax-$value[sl_id]' value='$value[sl_fax]' size='13'>&nbsp;<small>".__("Fax", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_email-$value[sl_id]' id='sl_email-$value[sl_id]' value='$value[sl_email]' size='13'>&nbsp;<small>".__("Email", SL_TEXT_DOMAIN)."</small><br>
		<input name='sl_image-$value[sl_id]' id='sl_image-$value[sl_id]' value='$value[sl_image]' size='13'>&nbsp;<small>".__("Image URL (shown with location)", SL_TEXT_DOMAIN)."</small>";
		
		print "</td><td style='vertical-align:top !important; width:40%'>";
	if (function_exists("do_sl_hook")) {do_sl_hook("sl_single_location_edit", "select-top");}
	print "</td></tr>
	</table>
</form></td>";

print "</tr>";
	}
/*-------------------------------------------*/
function sl_module($mod_name, $mod_heading="", $height="") {
	if (file_exists(SL_INCLUDES_PATH."/module-{$mod_name}.php")) {
		$css=(!empty($height))? "height:$height;" : "" ;
		print "<table class='widefat' style='background-color:transparent; border:0px; padding:4px; {$css}'>";
		if ($mod_heading){
			print "<thead><tr><th style='font-weight:bold; height:22px;'>$mod_heading</th></tr></thead>";
		}
		print "<tbody style='background-color:transparent; border:0px;'><tr><td style='background-color:transparent; border:0px;'>";
		include(SL_INCLUDES_PATH."/module-{$mod_name}.php");
		print "</td></tr></tbody></table><br>";
	}
}
/*--------------------------------------------*/
function sl_readme_parse($path_to_readme, $path_to_env){
	include($path_to_env);
//print "<span style='font-size:14px; font-family:Helvetica'>";
ob_start();
include($path_to_readme);
$txt=ob_get_contents();
ob_clean();

//TOC pt.1
$toc=$txt;
	preg_match_all("@\=\=\=[ ]([^\=\=\=]+)[ ]\=\=\=@", $toc, $toc_match_0);
	preg_match_all("@\=\=[ ]([^\=\=]+)[ ]\=\=@", $toc, $toc_match_1); //var_dump($toc_match_1); die();
	preg_match_all("@\=[ ]([^\=]+)[ ]\=@", $toc, $toc_match_2); //var_dump($toc_match_2); die();
	$toc_cont="";
	foreach ($toc_match_2[1] as $heading) {
	    if (!in_array($heading, $toc_match_1[1]) && !in_array($heading, $toc_match_0[1]) && !preg_match("@^[0-9]+\.[0-9]+@", $heading)) {
		$toc_cont.="<li style='margin-left:30px; list-style-type:circle'><a href='#".comma($heading)."' style='text-decoration:none'>$heading</a></li></li>";
	    } elseif (!in_array($heading, $toc_match_0[1]) && !preg_match("@^[0-9]+\.[0-9]+@", $heading)) { 
	    //!preg_match("@^[0-9]+\.[0-9]+@", $heading) prevents changelog numbers from showing up
	    	$toc_cont.="<li style='margin-left:15px; list-style-type:disc'><b><a href='#".comma($heading)."' style='text-decoration:none'>$heading</a></b></li>";
	    }
	}

//parsing
$th_start="<th style='font-size:125%; font-weight:bold;'>";
$h2_start="<h2 style='font-family:Georgia; margin-bottom:0.05em;'>";
$h3_start="<h3 style='font-family:Georgia; margin-bottom:0.05em; margin-top:0.3em'>";
$txt=str_replace("=== ", "$h2_start", $txt);
$txt=str_replace(" ===", "</h2>", $txt);
//$txt=str_replace("== ", "<div id='wphead' style='color:black; background: -moz-linear-gradient(center bottom, #D7D7D7, #E4E4E4) repeat scroll 0 0 transparent'><h1 id='site-heading'><span id='site-title'>", $txt);
$txt=str_replace("== ", "<table class='widefat' ><thead>$th_start", $txt);
$txt=str_replace(" ==", "</th></thead></table><!--a style='float:right' href='#readme_toc'>Table of Contents</a-->", $txt);
$txt=str_replace("= ", "$h3_start", $txt);
$txt=str_replace(" =", "</h3><a style='float:right; position:relative; top:-1.5em; font-size:10px' href='#readme_toc'>table of contents</a>", $txt);
$txt=preg_replace("@Tags:[ ]?[^\r\n]+\r\n@", "", $txt);

//TOC pt. 2
$txt=str_replace("</h2>", "</h2><a name='readme_toc'></a><div style='float:right;  width:500px; border-radius:1em; border:solid silver 1px; padding:7px; padding-top:0px; margin:10px; margin-right:0px;'><h3>Table of Contents</h2>$toc_cont</div>", $txt);
$txt=preg_replace_callback("@$h2_start<u>([^<.]*)</u></h1>@s", create_function('$matches', 
	'return "<h2 style=\'font-family:Georgia; margin-bottom:0.05em;\'><a name=\'".comma($matches[1])."\'></a>$matches[1]</u></h1>";'), $txt);
$txt=preg_replace_callback("@$th_start([^<.]*)</th>@s", create_function('$matches',
	'return "<th style=\'font-size:125%; font-weight:bold;\'><a name=\'".comma($matches[1])."\'></a>$matches[1]</th>";'), $txt);
$txt=preg_replace_callback("@$h3_start( )*([^<.]*)( )*</h3>@s", create_function('$matches',
	'return "<h3 style=\'font-family:Georgia; margin-bottom:0.05em; margin-top:0.3em\'><a name=\"".comma($matches[2])."\"></a>{$matches[1]}$matches[2]</h3>";'), $txt);

//creating hyperlinks on top of labeled URLs (ones preceded w/a label in brackets)
$txt=preg_replace("@\[([a-zA-Z0-9_/?&amp;\&\ \.%20,=\-\+\-]+)*\]\(([a-zA-Z]+://)(([.]?[a-zA-Z0-9_/?&amp;%20,=\-\+\-\#]+)*)\)@s", "<a onclick=\"window.parent.open('\\2'+'\\3');return false;\" href=\"#\">\\1</a>", $txt);

//converting asterisked lines into HTML list items
/*$txt=preg_replace("@\*[ ]?[ ]?([a-zA-Z0-9_/?&amp;\&\ \.%20,=\-\+\-\(\)\{\}\`\'\<\>\"\#\:]+)*(\r\n)?@s", "<li style='margin-left:15px; margin-bottom:0px;'>\\1</li>", $txt);*/
$txt=preg_replace("@\*[ ]?[ ]?([^\r\n]+)*(\r\n)?@s", "<li style='margin-left:15px; margin-bottom:0px;'>\\1</li>", $txt);

//additional formatting
$txt=preg_replace("@`([^`]+)*`@", "<strong class='sl_code code' style='padding:2px; border:0px'>\\1</strong>", $txt);
$txt=preg_replace("@__([^__]+)__@", "<strong>\\1</strong>", $txt);
$txt=preg_replace("@\r\n([0-9]\.)@", "\r\n&nbsp;&nbsp;&nbsp;\\1", $txt);
$txt=preg_replace("@([A-Za-z-0-9\/\\&;# ]+): @", "<strong>\\1: </strong>", $txt);

//$txt=preg_replace("@\[(.*)\]\(([a-zA-Z]+\://[.]?[a-zA-Z0-9_/?&amp;%20,=-\+-])*\)@s", "<a href=\"\\2\" target=_blank>\\1</a>", $txt);

//creating hyperlinks out of text URLs (which have 'http:' in the front)
$txt=do_hyperlink($txt, "'_blank'", "protocol");

print nl2br($txt);
//print "</span>";

}
/*---------------------------------------------------------------*/
function sl_translate_stamp($dateVar="",$mode="return", $date_only=0, $abbreviate_month=0) {
if ($dateVar!="") {
		$mm=substr($dateVar,4,2);
		$dd=substr($dateVar,6,2);
		if ($dd<10) {$dd=str_replace("0","",$dd); } 		$yyyy=substr($dateVar,0,4);
		if (strlen($yyyy)==2 && $yyyy>=50) {
			$yyyy="19".$yyyy;
		}
		elseif (strlen($yyyy)==2 && $yyyy>=00 && $yyyy<50) {
			$yyyy="20".$yyyy;
		}
}
$months=array("January","February","March","April","May","June","July","August","September","October","November","December");
$dt="";
if (!empty($mm)) {
	$dt=$months[$mm-1];
	
	if ($abbreviate_month!=0) 
		$dt=substr($dt,0,3).".";
	
	if ($dd!="" && $yyyy!="")
		$dt.=" $dd, $yyyy";
}

if ($date_only==0) {

$hr=substr($dateVar,8,2);
$min=substr($dateVar,10,2);
$sec=substr($dateVar,12,2);

if ($hr<12) {$hr=str_replace("0","",$hr); }
if ($hr>12) {$hr=$hr-12; $suffix="pm";} else {$suffix="am";}
if ($hr==12) {$suffix="pm";}
if ($hr==0) {$hr=12;}

$dt.=" $hr:$min:$sec $suffix";

}

if ($mode!="print")
	return $dt;
elseif ($mode=="print")
	print $dt;

}
/*---------------------------------------------------------------*/
function sl_translate_date($dateVar="",$mode="return") {
if ($dateVar!="") {
		$parts=explode("/",$dateVar);
		$mm=trim($parts[0]);
		$dd=trim($parts[1]);
		if ($dd<10) {$dd=str_replace("0","",$dd); } 		$yyyy=trim($parts[2]);
		if (strlen($yyyy)==2 && $yyyy>=50) {
			$yyyy="19".$yyyy;
		}
		elseif (strlen($yyyy)==2 && $yyyy>=00 && $yyyy<50) {
			$yyyy="20".$yyyy;
		}
}
$months=array("January","February","March","April","May","June","July","August","September","October","November","December");

if ($mm!="") {
	$dt=$months[$mm-1];
	
	if ($dd!="" && $yyyy!="")
		$dt.="&nbsp;$dd,&nbsp;$yyyy";
}

if ($mode=="return")
	return $dt;
elseif ($mode=="print")
	print $dt;

}
/*-----------------------------------------------*/
### Loading SL Variables ###
$sl_vars=sl_data('sl_vars');

### Addons Platform Load ###
if (file_exists(SL_ADDONS_PATH."/addons-platform/addons-platform.php")) {
// && (preg_match("@$sl_dir@", $_SERVER['REQUEST_URI']) || preg_match('@widgets@', $_SERVER['REQUEST_URI']) || !preg_match('@wp-admin@', $_SERVER['REQUEST_URI']))) {
	sl_initialize_variables(); // needed
	include_once(SL_ADDONS_PATH."/addons-platform/addons-platform.php");
}

### Overridable Functions ###
/*-----------------------------------*/
if (!function_exists("sl_template")){
   function sl_template($content) {

	global $sl_dir, $sl_base, $sl_uploads_base, $sl_path, $sl_uploads_path, $text_domain, $wpdb, $sl_vars;
	if(! preg_match('|\[store-locator|i', $content)) {
		return $content;
	}
	else {
		$height=($sl_vars['height'])? $sl_vars['height'] : "500" ;
		$width=($sl_vars['width'])? $sl_vars['width'] : "100" ;
		$radii=($sl_vars['radii'])? $sl_vars['radii'] : "1,5,10,(25),50,100,200,500" ;
		$height_units=($sl_vars['height_units'])? $sl_vars['height_units'] : "px";
		$width_units=($sl_vars['width_units'])? $sl_vars['width_units'] : "%";
		$sl_instruction_message=($sl_vars['instruction_message'])? $sl_vars['instruction_message'] : "Enter Your Address or Zip Code Above.";
	
		$r_array=explode(",", $radii);
		$search_label=($sl_vars['search_label'])? $sl_vars['search_label'] : "Address" ;
		
		$unit_display=($sl_vars['distance_unit']=="km")? "km" : "mi";
		$r_options="";
		foreach ($r_array as $value) {
			$s=(preg_match("@\(.*\)@", $value))? " selected='selected' " : "" ;
			$value=preg_replace("@[^0-9]@", "", $value);
			$r_options.="<option value='$value' $s>$value $unit_display</option>";
		}
		
		if ($sl_vars['use_city_search']==1) {
			$cs_array=$wpdb->get_results("SELECT CONCAT(TRIM(sl_city), ', ', TRIM(sl_state)) as city_state FROM ".SL_TABLE." WHERE sl_city<>'' AND sl_state<>'' AND sl_latitude<>'' AND sl_longitude<>'' GROUP BY city_state ORDER BY city_state ASC", ARRAY_A);
			//var_dump($cs_array); die();
			$cs_options="";
			if (!empty($cs_array)) {
				foreach($cs_array as $value) {
$cs_options.="<option value='$value[city_state]'>$value[city_state]</option>";
				}
			}
		}
		/*if ($sl_vars['use_name_search']==1) {
			$name_array=$wpdb->get_results("SELECT sl_store FROM ".SL_TABLE." WHERE sl_store<>'' ORDER BY sl_store ASC", ARRAY_A);
			//var_dump($cs_array); die();
			if ($name_array) {
				foreach($name_array as $value) {
					$name_options.="<option value='".comma($value[sl_store])."'>".comma($value[sl_store])."</option>";
				}
			}
		}*/
	
	if ($sl_vars['theme']!="") {
		$theme_base=SL_UPLOADS_BASE."/themes/".$sl_vars['theme'];
		$theme_path=SL_UPLOADS_PATH."/themes/".$sl_vars['theme'];	
	}
	else {
		$theme_base=SL_UPLOADS_BASE."/images";
		$theme_path=SL_UPLOADS_PATH."/images";
	}
	if (!file_exists($theme_path."/search_button.png")) {
		$theme_base=SL_BASE."/images";
		$theme_path=SL_PATH."/images";
	}
	$submit_img=$theme_base."/search_button.png";
	$loading_img=(file_exists(SL_UPLOADS_PATH."/images/loading.gif"))? SL_UPLOADS_BASE."/images/loading.gif" : SL_BASE."/images/loading.gif"; //for loading/processing gif image
	$mousedown=(file_exists($theme_path."/search_button_down.png"))? "onmousedown=\"this.src='$theme_base/search_button_down.png'\" onmouseup=\"this.src='$theme_base/search_button.png'\"" : "";
	$mouseover=(file_exists($theme_path."/search_button_over.png"))? "onmouseover=\"this.src='$theme_base/search_button_over.png'\" onmouseout=\"this.src='$theme_base/search_button.png'\"" : "";
	$button_style=(file_exists($theme_path."/search_button.png"))? "type='image' src='$submit_img' $mousedown $mouseover" : "type='submit'";
	$button_style.=" onclick=\"showLoadImg('show', 'loadImg');\""; //added 3/30/12 for loading/processing gif image
	//print "$submit_img | ".SL_UPLOADS_PATH."/themes/".$sl_vars['theme']."/search_button.png";
	$hide=($sl_vars['remove_credits']==1)? "display:none;" : "";
	
$form="
<div id='sl_div'>
  <form onsubmit='searchLocations(); return false;' id='searchForm' action=''>
    <table border='0' cellpadding='3px' class='sl_header' style='width:$width$width_units;'><tr>
	<td valign='top' id='search_label'>$search_label&nbsp;</td>
	<td ";
	
	if ($sl_vars['use_city_search']!=1) {$form.=" colspan='4' ";}
	
	$form.=" valign='top'><input type='text' id='addressInput' size='50' /></td>
	";
	
	if ($cs_array && $sl_vars['use_city_search']==1) {
		$form.="<td valign='top'></td>";
	}
	
	if ($cs_array && $sl_vars['use_city_search']==1) {
	$form.="
	<td id='addressInput2_container' colspan='2'>";
	$form.="<select id='addressInput2' onchange='aI=document.getElementById(\"searchForm\").addressInput;if(this.value!=\"\"){oldvalue=aI.value;aI.value=this.value;}else{aI.value=oldvalue;}'>
<option value=''>--".__("Search By City", SL_TEXT_DOMAIN)."--</option>$cs_options</select></td>";
	}
	
	/*if ($name_array && $sl_vars['use_name_search']==1) {
		$form.="<td valign='top'><nobr>&nbsp;<b>OR</b>&nbsp;</nobr></td>";
	}
	
	if ($name_array && $sl_vars['use_name_search']==1) {
	$form.="
	<td valign='top'>";
	$form.="<select id='addressInput3' onchange='aI=document.getElementById(\"searchForm\").addressInput;if(this.value!=\"\"){oldvalue=aI.value;aI.value=this.value;}else{aI.value=oldvalue;}'>
	<option value=''>--Search By Name--</option>
	$name_options
    </select>";
	
	//$form.="<input name='addressInput3'><input type='hidden' value='1' name='name_search'></td>";
	}*/
	
	$sl_radius_label=$sl_vars['radius_label'];
	$form.="
	</tr><tr>
	 <td id='radius_label'>".__("$sl_radius_label", SL_TEXT_DOMAIN)."</td>
	 <td id='radiusSelect_td' ";
	
	if ($sl_vars['use_city_search']==1) {$form.="colspan='2'";}
	 
	$form.="><select id='radiusSelect'>$r_options</select>
	</td>
	<td valign='top' ";
	
	if ($sl_vars['use_city_search']!=1) {$form.="colspan='2'";}
	
	$form.=" ><input $button_style value='Search Locations' id='addressSubmit'/></td>
	<td><img src='$loading_img' id='loadImg' style='opacity:0; filter:alpha(opacity=0); height:28px; vertical-align:bottom; position:relative; '></td>
	</tr></table>";
	$form.=(function_exists("do_sl_hook"))? do_sl_header() : "" ;
$form.="<table style='width:100%;/*border:solid silver 1px*/' cellspacing='0px' cellpadding='0px' > 
     <tr>
        <td style='width:100%' valign='top' id='map_td'> <div id='sl_map' style='width:$width$width_units; height:$height$height_units'></div><table cellpadding='0px' class='sl_footer' style='width:$width$width_units;{$hide}' ><tr><td class='sl_footer_left_column'><a href='http://www.viadat.com/store-locator' target='_blank'>LotsOfLocales&trade;</a></td><td class='sl_footer_right_column'> <a href='http://www.viadat.com' target='_blank' title='Map Maker for Creating Store Locators or Any Address Maps Using WordPress & Google Maps'>Viadat Creations</a></td></tr></table>
		</td>
      </tr>
	  <tr id='cm_mapTR'>
        <td width='' valign='top' style='/*display:hidden; border-right:solid silver 1px*/' id='map_sidebar_td'> <div id='map_sidebar' style='width:$width$width_units;/* $height$height_units; */'> <div class='text_below_map'>$sl_instruction_message</div></div>
        </td></tr>
  </table></form>
</div>";

	//preg_match("@\[STORE-LOCATOR [tag=\"(.*)\"]?\]@", $matched); 
	//global $map_tag=$matched[1];
	
	return preg_replace("@\[store-locator(.*)?\]@i", $form, $content);
	}
    }
}
?>
