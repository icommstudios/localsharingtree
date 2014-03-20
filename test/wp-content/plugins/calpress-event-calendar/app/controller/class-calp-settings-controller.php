<?php
//
//  class-calp-settings-controller.php
//  Create by the Calpress Team on 2012-03-01.

/**
 * Calp_Settings_Controller class
 *
 * @package Controllers
 * @author Calpress
 **/
class Calp_Settings_Controller {
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
	 * view function
	 *
	 * Display this plugin's settings page in the admin.
	 *
	 * @return void
	 **/
	function view() {
		global $calp_view_helper,
					 $calp_settings;

		if( isset( $_REQUEST['calp_save_settings'] ) ) {
			$this->save();
		}
		$args = array(
			'settings_page'            => $calp_settings->settings_page
		);
		$calp_view_helper->display( 'settings.php', $args );
	}
	
	/**
	 * view function
	 *
	 * Display this plugin's settings page in the admin.
	 *
	 * @return void
	 **/
	function addons_view() {
		global $calp_view_helper,
					 $calp_settings;

		$addons_url = CALP_API_SITE_URL . 'products.php?free=123';

		if ( extension_loaded('curl') ) {
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, $addons_url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        $addons = curl_exec( $ch );
	    } else {
	        $addons = file_get_contents( $addons_url );
	    }

		$addons = json_decode($addons);

		if ( $addons ) {
			$addons = array_reverse($addons);
			foreach ($addons as &$item) {
			    $item->title 	= urldecode($item->title);
	    		$item->image 	= urldecode($item->image);
	    		$item->content 	= urldecode($item->content);
	    		$item->url 		= urldecode($item->url);
	    		$item->price 	= urldecode($item->price);
	    		$item->alert 	= urldecode($item->alert);
	    		$item->button 	= urldecode($item->button);
			}
		}

		$args = array(
			'settings_page'         => $calp_settings->settings_page,
			'addons'				=> $addons,
		);
		$calp_view_helper->display( 'addons.php', $args );
	}

	/**
	 * save function
	 *
	 * Save the submitted settings form.
	 *
	 * @return void
	 **/
	function save() {
		global $calp_settings,
					 $calp_view_helper;

		$calp_settings->update( $_REQUEST );
		$calp_settings->save();

		$args = array(
			"msg" => __( "Settings Updated.", CALP_PLUGIN_NAME )
		);

		$calp_view_helper->display( "save_successful.php", $args );
	}

	/**
	 * add_ics_feed function
	 *
	 * Adds submitted ics feed to the database
	 *
	 * @return string JSON output
	 **/
	function add_ics_feed() {
		global $calp_view_helper,
					 $wpdb;

		$table_name = $wpdb->prefix . 'calp_event_feeds';

		$wpdb->insert(
			$table_name,
			array(
				'feed_url' 		=> str_replace(' ','',$_REQUEST["feed_url"]),    // convert webcal to http
				'feed_category' => $_REQUEST["feed_category"],
				'feed_tags'		=> $_REQUEST["feed_tags"],
			),
			array(
				'%s',
				'%d',
				'%s'
			)
		);
		$feed_id = $wpdb->insert_id;
		ob_start();
		$feed_category = get_term( $_REQUEST["feed_category"], 'events_categories' );
		$args = array(
			'feed_url' 			=> str_replace(' ','',$_REQUEST["feed_url"]),
			'event_category' 	=> $feed_category->name,
			'tags'				=> $_REQUEST["feed_tags"],
			'feed_id'			=> $feed_id,
			'events'         	=> 0
		);
		// display added feed row
		$calp_view_helper->display( 'feed_row.php', $args );

		$output = ob_get_contents();
		ob_end_clean();

		$output = array(
			"error" 	=> 0,
			"message"	=> stripslashes( $output )
		);

		echo json_encode( $output );
		exit();
	}

	/**
	 * flush_ics_feed function
	 *
	 * Deletes all event posts that are from that selected feed
	 *
	 * @param bool $ajax When set to true, the data is outputted using json_response
	 * @param bool|string $feed_url Feed URL
	 *
	 * @return void
	 **/
	function flush_ics_feed( $ajax = true, $feed_url = false )
	{
		global $wpdb,
		       $calp_view_helper;
		$ics_id = isset( $_REQUEST['ics_id'] ) ? (int) $_REQUEST['ics_id'] : 0;
		$table_name = $wpdb->prefix . 'calp_event_feeds';

		if( $feed_url === false )
		  $feed_url = $wpdb->get_var( $wpdb->prepare( "SELECT feed_url FROM $table_name WHERE feed_id = %d", $ics_id ) );

		if( $feed_url )
		{
			$table_name = $wpdb->prefix . 'calp_events';
			$sql = "SELECT post_id FROM {$table_name} WHERE ical_feed_url = '%s'";
			$events = $wpdb->get_results( $wpdb->prepare( $sql, $feed_url ) );
			$total = count( $events );

			foreach( $events as $event ) {
				// delete post (this will trigger deletion of cached events, and remove the event from events table)
				wp_delete_post( $event->post_id, 'true' );
			}

			$output = array(
				'error' 	=> false,
				'message'	=> sprintf( __( 'Flushed %d events', CALP_PLUGIN_NAME ), $total ),
				'count'   => $total,
			);
		}
		else
		{
			$output = array(
				'error' 	=> true,
				'message'	=> __( 'Invalid ICS feed ID', CALP_PLUGIN_NAME )
			);
		}

		if( $ajax )
			$calp_view_helper->json_response( $output );
	}

	/**
	 * update_ics_feed function
	 *
	 * Imports the selected iCalendar feed
	 *
	 * @return void
	 **/
	function update_ics_feed()
	{
		global $wpdb,
		       $calp_view_helper,
		       $calp_importer_helper;

		$feed_id = (int) $_REQUEST['ics_id'];
		$table_name = $wpdb->prefix . 'calp_event_feeds';
		$feed = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE feed_id = %d", $feed_id ) );

		if( $feed )
		{
			// flush the feed
			$this->flush_ics_feed( false, $feed->feed_url );
			// reimport the feed
			$count = @$calp_importer_helper->parse_ics_feed( $feed );
			if ( $count == 0 ) {
				// If results are 0, it could be result of a bad URL or other error, send a specific message
				$output = array(
					'error' 	=> true,
					'message'	=> __( 'No events were found', CALP_PLUGIN_NAME )
				);
			} else {
				$output = array(
					'error'       => false,
					'message'     => sprintf( __( 'Imported %d events', CALP_PLUGIN_NAME ), $count ),
					'flush_label' => sprintf( _n( 'Flush 1 event', 'Flush %s events', $count, CALP_PLUGIN_NAME ), $count ),
					'count'       => $count,
				);
			}
		}
		else
		{
			$output = array(
				'error' 	=> true,
				'message'	=> __( 'Invalid ICS feed ID', CALP_PLUGIN_NAME )
			);
		}

		$calp_view_helper->json_response( $output );
	}

	/**
	 * delete_ics_feed function
	 *
	 * Deletes submitted ics feed id from the database
	 *
	 * @return String JSON output
	 **/
	function delete_ics_feed()
	{
		global $wpdb,
		       $calp_view_helper;

		$ics_id = (int) $_REQUEST['ics_id'];
		$table_name = $wpdb->prefix . 'calp_event_feeds';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE feed_id = %d", $ics_id ) );
		$output = array(
			'error' 	=> false,
			'message'	=> 'Request successful.'
		);

		$calp_view_helper->json_response( $output );
	}

	/**
	 * add_meta_boxes function
	 *
	 *
	 *
	 * @return void
	 **/
	function add_meta_boxes() {
	  global $calp_settings_helper,
	         $calp_settings;
		
	  /* Add the 'General Settings' meta box. */
    add_meta_box( 'general-settings',
                  _x( 'General Settings', 'meta box', CALP_PLUGIN_NAME ),
                  array( &$calp_settings_helper, 'general_settings_meta_box' ),
                  $calp_settings->settings_page,
                  'left-side',
                  'default' );
    /* Add the 'ICS Import Settings' meta box. */
    add_meta_box( 'ics-import-settings',
                  _x( 'ICS Import Settings', 'meta box', CALP_PLUGIN_NAME ),
                  array( &$calp_settings_helper, 'ics_import_settings_meta_box' ),
                  $calp_settings->settings_page,
                  'left-side',
                  'default' );

	}

	/**
	 * admin_enqueue_scripts function
	 *
	 * Enqueue any scripts and styles in the admin side, depending on context.
	 *
	 * @return void
	 **/
	function admin_enqueue_scripts( $hook_suffix ) {
		global $calp_settings;

		if( $hook_suffix == 'widgets.php' ) {
			// Scripts
			wp_enqueue_script( 'calp-widget',     CALP_JS_URL  . '/widget.js',           array( 'jquery' ) );
			// Styles
			wp_enqueue_style(  'calp-widget-css', CALP_CSS_URL . '/widget.css' );
		}

		if( isset( $calp_settings->settings_page ) && $hook_suffix == $calp_settings->settings_page ) {
			// Scripts
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
			
			wp_enqueue_script( 'calp-settings', CALP_JS_URL . '/settings.js', array( 'jquery' ) );
			
			wp_localize_script( 'calp-settings', 'calp_settings', array(
					'page' => $calp_settings->settings_page,
				) );
			// Styles
			wp_enqueue_style(  'calp-widget', CALP_CSS_URL . '/settings.css' );
		}
	}

	/**
	 * plugin_action_links function
	 *
	 * Adds a link to Settings page in plugin list page
	 *
	 * @return array
	 **/
	function plugin_action_links( $links ) {
    $settings = sprintf( __( '<a href="%s">Settings</a>', CALP_PLUGIN_NAME ), admin_url( 'edit.php?post_type=' . CALP_POST_TYPE . '&page=' . CALP_PLUGIN_NAME . '-settings' ) );
    array_unshift( $links, $settings );
    return $links;
	}

	/**
	 * plugin_row_meta function
	 *
	 *
	 *
	 * @return void
	 **/
	function plugin_row_meta( $links, $file ) {
        return $links;
	}
}
// END class
