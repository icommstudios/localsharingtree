<?php
//
//  class-calp-app-helper.php
//  This file was originally created as part of the all-in-one-event-calendar it has been modified by the Calpress team
//
//  Created by The Seed Studio on 2011-07-13.
//  Updated by the Calpress Team on 2012-03-01.
//

/**
 * Calp_App_Helper class
 *
 * @package Helpers
 * @author Calpress (Modified from original work by time.ly)
 **/
class Calp_App_Helper {
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
	 * map_meta_cap function
	 *
	 * Assigns proper capability
	 *
	 * @return void
	 **/
	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		// If editing, deleting, or reading an event, get the post and post type object.
		if( 'edit_calp_event' == $cap || 'delete_calp_event' == $cap || 'read_calp_event' == $cap ) {
			$post = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );
			/* Set an empty array for the caps. */
			$caps = array();
		}

		/* If editing an event, assign the required capability. */
		if( 'edit_calp_event' == $cap ) {
			if( $user_id == $post->post_author )
				$caps[] = $post_type->cap->edit_posts;
			else
				$caps[] = $post_type->cap->edit_others_posts;
		}

		/* If deleting an event, assign the required capability. */
		else if( 'delete_calp_event' == $cap ) {
			if( $user_id == $post->post_author )
				$caps[] = $post_type->cap->delete_posts;
			else
				$caps[] = $post_type->cap->delete_others_posts;
		}

		/* If reading a private event, assign the required capability. */
		else if( 'read_calp_event' == $cap ) {
			if( 'private' != $post->post_status )
				$caps[] = 'read';
			elseif ( $user_id == $post->post_author )
				$caps[] = 'read';
			else
				$caps[] = $post_type->cap->read_private_posts;
		}

		/* Return the capabilities required by the user. */
		return $caps;
	}

	/**
	 * create_post_type function
	 *
	 * Create event's custom post type
	 * and registers events_categories and events_tags under
	 * event's custom post type taxonomy
	 *
	 * @return void
	 **/
	function create_post_type() {
	  global $calp_settings;

    // if the event contributor role is not created, create it
		if( !get_role( 'calp_event_assistant' ) ) {

		  // creating event contributor role with the same capabilities
		  // as subscriber role, later in this file, event contributor role will be extended
		  // to include more capabilities
			$caps = get_role( 'subscriber' )->capabilities;
			add_role( 'calp_event_assistant', 'Event Contributor', $caps );

			// add event managing capability to administrator, editor, author
			foreach( array( 'administrator', 'editor', 'author' ) as $user ) {
			  $role = get_role( $user );
			  // read events
			  $role->add_cap( 'read_calp_event' );
			  // edit events
			  $role->add_cap( 'edit_calp_event' );
			  $role->add_cap( 'edit_calp_events' );
			  $role->add_cap( 'edit_others_calp_events' );
			  $role->add_cap( 'edit_private_calp_events' );
			  $role->add_cap( 'edit_published_calp_events' );
			  // delete events
			  $role->add_cap( 'delete_calp_event' );
			  $role->add_cap( 'delete_calp_events' );
			  $role->add_cap( 'delete_others_calp_events' );
			  $role->add_cap( 'delete_published_calp_events' );
			  $role->add_cap( 'delete_private_calp_events' );
			  // publish events
			  $role->add_cap( 'publish_calp_events' );
			  // read private events
			  $role->add_cap( 'read_private_calp_events' );
			}

			// add event managing capability to contributors
			$role = get_role( 'calp_event_assistant' );
			$role->add_cap( 'edit_calp_events' );
			$role->add_cap( 'delete_calp_event' );
			$role->add_cap( 'read' );
		}
		// ===============================
		// = labels for custom post type =
		// ===============================
		$labels = array(
			'name' 								=> _x( 'Events', 'Custom post type name', CALP_PLUGIN_NAME ),
			'singular_name' 			=> _x( 'Event', 'Custom post type name (singular)', CALP_PLUGIN_NAME ),
			'add_new'							=> __( 'Add New', CALP_PLUGIN_NAME ),
			'add_new_item'				=> __( 'Add New Event', CALP_PLUGIN_NAME ),
			'edit_item'						=> __( 'Edit Event', CALP_PLUGIN_NAME ),
			'new_item'						=> __( 'New Event', CALP_PLUGIN_NAME ),
			'view_item'						=> __( 'View Event', CALP_PLUGIN_NAME ),
			'search_items'				=> __( 'Search Events', CALP_PLUGIN_NAME ),
			'not_found'						=> __( 'No Events found', CALP_PLUGIN_NAME ),
			'not_found_in_trash'	=> __( 'No Events found in Trash', CALP_PLUGIN_NAME ),
			'parent_item_colon'		=> __( 'Parent Event', CALP_PLUGIN_NAME ),
			'menu_name'						=> __( 'CalPress Events', CALP_PLUGIN_NAME ),
			'all_items'						=> $this->get_all_items_name()
		);


		// ================================
		// = support for custom post type =
		// ================================
		$supports = array( 'title', 'editor', 'comments' );

		// =============================
		// = args for custom post type =
		// =============================
		$args = array(
			'labels'							=> $labels,
			'public' 							=> true,
	    'publicly_queryable' 	=> true,
	    'show_ui' 						=> true,
	    'show_in_menu' 				=> true,
	    'query_var' 					=> true,
	    'rewrite' 						=> true,
	    'capability_type'			=> array( 'calp_event', 'calp_events' ),
	    'capabilities'        => array(
	      'read_post'               => 'read_calp_event',
	      'edit_post'               => 'edit_calp_event',
        'edit_posts'              => 'edit_calp_events',
        'edit_others_posts'       => 'edit_others_calp_events',
        'edit_private_posts'      => 'edit_private_calp_events',
        'edit_published_posts'    => 'edit_published_calp_events',
        'delete_post'             => 'delete_calp_event',
        'delete_posts'            => 'delete_calp_events',
        'delete_others_posts'     => 'delete_others_calp_events',
        'delete_published_posts'  => 'delete_published_calp_events',
        'delete_private_posts'    => 'delete_private_calp_events',
        'publish_posts'           => 'publish_calp_events',
        'read_private_posts'      => 'read_private_calp_events' ),
	    'has_archive' 				=> true,
	    'hierarchical' 				=> false,
	    'menu_position' 			=> 5,
	    'supports'						=> $supports
		);

		// ========================================
		// = labels for event categories taxonomy =
		// ========================================
		$events_categories_labels = array(
			'name'					=> _x( 'Event Categories', 'Event categories taxonomy', CALP_PLUGIN_NAME ),
			'singular_name'	=> _x( 'Event Category', 'Event categories taxonomy (singular)', CALP_PLUGIN_NAME )
		);

		// ==================================
		// = labels for event tags taxonomy =
		// ==================================
		$events_tags_labels = array(
			'name'					=> _x( 'Event Tags', 'Event tags taxonomy', CALP_PLUGIN_NAME ),
			'singular_name'	=> _x( 'Event Tag', 'Event tags taxonomy (singular)', CALP_PLUGIN_NAME )
		);

		// ======================================
		// = args for event categories taxonomy =
		// ======================================
		$events_categories_args = array(
			'labels'				=> $events_categories_labels,
			'hierarchical'	=> true,
			'rewrite'				=> array( 'slug' => 'events_categories' ),
			'capabilities'	=> array(
				'manage_terms' => 'edit_calp_events',
    		'edit_terms'   => 'edit_calp_events',
    		'delete_terms' => 'edit_calp_events',
    		'assign_terms' => 'edit_calp_events'
			)
		);

		// ================================
		// = args for event tags taxonomy =
		// ================================
		$events_tags_args = array(
			'labels'				=> $events_tags_labels,
			'hierarchical'	=> false,
			'rewrite'				=> array( 'slug' => 'events_tags' ),
			'capabilities'	=> array(
				'manage_terms' => 'edit_calp_events',
    		'edit_terms'   => 'edit_calp_events',
    		'delete_terms' => 'edit_calp_events',
    		'assign_terms' => 'edit_calp_events'
			)
		);

		// ======================================
		// = register event categories taxonomy =
		// ======================================
		register_taxonomy( 'events_categories', array( CALP_POST_TYPE ), $events_categories_args );

		// ================================
		// = register event tags taxonomy =
		// ================================
		register_taxonomy( 'events_tags', array( CALP_POST_TYPE ), $events_tags_args );

		// ========================================
		// = register custom post type for events =
		// ========================================
		register_post_type( CALP_POST_TYPE, $args );
	}

	/**
	 * taxonomy_filter_restrict_manage_posts function
	 *
	 * Adds filter dropdowns for event categories and event tags
	 *
	 * @return void
	 **/
	function taxonomy_filter_restrict_manage_posts() {
		global $typenow;

		// =============================================
		// = add the dropdowns only on the events page =
		// =============================================
		if( $typenow == CALP_POST_TYPE ) {
			$filters = get_object_taxonomies( $typenow );
			foreach( $filters as $tax_slug ) {
				$tax_obj = get_taxonomy( $tax_slug );
				wp_dropdown_categories( array(
					'show_option_all'	=> __( 'Show All ', CALP_PLUGIN_NAME ) . $tax_obj->label,
					'taxonomy'			=> $tax_slug,
			        'name'				=> $tax_obj->name,
			        'orderby'			=> 'name',
			        'selected'			=> isset( $_GET[$tax_slug] ) ? $_GET[$tax_slug] : '',
			        'hierarchical'		=> $tax_obj->hierarchical,
			        'show_count'		=> true,
			        'hide_if_empty'   	=> true
				));
			}

			// venues hidden element
            $this->venues_field();
		}
	}

	/**
	 * venues_field function
	 *
	 * Create venues hidden element
     * only on events page
	 *
	 * @return string
	 **/
    function venues_field() { ?>
        <input type="hidden" name="events_venues" value="<?php echo $this->get_param('events_venues') ? $this->get_param('events_venues') : 0 ?>" />
    <?php }

	 /**
	 * feilds function
	 *
	 * Add post fields
	 * only on events page
     * 
	 * @return string
	 **/
    function feilds ( $fields ) {
        global $wpdb, $typenow;
        if( $typenow == CALP_POST_TYPE ) {
            $fields .= ', inst.id as instance_id, inst.start as start, e.recurrence_rules as repeated';
        }
        return $fields;
    }

	 /**
	 * join function
	 *
	 * Join events table for filter
	 * only on events page
     * 
	 * @return string
	 **/
    function join( $join ) {
        global $wpdb, $typenow;
        
        if( $typenow == CALP_POST_TYPE ) {
            $table_name_events = $wpdb->prefix . 'calp_events';
            $table_name_posts = $wpdb->prefix . 'posts';
            $table_name_relationships = $wpdb->prefix . 'term_relationships';
            $table_name_taxonomy = $wpdb->prefix . 'term_taxonomy';
            $table_name_instances = $wpdb->prefix . 'calp_event_instances';
            $table_name_attends = $wpdb->prefix . 'calp_event_attends';
            $join = " INNER JOIN $table_name_events e ON $table_name_posts.ID = e.post_id
                    LEFT JOIN $table_name_relationships ON
                        $table_name_relationships.object_id = $table_name_posts.ID
                    LEFT JOIN $table_name_taxonomy tt1 ON
                        tt1.term_taxonomy_id = $table_name_relationships.term_taxonomy_id
                    LEFT JOIN $table_name_instances inst ON
                        inst.post_id = e.post_id";
        }
        return $join;
    }

    /**
	 * where function
	 *
	 * Filter events posts
	 * only on events page
     * 
	 * @return string
	 **/
    function where( $where ) {
        global $typenow;
        
        if( $typenow == CALP_POST_TYPE ) {
            // show events only with selected venue
            if ( $this->get_param('events_venues') ) {
                $venue = $this->get_param('events_venues');
                $where .= " AND e.venue = '$venue' ";
            }
            // show only pending events
            if ( intval($this->get_param('calp_events_filter')) == 2 ) {
                $where .= " AND wp_posts.post_status = 'pending' ";
            }
        }
        return $where;
    }

    /**
	 * groupby function
	 *
	 * Group events posts
	 * only on events page
     * 
	 * @return string
	 **/
    function groupby( $groupby ) {
        global $wpdb, $typenow;
        
        if( $typenow == CALP_POST_TYPE ) {
            $table_name_instances = $wpdb->prefix . 'calp_event_instances';
            $groupby = "inst.id";
        }
        return $groupby;
    }

	/**
	 * get_all_items_name function
	 *
	 * If current user can publish events and there
	 * is at least 1 event pending, append the pending
	 * events number to the menu
	 *
	 * @return string
	 **/
	function get_all_items_name() {

	  // if current user can publish events
	  if( current_user_can( 'publish_calp_events' ) ) {
	    // get all pending events
	    $query = new WP_Query(  array ( 'post_type' => 'calp_event', 'post_status' => 'pending', 'posts_per_page' => -1,  ) );

	    // at least 1 pending event?
      if( $query->post_count > 0 ) {
        // append the pending events number to the menu
        return sprintf( __( 'All Events <span class="update-plugins count-%d" title="%d Pending Events"><span class="update-count">%d</span></span>', CALP_PLUGIN_NAME ),
  	                    $query->post_count, $query->post_count, $query->post_count );
      }
    }

	  // no pending events, or the user doesn't have sufficient capabilities
	  return __( 'All Events', CALP_PLUGIN_NAME );
	}

	/**
	 * taxonomy_filter_post_type_request function
	 *
	 * Adds filtering of events list by event tags and event categories
	 *
	 * @return void
	 **/
	function taxonomy_filter_post_type_request( $query ) {
		global $pagenow, $typenow;
		if( 'edit.php' == $pagenow ) {
			$filters = get_object_taxonomies( $typenow );
			foreach( $filters as $tax_slug ) {
				$var = &$query->query_vars[$tax_slug];
				if( isset( $var ) ) {
				  $term = null;

				  if( is_numeric( $var ) )
					  $term = get_term_by( 'id', $var, $tax_slug );
					else
					  $term = get_term_by( 'slug', $var, $tax_slug );

					if ( $term )
                        $var = $term->slug;
				}
			}
		}
		// ===========================
		// = Order by Event date ASC =
		// ===========================
		if( $typenow == 'calp_event' ) {
			if( ! array_key_exists( 'orderby', $query->query_vars ) ) {
				$query->query_vars["orderby"] = 'calp_event_date';
				$query->query_vars["order"] 	= 'desc';
			}
		}

	}

	/**
	 * orderby function
	 *
	 * Orders events by event date
	 *
	 * @param string $orderby Orderby sql
	 * @param object $wp_query
	 *
	 * @return void
	 **/
	function orderby( $orderby, $wp_query ) {
		global $typenow, $wpdb, $post;

		if( $typenow == 'calp_event' ) {
			$wp_query->query = wp_parse_args( $wp_query->query );
			$table_name = $wpdb->prefix . 'calp_events';
            $order_filter = array( 'calp_event_date', 'calp_venue', 'calp_type' );
            $order_fields = array( 'calp_event_date' => 'start',
                                    'calp_venue' => 'venue',
                                    'calp_type' => 'recurrence_rules' );
            if ( in_array( @$wp_query->query['orderby'], $order_filter ) ) {
				$field = $order_fields[ $wp_query->query['orderby'] ];
                if ( $field == 'start' ) {
                    $orderby = 'inst.start '. $wp_query->get('order');
                } else {
                    $orderby = "(SELECT  $field  FROM {$table_name} WHERE post_id =  $wpdb->posts.ID) " . $wp_query->get('order');
                }
			}
		}
		return $orderby;
	}

	/**
	 * add_meta_boxes function
	 *
	 * Display event meta_box when creating or editing an event
	 *
	 * @return void
	 **/
	function add_meta_boxes() {
		global $calp_events_controller;
		add_meta_box(
		        CALP_POST_TYPE,
		        __( 'Event Details', CALP_PLUGIN_NAME ),
		        array( &$calp_events_controller, 'meta_box_view' ),
		        CALP_POST_TYPE
		    );
	}
	
	/**
	 * screen_layout_columns function
	 *
	 * Since WordPress 2.8 we have to tell, that we support 2 columns!
	 *
	 * @return void
	 **/
	function screen_layout_columns( $columns, $screen ) {
		global $calp_settings;
    
		if( isset( $calp_settings->settings_page ) && $screen == $calp_settings->settings_page )
			$columns[$calp_settings->settings_page] = 2;

		return $columns;
	}

	/**
	 * change_columns function
	 *
	 * Adds Event date/time column to our custom post type
	 * and renames Date column to Post Date
	 *
	 * @param array $columns Existing columns
	 *
	 * @return array Updated columns array
	 **/
	function change_columns( $columns ) {
		        if ( isset( $columns['date'] ) )
            unset( $columns['date'] );
        if ( isset( $columns['comments'] ) )
            unset( $columns['comments'] );
        
		$columns["calp_event_date"] 	= __( 'Start Date', CALP_PLUGIN_NAME );
		$columns["calp_category"] 	    = __( 'Category', CALP_PLUGIN_NAME );
		$columns["calp_type"] 	        = __( 'Type', CALP_PLUGIN_NAME );
        $columns["calp_event_venue"] 	= __( 'Venue', CALP_PLUGIN_NAME );
		$columns["comments"] 			= '<span class="vers"><img alt="Comments" src="'.CALP_SITE_URL.'/wp-admin/images/comment-grey-bubble.png" /></span>';
        $columns["date"] 				= __( 'Post Date', CALP_PLUGIN_NAME );

		return $columns;
	}

	/**
	 * custom_columns function
	 *
	 * Adds content for custom columns
	 *
	 * @return void
	 **/
	function custom_columns( $column, $post_id ) {
		global $calp_events_helper, $wpdb, $post;
		switch( $column ) {
			case 'calp_event_date':
                date_default_timezone_set("UTC");
                $instance = isset($post->instance_id)?$post->instance_id:0;
                $query = "SELECT start " .
                "FROM {$wpdb->prefix}calp_event_instances " .
                "WHERE id = $instance ";
                $start = $wpdb->get_var( $query );
                $timestamp = $calp_events_helper->gmt_to_local( strtotime($start) );
                echo date( 'd F Y',$timestamp );
				break;
			case 'calp_category':
				$e = new Calp_Event( $post_id );
				echo $e->category;
				break;
			case 'calp_event_venue':
				$e = new Calp_Event( $post_id );
				echo $e->venue;
				break;
			case 'calp_type':
				echo empty( $post->repeated )? '<b>One-Time</b>' : '<b>Repeated</b>';
				break;
		}
	}

	/**
	 * sortable_columns function
	 *
	 * Enable sorting of columns
	 *
	 * @return void
	 **/
	function sortable_columns( $columns ) {
		$columns["calp_event_date"] = 'calp_event_date';
		$columns["calp_event_venue"] = 'calp_venue';
        $columns["calp_type"] 	     = 'calp_type';
		return $columns;
	}

	/**
	 * get_param function
	 *
	 * Tries to return the parameter from POST and GET
	 * incase it is missing, default value is returned
	 *
	 * @param string $param Parameter to return
	 * @param mixed $default Default value
	 *
	 * @return mixed
	 **/
	function get_param( $param, $default='' ) {
	  if( isset( $_POST[$param] ) )
	    return $_POST[$param];
	  if( isset( $_GET[$param] ) )
	    return $_GET[$param];
	  return $default;
  }

	/**
	 * get_param_delimiter_char function
	 *
	 * Returns the delimiter character in a link
	 *
	 * @param string $link Link to parse
	 *
	 * @return string
	 **/
  function get_param_delimiter_char( $link ) {
    return strpos( $link, '?' ) === false ? '?' : '&';
	}

  /**
	 * inject_categories function
	 *
	 * Displays event categories whenever post categories are requested
	 *
	 * @param array $terms Terms to be returned by get_terms()
	 * @param array $taxonomies Taxonomies requested in get_terms()
	 * @param array $args Args passed to get_terms()
	 *
	 * @return string|array If "category" taxonomy was requested, then returns
	 *                      $terms with fake category pointing to calendar page
	 *                      with its children being the event categories
	 **/
	function inject_categories( $terms, $taxonomies, $args )
	{
		global $calp_settings;

    if( in_array( 'category', $taxonomies ) )
    {
    	// Create fake calendar page category
    	$count_args = $args;
    	$count_args['fields'] = 'count';
    	$count = get_terms( 'events_categories', $count_args );
    	$post = get_post( $calp_settings->calendar_page_id );
    	switch( $args['fields'] )
    	{
    		case 'all':
		    	$calendar = (object) array(
			    	'term_id'     => CALP_FAKE_CATEGORY_ID,
			    	'name'		    => $post->post_title,
			    	'slug'		    => $post->post_name,
			    	'taxonomy'    => 'events_categories',
			    	'description' => '',
			    	'parent'      => 0,
			    	'count'       => $count,
		    	);
		    	break;
	    	case 'ids':
	    		$calendar = 'calp_calendar';
	    		break;
    		case 'names':
	    		$calendar = $post->post_title;
	    		break;
    	}
    	$terms[] = $calendar;

    	if( $args['hierarchical'] ) {
    		$children = get_terms( 'events_categories', $args );
	    	foreach( $children as &$child ) {
	    		if( is_object( $child ) && $child->parent == 0 )
	    			$child->parent = CALP_FAKE_CATEGORY_ID;
	 				$terms[] = $child;
	    	}
	    }
    }

    return $terms;
  }

  /**
   * function calendar_term_link
   *
   * Corrects the URL for the calendar page when injected into the post
   * categories.
   *
   * @param string $link The normally generated link
   * @param object $term The term that we're getting the link for
   * @param string $taxonomy The name of the taxonomy of interest
   *
   * @return string The correct link to the calendar page
   */
  function calendar_term_link( $link, $term, $taxonomy )
  {
  	global $calp_calendar_helper;

  	if( $taxonomy == 'events_categories' ) {
	  	if( $term->term_id == CALP_FAKE_CATEGORY_ID )
	  		$link = $calp_calendar_helper->get_calendar_url( null );
	  	else
	  		$link = $calp_calendar_helper->get_calendar_url( null,
		  		array( 'cat_ids' => array( $term->term_id ) )
		  	);
	  }

  	return $link;
  }

  /**
   * function selected_category_link
   *
   * Corrects the output of wp_list_categories so that the currently viewed
   * event category (in calendar view) has the "active" CSS class applied to it.
   *
   * @param string $output The normally generated output of wp_list_categories()
   * @param object $args The args passed to wp_list_categories()
   *
   * @return string The corrected output
   */
  function selected_category_link( $output, $args )
  {
  	global $calp_calendar_controller, $calp_settings;

  	// First check if current page is calendar
  	if( is_page( $calp_settings->calendar_page_id ) )
  	{
	  	$cat_ids = array_filter( explode( ',', $calp_calendar_controller->get_requested_categories() ), 'is_numeric' );
	  	if( $cat_ids ) {
	  		// Mark each filtered event category link as selected
		  	foreach( $cat_ids as $cat_id ) {
		  		$output = str_replace(
			  		'class="cat-item cat-item-' . $cat_id . '"',
			  		'class="cat-item cat-item-' . $cat_id . ' current-cat current_page_item"',
			  		$output );
		  	}
		  	// Mark calendar page link as selected parent
		  	$output = str_replace(
			  	'class="cat-item cat-item-' . CALP_FAKE_CATEGORY_ID . '"',
			  	'class="cat-item cat-item-' . CALP_FAKE_CATEGORY_ID . ' current-cat-parent"',
			  	$output );
		  } else {
		  	// No categories filtered, so mark calendar page link as selected
		  	$output = str_replace(
			  	'class="cat-item cat-item-' . CALP_FAKE_CATEGORY_ID . '"',
			  	'class="cat-item cat-item-' . CALP_FAKE_CATEGORY_ID . ' current-cat current_page_item"',
			  	$output );
	  	}
	  }

  	return $output;
  }

  /**
   * admin_notices function
   *
   * Notify the user about anything special.
   *
   * @return void
   **/
  function admin_notices() {
    global $calp_view_helper,
           $calp_settings,
           $plugin_page;

    // If calendar page ID has not been set, and we're not updating the settings
    // page, the calendar is not properly set up yet
    if( ! $calp_settings->calendar_page_id || ! get_option( 'timezone_string' ) && ! isset( $_REQUEST['calp_save_settings'] ) )
    {
    	$args = array();

    	// Display messages for blog admin
    	if( current_user_can( 'manage_options' ) ) {
	    	// If not on the settings page already, direct user there with a message
	    	if( $plugin_page == CALP_PLUGIN_NAME . "-settings" ) {
	    	  if( ! $calp_settings->calendar_page_id && ! get_option( 'timezone_string' ) )
					  $args['msg'] = sprintf( __( '%sTo set up the plugin: %s 1. Select an option in the <strong>Calendar page</strong> dropdown list. %s 2. Select an option in the <strong>Timezone</strong> dropdown list. %s 3. Click <strong>Update Settings</strong>. %s', CALP_PLUGIN_NAME ), '<br /><br />', '<ul><ol>', '</ol><ol>', '</ol><ol>', '</ol><ul>' );
					else if( ! $calp_settings->calendar_page_id )
					  $args['msg'] = __( 'To set up the plugin: Select an option in the <strong>Calendar page</strong> dropdown list, the click <strong>Update Settings</strong>.', CALP_PLUGIN_NAME );
					else
					  $args['msg'] = __( 'To set up the plugin: Select an option in the <strong>Timezone</strong> dropdown list, the click <strong>Update Settings</strong>.', CALP_PLUGIN_NAME );
				// Else instruct user as to what to do on the settings page
				} else {
		      $args['msg'] = sprintf(
			        __( 'The plugin is installed, but has not been configured. <a href="%s">Click here to set it up now »</a>', CALP_PLUGIN_NAME ),
							admin_url( 'edit.php?post_type=' . CALP_POST_TYPE . '&page=' . CALP_PLUGIN_NAME . '-settings' )
						);
				}
			// Else display messages for other blog users
			} else {
				$args['msg'] = __( 'The plugin is installed, but has not been configured. Please log in as a WordPress Administrator to set it up.', CALP_PLUGIN_NAME );
			}

      $calp_view_helper->display( 'admin_notices.php', $args );
    }
  }
}
// END class
