<?php
//
//  class-calp-calendar-helper.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//

/**
 * Calp_Calendar_Helper class
 *
 * @package Helpers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_Calendar_Helper {
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
	 * get_events_for_month function
	 *
	 * Return an array of all dates for the given month as an associative
	 * array, with each element's value being another array of event objects
	 * representing the events occuring on that date.
	 *
	 * @param int $time         the UNIX timestamp of a date within the desired month
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array            array of arrays as per function description
	 **/
	function get_events_for_month( $time, $filter = array() )
    {
        global $calp_events_helper,
        	$calp_view_helper,
        	$calp_settings;

        $days_events = array();

        $bits = $calp_events_helper->gmgetdate( $time );

        $first_cell_index = gmdate( 'w', $time );
		// Modify weekday based on start of week setting
		$first_cell_index = ( 7 + $first_cell_index - $calp_settings->week_start_day ) % 7;

		// Get the last day of the month
		$last_day = gmdate( 't', $time );

		$last_timestamp = gmmktime( 0, 0, 0, $bits['mon'], $last_day + 1, $bits['year'] );
		// Figure out index of last table cell
		$last_cell_index = gmdate( 'w', $last_timestamp );
		// Modify weekday based on start of week setting
		$last_cell_index = ( 7 + $last_cell_index - $calp_settings->week_start_day ) % 7;

		$start_time = gmmktime( 0, 0, 0, $bits['mon'], 1 - $first_cell_index, $bits['year'] );
		$end_time   = gmmktime( 0, 0, 0, $bits['mon'], $last_day + $last_cell_index + 1, $bits['year'] );

        $month_events = $this->get_events_between( $start_time, $end_time, $filter, true );

        $month_end = ($last_cell_index > 0) ? $last_day + (7 - $last_cell_index) : $last_day;
        
        foreach( $month_events as $evt ) {
			$evt_start = $calp_events_helper->gmt_to_local( $evt->start );
			$evt_end = $calp_events_helper->gmt_to_local( $evt->end );

			// Iterate through each day of the week and generate new event object
			// based on this one for each day that it spans
			$day = 1 - $first_cell_index;
			for( $day; $day <= $month_end; $day++ ) {
				if ( !isset($days_events[$day]) ) {
				    $days_events[$day] = array();
				}
				$day_start = gmmktime( 0, 0, 0, $bits['mon'], $day, $bits['year'] );
				$day_end = gmmktime( 0, 0, 0, $bits['mon'], $day + 1, $bits['year'] );

				if( $evt_start >= $day_start && $evt_start < $day_end ) {
					$evt->googleMap = !empty ( $_evt->address ) ? $calp_view_helper->get_frontend_googlemap ( $_evt->address ) : false;
					array_push($days_events[$day], $evt);
				// If event falls on this day, make a copy.
				} elseif( $evt_end > $day_start && $evt_start < $day_end ) {
                    $evt->googleMap = !empty ( $_evt->address ) ? $calp_view_helper->get_frontend_googlemap ( $_evt->address ) : false;	
					array_push($days_events[$day], $evt);
				}
			}
		}

        return apply_filters( 'calp_get_events_for_month', $days_events, $time, $filter );
    }

	/**
	 * get_agenda_cell_array function
	 *
	 * Return an array of weeks, each containing an array of days, each
	 * containing the date for the day ['date'] (if inside the month) and
	 * the events ['events'] (if any) for the day, and a boolean ['today']
	 * indicating whether that day is today.
	 *
	 * @param int $timestamp	    UNIX timestamp of the 1st day of the desired
	 *                            month to display
	 * @param array $days_events  list of events for each day of the month in
	 *                            the format returned by get_events_for_month()
	 *
	 * @return void
	 **/
	function get_agenda_cell_array( $timestamp, $days_events , $current_item)
	{
		global $calp_settings, $calp_events_helper, $wpdb;

		// Decompose date into components, used for calculations below
		$bits = $calp_events_helper->gmgetdate( $timestamp );
		$today = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );	// Used to flag today's cell
        $current_timestamp = $calp_events_helper->gmt_to_local( time() );
		// Figure out index of first table cell
		$first_cell_index = gmdate( 'w', $timestamp );
		// Modify weekday based on start of week setting
		$first_cell_index = ( 7 + $first_cell_index - $calp_settings->week_start_day ) % 7;

		// Get the last day of the month
		$last_day = gmdate( 't', $timestamp );
		$last_timestamp = gmmktime( 0, 0, 0, $bits['mon'], $last_day, $bits['year'] );
		// Figure out index of last table cell
		$last_cell_index = gmdate( 'w', $last_timestamp );
		// Modify weekday based on start of week setting
		$last_cell_index = ( 7 + $last_cell_index - $calp_settings->week_start_day ) % 7;

		$weeks = array();
		$week = 0;
		$weeks[$week] = array();

        $current = 0;
        $current_item = (int) $current_item;
        if ( !empty($current_item) ) {
            // Find current event
            foreach ($days_events as $day) {
                foreach ($day as &$event) {
                    $event->current = ( $event->instance_id == $current_item ) ? true : false;
                    if ( $event->current ) {
                    	$current = $event->instance_id;
				    }
                }
            }
        } else {
            // Find current event
            foreach ($days_events as $day) {
                foreach ($day as &$event) {
                    $event->current = ($event->start <= $current_timestamp && $event->end >= $current_timestamp)
                        ? true : false;
                    if ( $event->current ) {
                        $current = $event->instance_id;
				        break 2;
                    }
                }
            }
        }
        if ( empty($current) ) {
        	// Find current event
            foreach ($days_events as $day) {
                foreach ($day as &$event) {
                    $event->current = ($event->start >= $current_timestamp)
                        ? true : false;
                    if ( $event->current ) {
                        $current = $event->instance_id;
				        break 2;
                    }
                }
            }
        }
        
		// Insert each month's day and associated events
		$i = 1 - $first_cell_index;
		$month_end = ($last_cell_index > 0) ? $last_day + (7 - $last_cell_index) : $last_day;
		
		for( $i; $i < $month_end; $i++ ) {
			$date = date('d', time());
			$weeks[$week][] = array(
				'date' => $i,
				'today' =>
					$bits['year'] == $today['year'] &&
					$bits['mon']  == $today['mon'] &&
					$date         == $today['mday'],
                'timestamp' => gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $i - 1, $bits['year'] ),
				'events' => isset($days_events[$i])?$days_events[$i]:array()
			);
			// If reached the end of the week, increment week
			if( count( $weeks[$week] ) == 7 )
				$week++;
		}
    
		return array('weeks' => $weeks, 'current' => $current );
	}

	/**
	 * get_month_cell_array function
	 *
	 * Return an array of weeks, each containing an array of days, each
	 * containing the date for the day ['date'] (if inside the month) and
	 * the events ['events'] (if any) for the day, and a boolean ['today']
	 * indicating whether that day is today.
	 *
	 * @param int $timestamp	    UNIX timestamp of the 1st day of the desired
	 *                            month to display
	 * @param array $days_events  list of events for each day of the month in
	 *                            the format returned by get_events_for_month()
	 *
	 * @return void
	 **/
	function get_month_cell_array( $timestamp, $days_events, $filter = array() )
	{
		global $calp_settings,
			$calp_events_helper,
			$calp_view_helper;
        
        $end_filter = $filter;
		// Decompose date into components, used for calculations below
		$bits = $calp_events_helper->gmgetdate( $timestamp );
		$today = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );	// Used to flag today's cell
        $current_timestamp = $calp_events_helper->gmt_to_local( time() );
		// Figure out index of first table cell
		$first_cell_index = gmdate( 'w', $timestamp );
		// Modify weekday based on start of week setting
		$first_cell_index = ( 7 + $first_cell_index - $calp_settings->week_start_day ) % 7;

		// Get the last day of the month
		$last_day = gmdate( 't', $timestamp );
		$last_timestamp = gmmktime( 0, 0, 0, $bits['mon'], $last_day, $bits['year'] );
		// Figure out index of last table cell
		$last_cell_index = gmdate( 'w', $last_timestamp );
		// Modify weekday based on start of week setting
		$last_cell_index = ( 7 + $last_cell_index - $calp_settings->week_start_day ) % 7;

		$weeks = array();
		$week = 0;
		$weeks[$week] = array();
        
        /// Insert any needed blank cells into first week
		$day = 1 - $first_cell_index;
		$month_end = ($last_cell_index > 0) ? $last_day + (7 - $last_cell_index) : $last_day;
        
		// Insert each month's day and associated events
		for( $i =  $day; $i < $month_end; $i++ ) {
			$evt_start_time = gmmktime( 0, 0, 0, $bits['mon'], $i, $bits['year'] );
			$new_bits = $calp_events_helper->gmgetdate( $evt_start_time );
			$date = date('d', $evt_start_time);
			$weeks[$week][] = array(
				'date' => $date,
				'today' =>
					$new_bits['year'] == $today['year'] &&
					$new_bits['mon']  == $today['mon'] &&
					$date            == $today['mday'],
                'timestamp' => gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $i, $bits['year'] ),
				'events' => isset($days_events[$i])?$days_events[$i]:array(),
			);
			// If reached the end of the week, increment week
			if( count( $weeks[$week] ) == 7 )
				$week++;
		}
        
		return $weeks;
	}

	/**
	 * get_week_cell_array function
	 *
	 * Return an associative array of weekdays, indexed by the day's date,
	 * starting the day given by $timestamp, each element an associative array
	 * containing three elements:
	 *   ['today']     => whether the day is today
	 *   ['allday']    => non-associative ordered array of events that are all-day
	 *   ['notallday'] => non-associative ordered array of non-all-day events to
	 *                    display for that day, each element another associative
	 *                    array like so:
	 *     ['top']       => how many minutes offset from the start of the day
	 *     ['height']    => how many minutes this event spans
	 *     ['indent']    => how much to indent this event to accommodate multiple
	 *                      events occurring at the same time (0, 1, 2, etc., to
	 *                      be multiplied by whatever desired px/em amount)
	 *     ['event']     => event data object
	 *
	 * @param int $timestamp    the UNIX timestamp of the first day of the week
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array            array of arrays as per function description
	 **/
	function get_week_cell_array( $timestamp, $filter = array() )
	{
		global $calp_events_helper, $calp_view_helper, $calp_settings;

		// Decompose given date and current time into components, used below
		$bits = $calp_events_helper->gmgetdate( $timestamp );
		$now = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );

		// Do one SQL query to find all events for the week, including spanning
		$week_events = $this->get_events_between(
			$timestamp,
			gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + 7, $bits['year'] ),
			$filter,
			true );

		// Split up events on a per-day basis
		$all_events = array();
		foreach( $week_events as $evt ) {
			$evt_start = $calp_events_helper->gmt_to_local( $evt->start );
			$evt_end = $calp_events_helper->gmt_to_local( $evt->end );

			// Iterate through each day of the week and generate new event object
			// based on this one for each day that it spans
			for( $day = $bits['mday']; $day < $bits['mday'] + 7; $day++ ) {
				$day_start = gmmktime( 0, 0, 0, $bits['mon'], $day, $bits['year'] );
				$day_end = gmmktime( 0, 0, 0, $bits['mon'], $day + 1, $bits['year'] );

				// If event falls on this day, make a copy.
				if( $evt_end > $day_start && $evt_start < $day_end ) {
					$_evt = clone $evt;
                    // If event has address put Google map
                    $_evt->googleMap = !empty ( $_evt->address ) ? $calp_view_helper->get_frontend_googlemap ( $_evt->address ) : false;
					if( $evt_start < $day_start ) {
						// If event starts before this day, adjust copy's start time
						$_evt->start = $calp_events_helper->local_to_gmt( $day_start );
						$_evt->start_truncated = true;
					}
					if( $evt_end > $day_end ) {
						// If event ends after this day, adjust copy's end time
						$_evt->end = $calp_events_helper->local_to_gmt( $day_end );
						$_evt->end_truncated = true;
					}

					// Place copy of event in appropriate category
					if( $_evt->allday )
						$all_events[$day_start]['allday'][] = $_evt;
					else
						$all_events[$day_start]['notallday'][] = $_evt;
				}
			}
		}

		// This will store the returned array
		$days = array();
		// =========================================
		// = Iterate through each date of the week =
		// =========================================
		for( $day = $bits['mday']; $day < $bits['mday'] + 7; $day++ )
		{
			$day_date = gmmktime( 0, 0, 0, $bits['mon'], $day, $bits['year'] );
			// Re-fetch date bits, since $bits['mday'] + 7 might be in the next month
			$day_bits = $calp_events_helper->gmgetdate( $day_date );

			// Initialize empty arrays for this day if no events to minimize warnings
			if( ! isset( $all_events[$day_date]['allday'] ) ) $all_events[$day_date]['allday'] = array();
			if( ! isset( $all_events[$day_date]['notallday'] ) ) $all_events[$day_date]['notallday'] = array();

			$notallday = array();
			$evt_stack = array( 0 ); // Stack to keep track of indentation
			foreach( $all_events[$day_date]['notallday'] as $evt )
			{
				$start_bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( $evt->start ) );

				// Calculate top and bottom edges of current event
                $top = $start_bits['hours'] * 19 + $start_bits['minutes'] / 3.15;
                $bottom = $top + min( $evt->getDuration() / 60, 1440 ) / 3.14;
                
				// While there's more than one event in the stack and this event's top
				// position is beyond the last event's bottom, pop the stack
				while( count( $evt_stack ) > 1 && $top >= end( $evt_stack ) )
					array_pop( $evt_stack );
				// Indentation is number of stacked events minus 1
				$indent = count( $evt_stack ) - 1;
				// Push this event onto the top of the stack
				array_push( $evt_stack, $bottom );

				$notallday[] = array(
					'top'    => $top,
					'height' => $bottom - $top,
					'indent' => $indent,
					'event'  => $evt,
				);
			}

			$days[$day_date] = array(
				'today'     =>
					$day_bits['year'] == $now['year'] &&
					$day_bits['mon']  == $now['mon'] &&
					$day_bits['mday'] == $now['mday'],
				'allday'    => $all_events[$day_date]['allday'],
				'notallday' => $notallday,
			);
		}

		return apply_filters( 'calp_get_week_cell_array', $days, $timestamp, $filter );
	}
    
	/**
	 * get_today_cell_array function
	 *
	 * Return an associative array of events by the day's date
	 * containing three elements:
	 *   ['allday']    => non-associative ordered array of events that are all-day
	 *   ['notallday'] => non-associative ordered array of non-all-day events to
	 *                    display for that day, each element another associative
	 *                    array like so:
	 *     ['top']       => how many minutes offset from the start of the day
	 *     ['height']    => how many minutes this event spans
	 *     ['indent']    => how much to indent this event to accommodate multiple
	 *                      events occurring at the same time (0, 1, 2, etc., to
	 *                      be multiplied by whatever desired px/em amount)
	 *     ['event']     => event data object
	 *
	 * @param int $timestamp    the UNIX timestamp of the first day of the week
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array            array of arrays as per function description
	 **/
	function get_today_cell_array( $timestamp, $filter = array() )
	{
		global $calp_events_helper, $calp_view_helper, $calp_settings;

		// Decompose given date and current time into components, used below
		$bits = $calp_events_helper->gmgetdate( $timestamp );
		$now = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
    
		// Do one SQL query to find all events for the week, including spanning
		$today_events = $this->get_events_between(
			gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] - 1, $bits['year'] ),
			gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + 1, $bits['year'] ),
			$filter,
			true );

		// Split up events on a per-day basis
		$all_events = array();
		$all_events['all'] = array();
		foreach( $today_events as $evt ) {
			$evt_start = $calp_events_helper->gmt_to_local( $evt->start );
			$evt_end = $calp_events_helper->gmt_to_local( $evt->end );

			// Iterate through each day of the week and generate new event object
			// based on this one for each day that it spans
            $day =  $bits['mday'];
            $day_start = gmmktime( 0, 0, 0, $bits['mon'], $day, $bits['year'] );
            $day_end = gmmktime( 0, 0, 0, $bits['mon'], $day + 1, $bits['year'] );

            // If event falls on this day, make a copy.
            if( $evt_end > $day_start && $evt_start < $day_end ) {
                $_evt = clone $evt;
                // If event has address put Google map
                $_evt->googleMap = !empty ( $_evt->address ) ? $calp_view_helper->get_frontend_googlemap ( $_evt->address ) : false;
                if( $evt_start < $day_start ) {
                    // If event starts before this day, adjust copy's start time
                    $_evt->start = $calp_events_helper->local_to_gmt( $day_start );
                    $_evt->start_truncated = true;
                }
                if( $evt_end > $day_end ) {
                    // If event ends after this day, adjust copy's end time
                    $_evt->end = $calp_events_helper->local_to_gmt( $day_end );
                    $_evt->end_truncated = true;
                }

                // Place copy of event in appropriate category
                if( $_evt->allday )
                    $all_events['allday'][] = $_evt;
                else
                    $all_events['notallday'][] = $_evt;
                    
                $all_events['all'][] = $_evt;
            }
		}

		// This will store the returned array
		$days = array();
        // Initialize empty arrays for this day if no events to minimize warnings
        if( ! isset( $all_events['allday'] ) ) $all_events['allday'] = array();
        if( ! isset( $all_events['notallday'] ) ) $all_events['notallday'] = array();

        $i = 0;
        $notallday = array();
        $evt_stack = array( 0 ); // Stack to keep track of indentation
        foreach( $all_events['notallday'] as $evt )
        {
            $start_bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( $evt->start ) );
            // Calculate top and bottom edges of current event
            $top = $start_bits['hours'] * 25 + $start_bits['minutes'] / 2.4;
            $bottom = $top + min( $evt->getDuration() / 60, 1440 ) / 2.4;
            // While there's more than one event in the stack and this event's top
            // position is beyond the last event's bottom, pop the stack
            while( count( $evt_stack ) > 1 && $top >= end( $evt_stack ) )
                array_pop( $evt_stack );
            // Indentation is number of stacked events minus 1
            $indent = count( $evt_stack ) - 1;
            // Push this event onto the top of the stack
            array_push( $evt_stack, $bottom );

            $notallday[] = array(
                'top'    => $top,
                'height' => $bottom - $top,
                'indent' => $indent + $i,
                'event'  => $evt,
            );
            
            $i += 2;
        }

        $day = array(
            'all'       => $all_events['all'],
            'allday'    => $all_events['allday'],
            'notallday' => $notallday,
            'empty_day' => empty( $all_events['allday'] ) && empty( $notallday )
        );
        
		return apply_filters( 'calp_get_week_cell_array', $day, $timestamp, $filter );
	}

	/**
	 * get_events_between function
	 *
	 * Return all events starting after the given start time and before the
	 * given end time that the currently logged in user has permission to view.
	 * If $spanning is true, then also include events that span this
	 * period. All-day events are returned first.
	 *
	 * @param int $start_time   limit to events starting after this (local) UNIX time
	 * @param int $end_time     limit to events starting before this (local) UNIX time
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 * @param bool $spanning    also include events that span this period
	 *
	 * @return array            list of matching event objects
	 **/
	function get_events_between( $start_time, $end_time, $filter, $spanning = false ) {

		global $wpdb, $calp_events_helper;

		// Convert timestamps to MySQL format in GMT time
		$start_time = $calp_events_helper->local_to_gmt( $start_time );
		$end_time = $calp_events_helper->local_to_gmt( $end_time );

		// Query arguments
		$args = array( $start_time, $end_time );

		// Get post status Where snippet and associated SQL arguments
		$this->_get_post_status_sql( $post_status_where, $args );

		// Get the Join (filter_join) and Where (filter_where) statements based on
		// $filter elements specified
		$this->_get_filter_sql( $filter );

		$query = $wpdb->prepare(
			"SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, " .
			"e.recurrence_rules, e.exception_rules, e.recurrence_dates, e.exception_dates, " .
			"e.venue, e.country, e.address, e.city, e.province, e.postal_code, e.show_map, " .
			"e.ical_feed_url, e.ical_source_url, e.ical_organizer, e.ical_contact, e.ical_uid " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
				$filter['filter_join'] .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
			"AND " .
				( $spanning ? "i.end > FROM_UNIXTIME( %d ) AND i.start < FROM_UNIXTIME( %d ) "
										: "i.start >= FROM_UNIXTIME( %d ) AND i.start < FROM_UNIXTIME( %d ) " ) .
			$filter['filter_where'] .
			$post_status_where .
			"ORDER BY allday DESC, i.start ASC, post_title ASC",
			$args );

		$events = $wpdb->get_results( $query, ARRAY_A );
		foreach( $events as &$event ) {
			$event = new Calp_Event( $event );
		}

		return $events;
	}
    
    /**
	 * get_agenda_item function
	 *
	 * Return events with current post id
	 *
	 * @param int $post_id      post id of selected agenda item
	 *
	 * @return array            event object
	 **/
	function get_agenda_item( $post_id ) {

		global $wpdb, $calp_events_helper, $calp_view_helper;

		$query = $wpdb->prepare(
			"SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, " .
			"e.recurrence_rules, e.exception_rules, e.recurrence_dates, e.exception_dates, " .
			"e.venue, e.country, e.address, e.city, e.province, e.postal_code, e.show_map, " .
			"e.ical_feed_url, e.ical_source_url, e.ical_organizer, e.ical_contact, e.ical_uid " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
			"AND i.id = %d ",
			(int) $post_id );

		$events = $wpdb->get_results( $query, ARRAY_A );
		foreach( $events as &$event ) {
			$event = new Calp_Event( $event );
		}
        
        if ( !empty($event)) {
            $_evt = $events['0'];
            $_evt->googleMap = !empty ( $_evt->address ) ? $calp_view_helper->get_frontend_googlemap ( $_evt->address ) : false;
            return $_evt;
        }
        
        return false;
	}
    
    /**
	 * get_event_by_postid function
	 *
	 * Return events with current post id
	 *
	 * @param int $post_id      post id of event
	 *
	 * @return array            event object
	 **/
	function get_event_by_postid( $post_id ) {

		global $wpdb, $calp_events_helper, $calp_view_helper;

		$query = $wpdb->prepare(
			"SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, " .
			"e.recurrence_rules, e.exception_rules, e.recurrence_dates, e.exception_dates, " .
			"e.venue, e.country, e.address, e.city, e.province, e.postal_code, e.show_map, " .
			"e.ical_feed_url, e.ical_source_url, e.ical_organizer, e.ical_contact, e.ical_uid " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
			"AND p.ID = %d ",
			(int) $post_id );

		$events = $wpdb->get_results( $query, ARRAY_A );
		foreach( $events as &$event ) {
			$event = new Calp_Event( $event );
		}
        
        if ( !empty($event)) {
            $_evt = $events['0'];
            $_evt->googleMap = !empty ( $_evt->address ) ? $calp_view_helper->get_frontend_googlemap ( $_evt->address ) : false;
            return $_evt;
        }
        
        return false;
	}

	/**
	 * get_search function
	 *
	 * Return search events of search result
	 *
	 * @param str $search_text      search text
	 *
	 * @return array            event object
	 **/
	function get_search( $search_text, $cat_ids = '' ) {
		global $wpdb, $calp_events_helper, $calp_view_helper;
        
        $search_text = urldecode($search_text);
		$filter = array( 'cat_ids' => explode(',', $cat_ids ) );
		$this->_get_filter_sql(  $filter );

        $search_text = mysql_real_escape_string($search_text);
        $timestamp = $calp_events_helper->gmt_to_local( time() );
        
		$current_query = "SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
			$filter['filter_join'] .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
            "AND UNIX_TIMESTAMP( i.start ) >= $timestamp ".
			"AND ( p.post_title LIKE '%$search_text%' OR p.post_content LIKE '%$search_text%' ) ".
			"AND ( post_status = 'publish' OR post_status = 'private' ) " .
			$filter['filter_where'] . "
            ORDER BY i.start DESC
            LIMIT 5";
        $current_events  = $wpdb->get_results( $current_query, ARRAY_A );
        $exclude = '';
        $exclude_ids = array();
        if ( !empty($current_events) ) {
           foreach ( $current_events as $event ) {
                $exclude_ids[] = $event['instance_id'];
           }
           $exclude = ' AND i.id NOT IN (' .implode(',', $exclude_ids) .')';
        }
        
        $upcoming_query  = "SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
			$filter['filter_join'] .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
            "AND UNIX_TIMESTAMP( i.start ) >= $timestamp ".
            "$exclude".
			"AND ( p.post_title LIKE '%$search_text%' OR p.post_content LIKE '%$search_text%' ) ".
			"AND ( post_status = 'publish' OR post_status = 'private' ) " .
			$filter['filter_where'] .
			"ORDER BY i.start ASC
            LIMIT 100";
        
		$upcoming_events = $wpdb->get_results( $upcoming_query, ARRAY_A );
        $upcoming_count = count($upcoming_events);
        
        // get events from old
        if ( count( $current_events ) < 5 ) {
            foreach ($upcoming_events as $event ) {
                $current_events[] = $event;
                $upcoming_count--;
                if ( count( $current_events ) == 5 )
                    break;
            }
        }
        
        $older_query = "SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
			$filter['filter_join'] .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
            "AND UNIX_TIMESTAMP( i.start ) < $timestamp ".
			"AND ( p.post_title LIKE '%$search_text%' OR p.post_content LIKE '%$search_text%' ) ".
			"AND ( post_status = 'publish' OR post_status = 'private' ) " .
			$filter['filter_where'] .
			"ORDER BY i.start DESC
            LIMIT 100";
            
        $older_events    = $wpdb->get_results( $older_query, ARRAY_A );
        $older_count = count($older_events);
        
        // get events from old
        if ( count( $current_events ) < 5 ) {
            foreach ($older_events as $event ) {
                $current_events[] = $event;
                $older_count--;
                if ( count( $current_events ) == 5 )
                    break;
            }
        }
        
        usort( $current_events, array($this,'sort_by_date'));
        
        foreach( $current_events as &$event ) {
			$event = new Calp_Event( $event );
		}
        
        return array(
            'current_events'    => $current_events,
            'older_events'      => $older_count,
            'upcoming_events'   => $upcoming_count
        );
	}

	/**
	 * sort_by_date function
	 *
	 * Function for sort array of events by event start date
	 * 
	 **/
    function sort_by_date($a, $b)
    {
        $a = $a['start'];
        $b = $b['start'];
        
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

	    /**
	 * get_search function
	 *
	 * Return search events of search result
	 *
	 * @param str $search_text      search text
	 *
	 * @return array            event object
	 **/
	function get_search_items( $search_text, $current_item, $older, $cat_ids = array() ) {
        global $wpdb, $calp_events_helper, $calp_view_helper;
        
        $filter = array( 'cat_ids' => $cat_ids );
        $this->_get_filter_sql( $filter );

        $search_text = mysql_real_escape_string($search_text);
        $timestamp = $calp_events_helper->gmt_to_local( time() );
        
		$older_query = "SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, " .
			"e.recurrence_rules, e.exception_rules, e.recurrence_dates, e.exception_dates, " .
			"e.venue, e.country, e.address, e.city, e.province, e.postal_code, e.show_map " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
			$filter['filter_join'] . 
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
            "AND UNIX_TIMESTAMP( i.start ) <= $timestamp ".
			"AND ( p.post_title LIKE '%$search_text%' OR p.post_content LIKE '%$search_text%' ) " .
           	"AND ( post_status = 'publish' OR post_status = 'private' ) " .
            $filter['filter_where'] .
            "ORDER BY e.start ASC
            LIMIT 25";
		
        $upcoming_query  = "SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, " .
			"e.recurrence_rules, e.exception_rules, e.recurrence_dates, e.exception_dates, " .
			"e.venue, e.country, e.address, e.city, e.province, e.postal_code, e.show_map " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
			$filter['filter_join'] . 
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
            "AND UNIX_TIMESTAMP( i.start ) > $timestamp ".
			"AND ( p.post_title LIKE '%$search_text%' OR p.post_content LIKE '%$search_text%' ) " .
            "AND ( post_status = 'publish' OR post_status = 'private' ) " .
            $filter['filter_where'] .
            "ORDER BY e.start ASC
            LIMIT 25";
		$older_events    = $wpdb->get_results( $older_query, ARRAY_A );
		$upcoming_events = $wpdb->get_results( $upcoming_query, ARRAY_A );
        if ( is_numeric($older) && $older == 1 ) {
            $current_item = isset($older_events['0']['instance_id']) ? $older_events['0']['instance_id'] : NULL;
        } elseif ( is_numeric($older) && ($older == 0) && !$current_item ) {
            $current_item = isset($upcoming_events['0']['instance_id']) ? $upcoming_events['0']['instance_id'] : NULL;
        }
        
        $events = array_merge($older_events, $upcoming_events);
        
        $days = array();
        $day = 0;
        foreach( $events as &$event ) {
			$event = new Calp_Event( $event );
            $event->googleMap = !empty ( $event->address ) ? $calp_view_helper->get_frontend_googlemap ( $event->address ) : false;
            $event->current = ( $event->instance_id == $current_item ) ? true : false;
            if ( $event->current ) {
            	$current = $event->instance_id;
            }
            
            if ( isset($timestamp) && (date('Y-m-d', $event->start) != date('Y-m-d', $timestamp)) ) {
               $day++; 
            }
            
            $timestamp = $event->start;
            
            $days[$day]['timestamp'] = $event->start;
            $days[$day]['events'][] = $event;
        }

        // If wasn't found current item
        if ( !isset( $current ) && isset($current_item) ) {
           $query = $wpdb->prepare(
			"SELECT DISTINCT p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, " .
			"e.recurrence_rules, e.exception_rules, e.recurrence_dates, e.exception_dates, " .
			"e.venue, e.country, e.address, e.city, e.province, e.postal_code, e.show_map, " .
			"e.ical_feed_url, e.ical_source_url, e.ical_organizer, e.ical_contact, e.ical_uid " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON p.ID = e.post_id " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
			"AND ( post_status = 'publish' OR post_status = 'private' ) " .
			"AND i.id = %d ",
			(int) $current_item );
			$event = $wpdb->get_results( $query, ARRAY_A );
			
			if ( $event ) $upcoming_events[] = $event['0'];
        }

        $events = array_merge($older_events, $upcoming_events);

        $days = array();
        $day = 0;
        foreach( $events as &$event ) {
			$event = new Calp_Event( $event );
            $event->googleMap = !empty ( $event->address ) ? $calp_view_helper->get_frontend_googlemap ( $event->address ) : false;
            $event->current = ( $event->instance_id == $current_item ) ? true : false;
            if ( $event->current ) {
            	$current = $event->instance_id;
            }
            
            if ( isset($timestamp) && (date('Y-m-d', $event->start) != date('Y-m-d', $timestamp)) ) {
               $day++; 
            }
            
            $timestamp = $event->start;
            
            $days[$day]['timestamp'] = $event->start;
            $days[$day]['events'][] = $event;
        }
        
        return array('weeks' => $days, 'current' => $current );
    }

	/**
	 * get_events_relative_to function
	 *
	 * Return all events starting after the given reference time, limiting the
	 * result set to a maximum of $limit items, offset by $page_offset. A
	 * negative $page_offset can be provided, which will return events *before*
	 * the reference time, as expected.
	 *
	 * @param int $time	          limit to events starting after this (local) UNIX time
	 * @param int $limit          return a maximum of this number of items
	 * @param int $page_offset    offset the result set by $limit times this number
	 * @param array $filter       Array of filters for the events returned.
	 *		                        ['cat_ids']   => non-associatative array of category IDs
	 *		                        ['tag_ids']   => non-associatative array of tag IDs
	 *                            ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array              three-element array:
	 *                              ['events'] an array of matching event objects
	 *															['prev'] true if more previous events
	 *															['next'] true if more next events
	 **/
	function get_events_relative_to( $time, $limit = 0, $page_offset = 0, $filter = array() ) {

		global $wpdb, $calp_events_helper;

		// Figure out what the beginning of the day is to properly query all-day
		// events; then convert to GMT time
		$bits = $calp_events_helper->gmgetdate( $time );

		// Convert timestamp to GMT time
		$time = $calp_events_helper->local_to_gmt( $time );

		// Query arguments
		$args = array( $time );

		if( $page_offset >= 0 )
			$first_record = $page_offset * $limit;
		else
			$first_record = ( -$page_offset - 1 ) * $limit;

		// Get post status Where snippet and associated SQL arguments
		$this->_get_post_status_sql( $post_status_where, $args );

		// Get the Join (filter_join) and Where (filter_where) statements based on
		// $filter elements specified
		$this->_get_filter_sql( $filter );

		$query = $wpdb->prepare(
			"SELECT DISTINCT SQL_CALC_FOUND_ROWS p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday, " .
			"e.recurrence_rules, e.exception_rules, e.recurrence_dates, e.exception_dates, " .
			"e.venue, e.country, e.address, e.city, e.province, e.postal_code, e.show_map, " .
			"e.ical_feed_url, e.ical_source_url, e.ical_organizer, e.ical_contact, e.ical_uid " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON e.post_id = p.ID " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
				$filter['filter_join'] .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
			"AND " .
				( $page_offset >= 0 ? "i.end >= FROM_UNIXTIME( %d ) "
					: "i.start < FROM_UNIXTIME( %d ) "
				) .
			$filter['filter_where'] .
			$post_status_where .
			// Reverse order when viewing negative pages, to get correct set of
			// records. Then reverse results later to order them properly.
			"ORDER BY i.start " . ( $page_offset >= 0 ? 'ASC' : 'DESC' ) .
				", post_title " . ( $page_offset >= 0 ? 'ASC' : 'DESC' ) .
			" LIMIT $first_record, $limit",
			$args );

		$events = $wpdb->get_results( $query, ARRAY_A );

		// Reorder records if in negative page offset
		if( $page_offset < 0 ) $events = array_reverse( $events );

		foreach( $events as &$event ) {
			$event = new Calp_Event( $event );
		}

		// Find out if there are more records in the current nav direction
		$more = $wpdb->get_var( 'SELECT FOUND_ROWS()' ) > $first_record + $limit;

		// Navigating in the future
		if( $page_offset > 0 ) {
			$prev = true;
			$next = $more;
		}
		// Navigating in the past
		elseif( $page_offset < 0 ) {
			$prev = $more;
			$next = true;
		}
		// Navigating from the reference time
		else {
			$query = $wpdb->prepare(
				"SELECT COUNT(*) " .
				"FROM {$wpdb->prefix}calp_events e " .
					"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
					"INNER JOIN $wpdb->posts p ON e.post_id = p.ID " .
					$filter['filter_join'] .
				"WHERE post_type = '" . CALP_POST_TYPE . "' " .
				"AND i.start < FROM_UNIXTIME( %d ) " .
				$filter['filter_where'] .
				$post_status_where,
				$args );
			$prev = $wpdb->get_var( $query );
			$next = $more;
		}
		return array(
			'events' => $events,
			'prev' => $prev,
			'next' => $next,
		);
	}

	/**
	 * get_agenda_date_array function
	 *
	 * Breaks down the given ordered array of event objects into dates, and
	 * outputs an ordered array of two-element associative arrays in the
	 * following format:
	 *	key: localized UNIX timestamp of date
	 *	value:
	 *		['events'] => two-element associatative array broken down thus:
	 *			['allday'] => all-day events occurring on this day
	 *			['notallday'] => all other events occurring on this day
	 *		['today'] => whether or not this date is today
	 *
	 * @param array $events
	 * @param int $day_start
	 * @param int $limit
	 *
	 * @return array
	 **/
	function get_agenda_date_array( $events, $day_start, $limit ) {
		global $calp_events_helper;

		$bits = $calp_events_helper->gmgetdate( $day_start );

		$day_end = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + 1, $bits['year'] );

		$dates = array();

		$count = 0;
		foreach( $events as $evt ) {
			$evt_start = $calp_events_helper->gmt_to_local( $evt->start );
			$evt_end = $calp_events_helper->gmt_to_local( $evt->end );
		
			$category = $evt->allday ? 'allday' : 'notallday';

			if( $evt_start >= $day_start && $evt_start < $day_end ) {
				$dates[$day_start]['events'][$category][] = $evt;
				$count++;
			// If event falls on this day, make a copy.
			} elseif( $evt_end > $day_start && $evt_start < $day_end ) {
				$dates[$day_start]['events'][$category][] = $evt;
				$count++;
			}

			// Finish on limit
			if ( $count >= $limit ) {
			    break;
			}
		}

		// Flag today
		$today = $calp_events_helper->gmt_to_local( time() );
		$today = $calp_events_helper->gmgetdate( $today );
		$today = gmmktime( 0, 0, 0, $today['mon'], $today['mday'], $today['year'] );
		if( isset( $dates[$today] ) )
			$dates[$today]['today'] = true;

		return $dates;
	}

	/**
	 * get_calendar_url function
	 *
	 * Returns the URL of the configured calendar page in the default view,
	 * optionally preloaded at the month containing the given event (rather than
	 * today's date), and optionally prefiltered by the given filters.
	 *
	 * @param object|null $event  The event to focus the calendar on
	 * @param array       $filter Array of filters for the events returned.
	 *		['cat_ids']   => non-associatative array of category IDs
	 *		['tag_ids']   => non-associatative array of tag IDs
	 *		['post_ids']  => non-associatative array of post IDs
	 *
	 * @return string The URL for this calendar
	 **/
	function get_calendar_url( $event = null, $filter = array() ) {
		global $calp_settings, $calp_events_helper, $calp_app_helper, $wpdb;

		$url = get_permalink( $calp_settings->calendar_page_id );

		if( $event )
		{
			$url .= $calp_app_helper->get_param_delimiter_char( $url );

			switch( $calp_settings->default_calendar_view )
			{
				case 'month':
					// Get components of localized timstamps and calculate month offset
					$today = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
					$desired = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( $event->start ) );
					$month_offset =
						( $desired['year'] - $today['year'] ) * 12 +
						$desired['mon'] - $today['mon'];

					$url .= "calp_month_offset=$month_offset";
					break;

				case 'week':
					// Get components of localized timstamps and calculate week offset
					/* TODO - code this; first need to find out first day of week based on week start day,
						 then calculate how many weeks off we are from that one
					$today = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
					$desired = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( $event->start ) );
					$week_offset =
						( $desired['year'] - $today['year'] ) * 12 +
						$desired['mon'] - $today['mon'];

					$url .= "calp_week_offset=$week_offset";*/
					break;

				case 'agenda':
					// Find out how many event instances are between today's first
					// instance and the desired event's instance
					$now = $calp_events_helper->local_to_gmt( time() );
					$after_today = $event->end >= $now;
					$query = $wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->prefix}calp_events e " .
							"INNER JOIN $wpdb->posts p ON e.post_id = p.ID " .
							"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
						"WHERE post_type = '" . CALP_POST_TYPE . "' " .
						"AND post_status = 'publish' " .
						( $after_today
							? "AND i.end >= FROM_UNIXTIME( %d ) AND i.end < FROM_UNIXTIME( %d ) "
							: "AND i.start < FROM_UNIXTIME( %d ) AND i.start >= FROM_UNIXTIME( %d ) "
						) .
						"ORDER BY i.start ASC",
						array( $now, $after_today ? $event->end : $event->start ) );
					$count = $wpdb->get_var( $query );
					// ( $count - 1 ) below solves boundary case for first event of each agenda page
					$page_offset = intval( ( $count - 1 ) / $calp_settings->agenda_events_per_page );
					if( ! $after_today ) $page_offset = -1 - $page_offset;

					$url .= "calp_page_offset=$page_offset";
					break;
			}

			$url .= "&calp_active_event=$event->post_id";
		}

		// Add filter parameters
		foreach( $filter as $key => $val ) {
			if( $val ) {
				$url .= $calp_app_helper->get_param_delimiter_char( $url ) .
					"calp_$key=" . join( ',', $val );
			}
		}

		return $url;
	}

	/**
	 * get_weekdays function
	 *
	 * Returns a list of abbreviated weekday names starting on the configured
	 * week start day setting.
	 *
	 * @param boolean $short_names  Short names of weekdays
	 *
	 * @return array
	 **/
	function get_weekdays( $short_names = false ) {
		global $calp_settings;
		static $weekdays;

		if( ! isset( $weekdays ) )
		{
			$time = strtotime( 'next Sunday' );
			$time = strtotime( "+{$calp_settings->week_start_day} days", $time );

			$weekdays = array();
			for( $i = 0; $i < 7; $i++ ) {
				$weekdays[] = date_i18n( $short_names?'D':'l', $time );
				$time += 60 * 60 * 24; // Add a day
			}
		}
		return $weekdays;
	}
    
	/**
	 * get_today_pagination_links function
	 *
	 * Returns a non-associative array of two links for the today view of the
	 * calendar:
	 * previous month, next week, and days list.
	 * Each element's key is an associative array containing the link's ID
	 * ['id'], text ['text'] and value to assign to link's href ['href'].
	 *
	 * @param int $cur_offset today offset of days, needed for hrefs
	 *
	 * @return array          array of link information as described above
	 **/
	function get_today_pagination_links( $cur_offset ) {
		global $calp_events_helper;

		$links = array();

		// Base timestamp on offset month
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
        
        $bits['mday'] += $cur_offset;
        
        $dateSub = date_i18n( 'j', gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'], $bits['year'] ));
        $dateAdd = date_i18n( 't', gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'], $bits['year'] )) - $dateSub;
        
        $links['previous'][] = array(
            'id' => '',
            'class' => '',
            'text' => date_i18n( 'M', gmmktime( 0, 0, 0, $bits['mon'] , $bits['mday'] - $dateSub, $bits['year'] ), true ),
            'href' => '#action=calp_today&calp_today_offset=' . ( $cur_offset - $dateSub ),
        );
        
        $links['previous'][] = array(
            'id' => '',
            'class' => '',
            'text' => date_i18n( 'M', gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'], $bits['year'] ), true ),
            'href' => '#action=calp_today&calp_month_offset=' . ( $cur_offset ),
        );

        $links['previous'][] = array(
            'id' => '',
            'class' => '',
            'text'  => date_i18n( 'j', gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] - 1, $bits['year'] )),
            'href' => '#action=calp_today&calp_today_offset=' . ( $cur_offset - 1 ),
        );
        
        for ($i = 0; $i < 18; $i++) {
            $links['middle'][] = array(
                'id'    => 'calp-week-link-'.($i+1),
                'class' => ($i == 0) ? 'calp-nav-current' : '',
                'text'  => date_i18n( 'j', gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $i, $bits['year'] )),
                'href' => '#action=calp_today&calp_today_offset=' . ( $cur_offset + $i ),
            );
        }
        
        $links['next'][] = array(
            'id' => 'calp-next-month',
            'class' => ($i == 0) ? 'calp-nav-current' : '',
            'text' => date_i18n( 'M', gmmktime( 0, 0, 0, $bits['mon'] + 1, $bits['mday'] + $dateAdd - 1, $bits['year'] ), true ),
            'href' => '#action=calp_today&calp_today_offset=' . ( $cur_offset + $dateAdd  + 1),
        );
        
		return $links;
	}
    
	/**
	 * get_week_pagination_links function
	 *
	 * Returns a non-associative array of two links for the week view of the
	 * calendar:
	 * list of weeks.
	 * Each element's key is an associative array containing the link's ID
	 * ['id'], text ['text'] and value to assign to link's href ['href'].
	 *
	 * @param int $cur_offset week offset of current week, needed for hrefs
	 *
	 * @return array          array of link information as described above
	 **/
	function get_week_pagination_links( $cur_offset ) {
		global $calp_events_helper;

		$links = array();

		// Base timestamp on offset week
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
		$bits['mday'] += $calp_events_helper->get_week_start_day_offset( $bits['wday'] );
		$bits['mday'] += $cur_offset * 7;
		/* translators: "%s" represents the week's starting date */
        for ($i = -2; $i < 7; $i++) {
            $links[] = array(
                'id'    => 'calp-week-link-'.($i+1),
                'class' => ($i == 0) ? 'calp-nav-current' : '',
                'text'  =>
                    sprintf(
                        __( '%s - %s', CALP_PLUGIN_NAME ),
                        date_i18n( __( 'M j', CALP_PLUGIN_NAME ), gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $i*7, $bits['year'] )),
                        date_i18n( __( 'M j', CALP_PLUGIN_NAME ), gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $i * 7 + 6 , $bits['year'] )) , true
                    ),
                'href' => '#action=calp_week&calp_week_offset=' . ( $cur_offset + $i ),
            );
        }
        
		return $links;
	}

	/**
	 * get_month_pagination_links function
	 *
	 * Returns a non-associative array of four links for the month view of the
	 * calendar:
	 * previous year, previous year, next year and list of days.
	 * Each element's key is an associative array containing the link's ID
	 * ['id'], text ['text'] and value to assign to link's href ['href'].
	 *
	 * @param int $cur_offset month offset of current month, needed for hrefs
	 *
	 * @return array          array of link information as described above
	 **/
	function get_month_pagination_links( $cur_offset ) {
		global $calp_events_helper;

		$links = array();

		// Base timestamp on offset month
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
		$bits['mon'] += $cur_offset;
		// 'mon' may now be out of range (< 1 or > 12), so recreate $bits to make sane
		$bits = $calp_events_helper->gmgetdate( gmmktime( 0, 0, 0, $bits['mon'], 1, $bits['year'] ) );
        
		$links['previous'][] = array(
			'id'    => 'calp-prev-year',
            'class' => '',
			'text'  => ( $bits['year'] - 1 ),
			'href'  => '#action=calp_month&calp_month_offset=' . ( $cur_offset - 12 ),
		);
        
		$links['previous'][] = array(
			'id' => 'calp-current-year',
            'class' => '',
			'text' => ( $bits['year'] ),
			'href' => '',
		);
		$links['previous'][] = array(
			'id' => 'calp-prev-month',
            'class' => '',
			'text' => date_i18n( 'M', gmmktime( 0, 0, 0, $bits['mon'] - 1, 1, $bits['year'] ), true ),
			'href' => '#action=calp_month&calp_month_offset=' . ( $cur_offset - 1 ),
		);
        
        for ($i = -3; $i < 9; $i++) {
            $links['middle'][] = array(
                'id' => 'calp-next-month',
                'class' => ($i == 0) ? 'calp-nav-current' : '',
                'text' => date_i18n( 'M', gmmktime( 0, 0, 0, $bits['mon'] + $i, 1, $bits['year'] ), true ),
                'href' => '#action=calp_month&calp_month_offset=' . ( $cur_offset + $i ),
            );
        }
        
		$links['next'][] = array(
			'id' => 'calp-next-year',
            'class' => '',
			'text' => ( $bits['year'] + 1 ),
			'href' => '#action=calp_month&calp_month_offset=' . ( $cur_offset + 12 ),
		);
        
		return $links;
	}

	/**
	 * get_agenda_pagination_links function
	 *
	 * Returns an associative array of two links for the agenda view of the
	 * calendar: previous page (if previous events exist), next page (if next
	 * events exist), in that order.
	 * Each element' is an associative array containing the link ID ['id'],
	 * text ['text'] and value to assign to link's href ['href'].
	 *
	 * @param int $cur_offset page offset of agenda view, needed for hrefs
	 * @param int $prev       whether there are more events before the current page
	 * @param int $next       whether there are more events after the current page
	 *
	 * @return array          array of link information as described above
	 **/
	function get_agenda_pagination_links( $cur_offset ) {
		global $calp_events_helper;

		$links = array();
        
		// Base timestamp on offset month
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
		$bits['mon'] += $cur_offset;
		// 'mon' may now be out of range (< 1 or > 12), so recreate $bits to make sane
		$bits = $calp_events_helper->gmgetdate( gmmktime( 0, 0, 0, $bits['mon'], 1, $bits['year'] ) );
        
		$links['previous'][] = array(
			'id'    => 'calp-prev-year',
            'class' => '',
			'text'  => ( $bits['year'] - 1 ),
			'href'  => '#action=calp_agenda&calp_agenda_offset=' . ( $cur_offset - 12 ),
		);
        
		$links['previous'][] = array(
			'id' => 'calp-current-year',
            'class' => '',
			'text' => ( $bits['year'] ),
			'href' => '',
		);
        
        for ($i = -3; $i < 9; $i++) {
            $links['middle'][] = array(
                'id' => 'calp-next-month',
                'class' => ($i == 0) ? 'calp-nav-current' : '',
                'text' => date_i18n( 'M', gmmktime( 0, 0, 0, $bits['mon'] + $i, 1, $bits['year'] ), true ),
                'href' => '#action=calp_agenda&calp_agenda_offset=' . ( $cur_offset + $i ),
            );
        }
        
		$links['next'][] = array(
			'id' => 'calp-next-year',
            'class' => '',
			'text' => ( $bits['year'] + 1 ),
			'href' => '#action=calp_agenda&calp_agenda_offset=' . ( $cur_offset + 12 ),
		);
        
		return $links;
	}

	/**
	 * _get_post_status_sql function
	 *
	 * Returns SQL snippet for properly matching event posts, as well as array
	 * of arguments to pass to $wpdb->prepare, in function argument references.
	 * Nothing is returned by the function.
	 *
	 * @param string &$sql  The variable to store the SQL snippet into
	 * @param array  &$args The variable to store the SQL arguments into
	 *
	 * @return void
	 */
	function _get_post_status_sql( &$post_status_where = '', &$args )
	{
		global $current_user;

		// Query the correct post status
		if( current_user_can( 'administrator' ) || current_user_can( 'editor' ) )
		{
			// User has privilege of seeing all published and private posts

			$post_status_where = "AND ( post_status = %s OR post_status = %s ) ";
			$args[]            = 'publish';
			$args[]            = 'private';
		}
		elseif( is_user_logged_in() )
		{
			// User has privilege of seeing all published and only their own private
			// posts.

			// get user info
			get_currentuserinfo();

			// include post_status = published
			//   OR
			// post_status = private AND author = logged-in user
			$post_status_where =
				"AND ( " .
					"post_status = %s " .
					"OR ( post_status = %s AND post_author = %d ) " .
				") ";

			$args[] = 'publish';
			$args[] = 'private';
			$args[] = $current_user->ID;
		} else {
			// User can only see published posts.
			$post_status_where = "AND post_status = %s ";
			$args[]            = 'publish';
		}
	}

	/**
	 * _get_filter_sql function
	 *
	 * Takes an array of filtering options and turns it into JOIN and WHERE statements
	 * for running an SQL query limited to the specified options
	 *
	 * @param array &$filter      Array of filters for the events returned.
	 *		                        ['cat_ids']   => non-associatative array of category IDs
	 *		                        ['tag_ids']   => non-associatative array of tag IDs
	 *		                        ['post_ids']  => non-associatative array of event post IDs
	 *														This array is modified to have:
	 *                              ['filter_join']  the Join statements for the SQL
	 *                              ['filter_where'] the Where statements for the SQL
	 *
	 * @return void
	 */
	function _get_filter_sql( &$filter,  $widget = false ) {
		global $wpdb;

		// Set up the filter join and where strings
		$filter['filter_join']  = '';
		$filter['filter_where'] = '';

		// By default open the Where with an AND ( .. ) to group all statements.
		// Later, set it to OR to join statements together.
		// TODO - make this cleaner by supporting the choice of AND/OR logic
		$where_logic = ' AND (';
		foreach( $filter as $filter_type => $filter_ids ) {
			// If no filter elements specified, don't do anything
			if( $filter_ids && is_array( $filter_ids ) && !empty( $filter_ids ) ) {
				switch ( $filter_type ) {
					// Limit by Category IDs
					case 'cat_ids':
						$filter['filter_join']   .= " INNER JOIN $wpdb->term_relationships AS trc ON e.post_id = trc.object_id ";
						$filter['filter_join']   .= " INNER JOIN $wpdb->term_taxonomy ttc ON trc.term_taxonomy_id = ttc.term_taxonomy_id AND ttc.taxonomy = 'events_categories' ";
						$filter['filter_where']  .= $where_logic . " ttc.term_id IN ( " . join( ',', $filter_ids ) . " ) ";
						$where_logic = ' OR ';
						break;
					// Limit by Tag IDs
					case 'tag_ids':
						$filter['filter_join']   .= " LEFT JOIN $wpdb->term_relationships AS trt ON e.post_id = trt.object_id ";
						$filter['filter_join']   .= " LEFT JOIN $wpdb->term_taxonomy ttt ON trt.term_taxonomy_id = ttt.term_taxonomy_id AND ttt.taxonomy = 'events_tags' ";
						$filter['filter_where']  .= $where_logic . " ttt.term_id IN ( " . join( ',', $filter_ids ) . " ) ";
						$where_logic = ' AND ';
						break;
					// Limit by post IDs
					case 'post_ids':
						$filter['filter_where']  .= $where_logic . " e.post_id IN ( " . join( ',', $filter_ids ) . " ) ";
						$where_logic = ' OR ';
						break;
				}
			}
		}
		if ( !$widget ) {
	        // Exclude all categories if no category selected
	        if ( empty( $filter['cat_ids'] ) ) {
	            $filter_ids = $this->get_categories_array();
	            if ( !empty( $filter_ids ) ) {
	                $filter['filter_join']   .= " INNER JOIN $wpdb->term_relationships AS trc ON e.post_id = trc.object_id ";
	                $filter['filter_join']   .= " INNER JOIN $wpdb->term_taxonomy ttc ON trc.term_taxonomy_id = ttc.term_taxonomy_id AND ttc.taxonomy = 'events_categories' ";
	                $filter['filter_where']  .= $where_logic . " ttc.term_id NOT IN ( " . join( ',', $filter_ids ) . " ) ";
	                $where_logic = ' OR ';
	            }
	        }
	    }
    
		// Close the Where statement bracket if any Where statements were set
		if( $filter['filter_where'] != '' ) {
			$filter['filter_where'] .= ' ) ';
		}
	}
    
    /**
	 * get_categories_array function
	 *
	 * Returns array of categories ID.
	 *
	 * @return array         An array of categries ID.
	 */
    
    function get_categories_array()
    {
        $categories = get_terms( 'events_categories', array( 'orderby' => 'name' ) );
        $categories_array = array();
        foreach ( $categories as $category ) {
            $categories_array[] = $category->term_id;
        }
        
        return $categories_array;
    }

    /**
	 * get_widget_events function
	 *
	 * Return all events for specific day for widget
	 *
	 * @param int $time	          limit to events starting after this (local) UNIX time
	 * @param int $limit          return a maximum of this number of items
	 * @param int $page_offset    offset the result set by $limit times this number
	 * @param array $filter       Array of filters for the events returned.
	 *		                        ['cat_ids']   => non-associatative array of category IDs
	 *		                        ['tag_ids']   => non-associatative array of tag IDs
	 *                            ['post_ids']  => non-associatative array of post IDs
	 * @param bool navigation
	 * @return array              three-element array:
	 *                              ['events'] an array of matching event objects
	 **/
    function get_widget_events( $start_time, $limit = 0, $filter = array(), $navigation = false )
    {
		global $calp_events_helper;

		if ( !$navigation ) {
			$allday_tz = get_option( 'timezone_string', 'America/Los_Angeles' );
			$offset = $calp_events_helper->get_timezone_offset( 'UTC', $allday_tz, false );
		}
		$bits = $calp_events_helper->gmgetdate( $start_time );

		$end_time = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + 1, $bits['year'] );

		$events = $this->get_events_between( $start_time, $end_time, $filter, true );

		return $events;
	}

	/**
	 * get_widget_upcoming_events function
	 *
	 * Return all events for specific day for widget
	 *
	 * @param int $time	          limit to events starting after this (local) UNIX time
	 * @param int $limit          return a maximum of this number of items
	 * @param int $page_offset    offset the result set by $limit times this number
	 * @param array $filter       Array of filters for the events returned.
	 *		                        ['cat_ids']   => non-associatative array of category IDs
	 *		                        ['tag_ids']   => non-associatative array of tag IDs
	 *                            ['post_ids']  => non-associatative array of post IDs
	 *
	 * @return array              three-element array:
	 *                              ['events'] an array of matching event objects
	 **/
    function get_widget_upcoming_events( $time, $limit = 0, $page_offset = 0, $filter = array() ) {

		global $wpdb, $calp_events_helper;

		// Query arguments
		$args = array();

		// Get post status Where snippet and associated SQL arguments
		$this->_get_post_status_sql( $post_status_where, $args );

		// Get the Join (filter_join) and Where (filter_where) statements based on
		// $filter elements specified
		$this->_get_filter_sql( $filter, true );

		$query = $wpdb->prepare(
			"SELECT DISTINCT SQL_CALC_FOUND_ROWS p.*, e.post_id, i.id AS instance_id, " .
			"UNIX_TIMESTAMP( i.start ) AS start, " .
			"UNIX_TIMESTAMP( i.end ) AS end, " .
			// Treat event instances that span 24 hours as all-day
			"IF( e.allday, e.allday, i.end = DATE_ADD( i.start, INTERVAL 1 DAY ) ) AS allday " .
			"FROM {$wpdb->prefix}calp_events e " .
				"INNER JOIN $wpdb->posts p ON e.post_id = p.ID " .
				"INNER JOIN {$wpdb->prefix}calp_event_instances i ON e.post_id = i.post_id " .
				$filter['filter_join'] .
			"WHERE post_type = '" . CALP_POST_TYPE . "' AND post_title != '' " .
			"AND i.start >= FROM_UNIXTIME( $time ) " .
			$filter['filter_where'] .
			$post_status_where .
			// Reverse order when viewing negative pages, to get correct set of
			// records. Then reverse results later to order them properly.
			"ORDER BY i.start ASC , post_title ASC" .
			" LIMIT $limit", $args );
		$events = $wpdb->get_results( $query, ARRAY_A );

		foreach( $events as &$event ) {
			$event = new Calp_Event( $event );
		}

		$dates = array();

		$count = 0;
		foreach( $events as $evt ) {
			$evt_start = $calp_events_helper->gmt_to_local( $evt->start );
			
			$evt_end = $calp_events_helper->gmt_to_local( $evt->end );

			$category = $evt->allday ? 'allday' : 'notallday';
			
			$bits = $calp_events_helper->gmgetdate( $evt_start );
			$start = $evt_start;
			
			$day = 0;
			while ( $start < $evt_end ) {
				$start = strtotime("+$day day", $start);
			   	$start_index = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $day, $bits['year'] );
				
				if ( $start < $evt_end ) {
					$dates[$start_index]['events'][$category][] = $evt;
					$count++;
				}

				// Finish on limit
				if ( $count >= $limit ) {
				    break 2;
				}


			   	$day++; 
			}

		}

		return $dates;
	}

	
    /**
	 * get_widget_pagination_links function
	 *
	 * Returns a non-associative array of two links for the today view of the
	 * calendar:
	 * previous month, next week, and days list.
	 *
	 * @param int $cur_offset today offset of days, needed for hrefs
	 *
	 * @return array          array of link information as described above
	 **/
	function get_widget_pagination_links( $cur_offset ) {
		global $calp_events_helper;

		$links = array();

		// Base timestamp on offset month
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
        
        $bits['mday'] += $cur_offset;
       
        $offset_date = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'], $bits['year'] );
        $offset_bits = $calp_events_helper->gmgetdate( $offset_date);
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $offset_bits['mon'], $offset_bits['year'] ); 
        
        $dateSub = $offset_bits['mday'];
        $dateAdd = $days_in_month - $dateSub;

        $links['previous'] = array(
            'text' => date_i18n( 'M', gmmktime( 0, 0, 0, $offset_bits['mon'] - 1, 1, $offset_bits['year'] ), true ),
            'offset' => $cur_offset - $dateSub,
        );
        
        $links['next'] = array(
            'text' => date_i18n( 'M', gmmktime( 0, 0, 0, $offset_bits['mon'] + 1, 1, $offset_bits['year'] ), true ),
            'offset' => $cur_offset + $dateAdd  + 1,
        );
        
		return $links;
	}

	/**
	*
	* time_diff function
	*
	* Get difference between timestamps broken down into years/months/weeks/etc.
	* @return array
	* @param int $date1 UNIX timestamp
	* @param int $date2 UNIX timestamp
	*/
	function time_diff_in_month($date1, $date2) {
		$invert =  (($date2 - $date1) > 0)? 0 : 1 ;
		if ( $invert == 1 ) {
		    list($date2, $date1) = array($date1, $date2);
		}		
		
		$current = gmdate('Y-m-1', $date1);
		$datetime2 = gmdate('Y-m-1', $date2);
		$month = 0;
		while ( $current < $datetime2 ) {
			$current = gmdate("Y-m-1", strtotime("+1 month", strtotime($current)));
		   	$month++; 
		}

		if (  $invert == 1 ) {
		    $month = $month*(-1);
		}

		return $month;
	}
}
// END class
