<?php

//Deal Filters
// 1) widget
// 2) query hooks

add_action( 'widgets_init', create_function( '', 'return register_widget("SF_GBS_Deal_Filters_Widget");' ) );
class SF_GBS_Deal_Filters_Widget extends WP_Widget {

	function SF_GBS_Deal_Filters_Widget() {
		$widget_ops = array( 'description' => gb__( 'Can only be used on the Deals List Page, otherwise we will gracefully hide the widget.' ) );
		parent::WP_Widget( false, $name = gb__( 'GBS :: Deal Filters (by Studio Fidelis)' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$post_type = get_query_var( 'post_type' );
		if ( sf_on_valid_deal_filter_page() ) {
			echo $before_widget;
			
			if ( !empty( $title ) ) { 
				echo $before_title . $title . $after_title; 
			}

			include('view.php');
			
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
?>
            <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php gb_e( 'Title:' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
	}

}

add_filter('gb_get_locations', 'sf_filter_locations_territories', 10, 1);
function sf_filter_locations_territories( $terms ) {
	if ( is_admin() ) return $terms;
	//Get territory menu and return top level only
	$locations = get_nav_menu_locations();
	$menu = wp_get_nav_menu_object( $locations[ 'sf_territory_menu' ] );
	$top_menu_items = array();
	
	if ( $menu && ! is_wp_error($menu) && !isset($menu_items) ) {
		$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
		foreach ( (array) $menu_items as $menu_item ) {
			if ( !$menu_item->menu_item_parent && $menu_item->object == 'gb_location' ) {
				$top_menu_items[ $menu_item->ID ] = get_term_by('id', $menu_item->object_id, 'gb_location');
			}
		}
	}
	if ( $top_menu_items ) {
		return $top_menu_items;	
	}
	return $terms;
}


register_nav_menus( array('sf_territory_menu' => gb__( 'Territory Menu' ) ) );

class SF_Territory_Menu_Walker extends Walker_Nav_Menu {
	
    // Don't start the second level
    function start_lvl(&$output, $depth=0, $args=array()) {
        //if( $depth > 0 ) return;
        //parent::start_lvl(&$output, $depth,$args);
		$output .= "\n";
    }
 
    function end_lvl(&$output, $depth=0, $args=array()) {
        //parent::end_lvl(&$output, $depth,$args);
		$output .= "\n";
    }
 
    // Don't print second-level elements
    function start_el(&$output, $item, $depth=0, $args=array()) {
		global $sf_territories;
		
        if ( $depth > 0 ) {
			//$sf_territories[] = '';
		} else {
			//parent::start_el(&$output, $item, $depth, $args);
			$selected_items = $_GET['sf_filter_ter'];
			if ( isset( $_GET['sf_filter_ter']) && in_array( $item->ID, $selected_items) ) {
				$checked_item = 'checked="checked"';
			} else {
				$checked_item = '';
			}
			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$output .= '<label for="territory_filter_'.$item->ID.'" class="sf_filter_item_label"><input '.$checked_item.' type="checkbox" name="sf_filter_ter[]" class="sf_filter_item  sf_filter_item_territory sf_filter_checkbox" value="'.$item->ID.'"> '.$title.'</label> ';
		}
    }
 
    function end_el(&$output, $item, $depth=0, $args=array()) {
        if( $depth > 0 ) return;
        //parent::end_el(&$output, $item, $depth, $args);
		$output .= "";
    }
 
    // Only follow down one branch
	/*
    function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
 		global $sf_territories;
        // Check if element as a 'current element' class
        $current_element_markers = array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor' );
        $current_class = array_intersect( $current_element_markers, $element->classes );
 
        // If element has a 'current' class, it is an ancestor of the current element
        $ancestor_of_current = !empty($current_class);
 
        // If this is a top-level link and not the current, or ancestor of the current menu item - stop here.
        if ( $depth == 0 ) {
			$id_field = $this->db_fields['id'];
			$id = $element->$id_field;
			$sf_territories[ $element->ID ] = $children_elements[$id];
		}
 
        //parent::display_element( $element, array(&$children_elements), $max_depth, $depth, $args, array(&$output));
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output);
    }
	*/
}

//On valid deal filter page
function sf_on_valid_deal_filter_page() {
	if ( isset($_REQUEST['sf_filter_ajax']) || is_post_type_archive( 'gb_deal' ) || is_tax( 'gb_category' ) || is_tax( 'gb_tag') || is_tax( 'gb_location') 
		|| is_page_template('page-currentdeals-location.php') || is_page_template('page-currentdeals.php') || is_page_template('page-currentdeals-random-location.php') 
		|| is_page_template('page-donedeals-location.php') || is_page_template('page-donedeals.php') || is_page_template('page-futuredeals-location.php')
		|| is_page_template('page-futuredeals.php') ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

//Filters - add location taxonomy
add_filter('parse_query', 'sf_filter_deal_archives_parse_query', 11 );
function sf_filter_deal_archives_parse_query ( $query ) {
	
   if ( !is_admin() && $query->is_main_query() ) {
	   
		$q_vars = &$query->query_vars;
		if ( isset($q_vars['suppress_filters']) ) {
			return $query;
		}
		if ( !sf_on_valid_deal_filter_page() ) {
			return $query;
		}
		
		$tax_query = array(); //tax query changes
		
		if ( isset( $_GET['sf_filter_loc'] ) && !empty( $_GET['sf_filter_loc'] ) ) {
			//Build tax query
			$tax_query[] = array(
							'taxonomy' => 'gb_location',
							'field' => 'id',
							'terms' => (array)$_GET['sf_filter_loc'],
							'operator' => 'IN'
						);
		} 
		if ( isset( $_GET['sf_filter_avg_price'] ) && !empty( $_GET['sf_filter_avg_price'] ) ) {
			//Build tax query
			$tax_query[] = array(
							'taxonomy' => 'sf_gb_avg_price',
							'field' => 'id',
							'terms' => (array)$_GET['sf_filter_avg_price'],
							'operator' => 'IN'
						);
		} 
		if ( isset( $_GET['sf_filter_cat'] ) && !empty( $_GET['sf_filter_cat'] ) ) {
			//Build tax query
			$tax_query[] = array(
							'taxonomy' => 'gb_category',
							'field' => 'id',
							'terms' => (array)$_GET['sf_filter_cat'],
							'operator' => 'IN'
						);
		} 
		
		//Set any tax query changes
		if ( !empty($tax_query) ) {
			if ( isset($q_vars['tax_query']) && !empty($q_vars['tax_query']) ) {
				$tax_query = array_merge ($tax_query, $q_vars['tax_query']);
			}
			//If more than one tax_query, set relation to and
			if ( sizeof($tax_query) > 1 ) {
				$tax_query = array_merge (array('relation' => 'AND'), $tax_query);
			}
			
			$query->set( 'tax_query', $tax_query );

		}
		
		//Add price filter
		/*
		if ( isset( $_GET['sf_filter_price'] ) && !empty( $_GET['sf_filter_price'] ) ) {
			
			//$prices = explode(',', $_GET['sf_filter_price']);
			
			//Build meta query
			$meta_query = array();
			foreach ( (array)$_GET['sf_filter_price'] as $price ) {
				if ( $price ) {
					$price_pieces = explode('-',$price);
					$meta_query[] = 
						array(
							'key' => '_base_price',
							'value' => array((float)$price_pieces[0], (float)$price_pieces[1]),
							'compare' => 'BETWEEN'
						);
				}
			}
					
			if ( isset($q_vars['meta_query']) && !empty($q_vars['meta_query']) ) {
				$meta_query = array_merge ($meta_query, $q_vars['meta_query']);
			}
			
			$query->set( 'meta_query', $meta_query );

			echo '<br><br>meta_query ';
			var_dump($meta_query );
			
			echo '<br><br>query ';
			var_dump($query );
				
		} 
		*/
		
		
   }

   return $query;
}

