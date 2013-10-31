<?php


function fsp_install()
{
	global $wpdb;
	if(get_option('xyz_credit_link')=="")
	{
		add_option("xyz_credit_link", '0');
	}
	add_option("xyz_fsp_html", 'Hello world.');
	add_option("xyz_fsp_tinymce", '1');
	add_option("xyz_fsp_delay", '0');
	add_option("xyz_fsp_page_count", '1');
	add_option("xyz_fsp_mode", 'delay_only'); //page_count_only,both are other options
	add_option("xyz_fsp_repeat_interval", '1');
	add_option("xyz_fsp_repeat_interval_timing", '1');//hrs or  minute
	add_option("xyz_fsp_z_index",'100000');
		
	add_option("xyz_fsp_corner_radius",'0');
	
	add_option("xyz_fsp_border_color",'#cccccc');
	add_option("xyz_fsp_bg_color",'#ffffff');
	add_option("xyz_fsp_title",'Title');
	add_option("xyz_fsp_title_color",'#fcfcfa');
	add_option("xyz_fsp_border_width",'10');
	add_option("xyz_fsp_page_option",'1');

	add_option("xyz_fsp_iframe",'1');
	

	$version=get_option('xyz_fsp_free_version');
	$currentversion=xyz_fsp_plugin_get_version();
	if($version=="")
	{
		add_option("xyz_fsp_free_version", $currentversion);
	}
	else
	{
	
		update_option('xyz_fsp_free_version', $currentversion);
	}
	
	
}
register_activation_hook(XYZ_FSP_PLUGIN_FILE,'fsp_install');


?>