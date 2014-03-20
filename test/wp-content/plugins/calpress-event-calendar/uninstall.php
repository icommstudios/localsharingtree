<?php
//
//  uninstall.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//

// plugin bootstrap
require_once( dirname( __FILE__ ) . '/calpress.php' );

/**
 * remove_taxonomy function
 *
 * Remove a taxonomy
 *
 * @return void
 **/
function remove_taxonomy( $taxonomy ) {
  global $wp_taxonomies, $calp_app_helper;
  
  // add event categories and event tags taxonomies
  // if missing
  if( ! taxonomy_exists( $taxonomy ) ) {
    $calp_app_helper->create_post_type();
  }
  
  // get all terms in $taxonomy
	$terms = get_terms( $taxonomy );
	
	// delete all terms in $taxonomy
	foreach( $terms as $term ) {
		wp_delete_term( $term->term_id, $taxonomy );
	}
	
	// deregister $taxonomy
	unset( $wp_taxonomies[$taxonomy] );
	
	// do we need to flush the rewrite rules? 
  $GLOBALS['wp_rewrite']->flush_rules();
}

// ====================================================================
// = Trigger Uninstall process only if WP_UNINSTALL_PLUGIN is defined =
// ====================================================================
if( defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  global $wpdb;
  
  // Delete event categories taxonomy
  remove_taxonomy( 'events_categories' );
  
  // Delete event tags taxonomy
  remove_taxonomy( 'events_tags' );

  // Delete db version
  delete_option( 'calp_db_version' );
  
  // Delete cron version
  delete_option( 'calp_cron_version' );
  
  // Delete settings
  delete_option( 'calp_settings' );
  
  // Delete scheduled cron
  wp_clear_scheduled_hook( 'calp_cron' );
  
  // Delete events
  $table_name = $wpdb->prefix . 'calp_events';
  $query = "SELECT DISTINCT post_id FROM $table_name";
  foreach( $wpdb->get_col( $query ) as $postid ) {
    wp_delete_post( (int) $postid, true );
  }
  
  // Delete table events
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
  
  // Delete table event instances
  $table_name = $wpdb->prefix . 'calp_event_instances';
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
  
  // Delete table event feeds
  $table_name = $wpdb->prefix . 'calp_event_feeds';
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
  
  // Delete table category colors
  $table_name = $wpdb->prefix . 'calp_event_category_colors';
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
