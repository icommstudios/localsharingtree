<?php
//
//  class-calp-calendar-controller.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//

/**
 * Calp_Calendar_Controller class
 *
 * @package Controllers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_Calendar_Controller {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	static $_instance = NULL;

	/**
	 * request class variable
	 *
	 * Stores a custom $_REQUEST array for all calendar requests
	 *
	 * @var array
	 **/
	private $request = array();

	/**
	 * __construct function
	 *
	 * Default constructor - calendar initialization
	 **/
	private function __construct() {
		// ===========
		// = ACTIONS =
		// ===========
		// Handle AJAX requests
		// Strange! Now regular WordPress requests will respond to the below AJAX
		// hooks! Thus we need to check to make sure we are being called by the
		// AJAX script before returning AJAX responses.
		if( basename( $_SERVER['SCRIPT_NAME'] ) == 'admin-ajax.php' )
		{
			add_action( 'wp_ajax_calp_today', array( &$this, 'ajax_today' ) );
			add_action( 'wp_ajax_calp_week', array( &$this, 'ajax_week' ) );
            add_action( 'wp_ajax_calp_month', array( &$this, 'ajax_month' ) );
			add_action( 'wp_ajax_calp_agenda', array( &$this, 'ajax_agenda' ) );
			add_action( 'wp_ajax_calp_agenda_item', array( &$this, 'ajax_agenda_item' ) );
			add_action( 'wp_ajax_calp_term_filter', array( &$this, 'ajax_term_filter' ) );
            add_action( 'wp_ajax_calp_popup', array( &$this, 'ajax_popup' ) );
            add_action( 'wp_ajax_calp_search', array( &$this, 'ajax_search' ) );

			add_action( 'wp_ajax_nopriv_calp_today', array( &$this, 'ajax_today' ) );
			add_action( 'wp_ajax_nopriv_calp_week', array( &$this, 'ajax_week' ) );
			add_action( 'wp_ajax_nopriv_calp_month', array( &$this, 'ajax_month' ) );
            add_action( 'wp_ajax_nopriv_calp_agenda', array( &$this, 'ajax_agenda' ) );
            add_action( 'wp_ajax_nopriv_calp_agenda_item', array( &$this, 'ajax_agenda_item' ) );
			add_action( 'wp_ajax_nopriv_calp_term_filter', array( &$this, 'ajax_term_filter' ) );
			add_action( 'wp_ajax_nopriv_calp_popup', array( &$this, 'ajax_popup' ) );
			add_action( 'wp_ajax_nopriv_calp_search', array( &$this, 'ajax_search' ) );
		}
	}

	/**
	 * process_request function
	 *
	 * Initialize/validate custom request array, based on contents of $_REQUEST,
	 * to keep track of this component's request variables.
	 *
	 * @return void
	 **/
	function process_request()
	{
		global $calp_settings;

		// Find out which view of the calendar page was requested, then validate
		// request parameters accordingly and save them to our custom request
		// object
		$this->request['action'] = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
		if( ! in_array( $this->request['action'],
			      array( 'calp_today', 'calp_week', 'calp_month', 'calp_agenda', 'calp_agenda_item', 'calp_term_filter', 'calp_popup', 'calp_search' ) ) )
			$this->request['action'] = 'calp_' . $calp_settings->default_calendar_view;
        
		switch( $this->request['action'] )
		{
            case 'calp_today':
				$this->request['calp_today_offset'] =
					isset( $_REQUEST['calp_today_offset'] ) ? intval( $_REQUEST['calp_today_offset'] ) : NULL;
				// Parse active event parameter as an integer ID
				$this->request['calp_active_event'] = isset( $_REQUEST['calp_active_event'] ) ? intval( $_REQUEST['calp_active_event'] ) : null;
				// Category/tag filter parameters
				$this->request['calp_cat_ids'] = isset( $_REQUEST['calp_cat_ids'] ) ? $_REQUEST['calp_cat_ids'] : null;
				$this->request['calp_tag_ids'] = isset( $_REQUEST['calp_tag_ids'] ) ? $_REQUEST['calp_tag_ids'] : null;
                $this->request['calp_today']    = isset( $_REQUEST['calp_today'] )  ? $_REQUEST['calp_today'] : null;
				break;
            
            case 'calp_week':
				$this->request['calp_week_offset'] =
					isset( $_REQUEST['calp_week_offset'] ) ? intval( $_REQUEST['calp_week_offset'] ) : 0;
				// Parse active event parameter as an integer ID
				$this->request['calp_active_event'] = isset( $_REQUEST['calp_active_event'] ) ? intval( $_REQUEST['calp_active_event'] ) : null;
				// Category/tag filter parameters
				$this->request['calp_cat_ids'] = isset( $_REQUEST['calp_cat_ids'] ) ? $_REQUEST['calp_cat_ids'] : null;
				$this->request['calp_tag_ids'] = isset( $_REQUEST['calp_tag_ids'] ) ? $_REQUEST['calp_tag_ids'] : null;
				break;
            
			case 'calp_month':
				$this->request['calp_month_offset'] =
					isset( $_REQUEST['calp_month_offset'] ) ? intval( $_REQUEST['calp_month_offset'] ) : 0;
				// Parse active event parameter as an integer ID
				$this->request['calp_active_event'] = isset( $_REQUEST['calp_active_event'] ) ? intval( $_REQUEST['calp_active_event'] ) : null;
				// Category/tag filter parameters
				$this->request['calp_cat_ids'] = isset( $_REQUEST['calp_cat_ids'] ) ? $_REQUEST['calp_cat_ids'] : null;
				$this->request['calp_tag_ids'] = isset( $_REQUEST['calp_tag_ids'] ) ? $_REQUEST['calp_tag_ids'] : null;
                
                break;
                
			case 'calp_agenda':
				$this->request['calp_agenda_offset'] =
					isset( $_REQUEST['calp_agenda_offset'] ) ? intval( $_REQUEST['calp_agenda_offset'] ) : NULL;
				// Parse active event parameter as an integer ID
				$this->request['calp_active_event'] = isset( $_REQUEST['calp_active_event'] ) ? intval( $_REQUEST['calp_active_event'] ) : null;
				// Category/tag filter parameters
				$this->request['calp_cat_ids'] = isset( $_REQUEST['calp_cat_ids'] ) ? $_REQUEST['calp_cat_ids'] : null;
				$this->request['calp_tag_ids'] = isset( $_REQUEST['calp_tag_ids'] ) ? $_REQUEST['calp_tag_ids'] : null;
				$this->request['calp_item_id'] = isset( $_REQUEST['calp_item_id'] ) ? $_REQUEST['calp_item_id'] : null;
				$this->request['calp_search']   = isset( $_REQUEST['calp_search'] ) ? $_REQUEST['calp_search'] : null;
				$this->request['calp_older']    = isset( $_REQUEST['calp_older'] ) ? $_REQUEST['calp_older'] : null;
				break;

			case 'calp_term_filter':
				$this->request['calp_post_ids'] = isset( $_REQUEST['calp_post_ids'] ) ? $_REQUEST['calp_post_ids'] : null;
				$this->request['calp_term_ids'] = isset( $_REQUEST['calp_term_ids'] ) ? $_REQUEST['calp_term_ids'] : null;
				break;
                
			case 'calp_agenda_item':
				$this->request['calp_item_id'] = isset( $_REQUEST['calp_item_id'] ) ? $_REQUEST['calp_item_id'] : null;
				break;
			case 'calp_popup':
				$this->request['calp_item_id'] = isset( $_REQUEST['calp_item_id'] ) ? $_REQUEST['calp_item_id'] : null;
				break;

			case 'calp_search':
				$this->request['calp_search_text'] 	= isset( $_REQUEST['calp_search_text'] ) ? $_REQUEST['calp_search_text'] : null;
				$this->request['calp_cat_ids'] 		= isset( $_REQUEST['calp_cat_ids'] )	 ? $_REQUEST['calp_cat_ids'] : null;
				break;
		}
	}

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
	 * view function
	 *
	 * Display requested calendar page.
	 *
	 * @return void
	 **/
	function view()
 	{
		global $calp_view_helper,
		       $calp_settings,
		       $calp_events_helper;

		$this->process_request();

		// Set body class
		add_filter( 'body_class', array( &$this, 'body_class' ) );
		// Queue any styles, scripts
		$this->load_css();
		$this->load_js();

		// Define arguments for specific calendar sub-view (month, agenda, etc.)
		$args = array(
			'active_event' => $this->request['calp_active_event']
		);

		// Find out which view of the calendar page was requested
		switch( $this->request['action'] )
		{
            case 'calp_today':
				$args['today_offset'] = $this->request['calp_today_offset'];
				$view = $this->get_today_view( $args );
				break;
                
            case 'calp_week':
				$args['week_offset'] = $this->request['calp_week_offset'];
				$view = $this->get_week_view( $args );
				break;
            
			case 'calp_month':
				$args['month_offset'] = $this->request['calp_month_offset'];
				$view = $this->get_month_view( $args );
				break;

			case 'calp_agenda':
				$args['agenda_offset'] = $this->request['calp_agenda_offset'];
				$view = $this->get_agenda_view( $args );
				break;
		}

	  	// Validate preselected category/tag/post IDs
	  	$cat_ids  = join( ',', array_filter( explode( ',', $this->request['calp_cat_ids'] ), 'is_numeric' ) );
	  	$tag_ids  = join( ',', array_filter( explode( ',', $this->request['calp_tag_ids'] ), 'is_numeric' ) );

	  	$categories = get_terms( 'events_categories', array( 'orderby' => 'name' ) );
    	foreach( $categories as &$cat ) {
      		$cat->color = $calp_events_helper->get_category_color_square( $cat->term_id );
    	}

    	$export_url = CALP_EXPORT_URL;
		if ( !empty($cat_ids) ) {
		    $export_url .= '&calp_cat_ids='. implode( ',', $cat_ids );
		}

		// Define new arguments for overall calendar view
		$args = array(
			'view'                    => $view,
			'categories'              => $categories,
			'tags'                    => get_terms( 'events_tags', array( 'orderby' => 'name' ) ),
			'selected_cat_ids'        => $cat_ids,
			'selected_tag_ids'        => $tag_ids,
			'hide_subscribe'		  => $calp_settings->hide_subscribe,
			'export_url'        	  => $export_url,
		);

		// Feed month view into generic calendar view
		echo apply_filters( 'calp_view', $calp_view_helper->get_view( 'calendar.php', $args ), $args );
	}
    
	/**
	 * get_today_view function
	 *
	 * Return the embedded week view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int today_offset   => specifies which today to display relative to the
	 *                        current day
	 *   int active_event  => specifies which event to make visible when
	 *                        page is loaded
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *   array post_ids    => restrict events returned to the given set of
	 *                        post IDs
	 *
	 * @return string	        returns string of view output
	 */
	function get_today_view( $args )
 	{
		global $calp_view_helper,
		       $calp_events_helper,
		       $calp_calendar_helper;

        // get last selected Item
        if ( is_null($args['today_offset']) && !empty($_COOKIE['agenda_item']) ) {
            $event_results = $calp_calendar_helper->get_agenda_item( (int)$_COOKIE['agenda_item'] );
            if ( $event_results ) {
                $bits = $calp_events_helper->gmgetdate( $event_results->start );
                $timestamp = gmmktime( 0, 0, 0, $bits['mon'], 2, $bits['year'] );
                $args['today_offset'] =  floor(( $timestamp - $calp_events_helper->gmt_to_local( time() ) ) / 86400 );
            }
        }
        
        if ( is_null($args['today_offset']) )
            $args['today_offset'] = 0;
        
		$defaults = array(
			'today_offset'   => 0,
			'active_event'  => null,
			'categories'    => array(),
			'tags'          => array(),
            'cat_ids'       => array()
		);
		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		// Get components of localized time
        date_default_timezone_set("UTC");
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
        
		// Now apply to reference timestamp
		$timestamp = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $today_offset, $bits['year'] );

		$cell_array = $calp_calendar_helper->get_today_cell_array( $timestamp, array( 'cat_ids' => $cat_ids ) );
		$pagination_links = $calp_calendar_helper->get_today_pagination_links( $today_offset );

		$view_args = array(
			'title'             => date_i18n( 'j', $timestamp ),
			'sub_title'         => sprintf( __( '%s , %s', CALP_PLUGIN_NAME ), date_i18n( 'j F', $timestamp ), date_i18n( 'Y l', $timestamp ) ),
			'events'            => $cell_array,
            'weekdays'          => $calp_calendar_helper->get_weekdays( true ),
			'weeks'             => $calp_events_helper->get_month_weeks( $timestamp, $today_offset ),
			'pagination_links'  => $pagination_links,
			'active_event'      => $active_event,
			'time_format'       => get_option( 'time_format', 'g a' )
		);
        
		return apply_filters( 'calp_get_today_view', $calp_view_helper->get_view( 'today.php', $view_args ), $view_args );
	}
    
	/**
	 * get_week_view function
	 *
	 * Return the embedded week view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int week_offset   => specifies which week to display relative to the
	 *                        current week
	 *   int active_event  => specifies which event to make visible when
	 *                        page is loaded
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *   array post_ids    => restrict events returned to the given set of
	 *                        post IDs
	 *
	 * @return string	        returns string of view output
	 */
	function get_week_view( $args )
 	{
		global $calp_view_helper,
		       $calp_events_helper,
		       $calp_calendar_helper;

		$defaults = array(
			'week_offset'   => 0,
			'active_event'  => null,
			'categories'    => array(),
			'tags'          => array(),
            'cat_ids'       => array()
		);
		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		// Get components of localized time
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
		// Day shift is initially the first day of the week according to settings
		$day_shift = $calp_events_helper->get_week_start_day_offset( $bits['wday'] );
		// Then apply week offset
		$day_shift += $args['week_offset'] * 7;

		// Now apply to reference timestamp
		$timestamp = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $day_shift, $bits['year'] );

		$cell_array = $calp_calendar_helper->get_week_cell_array( $timestamp, array( 'cat_ids' => $cat_ids) );
		$pagination_links = $calp_calendar_helper->get_week_pagination_links( $week_offset );

		/* translators: "%s" represents the week's starting date */
		$view_args = array(
			'title'             => sprintf( __( 'Week of %s', CALP_PLUGIN_NAME ), date_i18n( __( 'F j', CALP_PLUGIN_NAME ), $timestamp, true ) ),
			'cell_array'        => $cell_array,
			'now_top'           => $bits['hours'] * 60 + $bits['minutes'],
			'pagination_links'  => $pagination_links,
			'active_event'      => $active_event,
			'time_format'       => get_option( 'time_format', 'g a' )
		);
		return apply_filters( 'calp_get_week_view', $calp_view_helper->get_view( 'week.php', $view_args ), $view_args );
	}
    
    /**
	 * get_month_view function
	 *
	 * Return the embedded month view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int month_offset  => specifies which month to display relative to the
	 *                        current month
	 *   int active_event  => specifies which event to make visible when
	 *                        page is loaded
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *   array post_ids    => restrict events returned to the given set of
	 *                        post IDs
	 *
	 * @return string	        returns string of view output
	 **/
	function get_month_view( $args )
 	{
		global $calp_view_helper,
		       $calp_events_helper,
		       $calp_calendar_helper;

        $defaults = array(
          'month_offset'  => 0,
          'active_event'  => null,
          'categories'    => array(),
          'tags'          => array(),
          'cat_ids'       => array(),
        );
        $args = wp_parse_args( $args, $defaults );

		extract( $args );

		// Get components of localized time
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
		// Use first day of the month as reference timestamp, and apply month offset
		$timestamp = gmmktime( 0, 0, 0, $bits['mon'] + $month_offset, 1, $bits['year'] );

		$days_events = $calp_calendar_helper->get_events_for_month( $timestamp, array( 'cat_ids' => $cat_ids ) );
        
		$cell_array = $calp_calendar_helper->get_month_cell_array( $timestamp, $days_events, array( 'cat_ids' => $cat_ids ) );
		$pagination_links = $calp_calendar_helper->get_month_pagination_links( $month_offset );
        
		$view_args = array(
			'title'            => date_i18n( 'F Y', $timestamp, true ),
			'weekdays'         => $calp_calendar_helper->get_weekdays( true ),
			'cell_array'       => $cell_array,
			'pagination_links' => $pagination_links,
			'active_event'     => $active_event,
            'timestamp'        => $timestamp
		);
		return apply_filters( 'calp_get_month_view', $calp_view_helper->get_view( 'month.php', $view_args ), $view_args );
	}

	/**
	 * get_agenda_view function
	 *
	 * Return the embedded agenda view of the calendar, optionally filtered by
	 * event categories and tags.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int page_offset   => specifies which page to display relative to today's page
	 *   int active_event  => specifies which event to make visible when
	 *                        page is loaded
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *
	 * @return string	        returns string of view output
	 **/
	function get_agenda_view( $args )
 	{
		global $calp_view_helper,
		       $calp_app_helper,
		       $calp_events_helper,
		       $calp_calendar_helper,
		       $calp_settings;

		$defaults = array(
			'agenda_offset'   => 0,
			'active_event'  => null,
			'categories'    => array(),
			'tags'          => array(),
            'cat_ids'       => array(),
            'current_item'  => NULL,
            'search'        => NULL,
            'older'        	=> NULL,
		);
		$args = wp_parse_args( $args, $defaults );
        
		extract( $args );

		// Get localized time
        date_default_timezone_set("UTC");
		$timestamp = $calp_events_helper->gmt_to_local( time() );
        	
		if ( !empty($args['current_item']) && is_null($args['search']) ) {
			$event_results = $calp_calendar_helper->get_agenda_item( (int)$args['current_item'] );
			if ( !empty($event_results) ) {
			    $bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
				$date1 = gmmktime( 0, 0, 0, $bits['mon'], 1, $bits['year'] );
				$date2 = $calp_events_helper->gmt_to_local($event_results->start);
				$agenda_offset = $calp_calendar_helper->time_diff_in_month($date1, $date2);
			}
        }

        $bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
		// Use first day of the month as reference timestamp, and apply month offset
		$timestamp = gmmktime( 0, 0, 0, $bits['mon'] + $agenda_offset, 1, $bits['year'] );
        $title = date_i18n( 'F Y', $timestamp, true );

        $is_search = FALSE;

         // Search events selected event
        if ( is_null($args['agenda_offset']) && !is_null($args['current_item']) && !is_null($args['search']) ) {
            $event_results = $calp_calendar_helper->get_search_items( $args['search'], (int)$args['current_item'], false, $args['cat_ids'] );
            if ( isset($event_results['weeks']) ) {
                $cat_ids = $calp_calendar_helper->get_categories_array();
                $title = 'SEARCH: '.urldecode($args['search']);
                $current_item  = $event_results['current'];
                $events = $event_results;
                $is_search = TRUE;
            }
        }
        // Search events not selected event
        if ( is_null($args['agenda_offset']) && is_null($args['current_item']) && !is_null($args['older']) ) {
            $event_results = $calp_calendar_helper->get_search_items( $args['search'], false, $args['older'], $args['cat_ids'] );
            if ( isset($event_results['weeks']) ) {
                $cat_ids = $calp_calendar_helper->get_categories_array();
                $title = 'SEARCH: '.urldecode($args['search']);
                $current_item  = $event_results['current'];
                $events = $event_results;
                $is_search = TRUE;
            }
        }
        
		// Search events
        if ($is_search && isset($events)) {
            $event_results = $cell_array = $events;
        // Agenda Events
        } else {
            $event_results = $calp_calendar_helper->get_events_for_month( $timestamp, array( 'cat_ids' => $cat_ids ) );
            $cell_array = $calp_calendar_helper->get_agenda_cell_array( $timestamp, $event_results, $current_item );
        }
        
		$pagination_links = $calp_calendar_helper->get_agenda_pagination_links( $agenda_offset );
        
        $empty_days = true;
        $current_instance = false;
        foreach ($event_results as $result) {
            if (!empty($result)) {
                $empty_days = false;
            }
            if ( is_array($result) ) {
	            foreach ($result as $day) {
	                if ( isset($day->current) ) {
	                    $current_instance = $day->instance_id;
	                }
	                // Search items
	                $check_array = (array)$day;
	                if ( isset( $check_array['events'] ) ) {
	                   foreach ($day['events'] as $event) {
		                    if ( $event->current ) {
			                    $current_instance = $event->instance_id;
			                }
		                }
	                }
	                
	            }
            }
        }
        
        // Build current url for social buttons
        $request = array();
        $request['action'] = isset( $this->request['action'] ) ? $this->request['action'] : null;
        
        $current_url = get_permalink( get_option('calpress_page_id') );
        if ( !empty($cell_array['current']) ) {
        	$current_url .= $calp_app_helper->get_param_delimiter_char( $current_url );
        	$current_url .= 'action=calp_agenda&calp_item_id='.(int)$cell_array['current'];
        }
        
		// Incorporate offset into date
		$args = array(
            'title'             => $title,
			'weekdays'          => $calp_calendar_helper->get_weekdays(),
			'cell_array'        => $cell_array['weeks'],
			'timestamp'         => $timestamp,
			'empty_days'        => $empty_days,
			'pagination_links'  => $pagination_links,
			'active_event'      => $active_event,
			'current_url'       => $current_url
		);
		
		$file = $is_search ? 'agenda_search.php' : 'agenda.php';
		return apply_filters( 'calp_get_agenda_view', $calp_view_helper->get_view( $file, $args ), $args );
	}
    
	/**
	 * get_agenda_item_view function
	 *
	 * Return the embedded agenda view of the selected agenda item.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int page_offset   => specifies which page to display relative to today's page
	 *   int active_event  => specifies which event to make visible when
	 *                        page is loaded
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *
	 * @return string	        returns string of view output
	 **/
	function get_agenda_item_view( $args )
 	{
		global $calp_settings,
			   $calp_app_helper,
			   $calp_view_helper,
		       $calp_events_helper,
		       $calp_calendar_helper;
		extract( $args );

		$event_results = $calp_calendar_helper->get_agenda_item( $agenda_item );
        
        // Build current url for social buttons
        $current_url = get_permalink( get_option('calpress_page_id') );
        $current_url .= $calp_app_helper->get_param_delimiter_char( $current_url );
        $current_url .= 'action=calp_agenda&calp_item_id='.(int)$agenda_item;
        
        // Save last selected agenda item in cookie
        setcookie('agenda_item', (int)$agenda_item, 0, '/');
		// Incorporate offset into date
		$args = array(
            'current_post'  => $event_results,
            'current_url'   => $current_url
		);
		return apply_filters( 'calp_get_agenda_view', $calp_view_helper->get_view( 'agenda_list.php', $args ), $args );
	}
    
	/**
	 * get_popup_view function
	 *
	 * Return the embedded popup view.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int page_offset   => specifies which page to display relative to today's page
	 *   int active_event  => specifies which event to make visible when
	 *                        page is loaded
	 *   array categories  => restrict events returned to the given set of
	 *                        event category slugs
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *
	 * @return string	        returns string of view output
	 **/
	function get_popup_view( $args )
 	{
		global $calp_view_helper,
		       $calp_events_helper,
		       $calp_calendar_helper,
		       $calp_settings,
		       $calp_app_helper;
		extract( $args );

		$event_results = $calp_calendar_helper->get_agenda_item( $agenda_item );

        $share_url = get_permalink( get_option('calpress_page_id') );
        $share_url .= '#action=calp_agenda&calp_item_id='.(int)$agenda_item;

		// Incorporate offset into date
		$args = array(
            'event'  => $event_results,
            'share_url' => $share_url
		);
		return apply_filters( 'calp_get_agenda_view', $calp_view_helper->get_view( 'event-popup.php', $args ), $args );
	}
    
    /**
	 * ajax_week function
	 *
	 * AJAX request handler for today view.
	 *
	 * @return void
	 */
	function ajax_today() {
        global $calp_view_helper;

		$this->process_request();

		// View arguments
		$args = array(
			'today_offset' => $this->request['calp_today_offset'],
			'active_event' => $this->request['calp_active_event'],
            'cat_ids'      => array_filter( explode( ',', $this->request['calp_cat_ids'] ), 'is_numeric' )
		);
        
        if ( !is_null($this->request['calp_today']) ) {
            setcookie ( 'agenda_item', '', time() - 3600);
            unset( $_COOKIE['agenda_item'] );
        }
        
		// Return this data structure to the client
		$data = array(
			'body_class' => join( ' ', $this->body_class() ),
			'html' => $this->get_today_view( $args ),
		);
		$calp_view_helper->json_response( $data );
	}

	/**
	 * get_search_view function
	 *
	 * Return the embedded search view.
	 *
	 * @param array $args     associative array with any of these elements:
	 *   int page_offset   => specifies which page to display relative to today's page
	 *   int active_event  => specifies which event to make visible when
	 *                        page is loaded
	 *   array tags        => restrict events returned to the given set of
	 *                        event tag names
	 *
	 * @return string	        returns string of view output
	 **/
	function get_search_view( $args )
 	{
		global $calp_view_helper,
		       $calp_events_helper,
		       $calp_calendar_helper;
		extract( $args );

		$search_results = $calp_calendar_helper->get_search( $search_text, $cat_ids );
        
		// Incorporate offset into date
        date_default_timezone_set("UTC");
		$args = array(
            'events'  				=> $search_results,
            'search_text'  			=> $search_text,
            'calp_events_helper'	=> $calp_events_helper
		);
		return apply_filters( 'calp_get_search_view', $calp_view_helper->get_view( 'search.php', $args ), $args );
	}
    
    /**
	 * ajax_week function
	 *
	 * AJAX request handler for week view.
	 *
	 * @return void
	 */
	function ajax_week() {
		global $calp_view_helper;

		$this->process_request();

		// View arguments
		$args = array(
			'week_offset' => $this->request['calp_week_offset'],
			'active_event' => $this->request['calp_active_event'],
            'cat_ids'         => array_filter( explode( ',', $this->request['calp_cat_ids'] ), 'is_numeric' )
		);

		// Return this data structure to the client
		$data = array(
			'body_class' => join( ' ', $this->body_class() ),
			'html' => $this->get_week_view( $args ),
		);
		$calp_view_helper->json_response( $data );
	}

	/**
	 * ajax_month function
	 *
	 * AJAX request handler for month view.
	 *
	 * @return void
	 */
	function ajax_month() {
		global $calp_view_helper;

		$this->process_request();

		// View arguments
		$args = array(
			'month_offset'    => $this->request['calp_month_offset'],
			'active_event'    => $this->request['calp_active_event'],
			'cat_ids'         => array_filter( explode( ',', $this->request['calp_cat_ids'] ), 'is_numeric' )
		);

		// Return this data structure to the client
		$data = array(
			'body_class' => join( ' ', $this->body_class() ),
			'html' => $this->get_month_view( $args ),
		);
		$calp_view_helper->json_response( $data );
	}

	/**
	 * ajax_agenda function
	 *
	 * AJAX request handler for agenda view.
	 *
	 * @return void
	 **/
	function ajax_agenda() {
		global $calp_view_helper;

		$this->process_request();

		// View arguments
		$args = array(
			'agenda_offset'  => $this->request['calp_agenda_offset'],
			'active_event'   => $this->request['calp_active_event'],
            'current_item'   => $this->request['calp_item_id'],
            'search'    	 => $this->request['calp_search'],
            'older'     	 => $this->request['calp_older'],
            'cat_ids'        => array_filter( explode( ',', $this->request['calp_cat_ids'] ), 'is_numeric' )
		);

		// Return this data structure to the client
		$data = array(
			'body_class' => join( ' ', $this->body_class() ),
			'html' => $this->get_agenda_view( $args ),
		);
		$calp_view_helper->json_response( $data );
	}
    
    /**
	 * ajax_agenda_item function
	 *
	 * AJAX request handler for agenda item view.
	 *
	 * @return void
	 **/
	function ajax_agenda_item() {
		global $calp_view_helper;

		$this->process_request();

		// View arguments
		$args = array(
			'agenda_item'  => $this->request['calp_item_id']
		);
        
		// Return this data structure to the client
		$data = array(
			'html' => $this->get_agenda_item_view( $args ),
		);
		$calp_view_helper->json_response( $data );
	}
    
    /**
	 * ajax_popup function
	 *
	 * AJAX request handler for popup.
	 *
	 * @return void
	 **/
	function ajax_popup() {
		global $calp_view_helper;

		$this->process_request();

		// View arguments
		$args = array(
			'agenda_item'  => $this->request['calp_item_id']
		);
        
		// Return this data structure to the client
		$data = array(
			'html' => $this->get_popup_view( $args ),
		);
		$calp_view_helper->json_response( $data );
	}

    /**
	 * ajax_search function
	 *
	 * AJAX request handler for search.
	 *
	 * @return void
	 **/
	function ajax_search() {
		global $calp_view_helper;

		$this->process_request();

		// View arguments
		$args = array(
			'search_text'  	=> $this->request['calp_search_text'],
			'cat_ids'		=> $this->request['calp_cat_ids']
		);
        
		// Return this data structure to the client
		$data = array(
			'html' => $this->get_search_view( $args ),
		);
		$calp_view_helper->json_response( $data );
	}

	/**
	 * ajax_term_filter function
	 *
	 * AJAX request handler that takes a comma-separated list of event IDs and
	 * comma-separated list of term IDs and returns those event IDs within the
	 * set that have any of the term IDs.
	 *
	 * @return void
	 **/
	function ajax_term_filter() {
		global $calp_view_helper, $calp_events_helper;

		$this->process_request();

		$post_ids = array_unique( explode( ',', $this->request['calp_post_ids'] ) );

		if( $this->request['calp_term_ids'] ) {
			$term_ids = explode( ',', $this->request['calp_term_ids'] );
			$matching_ids = $calp_events_helper->filter_by_terms( $post_ids, $term_ids );
            $unmatching_ids = array_diff( $post_ids, $matching_ids );
		} else {
			// If no term IDs were provided for filtering, then hide all posts
			$matching_ids = array();
            $unmatching_ids = $post_ids;
		}

		$data = array(
			'matching_ids' => $matching_ids,
			'unmatching_ids' => $unmatching_ids,
	 	);
		$calp_view_helper->json_response( $data );
	}

	/**
	 * body_class function
	 *
	 * Append custom classes to body element.
	 *
	 * @return void
	 **/
	function body_class( $classes = array() ) {
		$classes[] = 'calp-calendar';

		// Reformat action for body class
		$action = $this->request['action'];
		$action = strtr( $action, '_', '-' );
		$action = preg_replace( '/^calp-/', '', $action );
        
		$classes[] = "calp-action-$action";
		if( isset( $this->request['calp_month_offset'] ) && ! $this->request['calp_month_offset'] &&
				isset( $this->request['calp_page_offset'] ) && ! $this->request['calp_page_offset'] ) {
			$classes[] = 'calp-today';
		}
		return $classes;
	}

	/**
	 * load_css function
	 *
	 * Enqueue any CSS files required by the calendar views, as well as embeds any
	 * CSS rules necessary for calendar container replacement.
	 *
	 * @return void
	 **/
	function load_css()
	{
		global $calp_settings;

		wp_enqueue_style( 'calp-general', CALP_CSS_URL . '/general.css', array(), 1 );
		wp_enqueue_style( 'calp-calendar', CALP_THEME_URL . $calp_settings->calendar_theme . '/calendar.css', array(), 1 );
	}

	/**
	 * selector_css function
	 *
	 * Inserts dynamic CSS rules into <head> section of page to replace
	 * desired CSS selector with calendar.
	 */
	function selector_css() {
		global $calp_view_helper, $calp_settings;

		$calp_view_helper->display_css(
			'selector.css',
			array( 'selector' => $calp_settings->calendar_css_selector )
		);
	}

	/**
	 * load_js function
	 *
	 * Enqueue any JavaScript files required by the calendar views.
	 *
	 * @return void
	 **/
	function load_js()
 	{
 		global $calp_settings;

		// Include dependent jQuery plugins
		wp_enqueue_script( 'jquery.scrollTo', CALP_JS_URL . '/jquery.scrollTo-min.js', array( 'jquery' ), 1 );
		// Include custom script
		wp_enqueue_script( 'calp-calendar', CALP_JS_URL . '/calendar.js', array( 'jquery', 'jquery.scrollTo' ), 1 );

		$data = array(
			// Point script to AJAX URL
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			// What this view defaults to, in case there is no #hash appended
			'default_hash'  => '#' . http_build_query( $this->request ),
			'export_url'    => CALP_EXPORT_URL,
			// Body classes if need to be set manually
			'body_class'    => join( ' ', $this->body_class() ),
		);

		wp_localize_script( 'calp-calendar', 'calp_calendar', $data );
	}

	/**
	 * function is_category_requested
	 *
	 * Returns the comma-separated list of category IDs that the calendar page
	 * was requested to be prefiltered by.
	 *
	 * @return string
	 */
	function get_requested_categories() {
		return $this->request['calp_cat_ids'];
	}
}
// END class
