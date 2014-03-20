<?php
//
//  class-calp-app-controller.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//


/**
 * Calp_App_Controller class
 *
 * @package Controllers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_App_Controller {
	/**
	 * _instance class variable
	 *
	 * Class instance
	 *
	 * @var null | object
	 **/
	private static $_instance = NULL;

	/**
	 * _load_domain class variable
	 *
	 * Load domain
	 *
	 * @var bool
	 **/
	private static $_load_domain = FALSE;

	/**
	 * page_content class variable
	 *
	 * String storing page content for output by the_content()
	 *
	 * @var null | string
	 **/
	private $page_content = NULL;

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
	 * Default constructor - application initialization
	 **/
	private function __construct()
 	{
		global $wpdb,
		       $calp_app_helper,
		       $calp_events_controller,
		       $calp_events_helper,
		       $calp_importer_controller,
		       $calp_settings_controller,
		       $calp_settings;

		// register_activation_hook
		register_activation_hook( CALP_PLUGIN_NAME . '/' . CALP_PLUGIN_NAME . '.php', array( &$this, 'activation_hook' ) );

		// Configure MySQL to operate in GMT time
		$wpdb->query( "SET time_zone = '+0:00'" );

		// Load plugin text domain
		$this->load_textdomain();

		// Install/update database schema as necessary
		$this->install_schema();

		// Install/update cron as necessary
		$this->install_cron();

		// ===========
		// = ACTIONS =
		// ===========
		// Create custom post type
		add_action( 'init', 											              array( &$calp_app_helper, 'create_post_type' ) );
		// Handle ICS export requests
		add_action( 'init', 											              array( &$this, 'parse_standalone_request' ) );
		// General initialization
		add_action( 'init',                                     array( &$calp_events_controller, 'init' ) );
		// Load plugin text domain
		add_action( 'init',                                     array( &$this, 'load_textdomain' ) );
		// Register The Event Calendar importer
		add_action( 'admin_init',                               array( &$calp_importer_controller, 'register_importer' ) );
		// add content for our custom columns
		add_action( 'manage_posts_custom_column',               array( &$calp_app_helper, 'custom_columns' ), 10, 2 );
		// Add filtering dropdowns for event categories and tags
		add_action( 'restrict_manage_posts',                    array( &$calp_app_helper, 'taxonomy_filter_restrict_manage_posts' ) );
		// Trigger display of page in front-end depending on request
		add_action( 'template_redirect',                        array( &$this, 'route_request' ) );
		// Add meta boxes to event creation/edit form
		add_action( 'add_meta_boxes',                           array( &$calp_app_helper, 'add_meta_boxes' ) );
		add_filter( 'screen_layout_columns',                    array( &$calp_app_helper, 'screen_layout_columns' ), 10, 2 );
		// Save event data when post is saved
		add_action( 'save_post',                                array( &$calp_events_controller, 'save_post' ), 10, 2 );
		// Delete event data when post is deleted
		add_action( 'delete_post',                              array( &$calp_events_controller, 'delete_post' ) );
		// Cron job hook
		add_action( 'calp_cron',                                 array( &$calp_importer_controller, 'cron' ) );
		// Category colors
		add_action( 'events_categories_add_form_fields',        array( &$calp_events_controller, 'events_categories_add_form_fields' ) );
		add_action( 'events_categories_edit_form_fields',       array( &$calp_events_controller, 'events_categories_edit_form_fields' ) );
		add_action( 'created_events_categories',                array( &$calp_events_controller, 'created_events_categories' ) );
		add_action( 'edited_events_categories',                 array( &$calp_events_controller, 'edited_events_categories' ) );
		add_action( 'admin_notices',                            array( &$calp_app_helper, 'admin_notices' ) );
		// Scripts/styles for settings/widget screens
		add_action( 'admin_enqueue_scripts',                    array( &$calp_settings_controller, 'admin_enqueue_scripts' ) );
		// Widgets
		add_action( 'widgets_init',                             create_function( '', "return register_widget( 'Calp_Agenda_Widget' );" ) );

		// ===========
		// = FILTERS =
		// ===========
		add_filter( 'posts_join',                    				    array( &$calp_app_helper, 'join' ), 10, 2 );
        add_filter( 'posts_fields',                    				    array( &$calp_app_helper, 'feilds' ), 10, 2 );
        add_filter( 'posts_where',                    				    array( &$calp_app_helper, 'where' ), 10, 2 );
        add_filter( 'posts_orderby',                    				array( &$calp_app_helper, 'orderby' ), 10, 2 );
        add_filter( 'posts_groupby',                    				array( &$calp_app_helper, 'groupby' ), 10, 2 );
		// add custom column names and change existing columns
		add_filter( 'manage_calp_event_posts_columns', 				array( &$calp_app_helper, 'change_columns' ) );
		// filter the post lists by custom filters
		add_filter( 'parse_query', 															array( &$calp_app_helper, 'taxonomy_filter_post_type_request' ) );
		// Override excerpt filters for proper event display in excerpt form
		add_filter( 'get_the_excerpt', 													array( &$calp_events_controller, 'event_excerpt' ), 11 );
		add_filter( 'the_excerpt', 															array( &$calp_events_controller, 'event_excerpt_noautop' ), 11 );
		remove_filter( 'the_excerpt', 													'wpautop', 10 );
		// Update event post update messages
		add_filter( 'post_updated_messages',  									array( &$calp_events_controller, 'post_updated_messages' ) );
		// Sort the custom columns
		add_filter( 'manage_edit-calp_event_sortable_columns', array( &$calp_app_helper, 'sortable_columns' ) );
		add_filter( 'map_meta_cap', 														array( &$calp_app_helper, 'map_meta_cap' ), 10, 4 );
		// Inject event categories, only in front-end, depending on setting
		/*if( $calp_settings->inject_categories && ! is_admin() ) {
			add_filter( 'get_terms',                              array( &$calp_app_helper, 'inject_categories' ), 10, 3 );
			add_filter( 'wp_list_categories',                     array( &$calp_app_helper, 'selected_category_link' ), 10, 2 );
		}*/
		// Rewrite event category URLs to point to calendar page
		add_filter( 'term_link',                                array( &$calp_app_helper, 'calendar_term_link' ), 10, 3 );
		// add a link to settings page on the plugin list page
		add_filter( 'plugin_action_links_' . CALP_PLUGIN_BASENAME, array( &$calp_settings_controller, 'plugin_action_links' ) );
		// add a link to donate page on plugin list page
		add_filter( 'plugin_row_meta',                          array( &$calp_settings_controller, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'post_type_link',                           array( &$calp_events_helper, 'post_type_link' ), 10, 3 );

		// ========
		// = AJAX =
		// ========
		// Add iCalendar feed
		add_action( 'wp_ajax_calp_add_ics',    array( &$calp_settings_controller, 'add_ics_feed' ) );
		// Delete iCalendar feed
		add_action( 'wp_ajax_calp_delete_ics', array( &$calp_settings_controller, 'delete_ics_feed' ) );
		// Flush iCalendar feed
		add_action( 'wp_ajax_calp_flush_ics',  array( &$calp_settings_controller, 'flush_ics_feed' ) );
		// Update iCalendar feed
		add_action( 'wp_ajax_calp_update_ics', array( &$calp_settings_controller, 'update_ics_feed' ) );
		
		// RRule to Text
		add_action( 'wp_ajax_calp_rrule_to_text', array( &$calp_events_helper, 'convert_rrule_to_text' ) );

		// ==============
		// = Shortcodes =
		// ==============
        add_shortcode( 'calpress',             array( &$this, 'calpress_shortcode' ) );
	}

	/**
	 * activation_hook function
	 *
	 * This function is called when activating the plugin
	 *
	 * @return void
	 **/
	function activation_hook() {

	  // load plugin text domain
	  $this->load_textdomain();

	  // flush rewrite rules
	  $this->rewrite_flush();
	}

	/**
	 * load_textdomain function
	 *
	 * Loads plugin text domain
	 *
	 * @return void
	 **/
	function load_textdomain() {
	  if( self::$_load_domain === FALSE ) {
	    load_plugin_textdomain( CALP_PLUGIN_NAME, false, CALP_LANGUAGE_PATH );
	    self::$_load_domain = TRUE;

	  }
	}

	/**
	 * rewrite_flush function
	 *
	 * Get permalinks to work when activating the plugin
	 *
	 * @return void
	 **/
	function rewrite_flush() {
		global $calp_app_helper;
		$calp_app_helper->create_post_type();
		flush_rewrite_rules( true );
	}

	/**
	 * install_schema function
	 *
	 * This function sets up the database, and upgrades it if it is out of date.
	 *
	 * @return void
	 **/
	function install_schema() {
		global $wpdb;

		// If existing DB version is not consistent with current plugin's version,
		// or does not exist, then create/update table structure using dbDelta().
		if( get_option( 'calp_db_version' ) != CALP_DB_VERSION )
		{
			error_reporting(E_ERROR);
			// =======================
			// = Create table events =
			// =======================
			$table_name = $wpdb->prefix . 'calp_events';
			$sql = "CREATE TABLE $table_name (
					post_id 			    bigint(20) NOT NULL,
					start 				    datetime NOT NULL,
					end 				    datetime,
					allday 				    tinyint(1) NOT NULL,
					recurrence_rules 	    longtext,
					exception_rules 	    longtext,
					recurrence_dates 	    longtext,
					exception_dates 	    longtext,
					venue 				    varchar(255),
					country 			    varchar(255),
					address 			    varchar(255),
					city 				    varchar(255),
					province 			    varchar(255),
					postal_code 		    varchar(32),
					show_map 			    tinyint(1),
					ical_feed_url 		    varchar(255),
					ical_source_url 	    varchar(255),
					ical_organizer 		    varchar(255),
					ical_contact 		    varchar(255),
					ical_uid 			    varchar(255),
					PRIMARY KEY  (post_id)
				) CHARACTER SET utf8;";

			// ==========================
			// = Create table instances =
			// ==========================
			$table_name = $wpdb->prefix . 'calp_event_instances';
			$sql .= "CREATE TABLE $table_name (
					id 			bigint(20) NOT NULL AUTO_INCREMENT,
					post_id     bigint(20) NOT NULL,
					start 	    datetime NOT NULL,
					end 		datetime NOT NULL,
					PRIMARY KEY  (id)
				) CHARACTER SET utf8;";

			// ======================
			// = Create table feeds =
			// ======================
			$table_name = $wpdb->prefix . 'calp_event_feeds';
			$sql .= "CREATE TABLE $table_name (
					`feed_id`       bigint(20) NOT NULL AUTO_INCREMENT,
					`feed_url`      varchar(255) NOT NULL,
					`feed_category` bigint(20) NOT NULL,
					`feed_tags`     varchar(255) NOT NULL,
					PRIMARY KEY  (feed_id)
				) CHARACTER SET utf8;";

            // ================================
			// = Create table category colors =
			// ================================
			$table_name = $wpdb->prefix . 'calp_event_category_colors';
			$sql .= "CREATE TABLE $table_name (
                    term_id     bigint(20) NOT NULL,
                    term_color  varchar(255) NOT NULL,
					PRIMARY KEY  (term_id)
				) CHARACTER SET utf8;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
            
            // ==================================
			// = Getting events from all-in-one =
			// ==================================
            $table_name_a = $wpdb->prefix . 'ai1ec_events';
            $table_name_b = $wpdb->prefix . 'calp_events';
            $database = DB_NAME;
            $sql = "SELECT COUNT(*) AS count 
                FROM information_schema.tables 
                WHERE table_name = '$table_name_a' AND table_schema = '$database'";
            
            $ai1ec_events = ( $wpdb->get_var( $sql ) > 0 ) ? true: false;
            if ( $ai1ec_events ) {
                $sql = "INSERT IGNORE INTO $table_name_b
                    (post_id,start,end,allday,recurrence_rules,exception_rules,recurrence_dates,exception_dates,
                    venue,country,address,city,province,postal_code,show_map, ical_feed_url, ical_source_url, ical_organizer, ical_contact, ical_uid)
                SELECT post_id,start,end,allday,recurrence_rules,exception_rules,recurrence_dates,exception_dates,
                    venue,country,address,city,province,postal_code,show_map, ical_feed_url,ical_source_url,ical_organizer,ical_contact,ical_uid
                FROM $table_name_a";
                $wpdb->query($sql);
            }
            
            $table_name_a = $wpdb->prefix . 'ai1ec_event_instances';
            $table_name_b = $wpdb->prefix . 'calp_event_instances';
            
            $sql = "SELECT COUNT(*) AS count 
                FROM information_schema.tables 
                WHERE table_name = '$table_name_a' AND table_schema = '$database'";
            
            $ai1ec_event_instances = ( $wpdb->get_var( $sql ) > 0 ) ? true: false;
            if ( $ai1ec_event_instances ) {
                $sql = "INSERT IGNORE INTO $table_name_b
                    (id, post_id, start, end)
                    SELECT id, post_id, start, end
                FROM $table_name_a";
                $wpdb->query($sql);
            }
            
            $table_name_a = $wpdb->prefix . 'ai1ec_event_category_colors';
            $table_name_b = $wpdb->prefix . 'calp_event_category_colors';
            
            $sql = "SELECT COUNT(*) AS count 
                FROM information_schema.tables 
                WHERE table_name = '$table_name_a' AND table_schema = '$database'";
            
            $ai1ec_event_category_colors = ( $wpdb->get_var( $sql ) > 0 ) ? true: false;
            if ( $ai1ec_event_category_colors ) {
                $sql = "INSERT IGNORE INTO $table_name_b
                    (term_id, term_color)
                    SELECT term_id, term_color
                FROM $table_name_a";
                $wpdb->query($sql);
            }
            
            $table_name_a = $wpdb->prefix . 'ai1ec_event_feeds';
            $table_name_b = $wpdb->prefix . 'calp_event_feeds';
            
            $sql = "SELECT COUNT(*) AS count 
                FROM information_schema.tables 
                WHERE table_name = '$table_name_a' AND table_schema = '$database'";
            
            $ai1ec_event_feeds = ( $wpdb->get_var( $sql ) > 0 ) ? true: false;
            if ( $ai1ec_event_feeds ) {
                $sql = "INSERT IGNORE INTO $table_name_b
                    (feed_id, feed_url, feed_category, feed_tags)
                    SELECT feed_id, feed_url, feed_category, feed_tags
                FROM $table_name_a";
                $wpdb->query($sql);
            }

            if ( $ai1ec_events ) {
	            $table_name = $wpdb->prefix . 'posts';
	            $sql = "UPDATE $table_name SET
	                post_type = 'calp_event'
	            WHERE post_type = 'ai1ec_event'";
	            $wpdb->query($sql);
	        }

			update_option( 'calp_db_version', CALP_DB_VERSION );
		}
	}

	/**
	 * install_cron function
	 *
	 * This function sets up the cron job for updating the events, and upgrades it if it is out of date.
	 *
	 * @return void
	 **/
	function install_cron() {
		// If existing CRON version is not consistent with current plugin's version,
		// or does not exist, then create/update cron using
		if( get_option( 'calp_cron_version' ) != CALP_CRON_VERSION ) {
			global $calp_settings;
			// delete our scheduled crons
			wp_clear_scheduled_hook( 'calp_cron' );
			// set the new cron
			wp_schedule_event( time(), $calp_settings->cron_freq, 'calp_cron' );
			// update the cron version
			update_option( 'calp_cron_version', CALP_CRON_VERSION );
		}
	}

	/**
	 * setup_menus function
	 * Adds the hook to admin_menu that is pointing to menu member function
	 *
	 * @return void
	 **/
	function setup_menus() {
		add_action( "admin_menu", array( &$this, "menu" ) );
	}

	/**
	 * menu function
	 * Display the admin menu items using the add_menu_page WP function.
	 *
	 * @return void
	 **/
	function menu() {
		global $calp_settings_controller,
		       $calp_settings_helper,
		       $calp_settings;

		// =================
		// = Settings Page =
		// =================
		$calp_settings->settings_page = add_submenu_page(
			'edit.php?post_type=' . CALP_POST_TYPE,
			__( 'Settings', CALP_PLUGIN_NAME ),
			__( 'Settings', CALP_PLUGIN_NAME ),
			'manage_options',
			CALP_PLUGIN_NAME . "-settings",
			array( &$calp_settings_controller, "view" )
		);
		
        // =================
        // = Addons Page =
        // =================
        $calp_settings->addons_page = add_submenu_page(
            'edit.php?post_type=' . CALP_POST_TYPE,
            __( 'Add-Ons', CALP_PLUGIN_NAME ),
            __( 'Add-Ons', CALP_PLUGIN_NAME ),
            'manage_options',
            CALP_PLUGIN_NAME . "-addons",
            array( &$calp_settings_controller, "addons_view" )
        );
		// Create a hook for adding meta boxes
		add_action( "load-{$calp_settings->settings_page}", array( &$calp_settings_helper, 'add_meta_boxes') );
		// Load the meta boxes
		add_action( "load-{$calp_settings->settings_page}", array( &$calp_settings_controller, 'add_meta_boxes' ) );
	}


	/**
	 * route_request function
	 *
	 * Determines if the page viewed should be handled by this plugin, and if so
	 * schedule new content to be displayed.
	 *
	 * @return void
	 **/
	function route_request() {
		global $calp_settings,
		       $calp_calendar_controller,
		       $calp_events_controller;

		// Find out if the calendar page ID is defined, and we're on it
		if( $calp_settings->calendar_page_id &&
		    is_page( $calp_settings->calendar_page_id ) )
		{
		  // Proceed only if the page password is correctly entered OR
		  // the page doesn't require a password
		  if( ! post_password_required( $calp_settings->calendar_page_id ) ) {
		    ob_start();
  			// Render view
  			$calp_calendar_controller->view();
  			// Save page content to local variable
  			$this->page_content = ob_get_contents();
  			ob_end_clean();

  			// Replace page content - make sure it happens at (almost) the very end of
  			// page content filters (some themes are overly ambitious here)
  			add_filter( 'the_content', array( &$this, 'append_content' ), PHP_INT_MAX - 1 );
		  }
		}
	}

	/**
	 * parse_standalone_request function
	 *
	 * @return void
	 **/
	function parse_standalone_request() {
		global $calp_exporter_controller,
	           $calp_app_helper;
			   

    date_default_timezone_set("UTC");
	$plugin     = $calp_app_helper->get_param('plugin');
    $action     = $calp_app_helper->get_param('action');
    $controller = $calp_app_helper->get_param('controller');

		if( ! empty( $plugin ) && $plugin == CALP_PLUGIN_NAME && ! empty( $controller ) && ! empty( $action ) ) {
			if( $controller == "calp_exporter_controller" ) :
			  switch( $action ) :
			    case 'export_events':
			      $calp_exporter_controller->export_events();
			      break;
			  endswitch;
			endif; // calp_exporter_controller
		}
	}

	/**
     * calpress_shortcode function
     *
     * page shortcode function to attach calendar
     */
    function calpress_shortcode( $attrs ) {
        global $post;
        ob_start();
        $this->shortcode_append_content( $post->ID, $attrs );
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

	/**
	 * append_content function
	 *
	 * Append locally generated content to normal page content (if in the loop;
	 * don't want to do it for all instances of the_content() on the page!)
	 *
	 * @param string $content Post/Page content
	 * @return string         Post/Page content
	 **/
	function append_content( $content )
	{
		global $calp_settings, $post;

		update_option( 'calpress_page_id', $post->ID);
		// Enclose entire content (including any admin-provided page content) in
		// the calendar container div
		if( in_the_loop() )
			$content = $content .
				'<div id="calp-fullscreen-cover" class="calp-cover calp-cover-dark" style="display:none;" onclick="CALPFull.toggle()"></div>
                <div id="calp-container" class="calp-container">'
                . $this->page_content .
				'</div>';

		return $content;
	}
    
    /**
	 * shortcode_append_content function
	 *
	 * Append locally generated content to normal page content using shortcode
	 *
	 **/
    function shortcode_append_content( $page_id )
    {
        global $calp_calendar_controller, $wp_filter, $post;

        update_option( 'calpress_page_id', $post->ID);

        if( ! post_password_required( $page_id ) ) {
            // remove custom content filters
            if( isset($wp_filter['the_content']['99']) ) {
                remove_all_filters('the_content', 99);
            }
            ob_start();
            $calp_calendar_controller->view();
            $page_content = ob_get_contents();
            ob_end_clean();
            echo '<div id="calp-fullscreen-cover" class="calp-cover calp-cover-dark" style="display:none;" onclick="CALPFull.toggle()"></div>
                <div id="calp-container" class="calp-container">' .
                $page_content .
            '</div>';
        }
    }
}
// END class
