<?php


function fsp_destroy()
{
	global $wpdb;
	delete_option("xyz_fsp_tinymce");
	if(get_option('xyz_credit_link')=="fsp")
	{
		update_option("xyz_credit_link", '0');
	}
	delete_option("xyz_fsp_html");
	delete_option("xyz_fsp_top");
	delete_option("xyz_fsp_width");
	delete_option("xyz_fsp_height");
	delete_option("xyz_fsp_left");
	delete_option("xyz_fsp_delay");
	delete_option("xyz_fsp_page_count");
	delete_option("xyz_fsp_mode"); //page_count_only,both are other options
	delete_option("xyz_fsp_repeat_interval");
	delete_option("xyz_fsp_repeat_interval_timing");//hrs or  minute
	delete_option("xyz_fsp_z_index");
	delete_option("xyz_fsp_color");	
	delete_option("xyz_fsp_corner_radius");
	delete_option("xyz_fsp_width_dim");
	delete_option("xyz_fsp_height_dim");
	delete_option("xyz_fsp_left_dim");
	delete_option("xyz_fsp_top_dim");
	delete_option("xyz_fsp_border_color");
	delete_option("xyz_fsp_bg_color");
	delete_option("xyz_fsp_opacity");
	delete_option("xyz_fsp_border_width");
	delete_option("xyz_fsp_page_option");
	delete_option("xyz_fsp_close_button_option");
	delete_option("xyz_fsp_iframe");
	
	delete_option("xyz_fsp_positioning");
	delete_option("xyz_fsp_position_predefined");
	delete_option("xyz_fsp_display_position");
	

}

register_uninstall_hook(XYZ_FSP_PLUGIN_FILE,'fsp_destroy');


?>