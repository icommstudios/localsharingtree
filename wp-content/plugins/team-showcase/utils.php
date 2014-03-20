<?php
//UTILS

function tshowcase_strip_http($url) {	
	$url = preg_replace('#^https?://#', '', $url);
    return $url;
}

//To Show styled messages
function tshowcase_message($msg) { ?>
<div id="message" class="updated"><p><?php echo $msg; ?></p></div>
<?php	
}

function tshowcase_get_img_style($style) {
	
	global $ts_imgstyles;
	
	$css = "";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$ts_imgstyles)) {
		$css .= $ts_imgstyles[$st].' ';
		}
	}
	
	return $css;
}

function tshowcase_get_info_style($style) {
	
	global $ts_infostyles;
	
	$css = "";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$ts_infostyles)) {
		$css .= $ts_infostyles[$st].' ';
		}
	}
	
	return $css;
}


function tshowcase_get_box_style($style) {
	
	global $ts_boxstyles;
	
	$css = "";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$ts_boxstyles)) {
		$css .= $ts_boxstyles[$st].' ';
		}
	}
	
	return $css;
}

function tshowcase_get_innerbox_style($style) {
	
	global $ts_innerboxstyles;
	
	$css = "";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$ts_innerboxstyles)) {
		$css .= $ts_innerboxstyles[$st].' ';
		}
	}
	
	return $css;
}


function tshowcase_get_wrap_style($style) {
	
	global $ts_wrapstyles;
	
	$css = "";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$ts_wrapstyles)) {
		$css .= $ts_wrapstyles[$st].' ';
		}
	}
	
	return $css;
}


function tshowcase_get_txt_style($style) {
	global $ts_txtstyles;
	
	$css = "";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$ts_txtstyles)) {
		$css .= $ts_txtstyles[$st].' ';
		}
	}
	
	return $css;
}

function tshowcase_get_pager_style($style) {
	global $ts_pagerstyles;
	
	$css = "";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$ts_pagerstyles)) {
		$css .= $ts_pagerstyles[$st].' ';
		}
	}
	
	return $css;
}

function tshowcase_get_pager_box_style($style) {
	global $ts_pagerboxstyles;
	
	$css = "";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$ts_pagerboxstyles)) {
		$css .= $ts_pagerboxstyles[$st].' ';
		}
	}
	
	return $css;
}

function tshowcase_get_themes($style,$layout) {
	global $ts_theme_names;
	
	$themearray = $ts_theme_names[$layout];
		
	$css = "default";
	$styles = explode(',',$style);
	
	foreach ($styles as $st) {
	if(array_key_exists($st,$themearray)) {
		$css = $themearray[$st]['key'];
		}
	}
		
	return $css;
}


//CREATE MAILTO LINKS
function tshowcase_mailto_filter($str) {
    $regex = '/(\S+@\S+\.\S+)/';
    $replace = '<a href="mailto:$1">$1</a>';

    return preg_replace($regex, $replace, $str);
}

function tshowcase_mailto_filter_ico($str) {
    $regex = '/(\S+@\S+\.\S+)/';
    $replace = 'mailto:$1';

    return preg_replace($regex, $replace, $str);
}






?>