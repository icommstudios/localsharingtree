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

add_filter('gb_list_locations_link', 'custom_sf_moredeals_gb_list_locations_link', 10, 2);
function custom_sf_moredeals_gb_list_locations_link($link, $slug) {
	//Change to /moredeals url
	$url = site_url('/moredeals');
	//Add location query var to set location (setting location is handled by built-in GBS functions)
	$url = add_query_arg( array('location' => $slug), $url);
	return $url;
}


register_nav_menus( array('sf_territory_menu' => gb__( 'Territory Menu' ) ) );

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
	
   if ( !is_admin() && ($query->is_main_query() || $query->query['sf_page_main_query']) ) {
	   
		$q_vars = &$query->query_vars;
		if ( isset($q_vars['suppress_filters']) ) {
			return $query;
		}
		if ( !sf_on_valid_deal_filter_page() ) {
			return $query;
		}
		
		//Do we have any filters to make?
		if ( !isset($_REQUEST['sf_filter_ajax']) ) {
			return $query;
		}
		
		$sf_filters = $_GET;
		/*
		session_start();
		if ( isset($_GET['sf-offer-filter-submitted']) ) {
			$sf_filters = $_GET;
			$_SESSION['sf-offer-filters'] = $sf_filters; //save to session
		} else {
			//Load from session
			$sf_filters = $_SESSION['sf-offer-filters'];
		}
		*/
		
		$tax_query = array(); //tax query changes
		
		//Remove any existing location filter
		unset($query->query['gb_location']);
		unset($q_vars['gb_location']);
		if ( $q_vars['tax_query'] ) {
			foreach ($q_vars['tax_query'] as $tqk => $tq ) {
				if ( isset($tq['taxonomy']) && $tq['taxonomy'] == 'gb_location' ) {
					unset($q_vars['tax_query'][$tqk]);
				}
			}
		}
		//Remove any existing cat filter
		unset($query->query['gb_category']);
		unset($q_vars['gb_category']);
		if ( $q_vars['tax_query'] ) {
			foreach ($q_vars['tax_query'] as $tqk => $tq ) {
				if ( isset($tq['taxonomy']) && $tq['taxonomy'] == 'gb_category' ) {
					unset($q_vars['tax_query'][$tqk]);
				}
			}
		}
		
		//Merge territories (if exist), with locations
		$filter_locations = (array)$sf_filters['sf_filter_ter'];
		if ( isset( $sf_filters['sf_filter_ter'] ) && !empty( $sf_filters['sf_filter_ter'] ) ) {
			$filter_locations = array_merge($filter_locations, (array)$sf_filters['sf_filter_ter']);
		}
		
		if ( !empty( $filter_locations ) ) {
			//Build tax query
			$tax_query[] = array(
							'taxonomy' => 'gb_location',
							'field' => 'id',
							'terms' => (array)$sf_filters['sf_filter_loc'],
							'operator' => 'IN'
						);
			
		} 
		if ( isset( $sf_filters['sf_filter_avg_price'] ) && !empty( $sf_filters['sf_filter_avg_price'] ) ) {
			//Build tax query
			$tax_query[] = array(
							'taxonomy' => 'sf_gb_avg_price',
							'field' => 'id',
							'terms' => (array)$sf_filters['sf_filter_avg_price'],
							'operator' => 'IN'
						);
		} 
		if ( isset( $sf_filters['sf_filter_cat'] ) && !empty( $sf_filters['sf_filter_cat'] ) ) {
			//Build tax query
			$tax_query[] = array(
							'taxonomy' => 'gb_category',
							'field' => 'id',
							'terms' => (array)$sf_filters['sf_filter_cat'],
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
		if ( isset( $sf_filters['sf_filter_price'] ) && !empty( $sf_filters['sf_filter_price'] ) ) {
			
			//$prices = explode(',', $sf_filters['sf_filter_price']);
			
			//Build meta query
			$meta_query = array();
			foreach ( (array)$sf_filters['sf_filter_price'] as $price ) {
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

