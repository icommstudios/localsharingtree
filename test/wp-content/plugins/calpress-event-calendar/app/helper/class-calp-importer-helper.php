<?php
//
//  class-calp-importer-helper.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//

/**
 * Calp_Importer_Helper class
 *
 * @package Helpers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_Importer_Helper {
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
	 * time_array_to_timestamp function
	 *
	 * Converts time array to time string.
	 * Passed array: Array( 'year', 'month', 'day', ['hour', 'min', 'sec', ['tz']] )
	 * Return int: UNIX timestamp in GMT
	 *
	 * @param array $t iCalcreator's time property array (*full* format expected)
	 * @param string $def_timezone Default time zone in case not defined in $t
	 *
	 * @return int UNIX timestamp
	 **/
	function time_array_to_timestamp( $t, $def_timezone ) {
		$ret = $t['value']['year'] .
			'-' . $t['value']['month'] .
			'-' . $t['value']['day'];
		if( isset( $t['value']['hour'] ) )
			$ret .= ' ' . $t['value']['hour'] .
				':' . $t['value']['min'] .
				':' . $t['value']['sec'];
		$timezone = '';
        $zones = array();
        $tza = timezone_abbreviations_list();
        foreach ($tza as $zone) {
            foreach ($zone as $item) {
                $zones[] = $item['timezone_id'];
            }
        }
		if( isset( $t['value']['tz'] ) && $t['value']['tz'] == 'Z' ) 
			$timezone = 'Z';
		elseif( isset( $t['params']['TZID'] ) && in_array( $t['params']['TZID'], $zones ) )
			$timezone = $t['params']['TZID'];

		if( empty( $timezone ) ) $timezone = $def_timezone;
		if( $timezone )
			$ret .= ' ' . $timezone;
		return strtotime( $ret );
	}
    
    /**
	 * allday_time_array_to_timestamp function
	 *
	 * Converts time array to time string.
	 * Passed array: Array( 'year', 'month', 'day', ['hour', 'min', 'sec', ['tz']] )
	 * Return int: UNIX timestamp in GMT
	 *
	 * @param array $t iCalcreator's time property array (*full* format expected)
	 * @param string $def_timezone Default time zone in case not defined in $t
	 *
	 * @return int UNIX timestamp
	 **/
	public function allday_time_array_to_timestamp( $t )
	{
	   	$ret = $t['value']['year'] .
			'-' . $t['value']['month'] .
			'-' . $t['value']['day'];
		if( isset( $t['value']['hour'] ) )
			$ret .= ' ' . $t['value']['hour'] .
				':' . $t['value']['min'] .
				':' . $t['value']['sec'];
		return strtotime( $ret );
	}


	/**
	 * Gets and parses an iCalendar feed into an array of Calp_Event objects
	 *
	 * @param object $feed Row from the calp_event_feeds table
	 *
	 * @return int Number of events imported
	 */
	function parse_ics_feed( &$feed )
	{
		global $calp_events_helper;

		$count = 0;
		// set unique id, required if any component UID is missing
		$config = array( 'unique_id' => 'calp' );

		// create new instance
		$v = new vcalendar( array(
			'unique_id' => $feed->feed_url,
			'url' => $feed->feed_url,
		) );
    
		// actual parse of the feed
		if( $v->parse() )
		{
            $v->components = array_reverse( $v->components );
            $timezone = $this->getTzid($v);
			if ( empty( $timezone ) ) {
			   $timezone = $v->getProperty( 'X-WR-TIMEZONE' );
			   $timezone = $timezone[1]; 
			}
			$timezone = $this->verifyTimezone( $timezone );
            
            // re-create instance after parsing for getting timezone
			$v = new vcalendar( array(
				'unique_id' => $feed->feed_url,
				'url' => $feed->feed_url,
			) );
			// parse of the feed again
            $v->parse();
			$v->sort();
			// Reverse the sort order, so that RECURRENCE-IDs are listed before the
			// defining recurrence events, and therefore take precedence during
			// caching.
			//$v->components = array_reverse( $v->components );

			// TODO: select only VEVENT components that occur after, say, 1 month ago.
			// Maybe use $v->selectComponents(), which takes into account recurrence

			// go over each event
			while( $e = $v->getComponent( 'vevent' ) )
			{
                // ignore vevent without information
				if ( !$e ) 
				    continue;
                    
                // get recurrence id
                if ( $recurrence_time = $e->getProperty( "RECURRENCE-ID", 1, true ) ) {
				   $recurrence_start = $this->time_array_to_timestamp( $recurrence_time, $timezone );
				}
                
				$start = $e->getProperty( 'dtstart', 1, true );
				$end = $e->getProperty( 'dtend', 1, true );

				// Event is all-day if no time components are defined
				$allday = ! isset( $start['value']['hour'] );

				// convert times to GMT UNIX timestamps
				if ( !$allday ) {
				    $start = $this->time_array_to_timestamp( $start, $timezone );
					$end = $this->time_array_to_timestamp( $end, $timezone );
				} else {
					$start = $this->allday_time_array_to_timestamp( $start );
					$end = $this->allday_time_array_to_timestamp( $end );
					// If all-day, and start and end times are equal
					if ( $start === $end ) {
					    $end += 24 * 60 * 60;
					}
					$allday_tz = get_option( 'timezone_string', 'America/Los_Angeles' );
					$offset = $calp_events_helper->get_timezone_offset( 'UTC', $allday_tz, false );
					if( $offset ) {
						$start -= $offset;
						$end -= $offset;
					}
					if ( isset($recurrence_start) ) {
					    $offset = $calp_events_helper->get_timezone_offset( $timezone, $allday_tz, false );
					    $recurrence_start -= $offset;
					}
				}

				if( $rrule = $e->createRrule() )
					$rrule = trim( end( explode( ':', $rrule ) ) );
				if( $exrule = $e->createExrule() )
					$exrule = trim( end( explode( ':', $exrule ) ) );
				if( $rdate = $e->createRdate() )
					$rdate = trim( end( explode( ':', $rdate ) ) );
				if( $exdate = $e->createExdate() )
					$exdate = trim( end( explode( ':', $exdate ) ) );

				$data = array(
					'start'				=> $start,
					'end'				=> $end,
					'allday' 			=> $allday,
					'recurrence_rules'	=> $rrule,
					'exception_rules'	=> $exrule,
					'recurrence_dates'	=> $rdate,
					'exception_dates' 	=> $exdate,
					'venue' 			=> $e->getProperty( 'location' ),
					'ical_feed_url' 	=> $feed->feed_url,
					'ical_source_url' 	=> $e->getProperty( 'url' ),
					'ical_organizer' 	=> $e->getProperty( 'organizer' ),
					'ical_contact' 		=> $e->getProperty( 'contact' ),
					'ical_uid'          => $e->getProperty( 'uid' ),
					'categories'		=> $feed->feed_category,
					'tags'				=> $feed->feed_tags,
					'post'				=> array(
						'post_status'	=> 'publish',
						'post_type'		=> CALP_POST_TYPE,
						'post_author'	=> 1,
						'post_title'	=> $e->getProperty( 'summary' ),
						'post_content'	=> stripslashes( str_replace( '\n', "\n", $e->getProperty( 'description' ) ) ),
					),
				);

				$event = new Calp_Event( $data );

				// TODO: when singular events change their times in an ICS feed from one
				// import to another, the matching_event_id is null, which is wrong. We
				// want to match that event that previously had a different time.
				// However, we also want the function to NOT return a matching event in
				// the case of recurring events, and different events with different
				// RECURRENCE-IDs... ponder how to solve this.. may require saving the
				// RECURRENCE-ID as another field in the database.
				$matching_event_id = $calp_events_helper->get_matching_event_id(
					$event->ical_uid,
					$event->ical_feed_url,
					$event->start,
					! empty( $event->recurrence_rules )
				);

				if( is_null( $matching_event_id ) )
				{
					// =================================================
					// = Event was not found, so store it and the post =
					// =================================================
					$event->save();
				}
				else
				{
					// ======================================================
					// = Event was found, let's store the new event details =
					// ======================================================

					// Update the post
					$post               = get_post( $matching_event_id );
					$post->post_title   = $event->post->post_title;
					$post->post_content = $event->post->post_content;
					wp_update_post( $post );

					// Update the event
					$event->post_id = $matching_event_id;
					$event->post    = $post;
					$event->save( true );

					// Delete event's cache
					$calp_events_helper->delete_event_cache( $matching_event_id );
				}
                
                if ( isset($recurrence_start) ) {
				    $calp_events_helper->remove_recurring_instance($event, $recurrence_start);
				}

				// Regenerate event's cache
				$calp_events_helper->cache_event( $event );

				$count++;
			}
		}

		return $count;
	}
    
	/**
	 * Get TZID from the date
	 *
	 * @param object $v ICS feed object
	 *
	 * @return string TZID
	 */
	function getTzid( $v = FALSE )
	{
		$timezone = NULL;
	    if ( $e = $v->getComponent( 'vevent' ) ) {
	    	if ( $date = $e->getProperty( 'dtstart', 1, true ) ) {
	    	    if ( isset($date['params']['TZID']) ) {
	    	        $timezone = $date['params']['TZID'];
	    	    }
	    	}
	    }

	    return $timezone;
	}
    
    /**
	 * verify TZID
	 *
	 * @param string $timezone TZID
	 *
	 * @return string TZID
	 */
	function verifyTimezone( $timezone )
	{
		$zoneList = timezone_identifiers_list();
  		if (!in_array($timezone, $zoneList)) {
  		 	$timezone = $this->getWindowsTzID($timezone);
  		}
        return $timezone;
	}

	/**
	 * convert windows TZID to PHP TZID
	 *
	 * @param string $timezone TZID
	 *
	 * @return string TZID
	 */
	function getWindowsTzID( $timezone )
	{
		$timezones = array(
			"Africa/Cairo" => "Egypt Standard Time",
	        "Africa/Casablanca" => "Morocco Standard Time",
	        "Africa/Johannesburg" => "South Africa Standard Time",
	        "Africa/Lagos" => "W. Central Africa Standard Time",
	        "Africa/Nairobi" => "E. Africa Standard Time",
	        "Africa/Windhoek" => "Namibia Standard Time",
	        "America/Anchorage" => "Alaskan Standard Time",
	        "America/Asuncion" => "Paraguay Standard Time",
	        "America/Bogota" => "SA Pacific Standard Time",
	        "America/Buenos_Aires" => "Argentina Standard Time",
	        "America/Caracas" => "Venezuela Standard Time",
	        "America/Cayenne" => "SA Eastern Standard Time",
	        "America/Chicago" => "Central Standard Time",
	        "America/Chihuahua" => "Mountain Standard Time (Mexico)",
	        "America/Cuiaba" => "Central Brazilian Standard Time",
	        "America/Denver" => "Mountain Standard Time",
	        "America/Godthab" => "Greenland Standard Time",
	        "America/Guatemala" => "Central America Standard Time",
	        "America/Halifax" => "Atlantic Standard Time",
	        "America/Indianapolis" => "US Eastern Standard Time",
	        "America/La_Paz" => "SA Western Standard Time",
	        "America/Los_Angeles" => "Pacific Standard Time",
	        "America/Mexico_City" => "Mexico Standard Time",
	        "America/Montevideo" => "Montevideo Standard Time",
	        "America/New_York" => "Eastern Standard Time",
	        "America/Phoenix" => "US Mountain Standard Time",
	        "America/Regina" => "Canada Central Standard Time",
	        "America/Santa_Isabel" => "Pacific Standard Time (Mexico)",
	        "America/Santiago" => "Pacific SA Standard Time",
	        "America/Sao_Paulo" => "E. South America Standard Time",
	        "America/St_Johns" => "Newfoundland Standard Time",
	        "Asia/Almaty" => "Central Asia Standard Time",
	        "Asia/Amman" => "Jordan Standard Time",
	        "Asia/Baghdad" => "Arabic Standard Time",
	        "Asia/Baku" => "Azerbaijan Standard Time",
	        "Asia/Bangkok" => "SE Asia Standard Time",
	        "Asia/Beirut" => "Middle East Standard Time",
	        "Asia/Calcutta" => "India Standard Time",
	        "Asia/Colombo" => "Sri Lanka Standard Time",
	        "Asia/Damascus" => "Syria Standard Time",
	        "Asia/Dhaka" => "Bangladesh Standard Time",
	        "Asia/Dubai" => "Arabian Standard Time",
	        "Asia/Irkutsk" => "North Asia East Standard Time",
	        "Asia/Jerusalem" => "Israel Standard Time",
	        "Asia/Kabul" => "Afghanistan Standard Time",
	        "Asia/Kamchatka" => "Kamchatka Standard Time",
	        "Asia/Karachi" => "Pakistan Standard Time",
	        "Asia/Katmandu" => "Nepal Standard Time",
	        "Asia/Krasnoyarsk" => "North Asia Standard Time",
	        "Asia/Magadan" => "Magadan Standard Time",
	        "Asia/Novosibirsk" => "N. Central Asia Standard Time",
	        "Asia/Rangoon" => "Myanmar Standard Time",
	        "Asia/Riyadh" => "Arab Standard Time",
	        "Asia/Seoul" => "Korea Standard Time",
	        "Asia/Shanghai" => "China Standard Time",
	        "Asia/Singapore" => "Singapore Standard Time",
	        "Asia/Taipei" => "Taipei Standard Time",
	        "Asia/Tashkent" => "West Asia Standard Time",
	        "Asia/Tbilisi" => "Georgian Standard Time",
	        "Asia/Tehran" => "Iran Standard Time",
	        "Asia/Tokyo" => "Tokyo Standard Time",
	        "Asia/Ulaanbaatar" => "Ulaanbaatar Standard Time",
	        "Asia/Vladivostok" => "Vladivostok Standard Time",
	        "Asia/Yakutsk" => "Yakutsk Standard Time",
	        "Asia/Yekaterinburg" => "Ekaterinburg Standard Time",
	        "Asia/Yerevan" => "Armenian Standard Time",
	        "Atlantic/Azores" => "Azores Standard Time",
	        "Atlantic/Cape_Verde" => "Cape Verde Standard Time",
	        "Atlantic/Reykjavik" => "Greenwich Standard Time",
	        "Australia/Adelaide" => "Cen. Australia Standard Time",
	        "Australia/Brisbane" => "E. Australia Standard Time",
	        "Australia/Darwin" => "AUS Central Standard Time",
	        "Australia/Hobart" => "Tasmania Standard Time",
	        "Australia/Perth" => "W. Australia Standard Time",
	        "Australia/Sydney" => "AUS Eastern Standard Time",
	        "Etc/GMT" => "UTC",
	        "Etc/GMT+11" => "UTC-11",
	        "Etc/GMT+12" => "Dateline Standard Time",
	        "Etc/GMT+2" => "UTC-02",
	        "Etc/GMT-12" => "UTC+12",
	        "Europe/Berlin" => "W. Europe Standard Time",
	        "Europe/Budapest" => "Central Europe Standard Time",
	        "Europe/Istanbul" => "GTB Standard Time",
	        "Europe/Kiev" => "FLE Standard Time",
	        "Europe/London" => "GMT Standard Time",
	        "Europe/Minsk" => "E. Europe Standard Time",
	        "Europe/Moscow" => "Russian Standard Time",
	        "Europe/Paris" => "Romance Standard Time",
	        "Europe/Warsaw" => "Central European Standard Time",
	        "Indian/Mauritius" => "Mauritius Standard Time",
	        "Pacific/Apia" => "Samoa Standard Time",
	        "Pacific/Auckland" => "New Zealand Standard Time",
	        "Pacific/Fiji" => "Fiji Standard Time",
	        "Pacific/Guadalcanal" => "Central Pacific Standard Time",
	        "Pacific/Honolulu" => "Hawaiian Standard Time",
	        "Pacific/Port_Moresby" => "West Pacific Standard Time",
	        "Pacific/Tongatapu" => "Tonga Standard Time"
		);
			
		$result = array_keys( $timezones, $timezone );
		if ( $result ) {
		    return $result['0'];
		}
		return '';
	}
}
// END class
