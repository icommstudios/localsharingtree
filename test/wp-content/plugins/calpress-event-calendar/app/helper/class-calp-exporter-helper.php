<?php
// 
//  class-calp-exporter-helper.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//  
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
// 

/**
 * Calp_Exporter_Helper class
 *
 * @package Helpers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_Exporter_Helper {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 * 
	 * @var null | object
	 **/
	private static $_instance = NULL;
	
	/**
	 * Constructor
	 *
	 * Default constructor
	 **/
	private function __construct() { }

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
	 * insert_event_in_calendar function
	 *
	 * Add event to the calendar
	 *
	 * @param object $event Event object
	 * @param object $c Calendar object
	 * @param bool $export States whether events are created for export
	 *
	 * @return void
	 **/
	function insert_event_in_calendar( $event, &$c, $export = false )
	{
		global $calp_events_helper;

		$tz = get_option( 'timezone_string' );

		$e = & $c->newComponent( 'vevent' );
		if ( $event->ical_uid ) {
		    $e->setProperty( 'uid', $event->ical_uid );
		}
		$e->setProperty( 'uid', $uid );
		$e->setProperty( 'url', CALP_SITE_URL );
		$e->setProperty( 'summary', html_entity_decode( apply_filters( 'the_title', $event->post->post_title ), ENT_QUOTES, 'UTF-8' ) );
		$content = convert_chars($event->post->post_content);
		$content = str_replace(']]>', ']]&gt;', $content);
		$e->setProperty( 'description', $content );
		//$start = $event->start;
		//$end = $event->end;
        //if( $event->ical_feed_url != '' ){
            $start = $calp_events_helper->gmt_to_local( $event->start );
            $end = $calp_events_helper->gmt_to_local( $event->end );
		//}
		if( $event->allday ) {
			$dtstart = $dtend = array();
			$dtstart["VALUE"] = $dtend["VALUE"] = 'DATE';
			// For exporting all day events, don't set a timezone
			if( $tz && !$export )
				$dtstart["TZID"] = $dtend["TZID"] = $tz;

			// For exporting all day events, only set the date not the time
			if( $export ) {
				$e->setProperty( 'dtstart', date( "Ymd", $start ), $dtstart );
				$e->setProperty( 'dtend', date( "Ymd", $end ), $dtend );
			} else {
				$e->setProperty( 'dtstart', date( "Ymd\T", $start ), $dtstart );
				$e->setProperty( 'dtend', date( "Ymd\T", $end ), $dtend );
			}
		} else {
			$dtstart = $dtend = array();
			if( $tz )
				$dtstart["TZID"] = $dtend["TZID"] = $tz;

			$e->setProperty( 'dtstart', date( "Ymd\THis\Z", $start ), $dtstart );

			$e->setProperty( 'dtend', date( "Ymd\THis\Z", $end ), $dtend );
		}
		$e->setProperty( 'location', $event->venue );
		
		$contact = ! empty( $event->contact_name ) ? $event->contact_name : '';
		$contact .= ! empty( $event->contact_phone ) ? " ($event->contact_phone)" : '';
		$contact .= ! empty( $event->contact_email ) ? " <$event->contact_email>" : '';
		$e->setProperty( 'contact', $contact );
		
		$rrule = array();
		if( ! empty( $event->recurrence_rules ) ) {
			$rules = array();
			foreach( explode( ';', $event->recurrence_rules ) AS $v) {
				if( strpos( $v, '=' ) === false ) continue;
				
				list($k, $v) = explode( '=', $v );
				// If $v is a comma-separated list, turn it into array for iCalcreator
				switch( $k ) {
					case 'BYSECOND':
          case 'BYMINUTE':
          case 'BYHOUR':
          case 'BYDAY':
          case 'BYMONTHDAY':
          case 'BYYEARDAY':
          case 'BYWEEKNO':
          case 'BYMONTH':
          case 'BYSETPOS':
						$exploded = explode( ',', $v );
						break;
					default:
						$exploded = $v;
						break;
				}
				// iCalcreator requires a more complex array structure for BYDAY...
				if( $k == 'BYDAY' ) {
					$v = array();
					foreach( $exploded as $day ) {
						$v[] = array( 'DAY' => $day );
					}
				} else {
					$v = $exploded;
				}
				$rrule[ $k ] = $v;
			}
		}

		if( ! empty( $rrule ) ) $e->setProperty( 'rrule', $rrule );
	}
}
