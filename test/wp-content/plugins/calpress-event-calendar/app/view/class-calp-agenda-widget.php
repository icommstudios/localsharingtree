<?php

/**
 * Calp_Agenda_Widget class
 *
 * A widget that displays the next X upcoming events (similar to Agenda view).
 */
class Calp_Agenda_Widget extends WP_Widget
{
	/**
	 * _construct function
	 *
	 * Constructor for widget.
	 */
	function __construct() {
		parent::__construct(
			'calp_agenda_widget',
			__( 'CalPress Events', CALP_PLUGIN_NAME ),
			array(
				'description' => __( 'CalPress: Lists of the upcoming events', CALP_PLUGIN_NAME ),
				'class' => 'calp-agenda-widget',
			)
		);

		// AJAX script before returning AJAX responses.
		if( basename( $_SERVER['SCRIPT_NAME'] ) == 'admin-ajax.php' )
		{
			add_action( 'wp_ajax_calp_widget_navigation', array( &$this, 'ajax_widget_navigation' ) );
			add_action( 'wp_ajax_nopriv_calp_widget_navigation', array( &$this, 'ajax_widget_navigation' ) );
		}
	}

	/**
	 * form function
	 *
	 * Renders the widget's configuration form for the Manage Widgets page.
	 *
	 * @param array $instance The data array for the widget instance being
	 *                        configured.
	 */
	function form( $instance )
	{
		global $calp_view_helper;

		$default = array(
			'title'                   => __( 'CalPress Events', CALP_PLUGIN_NAME ),
			'events_per_page'         => 10,
			'show_subscribe_buttons'  => true,
			'show_calendar_button'    => true,
			'hide_on_calendar_page'   => true,
			'show_upcoming_events' 	  => false,
			'show_calendar_navigator' => false,
			'limit_by_cat'            => false,
			'limit_by_tag'            => false,
			'limit_by_post'           => false,
			'event_cat_ids'           => array(),
			'event_tag_ids'           => array(),
			'event_post_ids'          => array(),
		);
		$instance = wp_parse_args( (array) $instance, $default );

		// Get available cats, tags, events to allow user to limit widget to certain categories
		$events_categories = get_terms( 'events_categories', array( 'orderby' => 'name', "hide_empty" => false ) );
		$events_tags       = get_terms( 'events_tags', array( 'orderby' => 'name', "hide_empty" => false ) );
	    $get_events        = new WP_Query( array ( 'post_type' => CALP_POST_TYPE, 'posts_per_page' => -1 ) );
	    $events_options    = $get_events->posts;

		// Generate unique IDs and NAMEs of all needed form fields
		$fields = array(
			'title'                   => array('value'   => $instance['title']),
			'events_per_page'         => array('value'   => $instance['events_per_page']),
			'show_subscribe_buttons'  => array('value'   => $instance['show_subscribe_buttons']),
			'show_calendar_button'    => array('value'   => $instance['show_calendar_button']),
			'hide_on_calendar_page'   => array('value'   => $instance['hide_on_calendar_page']),
			'show_upcoming_events' 	  => array('value'   => $instance['show_upcoming_events']),
			'show_calendar_navigator' => array('value'   => $instance['show_calendar_navigator']),
			'limit_by_cat'            => array('value'   => $instance['limit_by_cat']),
			'limit_by_tag'            => array('value'   => $instance['limit_by_tag']),
			'limit_by_post'           => array('value'   => $instance['limit_by_post']),
			'event_cat_ids'           => array(
			                                  'value'   => (array)$instance['event_cat_ids'],
			                                  'options' => $events_categories
			                                 ),
			'event_tag_ids'           => array(
			                                  'value'   => (array)$instance['event_tag_ids'],
			                                  'options' => $events_tags
			                                 ),
			'event_post_ids'          => array(
			                                  'value'   => (array)$instance['event_post_ids'],
			                                  'options' => $events_options
			                                 ),
		);
		foreach( $fields as $field => $data ) {
			$fields[$field]['id']    = $this->get_field_id( $field );
			$fields[$field]['name']  = $this->get_field_name( $field );
			$fields[$field]['value'] = $data['value'];
			if( isset($data['options']) ) {
				$fields[$field]['options'] = $data['options'];
			}
		}

		$calp_view_helper->display( 'agenda-widget-form.php', $fields );
	}

	/**
	 * update function
	 *
	 * Called when a user submits the widget configuration form. The data should
	 * be validated and returned.
	 *
	 * @param array $new_instance The new data that was submitted.
	 * @param array $old_instance The widget's old data.
	 * @return array The new data to save for this widget instance.
	 */
	function update( $new_instance, $old_instance )
	{
		// Save existing data as a base to modify with new data
		$instance = $old_instance;
		$instance['title']                   = strip_tags( $new_instance['title'] );
		$instance['events_per_page']         = intval( $new_instance['events_per_page'] );
		if( $instance['events_per_page'] < 1 ) $instance['events_per_page'] = 1;
		$instance['show_subscribe_buttons']  = $new_instance['show_subscribe_buttons'] ? true : false;
		$instance['show_calendar_button']    = $new_instance['show_calendar_button'] ? true : false;
		$instance['hide_on_calendar_page']   = $new_instance['hide_on_calendar_page'] ? true : false;
		$instance['show_upcoming_events'] 	 = $new_instance['show_upcoming_events'] ? true : false;
		$instance['show_calendar_navigator'] = $new_instance['show_calendar_navigator'] ? true : false;
		// For limits, set the limit to False if no IDs were selected, or set the respective IDs to empty if "limit by" was unchecked
		$instance['limit_by_cat']            = ( ! $new_instance['event_cat_ids'] || ! $new_instance['limit_by_cat'] ) ? false : true;
		$instance['event_cat_ids']           = ! $new_instance['limit_by_cat'] ? array() : $new_instance['event_cat_ids'] ;
		$instance['limit_by_tag']            = ( ! $new_instance['event_tag_ids'] || ! $new_instance['limit_by_tag'] ) ? false : true;
		$instance['event_tag_ids']           = ! $new_instance['limit_by_tag'] ? array() : $new_instance['event_tag_ids'] ;
		$instance['limit_by_post']           = ( ! $new_instance['event_post_ids'] || ! $new_instance['limit_by_post'] ) ? false : true;
		$instance['event_post_ids']          = ! $new_instance['limit_by_post'] ? array() : $new_instance['event_post_ids'] ;

		return $instance;
	}

	/**
	 * widget function
	 *
	 * Outputs the given instance of the widget to the front-end.
	 *
	 * @param array $args Display arguments passed to the widget
	 * @param array $instance The settings for this widget instance
	 */
	function widget( $args, $instance )
	{
		global $calp_view_helper,
		       $calp_events_helper,
		       $calp_calendar_helper,
		       $calp_settings;

		$defaults = array(
			'hide_on_calendar_page'   => true,
			'show_calendar_navigator' => false,
			'event_cat_ids'           => array(),
			'event_tag_ids'           => array(),
			'event_post_ids'          => array(),
			'events_per_page'         => 10,
		);
		$instance = wp_parse_args( $instance, $defaults );

		if( $instance['hide_on_calendar_page'] &&
		    is_page( $calp_settings->calendar_page_id ) )
			return;
        
/*        if ( empty($instance['event_cat_ids']) ) {
            $instance['event_cat_ids'] = $calp_calendar_helper->get_categories_array();
        }*/

        $instance['event_cat_ids'] = !empty($instance['event_cat_ids'])?$instance['event_cat_ids']:$calp_calendar_helper->get_categories_array();
        
		// Add params to the subscribe_url for filtering by Limits (category, tag)
		$subscribe_filter  = '';
		$subscribe_filter .= $instance['event_cat_ids'] ? '&calp_cat_ids=' . join( ',', $instance['event_cat_ids'] ) : '';
		$subscribe_filter .= $instance['event_tag_ids'] ? '&calp_tag_ids=' . join( ',', $instance['event_tag_ids'] ) : '';
		$subscribe_filter .= $instance['event_post_ids'] ? '&calp_post_ids=' . join( ',', $instance['event_post_ids'] ) : '';

		// Get localized time
		$timestamp = $calp_events_helper->gmt_to_local( time() );
    
		// Set $limit to the specified category/tag
		$limit = array(
		                'cat_ids'   => $instance['event_cat_ids'],
		                'tag_ids'   => $instance['event_tag_ids'],
		                'post_ids'  => $instance['event_post_ids'],
		              );
		// Get events, then classify into date array
		if ( !$instance['show_upcoming_events'] ) {
			$event_results = $calp_calendar_helper->get_widget_events(
				$timestamp, $instance['events_per_page'], $limit, false );
    		$dates = $calp_calendar_helper->get_agenda_date_array( $event_results, $timestamp, $instance['events_per_page'] );
		} else {
			$dates = $calp_calendar_helper->get_widget_upcoming_events(
				$timestamp, $instance['events_per_page'], $limit, false );
		}

		// Show mini calendar navigator
		if ($instance['show_calendar_navigator'] && !$instance['show_upcoming_events']) {
			$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
			$timestamp = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + 0, $bits['year'] );
			$args['weekdays'] = $calp_calendar_helper->get_weekdays();
			$args['weeks']    = $calp_events_helper->get_month_weeks( $timestamp, 0 );
			foreach( $args['weeks'] as &$week ) {
				foreach( $week as &$day ) {
					if ( !empty($day['date']) ) {
						$timestamp = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $day['offset'], $bits['year'] );
						$events = $calp_calendar_helper->get_widget_events( $timestamp, 1, $limit, true );
						$day['has_events'] = false;
						if ( !empty($events) ) {
						    $day['has_events'] = true;
						}
					}
				}
			}
			$links = $calp_calendar_helper->get_widget_pagination_links( 0 );
			$args['links'] = $links; 
		}

		$args['title']                   = $instance['title'];
		$args['show_subscribe_buttons']  = $instance['show_subscribe_buttons'];
		$args['show_calendar_button']    = $instance['show_calendar_button'];
		$args['show_calendar_navigator'] = $instance['show_calendar_navigator'] && !$instance['show_upcoming_events'];
		$args['dates']                   = $dates;
		$args['calendar_url']            = get_permalink( $calp_settings->calendar_page_id );
		$args['event_url']				 = $args['calendar_url'] . '#action=calp_agenda&calp_item_id=';
		$args['subscribe_url']           = CALP_EXPORT_URL . $subscribe_filter;

		$calp_view_helper->display( 'agenda-widget.php', $args );
	}

	    /**
	 * ajax_widget_navigation function
	 *
	 * AJAX request handler for widget navigation.
	 *
	 * @return void
	 **/
	function ajax_widget_navigation() {
        global $calp_view_helper;
        
        $args = array(
        	'calp_offset' => isset($_REQUEST['calp_offset']) ? intval($_REQUEST['calp_offset']) : 0
        );

        $all_instances = parent::get_settings();
        $instance = $all_instances[ $this->number ];

		$data = array(
			'html' => $this->widget_view( $args, $instance ),
		);
		$calp_view_helper->json_response( $data );
    }

    /**
	 * widget_view function
	 *
	 * Outputs the given instance of the widget to the front-end.
	 *
	 * @param array $args Display arguments passed to the widget
	 * @param array $instance The settings for this widget instance
	 */
	function widget_view( $args, $instance )
	{
		global $calp_view_helper,
		       $calp_events_helper,
		       $calp_calendar_helper,
		       $calp_settings;

		$defaults = array(
			'hide_on_calendar_page'   => true,
			'show_calendar_navigator' => false,
			'event_cat_ids'           => array(),
			'event_tag_ids'           => array(),
			'event_post_ids'          => array(),
			'events_per_page'         => 10,
		);
		$instance = wp_parse_args( $instance, $defaults );

		if( $instance['hide_on_calendar_page'] &&
		    is_page( $calp_settings->calendar_page_id ) )
			return;
        
        /*if ( empty($instance['event_cat_ids']) ) {
            $instance['event_cat_ids'] = $calp_calendar_helper->get_categories_array();
        }*/

        $instance['event_cat_ids'] = !empty($instance['event_cat_ids'])?$instance['event_cat_ids']:$calp_calendar_helper->get_categories_array();
        
		// Add params to the subscribe_url for filtering by Limits (category, tag)
		$subscribe_filter  = '';
		$subscribe_filter .= $instance['event_cat_ids'] ? '&calp_cat_ids=' . join( ',', $instance['event_cat_ids'] ) : '';
		$subscribe_filter .= $instance['event_tag_ids'] ? '&calp_tag_ids=' . join( ',', $instance['event_tag_ids'] ) : '';
		$subscribe_filter .= $instance['event_post_ids'] ? '&calp_post_ids=' . join( ',', $instance['event_post_ids'] ) : '';

		$today_offset = isset( $args['calp_offset'] ) ? intval($args['calp_offset']) : 0;

		// Get localized time
		$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
		$timestamp = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $today_offset, $bits['year'] );
    
		// Set $limit to the specified category/tag
		$limit = array(
		                'cat_ids'   => $instance['event_cat_ids'],
		                'tag_ids'   => $instance['event_tag_ids'],
		                'post_ids'  => $instance['event_post_ids'],
		              );
		
		// Get events, then classify into date array
		$event_results = $calp_calendar_helper->get_widget_events(
			$timestamp, $instance['events_per_page'], $limit, true );
		$dates = $calp_calendar_helper->get_agenda_date_array( $event_results, $timestamp, $instance['events_per_page'] );

		// Show mini calendar navigator
		if ($instance['show_calendar_navigator']) {
			$bits = $calp_events_helper->gmgetdate( $calp_events_helper->gmt_to_local( time() ) );
			$timestamp = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $today_offset, $bits['year'] );
			$args['weekdays'] = $calp_calendar_helper->get_weekdays();
			$args['weeks']    = $calp_events_helper->get_month_weeks( $timestamp, $today_offset );
			foreach( $args['weeks'] as &$week ) {
				foreach( $week as &$day ) {
					if ( !empty($day['date']) ) {
						$timestamp = gmmktime( 0, 0, 0, $bits['mon'], $bits['mday'] + $day['offset'], $bits['year'] );
						$events = $calp_calendar_helper->get_widget_events( $timestamp, 1, $limit, true );
						$day['has_events'] = false;
						if ( !empty($events) ) {
						    $day['has_events'] = true;
						}
					}
				}
			}
			$links = $calp_calendar_helper->get_widget_pagination_links( $today_offset );
			$args['links'] = $links; 
		}

		$args['title']                   = $instance['title'];
		$args['show_subscribe_buttons']  = $instance['show_subscribe_buttons'];
		$args['show_calendar_button']    = $instance['show_calendar_button'];
		$args['show_calendar_navigator'] = $instance['show_calendar_navigator'];
		$args['dates']                   = $dates;
		$args['calendar_url']            = get_permalink( $calp_settings->calendar_page_id );
		$args['event_url']				 = $args['calendar_url'] . '#action=calp_agenda&calp_item_id=';
		$args['subscribe_url']           = CALP_EXPORT_URL . $subscribe_filter;

		return apply_filters( 'agenda-widget-ajax-view', $calp_view_helper->get_view( 'agenda-widget-ajax-view.php', $args ), $args );
	}
}
