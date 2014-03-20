<?php
//
//  class-calp-exporter-controller.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//

/**
 * Calp_Exporter_Controller class
 *
 * @package Controllers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_Exporter_Controller {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

	/**
	 * get_instance function
	 *
	 * Return singleton instance
	 *
	 * @return object
	 **/
	static function get_instance() {
		if( self::$_instance === NULL ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() { }

	/**
	 * export_events function
	 *
	 * Export events
	 *
	 * @return void
	 **/
	function export_events() {
		global $calp_events_helper, $calp_events_helper, $calp_calendar_helper, $calp_exporter_helper, $wp_filter;
        
		$calp_cat_ids 	= isset( $_REQUEST['calp_cat_ids'] ) 	&& ! empty( $_REQUEST['calp_cat_ids'] ) 	? $_REQUEST['calp_cat_ids'] 	: implode(',',  $calp_calendar_helper->get_categories_array());
		$calp_tag_ids 	= isset( $_REQUEST['calp_tag_ids'] ) 	&& ! empty( $_REQUEST['calp_tag_ids'] ) 	? $_REQUEST['calp_tag_ids'] 	: false;
		$calp_post_ids = isset( $_REQUEST['calp_post_ids'] )	&& ! empty( $_REQUEST['calp_post_ids'] ) ? $_REQUEST['calp_post_ids'] : false;
		$filter = array();
		
		// remove custom filters
        remove_all_filters('the_content');
		
		if( $calp_cat_ids )
			$filter['cat_ids'] = split( ',', $calp_cat_ids );
		if( $calp_tag_ids )
			$filter['tag_ids'] = split( ',', $calp_tag_ids );
		if( $calp_post_ids )
			$filter['post_ids'] = split( ',', $calp_post_ids );
			
		// when exporting events by post_id, do not look up the event's start/end date/time
		$start  = $calp_post_ids !== false ? false : gmmktime() - 24 * 60 * 60; // Include any events ending today
		if ( !isset( $_REQUEST['calp_cat_ids'] ) || empty( $_REQUEST['calp_cat_ids'] ) ) {
            $start = false;
        }
        $end    = false;
		$events = $calp_events_helper->get_matching_events( $start, $end, $filter );
		if ( !$events) exit;
		
		$c = new vcalendar();
		$c->setProperty( 'calscale', 'GREGORIAN' );
		$c->setProperty( 'method', 'PUBLISH' );
		$c->setProperty( 'X-WR-CALNAME', get_bloginfo( 'name' ) );
		$c->setProperty( 'X-WR-CALDESC', get_bloginfo( 'description' ) );
		// Timezone setup
		$tz = get_option( 'timezone_string' );
		if( $tz ) {
			$c->setProperty( 'X-WR-TIMEZONE', $tz );
			$tz_xprops = array( 'X-LIC-LOCATION' => $tz );
			iCalUtilityFunctions::createTimezone( $c, $tz, $tz_xprops );
		}

		foreach( $events as $event ) {
			$calp_exporter_helper->insert_event_in_calendar( $event, $c, $export = true );
		}
		$str = $c->createCalendar();

		header( 'Content-type: text/calendar; charset=utf-8' );
        header( 'Content-Disposition: inline; filename=calpress.ics ');
		echo $str;
		exit;
	}
}
// END class
