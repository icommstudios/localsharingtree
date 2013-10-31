<?php
/*
Plugin Name:Full Screen Popup
Plugin URI: http://xyzscripts.com/wordpress-plugins/full-screen-popup/
Description: This plugin allows you to create a full screen popup window with custom content in your site. You can customize the popup display by configuring various settings such as display logic settings (trigger based on timeout after page load, based on number of pages browsed, popup repeat interval) and style settings(z-index, color, border etc). The plugin supports automatic and manual (shortcode) display.
Version: 1.0
Author: xyzscripts.com
Author URI: http://xyzscripts.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( !function_exists( 'add_action' ) ) 
{
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

ob_start();
error_reporting(0);

define('XYZ_FSP_PLUGIN_FILE',__FILE__);

require( dirname( __FILE__ ) . '/xyz-functions.php' );

require( dirname( __FILE__ ) . '/admin/install.php' );

require( dirname( __FILE__ ) . '/admin/menu.php' );

require( dirname( __FILE__ ) . '/create-fullscreen.php' );

require( dirname( __FILE__ ) . '/shortcode-handler.php' );

require( dirname( __FILE__ ) . '/admin/destruction.php' );

if(get_option('xyz_credit_link')=="fsp"){

	add_action('wp_footer', 'xyz_fsp_credit');

}
function xyz_fsp_credit() {
	$content = '<div style="clear:both;width:100%;text-align:center; font-size:11px; "><a target="_blank" href="http://xyzscripts.com/wordpress-plugins/full-screen-popup/details">Full Screen Popup</a> Powered By : <a target="_blank" title="PHP Scripts & Programs" href="http://www.xyzscripts.com" >XYZScripts.com</a></div>';
	echo $content;
}


?>