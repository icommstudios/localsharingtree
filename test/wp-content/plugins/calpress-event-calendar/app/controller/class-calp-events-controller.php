<?php
//
//  class-calp-events-controller.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//

/**
 * Calp_Events_Controller class
 *
 * @package Controllers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_Events_Controller {
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
	 **/
	private function __construct() { }

	/**
	 * delete_hook function
	 *
	 * If the deleted post is an event
	 * then all entries that match the post_id are
	 * removed from calp_events and calp_event_instances tables
	 *
	 * @param int $pid Post ID
	 *
	 * @return bool | int
	 **/
	function delete_post( $pid ) {
		global $wpdb;

		$sql = "SELECT
							ID
						FROM
							$wpdb->posts
						WHERE
							ID = %d AND
							post_type = '" . CALP_POST_TYPE . "'";

		// is this post an event?
		if( $wpdb->get_var( $wpdb->prepare( $sql, $pid ) ) ) {
			$table_name = $wpdb->prefix . 'calp_events';
			$sql = "DELETE FROM
								$table_name
							WHERE
								post_id = %d";
			// delete from calp_events
			$wpdb->query( $wpdb->prepare( $sql, $pid ) );

			$table_name = $wpdb->prefix . 'calp_event_instances';
			$sql = "DELETE FROM
								$table_name
							WHERE
								post_id = %d";
			// delete from calp_event_instances
			return $wpdb->query( $wpdb->prepare( $sql, $pid ) );
		}
		return true;
	}

	/**
	 * init function
	 *
	 * This function is executed when admin_head hook is called.
	 * Adds CSS and JS files.
	 *
	 * @return void
	 **/
	function init()
	{
		global $calp_events_helper, $calp_settings, $wp_locale;

		// Initialize dashboard view
		if( is_admin() ) {
			// ======
			// = JS =
			// ======
			// Include timespan helper functions
			wp_enqueue_script( 'jquery.calendrical', 			CALP_JS_URL . '/jquery.calendrical.js', array( 'jquery' ) );
			// Include timespan plugin
			wp_enqueue_script( 'jquery.timespan', 				CALP_JS_URL . '/jquery.timespan.js', array( 'jquery', 'jquery.calendrical' ) );
			// Include timespan plugin
			wp_enqueue_script( 'jquery.inputdate', 				CALP_JS_URL . '/jquery.inputdate.js', array( 'jquery', 'jquery.calendrical' ) );
			// Include Google Maps API
			wp_enqueue_script( 'gmap_api', 								'http://maps.google.com/maps/api/js?sensor=false&language=' . $calp_events_helper->get_lang() );
			// Include autocomplete_geomod plugin
			wp_enqueue_script( 'autocomplete_geomod', 		CALP_JS_URL . '/jquery.autocomplete_geomod.js', array( 'jquery' ) );
			// Include geo_autocomplete plugin
			wp_enqueue_script( 'geo_autocomplete', 				CALP_JS_URL . '/geo_autocomplete.js', array( 'jquery', 'autocomplete_geomod' ) );
			// Include element selector function
			//wp_enqueue_script( 'calp-element-selector', 	CALP_JS_URL . '/element-selector.js', array( 'jquery' ) );
			// Include jQuery Tools form elements
			wp_enqueue_script( 'jquery.tools-form',       CALP_JS_URL . '/jquery.tools.min.js', array( 'jquery' ), '1.2.5' );
			// Include add new event script
			wp_enqueue_script( 'calp-blockui', 		      CALP_JS_URL . '/jquery.blockUI.js', array( 'jquery' ) );
			wp_enqueue_script( 'calp-add_new_event', 		CALP_JS_URL . '/add_new_event.js', array( 'jquery', 
			                                                                                         'jquery.timespan', 
			                                                                                         //'calp-element-selector', 
			                                                                                         'jquery.tools-form', 
			                                                                                         'calp-blockui' ) );
			                                                                                         
			wp_enqueue_script( 'calp-color-picker', 	    CALP_JS_URL . '/colorpicker.js', array( 'jquery' ) );

			// Supply custom value to JavaScript from PHP
			wp_localize_script( 'calp-add_new_event', 'calp_add_new_event', array(
				// Current time, used for date/time pickers
				'now'                    => $calp_events_helper->gmt_to_local( time() ),
				// Date format for date pickers
				'date_format'            => $calp_settings->input_date_format,
				// Names for months in date picker header (escaping is done in wp_localize_script)
				'month_names'            => implode( ',', $wp_locale->month ),
				// Names for days in date picker header (escaping is done in wp_localize_script)
				'day_names'              => implode( ',', $wp_locale->weekday_initial ),
				// Start the week on this day in the date picker
				'week_start_day'         => $calp_settings->week_start_day,
				// 24h time format for time pickers
				'twentyfour_hour'        => $calp_settings->input_24h_time,
				// Set region biasing for geo_autocomplete plugin
				'region'                 => ( $calp_settings->geo_region_biasing ) ? $calp_events_helper->get_region() : '',
				// ICS feed error messages
				'duplicate_feed_message' => esc_html__( 'This feed is already being imported.', CALP_PLUGIN_NAME ),
				'invalid_url_message'    => esc_html__( 'Please enter a valid iCalendar URL.', CALP_PLUGIN_NAME ),
			) );

			// =======
			// = CSS =
			// =======
			// include autocomplete style
			wp_enqueue_style( 'autocomplete',         CALP_CSS_URL . '/jquery.autocomplete.css' );
			// include colorpicker style
			wp_enqueue_style( 'colorpicker',          CALP_CSS_URL . '/colorpicker.css' );
			// include add new event style
			wp_enqueue_style( 'calp_add_new_event',  CALP_CSS_URL . '/add_new_event.css' );
		}
		// Initialize front-end view
		else
		{
			// ======
			// = JS =
			// ======
			wp_enqueue_script( 'calp-event', CALP_JS_URL . '/event.js', array( 'jquery' ), 1 );
            //Include tiny scroll script
            wp_enqueue_script( 'calp-tinyscroll',  CALP_JS_URL . '/jquery.tinyscrollbar.min.js', array( 'jquery' ), false, true );
            // Google plus
            wp_localize_script( 'gapi', 'https://apis.google.com/js/plusone.js', array(
				'parsetags' => 'explicit'
			) );
			// Supply custom value to JavaScript from PHP
			wp_localize_script( 'calp-event', 'calp_event', array(
				// Language for Google Map
				'language' => $calp_events_helper->get_lang(),
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			) );

			// =======
			// = CSS =
			// =======
			wp_enqueue_style( 'calp-general', CALP_CSS_URL . '/general.css', array(), 1 );
		}
	}

	/**
	 * meta_box_view function
	 *
	 * Add Events Calculator box to the Add New Event page
	 *
	 * @return void
	 **/
	function meta_box_view() {
		global $calp_view_helper,
					 $calp_events_helper,
					 $post,
					 $wpdb,
					 $calp_settings;

		// ==================
		// = Default values =
		// ==================
		$all_day_event    = '';
		$start_timestamp  = '';
		$end_timestamp    = '';
		$show_map         = false;
		$google_map       = '';
		$venue            = '';
		$country          = '';
		$address          = '';
		$city             = '';
		$province         = '';
		$postal_code      = '';
		$rrule            = '';
		$rrule_text       = '';
		$repeating_event  = false;
		$end              = null;
		$until            = null;
		$count            = 100;

		try
	 	{
			$event = new Calp_Event( $post->ID );

			// Existing event was found. Initialize form values with values from
			// event object.

			$all_day_event    = $event->allday ? 'checked="checked"' : '';

            $start_timestamp  = $event->start;
			$end_timestamp 	  = $event->end;

			$show_map         = $event->show_map;
			$google_map       = $show_map ? 'checked="checked"' : '';

			$venue            = $event->venue;
			$country          = $event->country;
			$address          = $event->address;
			$city             = $event->city;
			$province         = $event->province;
			$postal_code      = $event->postal_code;
			$rrule            = empty( $event->recurrence_rules ) ? '' : $event->recurrence_rules;
			$repeating_event  = empty( $rrule ) ? false : true;
			if( $repeating_event ) {
				$rc = new SG_iCal_Recurrence( new SG_iCal_Line( 'RRULE:' . $rrule ) );
				
				if( $until = $rc->getUntil() )
					$until = ( is_numeric( $until ) ) ? $until : strtotime( $until );
				else if( $count = $rc->getCount() )
					$count = ( is_numeric( $count ) ) ? $count : 100;
				
				$rrule_text = $calp_events_helper->rrule_to_text( $rrule );
			}
			
		}
		catch( Calp_Event_Not_Found $e ) {
			// Event does not exist.
			// Leave form fields undefined (= zero-length strings)
			$event = null;
		}

		// Time zone
		$timezone = get_option( 'gmt_offset' );
		$timezone = sprintf( '(GMT%+d:%02d)', intval( $timezone ), ( abs( $timezone ) * 60 ) % 60 );

		// ===============================
		// = Display event time and date =
		// ===============================
        if ( empty($start_timestamp) ) {
		    $start_timestamp = $calp_events_helper->gmt_to_local( time() );
		    $end_timestamp 	 = $calp_events_helper->gmt_to_local( time() + 60*60 );
		} else {
			$start_timestamp = $calp_events_helper->gmt_to_local( $start_timestamp );
		    $end_timestamp 	 = $calp_events_helper->gmt_to_local( $end_timestamp );
		}
		if( is_null( $until ) ) {
			date_default_timezone_set ('UTC');
			$until   = time();
		}

		$args = array(
			'all_day_event'   => $all_day_event,
			'start_timestamp' => $start_timestamp,
			'end_timestamp'   => $end_timestamp,
			'row_daily'       => $calp_events_helper->row_daily(),
			'row_weekly'      => $calp_events_helper->row_weekly(),
			'row_monthly'     => $calp_events_helper->row_monthly(),
			'row_yearly'      => $calp_events_helper->row_yearly(),
			'count'           => $calp_events_helper->create_count_input( 'calp_count', $count ) . __( 'times', CALP_PLUGIN_NAME ),
			'end'             => $calp_events_helper->create_end_dropdown( $end ),
			'until'           => $until,
			'repeating_event' => $repeating_event,
			'rrule'           => $rrule,
			'rrule_text'      => $rrule_text,
			'timezone'        => $timezone,
			'ending'          => $end
		);
		$calp_view_helper->display( 'box_time_and_date.php', $args );

		// =================================================
		// = Display event location details and Google map =
		// =================================================
		$args = array(
			'venue'						=> $venue,
			'country' 				=> $country,
			'address'					=> $address,
			'city'						=> $city,
			'province' 				=> $province,
			'postal_code' 		=> $postal_code,
			'google_map' 			=> $google_map,
			'show_map'        => $show_map,
		);
		$calp_view_helper->display( 'box_event_location.php', $args );

		/*
			TODO Display Eventbrite ticketing
			$calp_view_helper->display( 'box_eventbrite.php' );
		*/
	}

	/**
	 * save_post function
	 *
	 * Saves meta post data
	 *
	 * @param int $post_id Post ID
	 *
	 * @return void
	 **/
	function save_post( $post_id, $post ) {
		global $wpdb, $calp_events_helper;

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if( isset( $_POST[CALP_POST_TYPE] ) && ! wp_verify_nonce( $_POST[CALP_POST_TYPE], 'calp' ) ) {
			return;
		} else if( ! isset( $_POST[CALP_POST_TYPE] ) ) {
			return;
		}

		if( isset( $post->post_status ) && $post->post_status == 'auto-draft' )
		return;

		// verify if this is not inline-editing
		if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' ) {
			return;
		}

		// verify that the post_type is that of an event
		if( isset( $_POST['post_type'] ) && $_POST['post_type'] != CALP_POST_TYPE ) {
			return;
		}

		$all_day          = isset( $_POST['calp_all_day_event'] )    ? 1                                             : 0;
		$start_time       = isset( $_POST['calp_start_time'] )       ? $_POST['calp_start_time']                    : '';
		$end_time         = isset( $_POST['calp_end_time'] )         ? $_POST['calp_end_time']                      : '';
		$venue            = isset( $_POST['calp_venue'] )            ? stripslashes( $_POST['calp_venue'] )         : '';
		$address          = isset( $_POST['calp_address'] )          ? stripslashes( $_POST['calp_address'] )       : '';
		$city             = isset( $_POST['calp_city'] )             ? stripslashes( $_POST['calp_city'] )          : '';
		$province         = isset( $_POST['calp_province'] )         ? stripslashes( $_POST['calp_province'] )      : '';
		$postal_code      = isset( $_POST['calp_postal_code'] )      ? stripslashes( $_POST['calp_postal_code'] )   : '';
		$country          = isset( $_POST['calp_country'] )          ? stripslashes( $_POST['calp_country'] )       : '';
		$google_map       = isset( $_POST['calp_google_map'] )       ? 1                                             : 0;
		$rrule = null;

		// =================================
		// = Repeating event, assing rrule =
		// =================================
		if( isset( $_POST['calp_repeat'] ) )
			$rrule = $_POST['calp_rrule'];

		$is_new = false;
		$event 	= null;
		try {
			$event = new Calp_Event( $post_id ? $post_id : null );
		} catch( Calp_Event_Not_Found $e ) {
			// Post exists, but event data hasn't been saved yet. Create new event
			// object.
			$is_new = true;
			$event = new Calp_Event();
			$event->post_id = $post_id;
		}
        
        $event->start               = $calp_events_helper->local_to_gmt( $start_time );
		$event->end                 = $calp_events_helper->local_to_gmt( $end_time );
		$event->allday              = $all_day;
		$event->venue               = $venue;
		$event->address             = $address;
		$event->city                = $city;
		$event->province            = $province;
		$event->postal_code         = $postal_code;
		$event->country             = $country;
		$event->show_map            = $google_map;
		$event->recurrence_rules    = $rrule;
		$event->save( ! $is_new );

		$calp_events_helper->delete_event_cache( $post_id );
		$calp_events_helper->cache_event( $event );
		return;
	}

	/**
	 * post_updated_messages function
	 *
	 * Filter success messages returned by WordPress when an event post is
	 * updated/saved.
	 */
	function post_updated_messages( $messages )
	{
		global $post, $post_ID;

		$messages[CALP_POST_TYPE] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Event updated. <a href="%s">View event</a>', CALP_PLUGIN_NAME ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', CALP_PLUGIN_NAME ),
			3 => __( 'Custom field deleted.', CALP_PLUGIN_NAME ),
			4 => __( 'Event updated.', CALP_PLUGIN_NAME ),
			/* translators: %s: date and time of the revision */
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s', CALP_PLUGIN_NAME ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Event published. <a href="%s">View event</a>', CALP_PLUGIN_NAME ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Event saved.' ),
			8 => sprintf( __( 'Event submitted. <a target="_blank" href="%s">Preview event</a>', CALP_PLUGIN_NAME ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', CALP_PLUGIN_NAME ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', CALP_PLUGIN_NAME ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Event draft updated. <a target="_blank" href="%s">Preview event</a>', CALP_PLUGIN_NAME ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

	/**
	 * event_excerpt function
	 *
	 * Overrides what wp_trim_excerpt() returned if the post is an event,
	 * and outputs better rich-text (but not too rich) excerpt instead.
	 *
	 * @return void
	 **/
	function event_excerpt( $text )
 	{
		global $calp_view_helper,
		       $calp_events_helper;

		if( get_post_type() != CALP_POST_TYPE )
			return $text;

		$event = new Calp_Event( get_the_ID() );

		ob_start();

		$this->excerpt_view( $event );

		// Re-apply any filters to the post content that normally would have been
		// applied if it weren't for our interference (below).
		echo
		 	shortcode_unautop( wpautop(
				$calp_events_helper->trim_excerpt( $event->post->post_content )
			) );

		$page_content = ob_get_contents();
		ob_end_clean();

		return $page_content;
	}

	/**
	 * event_excerpt_noautop function
	 *
	 * Conditionally apply wpautop() filter to content, only if it is not an
	 * event.
	 *
	 * @return void
	 **/
	function event_excerpt_noautop( $content )
	{
		if( get_post_type() != CALP_POST_TYPE )
			return wpautop( $content );
		return $content;
	}

	/**
	 * excerpt_view function
	 *
	 * Outputs event-specific details as HTML to be prepended to post content
	 * when displayed in an excerpt format.
	 *
	 * @param Calp_Event $event  The event being displayed
	 *
	 * @return void
	 **/
	function excerpt_view( &$event )
	{
		global $calp_view_helper,
		       $calp_calendar_helper;

		$location = str_replace( "\n", ', ', rtrim( $event->location ) );

		$args = array(
			'event' => &$event,
			'location' => $location,
		);
		$calp_view_helper->display( 'event-excerpt.php', $args );
	}

	/**
	 * get_map_view function
	 *
	 * Returns HTML markup displaying a Google map of the given event, if the event
	 * has show_map set to true. Returns a zero-length string otherwise.
	 *
	 * @return void
	 **/
	function get_map_view( &$event )
	{
		global $calp_view_helper, $calp_events_helper, $calp_settings;

		if( ! $event->show_map )
			return '';

		$args = array(
			'address' => $event->address,
			'gmap_url_link' => $calp_events_helper->get_gmap_url( $event, false )
		);
		return $calp_view_helper->get_view( 'event-map.php', $args );
	}

	/**
	 * events_categories_add_form_fields function
	 *
	 *
	 *
	 * @return void
	 **/
	 function events_categories_add_form_fields() {
		global $calp_view_helper;

		$args = array( 'edit' => false );
		$calp_view_helper->display( 'event_categories-color_picker.php', $args );
	 }

	 /**
 	 * events_categories_edit_form_fields function
 	 *
 	 *
 	 *
 	 * @return void
 	 **/
 	 function events_categories_edit_form_fields( $term ) {
		global $calp_view_helper, $wpdb;

		$table_name = $wpdb->prefix . 'calp_event_category_colors';
		$color      = $wpdb->get_var( "SELECT term_color FROM {$table_name} WHERE term_id = {$term->term_id}" );

		$style = '';
		$clr   = '';

		if( ! is_null( $color ) && ! empty( $color ) ) {
			$style = 'style="background-color: ' . $color . '"';
			$clr = $color;
		}
		$args = array(
			'style' => $style,
			'color' => $clr,
			'edit'  => true,
		);
		$calp_view_helper->display( 'event_categories-color_picker.php', $args );
	}

	 /**
	  * edited_events_categories function
	  *
	  *
	  *
	  * @return void
	  **/
	function created_events_categories( $term_id ) {
	  global $wpdb;
	  $tag_color_value = '';
	  if( isset( $_POST["tag-color-value"] ) && ! empty( $_POST["tag-color-value"] ) ) {
	    $tag_color_value = $_POST["tag-color-value"];
	  }

	  $table_name = $wpdb->prefix . 'calp_event_category_colors';
	  $wpdb->insert( $table_name, array( 'term_id' => $term_id, 'term_color' => $tag_color_value ), array( '%d', '%s' ) );
	}

	function edited_events_categories( $term_id ) {
	  global $wpdb;
	  $tag_color_value = '';
	  if( isset( $_POST["tag-color-value"] ) && ! empty( $_POST["tag-color-value"] ) ) {
	    $tag_color_value = $_POST["tag-color-value"];
	  }

	  $table_name = $wpdb->prefix . 'calp_event_category_colors';
	  $term = $wpdb->get_var( "SELECT term_id FROM {$table_name} WHERE term_id = {$term_id}" );

	  if( is_null( $term ) ) {
	    // term doesn't exist, create it
	    $wpdb->insert( $table_name, array( 'term_id' => $term_id, 'term_color' => $tag_color_value ), array( '%d', '%s' ) );
	  } else {
	    // term exist, update it
	    $wpdb->update( $table_name, array( 'term_color' => $tag_color_value ), array( 'term_id' => $term_id ), array( '%s' ), array( '%d' ) );
	  }


	}
}
// END class
