<?php
/*
Plugin Name: CalPress Calendar
Plugin URI: http://www.calpresspro.com
Description: CalPress is an advanced calendar plugin with an elegant design, that is easy to use.  Multiple views, event detail pop-ups, color categories and more.  Simple integration with Google Maps, Facebook and other social networks.
Author: Aspire2, 3dcv1r
Version: 1.5.2
Author URI: http://www.calpresspro.com
*/

/*
Portions of this program are based on All-in-One Event Calendar by Timely. http://wordpress.org/extend/plugins/all-in-one-event-calendar/

Copyright 2012-2013 Don Kassing (email: don@mycalpress.com)
Copyright 2012-2013 Alexandr Cvirovsky (email: 3dcv1r@gmail.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

@set_time_limit( 0 );
@ini_set( "memory_limit",       "256M" );
@ini_set( "max_input_time",     "-1" );

// ===============
// = Plugin Name =
// ===============
define( 'CALP_PLUGIN_NAME',      'calpress-event-calendar' );

// ===================
// = Plugin Basename =
// ===================
define( 'CALP_PLUGIN_BASENAME',  plugin_basename( __FILE__ ) );

// ====================
// = Database Version =
// ====================
define( 'CALP_DB_VERSION',       108 );

// ================
// = Cron Version =
// ================
define( 'CALP_CRON_VERSION',     103 );

// ===============
// = Plugin Path =
// ===============
define( 'CALP_PATH',             dirname( __FILE__ ) );

// ===============
// = Images Path =
// ===============
define( 'CALP_IMAGE_PATH',       CALP_PATH . '/img' );

// ============
// = CSS Path =
// ============
define( 'CALP_CSS_PATH',         CALP_PATH . '/css' );

// ===========
// = JS Path =
// ===========
define( 'CALP_JS_PATH',          CALP_PATH . '/js' );

// ============
// = Lib Path =
// ============
define( 'CALP_LIB_PATH',         CALP_PATH . '/lib' );

// =================
// = Language Path =
// =================
define( 'CALP_LANGUAGE_PATH',    CALP_PLUGIN_NAME . '/language' );

// ============
// = App Path =
// ============
define( 'CALP_APP_PATH',         CALP_PATH . '/app' );

// ===================
// = Controller Path =
// ===================
define( 'CALP_CONTROLLER_PATH',  CALP_APP_PATH . '/controller' );

// ==============
// = Model Path =
// ==============
define( 'CALP_MODEL_PATH',       CALP_APP_PATH . '/model' );

// =============
// = View Path =
// =============
define( 'CALP_VIEW_PATH',        CALP_APP_PATH . '/view' );

// ===============
// = Helper Path =
// ===============
define( 'CALP_HELPER_PATH',      CALP_APP_PATH . '/helper' );

// ==================
// = Exception Path =
// ==================
define( 'CALP_EXCEPTION_PATH',   CALP_APP_PATH . '/exception' );

// ==============
// = Plugin Url =
// ==============

define( 'CALP_URL',              plugins_url( '', __FILE__ ) );

// ==============
// = Images URL =
// ==============
define( 'CALP_IMAGE_URL',        CALP_URL . '/img' );

// ===========
// = CSS URL =
// ===========
define( 'CALP_CSS_URL',          CALP_URL . '/css' );

// ===========
// = THEME URL =
// ===========
define( 'CALP_THEME_URL',        CALP_URL . '/themes/' );

// ==========
// = JS URL =
// ==========
define( 'CALP_JS_URL',           CALP_URL . '/js' );

// =============
// = POST TYPE =
// =============
define( 'CALP_POST_TYPE',        'calp_event' );

// ================
// = RSS FEED URL =
// ================
define( 'CALP_RSS_FEED',         'http://feeds.feedburner.com/calp' );

// ======================================
// = FAKE CATEGORY ID FOR CALENDAR PAGE =
// ======================================
define( 'CALP_FAKE_CATEGORY_ID', -4113473042 ); // Numeric-only 1337-speak of CALP_CALENDAR - ID must be numeric

// ==============
// = SITE URL ===
// ==============
define( 'CALP_SITE_URL',       get_option( 'home' ) );

// ==============
// = API SITE URL ===
// ==============
define( 'CALP_API_SITE_URL',   'http://www.calpresspro.com/' );

// ==============
// = SCRIPT URL =
// ==============
$calp_script_url = CALP_SITE_URL . '/?plugin=' . CALP_PLUGIN_NAME;
define( 'CALP_SCRIPT_URL',       $calp_script_url );

// ====================================================
// = Convert http:// to webcal:// in CALP_SCRIPT_URL =
// =  (webcal:// protocol does not support https://)  =
// ====================================================
$tmp = str_replace( 'http://', 'webcal://', CALP_SCRIPT_URL );

// ==============
// = EXPORT URL =
// ==============
define( 'CALP_EXPORT_URL', "$tmp&controller=calp_exporter_controller&action=export_events&cb=".rand() );

// ====================================
// = Include iCal parsers and helpers =
// ====================================
require_once( CALP_LIB_PATH . '/iCalcreator.class.php' );
require_once( CALP_LIB_PATH . '/iCalUtilityFunctions.class.php' );
require_once( CALP_LIB_PATH . '/SG_iCal.php' );
require_once( CALP_LIB_PATH . '/helpers/SG_iCal_Line.php' );
require_once( CALP_LIB_PATH . '/helpers/SG_iCal_Duration.php' );
require_once( CALP_LIB_PATH . '/helpers/SG_iCal_Freq.php' );
require_once( CALP_LIB_PATH . '/helpers/SG_iCal_Recurrence.php' );
require_once( CALP_LIB_PATH . '/helpers/SG_iCal_Parser.php' );
require_once( CALP_LIB_PATH . '/helpers/SG_iCal_Query.php' );
require_once( CALP_LIB_PATH . '/helpers/SG_iCal_Factory.php' );

// ===============================
// = The autoload function =
// ===============================
function calp_autoload( $class_name )
{
	// Convert class name to filename format.
	$class_name = strtr( strtolower( $class_name ), '_', '-' );
	$paths = array(
		CALP_CONTROLLER_PATH,
		CALP_MODEL_PATH,
		CALP_HELPER_PATH,
		CALP_EXCEPTION_PATH,
		CALP_LIB_PATH,
		CALP_VIEW_PATH,
	);

	// Search each path for the class.
	foreach( $paths as $path ) {
		if( file_exists( "$path/class-$class_name.php" ) )
		 	require_once( "$path/class-$class_name.php" );
	}
}
spl_autoload_register( 'calp_autoload' );

// ===============================
// = Initialize and setup MODELS =
// ===============================
global $calp_settings;

$calp_settings = Calp_Settings::get_instance();

// ================================
// = Initialize and setup HELPERS =
// ================================
global  $calp_view_helper,
        $calp_settings_helper,
        $calp_calendar_helper,
        $calp_app_helper,
        $calp_events_helper,
        $calp_importer_helper,
        $calp_exporter_helper;

$calp_view_helper     = Calp_View_Helper::get_instance();
$calp_settings_helper = Calp_Settings_Helper::get_instance();
$calp_calendar_helper = Calp_Calendar_Helper::get_instance();
$calp_app_helper      = Calp_App_Helper::get_instance();
$calp_events_helper   = Calp_Events_Helper::get_instance();
$calp_importer_helper = Calp_Importer_Helper::get_instance();
$calp_exporter_helper = Calp_Exporter_Helper::get_instance();


// ====================================
// = Initialize and setup CONTROLLERS =
// ====================================
global $calp_app_controller,
       $calp_settings_controller,
       $calp_events_controller,
       $calp_calendar_controller,
       $calp_importer_controller,
       $calp_exporter_controller;

$calp_app_controller      = Calp_App_Controller::get_instance();
$calp_settings_controller = Calp_Settings_Controller::get_instance();
$calp_events_controller   = Calp_Events_Controller::get_instance();
$calp_calendar_controller = Calp_Calendar_Controller::get_instance();
$calp_importer_controller = Calp_Importer_Controller::get_instance();
$calp_exporter_controller = Calp_Exporter_Controller::get_instance();

// ===================
// = Call admin menu =
// ===================
$calp_app_controller->setup_menus();
